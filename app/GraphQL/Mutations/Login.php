<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use Kreait\Laravel\Firebase\Facades\Firebase;



final class Login
{


    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        $email = $args['email'];
        $password = $args['password'];
        $auth = Firebase::auth();
        $database = Firebase::database();

        $signInResult = $auth->signInWithEmailAndPassword($email, $password);

        $user = $signInResult->data();
        $this->startSession($user['localId']);

        // $users = $auth->listUsers($defaultMaxResults = 1000, $defaultBatchSize = 1000);
        // $newUsers = [];
        // array_map(function (\Kreait\Firebase\Auth\UserRecord $newUser) use (&$newUsers) {
        //     $newUsers[] = $newUser;
        // }, iterator_to_array($users));

        // Auth users per day.
        // $date = date('Y-m-d');

        // $newUsers = array_filter($newUsers, function ($user) use ($date) {
        //     return date('Y-m-d', $user->metadata->lastLoginAt->getTimestamp()) === $date;
        // });
        // dd($newUsers);

        return [
            'token' => $user['idToken'],
            'expires_in' => $user['expiresIn'],
            'refresh_token' => $user['refreshToken'],
            'user' => [
                'id' => $user['localId'],
                'name' => $user['displayName'],
                'email' => $user['email'],
                'email_verified_at' => null,
                'created_at' => null,
                'updated_at' => null,
            ]
        ];
    }


    public function startSession($userId)
    {
        $database = Firebase::database();
       

        $userSessions = $database->getReference('userSessions/' . $userId)->getSnapshot()->getValue();
        $userSessions = $userSessions ?? [];
        $userSessions[] = [
            'startTime' => date('Y-m-d H:i:s'),
            'endTime' => 0
        ];
        // Update the last session end time.
        if (count($userSessions) >= 2) {
            $database->getReference('userSessions/' . $userId . '/session' . (count($userSessions) - 1))
                ->set([
                    'startTime' => $userSessions['session' . (count($userSessions) - 1)]['startTime'],
                    'endTime' => date('Y-m-d H:i:s')
                ]);
        }

        $database->getReference('userSessions/' . $userId . '/session' . (count($userSessions) == 0 ? 1 : count($userSessions)))
            ->set([
                'startTime' => date('Y-m-d H:i:s'),
                'endTime' => 0
            ]);

        return $userSessions;
    }


}
