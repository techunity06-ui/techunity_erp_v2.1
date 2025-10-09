
$(document).ready(function() {
	load_indiamart();
	load_trade_india();
	$("#ind_add").validate({
		rules: {
			product_id: {
				required: true			
			},
			assign_user_ids: {
				required: true
			},
			branch_id: {
				required: true			
			}
		},
		messages: {
			product_id: {
				required: "Choose Product"
			},
			assign_user_ids: {
				required: "Choose User"
			},
			branch_id: {
				required: "Choose Branch"
			}
		}
	}); 
}); 
$("#ind_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#ind_add").valid()) {
		return false;
	} 
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#save').prop('disabled', true);
	var form_data=new FormData(this);	
	
	$.ajax({
		cache:false,
		url: root_domain+crm_domain + 'app/indiamart_data_list/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");
				//window.location=root_domain+crm_domain + 'inquiry_list';
				load_inquiry_datatable();
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			else if(arr.msg == '-1') {
				toastr.info("ALREADY EXISTS", "INFO");
			}
			else if(arr.msg == 'update') {	
				toastr.success("INQUIRY UPDATED SUCCESSFULLY", "SUCCESS");		
				//window.location=root_domain+crm_domain + 'inquiry_list';
			}
			Unloading();
			//$('#inquiry_add').trigger('reset');
			$("#bs-add_ind_data").modal("hide");	
			$("#stateid").select2("val","");
			$("#product_id").select2("val","");
			$("#cityid").select2("val","");
			$("#branch_id").select2("val","");
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function load_inquiry_datatable(){
	//var status=$('input[name=approved_status]:Checked').val();
	var date=$('#rep_date').val();
	var source_id=$('#rb_id').val();
	$("#inquiry-table").dataTable({
		//Amish Soni 15-09-2020
		"bStateSave": true,

		"bAutoWidth" : true,
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
		"sAjaxSource": root_domain+crm_domain + 'app/indiamart_data_list/',
		"fnServerParams": function ( aoData ) {
			aoData.push( {"name": "mode", "value": "fetch"}, {"name": "date", "value": date}, {"name": "source_id", "value": source_id} );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function delete_inquiry(i_id) 
{
	var r= confirm(" Are you sure want to delete");
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+crm_domain + 'app/indiamart_data_list/',
			data: { mode : "delete",  i_id : i_id },
			success: function(response)
			{
				console.log(response);
				if(response.trim() == "1") {
					toastr.success("DELETE SUCCESSFULLY", "SUCCESS");
					load_inquiry_datatable();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	} 
}
function load_indiamart(type) 
{
	var date=$('#rep_date').val();
	//alert(date);
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain + 'app/indiamart_data_list/',
		data: { mode : "load_indiamart",type:type,date:date },
		success: function(response)
		{
			load_inquiry_datatable();
		}
	});	
}
function load_trade_india(type) 
{
	var date=$('#rep_date').val();
	
	//alert(date);
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain + 'app/indiamart_data_list/',
		data: { mode : "load_trade_india",type:type,date:date },
		success: function(response)
		{
			
			load_inquiry_datatable();

		}
	});	
}

function open_update(i_id){
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain + 'app/indiamart_data_list/',
		data: { mode : "preedit",i_id:i_id },
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			$("#i_id").val(i_id);
			$("#QUERY_ID").val(arr.QUERY_ID);
			$("#SENDERNAME").val(arr.SENDERNAME);
			$("#SENDEREMAIL").val(arr.SENDEREMAIL);
			$("#GLUSR_USR_COMPANYNAME").val(arr.GLUSR_USR_COMPANYNAME);
			$("#MOB").val(arr.MOB);
			$("#ENQ_ADDRESS").val(arr.ENQ_ADDRESS);
			$("#ENQ_CITY").val(arr.ENQ_CITY);
			$("#ENQ_STATE").val(arr.ENQ_STATE);
			$("#PRODUCT_NAME").val(arr.PRODUCT_NAME);
			$("#ENQ_MESSAGE").val(arr.ENQ_MESSAGE);
			if(arr.user_ids){
				$('#assign_user_ids').select2("val",(arr.user_ids).split(","));
			}

			if(arr.enable_assing_user){
				$("#cust_owner").select2("val",arr.cust_owner);
			}

			$("#product_id").select2("val",arr.product_id);
			$("#branch_id").select2("val",arr.branch_id);
			//alert(arr.stateid);
			if(arr.stateid!="0"){
				$("#stateid").select2("val",arr.stateid);
				load_city(arr.stateid,"cityid",arr.cityid);
			}
			
			
			$("#bs-add_ind_data").modal("show");
			
			/* if(arr.prostatus==="0"){
				$("#ero").hide();
				$("#ero1").hide();
			}else{
				$("#ero").show();
				$("#ero1").show();
			} */
		}
	});	
}
function load_city(parentid,control,val1)
{	
	//alert(parentid);
	$.ajax({
		type: "POST",
		url: root_domain+'app/vender/',
		data: { mode : "load_city",  id : parentid},
		success: function(responce){
			//console.log(responce);
			//alert(responce);
			$('#'+control).html(responce);
			$("#"+control).select2("val",val1);
		}
	});
	
}
function open_user_model(){
	$("#bs-user_allocate").modal("show");
}
function print_cust_label()
{
	//var user=$("#assign_user_ids").val();
	//alert(user);
	//if(user!=""){
		Loading(true);
		var custid = $("#inquiry-table input:checkbox:checked").map(function(){
			return $(this).val();
		}).toArray();
		if(custid!="")
		{
			
			$.ajax({
				type: "POST",
				url: root_domain+crm_domain + 'app/indiamart_data_list/',
				data: { mode : "print_cust_label",  cust_id:custid},
				success: function(response)
				{
					toastr.success("ADD INQUIRY SUCCESSFULLY", "SUCCESS");
					load_inquiry_datatable();
					$("#bs-user_allocate").modal("hide");
					Unloading();							
				}
			});
			
		}
		else 
		{
			toastr.warning("SELECT DATA", "ERROR");
		}
		Unloading();
	/* }else{
		toastr.warning("Please Assign User", "WARNING");
	} */
}

function get_product_cat_wise(){
	var cat = $("#cat_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain+'app/indiamart_data_list/',
		data: { mode : "load_product_cat",  cat : cat},
		success: function(responce){
			$('#product_id').html(responce);
			//$("#"+control).select2("val",val1);
		}
	});
}
