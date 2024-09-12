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

use VirgilSecurityPure\Background\EncryptAndMigrateBackgroundProcess;
use VirgilSecurityPure\Background\RecoveryBackgroundProcess;
use VirgilSecurityPure\Config\BackgroundProcess;
use VirgilSecurityPure\Config\BuildCore;
use VirgilSecurityPure\Helpers\DBQueryHelper;
use WP_Background_Process;

/**
 * Class CoreFactory
 * @package VirgilSecurityPure\Core
 */
class CoreFactory
{
    /**
     * @param string $class
     * @return Core
     */
    public function buildCore(string $class): Core
    {
        switch ($class) {
            case BuildCore::CORE_PROTOCOL:
                return new CoreProtocol();
            case BuildCore::VIRGIL_CRYPTO_WRAPPER:
                return new VirgilCryptoWrapper();
            case BuildCore::PLUGIN_VALIDATOR:
                return new PluginValidator();
            case BuildCore::DB_QUERY_HELPER:
                return new DBQueryHelper();
            case BuildCore::CREDENTIALS_MANAGER:
                return new CredentialsManager();
            case BuildCore::FORM_HANDLER:
                return new FormHandler();
        }

        $this->throwError($class);
    }

    /**
     * @param string $class
     */
    private function throwError(string $class): void
    {
        Logger::log("Invalid class name: " . $class);
        var_dump("Invalid class name: $class");
        die;
    }
}
