//var datatable;
$(document).ready(function() {
	load_po_datatable();
	show_data();
	
	get_drawing_history();
	get_revision_data();
	
// validate vendor add form on keyup and submit
 $("#purchaseorder_add").validate({
	rules: {
		drawing_number: {
			required: true			
		},
		drawing_title: {
			required: true			
		},
		/*vender_id: {
			required: true			
		},*/
		/*drawing_size: {
			required: true			
		},
		drawing_scale: {
			required: true			
		}*/
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
		/*drawing_size: {
			required: "Enter Drawing Size"
		},
		drawing_scale: {
			required: "Enter Drawing Scale"
		},*/
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
		url: root_domain+'app/drawing/',
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

$("#revision_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	
	form.submitted = true;	
	//Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	
	$.ajax({
		cache:false,
		url: root_domain+'app/drawing/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				$('#revision_number').val(arr.revision_number_ref);
				$('#revision_id').val(arr.revision_id_ref);
				get_revision_data();
				get_drawing_history();
				toastr.success("REVISION ADDED SUCCESSFULLY", "SUCCESS");
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
			}
			$('#revision_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function get_revision_data(){
	var eid = $('#eid').val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/drawing/',
		data: { mode : "get_revision_data",  eid : eid },
		success: function(response)
		{
			$('#revision_data_div').html(response);
		}
	});	
}

function delete_po(id) 
{
	var r= confirm(" Are you sure want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/drawing/',
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
			"sAjaxSource": root_domain+'app/drawing/',
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
				url: root_domain+'app/drawing/',
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



function change_revision_status(id, status) 
{
	var r= confirm(" Are you want to Change Revision Status ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/drawing/',
				data: { mode : "change_revision_status", eid:id, status:status },
				success: function(response)
				{
					
					var resp = JSON.parse(response);
					var response = resp.msg;
					if(response.trim() == "1") {
						toastr.success("REVISION CHANGED SUCCESSFULLY", "SUCCESS");
						get_revision_data();
						get_drawing_history();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}

function change_drawing_status(id, status) 
{
	var r= confirm(" Are you want to Change Drawing Status ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/drawing/',
				data: { mode : "change_drawing_status", eid:id, status:status },
				success: function(response)
				{
					Unloading();
					var resp = JSON.parse(response);
					var response = resp.msg;
					if(response.trim() == "1") {
						toastr.success("DRAWING CHANGED SUCCESSFULLY", "SUCCESS");
						window.location=root_domain+'drawing_list';
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}


function view_revision_image(id)
{
	//alert(id);
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+'app/drawing/',
		data: { mode : "view_revision_image", id : id },
		success: function(response)
		{
			$('#revision_image_list').html(response);
			$("#ModalEditAccount").modal("show");
			
			Unloading();
		}
	});	
}
function view_drawing_image(id)
{
	//alert(id);
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+'app/drawing/',
		data: { mode : "view_drawing_image", id : id },
		success: function(response)
		{
			$('#drawing_image_list').html(response);
			$("#ModalEditAccount").modal("show");
			
			Unloading();
		}
	});	
}

function view_drawing_image(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+'app/drawing/',
		data: { mode : "view_drawing_image", id : id },
		success: function(response)
		{
			$('#revision_image_list').html(response);
			$("#ModalEditAccount").modal("show");
			
			Unloading();
		}
	});	
}

function show_data()
{
	Loading();
	var eid=$('#eid').val();
	$.ajax({
	type: "POST",
	url: root_domain+'app/drawing/',
	data: { mode : "load_tempoutward",eid:eid},
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
	url: root_domain+'app/drawing/',
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




function get_drawing_history(){
	var revision_id = $('#revision_id').val();
	var mode = "get_dr_history";
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+'app/drawing/',
	data: { mode : mode, revision_id : revision_id},
	success: function(data){
			$('#dr_history').html(data);				
			Unloading();
		}		
	});
}


function delete_data_image(id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/drawing/',
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

$("#drawing_number").blur(function(){
  var drawing_number = $(this).val();
  var data = drawing_validate(drawing_number);
  if(data=='1'){
		toastr.warning("DRAWING NUMBER ALREADY EXISTS.", "WARNING");
		$("#drawing_number").focus();
		return false;
	}
});


function drawing_validate(drawing_number){
	if(drawing_number){
	var ret ='';
	var eid = $('#eid').val();
	Loading();
	
			$.ajax({
			type: "POST",
			url: root_domain+'app/drawing/',
			async:false,
			data: { mode : 'check_drawing_number', drawing_number : drawing_number, eid : eid},
			success: function(data){
				Unloading();
				ret =  data;
			}		
		});
		return ret ;
	}
	
}