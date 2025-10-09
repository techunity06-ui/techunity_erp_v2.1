$(document).ready(function() {
		reload_data();
});
function reload_data()
{
	generate_report();
}

function generate_report() 
{
	var date=$("#rep_date").val();
	
	
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/sales_register_datewise/',
			data: { mode : "generate_report",date :  date,},
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