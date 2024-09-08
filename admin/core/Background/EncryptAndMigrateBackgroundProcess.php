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

namespace VirgilSecurityPure\Background;

use Virgil\Crypto\Exceptions\VirgilCryptoException;
use Virgil\Crypto\Exceptions\VirgilException;
use Virgil\PureKit\Pure\Exception\ClientException;
use Virgil\PureKit\Pure\Exception\EmptyArgumentException;
use Virgil\PureKit\Pure\Exception\IllegalStateException;
use Virgil\PureKit\Pure\Exception\NullArgumentException;
use Virgil\PureKit\Pure\Exception\NullPointerException;
use Virgil\PureKit\Pure\Exception\PheClientException;
use Virgil\PureKit\Pure\Exception\ProtocolException;
use Virgil\PureKit\Pure\Exception\PureCryptoException;
use Virgil\PureKit\Pure\Exception\PureException;
use Virgil\PureKit\Pure\Exception\PureStorageUserNotFoundException;
use Virgil\PureKit\Pure\Exception\ValidateException;
use Virgil\PureKit\Pure\Exception\VirgilCloudStorageException;
use VirgilSecurityPure\Config\Config;
use VirgilSecurityPure\Config\Log;
use VirgilSecurityPure\Config\Option;
use VirgilSecurityPure\Core\CoreProtocol;
use VirgilSecurityPure\Core\Logger;
use VirgilSecurityPure\Helpers\DBQueryHelper;
use WP_Background_Process;

/**
 * Class EncryptAndsMigrateBackgroundProcess
 * @package VirgilSecurityPure\Background
 */
class EncryptAndMigrateBackgroundProcess extends WP_Background_Process
{
    /**
     * @var null|CoreProtocol
     */
    private ?CoreProtocol $protocol;

    /**
     * @var DBQueryHelper|null
     */
    private ?DBQueryHelper $dbqh;

    /**
     * @var string
     */
    protected $action = Config::BACKGROUND_ACTION_MIGRATE;

    /**
     * @param CoreProtocol $protocol
     * @param DBQueryHelper $dbqh
     * @return void
     */
    public function setDep(CoreProtocol $protocol, DBQueryHelper $dbqh): void
    {
        $this->protocol = $protocol;
        $this->dbqh = $dbqh;
    }

    /**
     * @param mixed $item
     * @return bool
     * @throws EmptyArgumentException
     * @throws IllegalStateException
     * @throws NullArgumentException
     * @throws PheClientException
     * @throws VirgilCryptoException
     */
    protected function task(mixed $item): bool
    {
        try {
            $this->protocol->getPure()->authenticateUser($item->user_email, $item->user_pass);
            $this->markUserAsMigrated($item->ID);
            return false; // return false to remove the item from the queue
        } catch (PureStorageUserNotFoundException | VirgilCloudStorageException $e) {
            try {
                $this->protocol->encryptAndSaveKeyForBackup($item->ID, $item->user_pass);
                $this->protocol->getPure()->registerUser($item->user_email, $item->user_pass);
                $this->markUserAsMigrated($item->ID);
                return false; // return false to remove the item from the queue
            } catch (ProtocolException | PureCryptoException $e) {
                Logger::log('When migrate email = ' . $item->user_email . ' : ' . $e->getMessage());
            }
        } catch (VirgilException | PureException | ClientException | ValidateException $e) {
            Logger::log('Error when auth User email = ' . $item->user_email . ' ' . $e->getMessage() . ' the to reset password');
            try {
                // This is mostly demo situation, when local password is not equal to Pure password
                $this->protocol->encryptAndSaveKeyForBackup($item->ID, $item->user_pass);
                $this->protocol->getPure()->resetUserPassword($item->user_email, $item->user_pass, true);
                $this->markUserAsMigrated($item->ID);
                Logger::log('Password where reset for ' . $item->user_email);
            } catch (PureCryptoException | PureException | ClientException | ValidateException $e) {
                Logger::log('Error when reset password for User email = ' . $item->user_email . ' ' . $e->getMessage());
            }
            return false; // return false to remove the item from the queue
        }

        return $item; // return item to queue to try again
    }

    private function markUserAsMigrated(int $userId): void
    {
        // TODO: Optimize this code, it can be done in one query
        $this->dbqh->removePasswordHashForUser($userId);
    }
}
