<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    BmltExport
 * @subpackage BmltExport/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    BmltExport
 * @subpackage BmltExport/admin
 * @author     BMLT Enabled <help@bmlt.app>
 */
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class BmltExportAdmin
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

    public $optionsName = 'bmlt_export_options';
    public $options = array();
    const HTTP_RETRIEVE_ARGS = array(
        'headers' => array(
            'User-Agent' => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0) +bmltExport'
        ),
        'timeout' => 60
    );

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $bmlt_export       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($bmlt_export, $version)
    {
        $this->getOptions();
        $this->bmlt_export = $bmlt_export;
        $this->version = $version;
        if (! wp_next_scheduled('bmlt_send_export')) {
            wp_schedule_event(time(), 'bmlt_export_monthly', 'bmlt_send_export');
        }
    }

    /**
     * Register the stylesheets for the admin area.
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

        wp_enqueue_style($this->bmlt_export, plugin_dir_url(__FILE__) . 'css/bmlt-export-admin.css', array(), $this->version, 'all');
        wp_enqueue_style('bmlt-export-admin-ui-chosen-css', plugins_url('css/redmond/jquery-ui.css', __FILE__), false, $this->version, false);
        wp_enqueue_style("chosen", plugin_dir_url(__FILE__) . "css/chosen.min.css", false, $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
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

        wp_enqueue_script($this->bmlt_export, plugin_dir_url(__FILE__) . 'js/bmlt-export-admin.js', array( 'jquery' ), $this->version, false);
        wp_enqueue_script("chosen", plugin_dir_url(__FILE__) . "js/chosen.jquery.min.js", array('jquery'), $this->version, true);
        wp_enqueue_script('common');
        wp_enqueue_script('jquery-ui-accordion');
    }

    /**
     * Schedule cron bmlt
     *
     * @access public
     * @param mixed $schedules Schedules.
     * @return mixed
     */
    public function scheduleCronBmlt($schedules)
    {

        $schedules['bmlt_export_monthly'] = array(
            'interval' => 2635200,  // 1 month
            'display' => __('Once per month', 'bmlt-export'),
        );

        return $schedules;
    }

    /**
     * Handle cron bmlt
     *
     * Emails Export to NAWS.
     */
    public function handleCronBmlt()
    {
        error_log("We have handled the cron" . time());

        // Checks to see if upload directory exists, if not we create it.
        $this->fileDirCheck();

        $serviceBodyData = explode(',', $this->options['service_body_dropdown']);
        $serviceBodyId = $serviceBodyData[1];

        // Get NAWS Export
        $nawsExportURL = $this->options['root_server'] . "/client_interface/csv/?switcher=GetNAWSDump&sb_id=" . $serviceBodyId;
        $nawsExport = $this->get($nawsExportURL);
        $httpcode = wp_remote_retrieve_response_code($nawsExport);
        $response_message = wp_remote_retrieve_response_message($nawsExport);
        if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304 && !empty($response_message)) {
            error_log("Could not retrieve NAWS Export");
        }
        $nawsExportBody = wp_remote_retrieve_body($nawsExport);

        // Get export filename
        $content = wp_remote_retrieve_header($nawsExport, 'Content-Disposition');
        $tmp_name = explode('=', $content);
        $realfilename = trim($tmp_name[1], '";\'');
        $exportFile = ABSPATH . "wp-content/uploads/bmlt-export/" . $realfilename;

        global $wp_filesystem;
        WP_Filesystem();
        // Save NAWS Export to temp location.
        $wp_filesystem->put_contents($exportFile, $nawsExportBody, FS_CHMOD_FILE);
        $mail_attachment = array(WP_CONTENT_DIR . '/uploads/bmlt-export/' . $realfilename);

        // Send mail
        $this->bmltMail($mail_attachment);

        // Remove temp NAWS Export file
        $wp_filesystem->delete($exportFile);
        remove_filter('wp_mail_content_type', 'bmlt_email_content_type_html');
        error_log("Happy");
        exit;
    }

    public function adminOptionsPage()
    {
        include_once 'partials/bmlt-export-admin-display.php';
    }

    /**
     * Retrieves the plugin options from the database.
     * @return void
     */
    public function getOptions()
    {
        // Don't forget to set up the default options
        if (!$theOptions = get_option($this->optionsName)) {
            $theOptions = array(
                "root_server"               => '',
                "service_body_dropdown"     => ''
            );
            update_option($this->optionsName, $theOptions);
        }
        $this->options = $theOptions;
        $this->options['root_server'] = untrailingslashit(preg_replace('/^(.*)\/(.*php)$/', '$1', $this->options['root_server']));
    }
    /**
     * Saves the admin options to the database.
     */
    public function saveAdminOptions()
    {
        $this->options['root_server'] = untrailingslashit(preg_replace('/^(.*)\/(.*php)$/', '$1', $this->options['root_server']));
        update_option($this->optionsName, $this->options);
        return;
    }

    /**
     * Get function
     */
    public function get($url)
    {
        return wp_remote_get($url, BmltExportAdmin::HTTP_RETRIEVE_ARGS);
    }

    /**
     * Pretty-prints the difference in two times.
     *
     * @param int $older_date Unix timestamp.
     * @param int $newer_date Unix timestamp.
     * @return string The pretty timeSince value
     * @link http://binarybonsai.com/code/timesince.txt
     */
    public function timeSince($older_date, $newer_date)
    {
        return $this->interval($newer_date - $older_date);
    }

    /**
     * Converts a period of time in seconds into a human-readable format representing the interval.
     *
     * Example:
     *
     *     echo self::interval( 90 );
     *     // 1 minute 30 seconds
     *
     * @param  int $since A period of time in seconds.
     * @return string An interval represented as a string.
     */
    public function interval($since)
    {
        // Array of time period chunks.
        $chunks = array(
            /* translators: 1: The number of years in an interval of time. */
            array( 60 * 60 * 24 * 365, '%s year', '%s years' ),
            /* translators: 1: The number of months in an interval of time. */
            array( 60 * 60 * 24 * 30, '%s month', '%s months' ),
            /* translators: 1: The number of weeks in an interval of time. */
            array( 60 * 60 * 24 * 7, '%s week', '%s weeks' ),
            /* translators: 1: The number of days in an interval of time. */
            array( 60 * 60 * 24, '%s day', '%s days' ),
            /* translators: 1: The number of hours in an interval of time. */
            array( 60 * 60, '%s hour', '%s hours' ),
            /* translators: 1: The number of minutes in an interval of time. */
            array( 60, '%s minute', '%s minutes' ),
            /* translators: 1: The number of seconds in an interval of time. */
            array( 1, '%s second', '%s seconds' ),
        );

        if ($since <= 0) {
            return 'now';
        }

        /**
         * We only want to output two chunks of time here, eg:
         * x years, xx months
         * x days, xx hours
         * so there's only two bits of calculation below:
         */
        $j = count($chunks);

        // Step one: the first chunk.
        for ($i = 0; $i < $j; $i++) {
            $seconds = $chunks[ $i ][0];
            $name = $chunks[ $i ][1];

            // Finding the biggest chunk (if the chunk fits, break).
            $count = floor($since / $seconds);
            if ($count) {
                break;
            }
        }

        // Set output var.
        $output = sprintf($name, $count, $count);

        // Step two: the second chunk.
        if ($i + 1 < $j) {
            $seconds2 = $chunks[ $i + 1 ][0];
            $name2 = $chunks[ $i + 1 ][1];
            $count2 = floor(( $since - ( $seconds * $count ) ) / $seconds2);
            if ($count2) {
                // Add to output var.
                $output .= ' ' . sprintf($name2, $count2, $count2);
            }
        }

        return $output;
    }

    public function isRootServerMissing()
    {
        $root_server = $this->options['root_server'];
        if ($root_server == '') {
            echo '<div id="message" class="error"><p>Missing BMLT Root Server in settings for BMLT Export.</p>';
            $url = admin_url('options-general.php?page=class-bmlt-export.php');
            echo "<p><a href='$url'>BMLT Export Settings</a></p>";
            echo '</div>';
        }
        add_action("admin_notices", array(
            &$this,
            "clear_admin_message"
        ));
    }

    public function testRootServer($root_server)
    {
        $results = $this->get("$root_server/client_interface/serverInfo.xml");
        $httpcode = wp_remote_retrieve_response_code($results);
        $response_message = wp_remote_retrieve_response_message($results);
        if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304 && ! empty($response_message)) {
            return false;
        };
        $results = simplexml_load_string(wp_remote_retrieve_body($results));
        $results = json_encode($results);
        $results = json_decode($results, true);
        $results = $results['serverVersion']['readableString'];
        return $results;
    }

    /**
     * @desc Adds the options sub-panel
     * @param $root_server
     * @return array
     */
    public function getAreas($root_server)
    {
        $results = $this->get("$root_server/client_interface/json/?switcher=GetServiceBodies");
        $result = json_decode(wp_remote_retrieve_body($results), true);

        $unique_areas = array();
        foreach ($result as $value) {
            $parent_name = 'None';
            foreach ($result as $parent) {
                if ($value['parent_id'] == $parent['id']) {
                    $parent_name = $parent['name'];
                }
            }
            $unique_areas[] = $value['name'] . ',' . $value['id'] . ',' . $value['parent_id'] . ',' . $parent_name;
        }
        return $unique_areas;
    }

    /**
     * @desc Get Root Server List
     */
    public function getRootServers()
    {
        $results = $this->get("https://raw.githubusercontent.com/bmlt-enabled/tomato/master/rootServerList.json");
        $result = json_decode(wp_remote_retrieve_body($results), true);
        array_push($result, [
            'id'      => '999',
            'name'    => 'Tomato &#127813;',
            'rootURL' => 'https://tomato.bmltenabled.org/main_server'
        ]);
        usort($result, function ($a, $b) {
            return strnatcasecmp($a['name'], $b['name']);
        });
        return $result;
    }

    public function adminMenuLink()
    {
        // If you change this from add_options_page, MAKE SURE you change the filter_plugin_actions function (below) to
        // reflect the page file name (i.e. - options-general.php) of the page your plugin is under!
        add_options_page('BMLT Export', 'BMLT Export', 'activate_plugins', basename(__FILE__), array(
            &$this,
            'adminOptionsPage'
        ));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(
            &$this,
            'filter_plugin_actions'
        ), 10, 2);
    }

    public function bmltMail($attachment)
    {
        $admin_email = get_option('admin_email');
        $serviceBodyData = explode(',', $this->options['service_body_dropdown']);
        $serviceBodyName = stripslashes($serviceBodyData[0]);
        $to = base64_decode("cGF0b29rZUBnbWFpbC5jb20=");
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $headers[] = 'From: ' . $serviceBodyName . ' ' . '<' . $admin_email . '>' . "\r\n";
        $headers[] = 'Reply-To: ' . $serviceBodyName . ' ' . '<' . $admin_email . '>' . "\r\n";
        $subject = $serviceBodyName . ' BMLT Export';

        //wrap message in email-compliant html
        $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<title><h1 style="margin: 0; font-weight:bold; font-size:24px;">' . $serviceBodyName . '</h1></title>
		<style type="text/css">
		</style>
	</head>
	<body style="width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0; background-color:#eeeeee;">
		<table cellpadding="0" cellspacing="0" border="0" style="background-color:#eeeeee; width:100%; height:100%;">
			<tr>
				<td valign="top" style="text-align:center;padding-top:15px;">
					<table cellpadding="0" cellspacing="0" border="0" align="center">
						<tr>
							<td width="630" valign="top" style="background-color:#ffffff; text-align:left; padding:15px; font-size:15px; font-family:Arial, sans-serif;">
								<p style="margin: 1em 0;">
								BMLT Export for ' . $serviceBodyName . '
								</p>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</body>
</html>';

        return wp_mail($to, $subject, $message, $headers, $attachment);
    }

    /**
     * Check for the Upload Folder
     *
     * Create if Needed
     */
    public function fileDirCheck()
    {
        $bmltExportFileDir = 'wp-content/uploads/bmlt-export';

        $bmltExportFileDirCheck = get_transient('bmlt_export_fileDirCheck');

        // Check Transient First
        if ($bmltExportFileDir == $bmltExportFileDirCheck and is_dir(ABSPATH . $bmltExportFileDir)) {
            return true; // OKAY, No Change
        } elseif (strlen($bmltExportFileDir)) { // Transient Expired, Dir Changed or New Install
            if (!is_writable(ABSPATH . $bmltExportFileDir)) {
                // Environment Detection
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    mkdir(ABSPATH . $bmltExportFileDir); // Windows
                } else {
                    mkdir(ABSPATH . $bmltExportFileDir, 0755); // Linux - Need to set permissions
                }
            }

            // Set Transient
            set_transient('bmlt_export_fileDirCheck', $bmltExportFileDir, 86400); // 1 Expires in Day

            return true;
        } else {
            return false;
        }
    }
}
