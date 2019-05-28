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

namespace VirgilSecurityPure\Core;

use GuzzleHttp\Exception\ClientException;
use Virgil\CryptoImpl\VirgilCryptoException;
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

/**
 * Class FormHandler
 * @package VirgilSecurityPure\Core
 */
class FormHandler implements Core
{
    /**
     * @var CredentialsManager
     */
    protected $cm;

    /**
     * @var DBQueryHelper
     */
    protected $dbq;

    /**
     * @var \wpdb
     */
    protected $wpdb;

    /**
     * @var CoreProtocol
     */
    private $coreProtocol;

    /**
     * @var VirgilCryptoWrapper
     */
    private $virgilCryptoWrapper;

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
    public function setDep(CoreProtocol $coreProtocol, VirgilCryptoWrapper $virgilCryptoWrapper, CredentialsManager
    $credentialsManager, DBQueryHelper $DBQueryHelper) {
        $this->coreProtocol = $coreProtocol;
        $this->virgilCryptoWrapper = $virgilCryptoWrapper;
        $this->cm = $credentialsManager;
        $this->dbq = $DBQueryHelper;
    }

    /**
     *
     */
    public function demo()
    {
        if(!get_option(Option::RECOVERY_PUBLIC_KEY))
            Logger::log(Log::GENERATE_RECOVERY_KEYS, 0);
    }

    public function downloadRecoveryPrivateKey()
    {
        $this->virgilCryptoWrapper->generateKeys();
        $this->virgilCryptoWrapper->downloadPrivateKey();
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function credentials()
    {
        $this->cm->addInitialCredentials($_POST[Credential::APP_TOKEN], $_POST[Credential::SERVICE_PUBLIC_KEY],
        $_POST[Credential::APP_SECRET_KEY]);

        try {
            $protocol = $this->coreProtocol->init();
            $protocol->enrollAccount(Config::TEST_ENROLLMENT);
        }
        catch(ClientException $e) {
            if(401==$e->getCode()) {
                $this->cm->addEmptyCredentials();
                Logger::log(Log::INVALID_APP_TOKEN, 0);
            } else {
                $this->cm->addEmptyCredentials();
                Logger::log($e->getMessage(), 0);
            }
            Redirector::toPageLog();

        }
        catch (\Exception $e) {
            $this->cm->addEmptyCredentials();
            Logger::log("Invalid proof"==$e->getMessage() ? Log::INVALID_PROOF : $e->getMessage(), 0);

            Redirector::toPageLog();
        }

        Logger::log(Log::INIT_CREDENTIALS);
    }

    /**
     *
     */
    public function migrate()
    {
        $users = get_users(array('fields' => array('ID', 'user_pass')));

        $migrateBackgroundProcess = new EncryptAndMigrateBackgroundProcess();
        $migrateBackgroundProcess->setDep($this->coreProtocol->init(), $this->dbq, $this->virgilCryptoWrapper);

        update_option(Option::MIGRATE_START, microtime(true));

        Logger::log(Log::START_MIGRATION);

        try {
            foreach ($users as $user) {
                if(empty(get_user_meta($user->ID, Option::RECORD)) && empty(get_user_meta($user->ID, Option::PARAMS)))
                    $migrateBackgroundProcess->push_to_queue( $user );
            }
        } catch (\Exception $e) {
            wp_die($e->getMessage());
        }

        $migrateBackgroundProcess->save()->dispatch();
    }

    /**
     *
     */
    public function update()
    {
        if(!empty($_POST[Credential::UPDATE_TOKEN])) {

            $users = get_users(array('fields' => array('ID')));

            if(!$this->cm->addUpdateTokenToOldCredentials($_POST[Credential::UPDATE_TOKEN]))
                Logger::log("Add .".Credential::UPDATE_TOKEN." to old credentials", 0);

            update_option(Option::UPDATE_START, microtime(true));
            Logger::log(Log::START_UPDATE);

            try {
                $updateBackgroundProcess = new UpdateBackgroundProcess();
                $updateBackgroundProcess->setDep($this->coreProtocol->init(), $this->cm);

                foreach ($users as $user) {
                    $updateBackgroundProcess->push_to_queue( $user );
                }

                $updateBackgroundProcess->save()->dispatch();

            } catch (\Exception $e) {
                wp_die($e->getMessage());
            }
        }
        else {
            wp_die("Empty ".Credential::UPDATE_TOKEN);
        }
    }

    /**
     * 
     */
    public function recovery()
    {
        if(!empty($file = $_FILES[Crypto::RECOVERY_PRIVATE_KEY])) {

            if(350<$file['size']) {
                Logger::log(Log::RECOVERY_ERROR, 0);
                Redirector::toPageLog();
                exit();
            }

            $privateKeyIn = file_get_contents($file['tmp_name']);

            try{
                $this->virgilCryptoWrapper->importKey(Crypto::PRIVATE_KEY, $privateKeyIn);
            }
            catch (\Exception $e) {
                if($e instanceof VirgilCryptoException) {
                    Logger::log("Invalid ".Crypto::RECOVERY_PRIVATE_KEY, 0);
                } else {
                    Logger::log($e->getMessage(), 0);
                }

                Redirector::toPageLog();
                exit();
            }

            update_option(Option::RECOVERY_START, microtime(true));
            Logger::log(Log::START_RECOVERY);
            $users = get_users(array('fields' => array('ID')));

            try {
                $recoveryBackgroundProcess = new RecoveryBackgroundProcess();
                $recoveryBackgroundProcess->setDep($this->dbq, $this->virgilCryptoWrapper, $this->cm);

                $data['private_key_in'] = $privateKeyIn;

                foreach ($users as $user) {
                    $data['user'] = $user;

                    $recoveryBackgroundProcess->push_to_queue($data);
                }

                $recoveryBackgroundProcess->save()->dispatch();

            } catch (\Exception $e) {
                if($e instanceof VirgilCryptoException) {
                    Logger::log("Invalid Encrypted Data or Recovery Private Key", 0);
                }
                else {
                    Logger::log($e->getMessage(), 0);
                }
                Redirector::toPageLog();
                exit();
            }
        }
        else {
            wp_die("Empty ".Crypto::RECOVERY_PRIVATE_KEY);
        }
    }

    /**
     *
     */
    public function addUsers()
    {
        for ($i = 0; $i < (int)$_POST['number_of_users']; $i++) {
//        $user = wp_generate_password(8, false, false);
            $user = 'user_' . rand(100, 999) . '_' . $i;
            $password = &$user;
            wp_create_user($user, $password, $user . '@mailinator.com');
        }

        $num = (int)$_POST['number_of_users'];
        Logger::log(Log::DEV_ADD_USERS." (".$num.")");
    }

    /**
     *
     */
    public function restoreDefaults()
    {
        $this->wpdb->query("DELETE FROM {$this->wpdb->users} WHERE id NOT IN (1)");
        $this->wpdb->query("DELETE FROM {$this->wpdb->usermeta} WHERE user_id NOT IN (1)");

        $users = get_users(array('fields' => array('ID')));

        foreach ($users as $user) {
            delete_user_meta($user->ID, Option::RECORD);
            delete_user_meta($user->ID, Option::PARAMS);
            delete_user_meta($user->ID, Option::ENCRYPTED);
        }

        delete_option(Option::MIGRATE_START);
        delete_option(Option::MIGRATE_FINISH);
        delete_option(Option::UPDATE_START);
        delete_option(Option::UPDATE_FINISH);
        delete_option(Option::RECOVERY_PUBLIC_KEY);
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