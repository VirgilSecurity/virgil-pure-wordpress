<?php
use VirgilSecurityPure\Config\Config;
?>

<div class="virgil-pure-failed-container">
    <div class="virgil-pure-failed-content">
        <h2 class="virgil-pure-failed-title">EXTENSIONS MISSING</h2>
        <p class="virgil-pure-failed-desc">
            <?php
            foreach (Config::EXTENSIONS as $extension) {
                echo !extension_loaded($extension) ? "<b>$extension</b> can't be found.<br>" : null;
            }
            ?>
            Please check <a href="https://github.com/VirgilSecurity/virgil-pure-wordpress#step-1-add-the-crypto-extensions-into-your-server-before-using-the-plugin" target="_blank">this guide</a> for more information.
        </p>
    </div>
</div>