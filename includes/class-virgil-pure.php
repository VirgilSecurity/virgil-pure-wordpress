<?php

/**
 * Class Virgil_Pure
 */
class Virgil_Pure
{

    /**
     * @var Virgil_Pure_Loader
     */
    protected Virgil_Pure_Loader $loader;

    /**
     * @var string
     */
    protected string $Virgil_Pure;

    /**
     * @var string
     */
    protected string $version;

    /**
     * Virgil_Pure constructor.
     */
    public function __construct()
    {
        if (defined('VIRGIL_PURE_VERSION')) {
            $this->version = VIRGIL_PURE_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->Virgil_Pure = 'virgil-pure';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
    }

    /**
     * @return void
     */
    private function load_dependencies(): void
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-virgil-pure-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-virgil-pure-i18n.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-virgil-pure-admin.php';

        $this->loader = new Virgil_Pure_Loader();
    }

    /**
     * @return void
     */
    private function set_locale(): void
    {
        $plugin_i18n = new Virgil_Pure_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * @return void
     */
    private function define_admin_hooks(): void
    {

        $plugin_admin = new Virgil_Pure_Admin($this->get_Virgil_Pure(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_menu', $plugin_admin, 'virgil_pure_menu');
        $this->loader->add_action('admin_post_virgil_pure', $plugin_admin, 'virgil_pure_form_handler');
        $this->loader->add_action('plugins_loaded', $plugin_admin, 'virgil_pure_init_background_processes');
        $this->loader->add_filter('check_password', $plugin_admin, 'virgil_pure_check_password', 1, 4);
        $this->loader->add_action('after_password_reset', $plugin_admin, 'virgil_pure_password_reset', 1);
        $this->loader->add_action('profile_update', $plugin_admin, 'virgil_pure_profile_update', 1);
        $this->loader->add_action('user_register', $plugin_admin, 'virgil_pure_user_register', 1);
    }

    /**
     * @return void
     */
    public function run(): void
    {
        $this->loader->run();
    }

    /**
     * @return string
     */
    public function get_Virgil_Pure(): string
    {
        return $this->Virgil_Pure;
    }

    /**
     * @return string
     */
    public function get_version(): string
    {
        return $this->version;
    }
}
