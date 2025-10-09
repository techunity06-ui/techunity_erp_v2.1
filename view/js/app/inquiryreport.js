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
	var ledger_id = $("#companyid").val();
	$.ajax({
		type: "POST",
		url: root_domain + 'app/inquiryreport/',
		data: { mode : "generate_report", ledger_id :  ledger_id},
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
