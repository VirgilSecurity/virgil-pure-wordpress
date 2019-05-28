<?php
/**
 * Copyright (C) 2015-2019 Virgil Security Inc.
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are
 * met:
 *
 *     (1) Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *     (2) Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *     (3) Neither the name of the copyright holder nor the names of its
 *     contributors may be used to endorse or promote products derived from
 *     this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR ''AS IS'' AND ANY EXPRESS OR
 * IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
 * STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING
 * IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * Lead Maintainer: Virgil Security Inc. <support@virgilsecurity.com>
 */

namespace VirgilSecurityPure\Core;

use Dotenv\Dotenv;
use VirgilSecurityPure\Config\Credential;
use VirgilSecurityPure\Helpers\CredentialsChecker;
use VirgilSecurityPure\Helpers\ENVFormatter;
use VirgilSecurityPure\Helpers\Redirector;

/**
 * Class CredentialsManager
 * @package VirgilSecurityPure\Core
 */
class CredentialsManager implements Core
{
    /**
     * @var 
     */
    private $updateToken;

    /**
     * @return bool
     */
    public function addEmptyCredentials(): bool
    {
        $formatString = ENVFormatter::formatData("", "", "", "");
        file_put_contents(VIRGIL_PURE_CORE_ENV_FILE, $formatString);
        $this->updateENV();
        return true;
    }

    /**
     * @param string $appToken
     * @param string $servicePK
     * @param string $appSK
     * @return bool
     */
    public function addInitialCredentials(string $appToken, string $servicePK, string $appSK): bool
    {
        return $this->addCredentials($appToken, $servicePK, $appSK);
    }

    /**
     * @param string $updateToken
     * @return bool
     */
    public function addUpdateTokenToOldCredentials(string $updateToken): bool
    {
        $this->updateToken = $updateToken;

        return $this->addCredentials($_ENV[Credential::APP_TOKEN], $_ENV[Credential::SERVICE_PUBLIC_KEY],
        $_ENV[Credential::APP_SECRET_KEY], $updateToken);
    }

    public function addRotatedCredentials(string $servicePK, string $appSK): bool
    {
        return $this->addCredentials($_ENV[Credential::APP_TOKEN], $servicePK, $appSK);
    }

    /**
     * @param string $appToken
     * @param string $servicePK
     * @param string $appSK
     * @param string|null $ut
     * @return bool
     */
    private function addCredentials(string $appToken, string $servicePK, string $appSK, string $ut=null): bool
    {
        $credentials = [
            Credential::APP_TOKEN => $appToken,
            Credential::SERVICE_PUBLIC_KEY => $servicePK,
            Credential::APP_SECRET_KEY => $appSK,
            Credential::UPDATE_TOKEN => $ut,
        ];

        $credentialsChecker = new CredentialsChecker();
        try {
            $credentialsChecker->check($credentials);
        }
        catch (\Exception $e) {
            Logger::log($e->getMessage(), 0);
            Redirector::toPageLog();
        }

        $formatString = ENVFormatter::formatData($appToken, $servicePK, $appSK, $ut);
        file_put_contents(VIRGIL_PURE_CORE_ENV_FILE, $formatString);

        $this->updateENV();

        return true;
    }

    /**
     * @param string $updateToken
     * @return int
     */
    public function getVersion(string $updateToken): int
    {
        $version = CredentialExploder::explode($updateToken, 1);
        return $version;
    }

    /**
     * @return array
     */
    private function updateENV()
    {
        return (new Dotenv(VIRGIL_PURE_CORE))->overload();
    }
}