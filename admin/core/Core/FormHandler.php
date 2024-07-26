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
use GuzzleHttp\Exception\ClientException;
use Virgil\Crypto\Exceptions\VirgilCryptoException;
use Virgil\PureKit\Pure\Pure;
use VirgilSecurityPure\Background\EncryptAndMigrateBackgroundProcess;
use VirgilSecurityPure\Background\RecoveryBackgroundProcess;
use VirgilSecurityPure\Background\UpdateBackgroundProcess;
use VirgilSecurityPure\Config\Config;
use VirgilSecurityPure\Config\Option;
use VirgilSecurityPure\Config\Credential;
use VirgilSecurityPure\Config\Log;
use VirgilSecurityPure\Helpers\DBQueryHelper;
use VirgilSecurityPure\Helpers\Redirector;
use VirgilSecurityPure\Config\Crypto;
use wpdb;

/**
 * Class FormHandler
 * @package VirgilSecurityPure\Core
 */
class FormHandler implements Core
{
    /**
     * @var CredentialsManager
     */
    protected CredentialsManager $cm;

    /**
     * @var DBQueryHelper
     */
    protected DBQueryHelper $dbq;

    /**
     * @var wpdb
     */
    protected wpdb $wpdb;

    /**
     * @var CoreProtocol
     */
    private CoreProtocol $coreProtocol;

    /**
     * @var VirgilCryptoWrapper
     */
    private VirgilCryptoWrapper $virgilCryptoWrapper;

    /**
     * FormHandler constructor.
     */
    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    /**
     * @param CoreProtocol $coreProtocol
     * @param VirgilCryptoWrapper $virgilCryptoWrapper
     * @param CredentialsManager $credentialsManager
     * @param DBQueryHelper $DBQueryHelper
     */
    public function setDep(
        CoreProtocol $coreProtocol,
        VirgilCryptoWrapper $virgilCryptoWrapper,
        CredentialsManager $credentialsManager,
        DBQueryHelper $DBQueryHelper
    ): void {
        $this->coreProtocol = $coreProtocol;
        $this->virgilCryptoWrapper = $virgilCryptoWrapper;
        $this->cm = $credentialsManager;
        $this->dbq = $DBQueryHelper;
    }

    /**
     *
     */
    public function demo(): void
    {
        update_option(Option::RECOVERY_CHECKBOX_AGREE, true);
    }

    /**
     * @return void
     */
    public function credentials(): void
    {
        $this->cm->addInitialCredentials(
            $_POST[Credential::APP_TOKEN],
            $_POST[Credential::SERVICE_PUBLIC_KEY],
            $_POST[Credential::APP_SECRET_KEY],
            $_POST[Credential::NONROTATABLE_MASTER_SECRET],
            $_POST[Credential::BACKUP_PUBLIC_KEY],
        );

        try {
            // each time before use Pure we need init it - maybe in future add init function to __construct
            /** @var Pure $protocol */
            $this->coreProtocol->init();
            $this->coreProtocol->enrollAccount(Config::TEST_ENROLLMENT);
        } catch (ClientException $e) {
            $this->cm->addEmptyCredentials();
            if (401 == $e->getCode()) {
                Logger::log(Log::INVALID_APP_TOKEN, 0);
            } else {
                Logger::log($e->getMessage(), 0);
            }
            Redirector::toPageLog();
        } catch (Exception $e) {
            $this->cm->addEmptyCredentials();
            Logger::log("Invalid proof" == $e->getMessage() ? Log::INVALID_PROOF : $e->getMessage(), 0);

            Redirector::toPageLog();
        }

        Logger::log(Log::INIT_CREDENTIALS);
    }

    /**
     * @return void
     */
    public function migrate(): void
    {
        $users = get_users(['fields' => ['ID', 'user_pass', 'user_email']]);

        $migrateBackgroundProcess = new EncryptAndMigrateBackgroundProcess();
        $migrateBackgroundProcess->setDep($this->coreProtocol->init(), $this->dbq);

        update_option(Option::MIGRATE_START, microtime(true));

        Logger::log(Log::START_MIGRATION);

        try {
            foreach ($users as $user) {
                $metaRecord = get_user_meta($user->ID, Option::RECORD);
                $metaParams = get_user_meta($user->ID, Option::PARAMS);
                if (empty($metaRecord) && empty($metaParams)) {
                    $migrateBackgroundProcess->push_to_queue($user);
                }
            }
        } catch (Exception $e) {
            Logger::log('Push user to migration return error:  ' . $e->getMessage());
            wp_die($e->getMessage());
        }

        $migrateBackgroundProcess->save()->dispatch();
    }

    /**
     * @return void
     */
    public function update(): void
    {
        if (!empty($_POST[Credential::UPDATE_TOKEN])) {
            $users = get_users(['fields' => ['ID']]);

            if (!$this->cm->addUpdateTokenToOldCredentials($_POST[Credential::UPDATE_TOKEN])) {
                Logger::log("Add ." . Credential::UPDATE_TOKEN . " to old credentials", 0);
            }

            update_option(Option::UPDATE_START, microtime(true));
            Logger::log(Log::START_UPDATE);

            try {
                $updateBackgroundProcess = new UpdateBackgroundProcess();
                $updateBackgroundProcess->setDep($this->coreProtocol->init(), $this->cm);

                foreach ($users as $user) {
                    $updateBackgroundProcess->push_to_queue($user);
                }

                $updateBackgroundProcess->save()->dispatch();
            } catch (Exception $e) {
                wp_die($e->getMessage());
            }
        } else {
            wp_die("Empty " . Credential::UPDATE_TOKEN);
        }
    }

    /**
     * @return void
     */
    public function recovery(): void
    {
        if (!empty($file = $_FILES[Crypto::RECOVERY_PRIVATE_KEY])) {
            if (350 < $file['size']) {
                Logger::log(Log::RECOVERY_ERROR, 0);
                Redirector::toPageLog();
                exit();
            }

            $privateKeyIn = file_get_contents($file['tmp_name']);

            try {
                $this->virgilCryptoWrapper->importKey(Crypto::PRIVATE_KEY, $privateKeyIn);
            } catch (Exception $e) {
                if ($e instanceof VirgilCryptoException) {
                    Logger::log("Invalid " . Crypto::RECOVERY_PRIVATE_KEY . ': ' . $e->getMessage(), 0);
                } else {
                    Logger::log($e->getMessage(), 0);
                }

                Redirector::toPageLog();
                exit();
            }

            update_option(Option::RECOVERY_START, microtime(true));
            Logger::log(Log::START_RECOVERY);
            $users = get_users(['fields' => ['ID', 'user_email']]);

            try {
                $recoveryBackgroundProcess = new RecoveryBackgroundProcess();
                $recoveryBackgroundProcess->setDep($this->dbq, $this->virgilCryptoWrapper, $this->cm);

                $data['private_key_in'] = $privateKeyIn;

                foreach ($users as $user) {
                    $data['user'] = $user;

                    $recoveryBackgroundProcess->push_to_queue($data);
                }

                $recoveryBackgroundProcess->save()->dispatch();
            } catch (Exception $e) {
                if ($e instanceof VirgilCryptoException) {
                    Logger::log("Invalid Encrypted Data or Recovery Private Key", 0);
                } else {
                    Logger::log($e->getMessage(), 0);
                }
                Redirector::toPageLog();
                exit();
            }
        } else {
            wp_die("Empty " . Crypto::RECOVERY_PRIVATE_KEY);
        }
    }

    /**
     * @return void
     */
    public function addUsers(): void
    {
        for ($i = 0; $i < (int)$_POST['number_of_users']; $i++) {
            $user = 'user_' . rand(100, 999) . '_' . $i;
            $password = &$user;
            wp_create_user($user, $password, $user . '@mailinator.com');
        }

        $num = (int)$_POST['number_of_users'];
        Logger::log(Log::DEV_ADD_USERS . " (" . $num . ")");
    }

    /**
     * @return void
     */
    public function restoreDefaults(): void
    {
        $this->wpdb->query("DELETE FROM {$this->wpdb->users} WHERE id NOT IN (1)");
        $this->wpdb->query("DELETE FROM {$this->wpdb->usermeta} WHERE user_id NOT IN (1)");

        $users = get_users(['fields' => ['ID']]);

        foreach ($users as $user) {
            delete_user_meta($user->ID, Option::RECORD);
            delete_user_meta($user->ID, Option::PARAMS);
            delete_user_meta($user->ID, Option::ENCRYPTED);
        }

        delete_option(Option::MIGRATE_START);
        delete_option(Option::MIGRATE_FINISH);
        delete_option(Option::UPDATE_START);
        delete_option(Option::UPDATE_FINISH);
        delete_option('_transient_doing_cron');

        foreach (Config::ALL_BACKGROUND_PROCESSES as $bp) {
            $this->dbq->clearActionProcess($bp);
        }

        $this->cm->addEmptyCredentials();
        $this->dbq->clearTableLog();
        $pass = '$P$Be8bkgCZxUx096p9aAzZ3ydfE/qMyd0';
        $this->wpdb->query("UPDATE {$this->wpdb->users} SET user_pass='$pass' WHERE id=1");

        Logger::log(Log::DEV_RESTORE_DEFAULTS);
    }
}
