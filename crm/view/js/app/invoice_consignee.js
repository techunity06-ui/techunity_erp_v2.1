//var datatable;
$(document).ready(function() {

// validate vendor add form on keyup and submit
$("#consignee_add").validate({
	rules: {
		company_name: {
			required: true			
		},
		cust_address: {
			required: true
		},
		stateid: {
			required: true
		},
		cityid: {
			required: true
		},
		cust_pincode: {
			number:true
		},
		cust_mobile: {
			number:true
		},
		cust_email:{
			email:true
		}
	},
	messages: {
		company_name: {
			required: "Enter Company Name"
		},
		cust_address: {
			required: "Enter Address"
		},
		stateid: {
			required: "State must be select"
			},
		cityid: {
			required: "City must be select"
		},
		cust_pincode: {
			number:"Enter Only number "
		},
		cust_mobile: {
			number:"Enter Only number "
		},
		cust_email:{
			email:"Enter Valid Email"
		}
	
	}
});

});

$("#consignee_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#consignee_add").valid()) {
		return false;
	}
	else if(parseInt($('#opening_balance').val())>0 && $('#balance_typeid').val() =="" )
	{
		toastr.warning("Select Debit / Credit Option", "WARNING");
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/consignee/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{	
			//console.log(response);
			var data = JSON.parse(response);
			var responsevalue=data.res;
			if(responsevalue.trim() == '1') {
				Unloading();
				toastr.success("CONSIGNEE ADDED SUCCESSFULLY", "SUCCESS");	
				location.reload();
			}
			else if(responsevalue.trim() == '2') {
				toastr.success("CONSIGNEE ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-consignee-modal-lg").modal("hide");
				$('#consignee_id').append('<option value='+data.cust_id+'>'+data.company_name+'</option>');$("#consignee_id").trigger('change')
				$('#consignee_id').select2("val",data.cust_id);
				$('#consignee_add').trigger('reset');
				if(data.hide_modal == '1'){
					location.reload();
				}
				Unloading();
			}
			else if(responsevalue.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				$("#bs-example-modal-lg").modal("hide");
				$('#consignee_add').trigger('reset');
				Unloading();				
			}
			else if(responsevalue.trim() == 'update')
			{	
				toastr.success("CUSTOMER UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				location.reload();
			//	toastr.success("SLIDER UPDATED SUCCESSFULLY", "SUCCESS");		
			}
			$('#consignee_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
/*
function load_city(parentid,control,val1)
{	
	$.ajax({
	type: "POST",
	url: root_domain + crm_domain +'app/vender/',
	data: { mode : "load_city",  id : parentid},
	success: function(responce){
				//console.log(responce);
				$('#'+control).html(responce);
				$("#"+control).select2("val",val1);
			}
	});

}
function add_city()
{
	if($("#stateid").val()=='')
	{
		toastr.warning("Please Select the State", "WARNING");
	}
	else{
	$("#bs-example-modal-city").modal("show");
	$("#state_id").val($("#stateid").val());
	}
}*/
function view_consignee(cust_id)
{
	if(cust_id=='')
	{
		toastr.warning("Please Select Customer", "WARNING");
	}
	else{
		$("#modal-consignee-view").modal("show");
		$("#consignee_custmerid_view").val(cust_id);
		load_consignee_table();
	}
}
function consignee_modal_open(cust_id)
{
	if(cust_id=='')
	{
		toastr.warning("Please Select Customer", "WARNING");
	}
	else{
		$('#consignee_custmerid').val(cust_id);
		$('#bs-consignee-modal-lg').modal();
	}	
}

function load_consignee_table()
{
	var consignee_custid=$("#consignee_custmerid_view").val();
	datatable = $("#table-consignee").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bServerSide" : true,
			"bDestroy" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain + crm_domain +"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO DATA ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain + crm_domain +'app/consignee/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },{ "name": "customerid", "value": consignee_custid } );
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();
		
	//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}
function edit_consignee(id) 
{
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/consignee/',
		data: { mode : "preedit",  eid : id },
		success: function(response)
		{
			console.log(response);
			var obj = jQuery.parseJSON(response);
				
				load_city(obj.stateid,'con_cityid',obj.cityid);
				$("#modal-consignee-view").modal("hide");
				$('#bs-consignee-modal-lg').modal();
				$("#mode").val('edit');
				$("#consignee_eid").val(id);
				$('#consignee_custmerid').val(obj.cust_ref_id);				
				$("#company_name").val(obj.company_name);
				$("#cons_name").val(obj.cust_name);
				$("#cons_address").val(obj.cust_address);
				load_state(obj.countryid,'con_stateid',obj.stateid)	
				load_city(obj.stateid,'con_cityid',obj.cityid);
				$("#countryid").select2('val',obj.countryid);
				$("#con_stateid").select2('val',obj.stateid);
				$("#con_cityid").select2('val',obj.cityid);
				$("#cust_pincode").val(obj.cust_pincode);
				$("#cust_mobile").val(obj.cust_mobile);
				$("#cust_email").val(obj.cust_email);
				$("#gst_no").val(obj.gst_no);				
				Unloading();
		}
	});	
		
}
function delete_consignee(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain +'app/consignee/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response);
					if(response.trim() == "1") {
						toastr.success("CONSIGNEE DELETE SUCCESSFULLY", "SUCCESS");
						load_consignee_table()
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
}