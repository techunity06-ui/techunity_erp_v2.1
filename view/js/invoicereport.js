
 
function generate_report() 
{
	Loading(true);
	var cust=$("#cust_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/invoicereport/',
		data: { mode : "generate_report", custid :  cust},
		success: function(response)
		{
			console.log(response);
			if(response != "") {
				
				$('#adv-table').html(response);
				Unloading();
			}
										
		}
	});	

}
