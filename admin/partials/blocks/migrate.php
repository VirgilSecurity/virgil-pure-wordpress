<?php

use Plugin\Pure\Helpers\InfoHelper;
use Plugin\Pure\Config\Form;
use Plugin\Pure\Background\MigrateBackgroundProcess;

$total = InfoHelper::getTotalUsers();
$migrated = InfoHelper::getMigrated();
$migratedPercents = InfoHelper::getMigratedPercents();

$mbp = new MigrateBackgroundProcess();

$value = $mbp->is_process_running() ? "Migration In Progress" : "Start Migration";
$disabled = $mbp->is_process_running() ? "disabled" : null;
$message = $mbp->is_process_running() ? "- $migratedPercents% complete.<br>Please reload the page in a few minutes." :
    null;
?>

<div class="virgil-phe-global-section">
    <h3 class="virgil-phe-global-section-title">Migration</h3>
    <hr class="virgil-phe-global-line"/>

    <div class="virgil-phe-migration-container">

        <p class="virgil-phe-migration-users">
            Migration is a phase during which the plugin requests cryptographic data from Virgil server to associate users' passwords or their hash (or whatever you use) with cryptographic enrollment, provided by the server. Then enrollment records are created and stored in your database instead of users’ passwords.</p>

        <p class="virgil-phe-migration-users">Note: if you use the demo mode the plugin doesn’t replace users’ passwords with the associated records, it creates an additional column and stores records there.</p>

        <p class="virgil-phe-migration-users">
            <?= $migrated ?> out of <?= $total ?> users migrated
            <?= $message ?>
        </p>

        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post"
              id="passw0rd_form_migration">
            <input type="hidden" name="action" value="<?= Form::ACTION ?>">
            <input type="hidden" name="form_type" value="<?= Form::MIGRATE ?>">
            <?php wp_nonce_field('nonce', Form::NONCE) ?>
            <input  type="submit" name="submit" id="submit" class="virgil-phe-global-button
            virgil-phe-global-submit" value="<?= $value ?>" <?= $disabled ?>>
        </form>
    </div>
</div>