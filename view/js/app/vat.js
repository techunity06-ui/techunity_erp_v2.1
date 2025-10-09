$(document).ready(function() {
	  	generate_report();
});
function reload_data()
{
	generate_report();
}

function generate_report() 
{
	Loading();
	var date=$("#rep_date").val();
	var typeid=$("#type_id").val();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/vatreport/',
		data: { mode : "generate_report",date :  date,typeid:typeid},
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
