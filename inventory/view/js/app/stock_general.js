//var datatable;
$(document).ready(function() {
	// validate vendor add form on keyup and submit
	product_load();
	load_products();
	show_deduct_product();
	show_in_product();
	product_in_load();
	load_in_products();
	load_stock_general_datatable();
	$("#stock_general_add").validate({
		rules: {
			stock_general_no:{
				required: true			
			},
			stock_general_Date: {
				required: true			
			},
		},
		messages: {
			stock_general_no:{
				required: "Enter General No"			
			},
			stock_general_Date: {
				required: "Select General Date"
			},
		}
	}); 
});

$("#stock_general_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#stock_general_add").valid()) {
		return false;
	}
	/*product_count = get_product_check();
	if(product_count<=0){
		$('#item_id').select2("focus");
		toastr.warning("AT LEAST ONE PRODUCT SHOUD BE REQUIRED", "ERROR")
		return false;	
	}*/
	//var request_url = $("#requesturi").val();
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled","disabled");		
	$('#save').attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	$.ajax({
		cache:false,
		url: root_domain+inventory_domain+'app/stock_general/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);

			if(arr.msg == '1') {
				Unloading();
				toastr.success("GENERAL STOCK ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+inventory_domain+"stock_general_list"; 
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
				toastr.success("GENERAL STOCK UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location=root_domain+inventory_domain+"stock_general_list"; 
			}
			$('#stock_general_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
});

function load_deduct_productdetail(val,i="") {
	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/stock_general/',
		data: { mode : "load_productdata",eid :val },
		success: function(response)
		{
			var obj =jQuery.parseJSON(response)
			load_deduct_product_unit(val,obj.product_base_unit);
			load_stock_qty();
			
			$("#deduct").hide();
			$("#deduct_batch_wise").show();
		}
	});
}

function load_stock_general_datatable(){
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
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+inventory_domain+'app/stock_general/',
		"fnServerParams": function ( aoData ) {
			aoData.push( 
				{ "name": "mode", "value": "fetch" }
				//{ "name": "date", "value": date }
				);
		},
		/*"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		},
		"fnFooterCallback": function ( nRow, aaData, iStart, iEnd, aiDisplay ) {
			var iPageMarket = 0;
			for ( var i=0 ; i<aaData.length ; i++ )
			{
				iPageMarket += aaData[i][5]*1;

			}

			var nCells = nRow.getElementsByTagName('th');
			nCells[1].innerHTML = parseFloat(iPageMarket ).toFixed(2);
		}*/
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function load_deduct_product_unit(product_id,edit_unit){
	/*alert(product_id);
	alert(edit_unit);*/
	if(product_id){

	}else{
		var product_id=$("#product_deduct_id").val();
	}
	if(edit_unit){

	}else{
		var edit_unit=$("#deduct_unit_id").val();
	}
	//alert(product_id);
	if(product_id)//tax calculation on total 
	{
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain+ inventory_domain +'app/stock_general/',
			data: { mode : "load_product_unit", product_id : product_id},
			success: function(response)
			{
				var obj=jQuery.parseJSON(response);
				$("#deduct_unit_id").html(obj.unit_option);
				
				if(edit_unit!=0){
					$("#deduct_unit_id").val(edit_unit);
					if(obj.product_base_unit===edit_unit){
						if(obj.product_base_unit != obj.product_conv_unit){
							$("#base_deduct_unit_block").show();
							$("#convert_deduct_unit_block").hide();
							$("#product_conv_qty").attr("readonly","readonly");
							$("#product_deduct_qty").removeAttr("readonly","readonly");
						}else{
							$("#convert_unit_block").hide();
						}
						$("#pro_cal_type").val("product_qty_hide");
					}else{
						if(obj.product_base_unit != obj.product_conv_unit){
							$("#base_deduct_unit_block").hide();
							$("#product_qty").attr("readonly","readonly");
							$("#product_conv_qty").removeAttr("readonly","readonly");
							$("#convert_deduct_unit_block").show();
						}else{
							$("#base_unit_block").hide();
						}
						$("#pro_cal_type").val("product_conv_qty_hide");
					}	
				}else{
					$("#base_deduct_unit_block").show();
					$("#product_qty").removeAttr("readonly","readonly");
					$("#product_conv_qty").removeAttr("readonly","readonly");
					$("#convert_deduct_unit_block").hide();
					$("#pro_cal_type").val("product_qty_hide");
				}

				$('#deduct_unitid').val(obj.product_base_unit);
				$('#conv_deduct_unitid').val(obj.product_conv_unit);
				
				$('#deduct_unit_show').html(obj.base_unit_name);
				$('#convert_deduct_unit_show').html(obj.convert_unit_name);
				load_stock_qty();
			}
		});
	}
}

function load_in_productdetail(val,i="") {
	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/stock_general/',
		data: { mode : "load_productdata",eid :val },
		success: function(response)
		{
			var obj =jQuery.parseJSON(response)
			load_in_product_unit(val,obj.product_base_unit);
			/*if(obj.batch_wise_stock_manage==1){
				$("#in_st").hide();
				$("#batch_wise_in_st").show();
			}else{
				$("#batch_wise_in_st").hide();
				$("#in_st").show();
			}*/
		}
	});
}

function load_in_product_unit(product_id,edit_unit){
	if(product_id){

	}else{
		var product_id=$("#product_in_id").val();
	}
	if(edit_unit){

	}else{
		var edit_unit=$("#in_unit_id").val();
	}
	//alert(product_id);
	if(product_id)//tax calculation on total 
	{
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain+ inventory_domain +'app/stock_general/',
			data: { mode : "load_product_unit", product_id : product_id},
			success: function(response)
			{
				var obj=jQuery.parseJSON(response);
				$("#in_unit_id").html(obj.unit_option);

				if(edit_unit!=0){
					$("#in_unit_id").val(edit_unit);
					if(obj.product_base_unit===edit_unit){
						if(obj.product_base_unit != obj.product_conv_unit){
							$("#base_rate_block").show();
							$("#base_in_unit_block").show();
							$("#convert_in_unit_block").hide();
							$("#conv_rate_block").hide();
							$("#product_in_conv_qty").attr("readonly","readonly");
							$("#product_in_qty").removeAttr("readonly","readonly");
						}else{
							$("#convert_in_unit_block").hide();
							$("#conv_rate_block").hide();
						}
						$("#pro_cal_type").val("product_qty_hide");
					}else{
						if(obj.product_base_unit != obj.product_conv_unit){
							$("#base_rate_block").hide();
							$("#base_in_unit_block").hide();
							$("#product_in_qty").attr("readonly","readonly");
							$("#product_in_conv_qty").removeAttr("readonly","readonly");
							$("#convert_in_unit_block").show();
							$("#conv_rate_block").show();
						}else{
							$("#base_rate_block").hide();
							$("#base_unit_block").hide();
						}
						$("#pro_cal_type").val("product_conv_qty_hide");
					}	
				}else{
					$("#base_in_unit_block").show();
					$("#base_rate_block").show();
					$("#conv_rate_block").hide();
					$("#product_qty").removeAttr("readonly","readonly");
					$("#product_in_conv_qty").removeAttr("readonly","readonly");
					$("#convert_in_unit_block").hide();
					$("#pro_cal_type").val("product_qty_hide");
				}

				$('#in_unitid').val(obj.product_base_unit);
				$('#conv_in_unitid').val(obj.product_conv_unit);
				
				$('#in_unit_show').html(obj.base_unit_name);
				$('#convert_in_unit_show').html(obj.convert_unit_name);
				load_in_product_stock();
			}
		});
	}
}

function load_stock_qty(){
	//alert(old_qty);
	Loading(true);
	var product_id = $("#product_deduct_id").val();
	var unit_id=$("#deduct_unit_id").val();
	var base_unitid = $("#deduct_unitid").val();
	var edit_deduct_id = $("#edit_deduct_id").val();
	//alert(old_qty);
	$.ajax({
		type: "POST",
		url: root_domain+ inventory_domain+'app/stock_general/',
		data: { mode : "load_stock_qty", product_id:product_id,unit_id:unit_id,edit_deduct_id:edit_deduct_id },
		success: function(data){
			//console.log(data);
			if(unit_id == base_unitid){
				$("#product_deduct_qty").attr("placeholder",data);
			}else{
				$("#product_deduct_conv_qty").attr("placeholder",data);
			}

			$('#product_stock').val(parseFloat(data));
			$('.product_stock_label').show();
			$('#product_stock_label').html(parseFloat(data));
			$("#product_deduct_stock").val(parseFloat(data));
			Unloading();
		}		
	});
}

function product_load(po_type=''){
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=indent_po_pro_type1&search=purchase_pro_search&po_type='+po_type;
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		var len=json[0].length;
		//console.log(json);

		for(var i=0;i<len;i++)
		{	
			testData.push({ id:json['0'][i] ,text: json['1'][i]});
				//alert(json['1'][i]);
			}
		});

	return testData;
}
function load_products($po_type = '')
{
	$('#product_deduct_id').select2({
		data: product_load($po_type),
		placeholder: 'Search Product',
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

function product_in_load(po_type=''){
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=indent_po_pro_type1&search=purchase_pro_search&po_type='+po_type;
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		var len=json[0].length;
		//console.log(json);

		for(var i=0;i<len;i++)
		{	
			testData.push({ id:json['0'][i] ,text: json['1'][i]});
				//alert(json['1'][i]);
			}
		});

	return testData;
}
function load_in_products($po_type = '')
{
	$('#product_in_id').select2({
		data: product_in_load($po_type),
		placeholder: 'Search Product',
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

function show_deduct_product(){
	var eid = $('#eid').val();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/stock_general/',
		data: { mode : "load_deduct_product",eid:eid},
		success: function(data){
			$('#stock_deduct_detail').html(data);				
		}		
	});
}

function show_in_product(){
	var eid = $('#eid').val();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/stock_general/',
		data: { mode : "load_in_product",eid:eid},
		success: function(data){
			$('#stock_in_detail').html(data);				
		}		
	});
}
//Maulik End
function product_convert_deduct_qty(type){
	// console.log(type)
	if(type==2){
		var conv_qty_hide=$("#product_deduct_qty").val();
		var s=parseFloat(conv_qty_hide);
		results = s.toFixed(5);

		var num=$("#product_deduct_qtyh").val();
		var d=parseFloat(num);
		resultb = d.toFixed(5);

		var product_conv_qty_hide=$("#product_deduct_conv_qtyh").val();
	}else{
		var base_qty_hide=$("#product_deduct_conv_qty").val();
		var d=parseFloat(base_qty_hide);
		resultb = d.toFixed(5);
		
		var base_qty_hidess=$("#product_deduct_conv_qtyh").val();
		var s=parseFloat(base_qty_hidess);
		results = s.toFixed(5);

		var conv_qty_hide=$("#product_deduct_qty").val();
	}
	// console.log(base_qty_hide);
	// console.log(conv_qty_hide);
	var base_qty=$("#product_deduct_qty").val();
	var conv_qty=$("#product_deduct_conv_qty").val();
	var product_id=$("#product_deduct_id").val();
	
	if(product_id){
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/purchase_order/',
			data: { mode : "convert_qty",  type : type,base_qty:base_qty_hide,conv_qty:conv_qty_hide,product_id:product_id},
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);			
				if(type===1){
					$("#product_deduct_conv_qtyh").val(conv_qty);
				}else if(type===2){
					$("#product_deduct_qtyh").val(base_qty);
				}
				
				if(type===1){
					$("#product_deduct_qty").val(arr.show_qty);
					$("#product_deduct_qtyh").val(arr.hide_qty);

				}else if(type===2){
					$("#product_deduct_conv_qty").val(arr.show_qty);
					$("#product_deduct_conv_qtyh").val(arr.hide_qty);
					
				}else{
					$("#product_deduct_conv_qty").val(arr.show_qty);
					$("#product_deduct_conv_qtyh").val(arr.hide_qty);
					$("#product_deduct_qty").val(arr.show_qty);
					$("#product_deduct_qtyh").val(arr.hide_qty);
				}
			}
		});
	}else{
		toastr.warning("Select Product First", "WARNING");
		$("#product_deduct_id").select2('focus');
		$("#product_deduct_conv_qty").val("0");
		$("#product_deduct_conv_qtyh").val("0");
		$("#product_deduct_qty").val("0");
		$("#product_deduct_qtyh").val("0");
	}	
}

function product_convert_in_qty(type){
	// console.log(type)
	if(type==2){
		var conv_qty_hide=$("#product_in_qty").val();
		var s=parseFloat(conv_qty_hide);
		results = s.toFixed(5);

		var num=$("#product_in_qtyh").val();
		var d=parseFloat(num);
		resultb = d.toFixed(5);

		var product_conv_qty_hide=$("#product_conv_in_qtyh").val();
	}else{
		var base_qty_hide=$("#product_in_conv_qty").val();
		var d=parseFloat(base_qty_hide);
		resultb = d.toFixed(5);
		
		var base_qty_hidess=$("#product_conv_in_qtyh").val();
		var s=parseFloat(base_qty_hidess);
		results = s.toFixed(5);

		var conv_qty_hide=$("#product_in_qty").val();
	}
	// console.log(base_qty_hide);
	// console.log(conv_qty_hide);
	var base_qty=$("#product_in_qty").val();
	var conv_qty=$("#product_in_conv_qty").val();
	var product_id=$("#product_in_id").val();
	
	if(product_id){
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/purchase_order/',
			data: { mode : "convert_qty",  type : type,base_qty:base_qty_hide,conv_qty:conv_qty_hide,product_id:product_id},
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);			
				if(type===1){
					$("#product_conv_in_qtyh").val(conv_qty);
				}else if(type===2){
					$("#product_in_qtyh").val(base_qty);
				}
				
				if(type===1){
					$("#product_in_qty").val(arr.show_qty);
					$("#product_in_qtyh").val(arr.hide_qty);

				}else if(type===2){
					$("#product_in_conv_qty").val(arr.show_qty);
					$("#product_conv_in_qtyh").val(arr.hide_qty);
					
				}else{
					$("#product_in_conv_qty").val(arr.show_qty);
					$("#product_conv_in_qtyh").val(arr.hide_qty);
					$("#product_in_qty").val(arr.show_qty);
					$("#product_in_qtyh").val(arr.hide_qty);
				}
			}
		});
	}else{
		toastr.warning("Select Product First", "WARNING");
		$("#product_in_id").select2('focus');
		$("#product_in_conv_qty").val("0");
		$("#product_conv_in_qtyh").val("0");
		$("#product_in_qty").val("0");
		$("#product_in_qtyh").val("0");
	}	
}

function convert_rate(){
	var product_id  = $("#product_in_id").val();
	var unit_id     = $("#in_unit_id").val(); 
	var base_rate   = $("#product_in_rate").val();
	var conv_rate   = $("#product_in_conv_rate").val();
	
	if(product_id === ""){
		$('#product_in_id').select2("focus");
		toastr.warning("Please First Product Select", "ERROR")
		return false;	
	}

	if(unit_id === ""){
		$('#in_unit_id').select2("focus");
		toastr.warning("Please First Unit Select", "ERROR")
		return false;	
	}


	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/stock_general/',
		data: { mode : "convert_rate",  base_rate : base_rate ,conv_rate:conv_rate ,unit_id:unit_id ,product_id:product_id},
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			$('#product_in_rate').val(arr.base_rate);
			$('#product_in_conv_rate').val(arr.conv_rate);
		}
	});
}

function add_field_deduct()
{

	//var branch_id = $('#branch_id').val();
	
	/*if($("#product_type").val()=="")
	{
		toastr.warning("Select Product Type", "ERROR")
		$("#product_type").select2('focus');
		return false;
	}*/

	if($("#product_deduct_id").val()==="")
	{		
		toastr.warning("Select Product", "ERROR")
		$("#product_deduct_id").select2('focus')
		return false;
	}
	else if($("#product_deduct_qty").val()==="")
	{		
		toastr.warning("Enter Qty", "ERROR")
		$("#product_deduct_qty").focus();
		return false;
	}
	else if($("#deduct_unit_id").val()==="")
	{		
		toastr.warning("Select Unit", "ERROR")
		//$("#unitid").select2('focus');
		$("#deduct_unit_id").focus();
		return false;
	}
	
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/stock_general/',
		data: { 
			mode : "field_deduct_add",
			edit_id:$("#edit_deduct_id").val(),
			product_id:$("#product_deduct_id").val(),
			product_qty : $("#product_deduct_qty").val(),
			product_conv_qty:$("#product_deduct_conv_qty").val(),
			unit_id:$("#deduct_unitid").val(),
			conv_unitid:$("#conv_deduct_unitid").val(),
			rate_unitid:$("#deduct_unit_id").val(),
			general_stock_id:$("#eid").val(),
			sales_order_id:$("#sales_order_id_deduct").val(),
			for_user_id:$("#user_deduct").val(),
		},
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("PRODUCT ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-batch_wise_stock_deduct-modal").modal("hide");
				$("#product_deduct_id").prop("disabled", false);
				$("#edit_deduct_id").val("");
				$("#product_deduct_id").select2("val","");
				$("#product_deduct_qty").val("");
				$("#product_deduct_conv_qty").val("");
				$("#deduct_unitid").val("");
				$("#deduct_unit_show").html("");
				$("#convert_deduct_unit_show").html("");
				$("#conv_deduct_unitid").val("");
				$("#deduct_unit_id").val("");

				$('#product_stock').val("");
				$('.product_stock_label').hide();
				$('#product_stock_label').html("");
				$("#product_deduct_stock").val("");
				$("#product_deduct_qty").attr("placeholder","");
				$("#product_deduct_conv_qty").attr("placeholder","");
			
				$('#deduct').val('Add');
			}else if(arr.msg == '0'){
				Unloading();
				toastr.warning("SOMETHING WENT WRONG", "WARNING");
			}else if(arr.msg == '-1'){
				Unloading();
				toastr.warning("PRODUCT STOCK NOT AVAILABLE", "WARNING");
			}
			Unloading();
			show_deduct_product();
		}
	});
}

function edit_deduct_data(id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/stock_general/',
		data: { mode : "preedit_deduct",  id : id },
		success: function(response)
		{
			//console.log(response)
			var data = jQuery.parseJSON(response);
			$("#product_deduct_id").prop("disabled", true);
			$("#product_deduct_id").select2('data', { id:data.product_id, text: data.product_name});
			$("#deduct_unit_id").select2("val",data.rate_unit);
			$("#product_deduct_qty").val(data.product_qty);
			$("#deduct_unitid").val(data.unitid);
			$("#product_deduct_qtyh").val(data.product_qty);
			$("#deduct_unit_show").html(data.base_unit);
			$("#product_deduct_conv_qty").val(data.product_conv_qty);
			$("#conv_deduct_unitid").val(data.conv_unitid);
			$("#product_deduct_conv_qtyh").val(data.product_conv_qty);
			$("#convert_deduct_unit_show").val(data.conv_unit);
			$("#sales_order_id_deduct").select2("val",data.sales_order_id);
			$("#user_deduct").select2("val",data.for_user_id);
			
			$("#edit_deduct_id").val(id);

			$("#deduct_batch_wise").show();
			$("#deduct").hide();
			
			$('#deduct').val('Update');
			$("#deduct").css("visibility", "visible");
			load_deduct_product_unit(data.product_id,data.rate_unit);
			Unloading();
		}
	});
}


function add_field_in()
{

	//var branch_id = $('#branch_id').val();
	
	/*if($("#product_type").val()=="")
	{
		toastr.warning("Select Product Type", "ERROR")
		$("#product_type").select2('focus');
		return false;
	}*/

	if($("#product_in_id").val()==="")
	{		
		toastr.warning("Select Product", "ERROR")
		$("#product_in_id").select2('focus')
		return false;
	}
	else if($("#product_in_qty").val()==="")
	{		
		toastr.warning("Enter Qty", "ERROR")
		$("#product_in_qty").focus();
		return false;
	}
	else if($("#in_unit_id").val()==="")
	{		
		toastr.warning("Select Unit", "ERROR")
		//$("#unitid").select2('focus');
		$("#in_unit_id").focus();
		return false;
	}
	
	var baseqty = $("#product_in_qty").val();
	var total_batch_stock=document.getElementsByName('batch_stock[]');
	var cnt=total_batch_stock.length;
	var grandtotal_batch_stock=0;
	baseqty=parseFloat(baseqty).toFixed(4);
	for(var i=0;i<cnt;i++)
	{	
		grandtotal_batch_stock+=parseFloat(total_batch_stock[i].value);
	}
	var total=parseFloat(grandtotal_batch_stock).toFixed(4);

	if(baseqty !=total){
		toastr.warning("Batch Qty Wrong", "ERROR")
		return false;
	}
	
	var total_batch_stock1_arr=[];
	var batch_no_arr=[];	
	var godown_id_arr=[];	
	var arry_edit_arry=[];
	
	var total_batch_stock1 = $('input[name="batch_stock[]"]').val();
	var arry_edit = $('input[name="arry_edit[]"]').val();
	
	i = 0;
	$('input.batch_stock').each(function(){ 
		total_batch_stock1_arr[i++]=$(this).val();
	});  
	
	j = 0;
	$('input.batch_no').each(function(){ 
		batch_no_arr[j++]=$(this).val();
	});  
	
	k = 0;
	$('input.arry_edit').each(function(){ 
		arry_edit_arry[k++]=$(this).val();
	});

	l = 0;
	$('select.godown_id').each(function(){ 
		godown_id_arr[l++]=$(this).select2("val");
	});	

	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/stock_general/',
		data: { 
			mode : "field_in_add",
			total_base_stock:total_batch_stock1_arr,
			godown_id:godown_id_arr,
			batch_no:batch_no_arr,
			arry_edit:arry_edit_arry,
			edit_id:$("#edit_in_id").val(),
			product_id:$("#product_in_id").val(),
			product_qty : $("#product_in_qty").val(),
			product_conv_qty:$("#product_in_conv_qty").val(),
			unit_id:$("#in_unitid").val(),
			conv_unitid:$("#conv_in_unitid").val(),
			rate_unitid:$("#in_unit_id").val(),
			base_rate : $("#product_in_rate").val(),
			conv_rate : $("#product_in_conv_rate").val(),
			general_stock_id:$("#eid").val(),
			sales_order_id:$("#sales_order_id_in").val(),
			for_user_id:$("#user_in").val(),
		},
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("PRODUCT ADDED SUCCESSFULLY", "SUCCESS");
			}else if(arr.msg == '0'){
				Unloading();
				toastr.success("SOMETHING WENT WRONG", "WARNING");
			}
			$("#edit_in_id").val("");
			$("#product_in_id").prop("disabled", false);
			$("#product_in_id").select2("val","");
			$("#product_in_qty").val("");
			$("#product_in_conv_qty").val("");
			$("#in_unit_id").val("");
			$("#conv_in_unitid").val("");
			$("#in_unit_show").html("");
			$("#convert_in_unit_show").html("");
			$("#in_unitid").val("");
			$("#product_in_rate").val("");
			$("#product_in_conv_rate").val("");
			$("#bs-batch_wise_stock_in-modal").modal("hide");

			Unloading();
			show_in_product();
		}
	});
}

function edit_in_data(id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/stock_general/',
		data: { mode : "preedit_in",  id : id },
		success: function(response)
		{
			//console.log(response)
			var data = jQuery.parseJSON(response);
			$("#product_in_id").prop("disabled", true);
			$("#product_in_id").select2('data', { id:data.product_id, text: data.product_name});
			$("#in_unit_id").select2("val",data.rate_unit);
			$("#product_in_qty").val(data.product_qty);
			$("#in_unitid").val(data.unitid);
			$("#product_in_qtyh").val(data.product_qty);
			$("#in_unit_show").html(data.base_unit);
			$("#product_in_conv_qty").val(data.product_conv_qty);
			$("#conv_in_unitid").val(data.conv_unitid);
			$("#product_conv_in_qtyh").val(data.product_conv_qty);
			$("#convert_in_unit_show").val(data.conv_unit);
			$("#product_in_rate").val(data.product_rate);
			$("#product_in_conv_rate").val(data.product_conv_rate);
			$("#sales_order_id_in").select2("val",data.sales_order_id);
			$("#user_in").select2("val",data.for_user_id);
			$("#edit_in_id").val(id)
			$('#in_st').val('Update');
			$("#in_st").css("visibility", "visible");
			load_in_product_unit(data.product_id,data.rate_unit);
			Unloading();
		}
	});
}

function delete_deduct_data(id)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/stock_general/',
			data: { mode : "delete_deduct_data",  eid : id  },
			success: function(response)
			{
				//console.log(response)
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_deduct_product();

					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}

}

function delete_in_data(id)
{
	var r= confirm("Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/stock_general/',
			data: { mode : "delete_in_data",  eid : id  },
			success: function(response)
			{
				//console.log(response)
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_in_product();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}

function delete_stock_general(id){
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/stock_general/',
			data: { mode : "delete_stock_general",  eid : id  },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					load_stock_general_datatable();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}

function batch_wise_deduct_stock_open(){
	//alert($("#product_deduct_qty").val());
	//alert($("#product_deduct_stock").val());
	var product_qty = $("#product_deduct_qty").val();
	var b = parseFloat(product_qty);
	var base = b.toFixed(4);

	var product_stock = $("#product_deduct_stock").val();
	var s = parseFloat(product_stock);
	var stock = s;

	var product_conv_qty = $("#product_deduct_conv_qty").val();
	var c = parseFloat(product_conv_qty);
	var conv = c.toFixed(4);

	if($("#deduct_unit_id").val()==$("#deduct_unitid").val()){
		if(stock<base){
			toastr.warning("PRODUCT STOCK NOT AVAILABLE", "ERROR");
			$("#product_deduct_qty").focus();
			return false;
		}
	}else{
		if(stock<conv){
			toastr.warning("PRODUCT STOCK NOT AVAILABLE", "ERROR");
			$("#product_deduct_conv_qty").focus();
			return false;
		}
	}
	
	
	
	get_godownwise_batch_no();
	var rateunit 		= $("#deduct_unit_id").val();
	var baseunit 		= $("#deduct_unitid").val();
	var baseunitshow	= $("#deduct_unit_show").text();
	var convunitshow	= $("#convert_deduct_unit_show").text();
	var baseqty  		= $("#product_deduct_qty").val();
	var convqty  		= $("#product_deduct_conv_qty").val();
	var product_name 	= $("#product_deduct_id").select2('data').text;
	var product_id 		= $("#product_deduct_id").val();

	$("#produname_deduct").html(product_name+"-----"+baseqty+" "+baseunitshow);

	/*if(rateunit == baseunit){
		$("#produname_deduct").html(product_name+"-----"+baseqty+" "+baseunitshow);
	}else{
		$("#produname_deduct").html(product_name+"-----"+convqty+" "+convunitshow);
	}*/

	$.ajax({
		type: "POST",
		url: root_domain+ inventory_domain+'app/stock_general/',
		data: { mode : "batch_stock_model_open",qty:baseqty,product_id:product_id},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			$("#bs-batch_wise_stock_deduct-modal").modal("show");
			$("#batch_data").html(data.html_data);	
			$("#datatablededuct").html(data.html_data1);
			
			$(".batch_select2").select2({
				width: '100%',
			});	
			load_batch_datatable();
			validate_qty(0);	
		}
	});
}

function batch_wise_in_stock_open(){
	
	var rateunit 		= $("#in_unit_id").val();
	var baseunit 		= $("#in_unitid").val();
	var baseunitshow	= $("#in_unit_show").text();
	var convunitshow	= $("#convert_in_unit_show").text();
	var baseqty  		= $("#product_in_qty").val();
	var convqty  		= $("#product_in_conv_qty").val();
	var product_name 	= $("#product_in_id").select2('data').text;
	var trn_id 			= $("#edit_in_id").val();
	var product_id 		= $("#product_in_id").val();

	$("#produname_in").html(product_name+" ----- "+baseqty+" "+baseunitshow);

	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/stock_general/',
		data: { mode : "batch_stock_model_in_open",qty:baseqty,trn_id:trn_id,product_id:product_id},
		success: function(response)
		{
			$("#bs-batch_wise_stock_in-modal").modal("show");
			$("#batch_in_data").html(response);
			validate_batch_qty();
			if(trn_id == ''){
				$("#m_addrow1").hide();
			}
			$(".godown_id").select2({
				width: '100%'
			});
		}
	});
}

function get_batch_qty(id){
	/*var stock = $("#batch_id").find(':selected').attr("data-stock");
	$("#batch_stock").val(stock);*/

	var batch_no = $("#batch_id").val();
	var unit_id = $("#deduct_unit_id").val();
	var product_id = $("#product_deduct_id").val();
	var st_godown_id = $("#godown_id").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+ inventory_domain+'app/stock_general/',
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

function get_godown_qty(id){
	/*var stock = $("#batch_id").find(':selected').attr("data-stock");
	$("#batch_stock").val(stock);*/

	//var batch_no = $("#batch_id").val();
	var unit_id = $("#deduct_unit_id").val();
	var product_id = $("#product_deduct_id").val();
	var st_godown_id = $("#godown_deduct_id").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+ inventory_domain+'app/stock_general/',
		data: { 
			mode : "get_godown_qty",
			//batch_no:batch_no,
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

function add_batch_qty(){
		
	if($("#batch_id").val()==="")
	{		
		toastr.warning("Select Batch number", "ERROR")
		$("#batch_id").select2('focus')
		return false;
	}
	else if($("#qtyforbatch").val()==="")
	{		
		toastr.warning("Enter Qty", "ERROR")
		$("#qtyforbatch").focus();
		return false;
	}
	else if($("#godown_deduct_id").val()===""){
		toastr.warning("Choose Godown", "ERROR")
		$("#godown_deduct_id").focus();
		return false;
	}

	if(parseFloat($("#qtyforbatch").val())  > parseFloat($("#batch_stock").val())){
		toastr.warning("Stock Not Available", "ERROR")
		$("#qtyforbatch").focus();
		return false;
	}

	var stock_id 	= $("#batch_id").val();
	var qty 		= $("#qtyforbatch").val();
	var product_id 	=  $("#product_deduct_id").val();
	var edit_id 	= $("#edit_deduct_id").val();
	var unit_id 	= $("#deduct_unitid").val();
	var godown_id 	= $("#godown_deduct_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+ inventory_domain+'app/stock_general/',
		data: { mode : "add_batch_qty",qty:qty,product_id:product_id,stock_id:stock_id,
		edit_id:edit_id,unit_id:unit_id,godown_id:godown_id},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			var response1=data.res;
			if(response1.trim() == "1") {
				toastr.success("DATA ADDED SUCCESSFULLY", "SUCCESS");
				$("#batch_id").select2("val","");
				$("#godown_deduct_id").select2("val","");
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

function load_batch_datatable()
{
	var product_id=$('#product_deduct_id').val();
	
	var edit_id = $("#edit_deduct_id").val();
	
	datatable = $("#batch_stock_deduct_table").dataTable({
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
		"sAjaxSource": root_domain + inventory_domain +'app/stock_general/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch_batch_qty" },
				{ "name": "product_id", "value": product_id },
				{"name":"edit_id","value":edit_id} );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function delete_batch_stock_entry(batchstockid){

	$.ajax({
		type: "POST",
		url: root_domain+ inventory_domain+'app/stock_general/',
		data: { mode : "delete_batch_entry",batchstockid:batchstockid},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			var response1=data.res;
			if(response1.trim() == "1") {
				toastr.success("DATA DELETED SUCCESSFULLY", "SUCCESS");
				load_batch_datatable();				
			}
			else if(response1.trim() == "0") {
				toastr.warning("SOMETHING WENT WRONG", "WARNING");
				return false;
			}
			validate_qty(0);
		}
	});
}

function validate_qty(qtyforbatch1){

	var product_qty =  $("#product_deduct_qty").val();
	var product_id =  $("#product_deduct_id").val();
	var edit_id = $("#edit_deduct_id").val();
	var qtyforbatch = qtyforbatch1;
	
	$.ajax({
		type: "POST",
		url: root_domain+ inventory_domain+'app/stock_general/',
		data: { mode : "validate_qty",product_qty:product_qty,product_id:product_id,
		qtyforbatch:qtyforbatch,edit_id:edit_id},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			var response1=data.res;

			if(response1.trim() == "0") {
				$("#qtyforbatch").val('0')
				toastr.warning("Batch Quantity can not greater Product quantity", "WARNING");
				$("#m_addrow").hide();
				return false;
			}else if(response1.trim() == "1") {
				$("#m_addrow").show();
			}else{
				$("#m_addrow").hide();
			}
		}
	});
}

function validate_batch_qty(){
	var unitid = $('#in_unitid').val();
	var main_qty=$("#product_in_qty").val();
	var product_id = $("#product_in_id").val();
	
	var total_batch_stock=document.getElementsByName('batch_stock[]');
	var total_arry_sr=document.getElementsByName('arry_sr[]');
	var cnt=total_batch_stock.length;
	var grandtotal_batch_stock=0;
	var count=$("#count").val();
	main_qty=parseFloat(main_qty).toFixed(4);
	var qval="0";
	for(var i=0;i<cnt;i++)
	{	
		grandtotal_batch_stock+=parseFloat(total_batch_stock[i].value);
		var grandtotal_batch_stock_new=grandtotal_batch_stock;
		grandtotal_batch_stock_new=parseFloat(grandtotal_batch_stock_new).toFixed(4);
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
		if(parseFloat(grandtotal_batch_stock_new)>=parseFloat(main_qty)){
			qval="1";
		}else{
			qval="0";
		}
	}
	var total=parseFloat(grandtotal_batch_stock_new).toFixed(4);

	if(parseFloat(total)>parseFloat(main_qty)){
		$("#m_addrow1").hide();
	}else{
		if(parseFloat(total)<parseFloat(main_qty)){
			$("#m_addrow1").hide();
			count=parseFloat(count)+parseFloat(1);
			$('#count').val(count); 
			var pending_qty=parseFloat(main_qty)-parseFloat(total);
			
			$.ajax({
				type: "POST",
				url: root_domain+ inventory_domain+'app/stock_general/',
				data: { mode : "add_more",count:count,pending_qty:pending_qty,product_id:product_id},
				success: function(response)
				{
					$("#mix_loose_material_table").append(response);
					$("select.godown_id").select2({
						width: '100%'
					});
				}
			});
		}else{
			$("#m_addrow1").show();
		}
	}
	if(qval==="1"){
		//validate_dilivary_date();
	}
}
function remove_batch_data(count){
	$('#field'+count).html('');
	validate_batch_qty();
}

function qty_wise_batch_validation(count){
	var godown_id=$("#godown_id"+count).val();
	var batch_no = $("#batch_no"+count).val();
	var batch_stock=$("#batch_stock"+count).val();
	var arry_edit = $("#arry_edit"+count).val();
	var product_id = $("#product_in_id").val();
	/*var product_id = $("#product_in_id").val();*/
	if(godown_id===""){
		toastr.warning("Choose Godown", "ERROR")
		$("#godown_id"+count).focus();
		$("#batch_stock"+count).val("");
	}
	var i=0;
	$('input.batch_no').each(function(){ 
		var batch_id = $(this).attr('id');
		if(batch_id != 'batch_no'+count){
			if($("#"+batch_id).val()==batch_no){
				i++;				
			}	
		}
	});

	$.ajax({
		type: "POST",
		url: root_domain+ inventory_domain+'app/stock_general/',
		data: { mode : "check_batch_no",batch_no:batch_no,arry_edit:arry_edit,product_id:product_id},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			var batch_count = data.cnt;
			if(data.batch_wise_stock_manage==1){
				if(i>0 || batch_count >0 && batch_no !=""){
					toastr.warning("Already Exist Batch No.", "ERROR")
					$("#batch_no"+count).val("");
				}
			}
		}
	});
}

function stock_approval(gstock_id,general_stock_no){
	
	$('#preview_general_stock_approval_hist_modal').modal('show');
	$('#general_stock_no').html(general_stock_no);
	$('#ref_stock_id').val(gstock_id);
	load_general_stock_hist_datatable();
}

function add_general_stock_apprv_hist(){
	var form_data = {
		mode:"add_general_stock_apprv_hist",
		approve_status:$('#stock_approve_status').val(),
		approve_remark:$('#stock_approve_remark').val(),
		general_stock_id:$('#ref_stock_id').val()
	};
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/stock_general/',
		data: form_data,
		success: function(response)
		{
			$('#preview_general_stock_approval_hist_modal').modal('hide');
			$('#stock_approve_status').select2("val","2");
			$('#stock_approve_remark').val("");
			load_general_stock_hist_datatable();
			load_stock_general_datatable();
			Unloading();
		}
	});	
}

function load_general_stock_hist_datatable(){
	var general_stock_id = $('#ref_stock_id').val();
	
	$("#stock-general-history-datatable").dataTable({
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
		"aLengthMenu": [[5, 10, 20, -1], [5, 10, 20,"All"]],
		"iDisplayLength": 5,
		"sAjaxSource": root_domain+inventory_domain+'app/stock_general/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "load_general_stock_hist_datatable" }, { "name": "general_stock_id", "value": general_stock_id }  );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
	// validate the comment form when it is submitted  
}

function get_godownwise_batch_no(){
	Loading();
	var form_data = {
		mode:"get_godownwise_batch_no",
		godown_id:$('#godown_deduct_id').val(),
		product_id:$("#product_deduct_id").val()
	};

	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/stock_general/',
		data: form_data,
		success: function(response)
		{
			$('#batch_id').select2("val","");
			$('#batch_id').html(response);
			Unloading();
		}
	});
}
function load_in_product_stock(){
	//Loading();
	var product_id=$("#product_in_id").val();
	var unit_id=$("#in_unit_id").val();
	if(product_id=="" && unit_id==""){
		return false;
	}
	var form_data = {
		mode:"load_in_product_stock",
		product_id:product_id,
		unit_id:unit_id
	};

	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/stock_general/',
		data: form_data,
		success: function(response)
		{
			$("#insto").show();
			$("#insto").html("Stock:"+response);
			//Unloading();
		}
	});
}