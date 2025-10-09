//var datatable;
$(document).ready(function() {
	load_datatable();
});

function reload_data()
{
	//datatable.fnReloadAjax();
	load_datatable();
}	
function load_datatable()
{	
	var release_status=$('input[name=release_status]:Checked').val();
	var date=$('#rep_date').val();
	var branch_id=$('#branch_id').val();
	
	datatable = $("#dynamic-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide" : true,
		'aoColumnDefs': [{
	        'bSortable': false,
	        'aTargets': ['nosort']
    	}],
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+inventory_domain+'app/store_receive_pending_list_new/',
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
	}
	// function change_stock_status (unitname,grn_trn_id,total_qty,unit_id,godwn,product_name,grn_no,grn_date,product_id,batch_id,batch_no,reprocess_qc) 
	function change_stock_status (total_qty,batch_id,reprocess_qc,to_godown_id) 
	{
		// alert(batch_id)
		$("#total_qty").val(total_qty);
		$("#reprocess_qc").val(reprocess_qc);
		$("#tqty").html(total_qty);
		$("#batch_id").val(batch_id);
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/store_receive_pending_list_new/',
			data: {
				mode : "get_store_details",
				batch_id : batch_id,
				to_godown_id:to_godown_id
			},
			success: function(response)
			{

					var data = jQuery.parseJSON(response);

					if(data.batch_unit == data.conv_unit){
						$("#tqty2").html(data.accept_qty2);
						$(".unitname2").html(data.unit_name);
						$(".unitname").html(data.conv_unit_name);
					}else{
						$("#tqty2").html(data.accept_qty2);
						$(".unitname2").html(data.conv_unit_name);
						$(".unitname").html(data.unit_name);
					}

				$("#grn_trn_id").val(data.grn_trn_id);
				
				$("#godwn").html(data.gd_name);
				$("#proname").html(data.product_name);

				if((data.grn_trn_id === null && data.grn_id == '0') || (data.grn_trn_id == '0' && data.grn_id == '0')){
					console.log('if');
					$("#grnno").html(data.qc_no);
					$("#grndate").html(data.qc_date);
				}else{
					console.log('else');
					$("#grnno").html(data.grn_no);
					$("#grndate").html(data.grn_date);	
				}
				
				$("#unit_id").val(data.batch_unit);
				$("#product_id").val(data.product_id);
				$("#batch_no").val(data.batch_no);
				$("#so_details").empty().html(data.so_details);
				$('#store_accept_modal').modal('show');

				if(to_godown_id > 0){
					load_child_godown_list(to_godown_id);
				}

				show_data();
				get_store_accept_no();

				$("#godown_id").select2({
					width : "100%"
				});

				$("#godown_id").select2("val", data.selected_godown);

			}
		});
		

		/*var form_data = {
			mode:"show_accept_entry",
			grn_trn_id:grn_trn_id
		};	
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/store_receive_pending_list_new/',
			data: form_data,
			success: function(response)
			{
				$('#store_accept_modal').modal('show');
				$("#mod_per_div_sec").html(response);
			}
		});	*/
	}
function add_field()
{

	 if ($("#godown_id").val() == ""){
		toastr.warning("Select Godown", "ERROR");
		return false;
	}

	 if ($("#aqty").val() == ""){
		toastr.warning("Enter Quantity", "ERROR");
		return false;
	}
	var store_accept_id=$("#store_accept_id").val();
	var grn_trn_id=$("#grn_trn_id").val();
	var godown_id=$("#godown_id").val();
	var qty=$("#aqty").val();
	var unit_id=$("#unit_id").val();
	var edit_id=$("#edit_id").val();
	var product_id=$("#product_id").val();
	var batch_id=$("#batch_id").val();

	var used_qty=parseFloat($("#used_qty").val()).toFixed(5);
	var total_qty=parseFloat($("#total_qty").val()).toFixed(5);
	if(isNaN(used_qty)){ used_qty=0; }
	if(isNaN(total_qty)){ total_qty=0; }
	var tusedqty=parseFloat(used_qty)+parseFloat(qty);
	if(total_qty<tusedqty){
		toastr.warning("Qty Issue", "WARNING");
		return false;
	}

	Loading();

	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/store_receive_pending_list_new/',
		data: { 
			mode : "fieldadd",
			store_accept_id:store_accept_id,
			grn_trn_id:grn_trn_id,
			godown_id:godown_id,
			qty:qty,
			unit_id:unit_id,
			edit_id:edit_id,
			product_id:product_id,
			batch_id: batch_id
			
		},
		success: function(response)
		{

			$("#edit_id").val("");
			$("#godown_id").val("");
			$("#aqty").val("");
			$('#addrow').val('Add');
			Unloading();
			show_data();
		}
	});
}

function show_data()
{
	//Loading();
	var eid=$('#store_accept_id').val();
	var grn_trn_id=$('#grn_trn_id').val();
	var batch_id=$("#batch_id").val();

Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/store_receive_pending_list_new/',
		data: { mode : "load_tempoutward",eid:eid,grn_trn_id:grn_trn_id,batch_id: batch_id},
		success: function(data){
				//console.log(data);
				$('#sale_productdata').html(data);		
				Unloading();				
			}		

		});
	}
function edit_data(id)
	{
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/store_receive_pending_list_new/',
			data: { mode : "preedit",  id : id },
			success: function(response)
			{
					console.log(response)
					var data = jQuery.parseJSON(response);
					$("#godown_id").val(data.godown_id);
					$("#aqty").val(data.qty);
					$("#edit_id").val(id)
					$('#addrow').val('Update');
			}
		});
	}

	function delete_data(id)
	{
		var r= confirm(" Are you want to delete ?");

		if(r) {
			$.ajax({
				type: "POST",
				url: root_domain+inventory_domain+'app/store_receive_pending_list_new/',
				data: { mode : "delete_data",  eid : id },
				success: function(response)
				{
					//console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						show_data()
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}

	}

function save_store_accept()
{
    $('#save').prop('disabled', true);

	var store_accept_id=$("#store_accept_id").val();
	var grn_trn_id=$("#grn_trn_id").val();
	var store_accept_no=$("#store_accept_no").val();
	var store_accept_date=$("#store_accept_date").val();
	var batch_id = $("#batch_id").val();
	var batch_no = $("#batch_no").val();
	var reprocess_qc = $("#reprocess_qc").val();
	var remark=$("#remark").val();
	
	var used_qty=parseFloat($("#used_qty").val()).toFixed(5);
	var total_qty=parseFloat($("#total_qty").val()).toFixed(5);
	if(isNaN(used_qty)){ used_qty=0; }
	if(isNaN(total_qty)){ total_qty=0; }

	if(total_qty!=used_qty){
		
		toastr.warning("Qty Issue", "WARNING");
		$('#save').prop('disabled', false);
		return false;
	}

	   if($("#remark").attr("data-required") == "yes"){
		if(remark == ""){
				toastr.warning("Please enter remark", "WARNING"); 
				$('#save').prop('disabled', false);
			return false;
		}
	}
	
	Loading();

	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/store_receive_pending_list_new/',
		data: { 
			mode : "save_store_accept",
			store_accept_id:store_accept_id,
			grn_trn_id:grn_trn_id,
			store_accept_no:store_accept_no,
			store_accept_date:store_accept_date,
			remark:remark,
			batch_id:batch_id,
			batch_no:batch_no,
			reprocess_qc:reprocess_qc
			
		},
		success: function(response)
		{

			load_datatable();
			$('#store_accept_modal').modal('hide');
			$('#save').prop('disabled', false);
			Unloading();
		}
	});
}


function get_store_accept_no(){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/store_receive_pending_list_new/',
		data: { 
			mode : "get_store_accept_no",
		},
		success: function(response)
		{
			
			$('#store_accept_no').val(response);
			Unloading();
		}
	});
}

function load_child_godown_list(to_godown_id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/store_receive_pending_list_new/',
		data: { 
			mode : "load_child_godown_list",
			godown_id : to_godown_id
		},
		success: function(response)
		{
			$('#godown_id').empty().html(response);
			$('#godown_id').select2({
				width : "100%"
			});
			Unloading();
		}
	});
}


function checkAll()
{
	var checkboxes = document.getElementsByTagName('input'), val = null;    
	for (var i = 0; i < checkboxes.length; i++)
	{
		if (checkboxes[i].type == 'checkbox')
		{
			if (val === null) val = checkboxes[i].checked;
			checkboxes[i].checked = val;
		}
	}
}

function store_approve_all(){

	var checbox_checked_len = $('input:checkbox:checked').length;
	if($('#checkAll').is(':checked')){
		checbox_checked_len = checbox_checked_len - 1;
	}

	if(checbox_checked_len < 1)
	{
		toastr.warning("Please Select at least 1 checkbox ", "ERROR")
		return false;
	}else if(checbox_checked_len > 10)
	{
		toastr.warning("YOU CAN'T SELECT MORE THAN 10 STORE APPROVAL", "ERROR")
		return false;
	}
	else
	{
		Loading();
		var i = 1;
		var batch_id = "";
		$("input:checkbox").each(function () {
			if ($(this).is(":checked")) {
				console.log($(this).attr("value"))
				if(typeof $(this).attr("value") != 'undefined')
				{
					if(i == 1){
						batch_id = $(this).attr("value");
					}else{
						batch_id += "," + $(this).attr("value");
					}
					
					if(i == checbox_checked_len){
						$("#all_batch_id").val(batch_id);
						console.log(batch_id)
						setTimeout(function(){
							$("#store_approve_all_add").submit();
						},1500)
					}else{
						i++;	
					}
				}
			}
		});  
	}
}
