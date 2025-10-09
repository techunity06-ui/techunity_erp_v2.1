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
	var vender=$("#vender_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchasedetailreport/',
		data: { mode : "generate_report",date :  date,vender_id :vender},
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