//var datatable;
$(document).ready(function() {
	load_ledger_datatable();
});	 
function load_ledger_datatable() 
{
	
	var date=$("#start_date").val();
	var end_date=$("#end_date").val();
	var crm_tree_user1=$("#crm_tree_user1").val();
	var source_id=$("#source_id").val();
	
		Loading();
		
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/report_daily_folloup_work/',
			data: { mode : "generate_report_product_service",date:date,end_date:end_date,crm_tree_user1:crm_tree_user1},
			success: function(response)
			{
				//alert(response);
				//console.log(response);
				if(response != "") {
					$('#adv-table').html(response);
					Unloading();
				}
											
			}
		});	
	
}
