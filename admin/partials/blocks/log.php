<?php
use Plugin\Pure\Helpers\DBQueryHelper;
$dbQuery = new DBQueryHelper();
?>

<div class="virgil-phe-global-section">
    <h3 class="virgil-phe-global-page-title">Log</h3>

    <table class="virgil-phe-log-table virgil-phe-log-margin-bottom">
        <tr class="virgil-phe-log-table-head">
            <th class="virgil-phe-log-table-date">date</th>
            <th class="virgil-phe-log-table-desc">description</th>
        </tr>

        <?php foreach ($dbQuery->getAllLogs() as $log) { ?>

            <?php
            $trClass = 0==$log->status ? 'virgil-phe-log-table-tr-error' : null;
            ?>

            <tr class="<?= $trClass ?>">
                <td class="virgil-phe-log-table-date"><?= $log->date ?></td>
                <td class="virgil-phe-log-table-desc"><?= $log->description ?></td>
            </tr>
        <?php } ?>
    </table>
    <!--    <div class="virgil-phe-log-paginator">-->
    <!--        <div class="virgil-phe-log-paginator-page">page</div>-->
    <!--        <button class="virgil-phe-log-paginator-number">1</button>-->
    <!--        <button class="virgil-phe-log-paginator-number virgil-phe-log-paginator-active">2</button>-->
    <!--        <button class="virgil-phe-log-paginator-number">3</button>-->
    <!--        <button class="virgil-phe-log-paginator-number">...</button>-->
    <!--        <button class="virgil-phe-log-paginator-number">99</button>-->
    <!--        <button class="virgil-phe-log-paginator-number">666</button>-->
    <!--    </div>-->
</div>