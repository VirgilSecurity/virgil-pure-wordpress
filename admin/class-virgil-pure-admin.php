<?php

use VirgilSecurityPure\Config\Config;
use VirgilSecurityPure\Config\Crypto;
use VirgilSecurityPure\Config\Form;
use VirgilSecurityPure\Config\Option;
use VirgilSecurityPure\Core\CoreFactory;
use VirgilSecurityPure\Helpers\ConfigHelper;
use VirgilSecurityPure\Helpers\Redirector;
use VirgilSecurityPure\Helpers\InfoHelper;

/**
 * Class Virgil_Pure_Admin
 */
class Virgil_Pure_Admin
{
    /**
     * @var CoreFactory 
     */
    private $coreFactory;

    /**
     * @var \VirgilSecurityPure\Core\Core 
     */
    private $virgilCryptoWrapper;

    /**
     * Virgil_Pure_Admin constructor.
     * @param $Virgil_Pure
     * @param $version
     * @throws \Virgil\CryptoImpl\VirgilCryptoException
     */
    public function __construct($Virgil_Pure, $version)
    {
        $this->coreFactory = new CoreFactory();
        $coreProtocol = $this->coreFactory->buildCore('CoreProtocol');
        
        $this->virgilCryptoWrapper = $this->coreFactory->buildCore('VirgilCryptoWrapper');

        $this->protocol = $coreProtocol->init();
        $this->dbqh = $this->coreFactory->buildCore('DBQuery');
        $this->fh = $this->coreFactory->buildCore('FormHandler');
        $this->cm = $this->coreFactory->buildCore('CredentialsManager');
        $this->pv = $this->coreFactory->buildCore('PluginValidator');

        $this->fh->setDep($coreProtocol, $this->virgilCryptoWrapper, $this->cm, $this->dbqh);

        $this->Virgil_Pure = $Virgil_Pure;
        $this->version = $version;
    }

    /**
     *
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->Virgil_Pure, plugin_dir_url(__FILE__) . 'css/virgil-pure-admin.css', array(),
            $this->version, 'all');
    }

    /**
     *
     */
    public function virgil_pure_menu()
    {
        $devMode = get_option(Option::DEV_MODE);
        $extLoaded = extension_loaded(Config::EXTENSION_VSCE_PHE_PHP);
        
        $title = $extLoaded ? "Action" : "Info";

        add_menu_page(Config::MAIN_PAGE_TITLE, Config::MAIN_PAGE_TITLE, Config::CAPABILITY, Config::ACTION_PAGE);
        add_submenu_page(Config::ACTION_PAGE, $title, $title, Config::CAPABILITY, Config::ACTION_PAGE, array($this, 'virgil_pure_page_builder'));
        if ($extLoaded) {
            add_submenu_page(Config::ACTION_PAGE, 'Log', 'Log', Config::CAPABILITY, Config::LOG_PAGE, array($this, 'virgil_pure_page_builder'));
            add_submenu_page(Config::ACTION_PAGE, 'FAQ', 'FAQ', Config::CAPABILITY, Config::FAQ_PAGE, array($this, 'virgil_pure_page_builder'));
            if(InfoHelper::isAllUsersMigrated()&&ConfigHelper::isRecoveryKeyExists()&&0!==get_option(Option::DEMO_MODE))
                add_submenu_page(Config::ACTION_PAGE, 'Recovery', 'Recovery', Config::CAPABILITY, Config::RECOVERY_PAGE,
                    array($this, 'virgil_pure_page_builder'));
            if($devMode)
                add_submenu_page(Config::ACTION_PAGE, 'Dev', 'Dev', Config::CAPABILITY, Config::DEV_PAGE, array($this, 'virgil_pure_page_dev'));

        }
    }

    /**
     *
     */
    public function virgil_pure_form_handler()
    {
        if (in_array($_POST[Form::TYPE], Form::ALL)) {

            if (check_admin_referer('nonce', Form::NONCE)) {

                switch ($_POST[Form::TYPE]) {
                    case Form::DEMO:
                        $this->fh->demo();
                        break;

                    case Form::DOWNLOAD_RECOVERY_PRIVATE_KEY:
                        $this->fh->downloadRecoveryPrivateKey();
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

                    case Form::RECOVERY:
                        $this->fh->recovery();
                        break;

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
     * @throws \Virgil\CryptoImpl\VirgilCryptoException
     */
    public function virgil_pure_check_password($check, $password, $hash, $user_id): bool
    {
        if ($this->coreFactory->buildCore('PluginValidator')->check() && $user_id) {
            if (InfoHelper::isAllUsersMigrated()) {
                $passw0rdHash = $this->coreFactory->buildCore('passw0rdHash');

                $salt = get_user_meta($user_id, Option::PARAMS)[0];

                $hash = $passw0rdHash->hashPassword($password, $salt);

                $inputHash = $passw0rdHash->get($hash, 'hash');

                $userPass = get_user_meta($user_id, Option::RECORD)[0];
                $userPass = base64_decode($userPass);

                try {
                    $this->protocol->verifyPassword($inputHash, $userPass);
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
        if ($this->protocol) {
            $migrateBP = $this->coreFactory->buildBackgroundProcess('EncryptAndMigrate');
            $migrateBP->setDep($this->protocol, $this->dbqh, $this->virgilCryptoWrapper);

            $updateBP = $this->coreFactory->buildBackgroundProcess('Update');
            $updateBP->setDep($this->protocol, $this->cm);

            $recoveryBP = $this->coreFactory->buildBackgroundProcess('Recovery');
            $recoveryBP->setDep($this->dbqh, $this->virgilCryptoWrapper, $this->cm);
        }
    }

    /**
     * @param int $user_id
     * @throws \Virgil\CryptoImpl\VirgilCryptoException
     */
    public function virgil_pure_profile_update(int $user_id)
    {
        if ($this->pv->check()&&!empty(get_user_by('id', $user_id)->user_pass)) {
            $this->encrypt($user_id);
            $this->enroll($user_id);
        }
    }

    /**
     * @param WP_User $user
     * @throws \Virgil\CryptoImpl\VirgilCryptoException
     */
    public function virgil_pure_password_reset(WP_User $user)
    {
        if($this->pv->check()) {
            $this->encrypt($user->ID);
            $this->enroll($user->ID);
        }
    }

    /**
     * @param int $userId
     * @throws \Virgil\CryptoImpl\VirgilCryptoException
     */
    private function enroll(int $userId)
    {
        $user = get_user_by('id', $userId);

        $wppe = $this->coreFactory->buildCore('WPPasswordEnroller');
        $wppe->setDep($this->protocol, $this->coreFactory->buildCore('passwordHash'));

        $wppe->enroll($user);

        $this->dbqh->clearUserPass($user->ID);
    }

    /**
     * @param int $userId
     * @return bool
     */
    private function encrypt(int $userId)
    {
        $user = get_user_by('id', $userId);
        $pk = get_option(Option::RECOVERY_PUBLIC_KEY);
        if($pk) {
            $virgilPublicKey = $this->virgilCryptoWrapper->importKey(Crypto::PUBLIC_KEY, $pk);

            $password = $user->user_pass;
            $encrypted = $this->virgilCryptoWrapper->encrypt($password, $virgilPublicKey);

            update_user_meta($user->ID, Option::ENCRYPTED, $encrypted);
            return false;
        }
    }
}
