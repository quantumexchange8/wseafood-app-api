<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Enums\VoucherType;
use App\Models\PointLog;
use App\Models\UserVoucherRedemption;
use App\Models\Voucher;
use Auth;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

final readonly class VoucherMutation
{
    /**
     * @param array{} $args
     * @throws Throwable
     */
    public function __invoke(null $_, array $args)
    {
        $validator = Validator::make($args['input'], [
            'voucher_id' => ['required']
        ])->setAttributeNames([
            'voucher_id' => trans('public.voucher'),
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->errors()->all(),
            ];
        }

        $voucher = Voucher::where('claim_method', VoucherType::POINT_TO_CLAIM)
            ->find($args['input']['voucher_id']);

        if (!$voucher) {
            return [
                'success' => false,
                'message' => [
                    trans('public.voucher_not_found'),
                ],
            ];
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();

            // Example: deduct points if needed
            if ($voucher->claim_method == VoucherType::POINT_TO_CLAIM) {
                if ($user->point < $voucher->redeem_point) {
                    throw new Exception(__('public.insufficient_point'));
                }

                $new_point = $user->point + (-$voucher->redeem_point);

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

            $redemption = UserVoucherRedemption::create([
                'user_id'        => $user->id,
                'voucher_id'     => $voucher->id,
                'claimed_method' => $voucher->claim_method,
                'redeemed_at'    => now(),
                'status'         => VoucherType::REDEEMED,
            ]);

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

            DB::commit();

            return [
                'success' => true,
                'message' => [
                    trans('public.successfully_redeemed_voucher')
                ],
            ];
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error($e->getMessage());

            return [
                'success' => false,
                'message' => [
                     $e->getMessage(),
                ],
            ];
        }
    }
}
