<?php

namespace App\Enums;

enum VoucherType
{
    // Claim Method
    const POINT_TO_CLAIM = 'point_to_claim';
    const CODE_TO_CLAIM = 'code_to_claim';
    const ADD_FOR_MEMBER = 'add_for_member';

    // Claim Limit
    const LIMITED = 'limited';
    const UNLIMITED = 'unlimited';

    // Valid Types
    const ALL_DAY_TIME = 'all_day_time';
    const SPECIFIC_DAY = 'specific_day';
    const SPECIFIC_TIME = 'specific_time';
    const SPECIFIC_DAY_TIME = 'specific_day_time';

    // Redemption Type
    const REDEEMED = 'redeemed';
    const EXPIRED = 'expired';
    const USED = 'used';
}
