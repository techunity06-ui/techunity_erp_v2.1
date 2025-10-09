//var datatable;
$(document).ready(function() {
	load_datatable();
	// validate the comment form when it is submitted        
	$('#paid_amount').change(function(){
		$('#total_paid_amount').val(this.value);
		calculate_total_used();
	});
	$('#full_payment_checkbox').change(function() {
		if($(this).is(':checked'))
		{
				var total=0;
				var input_due_amount=document.getElementsByName('due_amount[]');
				var input_paid_amount=document.getElementsByName('bill_paid_amount[]');
				var cnt=input_due_amount.length;
				for(var i=0;i<cnt;i++)
				{	
					input_paid_amount[i].value=input_due_amount[i].value;
					total=total+parseFloat(input_due_amount[i].value);
				}
				$('#paid_amount').val(total);
				$('#total_paid_amount').val(total);
				calculate_total_used();
		}
		else
		{
			$('#paid_amount').val(0);
			$('#total_paid_amount').val(0);
		}
		calculate_total_used();
    });
	$('#tax_deducted_flag').change(function() {
		if($(this).is(':checked'))
		{
			$('.tax_deduct').removeClass('hidden');
		}
		else
		{
			$('.tax_deduct').addClass('hidden');
			var input_tax_amount=document.getElementsByName('tax_amount[]');
			var cnt=input_tax_amount.length;
			for(var i=0;i<cnt;i++)
			{	
				input_tax_amount[i].value='';
			}
		}
	})	

// validate vendor add form on keyup and submit
$("#complainpayment_add").validate({
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
function check_due_and_use_amount(record)
{
	var due_amount=parseFloat($('#due_amount'+record).val());
	var bill_paid_amount=parseFloat($('#bill_paid_amount'+record).val());
	var tax_amount=($('#tax_deducted_flag').is(':checked') && $('#tax_amount'+record).val()?parseFloat($('#tax_amount'+record).val()):0);
	bill_paid_amount=bill_paid_amount+tax_amount;
	if(bill_paid_amount=="")
	{
		toastr.warning("ENTER BILL AMOUNT","WARNING");
	}
	if(bill_paid_amount>due_amount)
	{
		toastr.warning("PAYMENT AMOUNT IS NOT BIGGER THAN DUE AMOUNT","WARNING");
		$('#bill_paid_amount'+record).focus();
		return false;
	}
	calculate_total_used();
}
function calculate_total_used()
{
	var input_paid_amount=document.getElementsByName('bill_paid_amount[]');
	var cnt=input_paid_amount.length;
	var total_used=0;
	for(var i=0;i<cnt;i++)
	{	
		if(input_paid_amount[i].value!="")
		{
			total_used=total_used+parseFloat(input_paid_amount[i].value);
		}
	}
	$('#total_used_payment').val(total_used);
	//$('#total_paid_amount').val($('#paid_amount').val());
	calculate_summary();
}
function calculate_summary()
{
	
	var excess_amount=0;
	var total_used=($('#total_used_payment').val()?parseFloat($('#total_used_payment').val()):0);
	var total_paid=($('#total_paid_amount').val()?parseFloat($('#total_paid_amount').val()):0);
	excess_amount=total_paid-total_used;
	$('#total_excess_payment').val(excess_amount);
	if(total_paid<total_used)
	{
		toastr.warning("USED AMOUNT IS NOT BIGGER THAN PAID AMOUNT","WARNING");
		//$('#paid_amount').focus();
		return false;
	}
	return true;
}
$("#complainpayment_add").on('submit',function(e) {
	var form = this;	
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#complainpayment_add").valid()) {
		return false;
	}
	if(parseInt($('#total_used_payment').val())===0)
	{
		toastr.warning("ENTER USE AMOUNT IN INVOICE/INCOME","WARNING");
		return false;
	}
	if(!calculate_summary())
	{
		return false;
	}
	var excess_amount=parseFloat($('#total_excess_payment').val());
	if(excess_amount>0)
	{	
		var r= confirm("Would you like to store credits amount of "+excess_amount+" as over payment to this Party?");
		if(!r) {
			return false;
		}
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	
	var form_data=new FormData(this);	
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+'app/complainpayment/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				toastr.success("Complaint Paymet Done Successfully", "SUCCESS");
				Unloading();
				
			}
			
			$('#complainpayment_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

		
});
///////////Credit amount use
$("#payment_credit_amount").on('submit',function(e) {
	var form = this;	
	e.preventDefault();
	e.stopPropagation();	
	if(!calculate_summary())
	{
		return false;
	}
	var excess_amount=parseFloat($('#total_excess_payment').val());
	if(excess_amount>0)
	{	
		var r= confirm("Would you like to store credits amount of "+excess_amount+" as over payment to this Party?");
		if(!r) {
			return false;
		}
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	
	var form_data=new FormData(this);	
	console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+'app/complainpayment/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			$('#modal_use_credit_amount').modal('hide');
			Unloading();
			load_datatable();
			//console.log(response);
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
				url: root_domain+'app/complainpayment/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response)
					if(response.trim() == "1") {
						toastr.success("Payment DELETE SUCCESSFULLY", "SUCCESS");
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
function load_billdata(partyid) {
Loading();
var emode=$('#complainpayment_add #mode').val();
	$.ajax({
	type: "POST",
	url: root_domain+'app/complainpayment/',
	data: { mode : "load_data", partyid :partyid,emode:emode,eid:$('#complainpayment_add #eid').val(),tax_deduct:$('#tax_deducted_flag').is(':checked')},
	success: function(data){	
		Unloading();
		var obj=jQuery.parseJSON(data);
		if(obj.status==1)
		{			
			$('#purchase_data tbody').html(obj.data);
			if(emode=="Add")
			{
				$('.fullpayment_label').html('Pay full amount ('+obj.total+')');
				$('.chkfull_payment').removeClass('hidden');
				$('#full_payment_checkbox').prop('checked',false);
			}
		}
		else
		{	
			$('.chkfull_payment').addClass('hidden');
			$('#purchase_data tbody').html('');
		}
		//$('#paid_amount').val('');
		calculate_total_used();
	}
	});
}
function load_data(val) {
	
	$.ajax({
	type: "POST",
	url: root_domain+'app/complainpayment/',
	data: { mode : "load_totaldata", purchasebill_id : val},
	success: function(data){
				//console.log(data);
				var data = JSON.parse(data);
				var due=(data.g_total)-(data.paid_amount);
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
			url: root_domain+'app/complainpayment/',
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

function get_chequeno(acc_id,refcontroll)
{
	if($("#paymentmodeid").val()==2)
	{
		Loading();
		editReq = $.ajax({
			type: "POST",
			url: root_domain+'app/complainpayment/',
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

function reload_data()
{
	load_datatable();
}	
function load_datatable(){
	
	var date=$("#rep_date").val();
	datatable = $("#dynamic-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bServerSide" : true,
			"bDestroy" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO Receipt ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/complainpayment/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },{ "name": "date", "value": date } );
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}
function use_credits(payment_mstid,partyid,credit_amount)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/complainpayment/',
			data: { mode : "load_data", payment_mstid :payment_mstid,emode:"credit",partyid:partyid },
			success: function(response)
			{
				console.log(response);
				var obj=jQuery.parseJSON(response);
				$('#modal_use_credit_amount').modal();
				$('#credit_data_table tbody').html(obj.data);
				$('#total_paid_amount').val(credit_amount);
				$('#payment_mstid').val(payment_mstid);
				Unloading();
			}
		});	
}

function get_all_complain(customer)
{
	//alert(customer);
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/complainpayment/',
			data: { mode : "get_complain", customer :customer },
			success: function(response)
			{
				$('#comp_id').html(response);
				//alert(response);
				Unloading();
			}
		});	
}

function getPendingPayment(complaint)
{
	//alert(complaint);
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/complainpayment/',
			data: { mode : "get_complain_pending_payment", complaint :complaint },
			success: function(response)
			{
				//$('#comp_id').html(response);
				//alert(response);
				$('#due_amount').val(response);
				Unloading();
			}
		});	
}

function get_final_comp_payment()
{
	var due_amount=Number($('#due_amount').val());
	var paid_amount=Number($('#paid_amount').val());
	
	if(paid_amount>due_amount)
	{
		$('#err_amt').show();
		document.getElementById('save').disabled=true;
		//alert('greater');
	}
	else
	{
		$('#err_amt').hide();
		document.getElementById('save').disabled=false;
		//alert('lower');
	}
}