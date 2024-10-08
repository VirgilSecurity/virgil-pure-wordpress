<?php

/**
 * Plugin Name:       Virgil Pure
 * Plugin URI:        https://github.com/VirgilSecurity/virgil-pure-wordpress
 * Description:       Free tool that protects user passwords from data breaches and both online and offline attacks, and renders stolen passwords useless even if your database has been compromised.
 *                    The Pure based on <a href="https://virgilsecurity.com/announcing-purekit" target="_blank">
 *                    powerful and revolutionary cryptographic technology
 *                    </a> that provides stronger and more modern security and can be used within any database
 *                    or login system that uses a password, so it's accessible for business of any industry or size.
 * Version:           0.4.0
 * Author:            Virgil Security
 * Author URI:        http://virgilsecurity.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       virgil-pure
 * Domain Path:       /languages
 */

use Dotenv\Dotenv;

require plugin_dir_path(__FILE__) . 'admin/core/vendor/autoload.php';

if (!defined('VIRGIL_PURE_CORE')) {
    define('VIRGIL_PURE_CORE', __DIR__ . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'core');
}

if (!defined('VIRGIL_PURE_CORE_ENV_FILE')) {
    define('VIRGIL_PURE_CORE_ENV_FILE', VIRGIL_PURE_CORE . DIRECTORY_SEPARATOR . '.env');
}

if (!is_file(VIRGIL_PURE_CORE_ENV_FILE)) {
    copy(VIRGIL_PURE_CORE_ENV_FILE . "-example", VIRGIL_PURE_CORE_ENV_FILE);
}

(Dotenv::createImmutable(VIRGIL_PURE_CORE))->load();

if (!defined('WPINC')) {
    die;
}

const VIRGIL_PURE_VERSION = '0..0';

/**
 * @brief Hook that is triggered when plugin is activated
 */
function activate_Virgil_Pure(): void
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-virgil-pure-activator.php';
    Virgil_Pure_Activator::activate();
}
register_activation_hook(__FILE__, 'activate_Virgil_Pure');

/**
 * @brief Hook that is triggered when plugin is deactivated
 */
function deactivate_Virgil_Pure(): void
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-virgil-pure-deactivator.php';
    Virgil_Pure_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'deactivate_Virgil_Pure');

/**
 * @brief Hook that is triggered when plugin is uninstalled
 */
function uninstall_Virgil_Pure(): void
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-virgil-pure-uninstaller.php';
    Virgil_Pure_Uninstaller::uninstall();
}
register_uninstall_hook(__FILE__, 'uninstall_Virgil_Pure');


/**
 * @brief Load main plugin class
 */
require plugin_dir_path(__FILE__) . 'includes/class-virgil-pure.php';

/**
 * @brief Run plugin
 */
function run_Virgil_Pure(): void
{
    $plugin = new Virgil_Pure();
    $plugin->run();
}

run_Virgil_Pure();
