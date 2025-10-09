$(document).ready(function() {
	load_series_type_datatable();        
	
	// validate vendor add form on keyup and submit
	$("#invoicetype_add").validate({
		rules: {
			invoice_type: {
				required: true
			},
			type_id: {
				required: true	
			}
		},
		messages: {
			invoice_type: {
				required: "Enter Invoice Type"
			},
			type_id:{
				required: "Enter Series Type"
			}
		}
	}); 
	// validate vendor edit form on keyup and submit
	$("#FormEditinvoicetype").validate({
		rules: {
			edit_invoice_type: {
				required: true
			},
			edit_type_id: {
				required: true
			}
		},
		messages: {
			edit_invoice_type: {
				required: "Enter Invoice Type",
			},
			edit_type_id: {
				required: "Enter Series Type",
			}
		}
	});		
	
});
$("#invoicetype_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#invoicetype_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var cust_id= $("#cust_id").val();
	var mode=$("#mode").val();
	
	var form_data = {
		cust_id:cust_id,
		invoicetype_name:$("#invoice_type").val(),				
		exciseinvoicestart:$("#exciseinvoice_start").val(),
		type_id:$("#type_id").val(),
		invoice_format:$("#invoice_format").val(),
		format_value:$("#format_value").val(),
		end_format_value:$("#end_format_value").val(),
		gst_code:$("#gst_code").val(),
		branch_id:$("#abranch_id").val(),
		mode:mode,
		is_ajax: 1
	};	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/series_type_mst/',
		type: "POST",
		data: form_data,
		success: function(resnse)
		{
			console.log(resnse);			
			if(resnse.trim() == '1') {
				toastr.success("SERIES TYPE ADDED SUCCESSFULLY", "SUCCESS");
				Unloading();
				load_series_type_datatable();
			}
			else if(resnse.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(resnse.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#invoicetype_add').trigger('reset');	
			$('#abranch_id').select2("val",1000);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditinvoicetype").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditinvoicetype").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		invoicetype_name: $("#edit_invoice_type").val(),		
		taxinvoicestart: $("#edit_taxinvoice_start").val(),		
		exciseinvoicestart: $("#edit_exciseinvoice_start").val(),
		type_id : $("#edit_type_id").val(),	
		invoice_format:$("#edit_invoice_format").val(),
		format_value:$("#edit_format_value").val(),		
		end_format_value:$("#edit_end_format_value").val(),
		gst_code:$("#edit_gst_code").val(),
		branch_id: $("#e_branch_id").val(),		
		mode:'edit',
		is_ajax: 1
	};	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/series_type_mst/',
		type: "POST",
		data: form_data,
		success: function(resnse)
		{
			console.log(resnse);
			
			if(resnse.trim() == '1') {
				toastr.success("SERIES TYPE UPDATED SUCCESSFULLY", "SUCCESS");
				load_series_type_datatable();
				Unloading();						
			}
			else if(resnse.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(resnse.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$("#ModalEditAccount").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function delete_catalog(id) 
{
	var r= confirm(" Are you sure want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/series_type_mst/',
			data: { mode : "delete", eid : id },
			success: function(resnse)
			{
				
				if(resnse.trim() == "1") {
					toastr.success("SERIES TYPE DELETE SUCCESSFULLY", "SUCCESS");
					load_series_type_datatable();
					Unloading();
				}
				else if(resnse.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function edit_test(id)
{
	$("#FormEditinvoicetype").valid();
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/series_type_mst/',
		data: { mode : "preedit", id : id },
		success: function(resnse)
		{
			var obj = jQuery.parseJSON(resnse);
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(id);								
			$("#edit_invoice_type").val(obj.invoice_type);
			$("#edit_taxinvoice_start").val(obj.taxinvoice_start);
			$("#edit_exciseinvoice_start").val(obj.exciseinvoice_start);
			$("#edit_type_id").val(obj.type_id);
			$("#edit_invoice_format").val(obj.invoice_format);
			$("#e_branch_id").select2("val", obj.branch_id);
			$("#edit_gst_code").val(obj.gst_code);
			if(obj.invoice_format>0)
			{
				$('#edit_format_value_div').removeClass('hidden');
				if(obj.invoice_format=='3'){
					$('#edit_end_format_value_div').removeClass('hidden');
					}else{
					$('#edit_end_format_value_div').addClass('hidden');
				}
				$("#edit_format_value").val(obj.format_value); 	
				$("#edit_end_format_value").val(obj.end_format_value); 	
			}
			else{
				$('#edit_format_value_div').addClass('hidden');
				$('#edit_end_format_value_div').addClass('hidden');
				$("#edit_format_value").val(''); 
				$("#edit_end_format_value").val(''); 
			}
			Unloading();
		}
	});	
}
function format_valuechange(typevalue)
{
	if(typevalue>0)
	{
		$('#format_value_div').removeClass('hidden');
		
		if(typevalue=='3'){
			$('#end_format_value_div').removeClass('hidden');
			}else{
			$('#end_format_value_div').addClass('hidden');
		}
		view_format($('#format_value').val());
	}
	else
	{
		$('#format_value_div').addClass('hidden');	
		$('#end_format_value_div').addClass('hidden');	
		$('#ex_format_div').addClass('hidden');	
	}
}
function view_format(formatval)
{
	var format_value=$('#format_value').val();
	var end_format_value=$('#end_format_value').val();
	
	var format=$('#invoice_format').val();
	var excise=$('#taxinvoice_start').val();
	
	if(format>0)
	{
		$('#ex_format_div').removeClass('hidden');	
		if(format==1)
		{
			$('#ex_format').html(formatval+excise);
		}
		else if(format==2)
		{
			$('#ex_format').html(excise+formatval);
		}
		else if(format==3)
		{
			$('#ex_format').html(format_value+"<b>"+excise+"</b>"+end_format_value);
		}
	}
	else
	{
		$('#format_value_div').addClass('hidden');	
		$('#end_format_value_div').addClass('hidden');	
		$('#ex_format_div').addClass('hidden');	
		
	}
}
function edit_format_valuechange(typevalue)
{
	if(typevalue>0)
	{
		$('#edit_format_value_div').removeClass('hidden');
		if(typevalue=='3'){
			$('#edit_end_format_value_div').removeClass('hidden');
			}else{
			$('#edit_end_format_value_div').addClass('hidden');
		}
	}
	else
	{
		$('#edit_format_value_div').addClass('hidden');
		$('#edit_end_format_value_div').addClass('hidden');
	}
}
function invoice_series_same()
{
	Loading(true);
	var typeid = $("#dynamic-table input:checkbox:checked").map(function(){
        return $(this).val();
	}).toArray();
	if(typeid!="")
	{
		
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/series_type_mst/',
			data: { mode : "invoice_series_same",  typeid:typeid},
			success: function(response)
			{
				var data = JSON.parse(response);
				if(data.status == "1") {
					toastr.success("SERIES TYPE SAME", "SUCCESS")
					location.reload();
					Unloading();					
				}
				else{
					toastr.warning("SELECT SERIES TYPE", "ERROR");
				}
				Unloading();							
			}
		});
		
	}
	else 
	{
		toastr.warning("SELECT SERIES TYPE", "ERROR");
	}
	Unloading();
}

function load_series_type_datatable(){
	var branch_id = $('#branch_id').val();

	datatable = $("#dynamic-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+administration_domain+'app/series_type_mst/',
		"fnServerParams": function ( aoData ) {
			aoData.push( 
				{ "name": "mode", "value": "fetch" },
				{"name": "branch_id", "value": branch_id }
			);
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