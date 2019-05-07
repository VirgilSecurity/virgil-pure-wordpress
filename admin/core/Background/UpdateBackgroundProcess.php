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

use Virgil\PureKit\Protocol\Protocol;
use VirgilSecurityPure\Config\Credential;
use VirgilSecurityPure\Config\Log;
use VirgilSecurityPure\Config\Option;
use VirgilSecurityPure\Core\CredentialsManager;
use VirgilSecurityPure\Core\Logger;
use VirgilSecurityPure\Config\Config;
use Virgil\PureKit\Protocol\RecordUpdater;

/**
 * Class UpdateBackgroundProcess
 * @package VirgilSecurityPure\Background
 */
class UpdateBackgroundProcess extends BaseBackgroundProcess
{
    /**
     * @var
     */
    private $recordUpdater;

    /**
     * @var CredentialsManager
     */
    private $credentialsManager;

    /**
     * @var Protocol
     */
    private $protocol;

    /**
     * @var string
     */
    protected $action = Config::BACKGROUND_ACTION_UPDATE;

    public function setDep(Protocol $protocol, CredentialsManager $credentialsManager)
    {
        $this->protocol = $protocol;
        $this->credentialsManager = $credentialsManager;
    }

    /**
     * @param mixed $user
     * @return bool|mixed
     */
    protected function task($user)
    {
        $record = get_user_meta($user->ID, Option::RECORD);

        try {
            if (is_null($this->recordUpdater))
                $this->recordUpdater = new RecordUpdater($_ENV[Credential::UPDATE_TOKEN]);
                $newRecordRaw = $this->recordUpdater->update(base64_decode($record[0]));
        } catch (\Exception $e) {
            if("PHE Client error"==$e->getMessage())
            {
                $msg = "Invalid ".Credential::UPDATE_TOKEN;
            }
            else {
                $msg = $e->getMessage();
            }

            $this->cancel_process();
            Logger::log($msg, 0);
        }


        if (!is_null($newRecordRaw)) {
            $newRecord = base64_encode($newRecordRaw);
            update_user_meta($user->ID, Option::RECORD, $newRecord);
        }

        return false;
    }

    /**
     *
     */
    protected function complete()
    {

        if ($this->is_queue_empty()) {
            update_option(Option::UPDATE_FINISH, microtime(true));

            $v = $this->credentialsManager->getVersion($_ENV[Credential::UPDATE_TOKEN]);

            $duration = round(get_option(Option::UPDATE_FINISH) - get_option
                (Option::UPDATE_START), 2);
            Logger::log(Log::FINISH_UPDATE . " (records ver.: $v, duration: $duration sec.)");


            $nk = $this->protocol->getNewRawKeys();

            $newAppSecretKey = Credential::APP_SECRET_KEY_PREFIX."." . $v . "." . base64_encode($nk[0]);
            $newServicePublicKey = Credential::SERVICE_PUBLIC_KEY_PREFIX."." . $v . "." . base64_encode($nk[1]);

            $this->credentialsManager->addRotatedCredentials($newServicePublicKey, $newAppSecretKey);

            unset($this->protocol);

            delete_option(Option::UPDATE_START);
            delete_option(Option::UPDATE_FINISH);
        }

        parent::complete();
    }
}