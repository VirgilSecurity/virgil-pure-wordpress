<?php

use VirgilSecurityPure\Config\Option;
use VirgilSecurityPure\Helpers\DBQueryHelper;

/**
 * Class Virgil_Pure_Uninstaller
 */
class Virgil_Pure_Uninstaller {

    /**
     *
     */
	public static function uninstall() {
        $dbQuery = new DBQueryHelper();
        $dbQuery->dropTableLog();

        delete_option(Option::ACTIVATION_DATE);
        delete_option(Option::DEV_MODE);
	}
}
