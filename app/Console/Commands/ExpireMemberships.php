<?php

namespace App\Console\Commands;

use App\Models\Membership;
use App\Models\MembershipHistory;
use Illuminate\Console\Command;

class ExpireMemberships extends Command
{
    protected $signature = 'memberships:expire';
    protected $description = 'Süresi dolan üyelikleri pasif et';

    public function handle()
    {
        $expiring = Membership::where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($expiring as $membership) {
            $membership->update(['is_active' => false]);

            MembershipHistory::create([
                'membership_id'      => $membership->id,
                'user_id'            => $membership->user_id,
                'previous_package_id' => $membership->package_id,
                'new_package_id'     => null,
                'change_reason'      => 'expired',
                'description'        => 'Üyelik süresi doldu.',
            ]);
        }

        $this->info("{$expiring->count()} üyelik pasif edildi.");
    }
}
