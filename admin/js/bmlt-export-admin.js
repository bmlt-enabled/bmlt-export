/**
* All of the code for admin-facing JavaScript
*/

function getBmltExportValueSelected() {
	const x = document.bmlt_export_options.service_body_dropdown.selectedIndex;
	const res = document.bmlt_export_options.service_body_dropdown.options[x].value.split(",");
	document.getElementById("txtSelectedValues1").innerHTML = '<b>Service Body ID:</b> <span class="bmlt_sb">' + res[1] + '</span>';
};

jQuery(document).ready(function($) {
	$("#bmlt_export_accordion").accordion({
		heightStyle: "content",
		active: false,
		collapsible: true
	});
	$(".bmlt_export_service_body_select").chosen({
		inherit_select_classes: true,
		width: "50%"
	});
	$(".bmlt_export_root_servers_select").chosen({
		width: '55%',
		create_option: true,
		persistent_create_option: true,
		skip_no_results: true
	});
});
