<?php

use VirgilSecurityPure\Config\Option;
use VirgilSecurityPure\Helpers\DBQueryHelper;

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

        $users = get_users(array('fields' => array('ID')));

        foreach ($users as $user) {
            delete_user_meta($user->ID, Option::RECORD);
            delete_user_meta($user->ID, Option::PARAMS);
        }

        delete_option(Option::DEV_MODE);
	}
}
