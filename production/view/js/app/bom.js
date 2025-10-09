//var datatable;
$(document).ready(function() {
	load_bom_datatable();
	
	/* Sanat :: comment below code bcz added in load_version_bom_data function */
	
	// if(alloted==1){
	// 	show_alloted_data();
	// }else{
	// 	show_data();
	// }
	load_bom_version_datatable();
	product_load();
	
//Search Product Wise
$("#fil_product_search").on("keyup", function() {
	var value = $(this).val().toLowerCase();
	$("#fil_product_tbl tr").filter(function() {
		$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
	});

}); 

/*$("#fil_product_search").on("keyup", function() {
    var value = $(this).val();

    //$("table tr").each(function(index) {
    $("#fil_product_tbl tr").each(function(index) {
	console.log(index);
        if (index !== 0) {
			
            $row = $(this);

            var id = $row.find("td:first").text();

            if (id.indexOf(value) !== -1) {
                $row.hide();
            }
            else {
                $row.show();
            }
        }
    });
});*/
// validate the comment form when it is submitted        
// validate vendor add form on keyup and submit
$("#bom_add").validate({
	rules: {
		bom_no: {
			required: true			
		},
		bom_date: {
			required: true			
		},
		sel_product_id: {
			required: true
		},
		sel_product_qty: {
			required: true
		},
		base_qty: {
			required: true
		}
	},
	messages: {
		bom_no: {
			required: "Enter BOM No."
		},
		bom_date: {
			required: "Enter date"
		},
		sel_product_id: {
			required: "Select Product Name"
		},
		sel_product_qty: {
			required: "Select Product Qty"
		},
		base_qty: {
			required: "Enter Qty."
		}
		
	}
}); 
});

function get_stock(product_id,old_qty)
{
	//alert(pid);
	var branch_id = $('#branch_id').val();
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode:"load_qty", product_id:product_id, branch_id:branch_id },
		success: function(resp){
			//console.log(resp);
			//alert(resp);
			if(resp){
				$('#product_qty').attr("placeholder",resp);
				$('#product_qty').attr("max",resp);
				$("#product_qty").attr("max",parseFloat(old_qty)+parseFloat(resp));
			}
			Unloading();
		}
	});
}

function submit_bom()
{
	$("#save_print").val(1)
	//$("#bom_add").submit();
}

$("#bom_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#bom_add").valid()) {
		return false;
	}

	if($("#bom_assign").val()=='yes'){
		var r= confirm(" Are you want to exit without assign BOM ?");

		if(r) {
			if($("#bom_assign_from").val()=='store_order'){
				window.location=root_domain+production_domain+'store_order_design_department';
			}else{
				window.location=root_domain+production_domain+'design_department_get_sales_order_details';
			}
		}else{
			return false;	
		}
	}


	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	/*for (instance in CKEDITOR.instances) 
	{
    	CKEDITOR.instances[instance].updateElement();
    }*/
    var form_data=new FormData(this);	
    $.ajax({
    	cache:false,
    	url: root_domain+production_domain+'app/bom/',
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
				toastr.success("BOM ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+production_domain+'bom_list';
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(arr.msg == '-1'){
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			else if(arr.msg == 'update'){	
				toastr.success("BOM UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain+production_domain+'bom_list';
			}
			$('#bom_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
});

function delete_bom(id) 
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{

					//console.log(response)
					if(response.trim() == "1") {
						toastr.success("BOM DELETE SUCCESSFULLY", "SUCCESS");
						load_bom_datatable();
						Unloading();
					}else if(response.trim() == "2") {
						toastr.info("Please Remove Used In Bom", "INFO");
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
						Unloading();
					}							
				}
			});	
	}

	
}

function add_field()
{
	if($("#product_id").val()===""){
		toastr.warning("Select Product Name", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	else if($("#product_base_qty").val()===""){
		toastr.warning("Enter Qty", "ERROR")
		return false;
	}
	else if($("#sel_product_id").val()===""){
		toastr.warning("Select Product Name", "ERROR");
		$("#sel_product_id").select2('focus');
		return false;
	}
	
	
	else if($("#base_qty").val()===""){
		toastr.warning("Enter Qty", "ERROR");
		$("#base_qty").focus();
		return false;
	}

	var check_pr_type_process = $("#is_process_required").val();
	// console.log($('#product_type').val());
	if(check_pr_type_process == '0'){
	// if(($('#product_type').val() == "3") || ($('#product_type').val() == "5")){

	}else{
		if($("#pro_version_id").val()===""){
			toastr.warning("Select Product Version", "ERROR");
			$("#pro_version_id").select2('focus');
			return false;
		}
	}
	var tot_standrad_qty=$("#base_qty").val();


	/* if(alloted==1){
		
		if(multiple_qty==$("#base_qty").val()){
			product_base_qty=$("#product_base_qty").val();
			product_conv_qty=$("#product_conv_qty").val();
		}else{
				if(multiple_qty==''){
				//	alert('hj');
					product_base_qty=$("#product_base_qty").val();
					product_conv_qty=$("#product_conv_qty").val();
				}else{
					//alert('f');
					product_base_qty=($("#product_base_qty").val()/$("#base_qty").val())*multiple_qty;
					product_conv_qty=($("#product_conv_qty").val()/$("#base_qty").val())*multiple_qty;
				}
		}
	}else{ */
		product_base_qty=$("#product_base_qty_hide").val();
		product_conv_qty=$("#product_conv_qty_hide").val();
		
	//}
	var values = [];
	$('.get_ms_kg').each(function(){
		values.push({ name: this.name, value: this.value }); 

	}); 

	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "fieldadd",
		tot_standrad_qty:tot_standrad_qty,
		invoicetype_id:$("#invoicetype_id").val().trim(),
		product_type:$("#product_type").val(),
		edit_id:$("#edit_id").val(),
		product_id:$("#product_id").val(),
		product_base_unit:$("#product_base_unit").val(),
		product_base_qty:product_base_qty,
		product_conv_unit:$("#product_conv_unit").val(),
		product_conv_qty:product_conv_qty,
		p_bom_id:$("#p_bom_id").val().trim(),
		bom_id:$("#bom_id").val().trim(),
		sel_product_id:$("#sel_product_id").val(),
		base_qty:$("#base_qty").val(),
		conv_qty:$("#conv_qty").val(),
		base_unit:$("#base_unit").val(),
		conv_unit:$("#conv_unit").val(),
		/*product_width:$('#product_width').val(),
		product_height:$('#product_height').val(),
		product_thickness:$('#product_thickness').val(),
		product_density:$('#product_density').val(),*/
     	conversation_factor : $("#conversation_factor").val(),
		/* Start :: Sanat added bom version  -  02-08-2022 */
		bom_version_id : $('#pro_version_id').val(),
		p_bom_version_id : $('#sel_bom_version_id').val(),
		/* End :: Sanat added bom version  -  02-08-2022 */
		values : values,
		product_kg:$('#product_kg').val() },
		success: function(response)
		{

			if(response=='-1')
			{
				toastr.info("ALREADY EXISTS", "INFO");
				Unloading();				
			}
			else
			{

				//var new_level_cnt=Number($('#level_cnt').val())+0.1;
				$("#product_type").select2("val","");
				$("#conversation_factor").select2("val","1");
				$("#product_id").select2("val","");
				$("#product_id").select2('focus');
				$("#product_qty").val("");
				$("#edit_id").val('');
				$('#addrow').val('Add');
				$('#get_spec_div').hide();
				$("#product_base_unit").val("");
				$("#product_uom").val("");
				$("#product_qty").val("");
				$("#product_act_qty").val("");
				$("#product_base_qty").val("");
				$("#product_base_unit_name").val("");
				$("#product_conv_unit_name").val("");
				$("#product_conv_qty").val("");

				/*Sanat Added : 04-08-2021 */
				$('#addprocess').val('Add');
				$('#pro_version_id').empty().append('<option value">Select Product Version</>');
				$("#pro_version_id").select2("val", "");

				Unloading();
				load_bom_version_datatable();
				if(alloted==1){
					show_alloted_data();
				}else{
					show_data();
				}

					//show_data();
				}
			}
		});
}

function reload_data()
{
	//datatable.fnReloadAjax();
	load_bom_datatable();
}	
function load_bom_datatable()
{
	var data=$('input[name=report]:Checked').val();
	var date=$('#rep_date').val();
	//var type=$('#type_id').val();
	var product_type = $('#child_usr_id').val();

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
		"sAjaxSource": root_domain+production_domain+'app/bom/',
		"fnServerParams": function ( aoData ) {
			aoData.push( 
				{ "name": "mode", "value": "fetch" },
				{ "name": "date", "value": date },
				{ "name": "product_type", "value": product_type }
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

	function clone_bom_trn_data(bom_id) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { mode : "clone_bom_trn_data", bom_id:bom_id },
			success: function(resp){

			//console.log(resp);
			//show_data();
			window.location=root_domain+production_domain+'bom_add';
			Unloading();
		}		
	});
	}
	
	function show_data()
	{

	//var form_mode= $("#mode").val();
	var bom_id= $("#bom_id").val();
	var sel_product_id= $("#sel_product_id").val();
	var sel_bom_version_id= $("#sel_bom_version_id").val();

	// console.log('prod -> ' + sel_product_id + ' :: bom version -> ' + sel_bom_version_id);
	get_bom_id(sel_product_id,sel_bom_version_id);

	//var thread= $("#thread").val();
	//var level= $("#level").val();
	//var sel_product_qty= $("#sel_product_qty").val();
	//var parent_id= $("#parent_id").val();
	//alert(form_mode);
	//alert(bom_id);
	//Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "load_tempoutward",bom_id:bom_id,sel_product_id:sel_product_id,bom_version_id:sel_bom_version_id },
		success: function(data){
			//console.log(data);
			$('#bom_productdata').html(data);		
			//Unloading();
		}		
	});
}

function show_alloted_data()
{
	//var form_mode= $("#mode").val();
	var bom_id= $("#bom_id").val();
	var sel_product_id= $("#sel_product_id").val();
	var sel_bom_version_id= $("#sel_bom_version_id").val();
	get_bom_id(sel_product_id,sel_bom_version_id);

	var lastparam=id3;

	var base_qty = $('#base_qty').val();
	var conv_qty = $('#conv_qty').val();
	
//	alert(id2);
	//var thread= $("#thread").val();
	//var level= $("#level").val();
	//var sel_product_qty= $("#sel_product_qty").val();
	//var parent_id= $("#parent_id").val();
	//alert(form_mode);
	//alert(bom_id);
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "load_alloted_tempoutward",lastparam:lastparam,bom_id:bom_id,id2:id2,sel_product_id:sel_product_id, base_qty:base_qty, conv_qty : conv_qty,bom_version_id:sel_bom_version_id },
		success: function(data){
			//console.log(data);
			$('#bom_productdata').html(data);		
			Unloading();
		}		
	});
}

function edit_data(id,pid)
{

	Loading();
	
	var base_qty = $('#base_qty').val();
	var conv_qty = $('#conv_qty').val();

	var child_pro_id = $(this).attr('data-pid');
	
	var sel_product_id= $("#sel_product_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "preedit",  id : id,id2:id2,sel_product_id:sel_product_id, base_qty : base_qty, conv_qty : conv_qty, child_id : pid},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			$('#product_id').html(data.producthtml);
			$("#product_type").select2("val",data.ptype);
			$("#product_id").select2("val",data.product_id).trigger('change');
			load_product_version(data.product_id);
			load_product_detail(data.product_id);

			$("#product_id").select2('data', { id:data.product_id, text: data.product_name});


			$("#p_bom_id").val(data.p_bom_id);

			$("#product_spec_hid").val(data.product_spec_hid);
			$("#edit_id").val(id);
			$('#addrow').val('Update');
			$('#addprocess').val('Update');


			if(data.product_specification!=0)
			{

				$('#get_spec_div').show();
				$('#get_spec_div').empty().prepend(data.product_specification_code);

					/*$('#product_width').val(data.product_width);
					$('#product_height').val(data.product_height);
					$('#product_thickness').val(data.product_thickness);
					$('#product_density').val(data.product_density);*/
					$('#product_kg').val(data.product_kg);
					
				}
				load_bom_version_datatable();	
				/* Sanat set bom versoin - 03-08-2021  */
				setTimeout(function(){ 
					$("#pro_version_id").val(data.bom_version_id).trigger('change');
					$("#product_base_qty").val((data.product_base_qty).trim());

					$("#product_base_unit").val(data.product_base_unit);
					$("#product_conv_unit").val(data.product_conv_unit);
					$("#product_conv_qty").val((data.product_conv_qty).trim());
					$("#product_base_qty_hide").val((data.product_base_qty).trim());
					$("#product_conv_qty_hide").val((data.product_conv_qty).trim());

					$("#product_base_unit_name").val(data.base_unit_name);
					$("#product_conv_unit_name").val(data.conv_unit_name);
					Unloading();

				}, 500);
				
				
				
				
			}
		});
}
function delete_data(id,table,whereid)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		//Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { mode : "delete_data", eid:id, table:table, whereid:whereid, bom_id:$("#eid").val() },
			success: function(response)
			{
				
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					if(alloted==1){
						show_alloted_data();
					}else{
						show_data();
					}
					//show_data();
					//Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}

function load_product(type_id){

	$("#product_id").select2("val","");
	
	var testData = [];
	var mainurl = root_domain+ production_domain +'app/bom/index.php?mode=load_product&type_id='+type_id;
	$.getJSON(mainurl, function(json) {
		console.log(json);
		var arr=new Array();
		var len=json[0].length;
		console.log(len);

		for(var i=0;i<len;i++)
		{	
			testData.push({ id:json['0'][i] ,text: json['1'][i]});
				//alert(json['1'][i]);
			}
		});

	load_cat_product('product_id',testData);
	/*Loading();

	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "load_product", type_id : type_id},
		success: function(data){
			// console.log(data);
			// $('#product_id').html(data);
			Unloading();
		}
	});*/

}

function load_sales_pro_data(sales_order_id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "get_sales_order_data", sales_order_id:sales_order_id},
		success: function(data){
			//console.log(data);
			$('#sales_order_pro_id').html(data);				
			Unloading();
		}
	});
}
function get_series_no(){
	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "get_series_no" },
		success: function(resp){
			//console.log(resp);
			$('#invoicetype_id').val(resp);	
			load_bom_no(resp);
		}		
	});	
}

function load_bom_no(id)
{
	//alert(id);
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "load_invoiceno", typeid : id},
		success: function(data){
			//console.log(data);
			var no = jQuery.parseJSON(data);
			$('#bom_no').val(no.invoiceno);
		}
	});
}
function entry_req_pro(sales_order_pro_id)
{
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "entry_req_pro", sales_order_pro_id : sales_order_pro_id},
		success: function(data){
			//console.log(data);
			//var no = jQuery.parseJSON(data);
			if(alloted==1){
				show_alloted_data();
			}else{
				show_data();
			}
		//	show_data();
		Unloading();
	}
});

}
function bom_req_po(){
	var	bom_trn_id = $("input[name='bom_trn_id[]']:Checked").map(function(){return $(this).val();}).get();
	//console.log(bom_trn_id);
	
	var pl_product = $('#pl_product').val();
	var pl_bom_id = $('#pl_bom_id').val();
	var planning_id = $('#planning_id').val();
	if(bom_trn_id.length > 0){
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { mode : "entry_bom_req_po", bom_trn_id:bom_trn_id, pl_product:pl_product,pl_bom_id:pl_bom_id,planning_id:planning_id },
			success: function(data){
				//console.log(data);
				//var no = jQuery.parseJSON(data);
				toastr.success("PO REQUEST SENT !!!", "SUCCESS");
				window.location=root_domain+'planning_list';
				Unloading();
			}
		});
	}
	else{
		toastr.warning("Select Product Before Requesting PO !!!", "WARNING");
		return false;
	}
	
}

function load_chk_box(){
	//alert('OK');
	if($("#all_chk_box").prop("checked")==true){
		$(".chk_box").prop('checked', true);
	}
	else{
		$(".chk_box").prop('checked', false);
	}
}

function get_product_data()
{
	
	//alert($("#sel_product_id").val());
	//alert($('#mode_edit_id').val());
	
	if($("#sel_product_id").val()===""){
		toastr.warning("Select Product Name", "ERROR");
		$("#sel_product_id").select2('focus');
		return false;
	}
	else if($("#sel_product_qty").val()===""){
		toastr.warning("Enter Qty", "ERROR")
		return false;
	}
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "get_bom_product_data",product_id:$("#sel_product_id").val(),product_qty:$("#sel_product_qty").val(),mode_edit:$('#mode_edit').val(),mode_edit_id:$('#mode_edit_id').val()},
		success: function(response)
		{
			//alert(response);
			show_data();
			$('#actual_qty').val($('#sel_product_qty').val());
			Unloading();
		}
	});
}

function add_actual_qty(id,bom_status)
{
	var r= confirm(" Are you want to change the status ?");

	if(r) {
	//alert(bom_status);
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "change_actual_status",eid:id,bom_status:bom_status},
		success: function(response)
		{

					//console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("STATUS CHANGED SUCCESSFULLY", "SUCCESS");
						if(alloted==1){
							show_alloted_data();
						}else{
							show_data();
						}
					//	show_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});
}

}

function change_bom_status(bom_id,bom_status)
{
	//alert(bom_id);
	//alert(bom_status);
	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "change_bom_status",bom_id:bom_id,bom_status:bom_status},
		success: function(response)
		{
			//alert(response);
			load_bom_datatable();
			//$('#actual_qty').val($('#sel_product_qty').val());
			//Unloading();
		}
	});
}

function add_bom_group()
{
	//alert('hii');
	if(!$('#sel_grp_id').val())
	{
		toastr.error("SELECT GROUP", "ERROR");
	}
	else
	{
		$('#add_bom_grp_modal').modal('show');
		//generate_report() ;
	}
}

$(document).ready(function() {
	//load_zone_datatable();
	// validate vendor add form on keyup and submit
	$("#grp_add").validate({
		rules: {
			grp_name: {
				required: true
			}
		},
		messages: {
			grp_name: {
				required: "Enter Group Name"			
			}
		}
	}); 
	
});
$("#grp_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#grp_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		grp_name: $("#grp_name").val(),
		grp_model: $("#grp_model").val(),
		mode:'Add_grp',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+production_domain+'app/bom/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			var data = JSON.parse(response);
			var responsevalue=data.res;
			if(responsevalue.trim() == '1') {
				Unloading();
				toastr.success("GROUP ADDED SUCCESSFULLY", "SUCCESS");	
				window.location=root_domain+'customer_list';
			}
			else if(responsevalue.trim() == '2') {
				toastr.success("GROUP ADDED SUCCESSFULLY", "SUCCESS");
				$("#add_bom_grp_modal").modal("hide");
				$('#sel_grp_id').append('<option value='+data.bg_id+'>'+data.bg_name+'</option>');	
				$("#sel_grp_id").trigger('change')
				$('#sel_grp_id').select2("val",data.bg_id);
				Unloading();
			}
			else if(responsevalue.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				$("#bs-add_bom_grp_modal-modal-lg").modal("hide");
				$('#sel_grp_id').trigger('reset');
				Unloading();				
			}
			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			//console.log(textStatus, errorThrown);
		}
	});
	
});

function alloc_process(product_id,product_name){
//	alert(product_name);
if(product_id) {
	$("#alloc_process_modal").modal("show");
	$("#alloc_product_id").html(product_name);
	$("#process_product_id").val(product_id);
	show_process_pro(); 
}

}

function add_procecss_product() {
	
	var process_id=$("#process_id").val();
	var pr_make_time=$("#pr_make_time").val();
	var process_product_id=$("#process_product_id").val();
	var eid=$("#eid").val();
	
	if($("#process_id").val()==""){
		toastr.warning("Select Process", "ERROR");
		$("#process_id").select2('focus');
		return false;
	}
	else if($("#pr_make_time").val()==""){
		toastr.warning("Enter Making Time.", "ERROR");
		$("#pr_make_time").focus();
		return false;
	}
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode:"add_product_process",edit_id:$("#edit_id1").val(),process_id:$("#process_id").val(),pr_make_time:pr_make_time,product_id:process_product_id,bom_id:eid },
		//contentType: false,
		//  processData:false,
		success: function(response)
		{
			//alert(response);
			// console.log(response);
			var resp = JSON.parse(response);
			if(resp.res=='1'){
				$("#process_id").select2("val","");
				$("#process_type_id").select2("val","");
				$("#pr_make_time").val("");
				$("#edit_id1").val('');
				$("#addrow").val("Add");
				$("#addrow").val("Add");
				show_process_pro(); 
				Unloading(); 
			}
			else if(resp.res=='-1'){
				toastr.info("Duplicate Record Found", "ERROR");
				show_process_pro(); 
				Unloading(); 
			}
			else{
				toastr.warning("SOMETHING WENT WRONG!!!", "ERROR");
				show_process_pro(); 
				Unloading(); 
			}
		}
	});
}

function show_process_pro(){
	var product_id=$("#process_product_id").val(); 
	var eid=$("#eid").val(); 
	//alert(product_id);
	$("#req-pro-table").dataTable({
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
		"sAjaxSource": root_domain+production_domain+'app/bom/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "show_product_process" },{ "name": "product_id", "value": product_id },{ "name": "bom_id", "value": eid } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function edit_product_pro(pro_id) { 
	Loading(true);
	$.ajax({

		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "edit_product_pro", bp_id : pro_id },
		//contentType: false,
		//processData:false,
		success: function(resnse)
		{
			//console.log(resnse);
			var resp = jQuery.parseJSON(resnse); 
			//alert(resp.p_process_id);
			$("#process_type_id").select2("val",resp.process_type_id);
			get_all_process(resp.process_type_id);
			$("#process_id").select2("val",resp.p_process_id);
			$("#pr_make_time").val(resp.pr_make_time);
			$("#edit_id1").val(pro_id);
			$("#addrow").val("Update"); 
			Unloading();
		}
	});	 
}
function goBack() {
	window.history.back();
}

function delete_product_pro(pro_id)  {
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { mode : "delete_product_pro",  bp_id : pro_id },
			success: function(resnse)
			{
				if(resnse.trim() == "1") {
					toastr.success("DELETED SUCCESSFULLY", "SUCCESS");
					show_process_pro();
					Unloading();
				}
				else if(resnse.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}

function get_all_process(ptype)
{
	//alert(ptype);
	Loading(true);
	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "get_process", p_id : ptype },
		success: function(resnse)
		{
			//$('#process_id').html("");
			//$('#process_id').select2("val","");
			$('#process_id').html(resnse);
			Unloading();
			//alert(resnse);
		}
	});	
}

function get_main_product(pro)
{
	/*  var url      = window.location.href;  
	var arr=url.split('/');

	if(arr[arr.length - 2] != "bom_add"){
		window.location= root_domain+production_domain+'bom_edit/' + pro;
		return false;
	}*/
	$('#main_product').val(pro);
	
	$('#sel_bom_version_id').val('');  

}

function update_visible(pid)
{
	if($('#chkv'+pid).is(":checked"))
	{
		
		var v_status='1';
		//alert("yse");
	}
	else
	{
		var v_status='0';
		//alert("no");
	}
	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "update_bom_visibility", pid : pid , v_status:v_status },
		success: function(resnse)
		{
			
		}
	});
	//alert(pid);
}

function get_bom_by_product(pid)
{
	//alert(pid);
	var planning_id=$('#planning_id').val();
	//alert(planning_id);
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "get_bom_by_product", pid : pid , planning_id: planning_id },
		success: function(resnse)
		{
			//alert(resnse);
			$('#product_bom_table').html(resnse);
		}
	});
}

function get_bom_id_by_product(pid)
{
	//alert(pid);
	var planning_id=$('#planning_id').val();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "get_bom_id_by_product", pid : pid,planning_id:planning_id },
		success: function(resnse)
		{
			//alert(resnse);
			var data=JSON.parse(resnse);
			$('#pl_bom_id').val(data.bom);
			$('#pl_qty_id').val(data.qty);
			$('#pqty').html(data.qty);
		}
	});
}


function load_product_detail(pro_id) {

	if(pro_id > 0){
		$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "load_productdata",eid :pro_id },
		success: function(response)
		{
			var resp = jQuery.parseJSON(response); 
				// console.log('---------------');
				// console.log(resp);
				//alert(resp.m_type_density);
				$('#p_bom_id').val(resp.bom_id);
				

				$('#product_conv_unit_name').val(resp.conv_unit_name);
				$('#product_conv_unit').val(resp.product_conv_unit);
				$('#product_conv_qty').val(resp.product_conv_qty.trim());
				$('#product_conv_qty_hide').val(resp.product_conv_qty.trim());

				$('#product_base_unit_name').val(resp.base_unit_name);
				$('#product_base_unit').val(resp.product_base_unit);
				$('#product_base_qty').val(resp.product_base_qty.trim());
				$('#product_base_qty_hide').val(resp.product_base_qty.trim());

				

				$('#product_spec_hid').val(resp.product_specification);
				$('#product_density').val(resp.m_type_density);
				

				/* $('#product_base_unit').val(resp.product_base_unit);
				$('#product_act_qty').val(resp.product_base_qty);
				$('#product_spec_act_qty').val(resp.product_base_qty);
				$('#product_qty').val(resp.product_conv_qty);
				$('#product_spec_hid_qty').val(resp.product_conv_qty);
				$('#product_uom').val(resp.product_conv_unit); */
				
				if(resp.product_specification!=0)
				{
					
					if($("#edit_id").val()==""){
						$('#get_spec_div').show();
						$('#get_spec_div').empty().prepend(resp.product_specification_code);
						get_ms_kg();

					/*$('#product_width').val('1');
					$('#product_height').val('1');
					$('#product_thickness').val('1');*/
					$('#product_kg').val('');
				}
				
			}
			else
			{

				$('#get_spec_div').hide();

					/*$('#product_width').val('1');
					$('#product_height').val('1');
					$('#product_thickness').val('1');*/
					$('#product_kg').val('');
				}
				
				
			}
		});

	}
}
/*
Code By Umair: 31-05-2021
Comment : Below Code is use for product specification dynamically
START
*/
function get_ms_kg_old()
{
	var product_width=Number($('#product_width').val());
	var product_height=Number($('#product_height').val());
	var product_thickness=Number($('#product_thickness').val());
	var product_density=Number($('#product_density').val());
	
	var total=(product_width/1000)*(product_height/1000)*(product_thickness/1000)*product_density;
	
	$('#product_kg').val(total.toFixed(2));
	//alert(total);
	
}
/*$(document).on('keyup','.get_ms_kg', function(){
	get_ms_kg();
	var field_val = $(this).val();
	var field_id = $(this).attr('data-parameter');
	var msid = $(this).attr('data-msid');

	var values = [];
	$('.get_ms_kg').each(function(){
	    values.push({ name: this.name, value: this.value }); 
	});
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "get_product_specification_cal", values : values, msid : msid },
		success: function(response)
		{
			$('#product_kg').val(response);
		}
	});
});*/
function get_ms_kg(){
	var msid = $('#msid').val();
	var values = [];
	$('.get_ms_kg').each(function(){
		values.push({ name: this.name, value: this.value }); 

	});
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "get_product_specification_cal", values : values, msid : msid },
		success: function(response)
		{
			$('#product_kg').val(response);
		}
	});
}
/*
Code By Umair: 31-05-2021
Comment : Below Code is use for product specification dynamically
END
*/

function get_conv_qty()
{
	var product_qty=Number($('#product_act_qty').val());
	var product_base_unit=Number($('#product_base_unit').val());
	var product_uom=Number($('#product_uom').val());
	var product_kg=Number($('#product_kg').val());
	var product_spec_hid=Number($('#product_spec_hid').val());
	
	if(product_spec_hid>0)
	{
		var total=product_qty*product_kg;
		
		$('#product_qty').val(total.toFixed(3));
		//alert(total);
	}
	else
	{
		$('#product_qty').val(product_qty);
	}
}

function set_kg_to_qty()
{
	//alert('hello')
	
	var product_qty=$('#product_qty').val();
	var product_kg=$('#product_kg').val();
	var product_id=$('#product_id').val();
	
	//alert(product_kg);
	
	if($('#set_kg').is(":checked"))
	{
		$('#product_base_qty').val(product_kg);
		$('#product_base_qty_hide').val(product_kg);
		$('#product_conv_qty').val(product_kg);
		$('#product_conv_qty_hide').val(product_kg);
		
	}
	else
	{
		load_product_detail(product_id);
	}
}


function get_conv_qty_bom()
{
	var product_qty=Number($('#product_act_qty').val());
	var product_base_unit=Number($('#product_base_unit').val());
	var product_uom=Number($('#product_uom').val());
	var product_kg=Number($('#product_spec_hid_qty').val());
	var product_spec_hid=Number($('#product_spec_hid').val());
	var product_spec_hid_qty=Number($('#product_spec_hid_qty').val());
	var product_spec_act_qty=Number($('#product_spec_act_qty').val());
	
	if(product_spec_hid>0)
	{
		var total=product_qty*product_kg;
		
		$('#product_qty').val(total.toFixed(3));
		//alert(total);
	}
	else
	{
		var new_pr=product_qty*product_spec_hid_qty/product_spec_act_qty;
		$('#product_qty').val(new_pr);
	}
}


/* Sanat ::  Change function and add product version ::  05-08-21 */
function check_duplicate(pro_id,bom_version_id,type)
{
	// alert(pro_id);
	if(pro_id===""){

		toastr.warning("Select Product", "ERROR");
		$("#sel_product_id").select2('focus');
		return false;
	}else  if(bom_version_id===""){
		toastr.warning("Select Product Version", "ERROR");
		$("#sel_product_id").select2('focus');
		return false;
	}

	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "check_duplicate", pro_id : pro_id,bom_version_id:bom_version_id },
		success: function(resnse)
		{
			//alert(resnse);
			//var data=JSON.parse(resnse);
			if(resnse>=1)
			{
				if(type == "copy"){
					// $('#copy_bom_duplicate').show();
					// $('#copy_save').prop('disabled', true);
				}else{
					// $('#bom_duplicate').show();

					// $(':input[type="submit"]').prop('disabled', true);
				// $('#copy_save').prop('disabled', true);
				// $('#copy_version').prop('disabled', false);
			}


		}
		else
		{
			if(type == "copy"){
				$('#copy_bom_duplicate').hide();
					// $('#copy_save').prop('disabled', false);
				}else{
					$('#bom_duplicate').hide();
					// $(':input[type="submit"]').prop('disabled', false);
				// $('#copy_save').prop('disabled', false);
				// $('#copy_version').prop('disabled', true);
			}
		}
	}
});

}

function get_bom_id(pro_id,bom_version_id)
{
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "get_bom_id", pro_id : pro_id, bom_version_id:bom_version_id },
		success: function(resnse)
		{
			if(resnse>=1)
			{

				$('#bom_id').val(resnse);
				$('#parent_id').val(resnse);
				// console.log('bom id ->' + resnse)
			}
		}
	});
}


function load_product_data()
{
	var product_id=$("#sel_product_id").val();

	/* Sanat :: Added bom version -  05-08-2021 */
	var bom_version_id=$("#sel_bom_version_id").val();
	//alert(product_id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "load_product_data",  product_id : product_id, bom_version_id:bom_version_id},
		success: function(response)
		{

				//console.log(response);
				var data = jQuery.parseJSON(response);
				//$("#product_type").select2("val",data.product_type);
				$("#base_unit").val(data.product_base_unit);
				$("#base_unit_name").val(data.base_unit_name);
				if(alloted!=1){
					$("#base_qty").val((data.product_base_qty).trim()).trigger('onkeyup');
					$("#conv_qty").val((data.product_conv_qty).trim());

				}
				
				$("#conv_unit").val(data.product_conv_unit);
				$("#conv_unit_name").val(data.conv_unit_name);
				$("#sel_product_id").select2('data', { id:product_id, text: data.product_name});

				
				$("#bom_id").val(data.bom_id);


				// $("#bom_unit_qty").val(data.product_base_qty);
				$("#drawing_id").val(data.drawing_number);
				$("#product_drawing_id").val(data.drawing_id).trigger('change');
				$("#product_revision_id").val(data.revision_id);
				
				$("#revision_id").val(data.revision_id).trigger('change');

			// $("#sel_bom_version_id").val(data.bom_version_id).trigger('change');

			/* End :: Sanat added -  02-08-2021 */

			

			if(alloted==1){
				show_alloted_data();
			}else{
				show_data();
			}

				//show_data();
				Unloading();
			}
		});
}
function convert_qty(type){
	var base_qty=$("#base_qty").val();
	var conv_qty=$("#conv_qty").val();
	var product_id=$("#sel_product_id").val();
	
	if(product_id){
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { mode : "convert_qty1",  type : type,base_qty:base_qty,conv_qty:conv_qty,product_id:product_id},
			success: function(response)
			{
				//alert(type);
				if(type===1){
					$("#conv_qty").val(response.trim());
				}else if(type===2){
					$("#base_qty").val(response.trim()).trigger('onkeyup');
				}else{
					$("#base_qty").val(response.trim()).trigger('onkeyup');
					$("#conv_qty").val(response.trim());
				}
			}
		});

	}else{
		toastr.warning("Select Product First", "WARNING");
		$("#base_qty").val("1");
		$("#conv_qty").val("1");
	}
}
function product_convert_qty(type){

	if(type==2){
		var conv_qty_hide=$("#product_conv_qty").val();
		var s=parseFloat(conv_qty_hide);
		results = s.toFixed(3);

		var	num=$("#product_conv_qty_hide").val();
		var d=parseFloat(num);
		resultb = d.toFixed(3);

		if(resultb===results){
			return false;
		}
		var product_base_qty_hide=$("#product_base_qty_hide").val();
	}else{
		var base_qty_hide=$("#product_base_qty").val();
		var d=parseFloat(base_qty_hide);
		resultb = d.toFixed(3);
		
		var base_qty_hidess=$("#product_base_qty_hide").val();
		var s=parseFloat(base_qty_hidess);
		results = s.toFixed(3);

		if(resultb===results){
			return false;
		}
		var conv_qty_hide=$("#product_conv_qty").val();
	}
	
	var base_qty=$("#product_base_qty").val();
	var conv_qty=$("#product_conv_qty").val();
	
	//var base_qty_hide=$("#product_base_qty_hide").val();
	//var conv_qty_hide=$("#product_conv_qty_hide").val();
	
	//var base_qty=$("#product_base_qty").val();
	
	//var conv_qty=$("#product_conv_qty").val();
	var product_id=$("#product_id").val();
	
	if(product_id){
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { mode : "convert_qty",  type : type,base_qty:base_qty_hide,conv_qty:conv_qty_hide,product_id:product_id},
			success: function(response)
			{
				
				var arr = jQuery.parseJSON(response);			
				//arr.show_qty
				//arr.hide_qty
				//alert(type);
				//alert(arr.show_qty);
				//alert(arr.hide_qty);
				console.log(arr);
				if(type===1){
					$("#product_base_qty_hide").val(base_qty.trim());
				}else if(type===2){
					$("#product_conv_qty_hide").val(conv_qty.trim());
				}
				
				if(type===1){
					$("#product_conv_qty").val((arr.show_qty).trim());
					$("#product_conv_qty_hide").val(arr.hide_qty);

				}else if(type===2){
					$("#product_base_qty").val((arr.show_qty).trim());
					$("#product_base_qty_hide").val(arr.hide_qty);				
					
				}else{
					$("#product_base_qty").val((arr.show_qty).trim());
					$("#product_base_qty_hide").val(arr.hide_qty);
					$("#product_conv_qty").val((arr.show_qty).trim());
					$("#product_conv_qty_hide").val(arr.hide_qty);
				}
			}
		});

	}else{
		toastr.warning("Select Product First", "WARNING");
		$("#product_base_qty").val("1");
		$("#product_base_qty_hide").val("1");
		$("#product_conv_qty").val("1");
		$("#product_conv_qty_hide").val("1");
	}
}

function load_product_types(){

	var product_type = $("#product_id option:selected").attr("data-product_type");

	if(product_type){
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { mode : "load_product_types",  product_type : product_type},
			success: function(response)
			{

				$('#product_type').empty().append(response);
				$("#product_type").select2({
					width: '100%'
				});


			}
		});
	}
}
function open_copy_bom_model(bom_id){
	if(bom_id === undefined){
		bom_id = $("#bom_id").val()
	}
	if($("#sel_product_id").val()===""){

		toastr.warning("Select Product Name", "ERROR");
		$("#sel_product_id").select2('focus');
		return false;
	}
	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : 'open_copy_bom_model',bom_id:bom_id},
		success: function(data){
			$('#preview_copy_bom_modal').modal('show');
			$('#mod_per_div_sec1').html(data);
			var testData = product_load();
			load_cat_product('copy_sel_product_id',testData);
			// $("#copy_sel_product_id").select2({
			// 	width: '100%',
			// });	
			$("#copy_sel_product_version").select2({
				width: '100%',
			});	

			Unloading();
		}		
	});

}
function copy_bom(){

	if($("#copy_sel_product_id").val()===""){
		toastr.warning("Select Product Name", "ERROR");
		$("#copy_sel_product_id").select2('focus');
		return false;
	}
	if($("#copy_sel_product_version").val()===""){
		toastr.warning("Select Product Version", "ERROR");
		$("#copy_sel_product_version").select2('focus');
		return false;
	}
	var sel_product_id=$("#copy_sel_product_id").val();
	var sel_product_version=$("#copy_sel_product_version").val();
	var bom_id=$("#bom_id").val();

	var sbom_id=$("#sbom_id").val();
	var bom_version=$("#sel_bom_version_id").val();
	var product_id=$("#sel_product_id").val();

	if(sel_product_id!=""){
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { 
				mode : 'copy_bom',
				sel_product_id:sel_product_id,
				bom_id:bom_id,
				sbom_id:sbom_id,
				bom_version_id:sel_product_version,
				product_id: product_id,
				bom_version : bom_version
			},
			success: function(data){
					//$('#mod_per_div_sec1').html(data);
					var arr = jQuery.parseJSON(data);			
					if(arr.msg == '1') {
						sessionStorage.setItem("selected_version_id", bom_version);
						toastr.success("BOM COPY SUCCESSFULLY", "SUCCESS");
						// window.location='';
						$('#sel_product_id').trigger('change');
						return false;
					}
					else if(arr.msg == '0') {
						toastr.warning("SOMETHING WRONG", "ERROR")

					}


					Unloading();
				}		
			});
		$('#preview_copy_bom_modal').modal('hide');
	}else{
		toastr.warning("Select Product First", "WARNING");
	}
}


/*
	Code By Sanat : 28-07-2021
	Comment : Bom version and add process functionality
	START
	*/

	function get_version_series_no(){

		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { mode : "get_version_series_no" },
			success: function(resp){

				$('#bom_srno').val(resp);	
				$('#bom_version_no').val('BOM-VER/'+resp);	

			}		
		});	
	}


function get_product_details(){
	var product_id = $("#sel_product_id").val();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { mode : "get_product_details",product_id:product_id },
			success: function(resp){
				
				var data = jQuery.parseJSON(resp);
				// console.log(data.product_base_qty)
				
				$('#bom_unit_qty').val(data.product_base_qty).trigger('change');	
				$('#bom_unit_name').val(data.base_unit_name);	
				$('#bom_unit').val(data.product_base_unit);	
				$('#bom_conv_qty').val(data.product_conv_qty);	
				$('#bom_conv_unit_name').val(data.conv_unit_name);	
				$('#bom_conv_unit').val(data.product_conv_unit);	
				
			}		
		});	
	}

	function load_bom_version_datatable()
	{

		var sel_product_id= $("#sel_product_id").val();
		
		var bom_type=$('input[name=bom_type_opt]:Checked').val();

		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { mode : "load_bom_version_datatable",sel_product_id:sel_product_id,bom_type:bom_type },
			success: function(data){
			//console.log(data);
			$('#bom_versiondata').html(data);


			if($('#fil_bom_version_tbl tr.no-data').length > 0){
				$("#addprocess").hide();
			}else{
				$("#addprocess").show();
			}

			if($('#sel_bom_version_id').val()==""){
				load_version_bom_data($('.defaultbom').attr('data-version'));
			}else{
				load_version_bom_data($('#sel_bom_version_id').val());	
			}
			
			Unloading();
		}		
	});
	}


	function show_bom_version_form(){
		if($("#sel_product_id").val()===""){
			toastr.warning("Select Product Name", "ERROR");
			$("#sel_product_id").select2('focus');
			return false;
		}
		get_version_series_no();
		get_product_details();
		$("#row_bom_version").show(200);
		$("#add_version").hide(200);
		$("#save_version").show(200);
		$("#cancel_version").show(200);
		$('#revision_id').val($("#product_revision_id").val()).trigger('change');


	}

	function hide_bom_version_form(){
		$("#row_bom_version").hide(200);
		$("#add_version").show(200);
		$("#save_version").hide(200);
		$("#cancel_version").hide(200);
		$('#version_name_req').hide();
		$('#bom_version_id').val('');

	// form reset 
}

function load_version_bom_data(bom_version_id){
	var product_id =  $('#sel_product_id').val();
	
	$('.trversion').removeClass('sel_bom_version');
	if(bom_version_id != ""){
		$('#fieldtr_'+ bom_version_id).addClass('sel_bom_version');
	}
	
	$("#sel_bom_version_id").val(bom_version_id).trigger('change');

	if((bom_version_id !== undefined) && (product_id != "" && bom_version_id !="")){
		check_duplicate(product_id,bom_version_id);
		load_product_data();
	}

	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "get_bom_version_data",  bom_version_id : bom_version_id},
		success: function(response)
		{
			
			if((response.trim() !== "null") && (response.trim()!="")){
				var data = jQuery.parseJSON(response);
				if(alloted !=1){
					$("#base_qty").val((data.bom_unit_qty).trim()).trigger('onkeyup');

				}

			}

				//Unloading();

				
			}
		});

}

function get_revision_data(drawing_id)
{
	
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "get_revision_data",drawing_id:drawing_id},
		success: function(data){
			var arr = jQuery.parseJSON(data);
			$('#revision_id').empty().append(arr.revision_id);
			$("#revision_id").select2({
				width: '100%'
			});	

			Unloading();
		}		
	});
}


function save_bom_version(){
	if($('#version_name').val()==""){
		$('#version_name_req').show();
		return;
	}

	else{
		$('#version_name_req').hide();
	}
	Loading();
	
	$.ajax({
		
		url: root_domain+production_domain+'app/bom/',
		type: "POST",
		data: { 
			mode : "check_version_name",
			version_name: $('#version_name').val(),
			product_id: $("#sel_product_id").val(),
			bom_version_id : $("#bom_version_id").val()
		},
		
		success: function(response)
		{
			Unloading();

			if(response >0) {
				
				toastr.warning("VERSION NAME ALREADY EXISTS ", "ERROR");
				$("#version_name").val('');
				$("#version_name").focus();
				
				return false;
			}else{
				if($("#bom_unit_qty").val() == "" || $("#bom_unit_qty").val() < 1){
						toastr.warning("PLEASE ENTER BOM QTY", "ERROR");
						return;
				}
				var is_default_bom = 0;
				var bom_active_status = 0;
				var bom_type=$('#bom_type').val();

				if($("#bom_active_status").prop('checked') == true){
					bom_active_status = 1;
				}
				if($("#is_default_bom").prop('checked') == true){
					is_default_bom = 1;
				}

				var product_id = $("#sel_product_id").val();
				var bom_version_id = $("#bom_srno").val();
				Loading()
				$.ajax({

					url: root_domain+production_domain+'app/bom/',
					type: "POST",
					data: { 
						mode : "add_bom_version",
						version_name: $('#version_name').val(),
						bom_version_no: $('#bom_version_no').val(),
						product_id : $("#sel_product_id").val(),
						drawing_id: $('#product_drawing_id').val(),
						revision_id: $('#revision_id').val(),
						bom_version_date : $("#bom_date").val(),
						bom_version_id : $("#bom_version_id").val(),
						bom_active_status: bom_active_status,
						is_default_bom: is_default_bom,
						bom_unit_qty : $("#bom_unit_qty").val(),
						base_unit : $("#bom_unit").val(),
						base_qty: $("#base_qty").val(),
						conv_unit: $("#conv_unit").val(),
						conv_qty: $("#conv_qty").val(),
						bom_id : $("#bom_id").val(),
						conversation_factor : $("#conversation_factor").val(),
						bom_type : bom_type

					},

					success: function(response)
					{

			//console.log(response);	
			var arr = jQuery.parseJSON(response);
			
			if(arr.msg == '1') {
				Unloading();
				toastr.success("BOM VERSION ADDED SUCCESSFULLY", "SUCCESS");
				load_bom_version_datatable();
				hide_bom_version_form();
				get_version_series_no();
				$("#version_name").val('');
				$('#bom_active_status').prop('checked', true);
				$('#is_default_bom').prop('checked', false);
				$('#revision_id').val(null).trigger('change');
				$('#bom_type').val('0').trigger('change');

				direct_show_product_process(product_id,bom_version_id,"");


			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			
			else if(arr.msg == 'update'){	
				toastr.success("BOM VERSION UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				$("#version_name").val('');
				hide_bom_version_form()
				$('#bom_active_status').prop('checked', true);
				$('#is_default_bom').prop('checked', false);
				$('#revision_id').val(null).trigger('change');
				load_bom_version_datatable();
				get_version_series_no();
			}
			
		}		
	});
			}

			
		}		
	});


}

/*$('#sel_bom_version_id').on('change',function(){
	if($(this).val()==""){
		$("#addprocess").hide();
	}else{
		$("#addprocess").show();
	}
});*/
function edit_bom_version(bom_version_id){

	var product_id=$("#sel_product_id").val();

	Loading();
	show_bom_version_form();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "get_bom_version_data",  bom_version_id : bom_version_id},
		success: function(response)
		{
			// console.log(response);
			var data = jQuery.parseJSON(response);

			$("#drawing_id").val(data.drawing_number);
			$("#product_drawing_id").val(data.drawing_id).trigger('change');
			$("#product_revision_id").val(data.revision_id);

			$("#bom_srno").val(data.bom_version_id);
			$('#bom_version_no').val(data.bom_no);
			$("#bom_version_id").val(data.bom_version_id);
			$("#version_name").val(data.version_name);
			$("#bom_date").val(data.bom_version_date);

			$("#bom_unit_qty").val(data.bom_unit_qty);
			$("#bom_unit").val(data.bom_unit);
			$("#bom_conv_unit").val(data.bom_conv_unit);
			$("#bom_conv_qty").val(data.bom_conv_qty);
			$("#bom_unit_name").val(data.base_unit_name);
			$("#bom_conv_unit_name").val(data.conv_unit_name);
			
			$('#bom_type').val(data.bom_type).trigger('change');
			
		   
			if(data.is_default_bom == '1'){
				$('#is_default_bom').prop('checked', true);
				
			}else{
				$('#is_default_bom').prop('checked', false);
			}
			if(data.bom_active_status== '1'){
				$('#bom_active_status').prop('checked', true);
			}else{
				$('#bom_active_status').prop('checked', false);
			}

			Unloading();

			setTimeout(function(){ 
				$("#revision_id").val(data.revision_id).trigger('change');
			}, 500);


		}
	});

	
}

function delete_bom_version(id,table,whereid,bom_id)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();

		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { mode : "check_version_used_in_other", eid:id, table:table, whereid:whereid,bom_id:bom_id },
			success: function(response)
			{
				
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {  //  used in other 
					toastr.info("BOM IS ALREADY IN USED", "INFO");
					show_bom_used_in_list(id,bom_id);
				}else if(response.trim() == "0"){
					check_bom_version_is_default(id,table,whereid,bom_id);
				}
				Unloading();
			}
		});

	}
}

function show_bom_used_in_list(bom_version_id,bom_id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "show_bom_used_in_list", bom_version_id:bom_version_id, bom_id:bom_id },
		success: function(response)
		{
			$("#div_bom_used").empty().html(response);
			$("#bom_in_used_list_modal").modal('show');
			Unloading();
		}
	});
}

function check_bom_version_is_default(bom_version_id,table,whereid,bom_id){
	Loading();
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { mode : "check_default_bom_version", bom_version_id:bom_version_id},
			success: function(response)
			{
				
				if(response.trim() == '1'){
				
					show_bom_version_list_modal(bom_version_id,table,whereid,bom_id);
				}else{
					final_delete_bom_version(bom_version_id,table,whereid,bom_id);
				}
				Unloading();
			}
		});
}

function show_bom_version_list_modal(bom_version_id,table,whereid,bom_id){

	var product_id = $("#sel_product_id").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { 
			mode : "get_bom_version_list", 
			bom_version_id:bom_version_id, 
			table:table, 
			whereid:whereid, 
			bom_id:bom_id,
			product_id:product_id 
		},
		success: function(response)
		{
			$("#default_bom_data").empty().html(response);
			$("#default_bom_version_set_modal").modal('show');
			Unloading();
		}
	});
}

function set_default_bom(bom_version_id,table,whereid,bom_id){


	var default_version = $("input[name=opt_bom_version]:checked").val();
	
	if (typeof default_version === "undefined") {
		toastr.warning("PLEASE SELECT VERSION", "ERROR");
		return false;
	}
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { 
			mode : "set_bom_default_version", 
			bom_version_id:bom_version_id, 
			table:table, 
			whereid:whereid, 
			bom_id:bom_id,
			product_id: $("#sel_product_id").val(),
			default_version:default_version 
		},
		success: function(response)
		{
			Unloading();
			var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					final_delete_bom_version(bom_version_id,table,whereid,bom_id);
				}else{
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
		}
	});

}

function final_delete_bom_version(id,table,whereid,bom_id){
	Loading();
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { mode : "delete_bom_version_data", eid:id, table:table, whereid:whereid, bom_id:bom_id },
			success: function(response)
			{
				Unloading();
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					$("#default_bom_version_set_modal").modal('hide');
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					setTimeout(function(){
						load_bom_version_datatable();	
						location.reload();
					},1000);
					// load_bom_version_datatable();
					location.reload();
					
				}
				else {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
}

function open_add_bom_process_model(){

	if($('#product_type').val() == ""){
		toastr.warning("Select Product Type", "ERROR");
		$("#product_type").select2('focus');
		return false;
	}

	if($("#sel_product_id").val()===""){
		toastr.warning("Select Product Name", "ERROR");
		$("#sel_product_id").select2('focus');
		return false;
	}

	if($("#product_id").val()===""){
		toastr.warning("Select Product Name", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	else if($("#product_base_qty").val()===""){
		toastr.warning("Enter Qty", "ERROR")
		return false;
	}
	else if($("#base_qty").val()===""){
		toastr.warning("Enter Qty", "ERROR");
		$("#base_qty").focus();
		return false;
	}

	var edit_id = $("#edit_id").val();

	Loading()
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { 
					mode : "check_duplicate_product_entry",
					product_id:$("#product_id").val(),
					p_bom_id:$("#p_bom_id").val().trim(),
					bom_id:$("#bom_id").val().trim(),
					bom_version_id : $('#pro_version_id').val(),
					p_bom_version_id : $('#sel_bom_version_id').val(),
					edit_id:edit_id
				},
			success: function(response)
			{
				if(response.trim() > 0){
					var r= confirm("Product Already Added. Are you want to add again ?");

					if(r) {
						final_add_field();
					}else{
						Unloading();
					}
				}else{
					final_add_field();
				}
				
			}
		});

}



function final_add_field(){
	var edit_id = $("#edit_id").val();
	var check_pr_type_process = $("#is_process_required").val();
	if(check_pr_type_process == '0'){
	// if(($('#product_type').val() == "3") || ($('#product_type').val() == "5")){
		// console.log("open_add_bom_process_model");
		if($('#pro_version_id option').length == 1){
			auto_add_product_version($("#product_id").val(),'');
			setTimeout(function(){
				add_field();
			},500);
		}else{
			add_field();
		}

	}else{


		var product_id = $("#product_id").val();
		

		if($('#pro_version_id option').length == 1){
			// console.log("open_add_bom_process_model");
			auto_add_product_version($("#product_id").val(),'');	

		}else if($("#pro_version_id").val()===""){
			toastr.warning("Select Product Version", "ERROR");
			$("#pro_version_id").select2('focus');
			return false;
		}

		var bom_version_id = $('#pro_version_id').val();
		//  check finish product not added in same product or any child product
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { 
				mode : "check_duplicate_product_validation",			
				product_id:product_id,
				bom_version_id:bom_version_id,
				bom_id : $("#bom_id").val().trim()
		},
		success: function(response)
		{
			if(response.trim()> 0){
				Unloading();
			toastr.warning("YOU CAN'T ADD THIS PRODUCT BECAUSE IT'S BOM PRODUCT OR SAME PARENT PRODUCT", "ERROR");
			Unloading();
			return false;
		}else{
			

			setTimeout(function(){

			if(edit_id!="" && edit_id > 0){
				// show_product_process(1);
				add_field();
				if(check_pr_type_process == '0'){
					direct_show_product_process(product_id,bom_version_id,edit_id);
				}
			}else{

				if(check_pr_type_process == '0'){
					add_field();
						// Loading();
						// setTimeout(function(){
						// 	Unloading();

						// },300);
						
						return false;
				}else{
					var r= confirm(" Are you want to update process ?");

				if(r) {
					bom_version_id = $('#pro_version_id').val();
					add_field();
					if(check_pr_type_process == '0'){
						direct_show_product_process(product_id,bom_version_id,edit_id);
					}
					// show_product_process(1,product_id,product_id,bom_version_id);
				}else{
					add_field();
						// Loading();
						// setTimeout(function(){
						// 	Unloading();

						// },300);
						
						return false;
						// show_product_process(0);
					}
				}

				}
			},600);
		}
		
		}
	});
		
	}
}


// process parameter

function add_process_value()
{
	var resource_id = '';
	if($("#prod_process_id").val()==="")
	{		
		toastr.warning("Select Process Name", "ERROR");
		$("#prod_process_id").select2("focus");
		return false;
	}
	/*if($("#process_rate").val()==="")
	{		
		toastr.warning("Enter Process Rate", "ERROR");
		$("#process_rate").focus();
		return false;
	}*/
	if($("#process_priority").val()==="")
	{		
		toastr.warning("Enter Process Priority", "ERROR");
		$("#process_priority").focus();
		return false;
	}
	if($("#process_type").val()==="")
	{		
		toastr.warning("Select Process Type", "ERROR");
		$("#process_type").focus();
		return false;
	}
	if($("#process_time").val()==="")
	{		
		toastr.warning("Select Process Time", "ERROR");
		$("#process_time").focus();
		return false;
	}
	if($("#process_type").val()=="1"){
		
		if($("#resource_id").val()==="" || $("#resource_id").val()==null)
		{		
			toastr.warning("Select Resource", "ERROR");
			$("#resource_id").focus();
			return false;
		}else{
			resource_id = $('#resource_id').val();
		}
	}

	if($("#process_loss").val()!=''){
		var value = $("#process_loss").val();
		if(value<0 || value>100){
			toastr.warning("LOSS value should be between 0 to 100.", "WARNING");
			return false;
		}
	}

	if($("#process_scrap_tolerance_plus").val()!=''){
		var value = $("#process_scrap_tolerance_plus").val();
		if(value<0 || value>100){
			toastr.warning("Scrap tolerance should be between 0 to 100.", "WARNING");
			return false;
		}
	}

	if($("#process_scrap_tolerance_minus").val()!=''){
		var value = $("#process_scrap_tolerance_minus").val();
		if(value<0 || value>100){
			toastr.warning("Scrap tolerance should be between 0 to 100.", "WARNING");
			return false;
		}
	}
	var product_id="";
	var bom_version_id = "";
	if($("#direct_product_id").val()==""){
		product_id = $("#product_id").val();
	}else{
		product_id = $("#direct_product_id").val();
	}

	if($("#direct_version_id").val()==""){
		bom_version_id = $("#pro_version_id").val();
	}else{
		bom_version_id = $("#direct_version_id").val();
	}
	var process_id = $("#prod_process_id").val();
	// alert(product_id)

	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { 
			mode : "add_process_value",
			edit_id:$("#edit_id").val(),
			process_id:process_id,
			process_rate:$("#process_rate").val(),
			process_priority:$("#process_priority").val(),
			product_id:product_id,
			process_type:$('#process_type').val(),
			process_time:$('#process_time').val(),
			process_opening:$('#process_opening').val(),
			process_loss:$('#process_loss').val(),
			process_scrap_tolerance_plus:$('#process_scrap_tolerance_plus').val(),
			process_scrap_tolerance_minus:$('#process_scrap_tolerance_minus').val(),
			resource_id:resource_id 

		},
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {

				// var process_id = arr.process_id;
				toastr.success("PROCESS ADDED SUCCESSFULLY", "SUCCESS");
				if($("#direct_product_id").val()==""){
					direct_show_product_process(product_id,bom_version_id,$("#edit_id").val());
				}else{
					direct_show_product_process(product_id,bom_version_id,$("#edit_id").val());
				}
				process_reset();
				var r= confirm("Are you want to add QC ?");

				if(r) {
					Unloading();
					show_qc_modal(process_id,product_id);
				}

			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")

			}else if(arr.msg == 'exist'){
				toastr.warning("PROCESS ALREADY EXISTS", "ERROR")
			}
			

			Unloading();


		}
	});
}

function process_reset(){

	$("#prod_process_id").select2("val","");
	$("#process_rate").val('');
	$("#process_priority").val('');
	$("#edit_id_process").val('')
	$("#process_type").val('');
	$("#process_time").val('');
	$("#process_sel_product_id").val('');
	/*$("#direct_product_id").val('');
	$("#direct_version_id").val('');*/

							// $("#add_process").val("Add");
							$("#resource_id").select2("val","");
							$("#process_loss").val('');
							$("#process_scrap_tolerance_plus").val('');
							$("#process_scrap_tolerance_minus").val('');
						}

						function show_product_process(show_popup)
						{
							$("#direct_product_id").val('');
							$("#direct_version_id").val('');

							$("#mask1").removeClass('hidden');

							// setTimeout(function(){ 
								
								
								var edit_id = $('#edit_id').val();
								var product_id = $("#product_id").val();
								var bom_version_id = $("#pro_version_id").val();

								$.ajax({
									type: "POST",
									url: root_domain+production_domain+'app/bom/',
									data: { 
										mode : 'get_product_process_data',
										product_id:product_id,
										bom_version_id:bom_version_id,
										edit_id : $('#edit_id').val()
									},
									success: function(data){

										$('#mod_per_div_add_process').empty();
										$('#mod_per_div_add_process').html(data);


										CKEDITOR.replace( 'process_desc', {
											enterMode: CKEDITOR.ENTER_BR
										});
										var current_number = $('.process_row').last().attr('data-cid');	

										current_number = current_number ? current_number : 0;
										var new_number = parseInt(current_number) + 1;

										$('.process_priority').val(new_number);
										$('.process_priority_label').html(new_number);
										if(show_popup){
											load_multislect_process();
											
											$(".ms-container").css('width',"100% !important");
											$('#preview_bom_add_process_modal').modal('show');
											if($("#multiple_value").val().length > 0){

												var selProcess = $("#multiple_value").val();
												// console.log(selProcess);
												const myArr = selProcess.split(",");
												$("#multiple_value").val('');
												for (const item of myArr) { // You can use `let` instead of `const` if you like
													$('#process_item').multiSelect('select', item);
												}

											}

										}else{
											bom_process_add();
										}


										$("#mask1").addClass('hidden');
									}		
								});
							// },500);

						}

						function manage_resource(type){
	if(type=='2'){
		$('.resource_label_manage').addClass('hide');
		$('.processRate_label_manage').removeClass('hide');
	}else{
		$('.resource_label_manage').removeClass('hide');
		$('.processRate_label_manage').addClass('hide');
	}
}

						function check_duplicate_process(process_id)
						{
	// console.log('check_duplicate_process');
	//alert(pro_id);
	if($("#direct_product_id").val()==""){
		var product_id = $("#product_id").val();
	}else{
		var product_id = $("#direct_product_id").val();
	}
	

	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "check_duplicate_process", product_id : product_id, process_id: process_id },
		success: function(resnse)
		{
			
			if(resnse>0)
			{
				toastr.warning("PROCESS ALREADY EXISTS", "ERROR")
				return false;

			}
			
		}
	});
}


function bom_process_add() {
/*	var counter = $("#process_item").length;


	var sel_process = [];
	$("#process_item :selected").each(function (i) {
		sel_process[i] = $(this).val();
	});

	var unsel_process = [];
	$("#process_item :not(:selected)").each(function (i) {
		unsel_process[i] = $(this).val();
	});*/

	// console.log($("#multiple_value").val());
	// return false;

		var counter = $("#process_left li").length;

/*	if(counter == 0){
		toastr.warning("PLEASE SELECT ANY ONE PROCESS", "ERROR")
		return false;
	}
*/


	var sel_process = $("#selected_process_ids").val();
	var unsel_process = $("#process_ids").val();

	if(counter == 0){
		add_field();
	}else{
		// var pro_counter = $("#process_item :selected").length;
		var pro_counter = $("#process_item").length;

		if (pro_counter == 0) {
			toastr.warning("SELECT PROCESS", "ERROR");
			return false;
		}


		var form_data = new FormData();
		var product_id = $("#product_id").val();
		var bom_version_id = $("#pro_version_id").val();

		form_data.append('mode','bom_process_add');
		form_data.append('sel_process',sel_process);
		form_data.append('unsel_process',unsel_process);
		if($('#process_sel_product_id').val() !=""){
			form_data.append('product_id',product_id);
		}else{
			form_data.append('product_id',$('#process_sel_product_id').val());
		}
		
		form_data.append('bom_id',$("#process_bom_id").val());
		form_data.append('bom_version_id',bom_version_id);
		form_data.append('multiple_value',$("#multiple_value").val());
		form_data.append('edit_id',$('#edit_id').val());

		$.ajax({		
			url: root_domain+production_domain+'app/bom/',
			type: "POST",
			data: form_data,
			contentType: false,
			cache: false,
			processData:false,		
			success: function(response)
			{
			//console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				
				$('#preview_bom_add_process_modal').modal('hide');
				toastr.success("BOM PROCESS ADDED SUCCESSFULLY", "SUCCESS");
				// if($('#process_sel_product_id').val() ==""){
					add_field();
			// }
			process_reset();
			Unloading();

		}
		else if(arr.msg == 'update') {
				// if($('#process_sel_product_id').val() ==""){
					add_field();
			// }
			process_reset();
			$('#preview_bom_add_process_modal').modal('hide');
			toastr.success("BOM PROCESS UPDATED SUCCESSFULLY", "SUCCESS");
				// add_field();

				Unloading();

			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			if(alloted==1){
				show_alloted_data();
			}else{
				show_data();
			}

			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	}

	
}

function load_product_version(product_id,type){

	if(product_id){
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { mode : "load_product_version",  product_id : product_id, bom_version_id : $("#sel_bom_version_id").val(),type : type},
			success: function(response)
			{
				if(type == 1)
				{
					$('#copy_sel_product_version').empty().append(response);
					$("#copy_sel_product_version").select2({
						width: '100%'
					});
				}else{
					$('#pro_version_id').empty().append(response);
					$("#pro_version_id").select2({
						width: '100%'
					});

				}
			}
		});
	}
}

function copy_check_duplicate(bom_version_id){
	if($("#copy_sel_product_id").val()===""){
		toastr.warning("Select Product Name", "ERROR");
		$("#copy_sel_product_id").select2('focus');
		return false;
	}
	else if(bom_version_id===""){
		toastr.warning("Select Product Version", "ERROR")
		return false;
	}

	check_duplicate($("#copy_sel_product_id").val(),bom_version_id,'copy');
}

function get_product_version_qty(product_version_id){

	if($("#product_id").val()===""){
		toastr.warning("Select Product Name", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}else if($("#pro_version_id").val()===""){
		toastr.warning("Select Product Version", "ERROR");
		$("#pro_version_id").select2('focus');
		return false;
	}

// alert($("#pro_version_id").val())
Loading();

$.ajax({
	type: "POST",
	url: root_domain+production_domain+'app/bom/',
	data: { mode : "get_bom_version_data",  bom_version_id : product_version_id},
	success: function(response)
	{
				// console.log(response);
				var data = jQuery.parseJSON(response);

				$("#product_base_qty").val((data.bom_unit_qty).trim());
				
				$("#product_conv_qty").val((data.bom_unit_qty).trim());
				$("#product_base_qty_hide").val((data.bom_unit_qty).trim());
				$("#product_conv_qty_hide").val((data.bom_unit_qty).trim());

				
				
				Unloading();
				
			}
		});
}

/*function check_direct_process(){
	if($("#sel_product_id").val()===""){
	toastr.warning("Select Product Name", "ERROR");
	$("#sel_product_id").select2('focus');
	return false;
}else if($("#sel_bom_version_id").val()===""){
	toastr.warning("Select Product Version", "ERROR");
	return false;
}
	product_id = $("#sel_product_id").val();
	bom_version_id = $("#sel_bom_version_id").val();
	check_process(product_id,bom_version_id,'');
}*/

function check_process(){
	
	var product_id = $("#sel_product_id").val();
	var bom_version_id = $("#sel_bom_version_id").val();
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "check_product_process",  product_id : product_id,bom_version_id:bom_version_id},
		success: function(response)
		{
			Unloading();
			if(response > 0)
			{
				open_add_bom_process_model();
			}else{
				toastr.warning("YOU CAN\'N ADD PRODUCT BECAUSE NO PROCESS ADDED IN MAIN PRODUCT.", "ERROR")
				return false;
			}



		}
	});

}

function auto_add_product_version(product_id,type){
	
	
	$('#revision_id').val($("#product_revision_id").val()).trigger('change');
	
	var qty = 1;
	if(type == "main"){
		qty = $("#base_qty").val()
	}else{
		qty = $("#product_base_qty_hide").val()
	}

	if(qty == "" || qty == '0'){
		qty = 1;
	}


	Loading()
	$.ajax({
		
		url: root_domain+production_domain+'app/bom/',
		type: "POST",
		data: { 
			mode : "add_bom_version",
			version_name: 'Version 1',
			bom_version_no: '',
			is_auto_add : 1,
			product_id : product_id,
			drawing_id: $('#product_drawing_id').val(),
			revision_id: $('#revision_id').val(),
			bom_version_date : $("#bom_date").val(),
			bom_version_id : '',
			bom_active_status: 1,
			is_default_bom: 1,
			bom_unit_qty : qty,
			base_unit : $("#product_base_unit").val(),
			base_qty: $("#product_base_qty").val(),
			conv_unit: $("#product_conv_unit").val(),
			conv_qty: $("#product_conv_qty").val(),
			conversation_factor : $("#conversation_factor").val(),
		},
		
		success: function(response)
		{
			//console.log(response);	
			var arr = jQuery.parseJSON(response);
			
			if(arr.msg == '1') {
				Unloading();

				if(type == "main"){
					$('#sel_bom_version_id').val(arr.bom_version_id);				
				}else{

					$('#pro_version_id').empty().append('<option value="">Choose Version</option><option value="'+ arr.bom_version_id +'">Version 1</option>');
					$('#pro_version_id').val(arr.bom_version_id).trigger('change');
				}

			}
		}

	});

}
/*
   END ::   Code by : Sanat BOM Version  28-07-2021
   */

// START JAYESH for assign bob
function bom_version_assign(pr_id,so_id,so_trans_id)
{


	var bom_version_id = $('#sel_bom_version_id').val();	
	var bom_id = $('#bom_id').val();	

	if(bom_version_id == ""){
		toastr.warning("PLEASE SELECT BOM VERSION FIRST", "ERROR");
		return false;
	}
	Loading();
	$.ajax({

		type: "POST",
		url: root_domain+production_domain+'/app/design_department_get_sales_order_details/',
		data: { mode : "assign_bom", pr_id:pr_id,so_id:so_id,so_trans_id:so_trans_id,bom_id:bom_id.trim(),bom_version_id:bom_version_id},
		success: function(response){
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				Unloading();
				toastr.success("BOM ASSIGNED SUCCESSFULLY", "SUCCESS");
				setTimeout(function(){
					window.location= root_domain+production_domain+'design_department_get_sales_order_details';

				},1000);			
			}
			else if(arr.msg == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}			
			Unloading();
		}		 
	}); 
}
function load_multislect_process(){

	$('#process_item').multiSelect({
		keepOrder: true,
		selectableHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
		selectionHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
		afterInit: function (ms) {
			var that = this,
			$selectableSearch = that.$selectableUl.prev(),
			$selectionSearch = that.$selectionUl.prev(),
			selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
			selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';

			that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
			.on('keydown', function (e) {
				if (e.which === 40) {
					that.$selectableUl.focus();
					return false;
				}
			});

			that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
			.on('keydown', function (e) {
				if (e.which == 40) {
					that.$selectionUl.focus();
					return false;
				}
			});
		},
		afterSelect: function(value, text){
			this.qs1.cache();
			this.qs2.cache();
			var get_val = $("#multiple_value").val();
			var hidden_val = (get_val != "") ? get_val+"," : get_val;
			$("#multiple_value").val(hidden_val+""+value);
		},
		afterDeselect: function(value, text){
			this.qs1.cache();
			this.qs2.cache();
			var get_val = $("#multiple_value").val();
			var new_val = get_val.replace(value, "");
			$("#multiple_value").val(new_val);
		}
		/*afterSelect: function () {
			this.qs1.cache();
			this.qs2.cache();
		},
		afterDeselect: function () {
			this.qs1.cache();
			this.qs2.cache();
		}*/
	});	

}	

function show_qc_modal(process_id,product_id){
	// alert(process_id)
	$('#qc_process_id').val(process_id);
	$('#qc_product_id').val(product_id);

	$('#qc_modal').modal('show');

	$("#param_id").select2({
		width: '100%'
	});

}

function check_base_value(str){
	if($.isNumeric(str)) {
		$('#tolerance_plus').attr('readonly', false);
		$('#tolerance_minus').attr('readonly', false);
		
	}else{
		$('#tolerance_plus').val('');
		$('#tolerance_minus').val('');
		

		$('#tolerance_plus').attr('readonly', true);
		$('#tolerance_minus').attr('readonly', true);
		
	}

}

function check_param_tolerance(value){
	if(value<0 || value>100){
		toastr.warning("Tolerance value should be between 0 to 100.", "WARNING");
		return false;
	}
}



function add_param_value()
{
	var tolerance_plus='';
	var tolerance_minus='';
	var param_unit_id='';
	if($("#param_id").val()==="")
	{		
		toastr.warning("Select Parameter", "ERROR");
		$("#param_id").select2("focus");
		return false;
	}
	
	if($("#param_value").val()==="")
	{		
		toastr.warning("Enter parameter value", "ERROR");
		$("#param_value").focus();
		return false;
	}else{
		var param_value = $("#param_value").val();
		if(Math.floor(param_value) == param_value && $.isNumeric(param_value)) {
			if($("#tolerance_plus").val()==="")
			{		
				toastr.warning("Enter tolerance value", "ERROR");
				$("#tolerance_plus").focus();
				return false;
			}
			if($("#tolerance_minus").val()==="")
			{		
				toastr.warning("Enter tolerance value", "ERROR");
				$("#tolerance_minus").focus();
				return false;
			}
			if($("#param_unit_id").val()==="")
			{		
				toastr.warning("Select unit", "ERROR");
				$("#param_unit_id").focus();
				return false;
			}
		}
	}

	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { 
			mode : "add_param_value",
			param_id:$("#param_id").val(),
			param_value:$("#param_value").val(),
			pid:$('#qc_product_id').val(),
			tolerance_plus:$('#tolerance_plus').val(),
			tolerance_minus:$('#tolerance_minus').val(),
			prod_process_id : $("#prod_process_id").val(),
			qc_process_id:$('#qc_process_id').val()
		},
		success: function(response)
		{
			if(response == '1'){
				toastr.success("QC PARAMETER ADDED SUCCESSFULLY", "SUCCESS");

			}
			$('#qc_modal').modal('hide');

			$("#param_id").select2("val","");
			$("#param_value").val('');
			$("#tolerance_plus").val('');
			$("#tolerance_minus").val('');
			$("#qc_process_id").val("");
			$("#edit_id_param").val('')
			$("#add_param").val("Add");

			$('#tolerance_plus').attr('readonly', false);
			$('#tolerance_minus').attr('readonly', false);
			
			// $('.qc_on_procduct').prop('checked', true);
			
			Unloading();
			
		}
	});
}

function check_process_loss(value){
	if(value<0 || value>100){
		toastr.warning("LOSS value should be between 0 to 100.", "WARNING");
		return false;
	}
}


function check_scrap_tolerance(value){
	if(value<0 || value>100){
		toastr.warning("SCRAP tolerance value should be between 0 to 100.", "WARNING");
		return false;
	}
}
function direct_show_product_process(product_id,bom_version_id,edit_id)
{


	$("#mask1").removeClass('hidden');

		// var product_id = $("#product_id").val();
		// var bom_version_id = $("#pro_version_id").val();

		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { 
				mode : 'get_product_process_data',
				product_id:product_id,
				bom_version_id:bom_version_id,
				edit_id : edit_id,
				direct : 1
			},
			success: function(data){

				$('#mod_per_div_add_process').empty();
				$('#mod_per_div_add_process').html(data);

				CKEDITOR.replace( 'process_desc', {
											enterMode: CKEDITOR.ENTER_BR
										});
				var current_number = $('.process_row').last().attr('data-cid');	

				current_number = current_number ? current_number : 0;
				var new_number = parseInt(current_number) + 1;

				$('.process_priority').val(new_number);
				$('.process_priority_label').html(new_number);
				
					load_multislect_process();
					//$('#process_item').multiSelect('refresh');
					$(".ms-container").css('width',"100% !important");
					$('#direct_product_id').val(product_id);
					$('#direct_version_id').val(bom_version_id);
					$('#preview_bom_add_process_modal').modal('show');
					if($("#multiple_value").val().length > 0){
						var selProcess = $("#multiple_value").val();
						// console.log(selProcess);
						const myArr = selProcess.split(",");
						$("#multiple_value").val('');
						for (const item of myArr) { // You can use `let` instead of `const` if you like
								$('#process_item').multiSelect('select', item);
							}
					}
				$("#mask1").addClass('hidden');
			}		
		});
	

}
function direct_bom_process_add(product_id,bom_version_id,edit_id) {
	/*var counter = $("#process_item").length;


	var sel_process = [];
	$("#process_item :selected").each(function (i) {
		sel_process[i] = $(this).val();
	});

	var unsel_process = [];
	$("#process_item :not(:selected)").each(function (i) {
		unsel_process[i] = $(this).val();
	});

	var pro_counter = $("#process_item :selected").length;

	if (pro_counter == 0) {
		toastr.warning("SELECT PROCESS", "ERROR");
		return false;
	}*/

	var counter = $("#process_right li").length;

	if(counter == 0){
		toastr.warning("PLEASE SELECT ANY ONE PROCESS", "ERROR")
		return false;
	}


	var form_data = new FormData();

	var sel_process = $("#selected_process_ids").val();
	var unsel_process = $("#process_ids").val();
	

	form_data.append('mode','bom_process_add');
	form_data.append('sel_process',sel_process);
	form_data.append('unsel_process',unsel_process);

	form_data.append('product_id',product_id);

	form_data.append('bom_id',$("#process_bom_id").val());
	form_data.append('bom_version_id',bom_version_id);
	form_data.append('multiple_value',$("#multiple_value").val());
	form_data.append('edit_id',edit_id);

	$.ajax({		
		url: root_domain+production_domain+'app/bom/',
		type: "POST",
		data: form_data,
		contentType: false,
		cache: false,
		processData:false,		
		success: function(response)
		{
			//console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				
				$('#preview_bom_add_process_modal').modal('hide');
				toastr.success("BOM PROCESS ADDED SUCCESSFULLY", "SUCCESS");
				
				process_reset();
				Unloading();


			}
			else if(arr.msg == 'update') {
				
				process_reset();
				$('#preview_bom_add_process_modal').modal('hide');
				toastr.success("BOM PROCESS UPDATED SUCCESSFULLY", "SUCCESS");
				
				Unloading();

			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			if(alloted==1){
				show_alloted_data();
			}else{
				show_data();
			}
			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

}
function process_error(){
	toastr.warning("YOU CAN\'N ACCESS BECAUSE NO PROCESS ADDED FOR THIS PRODUCT.", "ERROR")
	return false;
}

function copy_bom_validation(bom_id){
	if(bom_id === undefined){
		bom_id = $("#bom_id").val()
	}
	
	if($("#sel_product_id").val()===""){
		toastr.warning("Select Product Name", "ERROR");
		$("#sel_product_id").select2('focus');
		return false;
	}

	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : 'check_copy_bom_product',bom_id:bom_id},
		success: function(data){

			Unloading();

			if(data > 0){
				var r= confirm(" Product Already Added in this bom. Are want to copy other BOM in this BOM ?");

				if(r) {
					open_copy_bom_model(bom_id);
				}

			}else{
				open_copy_bom_model(bom_id);
			}
		}		
	});
}

function product_load(cond){

	var is_process_required_check = 0;
	if(cond == 'req_process'){
		is_process_required_check = 1;
	}
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	// var product_type=$("#product_type").val();
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=production_pro_type&search=bom_pro_search&is_process_required_check='+is_process_required_check;
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

$('.bom_edit').select2({
	data: product_load('req_process'),
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

function get_p_bom_id(bom_version_id)
{

	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "get_p_bom_id", product_id : $("#product_id").val(), bom_version_id:bom_version_id },
		success: function(resnse)
		{
			if(resnse>=1)
			{
				$('#p_bom_id').val(resnse);
			}
		}
	});
}

function change_base_qty(){
	var qty = $('#bom_unit_qty').val();
	if(qty != ""){
		$('#base_qty').val(qty).trigger('onkeyup');
	}
}

function bom_convert_qty(type){
	var base_qty=$("#bom_unit_qty").val();
	var conv_qty=$("#bom_conv_qty").val();
	var product_id=$("#sel_product_id").val();
	
	if(product_id){
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { mode : "convert_qty1",  type : type,base_qty:base_qty,conv_qty:conv_qty,product_id:product_id},
			success: function(response)
			{
				//alert(type);
				if(type===1){
					$("#bom_conv_qty").val(response.trim());
				}else if(type===2){
					$("#bom_unit_qty").val(response.trim()).trigger('onkeyup');
				}else{
					$("#bom_unit_qty").val(response.trim()).trigger('onkeyup');
					$("#bom_conv_qty").val(response.trim());
				}
				change_base_qty();
			}
		});

	}else{
		toastr.warning("Select Product First", "WARNING");
		$("#bom_unit_qty").val("1");
		$("#bom_conv_qty").val("1");
	}
}



function show_allocate_bom_list(bom_id){

	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "show_allocate_bom_list", bom_id : bom_id },
		success: function(resnse)
		{

			$("#allocate_bom_data").empty().html(resnse);
			$("#allocate_bom_modal").modal('show');
			Unloading();
		}
	});
}



$("body").on("click","#process_left li",function(){
	$("#process_left li").removeClass('selected');
	$("#process_right li").removeClass('selected')
	$(this).addClass('selected');

	$('#row_process_desc').hide();
	$("#process_save").show();
	$("#selected_process_id").val('');
	$("#chk_leftside_process").prop('checked',false)
});
$("body").on("click","#process_right li",function(){
   // $("#process_right li").on('click',function(e){
   	$("#process_left li").removeClass('selected');
   	$("#process_right li").removeClass('selected');
   	$(this).addClass('selected');

   	$('#row_process_desc').show();
   	$("#process_save").hide();
   	var selectedOpts = $('#process_right li.selected');
   	var process_id = selectedOpts.attr('id');
   	$("#selected_process_id").val(process_id);
   	var bom_id = $("#process_bom_id").val();
   	$("#btProcessDesc").html("Save");
   	get_process_desc(bom_id,process_id);
   	$("#chk_rightside_process").prop('checked',false)
   });
$("body").on("click","#moveRight",function(e){
   // $("#moveRight").on('click',function(e){
   	var selectedOpts = $('#process_left li.selected');
   	if (selectedOpts.length == 0) {
   		alert("Nothing to move.");
   		e.preventDefault();
   	}else{
   		selectedOpts.each(function(){ 
		   		var process_id = $(this).attr('id')
		   		var process_name = $(this).text();
		   		
		   		var html = "<li id='"+process_id+"'>" + process_name + "</li>";
		   		$('#process_right').append(html);
		   		$(this).remove();
   		   });
   		e.preventDefault();
   		updateIDs();
   		$("#chk_leftside_process").prop('checked',false)
   	}
   	
   });
$("body").on("click","#moveLeft",function(e){
     // $("#moveLeft").on('click',function(e){
     	var selectedOpts = $('#process_right li.selected');
     	console.log(selectedOpts.length);
     	if (selectedOpts.length == 0) {
     		alert("Nothing to move.");
     		e.preventDefault();
     	}else{
     		selectedOpts.each(function(){ 
		 		var process_id = $(this).attr('id')
	     		var process_name = $(this).text();
	     		var process_name = process_name.replace('+','');
	     		var html = "";
	     		html = "<li id='"+process_id+"'>" + process_name.trim() + "</li>";
	     		$('#process_left').append(html);
	     		$(this).remove();
	     		$('#row_process_desc').hide();
	     		$("#selected_process_id").val('');
	     		$("#process_save").show();
	     		$("#chk_rightside_process").prop('checked',false)
     		});
     		e.preventDefault();
     		updateIDs();
     	}
     });


function updateIDs() {
	$('#selected_process_ids').val('');
	$('#process_right li').each(function(index) {
		console.log($(this).attr('id'));
		$('#selected_process_ids').val($('#selected_process_ids').val() +  $(this).attr('id') + ",");
	});

	$('#process_ids').val('');
	$('#process_left li').each(function(index) {
		console.log($(this).attr('id'));
		$('#process_ids').val($('#process_ids').val() + $(this).attr('id') + ",");
	});
}

function save_process_desc(){
	var process_id = $("#selected_process_id").val();
	var process_bom_id = $("#process_bom_id").val();
	// var desc = $("#process_desc").val()
	var desc = CKEDITOR.instances['process_desc'].getData();
	var eid = $("#selected_desc_id").val();
	Loading();

	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "save_process_desc",process_bom_id:process_bom_id,process_id:process_id,desc:desc,eid,eid},
		success: function(response)
		{
			
			if(response.trim() == '1')
			{
				toastr.success("DESCRIPTION ADDED SUCCESSFULLY", "SUCCESS");
				$('#row_process_desc').hide();
				$("#selected_process_id").val('');
				$("#process_save").show();
				$("#btProcessDesc").html("Save");
			}else if(response.trim() == 'update') {
				toastr.success("DESCRIPTION UPDATE SUCCESSFULLY", "SUCCESS");
				$('#row_process_desc').hide();
				$("#selected_process_id").val('');
				$("#process_save").show();
				$("#btProcessDesc").html("Save");
			}
			else{
				toastr.warning("SOMETHING WRONG", "WARNING");
			}
			$("#selected_desc_id").val('');
			$("#process_right li").removeClass('selected')
			Unloading();
			
		}
	});	

}

function get_process_desc(bom_id,process_id){
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "get_process_desc",bom_id:bom_id,process_id:process_id},
		success: function(response)
		{
				/*CKEDITOR.replace( 'process_desc', {
											enterMode: CKEDITOR.ENTER_BR
										});*/
			if(response.trim() !== ""){
				var data=JSON.parse(response);
			// console.log(response);
			CKEDITOR.instances['process_desc'].setData(data.description);
			$("#selected_desc_id").val(data.id);

			$("#btProcessDesc").html("Update");
			
		}else {
			CKEDITOR.instances['process_desc'].setData("");
			$("#selected_desc_id").val('');
			$("#btProcessDesc").html("Save");
		}
		Unloading();
	}
});	
}

function select_all_left_side_process(){
	var process_left = $('#process_left li');
	if (process_left.length == 0) {
     		alert("No Process added.");
     		$("#chk_leftside_process").prop('checked',false)
     	}else{
     		if($("#chk_leftside_process").prop('checked')){
     			$("#process_left li").addClass('selected');
     		}else{
     			$("#process_left li").removeClass('selected');
     		}
     	}
}

function select_all_right_side_process(){

	var process_right = $('#process_right li');
     	if (process_right.length == 0) {
     		alert("No Process added.");
     		$("#chk_rightside_process").prop('checked',false);
     	}else{
     		if($("#chk_rightside_process").prop('checked')){
     			$("#process_right li").addClass('selected');
     		}else{
     			$("#process_right li").removeClass('selected');
     		}
     	}
}


function delete_force_bom_default_version(bom_id,bom_version_id,product_id){
	var r= confirm(" Are you sure you want to delete default bom ?");

	if(r) {
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { mode : "delete_default_bom_version",  bom_id : bom_id, bom_version_id:bom_version_id,product_id:product_id},
			success: function(response)
			{
					//console.log(response)
					if(response.trim() == "1") {
						toastr.success("BOM DELETE SUCCESSFULLY", "SUCCESS");
						Unloading();
						window.location=root_domain+production_domain+'bom_list';
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
						Unloading();
					}							
				}
			});	
	}
}
function bom_version_assign_store_order(pr_id,order_id,bom_id)
{


	var bom_version_id = $('#sel_bom_version_id').val();	
	var bom_id = $('#bom_id').val();	

	if(bom_version_id == ""){
		toastr.warning("PLEASE SELECT BOM VERSION FIRST", "ERROR");
		return false;
	}
	Loading();
	$.ajax({

		type: "POST",
		url: root_domain+production_domain+'/app/store_order_design_department/',
		data: { mode : "assign_bom", pr_id:pr_id,order_id:order_id,bom_id:bom_id.trim(),bom_version_id:bom_version_id},
		success: function(response){
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				Unloading();
				toastr.success("BOM ASSIGNED SUCCESSFULLY", "SUCCESS");
				setTimeout(function(){
					window.location= root_domain+production_domain+'store_order_design_department';

				},1000);			
			}
			else if(arr.msg == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}			
			Unloading();
		}		 
	}); 
}


function check_product_process_required(product_type){
	if(product_type == ""){
		$("#is_process_required").val(0);
		load_product(product_type);
	}else{
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { 
					mode : "check_product_process_required",
					product_type : product_type
				},
			success: function(response)
			{
				$("#is_process_required").val(response);	

				if(response == '0'){
					$(".hide_product_version").hide();
				}else{
					$(".hide_product_version").show();
				}
				load_product(product_type);	
			}
		});
	}	
}



function open_add_view_documents(bom_id,bom_version_id)
{
	$("#doc_bom_id").val(bom_id);
	$("#doc_bom_version_id").val(bom_version_id);
	
	Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/bom/',
			data: { mode : "view_document_data", bom_id : bom_id,bom_version_id:bom_version_id },
			success: function(response)
			{
				$('#documents_data_list').empty().html(response);
				view_documents(bom_id,bom_version_id);
				$("#preview_bom_document_upload").modal("show");
				Unloading();
			}
		});
	
}


function save_bom_documents(form){
	var bom_id = $("#doc_bom_id").val();
	var bom_version_id = $("#doc_bom_version_id").val();
	
	var img_len = 0;
	var img_msg = "";
	var image_name = "";
	
	image_name = $("#doc_image_name").val();
	img_msg = "ENTER DOCUMENT NAME";
	img_len = $("#dr_file")[0].files.length;
	

	if(image_name == ""){
			toastr.warning(img_msg, "ERROR");
	    return false;
	}

	if(img_len === 0){
			toastr.warning("PLEASE SELECT IMAGE", "ERROR");
	    return false;
	}
	
	var form_data = new FormData($('#frm_bom_doc')[0]);
	 // form_data.append('mode','save_drawing_image');
	 
 
//Sending form

	$.ajax({
		cache:false,
		url: root_domain+production_domain+'app/bom/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			if(response.trim() == '1') {
				Unloading();
				toastr.success("DOCUMENT ADDED SUCCESSFULLY", "SUCCESS");

				$("#doc_image_name").val('');
				$("#dr_file").val('');
				view_documents(bom_id,bom_version_id)
				
			}
			else if(response.trim() == '2') {
				toastr.warning("INVALID FILE", "ERROR");
				Unloading();
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
}

function view_documents(bom_id,bom_version_id)
{
	var id = $("#eid").val()
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : "view_document_data", bom_id : bom_id,bom_version_id : bom_version_id },
		success: function(response)
		{
			$('#documents_data_list').empty().html(response);
			Unloading();
		}
	});	
}

function delete_data_image(id,bom_id,bom_version_id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/bom/',
		data: { mode : 'delete_document', id : id},
		success: function(data){
			if(data=='1'){
				toastr.success("DOCUMENT DELETE SUCCESSFULLY", "SUCCESS");
				view_documents(bom_id,bom_version_id);
			}
			Unloading();			
		}			
	});
}


function showproduct() {
	branch_id = $('#branch_id').val();
	if (!branch_id) {
		toastr.warning("Choose Branch!!!", "ERROR");
		$('#branch_id').select2('focus');
		return false;
	}
	
	
	$('#modal-add-product').modal('show');
	$("#product_add_type").val('bom');

	//$("#ledger_name").focus();
}