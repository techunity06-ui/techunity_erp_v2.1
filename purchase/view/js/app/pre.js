//var datatable;
$(document).ready(function() {
	show_data();
	load_po_req_datatable();
	product_load();
	load_products();
	$("#pre_add").validate({
	rules: {
		branch_id: {
			required: true			
		},
	},
	messages: {
		vender_id: {
			required: "Choose Branch"
		},
	}
	});
});
function reload_data()
{
	load_po_req_datatable();
}	

function load_po_req_datatable()
{
	var po_type_status=$('input[name=po_type_status]:Checked').val();
	var date=$('#rep_date').val();
	var branch_id = $('#branch_id').val();
	
	//alert(po_type_status);
	datatable = $("#po-req-table").dataTable({
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
			"sAjaxSource": root_domain+purchase_domain+'app/pre/',
			"fnServerParams": function ( aoData ) {
				aoData.push( 
					{ "name": "mode", "value": "fetch" },
					{ "name": "po_type_status", "value": po_type_status },
					{ "name": "date", "value": date },
					{ "name": "branch_id", "value": branch_id }
				);
			},
			"fnDrawCallback": function( oSettings ) {
				//alert(oSettings);
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}

function product_detail(id){
	if(id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/pre/',
			data: { mode:"load_product_dtls", product_id:id },
			success: function(response)
			{
				//console.log(response);
				var resp=jQuery.parseJSON(response);
				//$('#rate').val(resp.product_purchase_rate);
				load_product_unit(id,resp.product_base_unit);
				//$('#unit_show').html(resp.unit_name);
				Unloading();						
			}
		});	
	}
}
function load_product_unit(product_id,unit_id){
	if(product_id)//tax calculation on total 
	{
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain+purchase_domain+'app/pre/',
			data: { mode : "load_product_unit", product_id : product_id},
			success: function(response)
			{
				var obj=jQuery.parseJSON(response);
					

					$('#unitid').val(obj.product_base_unit);
					$('#conv_unitid').val(obj.product_conv_unit);
					
					$('#unit_show').html(obj.base_unit_name);
					$('#convert_unit_show').html(obj.convert_unit_name);
					$("#convert_unit_block").show();
					if(obj.unit_status==="1"){
						$("#convert_unit_block").show();
					}else{
						$("#convert_unit_block").hide();
					}
				}
			});
	}
}
function product_convert_qty(type){
	if(type==2){
		var conv_qty_hide=$("#product_qty").val();
		var s=parseFloat(conv_qty_hide);
		results = s.toFixed(3);

		var	num=$("#product_qty_hide").val();
		var d=parseFloat(num);
		resultb = d.toFixed(3);
		var product_conv_qty_hide=$("#product_conv_qty_hide").val();
	}else{
		var base_qty_hide=$("#product_conv_qty").val();
		var d=parseFloat(base_qty_hide);
		resultb = d.toFixed(3);
		
		var base_qty_hidess=$("#product_conv_qty_hide").val();
		var s=parseFloat(base_qty_hidess);
		results = s.toFixed(3);

		var conv_qty_hide=$("#product_qty").val();
	}
	//alert(base_qty_hide);
	//alert(conv_qty_hide);
	var base_qty=$("#product_qty").val();
	var conv_qty=$("#product_conv_qty").val();
	var product_id=$("#product_id").val();
	
	if(product_id){
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/pre/',
			data: { mode : "convert_qty",  type : type,base_qty:base_qty_hide,conv_qty:conv_qty_hide,product_id:product_id},
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);			
				if(type===1){
					$("#product_conv_qty_hide").val(conv_qty);
				}else if(type===2){
					$("#product_qty_hide").val(base_qty);
				}
				
				if(type===1){
					$("#product_qty").val(arr.show_qty);
					$("#product_qty_hide").val(arr.hide_qty);

				}else if(type===2){
					$("#product_conv_qty").val(arr.show_qty);
					$("#product_conv_qty_hide").val(arr.hide_qty);
					
				}else{
					$("#product_conv_qty").val(arr.show_qty);
					$("#product_conv_qty_hide").val(arr.hide_qty);
					$("#product_qty").val(arr.show_qty);
					$("#product_qty_hide").val(arr.hide_qty);
				}
			}
		});
	}else{
		toastr.warning("Select Product First", "WARNING");
		$("#product_conv_qty").val("0");
		$("#product_conv_qty_hide").val("0");
		$("#product_qty").val("0");
		$("#product_qty_hide").val("0");
	}
	
}
function new_vendor(id){
	if(id == 'new'){
		$('#vendor_name').css('display','block');
	}else{
		$('#vendor_name').css('display','none');
	}
}
function add_field(){
	//alert($("#branch_id").val());
	var form_data = new FormData();
	form_data.append("mode","add_field");
	form_data.append('edit_id', $("#edit_id").val());
	form_data.append('sales_order_id', $("#sales_order_id").val());
	form_data.append('work_order_id', $("#work_order_id").val());
	form_data.append('img_name', $("#img_name").val());
	form_data.append('product_id', $("#product_id").val());
	form_data.append('product_desc', $("#product_desc").val());
	form_data.append('product_qty', $("#product_qty_hide").val());
	form_data.append('unitid', $("#unitid").val());
	form_data.append('product_conv_qty', $("#product_conv_qty_hide").val());
	form_data.append('conv_unitid', $("#conv_unitid").val());
	form_data.append('rate', $("#rate").val());
	form_data.append('cardrate', $("#rate").attr('data-pcard'));
	form_data.append('purchasecardtrn_id', $("#rate").attr('data-pcardid'));
	form_data.append('vender_id', $("#vender_id").val());
	form_data.append('vendor_name', $("#vendor_name").val());
	form_data.append('att_doc', document.getElementById('att_doc').files[0]);
	form_data.append("pre_id", $("#eid").val());
	form_data.append("branch_id", $("#branch_id").val());
	
	if(!$("#product_id").val()){		
		toastr.warning("Choose Product", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	
	if(!$("#product_qty").val()){		
		toastr.warning("Enter Qty", "ERROR");
		$("#product_qty").focus();
		return false;
	}
	
	/*if(!$("#rate").val()){		
		toastr.warning("Enter Rate", "ERROR");
		$("#rate").focus();
		return false;
	}*/
	
	/*if(!$("#vender_id").val()){		
		toastr.warning("Choose Vender", "ERROR");
		$("#vender_id").select2('focus');
		return false;
	}*/
	
	if($("#vender_id").val() == 'new'){
		if(!$("#vendor_name").val()){
			toastr.warning("Enter Vender", "ERROR");
			$("#vendor_name").focus();
			return false;
		}
	}

	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/pre/',
		data: form_data,
		contentType: false,
	    cache: false,
	    processData: false,
	    beforeSend:function(){
	     $('#uploaded_image').html("<label class='text-success'>Image Uploading...</label>");
	    },
		success: function(response)
		{	
			if(response.trim != "0"){
				//alert(response);
				var data = JSON.parse(response);
				var responsevalue=data.msg;
				if(data.l_id != ""){
					$('#vender_id').append('<option value='+data.l_id+'>'+data.l_name+'</option>');
				}
			}
			$("#product_id").prop("disabled", false);
			$('#uploaded_image').html("");
			$("#product_id").select2("val","");
			$("#product_desc").val("");
			$("#sales_order_id").select2("val","");
			$("#work_order_id").select2("val","");
			$("#product_qty").val("");
			$("#product_qty_hide").val("");
			$("#unitid").val("");
			$("#unit_show").html("");
			$("#convert_unit_show").html("");
			
			$("#product_conv_qty").val("");
			$("#product_conv_qty_hide").val("");
			$("#conv_unitid").val("");
			$("#convert_unit_block").hide();
			
			$("#rate").val("");
			$("#vender_id").select2("val","");
			$("#vendor_name").val("");
			$("#att_doc").val("");
			$("#edit_id").val("");
			$('#addrow').html('Add');
			$('#vendor_name').css('display','none');
			
			show_data();
			Unloading();
		}
	});
}

function load_rate(){
	var product_id = $("#product_id").val();
	var vender_id  = $("#vender_id").val();
	var unit_id    = $("#conv_unitid").val(); 
	if(!product_id){		
		toastr.warning("Please Select Product", "ERROR");
		$("#product_id").focus();
		$("#vender_id").select2("val","");
		return false;
	}
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/pre/',
		data: { mode : "load_rate", product_id:product_id,vender_id:vender_id,unit_id:unit_id },
		success: function(response)
		{
			var resp = jQuery.parseJSON(response);
			$('#rate').val(resp.rate);
			$('#rate').attr('data-pcard',resp.rate); 
			$('#rate').attr('data-pcardid',resp.purchasecardtrn_id);
			Unloading();
		}
	});
}

function show_data() {
	var eid = $('#eid').val();
	var modee = $('#mode').val();
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain +purchase_domain+ 'app/pre/',
		data: { mode : "show_data", pre_id:eid,modee:modee },
		success: function(resp){
			//console.log(resp);
			$('#show_prod_data').html(resp);
			Unloading();
		}		 
	}); 
}

function edit_data(id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/pre/',
		data: { mode:"edit_data", id:id },
		success: function(response)
		{
			//console.log(response);
			var resp = jQuery.parseJSON(response);
			$("#product_id").prop("disabled", true);
			$("#product_id").select2("val",resp.product_id);
			$("#product_id").select2('data', { id:resp.product_id, text: resp.product_name});
			$("#product_desc").val(resp.product_desc);
			$("#product_qty").val(resp.product_qty);
			$("#product_qty_hide").val(resp.product_qty);
			$("#product_conv_qty").val(resp.product_conv_qty);
			$("#product_conv_qty_hide").val(resp.product_conv_qty);
			$("#rate").val(resp.rate);
			$('#rate').attr('data-pcard',resp.price); 
			$('#rate').attr('data-pcardid',resp.purchasecardtrn_id);
			$("#vender_id").select2("val",resp.vender_id);
			$("#sales_order_id").select2("val",resp.so_id);
			so_to_workorder_load(resp.so_id,resp.sp_id);
			//alert(resp.sp_id);
			$("#work_order_id").select2("val",resp.sp_id);
			
			$("#img_name").val(resp.att_doc);
			$("#edit_id").val(id);
			$('#addrow').val('Update');
			load_product_unit(resp.product_id,resp.unitid);
			Unloading();
		}
	});
}

function delete_data(id) {
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + 'app/pre/',
			data: { mode:"delete_data", id:id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
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


$("#pre_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#pre_add").valid()) {
		return false;
	}
	
        
	form.submitted = true;	
	Loading();	
	$(this).attr("disabled","disabled");		
	var form_data=new FormData(this);
	
	$.ajax({
		cache:false,
		url: root_domain+purchase_domain+'app/pre/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(resnse)
		{		
			var data = JSON.parse(resnse);
			var responsevalue=data.msg;
			if(responsevalue.trim() == '1') {
				toastr.success("PRE ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+purchase_domain+'pre_list';
			}
			else if(responsevalue.trim() == '2') {
				toastr.warning("Add One Product Please!!", "ERROR");
				$("#product_id").select2('focus');
				$('#save').prop('disabled', false);
				Unloading();
			}
			else if(responsevalue.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if(responsevalue.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				$('#product_add').trigger('reset');
				Unloading();				
			}
			else if(responsevalue.trim() == 'update') {
				toastr.success("PRE UPDATED SUCCESSFULLY", "SUCCESS");		
				window.location=root_domain+purchase_domain+'pre_list';	
				Unloading();
			}
			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});


function check_product(pre_id){
    var has_product = false;
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain+purchase_domain+'app/pre/',
		data: { mode:"has_product", pre_id : pre_id },
		success: function(response)
		{
			//console.log(response);
			if(response == '0'){
				has_product = false;
			} else {
				has_product = true;
			}
		}
	});
    return has_product;
}

function delete_row(id) {
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/pre/',
			data: { mode:"delete", id:id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					load_po_req_datatable();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
}

function product_load(po_type=''){
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=indent_po_pro_type&search=purchase_pro_search&po_type='+po_type;
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
	$('#product_id').select2({
		data: product_load($po_type),
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
function showproduct(){
	
	//$.fn.modal.Constructor.prototype.enforceFocus = function() {};
	$('#modal-add-product').modal('show');

	$('#product_type').select2("val",0);
	$("#product_type").trigger('change');
	$("#product_add_type").val('manual_indent');
}

function add_hsn_invoice(){
	//$.fn.modal.Constructor.prototype.enforceFocus = function() {};
	$('#modal-add-hsn').modal('show');
	$("#hsn_add_type").val('product_purchase');
	$("#hsn_name").focus();
}
function so_to_workorder_load(id,wid){
	//var id = $("#sales_order_id").val();
	
	if(id!=''){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/pre/',
			data: { mode:"so_to_workorder_load", soid:id,edit_id:wid },
			success: function(response)
			{
				console.log(response);
				//var resp=jQuery.parseJSON(response);
				$("#work_order_id").html(response);
				//$("#work_order_id").select2("val",resp.sp_id);
				
				Unloading();						
			}
		});	
	}
	else
	{

		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/pre/',
			data: { mode:"so_to_workorder_load",soid:id,edit_id:wid},
			success: function(response)
			{
				console.log(response);
				$("#work_order_id").html(response);
				//$("#work_order_id").select2("val",id);
				
				//var resp=jQuery.parseJSON(response);
				//$('#rate').val(resp.product_purchase_rate);
				//load_product_unit(id,resp.product_base_unit);
				//$('#unit_show').html(resp.unit_name);
				Unloading();						
			}
		});	
	}
}