<?php
/**
 * Copyright (C) 2015-2024 Virgil Security Inc.
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

namespace VirgilSecurityPure\Helpers;

use VirgilSecurityPure\Config\Credential;
use VirgilSecurityPure\Exceptions\PluginPureException;

/**
 * Class CredentialsChecker
 * @package VirgilSecurityPure\Helpers
 */
class CredentialsChecker implements Helper
{
    /**
     * @var array|null
     */
    private ?array $credentials;
    /**
     * @var string|null
     */
    private ?string $appToken;
    /**
     * @var string|null
     */
    private ?string $servicePublicKey;
    /**
     * @var string|null
     */
    private ?string $appSecretKey;
    /**
     * @var string|null
     */
    private ?string $updateToken;
    private ?string $nonrotatableMasterSecret;
    private ?string $backupPublicKey;

    /**
     * @param array $credentials
     * @return bool
     * @throws PluginPureException
     */
    public function check(array $credentials): bool
    {
        $this->checkInputCredentials($credentials);
        $this->checkAppToken();
        $this->checkServicePublicKey();
        $this->checkAppSecretKey();
        $this->checkUpdateToken();
        $this->checkNonrotatableMasterSecret();
        $this->checkBackupPublicKey();
        $this->checkVersions();

        return true;
    }

    /**
     * @return void
     * @throws PluginPureException
     */
    private function checkNonrotatableMasterSecret(): void
    {
        if (!is_string($this->nonrotatableMasterSecret)) {
            throw new PluginPureException(Credential::NONROTATABLE_MASTER_SECRET." is not a string");
        }

        if (empty($this->nonrotatableMasterSecret)) {
            throw new PluginPureException("Empty ".Credential::NONROTATABLE_MASTER_SECRET);
        }
    }

    /**
     * @return void
     * @throws PluginPureException
     */
    private function checkBackupPublicKey(): void
    {
        if (!is_string($this->backupPublicKey)) {
            throw new PluginPureException(Credential::BACKUP_PUBLIC_KEY." is not a string");
        }

        if (empty($this->backupPublicKey)) {
            throw new PluginPureException("Empty ".Credential::BACKUP_PUBLIC_KEY);
        }
    }


    /**
     * @param array $credentials
     * @return void
     * @throws PluginPureException
     */
    private function checkInputCredentials(array $credentials): void
    {
        if (4!=count($credentials)) {
            throw new PluginPureException("Invalid count of credentials");
        }

        foreach (Credential::ALL as $credential) {
            if (!key_exists($credential, $credentials)) {
                throw new PluginPureException("Credential $credential does not exists");
            }
        }

        $this->credentials = $credentials;

        $this->appToken = $credentials[Credential::APP_TOKEN];
        $this->servicePublicKey = $credentials[Credential::SERVICE_PUBLIC_KEY];
        $this->appSecretKey = $credentials[Credential::APP_SECRET_KEY];
        $this->updateToken = $credentials[Credential::UPDATE_TOKEN];
        $this->nonrotatableMasterSecret = $credentials[Credential::NONROTATABLE_MASTER_SECRET];
        $this->backupPublicKey = $credentials[Credential::BACKUP_PUBLIC_KEY];
    }


    /**
     * @return void
     * @throws PluginPureException
     */
    private function checkAppToken(): void
    {
        if (!is_string($this->appToken)) {
            throw new PluginPureException(Credential::APP_TOKEN." is not a string");
        }

        if (empty($this->appToken)) {
            throw new PluginPureException("Empty ".Credential::APP_TOKEN);
        }

        if (32!=strlen($this->explode(Credential::APP_TOKEN, 1))) {
            throw new PluginPureException("Invalid key length of ".Credential::APP_TOKEN);
        }

        if (Credential::APP_TOKEN_PREFIX!=$this->explode(Credential::APP_TOKEN, 0)) {
            throw new PluginPureException("Invalid prefix for ".Credential::APP_TOKEN);
        }
    }

    /**
     * @return void
     * @throws PluginPureException
     */
    private function checkServicePublicKey(): void
    {
        if (!is_string($this->servicePublicKey)) {
            throw new PluginPureException(Credential::SERVICE_PUBLIC_KEY." is not a string");
        }

        if (empty($this->servicePublicKey)) {
            throw new PluginPureException("Empty ".Credential::SERVICE_PUBLIC_KEY);
        }

        if (65!= strlen((base64_decode($this->explode(Credential::SERVICE_PUBLIC_KEY, 2))))) {
            $message = "Invalid key length (base64 decoded) of " . Credential::SERVICE_PUBLIC_KEY;
            throw new PluginPureException($message);
        }

        if (Credential::SERVICE_PUBLIC_KEY_PREFIX!=$this->explode(Credential::SERVICE_PUBLIC_KEY, 0)) {
            throw new PluginPureException("Invalid prefix for ".Credential::SERVICE_PUBLIC_KEY);
        }

        if ((int)$this->explode(Credential::SERVICE_PUBLIC_KEY, 1) < 1) {
            throw new PluginPureException("Invalid version of ".Credential::SERVICE_PUBLIC_KEY);
        }
    }

    /**
     * @return void
     * @throws PluginPureException
     */
    private function checkAppSecretKey(): void
    {
        if (!is_string($this->appSecretKey)) {
            throw new PluginPureException(Credential::APP_SECRET_KEY." is not a string");
        }

        if (empty($this->appSecretKey)) {
            throw new PluginPureException("Empty ".Credential::APP_SECRET_KEY);
        }

        if (32!= strlen((base64_decode($this->explode(Credential::APP_SECRET_KEY, 2))))) {
            throw new PluginPureException("Invalid key length (base64 decoded) of ".Credential::APP_SECRET_KEY);
        }

        if (Credential::APP_SECRET_KEY_PREFIX!=$this->explode(Credential::APP_SECRET_KEY, 0)) {
            throw new PluginPureException("Invalid prefix for ".Credential::APP_SECRET_KEY);
        }

        if ((int)$this->explode(Credential::APP_SECRET_KEY, 1) < 1) {
            throw new PluginPureException("Invalid version of ".Credential::APP_SECRET_KEY);
        }
    }

    /**
     * @return void
     * @throws PluginPureException
     */
    private function checkUpdateToken(): void
    {
        if (!empty($this->updateToken)) {
            if (!is_string($this->updateToken)) {
                throw new PluginPureException(Credential::UPDATE_TOKEN." is not a string");
            }

            if (68!= strlen((base64_decode($this->explode(Credential::UPDATE_TOKEN, 2))))) {
                $message = "Invalid key length (base64 decoded) of " . Credential::UPDATE_TOKEN;
                throw new PluginPureException($message);
            }

            if (Credential::UPDATE_TOKEN_PREFIX!=$this->explode(Credential::UPDATE_TOKEN, 0)) {
                throw new PluginPureException("Invalid prefix for ".Credential::UPDATE_TOKEN);
            }

            if ((int)$this->explode(Credential::UPDATE_TOKEN, 1) < 1) {
                throw new PluginPureException("Invalid version of ".Credential::UPDATE_TOKEN);
            }
        }
    }

    /**
     * @return void
     * @throws PluginPureException
     */
    private function checkVersions(): void
    {
        $servicePublicKeyVersion = (int)$this->explode(Credential::SERVICE_PUBLIC_KEY, 1);
        $appSecretKeyVersion = (int)$this->explode(Credential::APP_SECRET_KEY, 1);

        if ($servicePublicKeyVersion != $appSecretKeyVersion) {
            throw new PluginPureException("Versions of ".Credential::SERVICE_PUBLIC_KEY. " and "
                .Credential::APP_SECRET_KEY. " are not equals");
        }

        if (!empty($this->updateToken)) {
            $updateTokenVersion = (int)$this->explode(Credential::UPDATE_TOKEN, 1);

            if ($updateTokenVersion-$servicePublicKeyVersion != 1) {
                throw new PluginPureException("Version of ".Credential::UPDATE_TOKEN
                    ." must be greater (+1) then versions of "
                    .Credential::SERVICE_PUBLIC_KEY." and ".Credential::APP_SECRET_KEY);
            }
        }
    }

    /**
     * @param string $key
     * @param int $element
     * @return string
     * @throws PluginPureException
     */
    private function explode(string $key, int $element): string
    {
        $credential = $this->credentials[$key];

        $res = explode(".", $credential);

        if (!isset($res[$element])) {
            throw new PluginPureException("Correct delimiter dot (.) does not exists at $key string");
        }

        return $res[$element];
    }
}
