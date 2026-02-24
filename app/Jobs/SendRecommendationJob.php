<?php

namespace App\Jobs;

use App\Mail\RecommendationMail;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendRecommendationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 60, 120];

    public function __construct(
        public array $profile,
        public string $email,
        public string $name,
        public string $lang = 'en'
    ) {}

    public function handle(): void
    {
        $recommendation = RecommendationService::generate($this->profile, $this->lang);

        if (!$recommendation) {
            Log::warning("Recommendation failed for {$this->email}, attempt {$this->attempts()}/{$this->tries}");
            throw new \RuntimeException("Gemini API failed for {$this->email}");
        }

        $user = new User();
        $user->name = $this->name;
        $user->email = $this->email;

        Mail::to($this->email)->send(new RecommendationMail($user, $recommendation, $this->lang));

        Log::info("Recommendation sent to {$this->email}");
    }

    public function failed(\Throwable $e): void
    {
        Log::error("Recommendation completely failed for {$this->email}: {$e->getMessage()}");
    }
}