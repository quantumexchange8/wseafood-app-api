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
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->errors()->all(),
                'user' => null,
            ];
        }

        $user = User::create([
            'first_name' => $args['input']['first_name'],
            'last_name' => $args['input']['last_name'],
            'email' => $args['input']['email'],
            'password' => Hash::make($args['input']['password']),
        ]);

        return [
            'success' => true,
            'message' => ['User created successfully'],
            'user' => $user
        ];
    }
}
