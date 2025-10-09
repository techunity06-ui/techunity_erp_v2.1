$(document).ready(function() {
	//report_ledger();
});
function report_ledger() 
{
	//alert('hello');
	var report_date=$("#report_date").val();
	if(report_date){
		//alert(emp_id);
		Loading();
		
		$.ajax({
			type: "POST",
			url: root_domain+'app/report_store_accept/',
			data: { mode : "generate_report_production",report_date:report_date },
			success: function(response)
			{
				//alert(response);
				//console.log(response);
				$('#adv-table').html(response);
				Unloading();
									
			}
		});	
	
	}else{
		alert("Select date");
	}
	
}
