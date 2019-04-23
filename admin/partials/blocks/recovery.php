<?php
use VirgilSecurityPure\Config\Form;
use VirgilSecurityPure\Config\Credential;
?>

<div class="virgil-phe-global-section">
    <h3 class="virgil-phe-global-section-title">Recovery</h3>
    <hr class="virgil-phe-global-line"/>
    <p class="virgil-phe-rotate-desc">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusamus aliquam aperiam asperiores beatae culpa, deleniti eligendi expedita impedit incidunt magnam maiores nam necessitatibus praesentium quam rerum similique, suscipit unde. Voluptatibus?</p>

    <form class="virgil-phe-credentials-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <div class="virgil-phe-global-field">
            <label class="virgil-phe-global-field-label" for="virgil-update-token">
                Recovery Private Key
            </label>
            <input id="virgil-update-token" class="virgil-phe-global-field-input" name="Recovery_Private_Key" required>
        </div>

        <input type="hidden" name="action" value="<?= Form::ACTION ?>">
        <input type="hidden" name="form_type" value="<?= Form::UPDATE ?>">
        <?php wp_nonce_field('nonce', Form::NONCE) ?>
        <input type="submit" name="submit" id="submit" class="virgil-phe-global-button virgil-phe-global-submit"
               value="Restore">
    </form>
</div>

<?php
try {
    $vcw = new \VirgilSecurityPure\Core\VirgilCryptoWrapper();
    $pk = $vcw->getKey(\VirgilSecurityPure\Config\Crypto::PUBLIC_KEY);
    $prk = $vcw->getKey(\VirgilSecurityPure\Config\Crypto::PRIVATE_KEY);



    var_dump($pk, $prk);
    die;
}
catch (\Exception $e) {
    wp_die($e->getMessage());
}
?>