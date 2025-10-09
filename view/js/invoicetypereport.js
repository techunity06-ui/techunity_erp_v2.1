
 
function generate_report() 
{
	Loading(true);
	var cust=$("#cust_id").val();
	if(cust=='')
	{
		toastr.info("Please select Type", "INFO")
		Unloading();
		return false;
	}
	var s_date=$('#from_date').val()
	var e_date=$('#to_date').val()
	$.ajax({
		type: "POST",
		url: root_domain+'app/invoicetypereport/',
		data: { mode : "generate_report", typeid : cust,s_date:s_date,e_date:e_date},
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
