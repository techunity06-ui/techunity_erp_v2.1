//var datatable;
$(document).ready(function() {
	load_stock_transfer_datatable();
	show_data();
	add_product_batch_wise();
//	load_purhcase_order_data($('#purchaseorder_id').val());
	
	// validate vendor add form on keyup and submit
	$("#stock_transfer_add").validate({
		rules: {
			transfer_no:{
				required: true			
			},
			transfer_date: {
				required: true			
			},
			to_godown_id: {
				required: true
			},
			from_godown_id: {
				required: true
			}
		},
		messages: {
			transfer_no:{
				required: "Enter Stock Transfer No."			
			},
			transfer_date: {
				required: "Enter Stock Transfer Date"
			},
			to_godown_id: {
				required: "Choose To Godown"
			},
			from_godown_id: {
				required: "Choose From Godown"
			}
		}
	}); 
});

$("#from_godown_id").on('change',function(){
	$("#godown_id").val($(this).val());
});

$("#stock_transfer_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#stock_transfer_add").valid()) {
		return false;
	}
	$("#from_godown_id").prop('disabled', false);
	var from_godown_id = $("#from_godown_id").val()
	var to_godown_id = $("#to_godown_id").val()
	
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled","disabled");		
	$('#save').attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+inventory_domain+'app/stock_transfer/',
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
				toastr.success("Transfer SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+inventory_domain+"stock_transfer_list"; 
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(arr.msg == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			else if(arr.msg== 'update') {
				toastr.success("Transfer UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location=root_domain+inventory_domain+'stock_transfer_list';
			}
			$('#stock_transfer_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function add_field()
{
	var from_godown_id = $("#from_godown_id").val()
	var to_godown_id = $("#to_godown_id").val()

	var stock_qty = parseFloat($("#stock_qty").val());
	var transfer_qty = parseFloat($("#transfer_qty").val());
	if(!$("#godown_id").val()){
		toastr.warning("Select Godown", "ERROR");
		$("#godown_id").select2('focus');
		return false;
	}
	else if(!$("#product_id").val()){
		toastr.warning("Select Product", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	else if(!$("#unit_id").val()){
		toastr.warning("Choose Unit", "ERROR");
		$("#unit_id").focus();
		return false;
	}
	else if(!$("#transfer_qty").val()){		
		toastr.warning("Enter Qty", "ERROR");
		$("#transfer_qty").focus();
		return false;
	}
	
	else if(transfer_qty > stock_qty){
		toastr.warning("TRANSFER QTY NOT BE GREATER THAN STOCK QTY .", "ERROR")
		return false;	
	}
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/stock_transfer/',
		data: { mode : "fieldadd",edit_id:$("#edit_id").val(),eid :$("#eid").val(),product_id:$("#product_id").val(),transfer_qty:$("#transfer_qty").val(),unit_type : $('#unit_id').find(':selected').attr('data-unit_type'),unit_id : $('#unit_id').val(),godown_id:$("#godown_id").val() },
		success: function(response)
		{
			$("#from_godown_id").prop('disabled', true);
			$("#to_godown_id").prop('disabled', true);
			//console.log(response);
			//$("#product_id").select2("val","");
			//$("#transfer_work_order_id").select2("val","");
			$("#product_id").select2("val","");
			$("#godown_id").select2("val","");
			$("#godown_id").select2('focus');
			//$("#work_order_id").val("");
			$("#unit_id").val("");
			$("#transfer_qty").val("");
			$("#transfer_qty").attr("placeholder","");
			$("#edit_id").val('');
			$("#stock_qty").val('');
			$('#addrow').val('Add');
			$("#isbatchwise").val('');
			$("#edit_id").val('');
			add_product_batch_wise();
			$('#bs-batch_wise_stock-modal').modal('hide');
			Unloading();
			show_data();
		}
	});
}	

function show_data() {
	var eid = $('#eid').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/stock_transfer/',
		data: { mode : "load_stock_transfer_trn_data", eid:eid },
		success: function(data){
			//console.log(data);
			$('#sale_productdata').html(data);		
			
			Unloading();
		}		 
	}); 
}


function load_stock_transfer_datatable()
{
	var data=$('input[name=report]:Checked').val();
	var date=$('#rep_date').val();
	
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
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+inventory_domain+'app/stock_transfer/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "report", "value": data },{ "name": "date", "value": date });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function edit_stock_transfer_data(stock_transfer_trn_id)
{ 
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/stock_transfer/',
		data: { mode:"preedit", stock_transfer_trn_id:stock_transfer_trn_id },
		success: function(response)
		{
			//console.log(response)
			
			var data = jQuery.parseJSON(response);
			/*$('#purchaseorder_id').html(data.po_html_resp);
			$("#purchaseorder_id").select2("val",data.purchaseorder_id);*/
			$('#product_id').html(data.pro_html_resp);
			$("#product_id").select2("val",data.product_id);
			$("#unitid").select2("val",data.unit_id);
			$("#product_des").val(data.description);
			$("#product_hsn_code").val(data.product_hsn_code);
			$("#pro_entry_date").datepicker("setDate", data.pro_entry_date);
			if(data.pro_mfg_date){
				$("#pro_mfg_date").datepicker("setDate", data.pro_mfg_date);
			}
			else{
				$("#pro_mfg_date").val("");
			}
			
			if(data.pro_exp_date){
				$("#pro_exp_date").datepicker("setDate", data.pro_exp_date);
			}
			else{
				$("#pro_exp_date").val("");
			}
			
			$("#transfer_qty").val(data.product_qty);
			$("#edit_id").val(data.stock_transfer_trn_id);
			$('#addrow').val('Update');
			Unloading();
		}
	});
}
function delete_stock_transfer_data(stock_transfer_trn_id)
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/stock_transfer/',
			data: { mode:"delete_data", id:stock_transfer_trn_id},
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
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


function load_product_unit(product_id="",edit_unit=""){
	if(product_id){

	}else{
		var product_id=$("#product_id").val();
	}
	if(edit_unit){

	}else{
		var edit_unit=$("#rate_unit_id").val();
	}
	//alert(product_id);
	if(product_id)//tax calculation on total 
	{

		$.ajax({
			type: "POST",
			async: false,
			url: root_domain+ inventory_domain +'app/stock_transfer/',
			data: { mode : "load_product_unit", product_id : product_id},
			success: function(response)
			{
				//console.log(response);
				var obj=jQuery.parseJSON(response);
				$("#unit_id").html(obj.unit_option);
				if(edit_unit!="0"){
					$("#unit_id").html(obj.edit_unit);
				}
				$('#isbatchwise').val(obj.batch_wise_stock_manage);
				add_product_batch_wise();
			}
		});
	}
}


function add_product_batch_wise(){
	var isbatchwise=$("#isbatchwise").val();
	if(isbatchwise==="" || isbatchwise==="0"){
		$(".product_add_batch_wise").hide();
		$(".product_add_direct").show();
	}else{
		$(".product_add_batch_wise").show();
		$(".product_add_direct").hide();
	}
}

function edit_grn_data(edit_id)
{ 
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/stock_transfer/',
		data: { mode:"preedit", edit_id:edit_id },
		success: function(response)
		{
			//console.log(response)
			
			var data = jQuery.parseJSON(response);
			
			$("#product_id").select2("val",data.product_id);
			$("#transfer_qty").val(data.stock_qty);
			load_product_unit(data.product_id,data.stock_unit);
			
			
			$("#edit_id").val(edit_id);
			$('#addrow').val('Update');
			Unloading();
		}
	});
}

function delete_gd_tranfer(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/stock_transfer/',
			data: { mode:"delete_gd_tranfer", id:id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					load_stock_transfer_datatable();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}


function product_load(){
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	// var product_type=$("#product_type").val();
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=production_pro_type&search=bom_pro_search';
	$.getJSON(mainurl, function(json) {
		// console.log(json);
		var arr=new Array();
		var len=json[0].length;
		// console.log(len);

		for(var i=0;i<len;i++)
		{	
			testData.push({ id:json['0'][i] ,text: json['1'][i]});
				//alert(json['1'][i]);
			}
		});

	return testData;
}

$('#product_id').select2({
	data: product_load(),
	placeholder: 'search',
	multiple: false,
    // query with pagination
    query: function(q) {
    	var pageSize,
    	results,
    	that = this;
      	pageSize = 20; // or whatever pagesize
      	results = [];
      	if (q.term && q.term !== '') {
        	// HEADS UP; for the _.filter function i use underscore (actually lo-dash) here
        	results = _.filter(that.data, function(e) {
        		return e.text.toUpperCase().indexOf(q.term.toUpperCase()) >= 0;
        	});
        } else if (q.term === '') {
        	results = that.data;
        }
        q.callback({
        	results: results.slice((q.page - 1) * pageSize, q.page * pageSize),
        	more: results.length >= q.page * pageSize,
        });
	  //$("#product_id").select2('data', { id:1, title: "UPS 3200B"});
	},
});

function get_child_godown(to_godown_id){
	var godown_id = $("#godown_id").val();
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/stock_transfer/',
			data: { mode:"get_child_godown_list", parent_id:to_godown_id,godown_id:godown_id },
			success: function(response)
			{
				$("#godown_id").empty().html(response);
				Unloading();					
			}
		});	
}


function load_stock_qty(){

	var product_id = $("#product_id").val();
	var unit_id=$("#unit_id").val();
	var godown_id=$("#godown_id").val();

	if(product_id == ""){
		return false;
	}
		Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/stock_transfer/',
		data: { mode : "load_stock_qty", product_id:product_id,unit_id:unit_id, godown_id:godown_id },
		success: function(data){
			//console.log(data);
			$('#stock_qty').val(parseFloat(data));
			$("#transfer_qty").attr("max",parseFloat(data));
			Unloading();
		}		
	});
}

function load_available_stock_godown(product_id=""){
	if(product_id){

	}else{
		var product_id=$("#product_id").val();
	}
	if(product_id == ""){
		return false;
	}
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/stock_transfer/',
		data: { mode : "load_available_stock_godown", product_id:product_id },
		success: function(response)
			{
				var data = JSON.parse(response);
           		$("#from_godown_id").empty();  

            if (data.from_godown_id) {
                $("#from_godown_id").append(data.from_godown_id);  
            }
            Unloading();  
        }
    });
	// $("#godown_id").on('click',function(){
	// 	var from_godown_id = $(this).val();
	// 	$("#from_godown_id").trigger(from_godown_id);
	// });
}

function get_godown_branch(godown_id,type){
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/stock_transfer/',
		data: { mode : "get_godown_branch", godown_id:godown_id },
		success: function(data){
			
			if(type == 'to'){	
				$("#to_branch_id").val(data.trim());
			}else if(type == 'from'){
				$("#from_branch_id").val(data.trim());
			}
			
			Unloading();
		}		
	});
}


function open_stock_transfer_model(stock_transfer_id,transfer_no) {
	$('#preview_stock_transfer_approve_model').modal('show');
	$('#apprv_stock_transfer_id').html(transfer_no);
	$("#stock_transfer_id").val(stock_transfer_id);
	
	load_stock_transfer_details(stock_transfer_id);
	load_stock_transfer_data();
}

function load_stock_transfer_details(stock_transfer_id) {
	$.ajax({
		type: "POST",
		url: root_domain + inventory_domain + 'app/stock_transfer/',
		data: { mode: "load_stock_transfer_details", stock_transfer_id: stock_transfer_id },
		success: function (resp) {
			var resp = JSON.parse(resp);

			$('#mod_stock_div_sec').html(resp.mod_stock_div_sec);
			$('#stock_transfer_approve_status').select2({
					width : "100%"
			});
		}
	});
}

function add_stock_transfer_apprv_hist() {

	var form_data = {
		mode: "add_stock_apprv_hist",
		approve_status: $('#stock_transfer_approve_status').val(),
		approve_remark: $('#stock_transfer_approve_remark').val(),
		stock_transfer_id: $('#stock_transfer_id').val(),
		
	};
	var status = 'Approved';
	if ($('#stock_transfer_approve_status').val() === '0') {
		status = 'Rejected';
	}
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + inventory_domain + 'app/stock_transfer/',
		data: form_data,
		success: function (response) {
			if (response) {
				$('#stock_transfer_approve_status').select2("val", "0");
				$('#stock_transfer_approve_remark').val("");
				load_stock_transfer_data();
				load_stock_transfer_datatable();

			} else {
				toastr.warning("You have already " + status, "ERROR");
				$('#stock_transfer_approve_status').select2("val", "0");
				$('#stock_transfer_approve_remark').val("");
			}
			$('#preview_stock_transfer_approve_model').modal('hide');
			Unloading();
		}
	});
}

function load_stock_transfer_data() {
	var stock_transfer_id = $('#stock_transfer_id').val();

	$("#stock-history-datatable").dataTable({
		"bAutoWidth": false,
		"bFilter": true,
		"bSort": true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide": true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='" + root_domain + "img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !"
		},
		"aLengthMenu": [[5, 10, 20, -1], [5, 10, 20, "All"]],
		"iDisplayLength": 5,
		"sAjaxSource": root_domain + inventory_domain + 'app/stock_transfer/',
		"fnServerParams": function (aoData) {
			aoData.push({ "name": "mode", "value": "load_stock_hist_datatable" }, { "name": "stock_transfer_id", "value": stock_transfer_id });
		},
		"fnDrawCallback": function (oSettings) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
	$('.dataTables_length select').addClass('form-control');
	// validate the comment form when it is submitted  

}


function open_batch_wise_qty(){
		
		load_batch_datatable();
		if($("#product_id").val()==="")
		{		
			toastr.warning("Select Product", "ERROR")
			$("#product_id").select2('focus')
			return false;
		}
		else if($("#transfer_qty").val()==="")
		{		
			toastr.warning("Enter Qty", "ERROR")
			$("#transfer_qty").focus();
			return false;
		}
		
		var qty=$("#transfer_qty").val();
		var product_id=$("#product_id").val();
		
		$.ajax({
			type: "POST",
			url: root_domain+ inventory_domain+'app/stock_transfer/',
			data: { mode : "batch_stock_model_open",qty:qty,product_id:product_id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$('#bs-batch_wise_stock-modal').modal('show');
				$("#batch_data").html(data.html_data);	
				$(".batch_select2").select2({
					width: '100%',
				//minimumInputLength: 3
			});	
				validate_qty(0);	
			}
		});
	}

function load_batch_datatable()
{
	var product_id=$('#product_id').val();
	
	var edit_id = $("#edit_id").val();
	
	datatable = $("#batch_stock_table").dataTable({
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
		"iDisplayLength": 5,
		"sAjaxSource": root_domain + inventory_domain +'app/stock_transfer/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch_batch_qty" },
				{ "name": "product_id", "value": product_id },
				{"name":"edit_id","value":edit_id} );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
	}

	function add_batch_qty(){
		
		if($("#batch_id").val()==="")
		{		
			toastr.warning("Select Batch number", "ERROR")
			$("#batch_id").select2('focus')
			return false;
		}
		else if($("#qtyforbatch").val()==="")
		{		
			toastr.warning("Enter Qty", "ERROR")
			$("#qtyforbatch").focus();
			return false;
		}

		var stock_id = $("#batch_id").val();
		var qty = $("#qtyforbatch").val();
		var product_id =  $("#product_id").val();
		var edit_id = $("#edit_id").val();
		var unit_id = $("#unit_id").val();
		$.ajax({
			type: "POST",
			url: root_domain+ inventory_domain+'app/stock_transfer/',
			data: { mode : "add_batch_qty",qty:qty,product_id:product_id,stock_id:stock_id,
			edit_id:edit_id,unit_id:unit_id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				var response1=data.res;
				if(response1.trim() == "1") {
					
					toastr.success("DATA ADDED SUCCESSFULLY", "SUCCESS");
					$("#batch_id").select2("val","");
					$("#qtyforbatch").val("");
					$("#batch_stock").val("");
					load_batch_datatable();
					validate_qty(0);
					
				}else if(response1.trim() == "-1") {
					toastr.warning("ALREADY EXISTS", "WARNING");
					return false;
				}
				else if(response1.trim() == "0") {
					toastr.warning("SOMETHING WENT WRONG", "WARNING");
					return false;
				}
				

			}
		});
	}
	function validate_qty(qtyforbatch1){

		var product_qty =  $("#transfer_qty").val();
		var product_id =  $("#product_id").val();
		var edit_id = $("#edit_id").val();
		var qtyforbatch = qtyforbatch1;
		
		$.ajax({
			type: "POST",
			url: root_domain+ inventory_domain+'app/stock_transfer/',
			data: { mode : "validate_qty",product_qty:product_qty,product_id:product_id,
			qtyforbatch:qtyforbatch,edit_id:edit_id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				var response1=data.res;

				if(response1.trim() == "0") {
					$("#qtyforbatch").val('0')
					toastr.warning("Batch Quantity can not greater Product quantity", "WARNING");
					$(".addbutton").hide();
					return false;
				}else if(response1.trim() == "1") {
					$(".addbutton").show();
				}else{
					$(".addbutton").hide();
				}
			}
		});
	}
	function get_batch_qty(id){
		/*var stock = $("#batch_id").find(':selected').attr("data-stock");
		$("#batch_stock").val(stock);*/

		var batch_no = $("#batch_id").val();
		var unit_id = $("#unit_id").val();
		var product_id = $("#product_id").val();
		var st_godown_id = $("#godown_id").val();
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+ inventory_domain+'app/stock_transfer/',
			data: { 
				mode : "get_batch_qty",
				batch_no:batch_no,
				st_godown_id:st_godown_id,
				unit_id:unit_id,
				product_id:product_id
			},
			success: function(response)
			{
				var stock = response.trim();
				$("#batch_stock").val(response);
				Unloading();
				validate_qty(0);
			}
		});
	}

	function delete_batch_stock_entry(batchstockid){

		$.ajax({
			type: "POST",
			url: root_domain+ inventory_domain+'app/stock_transfer/',
			data: { mode : "delete_batch_entry",batchstockid:batchstockid},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				var response1=data.res;
				if(response1.trim() == "1") {
					toastr.success("DATA DELETED SUCCESSFULLY", "SUCCESS");
					load_batch_datatable();				
				}
				else if(response1.trim() == "0") {
					toastr.warning("SOMETHING WENT WRONG", "WARNING");
					return false;
				}
				validate_qty(0);
			}
		});
	}

