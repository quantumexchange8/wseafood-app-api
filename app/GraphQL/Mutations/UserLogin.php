<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

final readonly class UserLogin
{
    /** @param  array{}  $args */
    public function __invoke(null $_, array $args): array
    {
        $validator = Validator::make($args['input'], [
            'dial_code' => ['required'],
            'phone' => [
                'required',
                function ($attribute, $value, $fail) use ($args) {
                    $dialCode = preg_replace('/\D/', '', $args['input']['dial_code']); // only digits
                    $phone = $value;

                    // Normalize input phone
                    $normalizedPhone = preg_replace('/[^0-9]/', '', $phone);

                    // Remove leading 0 or country code
                    if (str_starts_with($normalizedPhone, '0')) {
                        $normalizedPhone = substr($normalizedPhone, 1);
                    } elseif (str_starts_with($normalizedPhone, $dialCode)) {
                        $normalizedPhone = substr($normalizedPhone, strlen($dialCode));
                    }

                    // Final normalized value to check: dialCode + normalizedPhone
                    $finalPhone = $dialCode . $normalizedPhone;

                    // Check existence in DB
                    $exists = User::whereRaw("CONCAT(dial_code, REPLACE(REPLACE(REPLACE(phone, '+', ''), ' ', ''), '-', '')) = ?", [$finalPhone])->exists();

                    if (!$exists) {
                        $fail('This phone number is not registered.');
                    }
                }
            ],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->errors()->all(),
                'token' => null,
                'user' => null,
            ];
        }

        $user = User::where('dial_code', $args['input']['dial_code'])
            ->where('phone', $args['input']['phone'])
            ->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => [
                    'User does not exist',
                ],
                'token' => null,
                'user' => null,
            ];
        }

        if (!Hash::check($args['input']['password'], $user->password)) {
            return [
                'success' => false,
                'message' => [
                    'Invalid password entered',
                ],
                'token' => null,
                'user' => null,
            ];
        }

        $token = $user->createToken('login-token')->plainTextToken;

        return [
            'success' => true,
            'message' => [
                'Login successfully',
            ],
            'token' => $token,
            'user' => $user,
        ];
    }
}
