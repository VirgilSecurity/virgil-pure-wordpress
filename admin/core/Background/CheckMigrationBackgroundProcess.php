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

use VirgilSecurityPure\Config\Option;
use VirgilSecurityPure\Core\FormHandler;
use VirgilSecurityPure\Core\Logger;
use VirgilSecurityPure\Helpers\DBQueryHelper;

/**
 * Class EncryptAndsMigrateBackgroundProcess
 * @package VirgilSecurityPure\Background
 */
class CheckMigrationBackgroundProcess
{
    const CRON_HOOK = 'check_new_users_for_virgil_migration';

    /**
     * @return void
     */
    public static function setupCronJob()
    {
        //Use wp_next_scheduled to check if the event is already scheduled
        $timestamp = wp_next_scheduled( self::CRON_HOOK );

        //If $timestamp === false schedule daily backups since it hasn't been done previously
        if( $timestamp === false ){
            Logger::log('imhere');
            wp_schedule_event( time(), 'pure_action_encrypt_and_migrate_cron_interval', self::CRON_HOOK );
        }
    }

    public function __construct()
    {
        add_action(self::CRON_HOOK, [$this, 'migrateNewUsers']);
    }

    /**
     * @return void
     */
   public static function migrateNewUsers()
   {
       $dbqh = new DBQueryHelper();
       $fh = new FormHandler();
       if (get_option(Option::AUTO_MIGRATION)) {
           if ($dbqh->isQueueEmpty(get_option(Option::AUTO_MIGRATION))) {
               $fh->migrate();
               update_option(Option::LAST_CHECK, time());
           }
       }
   }
}
