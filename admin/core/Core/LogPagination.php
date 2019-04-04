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

namespace VirgilSecurityPure\Core;

use VirgilSecurityPure\Config\Config;

/**
 * Class LogPagination
 * @package VirgilSecurityPure\Core
 */
class LogPagination implements Pagination
{
    /**
     * @var float|int
     */
    private $offset;

    /**
     * @var \wpdb
     */
    private $wpdb;

    /**
     * @var string
     */
    private $query;

    /**
     * @var int
     */
    private $ipp;

    /**
     * @var float|int
     */
    private $p;

    /**
     * LogPagination constructor.
     */
    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;

        $this->query = "SELECT * FROM $wpdb->prefix".Config::PLUGIN_DB_LOG_TABLE;

        $this->ipp = 10;

        $this->p = isset($_GET['p']) ? abs((int) $_GET['p']) : 1;
        $this->offset = ($this->p*$this->ipp)-($this->ipp);
    }

    /**
     * @return string
     */
    public function getPag(): string {
        $pag = "";

        $this->wpdb->get_results($this->query);
        $total = $this->wpdb->num_rows;

        $totalPage = ceil($total/$this->ipp);

        if($totalPage > 1){

            $pag = paginate_links( array(
                    'base' => add_query_arg('p', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => $totalPage,
                    'current' => $this->p,
                ));
        }

        return $pag;
    }

    /**
     * @return array|null|object
     */
    public function getData()
    {
        return $this->wpdb->get_results( $this->query . " ORDER BY ID DESC LIMIT {$this->offset}, {$this->ipp}");
    }
}