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

namespace VirgilSecurityPure\Helpers;

use VirgilSecurityPure\Config\Config;
use VirgilSecurityPure\Config\Option;

/**
 * Class InfoHelper
 * @package plugin_pure
 */
class InfoHelper
{
    /**
     * @return string
     */
    public static function getMigrated(): string
    {
        global $wpdb;
        $record = Option::USER_RECORD;

        $sql = <<<SQL
            SELECT count(u.id) as c
            FROM $wpdb->users u
            LEFT JOIN $wpdb->usermeta um
            ON u.id=um.user_id
            WHERE um.meta_key = "$record"
SQL;
        $count = $wpdb->get_results($sql);
        return null == $count[0]->c ? 0 : $count[0]->c;
    }

    /**
     * @return float
     */
    public static function getMigratedPercents(): float
    {
        return round(self::getMigrated() / self::getTotalUsers(), 2) * 100;
    }

    /**
     * @return bool
     */
    public static function isAllUsersMigrated(): bool
    {
        return self::getTotalUsers() == self::getMigrated();
    }

    /**
     * @return string
     */
    public static function getTotalUsers(): string
    {
        return count_users()['total_users'];
    }

    /**
     * @return string
     */
    public static function getEnvFilePath(): string
    {
        return WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . Config::PLUGIN_NAME . DIRECTORY_SEPARATOR . '.env';
    }

    /**
     * @return bool
     */
    public static function isRecoveryCheckboxAgree(): bool
    {
        return (bool) get_option(Option::RECOVERY_CHECKBOX_AGREE);
    }
}
