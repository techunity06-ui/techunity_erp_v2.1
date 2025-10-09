$(document).ready(function() {
	load_maintenance_datatable();
	//show_data();
	product_load();
	// validate vendor add form on keyup and submit
	$("#maintenance_add").validate({
		rules: {
			maintenance_no: {
				required: true
			},
			maintenance_date: {
				required: true
			},
			cust_id: {
				required: true
			},
			product_id: {
				required: true
			},
			bill_no: {
				required: true
			},
			bill_date: {
				required: true
			},
			price: {
				number:true,
				required:true
			},
			calibration_period: {
				required: true
			},
			remind_before: {
				required: true
			}
		},
		messages: {
			maintenance_no: {
				required: "Enter Maintenance No"
			},
			maintenance_date: {
				required: "Enter Maintenance Date"
			},
			cust_id: {
				required: "Choose Customer"
			},
			product_id: {
				required: "Choose Product"
			},
			bill_no: {
				required: "Enter Bill no"
			},
			bill_date: {
				required: "Enter Bill date"
			},
			price: {
				number:"Enter Only number",
				required: "Enter Price"
			},
			calibration_period: {
				required: "Enter Calibration period"
			},
			remind_before: {
				required: "Enter Calibration Req"
			}
		}
	}); 
}); 

$("#maintenance_add").on('submit',function(e) {
	// var maintenance_id = $('#eid').val();
	// var inq_product_required = $('#inq_product_required').val();
	// var product = check_product(inq_id);
	// if(inq_product_required == '1'){
	// 	if(product === false){		
	// 		toastr.warning("Add Product Please!!", "ERROR");
	// 		$("#product_id").select2('focus');
	// 		return false;
	// 	}
	// }
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#maintenance_add").valid()) {
		return false;
	} 
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#save').prop('disabled', true);
	var form_data=new FormData(this);	
	
	//Hide Form Submit Alert
	setFormSubmitting();
	
	$.ajax({
		cache:false,
		url: root_domain + maintenance_domain + 'app/maintenance/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				toastr.success("MAINTENANCE ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + maintenance_domain + 'maintenance_list';
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			else if(arr.msg == '-1') {
				toastr.info("ALREADY EXISTS", "INFO");
			}
			else if(arr.msg == 'update') {	
				toastr.success("MAINTENANCE UPDATED SUCCESSFULLY", "SUCCESS");		
				window.location = root_domain + maintenance_domain + 'maintenance_list';	
			}
			Unloading();
			$('#maintenance_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function load_maintenance_datatable(){
	var date= $('#rep_date').val();

	$("#maintenance-datatable").dataTable({
		"bStateSave": true,
		"fixedHeader": true,
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !"
		},
		"aLengthMenu": [[-1, 10, 20, 30, 50], ["All", 10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + maintenance_domain + 'app/maintenance/',
		"fnServerParams": function ( aoData ) {
			aoData.push( {"name": "mode", "value": "fetch"}, 
				{"name": "date", "value": date});
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function delete_maintenance(maintenance_id,maintenance_no) {
	var r= confirm(" Are you sure, you want to delete '"+maintenance_no+"' ?");
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + maintenance_domain + 'app/maintenance/',
			data: { mode : "delete",  maintenance_id : maintenance_id },
			success: function(response)
			{
               	//console.log(response);
               	if(response.trim() == "1") {
               		toastr.success("MAINTENANCE DELETE SUCCESSFULLY", "SUCCESS");
               		load_maintenance_datatable();
               	}
               	else if(response.trim() == "0") {
               		toastr.warning("SOMETHING WRONG", "WARNING");
               	}	
               	Unloading();						
               }
           });	
	} 
}
function load_product_dtls(product_id){
	var branch_id = $('#branch_id').val();
	var cust_id = $('#cust_id').val();
	if(branch_id==''){
		toastr.warning("Select branch", "ERROR");
		$("#branch_id").focus();
		return false;
	}
	if(cust_id==''){
		toastr.warning("Select Company", "ERROR");
		$("#cust_id").focus();
		return false;
	}

	if(product_id){
		// Loading();
		$.ajax({
			type: "POST",
			url: root_domain + maintenance_domain + 'app/maintenance/',
			data: { mode:"load_product_dtls", product_id:product_id },
			beforeSend: function() {
				$('#product_category').select2("val","");
				$('#product_icode').val("");
				$('#drawing_no').val("");
			},
			success: function(response)
			{
				var resp=jQuery.parseJSON(response);
				$('#product_category').select2("val",resp.product_category);
				$('#product_icode').val(resp.product_icode);
				$('#drawing_no').val(resp.drawing_number);
				// Unloading();
			}
		});	
	}
}

function product_load(){
	var testData = [];
	var inquiry_type=1;
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type;
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		var len=json[0].length;
			// console.log(len);
			
			for(var i=0;i<len;i++)
			{	
				testData.push({ id:json['0'][i] ,text: json['1'][i]});
				//alert(json['1'][i]);
			}
		});
		load_cat_product('product_id', testData);
		var pro_id = $('#pro_id').val();
		if(pro_id!=''){
		    var product_name = $('#product_name').val();
			$("#product_id").select2('data', { id:pro_id, text: product_name});
		}
}

function load_cat_product(id, testData){
	// alert(id);
	$('#'+id).select2({
		data: testData,
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
		},
	});
}
function get_series_no(){
	
	$.ajax({
		type: "POST",
		url: root_domain + maintenance_domain + 'app/maintenance/',
		data: { mode : "get_series_no"},
		success: function(resp){
			var no = jQuery.parseJSON(resp);
				$('#maintenance_no').val(no.series_no);
			}		
		});	
}