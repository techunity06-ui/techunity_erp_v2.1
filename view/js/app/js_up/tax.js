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
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/tax/',
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
$("#tax_add").validate({
	rules: {
		tax_name: {
			required: true
		},
		tax_value: {
			required: true,
			number:true
		},
		tax_group:{
			required:true
		},
		ledger_id:{
			required:true
		}
	},
	messages: {
		tax_name: {
			required: "Enter tax Name"
		},
		tax_value: {
			required: "Enter tax value",
			number:"only number"
		},
		tax_group:{
			required:'Select Group'
		},
		ledger_id:{
			required:'Select Ledger'
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEdittax").validate({
	rules: {
		tax_name: {
			required: true
		},
		tax_value: {
			required: true,
			number:true
		},
		edit_ledger_id: {
			required: true
		}		

	},
	messages: {
		tax_name: {
			required: "Enter tax Name"
		},
		tax_value: {
			required: "Enter tax value",
			number:"only number"
		},
		edit_ledger_id: {
			required: "Select Ledger"
		}
	}
});		

});
$(".btn_close").click(function() {
    $("label.error").hide();
});

$("#tax_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#tax_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var token=  $("#token").val();		
	var form_data = {
		tax_name: $("#tax_name").val(),
		tax_value: $("#tax_value").val(),
		tax_group: $("#tax_group").val(),
		ledger_id: $("#ledger_id").val(),
		modal: $("#modal").val(),
		token:token,
		mode:$("#mode").val(),
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/tax/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			var data = JSON.parse(response);
			var responsevalue=data.res;
				if(responsevalue.trim() == '1') {
				toastr.success("TAX ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				datatable.fnReloadAjax();
				
			}
			else if(responsevalue.trim() == '2') {
				toastr.success("tax ADDED SUCCESSFULLY", "SUCCESS");
				$("#model_addemp").modal("hide");
				$('#taxid').append('<option value='+data.tax_id+'>'+data.tax_name+'</option>');				
				$("#taxid").trigger('change')
				$('#taxid').select2("val",data.taxid);
				$('#tax_add').trigger('reset');
				Unloading();
			}
			else if(responsevalue.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(responsevalue.trim() == '-1')
			{
				$("#model_addemp").modal("hide");
				$('#tax_add').trigger('reset');
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#tax_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEdittax").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEdittax").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		tax_name: $("#edit_tax_name").val(),
		tax_value: $("#edit_tax_value").val(),
		tax_group: $("#edit_tax_group").val(),
		ledger_id: $("#edit_ledger_id").val(),
		token:$("#edit_token").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/tax/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			console.log(response);
			var data = JSON.parse(response);
			var responsevalue=data.res;
			if(responsevalue.trim() == 'update') {
				toastr.success("TAX UPDATED SUCCESSFULLY", "SUCCESS");
				datatable.fnReloadAjax();
				Unloading();						
			}
			else if(responsevalue.trim() == '0') {
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
function delete_reload()
{
	datatable.fnReloadAjax();
}
function delete_tax(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/tax/',
				data: { mode : "delete", token :  $("#token").val(), eid : id },
				success: function(response)
				{
					console.log(response);
					var data = JSON.parse(response);
					var responsevalue=data.res;
					if(responsevalue.trim() == "1") {
						toastr.success("TAX DELETE SUCCESSFULLY", "SUCCESS");
						delete_reload();
						Unloading();
					}
					else if(responsevalue.trim() == "0") {
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
			url: root_domain+'app/tax/',
			data: { mode : "preedit", id : id },
			success: function(response)
			{
				console.log(response);
				var obj = jQuery.parseJSON(response);
				$("#ModalEditAccount").modal("show");
				$("#edit_id").val(id);				
				$("#edit_tax_name").val(obj.tax_name);
				$("#edit_tax_value").val(obj.tax_value);
				$("#edit_tax_group").select2("val",obj.tax_group);
				$("#edit_ledger_id").select2("val",obj.ledger_id);
				
				Unloading();
			}
		});	
	}