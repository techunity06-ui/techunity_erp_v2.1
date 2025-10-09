$(document).ready(function() {
	
	cost_center_report();
});

function cost_center_report(costid='') 
{
	var date=$("#rep_date").val();
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/cost_center_report/',
		data: { mode : "cost_center_report",date:date,costid:costid },
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			$('#costcenter_data_table').html(response);
			Unloading();
								
		}
	});	
	
}

