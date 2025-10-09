$(document).ready(function() {
	
	report_aging_receivable();
});
	

function report_aging_receivable() 
{
	//alert('hello');
	var date=$("#rep_date").val();
	//alert(bill_status_on);
	//alert(emp_id);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/aging_report/',
		data: { mode : "generate_aging_receivable",date:date},
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			$('#adv-table').html(response);
			Unloading();
								
		}
	});	
	
}