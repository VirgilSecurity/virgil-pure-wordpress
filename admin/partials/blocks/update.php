<?php
use VirgilSecurityPure\Config\Form;
use VirgilSecurityPure\Config\Credential;
?>

<div class="virgil-phe-global-section">
    <h3 class="virgil-phe-global-section-title">Update Records</h3>
    <hr class="virgil-phe-global-line"/>
    <p class="virgil-phe-rotate-desc">This function allows you to use a special update_token to update all of the
        enrollment records in your database. This action doesn't require changing users’ passwords or modifying the
        scheme of the existing table.</p>

    <p class="virgil-phe-rotate-desc">Navigate to your Pure application in the <a href="https://dashboard.virgilsecurity.com" target="_blank">Virgil Security dashboard</a>, get your
        update token and insert it into the field below. <a href="https://developer.virgilsecurity.com/docs/use-cases/v1/passwords-and-data-protection#update-user-record" target="_blank">Learn more about records
            rotation here</a></p>

    <form class="virgil-phe-credentials-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <div class="virgil-phe-global-field">
            <label class="virgil-phe-global-field-label" for="virgil-update-token">
                <?= Credential::UPDATE_TOKEN ?>
            </label>
            <input id="virgil-update-token" class="virgil-phe-global-field-input" name="<?= Credential::UPDATE_TOKEN ?>" required>
        </div>

        <input type="hidden" name="action" value="<?= Form::ACTION ?>">
        <input type="hidden" name="form_type" value="<?= Form::UPDATE ?>">
        <?php wp_nonce_field('nonce', Form::NONCE) ?>
        <input type="submit" name="submit" id="submit" class="virgil-phe-global-button virgil-phe-global-submit"
               value="UPDATE RECORDS">
    </form>
</div>