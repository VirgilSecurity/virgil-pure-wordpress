<?php
/**
 * WP Async Request
 *
 * @package WP-Background-Processing
 */

namespace VirgilSecurityPure\Background;

/**
 * Abstract WP_Async_Request class.
 *
 * @abstract
 */
abstract class WP_Async_Request
{

    /**
     * Prefix
     *
     * (default value: 'wp')
     *
     * @var string
     * @access protected
     */
    protected string $prefix = 'wp';

    /**
     * Action
     *
     * (default value: 'async_request')
     *
     * @var string
     * @access protected
     */
    protected string $action = 'async_request';

    /**
     * Identifier
     *
     * @var string
     * @access protected
     */
    protected string $identifier;

    /**
     * Data
     *
     * (default value: array())
     *
     * @var array
     * @access protected
     */
    protected array $data = [];

    /**
     * Initiate new async request
     */
    public function __construct()
    {
        $this->identifier = $this->prefix . '_' . $this->action;

        add_action('wp_ajax_' . $this->identifier, [$this, 'maybe_handle']);
        add_action('wp_ajax_nopriv_' . $this->identifier, [$this, 'maybe_handle']);
    }

    /**
     * Set data used during the request
     *
     * @param array $data Data.
     *
     * @return $this
     */
    public function data(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Dispatch the async request
     *
     * @return array
     */
    public function dispatch(): array
    {
        $url = add_query_arg($this->get_query_args(), $this->get_query_url());
        $args = $this->get_post_args();

        return wp_remote_post(esc_url_raw($url), $args);
    }

    /**
     * Get query args
     *
     * @return array
     */
    protected function get_query_args(): array
    {
        if (property_exists($this, 'query_args')) {
            return $this->query_args;
        }

        return [
            'action' => $this->identifier,
            'nonce' => wp_create_nonce($this->identifier),
        ];
    }

    /**
     * Get query URL
     *
     * @return string
     */
    protected function get_query_url(): string
    {
        if (property_exists($this, 'query_url')) {
            return $this->query_url;
        }

        return admin_url('admin-ajax.php');
    }

    /**
     * Get post args
     *
     * @return array
     */
    protected function get_post_args(): array
    {
        if (property_exists($this, 'post_args')) {
            return $this->post_args;
        }

        return [
            'timeout' => 0.01,
            'blocking' => false,
            'body' => $this->data,
            'cookies' => $_COOKIE,
            'sslverify' => apply_filters('https_local_ssl_verify', false),
        ];
    }

    /**
     * Maybe handle
     *
     * Check for correct nonce and pass to handler.
     */
    public function maybe_handle(): void
    {
        // Don't lock up other requests while processing
        session_write_close();

        check_ajax_referer($this->identifier, 'nonce');

        $this->handle();

        wp_die();
    }

    /**
     * Handle
     *
     * Override this method to perform any actions required
     * during the async request.
     */
    abstract protected function handle();
}
