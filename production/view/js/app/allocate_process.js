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
	Loading()
	$.ajax({
			type: "POST",
		url: root_domain+production_domain+'app/allocate_process/',
		data: { mode : "fetch",process_id:process_id,process_type:process_type,product_id:product_id,branch_id:branch_id },
		success: function(response)
		{
			//alert(response);
			$('#dynamic-table').html(response);
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
function show_material_list()
{
	
	var branch_id=$('#branch_id').val();//allocate ID
	var eid=$('#eid').val();//allocate ID
	var max_start_qty=parseFloat($('#machine_no').val());//allocate ID
	var pending_qty=$('#pr_p_qty1').val();//allocate ID
	var max_available_qty=parseFloat($('#max_available_qty').val());//allocate ID
	
	
	if(max_start_qty <= max_available_qty){
	//if(2 <= 1){
		Loading();
		$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/allocate_process/',
		data: { mode : 'show_material_list_new',eid:eid,max_start_qty:max_start_qty,pending_qty:pending_qty},
		success: function(data){
				$('#sub_row_mat').html(data);
				$("#sp_btn").show();
				Unloading();
			}		
			
		});
	}else{
		toastr.warning("Not Enter More then Available Qty", "ERROR");
		$("#sp_btn").hide();
	}
}
function show_material_list_old(){
	
	var product_id = $("#product_id_hid").val(); 
	var pro_type = $("#product_type_hid").val(); 
	var pqty = $("#product_qty_hid").val(); 
	var eid = $("#eid").val(); 
	var machine_no = $("#machine_no").val(); 
	//alert(machine_no);
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
		"sAjaxSource": root_domain+production_domain+'app/allocate_process/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "show_material_list" },{ "name": "pid", "value": product_id },{ "name": "pro_type", "value": pro_type },{ "name": "pqty", "value": pqty },{ "name": "eid", "value": eid },{ "name": "machine_no", "value": machine_no } );
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
function show_material_list1(){
	
	var product_id = $("#product_id_hid").val(); 
	var pro_type = $("#product_type_hid").val(); 
	var pqty = $("#product_qty_hid").val(); 
	var eid = $("#eid").val(); 
	var machine_no = $("#machine_no").val(); 
	//alert(machine_no);
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
		"sAjaxSource": root_domain+production_domain+'app/allocate_process/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "show_material_list" },{ "name": "pid", "value": product_id },{ "name": "pro_type", "value": pro_type },{ "name": "pqty", "value": pqty },{ "name": "eid", "value": eid },{ "name": "machine_no", "value": machine_no } );
		},
		"fnDrawCallback": function( oSettings ) {
			
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
		"sAjaxSource": root_domain+production_domain+'app/allocate_process/',
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
	
	var pevi=$("#previous_process_id").val();
	if(pevi==="0"){
		var data= new Array();
		$("input[name='machine_make[]']").each(function(){
			data.push($(this).val());
			//alert($(this).val());
		});
		
		var pr_p_qty1=$('#pr_p_qty1').val();
		var machine_no=Math.min.apply(Math, data);
		
		if(machine_no<=0)
		{
			$('#error_start_msg').show();
			$('#sp_btn').attr('disabled',true);
			$('#sp_btn').hide();
			$('#machine_no').val(0);
			$('#product_qty_hid').val(0);
		}
		else
		{
			$('#error_start_msg').hide();
			$('#sp_btn').attr('disabled',false);
			$('#sp_btn').show();
			if(machine_no>pr_p_qty1)
			{
				$('#machine_no').val(pr_p_qty1);
				$('#product_qty_hid').val(pr_p_qty1);
			$("#machine_no").attr({"max" : pr_p_qty1});
			}
			else
			{
				$('#machine_no').val(machine_no);
				$('#product_qty_hid').val(machine_no);
				$("#machine_no").attr({"max" : machine_no});
			}
		
		}
	}else{
		var pr_p_qty1=$('#pr_p_qty1').val();
		
		$('#machine_no').val(pr_p_qty1);
		$('#product_qty_hid').val(pr_p_qty1);
			$('#error_start_msg').hide();
			$('#sp_btn').attr('disabled',false);
			$('#sp_btn').show();
	}
	show_material_list1();
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
			"sProcessing": "<img src='"+root_domain+production_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 50, 100, -1], [10, 50, 100,"All"]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+production_domain+'app/allocate_process/',
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
		url: root_domain+production_domain+'app/allocate_process/',
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
	//alert("hi");
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/allocate_process/',
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
		url: root_domain+production_domain+'app/allocate_process/',
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
	/* if (!$("#start_allocate_add").valid()) {
		return false;
	} */
	var product_qty_hid=parseFloat($("#product_qty_hid").val());
	var machine_no=parseFloat($("#machine_no").val());
	//alert(machine_no);
	//alert(product_qty_hid);
	if(machine_no>product_qty_hid){
		toastr.warning("Check Process Qty", "ERROR")
		return false;
	}
	//return false;
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	
	var process_id_hid = $('#process_id_hid').val();
	var process_type_hid = $('#process_type_hid').val();
	var redirect_page = $('#redirect_page').val();
	
	//alert(process_id_hid);
	//alert(process_type_hid);
	$.ajax({
		cache:false,
		url: root_domain+production_domain+'app/allocate_process/',
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
				
				toastr.success("PROCESS STARTED SUCCESSFULLY", "SUCCESS");
				
				if(redirect_page=='working_process_detail_list' || redirect_page==''){
					window.location=root_domain+production_domain+'working_process_detail_list/'+process_id_hid+'/'+process_type_hid;
				}else{
					window.location=root_domain+production_domain+redirect_page;
				}
				
				
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
					window.location=root_domain+production_domain+'invoicereceipt/'+arr.eid+'/'+arr.printstatus;
				}
				else
				{
					window.location=root_domain+production_domain+'invoice_list';
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

function get_warehose_deduction(pro_id,pro_name,req_qty){
	//alert(pro_id);
	if(pro_id) {
		$("#get_warehose_deduction_modal").modal("show");
		$("#product_name").html(pro_name);
		$("#product_id").val(pro_id);
		$("#gd_req_qty").val(req_qty);
		show_godown_product_detail(pro_id,req_qty); 
	}
}

function show_godown_product_detail(pro_id,req_qty)
{
	//alert(req_qty);
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/allocate_process/',
		data: { mode : "show_godown_product_detail",pro_id:pro_id,req_qty:req_qty },
		success: function(response)
		{
			$('#godown_table').html(response);
			Unloading();
			
		}
	}); 
}

function get_series_no(){
	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/allocate_process/',
		data: { mode : "get_series_no" },
		success: function(resp){
			//console.log(resp);
			$('#invoicetype_id').val(resp);	
			load_jobcard_no(resp);
		}		
	});	
}

function load_jobcard_no(id)
{
	//alert(id);
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/allocate_process/',
		data: { mode : "load_invoiceno", typeid : id},
		success: function(data){
			//console.log(data);
			var no = jQuery.parseJSON(data);
			$('#pr_job_no').val(no.invoiceno);
		}
	});
}

function get_series_no_jobwork(){
	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/allocate_process/',
		data: { mode : "get_series_no_jobwork" },
		success: function(resp){
			//console.log(resp);
			$('#invoicetype_id_jobwork').val(resp);	
			load_jobcard_no_jobwork(resp);
		}		
	});	
}

function load_jobcard_no_jobwork(id)
{
	//alert(id);
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/allocate_process/',
		data: { mode : "load_invoiceno_jobwork", typeid : id},
		success: function(data){
			//console.log(data);
			var no = jQuery.parseJSON(data);
			$('#pr_jobwork_no').val(no.invoiceno);
		}
	});
}