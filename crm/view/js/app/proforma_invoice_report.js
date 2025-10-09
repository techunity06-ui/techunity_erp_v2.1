$(document).ready(function() {
	/* cust_so_detail_report();*/
});

function cust_so_detail_report() {
	var rep_date = $("#rep_date").val();
	var proforma_type = $("#proforma_type").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/proforma_invoice_report/',
		data: { mode : "cust_so_detail_report", date: rep_date, proforma_type: proforma_type},		
	   success: function(response)
		{
			if(response != "") {
				$('#proforma_order_detail').html(response);
				Unloading();
			}
		}
	});	
}