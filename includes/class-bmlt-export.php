<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    BmltExport
 * @subpackage BmltExport/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    BmltExport
 * @subpackage BmltExport/includes
 * @author     BMLT Enabled <help@bmlt.app>
 */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class BmltExport
{
// phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      BmltExportLoader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $bmlt_export    The string used to uniquely identify this plugin.
     */
    protected $bmlt_export;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        if (defined('BMLT_EXPORT_VERSION')) {
            $this->version = BMLT_EXPORT_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->bmlt_export = 'bmlt-export';

        $this->loadDependencies();
        $this->setLocale();
        $this->defineAdminHooks();
        $this->definePublicHooks();
        $this->defineAdminFilters();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - BmltExportLoader. Orchestrates the hooks of the plugin.
     * - BmltExportI18n. Defines internationalization functionality.
     * - BmltExportAdmin. Defines all hooks for the admin area.
     * - BmltExportPublic. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function loadDependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-bmlt-export-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-bmlt-export-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-bmlt-export-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-bmlt-export-public.php';

        $this->loader = new BmltExportLoader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the BmltExportI18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function setLocale()
    {

        $plugin_i18n = new BmltExportI18n();

        $this->loader->addAction('plugins_loaded', $plugin_i18n, 'loadPluginTextdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function defineAdminHooks()
    {

        $plugin_admin = new BmltExportAdmin($this->getBmltExport(), $this->getVersion());
        $this->loader->addAction('admin_menu', $plugin_admin, 'adminMenuLink');
        $this->loader->addAction('admin_init', $plugin_admin, 'register_setting');
        $this->loader->addAction('admin_enqueueScripts', $plugin_admin, 'enqueueStyles');
        $this->loader->addAction('admin_enqueueScripts', $plugin_admin, 'enqueueScripts');
        $this->loader->addAction("admin_notices", $plugin_admin, "isRootServerMissing");
        $this->loader->addAction('bmlt_send_export', $plugin_admin, 'handleCronBmlt');
    }

    /**
     * Register all of the filters related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function defineAdminFilters()
    {
        $plugin_admin = new BmltExportAdmin($this->getBmltExport(), $this->getVersion());
        $this->loader->addFilter('cron_schedules', $plugin_admin, 'scheduleCronBmlt');
        // $this->loader->addFilter('plugin_action_links_' . plugin_basename(__FILE__), $plugin_admin,'filter_plugin_actions',10,2);
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function definePublicHooks()
    {

        $plugin_public = new BmltExportPublic($this->getBmltExport(), $this->getVersion());

        $this->loader->addAction('wp_enqueueScripts', $plugin_public, 'enqueueStyles');
        $this->loader->addAction('wp_enqueueScripts', $plugin_public, 'enqueueScripts');
        $this->loader->addShortcode("bmlt_export", $plugin_public, "bmltExportShortcode", 10, 2);
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function getBmltExport()
    {
        return $this->bmlt_export;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    BmltExportLoader    Orchestrates the hooks of the plugin.
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function getVersion()
    {
        return $this->version;
    }
}
