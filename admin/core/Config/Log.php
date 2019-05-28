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
 * Class Log
 * @package VirgilSecurityPure\Config
 */
class Log
{
    const PLUGIN_ACTIVATION = "Plugin activation";
    const INIT_CREDENTIALS = "Init credentials";
    const START_MIGRATION = "Start of the migration process";
    const FINISH_MIGRATION = "The end of the migration process";
    const START_UPDATE = "Start of the update process";
    const FINISH_UPDATE = "The end of the update process";
    const START_ENCRYPT = "Start of the encrypt process";
    const FINISH_ENCRYPT = "The end of the encrypt process";
    const START_RECOVERY = "Start of the recovery process";
    const FINISH_RECOVERY = "The end of the recovery process";

    const DEV_ADD_USERS = "Add users";
    const DEV_RESTORE_DEFAULTS = "Restore defaults";

    const INVALID_APP_TOKEN = "Invalid ".Credential::APP_TOKEN;
    const INVALID_PROOF = "Invalid ".Credential::SERVICE_PUBLIC_KEY." or ".
    Credential::APP_SECRET_KEY;

    const GENERATE_RECOVERY_KEYS = "Generate Recovery Keys";
    const RECOVERY_ERROR = "Invalid ".Crypto::RECOVERY_PRIVATE_KEY;
}