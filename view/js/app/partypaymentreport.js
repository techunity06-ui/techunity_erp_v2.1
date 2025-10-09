$(document).ready(function() {
		reload_data();
});
function reload_data()
{
	generate_report();
}

function generate_report() 
{
	Loading();
	var ledger_id = $("#companyid").val();
	$.ajax({
		type: "POST",
		url: root_domain + 'app/partypaymentreport/',
		data: { mode : "generate_report", ledger_id :  ledger_id},
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
function print_detail() 
{
	Loading();
	var comapny_id=$("#company_id").val();
	var end_date=$("#to_date").val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/partydetailpaymentreport/',
		data: { mode : "generate_report", comapny_id :  comapny_id,end_date :  end_date},
		success: function(response)
		{
			//console.log(response);
			if(response != "") {
				Unloading();
				PrintMe1(response);
				$('#head_logo').show();
			}
		}
	});	

}

function view_history(id)
{
	Loading();		
	var start_date=$("#from_date").val();
	var end_date=$("#to_date").val();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/partywisepaymentreport/',
		data: { mode : "view_history", custid :id,start_date :  start_date,end_date :  end_date},
		success: function(response)
		{
			//console.log(response);
			if(response != "") {				
				$('#product_detail_data').html(response);
				$('#ModalEditAccount').modal();
				Unloading();
			}
										
		}
	});	
}
