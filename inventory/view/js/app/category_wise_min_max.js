//var datatable;
$(document).ready(function() {

	show_data();
	
});


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


function show_data() {
	var product_category = $('#product_category').val();
	var branch_id = $('#branch_id').val();
	
	
	datatable = $("#dynamic-table").dataTable({
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
		"sAjaxSource": root_domain+inventory_domain+'app/min_max_category/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "generate_report_min" },{ "name": "product_category", "value": product_category },{ "name": "branch_id", "value": branch_id });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function save_store_order_request(product_id,product_category,base_unit,conv_unit,base_qty){
	var req_qty = $("#req_qty"+product_id).val();
	var reorder_qty=$("#reorder_qty"+product_id).val();
	var wo_qty = req_qty / reorder_qty;

	if(reorder_qty != "" && reorder_qty > 0){
			if(!isInteger(wo_qty)){
				toastr.warning("You Can't Process. Reorder Qauntity is " + reorder_qty, "ERROR");
				return false;	
		}
	} 

	if(req_qty=="" || req_qty == 0){
		toastr.warning("Enter Request Qty", "WARNING"); 
		return false;
	}

	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/min_max_category/',
		data: { 
				mode:"add", 
				product_id:product_id, 
				product_category:product_category,
				base_unit:base_unit,
				conv_unit:conv_unit,
				req_qty:req_qty,
				base_qty:base_qty
			},
		success: function(resp){
			if(resp.trim() == '1'){
				toastr.success("STORE MATERIAL REQUEST SUCCESSFULLY", "SUCCESS");
				show_data();
			}else{
				toastr.warning("SOMETHING WRONG!", "WARNING"); 
			}
			Unloading();
		}
	});
}


function request_product_qty(){
	var checbox_checked_len = $('input:checkbox:checked').length;
	if(checbox_checked_len < 1)
	{
		toastr.warning("Please Select at least 1 cehckbox ", "ERROR")
		return false;
	}else{
		var bomObj = {};
		bomObj.product_id = [];
		bomObj.req_qty = [];
		bomObj.product_category = [];
		bomObj.base_unit = [];
		bomObj.conv_unit = [];
		bomObj.base_qty = [];
		
		var errorlog=0;
		$("input:checkbox").each(function () {
			if ($(this).is(":checked")) {
				if(typeof $(this).attr("value") != 'undefined')
				{
					var product_id = $(this).attr("value");
					
					var req_qty = $("#req_qty"+product_id).val();
					var reorder_qty=$("#reorder_qty"+product_id).val();
					
					var wo_qty = req_qty / reorder_qty;
					if(reorder_qty != "" && reorder_qty > 0){
							if(!isInteger(wo_qty)){
								errorlog +=parseFloat(1);

								$("#req_qty"+product_id).css("border", "1px solid red");
								toastr.warning("Please enter Qauntity as per reorder qty. Reorder Qauntity is " + reorder_qty, "ERROR");
								return false;	
						}
					} 
					if(req_qty=="" || req_qty == 0){
						errorlog +=parseFloat(1);

						toastr.warning("Enter Request Qty", "WARNING"); 
						 $("#req_qty"+product_id).css("border", "1px solid red");
						return false;
					}else{
						$("#req_qty"+product_id).css("border", "1px solid #ccc");
						bomObj.product_id.push(product_id);
						bomObj.req_qty.push(req_qty);
						bomObj.product_category.push($(this).attr("data-product_category"));
						bomObj.base_unit.push($(this).attr("data-product_base_unit"));
						bomObj.conv_unit.push($(this).attr("data-product_conv_unit"));
						bomObj.base_qty.push($(this).attr("data-product_min_stock"));
					}
				}
			}
		});  

		if(errorlog>"0"){
			// toastr.warning("Grater Thean Qty", "WARNING"); 
			return false;
		}

		Loading();
	
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/min_max_category/',
			data: { 
					mode:"add",
					doc_no : $("#doc_no").val(), 
					product_id:bomObj.product_id, 
					product_category:bomObj.product_category,
					base_unit:bomObj.base_unit,
					conv_unit:bomObj.conv_unit,
					req_qty:bomObj.req_qty,
					base_qty:bomObj.base_qty,
					invoicetype_id:$("#invoicetype_id").val()
				},
			success: function(resp){
				if(resp.trim() == '1'){
					toastr.success("STORE MATERIAL REQUEST SUCCESSFULLY", "SUCCESS");
					setTimeout(function(){
						Unloading();
						window.location.reload(); 
					},500);	
				}else{
					toastr.warning("SOMETHING WRONG!", "WARNING"); 
					Unloading();
				}
			}
		});
	}
}

function check_css_vaildation(product_id){
	$("#req_qty"+product_id).css("border", "1px solid #ccc");
}
function load_docno(id)
	{
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/min_max_category/',
			data: { mode : "load_docno", typeid : id},
			success: function(data){
				console.log(data);
				var no = jQuery.parseJSON(data);
				$('#doc_no').val(no.invoiceno);
				
			}
		});
	}