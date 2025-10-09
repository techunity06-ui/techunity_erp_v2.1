//var datatable;
$(document).ready(function() {
	load_po_datatable();
	
// validate vendor add form on keyup and submit

});
$("#customer_wise_product").on('submit',function(e) {
	if($("#price").val()=="")
	{
		toastr.warning("Enter valid amount", "ERROR")
		$("#price").focus();
		return false;
	}else if($("#rate_tolerance").val()=="")
	{
		toastr.warning("Enter tolerance", "ERROR")
		$("#rate_tolerance").focus();
		return false;
	}
	else if($("#grate").val()=="")
	{
		toastr.warning("Enter GRate", "ERROR")
		$("#grate").focus();
		return false;
	}
	else if($("#discount_percentage").val()=="")
	{
		toastr.warning("Enter discount rate", "ERROR")
		$("#discount_percentage").focus();
		return false;
	}
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	
	for (instance in CKEDITOR.instances) 
	{
    	CKEDITOR.instances[instance].updateElement();
	}	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+'app/customer_wise_product_list/',
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
				toastr.success("CUSTOMER PRICE ADDED SUCCESSFULLY", "SUCCESS");
				window.location.reload();
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if(arr.msg == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO");
				Unloading();
			}
			else if(arr.msg== 'update')
			{	
				toastr.success("CUSTOMER PRICE UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location.reload();
				
			}
			$('#customer_wise_product').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});



function reload_data()
{
	//datatable.fnReloadAjax();
	load_po_datatable();
}	
function load_po_datatable()
{
	var po_type_status=$('input[name=po_type_status]:Checked').val();
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
			"sAjaxSource": root_domain+'app/customer_wise_product_list/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },{ "name": "po_type_status", "value": po_type_status },{ "name": "date", "value": date });
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}



function get_vendor_details(tab){
	var vendor_id = $('#vender_id').val();
	var mode = "get_"+tab;
	var eid = $('#eid').val();
	if(vendor_id){
		Loading();
		$.ajax({
		type: "POST",
		url: root_domain+'app/customer_wise_product_list/',
		data: { mode : mode, vendor_id : vendor_id, eid : eid},
		success: function(data){
					$('#'+tab).html(data);				
					Unloading();
			}		
		});
	}else{
		$msg = "Please Select Vendor First.";
		toastr.warning($msg, "WARNING");
		$('#'+tab).html($msg);
	}
	
 }

 function get_items_details(tab,product_id=null) {
 	var product_id = $('#product_id').val();
	var mode = "get_"+tab;
	if(product_id){
		Loading();
		$.ajax({
		type: "POST",
		url: root_domain+'app/customer_wise_product_list/',
		data: { mode : mode, product_id : product_id},
		success: function(data){
				$('#'+tab).html(data);				
				Unloading();
			}		
		});
	}else{
		$msg = "Please Select Product First.";
		toastr.warning($msg, "WARNING");
		$('#'+tab).html($msg);
	}
 }

 function get_common_details(tab,v_or_iid=null, eid=null) {
 	var purchase_type = $('#purchase_type').val();
	var v_or_iid='';
	if(purchase_type=='0'){
		v_or_iid = $('#vender_id').val();
	}else if(purchase_type=='1'){
		v_or_iid = $('#product_id').val();
	}

 	var mode = "get_"+tab;
 	
 	if(product_id!='' || vendor_id!=''){
 		Loading();
		$.ajax({
		type: "POST",
		url: root_domain+'app/customer_wise_product_list/',
		data: { mode : mode, v_or_iid : v_or_iid, eid : eid, type : purchase_type},
		success: function(data){
				$('#'+tab).html(data);	
				$("#party_product").select2({
         	width: '100%',
         	minimumInputLength: 2,
         });		
				Unloading();
			}		
		});
 	}else{
 		$msg = "Please select vendor or product first.";
		toastr.warning($msg, "WARNING");
		$('#'+tab).html($msg);
 	}
 }

 function get_vendor_name(vid) {
 	Loading();
 	$.ajax({
		type: "POST",
		url: root_domain+'app/customer_wise_product_list/',
		data: { mode : 'set_vendor_sesion', vendor_id : vid},
		success: function(data){
				window.location.href=root_domain+'customer_wise_product_list';
				Unloading();
			}		
	});
 }

  function get_item_name(iid,ptype, pname) {
  	Loading();
 	$.ajax({
		type: "POST",
		url: root_domain+'app/customer_wise_product_list/',
		data: { mode : 'set_item_sesion', item_id : iid, product_type : ptype, product_name : pname},
		success: function(data){
				window.location.href=root_domain+'customer_wise_product_list';
				Unloading();
			}		
	});
 }

function add_price(){
	var purchase_type = $('#purchase_type').val();
	var v_or_iid='';
	if(purchase_type=='0'){
		v_or_iid = $('#vender_id').val();
	}else if(purchase_type=='1'){
		v_or_iid = $('#product_id').val();
	}

	var new_product = $('#party_product').val();
	var price = $('#party_rate').val();
	var party_category_id = $('#party_category_id').val();

	if(new_product!=''){
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/customer_wise_product_list/',
			data: { mode : 'set_new_item', purchase_type : purchase_type, v_or_iid : v_or_iid, new_product : new_product, price : price, party_category_id : party_category_id},
			success: function(data){
					Unloading();
					var response = jQuery.parseJSON( data );
					if(response.res=='1'){
						$msg = response.msg;
						toastr.success($msg, "SUCCESS");
						window.location.reload();
					}else{
						$msg = response.msg;
						toastr.warning($msg, "WARNING");
						return false;
					}
				}		
		});
	}else{
		$msg = "Please select item/vendor.";
		toastr.warning($msg, "WARNING");
		return false;
	}	
}

 $(document).on('keydown', "input[type='number']", function(event){
            if (event.shiftKey == true) {
                event.preventDefault();
            }

            if ((event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105) || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 37 || event.keyCode == 39 || event.keyCode == 46 || event.keyCode == 190) {

            } else {
                event.preventDefault();
            }
            
            if($(this).val().indexOf('.') !== -1 && event.keyCode == 190)
                event.preventDefault();
});


function get_item_information(product_id, vender_id, type,party_sales_id, party_category_id) {

	Loading();
 	$.ajax({
		type: "POST",
		url: root_domain+'app/customer_wise_product_list/',
		data: { mode : 'get_item_selected_information', product_id : product_id, vender_id : vender_id, type : type, party_sales_id : party_sales_id,party_category_id:party_category_id},
		success: function(data){
				Unloading();
				var obj = jQuery.parseJSON(data);
				var item_info = obj.item_info;
				var purchase_info = obj.purchase_info;

				$('#update_party_category_id').select2("val",obj.party_category_id);
				$('#update_party_product').empty().append(obj.product_party);
				$("#update_party_product").select2({
		         	width: '100%'
		        });
		        $('#update_party_sales_id').val(party_sales_id);
				if(item_info!=null){
					$('#product_id').val(item_info.product_id);
					$('#product_icode').val(item_info.product_icode);
					$('#product_desc').val(item_info.product_desc);
					$('#unit_name').val(item_info.unit_name);
					$('#drawing_number').val(item_info.drawing_number);
				}
				if(purchase_info!=null){
					$('#user_name').removeClass('hide');
					$('#price').val(purchase_info.price);
					$('#rate_tolerance').val(purchase_info.rate_tolerance);
					$('#grate').val(purchase_info.grate);
					$('#discount_percentage').val(purchase_info.discount_percentage);
					$('#lead_time').val(purchase_info.lead_time);
					$('#affected_date').val(purchase_info.affected_date);
					$('#quotation_no').val(purchase_info.quotation_number);
					$('#quotation_date').val(purchase_info.quotation_date);
					$('#item_make').val(purchase_info.item_make); 
					$('#user_name').val(purchase_info.user_name); 
					$('#terms_condition').val(purchase_info.terms_condition); 
				}else{
					$('#price').val('');
					$('#rate_tolerance').val('');
					$('#grate').val('');
					$('#discount_percentage').val('');
					$('#lead_time').val('');
					$('#affected_date').val(obj.today_date);
					$('#quotation_no').val('');
					$('#quotation_date').val(obj.today_date);
					$('#item_make').val(''); 
					$('#user_name').addClass('hide'); 
					$('#terms_condition').val('');
				}
			}		
	});
}       

function getProductByCategoryID(category_id, type=''){
	
	if(category_id!=''){
		$.ajax({
			type: "POST",
			url: root_domain+'app/customer_wise_product_list/',
			data: { mode : 'get_product_by_category', category_id : category_id},
			success: function(data){
				if(type!=''){
					$('#update_party_product').empty().append(data);
					$("#update_party_product").select2({
			         	width: '100%'
			        });
				}else{
					$('#party_product').empty().append(data);
					$("#party_product").select2({
			         	width: '100%'
			        });
				}
				
			}		
		});
	}else{
		toastr.warning('Please select category first.', "WARNING");
		$("#party_category_id").focus();
		return false;
	}
}

function update_record(){
	if($("#update_party_category_id").val()=="")
	{
		toastr.warning("Select category first.", "ERROR")
		$("#update_party_category_id").focus();
		return false;
	} if($("#update_party_product").val()=="")
	{
		toastr.warning("Select product.", "ERROR")
		$("#update_party_product").focus();
		return false;
	}
	var update_party_sales_id = $('#update_party_sales_id').val();
	if(update_party_sales_id!=''){
		$.ajax({
			type: "POST",
			url: root_domain+'app/customer_wise_product_list/',
			data: { 
				mode : 'edit', 
				id : update_party_sales_id, 
				party_category_id: $("#update_party_category_id").val(),
				party_product:$("#update_party_product").val(),
				vender_id: $("#vender_id").val()  
			},
			success: function(data){
				var arr = jQuery.parseJSON(data);
				if(arr.res == '1') {
					toastr.success("CATEGORY UPDATED SUCCESSFULLY", "SUCCESS");
					window.location.reload();
				}else if(arr.res == '0'){
					toastr.warning(arr.msg, "WARNING");
					return false;
				}
			}		
		});
	}
	
}