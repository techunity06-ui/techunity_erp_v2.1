//var datatable;
$(document).ready(function() {
	product_load();
	// load_bom_costing_template();
	load_bom_costing_datatable();
	if($("#mode").val() == "Add"){
		get_bom_costing_no();
	}else{
		
		$("#purchase_rate").select2('val',$("#edit_purchase_rate").val());
		$("#purchase_rate").select2('readonly',true);
		$("#template_id").select2('val',$("#edit_template_id").val());
		$("#template_id").select2('readonly',true);
		var product_id = $("#edit_product_id").val();
		var product_name = $("#product_name").val();
		$("#product_id").select2('data', { id:product_id,text:product_name});
		load_product_version(product_id);
		var bom_costing_id = $("#bom_costing_id").val();
		load_costing_report(bom_costing_id);
	}
	
$("#bom_costing").validate({
	rules: {
		costing_no: {
			required: true			
		},
		costing_date: {
			required: true			
		},
		product_id: {
			required: true			
		},
		bom_version_id: {
			required: true			
		},
		purchase_rate: {
			required: true
		},
		// template_id: {
		// 	required: true
		// },
		qty: {
			required: true
		}
	},
	messages: {
		costing_no: {
			required: "Enter Costing No"			
		},
		costing_date: {
			required: "Enter Costing Date"			
		},
		product_id: {
			required: "Select Product"
		},
		bom_version_id: {
			required: "Select BOM Version"
		},
		purchase_rate: {
			required: "Select Purchase Rate"
		},
		// template_id: {
		// 	required: "Select Costing Template"
		// },
		qty: {
			required: "Enter Qty."
		}
		
	}
}); 
});

function load_bom_costing_datatable()
{
	// var date=$('#rep_date').val();
	
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
		"aLengthMenu": [[-1, 10, 20, 30, 50], ["All", 10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+production_domain+'app/bom_costing/',
		"fnServerParams": function ( aoData ) {
			aoData.push( 
				{ "name": "mode", "value": "fetch" },
				// { "name": "date", "value": date },
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

$("#bom_costing").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#bom_costing").valid()) {
		return false;
	}

	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
    var form_data=new FormData(this);	
    $.ajax({
    	cache:false,
    	url: root_domain+production_domain+'app/bom_costing/',
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
				// toastr.success("BOM ADDED SUCCESSFULLY", "SUCCESS");
				$("#save").hide();
				load_costing_report(arr.bom_costing_id);
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			
			// $('#bom_costing').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
			Unloading();
		}
	});
});

function get_bom_costing_no(){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom_costing/',
		data: { mode : "load_bom_costing_no"},
		success: function(response)
		{
			$('#costing_no').val(response);
			Unloading();
		}
	});
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


function load_product_version(product_id){
	$('#bom_id').val("");
	$("#save").show();
	if(product_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom_costing/',
			data: { mode : "load_product_version",  product_id : product_id},
			success: function(response)
			{
				$('#bom_version_id').empty().append(response);
				$("#bom_version_id").select2({
					width: '100%'
				});

				if($("#mode").val() == "Edit"){
					$("#bom_version_id").select2('val',$("#edit_bom_version_id").val());
					$("#bom_version_id").select2('readonly',true);
				}

				Unloading();
			}
		});
	}
}


function get_bom_details(bom_version_id){
	var product_id = $("#product_id").val();

	if(product_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom_costing/',
			data: { mode : "get_bom_details",  product_id : product_id,bom_version_id:bom_version_id},
			success: function(response)
			{
				if(response != ""){
					var arr = jQuery.parseJSON(response);
					$('#bom_id').val(arr.bom_id.trim());
					$('#qty').val(arr.qty.trim());
				}else{
					$('#bom_id').val("");
					$('#qty').val("1");
				}
				
				Unloading();
			}
		});
	}else{
		toastr.warning("SELECT PRODUCT", "ERROR")
		return false;
	}
}

function load_bom_costing_template(){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom_costing/',
		data: { mode : "load_bom_costing_template"},
		success: function(response)
		{
			$('#template_id').empty().append(response);
			$("#template_id").select2({
				width: '100%'
			});

			if($("#mode").val() == "Edit"){
				$("#template_id").select2('val',$("#edit_template_id").val());
				$("#template_id").select2('readonly',true);
			}

			Unloading();
		}
	});	
}

function load_costing_report(bom_costing_id){
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom_costing/',
			data: { mode : "generate_costing_report",  bom_costing_id : bom_costing_id},
			success: function(response)
			{
				$("#bom_costing_id").val(bom_costing_id);
				$("#costing_report").empty().html(response);
				$("#dyn_template_id").select2({
					width : '100%'
				});
				Unloading();
			}
		});
}

function update_rm_rate(bom_costing_trn_id,bom_costing_id){
	var total_rate = $("#txt_rm_"+bom_costing_trn_id).val();
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom_costing/',
			data: { mode : "update_rm_rate",  bom_costing_trn_id : bom_costing_trn_id,total_rate : total_rate},
			success: function(response)
			{
				toastr.success("RATE UPDATE SUCCESSFULLY", "SUCCESS");
				load_costing_report(bom_costing_id);
				Unloading();
			}
		});

}

function update_process_rate(bom_costing_trn_id,bom_costing_id){

	var data = {};
		data.total_rate = [];
		data.bom_costing_process_id = [];
		
	$('input.process_rate_'+bom_costing_trn_id).each(function(){ 
		var rate=$(this).val();
		var bom_costing_process_id=$(this).attr("data-bom_costing_process_id");

		data.total_rate.push(rate);
		data.bom_costing_process_id.push(bom_costing_process_id);
	});

	Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom_costing/',
			data: { mode : "update_process_rate",  bom_costing_trn_id : bom_costing_trn_id,total_rate : data.total_rate,bom_costing_process_id:data.bom_costing_process_id},
			success: function(response)
			{
				toastr.success("PROCESS RATE UPDATE SUCCESSFULLY", "SUCCESS");
				load_costing_report(bom_costing_id);
				Unloading();
			}
		});

}


function delete_bom_costing(bom_costing_id) 
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom_costing/',
			data: { mode : "delete",  bom_costing_id : bom_costing_id },
			success: function(response)
			{

					//console.log(response)
					if(response.trim() == "1") {
						toastr.success("BOM COSTING DELETE SUCCESSFULLY", "SUCCESS");
						load_bom_costing_datatable();
						Unloading();
					}
					else {
						toastr.warning("SOMETHING WRONG", "WARNING");
						Unloading();
					}		
				}
			});	
	}
	
}


function change_template(template_id,costing_rate,bom_costing_id){
	
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom_costing/',
			data: { mode : "load_bom_costing_template_data",  template_id : template_id,costing_rate:costing_rate,bom_costing_id:bom_costing_id},
			success: function(response)
			{
				$("#tbl_template_data").empty().html(response);
				Unloading();
			}
		});
}


function save_costing_template_value(){
	var bom_costing_template_id = $("#dyn_template_id").val();
	var bom_costing_id = $("#bom_costing_id").val();
	var data = {};
	data.temp_name = [];
	data.value = [];
	data.operation = [];
	data.formula = [];
	// data.total_value = [];

	var grand_total = $("#lbl_grand_total").html();
	$('#tbl_template_data .tmp_typename').each(function(index){ 
		var name = $(this).html();
		data.temp_name.push(name);
	});
	$('#tbl_template_data .input_rate').each(function(index){ 
		var value = $(this).val();
		var formula = $(this).attr('data-cal-type');
		data.value.push(value);
		data.formula.push(formula);
	});
	$('#tbl_template_data .input_temp_rate').each(function(index){ 
		var operation = $(this).attr('data-operation');
		var value = parseFloat($(this).html().trim());
		data.operation.push(operation);
		// data.total_value.push(value);
	});

		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom_costing/',
			data: { 
					mode : "save_costing_data",  
					bom_costing_id : bom_costing_id,
					grand_total:grand_total,
					temp_name:data.temp_name,
					value : data.value,
					type:data.operation,
					formula : data.formula,
					bom_costing_template_id:bom_costing_template_id
				},
			success: function(response)
			{
				Unloading();
				location.href= root_domain+production_domain+'bom_costing_list';
			}
		});
}

function calculate_rate(id,costing_rate,type){ // type :: 1 - percentage, 2 - plus
	var value = $("#input_rate_"+id).val();
	var total = 0;
	if(type == 1){
		total = (costing_rate * value) / 100;
	}else {
		total = value;
	}
	$("#txt_tmp_total_"+id).html(total);
	get_grand_total(costing_rate);
}

function get_grand_total(costing_rate){
	var total = costing_rate;
	$('#tbl_template_data .input_temp_rate').each(function(index){ 
		var value = 0;
		value = parseFloat($(this).html().trim());
		var operation = $(this).attr('data-operation');
		if(operation == 0){
			total = total + value;
		}else {
			total = total - value;	
		}			
		$("#lbl_grand_total").html(total.toFixed(2));
	});
}