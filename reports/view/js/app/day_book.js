$(document).ready(function() {
		reload_data();
});
function reload_data()
{
	generate_report();
}

function generate_report() 
{
	var date=$("#rep_date").val();
	var prod_id=$("#prod_id").val();
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+report_domain+'app/day_book/',
		data: { mode : "generate_report", date :  date},
		success: function(response)
		{
			//console.log(response);
			if(response != "") {
				$('#adv-table').html(response);

				Unloading();
				 $([document.documentElement, document.body]).animate({
      				  scrollTop: $("#adv-table").offset().top
    			}, 1000);
			}
										
		}
	});	
	
}