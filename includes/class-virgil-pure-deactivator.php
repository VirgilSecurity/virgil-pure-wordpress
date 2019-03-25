<?php

use Plugin\Pure\Config\Option;
use Plugin\Pure\Helpers\DBQueryHelper;

/**
 * Class Virgil_Pure_Deactivator
 */
class Virgil_Pure_Deactivator {

    /**
     *
     */
	public static function deactivate() {
        $dbQuery = new DBQueryHelper();
        $dbQuery->dropTableLog();

        delete_option(Option::DEMO_MODE);
        delete_option(Option::ACTIVATION_DATE);
        delete_option(Option::DEV_MODE);
	}

}
