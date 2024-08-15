<?php

use VirgilSecurityPure\Background\CheckMigrationBackgroundProcess;
use VirgilSecurityPure\Config\Log;
use VirgilSecurityPure\Config\Option;
use VirgilSecurityPure\Core\Logger;
use VirgilSecurityPure\Helpers\DBQueryHelper;

/**
 * Class Virgil_Pure_Activator
 */
class Virgil_Pure_Activator
{

    /**
     * @return void
     */
    public static function activate(): void
    {
        $dbQuery = new DBQueryHelper();
        $dbQuery->createTableLog();

        if (!get_option(Option::DEV_MODE)) {
            update_option(Option::DEV_MODE, 0);
        }

        CheckMigrationBackgroundProcess::setupCronJob();

        wp_clear_scheduled_hook( 'auto_spell_cast' );
        wp_schedule_event( time(), 'wp_virgil-pure_action_encrypt_and_migrate_cron_interval', 'auto_spell_cast');
        add_action('auto_spell_cast', [CheckMigrationBackgroundProcess::class, 'migrateNewUsers']);

        Logger::log(Log::PLUGIN_ACTIVATION);
    }
}
