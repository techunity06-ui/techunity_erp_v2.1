$(document).ready(function() {
	
	load_all_ledger();
});


function load_all_ledger(){
	
	var date=$("#rep_date").val();
	var tds_cat = $("#tds_cat").val();
	//alert(tds_cat);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/tds_report/',
		data: { mode : "fetch_all_ledger",date:date,tds_cat:tds_cat },
		success: function(response)
		{
			//alert(response);
			$('#all_ledger').html(response);
			Unloading();
								
		}
	});
	
	
}

