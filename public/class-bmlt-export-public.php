<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    BmltExport
 * @subpackage BmltExport/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    BmltExport
 * @subpackage BmltExport/public
 * @author     BMLT Enabled <help@bmlt.app>
 */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class BmltExportPublic
{
// phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $bmlt_export    The ID of this plugin.
     */
    private $bmlt_export;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $bmlt_export       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($bmlt_export, $version)
    {

        $this->bmlt_export = $bmlt_export;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueueStyles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in BmltExportLoader as all of the hooks are defined
         * in that particular class.
         *
         * The BmltExportLoader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->bmlt_export, plugin_dir_url(__FILE__) . 'css/bmlt-export-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueueScripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in BmltExportLoader as all of the hooks are defined
         * in that particular class.
         *
         * The BmltExportLoader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->bmlt_export, plugin_dir_url(__FILE__) . 'js/bmlt-export-public.js', array( 'jquery' ), $this->version, false);
    }

    public function bmltExportShortcode($atts)
    {

        $atts = array_change_key_case((array)$atts, CASE_LOWER);
        extract(shortcode_atts(array(
            "last" => '0',
            "next" => '0'
        ), $atts));
        $last = sanitize_text_field($last);
        $next = sanitize_text_field($next);
        $nextRun = wp_next_scheduled('bmlt_send_export');

        if ($last && !$next) {
            $content = date_i18n('m-d-Y h:i:s A', $nextRun);
        } elseif ($next && !$last) {
            $content = date_i18n('m-d-Y h:i:s A', strtotime('-1 month', $nextRun));
        } else {
            $content = date_i18n('m-d-Y h:i:s A', $nextRun);
            $content .= '<br>';
            $content .= date_i18n('m-d-Y h:i:s A', strtotime('-1 month', $nextRun));
        }

        return $content;
    }
}
