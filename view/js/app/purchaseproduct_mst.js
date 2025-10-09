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
			"sAjaxSource": root_domain+'app/purchaseproductmst/',
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
$("#product_add").validate({
	rules: {
		product_name: {
			required: true
		}
	},
	messages: {
		product_name: {
			required: "Enter Product"
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditproduct").validate({
	rules: {
		edit_product_name: {
			required: true
		}
	},
	messages: {
		edit_product_name: {
			required: "Enter product",
		}
	}
});		

});
$("#product_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#product_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading();	
	$(this).attr("disabled","disabled");		
	
	var token	=  $("#token").val();	
	var mode=$("#mode").val();
	var form_data = {
		product_name: $("#product_name").val(),		
		product_des: $("#productdes").val(),		
		product_code: $("#product_code").val(),	
		product_mst_rate: $("#product_mst_rate").val(),	
		product_mst_unitid: $("#product_mst_unitid").val(),	
		intra_tax: $("#intra_tax").val(),	
		inter_tax: $("#inter_tax").val(),	
		multi_company: $("#multi_company").prop("checked") ? 1 : 0,	
		model:$("#model").val(),
		token:token,
		mode:'Add',
		is_ajax: 1
	};	
	$.ajax({
		cache:false,
		url: root_domain+'app/purchaseproductmst/',
		type: "POST",
		data: form_data,
		success: function(resnse)
		{
			console.log(resnse);			
			var data = JSON.parse(resnse);
			var responsevalue=data.res;
			if(responsevalue.trim() == '1') {
				$('#product_add').trigger('reset');
				toastr.success("PRODUCT ADDED SUCCESSFULLY", "SUCCESS")
			
				Unloading();
				datatable.fnReloadAjax();
			}
			else if(responsevalue.trim() == '2') {
				var id=parseInt($('#fieldcnt').val());
				toastr.success("PRODUCT ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-example-modal-addproduct").modal("hide");
				$('#product_id').append('<option value='+data.product_id+'>'+data.product_name+'</option>');
				$('#product_id').select2("val",data.product_id);
				$('#product_id').trigger('change')
				$('#product_add').trigger('reset');
				$('#addproduct').hide();
				Unloading();
			}
			else if(responsevalue.trim() == '3')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				$("#bs-example-modal-addproduct").modal("hide");
				$('#product_add').trigger('reset');
				Unloading();				
			}
			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditproduct").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditproduct").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		product_name: $("#edit_product_name").val(),		
		product_des: $("#edit_product_des").val(),		
		product_code: $("#edit_product_code").val(),
		product_mst_rate: $("#edit_product_mst_rate").val(),
		product_mst_unitid: $("#edit_product_mst_unitid").val(),
		intra_tax: $("#edit_intra_tax").val(),
		inter_tax: $("#edit_inter_tax").val(),
		multi_company: $("#edit_multi_company").prop("checked") ? 1 : 0,			
		token:$("#edit_token").val(),
		mode:'edit',
		is_ajax: 1
	};	
	$.ajax({
		cache:false,
		url: root_domain+'app/purchaseproductmst/',
		type: "POST",
		data: form_data,
		success: function(resnse)
		{
			console.log(resnse);
			
			if(resnse.trim() == '1') {
				toastr.success("PRODUCT UPDATED SUCCESSFULLY", "SUCCESS");
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
function delete_product(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/purchaseproductmst/',
				data: { mode : "delete", token :  $("#token").val(), eid : id },
				success: function(resnse)
				{
					
					if(resnse.trim() == "1") {
						toastr.success("PRODUCT DELETE SUCCESSFULLY", "SUCCESS");
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
			url: root_domain+'app/purchaseproductmst/',
			data: { mode : "preedit", id : id },
			success: function(resnse)
			{
				//console.log(resnse);
				var obj = jQuery.parseJSON(resnse);
				$("#ModalEditAccount").modal("show");
				$("#edit_id").val(id);								
				$("#edit_product_name").val(obj.product_name);
				$("#edit_product_des").val(obj.product_des);
				$("#edit_product_code").val(obj.product_code);
				$("#edit_product_mst_rate").val(obj.product_mst_rate);
				$("#edit_product_mst_unitid").select2("val",obj.product_mst_unitid);
				$("#edit_intra_tax").val(obj.intra_tax);
				$("#edit_inter_tax").val(obj.inter_tax);
				if(obj.multi_company==1)
				{
					$("#edit_multi_company").prop('checked',true);
				}
				else
				{
					$("#edit_multi_company").prop('checked',false);
				}
				Unloading();
			}
		});	
}
