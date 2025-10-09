//var datatable;
$(document).ready(function() {
	load_po_datatable();
	
// validate vendor add form on keyup and submit
 $("#purchasecard_add").validate({
	rules: {
		vender_id: {
			required: true			
		},
		purchasecard_no: {
			required: true			
		},
		purchaseorder_date:{
			required : true	
		}
	},
	messages: {
		vender_id: {
			required: "Select Vendor"
		},
		purchasecard_no: {
			required: "Enter P.C no"
		},
		purchasecard_date:{
			required : "Enter P.C date"
		}
	}
});

	$("#bom_costing").validate({
		rules: {
			bom_costing_id: {
				required: true			
			},
		},
		messages: {
			bom_costing_id: {
				required: "Select BOM Costing"
			}
		}
	}); 
});
$("#purchasecard_add").on('submit',function(e) {
	if($("#price").val()=="")
	{
		toastr.warning("Enter valid amount", "ERROR")
		$("#price").focus();
		return false;
	}else if($("#rate_tolerance").val()=="")
	{
		toastr.warning("Enter tolerance", "ERROR")
		$("#rate_tolerance").focus();
		return false;
	}
	else if($("#grate").val()=="")
	{
		toastr.warning("Enter GRate", "ERROR")
		$("#grate").focus();
		return false;
	}
	else if($("#discount_percentage").val()=="")
	{
		toastr.warning("Enter discount rate", "ERROR")
		$("#discount_percentage").focus();
		return false;
	}
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#purchasecard_add").valid()) {
		return false;
	}
	for (instance in CKEDITOR.instances) 
	{
    	CKEDITOR.instances[instance].updateElement();
	}	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	$.ajax({
		cache:false,
		url: root_domain+production_domain+'app/workorder_shortage_list/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("PURCHASE CARD ADDED SUCCESSFULLY", "SUCCESS");
				window.location.reload();
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if(arr.msg == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO");
				Unloading();
			}
			else if(arr.msg== 'update')
			{	
				toastr.success("PC UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location.reload();
				
			}
			$('#purchasecard_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});



function reload_data()
{
	//datatable.fnReloadAjax();
	load_po_datatable();
}	
function load_po_datatable()
{
	var po_type_status=$('input[name=po_type_status]:Checked').val();
	var date=$('#rep_date').val();
	var branch_id = $("#branch_id").val();
	var workorder_status=$('input[name=workorder_status]:Checked').val();
	// console.log(workorder_status);
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
			"sAjaxSource": root_domain+production_domain+'app/workorder_shortage_list/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },{ "name": "po_type_status", "value": po_type_status },{ "name": "date", "value": date },{ "name": "workorder_status", "value": workorder_status },{ "name": "branch_id", "value": branch_id });
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}


function get_series_no(type_id){
	$.ajax({
	type: "POST",
	url: root_domain+production_domain+'app/workorder_shortage_list/',
	data: { mode : "get_series_no", type_id:type_id},
	success: function(resp){
			$('#invoicetype_id').val(resp);	
			load_pono(resp);	
		}		
	});	
}

function load_pono(id)
{
	$.ajax({
	type: "POST",
	url: root_domain+production_domain+'app/workorder_shortage_list/',
	data: { mode : "load_invoiceno", typeid : id},
	success: function(data){
		var no = jQuery.parseJSON(data);
		$('#purchasecard_no').val(no.invoiceno);
	}
	});
}

function load_product(type_id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/workorder_shortage_list/',
		data: { mode : "load_product", type_id : type_id},
		success: function(data){
			$('#product_id').html(data);				
			Unloading();
		}
	});
}

function get_vendor_details(id){

	var mode = "get_po_login";
	
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+production_domain+'app/workorder_shortage_list/',
	data: { mode : mode, id : id},
	success: function(data){
		
		$('#work_order_login').modal('show');
			$('#po_login').html(data);				
			Unloading();
		}		
	});
}

 function get_items_details(tab,product_id=null) {
 	var product_id = $('#product_id').val();
	var mode = "get_"+tab;
	if(product_id){
		Loading();
		$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/workorder_shortage_list/',
		data: { mode : mode, product_id : product_id},
		success: function(data){
				$('#'+tab).html(data);	
				Unloading();			
			}		
		});
	}else{
		$msg = "Please Select Product First.";
		toastr.warning($msg, "WARNING");
		$('#'+tab).html($msg);
	}
 }


 $(document).on('keydown', "input[type='number']", function(event){
    if (event.shiftKey == true) {
        event.preventDefault();
    }
    if ((event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105) || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 37 || event.keyCode == 39 || event.keyCode == 46 || event.keyCode == 190) {
    } else {
        event.preventDefault();
    }
    if($(this).val().indexOf('.') !== -1 && event.keyCode == 190)
        event.preventDefault();
});


function get_item_information(id=null, product_id=null, vender_id=null, type=null) {
	
	
	Loading();
 	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/workorder_shortage_list/',
		data: { mode : 'get_item_selected_information', product_id : product_id, vendor_id : vender_id, type : type, id : id},
		success: function(data){
		
			
			$('#table_id').val(id);
			
			var arr = jQuery.parseJSON(data);
			$('#po_req_no').val(arr.po_req_no);
			$('#po_req_date').val(arr.po_req_date);
			$('#so_no').val(arr.so_no);
			$('#so_date').val(arr.so_date);
			$('#status').val(arr.status);
			$('#vender_id').val(arr.vender_id);
			$('#vendor_po_number').val(arr.vendor_po_number);
			$('#vender_po_date').val(arr.vender_po_date);
			$('#product_type').val(arr.product_type);
			$('#item_description').val(arr.item_description);
			$('#product_id').val(arr.product_id);
			$('#order_start_date').val(arr.order_start_date);
			$('#order_delivery_date').val(arr.order_delivery_date);
			$('#ds_number').val(arr.ds_number);
			$('#bom_no').val(arr.bom_no);
			$('#bom_id').val(arr.bom_id);
			$('#order_qty').val(arr.order_qty);
			$('#remark').val(arr.remark);
			$('#vender_id').attr('data-id', arr.vendorId);
			$('#reportv').html(arr.report);
			$('#work_order_details').modal('show');
			
			
			Unloading();
		}		
	});
}  

function reports()
{
	$('#work_order_reports').modal('show');
}

function notes()
{
	$('#work_order_notes').modal('show');
}



function edit_workorder(id)
{
	
	location.href=root_domain+production_domain+'edit_workorder/'+id;
	return false;
}     

function assign_bom_costing(sp_id){
	$("#sp_id").val(sp_id)
	Loading();
		$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/workorder_shortage_list/',
		data: { mode : 'get_bom_costing', sp_id : sp_id},
		success: function(data){
				$("#bom_costing_id").empty().html(data);
				$("#bom_costing_id").select2({
					width : "100%",
					placeholder : "Slect BOM Costing"
				});
				$('#bom_costing_model').modal('show');
				Unloading();
			}
		});
}

$("#bom_costing").on('submit',function(e) {
	if($("#bom_costing_id").val()=="")
	{
		toastr.warning("Select BOM Costing", "ERROR")
		$("#bom_costing_id").focus();
		return false;
	}

	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#bom_costing").valid()) {
		return false;
	}
		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	$.ajax({
		cache:false,
		url: root_domain+production_domain+'app/workorder_shortage_list/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				$('#bom_costing_model').modal('hide');
				Unloading();
				toastr.success("BOM COSTING ASSIGN SUCCESSFULLY", "SUCCESS");
				// window.location.reload();

			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
				load_po_datatable();
			$('#bom_costing').trigger('reset');	

		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});


function delete_workorder(sp_id){
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/workorder_shortage_list/',
			data: { mode : "delete",  eid : sp_id },
			success: function(response)
			{
					//console.log(response)
					if(response.trim() == "1") {
						toastr.success("WORKORDER DELETE SUCCESSFULLY", "SUCCESS");
						
						Unloading();
					}else if(response.trim() == "2") {
						toastr.info("PLEASE UNREQUEST SUB PRODUCT", "INFO");
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
						Unloading();
					}			
					load_po_datatable();				
				}
			});	
	}
}



function open_stock_allocation_so(rp_id,validate_qty){
	//alert(sales_order_trn_id);
	$("#workorder_shortage_modal").modal("show");
	$("#rp_id_model").val(rp_id);
	$("#validate_qty").val(validate_qty);
	$("#show_res_qty").html(validate_qty);
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/workorder_shortage_list/',
		data: { 
			mode : "show_stock_new",
			rp_id:rp_id
		},
		success: function(data){
			$("#sstock").html(data);
			show_reserve_temp_data();
		}
	})
}

function show_reserve_temp_data()
{
	//Loading();
	var rp_id=$('#rp_id_model').val();
	var batch_wise_stock_manage=$('#batch_wise_stock_manage').val();
	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/workorder_shortage_list/',
		data: { mode : "load_tempoutward",rp_id:rp_id,batch_wise_stock_manage:batch_wise_stock_manage},
		success: function(data){
				//console.log(data);
				$('#sale_productdata').html(data);				
				
			}		

		});
	
}	

function load_batch_no(){
	var godwn_id=$("#st_godown_id").val();
	var product_id=$("#product_id_model").val();
	var unit_id=$("#unit_id_model").val();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/workorder_shortage_list/',
		data: { mode : "load_batch_no",  godwn_id : godwn_id,product_id:product_id,unit_id:unit_id},
		success: function(responce){
			
			$('#st_stock_id').html(responce);
			$("#st_stock_id").select2("val","");
		}
	});
}

function load_godown_wise_stock(){
	var st_godown_id=$("#st_godown_id").val();
	var product_id=$("#product_id_model").val();
	var unit_id=$("#unit_id_model").val();
	var batch_id=$("#st_stock_id").val();
	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/workorder_shortage_list/',
		data: { 
			mode : "godown_stock",
			st_godown_id:st_godown_id,
			unit_id:unit_id,
			product_id:product_id,
			batch_id:batch_id
		},
		success: function(response)
		{
			//alert(response);
			var current_stock=response.trim();
			$('#st_stock_total').val(current_stock);
			$('#st_stock_reserve').attr('max', current_stock);
		}
	});
}

function add_reserve_temp()
{
	var st_godown_id = $('#st_godown_id').val();
	var st_stock_id = $('#st_stock_id').val();
	var st_stock_total = $('#st_stock_total').val();
	var st_stock_reserve = $('#st_stock_reserve').val();
	var rp_id = $('#rp_id_model').val();
	var unit_id = $('#unit_id_model').val();
	var product_id = $('#product_id_model').val();


	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/workorder_shortage_list/',
		data: { 
			mode : "fieldadd",
			st_godown_id:st_godown_id,
			st_stock_id:st_stock_id,
			st_stock_total:st_stock_total,
			st_stock_reserve:st_stock_reserve,
			rp_id:rp_id,
			unit_id:unit_id,
			product_id:product_id
		},
		success: function(response)
		{
				//console.log(response);
				//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
				$("#st_godown_id").select2("val","");
				$("#st_stock_id").select2("val","");
				$("#st_godown_id").val("");
				$("#st_stock_id").val("");
				
				$("#st_stock_total").val("");
				$("#st_stock_reserve").val("");
				$('#addrow').val('Add');
				
				show_reserve_temp_data();
				
			}
		});
}


function save_reserve_stock() {
	var bstock_arr=[];
	var bid_arr=[];

	i = 0;
	$('input.wip_res_stock').each(function(){ 
		bstock_arr[i++]=$(this).val();
	});
	
	j = 0;
	$('input.wip_stock_id').each(function(){ 
		bid_arr[j++]=$(this).val();
	});
		//console.log(bstock_arr);
		//return false;
		var total = 0;
		for (var i = 0; i < bstock_arr.length; i++) {
			total += bstock_arr[i] << 0;
		}
		
		var gstock_total=parseFloat($('#gstock_total').val());
		gstock_total=getNum(gstock_total);
		var tstock=total+gstock_total;
		var validate_qty=$("#validate_qty").val();
		if(validate_qty<tstock){
			toastr.warning("Increase Resverve Qty Please Enter currect Qty", "ERROR");
			return false;
		}
		
		var rp_id=$("#rp_id_model").val();

		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/workorder_shortage_list/',
			data: { 
				mode : "save_reserve_stock",
				rp_id:rp_id,
				bstock:bstock_arr,
				bid:bid_arr
			},
			success: function(data){
				
				$("#workorder_shortage_modal").modal("hide");
				show_data();
				Unloading();
			}		
			
		});
		
	}

function delete_data_stock(id)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/workorder_shortage_list/',
			data: { mode : "delete_data_stock",  eid : id },
			success: function(response)
			{
					//console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						show_reserve_temp_data()

						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
	}

}

function open_create_jobcard_model(rp_id,product_id,unit_id,total_qty,jobcard_qty,bom_version_id,branch_id){
	$("#product_id").val(product_id);
	$("#rp_id").val(rp_id);
	$("#total_qty").val(total_qty);
	$("#jobcard_qty").val(jobcard_qty);
	$("#unit_id").val(unit_id);
	$("#bom_version_id").val(bom_version_id);
	$("#branch_id_modal").val(branch_id);
	$("#jobcard_row").show();
	$("#process_row").hide();
	$("#material_row").hide();

	Loading();
		$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/workorder_shortage_list/',
		data: { mode : "get_product_details",  product_id : product_id, unit_id : unit_id, bom_version_id:bom_version_id },
		success: function(response)
		{
			//console.log(response)
			var data=jQuery.parseJSON(response)
			
			$("#lbl_product_name").html('<strong>'+data.product_name + '</strong>');
			$("#lbl_bom_ver_name").html('<strong>'+data.version_name+ '</strong>');
			$("#lbl_total_qty").html('<strong>'+total_qty + ' <span style="color:green">' + data.unit_name+ '</span></strong>');
			$("#lbl_jobcard_qty").html('<strong>'+jobcard_qty + ' <span style="color:green">' + data.unit_name+ '</span></strong>');
			var reorder_qty = data.reorder_qty;
			$("#reorder_qty").val(reorder_qty);
				
				if(reorder_qty != "" && reorder_qty > 0){
					
				   var chk_qty = 	Math.ceil(jobcard_qty  / reorder_qty);
				   // console.log(chk_qty)
				   jobcard_qty = 	reorder_qty * chk_qty;
				}

				$("#jobcard_qty").val(jobcard_qty);

			$("#shortage_jobcard_model").modal("show");
			Unloading();
		}
	});	

	
}	

function open_create_workorder_modal(product_id,rp_id,pending_qty,branch_id,cust_id){
	
	$("#pending_qty").val(pending_qty);
	$("#indent_qty").val(pending_qty);
	$("#so_product_id").val(product_id);
	$("#so_rp_id").val(rp_id);
	$("#production_branch_id").val(branch_id);
	$("#cust_id").val(cust_id);

	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/workorder_shortage_list/',
		data: { 
			mode : "get_product_name",
			product_id:product_id,
			
		},
		success: function(data){
			$("#product_name").val(data);
			$("#preview_shortage_indent").modal("show");
			Unloading();
		}		
		
	});
	
}

$("#create_wo_indent").on('submit',function(e) {

	var pending_qty = parseFloat($("#pending_qty").val());
	var indent_qty = parseFloat($("#indent_qty").val());

	if(indent_qty > pending_qty){
		toastr.warning("QUANTITY MUST BE LESS THAN OR EQUAL TO " + pending_qty, "WARNING");
		return false;
	}

	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+production_domain+'app/workorder_shortage_list/',
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
			toastr.success("INDED CREATED SUCCESSFULLY", "SUCCESS");
			reload_data();
			
		}
		else if(arr.msg == '0') {
			toastr.warning("SOMETHING WRONG", "ERROR")
			Unloading();
			reload_data();
		}
		$('#create_wo_indent').trigger('reset');	
		$("#preview_shortage_indent").modal("hide");
		
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});	
});

function create_jobcard(){
	var rp_id = $("#rp_id").val();
	var product_id = $("#product_id").val();
	var branch_id = $("#branch_id_modal").val();
	var	bom_version_id = $('#bom_version_id').val();
	var total_qty = $("#jobcard_qty").val();
	var reorder_qty=$("#reorder_qty").val();
	var wo_qty = total_qty / reorder_qty;
	if(reorder_qty != "" && reorder_qty > 0){
		if(!isInteger(wo_qty)){
			toastr.warning("Please enter quantity as per reorder qty. Reorder Qauntity is " + reorder_qty, "ERROR");
			return false;	
		}
	}

	Loading();

	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/workorder_shortage_list/',
		data: { 
			mode : "create_jobcard",
			product_id:product_id,
			bom_version_id:bom_version_id,
			jobcard_qty : total_qty,
			rp_id : rp_id,
			branch_id:branch_id
		},
		success: function(response){
			var data = jQuery.parseJSON(response);	
			$("#new_rp_id").val(data.rp_id);
			Unloading();
			show_process_list(product_id,data.rp_id,bom_version_id);
		}				
	});
}

function show_process_list(product_id,rp_id,bom_version_id)
{

	$("#mask1").removeClass('hidden');
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/workorder_shortage_list/',
		data: { 
			mode : 'get_product_process_data',
			product_id:product_id,
			rp_id : rp_id,
			bom_version_id:bom_version_id,
			edit_id:'1'
			
		},
		success: function(data){

			Loading();

			$("#jobcard_row").hide();
			$('#mod_per_div_add_process').empty();
			$('#mod_per_div_add_process').html(data);
			

			// $('#rp_id').val(rp_id);

			CKEDITOR.replace( 'process_desc', {
				enterMode: CKEDITOR.ENTER_BR
			});
			
			
			var current_number = $('.process_row').last().attr('data-cid');	

			current_number = current_number ? current_number : 0;
			var new_number = parseInt(current_number) + 1;
			
			$('.process_priority').val(new_number);
			$('.process_priority_label').html(new_number);
			
			load_multislect_process();
			
			$(".ms-container").css('width',"100% !important");
			$('#direct_product_id').val(product_id);
			$("#process_row").show();
			
			if($("#multiple_value").val().length > 0){
				var selProcess = $("#multiple_value").val();
				
				const myArr = selProcess.split(",");
				$("#multiple_value").val('');
					for (const item of myArr) { // You can use `let` instead of `const` if you like
						$('#process_item').multiSelect('select', item);
						// console.log(item)
					}
					
				}
				
				$("#mask1").addClass('hidden');
				updateIDs();
				Unloading();
			}

		});
	
}


function load_multislect_process(){
	$('#process_item').multiSelect({
		keepOrder: true,
		selectableHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
		selectionHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
		afterInit: function (ms) {
			var that = this,
			$selectableSearch = that.$selectableUl.prev(),
			$selectionSearch = that.$selectionUl.prev(),
			selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
			selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';

			that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
			.on('keydown', function (e) {
				if (e.which === 40) {
					that.$selectableUl.focus();
					return false;
				}
			});

			that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
			.on('keydown', function (e) {
				if (e.which == 40) {
					that.$selectionUl.focus();
					return false;
				}
			});
		},
		afterSelect: function(value, text){
			this.qs1.cache();
			this.qs2.cache();
			var get_val = $("#multiple_value").val();         
			var hidden_val = (get_val != "") ? get_val+"," : get_val;
			$("#multiple_value").val(hidden_val+""+value);
		},
		afterDeselect: function(value, text){
			this.qs1.cache();
			this.qs2.cache();
			//alert("test");
			var get_val = $("#multiple_value").val();
			var new_val = get_val.replace(value, "");
			$("#multiple_value").val(new_val);
		}
		
	});	
	
}	


$("body").on("click","#process_left li",function(){
	$("#process_left li").removeClass('selected');
	$("#process_right li").removeClass('selected')
	$(this).addClass('selected');

	$('#row_process_desc').hide();
	$("#process_save").show();
	$("#selected_process_id").val('');
	$("#chk_leftside_process").prop('checked',false)
});
$("body").on("click","#process_right li",function(){
   // $("#process_right li").on('click',function(e){
   	$("#process_left li").removeClass('selected');
   	$("#process_right li").removeClass('selected');
   	$(this).addClass('selected');

   	$('#row_process_desc').show();
   	$("#process_save").hide();
   	var selectedOpts = $('#process_right li.selected');
   	var process_id = selectedOpts.attr('id');
   	$("#selected_process_id").val(process_id);
   	var rp_id = $("#selected_rp_id").val();
   	$("#btProcessDesc").html("Save");
   	get_process_desc(rp_id,process_id);
 	$("#chk_rightside_process").prop('checked',false)

   });
$("body").on("click","#moveRight",function(e){
   // $("#moveRight").on('click',function(e){
   	var selectedOpts = $('#process_left li.selected');
   	if (selectedOpts.length == 0) {
   		alert("Nothing to move.");
   		e.preventDefault();
   	}else{
   		selectedOpts.each(function(){ 
		   		var process_id = $(this).attr('id')
		   		var process_name = $(this).text();
		   		
		   		var html = "<li id='"+process_id+"'>" + process_name + "</li>";
		   		$('#process_right').append(html);
		   		$(this).remove();
   		   });
   		e.preventDefault();
   		updateIDs();
   		$("#chk_leftside_process").prop('checked',false)
   	}
   	
   });
$("body").on("click","#moveLeft",function(e){
     // $("#moveLeft").on('click',function(e){
     	var selectedOpts = $('#process_right li.selected');
     	// console.log(selectedOpts.length);
     	if (selectedOpts.length == 0) {
     		alert("Nothing to move.");
     		e.preventDefault();
     	}else{
     		selectedOpts.each(function(){ 
		 		var process_id = $(this).attr('id')
	     		var process_name = $(this).text();
	     		var process_name = process_name.replace('+','');
	     		var html = "";
	     		html = "<li id='"+process_id+"'>" + process_name.trim() + "</li>";
	     		$('#process_left').append(html);
	     		$(this).remove();
	     		$('#row_process_desc').hide();
	     		$("#selected_process_id").val('');
	     		$("#process_save").show();
	     		$("#chk_rightside_process").prop('checked',false)
     		});
     		e.preventDefault();
     		updateIDs();
     	}
     });


function updateIDs() {
	$('#selected_process_ids').val('');
	$('#process_right li').each(function(index) {
		// console.log($(this).attr('id'));
		$('#selected_process_ids').val($('#selected_process_ids').val() +  $(this).attr('id') + ",");
	});

	$('#process_ids').val('');
	$('#process_left li').each(function(index) {
		// console.log($(this).attr('id'));
		$('#process_ids').val($('#process_ids').val() + $(this).attr('id') + ",");
	});
}

function save_process_desc(rp_id){
	var process_id = $("#selected_process_id").val();
	// var desc = $("#process_desc").val()
	var desc = CKEDITOR.instances['process_desc'].getData();
	var eid = $("#selected_desc_id").val();
	Loading();

	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/workorder_shortage_list/',
		data: { mode : "save_process_desc",rp_id:rp_id,process_id:process_id,desc:desc,eid,eid},
		success: function(response)
		{
			
			if(response.trim() == '1')
			{
				toastr.success("DESCRIPTION ADDED SUCCESSFULLY", "SUCCESS");
				$('#row_process_desc').hide();
				$("#selected_process_id").val('');
				$("#process_save").show();
				$("#btProcessDesc").html("Save");
			}else if(response.trim() == 'update') {
				toastr.success("DESCRIPTION UPDATE SUCCESSFULLY", "SUCCESS");
				$('#row_process_desc').hide();
				$("#selected_process_id").val('');
				$("#process_save").show();
				$("#btProcessDesc").html("Save");
			}
			else{
				toastr.warning("SOMETHING WRONG", "WARNING");
			}
			$("#selected_desc_id").val('');
			$("#process_right li").removeClass('selected')
			Unloading();
			
		}
	});	

}

function get_process_desc(rp_id,process_id){
	Loading();

	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/workorder_shortage_list/',
		data: { mode : "get_process_desc",rp_id:rp_id,process_id:process_id},
		success: function(response)
		{
			if(response.trim() !== ""){
				var data=JSON.parse(response);
			// console.log(response);
			CKEDITOR.instances['process_desc'].setData(data.description);
			$("#selected_desc_id").val(data.id);
			$("#btProcessDesc").html("Update");
			
		}else {
			CKEDITOR.instances['process_desc'].setData("");
			$("#selected_desc_id").val('');
			$("#btProcessDesc").html("Save");
		}
		Unloading();
	}
});	
}


function select_all_left_side_process(){
	var process_left = $('#process_left li');
	if (process_left.length == 0) {
     		alert("No Process added.");
     		$("#chk_leftside_process").prop('checked',false)
     	}else{
     		if($("#chk_leftside_process").prop('checked')){
     			$("#process_left li").addClass('selected');
     		}else{
     			$("#process_left li").removeClass('selected');
     		}
     	}
}

function select_all_right_side_process(){

	var process_right = $('#process_right li');
     	if (process_right.length == 0) {
     		alert("No Process added.");
     		$("#chk_rightside_process").prop('checked',false);
     	}else{
     		if($("#chk_rightside_process").prop('checked')){
     			$("#process_right li").addClass('selected');
     		}else{
     			$("#process_right li").removeClass('selected');
     		}
     	}
}



function show_material_list(rp_id){
	Loading();
	var jobcard_qty = $("#jobcard_qty").val();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/workorder_shortage_list/',
		data: { 
			mode : 'show_material_list',
			rp_id : rp_id,
			jobcard_qty:jobcard_qty
		},
		success: function(response)
		{
			$("#jobcard_row").hide();
			$("#process_row").hide();
			$("#material_row").show();
			$("#mod_per_div_show_material").empty().html(response);
			Unloading();
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});	
}

function bom_process_add(rp_id){
	var counter = $("#process_right li").length;

	if(counter == 0){
		toastr.warning("PLEASE SELECT ANY ONE PROCESS", "ERROR")
		return false;
	}

	var form_data = new FormData();
	var product_id = $("#product_id").val();
	
	var sel_process = $("#selected_process_ids").val();
	var unsel_process = $("#process_ids").val();
	
	form_data.append('mode','bom_process_add');
	form_data.append('sel_process',sel_process);
	form_data.append('unsel_process',unsel_process);
	form_data.append('branch_id',$("#branch_id_modal").val());
	form_data.append('rp_id',rp_id);
	if($('#process_sel_product_id').val() !=""){
		form_data.append('product_id',$('#process_sel_product_id').val());
	}else{
		form_data.append('product_id',product_id);
	}
		// form_data.append('multiple_value',$("#multiple_value").val());
		var edit_id =  $('#edit_id').val();
		if(typeof edit_id != 'undefined')
		{
			form_data.append('edit_id',$('#edit_id').val());
		}
		Loading();
		$.ajax({		
			url: root_domain+production_domain+'app/workorder_shortage_list/',
			type: "POST",
			data: form_data,
			contentType: false,
			cache: false,
			processData:false,	
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);			
				if(arr.msg == '1') {
					toastr.success("WORK ORDER PROCESS ADDED SUCCESSFULLY", "SUCCESS");
					Unloading();
					
					show_material_list(rp_id);
				}
				else if(arr.msg == 'update') {
					toastr.success("WORK ORDER PROCESS UPDATED SUCCESSFULLY", "SUCCESS");
					Unloading();
					
					show_material_list(rp_id);
				}
				else if(arr.msg == '0') {
					toastr.warning("SOMETHING WRONG", "ERROR")
					Unloading();
				}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});		
}


function update_rowmaterial_qty(){
	var jobcard_qty = $("#jobcard_qty").val();
	var bomObj = {};
	bomObj.rp_id = [];
	bomObj.qty = [];
	
	
	var errorlog=0;
	$("input.shortage_qty").each(function () {
		if(typeof $(this).attr("value") != 'undefined')
		{
			var rp_id = $(this).attr("data-rp_id");
			var req_qty = $(this).val();
			console.log(req_qty);
			
			var shortage_qty = $(this).attr("data-shortage_qty");
			console.log(shortage_qty);
			var reorder_qty=$(this).attr("data-reorder_qty");
			
			var wo_qty = req_qty / reorder_qty;
			if(reorder_qty != "" && reorder_qty > 0){
					if(!isInteger(wo_qty)){
						errorlog +=parseFloat(1);

						$("#shortage_qty_"+rp_id).css("border", "1px solid red");
						toastr.warning("Please enter Qauntity as per reorder qty. Reorder Qauntity is " + reorder_qty, "ERROR");
						return false;	
				}
			} 
			if(req_qty=="" || req_qty == 0){
				errorlog +=parseFloat(1);

				toastr.warning("Enter Quantity", "WARNING"); 
				 $("#shortage_qty_"+rp_id).css("border", "1px solid red");
				return false;
			}else if(parseFloat(req_qty) < parseFloat(shortage_qty)){
				errorlog +=parseFloat(1);
				toastr.warning("Please Check Shortage Qauntity. You Can't enter less Qauntity", "WARNING");
			}else{
				$("#shortage_qty_"+rp_id).css("border", "1px solid #ccc");
				bomObj.rp_id.push(rp_id);
				bomObj.qty.push(req_qty);
			}
		}else{
			errorlog +=parseFloat(1);
		}
	});  

	if(errorlog>"0"){
		// toastr.warning("Grater Thean Qty", "WARNING"); 
		return false;
	}

	Loading();

	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/workorder_shortage_list/',
		data: { 
				mode:"update_request_qty",
				rp_id:bomObj.rp_id, 
				qty:bomObj.qty,
				jobcard_qty:jobcard_qty
			},
		success: function(resp){
			if(resp.trim() == '1'){
				toastr.success("QUANTITY UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				$("#shortage_jobcard_model").modal("hide");
				load_po_datatable();
			}else{
				toastr.warning("SOMETHING WRONG!", "WARNING"); 
				Unloading();
			}
		}
	});
}

function close_jobcard_modal(type=""){

	var msg = "";
	if(type == 1){
		msg = "Are you sure you want to exit with default Process & Raw material Qauntity ?";
	}else{
		msg = "Are you sure you want to exit with default Raw material Qauntity ?";
	}
	Swal.fire({
		title: msg,
	  // text: "You won't be able to revert this!",
	  icon: 'question',
	  /*iconHtml: '<img src="https://picsum.photos/100/100">', // for custome image icon
	  customClass: {
	    icon: 'no-border'
	  }*/
	  showCancelButton: true,
	  confirmButtonColor: '#5cb85c',
	  cancelButtonColor: '#d9534f',
	  cancelButtonText: 'No',
	  confirmButtonText: 'Yes',
	  allowOutsideClick: false,
	  allowEscapeKey : false,
	  /*showClass: {
	    popup: 'animate__animated animate__fadeInDown'
	  },
	  hideClass: {
	    popup: 'animate__animated animate__fadeOutUp'
	  }*/
	  
	}).then((result) => {
		if (result.isConfirmed) {
			$("#shortage_jobcard_model").modal("hide");
			location.reload();
		}
	})
}