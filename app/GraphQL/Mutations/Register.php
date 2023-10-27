<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\User;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Carbon\Carbon;

final class Register
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        $name = $args['name'];
        $email = $args['email'];
        $password = $args['password'];
        $newUser = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $userProperties = [
            'uid' => $newUser->id,
            'email' => $email,
            'emailVerified' => false,
            'password' => $password,
            'displayName' => $name,
            'disabled' => false,
        ];
        $auth = Firebase::auth();
        $createdUser = $auth->createUser($userProperties);
        
        // convert 2023-10-18 18:22:04.548 UTC (+00:00) to 2023-10-18T18:22:04.548Z
        $createdAt = Carbon::parse($createdUser->metadata->createdAt)->toDateTimeString();
        $updatedAt = Carbon::parse($createdUser->metadata->lastLoginAt)->toDateTimeString();
        return [
            'token' => $createdUser->uid,
            'user' => [
                'id' => $createdUser->uid,
                'name' => $createdUser->displayName,
                'email' => $createdUser->email,
                'email_verified_at' => null,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]
        ];
    }
}
