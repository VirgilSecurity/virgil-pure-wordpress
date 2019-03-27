<?php

use Plugin\Pure\Core\LogPagination;

$lp = new LogPagination();
?>

<div class="virgil-phe-global-section">
    <h3 class="virgil-phe-global-page-title">Log</h3>

    <table class="virgil-phe-log-table virgil-phe-log-margin-bottom">
        <tr class="virgil-phe-log-table-head">
            <th class="virgil-phe-log-table-date">date</th>
            <th class="virgil-phe-log-table-desc">description</th>
        </tr>

        <?php foreach ($lp->getData() as $log) { ?>

            <?php
            $trClass = 0 == $log->status ? 'virgil-phe-log-table-tr-error' : null;
            ?>

            <tr class="<?= $trClass ?>">
                <td class="virgil-phe-log-table-date"><?= $log->date ?></td>
                <td class="virgil-phe-log-table-desc"><?= $log->description ?></td>
            </tr>
        <?php } ?>
    </table>

    <?php if($lp->getPag()) {?>
        <div class="virgil-phe-log-paginator">
            <div class="virgil-phe-log-paginator-page">page</div>
            <?= $lp->getPag() ?>
        </div>
    <?php } ?>
</div>