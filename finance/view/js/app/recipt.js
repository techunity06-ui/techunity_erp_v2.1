//var datatable;
$(document).ready(function() {
	delete_all_receipt_entry();
	load_datatable();
	load_payment_entry_datatable();
	$('#billby_bill_link').hide();
	jQuery('.numbersOnly').keyup(function () { 
	    this.value = this.value.replace(/[^0-9\.]/g,'');
	});

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
	
	if($('#vender_id').val() == 0)
	{
		toastr.warning("Please select Party first", "ERROR");
		return false;
	}else if($('#paid_amount').val() == 0){
		toastr.warning("Please insert paid amount", "ERROR");
		return false;
	}else if($('#gst_nature').val() == ''){
		toastr.warning("Please Select GST NATURE First", "ERROR");
		return false;
	}else{

	
		form.submitted = true;	
		Loading(true);	
		$(this).attr("disabled","disabled");		
		$('#save').prop("disabled",true);
		
		var form_data=new FormData(this);	
		//console.log(form_data);
		$.ajax({
			cache:false,
			url: root_domain+finance_root_domain+'app/recipt/',
			type: "POST",
			data: form_data,
			contentType: false,
			processData:false,
			success: function(response)
			{
				//alert(response);
				var arr = jQuery.parseJSON(response);			
				if(arr.msg == '1') {
					Unloading();
					toastr.success("PAYMENT ADDED SUCCESSFULLY", "SUCCESS");				
					window.location=root_domain+finance_root_domain+'recipt_list';				
				}			
				else if(arr.msg == '0') {
					toastr.warning("SOMETHING WRONG", "ERROR")
					Unloading();
				}
				else if(arr.msg == '3') {
					toastr.warning("Paid amount and Entry payment should be same", "ERROR")
					Unloading();
				}
				else if(arr.msg == '-1')
				{
					toastr.info("ALREADY EXISTS", "INFO")
					Unloading();				
				}
				else if(arr.msg == 'update')
				{	
					toastr.success("Payment UPDATED SUCCESSFULLY", "SUCCESS");				
					window.location=root_domain+finance_root_domain+'recipt_list';
				}
				$('#save').prop("disabled",false);
				//$('#purchasepayment_add').trigger('reset');	
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(textStatus, errorThrown);
			}
		});
	}
	
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
	$("#ledger_add_type").val('recipt');
	$("#ledger_name").focus();
}

// Start Code Added By Dhruv
function add_payment_entry_field(){

	if($('#entry_type').val() == '')
	{
		toastr.warning("Please select Entry Type", "ERROR");
		return false;
	}else if($('#receiver_ledger').val() == 0 || $('#receiver_ledger').val() == ''){
		toastr.warning("Please select Ledger", "ERROR");
		return false;
	}else if($('#entry_amount').val() == 0 || $('#entry_amount').val() == ''){
		toastr.warning("Please insert amount", "ERROR");
		return false;
	}else{

		var entry_type = $("#entry_type").val();	
		var ledger_id = $("#receiver_ledger").val();
		var entry_amount = $("#entry_amount").val();
		var payment_type = $("#payment_type_reciept_pmt_trn").val();
		var edit_payment_entry_id = $("#edit_payment_entry_id").val();
		var receiptid = $("#receiptid").val();
		var payment_date = $("#payment_date").val();
		var currency_enable = $("#currency_enable").val();
		var currency_id = $("#currency_id").val();
		var currency_rate = $("#currency_rate").val();

		var field_c=0;
		var cou;
		$(".fieldcount").each(function() {
			field_c = parseInt(field_c) + parseInt(1);
		});

		if(($("#gst_nature").val() != 95) && (field_c > 0) && (edit_payment_entry_id == 0 || edit_payment_entry_id == '') ){
			toastr.warning("CAN NOT INSERT MORE THAN ONE ENTRY FOR THIS GST NATURE", "ERROR");
			return false;
		}

		

		$.ajax({
				
			type:'POST',
			url: root_domain+finance_root_domain+'app/recipt/',
			data: { mode : "add_paymnt_entry_field","entry_type":entry_type,"ledger_id":ledger_id,"entry_amount":entry_amount,
			"payment_type":payment_type,"edit_payment_entry_id":edit_payment_entry_id,"receiptid":receiptid,"payment_date":payment_date,
			"currency_enable":currency_enable,"currency_id":currency_id,"currency_rate":currency_rate},
			success:function(response)
			{
				//alert(result);
				if(response.trim() == '1') {
					toastr.success("INSERTED SUCCESSFULLY", "SUCCESS");
					
					if($("#gst_nature").val() == 99){
						get_adv_receipt_popup(99,'receiver_ledger');
					}

					load_payment_entry_datatable();
					if($("#gst_nature").val() != 99){
						$('#entry_type').select2("val","");
						$('#receiver_ledger').select2("val","0");
						$('#entry_amount').val("");
					}
					$("#edit_payment_entry_id").val('');
					Unloading();						
				}
				else if(response.trim() == '2') {
					toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");
					load_payment_entry_datatable();
					$('#entry_type').select2("val","");
					$('#receiver_ledger').select2("val","0");
					$('#entry_amount').val("");
					$('#addrow').val('Add');
					$("#edit_payment_entry_id").val('');
					Unloading();
				}
				else if(response.trim() == '0') {
					toastr.warning("SOMETHING WRONG", "ERROR")
					Unloading();
				}	
				$('#billby_bill_link').hide();		
				
			}
		})
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
		"bStateSave": true,
		"fnStateSave": function (oSettings, oData) {
            localStorage.setItem('offersDataTables', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('offersDataTables'));
        },
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !"
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+finance_root_domain+'app/recipt/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch_payment_entry" },
				{ "name": "receiptid", "value": receiptid },
				{ "name": "company_bill_balance", "value": company_bill_balance },
				{ "name": "gst_nature", "value": gst_nature });
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
		url: root_domain+finance_root_domain+'app/recipt/',
		data: { mode : "preedit_payment_entry", "edit_payment_entry_id" : id },
		success: function(response)
		{
			//alert(response);
			var obj = jQuery.parseJSON(response);
			$("#edit_payment_entry_id").val(obj.receipt_payment_trn_id);
			$('#entry_type').select2("val",obj.entry_type);
			$('#receiver_ledger').select2("val",obj.ledger_id);
			$("#entry_amount").val(obj.amount);
			$('#addrow').val('Update');
			$("#r_amount").val(parseInt(obj.amount)+parseInt(r_amount));
			
			Unloading();
		}
	});

}

function delete_payment_entry(id){
	var r= confirm(" Are you sure , you want to delete this ?");
	
	if(r) {
		var receiptid = $("#receiptid").val();
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/recipt/',
			data: { mode : "delete_payment_entry",  eid : id,"receiptid":receiptid },
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

function delete_all_receipt_entry(){
	
		var bill_voucher_type = $("#bill_adjust_voucher_type").val();
		var receiptid = $("#receiptid").val();
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/recipt/',
			data: { mode : "delete_all_receipt_entry",bill_voucher_type:bill_voucher_type,receiptid:receiptid },
			success: function(response)
			{	
				Unloading();						
			}
		});	
}


function focus_paid_amt(){

	var tamount = 0;

	if($("#paid_amount").val() == 0){
		$("#receiver_ledger").select2("val","0");
		toastr.warning("PLEASE INSERT PAID AMOUNT FIRST", "ERROR")
		$("#paid_amount").focus();
		return false;
	}

	var payment_type = $("#payment_type_reciept_pmt_trn").val();
	var paid_amount = $("#paid_amount").val();
	var receiptid = $("#receiptid").val();

	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/recipt/',
		data: { mode : "get_total_receipt_payment", 'payment_type': payment_type,"paid_amount":paid_amount,"receiptid":receiptid },
		success: function(response)
		{
			//console.log(response)
			var r_amount = parseInt(paid_amount) - parseInt(response);
			$("#entry_amount").val(r_amount);
			$("#r_amount").val(r_amount);									
		}
	});
	
}

function verify_amount(p_amount){
	var d_amount = $("#r_amount").val();
	if( Number.parseFloat(p_amount) > Number.parseFloat(d_amount) ){
		toastr.warning("Inserted amount can not be greater then remaning amount ", "WARNING");
		$("#entry_amount").val(d_amount);
		return false;
	}
}

// End Code Added By Dhruv

function delete_payment(id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/recipt/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				//console.log(response)
				if(response.trim() == "1") {
					toastr.success("Payment DELETE SUCCESSFULLY", "SUCCESS");
					window.location=root_domain+finance_root_domain+'recipt_list';	
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
	
 	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/recipt/',
		data: { mode : "load_data", vender_id : val},
		success: function(response){
			var data = jQuery.parseJSON(response);
			
			$('#due_payment').val(parseInt(data.dueamo));
			$('#due_payment_type').val(data.type);
			//$('#paid_amount').attr('max',data.dueamo);
			showhide();
		}
	});
}
function load_data(val) {
	
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/recipt/',
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
			url: root_domain+finance_root_domain+'app/recipt/',
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
			url: root_domain+finance_root_domain+'app/recipt/',
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
	if($("#paymentmodeid").val()==2 && $("#due_payment_type").val()=="CR")
	{
		Loading();
		editReq = $.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/recipt/',
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
			"sEmptyTable": "NO Receipt ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+finance_root_domain+'app/recipt/',
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
function show_data()
{
	var vender_id=$("#vender_id").val();
	if(vender_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/recipt/',
			data: { mode : "load_tempoutward",vender_id:vender_id},
			success: function(data){
				//console.log(data);
				$('#sale_productdata').html(data);				
				// $("#paid_amount").attr({"disabled" : true});
				Unloading();
				tdskasar_show1();
			}		
			
		});
	}else{
		$('#sale_productdata').html('');	
	}
}
function show_invoice_data()
{
    	var vender_id=$("#vender_id").val();
	if(vender_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/recipt/',
			data: { mode : "load_invoice_data",vender_id:vender_id},
			success: function(data){
				//console.log(data);
				$('#sale_productdata').html(data);				
				// $("#paid_amount").attr({"disabled" : true});
				Unloading();
				tdskasar_show1();
                                //copy_full_payment();
			}		
			
		});
	}else{
		$('#sale_productdata').html('');	
	}
}
function paid_total(){
	var mode = $("#mode").val();
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
		} else {
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

	/*if(mode == 'Edit'){
		var paid_amount = $("#paid_amount").val();
		if(parseInt(total) > parseInt(paid_amount)){
			toastr.warning("Total Payment should be maximum "+paid_amount, "ERROR");
			$('#save').prop("disabled",true);
			return false;
		} else {
			$('#save').prop("disabled",false);
		}
	}*/
	
	//$('#bill_max_paid_amount').val(total);
	//var show_total=total+" "+type;
	$('#amount_used_payment').val(total);
	$('#amount_used_payment_type').val(type);
	//copy_full_payment();
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

	} else {
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
// function copy_full_payment(){
// 	var receiver_ledger = $("#receiver_ledger").val();
	
// 	if(receiver_ledger == 0){
// 		toastr.warning("PLEASE SELECT PARTY LEDGER FIRST", "ERROR")
// 		$("#receiver_ledger").select2("focus");
// 		$('#paid_amount').val("");
// 		return false;
// 	}
	
// 	var paid_amount=$('#paid_amount').val();
// 	var paid_typeid=$('#paid_typeid').val();
// 	var amount_used_payment=$('#amount_used_payment').val();
// 	var amount_used_payment_type=$('#amount_used_payment_type').val();
// 	if(paid_typeid=="1"){
// 		var paid_type="CR";
// 		}else{
// 		var paid_type="DR";
// 	}
// 	$('#amount_paid').val(paid_amount);
// 	$('#amount_paid_type').val(paid_type);
// 	if(paid_type===amount_used_payment_type){
// 		var exec = parseFloat(paid_amount)-parseFloat(amount_used_payment);
// 		var full_paid= parseFloat(amount_used_payment)+parseFloat(exec);
// 		$('#amount_in_excess').val(exec);
// 		$('#amount_in_excess_type').val(paid_type);
// 		$('#full_paid').val(full_paid);
// 		$('#full_paid_type').val(paid_type);
// 		}else if(amount_used_payment_type===""){
// 		var exec = parseFloat(paid_amount);
// 		$('#amount_in_excess').val(exec);
// 		$('#amount_in_excess_type').val(paid_type);
// 		$('#full_paid').val(exec);
// 		$('#full_paid_type').val(paid_type);
// 		}else if(amount_used_payment_type!=paid_type){
// 		var exec = parseFloat(paid_amount)+parseFloat(amount_used_payment);
// 		$('#amount_in_excess').val(exec);
// 		$('#full_paid').val(exec);
// 		if(paid_amount<amount_used_payment){
// 			$('#amount_in_excess_type').val(amount_used_payment_type);
// 			$('#full_paid_type').val(amount_used_payment_type);
// 			}else if(paid_amount>amount_used_payment){
// 			$('#amount_in_excess_type').val(paid_type);
// 			$('#full_paid_type').val(paid_type);
// 		}
// 	}
	
// }
function get_series_no(){
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/recipt/',
		data: { mode : "load_invoiceno" },
		success: function(resp){
			var no = jQuery.parseJSON(resp);
			$('#receipt_no').val(no.invoiceno);
			$('#receipt_no_reference').val(no.invoiceno);
		}		
	});	
}

function get_ledger_details(ledger_id)
{	
	var company_bill_balance = $('#company_bill_balance').val();

	$.ajax({
		
		type:'POST',
		url: root_domain + finance_root_domain +'app/recipt/',
		data : { mode:"get_ledger_details",ledger_id:ledger_id },
		success:function(result)
		{
			var obj = JSON.parse(result);
			//alert(company_bill_balance);
			//Bill by bill balance show hide
			if(obj.enable_billbybill_opening==1 && company_bill_balance==1)
			{
				$('#billby_bill_link').show();
			}else if(obj.enable_billbybill_opening==0 || company_bill_balance==0){
				$('#billby_bill_link').hide();
			}
			
		}
	})
	
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
function isreferencerequire(id){
	//var groupid = $("#paymentmodeid").find(':selected').attr("data-gid");
	if(id == 10 || id == 0){
		$(".reference_field").hide();
	}else{
		$(".reference_field").show();
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