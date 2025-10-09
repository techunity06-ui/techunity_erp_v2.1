$(document).ready(function() {
	load_datatable();
	load_tax_category_data();
	
	$("#tax_category_add").validate({
		rules: {
			tax_cat_name: {
				required: true
			},
			tax_gst: {
				required: true
			},
			
		},
		messages: {
			tax_cat_name: {
				required: "Enter Category Name"			
			},
			tax_gst: {
				required: "Enter Tax Percentage"
			},
		}
	}); 

	var mode = $('#mode').val();
	
	if(mode=='Edit')
	{
		get_other_tax($('#tax_gst').val());
	}

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
			"sAjaxSource": root_domain+administration_domain+'app/tax_category/',
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
	

	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/tax_category/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//alert(response);
			//console.log(response);	
			//var arr = jQuery.parseJSON(response);			
			if(response == '1') {
				Unloading();
				toastr.success("Tax Category ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+administration_domain+'tax_category';				
			}
			else if(response == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}else if(response == '3') {
				Unloading();
				toastr.success("Tax Category ADDED SUCCESSFULLY", "SUCCESS");	
				window.location=root_domain+administration_domain+'tax_category';
			}else if(response == '-1') {
				Unloading();
				toastr.warning("Tax Category Already Exist", "ERROR");	
			}
			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});


});

function add_field()
{	
	if(!$("#common_mst_id").val()){		
		toastr.warning("Select Payee Category", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	else if(!$("#tds_thresold_limit").val() || parseFloat($("#tds_thresold_limit").val())=='0'){		
		toastr.warning("Enter Threshold Limit", "ERROR");
		return false;
	}
	else if(!$("#tds_with_pan").val()){		
		toastr.warning("Enter TDS(With PAN)", "ERROR");
		return false;
	}
	else if(!$("#tds_without_pan").val()){		
		toastr.warning("Enter TDS(Without PAN)", "ERROR");
		return false;
	}
	else if(!$("#tds_surcharge").val()){		
		toastr.warning("Enter Surcharge", "ERROR");
		return false;
	}

	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/tds_tax_category/',
			data: { mode : "fieldadd",edit_id:$("#edit_id").val(),common_mst_id:$("#common_mst_id").val(),tds_thresold_limit:$("#tds_thresold_limit").val(),tds_with_pan:$("#tds_with_pan").val(),
			tds_without_pan:$("#tds_without_pan").val(),tds_surcharge:$("#tds_surcharge").val(),tds_cat_id:$("#eid").val() },
			success: function(response)
			{
				//alert(response);
				//console.log(response);
				$("#tds_without_pan").val("");
				$("#tds_surcharge").val("");

                $("#tds_with_pan").val("");
				$("#tds_thresold_limit").val("");
				$("#common_mst_id").val("");
				$("#common_mst_id").select2("val","");
				
				$('#addrow').val('Add');
				Unloading();
				show_data();
			}
		});
}

function edit_data(id,table,whereid)
{
	//alert(id);
	Loading();
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/tds_tax_category/',
				data: { mode : "preedit",  id : id ,table:table,whereid:whereid},
				success: function(response)
				{					
					var data = jQuery.parseJSON(response);
					
					$("#common_mst_id").select2("val",data.common_mst_id);

					$("#tds_thresold_limit").val(data.tds_thresold_limit);					
					$("#tds_with_pan").val(data.tds_with_pan);
					$("#tds_without_pan").val(data.tds_without_pan);
					$("#tds_surcharge").val(data.tds_surcharge);
					$("#edit_id").val(id);
					
					$('#addrow').val('Update');
					Unloading();
				}
			});
}

function delete_data(id)
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			//alert(id);
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/tds_tax_category/',
				data: { mode : "delete_data",  eid : id,invoice_id:$("#eid").val() },
				success: function(response)
				{
					
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						Unloading();
						show_data();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
					}
			});	
		}
	
}


function delete_tax_data(id)
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			//alert(id);
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/tax_category/',
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
				url: root_domain+administration_domain+'app/tax_category/',
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

function get_other_tax(tax)
{
	//alert(tax);
	
	var tax_cgst = Number(tax)/2;
	var tax_sgst = Number(tax)/2;
	var tax_igst = Number(tax);
	
	$('#tax_cgst').val(tax_cgst);
	$('#tax_sgst').val(tax_sgst);
	$('#tax_igst').val(tax_igst);
	
}

function add_tax_percentage()
{
	var tax_id = $('#tax_id').val();
	var tax_per = $('#tax_per').val();
	var eid = $('#eid').val();

	if(tax_id == 0){		
		toastr.warning("Select Tax First", "ERROR");
		$("#tax_id").select2('focus');
		return false;
	}
	else if(tax_per == ''){		
		toastr.warning("Enter Tax Percentage Value", "ERROR");
		return false;
	}
	
	$.ajax({
		
		type:'POST',
		url:root_domain+administration_domain+'app/tax_category/',
		data:{mode:'add_tax_percentage',tax_id:tax_id,tax_per:tax_per,eid:eid},
		success:function(result)
		{
			//alert(result);
			if(result == "1") {
				toastr.success("DATA INSERTED SUCCESSFULLY", "SUCCESS");
				$('#tax_id').select2("val","");
				$('#tax_per').val('');
				Unloading();
				load_tax_category_data();
			}
			else if(result == "0"){
				toastr.warning("SOMETHING WRONG", "WARNING");
				Unloading();
			}else if(result == "-1"){
				toastr.warning("Already Exist", "WARNING");
				Unloading();
			}	
			
		}
	})
	
}

function load_tax_category_data()
{
	var eid = $('#eid').val();
	// alert(eid);
	$.ajax({
		
		type:'POST',
		url:root_domain+administration_domain+'app/tax_category/',
		data:{mode:'load_tax_category_data',eid:eid},
		success:function(result)
		{
			console.log(result);
			$('#add_tax_list').html(result);
		}
	})

}