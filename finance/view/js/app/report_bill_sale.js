$(document).ready(function() {
	
	report_bill_sale();
});
	

function report_bill_sale() 
{
	//alert('hello');
	var date=$("#rep_date").val();
	var cust_id = $('#cust_id').val();
	//alert(bill_status_on);
	//alert(emp_id);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/bill_to_bill/',
		data: {mode:"generate_report_bill_sale",date:date,cust_id:cust_id},
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			$('#adv-table').html(response);
			Unloading();
								
		}
	});	
	
}