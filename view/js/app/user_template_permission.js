var datatable;
$(document).ready(function() {
		datatable = $("#dynamic-table").dataTable({
			"bStateSave": true,
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bServerSide" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO UserType ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/user_template_permission/',
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
	$("#user_template_permission_add").validate({
		rules: {
		usertype_id: {
				required: true
			}
		},
		messages: {
			usertype_id: {
				required: "Select User Type Name"
			}
		}
	}); 
});
$("#user_template_permission_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#user_template_permission_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = new FormData(this);
	$.ajax({
		cache:false,
		url: root_domain+'app/user_template_permission/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {				
				toastr.success("USER PERMISSION TEMPLATE ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				window.location=root_domain+'user_template_permission_list';
				$('#user_template_permission_add').trigger('reset');
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
			$('#user_template_permission_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function load_user_menu(id,temp_id)
{
	if(id != ''){ 
		Loading(true);
		if(id != '' && temp_id == null){
			$.ajax({
				type: "POST",
				url: root_domain+'app/user_template_permission/',
				data: { mode : "show_template_name", id : id, temp_id : temp_id},
				success: function(response)
				{
					var data = JSON.parse(response);
					console.log(data);
					$('#template_id').empty().append(data.template);
					$("#template_id").select2({
			         	width: '100%'
			        });	
					Unloading();
				}
			});	
		}
		$.ajax({
			type: "POST",
			url: root_domain+'app/user_template_permission/',
			data: { mode : "show_user_menu", id : id, temp_id : temp_id},
			success: function(response)
			{
				$("#show_user_menu").html(response);
				$(".save-permission-btn").css("display","block");
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
	}else{
		$('#template_id').select2('val','');
		$("#show_user_menu").html('');
		$(".save-permission-btn").css("display","none");
	}
}
function template_name(){
	$('#template_id').select2('val','');
}
