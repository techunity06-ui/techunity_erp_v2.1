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
			"sAjaxSource": root_domain+'app/company/',
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

});

$(".btn_close").click(function() {
    $("label.error").hide();
});

$("#company_name").on('submit',function(e) {
	
	// to get editor Data	
	for (instance in CKEDITOR.instances) 
	{
    	CKEDITOR.instances[instance].updateElement();
	}	
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#company_name").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");	
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+'app/company/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(responsevalue)
		{	
			console.log(responsevalue);
			responsevalue=responsevalue.trim();
			if(responsevalue  == '1') {
				Unloading();
				toastr.success("COMPANY ADDED SUCCESSFULLY", "SUCCESS");	
				$('#cust_add').trigger('reset');
				$('#company_name').trigger('reset');	
				window.location=root_domain+'company_list';
			}
			 
			else if(responsevalue  == '-1')
			{
				toastr.info("COMPANY ALREADY EXISTS", "INFO")
				Unloading();				
			}
			else if(responsevalue  == 'update')
			{	
				toastr.success("COMPANY UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain+'company_list';
			}
			else if(responsevalue  == '0')
			{	
				toastr.success("NO DATA UPDATED", "SUCCESS");		
				Unloading();
				window.location=root_domain+'company_list';
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_data(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/company/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response);
					if(response.trim() == "1") {
						toastr.success("COMPANY DELETE SUCCESSFULLY", "SUCCESS");
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
 