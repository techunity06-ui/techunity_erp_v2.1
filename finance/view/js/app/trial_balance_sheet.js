$(document).ready(function() {
	
	var mode = $('#r_mode').val();
	
	if(mode=='group_report')
	{

		load_all_ledger_group();
	}
	else
	{
		load_all_ledger();
	}
});


function load_all_ledger(){
	
	//alert('hiii');
	var date=$("#rep_date").val();
	//var showledger_id=$("#showledger_id").val();
	//alert(date);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/trial_balance_sheet/',
		data: { mode : "fetch_all_ledger",date:date },
		success: function(response)
		{
			//alert(response);
			$('#all_ledger').html(response);
			Unloading();
								
		}
	});
	
	
}


function load_all_ledger_group(){
	
	var date=$("#rep_date").val();
	//var showledger_id=$("#showledger_id").val();
	//alert(date);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/trial_balance_sheet/',
		data: { mode : "fetch_all_ledger_group",date:date,show_details :false },
		success: function(response)
		{
			//alert(response);
			$('#all_ledger').html(response);
			hide_details();
			Unloading();
								
		}
	});
	
	
}

Mousetrap.bind({
    'shift+v': show_details
});
Mousetrap.bind({
    'shift+c': hide_details
}); 

function show_details() {
    $(".descripc").show();
//    var date=$('#rep_date').val();
//	Loading();
//	$.ajax({
//		type: "POST",
//		url: root_domain+finance_root_domain+'app/balance_sheet/',
//		data: { mode : "load_balance_sheet",show_details :true, date : date },
//		success: function(response){
//				 $('#balance_sheet_id').html(response);
//                                 $(".descripc").show();
//                                 get_pl_value();
//				 Unloading();
//			}
//			
//	});
}
function hide_details() {
    $(".descripc").hide();
}