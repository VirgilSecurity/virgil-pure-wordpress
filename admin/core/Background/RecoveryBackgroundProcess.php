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

use VirgilSecurityPure\Config\Log;
use VirgilSecurityPure\Config\Option;
use VirgilSecurityPure\Core\Logger;
use VirgilSecurityPure\Config\Config;
use VirgilSecurityPure\Core\VirgilCryptoWrapper;
use VirgilSecurityPure\Helpers\DBQueryHelper;

class RecoveryBackgroundProcess extends BaseBackgroundProcess
{
    protected $action = Config::BACKGROUND_ACTION_RECOVERY;

    private $dbqh;

    private $vcw;

    private $pk;

    public function __construct(DBQueryHelper $dbqh, VirgilCryptoWrapper $vcw)
    {
        $this->dbqh = $dbqh;
        $this->vcw = $vcw;
        parent::__construct();
    }

    protected function task($user) {
        
        $password = $user->user_pass;

        update_user_meta($user->ID, Option::ENCRYPTED, $user->user_pass);
        return false;
    }

    protected function complete() {

        if($this->is_queue_empty())
        {
//            update_option(Option::MIGRATE_FINISH, microtime(true));
//
//            $duration = round(get_option(Option::MIGRATE_FINISH)-get_option
//                (Option::MIGRATE_START), 2);
//            Logger::log( Log::FINISH_MIGRATION." (duration: $duration sec.)");
//
//            delete_option(Option::MIGRATE_START);
//            delete_option(Option::MIGRATE_FINISH);

            update_option(Option::DEMO_MODE, 0);
            $this->dbqh->clearAllUsersPass();
            Logger::log(Log::DEMO_MODE_OFF);
        }

        parent::complete();
    }
}