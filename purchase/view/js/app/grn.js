//var datatable;
$(document).ready(function() {
	load_grn_datatable();
	//show_data();
//	load_purhcase_order_data($('#purchaseorder_id').val());

	// validate vendor add form on keyup and submit
	$("#grn_add").validate({
		rules: {
			grn_no:{
				required: true			
			},
			grn_date: {
				required: true			
			},
			vender_id: {
				required: true			
			},
			grn_file: {
				required: true			
			}
		},
		messages: {
			grn_no:{
				required: "Enter GRN No."			
			},
			grn_date: {
				required: "Enter GRN Date"
			},
			vender_id: {
				required: "Choose Vendor"
			},
			grn_file: {
				required: "Choose File"
			}
		}
	}); 
});

$("#grn_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#grn_add").valid()) {
		return false;
	}
	if($("#invoice_no").val()===""){
		if($("#challan_no").val()===""){
			toastr.warning("Please Enter Invoice no/Challan No", "ERROR")
			return false;
		}
	}
	
		var so_stock=(document.getElementsByName('grn_qty[]'));
		var cnt=so_stock.length;
		var so_stock1=0;
		for(var i=0;i<cnt;i++)
		{
			if(so_stock[i].value > 0){
				so_stock1 += parseFloat(so_stock[i].value);
			}
		} 
		if(so_stock1<="0"){
			toastr.warning("Enter Any One", "WARNING"); 
			  return false;
		}

	form.submitted = true;
	Loading(true);
	$(this).attr("disabled","disabled");		
	$('#save').attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+purchase_domain+'app/grn/',
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
				toastr.success("G.R.N. ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+purchase_domain+arr.back; 
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(arr.msg == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			else if(arr.msg== 'update') {
				toastr.success("G.R.N. UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location=root_domain+purchase_domain+'grn_list';
			}
			$('#grn_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function add_field()
{
	if(!$("#purchaseorder_id").val()){		
		toastr.warning("Choose Purchase Order", "ERROR");
		$("#purchaseorder_id").focus();
		return false;
	}
	else if(!$("#product_id").val()){
		toastr.warning("Select Product", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	else if(!$("#product_qty").val()){		
		toastr.warning("Enter Qty", "ERROR");
		$("#product_qty").focus();
		return false;
	}
	else if(!$("#unitid").val()){		
		toastr.warning("Select Unit", "ERROR");
		$("#unitid").select2('focus');
		return false;
	}
	var promaxval=$('#product_qty').attr("max");
	
	if(parseInt(promaxval)<parseInt($("#product_qty").val()))
	{
		toastr.warning("Please Check Pending Qty", "ERROR")
		return false;
	}
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/grn/',
		data: { mode : "fieldadd",edit_id:$("#edit_id").val(),purchaseorder_id:$("#purchaseorder_id").val(),product_id:$("#product_id").val(),product_des:$("#product_des").val(),pro_mfg_date:$("#pro_mfg_date").val(),pro_exp_date:$("#pro_exp_date").val(),product_qty:$("#product_qty").val(),unit_id:$("#unitid").val(),grn_id:$("#eid").val(),product_qc:$('#product_qc').val() },
		success: function(response)
		{
			//console.log(response);
			$("#product_id").select2("val","");
			$("#product_id").select2('focus');
			$("#product_des").val("");
			$("#product_qty").val("");
			$("#product_qty").attr("placeholder","");
			$("#unitid").select2('val','');
			$("#edit_id").val('');
			$('#addrow').val('Add');
			Unloading();
			show_data();
		}
	});
}	
function show_data() {
	var eid = $('#eid').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/grn/',
		data: { mode : "load_grn_trn_data", grn_id:eid },
		success: function(data){
			//console.log(data);
			$('#sale_productdata').html(data);		 
			Unloading();
		}		 
	}); 
}
function load_grn_datatable()
{
	var data=$('input[name=report]:Checked').val();
	var date=$('#rep_date').val();
	var grn_against=$('#grn_against').val();
	
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
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+purchase_domain+'app/grn/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "report", "value": data },{ "name": "date", "value": date },{ "name": "grn_against", "value": grn_against });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function edit_grn_data(grn_trn_id)
{ 
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/grn/',
		data: { mode:"preedit", grn_trn_id:grn_trn_id },
		success: function(response)
		{
			//console.log(response)
			
			var data = jQuery.parseJSON(response);
			/*$('#purchaseorder_id').html(data.po_html_resp);
			$("#purchaseorder_id").select2("val",data.purchaseorder_id);*/
			$('#product_id').html(data.pro_html_resp);
			$("#product_id").select2("val",data.product_id);
			$("#unitid").select2("val",data.unit_id);
			$("#product_des").val(data.description);
			$("#product_hsn_code").val(data.product_hsn_code);
			$("#pro_entry_date").datepicker("setDate", data.pro_entry_date);
			if(data.pro_mfg_date){
				$("#pro_mfg_date").datepicker("setDate", data.pro_mfg_date);
			}
			else{
				$("#pro_mfg_date").val("");
			}
			
			if(data.pro_exp_date){
				$("#pro_exp_date").datepicker("setDate", data.pro_exp_date);
			}
			else{
				$("#pro_exp_date").val("");
			}
			
			$("#product_qty").val(data.product_qty);
			$("#edit_id").val(grn_trn_id);
			$('#addrow').val('Update');
			Unloading();
		}
	});
}
function delete_grn_data(grn_trn_id,purchaseorder_id)
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/grn/',
			data: { mode:"delete_data", grn_trn_id:grn_trn_id, purchaseorder_id:purchaseorder_id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					show_data();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function delete_grn(grn_id,purchaseorder_id)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/grn/',
			data: { mode:"delete_grn", grn_id:grn_id, purchaseorder_id:purchaseorder_id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					load_grn_datatable();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function load_purhcase_order_data(){
	
	//alert('hii');
	//alert(order_id);
	var order_id=$('#purchaseorder_id').val();
	var grn_type=$('#grn_against').val();
	var mode1=$('#mode').val();
	var eid=$('#eid').val();
	var vender_id=$('#vender_id').val();
	var pmode=$('#pmode').val();
	var branch_id=$('#branch_id').val();
	if(branch_id==""){
		toastr.warning("Select Branch Name", "WARNING");
		return false;
	}
	//alert(vender_id);
	//alert(order_id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/grn/',
		data: { mode:"load_purhcase_order_data", order_id:order_id , grn_type:grn_type,mode1:mode1,eid:eid,vender_id:vender_id,branch_id:branch_id },
		success: function(response){
			//alert(response);
			//console.log(response);
			var resp = 	JSON.parse(response);
			/*$('#product_id').html(resp.pro_html);
			$('#product_id').select2('val','');
			$('#product_id').select2('focus');*/
			$('#field1').html(resp.pro_html);
			if(pmode==="padd"){
				//$('#vender_id').val(resp.vendor_id);
				//$('#vender_name').val(resp.vendor_name);
			}
			//$('#request_no').val(resp.request_id);
			Unloading();
		}
	});
	
}

function load_productdetail(product_id) {
	var purchaseorder_id = $("#purchaseorder_id").val();
	if(purchaseorder_id){
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/grn/',
			data: { mode:"load_productdetail", product_id:product_id, purchaseorder_id:purchaseorder_id },
			success: function(response)
			{
				//console.log(response);
				var obj=jQuery.parseJSON(response);
				//alert(obj.product_qc);
				$('#product_des').val(obj.description);
				$('#product_qty').attr("placeholder",obj.pending_qty);
				$('#product_qty').attr("max",obj.pending_qty);
				
				$('#unitid').select2("val",obj.unit_id);
				$('#product_rate').val(obj.product_rate);
				$('#taxable_value').val(obj.taxable_value);
				$('#formulaid').val(obj.formulaid);
				$('#product_amount').val(obj.product_amount);
				$('#product_qc').val(obj.product_qc);
			}
		});
	}
	else{
		toastr.warning("Choose Purchase Order !!!", "WARNING");
		$('#purchaseorder_id').select2('focus');
	}
} 
function load_po_ven_wise(vender_id) {
	//alert(vender_id);
	if(vender_id){
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/grn/',
			data: { mode:"load_po_ven_wise", vender_id:vender_id },
			success: function(response)
			{
				//alert(response);
				//console.log(response);
				var obj=jQuery.parseJSON(response);
				$('#purchaseorder_id').html(obj.pro_html);
				$('#purchaseorder_id').select2("val","");
			}
		});
	}
}

function load_grn_no() {
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/grn/',
		data: { mode : "load_grn_no" },
		success: function(data){
			//console.log(data);
			var no = jQuery.parseJSON(data);
			$('#grn_no').val(no.invoiceno);
		}
	});
}
function delete_attch(grn_attch_id) {
	var conf = confirm("Are you sure want to Delete Receipt ?");
	if(conf){
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/grn/',
			data: { mode : "delete_attch", grn_attch_id:grn_attch_id },
			success: function(response){
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("ATTACHMENT DELETED SUCCESSFULLY", "SUCCESS");
					location.reload();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();
			}
		}); 
	}
}

function get_order_no()
{
	//alert(gno);
	var vender_id=$("#vender_id").val();
	var grn_type=$("#grn_against").val();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/grn/',
		data: { mode : "get_order_no", grn_type:grn_type,vender_id:vender_id },
		success: function(response){
			//alert(response);
			//console.log(response);
			$('#purchaseorder_id').html(response);
		}
	});
}

$(document).on('keyup','.qty_mangement', function(){
	$enter_val = $(this).val();
	$exist_qty = $(this).attr('data-qty');
	$tolerance = $(this).attr('data-tol');
	$min_tolerance = $(this).attr('data-mini-tol');
	$max_tolerance = $(this).attr('data-max-tol');
	$pending_qty = $(this).attr('data-pendingqty');
	//alert($tolerance);
	if($tolerance=='1'){
		$minimum_tol_val = parseFloat($exist_qty)*parseFloat($min_tolerance)/100; 
		$mini_toll = parseFloat($exist_qty)-parseFloat($minimum_tol_val);

		$maximum_tol_val = parseFloat($exist_qty)*parseFloat($max_tolerance)/100; 
		$max_toll = parseFloat($exist_qty) + parseFloat($maximum_tol_val);

		$take_qty = parseFloat($exist_qty) - parseFloat($pending_qty);
		$remain_qty = parseFloat($max_toll) - parseFloat($take_qty)
		
		//console.log($mini_toll);console.log($max_toll);console.log($max_toll);
		if(parseFloat($enter_val) > parseFloat($remain_qty)){
			toastr.warning("Please enter your quantity less than "+$remain_qty, "WARNING");
			$(this).attr('max', $remain_qty);
			return false;
		}
		
	}else{
		if(parseFloat($enter_val)>parseFloat($pending_qty)){
			$(this).val(0);
			toastr.warning("Please enter your quantity less than "+$pending_qty, "WARNING");
			return false;
		}
	}
});
function product_convert_qty(type,cnt){
	//alert(type);
	if(type==2){
		var conv_qty_hide=$("#grn_qty"+cnt).val();
		var s=parseFloat(conv_qty_hide);
		results = s.toFixed(4);
		
		var	num=$("#grn_qty_hide"+cnt).val();
		var d=parseFloat(num);
		resultb = d.toFixed(4);
		if(resultb===results){
			return false;
		}
		var conv_grn_qty_hide=$("#conv_grn_qty_hide"+cnt).val();
	}else{
		var base_qty_hide=$("#conv_grn_qty"+cnt).val();
		var d=parseFloat(base_qty_hide);
		resultb = d.toFixed(4);
		
		var base_qty_hidess=$("#conv_grn_qty_hide"+cnt).val();
		var s=parseFloat(base_qty_hidess);
		results = s.toFixed(4);
		
		if(resultb===results){
			return false;
		}
		var conv_qty_hide=$("#grn_qty"+cnt).val();
	}
	
	var base_qty=$("#grn_qty"+cnt).val();
	var conv_qty=$("#conv_grn_qty"+cnt).val();
	var product_id=$("#grn_pid"+cnt).val();
	if(product_id){
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/grn/',
			data: { mode : "convert_qty",  type : type,base_qty:base_qty_hide,conv_qty:conv_qty_hide,product_id:product_id},
			success: function(response)
			{
				
				var arr = jQuery.parseJSON(response);			
				if(type===1){
					$("#conv_grn_qty_hide"+cnt).val(conv_qty);
				}else if(type===2){
					$("#grn_qty_hide"+cnt).val(base_qty);
				}
				
				if(type===1){
					$("#grn_qty"+cnt).val(arr.show_qty);
					$("#grn_qty_hide"+cnt).val(arr.hide_qty);
				}else if(type===2){
					$("#conv_grn_qty"+cnt).val(arr.show_qty);
					$("#conv_grn_qty_hide"+cnt).val(arr.hide_qty);
				}else{
					$("#conv_grn_qty"+cnt).val(arr.show_qty);
					$("#conv_grn_qty_hide"+cnt).val(arr.hide_qty);
					$("#grn_qty"+cnt).val(arr.show_qty);
					$("#grn_qty_hide"+cnt).val(arr.hide_qty);
				}
			}
		});
	}else{
		toastr.warning("Select Product First", "WARNING");
		$("#conv_grn_qty"+cnt).val("0");
		$("#conv_grn_qty_hide"+cnt).val("0");
		$("#grn_qty"+cnt).val("0");
		$("#grn_qty_hide"+cnt).val("0");
	}
}
function remove_data(count){
	var conf = confirm("Are you sure want to Remove?");
	if(conf){
		$("#trid"+count).html("");
	}
}

function show_batch_data(cnt,main_pending_qty,product_id,grn_id=''){
	
	var grn_no = $("#grn_no").val();
	
	
	var qty = $("#conv_grn_qty"+cnt).val();
	if(qty == '')
	{
	toastr.warning("Please Enter Qty", "WARNING");
	return false;
	}
	
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/grn/',
		data: { mode : "batch_model_open",qty:qty,main_pending_qty:main_pending_qty,grn_no:grn_no},
		success: function(response)
		{			
			$('#bs-batch-modal').modal('show');
			$("#main_product_qty").val(main_pending_qty);
			$("#grn_no").val(grn_no);
			
			$("#product_id").val(product_id);
			
			$("#batch_data").html(response);
				$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			}); 			
		}
	});
} 

function validate_batch_data(){
	var main_qty=$("#main_product_qty").val();
	
	var product_id=$("#product_id").val();	
	var total_delivery_qty=document.getElementsByName('batch_qty[]');
	var total_arry_sr=document.getElementsByName('arry_sr[]');
	var cnt=total_delivery_qty.length;
	
	var grandtotal_delivery_qty=0;
	var count=$("#count").val();
	
	main_qty=parseFloat(main_qty).toFixed(3);
	var qval="0";
	for(var i=0;i<cnt;i++)
	{	
		grandtotal_delivery_qty+=parseFloat(total_delivery_qty[i].value);
		var grandtotal_delivery_qty_new=grandtotal_delivery_qty;
		grandtotal_delivery_qty_new=parseFloat(grandtotal_delivery_qty_new).toFixed(3);
		
		
		/*var count1=total_arry_sr[i].value;
		
		
		
		//alert(count1);
		//alert(qval);
		if(count1!="1"){
			if(qval==="1"){
				//alert(qval);
				//alert(count1)
				$('#field'+count1).html('');
			}
		}
		if(parseFloat(grandtotal_delivery_qty_new)>=parseFloat(main_qty)){
			qval="1";
		}else{
			qval="0";
		}*/
	}
	var total=parseFloat(grandtotal_delivery_qty).toFixed(3);
	
	if(parseFloat(total)>parseFloat(main_qty)){
		$("#m_addrow").hide();
	}else{
		if(parseFloat(total)<parseFloat(main_qty)){
			$("#m_addrow").hide();
			count=parseFloat(count)+parseFloat(1);
			$('#count').val(count);
			var pending_qty=parseFloat(main_qty)-parseFloat(total); 
			
			$("#mix_batch_table").append('<tr id="field'+count+'"><td class="text-center" style="vertical-align:center;"><input type="text" class="form-control batch_no" id="batch_no'+count+'" name="batch_no[]" placeholder="Batch No" onchange="qty_wise_date_validation('+count+');" ></td><td class="text-center;" style="vertical-align:center;"><input type="text" class="form-control batch_qty" id="batch_qty'+count+'" name="batch_qty[]"  onchange="validate_batch_data();" placeholder="'+pending_qty+'" onkeyup="qty_wise_date_validation('+count+');" /></td><td   class="text-center" style="vertical-align:center;">	<input type="text" class="form-control default-date-picker valid mfg_date" id="mfgdate'+count+'" name="mfg_date[]" placeholder="Mfg date" onchange="get_exp_date('+count+');"  ></td><td   class="text-center" style="vertical-align:center;"><input type="text" class="form-control exp_date" id="expdate'+count+'" name="exp_date[]" placeholder="Exp date" readonly></td><td class="text-center" style="vertical-align:center;" ><button type="button" class="btn btn-round btn-danger btn-xs" onclick="remove_batch_data('+count+');" id="fieldremove'+count+'"><i class="fa fa-times"></i></button><input type="hidden" name="arry_sr[]" id="arry_sr" value="'+count+'" /></td></tr>');
			
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});

		}else{
			$("#m_addrow").show();
		}
	}
	if(qval==="1"){
		//validate_dilivary_date();
	}
}
function remove_batch_data(count){
	$('#field'+count).html('');
	validate_batch_data();
}

function qty_wise_date_validation(count){
	var delivery_date=$("#delivery_date"+count).val();
	var delivery_qty=$("#delivery_qty"+count).val();
	if(delivery_date===""){
		toastr.warning("Select Date", "ERROR")
		$("#delivery_date"+count).focus();
		$("#delivery_qty"+count).val("");
	}
}

function save_batch_data()
{
	var grn_no =$("#grn_no").val();
	
	var batch_no_arr=[];
	var batch_qty_arr=[];
	var mfg_date_arr=[];	
	var exp_date_arr=[];		
	
	var i = 0;
	var j = 0;
	var k = 0;
	var l = 0;

	$('input.batch_no').each(function(){ 	
	
		if($(this).val() != '')
		{
			batch_no_arr[i]=$(this).val();		
			
		}
		i++;
	}); 
	
	$('input.batch_qty').each(function(){ 	
		if($(this).val() != '')
		{
			batch_qty_arr[k]=$(this).val();					
		}
		k++;
	}); 
	
	$('input.mfg_date').each(function(){ 	
		if($(this).val() != '')
		{
			mfg_date_arr[l]=$(this).val();					
		}
		l++;
	}); 
	
	$('input.exp_date').each(function(){ 	
		if($(this).val() != '')
		{
			exp_date_arr[j]=$(this).val();					
		}
		j++;
	}); 
		
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/grn/',
		data: { mode : "save_batch_data",batch_no_arr:batch_no_arr,batch_qty_arr:batch_qty_arr,mfg_date_arr:mfg_date_arr,exp_date_arr:exp_date_arr,grn_no:grn_no},
		success: function(response)
		{
			if(response == 'true')
			{
				toastr.success("INSERTED SUCCESSFULLY", "SUCCESS");
				$('#bs-batch-modal').modal('hide');
				Unloading();
			}
			else
			{
				toastr.warning("Something Went Wrong", "ERROR");
				Unloading();
				return false;
			}
			
		}
	});	
}

function get_exp_date(cnt)
{	
	var product_id=$("#product_id").val();	
	var mfgdate=$("#mfgdate"+cnt).val();
	
	$.ajax({
		type: "POST",
		url:  root_domain+purchase_domain+'app/grn/',
		data: { mode : "get_exp_date_by_product",product_id:product_id,mfgdate:mfgdate},
		success: function(response)
		{
			if(response == '0')
			{
				toastr.warning("Expiry Days Not Set Please check in item master", "ERROR")
				return false;
			}
			else
			{					
				$("#expdate"+cnt).val(response);
			}
		
		}
		});
	
	
	
}



