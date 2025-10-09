//var datatable;
$(document).ready(function() {
	load_po_datatable();
	//Search Product Wise
$("#fil_product_search").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#fil_product_tbl tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
}); 
	
// validate vendor add form on keyup and submit
 $("#purchaseorder_add").validate({
	rules: {
		drawing_number: {
			required: true			
		},
		drawing_title: {
			required: true			
		},
		drawing_size: {
			required: true			
		},
		drawing_scale: {
			required: true			
		}
	},
	messages: {
		drawing_number: {
			required: "Enter Drawing Number"
		},
		drawing_title: {
			required: "Enter Drawing Title"
		},
		/*vender_id: {
			required: "Select Vendor"
		},*/
		drawing_size: {
			required: "Enter Drawing Size"
		},
		drawing_scale: {
			required: "Enter Drawing Scale"
		},
		purchaseorder_date:{
			required : "Enter P.O date"
		}
	}
}); 
});
$("#purchaseorder_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#purchaseorder_add").valid()) {
		return false;
	}
	var dranumb= drawing_validate($('#drawing_number').val());
	if(dranumb=='1'){
		toastr.warning("DRAWING NUMBER ALREADY EXISTS.", "WARNING");
		$("#drawing_number").focus();
		return false;
	}
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+'app/process_opening_stock/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("DRAWING ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+arr.back;
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if(arr.msg == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO");
				Unloading();
			}
			else if(arr.msg== 'update')
			{	
				toastr.success("DRAWING UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location=root_domain+'drawing_list';
				
			}
			$('#purchaseorder_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_po(id) 
{
	var r= confirm(" Are you sure want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/process_opening_stock/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response)
					if(response.trim() == "1") {
						toastr.success("PO DELETE SUCCESSFULLY", "SUCCESS");
						load_po_datatable();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}

function reload_data()
{
	//datatable.fnReloadAjax();
	load_po_datatable();
}		
function load_po_datatable()
{
	var po_type_status=$('input[name=po_type_status]:Checked').val();
	var date=$('#rep_date').val();
	var branch_id = $('#branch_id').val();
	
	datatable = $("#dynamic-table").dataTable({
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
			"aLengthMenu": [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/process_opening_stock/',
			"fnServerParams": function ( aoData ) {
				aoData.push( 
					{ "name": "mode", "value": "fetch" },
					{ "name": "date", "value": date },
					{"name": "branch_id", "value": branch_id }
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


function delete_data(id,table,whereid)
{
	var r= confirm(" Are you sure want to delete ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/process_opening_stock/',
				data: { mode : "delete_data",  eid : id ,table:table,whereid:whereid,purchaseorder_id:$("#eid").val() },
				success: function(response)
				{
					console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						show_data()
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}
function load_product_process()
{
	Loading();
	var product_id=$('#product_id').val();
	$.ajax({
	type: "POST",
	url: root_domain+'app/process_opening_stock/',
	data: { mode : "load_product_process",product_id:product_id},
	success: function(data){
			$('#sale_productdata').html(data);				
			Unloading();
		}		
		
	});
	
}

function get_so_no(cust_id)
{
	var eid=$('#eid').val();
	Loading()
	$.ajax({
	type: "POST",
	url: root_domain+'app/process_opening_stock/',
	data: { mode : "get_so_no",cust_id:cust_id,eid:eid },
	success: function(data){
			var arr = jQuery.parseJSON(data);
			$('#sales_order_id').empty().append(arr.sales_order_id);
			$("#sales_order_id").select2({
	         	width: '100%'
	        });	
			Unloading();
		}		
		
	});
}
function delete_data_image(id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/process_opening_stock/',
		data: { mode : 'delete_image', id : id},
		success: function(data){
			if(data=='1'){
				toastr.success("IMAGE DELETE SUCCESSFULLY", "SUCCESS");
				$('.imgdiv').hide();	
			}
			Unloading();			
		}		
			
	});
 }
