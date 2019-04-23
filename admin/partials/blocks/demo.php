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
            password hashes will be translated into cryptographically protected data.<br>
            <?php if(!StatusHelper::isAllUsersMigrated()) { ?>
                To switch demo mod off migrate all users first.
            <?php } else { ?>
                <a class="virgil-phe-demo-desc" href="<?= admin_url("/admin.php?page=" . Config::DEMO_MODE_OFF_PAGE) ?>">Change Mode</a>
            <?php } ?>
        </p>
    </div>
</div>