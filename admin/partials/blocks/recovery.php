<?php
use VirgilSecurityPure\Config\Form;
use VirgilSecurityPure\Config\Crypto;
?>

<div class="virgil-phe-global-section">
    <h3 class="virgil-phe-global-section-title">Recovery</h3>
    <hr class="virgil-phe-global-line"/>
    <p class="virgil-phe-rotate-desc"></p>

    <form class="virgil-phe-credentials-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <div class="virgil-phe-global-field">
            <label class="virgil-phe-global-field-label" for="virgil-recovery-private-key">
                <?= Crypto::RECOVERY_PRIVATE_KEY ?>
            </label>
            <input id="virgil-recovery-private-key" class="virgil-phe-global-field-input" name="<?= Crypto::RECOVERY_PRIVATE_KEY ?>" required>
        </div>

        <input type="hidden" name="action" value="<?= Form::ACTION ?>">
        <input type="hidden" name="form_type" value="<?= Form::RECOVERY ?>">
        <?php wp_nonce_field('nonce', Form::NONCE) ?>
        <input type="submit" name="submit" id="submit" class="virgil-phe-global-button virgil-phe-global-submit"
               value="Start Recovery">
    </form>
</div>