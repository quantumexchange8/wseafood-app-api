<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

final readonly class UserLogin
{
    /** @param  array{}  $args */
    public function __invoke(null $_, array $args)
    {
        $validator = Validator::make($args['input'], [
            'email' => ['required', 'email', 'exists:users,email'],
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

        $user = User::whereEmail($args['input']['email'])->first();

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
