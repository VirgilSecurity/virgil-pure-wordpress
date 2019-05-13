<?php

use VirgilSecurityPure\Config\Form;
use VirgilSecurityPure\Helpers\ConfigHelper;
use VirgilSecurityPure\Helpers\InfoHelper;
use VirgilSecurityPure\Helpers\StatusHelper;
use VirgilSecurityPure\Config\Config;

?>

<div class="virgil-phe-demo-container">
    <div class="virgil-phe-demo-content">
        <h2 class="virgil-phe-demo-title">Demo mode is on</h2>
        <p class="virgil-phe-demo-desc">
            In demo mode, no data in your database will be altered. To demonstrate how Virgil Pure works, a new
            column will be created to hold the newly protected password data. When you're ready to go live, your
            password hashes will be translated into cryptographically protected data.
            <?php if(!StatusHelper::isAllUsersMigrated()) { ?>
            <br>To switch demo mod off migrate all users first.</p>
            <?php } else { ?>
                </p>
                <div>
                <a class="virgil-phe-global-button virgil-phe-demo-button" href="<?= admin_url("/admin.php?page=" . 
                    Config::CHANGE_MODE) ?>">Change Mode</a></div>
            <?php } ?>

    </div>
</div>