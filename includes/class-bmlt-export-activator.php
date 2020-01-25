<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    BmltExport
 * @subpackage BmltExport/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    BmltExport
 * @subpackage BmltExport/includes
 * @author     BMLT Enabled <help@bmlt.app>
 */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class BmltExportActivator
{
// phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        $BmltExportAdmin = new BmltExportAdmin(BMLT_EXPORT_NAME, BMLT_EXPORT_VERSION);
        $BmltExportAdmin->fileDirCheck();
    }
}
