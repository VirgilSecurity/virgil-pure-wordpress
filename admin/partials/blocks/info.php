<?php

use VirgilSecurityPure\Helpers\ConfigHelper;
use VirgilSecurityPure\Helpers\InfoHelper;
use VirgilSecurityPure\Config\Config;

$systemInfoArr = [
    'EXTENSION NAME' => Config::EXTENSION_NAME,
    'OS' => PHP_OS,
    'PHP' => PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION,
    'PATH TO EXTENSION DIR' => PHP_EXTENSION_DIR,
    'PATH TO PHP.INI' => php_ini_loaded_file(),
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