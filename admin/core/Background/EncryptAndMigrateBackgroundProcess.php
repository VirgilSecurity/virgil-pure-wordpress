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
use Virgil\CryptoWrapper\Phe\PheClient;
use Virgil\PureKit\Pure\Exception\EmptyArgumentException;
use Virgil\PureKit\Pure\Exception\IllegalStateException;
use Virgil\PureKit\Pure\Exception\NullArgumentException;
use Virgil\PureKit\Pure\Exception\NullPointerException;
use Virgil\PureKit\Pure\Exception\PheClientException;
use Virgil\PureKit\Pure\Exception\PureCryptoException;
use Virgil\PureKit\Pure\Exception\PureLogicException;
use Virgil\PureKit\Pure\Exception\PureStorageUserNotFoundException;
use VirgilSecurityPure\Config\Config;
use VirgilSecurityPure\Config\Crypto;
use VirgilSecurityPure\Config\Log;
use VirgilSecurityPure\Config\Option;
use VirgilSecurityPure\Core\CoreProtocol;
use VirgilSecurityPure\Core\Logger;
use VirgilSecurityPure\Core\VirgilCryptoWrapper;
use VirgilSecurityPure\Exceptions\PluginPureException;
use VirgilSecurityPure\Helpers\DBQueryHelper;
use WpOrg\Requests\Exception;

/**
 * Class EncryptAndsMigrateBackgroundProcess
 * @package VirgilSecurityPure\Background
 */
class EncryptAndMigrateBackgroundProcess extends BaseBackgroundProcess
{
    /**
     * @var string|null
     */
    private ?string $passwordHash = null;

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
        Logger::log('TEST CODE START');
        $pheClient = new PheClient();
        $pheClient->setupDefaults();
        $clientPrivateKey = base64_decode("i08VHvNcRuBt+sny4GnRv+Ajf5lEpFIdvgykH1/SBYo=");
        $serverPublicKey = base64_decode("BAR+T8LkfugpOb2K18TwRWXc9SkZJdqa+4Gd0PuwZwfWtNF0+Iy4vRDohrwbUxoN9C91D2sm4vfWsy1ShMpa1+k=");
        $pheClient->setKeys($clientPrivateKey, $serverPublicKey);

        $pwd = hex2bin("cb1e2b5fa8c8d9d8e45a43ead8fe081814131c0a0b9d2b52ca8716875581512af646305f5c6d95c68fd597ded8337a4f40beca747b7ab0e3725e4c35d3e1cc5e");
        $enrollRecord = hex2bin("0a20f4056fbaf0490875ec841cfb6f9ad45b73b923fc1d6e55dfad20cf0c9ab2fb061220cde82a74f75d6b373ae120671bb7e7d6a36275eb97f8837e158108f63a746db01a41049ef6def3ed6c50959ae539fd4a95beaaf34941be6eb377b110d4b45676eb26b77c9769c09a2e622a479f7f3002b266b2c72e56bc30a76766b824373a2a6b0c30224104bf442de47b67a665b229d1425fc46f31d4ca32c5ff86099ab4a452bffd1fb3c9dc76cdf778e285fcc27d169f3629fb0eda73ffe949df8582446d23a9d9e3294c");

        $request = $pheClient->createVerifyPasswordRequest($pwd, $enrollRecord);
        if ($request === null) {
            Logger::log('Error when createVerifyPasswordRequest');
            return false;
        }

        Logger::log("SUCCESS: size of request:" . strlen($request));
        Logger::log('TEST CODE END');
        return false;

        try {
            $this->protocol->getPure()->authenticateUser($item->ID, $item->user_pass);
            Logger::log('Is already registered User ID = ' . $item->ID);
        } catch (VirgilCryptoException | PureLogicException | PureCryptoException | PheClientException | NullPointerException | NullArgumentException | IllegalStateException | EmptyArgumentException $e) {
            Logger::log('Error when auth User ID = ' . $item->ID . ' ' . $e->getMessage());
        } catch (PureStorageUserNotFoundException $e) {
            Logger::log('Going to register = User ID ' . $item->ID);
            $this->protocol->getPure()->registerUser($item->ID, $item->user_pass);
            update_user_meta($item->ID, Option::MIGRATE_START, true);
            return false;
        }
        Logger::log('MIGRATE FINISH FOR User ID = ' . $item->ID);
        update_user_meta($item->ID, Option::MIGRATE_FINISH, true);

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
        Logger::log("WE IN COMPLETE");
        if ($this->is_queue_empty()) {

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
