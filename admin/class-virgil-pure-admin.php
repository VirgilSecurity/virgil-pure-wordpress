<?php

use Virgil\Crypto\Exceptions\VirgilCryptoException;
use Virgil\PureKit\Pure\Exception\PheClientException;
use Virgil\PureKit\Pure\Exception\PureCryptoException;
use VirgilSecurityPure\Config\Config;
use VirgilSecurityPure\Config\Crypto;
use VirgilSecurityPure\Config\Form;
use VirgilSecurityPure\Config\Option;
use VirgilSecurityPure\Core\Core;
use VirgilSecurityPure\Core\CoreFactory;
use VirgilSecurityPure\Core\CoreProtocol;
use VirgilSecurityPure\Core\CredentialsManager;
use VirgilSecurityPure\Core\FormHandler;
use VirgilSecurityPure\Core\Logger;
use VirgilSecurityPure\Core\PluginValidator;
use VirgilSecurityPure\Core\VirgilCryptoWrapper;
use VirgilSecurityPure\Exceptions\PluginPureException;
use VirgilSecurityPure\Helpers\ConfigHelper;
use VirgilSecurityPure\Helpers\DBQueryHelper;
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
    private CoreFactory $coreFactory;

    private Core|VirgilCryptoWrapper $virgilCryptoWrapper;
    private ?CoreProtocol $protocol;
    private Core|DBQueryHelper $dbqh;
    private Core|FormHandler $fh;
    private Core|CredentialsManager $cm;
    private Core|PluginValidator $pv;
    private string $virgilPure;
    private string $version;

    /**
     * Virgil_Pure_Admin constructor.
     * @param string $virgilPure
     * @param $version
     */
    public function __construct(string $virgilPure, $version)
    {
        $this->coreFactory = new CoreFactory();
        /** @var CoreProtocol $coreProtocol */
        $coreProtocol = $this->coreFactory->buildCore('CoreProtocol');

        $this->virgilCryptoWrapper = $this->coreFactory->buildCore('VirgilCryptoWrapper');

        $coreProtocol->init();
        $this->protocol = $coreProtocol;
        $this->dbqh = $this->coreFactory->buildCore('DBQuery');
        $this->fh = $this->coreFactory->buildCore('FormHandler');
        $this->cm = $this->coreFactory->buildCore('CredentialsManager');
        $this->pv = $this->coreFactory->buildCore('PluginValidator');

        $this->fh->setDep($coreProtocol, $this->virgilCryptoWrapper, $this->cm, $this->dbqh);

        $this->virgilPure = $virgilPure;
        $this->version = $version;
    }

    /**
     * @return void
     */
    public function enqueue_styles(): void
    {
        wp_enqueue_style(
            $this->virgilPure,
            plugin_dir_url(__FILE__) . 'css/virgil-pure-admin.css',
            array(),
            $this->version
        );
    }

    /**
     * @return void
     */
    public function virgil_pure_menu(): void
    {
        $devMode = get_option(Option::DEV_MODE);
        $extLoaded = extension_loaded(Config::EXTENSION_VSCE_PHE_PHP);

        $title = $extLoaded ? "Action" : "Info";

        add_menu_page(Config::MAIN_PAGE_TITLE, Config::MAIN_PAGE_TITLE, Config::CAPABILITY, Config::ACTION_PAGE);
        add_submenu_page(Config::ACTION_PAGE, $title, $title, Config::CAPABILITY, Config::ACTION_PAGE, array($this, 'virgil_pure_page_builder'));
        if ($extLoaded) {
            add_submenu_page(Config::ACTION_PAGE, 'Log', 'Log', Config::CAPABILITY, Config::LOG_PAGE, array($this, 'virgil_pure_page_builder'));
            add_submenu_page(Config::ACTION_PAGE, 'FAQ', 'FAQ', Config::CAPABILITY, Config::FAQ_PAGE, array($this, 'virgil_pure_page_builder'));
            if (InfoHelper::isAllUsersMigrated() && ConfigHelper::isRecoveryKeyExists() && 0 !== get_option(Option::DEMO_MODE)) {
                add_submenu_page(
                    Config::ACTION_PAGE,
                    'Recovery',
                    'Recovery',
                    Config::CAPABILITY,
                    Config::RECOVERY_PAGE,
                    array($this, 'virgil_pure_page_builder')
                );
            }
            if ($devMode) {
                add_submenu_page(Config::ACTION_PAGE, 'Dev', 'Dev', Config::CAPABILITY, Config::DEV_PAGE, array($this, 'virgil_pure_page_dev'));
            }
        }
    }

    /**
     * @return void
     * @throws PluginPureException
     * @throws VirgilCryptoException
     */
    public function virgil_pure_form_handler(): void
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
     * @param $password
     * @param $user_id
     * @return bool
     * @throws Exception
     */
    public function virgil_pure_check_password($check, $password, $hash, $userId): bool
    {

        /** @var PluginValidator $pluginValidator */
        $pluginValidator = $this->coreFactory->buildCore('PluginValidator');

        if ($pluginValidator->check() && $userId) {
            if (InfoHelper::isAllUsersMigrated()) {
                var_dump(get_user_by('id', $userId)->user_email);
                var_dump($password);exit;
                return !empty($this->protocol->getPure()->authenticateUser(get_user_by('id', $userId)->user_email, $password)->getGrant()->getSessionId());
            }
        }

        return $check;
    }

    /**
     * @return void
     */
    private function checkPermissions(): void
    {
        if (!current_user_can(Config::CAPABILITY)) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
    }

    /**
     * @return void
     */
    public function virgil_pure_page_builder(): void
    {
        $this->checkPermissions();
        require_once plugin_dir_path(__FILE__) . 'partials/virgil-pure-admin-display.php';
    }

    /**
     * @return void
     */
    public function virgil_pure_page_dev(): void
    {
        $this->checkPermissions();
        require_once plugin_dir_path(__FILE__) . 'partials/_dev.php';
    }

    /**
     * @return void
     */
    public function virgil_pure_init_background_processes(): void
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
     * @return void
     * @throws PheClientException
     * @throws PluginPureException
     * @throws PureCryptoException
     * @throws VirgilCryptoException
     */
    public function virgil_pure_profile_update(int $user_id): void
    {
        if ($this->pv->check() && !empty(get_user_by('id', $user_id)->user_pass)) {
            $this->encrypt($user_id);
            $this->enroll($user_id); //TODO: CHANGE METHOD UPDATE PROFILE
        }
    }

    /**
     * @param WP_User $user
     * @return void
     * @throws PheClientException
     * @throws PluginPureException
     * @throws PureCryptoException
     * @throws VirgilCryptoException|Exception
     */
    public function virgil_pure_password_reset(WP_User $user): void
    {
        if ($this->pv->check()) {
            $this->encrypt($user->ID);
            $this->enroll($user->ID); //TODO: CHANGE METHOD RESET PROFILE
        }
    }

    /**
     * @deprecated
     * @param int $userId
     * @return void
     * @throws PheClientException
     * @throws PureCryptoException
     */
    private function enroll(int $userId): void
    {
        throw new Exception('Method not implemented');
    }

    /**
     * @param int $userId
     * @return void
     * @throws PluginPureException
     * @throws VirgilCryptoException
     */
    private function encrypt(int $userId): void
    {
        $user = get_user_by('id', $userId);
        $pk = get_option(Option::RECOVERY_PUBLIC_KEY);
        if ($pk) {
            $virgilPublicKey = $this->virgilCryptoWrapper->importKey(Crypto::PUBLIC_KEY, $pk);

            $password = $user->user_pass;
            $encrypted = $this->virgilCryptoWrapper->encrypt($password, $virgilPublicKey);

            update_user_meta($user->ID, Option::ENCRYPTED, $encrypted);
        }
    }
}
