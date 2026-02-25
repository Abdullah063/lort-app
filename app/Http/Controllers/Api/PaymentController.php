<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PackageCongratsMail;
use App\Models\MembershipHistory;
use App\Models\Payment;
use App\Models\Membership;
use App\Models\PackageDefinition;
use App\Services\KuveytTurkService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    public function __construct(private KuveytTurkService $kuveytTurk) {}

    // =============================================
    // ÖDEME BAŞLAT
    // POST /api/payment/start
    // =============================================
    public function start(Request $request)
    {
        $request->validate([
            'package_id'  => 'required|exists:package_definitions,id',
            'period'      => 'required|in:monthly,yearly',
            'card_number' => 'required|string|size:16',
            'card_month'  => 'required|string|size:2',
            'card_year'   => 'required|string|size:2',
            'card_cvv'    => 'required|string|min:3|max:4',
            'card_holder' => 'required|string',
            'installment' => 'nullable|integer|min:0|max:12',
        ]);

        $user    = auth('api')->user();
        $package = PackageDefinition::findOrFail($request->package_id);

        // Tutarı hesapla (kuruş cinsinden)
        $amount = $request->period === 'yearly'
            ? $package->yearly_price * 100
            : $package->monthly_price * 100;

        $amount = (int) $amount;

        // Benzersiz sipariş ID oluştur
        $orderId = 'ORD' . time() . Str::upper(Str::random(4));

        // Ödeme kaydı oluştur (pending)
        $membership = $user->memberships()->create([
            'package_id' => $package->id,
            'starts_at'  => now(),
            'is_active'  => false, // callback sonrası aktif edilecek
        ]);

        $payment = Payment::create([
            'user_id'        => $user->id,
            'membership_id'  => $membership->id,
            'amount'         => $amount / 100,
            'currency'       => 'TRY',
            'installment'    => $request->installment ?? 1,
            'status'         => 'pending',
            'payment_method' => 'credit_card',
            'provider_meta'  => [
                'order_id'   => $orderId,
                'package_id' => $package->id,
                'period'     => $request->period,
            ],
        ]);

        // 3D Secure HTML formunu al
        $html = $this->kuveytTurk->initPayment([
            'order_id'    => $orderId,
            'amount'      => $amount,
            'card_number' => $request->card_number,
            'card_month'  => $request->card_month,
            'card_year'   => $request->card_year,
            'card_cvv'    => $request->card_cvv,
            'card_holder' => $request->card_holder,
            'installment' => $request->installment ?? 0,
        ]);

        // HTML form döndür - frontend bunu webview veya modal içinde gösterir
        return response()->json([
            'payment_id' => $payment->id,
            'html'       => $html,
        ]);
    }

    // =============================================
    // ÖDEME CALLBACK - BAŞARILI
    // POST /api/payment/callback/ok
    // Kuveyt Türk bu URL'e POST atar
    // =============================================
    public function callbackOk(Request $request)
    {
        $authResponse = $request->input('AuthenticationResponse');

        if (!$authResponse) {
            return $this->redirectWithError('Geçersiz callback');
        }

        // Double decode
        $authResponse = urldecode($authResponse);

        $result = $this->kuveytTurk->confirmPayment($authResponse);

        // Order ID'yi response'dan çıkar
        $decoded = @simplexml_load_string(urldecode($authResponse));
        $orderId = $decoded ? (string) $decoded->MerchantOrderId : null;

        $payment = Payment::whereJsonContains('provider_meta->order_id', $orderId)->first();

        if (!$payment) {
            return $this->redirectWithError('Ödeme bulunamadı');
        }

        if (!$result['success']) {
            $payment->update([
                'status'        => 'failed',
                'error_code'    => $result['code'] ?? null,
                'error_message' => $result['message'] ?? 'Bilinmeyen hata',
            ]);

            return $this->redirectWithError($result['message']);
        }

        // Ödeme başarılı
        $payment->update([
            'status'       => 'completed',
            'provider_ref' => $result['provider_ref'],
            'provider_meta' => array_merge($payment->provider_meta ?? [], $result['provider_meta']),
        ]);

        // Üyeliği aktif et
        $membership = $payment->membership;
        $period     = $payment->provider_meta['period'] ?? 'monthly';

        $membership->update([
            'is_active' => true,
            'starts_at' => now(),
            'expires_at' => $period === 'yearly' ? now()->addYear() : now()->addMonth(),
        ]);

        // Eski üyelikleri pasif et
        $payment->user->memberships()
            ->where('id', '!=', $membership->id)
            ->update(['is_active' => false]);

        MembershipHistory::create([
            'membership_id'      => $membership->id,
            'user_id'            => $payment->user_id,
            'previous_package_id' => $payment->user->memberships()
                ->where('id', '!=', $membership->id)
                ->where('is_active', false)
                ->latest()
                ->value('package_id'),
            'new_package_id'     => $membership->package_id,
            'change_reason'      => 'purchase',
            'description'        => $payment->amount . ' TL ödeme ile ' . ($period === 'yearly' ? 'yıllık' : 'aylık') . ' paket satın alındı.',
        ]);
        // Bildirim gönder
        NotificationService::send($payment->user_id, 'package_congrats', [
            'package_name' => $membership->packageDefinition->display_name ?? '',
        ]);

        Mail::to($payment->user->email)->queue(new PackageCongratsMail(
            user: $payment->user,
            packageName: $membership->packageDefinition->display_name ?? $membership->packageDefinition->name,
            period: $period,
            amount: (string) $payment->amount,
            expiresAt: $membership->expires_at->format('d.m.Y'),
            lang: $payment->user->lang ?? 'tr',
        ));


        return $this->redirectWithSuccess($payment->id);
    }

    // =============================================
    // ÖDEME CALLBACK - BAŞARISIZ
    // POST /api/payment/callback/fail
    // =============================================
    public function callbackFail(Request $request)
    {
        $authResponse = $request->input('AuthenticationResponse');

        if ($authResponse) {
            $decodedXml = urldecode($authResponse);
            while (strpos($decodedXml, '<?xml') === false && strpos($decodedXml, '%3c') !== false) {
                $decodedXml = urldecode($decodedXml);
            }

            $decoded = @simplexml_load_string($decodedXml);
            $orderId = $decoded ? (string) $decoded->MerchantOrderId : null;

            $payment = Payment::whereJsonContains('provider_meta->order_id', $orderId)->first();

            if ($payment) {
                $responseCode    = (string) $decoded->ResponseCode;
                $responseMessage = (string) $decoded->ResponseMessage;

                $payment->update([
                    'status'        => 'failed',
                    'error_code'    => $responseCode,
                    'error_message' => $responseMessage,
                ]);

                // Üyeliği sil
                $payment->membership()->update(['is_active' => false]);
            }
        }

        return $this->redirectWithError('Ödeme başarısız');
    }

    // =============================================
    // ÖDEME GEÇMİŞİ
    // GET /api/payment/history
    // =============================================
    public function history()
    {
        $payments = Payment::where('user_id', auth('api')->id())
            ->with('membership.packageDefinition')
            ->latest()
            ->get();

        return response()->json(['payments' => $payments]);
    }

    // =============================================
    // YARDIMCI: Başarılı yönlendirme
    // =============================================
    private function redirectWithSuccess(int $paymentId)
    {
        $url = config('app.frontend_url') . '/payment/success?payment_id=' . $paymentId;
        return redirect($url);
    }

    // =============================================
    // YARDIMCI: Başarısız yönlendirme
    // =============================================
    private function redirectWithError(string $message)
    {
        $url = config('app.frontend_url') . '/payment/fail?message=' . urlencode($message);
        return redirect($url);
    }
}
