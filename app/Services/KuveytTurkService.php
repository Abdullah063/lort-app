<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class KuveytTurkService
{
    private string $merchantId;
    private string $customerId;
    private string $userName;
    private string $password;
    private string $apiUrl;
    private string $okUrl;
    private string $failUrl;

    public function __construct()
    {
        $this->merchantId = config('kuveytturk.merchant_id');
        $this->customerId = config('kuveytturk.customer_id');
        $this->userName   = config('kuveytturk.username');
        $this->password   = config('kuveytturk.password');
        $this->apiUrl     = config('kuveytturk.api_url');
        $this->okUrl      = config('kuveytturk.ok_url');
        $this->failUrl    = config('kuveytturk.fail_url');
    }

    // =============================================
    // ÖDEME BAŞLAT (3D Secure - Request 1)
    // HTML form döner, frontend bunu kullanıcıya gösterir
    // =============================================
    public function initPayment(array $data): string
    {
        $merchantOrderId = $data['order_id'];
        $amount          = $data['amount']; // Kuruş cinsinden (100 TL = 10000)
        $cardNumber      = $data['card_number'];
        $cardMonth       = $data['card_month'];
        $cardYear        = $data['card_year'];
        $cardCvv         = $data['card_cvv'];
        $cardHolder      = $data['card_holder'];
        $installment     = $data['installment'] ?? 0;

        $hashData = $this->generateHash($merchantOrderId, $amount);

        $xml = $this->buildPaymentXml([
            'hashData'       => $hashData,
            'merchantOrderId' => $merchantOrderId,
            'amount'         => $amount,
            'cardNumber'     => $cardNumber,
            'cardMonth'      => $cardMonth,
            'cardYear'       => $cardYear,
            'cardCvv'        => $cardCvv,
            'cardHolder'     => $cardHolder,
            'installment'    => $installment,
        ]);

        $response = Http::withHeaders(['Content-Type' => 'text/xml'])
            ->withBody($xml, 'text/xml')
            ->post($this->apiUrl);

        return $response->body(); // HTML form döner, frontend'e gönderilir
    }

    // =============================================
    // ÖDEME ONAYLA (3D Secure - Request 2)
    // Kuveyt Türk OkUrl'e POST attıktan sonra çağrılır
    // =============================================
    public function confirmPayment(string $authResponse): array
    {
        $decoded = $authResponse;

        // XML olana kadar decode et
        while (strpos($decoded, '<?xml') === false && strpos($decoded, '%3c') !== false) {
            $decoded = urldecode($decoded);
        }

        $xml = @simplexml_load_string($decoded);

        if (!$xml) {
            return ['success' => false, 'message' => 'Geçersiz response'];
        }

        $responseCode = (string) $xml->ResponseCode;

        if ($responseCode !== '00') {
            return [
                'success'  => false,
                'message'  => (string) $xml->ResponseMessage,
                'code'     => $responseCode,
            ];
        }

        // Onay isteği gönder
        $mdStatus = (string) ($xml->MD ?? $xml->MDStatus->MD ?? '');

        \Log::info('MD debug', [
            'md_direct' => (string) $xml->MD,
            'md_from_mdstatus' => isset($xml->MDStatus) ? (string) $xml->MDStatus->MD : 'yok',
        ]);
        $orderId     = (string) $xml->OrderId;
        $merchantOrderId = (string) $xml->MerchantOrderId;
        $amount      = (string) $xml->VPosMessage->Amount;

        $hashData = $this->generateConfirmHash($merchantOrderId, $amount);

        \Log::info('KuveytTurk parsed values', [
            'responseCode' => $responseCode,
            'md'           => $mdStatus,
            'orderId'      => $orderId,
            'merchantOrderId' => $merchantOrderId,
            'amount'       => $amount,
        ]);
        $confirmXml = $this->buildConfirmXml([
            'hashData'       => $hashData,
            'merchantOrderId' => $merchantOrderId,
            'amount'         => $amount,
            'md'             => $mdStatus,
        ]);

        \Log::info('Confirm XML', ['xml' => $confirmXml]);
        $confirmUrl = 'https://boatest.kuveytturk.com.tr/boa.virtualpos.services/Home/ThreeDModelProvisionGate';

        $response = Http::withHeaders([
            'Content-Type' => 'application/xml',
            'Content-Length' => strlen($confirmXml),
        ])
            ->withBody($confirmXml, 'application/xml')
            ->post($confirmUrl);

        \Log::info('KuveytTurk confirm response', [
            'status' => $response->status(),
            'body'   => substr($response->body(), 0, 1000),
        ]);

        $resultBody = $response->body();
        while (strpos($resultBody, '<?xml') === false && strpos($resultBody, '%3c') !== false) {
            $resultBody = urldecode($resultBody);
        }
        $result = @simplexml_load_string($resultBody);
        if (!$result) {
            return ['success' => false, 'message' => 'Onay response hatası'];
        }

        $resultCode = (string) $result->ResponseCode;

        return [
            'success'          => $resultCode === '00',
            'message'          => (string) $result->ResponseMessage,
            'code'             => $resultCode,
            'provider_ref'     => (string) $result->OrderId,
            'provider_meta'    => [
                'merchant_order_id' => $merchantOrderId,
                'rrn'               => (string) $result->RRN,
            ],
        ];
    }

    // =============================================
    // HASH HESAPLA (Request 1)
    // MerchantId + MerchantOrderId + Amount + OkUrl + FailUrl + UserName + hashedPassword
    // =============================================
    private function generateHash(string $merchantOrderId, string $amount): string
    {
        $hashedPassword = base64_encode(sha1($this->password, true));

        return base64_encode(
            sha1(
                $this->merchantId .
                    $merchantOrderId .
                    $amount .
                    $this->okUrl .
                    $this->failUrl .
                    $this->userName .
                    $hashedPassword,
                true
            )
        );
    }

    // =============================================
    // HASH HESAPLA (Request 2 - Onay)
    // =============================================
    private function generateConfirmHash(string $merchantOrderId, string $amount): string
    {
        $hashedPassword = base64_encode(sha1($this->password, true));

        return base64_encode(
            sha1(
                $this->merchantId .
                    $merchantOrderId .
                    $amount .
                    $this->userName .
                    $hashedPassword,
                true
            )
        );
    }

    // =============================================
    // XML OLUŞTUR (Request 1)
    // =============================================
    private function buildPaymentXml(array $data): string
    {
        return '<?xml version="1.0" encoding="utf-8"?>
        <KuveytTurkVPosMessage xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema">
            <APIVersion>1.0.0</APIVersion>
            <OkUrl>' . $this->okUrl . '</OkUrl>
            <FailUrl>' . $this->failUrl . '</FailUrl>
            <HashData>' . $data['hashData'] . '</HashData>
            <MerchantId>' . $this->merchantId . '</MerchantId>
            <CustomerId>' . $this->customerId . '</CustomerId>
            <UserName>' . $this->userName . '</UserName>
            <CardNumber>' . $data['cardNumber'] . '</CardNumber>
            <CardExpireDateYear>' . $data['cardYear'] . '</CardExpireDateYear>
            <CardExpireDateMonth>' . $data['cardMonth'] . '</CardExpireDateMonth>
            <CardCVV2>' . $data['cardCvv'] . '</CardCVV2>
            <CardHolderName>' . $data['cardHolder'] . '</CardHolderName>
            <CardType>MasterCard</CardType>
            <BatchID>0</BatchID>
            <TransactionType>Sale</TransactionType>
            <InstallmentCount>' . $data['installment'] . '</InstallmentCount>
            <Amount>' . $data['amount'] . '</Amount>
            <DisplayAmount>' . $data['amount'] . '</DisplayAmount>
            <CurrencyCode>0949</CurrencyCode>
            <MerchantOrderId>' . $data['merchantOrderId'] . '</MerchantOrderId>
            <TransactionSecurity>3</TransactionSecurity>
        </KuveytTurkVPosMessage>';
    }

    // =============================================
    // XML OLUŞTUR (Request 2 - Onay)
    // =============================================
    private function buildConfirmXml(array $data): string
    {
        return '<KuveytTurkVPosMessage xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xmlns:xsd="http://www.w3.org/2001/XMLSchema">
        <APIVersion>1.0.0</APIVersion>
        <HashData>' . $data['hashData'] . '</HashData>
        <MerchantId>' . $this->merchantId . '</MerchantId>
        <CustomerId>' . $this->customerId . '</CustomerId>
        <UserName>' . $this->userName . '</UserName>
        <TransactionType>Sale</TransactionType>
        <InstallmentCount>0</InstallmentCount>
        <Amount>' . $data['amount'] . '</Amount>
        <MerchantOrderId>' . $data['merchantOrderId'] . '</MerchantOrderId>
        <TransactionSecurity>3</TransactionSecurity>
        <KuveytTurkVPosAdditionalData>
            <AdditionalData>
                <Key>MD</Key>
                <Data>' . $data['md'] . '</Data>
            </AdditionalData>
        </KuveytTurkVPosAdditionalData>
    </KuveytTurkVPosMessage>';
    }
}
