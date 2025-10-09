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
					"sProcessing": "<img src='"+root_domain + crm_domain +"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO Payment Terms ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain + crm_domain +'app/payment_terms/',
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
$("#payment").validate({
	rules: {
		payment_terms1: {
			required: true
			
		}
	},
	messages: {
		payment_terms1: {
			required: "Enter Payment Terms"
			
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditpayment").validate({
	rules: {
		edit_payment_terms: {
			required: true
			
		}		

	},
	messages: {
		edit_payment_terms: {
			required: "Enter bank Name"
			
		}
	}
});		

});
$("#payment").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#payment").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var token=  $("#token").val();
	var payment_days= $('#payment_days').val();

	var form_data = {
		payment_terms: $("#payment_terms1").val(),
		payment_days: payment_days,
		token:token,
		mode:$("#mode").val(),
		model:$("#model").val(),
		is_ajax: 1
	};
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/payment_terms/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			var data = JSON.parse(response);
			var responsevalue=data.res;
			if(responsevalue.trim() == '1') {
				toastr.success("PAYMENT TERMS ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				datatable.fnReloadAjax();
				
			}
			else if(responsevalue.trim() == '2') {
				toastr.success("PAYMENT TERMS ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-payterms-modal-lg").modal("hide");
				$('#payment_terms').append('<option value='+data.terms_id+'>'+data.payment_days+' days'+'</option>');
				$('#payment_terms').val(data.terms_id);
				$("#payment_terms").trigger('change')
				$('#payment').trigger('reset');
				Unloading();
			}
			else if(responsevalue.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(responsevalue.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				$("#model_addbank").modal("hide");
				$('#bank_add').trigger('reset');
				Unloading();				
			}
			$('#payment').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditpayment").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditpayment").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		payment_terms: $("#edit_Patment_terms").val(),
		payment_days: $("#edit_payment_days").val(),
		token:$("#edit_token").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/payment_terms/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			
			if(response.trim() == '1') {
				toastr.success("PAYMENT TERMS UPDATED SUCCESSFULLY", "SUCCESS");
				datatable.fnReloadAjax();
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
function delete_reload()
{
	datatable.fnReloadAjax();
}
function delete_bank(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain +'app/payment_terms/',
				data: { mode : "delete", token :  $("#token").val(), eid : id },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success("PAYMENT TERMS DELETE SUCCESSFULLY", "SUCCESS");
						delete_reload();
						Unloading();
					}
					else if(response.trim() == "0") {
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
			url: root_domain + crm_domain +'app/payment_terms/',
			data: { mode : "preedit", id : id },
			success: function(response)
			{
				//console.log(response);
				var obj = jQuery.parseJSON(response);
				$("#ModalEditAccount").modal("show");
				$("#edit_id").val(id);				
				$("#edit_Patment_terms").val(obj.payment_terms);
				$("#edit_payment_days").val(obj.payment_days);
				Unloading();
			}
		});	
	}