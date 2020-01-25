<?php

/**
 *
 * @link              https://github.com/bmlt-enabled/bmlt-export
 * @since             1.0.0
 * @package           BmltExport
 *
 * @wordpress-plugin
 * Plugin Name:       BMLT Export
 * Plugin URI:        https://github.com/bmlt-enabled/bmlt-export
 * Description:       BMLT Export is a plugin that will automatically send a BMLT Export to NAWS once a month.
 * Version:           1.0.0
 * Author:            BMLT enabled
 * Author URI:        http://bmlt.app/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bmlt-export
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

/**
 * Current plugin version.
 */
if (!defined('BMLT_EXPORT_VERSION')) {
    define('BMLT_EXPORT_VERSION', '1.0.0');
}

if (!defined('BMLT_EXPORT_PATH')) {
    define('BMLT_EXPORT_PATH', plugin_dir_path(__FILE__));
}

if (!defined('BMLT_EXPORT_NAME')) {
    define('BMLT_EXPORT_NAME', 'bmlt-export');
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-bmlt-export-activator.php
 */
function activateBmltExport()
{
    require_once BMLT_EXPORT_PATH . 'includes/class-bmlt-export-activator.php';
    BmltExportActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-bmlt-export-deactivator.php
 */
function deactivateBmltExport()
{
    require_once BMLT_EXPORT_PATH . 'includes/class-bmlt-export-deactivator.php';
    BmltExportDeactivator::deactivate();
}

register_activation_hook(__FILE__, 'activateBmltExport');
register_deactivation_hook(__FILE__, 'deactivateBmltExport');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require BMLT_EXPORT_PATH . 'includes/class-bmlt-export.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function runBmltExport()
{

    $plugin = new BmltExport();
    $plugin->run();
}
runBmltExport();
