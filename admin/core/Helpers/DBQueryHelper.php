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
use VirgilSecurityPure\Core\Core;
use wpdb;

/**
 * Class DBQueryHelper
 * @package VirgilSecurityPure\Helpers
 */
class DBQueryHelper implements Core
{
    /**
     * @var wpdb
     */
    private wpdb $wpdb;

    /**
     * @var string
     */
    private string $tableLog;

    /**
     * @var string
     */
    private string $charsetCollate;

    /**
     * @var string
     */
    private string $tableUsers;

    /**
     * DBQueryHelper constructor.
     */
    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;

        $this->charsetCollate = $this->wpdb->get_charset_collate();
        $this->tableLog = $this->wpdb->prefix . Config::PLUGIN_DB_LOG_TABLE;
        $this->tableUsers = $this->wpdb->prefix . 'users';
    }

    /**
     * @return void
     */
    public function createTableLog(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->tableLog} (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		description varchar (512) NOT NULL,
        status smallint(3) NOT NULL,
		date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		PRIMARY KEY  (id)
	) {$this->charsetCollate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * @return void
     */
    public function dropTableLog(): void
    {
        $sql = "DROP TABLE IF EXISTS {$this->tableLog}";
        $this->wpdb->query($sql);
    }

    /**
     * @return void
     */
    public function clearTableLog(): void
    {
        $this->wpdb->query("DELETE FROM {$this->tableLog} WHERE id NOT IN (1)");
    }

    /**
     * @param int $id
     * @param string $password
     * @return void
     */
    public function passRecovery(int $id, string $password): void
    {
        $this->wpdb->query("UPDATE {$this->tableUsers} SET user_pass='{$password}' WHERE ID={$id}");
    }

    /**
     * @param int $id
     */
    public function removePasswordHashForUser(int $id): void
    {
        $this->wpdb->query("UPDATE {$this->tableUsers} SET user_pass=SUBSTRING(user_pass,1,12) WHERE ID={$id}");
    }

    /**
     * @param string $name
     */
    public function clearActionProcess(string $name): void
    {
        $process = '%' . $name . '_process%';
        $batch = '%' . $name . '_batch%';
        $this->wpdb->query(
            "DELETE FROM {$this->wpdb->options} WHERE option_name LIKE \"$process\" AND option_name LIKE \"$batch\""
        );
    }

    /**
     * @return void
     */
    public function clearPureParams(): void
    {
        $this->wpdb->query(
            "DELETE FROM {$this->wpdb->usermeta} WHERE meta_key IN ('$record')"
        );
    }
}
