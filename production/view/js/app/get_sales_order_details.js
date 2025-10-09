//var datatable;
$(document).ready(function() {

	show_data();
	
});
$("#so_allocation_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#so_allocation_add").valid()) {
		return false;
	}
	
	
	var so_stock=(document.getElementsByName('so_stock[]'));
	var so_working_stock=(document.getElementsByName('so_working_stock[]'));
	var cnt=so_stock.length;
	var so_stock1=0
	for(var i=0;i<cnt;i++)
	{
		if(so_stock[i].value > 0){
			so_stock1 += parseFloat(so_stock[i].value);
			//alert(so_stock1);
		}
	} 
	
	var cnt1=so_working_stock.length;
	var so_wostock1=0;
	for(var p=0;p<cnt1;p++)
	{
		if(so_working_stock[p].value > 0){
			so_wostock1 += parseFloat(so_working_stock[p].value);
			//alert(so_wostock1);
		}
	} 
	if(isNaN(parseFloat(so_stock1))){
		so_stock1=0;
	}
	if(isNaN(parseFloat(so_wostock1))){
		so_wostock1=0;
	}
	var total_add=parseFloat(so_stock1)+parseFloat(so_wostock1);
	var pending_qty=$("#ref_pending_qty").val();
	if(isNaN(parseFloat(pending_qty))){
		pending_qty=0;
	}
	
	if(total_add<=0){
		toastr.warning("Please Add Stock", "ERROR")
		return false;
	}
	
	if(total_add>pending_qty){
		toastr.warning("Please Check Stock", "ERROR")
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+production_domain+'app/get_sales_order_details/',
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
				toastr.success("ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+'get_sales_order_details';
				
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
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");		
				
				Unloading();
				window.location=root_domain+'get_sales_order_details';
				
			}
			$('#so_allocation_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function show_data() {
	var st_type = $('#st_type').val();
	var branch_id = $('#branch_id').val();
	
	//alert(st_type);
	datatable = $("#dynamic-table1").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide" : true,
		'aoColumnDefs': [{
			'bSortable': false,
			'aTargets': ['nosort']
		}],
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+production_domain+'app/get_sales_order_details/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "generate_report_min_new" },{ "name": "st_type", "value": st_type },{ "name": "branch_id", "value": branch_id });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
	
	
} 
function open_approv_quo1(sales_order_no,product_name,sotrn_id,product_id,pending_qty){
	$('#preview_so_allocate_modal').modal('show');
	$('#apprv_ref_no').html(sales_order_no);
	$('#pname').html(product_name);
	$('#pqty').html(pending_qty);
	$('#ref_sales_order_trn_id').val(sotrn_id);
	$('#ref_product_id').val(product_id); 
	$('#ref_pending_qty').val(pending_qty); 
	//alert("fdsa");
	load_entry_stock();
}
function load_entry_stock() {
	var ref_sales_order_trn_id = $('#ref_sales_order_trn_id').val();
	//alert(ref_sales_order_trn_id);
	//var ref_product_id = $('#ref_product_id').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'/app/get_sales_order_details/',
		data: { mode : "load_entry_stock", ref_sales_order_trn_id:ref_sales_order_trn_id },
		success: function(resp){
			console.log(resp);
			$('#mod_per_div_sec1').html(resp);
			Unloading();
		}		 
	}); 
}

function product_request(product_id,sales_ordertrn_id,qty)
{
	var r= confirm("Do You Want To Set Standard Version?");
	if(r) {
		
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'/app/get_sales_order_details/',
			data: { mode : "set_version", product_id:product_id,sales_ordertrn_id:sales_ordertrn_id,qty:qty},
			success: function(resp){
				if(resp == '1') {
					Unloading();
					toastr.success("STANDARD VERSION ASSIGNED SUCCESSFULLY", "SUCCESS");
					
				}else {
					
					Unloading();
				}
				window.location=root_domain+'sorequesproduct/'+product_id+'/'+sales_ordertrn_id;
			}		 
		}); 
	}else{	
		
		
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/get_sales_order_details/',
			data: { mode : "ger_version_by_product",product_id:product_id,sales_ordertrn_id:sales_ordertrn_id,qty:qty},
			success: function(response)
			{
				$("#show_product_from").html(response);
				$("#prodcuct_version").modal("show");							
				Unloading();
			}
		});
		
	}

}

function product_custom_versions(product_id,sales_ordertrn_id,qty)
{
	var version_id = $("#add_bom_version_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'/app/get_sales_order_details/',
		data: { mode : "set_custom_version", product_id:product_id,sales_ordertrn_id:sales_ordertrn_id,version_id:version_id,qty:qty},
		success: function(resp){
			
			if(resp == '1') {
				Unloading();
				toastr.success("VERSION ASSIGNED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+'sorequesproduct/'+product_id+'/'+sales_ordertrn_id;
			}else {
				toastr.warning("NOT ASSIGEND VERSION IN BOM !!!", "ERROR");
				window.location=root_domain+'sorequesproduct/'+product_id+'/'+sales_ordertrn_id;
				Unloading();
			}
			
		}		 
	}); 
}

function open_stock_allocation_so(sales_order_trn_id,validate_qty,unit_name){
	//alert(sales_order_trn_id);
	$("#reserve_stock_entry_so").modal("show");
	$("#sales_ordertrn_id_model").val(sales_order_trn_id);
	$("#validate_qty").val(validate_qty);
	$("#show_res_qty").html(validate_qty);
	$("#show_res_unit_name").html(unit_name);
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/get_sales_order_details/',
		data: { 
			mode : "show_stock_new",
			sales_order_trn_id:sales_order_trn_id
		},
		success: function(data){
			$("#sstock").html(data);
			$("#st_godown_id").select2({
				width : '100%'
			});
			$("#st_stock_id").select2({
				width: '100%'
			});
			show_reserve_temp_data();
		}
	})
}
function show_reserve_temp_data()
{
	//Loading();
	var sales_ordertrn_id=$('#sales_ordertrn_id_model').val();
	var batch_wise_stock_manage=$('#batch_wise_stock_manage').val();
	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/get_sales_order_details/',
		data: { mode : "load_tempoutward",sales_ordertrn_id:sales_ordertrn_id,batch_wise_stock_manage:batch_wise_stock_manage},
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
		url: root_domain+production_domain+'app/get_sales_order_details/',
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
		url: root_domain+production_domain+'app/get_sales_order_details/',
		data: { 
			mode : "godown_stock",
			st_godown_id:st_godown_id,
			unit_id:unit_id,
			product_id:product_id,
			batch_id:batch_id
		},
		success: function(response)
		{
			var data=JSON.parse(response);
			var current_stock=data.stock;
			var diff_stock=data.diff_stock;

			$('#st_stock_total').val(current_stock);
			$('#st_stock_reserve').attr('max', current_stock);
			$('#diff_st_stock_total').val(diff_stock);
			$('#diff_st_stock_reserve').attr('max', diff_stock);
		}
	});
}					

function add_reserve_temp()
{
	var st_godown_id = $('#st_godown_id').val();
	var st_stock_id = $('#st_stock_id').val();
	var st_stock_total = $('#st_stock_total').val();
	var st_stock_reserve = $('#st_stock_reserve').val();
	var sales_ordertrn_id = $('#sales_ordertrn_id_model').val();
	var unit_id = $('#unit_id_model').val();
	var product_id = $('#product_id_model').val();


	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/get_sales_order_details/',
		data: { 
			mode : "fieldadd",
			st_godown_id:st_godown_id,
			st_stock_id:st_stock_id,
			st_stock_total:st_stock_total,
			st_stock_reserve:st_stock_reserve,
			sales_ordertrn_id:sales_ordertrn_id,
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
function delete_data_stock(id)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/get_sales_order_details/',
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
		
		var sales_ordertrn_id=$("#sales_ordertrn_id_model").val();

		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/get_sales_order_details/',
			data: { 
				mode : "save_reserve_stock",
				sales_ordertrn_id:sales_ordertrn_id,
				bstock:bstock_arr,
				bid:bid_arr
			},
			success: function(data){
				
				$("#reserve_stock_entry_so").modal("hide");
				show_data();
				Unloading();
			}		
			
		});
		
	}
	function getNum(val) {
		if (isNaN(val)) {
			return 0;
		}
		return val;
	}				


	function open_create_workorder_modal(product_id,sales_ordertrn_id,pending_qty,branch_id,cust_id){
		

		$("#pending_qty").val(pending_qty);
		$("#indent_qty").val(pending_qty);
		$("#so_product_id").val(product_id);
		$("#sales_ordertrn_id").val(sales_ordertrn_id);
		$("#production_branch_id").val(branch_id);
		$("#cust_id").val(cust_id);

		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/get_sales_order_details/',
			data: { 
				mode : "get_product_name",
				product_id:product_id,
				
			},
			success: function(data){
				$("#product_name").val(data);
				$("#preview_workorder_indent").modal("show");
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
			url: root_domain+production_domain+'app/get_sales_order_details/',
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
				show_data();
				
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
				show_data();
			}
			$("#preview_workorder_indent").modal("hide");
			$('#create_wo_indent').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
		
	});
	function open_so_trn_modal(so_trn_id){
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'/app/get_sales_order_details/',
			data: { mode : "preview_so_trn_pro_description", so_trn_id:so_trn_id},
			success: function(response){
				$('#preview_so_trn_pro_description').modal('show');
				$('#preview_so_trn_pro_description_div').html(response);
			}		 
		});
	}


function checkAll()
{
	var checkboxes = document.getElementsByTagName('input'), val = null;    
	for (var i = 0; i < checkboxes.length; i++)
	{
		if (checkboxes[i].type == 'checkbox')
		{
			if (val === null) val = checkboxes[i].checked;
			checkboxes[i].checked = val;
		}
	}
}


function create_workorder(){
	var required_qty = $("#total_temp_batch_qty").val();
	var checbox_checked_len = $('input:checkbox:checked').length;
	if(checbox_checked_len < 1)
	{
		toastr.warning("Please Select at least 1 checkbox ", "ERROR")
		return false;
	}
	else
	{
		var bomObj = {};
		bomObj.pidChecked = [];
		bomObj.soidChecked = [];
		bomObj.bomidChecked=[];
		bomObj.branchidChecked=[];
		//bomObj.bomdChecked = [];

		$("input:checkbox").each(function () {
			if ($(this).is(":checked")) {
				if(typeof $(this).attr("value") != 'undefined')
				{
					bomObj.pidChecked.push($(this).attr("value"));
					bomObj.soidChecked.push($(this).attr("data-soid"));
					bomObj.bomidChecked.push($(this).attr("data-bomid"));
					bomObj.branchidChecked.push($(this).attr("data-branchid"));
				}
			}

		});

		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'/app/get_sales_order_details/',
			data: { mode : "create_workorder_shortage", product_id:bomObj.pidChecked,so_trn_id:bomObj.soidChecked,bom_id:bomObj.bomidChecked,branch_id:bomObj.branchidChecked},

			success: function(response){

				var arr = jQuery.parseJSON(response);			
				if(arr.msg == '1') {
					Unloading();
					toastr.success("WORKORDER CREATED SUCCESSFULLY", "SUCCESS");
					// setTimeout(function(){
					//  window.location.reload(); 
					// },500);			
				}
				else if(arr.msg == '0') {
					toastr.warning("SOMETHING WRONG", "ERROR")
					Unloading();
				}			
				$("#checkAll").prop('checked', false);
				Unloading();
				show_data();

			}		 
		}); 
	}
}

function reserve_stock_convert_qty(type){
	// alert('ok')
	var base_qty = 0;
	var conv_qty = 0;
	if(type==2){  // take base
		conv_qty  = $("#st_stock_reserve").val();
	}else{
		 base_qty = $("#st_stock_reserve").val();
	}

	var product_id=$("#product_id_model").val();
	
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/request_product/',
			data: { mode : "convert_qty",  type : type,base_qty:base_qty,conv_qty:conv_qty,product_id:product_id},
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);			
				
				$("#diff_st_stock_reserve").val(arr.hide_qty);
				
			}
		});	
	
}