<?php
use VirgilSecurityPure\Config\Form;
?>

<div class="virgil-phe-global-section">
    <h3 class="virgil-phe-global-page-title">Change Mode</h3>
     <div class="virgil-phe-demo-container">
            <div class="virgil-phe-demo-content">
                <h2 class="virgil-phe-demo-title">Recovery Private Key</h2>
                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                    <input type="hidden" name="action" value="<?= Form::ACTION ?>">
                    <input type="hidden" name="form_type" value="<?= Form::DOWNLOAD_RECOVERY_PRIVATE_KEY ?>">
                    <?php wp_nonce_field('nonce', Form::NONCE) ?>
                    <input type="submit" name="submit" id="submit" class="virgil-phe-global-button virgil-phe-demo-button"
                           value="Download">
                </form>
            </div>
        </div>

        <form class="virgil-phe-credentials-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <input type="hidden" name="action" value="<?= Form::ACTION ?>">
            <input type="hidden" name="form_type" value="<?= Form::DEMO ?>">
            <?php wp_nonce_field('nonce', Form::NONCE) ?>
            <input type="submit" name="submit" id="submit" class="virgil-phe-global-button virgil-phe-global-submit"
                   value="Switch Demo Mode Off">
        </form>
</div>