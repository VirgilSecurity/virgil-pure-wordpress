<?php

use Plugin\Pure\Config\Log;
use Plugin\Pure\Config\Option;
use Plugin\Pure\Core\Logger;
use Plugin\Pure\Helpers\DBQueryHelper;

/**
 * Class Virgil_Pure_Activator
 */
class Virgil_Pure_Activator {

    /**
     *
     */
	public static function activate() {
        $dbQuery = new DBQueryHelper();
        $dbQuery->createTableLog();

        update_option(Option::DEMO_MODE, 1);
        update_option(Option::ACTIVATION_DATE, current_time('mysql'));
        update_option(Option::DEV_MODE, 1);
        update_option(Option::CHECKED_CREDENTIALS, 0);

        Logger::log(Log::PLUGIN_ACTIVATION);
	}
}
