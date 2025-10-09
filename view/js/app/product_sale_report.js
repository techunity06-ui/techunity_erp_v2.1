$(document).ready(function() {
		reload_data();
});
function reload_data()
{
	generate_report();
}

function generate_report() 
{
	var cust_id=$("#cust_id").val();
	var date=$("#rep_date").val();
	
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/product_sale_report/',
		data: { mode : "generate_report", cust_id : cust_id,date :  date,},
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