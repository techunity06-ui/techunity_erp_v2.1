//var datatable;
$(document).ready(function() {
	show_data();
	load_po_req_datatable();
	$("#pre_add").validate({
	rules: {
		branch_id: {
			required: true			
		},
	},
	messages: {
		vender_id: {
			required: "Choose Branch"
		},
	}
	});
});
function reload_data()
{
	load_po_req_datatable();
}	

function load_po_req_datatable()
{
	var po_type_status=$('input[name=po_type_status]:Checked').val();
	var date=$('#rep_date').val();
	var branch_id = $('#branch_id').val();
	
	//alert(po_type_status);
	datatable = $("#po-req-table").dataTable({
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
			"sAjaxSource": root_domain+'app/pre/',
			"fnServerParams": function ( aoData ) {
				aoData.push( 
					{ "name": "mode", "value": "fetch" },
					{ "name": "po_type_status", "value": po_type_status },
					{ "name": "date", "value": date },
					{ "name": "branch_id", "value": branch_id }
				);
			},
			"fnDrawCallback": function( oSettings ) {
				//alert(oSettings);
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}

function product_detail(id){
	if(id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain +'app/pre/',
			data: { mode:"load_product_dtls", product_id:id },
			success: function(response)
			{
				//console.log(response);
				var resp=jQuery.parseJSON(response);
				$('#rate').val(resp.product_purchase_rate);
				$('#unit_show').html(resp.unit_name);
				Unloading();						
			}
		});	
	}
}
function new_vendor(id){
	if(id == 'new'){
		$('#vendor_name').css('display','block');
	}else{
		$('#vendor_name').css('display','none');
	}
}
function add_field(){
	var form_data = new FormData();
	form_data.append("mode","add_field");
	form_data.append('edit_id', $("#edit_id").val());
	form_data.append('img_name', $("#img_name").val());
	form_data.append('product_id', $("#product_id").val());
	form_data.append('product_qty', $("#product_qty").val());
	form_data.append('rate', $("#rate").val());
	form_data.append('vender_id', $("#vender_id").val());
	form_data.append('vendor_name', $("#vendor_name").val());
	form_data.append('att_doc', document.getElementById('att_doc').files[0]);
	form_data.append("pre_id", $("#eid").val());
	form_data.append("branch_id", $("#branch_id").val());
	
	if(!$("#product_id").val()){		
		toastr.warning("Choose Product", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	
	if(!$("#product_qty").val()){		
		toastr.warning("Enter Qty", "ERROR");
		$("#product_qty").focus();
		return false;
	}
	
	if(!$("#rate").val()){		
		toastr.warning("Enter Rate", "ERROR");
		$("#rate").focus();
		return false;
	}
	
	/*if(!$("#vender_id").val()){		
		toastr.warning("Choose Vender", "ERROR");
		$("#vender_id").select2('focus');
		return false;
	}*/
	
	if($("#vender_id").val() == 'new'){
		if(!$("#vendor_name").val()){
			toastr.warning("Enter Vender", "ERROR");
			$("#vendor_name").focus();
			return false;
		}
	}

	$.ajax({
		type: "POST",
		url: root_domain+'app/pre/',
		data: form_data,
		contentType: false,
	    cache: false,
	    processData: false,
	    beforeSend:function(){
	     $('#uploaded_image').html("<label class='text-success'>Image Uploading...</label>");
	    },
		success: function(response)
		{	
			//console.log(response);
			if(response != ""){
				var data = JSON.parse(response);
				var responsevalue=data.msg;
				if(data.l_id != ""){
					$('#vender_id').append('<option value='+data.l_id+'>'+data.l_name+'</option>');
				}
			}
			$("#product_id").prop("disabled", false);
			$('#uploaded_image').html("");
			$("#product_id").select2("val","");
			$("#product_qty").val("");
			$("#rate").val("");
			$("#vender_id").select2("val","");
			$("#vendor_name").val("");
			$("#att_doc").val("");
			$("#edit_id").val("");
			$('#addrow').html('Add');
			$('#vendor_name').css('display','none');
			
			show_data();
			Unloading();
		}
	});
}

function show_data() {
	var eid = $('#eid').val();
	var modee = $('#mode').val();
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + 'app/pre/',
		data: { mode : "show_data", pre_id:eid,modee:modee },
		success: function(resp){
			//console.log(resp);
			$('#show_prod_data').html(resp);
			Unloading();
		}		 
	}); 
}

function edit_data(id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/pre/',
		data: { mode:"edit_data", id:id },
		success: function(response)
		{
			//console.log(response);
			var resp = jQuery.parseJSON(response);
			$("#product_id").prop("disabled", true);
			$("#product_id").select2("val",resp.product_id);
			$("#product_qty").val(resp.product_qty);
			$("#rate").val(resp.rate);
			$("#vender_id").select2("val",resp.vender_id);
			$("#img_name").val(resp.att_doc);
			$("#edit_id").val(id);
			$('#addrow').val('Update');
			Unloading();
		}
	});
}

function delete_data(id) {
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + 'app/pre/',
			data: { mode:"delete_data", id:id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_data();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
}


$("#pre_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#pre_add").valid()) {
		return false;
	}
	
        
	form.submitted = true;	
	Loading();	
	$(this).attr("disabled","disabled");		
	var form_data=new FormData(this);
	
	$.ajax({
		cache:false,
		url: root_domain+'app/pre/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(resnse)
		{		
			var data = JSON.parse(resnse);
			var responsevalue=data.msg;
			if(responsevalue.trim() == '1') {
				toastr.success("PRE ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+'pre_list';
			}
			else if(responsevalue.trim() == '2') {
				toastr.warning("Add One Product Please!!", "ERROR");
				$("#product_id").select2('focus');
				$('#save').prop('disabled', false);
				Unloading();
			}
			else if(responsevalue.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if(responsevalue.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				$('#product_add').trigger('reset');
				Unloading();				
			}
			else if(responsevalue.trim() == 'update') {
				toastr.success("PRE UPDATED SUCCESSFULLY", "SUCCESS");		
				window.location = root_domain+'pre_list';	
				Unloading();
			}
			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});


function check_product(pre_id){
    var has_product = false;
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain+'app/pre/',
		data: { mode:"has_product", pre_id : pre_id },
		success: function(response)
		{
				//console.log(response);
				if(response == '0'){
					has_product = false;
				} else {
					has_product = true;
				}
		}
	});
    return has_product;
}

function delete_row(id) {
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + 'app/pre/',
			data: { mode:"delete", id:id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					load_po_req_datatable();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
}