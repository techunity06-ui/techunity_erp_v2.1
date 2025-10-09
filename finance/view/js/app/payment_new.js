//var datatable;
$(document).ready(function() {
	delete_all_payment_entry();
	load_datatable();
	$('#billby_bill_link').hide();
	$('.billbybill_td').hide();
	jQuery('.numbersOnly').keyup(function () { 
	    this.value = this.value.replace(/[^0-9\.]/g,'');
	});

	load_payment_entry_datatable();
	var mode = $('#mode').val();
	get_data_description($('#gst_nature').val());

	if(mode=='Edit')
	{
		//alert($('#is_pdc').val());
		check_pay_mode($('#payment_mode_id').val());
		isreferencerequire($('#payment_mode_id').val());
		get_pdc_date($('#is_pdc').val());
		currency_change();
	}

	$("#purchasepayment_add").validate({
		rules: {
			bill_no: {
				required: true			
			},
			paid_amount: {
				required: true
			},
			pur_acc_id:{
				required: true
			},
			paymentmodeid:{
				required: true
			}
			
		},
		messages: {
			bill_no: {
				required: "Choose Bill number"
			},
			paid_amount: {
				required: "Paid amount required",
				max:"Not enter Maximum than due payment"
			},
			pur_acc_id:{
				required: "Choose Bank Account"
			},
			paymentmodeid:{
				required: "Choose Party"
			}
		}
	}); 
});
$("#purchasepayment_add").on('submit',function(e) {
	var form = this;	
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#purchasepayment_add").valid()) {
		return false;
	}

	var mode = $("#mode").val();
	if(mode == 'Edit'){
		var paid_amount = $("#paid_amount").val();
		var amount_used_payment = $("#paid_amount").val();
		if(amount_used_payment > paid_amount){
			toastr.warning("Maximum amount should be "+paid_amount, "ERROR");
		}
	}
	
	if ($('#currency_enable').is(":checked"))
	{
		if($("#currency_id").val()==""){
			toastr.warning("Select Currency", "ERROR")
			$("#currency_id").focus();
			return false;
		}
		if($("#currency_rate").val()==""){
			toastr.warning("Enter Currency Rate", "ERROR")
			$("#currency_rate").focus();
			return false;
		}
	}
	if(($("#is_pdc").val()==1) && ($("#pdc_date").val()=='') ){
		toastr.warning("Select PDC Date", "ERROR");
		$("#pdc_date").focus();
		return false;
	}
	var paid_amt = get_paid_amt();
	//alert($("#paid_amount").val());
	if(parseFloat($("#paid_amount").val()) != paid_amt){
		toastr.warning("Credit and Debit amount is not matching", "ERROR");
		$("#paid_amount").focus();
		return false;
	}
	var d=$('input[name=payment_type]:Checked').val();
	
	if(0>parseInt($('#amount_in_excess').val()))
	{
		toastr.warning("Not Enter excess Amount Less Then 0", "ERROR");
		return false;
	}
	
	if(parseInt($('#paid_amount').val())>parseInt($('#max_paid_amount').val()))
	{
		toastr.warning("Not Enter Maximum than Balance", "ERROR");
		return false;
	}

	// if($("#payment_mode_id").val() != 10 && $('#cheque_dtl')!=''){
	// 	toastr.warning("Please insert reference number", "ERROR");
	// 	return false;
	// }
	
	// if(parseInt($('#paid_amount').val())!=parseInt($('#full_paid').val()))
	// {
	// 	toastr.warning("Balance Doesn't Match", "ERROR");
	// 	return false;
	// }
	// if($('#paid_typeid').val()==$('#full_paid_type').val())
	// {
	// 	toastr.warning("Balance Doesn't Match", "ERROR");
	// 	return false;
	// }
	
	form.submitted = true;	
	Loading(true);	
	//$(this).attr("disabled","disabled");		
	$('#save').prop("disabled",true);		
		
	var form_data=new FormData(this);	
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+ finance_root_domain +'app/payment_new/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//alert(response);
			console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				Unloading();
				toastr.success("PAYMENT ADDED SUCCESSFULLY", "SUCCESS");
				if (typeof arr.cheque_genid != 'undefined')
				{
					window.location=root_domain+'cheque_app/generate-cheque/'+arr.cheque_genid;
				}
				else
				{
					window.location=root_domain+finance_root_domain+'payment_list';
				}
			}
			if(arr.msg == '2') {
				Unloading();
				toastr.success("PAYMENT ADDED SUCCESSFULLY", "SUCCESS");
				if (typeof arr.eid != 'undefined') {
					window.location=root_domain+'purchase_list/'+arr.eid;
				}
				else {
					window.location=root_domain+'purchase_list';
				}
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(arr.msg == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			else if(arr.msg == 'update') {	
				toastr.success("Payment UPDATED SUCCESSFULLY", "SUCCESS");	
				Unloading();
				if (typeof arr.eid != 'undefined') {
					window.location=root_domain+finance_root_domain+'payment_list/'+arr.eid;
				}
				else {
					window.location=root_domain+'purchase_list';
				}
				//	toastr.success("SLIDER UPDATED SUCCESSFULLY", "SUCCESS");		
			}
			$('#save').prop("disabled",false);		
			$('#purchasepayment_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function showledger(){
	branch_id = $('#branch_id').val();
	if(!branch_id){
		toastr.warning("Choose Branch!!!", "ERROR");
		$('#branch_id').select2('focus');
		return false;
	}
	$.fn.modal.Constructor.prototype.enforceFocus = function() {};
	$('#modal-add-ledger').modal('show');
	get_opening_balance('0');
	$("#ledger_add_type").val('payment');
	$("#ledger_name").focus();
}

function get_paid_amt(){
	var receipt_id = $("#receiptid").val();
	var paid_amt = "";
	var gst_nature = $("#gst_nature").val();
	$.ajax({
		async: false,
		type: "POST",
		url: root_domain+finance_root_domain+'app/payment_new/',
		data: { mode : "paid_amt",receipt_id:receipt_id,gst_nature:gst_nature },
		success: function(response)
		{
			//alert(response);
			//var data = jQuery.parseJSON(response);
			paid_amt = response;
			//alert(paid_amt);
		}
	});
	return (paid_amt);
}
//Start added by Dhruv
function focus_paid_amt_billbybill(check){
	if($("#paid_amount").val() == ''){
		$("#enable_billby_bill_show").val("no");
		toastr.warning("PLEASE INSERT PAID AMOUNT FIRST", "ERROR")
		$("#entry_type").select2("val","");
		$("#paid_amount").focus();
		return false;
	}else{
		get_bill_show(check,'purchase','entry_amount','vender_id');
	}
}
function get_symbol(){

	$(".sp_cr").remove();
	$(".currency_icon").html('');

	var symbl = $("#currency_id").find(':selected').attr("data-currency-symbol");
	var textt = " (<i class='"+symbl+"'></i>)"; 
	$(".currency_icon").each(function() {
		$(this).append(textt);		
	});
}
// function focus_paid_amt(){
// 	var tamount = 0;
// 	if($("#paid_amount").val() == ''){
// 		$("#paymentmodeid").select2("val","0");
// 		toastr.warning("PLEASE INSERT PAID AMOUNT FIRST", "ERROR")
// 		$("#paid_amount").focus();
// 		return false;
// 	}

// 	$(".amount_p").each(function() {
// 		tamount = Number(tamount) + Number($(this).text());
// 	});
// 	$("#entry_amount").val(Number($("#paid_amount").val())-Number(tamount));
	
// }

function focus_paid_amt(){

	var tamount = 0;

	if($("#paid_amount").val() == 0){
		$("#receiver_ledger").select2("val","0");
		toastr.warning("PLEASE INSERT PAID AMOUNT FIRST", "ERROR")
		$("#paid_amount").focus();
		$("#entry_type").select2("val","");
		return false;
	}

	var payment_type = $("#payment_type_reciept_pmt_trn").val();
	var paid_amount = $("#paid_amount").val();
	var receiptid = $("#receiptid").val();

	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/payment_new/',
		data: { mode : "get_total_receipt_payment", 'payment_type': payment_type,"paid_amount":paid_amount,"receiptid":receiptid },
		success: function(response)
		{
			if(Number(response) < 0){
				toastr.warning("PLEASE INSERT PAID AMOUNT GREATER THAN ALREADY INSERTED ENTRY TOTAL", "ERROR")
				$("#entry_type").select2("val","");
				$("#paid_amount").focus();
			}else{
				$("#entry_amount").val(response);
				$("#r_amount").val(response);
			}			
									
		}
	});
	
}


function verify_amount(p_amount){
	var d_amount = $("#paid_amount").val();
	if(Number.parseFloat(p_amount) > Number.parseFloat(d_amount) ){
		toastr.warning("Inserted amount can not be greater then remaning amount ", "WARNING");
		$("#entry_amount").val(d_amount);
		return false;
	}
}

function isreferencerequire(id){
	//var groupid = $("#paymentmodeid").find(':selected').attr("data-gid");
	if(id == 10 || id == 0){
		$(".reference_field").hide();
	}else{
		$(".reference_field").show();
	}
}


function get_ledger_details(ledger_id)
{
	//alert(ledger_id);	
	var company_cost_center = $('#company_cost_center').val();
	var company_bill_balance = $('#company_bill_balance').val();

	$.ajax({
		
		type:'POST',
		url: root_domain + finance_root_domain +'app/payment_new/',
		data : { mode:"get_ledger_details",ledger_id:ledger_id },
		success:function(result)
		{
			var obj = JSON.parse(result);
			//Cost Center popup
			if(obj.enable_cost_center==1 && company_cost_center==1)
			{
				$('#div_cost_center').show();
			}
			
			//Bill by bill balance show hide
			if(obj.enable_billbybill_opening==1 && company_bill_balance==1)
			{				
				$('#billby_bill_link').show();
			}else if(obj.enable_billbybill_opening==0 || company_bill_balance==0){
				$('#billby_bill_link').hide();
			}
			//check whether it is TDS entry(added by dhruv)
			if(obj.ledger_Tax_type == 9891 || obj.ledger_Tax_type == 9892){
				var gst_nature = $("#gst_nature").val();
				if(gst_nature != '69'){
					toastr.warning("Please select GST Nature as 'NOT APPLICABLE'", "ERROR")
					$('#vender_id').select2("val","");
				}else{
					$("#ledger_Tax_type").val(obj.ledger_Tax_type);
				}
				
			}else{
				$("#ledger_Tax_type").val('0');
			}
			//$(".billbybill_td").hide();
			
		}
	})
	
}
var rowIdx = 0;
function add_entry_field() 
{
	var tamount_p =0;
	$(".amount_p").each(function() {
		tamount_p = Number(tamount_p) + Number($(this).text());
	});

	var r_amount = Number($("#paid_amount").val()) - Number(tamount_p);
	if(Number($("#entry_amount").val()) > Number(r_amount) ){
		toastr.warning("It can not be greater then paid amount", "ERROR")
		this.value = this.value.replace(0);
		$("#entry_amount").val("");
		return false;
	}
	
		
	    
	var entry_type = $("#entry_type").val();
	var entry_type_text = $("#entry_type").select2('data').text;
	
	var paymentmodeid = $("#paymentmodeid").val();
	var paymentmodeid_text = $("#paymentmodeid").select2('data').text;
	var entry_amount = $("#entry_amount").val();


	$(`<tr id='fieldtr' class="R${++rowIdx}"><td data-label='Entry Type' class='text-center'>${entry_type_text}</td>
		<td data-label='Ledger Name' class='text-center'>${paymentmodeid_text}													
					</td><td data-label="Cr Amount" class="text-center amount_p ">
						${entry_amount}
					</td>
					
					<td data-label="Action" class="text-center">
						<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
    type="button" value="R${rowIdx}" onclick="removeSundry(${entry_amount},${rowIdx})"><i class="fa fa-times"></i></button>
						
                    </td>	
				</tr>`).insertAfter('tbody .transaction_table_field');	

	$('#entry_type').select2("val","");
	$('#paymentmodeid').select2("val","0");
	$('#entry_amount').val('');
	
	
}

function removeSundry(entry_amount,id){
            	
    $('.R'+id).remove();	

}

function add_tds_details(ledgr,amt,tds_per){

	var entry_type = 1;	
	var ledger_id = ledgr;
	var entry_amount = amt;
	var payment_type = $("#payment_type_reciept_pmt_trn").val();
	var edit_payment_entry_id = $("#edit_payment_entry_id").val();
	var istds = 'yes';
	var tds_per = tds_per;
	var receiptid = $("#receiptid").val();
	Loading(true);
	$.ajax({
			
		type:'POST',
		url: root_domain+finance_root_domain+'app/payment_new/',
		data: { mode : "add_paymnt_entry_field","entry_type":entry_type,"ledger_id":ledger_id,"entry_amount":entry_amount,
		"payment_type":payment_type,"edit_payment_entry_id":edit_payment_entry_id,"istds":istds,"tds_per":tds_per,"receiptid":receiptid},
		success:function(response)
		{
			//alert(result);
			if(response.trim() == '1') {
				toastr.success("INSERTED SUCCESSFULLY", "SUCCESS");
				$("#paid_amount").val(Number($("#entry_amount").val()) - Number(entry_amount));	
				$('#ModalAdvancePymentTds').modal('toggle');
				load_payment_entry_datatable();	
				add_payment_entry_field();		
				Unloading();

			}			
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}			
			
		}
	})
	// $(`<tr id='fieldtr' class="R1000"><td data-label='Entry Type' class='text-center'>Credit</td>
	// 	<td data-label='Ledger Name' class='text-center'>												
	// 		</td><td data-label="Cr Amount" class="text-center amount_p ">
	// 			${amt}
	// 		</td>

	//    </tr>`).insertAfter('tbody .transaction_table_field');

	// $('#ModalAdvancePymentTds').modal('toggle');
}

function add_payment_entry_field(){

    var party_form = $("#vender_id").find('option:selected').attr('data-formgroup');
	var entry_type = $("#entry_type").val();	
	var ledger_id = $("#vender_id").val();
	var entry_amount = $("#entry_amount").val();
	//alert(entry_amount);
	var payment_type = $("#payment_type_reciept_pmt_trn").val();
	var edit_payment_entry_id = $("#edit_payment_entry_id").val();
	var receiptid = $("#receiptid").val();
	var bill_adjust_voucher_type=$("#bill_adjust_voucher_type").val();
	var enable_billby_bill_show=$("#enable_billby_bill_show").val();

	var payment_date = $("#payment_date").val();
	var currency_enable = $("#currency_enable").val();
	var currency_id = $("#currency_id").val();
	var currency_rate = $("#currency_rate").val();
	//alert(ledger_id);
	if(entry_type=='')
	{
		toastr.warning("PLEASE SELECT ENTRY TYPE", "ERROR");
		$('#entry_amount').select2("focus");
		return false;
	}


	if(entry_amount=='')
	{
		toastr.warning("PLEASE SELECT ENTRY AMOUNT", "ERROR");
		$('#entry_amount').focus();
		return false;
	}

	if(ledger_id=='0' || ledger_id=='')
	{
		toastr.warning("PLEASE SELECT LEDGER", "ERROR");
		$('#vender_id').focus();
		return false;
	}

	if($("#gst_nature").val() =='')
	{
		toastr.warning("PLEASE SELECT GST NATURE", "ERROR");
		$('#gst_nature').focus();
		return false;
	}

	var field_c=0;
	var cou;
	$(".fieldcount").each(function() {
		field_c = parseInt(field_c) + parseInt(1);
	});

	if(($("#gst_nature").val() != 69) && (field_c > 0) && (edit_payment_entry_id == 0 || edit_payment_entry_id == '') ){
		toastr.warning("CAN NOT INSERT MORE THAN ONE ENTRY FOR THIS GST NATURE", "ERROR");
		return false;
	}

	// if(($("#gst_nature").val() == 70) && (party_form!='expense_form') ){
	// 	toastr.warning("PLEASE SELECT EXPENCE PARTY LEDGER FIRST", "ERROR")
	// 	return false;
	// }

	if(ledger_id == '9892'){
		get_tcs_reference_popup(ledger_id,entry_amount);
	}
	Loading(true);
		
	$.ajax({
			
		type:'POST',
		url: root_domain+finance_root_domain+'app/payment_new/',
		data: { mode : "add_paymnt_entry_field","entry_type":entry_type,"ledger_id":ledger_id,"entry_amount":entry_amount,
		"payment_type":payment_type,"edit_payment_entry_id":edit_payment_entry_id,"receiptid":receiptid
		,"payment_date":payment_date,"currency_enable":currency_enable,"currency_id":currency_id,"currency_rate":currency_rate,"bill_adjust_voucher_type":bill_adjust_voucher_type,"enable_billby_bill_show":enable_billby_bill_show},
		success:function(response)
		{
			//alert(result);
			if(response.trim() == '1') {
				toastr.success("INSERTED SUCCESSFULLY", "SUCCESS");
				
				if($("#ledger_Tax_type").val() == '9891'){
					get_tds_reference_popup(ledger_id,entry_amount);
				}

				if($("#gst_nature").val() == 72){
					get_adv_payment_ref(72,'vender_id');
				}
				if($("#gst_nature").val() == 70){
					get_registered_expence_popup(70,'vender_id','entry_amount');
				}
				if($("#gst_nature").val() == 73){
					get_payment_gov_popup(73);
				}

				load_payment_entry_datatable();
					if($("#gst_nature").val() == 70 || $("#gst_nature").val() == 72 ){
				}else{
					$('#entry_type').select2("val","");
					$('#vender_id').select2("val","0");
					$('#entry_amount').val("");
				}				
				Unloading();						
			}
			else if(response.trim() == '2') {
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");
				load_payment_entry_datatable();
				$('#entry_type').select2("val","");
				$('#vender_id').select2("val","0");
				$('#entry_amount').val("");
				$('#addrow').val('Add');
				Unloading();
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}	
			showHideLink($("#gst_nature").val());
			$('#billby_bill_link').hide();	
			$('#edit_payment_entry_id').val('');	
			
		}
	})
}

function load_payment_entry_datatable(){
	
	var receiptid = $("#receiptid").val();       
	var company_bill_balance = $('#company_bill_balance').val(); 
	var gst_nature = $("#gst_nature").val();

	datatable = $("#payment_entry_table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : false,
		"bSort" : false,
		"bProcessing": true,
		"bPaginate": false,
		"bInfo": false,
		"bServerSide" : true,
		"bDestroy" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !"
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+finance_root_domain+'app/payment_new/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch_payment_entry" },
				{ "name": "receiptid", "value": receiptid },{ "name": "company_bill_balance", "value": company_bill_balance }
				,{ "name": "gst_nature", "value": gst_nature });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function edit_payment_entry(id){
	
	Loading(true);
	var r_amount = $("#r_amount").val();
	editReq = $.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/payment_new/',
		data: { mode : "preedit_payment_entry", "edit_payment_entry_id" : id },
		success: function(response)
		{
			//alert(response);
			var obj = jQuery.parseJSON(response);
			$("#edit_payment_entry_id").val(obj.receipt_payment_trn_id);
			$('#entry_type').select2("val",obj.entry_type);
			$('#vender_id').select2("val",obj.ledger_id);
			$("#entry_amount").val(obj.amount);
			$('#addrow').val('Update');
			$("#r_amount").val(parseInt(obj.amount)+parseInt(r_amount));
			Unloading();
		}
	});

}

function delete_payment_entry(id,ledger_id){
	var gst_nature = $("#gst_nature").val();
	if(gst_nature == 70){
		var r1= confirm("It will delete all Expence entry, Are you sure , you want to delete this ?");
		if(r1){
			delete_all_register_expence_entry();
		}
		
	}else{
		var r= confirm(" Are you sure , you want to delete this ?");
		
		if(r) {
			var receiptid = $("#receiptid").val();
			var bill_voucher_type = $("#bill_adjust_voucher_type").val();
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+finance_root_domain+'app/payment_new/',
				data: { mode : "delete_payment_entry",  eid : id,"receiptid":receiptid,ledger_id:ledger_id,bill_voucher_type:bill_voucher_type },
				success: function(response)
				{
					//console.log(response)
					if(response.trim() == "1") {
						toastr.success("Payment Entry DELETED SUCCESSFULLY", "SUCCESS");
						load_payment_entry_datatable();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}	
					Unloading();						
				}
			});	
		}
	}
}

function delete_all_register_expence_entry(){
	var bill_voucher_type = $("#bill_adjust_voucher_type").val();
	var receiptid = $("#receiptid").val();
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/payment_new/',
		data: { mode : "delete_all_register_expence_entry",bill_voucher_type:bill_voucher_type,receiptid:receiptid },
		success: function(response)
		{
			load_payment_entry_datatable();	
			Unloading();	

		}
	});	
}

function delete_all_payment_entry(){
	
		var bill_voucher_type = $("#bill_adjust_voucher_type").val();
		var receiptid = $("#receiptid").val();
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/payment_new/',
			data: { mode : "delete_all_payment_entry",bill_voucher_type:bill_voucher_type,receiptid:receiptid },
			success: function(response)
			{
				load_payment_entry_datatable();	
				Unloading();	

			}
		});	
}


//End Code by dhruv

function delete_payment(id) 
{
	var r= confirm(" Are you sure,you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/payment_new/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				//console.log(response)
				if(response.trim() == "1") {
					toastr.success("Payment DELETE SUCCESSFULLY", "SUCCESS");
					load_datatable();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
}
function load_billdata(val) {
	
 	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/payment_new/',
		data: { mode : "load_data", vender_id : val},
		success: function(response){
			var data = jQuery.parseJSON(response);
			
			$('#due_payment').val(parseInt(data.dueamo));
			$('#due_payment_type').val(data.type);
			//$('#paid_amount').attr('max',data.dueamo);
			//showhide();
		}
	});
}
function load_data(val) {
	
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/payment_new/',
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
	if($("#due_payment_type").val()=="CR"){	
		Loading();
		
		editReq = $.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/payment_new/',
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
}
function get_cash_opening_bal(acc_id,amt_text,amt_err)
{

	$('.amtbalance').css('display','none');
	if(acc_id==1 && $("#due_payment_type").val()=="CR")
	{
		Loading();
		editReq = $.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/payment_new/',
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
	/* if($("#paymentmodeid").val()==2 && $("#due_payment_type").val()=="CR")
	{ */
	Loading();
	editReq = $.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/payment_new/',
		data: { mode : "get_chequeno", acc_id :acc_id },
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			response=response.trim();
			if(response!="")
			{
				$('#'+refcontroll).val(parseInt(response)+parseInt(1));
			}
			Unloading();
		}
	});	
//}
}
function reload_data()
{
	load_datatable();
}	
function load_datatable(){
	
	var date=$("#rep_date").val();
	var pay=$('#pay_status').val();
        var branch_id = $('#branch_id').val();
        
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
			"sEmptyTable": "NO DATA ADDED YET !"
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+finance_root_domain+'app/payment_new/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "date", "value": date },{ "name": "pay", "value": pay },{ "name": "branch_id", "value": branch_id } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}
function show_payment_data()
{
    var start_date = $("#start_date").val();
    var end_date = $("#end_date").val();
	var vender_id=$("#vender_id").val();
	if(vender_id!=""){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/payment_new/',
			data: { mode : "load_tempoutward",vender_id:vender_id, start_date: start_date, end_date:end_date},
			success: function(data){
			
				Unloading();
				tdskasar_show1();
			}		
			
		});
		}else{
		$('#sale_productdata').html('');	
	}
}
function show_purchase_data()
{
	var start_date = $("#start_date").val();
    var end_date = $("#end_date").val();
	var vender_id = $("#vender_id").val();
	if(vender_id != ""){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/payment_new/',
			data: { mode : "load_purchase_data",vender_id:vender_id, start_date: start_date, end_date:end_date},
			success: function(data){
				//console.log(data);
				$('#sale_productdata').html(data);				
				Unloading();
				tdskasar_show1();
                                copy_full_payment();
			}		
			
		});
        }else{
		$('#sale_productdata').html('');	
	}
}
function paid_total(){
	var total = 0;
	var total1 = 0;
	var total2 = 0;
	var type = "";
	var cou=$("#cou").val();
	if (isNaN(cou)) 
	{
		cou=1;
	}
	for (i = 0; i < cou; i++) 
	{
		var paid=$("#o_amount"+i).val();
		var ref_type=$("#o_ref_type"+i).val();
		
		if (paid==="" || paid===undefined)
		{
			paid=0;
		}
		paid=parseInt(paid);
		if(ref_type==1){
			total1 += parseInt(paid);
			}else{
			total2 += parseInt(paid);
		}
		
	}
	if (isNaN(total1)) 
	{
		total1=0;
	}
	if (isNaN(total2)) 
	{
		total2=0;
	}
	total=parseInt(total1)-parseInt(total2);
	
	if(total>=0){
		type= "CR";
		}else{
		type= "DR";
	}
	
	if (isNaN(total)) {
		total=0;
	}
	total = ''+total+'';
	total = total.replace("-" ,"");
	
	//$('#bill_max_paid_amount').val(total);
	//var show_total=total+" "+type;
	$('#amount_used_payment').val(total);
	$('#amount_used_payment_type').val(type);
	copy_full_payment();
}
function use_amount(i){
	if($("#chk_cust"+i).prop('checked')) {
		var paid=$("#o_ref_due"+i).val();
		var mode = $("#mode").val();
		var amount_to_pay = "0";
		if(mode == 'Edit'){
			var paid_amount = $("#paid_amount").val();
			var amount_used_payment = $("#amount_used_payment").val();
			amount_to_pay = parseInt(amount_used_payment) + parseInt(paid);
			if(amount_to_pay == 'NaN'){
				if(parseInt(paid) < parseInt(paid_amount)){
					$('#o_amount'+i).val(paid);
				} else {
					$('#o_amount'+i).val(paid_amount);
				}

			} else {
				if(parseInt(amount_to_pay) >= parseInt(paid_amount)){
					toastr.warning("Total Payment should be maximum "+paid_amount, "ERROR");
					$("#chk_cust"+i).removeAttr('checked');
				} else {
					if(parseInt(paid) < parseInt(paid_amount)){
						$('#o_amount'+i).val(paid);
					} else {
						$('#o_amount'+i).val(paid_amount);
					}
				}
			}
		} else {
			$('#o_amount'+i).val(paid);
		}
	}else{
		$('#o_amount'+i).val("");
	}
	paid_total();
}
function get_tds(type,i)
{
	
	var o_ref_amount=parseFloat($('#o_ref_amount'+i).val());
	var o_ref_due=parseFloat($('#o_ref_due'+i).val());
	var o_kasar=parseFloat($('#o_kasar'+i).val());
	var disc=0;
	if(o_ref_amount!="")
	{	
		if(type=="2")
		{
			disc=100*parseFloat($('#o_tds'+i).val())/(o_ref_amount);
			var  disc1=disc.toFixed(2);			
			$('#o_tds_per'+i).val(disc1);
			if (isNaN(o_kasar)){ o_kasar=0; }
			var tds=$('#o_tds'+i).val();
			if (isNaN(tds)){ tds=0; }
			var maxq=parseFloat(o_ref_due)-(parseFloat(tds)+parseFloat(o_kasar));
			$("#o_amount"+i).attr("max",maxq);
		}
		else if(type=="1")
		{
			
			disc=((o_ref_amount)*parseFloat($('#o_tds_per'+i).val()))/100;	
			var	disc1=disc.toFixed(2);
			$('#o_tds'+i).val(disc1);
			if (isNaN(disc1)){ disc1=0; }
			if (isNaN(o_kasar)){ o_kasar=0; }
			
			var maxq=parseFloat(o_ref_due)-(parseFloat(disc1)+parseFloat(o_kasar));
			$("#o_amount"+i).attr("max",maxq);
		}
	}
}
function get_kasar(i)
{
	var o_ref_due=parseFloat($('#o_ref_due'+i).val());
	var o_kasar=parseFloat($('#o_kasar'+i).val());
	var o_tds=parseFloat($('#o_tds'+i).val());
	if (isNaN(o_ref_due)){ o_ref_due=0; }
	if (isNaN(o_kasar)){ o_kasar=0; }
	if (isNaN(o_tds)){ o_tds=0; }
	
	var maxq=parseFloat(o_ref_due)-(parseFloat(o_tds)+parseFloat(o_kasar));
	$("#o_amount"+i).attr("max",maxq);
}
function showhide(){
	var due_payment_type=$('#due_payment_type').val();
	if(due_payment_type=="DR"){
		$('.cr').attr("style","display:none");
		$('.dr').attr("style","display:block");
	}
	if(due_payment_type=="CR"){
		$('.dr').attr("style","display:none");
		//$('.cr').attr("style","display:block");
	}
}
function tdskasar_show1(){
	if($("#tdskasar_show").prop('checked')) {
		$('.tdskasar1').hide();
        $('.tdskasar').show();
		}else{
		$('.tdskasar').hide();
        $('.tdskasar1').show();
	}
}
function copy_full_payment(){
	var paid_amount=$('#paid_amount').val();
	var paid_typeid=$('#paid_typeid').val();
	var amount_used_payment=$('#amount_used_payment').val();
	var amount_used_payment_type=$('#amount_used_payment_type').val();
	if(paid_typeid=="1"){
		var paid_type="CR";
		}else{
		var paid_type="DR";
	}
	$('#amount_paid').val(paid_amount);
	$('#amount_paid_type').val(paid_type);
	if(paid_type===amount_used_payment_type){
		var exec = parseFloat(paid_amount)-parseFloat(amount_used_payment);
		var full_paid= parseFloat(amount_used_payment)+parseFloat(exec);
		$('#amount_in_excess').val(exec);
		$('#amount_in_excess_type').val(paid_type);
		$('#full_paid').val(full_paid);
		$('#full_paid_type').val(paid_type);
        }else if(amount_used_payment_type===""){
		var exec = parseFloat(paid_amount);
		$('#amount_in_excess').val(exec);
		$('#amount_in_excess_type').val(paid_type);
		$('#full_paid').val(exec);
		$('#full_paid_type').val(paid_type);
        }else if(amount_used_payment_type!=paid_type){
		var exec = parseFloat(paid_amount)+parseFloat(amount_used_payment);
		$('#amount_in_excess').val(exec);
		$('#full_paid').val(exec);
		if(paid_amount<amount_used_payment){
			$('#amount_in_excess_type').val(amount_used_payment_type);
			$('#full_paid_type').val(amount_used_payment_type);
                }else if(paid_amount>amount_used_payment){
			$('#amount_in_excess_type').val(paid_type);
			$('#full_paid_type').val(paid_type);
		}
	}
	
}
function get_series_no(){
	
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/payment_new/',
		data: { mode : "load_invoiceno" },
		success: function(resp){
			//console.log(resp);
			var no = jQuery.parseJSON(resp);
			$('#receipt_no').val(no.invoiceno);
			$('#receipt_no_reference').val(no.invoiceno);
		}		
	});	
}

function get_pdc_date(check)
{
	//alert(check);
	if(check==1)
	{
		$('.pdc_date_class').show();
	}
	else
	{
		$('.pdc_date_class').hide();
	}
}
function check_pay_mode(check_value)
{
	
	//10 - cash
	if(check_value != "11"){
		$('#cheque_data').hide();
        $('#save_cheque').hide();
        $('#cheque_dtl').val('');
	}else{
		$('#save_cheque').show();
		//$('#cheque_dtl').val('');
		$('#cheque_data').show();
        $('#save_cheque').show();
       // get_chequeno(id,'cheque_dtl');
	}
}