$(document).ready(function() {
	
	bank_reco_all_entries_report();
	bank_reco_cleared_entries_report();
	bank_reco_uncleared_entries_report();
});

function bank_reco_all_entries_report() 
{
	//alert('hello');
	var date=$("#rep_date").val();
	var b_l_id=$("#b_l_id").val(); 
	//alert(emp_id);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/bank_reconsilation/',
		data: { mode : "bank_reco_all_entries_report",date:date,b_l_id:b_l_id },
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			$('#data_table').html(response);
			Unloading();
								
		}
	});	
	
}

function bank_reco_cleared_entries_report() 
{
	//alert('hello');
	var date=$("#rep_date").val();
	//alert(emp_id);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/bank_reconsilation/',
		data: { mode : "bank_reco_cleared_entries_report",date:date },
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			$('#data_table_for_cleared').html(response);
			Unloading();
								
		}
	});	
	
}

function bank_reco_uncleared_entries_report() 
{
	//alert('hello');
	var date=$("#rep_date").val();
	//alert(emp_id);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/bank_reconsilation/',
		data: { mode : "bank_reco_uncleared_entries_report",date:date },
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			$('#data_table_for_uncleared').html(response);
			Unloading();
								
		}
	});	
	
}

function clear_entry(general_book_id){
	var r= confirm(" Are you sure , you want to Clear this Entry ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/bank_reconsilation/',
			data: { mode : "clear_entry"},
			success: function(response)
			{
				$("#ModalClearEntry").modal("show");
				$("#general_book_id").val(general_book_id);
				Unloading();				
			}
		});
	}
}

function clear_entry_with_date(){
	var clear_date = $("#clear_date").val();
	var general_book_id = $("#general_book_id").val();
	var clear_full_voucher = $('#clear_full_voucher').val();
	//alert(clear_full_voucher);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/bank_reconsilation/',
		data: { mode : "update_clear_entry",clear_date:clear_date,general_book_id:general_book_id,clear_full_voucher:clear_full_voucher},
		success: function(response)
		{
			//alert(response);
			if(response.trim() == '1') {
				toastr.success("ENTRY CLEARED SUCCESSFULLY", "SUCCESS");
				bank_reco_all_entries_report();
				bank_reco_uncleared_entries_report();
				$('#ModalClearEntry').modal('toggle');
				$("#general_book_id").val('');
				//$("#clear_date").val('');	
				Unloading();
			}			
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				$("#general_book_id").val('');
				Unloading();
			}
		}
	});
}