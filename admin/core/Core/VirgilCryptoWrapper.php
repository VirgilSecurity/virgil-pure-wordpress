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

use Virgil\Crypto\Core\VirgilKeys\VirgilPublicKeyCollection;
use Virgil\Crypto\Exceptions\VirgilCryptoException;
use Virgil\Crypto\VirgilCrypto;
use Virgil\Crypto\Core\VirgilKeys\VirgilKeyPair;
use Virgil\Crypto\Core\VirgilKeys\VirgilPrivateKey;
use Virgil\Crypto\Core\VirgilKeys\VirgilPublicKey;
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
     * @var VirgilCrypto
     */
    private VirgilCrypto $vc;

    /**
     * @var VirgilKeyPair
     */
    private VirgilKeyPair $keyPair;

    /**
     * VirgilCryptoWrapper constructor.
     */
    public function __construct()
    {
        $this->vc = new VirgilCrypto();
    }

    /**
     * @return void
     * @throws VirgilCryptoException
     */
    public function generateKeys(): void
    {
        $this->keyPair = $this->vc->generateKeyPair();
    }

    /**
     * @param int $type
     * @return string
     * @throws PluginPureException
     * @throws VirgilCryptoException
     */
    public function getKey(int $type): string
    {
        switch ($type) {
            case Crypto::PUBLIC_KEY:
                $keyObject = $this->keyPair->getPublicKey();
                $res = $this->vc->exportPublicKey($keyObject);
                break;
            case Crypto::PRIVATE_KEY:
                $keyObject = $this->keyPair->getPrivateKey();
                $res = $this->vc->exportPrivateKey($keyObject);
                break;
            default:
                throw new PluginPureException('Invalid key type (Get key)');
        }

        return $res;
    }

    /**
     * @return void
     * @throws PluginPureException
     * @throws VirgilCryptoException
     */
    public function downloadPrivateKey(): void
    {
        // earlier here been 'to PEM'
        $prefix = get_bloginfo('name');
        $file = $prefix.Crypto::RECOVERY_PRIVATE_KEY_FILE;
        $prk = $this->getKey(Crypto::PRIVATE_KEY);
        $keyPair = $this->vc->importPrivateKey($prk);

        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header('Content-type: text/plain');
        header("Content-disposition: attachment; filename=$file");
        echo $this->derToPem($this->vc->exportPrivateKey($keyPair->getPrivateKey()));
        update_option(Option::RECOVERY_PUBLIC_KEY, $this->derToPem($this->vc->exportPublicKey($keyPair->getPublicKey())));
        Logger::log($file." downloaded");
        exit;
    }

    /**
     * @param string $der
     * @return string
     */
    private function derToPem(string $der): string
    {
        return '-----BEGIN PRIVATE KEY-----' . PHP_EOL .
        chunk_split(base64_encode($der), 64, PHP_EOL) .
        '-----END PRIVATE KEY-----' . PHP_EOL;
    }

    /**
     * @param int $type
     * @param string $key
     * @return VirgilPublicKey|VirgilKeyPair
     * @throws PluginPureException
     * @throws VirgilCryptoException
     */
    public function importKey(int $type, string $key): VirgilPublicKey|VirgilKeyPair
    {
        // earlier here been 'to DER'
        return match ($type) {
            Crypto::PUBLIC_KEY => $this->vc->importPublicKey($key),
            Crypto::PRIVATE_KEY => $this->vc->importPrivateKey($key),
            default => throw new PluginPureException('Invalid key type (Import Key)'),
        };
    }

    /**
     * @param string $password
     * @param VirgilPublicKey $virgilPublicKey
     * @return string
     * @throws VirgilCryptoException
     */
    public function encrypt(string $password, VirgilPublicKey $virgilPublicKey): string
    {
        $keyCollection = new VirgilPublicKeyCollection;
        $keyCollection->addPublicKey($virgilPublicKey);
        return base64_encode($this->vc->encrypt($password, $keyCollection));
    }

    /**
     * @param string $encryptedPassword
     * @param VirgilPrivateKey $virgilPrivateKey
     * @return string
     * @throws VirgilCryptoException
     */
    public function decrypt(string $encryptedPassword, VirgilPrivateKey $virgilPrivateKey): string
    {
        return $this->vc->decrypt($encryptedPassword, $virgilPrivateKey);
    }
}
