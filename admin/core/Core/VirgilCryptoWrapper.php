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
use Virgil\CryptoImpl\VirgilKeyPair;
use Virgil\CryptoImpl\VirgilPrivateKey;
use Virgil\CryptoImpl\VirgilPublicKey;
use VirgilSecurityPure\Config\Crypto;
use VirgilSecurityPure\Config\Option;
use VirgilSecurityPure\Exceptions\PluginPureException;

/**
 * Class VirgilCryptoWrapper
 * @package VirgilSecurityPure\Core
 */
class VirgilCryptoWrapper implements Core
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
     */
    public function __construct() {
        $this->vc = new VirgilCrypto();
    }

    /**
     * @return void
     * @throws \Virgil\CryptoImpl\VirgilCryptoException
     */
    public function generateKeys(): void
    {
        $this->keyPair = $this->vc->generateKeys();
    }

    /**
     * @param int $type
     * @return string
     * @throws PluginPureException
     * @throws \Virgil\CryptoImpl\VirgilCryptoException
     */
    public function getKey(int $type): string {
        if(!($this->keyPair instanceof VirgilKeyPair))
            throw new PluginPureException("Invalid or empty key pair");

        switch ($type) {
            case Crypto::PUBLIC_KEY:
                $keyObject = $this->keyPair->getPublicKey();
                $res = $this->vc->exportPublicKey($keyObject);
                break;
            case Crypto::PRIVATE_KEY:
                $keyObject = $this->keyPair->getPrivateKey();
                $res = $this->vc->exportPrivateKey($keyObject, Crypto::PRIVATE_KEY_PASSWORD);
                break;
            default:
                throw new PluginPureException('Invalid key type (Get key)');
                break;
        }

        return $res;
    }

    /**
     * @throws PluginPureException
     * @throws \Virgil\CryptoImpl\VirgilCryptoException
     */
    public function downloadPrivateKey() {
        $prefix = get_bloginfo('name');
        $file = $prefix.Crypto::RECOVERY_PRIVATE_KEY_FILE;
        $pk = $this->getKey(Crypto::PUBLIC_KEY);
        $prk = $this->getKey(Crypto::PRIVATE_KEY);

        $pemPrK = \VirgilKeyPair::privateKeyToPEM($prk, Crypto::PRIVATE_KEY_PASSWORD);
        $pemPK = \VirgilKeyPair::publicKeyToPEM($pk);

        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header('Content-type: text/plain');
        header("Content-disposition: attachment; filename=$file");
        echo $pemPrK;
        update_option(Option::RECOVERY_PUBLIC_KEY, $pemPK);
        Logger::log($file." downloaded");
        exit;
    }

    /**
     * @param int $type
     * @param string $key
     * @return \Virgil\CryptoImpl\VirgilPrivateKey|VirgilPublicKey
     * @throws PluginPureException
     * @throws \Virgil\CryptoImpl\VirgilCryptoException
     */
    public function importKey(int $type, string $key)
    {
        switch ($type) {
            case Crypto::PUBLIC_KEY:
                $keyData = \VirgilKeyPair::publicKeyToDER($key);
                $keyObject = $this->vc->importPublicKey($keyData);
                break;
            case Crypto::PRIVATE_KEY:
                $keyData = \VirgilKeyPair::privateKeyToDER($key, Crypto::PRIVATE_KEY_PASSWORD);
                $keyObject = $this->vc->importPrivateKey($keyData, Crypto::PRIVATE_KEY_PASSWORD);
                break;
            default:
                throw new PluginPureException('Invalid key type (Import Key)');
                break;
        }

        return $keyObject;
    }

    /**
     * @param string $password
     * @param VirgilPublicKey $virgilPublicKey
     * @return string
     * @throws \Virgil\CryptoImpl\VirgilCryptoException
     */
    public function encrypt(string $password, VirgilPublicKey $virgilPublicKey): string
    {
        return base64_encode($this->vc->encrypt($password, [$virgilPublicKey]));
    }

    /**
     * @param string $encryptedPassword
     * @param VirgilPrivateKey $virgilPrivateKey
     * @return string
     * @throws \Virgil\CryptoImpl\VirgilCryptoException
     */
    public function decrypt(string $encryptedPassword, VirgilPrivateKey $virgilPrivateKey): string
    {
        return $this->vc->decrypt($encryptedPassword, $virgilPrivateKey);
    }
}