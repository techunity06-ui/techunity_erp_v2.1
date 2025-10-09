//var datatable;
$(document).ready(function() {
		load_datatable(); 
// validate vendor add form on keyup and submit
$("#hrms_leave_block_add").validate({
	rules: {
		leave_block_list_name: {
			required: true			
		},
	},
	messages: {
		leave_block_list_name: {
			required: "Enter Block List Name"
		},
	}
}); 
});
$("#hrms_leave_block_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#hrms_leave_block_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/hrms_leave_block/',
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
				toastr.success("HRMS LEAVE BLOCK ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + hrms_domain + 'hrms_leave_block_list';
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(arr.msg == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			else if(arr.msg == 'update')
			{	
				toastr.success("HRMS LEAVE BLOCK UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain + hrms_domain + 'hrms_leave_block_list';
			}
			$('#sales_order_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_hrms_leave_block(id) 
{
	var r= confirm(" Are you want to delete ?");
		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + hrms_domain + 'app/hrms_leave_block/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response)
					if(response.trim() == "1") {
						toastr.success("HRMS LEAVE BLOCK DELETE SUCCESSFULLY", "SUCCESS");
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
function load_leaveblockdetail(val) {
	if(val!=0)
	{
		$('#addproduct').hide();
	}
	else
	{
		$('#addproduct').show();
	}
	var cust_id = $('#cust_id').val();
	if(cust_id==''){
		toastr.warning("Please Select Customer First","ERROR");
		$('#cust_id').select2('focus');
		return false;
	}
	$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_leave_block/',
			data: { mode : "load_productdata",eid :val, cust_id:cust_id },
			success: function(response)
			{
				console.log(response);
				var obj =jQuery.parseJSON(response)
				$('#product_hsn_code').val(obj.product_hsn);
				$('#formulaid').val(obj.fom_id);
				$('#product_rate').val(obj.product_sale_rate);
				$('#unit_id').select2("val",obj.product_base_unit);
			}
		});
}

function add_field()
{
	if($("#block_date").val()==="")
	{		
		toastr.warning("Select Block Date", "ERROR")
		return false;
	}
	if($("#block_reason").val()==="")
	{		
		toastr.warning("Enter Block Reason", "ERROR")
		return false;
	}
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_leave_block/',
			data: { mode : "fieldadd", eid : $("#eid").val(), edit_id:$("#edit_id").val(),block_date:$("#block_date").val(),block_reason:$("#block_reason").val() },
			success: function(response)
			{
				$("#block_date").val("")
				$("#block_reason").val("")
				$('#addproduct').show();
				$('#addrow').val('Add');
				Unloading();
				show_data();
			}
		});
}

function add_block_field(){
	if($("#employee_id").val() === null)
	{		
		toastr.warning("Select Employee Name", "ERROR")
		return false;
	}
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_leave_block/',
			data: { mode : "fieldblockadd", eid : $("#eid").val(), edit_id:$("#edit_id").val(), employee_id:$("#employee_id").val()},
			success: function(response)
			{
				$("#employee_id").select2('val','');
				$('#addproduct').show();
				$('#addblockrow').val('Add');
				Unloading();
				show_block_data();
			}
	});
}

function reload_data()
{
	//datatable.fnReloadAjax();
	load_datatable();
}	
function load_datatable()
{
	var data=$('input[name=report]:Checked').val();
	var date=$('#rep_date').val();
	var type=$('#type_id').val();
	datatable = $("#dynamic-table").dataTable({
			"bStateSave": true,
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
			"aLengthMenu": [[-1, 10, 20, 30, 50], ["All", 10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain + hrms_domain + 'app/hrms_leave_block/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },{ "name": "date", "value": date } );
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}

function show_data()
{
	var lb_id=$("#eid").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_leave_block/',
		data: { mode : "load_tempoutward",lb_id:lb_id},
		success: function(data){
			$('#hrms_leaveblockdata').html(data);				
			Unloading();
		}		
	});
}
function show_block_data()
{
	var lb_id=$("#eid").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_leave_block/',
		data: { mode : "load_blocktempoutward",lb_id:lb_id},
		success: function(data){
			$('#hrms_leaveblockallowuserdata').html(data);				
			Unloading();
		}		
	});
}
function edit_data(id)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_leave_block/',
			data: { mode : "preedit",  id : id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$("#block_date").val(data.block_date)
				$("#block_reason").val(data.block_reason)
				$("#edit_id").val(id);
				$('#addrow').val('Update');
				Unloading();
			}
		});
}
function edit_block_data(id)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_leave_block/',
			data: { mode : "preblockedit",  id : id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$("#employee_id").select2("val",data.employee_id)
				$("#edit_id").val(id)
				$('#addblockrow').val('Update');
				Unloading();
			}
		});
}
function delete_data(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_leave_block/',
			data: { mode : "delete_data",  eid : id},
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function delete_block_data(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_leave_block/',
			data: { mode : "delete_block_data",  eid : id},
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_block_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function delete_attch(so_attch_id) {
	var conf = confirm("Are you sure want to Delete ?");
	if(conf){
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/sales_order/',
			data: { mode : "delete_attch", so_attch_id:so_attch_id },
			success: function(response){
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("ATTACHMENT DELETED SUCCESSFULLY", "SUCCESS");
					location.reload();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();
			}
		}); 
	}
}
function changeStatus(id,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_leave_block/',
		data: { mode : "change_status", eid : id,p_status:p_status },
		success: function(response)
		{
			toastr.success("HRMS LEAVE BLOCK STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			load_datatable(); 
		}
	});
	Unloading();
}