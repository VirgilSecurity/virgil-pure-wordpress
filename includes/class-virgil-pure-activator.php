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

        if(!get_option(Option::DEMO_MODE))
            update_option(Option::DEMO_MODE, 1);

        if(!get_option(Option::DEV_MODE))
            update_option(Option::DEV_MODE, 0);

        Logger::log(Log::PLUGIN_ACTIVATION);
	}
}
