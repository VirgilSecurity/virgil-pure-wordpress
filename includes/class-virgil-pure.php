<?php

/**
 * Class Virgil_Pure
 */
class Virgil_Pure {

    /**
     * @var
     */
	protected $loader;

    /**
     * @var string
     */
	protected $Virgil_Pure;

    /**
     * @var string
     */
	protected $version;

    /**
     * Virgil_Pure constructor.
     */
	public function __construct() {
		if ( defined( 'VIRGIL_PURE_VERSION' ) ) {
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
     *
     */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-virgil-pure-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-virgil-pure-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-virgil-pure-admin.php';

		$this->loader = new Virgil_Pure_Loader();
	}

    /**
     *
     */
	private function set_locale() {

		$plugin_i18n = new Virgil_Pure_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

    /**
     *
     */
	private function define_admin_hooks() {

		$plugin_admin = new Virgil_Pure_Admin( $this->get_Virgil_Pure(), $this->get_version() );

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_menu', $plugin_admin, 'virgil_pure_menu');
		$this->loader->add_action('admin_post_virgil_pure', $plugin_admin, 'virgil_pure_form_handler');
		$this->loader->add_action('plugins_loaded', $plugin_admin, 'virgil_pure_init_background_processes');
		$this->loader->add_filter('check_password', $plugin_admin, 'virgil_pure_check_password', 1, 4);
		$this->loader->add_action('after_password_reset', $plugin_admin, 'virgil_pure_password_reset', 1, 1);
		$this->loader->add_action('profile_update', $plugin_admin, 'virgil_pure_profile_update', 1, 1);
	}

    /**
     *
     */
	public function run() {
		$this->loader->run();
	}

    /**
     * @return string
     */
	public function get_Virgil_Pure(): string {
		return $this->Virgil_Pure;
	}

    /**
     * @return mixed
     */
	public function get_loader() {
		return $this->loader;
	}

    /**
     * @return string
     */
	public function get_version(): string {
		return $this->version;
	}

}
