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
                    $dialCode = $args['input']['dial_code']; // e.g. "+60"
                    $dialCodeNumeric = preg_replace('/\D/', '', $dialCode); // "60"
                    $phone = $args['input']['phone'];

                    // Normalize input phone: remove non-digits
                    $normalizedPhone = preg_replace('/[^0-9]/', '', $phone);

                    // Remove leading '0' if present
                    if (str_starts_with($normalizedPhone, '0')) {
                        $normalizedPhone = substr($normalizedPhone, 1);
                    }

                    // If phone starts with dial code, strip it
                    if (str_starts_with($normalizedPhone, $dialCodeNumeric)) {
                        $normalizedPhone = substr($normalizedPhone, strlen($dialCodeNumeric));
                    }

                    // Final normalized phone_number: dialCode + normalizedPhone
                    $finalPhoneNumber = $dialCode . $normalizedPhone;

                    // Check existence in DB
                    $exists = User::where('phone_number', $finalPhoneNumber)->exists();

                    if (!$exists) {
                        $fail("The phone number $finalPhoneNumber has not been registered.");
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

        $dialCode = $args['input']['dial_code']; // e.g. "+60"
        $dialCodeNumeric = preg_replace('/\D/', '', $dialCode); // "60"
        $phone = $args['input']['phone'];

        // Normalize input phone: remove non-digits
        $normalizedPhone = preg_replace('/[^0-9]/', '', $phone);

        // Remove leading '0' if present
        if (str_starts_with($normalizedPhone, '0')) {
            $normalizedPhone = substr($normalizedPhone, 1);
        }

        // If phone starts with dial code, strip it
        if (str_starts_with($normalizedPhone, $dialCodeNumeric)) {
            $normalizedPhone = substr($normalizedPhone, strlen($dialCodeNumeric));
        }

        // Final phone number: dial_code + normalizedPhone
        $finalPhoneNumber = $dialCode . $normalizedPhone;

        $user = User::firstWhere('phone_number', $finalPhoneNumber);

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
