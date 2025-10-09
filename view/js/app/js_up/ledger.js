$(document).ready(function() {
	load_ledger_datatable();
	// validate vendor add form on keyup and submit
	
	show_bank_data();
	show_contact_data();
	
	$("#ledger_add").validate({
	
}); 
	
});


$("#ledger_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#ledger_add").valid()) {
		return false;
	}
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");	 
	
	var form_data=new FormData(this);
	var form_type=$('#form_type').val();
	//alert(form_data)
	
	$.ajax({
		cache:false,
		url: root_domain+'app/ledger/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);			
			var obj=jQuery.parseJSON(response);
			response=obj.res;
			if(response.trim() == '1') {
				toastr.success("LEDGER ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				$('#ledger_add').trigger('reset');
				$('#countryid').select2('val','');
				$('#stateid').select2('val','');
				$('#cityid').select2('val','');
				$('#cust_gst_reg').select2('val','');
				$('#zone_id').select2('val','');
				$('#pay_terms').select2('val','');
				$('#bill_type').select2('val','');
				$('#ledger_grp').select2('val','');
				$('#emp_zone_id').select2('val','');
				$('#bankid').select2('val','');
				$('#balance_typeid').select2('val','');
				$('#branch_id_customer').select2('val','');
				$('#branch_id_emp').select2('val','');
				
				$("#"+form_type).addClass("ledger_forms");
				//datatable.fnReloadAjax();
			}
			else if(response.trim() == '2') {
				toastr.success("LEDGER ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-example-modal-city").modal("hide");
				$('#cityid').append('<option value='+obj.cityid+'>'+obj.city_name+'</option>');
				$("#cityid").trigger('change')
				$('#cityid').select2("val",obj.cityid);
				$('#ledger_add').trigger('reset');
				Unloading();
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response.trim() == '-1') {
				toastr.warning("LEDGER WITH SAME NAME ALREADY EXIST", "ERROR")
				Unloading();
			}
			else if(response.trim() == '3') {
				toastr.success("LEDGER UPDATED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+'ledger_list';
				Unloading();
			}
			
			$('#ledger_add').trigger('reset');	
			$('#stateid').select2("val",state);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function load_ledger_datatable(){
	datatable = $("#ledger-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"bStateSave": true,
        "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('offersDataTables', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('offersDataTables'));
        },
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+'app/ledger/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}



function add_bank()
{
	if($("#bank_ac").val()==""){
		toastr.warning("Select Account Number", "ERROR");
		$("#bank_ac").focus();
		return false;
	}
	else if($("#bank_name").val()==""){
		toastr.warning("Select bank name", "ERROR");
		$("#bank_name").focus();
		return false;
	}
	
	var bank_ac=$('#bank_ac').val();
	var bank_name=$('#bank_name').val();
	var ac_name=$('#ac_name').val();
	var bank_ifsc=$('#bank_ifsc').val();
	var cust_id=$('#ledger_id').val();
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+'app/ledger/',
		data: { mode : "add_bank_name",edit_id:$("#edit_id").val(),bank_ac:$("#bank_ac").val(),bank_name:$("#bank_name").val(),ac_name:$("#ac_name").val(),bank_ifsc:$("#bank_ifsc").val(),bank_open:$("#bank_open").val(),cust_id:cust_id },
		success: function(response)
		{
			console.log(response);
			$("#bank_ac").val("");
			$("#bank_name").val("");
			$("#ac_name").val("");
			$("#bank_ifsc").val("");
			$("#bank_open").val("");
			$("#add_bank_bt").val("Add");
			Unloading();
			show_bank_data();
			
		}
	});
	
}

function show_bank_data()
{
	var form_mode=$('#mode').val();
	var cust_id=$('#ledger_id').val();
	//alert(cust_id);
	//var mode=$('#mode').val();
	//alert(cust_id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/ledger/',
		data: { mode : "load_bank_detail", cust_id:cust_id,form_mode:form_mode },
		success: function(data){
			//console.log(data);
			$('#table_bank_details').html(data);				
			Unloading();
		}		
	});
}

function edit_data_bank(id)
{
	//var form_mode=$("#jobwork_outward_add #mode").val();
	//alert(id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/ledger/',
		data: { mode : "preedit_bank",  id : id },
		success: function(response)
		{
			//alert(response);
			console.log(response);
			var data = jQuery.parseJSON(response);
			$('#bank_ac').val(data.bank_ac);
			$('#ac_name').val(data.ac_name);
			$("#bank_ifsc").val(data.bank_ifsc);
			$("#bank_open").val(data.bank_open);
			$("#bank_name").select2("val",data.b_name);
			
			//$("#outward_product_amount").val(data.outward_product_amount);
			$("#edit_id").val(id);
			$("#add_bank_bt").val("Update");
			/*if(form_mode=='Edit'){
				load_stock(data.raw_product_id,data.outward_product_qty)
			}else{
				load_stock(data.raw_product_id,0)
			}*/
			show_bank_data();
			Unloading();
		}
	});
}


function delete_data_bank(id)
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/ledger/',
				data: { mode : "delete_data_bank",  eid : id },
				success: function(response)
				{
					console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						show_bank_data();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}

					show_bank_data();					
				}
			});	
		}
	
}

//contact person details


function add_contact_person()
{
	if($("#con_name").val()==""){
		toastr.warning("Enter Name Of Person", "ERROR");
		$("#con_name").focus();
		return false;
	}
	
	var con_name=$('#con_name').val();
	var con_mobile=$('#con_mobile').val();
	var con_email=$('#con_email').val();
	var cust_id=$('#ledger_id').val();
	
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+'app/ledger/',
		data: { mode : "add_contact_person",edit_id:$("#edit_id_contact").val(),con_name:$("#con_name").val(),con_mobile:$("#con_mobile").val(),con_email:$("#con_email").val(),cust_id:cust_id },
		success: function(response)
		{
			console.log(response);
			$("#con_name").val("");
			$("#con_mobile").val("");
			$("#con_email").val("");
			$("#add_contact_bt").val("Add");
			Unloading();
			show_contact_data();
			
		}
	});
	
}


function show_contact_data()
{
	var form_mode=$('#mode').val();
	var cust_id=$('#ledger_id').val();
	//alert(cust_id);
	//var mode=$('#mode').val();
	//alert(cust_id);
	//alert(form_mode);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/ledger/',
		data: { mode : "load_contact_detail", cust_id:cust_id,form_mode:form_mode },
		success: function(data){
			//console.log(data);
			//alert(data);
			$('#table_contact_details').html(data);				
			Unloading();
		}		
	});
}

function edit_data_contact(id)
{
	
	//var form_mode=$("#jobwork_outward_add #mode").val();
	//alert(id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/ledger/',
		data: { mode : "preedit_contact",  id : id },
		success: function(response)
		{
			//alert(response);
			console.log(response);
			var data = jQuery.parseJSON(response);
			$('#con_name').val(data.cust_contact_person_name);
			$('#con_mobile').val(data.cust_contact_person_no);
			$("#con_email").val(data.cust_contact_person_email);
			
			//$("#outward_product_amount").val(data.outward_product_amount);
			$("#edit_id_contact").val(id);
			$("#add_contact_bt").val("Update");
			/*if(form_mode=='Edit'){
				load_stock(data.raw_product_id,data.outward_product_qty)
			}else{
				load_stock(data.raw_product_id,0)
			}*/
			show_contact_data();
			Unloading();
		}
	});
}


function delete_data_contact(id)
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/ledger/',
				data: { mode : "delete_data_contact",  eid : id },
				success: function(response)
				{
					console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						show_contact_data();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}
					show_contact_data();
				
				}
			});	
		}
	
}

function delete_ledger(id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/ledger/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				console.log(response);
				if(response.trim() == "1") {
					toastr.success("LEDGER DELETE SUCCESSFULLY", "SUCCESS");
					load_ledger_datatable();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}

function alloc_sold_pro(cust_id){
	if(cust_id) {
		$("#alloc_sold_pro_modal").modal("show");
		$("#alloc_cust_id").val(cust_id);
		show_sold_pro(); 
	}
}
function add_sold_pro_field() {
	var cust_id=$("#alloc_cust_id").val();
	var sold_inv_foc_date=$("#sold_inv_foc_date").val();
	
	if($("#product_id").val()==""){
		toastr.warning("Select Product Name", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	else if(!sold_inv_foc_date){
		toastr.warning("Choose FOC Date.", "ERROR");
		$("#sold_inv_foc_date").focus();
		return false;
	}
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/ledger/',
		data: { mode:"add_sold_pro_field", edit_id:$("#edit_id1").val(), product_id:$("#product_id").val(), model_id:$("#model_id").val(), sold_pro_srl_no:$("#sold_pro_srl_no").val(), sold_inv_rmrk:$("#sold_inv_rmrk").val(), sold_inv_foc_date:sold_inv_foc_date, cust_id:cust_id },
		//contentType: false,
		//  processData:false,
		success: function(response)
		{
			console.log(response);
			var resp = JSON.parse(response);
			if(resp.res=='1'){
				$("#product_id").select2("val","");
				$("#sold_inv_foc_date").val("");
				$("#sold_pro_srl_no").val('');
				$("#sold_inv_rmrk").val('');
				$("#edit_id1").val('');
				$("#addcustrow").val("Add");
				Unloading(); 
				show_sold_pro();  
			}
			else if(resp.res=='-1'){
				toastr.info("Duplicate Record Found", "ERROR");
				Unloading(); 
			}
			else{
				toastr.warning("SOMETHING WENT WRONG!!!", "ERROR");
				Unloading(); 
			}
		}
	});
}
function show_sold_pro(){
	var cust_id=$("#alloc_cust_id").val(); 
	$("#sold-pro-table").dataTable({
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
		"sAjaxSource": root_domain+'app/ledger/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "show_sold_pro" },{ "name": "cust_id", "value": cust_id } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}
function edit_sold_pro(cust_sold_pro_id) { 
	Loading(true);
	 $.ajax({
		type: "POST",
		url: root_domain+'app/ledger/',
		data: { mode : "edit_sold_pro", cust_sold_pro_id : cust_sold_pro_id },
		//contentType: false,
		//processData:false,
		success: function(resnse)
		{
			//console.log(resnse);
			var resp = jQuery.parseJSON(resnse); 
			$("#product_id").select2("val",resp.product_id);
			$("#model_id").html(resp.model_resp_html);
			$("#model_id").select2("val",resp.model_id);
			$("#edit_id1").val(cust_sold_pro_id);
			$("#sold_inv_no").val(resp.sold_inv_no);
			$("#sold_pro_srl_no").val(resp.sold_pro_srl_no);
			$("#sold_inv_rmrk").val(resp.sold_inv_rmrk);
			$("#sold_inv_date").datepicker("setDate", resp.sold_inv_date);
			$("#sold_inv_foc_date").datepicker("setDate", resp.sold_inv_foc_date);
			$("#sold_inv_rate").val(resp.sold_inv_rate);
			$("#addcustrow").val("Update"); 
			Unloading();
		}
	});	 
}
function delete_sold_pro(cust_sold_pro_id)  {
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/ledger/',
			data: { mode : "delete_sold_pro",  cust_sold_pro_id : cust_sold_pro_id },
			success: function(resnse)
			{
				if(resnse.trim() == "1") {
					toastr.success("CUSTOMER PRODUCT DELETED SUCCESSFULLY", "SUCCESS");
					show_sold_pro();
					Unloading();
				}
				else if(resnse.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}

function changeStatus(lid,l_status)
{
	
		//alert(sp_id);
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/ledger/',
			data: { mode : "change_status", lid : lid,l_status:l_status },
			success: function(response)
			{
				toastr.success("STATUS CHANGED SUCCESSFULLY", "SUCCESS");
				Unloading();
				load_ledger_datatable();
			}
		});
	
}


function get_branch_by_zone(zid,sindex,bid)
{
	//alert(zid);
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+'app/ledger/',
		data: { mode : "get_branch_by_zone",zid : zid,bid : bid,sindex:sindex },
		success: function(resnse)
		{
			//alert(resnse);
			$('#'+sindex).select2('focus');
			$('#'+sindex).html(resnse);
			Unloading();			
		}
	});	
}

function report_ledger() 
{
	//alert('hello');
	var date=$("#rep_date").val();
	//alert(emp_id);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/ledger/',
		data: { mode : "generate_report_ledger",date:date },
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			$('#data_table').html(response);
			Unloading();
								
		}
	});	
	
}

function report_ledger_detail() 
{
	
	var date=$("#rep_date").val();
	var l_id=$("#l_id").val();
	//alert(emp_id);
	//alert(l_id);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/ledger/',
		data: { mode : "generate_report_ledger_detail",date:date,l_id:l_id },
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			$('#data_table').html(response);
			Unloading();
								
		}
	});	
	
}

/*function report_ledger_form() 
{
	old
	var date=$("#rep_date").val();
	var emp_id=$("#l_id").val();
	//alert(emp_id);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/employee_expense/',
		data: { mode : "generate_report_emp_ledger",date:date,emp_id:emp_id },
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			$('#adv-table').html(response);
			Unloading();
								
		}
	});	
	
}*/
function report_ledger_form() 
{
	
	var date=$("#rep_date").val();
	var cust_id=$("#l_id").val();
	//alert(emp_id);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/cust_ledger/',
		data: { mode : "generate_report",date:date,cust_id:cust_id },
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			$('#adv-table').html(response);
			Unloading();
								
		}
	});	
	
}

function get_ledger_tree()
{
	 var treeData;
	   
	   $.ajax({
			type: "POST",  
			url: root_domain+'app/ledger/',
			data: { mode : "ledger_tree" },
		//	dataType: "json",			
			success: function(response)  
			{
				//alert(response);
				initTree(response);
				
			}   
	  });
	   
	  function initTree(treeData) {
		$('#treeview_json').treeview({data: treeData});
		
	  }
}

$(function() {
	var txt = $("input#emp_email");
	var func = function() {
		txt.val(txt.val().replace(/\s/g, ''));
	}
	txt.keyup(func).blur(func);
});

function checkUsername(uname)
{
	//alert(uname);
	var emp_email1=$('#emp_email_hid').val();
	
	if(emp_email1!=uname)
	{
		$.ajax({
				type: "POST",  
				url: root_domain+'app/ledger/',
				data: { mode : "check_username",uname:uname },
			//	dataType: "json",			
				success: function(response)  
				{
					//alert(response);
					if(response>0)
					{
						$('#user_error').html("<strong style='color:red'>Sorry.This Username Already Exist</strong><br>");
						$('#btn_submit').attr('disabled',true);
						
					}
					else
					{
						$('#user_error').html("<strong style='color:green'>Username Available</strong><br>");
						$('#btn_submit').attr('disabled',false);
					}
					//$('#user_error').html(response);
					
					//alert(response);
				}   
		  });
	}
}
function load_city_all(){
	var alloc_stateid = $('#alloc_stateid').val();
	
	$.ajax({
		type: "POST",  
		url: root_domain+'app/ledger/',
		data: { mode : "load_city_all", alloc_stateid:alloc_stateid },
		success: function(response)  
		{
			//console.log(response);
			var resp=JSON.parse(response);
			$('#alloc_cityid').html(resp.html_resp);
			$('#alloc_cityid').select2("val","");
			
		}   
  });
}
function load_report_to_users(report_to_user_type){	
	$.ajax({
		type: "POST",  
		url: root_domain+'app/ledger/',
		data: { mode : "load_report_to_users", report_to_user_type:report_to_user_type },
		success: function(response)  
		{
			//console.log(response);
			var resp=JSON.parse(response);
			$('#report_to_user_id').html(resp.html_resp);
			$('#report_to_user_id').select2("val","");
			
		}   
  });
}

function changeGstText(cust_gst_reg){
	if(cust_gst_reg=='0'){
		$('#gst_div').show();
	}
	else{
		$('#gst_div').hide();
		$('#gst_no').val("");
	}
}