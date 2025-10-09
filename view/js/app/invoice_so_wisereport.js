$(document).ready(function() {
		reload_data();
//		show_all();
});
function reload_data()
{
	generate_report();
}

function generate_report() 
{
	Loading();
	var date=$('#rep_date').val();
	var type=$('#type_id').val();
	var cust=$("#cust_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/invoice_so_wisereport/',
		data: { mode : "generate_report",date :  date,type_id:type,cust_id :cust},
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