<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    BmltExport
 * @subpackage BmltExport/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    BmltExport
 * @subpackage BmltExport/includes
 * @author     BMLT Enabled <help@bmlt.app>
 */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class BmltExportI18n
{
// phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function loadPluginTextdomain()
    {

        load_plugin_textdomain(
            'bmlt-export',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
