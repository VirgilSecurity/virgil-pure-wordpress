<?php
use VirgilSecurityPure\Helpers\InfoHelper;
use VirgilSecurityPure\Config\Config;

$systemInfoArr = [
    'EXTENSION VSCE_PHE_PHP' => Config::EXTENSION_VSCE_PHE_PHP,
    'EXTENSION_VSCF_FOUNDATION_PHP' => Config::EXTENSION_VSCF_FOUNDATION_PHP,
    'EXTENSION_VSCP_PYTHIA_PHP' => Config::EXTENSION_VSCP_PYTHIA_PHP,
    'OS' => PHP_OS,
    'PHP' => PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION,
    'PATH TO EXTENSION DIR' => PHP_EXTENSION_DIR,
    'PATH TO PHP.INI' => php_ini_loaded_file(),
];

$pluginInfoArr = [
    'MIGRATED USERS' => InfoHelper::getMigrated() . "/" . InfoHelper::getTotalUsers(),
    'CREDENTIALS' => InfoHelper::getEnvFilePath(),
];

$infoArr = !Config::isAllExtensionEnabled() ?
    $systemInfoArr :
    $pluginInfoArr;

?>

<div class="virgil-pure-global-section">
    <h3 class="virgil-pure-global-section-title">Info</h3>
    <hr class="virgil-pure-global-line"/>
    <table class="virgil-pure-info-table">
        <?php foreach ($infoArr as $key => $value) { ?>
            <tr>
                <td class="virgil-pure-info-table-key"><?= $key ?></td>
                <td class="virgil-pure-info-table-value"><?= $value ?></td>
            </tr>
        <?php } ?>
    </table>
</div>