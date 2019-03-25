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

namespace Plugin\Pure\Core;

use Plugin\Pure\Config\Config;
use Plugin\Pure\Config\Credential;
use Plugin\Pure\Config\Option;
use Plugin\Pure\Helpers\InfoHelper;

class PageBuilder
{
    /**
     * @return bool
     */
    public function disabledBlock(): bool
    {
        return !extension_loaded(Config::EXTENSION_NAME);
    }

    public function demoModeBlock(): bool
    {
        if($this->disabledBlock())
            return false;

        return (bool)get_option(Option::DEMO_MODE);
    }

    public function credentialsBlock()
    {
        if($this->disabledBlock())
            return false;

        return ((bool)!get_option(Option::CHECKED_CREDENTIALS)&&!$this->isCredentialsSet()
            &&$this->isMainPage());
    }

    public function migrationBlock()
    {
        if($this->disabledBlock())
            return false;

        return ((bool)get_option(Option::CHECKED_CREDENTIALS)&&$this->isCredentialsSet()
            &&$this->isMainPage()&&!$this->isAllUsersMigrated());
    }

    public function updateBlock()
    {
        if($this->disabledBlock())
            return false;

        return ((bool)get_option(Option::CHECKED_CREDENTIALS)&&$this->isCredentialsSet()
            &&$this->isMainPage()&&$this->isAllUsersMigrated());
    }

    public function logBlock()
    {
        if($this->disabledBlock())
            return false;

        return $this->isLogPage();
    }

    public function faqBlock()
    {
        return $this->isFAQPage();
    }

    public function infoBlock()
    {
        return $this->isMainPage()||$this->disabledBlock();
    }

    private function isAllUsersMigrated()
    {
        return 100==(int)InfoHelper::getMigratedPercents();
    }

    private function isMainPage()
    {
        return Config::MAIN_PAGE==$_GET['page'];
    }

    private function isLogPage()
    {
        return Config::LOG_PAGE==$_GET['page'];
    }

    private function isFAQPage()
    {
        return Config::FAQ_PAGE==$_GET['page'];
    }

    /**
     * @return bool
     */
    private function isCredentialsSet(): bool
    {
        return(!empty($_ENV[Credential::APP_TOKEN])&&!empty($_ENV[Credential::APP_SECRET_KEY])&&!empty
            ($_ENV[Credential::SERVICE_PUBLIC_KEY]));
    }
}