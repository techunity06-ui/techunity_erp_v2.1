//var datatable;
$(document).ready(function() {
	//load_datatable();
	//show_material_list();
	//get_amount();
	load_grn_no();

});

function get_machine_no_qty(qty)
{
	var pr_p_qty1=Number($('#pr_p_qty1').val());
	if(Number(qty)>pr_p_qty1)
	{
		$('#error_qty').html('Quantity not more than pending');
		$('#sp_btn1').prop('disabled',true);
	}
	else
	{
		$('#error_qty').html('');
		$('#sp_btn1').prop('disabled',false);
	}
	//alert(qty);
	//alert(pr_p_qty1);
}


 $("#end_allocate_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	//alert("fadu");
	if (!$("#end_allocate_add").valid()) {
		return false;
	}
	var qq=parseFloat($("#pr_p_qty1").val());
	var pp=parseFloat($("#machine_no1").val());
	if(isNaN(qq)){qq=0;}
	if(isNaN(pp)){pp=0;}
	//alert(qq);
	//alert(pp);
	if(qq<pp){
		toastr.warning("Qty Not Metch", "ERROR")
		return false;
	}
	//alert("hi");
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	//alert("fadu");
	var process_id_hid = $('#process_id_hid').val();
	var process_type_hid = $('#process_type_hid').val();
	var redirect_page = $('#redirect_page').val();
	
	$.ajax({
		cache:false,
		url: root_domain+'app/allocate_process_end/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//alert(response);
			//var arr = jQuery.parseJSON(response);			
			if(response == '1') {
				Unloading();
				
				toastr.success("PROCESS STARTED SUCCESSFULLY", "SUCCESS");
				//alert('fdj');
				//window.location=root_domain+'process_detail_list/'+process_id_hid+'/'+process_type_hid;
				
				if(redirect_page=='working_process_detail_list' || redirect_page==''){
					window.location=root_domain+'working_process_detail_list/'+process_id_hid+'/'+process_type_hid;
				}else{
					window.location=root_domain+redirect_page;
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
 
function get_child_data()
{
	var product_id = $("#product_id_hid").val(); 
	var pro_type = $("#product_type_hid").val(); 
	var pqty = $("#machine_no1").val(); 
	
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
		"sAjaxSource": root_domain+'app/allocate_reprocess/',
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
	

	//alert(product_id_hid);
	
}


function get_machine_no()
{
	var data= new Array();
	$("input[name='machine_make[]']").each(function(){
		data.push($(this).val());
		//alert($(this).val());
	});
	
	
	var machine_no=Math.min.apply(Math, data);
	//$('#machine_no').val(machine_no);
	
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
function load_grn_no() {
	//alert("hi");
	$.ajax({
		type: "POST",
		url: root_domain+'app/grn/',
		data: { mode : "load_grn_no" },
		success: function(data){
			//console.log(data);
			var no = jQuery.parseJSON(data);
			$('#grn_no').val(no.invoiceno);
		}
	});
}
function show_material_list()
{
	
	var eid=$('#eid').val();//allocate ID
	var max_start_qty=$('#machine_no1').val();//allocate ID
	var pending_qty=$('#pr_p_qty1').val();//allocate ID
	var pre_alloc_id=$('#pre_alloc_id').val();
	//alert(pre_alloc_id);
	var branch_id=$('#branch_id').val();
	var pid=$('#eid').val();
	//var max_available_qty=$('#max_available_qty').val();//allocate ID
	//if(max_start_qty<=max_available_qty){
		Loading();
		$.ajax({
		type: "POST",
		url: root_domain+'app/allocate_process_end/',
		data: { mode : 'show_material_list_new',eid:eid,max_start_qty:max_start_qty,pending_qty:pending_qty,branch_id:branch_id,pre_alloc_id:pre_alloc_id,pid:pid},
		success: function(data){
				$('#sub_row_mat').html(data);
				$("#sp_btn").show();
				Unloading();
			}		
			
		});
	/* }else{
		toastr.warning("Not Enter More then Available Qty", "ERROR");
		$("#sp_btn").hide();
	} */
}
function open_scrap_entry(){
	
	var product_id=$("#product_id_hid").val();
	var process_id=$("#process_id_hid").val();
	var allo_id=$("#eid").val();
	var branch_id=$("#branch_id").val();
	var qty=$("#machine_no1").val();
	//alert(product_id);
	//alert(process_id);
	if(qty>0){
		$.ajax({
			type: "POST",
			url: root_domain+'app/allocate_process_end/',
			data: { mode : 'open_scrap_entry',product_id:product_id,process_id:process_id,allo_id:allo_id,branch_id:branch_id,qty:qty},
			success: function(data){
					$('#preview_scrap_entry_modal').modal('show');
					$('#mod_per_div_sec1').html(data);
					Unloading();
				}		
			});
	}else{
		toastr.info("Enter Prosuct Qty", "INFO");
	}
}
function scrap_save1(){
	var sallo_id=$("#sallo_id").val();
	var product_scrap_id=$("#product_scrap_id").val();
	var sproduct=$("#sproduct").val();
	var sprocess=$("#sprocess").val();
	var scrap_received_qty=$("#scrap_received_qty").val();
	var sbranch_id=$("#sbranch_id").val();
	
	if(parseFloat($("#scrap_received_qty").val()) > parseFloat($("#scrap_received_qty").attr('max'))) {		
		toastr.warning("Not Enter Max Scrap Entry", "ERROR");
		$("#scrap_received_qty").focus();
		return false;
	}
	
	if(parseFloat($("#scrap_received_qty").val()) < parseFloat($("#scrap_received_qty").attr('min'))){		
		toastr.warning("Not Enter Min Scrap Entry", "ERROR");
		$("#scrap_received_qty").focus();
		return false;
	}
	
	if(product_scrap_id!=""){
		$.ajax({
			type: "POST",
			url: root_domain+'app/allocate_process_end/',
			data: { mode : 'scrap_save',sproduct:sproduct,sprocess:sprocess,sallo_id:sallo_id,product_scrap_id:product_scrap_id,scrap_received_qty:scrap_received_qty,sbranch_id:sbranch_id},
			success: function(data){
					//$('#mod_per_div_sec1').html(data);
					Unloading();
				}		
			});
		$('#preview_scrap_entry_modal').modal('hide');
	}
} 
function scrap_rate_change(){
	//alert("hi");
	var product_scrap_id=$("#product_scrap_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/allocate_process_end/',
		data: { mode : "scrap_rate_change",product_scrap_id:product_scrap_id },
		success: function(data){
			//console.log(data);
			var no = jQuery.parseJSON(data);
			$('#scrap_rate').val(no.product_sale_rate);
		}
	});
}
