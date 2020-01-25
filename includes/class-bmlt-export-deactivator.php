<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    BmltExport
 * @subpackage BmltExport/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    BmltExport
 * @subpackage BmltExport/includes
 * @author     BMLT Enabled <help@bmlt.app>
 */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class BmltExportDeactivator
{
// phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function deactivate()
    {
        // delete cron event
        $timestamp = wp_next_scheduled('bmlt_send_export');
        wp_unschedule_event($timestamp, 'bmlt_send_export');

        // remove schedule
        remove_filter('cron_schedules', 'scheduleCronBmlt');
    }
}
