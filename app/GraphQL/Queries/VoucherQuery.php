<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Enums\VoucherType;
use App\Models\Voucher;

final readonly class VoucherQuery
{
    /** @param  array{}  $args */
    public function getVouchers(null $_, array $args): array
    {
        $vouchers = Voucher::with('media')
            ->where([
                'claim_method' => VoucherType::POINT_TO_CLAIM,
                'status' => 'active',
            ])
            ->orderBy('redeem_point')
            ->get();

        return [
            'success' => true,
            'message' => [trans('public.successfully_fetched_vouchers')],
            'data' => $vouchers,
        ];
    }

    /** @param array{} $args
     */
    public function getVoucherDetail(null $_, array $args): array
    {
        $voucher = Voucher::with('media')
            ->where('status', 'active')
            ->find($args['voucher_id']);

        if (!$voucher) {
            return [
                'success' => false,
                'message' => [trans('public.voucher_not_found')],
                'data' => null,
            ];
        }

//        event(new ContentViewedEvent($voucher, 'detail'));

        return [
            'success' => true,
            'message' => [trans('public.successfully_fetched_voucher')],
            'data' => $voucher,
        ];
    }
}
