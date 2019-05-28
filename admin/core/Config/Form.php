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

namespace VirgilSecurityPure\Config;

/**
 * Class Form
 * @package VirgilSecurityPure\Config
 */
class Form
{
    const PREFIX = Config::PLUGIN_NAME."_form_";

    const ALL = [self::DEMO, self::DOWNLOAD_RECOVERY_PRIVATE_KEY, self::CREDENTIALS, self::MIGRATE, self::UPDATE,
        self::DEV_ADD_USERS, self::DEV_RESTORE_DEFAULTS, self::RECOVERY];

    const NONCE = self::PREFIX.'nonce';
    const ACTION = 'virgil_pure';
    const TYPE = 'form_type';

    const DEMO = self::PREFIX.'demo';
    const DOWNLOAD_RECOVERY_PRIVATE_KEY = self::PREFIX.'downloadRecoveryPrivateKey';
    const CREDENTIALS = self::PREFIX.'credentials';
    const MIGRATE = self::PREFIX.'migrate';
    const UPDATE = self::PREFIX.'update';
    const RECOVERY = self::PREFIX.'recovery';
    const DEV_ADD_USERS = self::PREFIX.'addUsers';
    const DEV_RESTORE_DEFAULTS = self::PREFIX.'restoreDefaults';
}