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
use Virgil\PureKit\Pure\Exception\EmptyArgumentException;
use Virgil\PureKit\Pure\Exception\IllegalStateException;
use Virgil\PureKit\Pure\Exception\NullArgumentException;
use Virgil\PureKit\Pure\Exception\NullPointerException;
use Virgil\PureKit\Pure\Exception\PheClientException;
use Virgil\PureKit\Pure\Exception\PureCryptoException;
use Virgil\PureKit\Pure\Exception\PureLogicException;
use VirgilSecurityPure\Config\Config;
use VirgilSecurityPure\Config\Crypto;
use VirgilSecurityPure\Config\Log;
use VirgilSecurityPure\Config\Option;
use VirgilSecurityPure\Core\CoreProtocol;
use VirgilSecurityPure\Core\Logger;
use VirgilSecurityPure\Core\passw0rdHash;
use VirgilSecurityPure\Core\VirgilCryptoWrapper;
use VirgilSecurityPure\Exceptions\PluginPureException;
use VirgilSecurityPure\Helpers\DBQueryHelper;

/**
 * Class EncryptAndsMigrateBackgroundProcess
 * @package VirgilSecurityPure\Background
 */
class EncryptAndMigrateBackgroundProcess extends BaseBackgroundProcess
{
    /**
     * @var passw0rdHash|null
     */
    private ?passw0rdHash $passw0rdHash = null;

    /**
     * @var null|CoreProtocol
     */
    private ?CoreProtocol $protocol;

    /**
     * @var DBQueryHelper|null
     */
    private ?DBQueryHelper $dbqh;

    /**
     * @var VirgilCryptoWrapper|null
     */
    private ?VirgilCryptoWrapper $vcw;

    /**
     * @var string
     */
    protected string $action = Config::BACKGROUND_ACTION_MIGRATE;

    /**
     * @param CoreProtocol $protocol
     * @param DBQueryHelper $dbqh
     * @param VirgilCryptoWrapper $vcw
     * @return void
     */
    public function setDep(CoreProtocol $protocol, DBQueryHelper $dbqh, VirgilCryptoWrapper $vcw): void
    {
        $this->protocol = $protocol;
        $this->protocol->init();
        $this->dbqh = $dbqh;
        $this->vcw = $vcw;
    }

    /**
     * @param mixed $item
     * @return bool
     * @throws EmptyArgumentException
     * @throws IllegalStateException
     * @throws NullArgumentException
     * @throws PheClientException
     * @throws PureCryptoException
     * @throws VirgilCryptoException
     */
    protected function task(mixed $item): bool
    {
        if (is_null($this->passw0rdHash)) { // не нужен
            $this->passw0rdHash = new passw0rdHash();
        }

        //$pk = get_option(Option::RECOVERY_PUBLIC_KEY);
        Logger::log("[DELETE AFTER TEST] - task for {$item->ID} {$item->user_pass}");
        $this->protocol->getPure()->registerUser($item->ID, $item->user_pass);
        Logger::log("[DELETE AFTER TEST] - REGISTER FOR {$item->ID} {$item->user_pass} COMPLETE");
        update_user_meta($item->ID, Option::MIGRATE_START, true);

        return false;
    }

    /**
     * @return void
     * @throws PheClientException
     * @throws PureCryptoException
     * @throws VirgilCryptoException
     * @throws EmptyArgumentException
     * @throws IllegalStateException
     * @throws NullArgumentException
     * @throws NullPointerException
     * @throws PureLogicException
     */
    protected function complete(): void
    {
        // добавить бэк-процесс по всем юзерам, проверять через pure->authenticateUser() [PureTest - 336 по гранту и проверить сессию у гранта] и меняем мету
        //
        Logger::log("[DELETE AFTER TEST] - complete");
        if ($this->is_queue_empty()) {
            Logger::log("[DELETE AFTER TEST] - complete IS_QUEUE_EMPTY");

            update_option(Option::MIGRATE_FINISH, microtime(true));

            $duration = round(get_option(Option::MIGRATE_FINISH) - get_option(Option::MIGRATE_START), 2);
            Logger::log(Log::FINISH_MIGRATION . " (duration: $duration sec.)");

            $this->dbqh->clearAllUsersPass($this->protocol->getPure());

            delete_option(Option::MIGRATE_START);
            delete_option(Option::MIGRATE_FINISH);
            parent::complete();
        }
    }
}
