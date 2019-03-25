<?php
use Plugin\Pure\Config\Config;
?>

<div class="virgil-phe-failed-container">
    <div class="virgil-phe-failed-content">
        <h2 class="virgil-phe-failed-title">EXTENSION MISSING</h2>
        <p class="virgil-phe-failed-desc">
            <?= Config::EXTENSION_NAME ?> can't be found. Please check <a href="https://github
            .com/VirgilSecurity/virgil-passw0rd-php#install-and-configure-sdk" target="_blank">this guide</a> for more
            information.
        </p>
    </div>
</div>