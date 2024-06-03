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

namespace VirgilSecurityPure\Core;

use PasswordHash;

require_once(ABSPATH . 'wp-includes/class-phpass.php');

/**
 * Class passw0rdHash
 * @package passw0rd
 */
class passw0rdHash implements Core
{
    /**
     * @var PasswordHash
     */
    private PasswordHash $corePH;

    /**
     * passw0rdHash constructor.
     */
    public function __construct()
    {
        $this->corePH = new PasswordHash(8, true);
    }

    /**
     * @param string $userPass
     * @param string $type
     * @return null|string
     */
    public function get(string $userPass, string $type): ?string
    {
        $res = null;

        switch ($type) {
            case 'params':
                $res = substr($userPass, 0, 12);
                break;
            case 'hash':
                $res = substr($userPass, 12);
                break;
        }

        return $res;
    }

    /**
     * @param string $password
     * @param string $salt
     * @return string
     */
    public function hashPassword(string $password, string $salt): string
    {
        if (strlen($password) > 4096) {
            return '*';
        }

        $random = '';

        if (CRYPT_BLOWFISH == 1 && !$this->corePH->portable_hashes) {
            $random = $this->corePH->get_random_bytes(16);
            $hash = crypt($password, $this->corePH->gensalt_blowfish($random));
            // Is it work before for debug ?
            if (strlen($hash) == 60) {
                /*var_dump('Hash password: CRYPT_BLOWFISH');
                die;*/

                return $hash;
            }
        }

        if (CRYPT_EXT_DES == 1 && !$this->corePH->portable_hashes) {
            if (strlen($random) < 3) {
                $random = $this->corePH->get_random_bytes(3);
            }
            $hash =  crypt($password, $this->gensalt_extended($random));
            if (strlen($hash) == 20) {
                /*var_dump('Hash password: CRYPT_EXT_DES');
                die;*/

                return $hash;
            }
        }

        if (strlen($random) < 6) {
            $hash = $this->corePH->crypt_private($password, $salt);
            if (strlen($hash) == 34) {
                return $hash;
            }
        }

        # Returning '*' on error is safe here, but would _not_ be safe
        # in a crypt(3)-like function used _both_ for generating new
        # hashes and for validating passwords against existing hashes.
        return '*';
    }

    /**
     * @param string $password
     * @param string $stored_hash
     * @return bool
     */
    public function checkPassword(string $password, string $stored_hash): bool
    {
        return $this->corePH->CheckPassword($password, $stored_hash);
    }

    /**
     * @param $input
     * @return string
     */
    private function gensalt_extended($input): string
    {
        $count_log2 = min($this->corePH->iteration_count_log2 + 8, 24);
        # This should be odd to not reveal weak DES keys, and the
        # maximum valid value is (2**24 - 1) which is odd anyway.
        $count = ( 1 << $count_log2 ) - 1;

        $output = '_';
        $output .= $this->corePH->itoa64[ $count & 0x3f ];
        $output .= $this->corePH->itoa64[ ( $count >> 6 ) & 0x3f ];
        $output .= $this->corePH->itoa64[ ( $count >> 12 ) & 0x3f ];
        $output .= $this->corePH->itoa64[ ( $count >> 18 ) & 0x3f ];

        $output .= $this->corePH->encode64($input, 3);

        return $output;
    }
}
