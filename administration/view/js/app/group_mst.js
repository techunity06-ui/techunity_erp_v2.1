var datatable;
$(document).ready(function() {
        load_datatable();
	// validate the comment form when it is submitted        

// validate vendor add form on keyup and submit
$("#group_add").validate({
	rules: {
		g_name: {
			required: true
			//lettersonly: true
		},
		g_parent: {
			required: true
		},
                e_branch_id: {
			required: true
		}
		
	},
	messages: {
		g_name: {
			required: "Enter Sub Category Name"			
		},
		g_parent: {
			required: "Please Select Category "
		},
                e_branch_id: {
			required: "Select branch"
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditunit").validate({
	rules: {
                e_g_name: {
                                required: true,
                                //lettersonly: true
                },e_g_parent: {
                        required: true,
                }
        },
	messages: {
		e_g_name: {
			required: "Enter Sub Category Name"		
		},
                e_g_parent: {
                    required: "Please Select Category "
                }
	}
});		

});
$("#group_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#group_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var g_name=$("#g_name").val();
	var g_parent=$("#g_parent").val();
	var g_opening=$("#g_opening").val();
	var g_form=$("#g_form").val();
	var group_series_start=$("#group_series_start").val();
	var series_format=$("#series_format").val();
	var format_value=$("#format_value").val();
	var end_format_value=$("#end_format_value").val();
	var group_priority = $('#group_priority').val();
	//alert(g_parent);
	var form_data = {
		g_name: g_name,
		g_parent: g_parent,
		g_opening: g_opening,
		g_form: g_form,
		group_series_start: group_series_start,
		series_format: series_format,
		format_value: format_value,
		end_format_value: end_format_value,
		group_priority:group_priority,
		mode:$("#mode").val(),
		is_ajax: 1
	};

	//alert(form_data);	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/groupmst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{	
			console.log(response);
			var resp = JSON.parse(response);
			var msg= resp.msg;
			if(msg.trim() == '1') {				
				toastr.success("GROUP LIST ADDED SUCCESSFULLY", "SUCCESS");
				get_group_dropdown('g_parent');
				Unloading();
				datatable.fnReloadAjax();
				load_datatable();
			}
			if(msg.trim() == '2') {				
				toastr.success("GROUP LIST ADDED SUCCESSFULLY", "SUCCESS");
				$("#add_expense_head_modal").modal("hide");
				$('#expense_head_id').append('<option value='+resp.g_id+'>'+resp.g_name+'</option>'); 
				$('#expense_head_id').select2("val",resp.g_id);
				$("#expense_head_id").trigger('change'); 
				Unloading();
				datatable.fnReloadAjax();
			}
			else if(msg.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(msg.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#group_add').trigger('reset');
			$('#e_branch_id').select2('val', '1000');
			$('#g_parent').select2('val', '');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditunit").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditunit").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		e_g_branch_id: $("#e_g_branch_id").val(),
		e_g_name: $("#e_g_name").val(),
		e_g_parent: $("#e_g_parent").val(),
		e_g_opening: $("#e_g_opening").val(),
		e_g_form: $("#e_g_form").val(),
		edit_taxinvoice_start: $("#edit_taxinvoice_start").val(),
		edit_invoice_format: $("#edit_invoice_format").val(),
		edit_format_value: $("#edit_format_value").val(),
		edit_end_format_value: $("#edit_end_format_value").val(),
		edit_group_priority : $('#edit_group_priority').val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/groupmst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				toastr.success("GROUP LIST UPDATED SUCCESSFULLY", "SUCCESS");
				datatable.fnReloadAjax();
				load_datatable();
				Unloading();						
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response.trim() == '-1')
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

function load_datatable(){
        var branch_id = $('#branch_id').val();
        datatable = $("#dynamic-table").dataTable({
                "bAutoWidth" : false,
                "bFilter" : true,
                "bSort" : true,
                "bDestroy" : true,
                "bProcessing": true,
                "bServerSide" : true,
                "oLanguage": {
                                "sLengthMenu": "_MENU_",
                                "sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
                                "sEmptyTable": "NO DATA ADDED YET !"
                },
                "aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
                "iDisplayLength": 10,
                "sAjaxSource": root_domain+administration_domain+'app/groupmst/',
                "fnServerParams": function ( aoData ) {
                        aoData.push( { "name": "mode", "value": "fetch" }, { "name": "branch_id", "value": branch_id } );
                },
                "fnDrawCallback": function( oSettings ) {
                        $('.ttip, [data-toggle="tooltip"]').tooltip();
                }
        }).fnSetFilteringDelay();

        //Search input style
        $('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
        $('.dataTables_length select').addClass('form-control');
}
function delete_reload()
{
	//datatable.fnReloadAjax();
        load_datatable();
}
function delete_category(id) 
{
	var r= confirm(" Are you sure want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/groupmst/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					var resp = JSON.parse(response);
					if(resp.msg == "-1") {
						swal("CURRENT RECORD ALREADY USED BELOW MODULES", ""+resp.table+"", "warning");
	         		    delete_reload();
						Unloading();
					}else if(resp.msg == "1") {
						toastr.success("GROUP LIST DELETE SUCCESSFULLY", "SUCCESS");
						delete_reload();
						Unloading();
					}else if(resp.msg == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}
function edit_group(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/groupmst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			console.log(response);
			var obj = jQuery.parseJSON(response);
			
			
			$("#edit_id").val(obj.g_id);
			$("#edit_pid").val(obj.g_pid);
			get_group_dropdown('e_g_parent');
                        $("#e_g_branch_id").val(obj.branch_id);
			$("#e_g_name").val(obj.g_name);
			$("#e_g_opening").val(obj.g_open_balance);
			$("#e_g_form").val(obj.form_id);
			$("#edit_taxinvoice_start").val(obj.group_start_series);
			$("#edit_invoice_format").val(obj.group_format);
			$('#edit_group_priority').val(obj.group_priority);
			if(obj.group_format>0)
			{
				$('#edit_format_value_div').removeClass('hidden');
				if(obj.group_format=='3'){
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
			setTimeout(function(){
				$("#FormEditunit").valid();
				$("#ModalEditAccount").modal("show");
			},300);
			
			Unloading();
		}
	});	
}

function get_group_dropdown(sel_id)
{
	var id = $('#edit_pid').val();
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/groupmst/',
		data: { mode : "get_group_dropdown_data",id:id },
		success: function(response)
		{
			$('#'+sel_id).html(response);
			Unloading();
		}
	});	
}

function get_form_type(gid,text_id)
{
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/groupmst/',
		data: { mode : "get_form_type",gid:gid },
		success: function(response)
		{
			$('#'+text_id).val(response);
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
	
	var format=$('#series_format').val();
	var excise=$('#group_series_start').val();
	
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
