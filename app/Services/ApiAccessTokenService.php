<?php

namespace App\Services;

use App\Exceptions\AuthTokenIssueException;
use Illuminate\Support\Facades\Log;
use Throwable;

class ApiAccessTokenService
{
    public function issueForUser(object $user, string $tokenName = 'LaravelAuthApp'): string
    {
        try {
            return $user->createToken($tokenName)->accessToken;
        } catch (Throwable $exception) {
            Log::error('API access token issuance failed', [
                'user_type' => get_class($user),
                'user_id' => method_exists($user, 'getKey') ? $user->getKey() : null,
                'reason' => $exception->getMessage(),
            ]);

            throw new AuthTokenIssueException(
                message: translate('authentication_service_is_temporarily_unavailable') . ' ' . translate('contact_with_the_administrator'),
                reason: $exception->getMessage(),
            );
        }
    }
}
