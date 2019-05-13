<?php
use VirgilSecurityPure\Config\Form;
use VirgilSecurityPure\Config\Config;
?>

<div class="virgil-pure-global-section">
    <h3 class="virgil-pure-global-page-title">Change Mode</h3>

    <p class="virgil-pure-rotate-desc">
        If you’ve made it to this page, that means you’re almost ready to go live with the Pure plugin in your database.
    </p>
     <div class="virgil-pure-demo-container">

         <div class="virgil-pure-demo-content">

             <h2 class="virgil-pure-demo-title">Recovery Private Key</h2>

             <p class="virgil-pure-demo-desc">
             Before you switch off Demo Mode, you’ll need to generate a recovery key so that the password hashes that are currently in your database can be recovered if you ever need to deactivate the Pure plugin. Your recovery key will encrypt the password hashes, and will store the encrypted values in a new table in your database.
             <br><br>

             The recovery key utilizes a public and private key pair. The public key will be stored in your database
                 and the private key must be stored by you securely on another external device. <a class="virgil-pure-demo-desc" href="<?= admin_url
                 ("/admin.php?page=" . Config::FAQ_PAGE) ?>">Please read our FAQ section</a> for best practices and more
                 information.
             <br><br>
             When you need to deactivate the Pure plugin, you can go through the Recovery process via the Wordpress
             dashboard or CLI and use the recovery key to restore the original password hashes.

         </p>
                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                    <input type="hidden" name="action" value="<?= Form::ACTION ?>">
                    <input type="hidden" name="form_type" value="<?= Form::DOWNLOAD_RECOVERY_PRIVATE_KEY ?>">
                    <?php wp_nonce_field('nonce', Form::NONCE) ?>
                    <input type="submit" name="submit" id="submit" class="virgil-pure-global-button virgil-pure-demo-button"
                           value="Generate and Download">
                </form>
            </div>
        </div>

    <p class="virgil-pure-rotate-desc">
        As soon as you press the “Switch Off Demo Mode” button, the hashes of your user passwords will be transformed into cryptographically protected data, and no one will be able to breach your database’s user passwords.
    </p>

        <form class="virgil-pure-credentials-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <input type="hidden" name="action" value="<?= Form::ACTION ?>">
            <input type="hidden" name="form_type" value="<?= Form::DEMO ?>">
            <?php wp_nonce_field('nonce', Form::NONCE) ?>
            <input type="submit" name="submit" id="submit" class="virgil-pure-global-button virgil-pure-global-submit"
                   value="Switch Off Demo Mode">
        </form>
</div>