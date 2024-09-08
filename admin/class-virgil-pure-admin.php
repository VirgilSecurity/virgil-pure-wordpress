<?php

use Virgil\Crypto\Exceptions\VirgilCryptoException;
use Virgil\PureKit\Pure\Exception\EmptyArgumentException;
use Virgil\PureKit\Pure\Exception\IllegalStateException;
use Virgil\PureKit\Pure\Exception\NullArgumentException;
use Virgil\PureKit\Pure\Exception\PheClientException;
use Virgil\PureKit\Pure\Exception\PureCryptoException;
use VirgilSecurityPure\Background\EncryptAndMigrateBackgroundProcess;
use VirgilSecurityPure\Background\RecoveryBackgroundProcess;
use VirgilSecurityPure\Config\BuildCore;
use VirgilSecurityPure\Config\Config;
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
    private EncryptAndMigrateBackgroundProcess $migrateBP;
    private RecoveryBackgroundProcess $recoveryBP;
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
        $coreProtocol = $this->coreFactory->buildCore(BuildCore::CORE_PROTOCOL);

        $this->virgilCryptoWrapper = $this->coreFactory->buildCore(BuildCore::VIRGIL_CRYPTO_WRAPPER);

        $coreProtocol->init();
        $this->protocol = $coreProtocol;
        $this->dbqh = $this->coreFactory->buildCore(BuildCore::DB_QUERY_HELPER);
        $this->cm = $this->coreFactory->buildCore(BuildCore::CREDENTIALS_MANAGER);
        $this->fh = $this->coreFactory->buildCore(BuildCore::FORM_HANDLER);
        $this->fh->setDep($coreProtocol, $this->virgilCryptoWrapper, $this->cm, $this->dbqh);
        $this->pv = $this->coreFactory->buildCore(BuildCore::PLUGIN_VALIDATOR);

        $this->migrateBP = new EncryptAndMigrateBackgroundProcess();
        $this->migrateBP->setDep($this->protocol, $this->dbqh);

        $this->recoveryBP = new RecoveryBackgroundProcess();
        $this->recoveryBP->setDep($this->dbqh, $this->virgilCryptoWrapper, $this->cm);

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

        $extLoaded = Config::isAllExtensionEnabled();
        $title = $extLoaded ? "Action" : "Info";

        add_menu_page(Config::MAIN_PAGE_TITLE, Config::MAIN_PAGE_TITLE, Config::CAPABILITY, Config::ACTION_PAGE);
        $pageBuilder = [$this, self::PAGE_BUILDER];
        add_submenu_page(Config::ACTION_PAGE, $title, $title, Config::CAPABILITY, Config::ACTION_PAGE, $pageBuilder);
        if ($extLoaded) {
            add_submenu_page(Config::ACTION_PAGE, 'Log', 'Log', Config::CAPABILITY, Config::LOG_PAGE, $pageBuilder);
            add_submenu_page(Config::ACTION_PAGE, 'FAQ', 'FAQ', Config::CAPABILITY, Config::FAQ_PAGE, $pageBuilder);
            if (InfoHelper::isContinuesMigrationOn()) {
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
     * @return void
     */
    public function virgil_pure_form_handler(): void
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
                        Redirector::toPage(Config::ACTION_PAGE);
                        break;

                    case Form::RECOVERY:
                        $this->fh->recovery();
                        Redirector::toPageLog();
                        break;

                    case Form::DEV_ADD_USERS:
                        $this->fh->addUsers();
                        Redirector::toPage(Config::ACTION_PAGE);
                        break;

                    case Form::DEV_RESTORE_DEFAULTS:
                        $this->fh->restoreDefaults();
                        break;
                }
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
        $pluginValidator = $this->coreFactory->buildCore(BuildCore::PLUGIN_VALIDATOR);

        if ($pluginValidator->check() && $userId) {
            if (InfoHelper::isUserMigrated($hash)) {
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
    }

    public function virgil_pure_user_register(int $userId): void
    {
        if (InfoHelper::isContinuesMigrationOn()) {
            $user = get_user_by('ID', $userId);
            $this->migrateBP->push_to_queue($user);
            $this->migrateBP->save()->dispatch();
        }
    }

    /**
     * @param int $userId
     * @return void
     * @throws EmptyArgumentException
     * @throws IllegalStateException
     * @throws NullArgumentException
     * @throws PheClientException
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
     * @throws PureCryptoException
     * @throws VirgilCryptoException
     */
    private function updatePassword(WP_User $user): void
    {
        if ($this->pv->check() && InfoHelper::isContinuesMigrationOn()) {
            $this->protocol->encryptAndSaveKeyForBackup($user->ID, $user->user_pass);
            $this->protocol->getPure()->resetUserPassword($user->user_email, $user->user_pass, true);
            $this->migrateBP->push_to_queue($user);
            $this->migrateBP->save()->dispatch();
        }
    }
}
