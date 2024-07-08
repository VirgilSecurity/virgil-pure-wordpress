<?php

/**
 * Class Virgil_Pure_i18n
 */
class Virgil_Pure_i18n
{

    /**
     * @return void
     */
    public function load_plugin_textdomain(): void
    {

        load_plugin_textdomain(
            'virgil-pure',
            false,
            dirname(plugin_basename(__FILE__), 2) . '/languages/'
        );
    }
}
