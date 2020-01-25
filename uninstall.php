<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://github.com/bmlt-enabled/bmlt-export
 * @since      1.0.0
 *
 * @package    Bmlt_Export
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// delete plugin transient
delete_transient('bmlt_export_file_dir_check');

// delete cron event
$timestamp = wp_next_scheduled('bmlt_send_export');
wp_unschedule_event($timestamp, 'bmlt_send_export');

// remove schedule
remove_filter('cron_schedules', 'schedule_cron_bmlt');

/**
 * Removes Upload Folder
 */
$dir = ABSPATH . 'wp-content/uploads/bmlt-export';
if (is_dir($dir)) {
    $objects = scandir($dir);
    foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
            if (filetype($dir."/".$object) == "dir") {
                rmdir($dir."/".$object);
            } else {
                unlink($dir."/".$object);
            }
        }
    }
    reset($objects);
    rmdir($dir);
}
