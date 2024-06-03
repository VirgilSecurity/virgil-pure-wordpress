<?php

/**
 * Class Virgil_Pure_Loader
 */
class Virgil_Pure_Loader {

    /**
     * @var array
     */
	protected array $actions;

    /**
     * @var array
     */
	protected array $filters;

    /**
     * Virgil_Pure_Loader constructor.
     */
	public function __construct() {

		$this->actions = [];
		$this->filters = [];

	}

    /**
     * @param $hook
     * @param $component
     * @param $callback
     * @param int $priority
     * @param int $accepted_args
     */
	public function add_action( $hook, $component, $callback, int $priority = 10, int $accepted_args = 1 ): void
    {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

    /**
     * @param $hook
     * @param $component
     * @param $callback
     * @param int $priority
     * @param int $accepted_args
     */
	public function add_filter( $hook, $component, $callback, int $priority = 10, int $accepted_args = 1 ): void
    {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

    /**
     * @param $hooks
     * @param $hook
     * @param $component
     * @param $callback
     * @param $priority
     * @param $accepted_args
     * @return array
     */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ): array
    {

		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);

		return $hooks;

	}

    /**
     * @return void
     */
	public function run(): void
    {

		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

	}

}
