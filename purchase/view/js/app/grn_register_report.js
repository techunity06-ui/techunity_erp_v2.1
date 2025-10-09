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
	var date=$("#rep_date").val();
	var po_no= $("#po_no").val();
	var product_id = $("#product_id").val();
	$.ajax({
		type: "POST",
		url: root_domain +purchase_domain+'app/grn_register_report/',
		data: { mode : "generate_report", ledger_id :  ledger_id,date:date,po_no:po_no,product_id:product_id},
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
		url: root_domain+purchase_domain+'app/grn_register_report/',
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
