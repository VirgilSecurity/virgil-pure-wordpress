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

use VirgilSecurityPure\Config\Config;
use VirgilSecurityPure\Config\Option;
use VirgilSecurityPure\Core\Core;

/**
 * Class DBQueryHelper
 * @package VirgilSecurityPure\Helpers
 */
class DBQueryHelper implements Core
{
    /**
     * @var \wpdb
     */
    private $wpdb;

    /**
     * @var string
     */
    private $tableLog;

    /**
     * @var string
     */
    private $charsetCollate;

    private $tableUsers;

    /**
     * DBQueryHelper constructor.
     */
    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;

        $this->charsetCollate = $this->wpdb->get_charset_collate();
        $this->tableLog = $this->wpdb->prefix . Config::PLUGIN_DB_LOG_TABLE;
        $this->tableUsers = $this->wpdb->prefix.'users';
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return array|null|object
     */
    public function getAllLogs(int $offset = 0, int $limit = 0)
    {
        return $this->wpdb->get_results("SELECT * FROM {$this->tableLog} ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}");
    }

    /**
     *
     */
    public function createTableLog()
    {
        $sql = "CREATE TABLE {$this->tableLog} (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		description varchar (255) NOT NULL,
        status smallint(3) NOT NULL,
		date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		PRIMARY KEY  (id)
	) {$this->charsetCollate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     *
     */
    public function dropTableLog()
    {
        $sql = "DROP TABLE IF EXISTS {$this->tableLog}";
        $this->wpdb->query($sql);
    }

    /**
     *
     */
    public function clearTableLog()
    {
        $this->wpdb->query("DELETE FROM {$this->tableLog} WHERE id NOT IN (1)");
    }

    /**
     *
     */
    public function clearAllUsersPass()
    {
        $this->wpdb->query("UPDATE {$this->tableUsers} SET user_pass=''");
    }

    public function passRecovery(int $id, string $password)
    {
        $this->wpdb->query("UPDATE {$this->tableUsers} SET user_pass='{$password}' WHERE id={$id}");
    }

    /**
     * @param int $id
     */
    public function clearUserPass(int $id)
    {
        $this->wpdb->query("UPDATE {$this->tableUsers} SET user_pass='' WHERE ID=$id");
    }

    /**
     * @param string $name
     */
    public function clearActionProcess(string $name)
    {
        $process = '%'.Config::PLUGIN_NAME.'_action_'.$name.'_process%';
        $batch = '%'.Config::PLUGIN_NAME.'_action_'.$name.'_batch%';
        $this->wpdb->query("DELETE FROM {$this->wpdb->options} WHERE option_name LIKE \"$process\" AND option_name LIKE \"$batch\"");
    }

    /**
     *
     */
    public function clearPureParams()
    {
        $encrypted = Option::ENCRYPTED;
        $params = Option::PARAMS;
        $record = Option::RECORD;

        $this->wpdb->query("DELETE FROM {$this->wpdb->usermeta} WHERE meta_key IN ('$encrypted', '$params', '$record')");
    }


}