//var datatable;
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
		"sAjaxSource": root_domain+'app/user/',
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
$("#user_add").validate({
	rules: {
		user_name: {
			required: true			
		},
		user_email:{
			required:true
		},
		password:{
			minlength:5
		},
		user_address:{
			required: true
		},
		stateid: {
			required: true
		},
		cityid: {
			required: true
		},
		user_mobile: {
			required: true,
			number:true
		},
		usertype_id : {
			required: true
		} 
	},
	messages: {
		user_name: {
			required: "Enter User Name"
		},
		user_email:{
			required:"Enter Email"
		},
		password:{
			minlength:"Enter more than 5 Character"
		},
		user_address: {
			required: "Enter Address"
		},
		stateid: {
			required: "State must be select"
		},
		cityid: {
			required: "City must be select"
		},
		user_mobile: {
			required: "Enter Mobile no",
			number:"Enter Only number "
		},
		usertype_id : {
			required: "Select User Type"
		}
	}
}); 

});

$(".btn_close").click(function() {
    $("label.error").hide();
});

$("#user_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#user_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+'app/user/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{	
			console.log(response);
			var data = JSON.parse(response);
			var responsevalue=data.res;
			if(responsevalue.trim() == '1') {
				Unloading();
				toastr.success("USER ADDED SUCCESSFULLY", "SUCCESS");	
				window.location=root_domain+'user_list';
			}
			else if(responsevalue.trim() == '2') {
				toastr.success("CUSTOMER ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-example-modal-lg").modal("hide");
				$('#cust_id').append('<option value='+data.cust_id+'>'+data.company_name+'</option>');				
				$("#cust_id").trigger('change')
				$('#cust_id').select2("val",data.cust_id);
				$('#user_add').trigger('reset');
				Unloading();
			}
			else if(responsevalue.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO");
				$("#bs-example-modal-lg").modal("hide");
				$('#user_add').trigger('reset');
				Unloading();				
			}
			else if(responsevalue.trim() == 'update') {	
				toastr.success("USER UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain+'user_list';	
			}
			$('#user_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_user(id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/user/',
			data: { mode:"delete", eid:id },
			success: function(response)
			{
				console.log(response);
				if(response.trim() == "1") {
					toastr.success("USER DELETE SUCCESSFULLY", "SUCCESS");
					datatable.fnReloadAjax();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function load_state(control,val1) 
{
	$.ajax({
		type: "POST",
		url: root_domain+'app/user/',
		data: { mode : "load_state"},
		success: function(data){
			//console.log(data);
			$('#'+control).html(data);
			$("#"+control).select2("val",val1);
		}
	});
}

function load_city(parentid,control,val1)
{	
	$.ajax({
		type: "POST",
		url: root_domain+'app/user/',
		data: { mode : "load_city",  id : parentid},
		success: function(responce){
			//console.log(responce);
			$('#'+control).html(responce);
			$("#"+control).select2("val",val1);
		}
	});
	
}
