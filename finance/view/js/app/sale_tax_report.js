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
	var cust_id = $("#cust_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/sale_tax_report/',
		data: { mode : "generate_report",date :  date,cust_id:cust_id},
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
