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

namespace VirgilSecurityPure\Helpers;

use VirgilSecurityPure\Config\Option;

/**
 * Class StatusHelper
 * @package VirgilSecurityPure\Helpers
 */
class StatusHelper
{
    /**
     * @return bool
     */
    public static function isAllUsersMigrated() {
        return self::getTotalUsers()==self::getMigrated();
    }

    /**
     * @return int
     */
    public static function getMigrated(): int {
        global $wpdb;
        $record = Option::RECORD;

        $sql = <<<SQL
            SELECT count(u.id) as c
            FROM wp_users u 
            LEFT JOIN wp_usermeta um 
            ON u.id=um.user_id 
            WHERE um.meta_key = "$record"
SQL;
        $count = $wpdb->get_results($sql);
        $res = null == $count[0]->c ? 0 : $count[0]->c;

        return (int)$res;
    }

    /**
     * @return float
     */
    public static function getMigratedPercents(): float {
        return (float) (round(self::getMigrated()/self::getTotalUsers(), 2))*100;
    }

    /**
     * @return int
     */
    public static function getTotalUsers(): int {
        return count_users()['total_users'];
    }
}