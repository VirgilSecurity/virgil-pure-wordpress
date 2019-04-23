<?php
use VirgilSecurityPure\Config\Form;
use VirgilSecurityPure\Helpers\ConfigHelper;
use VirgilSecurityPure\Helpers\InfoHelper;
use VirgilSecurityPure\Helpers\StatusHelper;
?>

<?php if(StatusHelper::isAllUsersMigrated()) { ?>
    <div class="virgil-phe-demo-container">

        <div class="virgil-phe-demo-content">
            <h2 class="virgil-phe-demo-title">Recovery Private Key</h2>
            <p class="virgil-phe-demo-desc">
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Deserunt ducimus eaque ex in itaque laboriosam numquam perferendis quia repudiandae sapiente. Aperiam autem culpa, dicta incidunt numquam omnis praesentium sint veritatis.
            </p>

            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="<?= Form::ACTION ?>">
                <input type="hidden" name="form_type" value="<?= Form::DOWNLOAD_RECOVERY_PRIVATE_KEY ?>">
                <?php wp_nonce_field('nonce', Form::NONCE) ?>
                <input type="submit" name="submit" id="submit" class="virgil-phe-global-button virgil-phe-demo-button"
                       value="Download" onClick="document.location.reload(true)">
            </form>
        </div>
    </div>
<?php } ?>

<!--<div class="virgil-phe-demo-container">-->
<!--    <div class="virgil-phe-demo-content">-->
<!--        <h2 class="virgil-phe-demo-title">Demo mode is on</h2>-->
<!--        <p class="virgil-phe-demo-desc">-->
<!--            In demo mode, no data in your database will be altered. To demonstrate how Virgil Pure works, a new-->
<!--            column will be created to hold the newly protected password data. When you're ready to go live, your-->
<!--            password hashes will be translated into cryptographically protected data.-->
<!--        </p>-->
<!---->
<!--        --><?php //if(!StatusHelper::isAllUsersMigrated()) { ?>
<!--            <p class="virgil-phe-demo-desc">To switch demo mod off migrate all users first.</p>-->
<!--        --><?php //} else { ?>
<!--            <form action="--><?php //echo esc_url(admin_url('admin-post.php')); ?><!--" method="post">-->
<!--                <input type="hidden" name="action" value="--><?//= Form::ACTION ?><!--">-->
<!--                <input type="hidden" name="form_type" value="--><?//= Form::DEMO ?><!--">-->
<!--                --><?php //wp_nonce_field('nonce', Form::NONCE) ?>
<!--                <input type="submit" name="submit" id="submit" class="virgil-phe-global-button virgil-phe-demo-button"-->
<!--                       value="Switch demo mode off">-->
<!--            </form>-->
<!--        --><?php //} ?>
<!--    </div>-->
<!--</div>-->