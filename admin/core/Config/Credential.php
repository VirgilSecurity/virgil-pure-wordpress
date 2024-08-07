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

namespace VirgilSecurityPure\Config;

/**
 * Class Credential
 * @package plugin_pure
 */
class Credential
{
    public const ALL = [
        self::APP_TOKEN,
        self::SERVICE_PUBLIC_KEY,
        self::APP_SECRET_KEY,
        self::UPDATE_TOKEN,
        self::NONROTATABLE_MASTER_SECRET,
        self::BACKUP_PUBLIC_KEY
    ];

    public const REQUIRED_CREDENTIALS = [
        self::APP_TOKEN,
        self::SERVICE_PUBLIC_KEY,
        self::APP_SECRET_KEY,
        self::NONROTATABLE_MASTER_SECRET,
        self::BACKUP_PUBLIC_KEY
    ];

    public const APP_TOKEN = 'APP_TOKEN';
    public const SERVICE_PUBLIC_KEY = 'SERVICE_PUBLIC_KEY';
    public const APP_SECRET_KEY = 'APP_SECRET_KEY';
    public const UPDATE_TOKEN = 'UPDATE_TOKEN';
    public const NONROTATABLE_MASTER_SECRET = 'NONROTATABLE_MASTER_SECRET';
    public const BACKUP_PUBLIC_KEY = 'BACKUP_PUBLIC_KEY';

    const APP_TOKEN_PREFIX = 'AT';
    const SERVICE_PUBLIC_KEY_PREFIX = 'PK';
    const APP_SECRET_KEY_PREFIX = 'SK';
    const UPDATE_TOKEN_PREFIX = 'UT';

    /**
     * @return bool
     */
    public static function isAllRequiredCredentialsSet(): bool
    {
        $allSet = true;
        foreach (self::REQUIRED_CREDENTIALS as $credential) {
            if (empty($_ENV[$credential])) {
                $allSet = false;
            }
        }

        return $allSet;
    }
}
