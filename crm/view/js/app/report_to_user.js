$(document).ready(function() {
	load_cust_datatable();
});

$("#FormEditReportuserMst").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditReportuserMst").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		user_locked_reason: $("#user_locked_reason").val(),
		mode:'unlock',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain+'app/report_to_user/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			if(response.trim() == '1') {
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");
				load_cust_datatable();
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
			$("#ModalEditReportuserMst").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function edit_user_unlock(id) 
{
	// var r= confirm(" Are you sure want to user unlock ?");
	
	// if(r) {
		// Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/report_to_user/',
			data: { mode : "preedit",  eid : id },
			success: function(response)
			{
				console.log(response);
				var obj =jQuery.parseJSON(response);
				$("#ModalEditReportuserMst").modal("show");
				$('#usertype').html(obj.usertype_name);			
				$('#user_name').html(obj.user_name);			
				$('#user_mail').html(obj.user_mail);			
				$('#user_phone').html(obj.user_phone);			
				$('#edit_id').val(obj.user_id);			
			}
		});	
	// }
}

function load_cust_datatable(){
	var party_type = $('input[name="party_type"]:checked').val();
	var business_type = $('#business_type').val();
	var branch_id = $('#branch_id').val();
	datatable = $("#dynamic-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		/*"aoColumns": [
			{ "bSortable": true },
			{ "bSortable": true },
			{ "bSortable": true },
			{ "bSortable": true },
			{ "bSortable": true }
		
		], */
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + crm_domain +'app/report_to_user/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },
					{ "name": "party_type", "value": party_type },
					{ "name": "business_type", "value": business_type },
					{ "name": "branch_id", "value": branch_id },
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