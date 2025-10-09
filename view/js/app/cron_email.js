
$(document).ready(function() {

	load_email_datatable();
	$("#cron_email_add").validate({
		rules: {
			email_user_id:{
				required:true
			},
			// director_name:{
			// 	required:true
			// },
			
			// director_email:{
			// 	email:true,
			// 	required:true
			// },
			
		},
		messages: {
			// director_name:{
			// 	required: "Director name must be Enter"
			// },
			
			// director_email:{
			// 	email:"Enter Valid Email",
			// 	required: "Email must be Enter"
			// },
			email_user_id:{
				required: "Select User"
			},
			
		}
	});
        
 
});

$("#cron_email_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#cron_email_add").valid()) {
		return false;
	}
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");	 
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+'app/cron_email/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{	
			//console.log(response);
			
			var arr = jQuery.parseJSON(response);
			
			if(arr.msg == '1') {
						
				Unloading();
				toastr.success("EMAIL ADDED SUCCESSFULLY", "SUCCESS");	
				load_email_datatable();
			}
			else if(arr.msg == '1') {
				Unloading();
				toastr.error("Something Went Wrong", "ERROR");	
			}
			
			else if(arr.msg =='update')
			{	
				toastr.success("EMAIL UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				load_email_datatable();
				
			}
			$('#cron_email_add').trigger('reset');
			$('#eid').val('');	
			$('#email_user_id').select2('val','');
			$("#btn_submit").html("Submit");
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function edit_data(id){
	$("#eid").val(id);
	$("#btn_submit").html("Update");

	$.ajax({
			type: "POST",
			url: root_domain+'app/cron_email/',
			data: { mode : "get_cron_email_data",  send_email_id : id },
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
					$("#director_name").val(data.director_name);
					$("#director_email").val(data.email);	
					$("#email_user_id").val(data.email_user_id).trigger('change');
					$('#email_user_id').focus();
			}
		});	

}

function delete_email(id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/cron_email/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				console.log(response);
				if(response.trim() == "1") {
					toastr.success("EMAIL DELETE SUCCESSFULLY", "SUCCESS");
					load_email_datatable();
				}
				
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}			
				Unloading();				
			}
		});	
	}
}


function load_email_datatable(){
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
		"sAjaxSource": root_domain+'app/cron_email/',
		"fnServerParams": function ( aoData ) {
			aoData.push( 
				{ "name": "mode", "value": "fetch" },
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
