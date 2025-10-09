$(document).ready(function() {
	
load_salesman_datatable();

$("#salesman_add").on('submit',function(e) {

	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#salesman_add").valid()) {
		return false;
	}
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");	 
	
	var form_data=new FormData(this);
	var form_type=$('#form_type').val();
	//form_data.append('file', $('#emp_profile_img').prop('files')[0]);
	
	$.ajax({
		cache:false,
		url: root_domain+'app/salesman_mst/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//alert(response);
			//console.log(response);			
			// var obj=jQuery.parseJSON(response);
			// response=obj.res;
			if(response.trim() == '1') {
				toastr.success("SALESMAN ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				
				$("#"+form_type).addClass("ledger_forms");
				window.location=root_domain+'salesman_list';
			}
			else if(response.trim() == '2') {
				toastr.success("SALESMAN ADDED SUCCESSFULLY", "SUCCESS");
				$('#salesman_add').trigger('reset');
				Unloading();
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response.trim() == '-1') {
				toastr.warning("SALESMAN WITH SAME NAME ALREADY EXIST", "ERROR")
				Unloading();
			}
			else if(response.trim() == '3') {
				toastr.success("SALESMAN UPDATED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+'salesman_list';
				Unloading();
			}
			
			$('#salesman_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function load_salesman_datatable(){
//var branch_id = $('#branch_id').val();

datatable = $("#salesman-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"oLanguage": {
				"sLengthMenu": "_MENU_",
				"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
				"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+'app/salesman_mst/',
		"fnServerParams": function ( aoData ) {
			aoData.push( 
				{ "name": "mode", "value": "fetch" }					
			);
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

});