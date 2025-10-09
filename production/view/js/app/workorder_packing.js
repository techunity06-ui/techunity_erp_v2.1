var datatable;

$(document).ready(function() {
	load_batch_no();
	load_tempout_data();
	load_workorder_stock_qty();
	load_new_product_unit();
});


function load_tempout_data()
{
	var workorder_id = $("#workorder_id").val()
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/workorder_packing/',
		data: { 
			mode : "load_tempoutward",
			workorder_id: workorder_id
		},
		success: function(data){
			$("#dynamic-table").empty().html(data);
			Unloading();
		}
	}); 
	
}


/*function load_tempout_data()
{
	
	datatable = $("#dynamic-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : false,
			"bProcessing": false,
			"bDestroy": true,
			"bServerSide" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO DATA ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+production_domain+'app/workorder_packing/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "load_tempoutward" });
			},
			"fnDrawCallback": function( oSettings ) {
				//alert(oSettings);
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
	
}*/



function get_packing_size(packing_id){
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/workorder_packing/',
		data: { 
			mode : "get_packing_size", 
			packing_id : packing_id
		},
		success: function(data){
			$("#packing_size").val(data);
			$("#size").val(data);
			Unloading();
		}
	});
}


function calculate_total_box_qty(box_qty){
	var size =$("#size").val();
	console.log(size)
	console.log(box_qty)
	var total_qty = parseFloat(size) *  parseFloat(box_qty); 

	$("#total_box_qty").val(total_qty);
}

function load_batch_no(){
	var product_id = $("#product_id").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/workorder_packing/',
		data: { 
			mode : "get_batch_no", 
			product_id : product_id
		},
		success: function(data){
			$("#batch_no").val(data);
			Unloading();
		}
	});

}


function load_workorder_stock_qty(){
	var workorder_id = $("#workorder_id").val();
	var product_id = $("#product_id").val();
	var unit_id = $('#packing_unit').val();

	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/workorder_packing/',
		data: { 
			mode : "get_workorder_qty", 
			workorder_id : workorder_id,
			product_id : product_id,
			unit_id : unit_id
		},
		success: function(data){
			$("#wo_stock_qty").val(data);
			Unloading();
		}
	});
}


function load_new_product_unit(){
	Loading();
	var product_id = $("#product_id").val()

	$.ajax({
		type: "POST",
		url: root_domain + production_domain + 'app/workorder_packing/',
		data: { mode : "get_product_unit", product_id:product_id },
		success: function(data){
			$('#packing_unit').empty().html(data);
			$('#packing_unit').select2({
				width: "100%"
			});

			Unloading();
			load_workorder_stock_qty();
		}
	});
}

function add_field(){
	var workorder_stock_qty = parseFloat($("#wo_stock_qty").val());
	var total_temp_qty = 0;
	var workorder_id = $("#workorder_id").val();
	$('input.temp_qty').each(function(index){ 
		console.log(parseFloat($(this).val()))
		total_temp_qty = total_temp_qty + parseFloat($(this).val())
	});
	var box_qty = $("#box_qty").val();

	var pending_qty = workorder_stock_qty - total_temp_qty;

	var total_box_qty = parseFloat($("#total_box_qty").val());

	if(total_box_qty > pending_qty){
		toastr.warning("YOU CAN'T ADD MORE THAN STOCK QTY.", "ERROR")
		return false;
	}


	var size = $("#size").val();
	var box_qty = $("#box_qty").val();
	var valid_qty = size * box_qty;

	if(total_box_qty > valid_qty){
		toastr.warning("PLEASE CHECK TOTAL QUANTITY NOT MORE THAN "+ valid_qty, "ERROR")
		return false;
	}
var product_id = $("#product_id").val();
	Loading();

	$.ajax({
		type: "POST",
		url: root_domain + production_domain + 'app/workorder_packing/',
		data: {
			mode : "fieldadd",
			packing_id:$("#packing_id").val(),
			size:size,
			box_qty:box_qty,
			total_box_qty:total_box_qty,
			batch_no:$("#batch_no").val(),
			workorder_id : workorder_id,
			product_id:product_id
		},
		success: function(data){
			var arr = jQuery.parseJSON(data);
			if(arr.msg == '1'){
				toastr.success("PACING ADDED SUCCESSFULLY", "SUCCESS")
				load_batch_no();
				$("#packing_size").val("");
				$("#size").val("");
				$("#box_qty").val("");
				$("#total_box_qty").val("");
				$("#packing_id").select2("val","");
			}else{
				toastr.warning("SOMETHING WENT WRONG.", "ERROR")
			}
			load_tempout_data();
			Unloading();
		}
	});
}

function check_All()
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

function workorder_packing_print(){

	var checbox_checked_len = $('input:checkbox:checked').length;
	if($('#checkAll').is(':checked')){
		checbox_checked_len = checbox_checked_len - 1;
	}

	if(checbox_checked_len < 1)
	{
		toastr.warning("Please Select at least 1 checkbox ", "ERROR")
		return false;
	}else if(checbox_checked_len > 10)
	{
		toastr.warning("YOU CAN'T SELECT MORE THAN 10 QC", "ERROR")
		return false;
	}
	else
	{
		Loading();
		var i = 1;
		var workorder_packing_trn_id = ""
		$("input:checkbox").each(function () {
			if ($(this).is(":checked")) {
				if(typeof $(this).attr("value") != 'undefined')
				{
					if(i == 1){
						workorder_packing_trn_id = $(this).attr("value");
					}else{
						workorder_packing_trn_id += "," + $(this).attr("value");
					}
					
					if(i == checbox_checked_len){
						$("#workorder_packing_trn_id").val(workorder_packing_trn_id);
						setTimeout(function(){
							$("#wo_print_add").submit();
						},1500)
					}else{
						i++;	
					}
				}
			}
		});  
	}
}

function delete_packing(id) 
{
	var r= confirm(" Are you sure want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + production_domain + 'app/workorder_packing/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					Unloading();
					if(response.trim() == "1") {
						toastr.success("PACKING DELETE SUCCESSFULLY", "SUCCESS");
						load_tempout_data();
						
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}	

				}
			});	
		}	
}


$("#Workorder_packing_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#Workorder_packing_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var product_id = $("#product_id").val();
	var workorder_id = $("#workorder_id").val();
	var packing_unit = $("#packing_unit").val();
	var remark = $("#remark").val();

	var total_temp_qty = 0;
	$('input.temp_qty').each(function(index){ 
		total_temp_qty = total_temp_qty + parseFloat($(this).val())
	});
	

	var form_data = {
		product_id: product_id,
		workorder_id: workorder_id,
		packing_unit: packing_unit,
		remark: remark,
		total_qty: total_temp_qty,
		mode:$("#mode").val(),
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+production_domain+'app/workorder_packing/',
		type: "POST",
		data: form_data,
		success: function(response)
		{			
			var obj=jQuery.parseJSON(response);
			if(obj.msg == '1') {
				$("#btn_print").show()
				toastr.success("PACKING GENERATE SUCCESSFULLY", "SUCCESS")
				Unloading();
				load_workorder_stock_qty();
				load_tempout_data();
				window.location = root_domain + production_domain + 'work_order';

			}
			else {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});