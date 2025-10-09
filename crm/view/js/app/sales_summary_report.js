$(document).ready(function() {
		reload_data();
});
function reload_data()
{
	generate_report();
}

function generate_report() 
{
	$('.so-details-section').addClass('hidden');
	var date=$("#rep_date").val();
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain+'app/sales_summary_report/',
		data: { mode : "generate_report",date :  date,},
		success: function(response)
		{
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
										
		}
	});	
	
}

function generate_detail_report(month) 
{
	var date=$("#rep_date").val();
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain+'app/sales_summary_report/',
		data: { mode : "generate_detail_report",date :  date,month:month},
		success: function(response)
		{
			
			if(response != "") {
				$('.so-details-section').removeClass('hidden');
				$('#adv-detail-table').html(response);
				Unloading();
			}
										
		}
	});	
	
}