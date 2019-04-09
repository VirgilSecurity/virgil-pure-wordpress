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
use VirgilSecurityPure\Background\MigrateBackgroundProcess;
use VirgilSecurityPure\Background\UpdateBackgroundProcess;
use VirgilSecurityPure\Config\Config;
use VirgilSecurityPure\Config\Option;
use VirgilSecurityPure\Config\Credential;
use VirgilSecurityPure\Config\Log;
use VirgilSecurityPure\Helpers\DBQueryHelper;
use VirgilSecurityPure\Helpers\Redirector;

/**
 * Class FormHandler
 * @package VirgilSecurityPure\Core
 */
class FormHandler
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
     * FormHandler constructor.
     * @param CoreProtocol $coreProtocol
     */
    public function __construct(CoreProtocol $coreProtocol)
    {
        global $wpdb;
        $this->wpdb = $wpdb;

        $this->cm = new CredentialsManager();
        $this->dbq = new DBQueryHelper();

        $this->coreProtocol = $coreProtocol;
    }

    /**
     *
     */
    public function demo()
    {
        update_option(Option::DEMO_MODE, 0);
        $this->dbq->clearAllUsersPass();
        Logger::log(Log::DEMO_MODE_OFF);
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

        $migrateBackgroundProcess = new MigrateBackgroundProcess($this->coreProtocol->init());

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
                $updateBackgroundProcess = new UpdateBackgroundProcess($this->coreProtocol->init());

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
        $this->wpdb->query('DELETE FROM wp_users WHERE id NOT IN (1)');
        $this->wpdb->query('DELETE FROM wp_usermeta WHERE user_id NOT IN (1)');

        $users = get_users(array('fields' => array('ID')));

        foreach ($users as $user) {
            delete_user_meta($user->ID, Option::RECORD);
            delete_user_meta($user->ID, Option::PARAMS);
        }

        update_option(Option::DEMO_MODE, 1);

        delete_option(Option::MIGRATE_START);
        delete_option(Option::MIGRATE_FINISH);
        delete_option(Option::UPDATE_START);
        delete_option(Option::UPDATE_FINISH);
        delete_option('_transient_doing_cron');
        $this->wpdb->query("DELETE FROM wp_options WHERE option_name LIKE '%migrate_batch_%'");
        $this->wpdb->query("DELETE FROM wp_options WHERE option_name LIKE '%migrate_process'");
        $this->wpdb->query("DELETE FROM wp_options WHERE option_name LIKE '%update_process'");

        $this->cm->addEmptyCredentials();

        $this->dbq->clearTableLog();

        $pass = '$P$Be8bkgCZxUx096p9aAzZ3ydfE/qMyd0';
        $this->wpdb->query("UPDATE wp_users SET user_pass='$pass' WHERE id=1");

        Logger::log(Log::DEV_RESTORE_DEFAULTS);
    }
}