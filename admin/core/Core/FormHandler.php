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

namespace Plugin\Pure\Core;

use GuzzleHttp\Exception\ClientException;
use Plugin\Pure\Background\MigrateBackgroundProcess;
use Plugin\Pure\Background\UpdateBackgroundProcess;
use Plugin\Pure\Config\Config;
use Plugin\Pure\Config\Option;
use Plugin\Pure\Config\Credential;
use Plugin\Pure\Config\Log;
use Plugin\Pure\Helpers\DBQueryHelper;
use Plugin\Pure\Helpers\Redirector;

/**
 * Class FormHandler
 * @package Plugin\Pure\Core
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
    private $protocol;

    /**
     * FormHandler constructor.
     * @param CoreProtocol|null $protocol
     */
    public function __construct(CoreProtocol $protocol=null)
    {
        global $wpdb;
        $this->wpdb = $wpdb;

        $this->cm = new CredentialsManager();
        $this->dbq = new DBQueryHelper();

        $this->protocol = $protocol;
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
            $p = $this->protocol->init();
            $p->enrollAccount(Config::TEST_ENROLLMENT);
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

        update_option(Option::CHECKED_CREDENTIALS, 1);
        Logger::log(Log::INIT_CREDENTIALS);
    }

    /**
     *
     */
    public function migrate()
    {
        $users = get_users(array('fields' => array('ID', 'user_pass')));

        $migrateBackgroundProcess = new MigrateBackgroundProcess($this->protocol);

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
                $updateBackgroundProcess = new UpdateBackgroundProcess($this->protocol);

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
}