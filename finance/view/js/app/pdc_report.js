$(document).ready(function() {
	  	generate_report();
	  	pdc_regularized();
		
});
function reload_data()
{
	generate_report();
	pdc_regularized();
	
}
//,typeid:typeid
function generate_report() 
{
	Loading();
	//alert("hii");
	var date=$("#rep_date").val();
	//alert(date);
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/pdc_report/',
		data: { mode : "generate_report",date :  date},
		success: function(response)
		{
			//alert(response);
			console.log(response);
			if(response != "") {
				
				$('#adv-table').html(response);
				Unloading();
			}
										
		}
	});	

}

function pdc_regularized() 
{
	Loading();
	//alert("hii");
	var date=$("#rep_date").val();
	//alert(date);
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/pdc_report/',
		data: { mode : "pdc_regularized_report",date :  date},
		success: function(response)
		{
			//alert(response);
			console.log(response);
			if(response != "") {
				
				$('#pdc-regularized-table').html(response);
				Unloading();
			}
										
		}
	});	

}


function check_all_change()
{
	//alert(check);
	if($('#checkAll').is(':checked'))
	{
		//alert('hii');
		$(".pdc_checkbox").prop('checked',true);
	}
	else
	{
		$(".pdc_checkbox").prop('checked',false);
	}
}

function regularize_pdc(){
	var receipt_id=new Array();
	$(".pdc_checkbox").each(function() {
		if($(this).is(':checked')){
			receipt_id.push($(this).val());
		}		
	});

	if(receipt_id == '')
	{
		toastr.warning("Please check atleast one ", "ERROR");
		return false;
	}

	var r= confirm(" Are you sure , you want to regularize this entry ?");
	
	if(r) {

		$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/pdc_report/',
			data: { mode : "entry_regularize",receipt_id :  receipt_id},
			success: function(response)
			{
				if(response == '1') {
					toastr.success("ENTRY REGULARIZE SUCCESSFULLY", "SUCCESS");
					generate_report();
					Unloading();
				}
				else if(response == '0') {
					toastr.warning("SOMETHING WRONG", "ERROR")
					Unloading();				
				}
											
			}
		});
	}
	
}