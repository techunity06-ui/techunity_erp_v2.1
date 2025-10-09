//var datatable;
$(document).ready(function() {
	generate_report_product_service();
});	 
function generate_report_product_service() 
{
	
	var date=$("#rep_date").val();
	var source_id=$("#source_id").val();
	//alert(source_id);
	if(source_id!="")
	{
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/leads_by_source_reports/',
		data: { mode : "generate_report_product_service",date:date,source_id:source_id},
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
}

