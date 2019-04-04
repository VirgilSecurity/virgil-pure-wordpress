<?php
use VirgilSecurityPure\Config\Form;
use VirgilSecurityPure\Helpers\InfoHelper;
use VirgilSecurityPure\Helpers\StatusHelper;
?>

<div class="virgil-phe-demo-container">
    <div class="virgil-phe-demo-content">
        <h2 class="virgil-phe-demo-title">Demo mode is on</h2>
        <p class="virgil-phe-demo-desc">
            In demo mode, no data in your database will be altered. To demonstrate how Virgil Pure works, a new
            column will be created to hold the newly protected password data. When you're ready to go live, your
            password hashes will be translated into cryptographically protected data.
        </p>

        <?php if(!StatusHelper::isAllUsersMigrated()) { ?>
            <p class="virgil-phe-demo-desc">To switch demo mod off migrate all users first.</p>
        <?php } ?>

        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <input type="hidden" name="action" value="<?= Form::ACTION ?>">
            <input type="hidden" name="form_type" value="<?= Form::DEMO ?>">
            <?php wp_nonce_field('nonce', Form::NONCE) ?>
            <input type="submit" name="submit" id="submit" class="virgil-phe-global-button virgil-phe-demo-button"
                   value="Switch demo mode off" <?= 100==InfoHelper::getMigratedPercents() ? null : "disabled" ?>
            >
        </form>
    </div>
</div>