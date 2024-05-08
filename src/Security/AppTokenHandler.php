<?php

// src/Security/AccessTokenHandler.php
namespace App\Security;

use App\Repository\AccessTokenRepository;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private AccessTokenRepository $repository
    ) {
    }

    public function getUserBadgeFrom(#[\SensitiveParameter] string $accessToken): UserBadge
    {
        // e.g. query the "access token" database to search for this token
        $accessToken = $this->repository->findOneByValue($accessToken);

        if (null === $accessToken ) {
            throw new BadCredentialsException("Invalid access token");
        } else if ($accessToken->isExpired()) {
            throw new BadCredentialsException("Expired access token");
        } else if ($accessToken->isRevoked()) {
            throw new BadCredentialsException("Revoked access token");
        } else if ($accessToken->isConsumed()) {
            throw new BadCredentialsException("Consumed access token");
        } 

        // and return a UserBadge object containing the user identifier from the found token
        // (this is the same identifier used in Security configuration; it can be an email,
        // a UUUID, a username, a database ID, etc.)
        return new UserBadge($accessToken->getUserId());
    }
}