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

use Virgil\CryptoImpl\VirgilCrypto;
use VirgilSecurityPure\Config\Option;

/**
 * Class VirgilCryptoWrapper
 * @package VirgilSecurityPure\Core
 */
class VirgilCryptoWrapper
{
    /**
     * @var
     */
    private $vc;

    /**
     * @var \Virgil\CryptoImpl\VirgilKeyPair
     */
    private $keyPair;

    /**
     * VirgilCryptoWrapper constructor.
     * @throws \Virgil\CryptoImpl\VirgilCryptoException
     */
    public function __construct() {
        $this->vc = new VirgilCrypto();
        $this->keyPair = $this->vc->generateKeys();
    }

    /**
     * @param bool $base64Encode
     * @return string
     */
    public function getPublicKey(bool $base64Encode = false):string {
        return $base64Encode ? base64_encode($this->keyPair->getPublicKey()->getValue()) : $this->getPublicKey()->getValue();
    }

    /**
     * @param bool $base64Encode
     * @return string
     */
    public function getPrivateKey(bool $base64Encode = false):string {
        return $base64Encode ? base64_encode($this->keyPair->getPrivateKey()->getValue()) : $this->getPrivateKey()->getValue();
    }

    /**
     *
     */
    public function downloadPrivateKey() {
        $prefix = get_bloginfo('name');
        $file = $prefix.'_recovery_private_key.txt';

        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header('Content-type: text/plain');
        header("Content-disposition: attachment; filename=$file");
        echo $this->getPrivateKey(true);

        update_option(Option::RECOVERY_PUBLIC_KEY, $this->getPublicKey(true));
        Logger::log($file." downloaded");
        exit;
    }

    /**
     * @param string $password
     * @param string $publicKey
     * @return string
     * @throws \Virgil\CryptoImpl\VirgilCryptoException
     */
    public function encrypt(string $password, string $publicKey) {
        return $this->vc->encrypt($password, [$publicKey]);
    }
}