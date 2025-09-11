<?php

namespace App\Enums;

enum VoucherType
{
    // Claim Method
    const POINT_TO_CLAIM = 'point_to_claim';
    const CODE_TO_CLAIM = 'code_to_claim';
    const ADD_FOR_MEMBER = 'add_for_member';

    // Add for Member Rule Type
    const FIRST_TIME_REGISTRATION = 'first_time_registration';
    const EVENT_BASED = 'event_based';
    const MEMBER_BIRTHDAY = 'member_birthday';
    const SPECIAL_HOLIDAY = 'special_holiday';

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

    // Status
    const ACTIVE = 'active';
    const SCHEDULE = 'schedule';
    const ENDED = 'ended';
    const FULLY_CLAIMED = 'fully_claimed';
    const INACTIVE = 'inactive';
}
