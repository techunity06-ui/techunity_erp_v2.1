$(document).ready(function() {
load_datatable();
show_data();


});

function load_datatable(){
	//var branch_id = $('#branch_id').val();

	datatable = $("#tds-tax-category-table").dataTable({
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
			"sAjaxSource": root_domain+administration_domain+'app/tds_tax_category/',
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

function show_data()
{
	var eid = $('#eid').val();
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+administration_domain+'app/tds_tax_category/',
	data: { mode : "load_tempoutward", eid:eid },
	success: function(data){
				
            //alert(data);
            //console.log(data);
            $('#sale_productdata').html(data);				
            //get_amount();
            Unloading();
		}		
		
	});
	
}

$("#tds_tax_category_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#tds_tax_category_add").valid()) {
		return false;
	}
	else if(!$("#tds_cat_name").val())
	{
		toastr.warning("Please insert Tds Category", "ERROR")
		return false;
	}
	else if(!$("#tds_section").val())
	{
		toastr.warning("Please insert Section code", "ERROR")
		return false;
	}
	else if(!$("#tds_date").val())
	{
		toastr.warning("SELECT Tds Date", "ERROR")
		return false;
	}
	/*else if($("#effective_ledger_id").val() == 0)
	{
		toastr.warning("Please Select Effective ledger", "ERROR")
		return false;
	}*/
	else if($("#field_cnt").val() == 0){
		toastr.warning("Please Add Atleast one Payee category", "ERROR")
		return false;
	}

	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/tds_tax_category/',
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
				toastr.success("TDS TAX CATEGORY ADDED SUCCESSFULLY", "SUCCESS");	
				show_data();			
			}
			else if(response == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}else if(response == '3') {
				Unloading();
				toastr.success("TDS TAX CATEGORY ADDED SUCCESSFULLY", "SUCCESS");	
				show_data();
				window.location=root_domain+administration_domain+'tds_tax_category_list';
			}else if(response == '-1') {
				toastr.warning("Duplicate entry, Already Exist", "ERROR")
				Unloading();
			}
			$("#effective_ledger_id").select2("val","0");
			$('#tds_tax_category_add').trigger('reset');	
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
				if(response.trim() == '1') {
					toastr.success("TDS TAX CATEGORY DETAIL ADDED SUCCESSFULLY", "SUCCESS");
					$("#tds_without_pan").val("");
					$("#tds_surcharge").val("");

	                $("#tds_with_pan").val("");
					$("#tds_thresold_limit").val("");
					$("#common_mst_id").val("");
					$("#common_mst_id").select2("val","");
					$("#edit_id").val("");
					$('#addrow').val('Add');
					Unloading();
					show_data();
				}else if(response.trim() == '-1') {
					toastr.info("ALREADY EXISTS", "INFO");
					Unloading();
				}else if(response.trim() == '2') {
					toastr.success("TDS TAX CATEGORY DETAIL UPDATED SUCCESSFULLY", "SUCCESS");
					$("#tds_without_pan").val("");
					$("#tds_surcharge").val("");

	                $("#tds_with_pan").val("");
					$("#tds_thresold_limit").val("");
					$("#common_mst_id").val("");
					$("#common_mst_id").select2("val","");
					$("#edit_id").val("");
					$('#addrow').val('Add');
					Unloading();
					show_data();
				}
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


function delete_tds_data(id)
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			//alert(id);
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/tds_tax_category/',
				data: { mode : "delete_tds_data",  eid : id},
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

