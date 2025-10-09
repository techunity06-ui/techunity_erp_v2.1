//var datatable;
$(document).ready(function() {
	product_load();
	add_product_batch_wise();
	load_returnable_channal_data();
	auto_remove_temp_data();
	show_item_code_data();
	return_challan_type_permission();
	get_return_date();
	sales_order();
	// validate vendor add form on keyup and submit
	$("#returnable_channal_add").validate({
		rules: {
			branch_id:{
				required: true			
			},
			cust_id: {
				required: true			
			},
		},
		messages: {
			branch_id:{
				required: "Select Branch"			
			},
			cust_id: {
				required: "Select Customer"
			},
		}
	}); 
});

$("#returnable_channal_add").on('submit',function(e) {
	var returnable_type = $('input[name="returnable_type"]:checked').val();
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#returnable_channal_add").valid()) {
		return false;
	}
	product_count = get_product_check();
	if(product_count<=0){
		$('#item_id').select2("focus");
		toastr.warning("AT LEAST ONE PRODUCT SHOUD BE REQUIRED", "ERROR")
		return false;	
	}
	var request_url = $("#requesturi").val();
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled","disabled");		
	$('#save').attr("disabled","disabled");		
	 
	var form_data=new FormData(this);
	form_data.append('returnable_type',returnable_type);
	$.ajax({
		cache:false,
		url: root_domain+inventory_domain+'app/returnable_channal/',
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
				toastr.success("CHANNAL ADDED SUCCESSFULLY", "SUCCESS");
				if(request_url == 'non_returnable_channal_add'){
					window.location=root_domain+inventory_domain+"non_returnable_channal_list"; 
				}else{
					window.location=root_domain+inventory_domain+"returnable_channal_list"; 
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
			else if(arr.msg== 'update') {
				toastr.success("CHANNAL UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				if(request_url == 'non_returnable_channal_update'){
					window.location=root_domain+inventory_domain+"non_returnable_channal_list"; 
				}else{
					window.location=root_domain+inventory_domain+"returnable_channal_list"; 
				}
			}
			$('#returnable_channal_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function add_returnable_channal()
{
	var returnable_type = $('input[name="returnable_type"]:checked').val();
	// alert($("#item_id").val());
	if(!$("#item_id").val()){
		toastr.warning("Select Product", "ERROR");
		$("#item_id").select2('focus');
		return false;
	}
	else if(!$("#unit_id").val()){		
		toastr.warning("Select Unit", "ERROR");
		$("#unit_id").select2('focus');
		return false;
	}
	else if(!$("#item_stock").val() && returnable_type != "without_stock"){		
		toastr.warning("Enter Item Stock", "ERROR");
		$("#item_stock").select2('focus');
		return false;
	}
	else if(!$("#item_qty").val()){		
		toastr.warning("Enter Item Qty", "ERROR");
		$("#item_qty").select2('focus');
		return false;
	}
	else if(!$("#item_price").val()){		
		toastr.warning("Enter Item Price", "ERROR");
		$("#item_price").select2('focus');
		return false;
	}
	var promaxval=$('#item_qty').attr("max");
	
	if(parseInt(promaxval)<parseInt($("#item_qty").val()) && returnable_type != "without_stock")
	{
		toastr.warning("Please Check Pending Qty", "ERROR")
		return false;
	}
	var return_type=$("#return_type").val();
	if(return_type==="product_wise"){
		var mqty=$("#item_qty").val();
		
		var total_delivery_qty=document.getElementsByName('delivery_qty[]');
		var cnt=total_delivery_qty.length;
		var grandtotal_delivery_qty=0;
		mqty=parseFloat(mqty).toFixed(5);
		for(var i=0;i<cnt;i++)
		{	
			grandtotal_delivery_qty+=parseFloat(total_delivery_qty[i].value);
		}
		var total=parseFloat(grandtotal_delivery_qty).toFixed(5);

		if(mqty!=total){
			toastr.warning("Return Qty Wrong", "ERROR")
			return false;
		}
	}

	var total_delivery_qty1_arr=[];
	var delivery_date_arr=[];	
	var arry_edit_arry=[];
	//var total_delivery_qty1=document.getElementsByName('delivery_qty[]');
	var total_delivery_qty1 = $('input[name="delivery_qty[]"]').val();
	var arry_edit = $('input[name="arry_edit[]"]').val();
	
	i = 0;
	$('input.delivery_qty').each(function(){
		total_delivery_qty1_arr[i++]=$(this).val();
	});  
	
	j = 0;
	$('input.delivery_date').each(function(){ 
		delivery_date_arr[j++]=$(this).val();
	});  
	

	
	k = 0;
	$('input.arry_edit').each(function(){ 
		arry_edit_arry[k++]=$(this).val();
	});
	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/returnable_channal/',
		data: { 
			mode : "fieldadd",
			total_delivery_qty:total_delivery_qty1_arr,
			delivery_date:delivery_date_arr,
			arry_edit:arry_edit_arry,
			return_date: $("#return_date").val(),
			eid : $("#eid").val(), 
			edit_id:$("#edit_id").val(),
			return_type:$("#return_type").val(),
			item_id:$("#item_id").val(),
			item_hsn_code:$("#hsncode").text(),
			item_description:$("#item_description").val(),
			unit_id:$("#unit_id").val(),
			item_stock:$("#item_stock").val(),
			item_qty:$("#item_qty").val(),
			item_price:$("#item_price").val(),
			returnable_type : returnable_type
		},
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.resp == '1') {
				Unloading();
				toastr.success("DATA ADDED SUCCESSFULLY", "SUCCESS");
				$('#bs-return_date-modal').modal('hide');
				$("#item_id").select2("val","");
				$("#item_id").select2('focus');
				$("#item_description").val("");
				$("#unit_id").select2('val','');
				$("#item_stock").val("");
				$("#item_stock").attr("placeholder","");
				$("#item_qty").val("");
				$("#item_qty").attr("placeholder","");
				$("#item_price").val("");
				$(".hsncode").hide();
				$('#add_returnable_channal_btn').val('Add');
				Unloading();
				show_item_code_data();
			}
			else if(arr.resp == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(arr.resp== 'update') {
				toastr.success("DATA UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				$('#bs-return_date-modal').modal('hide');
				$("#item_id").select2("val","");
				$("#item_id").select2('focus');
				$("#item_description").val("");
				$("#unit_id").select2('val','');
				$("#item_stock").val("");
				$("#item_stock").attr("placeholder","");
				$("#item_qty").val("");
				$("#item_qty").attr("placeholder","");
				$("#item_price").val("");
				$(".hsncode").hide();
				$("#edit_id").val("");
				$('#add_returnable_channal_btn').val('Add');
				Unloading();
				show_item_code_data();
			}
			$("#isbatchwise").val('');
			$("#edit_id").val('');
			add_product_batch_wise();
			$('#bs-batch_wise_stock-modal').modal('hide');
			
		}
	});
}	

function show_item_code_data()
{
	var returnable_type = $('input[name="returnable_type"]:checked').val();
	var form_mode=$('#mode').val();
	var returnable_id=$('#eid').val();
	Loading();
	$.ajax({
		type: "POST",  
		url: root_domain+inventory_domain+'app/returnable_channal/',
		data: { mode : "load_returnable_channal_info", returnable_id:returnable_id,form_mode:form_mode,returnable_type:returnable_type },
		success: function(data){
			var obj = jQuery.parseJSON(data);
			$('#table_returnable_channal_data').html(obj.html_data);
			if(obj.stock == 1 ){
				$('#save').attr('disabled','disabled');
			}else{
				$('#save').removeAttr('disabled');
			}

			if(obj.temp_data_count > 0){
				$('.returnable_type').attr('disabled','disabled');
			}else{
				$('.returnable_type').removeAttr('disabled');
			}
			Unloading();
		}		
	});
}

function approve_returnable_channal(id) 
{
	$('#preview_returnable_approval_hist_modal').modal('show');
	$('#ref_ord_id').val(id);

	$.ajax({
		type: "POST",  
		url: root_domain+inventory_domain+'app/returnable_channal/',
		data: { mode : "load_challan_data", id:id },
		success: function(data){
			var obj = jQuery.parseJSON(data);
			$("#challan_type").html(obj.returnable_type);
			$("#party_name").html(obj.l_name);
			$("#challan_no").html(obj.channal_id);
			$("#challan_date").html(obj.date);
		}		
	});
	load_returnable_channal_item_data();
}

function load_returnable_channal_data()
{
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
		"sAjaxSource": root_domain+inventory_domain+'app/returnable_channal/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function load_returnable_channal_item_data()
{
	var returnable_id = $('#ref_ord_id').val();
	datatable = $("#returnable-channal-datatable").dataTable({
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
		"sAjaxSource": root_domain+inventory_domain+'app/returnable_channal/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetchitemlist" },{ "name": "returning_id", "value": returnable_id });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}


function edit_returnable_channal_item_data(returnable_channal_id)
{ 
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/returnable_channal/',
		data: { mode:"preedit", returnable_channal_id:returnable_channal_id },
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			$("#item_id").select2('data', { id:data.item_id, text: data.product_name});
			//$("#item_id").select2("val",data.item_id);
			$('#hsncode').text(data.item_hsn);
			$(".hsncode").show();
			$("#item_description").val(data.item_description);
			$("#unit_id").select2("val",data.item_unit_id);
			$("#item_qty").val(data.item_qty);
			$("#item_price").val(data.item_price);
			$("#edit_id").val(returnable_channal_id);
			$('#add_returnable_channal_btn').val('Update');
			$("#isbatchwise").val(data.batch_wise_stock_manage);
			if(data.batch_wise_stock_manage == 1){
				$('#addrow1').val('Update');
			}else{
				$('#addrow').val('Update');
			}
			add_product_batch_wise();
			Unloading();
			load_stock_qty(data.item_id,0);
		}
	});
}

function check_approve_returnable_channal(returnable_channal_id, row_key)
{
	var r= confirm(" Are you sure want to approve qty ?");
	var row_value = $("#"+row_key).val();
	var row_remark_desc = $("#remark_"+row_key).val();
	if(r) {
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/returnable_channal/',
			data: { mode:"approve_data", returnable_channal_id:returnable_channal_id,row_value: row_value,row_remark_desc: row_remark_desc},
			success: function(response)
			{
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA APPROVE SUCCESSFULLY", "SUCCESS");
					load_returnable_channal_item_data();
					load_returnable_channal_data();
				}else if(response.trim() == "-1") {
					toastr.warning("STOCK IS NOT AVAILABLE IN GODOWN", "WARNING");
					load_returnable_channal_item_data();
					load_returnable_channal_data();
				}else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}

function check_disapprove_returnable_channal(returnable_channal_id, row_key){
	
	if($("#remark_"+row_key).val()==''){
		toastr.warning("Disapprove Remark Must Be required", "WARNING");
		$("#remark_"+row_key).focus();
		return false;
	}
	var r= confirm("Are you sure want to Disapprove qty ?");
	var row_value = $("#"+row_key).val();
	var approve_qty = $('#approve_'+row_key).val();
	var disapprove_qty = $('#disapprove_'+row_key).val();
	var row_remark_desc = $("#remark_"+row_key).val();

	if(r) {
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/returnable_channal/',
			data: { mode:"disapprove_data", returnable_channal_id:returnable_channal_id,row_value: row_value,approve_qty:approve_qty,disapprove_qty:disapprove_qty,row_remark_desc: row_remark_desc},
			success: function(response)
			{
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DISAPPROVE SUCCESSFULLY", "SUCCESS");
					load_returnable_channal_item_data();
					load_returnable_channal_data();
				}else if(response.trim() == "-1") {
					toastr.warning("STOCK IS NOT AVAILABLE IN GODOWN", "WARNING");
					load_returnable_channal_item_data();
				}else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});
	}

}

function delete_returnable_channal_item_data(returnable_channal_id)
{
	var r= confirm(" Are you sure want to delete ?");
	
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/returnable_channal/',
			data: { mode:"delete_data", returnable_channal_id:returnable_channal_id},
			success: function(response)
			{
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					show_item_code_data();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function delete_returnable_channal(returnable_channal_id)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/returnable_channal/',
			data: { mode:"delete_returnable_channal", returnable_channal_id:returnable_channal_id},
			success: function(response)
			{
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					load_returnable_channal_data();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}


function load_productdetail(product_id) {
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/returnable_channal/',
		data: { mode:"load_productdetail", product_id:product_id},
		success: function(response)
		{
			var obj=jQuery.parseJSON(response);
			$('#item_description').val(obj.product_desc);
			$('#unit_id').select2("val",obj.product_base_unit);
			$('#item_qty').val(0);
			$('#isbatchwise').val(obj.batch_wise_stock_manage);
			 add_product_batch_wise();
			$('#item_price').val(obj.product_sale_rate);
			load_stock_qty(product_id,0);
		}
	});
	
} 

function load_stock_qty(product_id,old_qty){
	var returnable_type = $('input[name="returnable_type"]:checked').val();

	if(returnable_type == "without_stock"){
		return false;
	}
	Loading(true);
	var unit_id=$("#unit_id").val();
	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/returnable_channal/',
		data: { mode : "load_stock_qty", product_id:product_id,unit_id:unit_id },
		success: function(data){
			//console.log(data);
			var stock_qty = parseFloat(old_qty)+parseFloat(data)+parseFloat($("#item_qty").val());
			$('#item_stock').attr("placeholder",data);
			$('#item_stock').attr("max",stock_qty);
			$('#item_stock').val(stock_qty);
			$("#item_qty").attr("placeholder",data);
			$("#item_qty").attr("max",stock_qty);
			if($("#edit_id").val() != "" && $("#edit_id").val() > 0){
			 $('#item_stock').val(stock_qty);
			}
			Unloading();
		}		
	});
}

function preview_cust_person(){
        var cust_id = $('#cust_id').val();
	if(!cust_id){
		toastr.warning("Choose Customer!!!", "ERROR");
		$('#cust_id').select2('focus');
		return false;
	}
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + 'app/customer/',
		data: { mode : "preview_cust_person", cust_id:cust_id },
		success: function(response)
		{
			//console.log(response);
			var obj =jQuery.parseJSON(response);
			$('#preview_cust_person_modal').modal("show");
			$('#preview_cust_person_modal_div').html(obj.html_resp);
			Unloading();
		}
	});
}

function preview_cust_dtls(){
	var cust_id = $('#cust_id').val();
	if(cust_id){
	
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + 'app/customer/',
			data: { mode:"preview_cust_dtls", cust_id:cust_id },
			success: function(response)
			{
				//console.log(response);
				var obj =jQuery.parseJSON(response);
				$('#preview_cust_dtls_modal1').modal('show');
				$('#preview_cust_dtls_div').html(obj.html_resp);
				Unloading();
			}
		});
	} else {
            toastr.warning("Select Company First", "ERROR");
        }
}

function copy_inq_name() {
	if($('#cust_id').val())
	{
		$('#inquiry_name').val("@"+$('#cust_id option:selected').text());
	}
}

function get_return_date(){
	var returnable_type = $('input[name="returnable_type"]:checked').val();
	if(returnable_type == "returnable"){
		$("#returnable").show();
	}else{
		$("#returnable").hide();
	}

	if(returnable_type == "without_stock"){
		$(".withstock").hide();		
	}else{
		$(".withstock").show();
	}
	add_product_batch_wise();
	
}
function return_challan_type_permission(){
	var return_type = $("#return_type").val();
	if(return_type == 'challan_wise'){
		$(".return_date_product_wise").hide();
		$(".return_date_challan_wise").show();
	}else{
		$(".return_date_product_wise").show();
		$(".return_date_challan_wise").hide();
	}
}

function open_approv_quo1(){
	
	if(!$("#item_id").val()){
		toastr.warning("Select Product", "ERROR");
		$("#item_id").select2('focus');
		return false;
	}
	else if(!$("#unit_id").val()){		
		toastr.warning("Select Unit", "ERROR");
		$("#unit_id").select2('focus');
		return false;
	}
	else if(!$("#item_stock").val()){		
		toastr.warning("Enter Item Stock", "ERROR");
		$("#item_stock").select2('focus');
		return false;
	}
	else if(!$("#item_qty").val()){		
		toastr.warning("Enter Item Qty", "ERROR");
		$("#item_qty").select2('focus');
		return false;
	}
	else if(!$("#item_price").val()){		
		toastr.warning("Enter Item Price", "ERROR");
		$("#item_price").select2('focus');
		return false;
	}
	var promaxval=$('#item_qty').attr("max");
	
	if(parseInt(promaxval)<parseInt($("#item_qty").val()))
	{
		toastr.warning("Please Check Pending Qty", "ERROR")
		return false;
	}
	
	var trn_id=$("#edit_id").val();
	var product_name = $("#item_id").select2('data').text;
	var unit_show = $("#unit_id").select2('data').text;
	var qty = $('#item_qty').val();

	$("#model_product_name").html(product_name+" --- "+qty +" "+unit_show);
	$("#m_trn_id").val(trn_id);
	$("#m_qty").val(qty);
	//alert();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/returnable_channal/',
		data: { mode : "return_date_model_open",qty:qty,trn_id:trn_id},
		success: function(response)
		{
			$('#bs-return_date-modal').modal('show');
			$("#date_des").html(response);
			//$("#m_addrow").hide();
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			
		}
	});
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

function validate_dilivary_date(){
	var main_qty=$("#item_qty").val();
	var total_delivery_qty=document.getElementsByName('delivery_qty[]');
	var total_arry_sr=document.getElementsByName('arry_sr[]');
	var cnt=total_delivery_qty.length;
	var grandtotal_delivery_qty=0;
	var count=$("#count").val();
	main_qty=parseFloat(main_qty).toFixed(5);
	var qval="0";
	for(var i=0;i<cnt;i++)
	{	
		grandtotal_delivery_qty+=parseFloat(total_delivery_qty[i].value);
		var grandtotal_delivery_qty_new=grandtotal_delivery_qty;
		grandtotal_delivery_qty_new=parseFloat(grandtotal_delivery_qty_new).toFixed(5);
		var count1=total_arry_sr[i].value;
		
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
		}
	}
	var total=parseFloat(grandtotal_delivery_qty).toFixed(5);
	
	if(parseFloat(total)>parseFloat(main_qty)){
		$("#m_addrow").hide();
	}else{
		if(parseFloat(total)<parseFloat(main_qty)){
			$("#m_addrow").hide();
			count=parseFloat(count)+parseFloat(1);
			$('#count').val(count); 
			var pending_qty=parseFloat(main_qty)-parseFloat(total);
			
			$("#mix_loose_material_table").append('<tr id="field'+count+'"><td class="text-center" style="vertical-align:center;"><input type="text" class="form-control default-date-picker delivery_date" id="delivery_date'+count+'" name="delivery_date[]" placeholder="Delivery Date" onchange="qty_wise_date_validation('+count+');" ></td><td class="text-center;" style="vertical-align:center;"><input type="text" class="form-control delivery_qty" id="delivery_qty'+count+'" name="delivery_qty[]" onchange="validate_dilivary_date();" placeholder="'+pending_qty+'" onkeyup="qty_wise_date_validation('+count+');" /></td><td class="text-center" style="vertical-align:center;" ><button type="button" class="btn btn-round btn-danger btn-xs" onclick="remove_dilivary_date('+count+');" id="fieldremove'+count+'"><i class="fa fa-times"></i></button><input type="hidden" name="arry_sr[]" id="arry_sr" value="'+count+'" /></td></tr>')
			
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

function remove_dilivary_date(count){
	$('#field'+count).html('');
	validate_dilivary_date();
}


function get_hsn(product_id){
	$.ajax({
        type: "POST",
        async: false,
        url: root_domain+inventory_domain+'app/returnable_channal/',
        data: { mode : "get_hsn_code",product_id:product_id},
        success: function(response)
        {
            if(response != ''){
            	$('#hsncode').text(response);
		$(".hsncode").show();
            }else{
            	toastr.warning("Please select valid HSN code product", "WARNING");
            	$(".hsncode").hide();
		$('#product_id').select2("val","");
		return false;
            }
        }
    });
}

function sales_order(){
	if($('#chln_type').val()=='internal'){
		$('#sales_order').hide();	
	}else{
		$('#sales_order').show();
	}
}

function get_salesorder_no(){
	
	var cust_id  = $("#cust_id").val();
	var sales_id = $("#sales_id").val();
	var form_mode = $('#mode').val();
	

	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/returnable_channal/',
		data: { mode : "get_salesorder_no",  cust_id : cust_id,sales_id:sales_id},
		success: function(responce){
			console.log(responce);
			$('#sales_order_id').html(responce);
			$("#sales_order_id").select2("val","");
			if(sales_id != ""){
				$("#sales_order_id").select2("val",sales_id);
			}
		}
	});
}

function get_sales_order_data_load(){
	var sales_order_id=$('#sales_order_id').val();
	var returnable_type = $('input[name="returnable_type"]:checked').val();
	var eid = $('#eid').val();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/returnable_channal/',
		data: { mode : "get_sales_order_data_load",  sales_order_id : sales_order_id,eid:eid,returnable_type:returnable_type},
		success: function(responce){
			show_item_code_data();
		}
	});	
}



function add_product_batch_wise(){
	var isbatchwise=$("#isbatchwise").val();
	if(isbatchwise==="" || isbatchwise==="0"){
		$(".product_add_batch_wise").show();
		$(".product_add_direct").hide();
	}else{ 
		$(".product_add_batch_wise").show();
		$(".product_add_direct").hide();
	}
	var returnable_type = $('input[name="returnable_type"]:checked').val();

	if(returnable_type != "" && returnable_type == "without_stock"){
		$(".product_add_batch_wise").hide();
		$(".product_add_direct").show();
	}
}

function open_batch_wise_qty(){
		var isbatchwise=$("#isbatchwise").val();
		load_batch_datatable();
		if($("#item_id").val()==="")
		{		
			toastr.warning("Select Product", "ERROR")
			$("#item_id").select2('focus')
			return false;
		} 
		else if ($("#is_power_drive").val() == true && ($("#item_stock").val() == "" || $("#item_stock").val() == 0)) {
			add_returnable_channal();
			return false;
		} 
		else if($("#item_qty").val()=="" || $("#item_qty").val()==0)
		{		
			toastr.warning("Enter Qty", "ERROR")
			$("#item_qty").focus();
			return false;
		}
		var qty=$("#item_qty").val();
		var product_id=$("#item_id").val();
		//var 
		$.ajax({
			type: "POST",
			url: root_domain+ inventory_domain+'app/returnable_channal/',
			data: { mode : "batch_stock_model_open",qty:qty,product_id:product_id,isbatchwise:isbatchwise},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$('#bs-batch_wise_stock-modal').modal('show');
				$("#batch_data").html(data.html_data);	
				$(".batch_select2").select2({
					width: '100%',
				//minimumInputLength: 3
			});	
				validate_qty(0);	
			}
		});
	}

function load_batch_datatable()
{
	var product_id=$('#item_id').val();
	
	var edit_id = $("#edit_id").val();
	
	var isbatchwise=$("#isbatchwise").val();
	datatable = $("#batch_stock_table").dataTable({
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
		"iDisplayLength": 5,
		"sAjaxSource": root_domain + inventory_domain +'app/returnable_channal/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch_batch_qty" },
				{ "name": "product_id", "value": product_id },
				{"name":"edit_id","value":edit_id},
				{"name":"isbatchwise","value":isbatchwise} );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
	}

	function add_batch_qty(){
		var batch_stock = parseFloat($("#batch_stock").val());
        var qtyforbatch = parseFloat($("#qtyforbatch").val());
		if($("#batch_id").val()==="")
		{
			toastr.warning("Select Batch number", "ERROR")
			$("#batch_id").select2('focus')
			return false;
		}

		if($("#godown_id").val()==="")
		{		
			toastr.warning("Select Godown", "ERROR")
			$("#godown_id").select2('focus')
			return false;
		}
		else if($("#qtyforbatch").val()==="")
		{		
			toastr.warning("Enter Qty", "ERROR")
			$("#qtyforbatch").focus();
			return false;
		}
		else if(batch_stock < qtyforbatch){
			toastr.warning("Enter Valid Qty", "ERROR")
			$("#qtyforbatch").focus();
			return false;
		}

		var stock_id = $("#batch_id").val();
		var batch_no = $("#batch_id").select2('data').text;
		var godown_id = $("#godown_id").val();
		var qty = $("#qtyforbatch").val();
		var product_id =  $("#item_id").val();
		var edit_id = $("#edit_id").val();
		var unit_id = $("#unit_id").val();
		$.ajax({
			type: "POST",
			url: root_domain+ inventory_domain+'app/returnable_channal/',
			data: { mode : "add_batch_qty",qty:qty,product_id:product_id,stock_id:stock_id,batch_no:batch_no,
			edit_id:edit_id,unit_id:unit_id,godown_id:godown_id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				var response1=data.res;
				if(response1.trim() == "1") {
					
					toastr.success("DATA ADDED SUCCESSFULLY", "SUCCESS");
					$("#batch_id").select2("val","");
					$("#qtyforbatch").val("");
					$("#batch_stock").val("");
					load_batch_datatable();
					validate_qty(0);
					
				}else if(response1.trim() == "-1") {
					toastr.warning("ALREADY EXISTS", "WARNING");
					return false;
				}
				else if(response1.trim() == "0") {
					toastr.warning("SOMETHING WENT WRONG", "WARNING");
					return false;
				}
			}
		});
	}
	function validate_qty(qtyforbatch1){

		var product_qty =  $("#item_qty").val();
		var product_id =  $("#item_id").val();
		var edit_id = $("#edit_id").val();
		var qtyforbatch = qtyforbatch1;
		
		$.ajax({
			type: "POST",
			url: root_domain+ inventory_domain+'app/returnable_channal/',
			data: { mode : "validate_qty",product_qty:product_qty,product_id:product_id,
			qtyforbatch:qtyforbatch,edit_id:edit_id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				var response1=data.res;

				if(response1.trim() == "0") {
					$("#qtyforbatch").val('0')
					toastr.warning("Batch Quantity can not greater Product quantity", "WARNING");
					$(".addbutton").hide();
					return false;
				}else if(response1.trim() == "1") {
					$(".addbutton").show();
				}else{
					$(".addbutton").hide();
				}
			}
		});
	}
	function get_batch_qty(id){
		/*var stock = $("#batch_id").find(':selected').attr("data-stock");
		$("#batch_stock").val(stock);*/

		var batch_no = $("#batch_id").val();
		var unit_id = $("#unit_id").val();
		var product_id = $("#item_id").val();
		var st_godown_id = $("#godown_id").val();
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+ inventory_domain+'app/returnable_channal/',
			data: { 
				mode : "get_batch_qty",
				batch_no:batch_no,
				st_godown_id:st_godown_id,
				unit_id:unit_id,
				product_id:product_id
			},
			success: function(response)
			{
				var stock = response.trim();
				$("#batch_stock").val(stock);
				Unloading();
				validate_qty(0);
			}
		});
	}

	function delete_batch_stock_entry(batchstockid,returnable_channal_id="",batch_no="",stock_id=""){

		$.ajax({
			type: "POST",
			url: root_domain+ inventory_domain+'app/returnable_channal/',
			data: { mode : "delete_batch_entry",batchstockid:batchstockid,returnable_channal_id:returnable_channal_id,batch_no:batch_no,stock_id:stock_id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				var response1=data.res;
				if(response1.trim() == "1") {
					toastr.success("DATA DELETED SUCCESSFULLY", "SUCCESS");
					load_batch_datatable();			
					open_batch_wise_qty();	
				}
				else if(response1.trim() == "0") {
					toastr.warning("SOMETHING WENT WRONG", "WARNING");
					return false;
				}
				validate_qty(0);
			}
		});
	}

	function get_product_check(){
		var eid = $("#eid").val();
		var product_count = "";
		$.ajax({
			async: false,
			type: "POST",
			url: root_domain+inventory_domain+'app/returnable_channal/',
			data: { mode : "get_product_check",eid:eid },
			success: function(response)
			{
				product_count = response;
			}
		});
		return (product_count);
	}

function product_load(){
	var testData = [];
	// var inquiry_type=$("#inquiry_type").val();
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type=1&type=inventory_pro_type&search=production_pro_search';
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		var len=json[0].length;
			// console.log(len);
			
			for(var i=0;i<len;i++)
			{	
				testData.push({ id:json['0'][i] ,text: json['1'][i]});
				//alert(json['1'][i]);
			}
		});
	load_cat_product('item_id', testData)	
	// return testData;
}

function load_cat_product(id, testData){
	$('#'+id).select2({
		data: testData,
		placeholder: 'search',
		multiple: false,
	    // query with pagination
	    query: function(q) {
	    	var pageSize,
	    	results,
	    	that = this;
	      	pageSize = 20; // or whatever pagesize
	      	results = [];
	      	if (q.term && q.term !== '') {
	        	// HEADS UP; for the _.filter function i use underscore (actually lo-dash) here
	        	results = _.filter(that.data, function(e) {
	        		return e.text.toUpperCase().indexOf(q.term.toUpperCase()) >= 0;
	        	});
	        } else if (q.term === '') {
	        	results = that.data;
	        }
	        q.callback({
	        	results: results.slice((q.page - 1) * pageSize, q.page * pageSize),
	        	more: results.length >= q.page * pageSize,
	        });
		  //$("#product_id").select2('data', { id:1, title: "UPS 3200B"});
		},
	});
}

function auto_remove_temp_data(){
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/returnable_channal/',
		data: { mode : "auto_delete_temp_data"},
		success: function(responce){
			Unloading();
		}
	});
}