function reload_data()
{
	generate_report();
}

function generate_report() 
{
	Loading(true);
	var comapny_id=$("#companyid").val();
	var end_date=$("#to_date").val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/partydetailpaymentreport/',
		data: { mode : "generate_report", comapny_id :  comapny_id,end_date :  end_date},
		success: function(response)
		{
			//console.log(response);
			if(response != "") {
				$('#adv-table').html(response);
				$('#head_logo').hide();
				Unloading();
			}
										
		}
	});	

}