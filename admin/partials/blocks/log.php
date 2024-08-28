<?php

use VirgilSecurityPure\Core\LogPagination;

$lp = new LogPagination();
?>
<?php
if (get_option('virgil_error_redirect')) { ?>
    <div id="virgil-status-notice" class="notice virgil-pure-failed-content">
        <p><?php
            echo esc_html(get_option('virgil_error_redirect')); ?></p>
    </div>
    <?php
    delete_option('virgil_error_redirect');
} ?>
<div class="virgil-pure-global-section">
    <h3 class="virgil-pure-global-page-title">Log</h3>

    <table class="virgil-pure-log-table virgil-pure-log-margin-bottom">
        <tr class="virgil-pure-log-table-head">
            <th class="virgil-pure-log-table-date">date</th>
            <th class="virgil-pure-log-table-desc">description</th>
        </tr>

        <?php foreach ($lp->getData() as $log) { ?>
            <?php
            $trClass = 0 == $log->status ? 'virgil-pure-log-table-tr-error' : null;
            ?>

            <tr class="<?= $trClass ?>">
                <td class="virgil-pure-log-table-date"><?= $log->date ?></td>
                <td class="virgil-pure-log-table-desc"><?= $log->description ?></td>
            </tr>
        <?php } ?>
    </table>

    <?php if ($lp->getPag()) {?>
        <div class="virgil-pure-log-paginator">
            <div class="virgil-pure-log-paginator-page">page</div>
            <?= $lp->getPag() ?>
        </div>
    <?php } ?>
</div>