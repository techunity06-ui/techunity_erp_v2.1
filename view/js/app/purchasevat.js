$(document).ready(function() {
	  	generate_report();
});
function reload_data()
{
	generate_report();
}

function generate_report() 
{
	Loading(true);
	var date=$("#rep_date").val();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchasevatreport/',
		data: { mode : "generate_report",date :  date},
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
