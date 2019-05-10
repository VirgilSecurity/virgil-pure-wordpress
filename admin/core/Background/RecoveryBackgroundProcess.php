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

namespace VirgilSecurityPure\Background;

use Virgil\CryptoImpl\VirgilCryptoException;
use VirgilSecurityPure\Config\Crypto;
use VirgilSecurityPure\Config\Log;
use VirgilSecurityPure\Config\Option;
use VirgilSecurityPure\Core\CredentialsManager;
use VirgilSecurityPure\Core\Logger;
use VirgilSecurityPure\Config\Config;
use VirgilSecurityPure\Core\VirgilCryptoWrapper;
use VirgilSecurityPure\Helpers\DBQueryHelper;

class RecoveryBackgroundProcess extends BaseBackgroundProcess
{
    protected $action = Config::BACKGROUND_ACTION_RECOVERY;

    private $vcw;
    private $dbqh;
    private $credentialsManager;
    
    public function setDep(DBQueryHelper $dbqh, VirgilCryptoWrapper $vcw, CredentialsManager $credentialsManager) {
        $this->vcw = $vcw;
        $this->dbqh = $dbqh;
        $this->credentialsManager = $credentialsManager;
    }

    protected function task($data) {

        if($data) {
            $user = $data['user'];
            $id = $user->ID;
            $privateKeyIn = $data['private_key_in'];
            $encryptedIn = get_user_meta($id, Option::ENCRYPTED)[0];
            $privateKey = $this->vcw->importKey(Crypto::PRIVATE_KEY, $privateKeyIn);
            try {
                $decrypted = $this->vcw->decrypt(base64_decode($encryptedIn), $privateKey);
            }
            catch (VirgilCryptoException $exception) {
                $this->complete();
                Logger::log("Invalid ".Crypto::RECOVERY_PRIVATE_KEY, 0);
                return false;
            }

            $this->dbqh->passRecovery($id, $decrypted);
            return false;
        }
    }

    protected function complete() {

        if($this->is_queue_empty())
        {
            update_option(Option::RECOVERY_FINISH, microtime(true));

            $duration = round(get_option(Option::RECOVERY_FINISH)-get_option
                (Option::RECOVERY_START), 2);
            Logger::log( Log::FINISH_RECOVERY." (duration: $duration sec.)");

            delete_option(Option::RECOVERY_START);
            delete_option(Option::RECOVERY_FINISH);
            $this->dbqh->clearPureParams();
            delete_option(Option::RECOVERY_PUBLIC_KEY);
            update_option(Option::DEMO_MODE, 1);
            $this->credentialsManager->addEmptyCredentials();
        }

        parent::complete();
    }
}