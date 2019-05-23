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
 * Class Config
 * @package VirgilSecurityPure\Config
 */
class Config
{
    const EXTENSION_VSCE_PHE_PHP = 'vsce_phe_php';
    const EXTENSION_VIRGIL_CRYPTO_PHP = 'virgil_crypto_php';
    const EXTENSIONS = [self::EXTENSION_VSCE_PHE_PHP, self::EXTENSION_VIRGIL_CRYPTO_PHP];

    const PLUGIN_NAME = 'virgil-pure';
    const PLUGIN_NAME_UNDERSCORE = 'virgil_pure';

    const PLUGIN_FULL_NAME = self::PLUGIN_NAME.DIRECTORY_SEPARATOR.self::PLUGIN_NAME.'.php';

    const MAIN_PAGE = 'Virgil_Pure';

    const MAIN_PAGE_TITLE = 'Virgil Pure';
    const ACTION_PAGE = self::MAIN_PAGE.'_Action';
    const LOG_PAGE = self::MAIN_PAGE.'_Log';
    const FAQ_PAGE = self::MAIN_PAGE.'_FAQ';
    const RECOVERY_PAGE = self::MAIN_PAGE.'_Recovery';
    const DEV_PAGE = self::MAIN_PAGE.'_Dev';
    const CHANGE_MODE = self::MAIN_PAGE.'_Change_Mode';

    const CAPABILITY = 'administrator';

    const TEST_ENROLLMENT = 'test-enrollment';

    const PLUGIN_DB_LOG_TABLE = self::PLUGIN_NAME_UNDERSCORE.'_log';

    const BACKGROUND_ACTION_MIGRATE = self::PLUGIN_NAME."_action_encrypt_and_migrate";
    const BACKGROUND_ACTION_UPDATE = self::PLUGIN_NAME."_action_update";
    const BACKGROUND_ACTION_RECOVERY = self::PLUGIN_NAME."_action_recovery";

    const ALL_BACKGROUND_PROCESSES = ['encrypt_and_migrate', 'update', 'recovery'];
}