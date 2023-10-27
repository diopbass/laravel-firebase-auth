<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use Kreait\Laravel\Firebase\Facades\Firebase;

final class Logout
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        $auth = Firebase::auth();
        $database = Firebase::database();
        $auth->revokeRefreshTokens($args['uid']);
        $signOutResult = $auth->getUser($args['uid']);
        $userId = $signOutResult?->uid;

        // Update the last session end time.

        $userSessions = $database->getReference('userSessions/' . $userId)->getSnapshot()->getValue();
        $userSessions = $userSessions ?? [];
        if (count($userSessions) === 0) {
            return [
                'status' => 'success',
                'message' => 'User logged out successfully.'
            ];
        }
        $userSessions['session' . count($userSessions)]['endTime'] = date('Y-m-d H:i:s');

        $database->getReference('userSessions/' . $userId . '/session' . (count($userSessions)))
            ->set([
                'startTime' => $userSessions['session' . count($userSessions)]['startTime'],
                'endTime' => date('Y-m-d H:i:s')
            ]);


        return [
            'status' => 'success',
            'message' => 'User logged out successfully.'
        ];
    }
}
