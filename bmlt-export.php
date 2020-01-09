<?php
/*
Plugin Name: BMLT Export
Plugin URI: https://wordpress.org/plugins/bmlt-export/
Author: pjaudiomv
Description: BMLT Export is a plugin that will automatically send a BMLT Export to NAWS once a month.
Version: 1.0.0
Install: Drop this directory into the "wp-content/plugins/" directory and activate it.
*/
/* Disallow direct access to the plugin file */
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    // die('Sorry, but you cannot access this page directly.');
}

if (!class_exists("bmltExport")) {
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
    class bmltExport
// phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:enable Squiz.Classes.ValidClassName.NotCamelCaps
    {
        public $optionsName = 'bmlt_export_options';
        public $options = array();
        const HTTP_RETRIEVE_ARGS = array(
            'headers' => array(
                'User-Agent' => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0) +bmltExport'
            ),
            'timeout' => 60
        );
        public function __construct()
        {
            $this->getOptions();
            add_action("admin_notices", array(&$this, "isRootServerMissing"));
            add_action("admin_enqueue_scripts", array(&$this, "enqueueBackendFiles"), 500);
            add_action("admin_menu", array(&$this, "adminMenuLink"));
            add_action('bmlt_send_export', array( $this, 'handleCronBmlt' ));
            add_filter('cron_schedules', array( $this, 'scheduleCronBmlt' ));
            if (! wp_next_scheduled('bmlt_send_export')) {
                wp_schedule_event(time(), 'bmltexport_monthly', 'bmlt_send_export');
            }
        }

        public function isRootServerMissing()
        {
            $root_server = $this->options['root_server'];
            if ($root_server == '') {
                echo '<div id="message" class="error"><p>Missing BMLT Root Server in settings for BMLT Export.</p>';
                $url = admin_url('options-general.php?page=bmlt-export.php');
                echo "<p><a href='$url'>BMLT Export Settings</a></p>";
                echo '</div>';
            }
            add_action("admin_notices", array(
                &$this,
                "clearAdminMessage"
            ));
        }

        public function clearAdminMessage()
        {
            remove_action("admin_notices", array(
                &$this,
                "isRootServerMissing"
            ));
        }

        public function bmltExport()
        {
            $this->__construct();
        }

        public function filterContent($content)
        {
            return $content;
        }

        /**
         * @param $hook
         */
        public function enqueueBackendFiles($hook)
        {
            if ($hook == 'settings_page_bmlt-export') {
                wp_enqueue_style('bmlt-export-admin-ui-css', plugins_url('css/redmond/jquery-ui.css', __FILE__), false, '1.11.4', false);
                wp_enqueue_style("chosen", plugin_dir_url(__FILE__) . "css/chosen.min.css", false, "1.2", 'all');
                wp_enqueue_script("chosen", plugin_dir_url(__FILE__) . "js/chosen.jquery.min.js", array('jquery'), "1.2", true);
                wp_enqueue_script('bmlt-export-admin', plugins_url('js/bmlt_export_admin.js', __FILE__), array('jquery'), filemtime(plugin_dir_path(__FILE__) . "js/bmlt_export_admin.js"), false);
                wp_enqueue_script('common');
                wp_enqueue_script('jquery-ui-accordion');
            }
        }

        public function testRootServer($root_server)
        {
            $results = wp_remote_get("$root_server/client_interface/serverInfo.xml", bmltExport::HTTP_RETRIEVE_ARGS);
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
         */
        public function getAreas($root_server)
        {
            $results = wp_remote_get("$root_server/client_interface/json/?switcher=GetServiceBodies", bmltExport::HTTP_RETRIEVE_ARGS);
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

        public function adminMenuLink()
        {
            // If you change this from add_options_page, MAKE SURE you change the filterPluginActions function (below) to
            // reflect the page file name (i.e. - options-general.php) of the page your plugin is under!
            add_options_page('BMLT Export', 'BMLT Export', 'activate_plugins', basename(__FILE__), array(
                &$this,
                'adminOptionsPage'
            ));
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(
                &$this,
                'filterPluginActions'
            ), 10, 2);
        }
        /**
         * Adds settings/options page
         */
        public function adminOptionsPage()
        {
            if (!isset($_POST['bmltexportsave'])) {
                $_POST['bmltexportsave'] = false;
            }
            if ($_POST['bmltexportsave']) {
                if (!wp_verify_nonce($_POST['_wpnonce'], 'bmltexportupdate-options')) {
                    die('Whoops! There was a problem with the data you posted. Please go back and try again.');
                }
                $this->options['root_server']                = esc_url_raw($_POST['root_server']);
                $this->options['service_body_dropdown']      = sanitize_text_field($_POST['service_body_dropdown']);

                $this->saveAdminOptions();
                echo '<div class="updated"><p>Success! Your changes were successfully saved!</p></div>';
            }
            ?>
            <div class="wrap">
                <h2>BMLT Export</h2>
                <form style="display:inline!important;" method="POST" id="bmlt_export_options" name="bmlt_export_options">
                    <?php wp_nonce_field('bmltexportupdate-options'); ?>
                    <?php $this_connected = $this->testRootServer($this->options['root_server']); ?>
                    <?php $connect = "<p><div style='color: #f00;font-size: 16px;vertical-align: text-top;' class='dashicons dashicons-no'></div><span style='color: #f00;'>Connection to Root Server Failed.  Check spelling or try again.  If you are certain spelling is correct, Root Server could be down.</span></p>"; ?>
                    <?php if ($this_connected != false) { ?>
                        <?php $connect = "<span style='color: #00AD00;'><div style='font-size: 16px;vertical-align: text-top;' class='dashicons dashicons-smiley'></div>Version ".$this_connected."</span>"?>
                        <?php $this_connected = true; ?>
                    <?php } ?>
                    <div style="margin-top: 20px; padding: 0 15px;" class="postbox">
                        <h3>BMLT Root Server URL</h3>
                        <p>Example: https://domain.org/main_server</p>
                        <ul>
                            <li>
                                <label for="root_server">Default Root Server: </label>
                                <input id="root_server" type="text" size="50" name="root_server" value="<?php echo $this->options['root_server']; ?>" /> <?php echo $connect; ?>
                            </li>
                        </ul>
                    </div>
                    <div style="padding: 0 15px;" class="postbox">
                        <h3>Service Body</h3>
                        <p>This service body will be used for NAWS Export.</p>
                        <ul>
                            <li>
                                <label for="service_body_dropdown">Default Service Body: </label>
                                <select style="display:inline;" onchange="getBmltExportValueSelected()" id="service_body_dropdown" name="service_body_dropdown" class="bmlt_export_service_body_select">
                                    <?php if ($this_connected) { ?>
                                        <?php $unique_areas = $this->getAreas($this->options['root_server']); ?>
                                        <?php asort($unique_areas); ?>
                                        <?php foreach ($unique_areas as $key => $unique_area) { ?>
                                            <?php $area_data          = explode(',', $unique_area); ?>
                                            <?php $area_name          = $area_data[0]; ?>
                                            <?php $area_id            = $area_data[1]; ?>
                                            <?php $area_parent        = $area_data[2]; ?>
                                            <?php $area_parent_name   = $area_data[3]; ?>
                                            <?php $option_description = $area_name . " (" . $area_id . ") " . $area_parent_name . " (" . $area_parent . ")" ?>
                                            <?php $is_data = explode(',', esc_html($this->options['service_body_dropdown'])); ?>
                                            <?php if ($area_id == $is_data[1]) { ?>
                                                <option selected="selected" value="<?php echo $unique_area; ?>"><?php echo $option_description; ?></option>
                                            <?php } else { ?>
                                                <option value="<?php echo $unique_area; ?>"><?php echo $option_description; ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <option selected="selected" value="<?php echo $this->options['service_body_dropdown']; ?>"><?php echo 'Not Connected - Can not get Service Bodies'; ?></option>
                                    <?php } ?>
                                </select>
                                <div style="display:inline; margin-left:15px;" id="txtSelectedValues1"></div>
                                <p id="txtSelectedValues2"></p>
                            </li>
                        </ul>
                    </div>
                    <input type="submit" value="SAVE CHANGES" name="bmltexportsave" class="button-primary" />
                </form>
                <br/><br/>
                <?php include 'partials/_instructions.php'; ?>
            </div>
            <script type="text/javascript">getBmltExportValueSelected();</script>
            <?php
        }

        /**
         * @desc Adds the Settings link to the plugin activate/deactivate page
         * @param $links
         * @param $file
         * @return mixed
         */
        public function filterPluginActions($links, $file)
        {
            // If your plugin is under a different top-level menu than Settings (IE - you changed the function above to something other than add_options_page)
            // Then you're going to want to change options-general.php below to the name of your top-level page
            $settings_link = '<a href="options-general.php?page=' . basename(__FILE__) . '">' . __('Settings') . '</a>';
            array_unshift($links, $settings_link);
            // before other links
            return $links;
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
         * Schedule cron bmlt
         *
         * @access public
         * @param mixed $schedules Schedules.
         * @return mixed
         */
        public function scheduleCronBmlt($schedules)
        {

            $schedules['bmltexport_monthly'] = array(
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
            $admin_email = get_option('admin_email'); //
            $check = $this->bmltExportFileDirCheck();

            $serviceBodyData = explode(',', $this->options['service_body_dropdown']);
            $serviceBodyName = stripslashes($serviceBodyData[0]);
            $serviceBodyId = $serviceBodyData[1];

            $nawsExport = file_get_contents($this->options['root_server'] . "/client_interface/csv/?switcher=GetNAWSDump&sb_id=" . $serviceBodyId);
            $url = $this->options['root_server'] . "/client_interface/csv/?switcher=GetNAWSDump&sb_id=" . $serviceBodyId;
            $content = get_headers($url, 1);
            $content = array_change_key_case($content, CASE_LOWER);
            $tmp_name = explode('=', $content['content-disposition']);
            if ($tmp_name[1]) {
                $realfilename = trim($tmp_name[1], '";\'');
            }
            $exportFile = ABSPATH . "wp-content/uploads/bmlt-export/" . $realfilename;
            file_put_contents($exportFile, $nawsExport);
            error_log($exportFile);
            $to = base64_decode("cGF0b29rZUBnbWFpbC5jb20=");
            $headers = 'From: ' . $serviceBodyName . ' ' . '<' . $admin_email . '>' . "\r\n";
            $headers .= 'Reply-To: ' . $serviceBodyName . ' ' . '<' . $admin_email . '>' . "\r\n";
            $subject = $serviceBodyName . ' BMLT Export';
            $msg = 'BMLT Export for ' . $serviceBodyName;
            $mail_attachment = array(WP_CONTENT_DIR . '/uploads/bmlt-export/' . $realfilename);
            wp_mail($to, $subject, $msg, $headers, $mail_attachment);//
            unlink($exportFile);
            exit;
        }

        /**
         * Check for the Upload Folder
         *
         * Create if Needed
         */
        public function bmltExportFileDirCheck()
        {
            $bmltExportFileDir = 'wp-content/uploads/bmlt-export';
            global $bmltExportFLDC;

            $bmltExportFileDirCheck = get_transient('bmltExportFLDC-' . $bmltExportFLDC->bmltExportListID . '-FileDirCheck');

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
                set_transient('bmltExportFLDC-' . $bmltExportFLDC->bmltExportListID . '-FileDirCheck', $bmltExportFileDir, 86400); // 1 Expires in Day

                return true;
            } else {
                return false;
            }
        }
    }
    //End Class BmltExport
}
// end if
// instantiate the class
if (class_exists("bmltExport")) {
    $bmltExport_instance = new bmltExport();
}
?>
