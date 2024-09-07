<?php

use VirgilSecurityPure\Config\Option;
use VirgilSecurityPure\Helpers\DBQueryHelper;

/**
 * Class Virgil_Pure_Deactivator
 */
class Virgil_Pure_Deactivator
{

    /**
     * @brief Drop required DB tables
     */
    public static function deactivate(): void
    {
        $dbQuery = new DBQueryHelper();
        $dbQuery->dropTableLog();

        $users = get_users(array('fields' => array('ID')));

        foreach ($users as $user) {
            delete_user_meta($user->ID, Option::USER_RECORD);
        }

        delete_option(Option::DEV_MODE);
    }
}
