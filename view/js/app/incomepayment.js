//var datatable;
$(document).ready(function() {
		datatable = $("#dynamic-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bServerSide" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO Receipt ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/incomepayment/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" } );
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
	// validate the comment form when it is submitted        

// validate vendor add form on keyup and submit
$("#incomepayment_add").validate({
	rules: {
		vender_id: {
			required: true			
		},
		bill_no: {
			required: true			
		},
		paid_amount: {
			required: true
		},
		pur_acc_id:{
			required: true
		}
	},
	messages: {
		vender_id: {
			required: "Choose Vendor"
		},
		bill_no: {
			required: "Choose Bill number"
		},
		paid_amount: {
			required: "Paid amount required",
			max:"Not enter Maximum than due payment"
		},
		pur_acc_id:{
			required: "Choose Bank Account"
		}

	
	}
}); 

});

$("#incomepayment_add").on('submit',function(e) {
	var form = this;	
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#incomepayment_add").valid()) {
		return false;
	}
	if(parseInt($('#paid_amount').val())>parseInt($('#max_paid_amount').val()))
	{
		toastr.warning("Not Enter Maximum than Balance", "ERROR");
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	
	var form_data=new FormData(this);	
	console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+'app/incomepayment/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				Unloading();
				toastr.success("PAYMENT ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+'incomepayment_list';
			}
			if(arr.msg == '2') {
				Unloading();
				toastr.success("PAYMENT ADDED SUCCESSFULLY", "SUCCESS");
				if (typeof arr.eid != 'undefined')
				{
					window.location=root_domain+'income_list/'+arr.eid;
				}
				else
				{
					window.location=root_domain+'income_list';
				}
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(arr.msg == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			else if(arr.msg == 'update')
			{	
				toastr.success("PAYMENT UPDATED SUCCESSFULLY", "SUCCESS");		
			
				Unloading();
				if (typeof arr.eid != 'undefined')
				{
					window.location=root_domain+'incomepayment_list/'+arr.eid;
				}
				else
				{
					window.location=root_domain+'income_list';
				}
			//	toastr.success("SLIDER UPDATED SUCCESSFULLY", "SUCCESS");		
			}
			$('#incomepayment_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_payment(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/incomepayment/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response)
					if(response.trim() == "1") {
						toastr.success("PAYMENT DELETE SUCCESSFULLY", "SUCCESS");
						datatable.fnReloadAjax();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}
function load_billdata(val) {
//	console.log(val);
	var payment_type=$('input[name=payment_type]:Checked').val();
 	$.ajax({
	type: "POST",
	url: root_domain+'app/incomepayment/',
	data: { mode : "load_data", cust_id : val,payment_type:payment_type},
	success: function(data){
		console.log(data);
		if(payment_type==1)
		{
			$('#invoice_id').html(data);
		}
		else
		{
			$('#due_payment').val(parseInt(data));
		}
	}
	});
}
function load_data(val) {
	
	$.ajax({
	type: "POST",
	url: root_domain+'app/incomepayment/',
	data: { mode : "load_totaldata", invoice_id : val},
	success: function(data){
				//console.log(data);
				var data = JSON.parse(data);
				var due=parseFloat((data.g_total)-(data.paid_amount)).toFixed(2);
				$('#due_payment').val(due);
				var payment_type=$('input[name=payment_type]:Checked').val();
				if(payment_type==1)
				{
					$('#paid_amount').attr('max',due);
				}
	}
	});
}
 function get_opening_bal(acc_id,amt_text,amt_err)
{
		Loading();
	
		editReq = $.ajax({
			type: "POST",
			url: root_domain+'app/incomepayment/',
			data: { mode : "get_opn_bal", acc_id :acc_id },
			success: function(response)
			{
				//console.log(response);
				response=response.trim();
				$('.amtbalance').css('display','');
				$('#'+amt_text).val(response);
				$('#'+amt_err).html('Balance '+response);
				Unloading();
			}
		});	
}
function get_cash_opening_bal(acc_id,amt_text,amt_err)
{
	$('.amtbalance').css('display','none');
	if(acc_id==1)
	{
		Loading();
		editReq = $.ajax({
			type: "POST",
			url: root_domain+'app/incomepayment/',
			data: { mode : "get_opn_bal", acc_id :'0' },
			success: function(response)
			{
				//console.log(response);
				response=response.trim();
				$('.amtbalance').css('display','');
				$('#'+amt_text).val(response);
				$('#'+amt_err).html('Balance '+response);
				Unloading();
			}
		});	
	}
}
function get_chequeno(acc_id,refcontroll)
{
	if($("#paymentmodeid").val()==2)
	{
		Loading();
		editReq = $.ajax({
			type: "POST",
			url: root_domain+'app/incomepayment/',
			data: { mode : "get_chequeno", acc_id :acc_id },
			success: function(response)
			{
				//console.log(response);
				response=response.trim();
				if(response!="")
				{
				$('#'+refcontroll).val(parseInt(response)+parseInt(1));
				}
				Unloading();
			}
		});	
	}
}
function load_debit_note(vendor_id)
{
	Loading()
	$("#debitnote_id").html('')
	$("#debitnote_id").select2('val','')
	$("#debit_amount").val('')
	$("#debitnote_amount").val('')
			
	$.ajax({
		type: "POST",
		url: root_domain+'app/incomepayment/',
		data: { mode : "load_debit_note", vendor_id :vendor_id },
		success: function(response)
		{
				//console.log(response);
				$("#debitnote_id").html(response)
				$("#note_payment").css("display","")
				Unloading();
		}
	});

}
function load_debit_note_amount()
{
	Loading()
	var purchasedebit_note=$("#debitnote_id").val()
	$.ajax({
		type: "POST",
		url: root_domain+'app/incomepayment/',
		data: { mode : "load_debit_note_amount", purchasedebit_note :purchasedebit_note },
		success: function(response)
		{
				//console.log(response);
				response=parseInt(response)
				$("#debit_amount").val(response)
				$("#debitnote_amount").val(response)
				var payment_type=$('input[name=payment_type]:Checked').val();
				if(payment_type==1)
				{
				add_debitamount($("#due_payment").val())
				}
				Unloading();
		}
	});
}
function add_debitamount(due)
{
	$('#paid_amount').attr('max',parseInt(due)-($("#debitnote_amount").val()));
					
}