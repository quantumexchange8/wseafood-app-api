<?php

namespace App\Services;

use App\Models\User;
use App\Models\Voucher;
use App\Models\UserVoucherRedemption;
use App\Models\PointLog;
use App\Enums\VoucherType;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class VoucherRedemptionService
{
    /**
     * Redeem a voucher for a user.
     *
     * @throws Throwable
     */
    public function redeem(User $user, Voucher $voucher): UserVoucherRedemption
    {
        return DB::transaction(function () use ($user, $voucher) {
            // Lock for update
            $voucher = Voucher::where('id', $voucher->id)->lockForUpdate()->firstOrFail();

            // Check voucher limit
            if ($voucher->claim_limit == 'limited' && $voucher->voucher_limit > 0) {
                $totalClaimed = $voucher->redemptions()->count();

                if ($totalClaimed > $voucher->voucher_limit) {
                    throw new Exception(__('public.voucher_limit_reached'));
                }
            }

            // Check member limit
            if ($voucher->claim_amount_per_member > 0) {
                $userClaimed = $voucher->redemptions()
                    ->where('user_id', $user->id)
                    ->count();

                if ($userClaimed >= $voucher->claim_amount_per_member) {
                    throw new Exception(__('public.voucher_member_limit_reached'));
                }
            }

            // Deduct points if needed
            if ($voucher->claim_method == VoucherType::POINT_TO_CLAIM) {
                if ($user->point < $voucher->redeem_point) {
                    throw new Exception(__('public.insufficient_point'));
                }

                $new_point = $user->point - $voucher->redeem_point;

                PointLog::create([
                    'user_id'       => $user->id,
                    'type'          => 'redemption',
                    'adjust_type'   => 'point_out',
                    'amount'        => -$voucher->redeem_point,
                    'earning_point' => -$voucher->redeem_point,
                    'old_point'     => $user->point,
                    'new_point'     => $new_point,
                    'remark'        => 'Redeem voucher - ' . $voucher->voucher_name,
                ]);

                $user->decrement('point', $voucher->redeem_point);
            }

            // Create redemption
            $redemption = UserVoucherRedemption::create([
                'user_id'        => $user->id,
                'voucher_id'     => $voucher->id,
                'claimed_method' => $voucher->claim_method,
                'redeemed_at'    => now(),
                'status'         => VoucherType::REDEEMED,
            ]);

            // Calculate expiry if applicable
            if ($voucher->validity_count > 0) {
                $expiredAt = match ($voucher->validity_count_type) {
                    'day', 'days'     => now()->addDays($voucher->validity_count),
                    'month', 'months' => now()->addMonths($voucher->validity_count),
                    'year', 'years'   => now()->addYears($voucher->validity_count),
                    default           => now(),
                };

                $redemption->update([
                    'expired_at' => $expiredAt->endOfDay(),
                ]);
            }

            // Check total count again & update voucher status if fully claimed
            if ($voucher->claim_limit == 'limited' && $voucher->voucher_limit > 0) {
                // Use fresh count after new redemption
                $totalClaimed = $voucher->redemptions()->count();

                if ($totalClaimed >= $voucher->voucher_limit) {
                    $voucher->status = VoucherType::FULLY_CLAIMED;
                    $voucher->save();
                }
            }

            return $redemption;
        });
    }
}
