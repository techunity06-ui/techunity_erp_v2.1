//var datatable;
$(document).ready(function() {

	show_data();
	
});

function show_data() {
	var stage1=$("#stage").val();
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
		"sAjaxSource": root_domain+production_domain+'app/solid_printing_entry/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "generate_report_min_new" },{ "name": "stage1", "value": stage1 });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
	
	
} 

function open_allo_modal(product_id,balty){
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'/app/solid_printing_entry/',
		data: { mode : "preview_solid_allocate", product_id:product_id,balty:balty},
		success: function(response){
			var arr = jQuery.parseJSON(response);
			$('#solid_exe_allo').modal('show');
			$("#solid_exe_allo_div").html(arr.html);
		}		 
	});
}
function reserve_exe(reserve_id){
	//alert(reserve_id);
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'/app/solid_printing_entry/',
		data: { mode : "reserve_exe", reserve_id:reserve_id},
		success: function(response){
			var arr = jQuery.parseJSON(response);
			if(arr.msg==1){
				$("#res"+reserve_id).html("");
				$("#res"+reserve_id).html("<button class='btn btn-xs btn-primary' data-original-title='Sales Order Detail' data-toggle='tooltip' data-placement='top' type='button' >Done</button>");
			}else{
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
		}		 
	});
}
function save_solid_mixing_planning(){
	var mixing_finish_qty=$("#mixing_finish_qty").val();
	var batch_size_id=$("#batch_size_id").val();
	var product_id=$("#product_id").val();
	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'/app/solid_printing_entry/',
		data: { mode : "save_mixing", product_id:product_id,batch_size_id:batch_size_id,mixing_finish_qty:mixing_finish_qty},
		success: function(response){
			var arr = jQuery.parseJSON(response);
			$('#solid_mixing').modal('hide');
			if(arr.msg == '1') {
				toastr.success("Entry Save SUCCESSFULLY", "SUCCESS");
				show_data();
				//window.location=root_domain+inventory_domain+'stock_general_sticker_common_print/'+product_id+'/'+sales_ordertrn_id;
			}else{
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			
		}		 
	});
}
function open_exe_end_modal(product_id,balty){
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'/app/solid_printing_entry/',
		data: { mode : "open_exe_end_modal", product_id:product_id,balty:balty},
		success: function(response){
			var arr = jQuery.parseJSON(response);
			$('#solid_exe_entry').modal('show');
			$("#pro_name").html(arr.product_name);
			$("#batchsiz").html(arr.balty_name);
			$("#tqty").html(arr.pending_qty);
			$("#finish_qty").val(arr.pending_qty);
			$("#balty").val(balty);
			$("#product_id").val(product_id);
			roll_stock_model();
		}		 
	});
}
function roll_stock_model(){
	var row_id=$("#row_id").val();
	var finish_qty=$("#finish_qty").val();
	if(row_id==0){
		$("#show_roll").html("<table cellspacing='10' style='border-spacing:10px;' class='display table table-bordered table-striped' id='mix_loose_material_table'><tr ><td>Roll Size</td><td>Roll Qty</td><td>Action</td></tr><tr id='rtr"+row_id+"'><td><input type='number' class='form-control rolsiz' value='' id='rolsiz"+row_id+"' name='rolsiz[]' /></td><td><input type='number' class='form-control rolqty' value='' id='rolqty"+row_id+"' name='rolqty[]' /></td><td><button class='btn btn-xs btn-primary' data-original-title='Add' data-toggle='tooltip' data-placement='top' type='button' onclick='roll_stock_model();'>Add</button></td></tr></table>");
	}else{
		//$("#show_roll").html("<tr><td><input type='number' class='form-control' value='' id='rolsiz'"+row_id+" name='rolsiz[]' /></td><td><input type='number' class='form-control' value='' id='rolqty'"+row_id+" name='rolqty[]' /></td></tr>");
		$("#mix_loose_material_table").append("<tr id='rtr"+row_id+"'><td><input type='number' class='form-control rolsiz' value='' id='rolsiz"+row_id+"' name='rolsiz[]' /></td><td><input type='number' class='form-control rolqty' value='' id='rolqty"+row_id+"' name='rolqty[]' /></td><td><button class='btn btn-xs btn-danger' data-original-title='Remove' data-toggle='tooltip' data-placement='top' type='button' onclick='remove_row("+row_id+");'>Remove</button></td></tr>");
	}

	var newroll_id=parseFloat(row_id)+parseFloat(1);
	$("#row_id").val(newroll_id);
}
function remove_row(row_id){
	$("#rtr"+row_id).html("");
}
function save_solid_exe_planning(){
	var total_roll_size=document.getElementsByName('rolsiz[]');
	var total_roll_qty=document.getElementsByName('rolqty[]');
	var cnt=total_roll_qty.length;
	var finish_qty=$("#finish_qty").val();
	var rqty=0;
	for(var i=0;i<cnt;i++)
	{
		var qty=parseFloat(total_roll_size[i].value)*parseFloat(total_roll_qty[i].value);	
		rqty=parseFloat(rqty)+parseFloat(qty);
	}
	if(finish_qty!=rqty){
		toastr.warning("Qty Not Match", "ERROR");
		return false;
	}
	var rolsiz_arr=[];
	var rolqty_arr=[];
	i = 0;
	$('input.rolsiz').each(function(){ 
		rolsiz_arr[i++]=$(this).val();
	});  
	
	j = 0;
	$('input.rolqty').each(function(){ 
		rolqty_arr[j++]=$(this).val();
	});  
	
	var product_id=$("#product_id").val();
	var balty=$("#balty").val();
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'/app/solid_printing_entry/',
		data: { mode : "save_solid_exe_planning", product_id:product_id,balty:balty,rolsize:rolsiz_arr,rolqty:rolqty_arr,finish_qty:finish_qty},
		success: function(response){
			var arr = jQuery.parseJSON(response);
			$('#solid_exe_entry').modal('hide');
			show_data();
			window.location=root_domain+inventory_domain+'solid_production_sticker_common_print/'+arr.id+'/3';
			Unloading();
		}		 
	});
}