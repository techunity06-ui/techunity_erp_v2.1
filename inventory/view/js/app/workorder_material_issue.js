//var datatable;
$(document).ready(function() {
	load_workorder_material_issue_datatable();

$("#material_issue").validate({
	rules: {
		material_issue_no: {
			required: true			
		},
		material_issue_date: {
			required: true			
		},
		workorder_id: {
			required: true
		},
		rp_id: {
			required: true			
		},
		process_id: {
			required: true			
		},
		branch_id: {
			required: true			
		}
	},
	messages: {
		material_issue_no: {
			required: "Enter Material Issue No"			
		},
		material_issue_date: {
			required: "Enter Material Issue Date"			
		},
		workorder_id: {
			required: "Select Workorder No"
		},
		rp_id: {
			required: "Select Product"
		},
		process_id: {
			required: "Select Process"
		},branch_id: {
			required: "Select Branch"
		}
	}
}); 
});

function load_workorder_material_issue_datatable()
{
	// var date=$('#rep_date').val();
	
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
		"sAjaxSource": root_domain+inventory_domain+'app/workorder_material_issue/',
		"fnServerParams": function ( aoData ) {
			aoData.push( 
				{ "name": "mode", "value": "fetch" }
				);
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();


		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
	}

$("#material_issue").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#material_issue").valid()) {
		return false;
	}
	if($("#fil_product_tbl tr").length == 0){
		toastr.warning("ENTER SOME DETAILS", "ERROR");
		return false;
	}

	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	
    var form_data=new FormData(this);	
    $.ajax({
    	cache:false,
    	url: root_domain+inventory_domain+'app/workorder_material_issue/',
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
				toastr.success("WORKORDER MATERIAL ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+inventory_domain+'workorder_material_issue';
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			
			// $('#material_issue').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
			Unloading();
		}
	});
});

function get_material_issue_no(){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/workorder_material_issue/',
		data: { mode : "load_material_issue_no"},
		success: function(response)
		{
			$('#material_issue_no').val(response);
			Unloading();
		}
	});
}

function product_load(){
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	// var product_type=$("#product_type").val();
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=production_pro_type&search=bom_pro_search';
	$.getJSON(mainurl, function(json) {
		// console.log(json);
		var arr=new Array();
		var len=json[0].length;
		// console.log(len);

		for(var i=0;i<len;i++)
		{	
			testData.push({ id:json['0'][i] ,text: json['1'][i]});
				//alert(json['1'][i]);
			}
		});

	return testData;
}

$('#product_id').select2({
	data: product_load(),
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


function get_workorder_product(workorder_id){
	$("#wo_product_id").val("");
	if(workorder_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/workorder_material_issue/',
			data: { mode : "get_workorder_product",  workorder_id : workorder_id},
			success: function(response)
			{
				$('#rp_id').empty().append(response);
				$("#rp_id").select2({
					width: '100%'
				});

				Unloading();
			}
		});
	}
}


function get_workorder_product_process(rp_id){
	if(rp_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/workorder_material_issue/',
			data: { mode : "get_workorder_product_process",  rp_id : rp_id},
			success: function(response)
			{
				$('#process_id').empty().append(response);
				Unloading();
			}
		});
	}
}

function get_product_id(rp_id){
	if(rp_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/workorder_material_issue/',
			data: { mode : "get_product_id",  rp_id : rp_id},
			success: function(response)
			{
				
				if(response != ""){
					$('#wo_product_id').val(response.trim());
				}else{
					$('#wo_product_id').val("");
				}
				
				Unloading();
			}
		});
	}else{
		toastr.warning("SELECT PRODUCT", "ERROR")
		return false;
	}
}
function product_convert_qty(type){

	if(type==2){
		var conv_qty_hide=$("#product_conv_qty").val();
		var s=parseFloat(conv_qty_hide);
		results = s.toFixed(3);

		var	num=$("#product_conv_qty_hide").val();
		var d=parseFloat(num);
		resultb = d.toFixed(3);

		// if(resultb===results){
		// 	return false;
		// }
		var product_base_qty_hide=$("#product_base_qty_hide").val();
	}else{
		var base_qty_hide=$("#product_base_qty").val();
		var d=parseFloat(base_qty_hide);
		resultb = d.toFixed(3);
		
		var base_qty_hidess=$("#product_base_qty_hide").val();
		var s=parseFloat(base_qty_hidess);
		results = s.toFixed(3);

		// if(resultb===results){
		// 	return false;
		// }
		var conv_qty_hide=$("#product_conv_qty").val();
	}
	
	var base_qty=$("#product_base_qty").val();
	var conv_qty=$("#product_conv_qty").val();

	var product_base_qty=$("#product_base_qty").data('product_base_qty');
	var product_conv_qty=$("#product_conv_qty").data('product_conv_qty');
	var product_id=$("#product_id").val();

	console.log("base_qty : " +base_qty)
	console.log("conv_qty : " +conv_qty)
	console.log("product_base_qty : " +product_base_qty)
	console.log("product_conv_qty : " +product_conv_qty)
	
	if(product_id){

		if(type===1){
					$("#product_base_qty_hide").val(base_qty.trim());
				}else if(type===2){
					$("#product_conv_qty_hide").val(conv_qty.trim());
				}

				if(type=="1"){
					ret_qty=(base_qty/product_base_qty)*product_conv_qty;
				}else if(type=="2"){
					ret_qty=(conv_qty/product_conv_qty)*product_base_qty;
				}else{
					ret_qty="1";
				}
				
				if(type===1){
					$("#product_conv_qty").val(ret_qty);
					$("#product_conv_qty_hide").val(ret_qty);

				}else if(type===2){
					$("#product_base_qty").val(ret_qty);
					$("#product_base_qty_hide").val(ret_qty);				
					
				}else{
					$("#product_base_qty").val(1);
					$("#product_base_qty_hide").val(1);
					$("#product_conv_qty").val(1);
					$("#product_conv_qty_hide").val(1);
				}


	}else{
		toastr.warning("Select Product First", "WARNING");
		$("#product_base_qty").val("1");
		$("#product_base_qty_hide").val("1");
		$("#product_conv_qty").val("1");
		$("#product_conv_qty_hide").val("1");
	}
}



function load_product_detail(pro_id) {
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/workorder_material_issue/',
		data: { mode : "load_productdata",eid :pro_id },
		success: function(response)
		{
			var resp = jQuery.parseJSON(response); 
				
				$('#product_base_qty').data('product_base_qty',resp.product_base_qty.trim());
				$('#product_conv_qty').data('product_conv_qty',resp.product_conv_qty.trim());

				$('#product_conv_unit_name').val(resp.conv_unit_name);
				$('#product_conv_unit').val(resp.product_conv_unit);
				$('#product_conv_qty').val(resp.product_conv_qty.trim());
				$('#product_conv_qty_hide').val(resp.product_conv_qty.trim());

				$('#product_base_unit_name').val(resp.base_unit_name);
				$('#product_base_unit').val(resp.product_base_unit);
				$('#product_base_qty').val(resp.product_base_qty.trim());
				$('#product_base_qty_hide').val(resp.product_base_qty.trim());
				// load_stock_qty(pro_id,0);
				Unloading();
				
			}
		});
}

// function load_productdetail(pro_id) {
// 	$.ajax({
// 		type: "POST",
// 		url: root_domain+inventory_domain+'app/workorder_material_issue/',
// 		data: { mode:"load_productdata", eid :pro_id },
// 		success: function(response)
// 		{
// 			var resp=jQuery.parseJSON(response);

// 			// $('#item_description').val(obj.product_desc);
// 			// $('#unit_id').select2("val",obj.product_base_unit);
// 			$('#product_base_unit_name').val(resp.base_unit_name);
// 			$('#product_base_unit').val(resp.product_base_unit);
// 			// $('#item_qty').val(0);
// 			$('#product_base_qty').val(resp.product_base_qty.trim());
// 			$('#product_base_qty_hide').val(resp.product_base_qty.trim());
// 			// $('#isbatchwise').val(obj.batch_wise_stock_manage);
// 			//  add_product_batch_wise();
// 			// $('#item_price').val(obj.product_sale_rate);
// 			load_stock_qty(product_id,0);
// 		}
// 	});
	
// } 


function add_field()
{
	if($("#workorder_id").val()===""){
		toastr.warning("Select workorder", "ERROR");
		$("#workorder_id").select2('focus');
		return false;
	}else if($("#rp_id").val()===""){
		toastr.warning("Select Product", "ERROR");
		$("#rp_id").select2('focus');
		return false;
	}else if($("#process_id").val()===""){
		toastr.warning("Select Process", "ERROR");
		$("#process_id").select2('focus');
		return false;
	}else if($("#allocate_user_id").val()===""){
		toastr.warning("Select Allocate User", "ERROR");
		$("#allocate_user_id").select2('focus');
		return false;
	}else if($("#product_id").val()===""){
		toastr.warning("Select Product Name", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}else if($("#product_base_qty").val()==="" || $("#product_base_qty").val()=="0"){
		toastr.warning("Enter Qty", "ERROR");
		$("#product_base_qty").focus();
		return false;
	}
	else if($("#branch_id").val()==="" || $("#branch_id").val()=="0"){
		toastr.warning("Select Branch", "ERROR");
		$("#branch_id").focus();
		return false;
	}
	else if($("#item_stock").val()=="" || $("#item_stock").val()=="0"){
		toastr.warning("YOU CAN'T ADD BECAUSE NO STOCK AVAILABLE FOR THIS PRODUCT", "ERROR");
		return false;
	}
	else if(parseFloat($("#product_base_qty").val()) >  parseFloat($("#item_stock").val())){
		toastr.warning("YOU CAN'T ADD MORE THAN STOCK QTY", "ERROR");
		return false;
	}
	var eid = $("#edit_id").val();
	var branch_id = $("#branch_id").val();

	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/workorder_material_issue/',
		data: { 
			mode : "fieldadd",
			product_id:$("#product_id").val(),
			product_base_unit:$("#product_base_unit").val(),
			product_base_qty:$("#product_base_qty").val(),
			product_conv_unit:$("#product_conv_unit").val(),
			product_conv_qty:$("#product_conv_qty").val(),
			eid:eid,
			branch_id:branch_id
		},
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				toastr.success("PRODUCT ADDED SUCCESSFULLY", "SUCCESS");
				load_tempout_data();

			}else if(arr.msg == "update"){
				toastr.success("PRODUCT UPDATED SUCCESSFULLY", "SUCCESS");
				load_tempout_data();				
			}
			else
			{
				toastr.warning("SOMETHING WRONG", "ERROR");
			}

			reset_data();
			Unloading();
		}
	});
}

function reset_data(){
	$("#product_id").select2('val','');
	$("#edit_id").val("");
	$('#addrow').val('Add');
	
	$("#product_base_qty").val("1");
	$("#product_conv_qty").val("1");
	$("#product_base_unit").val("");
	$("#product_conv_unit").val("");
	$("#product_base_qty_hide").val("1");
	$("#product_conv_qty_hide").val("1");
	$("#product_base_unit_name").val("NOS");
	$("#product_conv_unit_name").val("NOS");
	$('#product_base_qty').data('product_base_qty','');
	$('#product_conv_qty').data('product_conv_qty','');
	$("#item_stock").val('');
}
function load_tempout_data(){
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/workorder_material_issue/',
		data: { 
			mode : "load_tempoutward",
		},
		success: function(response)
		{
			$('#material_issue_temp_div').empty().html(response);
			Unloading();
		}
	});
}


function delete_tempout_data(){
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/workorder_material_issue/',
		data: { 
			mode : "delete_tempout_data",
		},
		success: function(response)
		{
			
			Unloading();
		}
	});
}

function delete_data(id,table,whereid)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/workorder_material_issue/',
			data: { mode : "delete_data", eid:id},
			success: function(response)
			{
				
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					load_tempout_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}


function edit_data(id)
{

	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/workorder_material_issue/',
		data: { mode : "preedit",  id : id},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			$("#product_id").select2('data', { id:data.product_id, text: data.product_name}).trigger('change');

			setTimeout(function(){ 
			$("#edit_id").val(data.material_issue_trn_id);
			$('#product_base_qty').data('product_base_qty',data.product_base_qty.trim());
			$('#product_conv_qty').data('product_conv_qty',data.product_conv_qty.trim());
			$('#addrow').val('Update');
			
			$("#product_base_qty").val((data.base_qty).trim());

			$("#product_base_unit").val(data.base_unit);
			$("#product_conv_unit").val(data.conv_unit);
			$("#product_conv_qty").val((data.conv_qty).trim());
			$("#product_base_qty_hide").val((data.base_qty).trim());
			$("#product_conv_qty_hide").val((data.conv_qty).trim());

			$("#product_base_unit_name").val(data.base_unit_name);
			$("#product_conv_unit_name").val(data.conv_unit_name);
			Unloading();
			}, 500);

			}
		});
}

function load_stock_qty(product_id,old_qty){
	Loading(true);
	var unit_id=$("#base_unit").val();
	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/workorder_material_issue/',
		data: { mode : "load_stock_qty", product_id:product_id,unit_id:unit_id },
		success: function(data){
			//console.log(data);
			$('#item_stock').attr("placeholder",data);
			$('#item_stock').attr("max",parseFloat(old_qty)+parseFloat(data));
			$('#item_stock').val(parseFloat(old_qty)+parseFloat(data));
			$("#item_qty").attr("placeholder",data);
			 $("#item_qty").attr("max",parseFloat(old_qty)+parseFloat(data));
			Unloading();
		}		
	});
}

function open_approv_model(id,no){
	$('#preview_work_order_material_approval').modal('show');
	$('#material_issue_no').html(no);
	$('#material_issue_id').val(id);
	
	workorder_material_load();
	workorder_direct_hist();
}


function workorder_material_load(){
	var material_issue_id = $('#material_issue_id').val();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/workorder_material_issue/',
		data: { mode : "load_wo_direct_material_detail", material_issue_id:material_issue_id },
		success: function(resp){
		//console.log(resp);
		var resp=JSON.parse(resp);
		$('#detail_show').html(resp.detail_show);
		}
	});
}
function workorder_direct_hist(){
	var material_issue_id = $('#material_issue_id').val();

		$("#order-pocard-history-datatable").dataTable({
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
			"sAjaxSource": root_domain+inventory_domain+'app/workorder_material_issue/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "load_wo_direct_hist" }, { "name": "material_issue_id", "value": material_issue_id }  );
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function add_wo_apprv_hist(){
	
	var form_data = {
		mode:"add_wo_apprv_hist",
		approve_status:$('#approve_status').val(),
		approve_remark:$('#approve_remark').val(),
		material_issue_id:$('#material_issue_id').val()
	};
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/workorder_material_issue/',
		data: form_data,
		success: function(response)
		{
			$('#approve_status').select2("val","0");
			$('#approve_remark').val("");
			load_workorder_material_issue_datatable();
			workorder_direct_hist();
			//load_order_confirm_datatable();
			workorder_material_load();

			$('#preview_work_order_material_approval').modal('hide');
			Unloading();
		}
	});	
}