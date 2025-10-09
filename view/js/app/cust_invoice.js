$(document).ready(function() {
    //reload_data();
});
function reload_data()
{
	generate_report();
}

function generate_report() {
	var date = $("#rep_date").val();
	var cust_id = $("#cust_id").val();
        var bill_type = $('input[name=bill_type]:Checked').val();
	if(cust_id !== "")
	{
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain + 'app/report_cust_invoice/',
		data: { mode : "generate_report", date : date,cust_id:cust_id, bill_type : bill_type},
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
}

function load_customer(){	
        var cust_type =$('input[name=cust_type]:Checked').val();
        
        $.ajax({
		type: "POST",
		url: root_domain + 'app/report_cust_invoice/',
		data: { mode : "load_customer",  cust_type : cust_type},
		success: function(responce){
			//console.log(responce);
			$('#cust_id').html(responce);
			$("#cust_id").select2({width: '100%'});
		}
	});
	
}