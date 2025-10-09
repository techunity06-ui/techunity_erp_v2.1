$(document).ready(function() {
		reload_data();
});
function reload_data()
{
	generate_report();
}

function generate_report()
{
	var vender_id=$("#vender_id").val();
	var date=$("#rep_date").val();	
	
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/purchase_register/',
			data: { mode : "generate_report", vender_id : vender_id,date :  date},
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