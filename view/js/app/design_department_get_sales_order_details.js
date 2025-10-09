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
		url: root_domain+'app/get_sales_order_details/',
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
		'aoColumnDefs': [{
        'bSortable': false,
        'aTargets': ['nosort']
    }],
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
		"sAjaxSource": root_domain+'app/design_department_get_sales_order_details/',
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
function assign_bom_version(product_id,so_id){
	
	$('#preview_so_allocate_modal').modal('show');
	
	$('.product_name').text(product_id);
	load_bom_datatable(product_id,so_id);
	
	
}

function edit_custom_bom(product_id,so_id,bom_id){
	$('#preview_so_allocate_modal').modal('hide');
	$('#preview_edit_bom_modal').modal('show');	
	$('.bom_id').text(product_id);	
	
}



function assign_standard_bom()
{
	
	var checbox_checked_len = $('input:checkbox:checked').length;
	if(checbox_checked_len < 1)
	{
		toastr.warning("Please Select at least 1 cehckbox ", "ERROR")
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
		url: root_domain+'/app/design_department_get_sales_order_details/',
		data: { mode : "assign_standard_bom", product_id:bomObj.pidChecked,so_trn_id:bomObj.soidChecked,bom_id:bomObj.bomidChecked,branch_id:bomObj.branchidChecked},

		success: function(response){

			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
			Unloading();
			toastr.success("STANDARD BOM ASSIGNED SUCCESSFULLY", "SUCCESS");
				setTimeout(function(){
				window.location.reload(); 
				},1000);			
			}
			else if(arr.msg == '0') {
			toastr.warning("SOMETHING WRONG", "ERROR")
			Unloading();
			}			
			Unloading();
			show_data();

		}		 
		}); 
	}
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


function load_bom_datatable(product_id,so_id)
{	
	
	datatable = $("#assignbom-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			'aoColumnDefs': [{
			'bSortable': false,
			'aTargets': ['nosort']
			}],
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
			"sAjaxSource": root_domain+'app/design_department_get_sales_order_details/',
			"fnServerParams": function ( aoData ) {
				aoData.push( 
							{ "name": "mode", "value": "product_bom_data_fetch" },
							{ "name": "product_id", "value": product_id },
							{ "name": "so_id", "value": so_id }
							
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

function assign_custom_bom(pr_id,so_id,bom_id)
{		
		Loading();
		$.ajax({
		type: "POST",
		url: root_domain+'/app/design_department_get_sales_order_details/',
		data: { mode : "assign_custom_bom", pr_id:pr_id,so_id:so_id,bom_id:bom_id},
		success: function(response){
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
			Unloading();
			toastr.success("CUSTOM BOM ASSIGNED SUCCESSFULLY", "SUCCESS");
				setTimeout(function(){
				window.location.reload(); 
				},1000);			
			}
			else if(arr.msg == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			else if(arr.msg == '0') {
			toastr.warning("SOMETHING WRONG", "ERROR")
			Unloading();
			}			
			Unloading();
		}		 
		}); 
	
}
			
			


