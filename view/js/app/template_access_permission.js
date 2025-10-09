$(document).ready(function() {
	load_template_datatable();	       

	// validate vendor add form on keyup and submit
	$("#template_access_permission_add").validate({
		rules: {
		template_name: {
				required: true
			}
		},
		messages: {
			template_name: {
				required: "Enter Template Name"
			}
		}
	}); 
});
$("#template_access_permission_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#template_access_permission_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = new FormData(this);
	$.ajax({
		cache:false,
		url: root_domain+'app/template_access_permission/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			if(response.trim() == '1') {				
				toastr.success("TEMPLATE ACCESS PERMISSION ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				window.location=root_domain+'template_access_permission_list';
				$('#template_access_permission_add').trigger('reset');
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
			else if(response.trim() == 'update')
			{	
				toastr.success("TEMPLATE ACCESS PERMISSION UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain + 'template_access_permission_list';
			}
			$('#template_access_permission_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function delete_template_access_permission(id) 
{
	var r= confirm("Are you sure want to delete?");
		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + 'app/template_access_permission/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response)
					if(response.trim() == "1") {
						toastr.success("TEMPLATE ACCESS PERMISSION DELETE SUCCESSFULLY", "SUCCESS");
						load_template_datatable();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
}
function changeStatus(id,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + 'app/template_access_permission/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("TEMPLATE ACCESS PERMISSION STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			load_template_datatable();
		}
	});
	Unloading();
}
function load_user_menu(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+'app/template_access_permission/',
		data: { mode : "show_user_menu", id : id },
		success: function(response)
		{
			$("#show_user_menu").html(response);
			Unloading();

			var totalRows = $('.headerRow').length;
			if(totalRows > 0) {
				$('.headerRow').each(function( index ) {
					var i = index + 1;
				  	$(this).find('.allMenuShow').each(function( index ) {
				  		var dataCls = $(this).attr('data-cls');
					  	var totalGroupChkBox = $('.sub_'+i+' .'+dataCls).length;
					  	var checkedGroupChkBox = $('.sub_'+i+' .'+dataCls+':checked').length;
				  		if(totalGroupChkBox && totalGroupChkBox === checkedGroupChkBox) {
					  		$('td[data-cls="'+dataCls+'"][data-id="'+i+'"] .mainChk').prop('checked', true);
					  	}
					});
				});
			}
		}
	});	
}

function load_template_datatable(){
	datatable = $("#template-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bServerSide" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO TEMPLATE ACCESS PERMISSION ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/template_access_permission/',
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
}