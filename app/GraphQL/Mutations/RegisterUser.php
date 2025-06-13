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
                    $dialCode = preg_replace('/\D/', '', $args['input']['dial_code']); // only digits
                    $phone = $value;

                    // Normalize input phone:
                    $normalizedPhone = preg_replace('/[^0-9]/', '', $phone); // remove non-digits

                    // Remove leading 0 or country code (if already present)
                    if (str_starts_with($normalizedPhone, '0')) {
                        $normalizedPhone = substr($normalizedPhone, 1);
                    } elseif (str_starts_with($normalizedPhone, $dialCode)) {
                        $normalizedPhone = substr($normalizedPhone, strlen($dialCode));
                    }

                    // Final normalized value to check: dialCode + normalizedPhone
                    $finalPhone = $dialCode . $normalizedPhone;

                    // Check existence in DB
                    $exists = User::whereRaw("CONCAT(dial_code, REPLACE(REPLACE(REPLACE(phone, '+', ''), ' ', ''), '-', '')) = ?", [$finalPhone])->exists();

                    if ($exists) {
                        $fail('This phone number has already been registered.');
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

        $user = User::create([
            'full_name' => $args['input']['full_name'],
            'email' => $args['input']['email'],
            'dial_code' => $args['input']['dial_code'],
            'phone' => $args['input']['phone'],
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
