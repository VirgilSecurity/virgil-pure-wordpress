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
use VirgilSecurityPure\Config\Credential;
use VirgilSecurityPure\Helpers\InfoHelper;

/**
 * Class PageBuilderProtected
 * @package VirgilSecurityPure\Core
 */
class PageBuilderProtected
{
    /**
     * @return bool
     */
    protected function isAllUsersMigrated(): bool
    {
        return 100==(int)InfoHelper::getMigratedPercents();
    }

    /**
     * @return bool
     */
    protected function isMainPage(): bool
    {
        return Config::ACTION_PAGE==$_GET['page'];
    }

    /**
     * @return bool
     */
    protected function isLogPage(): bool
    {
        return Config::LOG_PAGE==$_GET['page'];
    }

    /**
     * @return bool
     */
    protected function isFAQPage(): bool
    {
        return Config::FAQ_PAGE==$_GET['page'];
    }

    /**
     * @return bool
     */
    protected function isRecoveryPage(): bool
    {
        return Config::RECOVERY_PAGE==$_GET['page'];
    }

    /**
     * @return bool
     */
    protected function isCredentialsSet(): bool
    {
        return(!empty($_ENV[Credential::APP_TOKEN])&&!empty($_ENV[Credential::APP_SECRET_KEY])&&!empty
            ($_ENV[Credential::SERVICE_PUBLIC_KEY]));
    }

    /**
     * @return bool
     */
    protected function isRecoveryPublicKeyExists(): bool
    {
        return InfoHelper::isRecoveryPrivateKeyExists();
    }
}