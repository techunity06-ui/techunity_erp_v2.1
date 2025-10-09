$(document).ready(function() {
	
	load_all_ledger();
});


function load_all_ledger(){
	
	var date=$("#rep_date").val();

	//alert(date);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/tcs_report/',
		data: { mode : "fetch_all_tcs_entry",date:date },
		success: function(response)
		{
			//alert(response);
			$('#all_tcs').html(response);
			Unloading();
								
		}
	});
	
	
}

