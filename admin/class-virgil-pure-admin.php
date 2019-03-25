<?php

use Plugin\Pure\Background\MigrateBackgroundProcess;
use Plugin\Pure\Background\UpdateBackgroundProcess;
use Plugin\Pure\Config\Config;
use Plugin\Pure\Config\Form;
use Plugin\Pure\Config\Option;
use Plugin\Pure\Core\CoreProtocol;
use Plugin\Pure\Core\FormHandler;
use Plugin\Pure\Core\passw0rdHash;
use Plugin\Pure\Core\WPPasswordEnroller;
use Plugin\Pure\Helpers\DBQueryHelper;
use Plugin\Pure\Helpers\Redirector;
use Plugin\Pure\Helpers\StatusHelper;

/**
 * Class Virgil_Pure_Admin
 */
class Virgil_Pure_Admin
{
    /**
     * @var string
     */
    private $Virgil_Pure;

    /**
     * @var string
     */
    private $version;

    /**
     * @var CoreProtocol
     */
    private $protocol;

    /**
     * @var FormHandler
     */
    private $fh;

    /**
     * @var DBQueryHelper
     */
    private $dbqh;

    /**
     * @var passw0rdHash
     */
    private $ph;

    /**
     * Virgil_Pure_Admin constructor.
     * @param $Virgil_Pure
     * @param $version
     */
    public function __construct($Virgil_Pure, $version)
    {
        $this->protocol = new CoreProtocol();
        $this->dbqh = new DBQueryHelper();
        $this->fh = new FormHandler($this->protocol);
        $this->ph = new passw0rdHash();

        $this->Virgil_Pure = $Virgil_Pure;
        $this->version = $version;
    }

    /**
     *
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->Virgil_Pure, plugin_dir_url(__FILE__) . 'css/virgil-pure-admin.css', array(), $this->version, 'all');
    }

    public function virgil_pure_menu()
    {
        $devMode = get_option(Option::DEV_MODE);
        $extLoaded = extension_loaded(Config::EXTENSION_NAME);
        $title = $extLoaded ? "Action" : "Info";

        add_menu_page(Config::MAIN_PAGE_TITLE, Config::MAIN_PAGE_TITLE, Config::CAPABILITY, Config::MAIN_PAGE);
        add_submenu_page(Config::MAIN_PAGE, $title, $title, Config::CAPABILITY, Config::MAIN_PAGE, array($this, 'virgil_pure_page_builder'));

        if ($extLoaded) {
            add_submenu_page(Config::MAIN_PAGE, 'Log', 'Log', Config::CAPABILITY, Config::LOG_PAGE, array($this, 'virgil_pure_page_builder'));
        }

        add_submenu_page(Config::MAIN_PAGE, 'FAQ', 'FAQ', Config::CAPABILITY, Config::FAQ_PAGE, array($this, 'virgil_pure_page_builder'));

        if ($extLoaded && $devMode) {
            add_submenu_page(Config::MAIN_PAGE, 'Dev', '* Dev', Config::CAPABILITY, Config::DEV_PAGE, array($this, 'virgil_pure_page_dev'));
        }
    }

    /**
     *
     */
    function virgil_pure_form_handler()
    {
        if (in_array($_POST[Form::TYPE], Form::ALL)) {

            if (check_admin_referer('nonce', Form::NONCE)) {

                switch ($_POST[Form::TYPE]) {
                    case Form::DEMO:
                        $this->fh->demo();
                        break;

                    case Form::CREDENTIALS:
                        $this->fh->credentials();
                        break;

                    case Form::MIGRATE:
                        $this->fh->migrate();
                        break;

                    case Form::UPDATE:
                        $this->fh->update();
                        break;

                    //dev
                    case Form::DEV_ADD_USERS:
                        $this->fh->addUsers();
                        break;

                    case Form::DEV_RESTORE_DEFAULTS:
                        $this->fh->restoreDefaults();
                        break;
                }

                Redirector::toPageLog();

            } else {
                wp_die($_POST[Form::TYPE] . ' form response error');
            }
        } else {
            wp_die('Invalid form type ' . $_POST[Form::TYPE]);
        }
    }

    /**
     * @param $check
     * @param $password
     * @param $hash
     * @param $user_id
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function virgil_pure_check_password($check, $password, $hash, $user_id): bool
    {
        if ($user_id) {
            if (StatusHelper::isAllUsersMigrated()) {
                $passw0rdHash = new passw0rdHash();

                $salt = get_user_meta($user_id, Option::PARAMS)[0];

                $hash = $passw0rdHash->hashPassword($password, $salt);

                $inputHash = $passw0rdHash->get($hash, 'hash');

                $userPass = get_user_meta($user_id, Option::RECORD)[0];
                $userPass = base64_decode($userPass);

                try {
                    $p = $this->protocol->init();
                    $p->verifyPassword($inputHash, $userPass);
                    $check = true;

                } catch (\Exception $e) {
                    $check = false;
                }
                return $check;
            }
        }

        return $check;
    }

    /**
     *
     */
    private function checkPermissions()
    {
        if (!current_user_can(Config::CAPABILITY)) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
    }

    /**
     *
     */
    public function virgil_pure_page_builder()
    {
        $this->checkPermissions();
        require_once plugin_dir_path(__FILE__) . 'partials/virgil-pure-admin-display.php';
    }

    /**
     *
     */
    public function virgil_pure_page_dev()
    {
        $this->checkPermissions();
        require_once plugin_dir_path(__FILE__) . 'partials/_dev.php';
    }

    /**
     *
     */
    public function virgil_pure_init_background_processes()
    {
        if($this->protocol) {
            new MigrateBackgroundProcess($this->protocol);
            new UpdateBackgroundProcess($this->protocol);
        }
    }

    /**
     * @param $user_id
     * @param $old_user_data
     */
    public function virgil_pure_profile_update($user_id, $old_user_data)
    {
        var_dump($user_id, $old_user_data);
        die;
    }

    /**
     * @param $user
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Virgil\PureKit\Exceptions\ProtocolException
     */
    public function virgil_pure_password_reset($user)
    {
        $user = get_user_by('id', $user->ID);

        $wppe = new WPPasswordEnroller($this->protocol, $this->ph);
        $wppe->enroll($user);

        $this->dbqh->clearUserPass($user->ID);
    }

}
