//var datatable;
$(document).ready(function() {

	show_data();
	
});
$("#so_allocation_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#so_allocation_add").valid()) {
		return false;
	}
	
	
	 var so_stock=(document.getElementsByName('so_stock[]'));
	var so_working_stock=(document.getElementsByName('so_working_stock[]'));
	var cnt=so_stock.length;
	var so_stock1=0
	for(var i=0;i<cnt;i++)
	{
		if(so_stock[i].value > 0){
			so_stock1 += parseFloat(so_stock[i].value);
			//alert(so_stock1);
		}
	} 
	
	var cnt1=so_working_stock.length;
	var so_wostock1=0;
	for(var p=0;p<cnt1;p++)
	{
		if(so_working_stock[p].value > 0){
			so_wostock1 += parseFloat(so_working_stock[p].value);
			//alert(so_wostock1);
		}
	} 
	if(isNaN(parseFloat(so_stock1))){
		so_stock1=0;
	}
	if(isNaN(parseFloat(so_wostock1))){
		so_wostock1=0;
	}
	var total_add=parseFloat(so_stock1)+parseFloat(so_wostock1);
	var pending_qty=$("#ref_pending_qty").val();
	if(isNaN(parseFloat(pending_qty))){
		pending_qty=0;
	}
	
	if(total_add<=0){
		toastr.warning("Please Add Stock", "ERROR")
		return false;
	}
	
	if(total_add>pending_qty){
		toastr.warning("Please Check Stock", "ERROR")
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+'app/get_sales_order_details/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				Unloading();
				toastr.success("ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+'get_sales_order_details';
				
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
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");		
			
				Unloading();
				window.location=root_domain+'get_sales_order_details';
					
			}
			$('#so_allocation_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

 function show_data() {
	var st_type = $('#st_type').val();
	var branch_id = $('#branch_id').val();
	
	//alert(st_type);
	datatable = $("#dynamic-table1").dataTable({
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
		"sAjaxSource": root_domain+'app/get_sales_order_details/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "generate_report_min_new" },{ "name": "st_type", "value": st_type },{ "name": "branch_id", "value": branch_id });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
	
	
} 
function open_approv_quo1(sales_order_no,product_name,sotrn_id,product_id,pending_qty){
	$('#preview_so_allocate_modal').modal('show');
	$('#apprv_ref_no').html(sales_order_no);
	$('#pname').html(product_name);
	$('#pqty').html(pending_qty);
	$('#ref_sales_order_trn_id').val(sotrn_id);
	$('#ref_product_id').val(product_id); 
	$('#ref_pending_qty').val(pending_qty); 
	//alert("fdsa");
	load_entry_stock();
}
function load_entry_stock() {
	var ref_sales_order_trn_id = $('#ref_sales_order_trn_id').val();
	//alert(ref_sales_order_trn_id);
	//var ref_product_id = $('#ref_product_id').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'/app/get_sales_order_details/',
		data: { mode : "load_entry_stock", ref_sales_order_trn_id:ref_sales_order_trn_id },
		success: function(resp){
			console.log(resp);
			$('#mod_per_div_sec1').html(resp);
			Unloading();
		}		 
	}); 
}

function product_request(product_id,sales_ordertrn_id,qty)
{
	var r= confirm("Do You Want To Set Standard Version?");
					if(r) {
					
						Loading();
						$.ajax({
						type: "POST",
						url: root_domain+'/app/get_sales_order_details/',
						data: { mode : "set_version", product_id:product_id,sales_ordertrn_id:sales_ordertrn_id,qty:qty},
						success: function(resp){
						if(resp == '1') {
						Unloading();
						toastr.success("STANDARD VERSION ASSIGNED SUCCESSFULLY", "SUCCESS");
						
						}else {
						
						Unloading();
						}
						window.location=root_domain+'sorequesproduct/'+product_id+'/'+sales_ordertrn_id;
						}		 
						}); 
				}else{	
			
			
	Loading();
		$.ajax({
				type: "POST",
				url: root_domain+'app/get_sales_order_details/',
				data: { mode : "ger_version_by_product",product_id:product_id,sales_ordertrn_id:sales_ordertrn_id,qty:qty},
				success: function(response)
				{
					$("#show_product_from").html(response);
					$("#prodcuct_version").modal("show");							
					Unloading();
				}
		});
	
}

}

function product_custom_versions(product_id,sales_ordertrn_id,qty)
{
	var version_id = $("#add_bom_version_id").val();
				$.ajax({
						type: "POST",
						url: root_domain+'/app/get_sales_order_details/',
						data: { mode : "set_custom_version", product_id:product_id,sales_ordertrn_id:sales_ordertrn_id,version_id:version_id,qty:qty},
						success: function(resp){
						
						if(resp == '1') {
						Unloading();
						toastr.success("VERSION ASSIGNED SUCCESSFULLY", "SUCCESS");
						window.location=root_domain+'sorequesproduct/'+product_id+'/'+sales_ordertrn_id;
						}else {
						toastr.warning("NOT ASSIGEND VERSION IN BOM !!!", "ERROR");
						window.location=root_domain+'sorequesproduct/'+product_id+'/'+sales_ordertrn_id;
						Unloading();
						}
						
						}		 
						}); 
}

function open_stock_allocation_so(sales_order_trn_id){
	alert(sales_order_trn_id);
}
						
						
							
