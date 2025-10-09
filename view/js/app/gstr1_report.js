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
	var date=$("#rep_date").val();
	var typeid=$("#type_id").val();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/gstr1_report/',
		data: { mode : "generate_report",date :  date},
		success: function(response)
		{
			//console.log(response);
			var resp=jQuery.parseJSON(response);
			if(response != "") {
				
				$('#b2b_data').html(resp.b2b_data);
				$('#b2cs_data').html(resp.b2cs_data);
				Unloading();
			}
										
		}
	});	

}
