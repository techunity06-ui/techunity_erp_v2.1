$(document).ready(function() {
	load_products();
});

function product_load(po_type=''){
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=rejection_pro_search&search=production_pro_search&po_type='+po_type;
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		var len=json[0].length;
		//console.log(json);

		for(var i=0;i<len;i++)
		{	
			testData.push({ id:json['0'][i] ,text: json['1'][i]});
				//alert(json['1'][i]);
			}
		});

	return testData;
}
function load_products(po_type = '')
{
	// po_type = $("#rejection_pro_type").val();

	// console.log("load products");
	$('.new_product_id').select2({
		data: product_load(po_type),
		placeholder: 'Search Product',
		width: "100%",
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

function sub_accept_value(cnt)
{
	//alert(cnt);
	var grn_pqty = Number($('#grn_pqty'+cnt).val());
	
	var qty_accept = Number($('#qty_accept'+cnt).val());
	var qty_reject = Number($('#qty_reject'+cnt).val());
	var qty_reprocess = Number($('#qty_reprocess'+cnt).val());
	//alert(grn_pqty);
	var remain_qty1=grn_pqty-(qty_accept+qty_reprocess);
	if(remain_qty1<0){
		remain_qty1=0;
	}

	var remain_qty=(qty_accept+qty_reprocess+remain_qty1);
	//var remain_qty1=grn_pqty-(qty_accept+qty_reject+qty_reprocess);
	//alert(remain_qty);
	$('#qty_accept_hid'+cnt).val(remain_qty1);
	$('#qty_reject'+cnt).val(remain_qty1);
	if(remain_qty<grn_pqty)
	{
		toastr.warning("Value Not More Than Total Qty", "WARNING");
		// $('#qty_error'+cnt).html('Value Not More Than Total Qty');
		$(".qc_detail_head"+cnt).css("background-color", "#ff7070")
		$('#save').prop('disabled',true);
	}else if(remain_qty>grn_pqty){
		toastr.warning("Value Not More Than Total Qty", "WARNING");
		// $('#qty_error'+cnt).html('Value Not More Than Total Qty');
		$(".qc_detail_head"+cnt).css("background-color", "#ff7070")
		$('#save').prop('disabled',true);
	}
	else
	{
		//$('#qty_reject'+cnt).val('0');
		// $('#qty_error'+cnt).html('');
		$(".qc_detail_head"+cnt).css("background-color", "#efefef")
		$('#save').prop('disabled',false);
	}
	
}


function check_validation_qc_all(){
	
	var errorlog=0;
	$('input.total_pending_qty').each(function(index){ 
		var cnt = index + 1;
		// console.log(cnt)
     	//bstart_qty[i++]=$(this).val();
		var pending_qty=parseFloat($(this).val());
		var qty_accept = parseFloat($("#qty_accept"+cnt).val());
		var qty_reject = parseFloat($("#qty_reject"+cnt).val());
		var qty_reprocess = parseFloat($("#qty_reprocess"+cnt).val());
		/*console.log(qty_accept)
		console.log(qty_reject)
		console.log(qty_reprocess)*/
		var total_qty = qty_accept +  qty_reject + qty_reprocess;
		// console.log(total_qty +' == '+pending_qty)
		if(total_qty > pending_qty){
			errorlog +=parseFloat(1);
			$(".qc_detail_head"+cnt).css("background-color", "#ff7070")
		}else{
			$(".qc_detail_head"+cnt).css("background-color", "transparent")
		}
		if(qty_accept > 0){

			if($("#qc_godown"+cnt).val() == ""){
				
				errorlog +=parseFloat(1);
				$("#s2id_qc_godown"+cnt+" .select2-choice").css("border", "2px solid red")
			}else{
				$("#s2id_qc_godown"+cnt+" .select2-choice").css("border", "none")
			}
		}
		if(qty_reject > 0){
			if($("#qc_reject_godown"+cnt).val() == ""){
				errorlog +=parseFloat(1);
				$("#s2id_qc_reject_godown"+cnt+" .select2-choice").css("border", "2px solid red")
			}else{
				$("#s2id_qc_reject_godown"+cnt+" .select2-choice").css("border", "none")
			}
			if($("#new_product_id"+cnt).val() == ""){
				errorlog +=parseFloat(1);
				$("#s2id_new_product_id"+cnt+" .select2-choice").css("border", "2px solid red")
			}else{
				$("#s2id_new_product_id"+cnt+" .select2-choice").css("border", "none")
			}
		}
		if(qty_reprocess > 0){
			if($("#qc_reporcess_godown"+cnt).val() == ""){
				errorlog +=parseFloat(1);
				$("#s2id_qc_reporcess_godown"+cnt+" .select2-choice").css("border", "2px solid red")
			}else{
				$("#s2id_qc_reporcess_godown"+cnt+" .select2-choice").css("border", "none")
			}
			if($("#new_process"+cnt).val() == ""){
				errorlog +=parseFloat(1);
				$("#s2id_new_process"+cnt+" .select2-choice").css("border", "2px solid red")
			}else{
				$("#s2id_new_process"+cnt+" .select2-choice").css("border", "none")
			}
		}

	});
	
	if(errorlog > 0){
		toastr.warning("PLEASE CHECK THE ERROR AND FILL PROPER VALUE", "ERROR")
		return false;
	}else{
		$("#qc_add").submit();	
	}
	
}


$("#qc_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	

	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	$.ajax({
		cache:false,
		url: root_domain+production_domain+'app/qc_all/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				Unloading();
				toastr.success("QC ALL SUCCESSFULLY", "SUCCESS");
				//window.location=root_domain+purchase_domain+'qc_done_list';
				//alert(arr.back);
				setTimeout(function(){
					window.location=root_domain+arr.back;
				},700)
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
			else if(arr.msg == '2')
			{	
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain+arr.back
			}
			//Unloading();
			$('#qc_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});


function load_new_product_unit(product_id,cnt){
	Loading();

	$.ajax({
		type: "POST",
		url: root_domain + production_domain + 'app/qc_all/',
		data: { mode : "get_product_unit", product_id:product_id },
		success: function(data){
			$('#new_product_unit'+cnt).empty().html(data);
			$('#new_product_unit'+cnt).select2({
				width: "100%"
			})
			Unloading();
		}
	});
}