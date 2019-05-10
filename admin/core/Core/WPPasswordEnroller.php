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

use Virgil\PureKit\Protocol\Protocol;
use VirgilSecurityPure\Config\Option;
use WP_User;

/**
 * Class WPPasswordEnroller
 * @package VirgilSecurityPure\Core
 */
class WPPasswordEnroller implements Core
{
    /**
     * @var Protocol
     */
    private $protocol;

    /**
     * @var passw0rdHash
     */
    private $passw0rdHash;


    /**
     * @param Protocol $protocol
     * @param passw0rdHash $passw0rdHash
     */
    public function setDep(Protocol $protocol, passw0rdHash $passw0rdHash) {
        $this->protocol = $protocol;
        $this->passw0rdHash = $passw0rdHash;
    }

    /**
     * @param WP_User $user
     * @param bool $clearUserPass
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Virgil\PureKit\Exceptions\ProtocolException
     */
    public function enroll(WP_User $user, bool $clearUserPass = false): bool
    {
        $hash = $this->passw0rdHash->get($user->user_pass, 'hash');
        $params = $this->passw0rdHash->get($user->user_pass, 'params');

        $enrollment = $this->protocol->enrollAccount($hash);
        $record = base64_encode($enrollment[0]);

        update_user_meta($user->ID, Option::RECORD, $record);
        update_user_meta($user->ID, Option::PARAMS, $params);

        return true;
    }
}