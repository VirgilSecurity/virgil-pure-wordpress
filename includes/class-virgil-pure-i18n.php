<?php

/**
 * Class Virgil_Pure_i18n
 */
class Virgil_Pure_i18n {

    /**
     *
     */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'virgil-pure',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}
}
