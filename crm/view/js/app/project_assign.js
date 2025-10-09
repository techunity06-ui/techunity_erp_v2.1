//var datatable;
$(document).ready(function() {
	load_datatable();
	var pcode = $('#product_type').val();
	var base_unit = $('#product_base_unit').val();
	get_product_code(pcode);
	get_base_unit(base_unit);
	product_load();
	// validate the comment form when it is submitted        
	// validate vendor add form on keyup and submit
	$("#project_assign_add").validate({
		rules: {
			project_name: {
				required: true			
			},
			branch_id: {
				required: true
			}
		},
		messages: {
			project_name: {
				required: "Enter Project Name"
			},
			branch_id: {
				required: "Select Branch"
			}

		}
	});
});

$("#project_assign_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#project_assign_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	for (instance in CKEDITOR.instances) 
	{
		CKEDITOR.instances[instance].updateElement();
	}
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/project_assign/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				Unloading();
				toastr.success("PROJECT ADDED SUCCESSFULLY", "SUCCESS");
				
				window.location=root_domain + crm_domain +'project_assign_list';
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
				toastr.success("PROJECT UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain + crm_domain +'project_assign_list';
			}
			$('#project_assign_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
});

function delete_project_assign(id) 
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+crm_domain +'app/project_assign/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("SALES ORDER DELETE SUCCESSFULLY", "SUCCESS");
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
function load_productdetail(val) {
	if(val!=0)
	{
		$('#addproduct').hide();
	}
	else
	{
		$('#addproduct').show();
	}
	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/project_assign/',
		data: { mode : "load_productdata",eid :val },
		success: function(response)
		{
			var obj =jQuery.parseJSON(response);
			CKEDITOR.instances['product_des'].setData(obj.product_desc);
			CKEDITOR.instances['product_spec'].setData(obj.product_spec);
			$('#product_hsn_code').select2("val",obj.product_hsn);
			$('#product_rate').val(obj.product_sale_rate);

		}
	});
}
function add_field()
{
	
	if($("#product_id").val()==="")
	{		
		toastr.warning("Select Product Name", "ERROR")
		return false;
	}
	if($("#product_qty").val()==="")
	{		
		toastr.warning("Enter Qty", "ERROR")
		return false;
	}
	/*if($("#sqr_ft").val()==="")
	{		
		toastr.warning("Enter Sqr/Ft", "ERROR")
		return false;
	}*/
	if($("#product_rate").val()==="")
	{		
		toastr.warning("Enter Rate", "ERROR")
		return false;
	}
	if($("#branch_id").val()==="")
	{		
		toastr.warning("Select Branch Id", "ERROR")
		return false;
	}
	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/project_assign/',
		data: { mode : "fieldadd",
		edit_id:$("#edit_id").val(),
		product_id:$("#product_id").val(),
		product_disc:$("#product_des").val(),
		product_spec:$("#product_spec").val(),
		product_hsn_code:$("#product_hsn_code").val(),
		product_qty:$("#product_qty").val(),
		product_rate:$("#product_rate").val(),
		project_assign_id:$("#eid").val(),
		branch_id:$("#branch_id").val()
	},
	success: function(response)
	{
		console.log(response);
		$("#product_id").select2("val","")
		$("#product_des").val("")
		$("#product_spec").val("")
		$("#product_hsn_code").select2("val","")
		$("#product_qty").val("")
		$("#product_rate").val('')
		$("#edit_id").val('')
		$('#addrow').val('Add');
		Unloading();
		show_data();
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
	var branch_id=$('#branch_id').val();
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
		"sAjaxSource": root_domain+ crm_domain +'app/project_assign/',
		"fnServerParams": function ( aoData ) {

			aoData.push( { "name": "mode", "value": "fetch" },
				{ "name": "branch_id", "value": branch_id }
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

function show_data()
{
	var so_id=$("#eid").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/project_assign/',
		data: { mode : "load_tempoutward",so_id:so_id},
		success: function(data){
				//console.log(data);
				$('#sale_productdata').html(data);				
				Unloading();
			}		
		});
}

function edit_data(id)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/project_assign/',
		data: { mode : "preedit",  id : id},
		success: function(response)
		{
				//console.log(response)
				var data = jQuery.parseJSON(response);
				$("#product_id").select2('data', { id:data.product_id, text: data.product_name})
				$("#product_hsn_code").select2("val",data.product_hsn_code)
				$("#product_des").val(data.description)
				$("#product_qty").val(data.product_qty)
				$("#product_rate").val(data.product_rate)
				$("#formulaid").val(data.formulaid);
				$("#edit_id").val(id)
				$('#addrow').val('Update');
				CKEDITOR.instances['product_des'].setData(data.product_desc);
				CKEDITOR.instances['product_spec'].setData(data.product_spec);
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
			url: root_domain + crm_domain +'app/project_assign/',
			data: { mode : "delete_data",  eid : id},
			success: function(response)
			{
					//console.log(response)
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

function get_amount()
{	
	var product_qty = parseFloat($("#product_qty").val());
	var product_rate = parseFloat($("#product_rate").val());

	if(product_qty && product_rate && product_qty!='0' && product_rate!='0')
	{
		var product_amount=parseFloat((product_qty)*(product_rate));
		/*$("#product_amount").val(parseFloat(product_amount).toFixed(2));
		$("#product_total").val(parseFloat(product_amount).toFixed(2));*/
		if($("#formulaid").val()!="")//tax calculation
		{
			var formulaid=$("#formulaid").val();
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain + 'app/project_assign/',
				data: { mode : "getproduct_amount", product_amount:product_amount ,formulaid:formulaid },
				success: function(response)
				{
					var obj=jQuery.parseJSON(response);
					//$('#product_total').val(obj.product_total);
				}
			});
		}
	}
	else {
		//$("#product_amount").val(0);
	}
}
function get_product_code(pcode)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain+'app/project_assign/',
		data: { mode : "get_product_code",  pcode : pcode },
		success: function(response)
		{
			var data=jQuery.parseJSON(response)
			var series=data.series;
			var code=data.code;
			$('#product_icode').val(series);
			$('#product_icode_code').val(code);
			Unloading();			
		}
	});	
}
function get_base_unit(unit){
	if(unit!=""){
		$('#product_conv_unit').val(unit);
	}
}
function check_pro_unit(pro_unit)
{
	var cmode = $('#mode').val();
	var unit = $('#product_base_units').val();
	var product_id = $('#eid').val();
	//Loading();
	if(cmode=='edit'){
		$.ajax({
			type: "POST",
			url: root_domain+crm_domain+'app/project_assign/',
			data: { mode : "check_pro_unit", unit_id : pro_unit, unit: unit, product_id: product_id},
			success: function(responce){
				// Unloading();
				// console.log(responce);
				var data = jQuery.parseJSON(responce);
				if(data.status==0){
					toastr.warning("This Project used in "+data.table+" .Please Change or delete Project from "+data.table, "WARNING");	
					$('#product_base_unit').select2('val',unit);
				}
			}
		});
	}
}
function view_project_assign(id){
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/project_assign/',
		data: { mode : "view_project_assign", id : id },
		success: function(response)
		{
			console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#ModalViewProject").modal("show");
			$("#project_name").html(obj.project_name);
			$("#project_code").html(obj.project_code);
			$("#project_unit").html(obj.project_unit);
			$("#show_product").html(obj.show_product);
			Unloading();
		}
	});

}
function product_load(){
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=crm_pro_type&search=crm_pro_search';
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
	load_cat_product('product_id', testData)	
	// return testData;
}

function load_cat_product(id, testData){
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
		  //$("#product_id").select2('data', { id:1, title: "UPS 3200B"});
		},
	});
}