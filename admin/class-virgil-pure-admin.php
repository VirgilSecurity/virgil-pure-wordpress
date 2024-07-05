<?php

use Virgil\Crypto\Exceptions\VirgilCryptoException;
use Virgil\PureKit\Pure\Exception\EmptyArgumentException;
use Virgil\PureKit\Pure\Exception\IllegalStateException;
use Virgil\PureKit\Pure\Exception\NullArgumentException;
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

    private const PAGE_BUILDER = 'virgil_pure_page_builder';

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
            [],
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
        $pageBuilder = [$this, self::PAGE_BUILDER];
        add_submenu_page(Config::ACTION_PAGE, $title, $title, Config::CAPABILITY, Config::ACTION_PAGE, $pageBuilder);
        if ($extLoaded) {
            add_submenu_page(Config::ACTION_PAGE, 'Log', 'Log', Config::CAPABILITY, Config::LOG_PAGE, $pageBuilder);
            add_submenu_page(Config::ACTION_PAGE, 'FAQ', 'FAQ', Config::CAPABILITY, Config::FAQ_PAGE, $pageBuilder);
            if ($this->isAddSubmenuPage()) {
                add_submenu_page(
                    Config::ACTION_PAGE,
                    'Recovery',
                    'Recovery',
                    Config::CAPABILITY,
                    Config::RECOVERY_PAGE,
                    $pageBuilder
                );
            }
            if ($devMode) {
                add_submenu_page(
                    Config::ACTION_PAGE,
                    'Dev',
                    'Dev',
                    Config::CAPABILITY,
                    Config::DEV_PAGE,
                    [$this, 'virgil_pure_page_dev']
                );
            }
        }
    }

    /**
     * @return bool
     */
    private function isAddSubmenuPage(): bool
    {
        return InfoHelper::isAllUsersMigrated() && ConfigHelper::isRecoveryKeyExists() && ConfigHelper::isDemoMode();

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
     * @param $check
     * @param $password
     * @param $hash
     * @param $userId
     * @return bool
     */
    public function virgil_pure_check_password($check, $password, $hash, $userId): bool
    {

        /** @var PluginValidator $pluginValidator */
        $pluginValidator = $this->coreFactory->buildCore('PluginValidator');

        if ($pluginValidator->check() && $userId) {
            if (InfoHelper::isAllUsersMigrated()) {
                try {
                    $wpUser = get_user_by('id', $userId);
                    $this->protocol->auth($wpUser->user_email, $password, $hash);
                    return true;
                } catch (Exception $e) {
                    Logger::log("Invalid auth " . $wpUser->user_email . ': ' . $e->getMessage(), 0);
                    return false;
                }
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
     * @param int $userId
     * @return void
     * @throws EmptyArgumentException
     * @throws IllegalStateException
     * @throws NullArgumentException
     * @throws PheClientException
     * @throws PluginPureException
     * @throws PureCryptoException
     * @throws VirgilCryptoException
     */
    public function virgil_pure_profile_update(int $userId): void
    {
        $wpUser = get_user_by('id', $userId);
        $this->updatePassword($wpUser);
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
        $this->updatePassword($user);
    }

    /**
     * @param WP_User $user
     * @return void
     * @throws EmptyArgumentException
     * @throws IllegalStateException
     * @throws NullArgumentException
     * @throws PheClientException
     * @throws PluginPureException
     * @throws PureCryptoException
     * @throws VirgilCryptoException
     */
    private function updatePassword(WP_User $user): void
    {
        if ($this->pv->check()) {
            $this->encrypt($user->ID);
            $this->protocol->getPure()->resetUserPassword($user->user_email, $user->user_pass, true);
            $this->dbqh->clearUserPass($user->ID);
        }
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
