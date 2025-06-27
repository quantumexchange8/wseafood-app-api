<?php declare(strict_types=1);

namespace App\GraphQL\Mutations\Auth;

use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\SendOtpNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Notification;
use Random\RandomException;

final readonly class ResetPassword
{
    /** @param  array{}  $args
     * @throws RandomException
     */
    public function sendPasswordResetEmail(null $_, array $args): array
    {
        $validator = Validator::make($args['input'], [
            'email' => ['required', 'email'],
        ])->setAttributeNames([
            'email' => trans('public.email'),
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->errors()->all(),
            ];
        }

        $email = $args['input']['email'];
        $exists = User::where('email', $email)->exists();

        if (!$exists) {
            return [
                'success' => false,
                'message' => [trans('passwords.user')],
            ];
        }

        // Check if recent code exists for this email within 1 minute
        $recentOtp = OtpCode::where('email', $email)
            ->where('updated_at', '>=', now()->subMinute())
            ->first();

        if ($recentOtp) {
            return [
                'success' => false,
                'message' => [trans('public.send_otp_every_min')],
            ];
        }

        $otp = random_int(100000, 999999);

        OtpCode::updateOrCreate(
            ['email' => $email],
            [
                'code'       => $otp,
                'expires_at' => now()->addMinutes(5),
            ]
        );

        Notification::route('mail', $email)
            ->notify(new SendOtpNotification($otp));

        return [
            'success' => true,
            'message' => [
                trans('public.sent_otp')
            ],
            'user' => [
                'email' => $email,
            ]
        ];
    }

    public function verifyPasswordResetEmail(null $_, array $args): array
    {
        $validator = Validator::make($args['input'], [
            'email' => ['required', 'email'],
            'otp_code' => ['required'],
        ])->setAttributeNames([
            'email' => trans('public.email'),
            'otp_code' => trans('public.otp_code'),
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->errors()->all(),
            ];
        }

        $email = $args['input']['email'];
        $otp_code = $args['input']['otp_code'];

        $exists = OtpCode::where('email', $email)->exists();

        if (!$exists) {
            return [
                'success' => false,
                'message' => [trans('passwords.user')],
            ];
        }

        OtpCode::where('expires_at', '<', now())
            ->delete();

        $record = OtpCode::where('email', $email)
            ->where('code', $otp_code)
            ->where('expires_at', '>=', now())
            ->first();

        if (!$record) {
            return [
                'success' => false,
                'message' => [
                    trans('public.otp_expired')
                ],
            ];
        }

        return [
            'success' => true,
            'message' => [
                trans('public.otp_verified')
            ],
            'token' => $otp_code,
            'user' => [
                'email' => $email,
            ]
        ];
    }

    public function updateNewPassword(null $_, array $args): array
    {
        $validator = Validator::make($args['input'], [
            'email' => ['required', 'email'],
            'otp_code' => ['required'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()->mixedCase()],
        ])->setAttributeNames([
            'email' => trans('public.email'),
            'otp_code' => trans('public.otp_code'),
            'password' => trans('public.password'),
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->errors()->all(),
            ];
        }

        $email = $args['input']['email'];
        $otp_code = $args['input']['otp_code'];
        $password = $args['input']['password'];

        $record = OtpCode::where('email', $email)
            ->where('code', $otp_code)
            ->first();

        if (!$record) {
            return [
                'success' => false,
                'message' => [
                    trans('public.invalid_otp')
                ],
            ];
        }

        $user = User::firstWhere('email', $email);

        if (!$user) {
            return [
                'success' => false,
                'message' => [
                    trans('public.user_does_not_exist'),
                ],
            ];
        }

        $user->update([
            'password' => Hash::make($args['input']['password']),
        ]);

        return [
            'success' => true,
            'message' => [trans('passwords.reset')],
        ];
    }
}
