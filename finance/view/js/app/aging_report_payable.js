$(document).ready(function() {
	
	report_aging_payable();
});
	

function report_aging_payable() 
{
	//alert('hello');
	var date=$("#rep_date").val();
	var bill_status_on = $('#bill_status_on').val();
	//alert(bill_status_on);
	//alert(emp_id);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/aging_report/',
		data: { mode : "generate_aging_payable",date:date,bill_status_on:bill_status_on},
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			$('#adv-table').html(response);
			Unloading();
								
		}
	});	
	
}