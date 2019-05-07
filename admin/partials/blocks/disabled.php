<?php
use VirgilSecurityPure\Config\Config;
?>

<div class="virgil-phe-failed-container">
    <div class="virgil-phe-failed-content">
        <h2 class="virgil-phe-failed-title">EXTENSION MISSING</h2>
        <p class="virgil-phe-failed-desc">
            <?= Config::EXTENSION_VSCE_PHE_PHP ?> can't be found. Please check <a href="https://github
            .com/VirgilSecurity/virgil-pure-wordpress#step-1-add-the-vsce_phe_php-extension-into-your-server-before-using-the-plugin" target="_blank">this guide</a> for more information.
        </p>
    </div>
</div>