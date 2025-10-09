$(document).ready(function() {
	reload_data();
});
function reload_data()
{
	generate_report();
}

function generate_report() 
{
	var user_id=$("#user_id").val();
	var state_id=$("#state_id").val();
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain+'app/customer_target_report/',
		data: { mode : "generate_report",user_id :  user_id, state_id: state_id},
		success: function(response)
		{
			//console.log(response);
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}

		}
	});	
	
}