<?php declare(strict_types=1);

namespace App\GraphQL\Mutations\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

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
                        $fail(trans('public.phone_registered', [
                            'number' => $finalPhoneNumber,
                        ]));
                    }
                }
            ],
            'email' => ['required', 'email', 'unique:users'],
            'dob' => ['required'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()->mixedCase()],
        ])->setAttributeNames([
            'full_name' => trans('public.full_name'),
            'dial_code' => trans('public.dial_code'),
            'phone' => trans('public.phone'),
            'email' => trans('public.email'),
            'dob' => trans('public.dob'),
            'password' => trans('public.password'),
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

        $id_no = 'MBR' . Str::padLeft($user->id, 5, "0");
        $user->id_number = $id_no;
        $user->save();

        return [
            'success' => true,
            'message' => [trans('public.user_created_successfully')],
            'user' => $user
        ];
    }
}
