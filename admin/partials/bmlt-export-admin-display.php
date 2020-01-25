<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Bmlt_Export
 * @subpackage Bmlt_Export/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<?php
if (!isset($_POST['bmlt_export_save'])) {
    $_POST['bmlt_export_save'] = false;
}
if ($_POST['bmlt_export_save']) {
    if (!wp_verify_nonce($_POST['_wpnonce'], 'bmltexportupdate-options')) {
        die('Whoops! There was a problem with the data you posted. Please go back and try again.');
    }
    $this->options['root_server']                = esc_url_raw($_POST['root_server']);
    $this->options['service_body_dropdown']      = sanitize_text_field($_POST['service_body_dropdown']);

    $this->save_admin_options();
    echo '<div class="updated"><p>Success! Your changes were successfully saved!</p></div>';
}
?>
<div class="wrap">
    <h2>BMLT Export</h2>
    <form style="display:inline!important;" method="POST" id="bmlt_export_options" name="bmlt_export_options">
        <?php wp_nonce_field('bmltexportupdate-options'); ?>
        <?php $this_connected = $this->test_root_server($this->options['root_server']); ?>
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
                    <select style="display:inline;" id="root_server" name="root_server" class="bmlt_export_root_servers_select">
                        <?php
                        $rootServerList = $this->get_root_servers();
                        foreach ($rootServerList as $rootServer) { ?>
                            <?php $rootServerURL = rtrim($rootServer['rootURL'],"/"); ?>
                            <?php if ($rootServerURL == rtrim($this->options['root_server'],"/")) { ?>
                                <option selected="selected" value="<?php echo $rootServerURL; ?>"><?php echo $rootServer['name'] . " (" . $rootServerURL . ")"; ?></option>
                            <?php } else { ?>
                                <option value="<?php echo $rootServerURL; ?>"><?php echo $rootServer['name'] . " (" . $rootServerURL . ")"; ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
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
                            <?php $unique_areas = $this->get_areas($this->options['root_server']); ?>
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
                </li>
            </ul>
        </div>
        <div style="margin-top: 20px; padding: 0 15px;" class="postbox">
            <p>
                <?php echo "<strong>Next Execution Time:</strong> " . date_i18n('m-d-Y h:i:s A', wp_next_scheduled('bmlt_send_export')) . " (" . $this->time_since(time(), wp_next_scheduled('bmlt_send_export')) . ")"; ?>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <?php echo "<strong>Current Server Time:</strong> " . date_i18n('m-d-y h:i:s A'); ?>
            </p>
        </div>
        <input type="submit" value="SAVE CHANGES" name="bmlt_export_save" class="button-primary" />
    </form>
    <br/><br/>
    <?php include 'bmlt-export-instructions.php'; ?>
</div>
<script type="text/javascript">getBmltExportValueSelected();</script>
