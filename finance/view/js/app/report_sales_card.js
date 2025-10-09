$(document).ready(function() {
	report_sales_card();
	report_elcon_sales_card();
});
	

function report_sales_card() 
{
	var party_sales_id=$("#party_sales_id").val();
	//alert(emp_id);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/report_sales_card/',
		data: { mode : "generate_report_sales_card",party_sales_id:party_sales_id },
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			$('#data_table').html(response);
			Unloading();
								
		}
	});	
	
}
function report_elcon_sales_card(){
	var elcon_sales_id=$("#elcon_sales_id").val();
	//alert(emp_id);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/report_sales_card/',
		data: { mode : "report_elcon_sales_card",elcon_sales_id:elcon_sales_id },
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			$('#adv-table').html(response);
			Unloading();
								
		}
	});
}