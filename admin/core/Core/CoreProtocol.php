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

use Exception;
use PasswordHash;
use Virgil\Crypto\Exceptions\VirgilCryptoException;
use Virgil\CryptoWrapper\Phe\PheClient;
use Virgil\PureKit\Pure\AuthResult;
use Virgil\PureKit\Pure\Exception\EmptyArgumentException;
use Virgil\PureKit\Pure\Exception\IllegalStateException;
use Virgil\PureKit\Pure\Exception\NullArgumentException;
use Virgil\PureKit\Pure\Exception\NullPointerException;
use Virgil\PureKit\Pure\Exception\PheClientException;
use Virgil\PureKit\Pure\Exception\PureCryptoException;
use Virgil\PureKit\Pure\Exception\PureLogicException;
use Virgil\PureKit\Pure\PheManager;
use Virgil\PureKit\Pure\Pure;
use Virgil\PureKit\Pure\PureContext;
use Virgil\PureKit\Pure\PureCrypto;
use VirgilSecurityPure\Config\Config;
use VirgilSecurityPure\Config\Credential;
use VirgilSecurityPure\Config\Option;
use VirgilSecurityPure\Helpers\Redirector;

require_once ABSPATH . 'wp-includes/class-phpass.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * Class CoreProtocol
 * @package VirgilSecurityPure\Core
 */
class CoreProtocol implements Core
{
    private PureCrypto $pureCrypto;
    private PheManager $pheManager;
    private Pure $protocol;
    private PheClient $pheClient;
    private PasswordHash $hashPassword;

    /**
     * @return null|static
     */
    public function init(): ?static
    {
        try {
            if (Credential::isAllRequiredCredentialsSet()) {
                $this->protocol = $this->createProtocol();
            } else {
                $description = "Invalid credentials: Fill all Credentials";
                Logger::log($description, 0);
                add_option( 'virgil_error_redirect', $description);
                Redirector::toPageLog();
            }
        } catch (Exception $e) {
            add_option( 'virgil_error_redirect', $e->getMessage());
            if (0 == $e->getCode()) {
                Logger::log("Invalid credentials: " . $e->getMessage(), 0);
                $credentialsManager = new CredentialsManager();
                $credentialsManager->addUpdateTokenToOldCredentials("");
            } else {
                Logger::log($e->getMessage(), 0);
            }

            if (is_plugin_active(Config::PLUGIN_FULL_NAME)) {
                Redirector::toPageLog();
            }
        }

        return $this;
    }

    /**
     * @return Pure
     * @throws VirgilCryptoException
     * @throws EmptyArgumentException
     * @throws IllegalStateException
     * @throws NullArgumentException
     * @throws PureCryptoException
     * @throws PureLogicException
     */
    private function createProtocol(): Pure
    {
        $context = PureContext::createVirgilContext(
            $_ENV[Credential::APP_TOKEN],
            $_ENV[Credential::NONROTATABLE_MASTER_SECRET],
            $_ENV[Credential::BACKUP_PUBLIC_KEY],
            $_ENV[Credential::APP_SECRET_KEY],
            $_ENV[Credential::SERVICE_PUBLIC_KEY]
        );

        if (!empty($_ENV[Credential::UPDATE_TOKEN])) {
            $context->setUpdateToken($_ENV[Credential::UPDATE_TOKEN]);
        }

        $this->pureCrypto = new PureCrypto($context->getCrypto());
        $this->pheManager = new PheManager($context);
        $this->pheClient = new PheClient();
        $this->hashPassword = new PasswordHash(8, true);
        return new Pure($context);
    }

    /**
     * @param string $passwordHash
     * @return array
     * @throws PureCryptoException
     * @throws PheClientException
     */
    public function enrollAccount(string $passwordHash): array
    {
        $this->pureCrypto->computePasswordHash($passwordHash);
        return $this->pheManager->getEnrollment($passwordHash);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getNewRawKeys(): array // [new_client_private_key, new_server_public_key]
    {
        return $this->pheClient->rotateKeys($_ENV[Credential::UPDATE_TOKEN]);
    }

    /**
     * @param $value
     * @return string
     * @throws IllegalStateException
     * @throws NullArgumentException
     * @throws PureCryptoException
     */
    public function performRotation($value): string
    {
        return $this->pheManager->performRotation($value);
    }

    public function getPure(): Pure
    {
        return $this->protocol;
    }

    /**
     * @param string $email
     * @param string $password
     * @param string $hash
     * @return AuthResult
     * @throws EmptyArgumentException
     * @throws IllegalStateException
     * @throws NullArgumentException
     * @throws NullPointerException
     * @throws PheClientException
     * @throws PureCryptoException
     * @throws PureLogicException
     * @throws VirgilCryptoException
     */
    public function auth(string $email, string $password, string $hash): AuthResult
    {
        $preparedPassword = $this->hashPassword->crypt_private($password, $hash);
        return $this->protocol->authenticateUser($email, $preparedPassword);
    }

    /**
     * @param $userId
     * @param $userPass
     * @return void
     * @throws PureCryptoException
     */
    public function encryptAndSaveKeyForBackup($userId, $userPass): void
    {
        $buppk = $this->getPure()->getBuppk();
        $privateKey = $this->getPure()->getOskp()->getPrivateKey();
        $encryptedKey = $this->pureCrypto->encryptForBackup($userPass, $buppk, $privateKey);
        update_user_meta($userId, Option::ENCRYPT_BACKUP_KEY, base64_encode($encryptedKey));
    }
}
