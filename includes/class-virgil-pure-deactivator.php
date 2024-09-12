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

        delete_option(Option::DEV_MODE);
    }
}
