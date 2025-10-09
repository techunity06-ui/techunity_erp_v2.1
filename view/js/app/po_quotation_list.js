//var datatable;
$(document).ready(function() {
	load_po_req_datatable();
	show_data();
});
function reload_data()
{
	load_po_req_datatable();
}	
$("#approve_indent_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#approve_indent_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+'app/po_quotation_list/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);	
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("PURCHASE ORDER ADDED SUCCESSFULLY", "SUCCESS");
				//window.location=root_domain+'po_quotation_list';
				window.location=root_domain+arr.redirect;
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
				toastr.success("PO UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				//window.location=root_domain+'po_quotation_list';
				window.location=root_domain+arr.redirect;
				
			}
			$('#purchaseorder_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function load_po_req_datatable()
{
	var po_type_status=$('input[name=po_type_status]:Checked').val();
	var date=$('#rep_date').val();
	var branch_id = $('#branch_id').val();
	
	//alert(po_type_status);
	datatable = $("#po-req-table").dataTable({
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
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/po_quotation_list/',
			"fnServerParams": function ( aoData ) {
				aoData.push( 
					{ "name": "mode", "value": "fetch" },
					{ "name": "po_type_status", "value": po_type_status },
					{ "name": "date", "value": date },
					{ "name": "branch_id", "value": branch_id }
				);
			},
			"fnDrawCallback": function( oSettings ) {
				//alert(oSettings);
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}
function show_data()
{
	var eid = $('#approve_indent_id').val();
	var pro_type= $("input[name='proType']:checked"). val()
	Loading()
	$.ajax({
	type: "POST",
	url: root_domain+'app/po_quotation_list/',
	data: { mode : "load_tempoutward", eid:eid,pro_type:pro_type },
	success: function(data){
				
				//alert(data);
				//console.log(data);
				 $('#sale_productdata').html(data);				
				 Unloading();
		}		
		
	});
	
}
function cancel_po_status(eid){
	Loading()
	$.ajax({
	type: "POST",
	url: root_domain+'app/po_quotation_list/',
	data: { mode : "app_quo", eid:eid },
	success: function(data){
			//$('#sale_productdata').html(data);
			show_data();			
		}		
	});
}

function disapprove_po_status(eid){
	Loading()
	$.ajax({
	type: "POST",
	url: root_domain+'app/po_quotation_list/',
	data: { mode : "disapprove_quo", eid:eid },
	success: function(data){
			//$('#sale_productdata').html(data);
			show_data();			
		}		
	});
}

function delete_qo(id,aid) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/po_quotation_list/',
				data: { mode : "vendor_quotation_delete",  id : id, aid : aid },
				success: function(response)
				{
					
					var arr = jQuery.parseJSON(response);
					if(arr.msg == "1") {
						toastr.success("PO DELETE SUCCESSFULLY", "SUCCESS");
						Unloading();
						window.location=root_domain+arr.redirect;
					}
					else if(arr.msg == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}

}