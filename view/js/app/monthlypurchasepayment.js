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
	var cust_id=$("#cust_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/monthlypurchasepayment/',
		data: { mode : "generate_report", date :  date, cust_id : cust_id},
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