<?php

use VirgilSecurityPure\Config\Form;
use VirgilSecurityPure\Config\Credential;

?>

<div class="virgil-phe-global-section">
    <h3 class="virgil-phe-global-section-title">Credentials</h3>
    <hr class="virgil-phe-global-line"/>

    <p class="virgil-phe-global-info-p">
        You'll have to briefly visit the Virgil Security site to get your application credentials, and come back here to paste them in the
        corresponding fields below.
    </p>

    <ol type="1" class="virgil-phe-global-credentials-ol">
        <li>Navigate to <a href="https://dashboard.virgilsecurity.com" target="_blank">dashboard.virgilsecurity.com</a>, sign up for a free account, and create a new Pure application.</li>
        <li>You can generate all three credentials either in the browser or via CLI.</li>
        <li>Copy and paste them into the corresponding fields below. Be sure to save the <?= Credential::APP_SECRET_KEY ?> in a safe place.</li>
    </ol>

    <form class="virgil-phe-credentials-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
          method="post">
        <div class="virgil-phe-global-field">
            <label class="virgil-phe-global-field-label" for="virgil-id-app-token">
                <?= Credential::APP_TOKEN ?>
            </label>
            <input id="virgil-id-app-token" class="virgil-phe-global-field-input" name="<?= Credential::APP_TOKEN ?>" required>
        </div>
        <div class="virgil-phe-global-field">
            <label class="virgil-phe-global-field-label" for="virgil-id-service-pk">
                <?= Credential::SERVICE_PUBLIC_KEY ?>
            </label>
            <input id="virgil-id-service-pk" class="virgil-phe-global-field-input" name="<?= Credential::SERVICE_PUBLIC_KEY ?>" required>
        </div>
        <div class="virgil-phe-global-field">
            <label class="virgil-phe-global-field-label" for="virgil-id-app-secret-key">
                <?= Credential::APP_SECRET_KEY ?>
            </label>
            <input id="virgil-id-app-secret-key" class="virgil-phe-global-field-input" name="<?= Credential::APP_SECRET_KEY ?>" required>
        </div>

        <input type="hidden" name="action" value="<?= Form::ACTION ?>">
        <input type="hidden" name="form_type" value="<?= Form::CREDENTIALS ?>">
        <?php wp_nonce_field('nonce', Form::NONCE) ?>

        <input type="submit" name="submit" id="submit" class="virgil-phe-global-button virgil-phe-global-submit"
               value="SAVE CREDENTIALS">
    </form>
</div>