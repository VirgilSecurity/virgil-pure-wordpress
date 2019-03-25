<?php

use Plugin\Pure\Helpers\ConfigHelper;
use Plugin\Pure\Helpers\InfoHelper;

$systemInfoArr = [
    'SERVER OS' => InfoHelper::getOSVersion(),
    'PHP VERSION' => InfoHelper::getPHPVersion(),
    'PHP EXTENSION' => InfoHelper::getExtensionDir() . DIRECTORY_SEPARATOR .
        InfoHelper::getExtensionName() .InfoHelper::getExtensionExtension(),
];

$pluginInfoArr = [
    'MIGRATED USERS' => InfoHelper::getMigrated() . "/" . InfoHelper::getTotalUsers(),
    'CREDENTIALS' => InfoHelper::getEnvFilePath(),
];

$infoArr = ConfigHelper::isExtensionLoaded() ? $systemInfoArr : $pluginInfoArr;

?>

<div class="virgil-phe-global-section">
    <h3 class="virgil-phe-global-section-title">Info</h3>
    <hr class="virgil-phe-global-line"/>
    <table class="virgil-phe-info-table">
        <?php foreach ($infoArr as $key => $value) { ?>
            <tr>
                <td class="virgil-phe-info-table-key"><?= $key ?></td>
                <td class="virgil-phe-info-table-value"><?= $value ?></td>
            </tr>
        <?php } ?>
    </table>
</div>