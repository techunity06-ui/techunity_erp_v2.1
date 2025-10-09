//var datatable;
$(document).ready(function() {
	load_datatable();
	
});

function load_datatable()
{
	var process_id=$('#process_id').val();
	var process_type=$('#process_type').val();
	var product_id=$('#product_id').val();
	var branch_id=$('#branch_id').val();
	
	
	//alert(process_id);
	
	$.ajax({
			type: "POST",
			url: root_domain+'app/allocate_process/',
			data: { mode : "fetch_working",process_id:process_id,process_type:process_type,product_id:product_id,branch_id:branch_id },
			success: function(response)
			{
				//alert(response);
				$('#dynamic_table_working').html(response);
				Unloading();
				
			}
	}); 
}

function add_allocate_process(aid,pid,pname,pqty,qc_id,process_id,ref_type,pr_process_type,pro_type)
{
	//alert(st_time);
	//alert(pname);
	
	$('#table_show_allocate_process').modal("show");
	$('#process_id_name').html(pname);
	
	$('#pr_p_qty').val(pqty);
	
	$('#product_id').val(pid);
	$('#qc_id').val(qc_id);
	$('#p_id').val(process_id);
	$('#ref_type').val(ref_type);
	$('#pr_process_type').val(pr_process_type);
	$('#aid').val(aid);
	show_material_list_allocated(pid,pro_type,pqty);
	//$('#pr_st_time').val(st_time);
	//show_process_details(pid);
			
}


function start_process(pid,pname,pqty,product_id,pro_type)
{
	//alert(pid);
	
	$('#table_start_allocate_process').modal("show");
	$('#process_id_name1').html(pname);
	$('#pr_p_qty1').val(pqty);
	$('#p_id1').val(pid);
	$('#pro_id1').val(product_id);
	show_material_list(product_id,pro_type,pqty);
	//alert(pname)
			
}

//Start Process modal 

function show_material_list(){
	
	var product_id = $("#product_id_hid").val(); 
	var pro_type = $("#product_type_hid").val(); 
	var pqty = $("#product_qty_hid").val(); 
	
	$("#material_details").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bDestroy": true,
		"bProcessing": true,
		"bServerSide" : true,
		"bPaginate": false,
		"bFilter" : false,
		"bInfo" : false,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 50, 100, -1], [10, 50, 100,"All"]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+'app/allocate_process/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "show_material_list" },{ "name": "pid", "value": product_id },{ "name": "pro_type", "value": pro_type },{ "name": "pqty", "value": pqty } );
		},
		"fnDrawCallback": function( oSettings ) {
			get_machine_no();
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
	
}

/*function show_material_list(product_id,pro_type,pqty){
	
	//var cust_id=$("#alloc_cust_id").val(); 
	//alert(pqty);
	$("#material_details").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bDestroy": true,
		"bProcessing": true,
		"bServerSide" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 50, 100, -1], [10, 50, 100,"All"]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+'app/allocate_process/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "show_material_list" },{ "name": "pid", "value": product_id },{ "name": "pro_type", "value": pro_type },{ "name": "pqty", "value": pqty } );
		},
		"fnDrawCallback": function( oSettings ) {
			get_machine_no();
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
	
	
}
*/

function get_machine_no()
{
	var data= new Array();
	$("input[name='machine_make[]']").each(function(){
		data.push($(this).val());
		//alert($(this).val());
	});
	
	
	var machine_no=Math.min.apply(Math, data);
	$('#machine_no').val(machine_no);
	
	if(machine_no==0)
	{
		$('#error_start_msg').show();
		$('#sp_btn').attr('disabled',true);
		$('#sp_btn').hide();
	}
	else
	{
		$('#error_start_msg').hide();
		$('#sp_btn').attr('disabled',false);
		$('#sp_btn').show();
	}
	//alert(data);
}

//End Process Modal List


//Allocate Process Modal List

function show_material_list_allocated(product_id,pro_type,pqty){
	
	//var cust_id=$("#alloc_cust_id").val(); 
	//alert(pqty);
	$("#material_details1").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bDestroy": true,
		"bProcessing": true,
		"bServerSide" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 50, 100, -1], [10, 50, 100,"All"]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+'app/allocate_process/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "show_material_list_allocate" },{ "name": "pid", "value": product_id },{ "name": "pro_type", "value": pro_type },{ "name": "pqty", "value": pqty } );
		},
		"fnDrawCallback": function( oSettings ) {
			get_machine_no_allocated();
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
	
	
}


function get_machine_no_allocated()
{
	var data= new Array();
	$("input[name='machine_make_allocate[]']").each(function(){
		data.push($(this).val());
		//alert($(this).val());
	});
	
	
	var machine_no=Math.min.apply(Math, data);
	$('#pr_available_qty').val(machine_no);
	
	if(machine_no==0)
	{
		$('#error_start_msg_allocated').show();
		$('#sp_btn').attr('disabled',true);
	}
	else
	{
		$('#error_start_msg_allocated').hide();
		$('#sp_btn').attr('disabled',false);
	}
	//alert(data);
}

function add_start_process()
{
	var pr_st_time1=$('#pr_st_time1').val();
	var p_id=$('#p_id1').val();
	
	$.ajax({
		
		type: "POST",
		url: root_domain+'app/allocate_process/',
		data: { mode : "add_start_process",process_id:p_id },
		success: function(response)
		{
			if(response=='0'){
				toastr.success("Process Started Successfully", "SUCCESS");
				load_datatable();
				$('#table_start_allocate_process').modal("hide");
				Unloading(); 
			}
			
		}
	}); 
	
}

function show_process_details(pid)
{
	$.ajax({
		type: "POST",
		url: root_domain+'app/allocate_process/',
		data: { mode : "show_process_details",pid:pid },
		success: function(response)
		{
			//alert(response);
			$('#dynamic-table').html(response);
			Unloading();
			
		}
	}); 
	//alert(pid);
}

function start_process_allocation()
{
	var pr_st_time=$('#pr_st_time').val();
	var pr_end_time=$('#pr_end_time').val();
	var pr_pr_qty=$('#pr_pr_qty').val();
	var product_id=$('#product_id').val();
	var qc_id=$('#qc_id').val();
	var p_id=$('#p_id').val();
	var ref_type=$('#ref_type').val();
	var pr_process_type=$('#pr_process_type').val();
	var aid=$('#aid').val();
	var pr_remain_qty=$('#pr_remain_qty').val();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/allocate_process/',
		data: { mode : "start_process_allocation",pr_st_time:pr_st_time,pr_end_time:pr_end_time,pr_pr_qty:pr_pr_qty,product_id:product_id,qc_id:qc_id,process_id:p_id,ref_type:ref_type,pr_process_type:pr_process_type,aid:aid,pr_remain_qty:pr_remain_qty },
		success: function(response)
		{
			//alert(response);
			toastr.success("QUANTITY ADDED SUCCESSFULLY", "SUCCESS");
			$('#pr_pr_qty').val('');
			//alert(response);
			$('#table_show_allocate_process').modal("hide");
			load_datatable();
			//$('#dynamic-table').html(response);
			Unloading();
			
		}
	}); 
}

$("#start_allocate_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#start_allocate_add").valid()) {
		return false;
	}
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	
	var process_id_hid = $('#process_id_hid').val();
	var process_type_hid = $('#process_type_hid').val();
	
	$.ajax({
		cache:false,
		url: root_domain+'app/allocate_process/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//alert(response);
			console.log(response);	
			//var arr = jQuery.parseJSON(response);			
			if(response == '1') {
				Unloading();
				
				toastr.success("PROCESS STARTED SUCCESSFULLY", "SUCCESS");
				
				//window.location=root_domain+'process_detail_list/'+process_id_hid+'/'+process_type_hid;
				window.location=root_domain+'working_process_detail_list/'+process_id_hid+'/'+process_type_hid;
				
			}
			else if(response == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			else if(response == 'update')
			{	
				toastr.success("BILL UPDATED SUCCESSFULLY", "SUCCESS");		
			
				Unloading();
				if ($("#save_print").val() == '1')
				{	
					window.location=root_domain+'invoicereceipt/'+arr.eid+'/'+arr.printstatus;
				}
				else
				{
					window.location=root_domain+'invoice_list';
				}		
			}
			$('#start_allocate_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function get_final_qty()
{
	var pr_pr_qty=Number($('#pr_pr_qty').val());
	var pr_p_qty=Number($('#pr_p_qty').val());
	
	var remain_qty=pr_p_qty-pr_pr_qty;
	
	$('#pr_remain_qty').val(remain_qty);
	
	if(remain_qty<0)
	{
		$('#pr_button').attr('disabled',true);
	}
	else
	{
		$('#pr_button').attr('disabled',false);
	}
}

function machine_configuration(product_id = false, process_id = false){
	if(product_id) {
		$.ajax({
			type: "POST",
			url: root_domain+'app/allocate_process/',
			data: { mode : "product_load",product_id:product_id,process_id: process_id },
			success: function(response)
			{
				var res = JSON.parse(response);
				$("#table_show_machine_configuration").modal("show");
				$("#model_html").html(res.html_resp);
			}
		}); 
	}
} 
function get_related_process(parentid,control,val1)
{	
	$.ajax({
		type: "POST",
		url: root_domain+'app/allocate_process/',
		data: { mode : "load_process",  id : parentid},
		success: function(responce){
			$("#process_data_select").html(responce);
		}
	});
}
$("body").on('submit', '#machine_configuration_add' ,function(e) {
	if($("#product_id_model").val()=="")
	{		
		toastr.warning("Select Product Name", "ERROR")
		return false;
	}
	if($("#process_id_model").val()=="")
	{		
		toastr.warning("Select Process Name", "ERROR")
		return false;
	}
	if($("#short_count").val()=="")
	{		
		toastr.warning("Enter Short Count", "ERROR")
		return false;
	}
	var ext = $('#upload_machine_file').val().split('.').pop().toLowerCase();
    if($('#upload_machine_file').val() != '' && $.inArray(ext, ['gif','png','jpg','jpeg']) === -1) {
        toastr.warning("Only image type jpg/png/jpeg/gif is allowed", "ERROR");
        $("#upload_machine_file").focus();
        return false;
    }

	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#machine_configuration_add").valid()) {
		return false;
	}		
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$("#submit_btn").attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	
	$.ajax({
		cache:false,
		url: root_domain + 'app/machine_configuration/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {				
				toastr.success("MACHINE CONFIGURATION ADDED SUCCESSFULLY", "SUCCESS");
				$("#table_show_machine_configuration").modal("hide");
			}
			else if(response.trim() == '2') {
				toastr.success("MACHINE CONFIGURATION UPDATED SUCCESSFULLY", "SUCCESS");
				$("#table_show_machine_configuration").modal("hide");
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			else if(response.trim() == '-1'){
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			Unloading();
			$('#machine_configuration_add').trigger('reset');
			$("#submit_btn").removeAttr("disabled");
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});