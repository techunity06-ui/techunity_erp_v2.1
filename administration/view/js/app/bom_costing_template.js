$(document).ready(function() {
	load_datatable();
	load_tax_category_data();
	
	$("#tax_category_add").validate({
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

function load_datatable(){
	//var branch_id = $('#branch_id').val();

	datatable = $("#tax-category-table").dataTable({
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
			"sAjaxSource": root_domain+administration_domain+'app/bom_costing_template/',
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

$("#tax_category_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	
	if (!$("#tax_category_add").valid()) {
		return false;
	}
	//alert("fds");

	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/bom_costing_template/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//alert(response);
			//console.log(response);	
			//var arr = jQuery.parseJSON(response);			
			if(response.trim() == '1') {
				Unloading();
				toastr.success("ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+administration_domain+'bom_costing_template';				
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}else if(response == '3') {
				Unloading();
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");	
				window.location=root_domain+administration_domain+'bom_costing_template';
			}else if(response.trim() == '-1') {
				Unloading();
				toastr.warning("Already Exist", "ERROR");	
			}
			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});


});


function delete_tax_data(id)
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			//alert(id);
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/bom_costing_template/',
				data: { mode : "delete_tax_data",  eid : id},
				success: function(response)
				{
					//alert(response);
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						Unloading();
						load_datatable();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
					}
			});	
		}
	
}


function delete_tax_details_data(id)
{
	//alert(id);
	var r= confirm(" Are you want to delete ?");

		if(r) {
			//alert(id);
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/bom_costing_template/',
				data: { mode : "delete_tax_details_data",  eid : id},
				success: function(response)
				{
					//alert(response);
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						Unloading();
						load_tax_category_data();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
					}
			});	
		}
	
}

function add_tax_percentage()
{
	var type_name = $('#type_name').val();
	var type = $('#type').val();
	var per = $('#per').val();
	var amount = $('#amount').val();
	var edit_id = $('#edit_id').val();
	var eid = $('#eid').val();

	if(type_name == 0){		
		toastr.warning("Select Type Name", "ERROR");
		$("#tax_id").select2('focus');
		return false;
	}
	else if(type == ''){		
		toastr.warning("Enter Type", "ERROR");
		return false;
	}
	else if(per == '' && amount==''){		
		toastr.warning("Enter Percentage/Amount Value", "ERROR");
		return false;
	}
	
	$.ajax({
		
		type:'POST',
		url:root_domain+administration_domain+'app/bom_costing_template/',
		data:{mode:'add_tax_percentage',type_name:type_name,type:type,eid:eid,per:per,amount:amount,edit_id:edit_id,eid:eid},
		success:function(result)
		{
			//alert(result);
			if(result == "1") {
				toastr.success("DATA INSERTED SUCCESSFULLY", "SUCCESS");
				
				Unloading();
			}
			else if(result == "0"){
				toastr.warning("SOMETHING WRONG", "WARNING");
				Unloading();
			}else if(result == "2"){
				toastr.warning("DATA Edit SUCCESSFULLY", "WARNING");
				Unloading();
			}else{
				Unloading();
			}
			load_tax_category_data();	

			$('#type').select2("val","");
			$('#type_name').val('');
			$('#per').val('');
			$('#amount').val('');
			$('#edit_id').val('');
			
		}
	})
	
}
function load_tax_category_data()
{
	var eid = $('#eid').val();
	//alert(eid);
	$.ajax({
		
		type:'POST',
		url:root_domain+administration_domain+'app/bom_costing_template/',
		data:{mode:'load_tax_category_data',eid:eid},
		success:function(result)
		{
			console.log(result);
			$('#add_tax_list').html(result);
		}
	})

}