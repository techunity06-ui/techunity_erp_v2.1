//var datatable;
$(document).ready(function() {
	generate_report_emp_ledger();
});

function generate_report_emp_ledger() 
{
	
	var date=$("#rep_date").val();
	var user_id=$("#user_id").val();
	
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/report_sale_emp_acct/',
		data: { mode : "generate_report_emp_ledger", date:date,user_id:user_id },
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			$('#adv-table').html(response);
			Unloading();
								
		}
	});	
	
}