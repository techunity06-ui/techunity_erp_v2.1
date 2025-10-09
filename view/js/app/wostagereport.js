$(document).ready(function() {
		reload_data();
});
function reload_data()
{
	generate_report();
}

function generate_report() 
{
	Loading();
	var mode_type=$('input[name="mode_type"]:checked').val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/wostagereport/',
		data: { mode : "generate_stage_report",mode_type : mode_type},
		success: function(response)
		{
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
										
		}
	});	
}

