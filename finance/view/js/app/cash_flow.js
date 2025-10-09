$(document).ready(function() {
	
	load_cashflow_by_month();
	load_cashinflow();
	load_cashoutflow();
});


function load_cashflow_by_month(){
	
	var date=$("#rep_date").val();

	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/cash_flow_report/',
		data: { mode : "fetch_cashflow_by_month",date:date },
		success: function(response)
		{
			//alert(response);
			$('#all_ledger').html(response);
			Unloading();
								
		}
	});
	
	
}

function load_cashinflow(){
	
	var date=$("#rep_date").val();
    var monthyear = $("#monthyear").val();
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/cash_flow_report/',
		data: { mode : "load_cashinflow",date:date,monthyear:monthyear },
		success: function(response)
		{
			//alert(response);
			$('#cashinflow_group').html(response);
			Unloading();
								
		}
	});
	
	
}

function load_cashoutflow(){
	
	var date=$("#rep_date").val();
	var monthyear = $("#monthyear").val();
	Loading();

	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/cash_flow_report/',
		data: { mode : "load_cashoutflow",date:date,monthyear:monthyear },
		success: function(response)
		{
			//alert(response);
			$('#cashoutflow_group').html(response);
			Unloading();
								
		}
	});
	
	
}

