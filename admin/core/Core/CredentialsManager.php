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

namespace VirgilSecurityPure\Core;

use Dotenv\Dotenv;
use Exception;
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
     * @return bool
     */
    public function addEmptyCredentials(): bool
    {
        $formatString = ENVFormatter::formatData();
        file_put_contents(VIRGIL_PURE_CORE_ENV_FILE, $formatString);
        $this->updateENV();
        return true;
    }

    /**
     * @param string $aT
     * @param string $sPK
     * @param string $aSK
     * @param string $uNMS
     * @param string $bPKey
     * @return bool
     */
    public function addInitialCredentials(string $aT, string $sPK, string $aSK, string $uNMS, string $bPKey): bool
    {
        return $this->add($aT, $sPK, $aSK, $uNMS, $bPKey);
    }

    /**
     * @param string $updateToken
     * @return bool
     */
    public function addUpdateTokenToOldCredentials(string $updateToken): bool
    {
        return $this->add(
            $_ENV[Credential::APP_TOKEN],
            $_ENV[Credential::SERVICE_PUBLIC_KEY],
            $_ENV[Credential::APP_SECRET_KEY],
            $_ENV[Credential::NONROTATABLE_MASTER_SECRET],
            $_ENV[Credential::BACKUP_PUBLIC_KEY],
            $updateToken
        );
    }

    /**
     * @param string $servicePK
     * @param string $appSK
     * @return bool
     */
    public function addRotatedCredentials(string $servicePK, string $appSK): bool
    {
        $appToken = $_ENV[Credential::APP_TOKEN];
        $nonrotableMasterSecret = $_ENV[Credential::NONROTATABLE_MASTER_SECRET];
        $backupPublicKey = $_ENV[Credential::BACKUP_PUBLIC_KEY];
        return $this->add($appToken, $servicePK, $appSK, $nonrotableMasterSecret, $backupPublicKey);
    }

    /**
     * @param string $aT
     * @param string $sPK
     * @param string $aSK
     * @param string $nMS
     * @param string $bPK
     * @param string|null $ut
     * @return bool
     */
    private function add(string $aT, string $sPK, string $aSK, string $nMS, string $bPK, string $ut = null): bool
    {
        $credentials = [
            Credential::APP_TOKEN => $aT,
            Credential::SERVICE_PUBLIC_KEY => $sPK,
            Credential::APP_SECRET_KEY => $aSK,
            Credential::UPDATE_TOKEN => $ut,
            Credential::NONROTATABLE_MASTER_SECRET => $nMS,
            Credential::BACKUP_PUBLIC_KEY => $bPK
        ];

        $credentialsChecker = new CredentialsChecker();
        try {
            $credentialsChecker->check($credentials);
        } catch (Exception $e) {
            Logger::log($e->getMessage(), 0);
            Redirector::toPageLog();
        }

        $formatString = ENVFormatter::formatData($aT, $sPK, $aSK, $nMS, $bPK, $ut);

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
        return CredentialExploder::explode($updateToken, 1);
    }

    /**
     * @return void
     */
    private function updateENV(): void
    {
        $dotenv = Dotenv::createImmutable(dirname(VIRGIL_PURE_CORE), basename(VIRGIL_PURE_CORE));
        // we need make sure we've entered all the data
        $dotenv->load();
    }
}
