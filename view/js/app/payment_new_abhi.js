//var datatable;
$(document).ready(function() {
	load_datatable();
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
				required: "Choose Ledger"
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
	
	if(parseInt($('#paid_amount').val())!=parseInt($('#full_paid').val()))
	{
		toastr.warning("Balance Not Match", "ERROR");
		return false;
	}
	if($('#paid_typeid').val()==$('#full_paid_type').val())
	{
		toastr.warning("Balance Not Match", "ERROR");
		return false;
	}
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#save').prop("disabled",true);		
		
	var form_data=new FormData(this);	
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+'app/payment_new/',
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
				toastr.success("PAYMENT ADDED SUCCESSFULLY", "SUCCESS");
				if (typeof arr.cheque_genid != 'undefined') {
					window.location=root_domain+'cheque_app/generage-cheque/'+arr.cheque_genid;
				}
				else {
					window.location=root_domain+'payment_list';
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
					window.location=root_domain+'payment_list/'+arr.eid;
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

function delete_payment(id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/payment_new/',
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
		url: root_domain+'app/payment_new/',
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
		url: root_domain+'app/payment_new/',
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
			url: root_domain+'app/payment_new/',
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
			url: root_domain+'app/payment_new/',
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
		url: root_domain+'app/payment_new/',
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
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+'app/payment_new/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "date", "value": date },{ "name": "pay", "value": pay } );
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
	if(vender_id!=""){
		Loading()
		$.ajax({
			type: "POST",
			url: root_domain+'app/payment_new/',
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
		$('#o_amount'+i).val(paid);
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
		url: root_domain+'app/payment_new/',
		data: { mode : "load_invoiceno" },
		success: function(resp){
			//console.log(resp);
			var no = jQuery.parseJSON(resp);
			$('#receipt_no').val(no.invoiceno);
		}		
	});	
}
