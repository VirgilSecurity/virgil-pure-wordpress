<?php
use VirgilSecurityPure\Config\Form;
?>

<div class="virgil-pure-global-container">

    <?php require_once 'blocks/_header.php'; ?>

    <h3 class="virgil-pure-global-section-title">Dev</h3>
    <hr class="virgil-pure-global-line"/>

    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">

        <select name="number_of_users">

            <?php $amountArr = [10, 20, 50, 100, 200, 1000];
            foreach ($amountArr as $value) {
                ?>
                <option value="<?= $value ?>"><?= $value ?></option>
            <?php } ?>
        </select>

        <input type="hidden" name="action" value="<?= Form::ACTION ?>">
        <input type="hidden" name="form_type" value="<?= Form::DEV_ADD_USERS ?>">
        <?php wp_nonce_field('nonce', Form::NONCE) ?>
        <br>
        <?php echo get_submit_button("Add users", "Submit", "submit", false); ?>
    </form>

    <hr>

    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <input type="hidden" name="action" value="<?= Form::ACTION ?>">
        <input type="hidden" name="form_type" value="<?= Form::DEV_RESTORE_DEFAULTS ?>">
        <?php wp_nonce_field('nonce', Form::NONCE) ?>
        <?php echo get_submit_button("Restore defaults", "Submit", "submit", false); ?>
    </form>
</div>