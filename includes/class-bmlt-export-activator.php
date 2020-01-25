<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Bmlt_Export
 * @subpackage Bmlt_Export/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Bmlt_Export
 * @subpackage Bmlt_Export/includes
 * @author     BMLT Enabled <help@bmlt.app>
 */
class Bmlt_Export_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        $BmltExportAdmin = new Bmlt_Export_Admin( BMLT_EXPORT_NAME, BMLT_EXPORT_VERSION);
        $BmltExportAdmin->file_dir_check();
	}

}
