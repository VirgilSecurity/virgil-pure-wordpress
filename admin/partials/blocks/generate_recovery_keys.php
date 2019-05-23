<?php
use VirgilSecurityPure\Config\Form;
use VirgilSecurityPure\Config\Config;
?>

<div class="virgil-pure-global-section">
    <h3 class="virgil-pure-global-page-title">Generate Recovery Keys</h3>
     <div class="virgil-pure-demo-container">

         <div class="virgil-pure-demo-content">

             <h2 class="virgil-pure-demo-title">Recovery Private Key</h2>

             <p class="virgil-pure-demo-desc">
             You’ll need to generate a recovery key so that the password hashes that are currently in your database
                 can be recovered if you ever need to deactivate the Pure plugin. Your recovery key will encrypt the
                 password hashes, and will store the encrypted values in a (wp_usermeta) table in your database.
             <br><br>

             The recovery key utilizes a public and private key pair. The public key will be stored in your database
                 and the private key must be stored by you securely on another external device. <a class="virgil-pure-demo-desc" href="<?= admin_url
                 ("/admin.php?page=" . Config::FAQ_PAGE) ?>">Please read our FAQ section</a> for best practices and more
                 information.
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

        <form class="virgil-pure-credentials-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">

            <label for="phe-agree-checkbox">
                <input id="phe-agree-checkbox" type="checkbox" onchange="document
                .getElementById('submitNextStep').disabled = !this.checked;">
                <span>I'm aware that if I lose the Recovery Private Key, I will not  be able to recover encrypted
                    records</span>
            </label>

            <input type="hidden" name="action" value="<?= Form::ACTION ?>">
            <input type="hidden" name="form_type" value="<?= Form::DEMO ?>">
            <?php wp_nonce_field('nonce', Form::NONCE) ?>
            <br><br>
            <input type="submit" name="submit" id="submitNextStep" class="virgil-pure-global-button
            virgil-pure-global-submit" disabled value="Next Step">
        </form>
</div>