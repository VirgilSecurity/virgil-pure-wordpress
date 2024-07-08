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

use Exception;
use Virgil\Crypto\Exceptions\VirgilCryptoException;
use VirgilSecurityPure\Config\Crypto;
use VirgilSecurityPure\Config\Log;
use VirgilSecurityPure\Config\Option;
use VirgilSecurityPure\Core\CredentialsManager;
use VirgilSecurityPure\Core\Logger;
use VirgilSecurityPure\Config\Config;
use VirgilSecurityPure\Core\VirgilCryptoWrapper;
use VirgilSecurityPure\Exceptions\PluginPureException;
use VirgilSecurityPure\Helpers\DBQueryHelper;

/**
 * Class RecoveryBackgroundProcess
 * @package VirgilSecurityPure\Background
 */
class RecoveryBackgroundProcess extends BaseBackgroundProcess
{
    /**
     * @var string
     */
    protected string $action = Config::BACKGROUND_ACTION_RECOVERY;

    /**
     * @var VirgilCryptoWrapper|null
     */
    private ?VirgilCryptoWrapper $vcw;
    /**
     * @var DBQueryHelper|null
     */
    private ?DBQueryHelper $dbqh;
    /**
     * @var CredentialsManager|null
     */
    private ?CredentialsManager $credentialsManager;

    /**
     * @param DBQueryHelper $dbqh
     * @param VirgilCryptoWrapper $vcw
     * @param CredentialsManager $credentialsManager
     */
    public function setDep(DBQueryHelper $dbqh, VirgilCryptoWrapper $vcw, CredentialsManager $credentialsManager): void
    {
        $this->vcw = $vcw;
        $this->dbqh = $dbqh;
        $this->credentialsManager = $credentialsManager;
    }

    /**
     * @param mixed $item
     * @return bool
     * @throws PluginPureException
     * @throws VirgilCryptoException
     */
    protected function task(mixed $item): bool
    {
        if ($item) {
            $user = $item['user'];
            $id = $user->ID;
            $privateKeyIn = $item['private_key_in'];
            $encryptedIn = get_user_meta($id, Option::ENCRYPTED)[0];
            $privateKey = $this->vcw->importKey(Crypto::PRIVATE_KEY, $privateKeyIn)->getPrivateKey();
            try {
                $decrypted = $this->vcw->decrypt(base64_decode($encryptedIn), $privateKey);
            } catch (Exception) {
                Logger::log("Invalid ".Crypto::RECOVERY_PRIVATE_KEY, 0);
                $this->cancel_process();
                $this->getFinalLog(0);
                $this->dbqh->clearActionProcess('recovery');
                exit;
            }

            $this->dbqh->passRecovery($id, $decrypted);
        }

        return false;
    }

    /**
     * @return void
     */
    protected function complete(): void
    {

        if ($this->is_queue_empty()) {
            $this->getFinalLog();
            $this->dbqh->clearPureParams();
            delete_option(Option::RECOVERY_PUBLIC_KEY);
            $this->credentialsManager->addEmptyCredentials();
        }

        parent::complete();
    }

    /**
     * @param int $status
     * @return void
     */
    private function getFinalLog(int $status = 1): void
    {
        update_option(Option::RECOVERY_FINISH, microtime(true));

        $duration = round(get_option(Option::RECOVERY_FINISH)-get_option(Option::RECOVERY_START), 2);
        Logger::log(Log::FINISH_RECOVERY." (duration: $duration sec.)", $status);

        delete_option(Option::RECOVERY_START);
        delete_option(Option::RECOVERY_FINISH);
    }
}
