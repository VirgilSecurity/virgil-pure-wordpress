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
use VirgilSecurityPure\Config\Option;

/**
 * Class PageBuilder
 * @package VirgilSecurityPure\Core
 */
class PageBuilderPublic extends PageBuilderProtected
{
    /**
     * @return bool
     */
    public function disabled(): bool
    {
        return !extension_loaded(Config::EXTENSION_VSCE_PHE_PHP)||!extension_loaded(Config::EXTENSION_VIRGIL_CRYPTO_PHP);
    }

    /**
     * @return bool
     */
    public function credentials(): bool
    {
        if($this->disabled()||!$this->isRecoveryPublicKeyExists())
            return false;

        return (!$this->isCredentialsSet()&&$this->isMainPage());
    }

    /**
     * @return bool
     */
    public function migrate(): bool
    {
        if($this->disabled()||!$this->isRecoveryPublicKeyExists())
            return false;

        return ($this->isCredentialsSet()&&$this->isMainPage()&&!$this->isAllUsersMigrated());
    }

    /**
     * @return bool
     */
    public function update(): bool
    {
        if($this->disabled()||!$this->isRecoveryPublicKeyExists())
            return false;

        return ($this->isCredentialsSet()&&$this->isMainPage()&&$this->isAllUsersMigrated());
    }

    /**
     * @return bool
     */
    public function log(): bool
    {
        if($this->disabled())
            return false;

        return $this->isLogPage();
    }

    /**
     * @return bool
     */
    public function faq(): bool
    {
        return $this->isFAQPage();
    }

    /**
     * @return bool
     */
    public function recovery(): bool
    {
        if($this->disabled())
            return false;

        return $this->isRecoveryPage();
    }

    /**
     * @return bool
     */
    public function generate_recovery_keys(): bool
    {
        return !$this->isRecoveryPublicKeyExists()&&(!$this->isFAQPage()&&!$this->isLogPage());
    }

    /**
     * @return bool
     */
    public function info(): bool
    {
        return ($this->isMainPage()||$this->disabled())&&!$this->generate_recovery_keys();
    }
}