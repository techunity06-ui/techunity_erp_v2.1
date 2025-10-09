//var datatable;
$(document).ready(function() {
	load_datatable();
	show_data();
	//get_amount();
	/*$('#voucher_typeid').change(function(){
		var old_typeid=$('#old_v_typeid').val();
		if(this.value=="2" || old_typeid=="2")
		{
		if(this.value=="2")
		{
		$('#cust_id').attr('disabled','disabled');
		}
		else
		$('#cust_id').removeAttr('disabled','disabled');
		$('#old_v_typeid').val(this.value);
		$.ajax({
		cache:false,
		url: root_domain+'app/account_voucher/',
		type: "POST",
		data: {mode:"load_account",voucher_typeid:this.value},
		success: function(response)
		{
		var arr = jQuery.parseJSON(response);			
		$('#accountid').html(arr.data);
		
		},
		error: function(jqXHR, textStatus, errorThrown) {
		console.log(textStatus, errorThrown);
		}
		});
		}
	});*/
	// validate the comment form when it is submitted        
	// validate vendor add form on keyup and submit
	$("#voucher_add").validate({
		rules: {
			voucher_typeid:{
				required:true
			},
			voucher_date: {
				required: true			
			},
			voucher_no:{
				required: true			
			}
			
		},
		messages: {
			voucher_typeid:{
				required:"Enter Voucher type"
			},
			voucher_date: {
				required: "Enter Voucher Date"
			},
			voucher_no:{
				required: "Enter Voucher Number"
			}
		}
	}); 
});
function submit_estimate()
{
	$("#save_new").val(1)
	//$("#voucher_add").submit();
}
$("#voucher_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#voucher_add").valid()) {
		return false;
	}
	if(parseFloat($('#debit_total').val())==0 && parseFloat($('#credit_total').val())==0) {
		toastr.warning("Accounts entry Required","WARNING");
		return false;
	}
	else if($('#debit_total').val()!=$('#credit_total').val()) {
		toastr.warning("Debit and Credit Amount not match","WARNING");
		$('#credit_total').focus()
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#save').prop("disabled",true);
	$('#saveprint').prop("disabled",true);
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+'app/account_voucher/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				Unloading();
				toastr.success("VOUCHER ADDED SUCCESSFULLY", "SUCCESS");
				if ($("#save_new").val() == '1'){
					location.reload();
				}
				else{
					window.location=root_domain+'account-voucher-list';
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
				toastr.success("VOUCHER UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				if ($("#save_new").val() == '1') {	
					window.location=root_domain+'account-voucher-entry';
				}
				else {
					window.location=root_domain+'account-voucher-list';
				}
			}	
			$('#save').prop("disabled",false);
			$('#saveprint').prop("disabled",false);	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_voucher(id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/account_voucher/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				console.log(response)
				if(response.trim() == "1") {
					toastr.success("VOUCHER DELETE SUCCESSFULLY", "SUCCESS");
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

function get_amount()
{	
	var id=parseInt($('#fieldcnt').val())+1;
	var tax_type=$('input[name=tax_inclusive]:checked').val();
	if($("#expense_amount").val()!="")
	{
		/*if(!$('input[name=tax_inclusive]:checked' ).val())
			{
			toastr.warning("Enter Amount or Select Amount Option", "ERROR");
			return false;
		}*/
		var amount=parseFloat($("#expense_amount").val()).toFixed(2);
		$("#expense_gtotal").val((amount));
		if($("#formulaid").val()!="" && tax_type=="exclusive")//tax calculation
		{
			$.ajax({
				type: "POST",
				url: root_domain+'app/account_voucher/',
				data: { mode : "getproduct_amount",  amount :amount,formulaid:$("#formulaid").val()},
				success: function(response)
				{
					var obj=jQuery.parseJSON(response);
					$("#expense_gtotal").val(obj.total);
				}
			});
		}
	}
	else
	{
		$("#product_amount").val(0);
	}
	get_gtotal();
}
function get_gtotal()
{	
	//var id=parseInt($('#fieldcnt').val());
	var t=0;
	//var d=parseInt($('#discount').val());
	var credit_amount=(document.getElementsByName('credit_amount[]'));
	var debit_amount=(document.getElementsByName('debit_amount[]'));
	var cnt=credit_amount.length;
	var credit_total=0,debit_total=0,c_total=0;
	for(var i=0;i<cnt;i++)
	{	
		var credit=credit_amount[i].value;
		var debit=debit_amount[i].value;
		if(credit>0)
		credit_total=parseFloat(credit_total)+parseFloat(credit);
		if(debit>0)
		debit_total=parseFloat(debit_total)+parseFloat(debit);
	}
	$("#credit_total").val(parseFloat(credit_total).toFixed(2));
	$("#debit_total").val(parseFloat(debit_total).toFixed(2));
	
}
function add_field()
{
	if(!$("#type_id").val()) {		
		toastr.warning("Choose Type", "ERROR");
		$('#type_id').select2('focus');
		return false;
	}
	else if(!$("#l_id").val()) {		
		toastr.warning("Choose Ledger", "ERROR");
		$('#l_id').select2('focus');
		return false;
	}
	else if(!$("#input_amt").val() || parseFloat($("#input_amt").val())=='0') {		
		toastr.warning("Enter Input Amount", "ERROR");
		$('#input_amt').focus();
		return false;
	}
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/account_voucher/',
		data: { mode : "fieldadd",edit_id:$("#edit_id").val(),type_id:$("#type_id").val(),l_id:$("#l_id").val(),input_amt:$("#input_amt").val(),voucher_mstid:$("#eid").val()},
		success: function(response)
		{
			console.log(response);
			//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
			$("#type_id").select2("val","");
			$("#l_id").select2("val","");
			$("#input_amt").val("");
			$("#edit_id").val('');
			Unloading();
			$('#add_btn').html("Add");
			show_data()
		}
	});
}
function load_paymentmode(val) {
	$.ajax({
		type: "POST",
		url: root_domain+'app/account_voucher/',
		data: { mode : "paymentmode", paymentmodeid : val},
		success: function(response){
			//console.log(response);
			$('#product_list').append(response);
		}
	});
}

function reload_data()
{
	//datatable.fnReloadAjax();
	load_datatable();
}	
function load_datatable()
{
	var data=$('#payment_status').val();
	var date=$('#rep_date').val();
	datatable = $("#dynamic-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[-1, 10, 20, 30, 50], ["All", 10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+'app/account_voucher/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "report", "value": data },{ "name": "date", "value": date });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}
function load_estimateno(id)
{
	$.ajax({
		type: "POST",
		url: root_domain+'app/account_voucher/',
		data: { mode : "load_estimateno", typeid : id},
		success: function(data){
			//console.log(data);
			var no = jQuery.parseJSON(data);
			$('#estimate_no').val(no.estimateno);
		}
	});
}

function show_data()
{
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+'app/account_voucher/',
		data: { mode : "load_tempoutward",voucher_mstid:$("#eid").val() },
		success: function(data){
			//console.log(data);
			$('#sale_productdata').html(data);				
			get_amount();
			Unloading();
		}		
		
	});	
}

function edit_data(id)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/account_voucher/',
		data: { mode : "preedit", id:id },
		success: function(response)
		{
			//console.log(response)
			var data = jQuery.parseJSON(response);
			$("#type_id").select2("val",data.type_id);
			$("#l_id").select2("val",data.l_id);
			if(data.type_id=="1"){
				$("#input_amt").val(data.cr_amount);
			}
			else if(data.type_id=="2"){
				$("#input_amt").val(data.dr_amount);	
			}
			$("#edit_id").val(id);
			$('#add_btn').html("Update");
			Unloading();
		}
	});
}
function delete_data(id)
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/account_voucher/',
			data: { mode : "delete_data", eid:id },
			success: function(response)
			{
				console.log(response)
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_data();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}		
				Unloading();					
			}
		});	
	}
	
}
/**Payment Function***/
function get_chequeno(acc_id,refcontroll)
{
	if($("#paymentmodeid").val()==2)
	{
		Loading();
		editReq = $.ajax({
			type: "POST",
			url: root_domain+'app/purchasepayment/',
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
function get_opening_bal(acc_id,amt_text,amt_err)
{
	Loading();
	
	editReq = $.ajax({
		type: "POST",
		url: root_domain+'app/purchasepayment/',
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
			url: root_domain+'app/purchasepayment/',
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

function get_journal_debit(amt)
{
	//alert(amt);
	$('#journal_debit').val(amt);
}