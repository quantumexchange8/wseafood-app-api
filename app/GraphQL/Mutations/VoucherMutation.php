<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Enums\VoucherType;
use App\Models\Voucher;
use App\Services\VoucherRedemptionService;
use Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

final readonly class VoucherMutation
{
    /**
     * @param array{} $args
     * @throws Throwable
     */
    public function __invoke(null $_, array $args): array
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
            $user = Auth::user();

            $service = app(VoucherRedemptionService::class);
            $service->redeem($user, $voucher);

            return [
                'success' => true,
                'message' => [
                    trans('public.successfully_redeemed_voucher')
                ],
            ];
        } catch (Throwable $e) {
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
