<?php

namespace Napp\Api\Auth;

use Illuminate\Auth\TokenGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

class ApiGuard extends TokenGuard
{
    /**
     * @param UserProvider $provider
     * @param Request $request
     */
    public function __construct(UserProvider $provider, Request $request)
    {
        parent::__construct($provider, $request);

        $this->storageKey = 'api_key';
    }

    /**
     * We need to break the interface here.
     * Front API login action uses email/password for user authentication and not standard api key.
     *
     * @param array $credentials
     * @return bool
     */
    public function attempt(array $credentials = [])
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        if (null === $user) {
            return false;
        }

        if (true === $this->provider->validateCredentials($user, $credentials)) {
            $this->setUser($user);

            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getTokenForRequest()
    {
        return $this->request->header(NappHttpHeaders::NAPP_API_KEY);
    }
}