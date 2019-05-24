<?php
use VirgilSecurityPure\Config\Form;
use VirgilSecurityPure\Config\Credential;
?>

<div class="virgil-pure-global-section">
    <h3 class="virgil-pure-global-section-title">Credentials</h3>
    <hr class="virgil-pure-global-line"/>

    <p class="virgil-pure-global-info-p">
        You'll have to briefly visit the Virgil Security site to get your application credentials, and come back here to paste them in the
        corresponding fields below.
    </p>

    <ol type="1" class="virgil-pure-global-credentials-ol">
        <li>Navigate to <a href="https://dashboard.virgilsecurity.com" target="_blank">dashboard.virgilsecurity.com</a>, sign up for a free account, and create a new Pure application.</li>
        <li>You can generate all three credentials either in the browser or via CLI.</li>
        <li>Copy and paste them into the corresponding fields below. Be sure to save the <?= Credential::APP_SECRET_KEY ?> in a safe place.</li>
    </ol>

    <form autocomplete="off" class="virgil-pure-credentials-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
          method="post">
        <div class="virgil-pure-global-field">
            <label class="virgil-pure-global-field-label" for="virgil-id-app-secret-key">
                <?= Credential::APP_SECRET_KEY ?>
            </label>
            <input autocomplete="off" id="virgil-id-app-secret-key" class="virgil-pure-global-field-input" name="<?=
            Credential::APP_SECRET_KEY ?>" required>
        </div>
        <div class="virgil-pure-global-field">
            <label class="virgil-pure-global-field-label" for="virgil-id-app-token">
                <?= Credential::APP_TOKEN ?>
            </label>
            <input autocomplete="off" id="virgil-id-app-token" class="virgil-pure-global-field-input" name="<?=
            Credential::APP_TOKEN ?>" required>
        </div>
        <div class="virgil-pure-global-field">
            <label class="virgil-pure-global-field-label" for="virgil-id-service-pk">
                <?= Credential::SERVICE_PUBLIC_KEY ?>
            </label>
            <input autocomplete="off" id="virgil-id-service-pk" class="virgil-pure-global-field-input" name="<?=
            Credential::SERVICE_PUBLIC_KEY ?>" required>
        </div>

        <input type="hidden" name="action" value="<?= Form::ACTION ?>">
        <input type="hidden" name="form_type" value="<?= Form::CREDENTIALS ?>">
        <?php wp_nonce_field('nonce', Form::NONCE) ?>

        <input type="submit" name="submit" id="submit" class="virgil-pure-global-button virgil-pure-global-submit"
               value="SAVE CREDENTIALS">
    </form>
</div>