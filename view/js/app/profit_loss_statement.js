$(document).ready(function() {
		reload_data();
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
		url: root_domain+'app/profit_loss_statement/',
		data: { mode : "generate_report", date :  date},
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