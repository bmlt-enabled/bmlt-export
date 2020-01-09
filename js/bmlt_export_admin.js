function getBmltExportValueSelected() {
	var x = document.bmlt_export_options.service_body_dropdown.selectedIndex;
	var res = document.bmlt_export_options.service_body_dropdown.options[x].value.split(",");
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
});
