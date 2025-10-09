//var datatable;
$(document).ready(function() {

	show_data();
	
});

function open_stock_allocation_so(sales_order_trn_id){
	//alert(sales_order_trn_id);
	$('#preview_so_branch_allocate_modal').modal('show');
	$('#ref_sales_order_trn_id').val(sales_order_trn_id);
	//show_data();
}
function add_branch() {
	var branch_id = $('#branch_id').val();
	var ref_sales_order_trn_id = $('#ref_sales_order_trn_id').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain+'/app/sales_order_stock_allocation/',
		data: { mode : "add_branch", ref_sales_order_trn_id:ref_sales_order_trn_id,branch_id:branch_id },
		success: function(resp){
			//console.log(resp);
			if(resp.trim()==1){
				toastr.success("BRANCH ALLOCATION SUCCESSFULLY", "SUCCESS");
			}else{
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			$('#preview_so_branch_allocate_modal').modal('hide');
			show_data();
			Unloading();
		}		 
	}); 
}

function show_data() {
	//var st_type = $('#st_type').val();
	//var branch_id = $('#branch_id').val();
	
	//alert(st_type);
	datatable = $("#dynamic-table1").dataTable({
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
		"sAjaxSource": root_domain+crm_domain+'app/sales_order_stock_allocation/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "generate_report_min_new" });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
	
} 

function open_stock_allocation_so(sales_order_trn_id,validate_qty,unit_name,validate_qty_conv = "",unit_name_conv = ""){
	// alert(validate_qty);
	$("#reserve_stock_entry_so").modal("show");
	$("#sales_ordertrn_id_model").val(sales_order_trn_id);
	$("#show_res_unit_name").text(unit_name);
	
	$("#validate_qty").val(validate_qty);
	$("#show_res_qty").html(validate_qty);

	$("#show_res_unit_name_conv").text(unit_name_conv);
	$("#show_res_qty_conv").html(validate_qty_conv ? " | " + validate_qty_conv : "" );
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain+'app/sales_order_stock_allocation/',
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
		url: root_domain+crm_domain+'app/sales_order_stock_allocation/',
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
		url: root_domain+crm_domain+'app/sales_order_stock_allocation/',
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
		url: root_domain+crm_domain+'app/sales_order_stock_allocation/',
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
			/*var current_stock=response.trim();
			$('#st_stock_total').val(current_stock);
			$('#st_stock_reserve').attr('max', current_stock);*/

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

	if(st_godown_id == ""){
		toastr.warning("Please Select Godown", "ERROR");
				return false;
	}

	if(st_stock_reserve == ""){
		toastr.warning("Please Enter Reserve Stock", "ERROR");
				return false;
	}

	if(parseFloat(st_stock_reserve) > parseFloat(st_stock_total)){
		toastr.warning("Stock not valid. Please check godown stock.", "ERROR");
		return false;
	}

	
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain+'app/sales_order_stock_allocation/',
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
			url: root_domain+crm_domain+'app/sales_order_stock_allocation/',
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
			url: root_domain+crm_domain+'app/sales_order_stock_allocation/',
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

function reserve_stock_convert_qty(type){
	
	var base_qty = 0;
	var conv_qty = 0;
	if(type==2){  // take base
		conv_qty  = $("#st_stock_reserve").val();
	}else{
		 base_qty = $("#diff_st_stock_reserve").val();
	}

	var product_id=$("#product_id_model").val();
	
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/request_product/',
			data: { mode : "convert_qty",  type : type,base_qty:base_qty,conv_qty:conv_qty,product_id:product_id},
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);	

				if(type==2){
					$("#diff_st_stock_reserve").val(arr.hide_qty);
				}else{
					$("#st_stock_reserve").val(arr.hide_qty);
				}
				
				
				
			}
		});	
	
}