$(document).ready(function() {
		reload_data();
});
function reload_data()
{
	generate_report();
}

function generate_report() 
{
	var invoice_id=$("#invoice_id").val();
	var date=$("#rep_date").val();
	var cust_id=$("#cust_id").val();
	
	
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/purchase_pending/',
			data: { mode : "generate_report",invoice_id:invoice_id,date:date,cust_id:cust_id},
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
function load_quotation_sales_order(estimate_id,so_id){
	if(estimate_id){
	$('#sales_order_div').attr("style","display:block");
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/invoice/',
		data: { mode : "load_quotation_sales_order", estimate_id : estimate_id },
		success: function(data){
				//console.log(data);
				var resp = 	JSON.parse(data);
				
				$('#so_id').select2('val','');
				 $('#so_id').html(resp.so_html);
				 $('#so_id').select2('val',so_id);
				 Unloading();
			}
			
	});
	}else {
		$('#sales_order_div').attr("style","display:none");
	}
}