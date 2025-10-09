var datatable;
$(document).ready(function() {
		datatable = $("#dynamic-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bServerSide" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO DATA ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 100,
			"sAjaxSource": root_domain+'app/invoicetypemst/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" } );
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
	// validate the comment form when it is submitted        

// validate vendor add form on keyup and submit
$("#invoicetype_add").validate({
	rules: {
		invoice_type: {
			required: true
		}
	},
	messages: {
		invoice_type: {
			required: "Enter Invoice Type"
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditinvoicetype").validate({
	rules: {
		edit_invoice_type: {
			required: true
		}
	},
	messages: {
		edit_invoice_type: {
			required: "Enter Invoice Type",
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
	
	var token=  $("#token").val();	
	var cust_id= $("#cust_id").val();
	var mode=$("#mode").val();
		
	var form_data = {
		cust_id:cust_id,
		invoicetype_name:$("#invoice_type").val(),		
		//taxinvoicestart: $("#taxinvoice_start").val(),		
		exciseinvoicestart:$("#exciseinvoice_start").val(),
		invoice_format:$("#invoice_format").val(),
		format_value:$("#format_value").val(),
		end_format_value:$("#end_format_value").val(),
		token:token,
		mode:mode,
		is_ajax: 1
	};	
	$.ajax({
		cache:false,
		url: root_domain+'app/invoicetypemst/',
		type: "POST",
		data: form_data,
		success: function(resnse)
		{
			console.log(resnse);			
			if(resnse.trim() == '1') {
				toastr.success("INVOICE TYPE ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				datatable.fnReloadAjax();
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
			//$('#cust_id').select2("val",cust_id);
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
		invoice_format:$("#edit_invoice_format").val(),
		format_value:$("#edit_format_value").val(),		
		end_format_value:$("#edit_end_format_value").val(),		
		token:$("#edit_token").val(),
		mode:'edit',
		is_ajax: 1
	};	
	$.ajax({
		cache:false,
		url: root_domain+'app/invoicetypemst/',
		type: "POST",
		data: form_data,
		success: function(resnse)
		{
			console.log(resnse);
			
			if(resnse.trim() == '1') {
				toastr.success("INVOICE TYPE UPDATED SUCCESSFULLY", "SUCCESS");
				datatable.fnReloadAjax();
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
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/invoicetypemst/',
				data: { mode : "delete", token :  $("#token").val(), eid : id },
				success: function(resnse)
				{
					
					if(resnse.trim() == "1") {
						toastr.success("INVOICE TYPE DELETE SUCCESSFULLY", "SUCCESS");
						datatable.fnReloadAjax();
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
		Loading(true);
		editReq = $.ajax({
			type: "POST",
			url: root_domain+'app/invoicetypemst/',
			data: { mode : "preedit", id : id },
			success: function(resnse)
			{
				console.log(resnse);
				var obj = jQuery.parseJSON(resnse);
				$("#ModalEditAccount").modal("show");
				$("#edit_id").val(id);								
				$("#edit_invoice_type").val(obj.invoice_type);
				$("#edit_taxinvoice_start").val(obj.taxinvoice_start);
				$("#edit_exciseinvoice_start").val(obj.exciseinvoice_start);
				$("#edit_invoice_format").val(obj.invoice_format);
				
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
			url: root_domain+'app/invoicetypemst/',
			data: { mode : "invoice_series_same",  typeid:typeid},
			success: function(response)
			{
				//response=response.replace(/\<head\s*[\/]?>/gi,"");
				var data = JSON.parse(response);
				if(data.status == "1") {
					//datatable.fnReloadAjax();
					toastr.success("SERIES TYPE SAME", "SUCCESS")
					location.reload();
				Unloading();					
				}
				else{
					toastr.warning("SELECT INVOICE TYPE", "ERROR");
				}
				Unloading();							
			}
		});
		
	}
	else 
	{
		toastr.warning("SELECT INVOICE TYPE", "ERROR");
	}
	Unloading();
}