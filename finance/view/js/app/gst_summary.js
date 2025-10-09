$(document).ready(function() {
	  	generate_report();
		
});
function reload_data()
{
	generate_report();
	
}
//,typeid:typeid
function generate_report() 
{
	Loading();
	//alert("hii");
	var date=$("#rep_date").val();
	//alert(date);
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/gst_summary/',
		data: { mode : "generate_report",date :  date},
		success: function(response)
		{
			//alert(response);
			console.log(response);
			if(response != "") {
				
				$('#adv-table').html(response);
				Unloading();
			}
										
		}
	});	

}
