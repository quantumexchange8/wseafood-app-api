<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\User;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

final readonly class RegisterUser
{
    /** @param array{} $args
     */
    public function __invoke(null $_, array $args): array
    {
        $validator = Validator::make($args['input'], [
            'full_name' => ['required', 'regex:/^[\p{L}\p{N}\p{M}. @]+$/u', 'max:255'],
            'dial_code' => ['required'],
            'phone' => [
                'required',
                function ($attribute, $value, $fail) use ($args) {
                    $dialCode = $args['input']['dial_code'];
                    $dialCodeNumeric = preg_replace('/\D/', '', $dialCode);
                    $phone = $value;

                    $normalizedPhone = preg_replace('/[^0-9]/', '', $phone);

                    if (str_starts_with($normalizedPhone, '0')) {
                        $normalizedPhone = substr($normalizedPhone, 1);
                    }

                    if (str_starts_with($normalizedPhone, $dialCodeNumeric)) {
                        $normalizedPhone = substr($normalizedPhone, strlen($dialCodeNumeric));
                    }

                    $finalPhoneNumber = $dialCode . $normalizedPhone;

                    $exists = User::where('phone_number', $finalPhoneNumber)->exists();

                    if ($exists) {
                        $fail("The phone number $finalPhoneNumber has already been registered.");
                    }
                }
            ],
            'email' => ['required', 'email', 'unique:users'],
            'dob' => ['required'],
            'password' => ['required', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->errors()->all(),
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

        $user = User::create([
            'full_name' => $args['input']['full_name'],
            'email' => $args['input']['email'],
            'dial_code' => $args['input']['dial_code'],
            'phone' => $normalizedPhone,
            'phone_number' => $finalPhoneNumber,
            'dob' => $args['input']['dob'],
            'password' => Hash::make($args['input']['password']),
        ]);

        return [
            'success' => true,
            'message' => ['User created successfully'],
            'user' => $user
        ];
    }
}
