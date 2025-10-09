var datatable;
$(document).ready(function() {
	load_print_setup_table();
	show_print_menu();
	// validate vendor add form on keyup and submit
	$("#print_setup_add").validate({
		rules: {
			print_name: {
				required: true
			},
			fa_icon: {
				required: true
			},
			page_path: {
				required: true
			}
		},
		messages: {
			print_name: {
				required: "Enter Print Name"
			},
			fa_icon: {
				required: "Enter Fa-icon"
			},
			page_path: {
				required: "Enter Page Path"
			}
		}
	});        
});
$("#print_setup_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#print_setup_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");
	
	var str = $("#print_setup_add").serializeArray();
	$.ajax({
		cache:false,
		url: root_domain+'app/print_setup/',
		type: "POST",
		data: str,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {

				toastr.success("PRINT SETUP ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				window.location=root_domain + 'print_setup_list';

			}
			else if(arr.msg == '2') {
				toastr.success("PRINT SETUP UPDATED SUCCESSFULLY", "SUCCESS")
				Unloading();
				window.location=root_domain + 'print_setup_list';
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(arr.msg == '-1')
			{
				toastr.warning("CURRENT PRINT SETUP ALREADY EXISTS", "ERROR")
				Unloading();				
			}
			$('#print_setup_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
$("#printsetup_permission_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#printsetup_permission_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");
	
	var str = $("#printsetup_permission_add").serializeArray();
	$.ajax({
		cache:false,
		url: root_domain+'app/print_setup/',
		type: "POST",
		data: str,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {

				toastr.success("PRINT SETUP ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				window.location=root_domain + 'print_setup_permission';

			}
			else if(arr.msg == '2') {
				toastr.success("PRINT SETUP UPDATED SUCCESSFULLY", "SUCCESS")
				Unloading();
				window.location=root_domain + 'print_setup_permission';
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(arr.msg == '-1')
			{
				toastr.warning("CURRENT PRINT SETUP ALREADY EXISTS", "ERROR")
				Unloading();				
			}
			//$('#printsetup_permission_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function delete_data(id) 
{
	var r= confirm("Are you sure want to delete?");
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/print_setup/',
			data: { mode : "delete", eid : id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("PRINT SETUP DELETE SUCCESSFULLY", "SUCCESS");
					load_print_setup_table();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function approve_status(id,approve_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + 'app/print_setup/',
		data: { mode : "approve_status", eid : id , approve_status:approve_status },
		success: function(response)
		{
			toastr.success("PRINT SETUP STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			load_print_setup_table();
		}
	});
	Unloading();
}
function load_print_setup_table(){	
	var datatable = $("#dynamic-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bDestroy": true,
			"bServerSide" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO DATA ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 50, 100], [10, 20, 50, 100]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/print_setup/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" }
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
function show_print_menu(){
	$.ajax({
			type: "POST",
			url: root_domain+'app/print_setup/',
			data: { mode : "show_print_menu"},
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
}