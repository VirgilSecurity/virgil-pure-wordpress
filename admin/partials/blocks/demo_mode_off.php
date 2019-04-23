<?php
use VirgilSecurityPure\Config\Form;
use VirgilSecurityPure\Helpers\ConfigHelper;
use VirgilSecurityPure\Helpers\InfoHelper;
use VirgilSecurityPure\Helpers\StatusHelper;
use VirgilSecurityPure\Config\Credential;
?>


<div class="virgil-phe-global-section">
    <h3 class="virgil-phe-global-page-title">Change Mode</h3>
     <div class="virgil-phe-demo-container">
            <div class="virgil-phe-demo-content">
                <h2 class="virgil-phe-demo-title">Recovery Private Key</h2>
                <p class="virgil-phe-demo-desc">
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit. Animi architecto consequuntur dicta earum et facilis inventore iure magnam, maiores molestiae nam nostrum quas quibusdam, quidem rerum soluta voluptate. Culpa, soluta.
                </p>

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
            <div class="virgil-phe-global-field">
                <label class="virgil-phe-global-field-label" for="virgil-update-token">
                    I'm aware that if I lose the Recovery Private Key, I will not be able to recover encrypted records
                    <input type="checkbox" id="virgil-update-token" name="virgil-update-token" class="virgil-phe-global-field-input">
                </label>
            </div>

            <input type="hidden" name="action" value="<?= Form::ACTION ?>">
            <input type="hidden" name="form_type" value="<?= Form::DEMO ?>">
            <?php wp_nonce_field('nonce', Form::NONCE) ?>
            <input type="submit" name="submit" id="submit" class="virgil-phe-global-button virgil-phe-global-submit"
                   value="Switch Demo Mode Off">
        </form>
</div>