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

use VirgilSecurityPure\Config\Credential;
use VirgilSecurityPure\Helpers\Redirector;
use Virgil\PureKit\Protocol\Protocol;
use Virgil\PureKit\Protocol\ProtocolContext;

/**
 * Class CoreProtocol
 * @package VirgilSecurityPure\Core
 */
class CoreProtocol implements Core
{
    /**
     * @return null|Protocol
     */
    public function init()
    {
        $protocol = null;

        try {
            if(!empty($_ENV[Credential::APP_TOKEN]) && !empty($_ENV[Credential::APP_SECRET_KEY]) && !empty
                ($_ENV[Credential::SERVICE_PUBLIC_KEY]))

                $protocol = $this->createProtocol();

        } catch (\Exception $e) {
            if(0==$e->getCode()) {
                Logger::log("Invalid credentials", 0);
                $credentialsManager = new CredentialsManager();
                $credentialsManager->addUpdateTokenToOldCredentials("");
            }
            else {
                Logger::log($e->getMessage(),0);
            }
            Redirector::toPageLog();
        }

        return $protocol;
    }

    /**
     * @return Protocol
     * @throws \Exception
     */
    private function createProtocol(): Protocol
    {
        $context = (new ProtocolContext)->create([
            'appToken' => $_ENV[Credential::APP_TOKEN],
            'appSecretKey' => $_ENV[Credential::APP_SECRET_KEY],
            'servicePublicKey' => $_ENV[Credential::SERVICE_PUBLIC_KEY],
            'updateToken' => $_ENV[Credential::UPDATE_TOKEN],
        ]);

        return new Protocol($context);
    }
}