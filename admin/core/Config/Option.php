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
 * Class Option
 * @package VirgilSecurityPure\Config
 */
class Option
{
    const DEV_MODE = Config::PLUGIN_NAME.'_dev_mode';
    const DEMO_MODE = Config::PLUGIN_NAME.'_demo_mode';
    const ACTIVATION_DATE = Config::PLUGIN_NAME.'_activation_date';
    const MIGRATE_START = Config::PLUGIN_NAME.'_migrate_start';
    const MIGRATE_FINISH = Config::PLUGIN_NAME.'_migrate_finish';
    const UPDATE_START = Config::PLUGIN_NAME.'_update_start';
    const UPDATE_FINISH = Config::PLUGIN_NAME.'_update_finish';
    const ENCRYPT_START = Config::PLUGIN_NAME.'_encrypt_start';
    const ENCRYPT_FINISH = Config::PLUGIN_NAME.'_encrypt_finish';
    const RECOVERY_START = Config::PLUGIN_NAME.'_recovery_start';
    const RECOVERY_FINISH = Config::PLUGIN_NAME.'_recovery_finish';

    const PREREALISE_PREFIX = '_rf';

    const RECORD = Config::PLUGIN_NAME.'_record'.self::PREREALISE_PREFIX;
    const PARAMS = Config::PLUGIN_NAME.'_params'.self::PREREALISE_PREFIX;
    const ENCRYPTED = Config::PLUGIN_NAME.'_encrypted'.self::PREREALISE_PREFIX;

    const RECOVERY_PUBLIC_KEY = Config::PLUGIN_NAME.'_recovery_public_key';
    const RECOVERY_PRIVATE_KEY = Config::PLUGIN_NAME.'_recovery_private_key';
}