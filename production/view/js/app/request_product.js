var datatable;
$(document).ready(function() {
	
	work_order_submit_per();
	toggle_process_main_button();
	load_salesno();
	view_workorder_image();

	
	$("#product_request_add").validate({
		
		ignore:[],
		
		rules: {
			
			po_req_no:{
				required:true
			},
			
		},
		messages: {
			po_req_no:{
				required:"enter po request no "
			},
			
		}
	}); 

});

function load_salesno(){
	
	var mode=$('#mode').val();
	var po_req_no=$('#po_req_nos').val();
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "load_invoiceno" },
		success: function(data){
		//alert("da");
			//console.log(data); return false;
			var no = jQuery.parseJSON(data);
		//alert(no.invoiceno);
		if(po_req_no == '')
		{
			$('#po_req_no').val(no.invoiceno);
		}
		else
		{
			$('#po_req_no').val(po_req_no);
		}
		Unloading();
		check_main_process_request();
		check_poreq_status();
	}
});
}
function check_poreq_status(){
	
	var eid=$('#eid').val();
	var po_req_no=$('#po_req_no').val();
	var bom_version_id = $('#bom_version_id').val();
	var wo_type = $("#wo_type").val();
	var rp_id = "";
	if(wo_type == "direct_jobcard"){
		rp_id = $("#job_rp_id").val();
	}
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "check_poreq_status",eid:eid,po_req_no:po_req_no,wo_type:wo_type,rp_id:rp_id},
		success: function(data){
			data=data.trim();
			//alert(data);
			Unloading()
			if(data==="0"){
				
				if(bom_version_id !="" ){
					$(".mainRequest").hide();
					$(".mainRequested").hide();
					$("#main_poreq_status").val("0");
					$('#rp_po_qty').attr('readonly',true);
				} 
				else
				{
					
					$(".mainRequest").show();
					$(".mainRequested").hide();
					$("#main_poreq_status").val("0");
					$('#rp_po_qty').attr('readonly',false);
				}
				
			} else if(data===""){
				$(".mainRequest").show();
				$(".mainRequested").hide();
				$("#main_poreq_status").val("0");
				$('#rp_po_qty').attr('readonly',false);
			} 
			
			else{
				//alert("fdsa");
				$(".mainRequest").hide();
				$(".mainRequested").show();
				$("#main_poreq_status").val("1");
				$('#rp_po_qty').attr('readonly',true);
				//$('#set_process_btn').hide();
			}
			
		}
	});
}
function main_po_reqdata(){
	var eid=$('#eid').val();
	var po_req_no=$('#po_req_no').val();
	var rp_po_qty=$('#rp_po_qty').val();
	var sales_order_trn_id=$('#sales_order_trn_id').val();
	//alert("fdsa");
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "main_po_reqdata",eid:eid,po_req_no:po_req_no,rp_po_qty:rp_po_qty,sales_order_trn_id:sales_order_trn_id },
		success: function(data){
			//alert(data);
			Unloading()
			check_poreq_status();
		}
	});
}
function set_main_process_request_qty()
{
	var branch_id=$('#branch_id').val();
	if(branch_id==''){
		toastr.warning("Please Select Branch", "ERROR");
		return false;
	}
	$("#set_process_btn").hide();
	var po_req_no=$('#po_req_no').val();
	var po_req_date=$('#po_req_date').val();
	var rp_req_qty=$('#rp_req_qty').val();
	var in_process_qty_main=$('#in_process_qty_main').val();
	var rp_po_qty=$('#rp_po_qty').val();
	var eid=$('#eid').val();
	var pr_type=$('#pr_type').val();
	var cust_id=$('#cust_id').val();
	var sales_order_date=$('#sales_order_date').val();
	var po_no=$('#po_no').val();
	var po_date=$('#po_date').val();
	var sales_order_no=$('#sales_order_no').val();
	var sales_order_trn_id=$('#sales_order_trn_id').val();
	var bom_version_id=$('#bom_version_id').val();
	var jobwork_type = $('#job_work_type').val();
	//alert(eid);
	var bom_id = $("#bom_id").val();
	var customer_req_material = $("#customer_req_material").val();
	var customer_req_grade = $("#customer_req_grade").val();
	var customer_req_size = $("#customer_req_size").val();
	var customer_req_id = $("#customer_req_id").val();
	var customer_req_length = $("#customer_req_length").val();
	var customer_req_heat = $("#customer_req_heat").val();
	var customer_req_coc = $("#customer_req_coc").val();
	var customer_ref_no = $("#customer_ref_no").val();
	var customer_asset_serial = $("#customer_asset_serial").val();
	var customer_bevel_spec = $("#customer_bevel_spec").val();
	var smode=$("#smode").val();
	var store_order_id=$("#store_order_id").val();
	var sales_order_id=$("#sales_order_id").val();
	var reject_status=$("#reject_status").val();
	if(in_process_qty_main=='' && rp_po_qty==""){
		toastr.warning("Please Enter process/poqty", "ERROR");
		return false;
	}
	if(in_process_qty_main==0 && rp_po_qty==0){
		toastr.warning("Please Enter process/poqty", "ERROR");
		return false;
	}
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { 

			mode : "add_main_process_request_qty",
			smode:smode,
			po_req_no:po_req_no,
			po_req_date:po_req_date,
			rp_req_qty:rp_req_qty,
			in_process_qty_main:in_process_qty_main,
			rp_po_qty:rp_po_qty,
			pr_type:pr_type,
			eid:eid,
			cust_id:cust_id,
			sales_order_date:sales_order_date,
			po_no:po_no,
			po_date:po_date,
			sales_order_no:sales_order_no,
			branch_id:branch_id, 
			sales_order_trn_id:sales_order_trn_id,
			bom_version_id:bom_version_id,
			jobwork_type:jobwork_type,
			bom_id : bom_id,
			customer_req_material:customer_req_material,
			customer_req_grade:customer_req_grade,
			customer_req_size:customer_req_size,
			customer_req_id:customer_req_id,
			customer_req_length:customer_req_length,
			customer_req_heat:customer_req_heat,
			customer_req_coc:customer_req_coc,
			customer_ref_no:customer_ref_no,
			customer_asset_serial:customer_asset_serial,
			customer_bevel_spec:customer_bevel_spec,
			store_order_id:store_order_id,
			sales_order_id:sales_order_id,
			reject_status:reject_status 
		},
		success: function(response){
		// return;

		var data=JSON.parse(response);
		Unloading();
		if(data.msg=='1') {
			window.location=root_domain+production_domain+'edit_workorder/'+data.sp_id; 

			get_tree_request();
		}
		else if(data.msg=='2'){
			toastr.warning("Product BOM Not Found !!!", "ERROR");
		}else{
			toastr.warning("SOMETHING WRONG !!!", "ERROR");
		}
		
		// location.reload();
		// window.location=root_domain+production_domain+'edit_workorder/'+data.sp_id; 
	}
});
	
}

function check_main_process_request()
{
	//path
	
	var po_req_no=$('#po_req_no').val();	
	var eid=$('#eid').val();
	var sales_order_trn_id=$('#sales_order_trn_id').val();
	var bom_version_id = $("#bom_version_id").val();
	var wo_type = $("#wo_type").val();
	var rp_id = "";
	var store_order_id = $("#store_order_id").val();
	if(wo_type == "direct_jobcard"){
		rp_id = $("#job_rp_id").val();
	}
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "check_main_process_request",po_req_no:po_req_no,eid:eid,sales_order_trn_id:sales_order_trn_id,bom_version_id:bom_version_id,wo_type:wo_type,rp_id:rp_id,store_order_id:store_order_id},
		success: function(response)
		{
			var data=JSON.parse(response);
			Unloading();
			if(data.count>0)
			{
				$('#rp_req_qty').val(data.req_qty);
				$('#in_process_qty_main').val(data.process_qty);
				$('#rp_po_qty').val(data.po_qty);
				$('#po_req_no').val(data.po_req_no);
				
				$('#rp_req_qty').attr('readonly',true);
				$('#in_process_qty_main').attr('readonly',true);
				$('#rp_po_qty').attr('readonly',true);
				$('#set_process_btn').hide();
				$('#set_process_btn').hide();
				
				setTimeout(function(){ get_tree_request(); }, 1000);
			}else{
				cal_po_qty();
			}
		}
	});
}
function convert_unit_fun(rp_id,rname,convtype){
	var pro_base_qty=parseFloat($('#pro_base_qty'+rp_id).val());
	var pro_convert_qty=parseFloat($('#pro_convert_qty'+rp_id).val());
	var current_stock=parseFloat($('#current_stock'+rp_id).val());
	
	
	
	// var convtype=$('#convtype'+rp_id).val();
	var convtype_production=$('#convtype_production'+rp_id).val();
	var convtype_po=$('#convtype_po'+rp_id).val();
	
	pro_base_qty=getNum(pro_base_qty);
	pro_convert_qty=getNum(pro_convert_qty);


	/*console.log(convtype)
	console.log(pro_base_qty)
	console.log(pro_convert_qty)*/

	if(pro_base_qty!=pro_convert_qty){
		if(convtype=="conv_unit"){

			if(rname == 'req_qty'){
				var c_req_qty_conv=parseFloat($('#req_qty_conv'+rp_id).val());
				c_req_qty_conv=getNum(c_req_qty_conv);
				var c_req_qty=unit_conv(pro_base_qty,pro_convert_qty,convtype,c_req_qty_conv);
				$('#req_qty'+rp_id).val(c_req_qty);
			}

			if(rname == 'res_qty'){
				var res_qty_conv=parseFloat($('#res_qty_conv'+rp_id).val());
				c_res_qty_conv=getNum(res_qty_conv);
				var c_res_qty=unit_conv(pro_base_qty,pro_convert_qty,convtype,c_res_qty_conv);
				$('#res_qty'+rp_id).val(c_res_qty);
			}

			if(rname == 'process_qty'){
				var c_production_qty=parseFloat($('#conv_process_qty'+rp_id).val());
				c_production_qty=getNum(c_production_qty);
				var production_qty_conv=unit_conv(pro_base_qty,pro_convert_qty,convtype,c_production_qty);
				$('#process_qty'+rp_id).val(production_qty_conv);
			}

			if(rname == 'po_qty'){
				
				var c_purchase_qty=parseFloat($('#po_qty'+rp_id).val());
				c_purchase_qty=getNum(c_purchase_qty);
				var purchase_qty_conv=unit_conv(pro_base_qty,pro_convert_qty,convtype,c_purchase_qty);
				$('#base_po_qty'+rp_id).val(purchase_qty_conv);
			}

		}else{

			if(rname == 'req_qty'){
				var c_req_qty=parseFloat($('#req_qty'+rp_id).val());
				c_req_qty=getNum(c_req_qty);
				var req_qty_conv=unit_conv(pro_base_qty,pro_convert_qty,convtype,c_req_qty);
				$('#req_qty_conv'+rp_id).val(req_qty_conv);
			}

			if(rname == 'res_qty'){
				var res_qty=parseFloat($('#res_qty'+rp_id).val());
				c_res_qty=getNum(res_qty);
				var res_qty_conv=unit_conv(pro_base_qty,pro_convert_qty,convtype,c_res_qty);
				$('#res_qty_conv'+rp_id).val(res_qty_conv);
			}

			if(rname == 'process_qty'){
				var c_production_qty=parseFloat($('#process_qty'+rp_id).val());
				c_production_qty=getNum(c_production_qty);
				var production_qty_conv=unit_conv(pro_base_qty,pro_convert_qty,convtype,c_production_qty);
				$('#conv_process_qty'+rp_id).val(production_qty_conv);
			}

			if(rname == 'po_qty'){
				var c_purchase_qty=parseFloat($('#base_po_qty'+rp_id).val());
				c_purchase_qty=getNum(c_purchase_qty);
				var purchase_qty_conv=unit_conv(pro_base_qty,pro_convert_qty,convtype,c_purchase_qty);
				$('#po_qty'+rp_id).val(purchase_qty_conv);
			}
		}

	}else{
		if(convtype=="conv_unit"){

			if(rname == 'req_qty'){
				var c_req_qty=parseFloat($('#req_qty_conv'+rp_id).val());
				c_req_qty=getNum(c_req_qty);
				$('#req_qty'+rp_id).val(c_req_qty);
			}

			if(rname == 'res_qty'){
				var res_qty_conv=parseFloat($('#res_qty_conv'+rp_id).val());
				c_res_qty=getNum(res_qty_conv);
				$('#res_qty'+rp_id).val(c_res_qty);
			}

			if(rname == 'process_qty'){
				var c_production_qty=parseFloat($('#conv_process_qty'+rp_id).val());
				c_production_qty=getNum(c_production_qty);
				$('#process_qty'+rp_id).val(c_production_qty);
			}

			if(rname == 'po_qty'){
				var c_purchase_qty=parseFloat($('#po_qty'+rp_id).val());
				c_purchase_qty=getNum(c_purchase_qty);
				$('#base_po_qty'+rp_id).val(c_purchase_qty);
			}			
		}else{
			if(rname == 'req_qty'){
				var c_req_qty=parseFloat($('#req_qty'+rp_id).val());
				c_req_qty=getNum(c_req_qty);
				$('#req_qty_conv'+rp_id).val(c_req_qty);
			}

			if(rname == 'res_qty'){
				var res_qty=parseFloat($('#res_qty'+rp_id).val());
				c_res_qty=getNum(res_qty);
				$('#res_qty_conv'+rp_id).val(c_res_qty);
			}

			if(rname == 'process_qty'){
				var c_production_qty=parseFloat($('#process_qty'+rp_id).val());
				c_production_qty=getNum(c_production_qty);
				$('#conv_process_qty'+rp_id).val(c_production_qty);
			}

			if(rname == 'po_qty'){
				var c_purchase_qty=parseFloat($('#base_po_qty'+rp_id).val());
				c_purchase_qty=getNum(c_purchase_qty);
				$('#po_qty'+rp_id).val(c_purchase_qty);
			}
			
		}

	}
	error_check(rp_id,rname);
}
function unit_conv(pro_base_qty,pro_convert_qty,convtype,qty){
	//alert(qty);
	if(convtype=="conv_unit"){
		var return_qty=(parseFloat(qty)/parseFloat(pro_convert_qty))*parseFloat(pro_base_qty);
	}else{
		var return_qty=(parseFloat(qty)/parseFloat(pro_base_qty))*parseFloat(pro_convert_qty);
	}
	return return_qty;
}
function error_check(rp_id,rname){
	var current_stock=parseFloat($('#current_stock'+rp_id).val());
	var base_current_stock=parseFloat($('#base_current_stock'+rp_id).val());
	var req_qty=parseFloat($('#req_qty'+rp_id).val());
	var req_qty_one=parseFloat($('#req_qty_one'+rp_id).val());
	var res_qty=parseFloat($('#res_qty'+rp_id).val());
	var res_qty_conv=parseFloat($('#res_qty_conv'+rp_id).val());
	var process_qty=parseFloat($('#process_qty'+rp_id).val());
	var po_qty=parseFloat($('#base_po_qty'+rp_id).val());
	var basic_req_qty=parseFloat($('#basic_req_qty'+rp_id).val());
	current_stock=getNum(current_stock).toFixed(5);
	req_qty=getNum(req_qty).toFixed(5);
	req_qty_one=getNum(req_qty_one).toFixed(5);
	res_qty=getNum(res_qty).toFixed(5);
	process_qty=getNum(process_qty).toFixed(5);
	po_qty=getNum(po_qty).toFixed(5);
	basic_req_qty=getNum(basic_req_qty).toFixed(5);
	res_qty_conv=getNum(res_qty_conv).toFixed(5);
	var reqbu=0;var reqbu1=0;var reqbu2=0;
	//alert(current_stock);
	//alert(res_qty);
/*console.log('req : ' + req_qty)
console.log('res_qty : ' + res_qty)
console.log('process_qty : ' + process_qty)
console.log('po_qty : ' + process_qty)*/
	if(parseFloat(base_current_stock)<parseFloat(res_qty)){
		$("#res_qty_err"+rp_id).css("display","block");
		$("#res_qty_err"+rp_id).html("Not Add "+base_current_stock+" < "+res_qty+"");
		//$("#reqest_btn"+rp_id).hide();
		reqbu=0;
		//return false;
	}else{
		$("#res_qty_err"+rp_id).css("display","none");
		$("#reqest_btn"+rp_id).show();
		reqbu=1;
	}
	
	var used_qty=parseFloat(res_qty) + parseFloat(process_qty) + parseFloat(po_qty);
	//alert(used_qty);
	//alert(req_qty);
	if(parseFloat(req_qty)<used_qty){
		$("#reqest_btn"+rp_id).hide();
		reqbu1=0;
		$("#"+rname+"_err"+rp_id).css("display","block");
		$("#"+rname+"_err"+rp_id).html("Not Add "+req_qty+" < "+used_qty+"");
	}else if(parseFloat(req_qty)<used_qty){
		$("#reqest_btn"+rp_id).hide();
		reqbu1=0;
		$("#"+rname+"_err"+rp_id).css("display","block");
		$("#"+rname+"_err"+rp_id).html("Not Add "+req_qty+" > "+used_qty+"");
	}else if(parseFloat(req_qty)==used_qty){
		$("#reqest_btn"+rp_id).show();
		reqbu1=1;
		$("#"+rname+"_err"+rp_id).css("display","none");
	}
	
	//if(basic_req_qty>req_qty){
		if(req_qty>req_qty){
		$("#req_qty_err"+rp_id).css("display","block");
		$("#req_qty_err"+rp_id).html("Enter Minimum "+basic_req_qty);
		$("#reqest_btn"+rp_id).hide();
		reqbu2=0;
	}else{
		$("#req_qty_err"+rp_id).css("display","none");
		$("#reqest_btn"+rp_id).show();
		reqbu2=1;
	}
	var ccc=parseFloat(reqbu2)+parseFloat(reqbu)+parseFloat(reqbu1);
	// console.log(ccc);
	if(ccc==3){
		$("#reqest_btn"+rp_id).show();
	}else{
		$("#reqest_btn"+rp_id).hide();
	}
}

function add_product_request(rp_id,stock_check_flag,lead_time_process)
{	

	var extra_stock = $("#extra_stock").val();
	var ext_stock_vendor_id = $("#ext_stock_vendor_id").val();
	if(lead_time_process == 0)
	{	
		alert("Please Set Product Lead Time");		
		$("#stock_check_flag_modal").val(stock_check_flag);
		$("#lead_time_process_modal").val(lead_time_process);
		$("#rp_id_modal").val(rp_id);		
		$("#product_lead_and_process").modal("show");
		var product_id = $('#req_product_id'+rp_id).val();
		$("#product_id").val(product_id);
		$('#'+rp_id).css('background-color','#FFFFFF !important');
		$('.'+product_id).css('background-color','#FFFFFF !important');
		return false;
	}
	var current_stock=parseFloat($('#current_stock'+rp_id).val());
	var req_qty=parseFloat($('#req_qty'+rp_id).val());
	var req_qty_one=parseFloat($('#req_qty_one'+rp_id).val());
	var reorder_qty=parseFloat($('#reorder_qty'+rp_id).val());
	var reorder_conv_qty=parseFloat($('#reorder_conv_qty'+rp_id).val());
	var res_qty=parseFloat($('#res_qty'+rp_id).val());
	var process_qty=parseFloat($('#process_qty'+rp_id).val());
	var po_qty=parseFloat($('#po_qty'+rp_id).val());
	var branch_id = $('#branch_id').val();

	var rp_po_base_qty =  parseFloat($('#base_po_qty'+rp_id).val());
	var in_process_conv_qty = parseFloat($('#conv_process_qty'+rp_id).val());


	var product_id = $('#req_product_id'+rp_id).val();
	var unit_id = $('#req_unitid'+rp_id).val();
	var unit_name = $('#req_unitname'+rp_id).val();
	var customer_id = $("#customer_id").val();

	var res_qty_conv=parseFloat($('#res_qty_conv'+rp_id).val());
	var convtype=$('#convtype'+rp_id).val();

	var is_reserve_godown = $("#is_reserve_godown").val();
	var default_godown_id = $("#default_godown_id").val();
	
	current_stock=getNum(current_stock);
	req_qty=getNum(req_qty);
	req_qty_one=getNum(req_qty_one);
	res_qty=getNum(res_qty);
	res_qty_conv=getNum(res_qty_conv);
	process_qty=getNum(process_qty);
	po_qty=getNum(po_qty);
	var stock_model=0;
	if(stock_check_flag==1){
		if(res_qty>0){
			stock_model=1;
			if(is_reserve_godown == '1' && default_godown_id > 0){
				stock_model=2;
			}
		}else{
			stock_model=0;
		}
	}
	if(process_qty > 0){
		var wo_qty = process_qty / reorder_qty;
		if(reorder_qty != "" && reorder_qty > 0){
				if(!isInteger(wo_qty)){
					toastr.warning("You Can't Process. Reorder Qauntity is " + reorder_qty, "ERROR");
					return false;	
			}
		}
	}
	if(stock_model==0)
	{	

		var bstock_arr=[];
		var bid_arr=[];

		i = 0;
		$('input.wip_res_stock').each(function(){ 
			bstock_arr[i++]=$(this).val();
		});
		
		j = 0;
		$('input.wip_stock_id').each(function(){ 
			bid_arr[j++]=$(this).val();
		});
	//console.log(bstock_arr);
	//return false;
	var total = 0;
	for (var i = 0; i < bstock_arr.length; i++) {
		total += bstock_arr[i] << 0;
	}

	if(is_reserve_godown == '1' && default_godown_id > 0){
		total=parseFloat($('#direct_reserve_stock').val());
	}
	
		var process_res_stock_arr=[];
		var purchase_res_stock_arr=[];
		var sp_purchase_trn_id=[];
		var process_godown_arr=[];
		var process_arr=[];

		p = 0;
		$('input.sp_process_stock').each(function(){ 
			process_res_stock_arr[p++]=$(this).val();
		});
		
		q = 0;
		$('input.sp_process_id').each(function(){ 
			process_arr[q++]=$(this).val();
		});

		r = 0;
		$('input.sp_godown_id').each(function(){ 
			process_godown_arr[r++]=$(this).val();
		});

		ps = 0;
		$('input.sp_purchase_stock').each(function(){ 
			purchase_res_stock_arr[ps++]=$(this).val();
		});
		pp = 0;
		$('input.sp_purchase_trn_id').each(function(){ 
			sp_purchase_trn_id[pp++]=$(this).val();
		});

	//console.log(bstock_arr);
	//return false;
	var total_process_stock = 0;
	for (var k = 0; k < process_res_stock_arr.length; k++) {
		total_process_stock += process_res_stock_arr[k] << 0;
	}
//console.log(purchase_res_stock_arr.length);
	var total_purchase_stock = 0;
	for (var ks = 0; ks < purchase_res_stock_arr.length; ks++) {
		total_purchase_stock += getNum(purchase_res_stock_arr[ks]);
		//alert(purchase_res_stock_arr[ks]);
	}


	var gstock_total=parseFloat($('#gstock_total').val());
	gstock_total=getNum(gstock_total);
	var tstock=parseFloat(total) + parseFloat(gstock_total) + parseFloat(total_process_stock) + parseFloat(total_purchase_stock);
	//alert("hi");
	//alert(total_purchase_stock);

	if(convtype=="conv_unit"){
		/*console.log(tstockc)
	console.log(res_qty_convc)*/
		if(res_qty_conv>0){
			var tstockc = tstock;
		    var res_qty_convc =res_qty_conv;
			if(tstockc!=res_qty_convc){
				toastr.warning("Increase Resverve Qty Please Enter currect Qty1", "ERROR");
				return false;
			}
		}
	}else{
		console.log(tstock)
	console.log(res_qty)
		if(res_qty>0){
			if(tstock!=res_qty){
				toastr.warning("Increase Resverve Qty Please Enter currect Qty", "ERROR");
				return false;
			}
		}
	}
	
	//alert(total);
	//return false;

	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { 
			mode : "add_product_request",
			current_stock:current_stock,
			req_qty:req_qty,
			req_qty_one:req_qty_one,
			res_qty:res_qty,
			process_qty:process_qty,
			po_qty:po_qty,
			rp_id:rp_id,
			branch_id : branch_id,
			bstock:bstock_arr,
			bid:bid_arr,
			customer_id: customer_id,
			sp_purchase_trn_id:sp_purchase_trn_id,
			purchase_res_stock:purchase_res_stock_arr,
			process_res_stock:process_res_stock_arr,
			process_id:process_arr,
			process_godown:process_godown_arr,
			convtype:convtype,
			res_qty_conv:res_qty_conv,
			rp_po_base_qty:rp_po_base_qty,
			in_process_conv_qty:in_process_conv_qty,
			extra_stock:extra_stock,
			ext_stock_vendor_id:ext_stock_vendor_id
		},
		success: function(data){
			
			var resp =JSON.parse(data);
				//console.log(resp);
					Unloading();
				if(resp.trn_ids!=0)
				{
					var exp_trn_ids=(resp.trn_ids).split(",");
					var insert_id=resp.insert_id;
					
					var i;
					
					//alert('Update from : add_product_request');
					var inh_qty=Number($('#process_qty'+insert_id).val());
					
					for (i = 0; i < exp_trn_ids.length; ++i) {
						
						var chil=Number($('#req_qty_one'+exp_trn_ids[i]).val());
						//alert(inh_qty);
						//alert(chil);
						var req_qty1=parseFloat(chil)*parseFloat(inh_qty);
						req_qty1 = req_qty1.toFixed(4);
						$("#req_qty"+exp_trn_ids[i]).val(req_qty1);
						$("#basic_req_qty"+exp_trn_ids[i]).val(req_qty1);
						var pq=Number($("#process_qty"+exp_trn_ids[i]).val());
						//alert(pq);
						if(pq>0){
							$("#process_qty"+exp_trn_ids[i]).val(req_qty1);
						}else{
							$("#po_qty"+exp_trn_ids[i]).val(req_qty1);
						}
						var com1='<a class="btn btn-primary dispbtn" data-original-title="" id="reqest_btn'+exp_trn_ids[i]+'" data-toggle="tooltip" data-placement="top" onclick="add_product_request('+exp_trn_ids[i]+')" ><i class="fa fa-paper-plane"></i> Request</a>';
						$(".action"+exp_trn_ids[i]).html(com1);
						
						/* $('.csb'+exp_trn_ids[i]).show();
						
						var total_qty=Number($('.tct'+exp_trn_ids[i]).val());
						
						var req_qty=total_qty*inh_qty;
						
						var check_qty=Number($('.inpc'+exp_trn_ids[i]).val());
						
						$('.rt'+exp_trn_ids[i]).val(req_qty.toFixed(4));
						//alert(check_qty);
						if(check_qty!='1')
						{
							$('.pt'+exp_trn_ids[i]).val(req_qty.toFixed(4));
							$('.po'+exp_trn_ids[i]).val(0);
						}
						else
						{
							$('.pt'+exp_trn_ids[i]).val(0);
							$('.po'+exp_trn_ids[i]).val(req_qty.toFixed(4));
						}
						$('.perent'+exp_trn_ids[i]).val(insert_id);
						*/					
					}
					
					/*$('.csb'+insert_id).prop("disabled",true).removeClass("btn-primary").addClass("btn-danger").html("Requested");*/
					var com='<a class="btn btn-danger dispbtn" data-original-title="" data-toggle="tooltip" data-placement="top" > Requested</a>';
					$(".action"+insert_id).html(com);
					
					$('#po_qty'+insert_id).attr("readonly",true);
					$('#process_qty'+insert_id).attr("readonly",true);
					$('#res_qty'+insert_id).attr("readonly",true);
					$('#req_qty'+insert_id).attr("readonly",true);
					//$('#current_stock'+insert_id).attr("readonly",true);
					//$('.submi'+trn_id).val("0");
					//alert(trn_id);
					
					
					
				}
				else
				{
					var com='<a class="btn btn-danger dispbtn" data-original-title="" data-toggle="tooltip" data-placement="top" > Requested</a>';
					$(".action"+resp.insert_id).html(com);
					
					$('#po_qty'+resp.insert_id).attr("readonly",true);
					$('#process_qty'+resp.insert_id).attr("readonly",true);
					$('#res_qty'+resp.insert_id).attr("readonly",true);
					$('#req_qty'+resp.insert_id).attr("readonly",true);
					//$('#current_stock'+insert_id).attr("readonly",true);
					
				}
				work_order_submit_per();
				get_tree_request_level_wise(rp_id);
				toggle_process_stock_button($("#work_order_id").val(),$("#wo_rp_id").val());
				$("#reserve_stock_entry_wo").modal("hide");

			}		
			
		});
}
else if(stock_model == 2){
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { 
			mode : "default_godown_stock_reserve",
			product_id:product_id,
			unit_id:unit_id,
			rp_id:rp_id,
			customer_id: customer_id,
			extra_stock:extra_stock,
			ext_stock_vendor_id:ext_stock_vendor_id,
			branch_id:branch_id,
			res_qty : res_qty,
			default_godown_id:default_godown_id
		},
		success: function(data){
			var resp =JSON.parse(data);
				
			if(resp.msg == '-1'){
				$('#direct_reserve_stock').val(0);
				toastr.warning("STOCK NOT AVAILABLE IN DEFAULT GODOWN", "ERROR");
			}else if(resp.msg == '1'){
				$('#direct_reserve_stock').val(res_qty);
				add_product_request(rp_id);
			}else{
				$('#direct_reserve_stock').val(0);
				toastr.warning("SOMETHING WRONG.!", "ERROR");
			}

			Unloading();
			
		}
	})
}
else{
	/*console.log(res_qty_conv);
	console.log(res_qty_conv);*/
	$("#reserve_stock_entry_wo").modal("show");
	$("#current_stock_model").val(current_stock);
	$("#req_qty_model").val(req_qty);
	$("#req_qty_one_model").val(req_qty_one);
	$("#res_qty_model").val(res_qty);
	$("#show_res_qty").html(res_qty);
	$("#process_qty_model").val(process_qty);
	$("#po_qty_model").val(po_qty);
	$("#rp_id_model").val(rp_id);
	$("#branch_id_model").val(branch_id);
	$("#product_id_model").val(product_id);
	$("#unit_id_model").val(unit_id);
	$("#extra_stock").val(extra_stock);
	$("#res_unit_name_model").html(unit_name);
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { 
			mode : "show_stock_new",
			product_id:product_id,
			unit_id:unit_id,
			rp_id:rp_id,
			customer_id: customer_id,
			extra_stock:extra_stock,
			ext_stock_vendor_id:ext_stock_vendor_id,
			branch_id:branch_id
		},
		success: function(data){
			Unloading();
			$("#sstock").html(data);
			$("#st_godown_id").select2({
				width: '100%'
			});
			$("#st_stock_id").select2({
				width: '100%'
			});
			show_reserve_temp_data(extra_stock);
		}
	})

}
}
function add_product_request_ol(cnt,trn_id)
{
	//alert(cnt);
	//alert(trn_id);
	
	var atstock=parseFloat($("#at_reserve"+cnt).val());
	var req_qty=parseFloat($("#req_qty"+cnt).val());
	var in_process_qty=parseFloat($("#in_process_qty"+cnt).val());
	var po_qty=parseFloat($("#po_qty"+cnt).val());
	//var at_reserve2=parseFloat($("#at_reserve2"+cnt).val());
	atstock=getNum(atstock);
	req_qty=getNum(req_qty);
	in_process_qty=getNum(in_process_qty);
	po_qty=getNum(po_qty);
		//at_reserve2=getNum(at_reserve2);
		
		var acutal=parseFloat(req_qty)-parseFloat(atstock);
		var actual_req=parseFloat(in_process_qty)+parseFloat(po_qty);
		var actual_req_new=parseFloat(in_process_qty)+parseFloat(po_qty)+parseFloat(atstock);
		actual_req_new=getNum(actual_req_new);
	//alert(atstock);
	//alert(req_qty);
	//alert(acutal);
	//alert(actual_req);
	if(atstock>req_qty){
		toastr.warning("less Than Request Qty Please Increase Qty", "ERROR");
		$("#req_qty"+cnt).focus();
		return false;
	}
	if(req_qty>actual_req_new){
		toastr.warning("less Than Qty Please Increase Qty", "ERROR");
		$("#req_qty"+cnt).focus();
		return false;
	}
	if(acutal>actual_req){
		toastr.warning("less Than Qty Please Increase Qty", "ERROR");
		$("#req_qty"+cnt).focus();
		return false;
	}
	
	
	if($("#req_qty"+cnt).val()=="")
	{
		toastr.warning("insert Required Qty", "ERROR");
		$("#req_qty"+cnt).focus();
		return false;
	}
	else if($("#in_process_qty"+cnt).val()==="" && $('#in_process_qty_check'+cnt).val()!='1')
	{		
		toastr.warning("insert Inward Process Qty", "ERROR");
		$("#in_process_qty"+cnt).focus();
		return false;
	}
	else if($("#out_process_qty"+cnt).val()==="" && $('#in_process_qty_check'+cnt).val()!='1')
	{		
		toastr.warning("insert Outward Qty", "ERROR");
		$("#out_process_qty"+cnt).focus();
		return false;
	}
	else if(po_qty==="")
	{	
		//else if($("#po_qty"+cnt).val()==="")
		
		toastr.warning("insert PO Qty", "ERROR");
		$("#po_qty"+cnt).focus();
		return false;
	}
	
	
	var req_qty=$('#req_qty'+cnt).val();
	var purchase_unit=$('#purchase_unit'+cnt).val();
	var in_process_qty=$('#in_process_qty'+cnt).val();
	var process_unit=$('#process_unit'+cnt).val();
	var out_process_qty=$('#out_process_qty'+cnt).val();
	var at_reserve=$('#at_reserve'+cnt).val();
	var perent=$('#perent'+cnt).val();
	var po_qty=$('#po_qty'+cnt).val();
	var pr_id=$('#pr_id'+cnt).val();
	var po_req_no=$('#po_req_no').val();
	var parent_product=$('#eid').val();
	
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "add_product_request",req_qty:req_qty,in_process_qty:in_process_qty,out_process_qty:out_process_qty,po_qty:po_qty,pr_id:pr_id,po_req_no:po_req_no,parent_product:parent_product,cnt:cnt,trn_id:trn_id,process_unit:process_unit,purchase_unit:purchase_unit,at_reserve:at_reserve,perent:perent,reserve_stock:atstock },
		success: function(data){
			
			var resp =JSON.parse(data);
			Unloading();
			//console.log(resp);
			if(resp.trn_ids!=0)
			{
				var exp_trn_ids=(resp.trn_ids).split(",");
				var insert_id=resp.insert_id;
				
				var i;
				
				//alert('Update from : add_product_request');
				var inh_qty=Number($('.pt'+trn_id).val());
				
				for (i = 0; i < exp_trn_ids.length; ++i) {
					
					$('.csb'+exp_trn_ids[i]).show();
					
					var total_qty=Number($('.tct'+exp_trn_ids[i]).val());
					
					var req_qty=total_qty*inh_qty;
					
					var check_qty=Number($('.inpc'+exp_trn_ids[i]).val());
					
					$('.rt'+exp_trn_ids[i]).val(req_qty.toFixed(4));
					//alert(check_qty);
					if(check_qty!='1')
					{
						$('.pt'+exp_trn_ids[i]).val(req_qty.toFixed(4));
						$('.po'+exp_trn_ids[i]).val(0);
					}
					else
					{
						$('.pt'+exp_trn_ids[i]).val(0);
						$('.po'+exp_trn_ids[i]).val(req_qty.toFixed(4));
					}
					$('.perent'+exp_trn_ids[i]).val(insert_id);
					//alert
					//insert_id
					//console.log(total_qty);
					//alert(exp_trn_ids[i]);
				}
				
				$('.csb'+trn_id).prop("disabled",true).removeClass("btn-primary").addClass("btn-danger").html("Requested");
				
				$('.rt'+trn_id).attr("readonly",true);
				$('.pt'+trn_id).attr("readonly",true);
				$('.po'+trn_id).attr("readonly",true);
				$('.submi'+trn_id).val("0");
				//alert(trn_id);
				
				
				
			}
			else
			{
				$('.csb'+trn_id).prop("disabled",true).removeClass("btn-primary").addClass("btn-danger").html("Requested");
				$('.submi'+trn_id).val("0");
				//alert(trn_id);
				
				
			}
			$('#at_reserve'+cnt).attr("readonly",true);
			get_all_requested_qty();
			lock_main_request();
			check_submit_btn();
			/*if(data=='0')
			{
				get_tree_request();
				
				/
			}*/
			// Unloading();
		}		
		
	});
	
}

function get_main_form_submit()
{

	/*if (!$("#product_request_add").valid()) {
		return false;
	}*/	

	var rp_req_qty=parseFloat($("#rp_req_qty").val());
	var rp_po_qty=parseFloat($("#rp_po_qty").val());
	var process_qty=parseFloat($("#in_process_qty_main").val());
	rp_po_qty=getNum(rp_po_qty);
	rp_req_qty=getNum(rp_req_qty);
	process_qty=getNum(process_qty);
	if(rp_po_qty<0){ rp_po_qty=0; }
	if(process_qty<0){ process_qty=0; }
	var uqty=parseFloat(rp_po_qty)+parseFloat(process_qty);
	if(rp_req_qty!=uqty){
		toastr.error("PLEASE ENTER VALID QTY", "ERROR");
		return false;
	}
	
	var cust_id=$("#cust_id").val();
	var sales_order_date=$("#sales_order_date").val();
	var po_no=$("#po_no").val();
	var po_date=$("#po_date").val();
	var sales_order_no=$("#sales_order_no").val();
	var po_req_no=$("#po_req_no").val();
	var po_req_date=$("#po_req_date").val();
	var po_product_name=$("#po_product_name").val();
	var rp_req_qty=$("#rp_req_qty").val();
	var in_process_qty=$("#in_process_qty_main").val();
	var rp_po_qty=$("#rp_po_qty").val();
	var main_poreq_status=$("#main_poreq_status").val();
	var branch_id=$("#branch_id").val();
	var category_name=$("#category_name").val();
	var remark=$("#remark").val();
	var smode=$("#smode").val();
	var mode=$("#mode").val();
	var eid=$("#eid").val();
	var pr_type=$("#pr_type").val();
	var bom_id=$("#bom_id").val();
	var process_status=$("#process_status").val();
	var work_order_id=$("#work_order_id").val();
	var bom_check=$("#bom_check").val();
	var sales_order_trn_id=$("#sales_order_trn_id").val();
	var customer_req_material = $("#customer_req_material").val();
	var customer_req_grade = $("#customer_req_grade").val();
	var customer_req_size = $("#customer_req_size").val();
	var customer_req_id = $("#customer_req_id").val();
	var customer_req_length = $("#customer_req_length").val();
	var customer_req_heat = $("#customer_req_heat").val();
	var customer_req_coc = $("#customer_req_coc").val();
	var customer_ref_no = $("#customer_ref_no").val();
	var customer_asset_serial = $("#customer_asset_serial").val();
	var customer_bevel_spec = $("#customer_bevel_spec").val();
	var bom_costing_id = $("#bom_costing_id").val();
	Loading();
	$.ajax({

	type: "POST",
	url: root_domain+production_domain+'app/request_product/',
	data: { cust_id : cust_id,sales_order_date:sales_order_date,po_no:po_no,po_date:po_date,sales_order_no:sales_order_no,po_req_no:po_req_no,po_req_date:po_req_date,po_product_name:po_product_name,rp_req_qty:rp_req_qty,in_process_qty:in_process_qty,rp_po_qty:rp_po_qty,main_poreq_status:main_poreq_status,branch_id:branch_id,category_name:category_name,remark:remark,smode:smode,mode:mode,eid:eid,pr_type:pr_type,bom_id:bom_id,process_status:process_status,work_order_id:work_order_id,bom_check:bom_check,sales_order_trn_id:sales_order_trn_id,customer_req_material:customer_req_material,customer_req_grade:customer_req_grade,customer_req_size:customer_req_size,customer_req_id:customer_req_id,customer_req_length:customer_req_length,customer_req_heat:customer_req_heat,customer_req_coc:customer_req_coc,customer_ref_no:customer_ref_no,customer_asset_serial:customer_asset_serial,customer_bevel_spec:customer_bevel_spec,bom_costing_id:bom_costing_id },
	success: function(msg){
		
	
/*	type: "POST",
	url: root_domain+production_domain+'app/request_product/',
	data: { cust_id : cust_id,sales_order_date:sales_order_date,po_no:po_no,po_date:po_date,sales_order_no:sales_order_no,po_req_no:po_req_no,po_req_date:po_req_date,po_product_name:po_product_name,rp_req_qty:rp_req_qty,in_process_qty:in_process_qty,rp_po_qty:rp_po_qty,main_poreq_status:main_poreq_status,branch_id:branch_id,category_name:category_name,remark:remark,smode:smode,mode:mode,eid:eid,pr_type:pr_type,bom_id:bom_id,process_status:process_status,work_order_id:work_order_id,bom_check:bom_check,sales_order_trn_id:sales_order_trn_id },
	success: function(msg){
*/			
			//console.log(response);
			//var resp = JSON.parse(response);
			//var msg= resp.msg;
			var redirect_url= $("#redirect_url").val();
			Unloading();
			if(msg.trim() == '1') {
				toastr.success("INSERTED SUCCESSFULLY", "SUCCESS")
				// Unloading();
				window.location=redirect_url; 
			}
			else if(msg.trim() == '2') {
				toastr.success("COMPLAINT DONE SUCCESSFULLY", "SUCCESS");
				//$("#modal-complain-add").modal("hide");
				// Unloading();
			}
			else if(msg.trim() == '0') {
				toastr.error("PLEASE ENTER AT LEAST ONE OLD SPARE PART", "ERROR")
				// Unloading();
			}
			else if(msg.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				// Unloading();				
			}
			else if(msg.trim() == '3') {
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");
				
				window.location=redirect_url;
				// Unloading();
			}
			$('#product_request_add').trigger('reset'); 	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
} 

/* $("#product_request_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#product_request_add").valid()) {
		
		return false;
	}		
	
	var rp_req_qty=parseFloat($("#rp_req_qty").val());
	var rp_po_qty=parseFloat($("#rp_po_qty").val());
	var process_qty=parseFloat($("#in_process_qty_main").val());
		rp_po_qty=getNum(rp_po_qty);
		rp_req_qty=getNum(rp_req_qty);
		process_qty=getNum(process_qty);
		if(rp_po_qty<0){ rp_po_qty=0; }
		if(process_qty<0){ process_qty=0; }
	var uqty=parseFloat(rp_po_qty)+parseFloat(process_qty);
	if(rp_req_qty!=uqty){
		toastr.error("PLEASE ENTER VALID QTY", "ERROR");
		return false;
	}
		
	
	
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	
		
	$.ajax({
		cache:false,
		url: root_domain+production_domain+'app/request_product/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(msg)
		{
		
			//console.log(response);
			//var resp = JSON.parse(response);
			//var msg= resp.msg;
			if(msg.trim() == '1') {
				toastr.success("INSERTED SUCCESSFULLY", "SUCCESS")
				Unloading();
				window.location=root_domain+production_domain+'get_stock_detail';
				//load_complaint_datatable();
				//$("#modal-complain-add").modal("hide");
			}
			else if(msg.trim() == '2') {
				toastr.success("COMPLAINT DONE SUCCESSFULLY", "SUCCESS");
				//$("#modal-complain-add").modal("hide");
				Unloading();
			}
			else if(msg.trim() == '0') {
				toastr.error("PLEASE ENTER AT LEAST ONE OLD SPARE PART", "ERROR")
				Unloading();
			}
			else if(msg.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#product_request_add').trigger('reset'); 	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
*/

function get_bom_request_qty(x)
{
	
	var counter_tree=Number($('#counter_tree').val());
	
	var in_process_qty=$('#in_process_qty_main').val();
	
	for(var i=0;i<counter_tree;i++)
	{
		var total_qty=parseFloat($('#total_qty'+i).val());
		var req_qty=(parseFloat(total_qty))*(parseFloat(x));
		req_qty = getNum(req_qty);
		//alert(req_qty);
		//alert(i);
		$('#req_qty'+i).val(req_qty.toFixed(4));
		
	}
	//alert('Update from : get_bom_request_qty');

}


function getNum(val) {
	if (isNaN(val)) {
		return 0;
	}
	return val;
}
function get_inhouse_request_qty(x)
{
	
	var counter_tree=Number($('#counter_tree').val());
	for(var i=0;i<counter_tree;i++)
	{
		var total_qty=Number($('#total_qty'+i).val());
		
		var req_qty=total_qty*x;
		req_qty = getNum(req_qty);
		
		var check_qty=Number($('#in_process_qty_check'+i).val());
		
		if(check_qty!='1')
		{
			$('#in_process_qty'+i).val(req_qty.toFixed(4));
		}
	}
	
}


function get_request_inner(cnt,trns_id)
{
	$('.pt'+trns_id).val(0);
	$('.po'+trns_id).val(0);
}

function get_inhouse_inner(cnt,trns_id)
{
	var pr_id=Number($('.pt'+trns_id).val());
	var req_id=Number($('.rt'+trns_id).val());
	var at_reserve=Number($('#at_reserve'+cnt).val());
	
	var total=req_id-(pr_id+at_reserve);
	//alert(total);
	//alert(req_id);
	//alert(pr_id);
	if(total<0){
		total=0;
	}
	$('.po'+trns_id).val(total);
	
}


function get_outward_request_qty(x)
{

	var counter_tree=Number($('#counter_tree').val());
	
	for(var i=0;i<counter_tree;i++)
	{
		var total_qty=Number($('#total_qty'+i).val());
		
		var req_qty=total_qty*x;
		
		var check_qty=Number($('#in_process_qty_check'+i).val());
		
		if(check_qty!='1')
		{
			
			$('#out_process_qty'+i).val(req_qty);
			
		}
	}
	
}

function get_po_request_qty(x)
{
	
	var counter_tree=Number($('#counter_tree').val());
	
	for(var i=0;i<counter_tree;i++)
	{
		var total_qty=Number($('#total_qty'+i).val());
		
		var req_qty=total_qty*x;
		req_qty=getNum(req_qty);
		
		var check_qty=Number($('#in_process_qty_check'+i).val());
		
		if(check_qty=='1')
		{		
			$('#po_qty'+i).val(req_qty.toFixed(4));
		}
	}	
}

function get_tree_request()
{
	var main_mode=$('#mode').val();
	var eid=$('#eid').val();//Product ID
	var pr_type=$('#pr_type').val();
	var bom_id=$('#bom_id').val();
	var po_req_no=$('#po_req_no').val();
	var sales_order_trn_id=$('#sales_order_trn_id').val();
	var bom_version_id = $('#bom_version_id').val();	
	var sp_id = $('#work_order_id').val();
	var wo_type = $("#wo_type").val();
	var extra_stock = $("#extra_stock").val();
	var ext_stock_vendor_id = $("#ext_stock_vendor_id").val();
	var jobwork_type = $('#job_work_type').val();
	var rp_id = "";
	if(wo_type == "direct_jobcard"){
		rp_id = $("#job_rp_id").val();
		check_product_btn(rp_id);
	}else{
		check_product_btn(sp_id);	
	}
	
	/*if(rp_id == ""){
		rp_id = $("#wo_rp_id").val();
	}*/
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : 'get_tree_request_new',eid:eid,pr_type:pr_type,bom_id:bom_id,po_req_no:po_req_no,sales_order_trn_id:sales_order_trn_id,bom_version_id:bom_version_id,sp_id:sp_id,main_mode:main_mode,wo_type:wo_type,rp_id:rp_id,extra_stock:extra_stock,ext_stock_vendor_id:ext_stock_vendor_id,jobwork_type:jobwork_type},
		success: function(data){		
			Unloading();
			
			$('#show_tree_request').html(data);			
			get_all_requested_qty();			
			get_inhouse_request_qty($('#in_process_qty_main').val());
			get_bom_request_qty($('#in_process_qty_main').val());
			get_po_request_qty($('#in_process_qty_main').val());
			work_order_submit_per();
			check_auto_mrp();
			toggle_process_stock_button($("#work_order_id").val(),$("#wo_rp_id").val());
		}		
		
	});
}

function get_all_requested_qty()
{

	var cnt=Number($('#counter_tree').val());
	var po_req_no=$('#po_req_no').val();
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : 'get_all_requested_qty',po_req_no:po_req_no },
		success: function(response){
			
				//console.log(response);
				var data=JSON.parse(response);
				//console.log(data);
				var array=data.data;
				Unloading();
				if(data.count>0)
				{
					for(var i=0;i< array.length;i++)
					{
						
						//console.log(array[i]['rp_req_qty']);
						var cnt_var=array[i]['row_cnt'];
						//alert(cnt_var);
						$('.rt'+cnt_var).val(array[i]['rp_req_qty']);
						$('.pt'+cnt_var).val(array[i]['in_process_qty']);
						$('.po'+cnt_var).val(array[i]['rp_po_qty']);
						
						$('.rt'+cnt_var).attr('readonly',true);
						$('.pt'+cnt_var).attr('readonly',true);
						$('.po'+cnt_var).attr('readonly',true);
						
						
						// get under tree qty
						get_under_tree(cnt_var);
						//get_under_tree(array[i]['rp_id']);
						//console.log('check qty:'+array[i]['rp_id']);
						//var trn_id=cnt_var;
						
					}
					//alert('Update from : get_all_requested_qty');
					
				}
				//alert(data.count);
				/*if(data.count>0)
				{
					//alert(data.count);
					$('#req_qty'+data.count_var).val(data.rp_req_qty);
					$('#in_process_qty'+data.count_var).val(data.in_process_qty);
					$('#po_qty'+data.count_var).val(data.rp_po_qty);
					
					$('#req_qty'+data.count_var).attr('readonly',true);
					$('#in_process_qty'+data.count_var).attr('readonly',true);
					$('#po_qty'+data.count_var).attr('readonly',true);
				}
				Unloading();*/
			}		
			
		});
	

}

function get_under_tree(trn_id)
{
	var po_req_no=$('#po_req_no').val();
	//alert(trn_id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "get_under_tree",trn_id:trn_id,po_req_no:po_req_no },
		success: function(data){
			
			var resp =JSON.parse(data);
			//console.log(resp);
			Unloading();
			if(resp.trn_ids)
			{
				var exp_trn_ids=(resp.trn_ids).split(",");
				
				var i;
				
				var inh_qty=parseFloat($('.pt'+trn_id).val());
				
				//alert('Update from : get_under_tree');
				var h=exp_trn_ids;
				//alert(h);
				for (i = 0; i < exp_trn_ids.length; ++i) {
					
					$('.csb'+exp_trn_ids[i]).show();
					
					var total_qty=parseFloat($('.tct'+exp_trn_ids[i]).val());
					
					var req_qty=(parseFloat(total_qty))*(parseFloat(inh_qty));
					//alert(inh_qty);
					//alert(total_qty);
					var check_qty=parseFloat($('.inpc'+exp_trn_ids[i]).val());
					
					$('.rt'+exp_trn_ids[i]).val(req_qty.toFixed(4));
					
					if(check_qty!='1')
					{
						$('.pt'+exp_trn_ids[i]).val(req_qty.toFixed(4));
						$('.po'+exp_trn_ids[i]).val(0);
					}
					else
					{
						$('.pt'+exp_trn_ids[i]).val(0);
						$('.po'+exp_trn_ids[i]).val(req_qty.toFixed(4));
					}
					//console.log(total_qty);
				}
				
				$('.csb'+trn_id).prop("disabled",true).removeClass("btn-primary").addClass("btn-danger").html("Requested");
				
				$('.rt'+trn_id).attr("readonly",true);
				$('.pt'+trn_id).attr("readonly",true);
				$('.po'+trn_id).attr("readonly",true);
				
				
			}
			else
			{
				$('.csb'+trn_id).prop("disabled",true).removeClass("btn-primary").addClass("btn-danger").html("Requested");
			}
			
			//get_all_requested_qty();
			//lock_main_request();
			/*if(data=='0')
			{
				get_tree_request();
				
				/
			}*/
			// Unloading();
		}		
		
	});
}


function lock_main_request()
{
	var po_req_no=$('#po_req_no').val();
	var eid=$('#eid').val();
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : 'lock_main_request',eid:eid,po_req_no:po_req_no },
		success: function(response){
			
			
			if(response.trim()=='1')
			{
				
				$('#rp_req_qty').attr('readonly',true);
				$('#in_process_qty_main').attr('readonly',true);
				$('#rp_po_qty').attr('readonly',true);
				$('#set_process_btn').hide();
				
			}
			Unloading();
		}		
		
	});
}
function cal_po_qty(){
	
	
	var bom_version_id = $("#bom_version_id").val();
	var process_status=$("#process_status").val();
	var rp_req_qty=parseFloat($("#rp_req_qty").val());
	//pathik start date : 12-12-2020 
	// bom check if yes process qty show other wise hidden and purchase qty only show 
	var bom_check=$("#bom_check").val();
	
	
	if(bom_check>"0"){
		
		
			//if(type=="2"){
				//pathik end	
				var rp_po_qty=parseFloat($("#rp_po_qty").val());
				rp_po_qty=getNum(rp_po_qty);
				rp_req_qty=getNum(rp_req_qty);
				
				var process_qty=parseFloat(rp_req_qty)-parseFloat(rp_po_qty);
					//alert(rp_req_qty);
					//alert(rp_po_qty);
					if(process_qty<"0"){
						toastr.error("Wrong Qty Enter Please Check", "ERROR");
						//$("#in_process_qty_main").val(0);
						$('#set_process_btn').hide();
						$('#save').hide();
						$('#req_val').hide();
					}else{
						//alert("fa");
						$('#set_process_btn').show();
						$('#save').show();
						$('#req_val').show();
						
					}
					
					$("#in_process_qty_main").val(process_qty);
					
				}else{
					
					if(bom_version_id == '')
					{
						$("#in_process_qty_main").val("0");
						$("#rp_po_qty").val(rp_req_qty);
						$('#set_process_btn').hide();
						$('.proc1').hide();
						$('#req_val').html("<u><center><span style='color:red;font-size:20px;'>Note : This  is Only Purchase Product</br> If Your Process Product Create BOM First</span></center></u>");
						$('#save').show();
						$('#mode').val("purchase_mode");
					}
					
				}
	//pathik end
}
function dd(){
	$('.dispbtn').show();
	$('.dispbtn').hide();
	//alert("dsa");
}
function show_btn(number,sub_num,hh){
	//alert(hh);
	/* if(hh==1){
	alert(sub_num);	
} */
var cla="sho"+number+""+sub_num;
cla = cla.replace(".", "");
cla = cla.replace(".", "");
cla = cla.replace(".", "");
rcla = "r"+cla;
	//alert(rcla);
	
	if($("a").hasClass(cla)){
		var cli=cla;
		var res = cli.replace(".", "");
		var res = cli.replace(".", "");
		var res = cli.replace(".", "");
		var req=$("#req"+res).val();
		if(req==0){
			//request done
			$("."+cla).hide();
			
			var new_number=number+"."+sub_num;
			var cla_new="sho"+number+""+sub_num+"1";
			cla_new = cla_new.replace(".", "");
			cla_new = cla_new.replace(".", "");
			cla_new = cla_new.replace(".", "");
			rcla_new = "r"+cla_new;
			if($("a").hasClass(cla_new)){
				show_btn(new_number,1);
			}else if($("a").hasClass(rcla_new)){
				show_btn(new_number,1);
			}
		}else{
			//request not done
			
			var cla1="sho"+number;
			var cli1=cla1;
			var res1 = cli1.replace(".", "");
			var res1 = res1.replace(".", "");
			var res1 = res1.replace(".", "");
			var req1=$("#req"+res1).val();
			req1=parseFloat(req1);
			if(isNaN(req1)){
				req1=0;
			}
			
			if(req1==0){
				$("."+cla).show();
			}else{
				
				$("."+cla).hide();
			}
		}
		var new_sub_num=parseFloat(sub_num)+parseFloat(1);
		var new_class1="sho"+number+""+new_sub_num;
		
		new_class1 = new_class1.replace(".", "");
		new_class1 = new_class1.replace(".", "");
		new_class1 = new_class1.replace(".", "");
		new_class1 = new_class1.replace(".", "");
		rnew_class1= "r"+new_class1;
		//alert(new_class1);
		
		if($("a").hasClass(new_class1)){
			//alert(new_class1);
			show_btn(number,new_sub_num);
		}else if($("a").hasClass(rnew_class1)){
			//alert(new_class1);
			//alert(rnew_class1);
			show_btn(number,new_sub_num,1);
			//alert(number);
			//alert(sub_num);
			//alert(new_sub_num);
		}
		var new_number=number+"."+sub_num;
		var cla_new="sho"+number+""+sub_num+"1";
		cla_new = cla_new.replace(".", "");
		cla_new = cla_new.replace(".", "");
		cla_new = cla_new.replace(".", "");
		cla_new = cla_new.replace(".", "");
		
		if($("a").hasClass(cla_new)){
			
			show_btn(new_number,1);
		}
		
	}else if($("a").hasClass(rcla)){
		
		var cli=cla;
		//alert(cli);
		var res = cli.replace(".", "");
		var res = cli.replace(".", "");
		var res = cli.replace(".", "");
		var req=$("#req"+res).val();
		if(req==0){
			//request done
			$("."+cla).hide();
			
			var new_number=number+"."+sub_num;
			var cla_new="sho"+number+""+sub_num+"1";
			cla_new = cla_new.replace(".", "");
			cla_new = cla_new.replace(".", "");
			cla_new = cla_new.replace(".", "");
			rcla_new = "r"+cla_new;
			if($("a").hasClass(cla_new)){
				show_btn(new_number,1);
			}else if($("a").hasClass(rcla_new)){
				show_btn(new_number,1);
			}
		}else{
			//request not done
			
			var cla1="sho"+number;
			var cli1=cla1;
			var res1 = cli1.replace(".", "");
			var res1 = res1.replace(".", "");
			var res1 = res1.replace(".", "");
			var req1=$("#req"+res1).val();
			req1=parseFloat(req1);
			if(isNaN(req1)){
				req1=0;
			}
			
			if(req1==0){
				$("."+cla).show();
			}else{
				
				$("."+cla).hide();
			}
		}
		var new_sub_num=parseFloat(sub_num)+parseFloat(1);
		var new_class1="sho"+number+""+new_sub_num;
		
		new_class1 = new_class1.replace(".", "");
		new_class1 = new_class1.replace(".", "");
		new_class1 = new_class1.replace(".", "");
		new_class1 = new_class1.replace(".", "");
		rnew_class1= "r"+new_class1;
		//alert(new_class1);
		
		if($("a").hasClass(new_class1)){
			//alert(new_class1);
			show_btn(number,new_sub_num);
		}else if($("a").hasClass(rnew_class1)){
			//alert(new_class1);
			//alert(rnew_class1);
			show_btn(number,new_sub_num,1);
			//alert(number);
			//alert(sub_num);
			//alert(new_sub_num);
		}
		var new_number=number+"."+sub_num;
		var cla_new="sho"+number+""+sub_num+"1";
		cla_new = cla_new.replace(".", "");
		cla_new = cla_new.replace(".", "");
		cla_new = cla_new.replace(".", "");
		cla_new = cla_new.replace(".", "");
		
		if($("a").hasClass(cla_new)){
			
			show_btn(new_number,1);
		}
		
	} 
	
}
function change_status(number1){
	//$('.dispbtn').hide();
	var number1= number1.replace(".", "");
	var number1 = number1.replace(".", "");
	var number1 = number1.replace(".", "");
	var number1 = number1.replace(".", "");
	$("#reqsho"+number1).val("0");
}
function check_req_qty(cnt,amount){
	var req_qty=parseFloat($("#req_qty"+cnt).val());
	//alert(amount);
	//alert(req_qty);
	amount=parseFloat(amount);
	amount=getNum(amount);
	req_qty=getNum(req_qty);
	
	if(req_qty<amount){
		toastr.warning("Please Enter Request Qty", "ERROR");
		$("#req_qty"+cnt).focus();
		$("#po_qty"+cnt).val(0);
		$("#in_process_qty"+cnt).val(0);
		
	}
}
function check_reseve_qty(cnt,amount){
	var at_stock=parseFloat($("#at_stock"+cnt).val());
	var req_qty=parseFloat($("#req_qty"+cnt).val());
	amount=parseFloat(amount);
	amount=getNum(amount);
	at_stock=getNum(at_stock);
	req_qty=getNum(req_qty);
	
	//alert(cnt);
	//alert(at_stock);
	//alert(req_qty);
	if(at_stock>0){
		if(at_stock>req_qty){
			//req_qty
			//alert(amount);
			//alert(req_qty);
			//alert(amount);
			if(req_qty<amount){
				//alert(amount);
				toastr.warning("Not Enter More then Request Qty", "ERROR");
				$("#at_reserve"+cnt).focus();
				$("#at_reserve"+cnt).val(0);
				// $(".csb"+cnt).hide();
			}else{
				//$(".csb"+cnt).show();
			} 
		}else{
			//at_stock
			//alert("2");
			if(at_stock<amount){
				//alert(amount);
				toastr.warning("Not Enter More then Current Stock", "ERROR");
				$("#at_reserve"+cnt).focus();
				$("#at_reserve"+cnt).val(0);
				 //$(".csb"+cnt).hide();
				}else{
				//$(".csb"+cnt).show();
			} 
		}
	}else{
		toastr.warning("Not Enter More then Current Stock", "ERROR");
		$("#at_reserve"+cnt).focus();
		$("#at_reserve"+cnt).val(0);
		//$(".csb"+cnt).hide();
	}
	get_reserve_inner(cnt);
}
function get_reserve_inner(cnt)
{
	var req_qty=Number($('#req_qty'+cnt).val());
	var at_reserve=Number($('#at_reserve'+cnt).val());
	var in_process_qty=Number($('#in_process_qty'+cnt).val());
	var po_qty=Number($('#po_qty'+cnt).val());
	var tot=0;
	
	if(in_process_qty!=0){
		if(po_qty===0){
			tot=req_qty-at_reserve;
			if(tot<0){
				tot=0;
			}
			//$('#in_process_qty'+cnt).val(tot);
			if($('#in_process_qty'+cnt).attr("readonly")){
				//alert("yes");
				$('#po_qty'+cnt).val(tot);
			}else{
				//alert("no");
				$('#in_process_qty'+cnt).val(tot);
			}
		}
	}
	if(po_qty!=0){
		if(in_process_qty===0){
			tot=req_qty-at_reserve;
			if(tot<0){
				tot=0;
			}
			//$('#po_qty'+cnt).val(tot);
			if($('#in_process_qty'+cnt).attr("readonly")){
				//alert("yes");
				$('#po_qty'+cnt).val(tot);
			}else{
				//alert("no");
				$('#in_process_qty'+cnt).val(tot);
			}
		}
	}
	
	if(in_process_qty==0){
		if(po_qty===0){
			tot=req_qty-at_reserve;
			if(tot<0){
				tot=0;
			}
			//alert("ds");
			if($('#in_process_qty'+cnt).attr("readonly")){
				//alert("yes");
				$('#po_qty'+cnt).val(tot);
			}else{
				//alert("no");
				$('#in_process_qty'+cnt).val(tot);
			}
			
		}
	}
}
function check_submit_btn(){
	
	var product_qtyltr=document.getElementsByName('submi[]');
	var cnt=product_qtyltr.length;
	var total_ltr=0
	for(var k=0;k<cnt;k++)
	{
		total_ltr+=parseFloat(product_qtyltr[k].value);
	}
	if(total_ltr>0){
		$("#save").hide();
	}else{
		$("#save").show();
	}
	//alert(total_ltr);
}
function work_order_submit_per()
{	
	var wo_type = $("#wo_type").val();
	var rp_id = ""; 
	if(wo_type == "direct_jobcard"){
		rp_id = $("#job_rp_id").val();
		Loading()
		$.ajax({
				type: "POST",
				url: root_domain+production_domain+'app/request_product/',
				data: { mode : 'jobcard_submit_per',rp_id:rp_id },
				success: function(response){
					response=response.trim();
				//alert(response);
				Unloading()
				if(response.trim()=='1')
				{
					$("#save").show();
				}else{
					$("#save").hide();
				}
			}
		});
	}else{
		var work_order_id=$('#work_order_id').val();
		if(work_order_id){
			Loading()
			$.ajax({
				type: "POST",
				url: root_domain+production_domain+'app/request_product/',
				data: { mode : 'work_order_submit_per',work_order_id:work_order_id },
				success: function(response){
					response=response.trim();
					Unloading()
				//alert(response);
				if(response.trim()=='1')
				{
					$("#save").show();
				}else{
					$("#save").hide();
				}
				
				var bom_version_id = $("#add_bom_version_id").val();
				var in_process_qty_main=$('#in_process_qty_main').val();
				var rp_po_qty=$('#rp_po_qty').val();
				//alert(typeof bom_version_id);
				if(typeof bom_version_id==="undefined"){
					//alert("ds");
				}else {
					check_child_product(work_order_id);
				}
				
				
			}
			
		});
		}else{
			$("#save").hide();
		}
	}
}

/* START JAYESH */

function check_child_product(work_order_id)
{
	// alert("dsa");
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : 'check_child_product',work_order_id:work_order_id },
		success: function(response){
						//alert(response);
						Unloading()
						if(response.trim()=='1')
						{
							$("#save").show();
						}else{
							
							$("#save").hide();
						}
					}
				});
}


function add_work_order_product(product_id,qty,unit_id){
	
	var qty = $("#rp_req_qty").val();	
	var product_name=$("#po_product_name").val();
	var product_qty=$("#product_qty").val();
	var sales_order_date = $("#sales_order_date").val();
	var sales_order_no = $("#sales_order_no").val();
	$("#product_add_type").val("main");
	$("#mtype").text('Add');
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "add_work_order_product",product_id:product_id,qty:qty,product_name:product_name,sales_order_date:sales_order_date,sales_order_no:sales_order_no,product_qty:product_qty,unit_id:unit_id},
		success: function(response)
		{
			Unloading();
			$("#show_product_from").html(response);
			$("#add_workorder_product").modal("show");
			set_product_load_for_wo_product_id();
			$(".select33").select2({
				width: '100%'
			});
		}
	});	
}



function add_sub_product(rp_id,sub_product_id,main_product_id,product_qty,unit_id){
	$("#product_add_type").val("sub");
	var eid=$("#eid").val();
	var qty = $("#rp_req_qty").val();
	var sales_order_date = $("#sales_order_date").val();
	var sales_order_no = $("#sales_order_no").val();	
	var product_name=$("#po_product_name").val();
	$("#mtype").text('Add');
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "add_work_order_sub_product",sub_product_id:sub_product_id,rp_id:rp_id,qty:product_qty,main_product_id:main_product_id,product_name:product_name,sales_order_date:sales_order_date,sales_order_no:sales_order_no,unit_id:unit_id},
		success: function(response)
		{
			Unloading();
			$("#show_sub_product_from").html(response);
			set_product_load_for_wo_sub_product_id();
			$("#add_workorder_sub_product").modal("show");
			$(".select34").select2({
				width: '100%'
			}); 
			
		}
	});	
}


function save_work_order_product()
{
	var extra_stock = $("#extra_stock").val();
	var ext_stock_vendor_id = $("#ext_stock_vendor_id").val();

	if($("#wo_product_type").val()==="")
	{		
		toastr.warning("Select Product Type", "ERROR");
		$("#wo_product_type").select2("focus");
		return false;
	}
	if($("#wo_product_id").val()==="")
	{		
		toastr.warning("Select Product", "ERROR");
		$("#wo_product_id").select2("focus");
		return false;
	}
	if($("#product_qty").val()=="")
	{		
		toastr.warning("Please Enter Qty", "ERROR");
		$("#product_qty").focus();
		return false;
	}
	var wo_product_type = $("#wo_product_type").val();
	var main_product_id = $("#prod_id").val();
	var wo_product_id = $("#wo_product_id").val();
	var qty = $("#rp_req_qty").val();	
	var product_qty = $("#product_qty").val();	
	var bom_version_id = $("#add_bom_version_id").val();	
	var bom_version_id = $("#add_bom_version_id").val();
	var sp_id = $("#work_order_id").val();
	var branch_id = $("#branch_id").val();
	var customer_id = $("#customer_id").val();
	var jobwork_type = $('#job_work_type').val();

	Loading()
	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "save_work_order_product",main_product_id:main_product_id,wo_product_id:wo_product_id,qty:qty,product_qty:product_qty,bom_version_id:bom_version_id,sp_id:sp_id,wo_product_type:wo_product_type,branch_id:branch_id,customer_id:customer_id,jobwork_type:jobwork_type,extra_stock:extra_stock,ext_stock_vendor_id:ext_stock_vendor_id},
		success: function(response)
		{
			var data=JSON.parse(response);
			Unloading();
			if(data.msg=='1'){
				toastr.success("ADDED SUCCESSFULLY", "SUCCESS");
				$("#add_workorder_product").modal("hide");
				if(data.process_required == '1'){
				var r= confirm(" Are you want to update process ?");
				if(r) {
						// show_product_process(1,wo_product_id,data.rp_id);
						show_product_process(1,wo_product_id,'','',data.rp_id);
					}else{
						Loading();
						setTimeout(function(){
							Unloading();
							// add_field();
						},300);
						get_tree_request();
					}
				}else{
					get_tree_request();
				
				}
				check_main_process_request();
					check_poreq_status();
					return false;
				}else{
					toastr.warning("SOMETHING WRONG", "ERROR")
				}
			}
		});	
	
}

function save_work_order_sub_product()
{
	var sub_product_id = $("#wo_sub_product_id").val();
	var main_product_id = $("#main_product_id").val();
	var qty = $("#sub_qty").val();
	var branch_id = $("#branch_id").val();
	var jobwork_type = $('#job_work_type').val();
	var extra_stock =  $('#extra_stock').val();
	var ext_stock_vendor_id =  $('#ext_stock_vendor_id').val();
	
	var rp_id = $("#rp_id").val();
	var product_qty = $("#sub_product_qty").val();
	var bom_version_id = $("#add_sub_bom_version_id").val();	
	var wo_product_type = $("#wo_sub_product_type").val();
	var customer_id = $("#customer_id").val();
	if($("#wo_sub_product_type").val()==="")
	{		
		toastr.warning("Select Product Type", "ERROR");
		$("#wo_sub_product_type").select2("focus");
		return false;
	}
	if(sub_product_id == "")
	{
		toastr.warning("Select Product ", "ERROR");
		$("#wo_sub_product_id").select2("focus");
		return false;
	}
	if(bom_version_id == "")
	{
		toastr.warning("Select Bom Version", "ERROR");
		$("#add_sub_bom_version_id").select2("focus");
		return false;
	}
	if(product_qty == "")
	{
		toastr.warning("Enter Qty", "ERROR");
		$("#sub_product_qty").focus();
		return false;
	}

	/*var r= confirm(" Are you want to update process ?");
		if(r) {
			direct_show_product_process(sub_product_id,rp_id);
		}else{
			Loading();
			setTimeout(function(){
				Unloading();
				add_field();
			},300);	*/
			
			Loading()
			$.ajax({
				type: "POST",
				url: root_domain+production_domain+'app/request_product/',
				data: { mode : "save_work_order_sub_product",sub_product_id:sub_product_id,main_product_id:main_product_id,qty:qty,rp_id:rp_id,product_qty:product_qty,bom_version_id:bom_version_id,wo_product_type:wo_product_type,branch_id:branch_id,customer_id:customer_id,jobwork_type:jobwork_type,extra_stock:extra_stock,ext_stock_vendor_id:ext_stock_vendor_id},
				success: function(response)
				{
					
					var data=JSON.parse(response);
					Unloading();
					if(data.msg=='1'){
						toastr.success("ADDED SUCCESSFULLY", "SUCCESS");
						$("#add_workorder_sub_product").modal("hide");
						
						if(data.process_required == '1'){
							var r= confirm(" Are you want to update process ?");
							if(r) {
								
								show_product_process(1,sub_product_id,'','',data.rp_id);
							}else{
								Loading();
								setTimeout(function(){
									Unloading();
									// add_field();
								},300);
								get_tree_request();
							}
						}else{
							get_tree_request();
						}
						
						return false;
						// get_tree_request();
						// return false;
					}else{
						toastr.warning("SOMETHING WRONG", "ERROR")
					}
				}
			});	
	//}
}
function delete_work_order_product(prd_id,rp_id,parent_delete_flag,sp_id)
{
	
	var qty = $("#req_qty").val();	
	if (!confirm("Do you want to delete"))
	{    
		return false;
	}
	else
	{
		Loading();
		$.ajax({

			type: "POST",
			url: root_domain+production_domain+'app/request_product/',
			data: { mode : "delete_work_order_product",rp_id:rp_id,parent_delete_flag:parent_delete_flag,sp_id:sp_id},
			success: function(response)
			{
					Unloading();
				if(response.trim()=="1"){
					toastr.success("DELETED  SUCCESSFULLY", "SUCCESS");
					get_tree_request();
					// location.reload();
					check_main_process_request();
					check_poreq_status();

					return false;
				}else{
					toastr.warning("SOMETHING WRONG", "ERROR")
				}
			}
		});	
	}	
}

/* New changes regarding delete */
/*
function delete_work_order_product(prd_id,rp_id,parent_delete_flag,sp_id)
{
	
	
	var htmlcontent = '<div id="delete_dialog" title="Do Yo Want to Delete  product ?"><p><button><a onclick="single_delete(1,'+rp_id+','+sp_id+',0)">Signle</a></button>&nbsp;&nbsp;<button><a onclick="single_delete(2,'+rp_id+','+sp_id+',1)"> All </a></button>&nbsp;&nbsp;<button><a onclick="single_delete(3,'+rp_id+','+sp_id+',0)"> Cancel </a></button></p></div>';
		$( "#delete_dialog" ).html(htmlcontent);
		$( "#delete_dialog" ).dialog();
}

function single_delete(flag,rp_id,sp_id,parent_delete_flag)
{
	if(flag == '3')
	{
		 $("#delete_dialog").dialog("close");
		 return false;
	}
	else
	{    	
		$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "delete_work_order_product",rp_id:rp_id,parent_delete_flag:parent_delete_flag,sp_id:sp_id},
		success: function(response)
		{
			if(response==="1"){
					toastr.success("DELETED  SUCCESSFULLY", "SUCCESS");
					$("#delete_dialog").dialog("close");
					get_tree_request();
					return false;
				}else{
					toastr.warning("SOMETHING WRONG", "ERROR");
					$("#delete_dialog").dialog("close");
					return false;
				}
		}
		});	
	}
}

*/

function edit_work_order_product(product_id,rp_id,rp_pid,rp_pro_qty){
	$("#product_add_type").val("main");
	var qty = $("#req_qty").val();
	var main_qty = $("#qty").val();
	$("#mtype").text('Edit');
	var product_name=$("#po_product_name").val();
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "edit_work_order_product",product_id:product_id,qty:qty,rp_pid:rp_pid,rp_id:rp_id,rp_pro_qty:rp_pro_qty,product_name:product_name},
		success: function(response)
		{	
			Unloading();
			$("#show_product_from").html(response);
			$("#add_workorder_product").modal("show");
			// load_product_detail(rp_pid);
			$(".select22").select2({
				width: '100%'
			});
			
		}
	});
	
}


function edit_save_work_order_product(rp_id)
{
	var main_product_id = $("#prod_id").val();
	var wo_product_id = $("#wo_product_id").val();
	var qty = $("#req_qty").val();
	var rp_product_qty = $("#product_qty").val();
	var main_qty = $("#qty").val();
	
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "edit_save_work_order_product",main_product_id:main_product_id,wo_product_id:wo_product_id,qty:qty,rp_id:rp_id,rp_product_qty:rp_product_qty,main_qty:main_qty},
		success: function(response)
		{
			Unloading();
			if(response.trim()=="1"){
				toastr.success("ADDED SUCCESSFULLY", "SUCCESS");
				$("#add_workorder_product").modal("hide");
				var r= confirm(" Are you want to update process ?");
				if(r) {
					direct_show_product_process(wo_product_id,rp_id);
				}else{
					Loading();
					setTimeout(function(){
						Unloading();
						// add_field();
					},300);
				}
				get_tree_request();
				return false;

			}else{
				toastr.warning("SOMETHING WRONG", "ERROR")
			}
		}
	});	
	
}

function load_product(type_id, type = 0){ // 1 for wo sub product 
	
	/*Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/direct_jobcard/',
		data: { mode : "load_product", type_id : type_id},
		success: function(data){
			$('#wo_product_id').html(data);
			$('#wo_sub_product_id').html(data);				
			Unloading();
		}
	});*/

	if(type){
		set_product_load_for_wo_sub_product_id(type_id)
	}else{
		set_product_load_for_wo_product_id(type_id)
	}
}

function check_bom_version(product_id ='')
{
	if(product_id == '')
	{
		var product_id=$("#product_id").val();
	}
	
	var branch_id=$("#branch_id").val();	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "check_bom_version_by_product",product_id:product_id,branch_id:branch_id},
		success: function(response)
		{
			$('#bom_version_id').html('');
			if(response != 0)
			{	
				$('#bom_version_id').html(response);					
			}	
			$("#bom_version_id").val("10000");
			$('#bom_version_id').trigger('change');			
			Unloading();	
		}
	});
	
}


function direct_show_product_process(product_id,rp_id='',version_id='')
{
	
	var bom_version_id = version_id;
	if(version_id == ""){
		bom_version_id = $('#bom_version_id').val();
	}
								// alert(version_id)
	$("#mask1").removeClass('hidden');
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { 
			mode : 'get_product_process_data',
			product_id:product_id,
			rp_id : rp_id,
			bom_version_id:bom_version_id,
			edit_id:'1'
			
		},
		success: function(data){
			Unloading();
			$('#mod_per_div_add_process').empty();
			$('#mod_per_div_add_process').html(data);
			$('#rp_id').val(rp_id);

			CKEDITOR.replace( 'process_desc', {
				enterMode: CKEDITOR.ENTER_BR
			});
			
			
			var current_number = $('.process_row').last().attr('data-cid');	

			current_number = current_number ? current_number : 0;
			var new_number = parseInt(current_number) + 1;
			
			$('.process_priority').val(new_number);
			$('.process_priority_label').html(new_number);
			
			load_multislect_process();
			
			$(".ms-container").css('width',"100% !important");
			$('#direct_product_id').val(product_id);
			$('#preview_bom_add_process_modal').modal('show');
			
			if($("#multiple_value").val().length > 0){
				var selProcess = $("#multiple_value").val();
				
				const myArr = selProcess.split(",");
				$("#multiple_value").val('');
					for (const item of myArr) { // You can use `let` instead of `const` if you like
						$('#process_item').multiSelect('select', item);
						// console.log(item)
					}
					
				}
				
				$("#mask1").addClass('hidden');
				updateIDs();
			}		
		});
	
}


function direct_bom_process_add(product_id,bom_version_id,edit_id) {
	var counter = $("#process_item").length;


	var sel_process = [];
	$("#process_item :selected").each(function (i) {
		sel_process[i] = $(this).val();
	});

	var unsel_process = [];
	$("#process_item :not(:selected)").each(function (i) {
		unsel_process[i] = $(this).val();
	});

	// console.log($("#multiple_value").val());
	// return false;

	// if(counter == 0){
	// 	add_field();
	// }else{
		var pro_counter = $("#process_item :selected").length;

		if (pro_counter == 0) {
			toastr.warning("SELECT PROCESS", "ERROR");
			return false;
		}

		var form_data = new FormData();
		
		form_data.append('mode','bom_process_add');
		form_data.append('sel_process',sel_process);
		form_data.append('unsel_process',unsel_process);
		form_data.append('rp_id',$('#rp_id').val());
		form_data.append('product_id',product_id);
		
		form_data.append('bom_id',$("#bom_id").val());
		form_data.append('bom_version_id',bom_version_id);
		form_data.append('multiple_value',$("#multiple_value").val());
		form_data.append('edit_id',edit_id);
		Loading()
		$.ajax({		
			url: root_domain+production_domain+'app/request_product/',
			type: "POST",
			data: form_data,
			contentType: false,
			cache: false,
			processData:false,		
			success: function(response)
			{
			//console.log(response);	
			var arr = jQuery.parseJSON(response);			
				Unloading();
			if(arr.msg == '1') {
				
				$('#preview_bom_add_process_modal').modal('hide');
				toastr.success("BOM PROCESS ADDED SUCCESSFULLY", "SUCCESS");
				
				process_reset();


			}
			else if(arr.msg == 'update') {
				
				process_reset();
				$('#preview_bom_add_process_modal').modal('hide');
				toastr.success("BOM PROCESS UPDATED SUCCESSFULLY", "SUCCESS");
				
				// Unloading();

			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				// Unloading();
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
	// }
	
}


function add_process_value()
{
	var resource_id = '';
	if($("#prod_process_id").val()==="")
	{		
		toastr.warning("Select Process Name", "ERROR");
		$("#prod_process_id").select2("focus");
		return false;
	}
	
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

	if($("#direct_product_id").val()==""){
		if($("#process_sel_product_id").val() != "")
		{
			product_id = $("#process_sel_product_id").val();
		}else
		{
			product_id = $("#product_id").val();
		}
	}
	else{
		product_id = $("#direct_product_id").val();
	}

	
	var process_id = $("#prod_process_id").val();
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
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
			var rp_id = $('#rp_id').val();
			
			var arr = jQuery.parseJSON(response);
			Unloading();			
			if(arr.msg == '1') {

				var process_id = arr.process_id;
				toastr.success("PROCESS ADDED SUCCESSFULLY", "SUCCESS");

				if($("#direct_product_id").val()==""){
					
				// show_product_process(1,product_id);
				show_product_process(1,product_id,'','',rp_id);
			}else{
				
				direct_show_product_process(product_id,rp_id);
			}
			process_reset();
			var r= confirm("Are you want to add QC ?");

			if(r) {
				// Unloading();
				show_qc_modal(process_id,product_id);
			}

		}
		else if(arr.msg == '0') {
			toastr.warning("SOMETHING WRONG", "ERROR")

		}else if(arr.msg == 'exist'){
			toastr.warning("PROCESS ALREADY EXISTS", "ERROR")
		}
		

		// Unloading();

	}
});
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
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "check_duplicate_process", product_id : product_id, process_id: process_id },
		success: function(resnse)
		{
			Unloading();
			if(resnse>0)
			{
				toastr.warning("PROCESS ALREADY EXISTS", "ERROR")
				return false;
			}
			
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
			//alert("test");
			var get_val = $("#multiple_value").val();
			var new_val = get_val.replace(value, "");
			$("#multiple_value").val(new_val);
		}
		
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
function bom_process_add(rp_id='') {
	var counter = $("#process_right li").length;

	if(counter == 0){
		toastr.warning("PLEASE SELECT ANY ONE PROCESS", "ERROR")
		return false;
	}

	var form_data = new FormData();
	var product_id = $("#direct_product_id").val();
	
	if(rp_id!= '')
	{
		var rp_id = rp_id;
	}
	var sel_process = $("#selected_process_ids").val();
	var unsel_process = $("#process_ids").val();
	
	form_data.append('mode','bom_process_add');
	form_data.append('sel_process',sel_process);
	form_data.append('unsel_process',unsel_process);
	form_data.append('branch_id',$("#branch_id").val());
	form_data.append('rp_id',rp_id);
	if($('#process_sel_product_id').val() !=""){
		form_data.append('product_id',$('#process_sel_product_id').val());
		
	}else{
		form_data.append('product_id',product_id);
	}
		// form_data.append('multiple_value',$("#multiple_value").val());
		var edit_id =  $('#edit_id').val();
		if(typeof edit_id != 'undefined')
		{
			form_data.append('edit_id',$('#edit_id').val());
		}
		
Loading()
		$.ajax({		
			url: root_domain+production_domain+'app/request_product/',
			type: "POST",
			data: form_data,
			contentType: false,
			cache: false,
			processData:false,	
			success: function(response)
			{
				
				var arr = jQuery.parseJSON(response);
				Unloading();			
				if(arr.msg == '1') {
					
					
					$('#preview_bom_add_process_modal').modal('hide');
					toastr.success("WORK ORDER PROCESS ADDED SUCCESSFULLY", "SUCCESS");
					$('#in_process_qty_main').attr("readonly", false); 
				//$('#add_wo_prd').css('display','block');
				// if($('#process_sel_product_id').val() ==""){
					//return false;
					// add_field();
			// }
			process_reset();
			//location.href="";
			// Unloading();

		}
		else if(arr.msg == 'update') {
				// if($('#process_sel_product_id').val() ==""){
					// add_field();
			// }
			process_reset();
			$('#preview_bom_add_process_modal').modal('hide');
			toastr.success("WORK ORDER PROCESS UPDATED SUCCESSFULLY", "SUCCESS");
			$('#in_process_qty_main').attr("readonly", false); 
			$('#add_wo_prd').css('display','block');
				// add_field();
				// Unloading();

			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				// Unloading();
			}
			
			get_tree_request();
			
			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
		
		
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
			url: root_domain+production_domain+'app/request_product/',
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
				Unloading();
				if(response.trim() == '1'){
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
				
				// Unloading();
				
			}
		});
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

	function check_process_loss(param1){
		
		if(param1.value<0 || param1.value>100){
			$("#"+param1.id).val('100');
			toastr.warning("LOSS value should be between 0 to 100.", "WARNING");
			return false;
		}
	}


	function check_scrap_tolerance(param1){
		if(param1.value<0 || param1.value>100){
			$("#"+param1.id).val('100');
			toastr.warning("SCRAP tolerance value should be between 0 to 100.", "WARNING");
			return false;
		}
	}

	/* Hide by sanat bcz not used in anywhere */
// 	function add_field()
// 	{
// 		if($("#product_id").val()===""){
// 			toastr.warning("Select Product Name", "ERROR");
// 			$("#product_id").select2('focus');
// 			return false;
// 		}
// 		else if($("#product_base_qty").val()===""){
// 			toastr.warning("Enter Qty", "ERROR")
// 			return false;
// 		}
// 		else if($("#sel_product_id").val()===""){
// 			toastr.warning("Select Product Name", "ERROR");
// 			$("#sel_product_id").select2('focus');
// 			return false;
// 		}
		
		
// 		else if($("#base_qty").val()===""){
// 			toastr.warning("Enter Qty", "ERROR");
// 			$("#base_qty").focus();
// 			return false;
// 		}
// 	// console.log($('#product_type').val());
// 	if(($('#product_type').val() == "3") || ($('#product_type').val() == "5")){

// 	}else{
// 		if($("#pro_version_id").val()===""){
// 			toastr.warning("Select Product Version", "ERROR");
// 			$("#pro_version_id").select2('focus');
// 			return false;
// 		}
// 	}
// 	var tot_standrad_qty=$("#base_qty").val();

// 	/* if(alloted==1){
		
// 		if(multiple_qty==$("#base_qty").val()){
// 			product_base_qty=$("#product_base_qty").val();
// 			product_conv_qty=$("#product_conv_qty").val();
// 		}else{
// 				if(multiple_qty==''){
// 				//	alert('hj');
// 					product_base_qty=$("#product_base_qty").val();
// 					product_conv_qty=$("#product_conv_qty").val();
// 				}else{
// 					//alert('f');
// 					product_base_qty=($("#product_base_qty").val()/$("#base_qty").val())*multiple_qty;
// 					product_conv_qty=($("#product_conv_qty").val()/$("#base_qty").val())*multiple_qty;
// 				}
// 		}
// 	}else{ */
// 		product_base_qty=$("#product_base_qty_hide").val();
// 		product_conv_qty=$("#product_conv_qty_hide").val();
		
// 	//}
// 	var values = [];
// 	$('.get_ms_kg').each(function(){
// 		values.push({ name: this.name, value: this.value }); 
// 	}); 

// 	Loading();	
// 	$.ajax({
// 		type: "POST",
// 		url: root_domain+production_domain+'app/request_product/',
// 		data: { mode : "fieldadd",
// 		tot_standrad_qty:tot_standrad_qty,
// 		invoicetype_id:$("#invoicetype_id").val(),
// 		product_type:$("#product_type").val(),
// 		edit_id:$("#edit_id").val(),
// 		product_id:$("#product_id").val(),
// 		product_base_unit:$("#product_base_unit").val(),
// 		product_base_qty:product_base_qty,
// 		product_conv_unit:$("#product_conv_unit").val(),
// 		product_conv_qty:product_conv_qty,
// 		p_bom_id:$("#p_bom_id").val(),
// 		bom_id:$("#bom_id").val(),
// 		sel_product_id:$("#sel_product_id").val(),
// 		base_qty:$("#base_qty").val(),
// 		conv_qty:$("#conv_qty").val(),
// 		base_unit:$("#base_unit").val(),
// 		conv_unit:$("#conv_unit").val(),
// 				/*product_width:$('#product_width').val(),
// 				product_height:$('#product_height').val(),
// 				product_thickness:$('#product_thickness').val(),
// 				product_density:$('#product_density').val(),*/

// 				/* Start :: Sanat added bom version  -  02-08-2022 */
// 				bom_version_id : $('#pro_version_id').val(),
// 				p_bom_version_id : $('#sel_bom_version_id').val(),
// 				/* End :: Sanat added bom version  -  02-08-2022 */
// 				values : values,
// 				product_kg:$('#product_kg').val() },
// 				success: function(response)
// 				{

// 					if(response=='-1')
// 					{
// 						toastr.info("ALREADY EXISTS", "INFO");
// 						Unloading();				
// 					}
// 					else
// 					{

// 				//var new_level_cnt=Number($('#level_cnt').val())+0.1;
// 				$("#product_type").select2("val","");
// 				$("#product_id").select2("val","");
// 				$("#product_id").select2('focus');
// 				$("#product_qty").val("");
// 				$("#edit_id").val('');
// 				$('#addrow').val('Add');
// 				$('#get_spec_div').hide();
// 				$("#product_base_unit").val("");
// 				$("#product_uom").val("");
// 				$("#product_qty").val("");
// 				$("#product_act_qty").val("");
// 				$("#product_base_qty").val("");
// 				$("#product_base_unit_name").val("");
// 				$("#product_conv_unit_name").val("");
// 				$("#product_conv_qty").val("");

// 				/*Jayesh Added : 04-08-2021 */
// 				$('#addprocess').val('Add');
// 				$('#pro_version_id').empty().append('<option value">Select Product Version</>');

// 				Unloading();
// 				/*//load_bom_version_datatable();
// 				if(alloted==1){
// 					show_alloted_data();
// 				}else{
// 					show_data();
// 				}*/
// 					//show_data();
// 				}
// 			}
// 		});
// }

function check_bom_version(product_id = '')
{
	if(product_id == '')
	{
		var product_id=$("#product_id").val();
	}
	
	var branch_id=$("#branch_id").val();
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "check_bom_version_by_product",product_id:product_id,branch_id:branch_id},
		success: function(response)
		{ 
					Unloading();
			$('#add_bom_version_id').html('');
			if(response != 0)
			{
				$('#add_bom_version_id').html(response);
				$('#add_sub_bom_version_id').html(response);
			}
			else
			{
				$('#add_bom_version_id').html('<option selected="selected" value="10000">R&D</option>');
				$('#add_sub_bom_version_id').html('<option selected="selected" value="10000">R&D</option>');
				
			}
			$("#add_bom_version_id").val("10000");
			$('#add_bom_version_id').trigger('change');
			$("#add_sub_bom_version_id").val("10000");
			$('#add_sub_bom_version_id').trigger('change');
					//$('#add_bom_version_id').html(response);
					
				}
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

function show_product_process(show_popup,product_id="",bom_version_id="",edit_id="",rp_id='')
{
	if(product_id != ""){
		$("#direct_product_id").val(product_id);	
	}
							// $("#direct_product_id").val('');
							
							//	$("#rp_id").val('');
							
							if(rp_id != '')
							{
								rp_id = rp_id;
							}
							else{
								rp_id = '';
							}
							

							$("#mask1").removeClass('hidden');

							setTimeout(function(){ 
								if(product_id != ""){
									product_id = product_id;
								}
								if(product_id == ""){
									product_id = $("#product_id").val();
								}
								if(bom_version_id == ""){
									bom_version_id = $("#pro_version_id").val();
								}
							/*if(edit_id == ""){
								
							}*/
							edit_id = 1;
								// var product_id = $("#product_id").val();
								// var bom_version_id = $("#pro_version_id").val();
								
							//alert("tets");
							
							Loading()	
							
							$.ajax({
								type: "POST",
								url: root_domain+production_domain+'app/request_product/',
								data: { 
									mode : 'get_product_process_data',
									product_id:product_id,
									rp_id:rp_id,
									bom_version_id:bom_version_id,
									edit_id :edit_id
								},
								success: function(data){

									Unloading();
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
												// console.log(selProcess);
												const myArr = selProcess.split(",");
												$("#multiple_value").val('');
												for (const item of myArr) { // You can use `let` instead of `const` if you like
												//alert(item);
												$('#process_item').multiSelect('select', item);

											}

										}

									}else{
										bom_process_add();
									}


									$("#mask1").addClass('hidden');
								}		
							});
						},500);

						}


						function check_product_btn(sp_id)
						{
							var wo_type = $("#wo_type").val();
							var rp_id = "";
							if(wo_type == "direct_jobcard"){
								rp_id = $("#job_rp_id").val();
							}

							Loading();
							$.ajax({
								type: "POST",
								url: root_domain+production_domain+'app/request_product/',
								data: { mode : 'check_work_order_process',sp_id:sp_id,rp_id:rp_id,wo_type:wo_type},
								success: function(data){
									Unloading();
									if(data.trim()== '1')
									{
										$('#add_wo_prd').css('display','block');
										$('#process_mode').text('Edit');
									}
									else
									{
										$('#add_wo_prd').css('display','none');
										$('#process_mode').text('Add');
									}
									
								}	
		//yUnloading();	
		
	});
						}
						function check_product_unit(product_id,id)
						{
							Loading();
							$.ajax({
								type: "POST",
								url: root_domain+production_domain+'app/request_product/',
								data: { mode : 'check_product_unit',product_id:product_id},
								success: function(data){
									Unloading();
									if(data != 0)
									{
										if(id==1)
										{
											$('#product_unit').text(data);
											$('.product_unit').text(data);
										}
										else
										{
											$('#sub_product_unit').text(data);
											$('.product_unit').text(data);
										}
									}
									return false;	
								}
								
							});
						}
						function pending_approval()
						{
							$( "#dialog" ).dialog();
	//toastr.warning("Please Contact to  Authorise Person For Approve Requested Product", "ERROR");
	return false;
}
function workorder_permission(rp_id)
{
	$("#wrp_id").val(rp_id);
	$('#work_order_approve').modal('show');
	Loading()
	$.ajax({

		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : 'get_requested_proudct_details',rp_id:rp_id},
		success: function(data){
		// console.log(data);
		var arr = jQuery.parseJSON(data);
			Unloading();
		$('#wo_product_name').text(arr.product_name);
		$('#wo_qty').text(arr.rp_req_qty);

		load_wo_hist_datatable();
		
	}
});
}


function add_wo_apprv_hist(){
	
	
	var form_data = {
		mode:"add_wo_apprv_hist",
		approve_status:$('#wo_approve_status').val(),
		approve_remark:$('#wo_approve_remark').val(),
		rp_id:$('#wrp_id').val(),
		po_approve_status: $('#wo_approve_status').val()
	};
	var status = 'Approved';
	if($('#wo_approve_status').val() === '2'){
		status = 'Rejected';
	}
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: form_data,
		success: function(response)
		{
			Unloading();
			if(response){
				$('#wo_approve_status').select2("val","1");
				$('#wo_approve_remark').val("");
				load_wo_hist_datatable();
				get_tree_request();
                //load_datatable();
            } else {
            	toastr.warning("You have already "+ status, "ERROR");
            	$('#wo_approve_status').select2("val","1");
            	$('#wo_approve_remark').val("");
            }
            
            // Unloading();
            return false;
            
        }
    });	
}   


function load_wo_hist_datatable(){
	var rp_id = $('#wrp_id').val();
	
	$("#order-wo-history-datatable1").dataTable({
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
		"aLengthMenu": [[5, 10, 20, -1], [5, 10, 20,"All"]],
		"iDisplayLength": 5,
		"sAjaxSource": root_domain+production_domain+'app/request_product/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "load_wo_hist_datatable" }, { "name": "rp_id", "value": rp_id }  );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
	// validate the comment form when it is submitted  
	
} 

function load_product_detail(pro_id) {
Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "load_productdata",eid :pro_id },
		success: function(response)
		{
			var resp = jQuery.parseJSON(response); 
			Unloading();
			
			$('#p_bom_id').val(resp.bom_id);
			$('#product_base_unit_name').val(resp.base_unit_name);
			$('#product_base_unit').val(resp.product_base_unit);
			$('#product_base_qty').val(resp.product_base_qty);
			$('#product_qty').val(resp.product_base_qty);
			$('#sub_product_qty').val(resp.product_base_qty);
			$('#product_base_qty_hide').val(resp.product_base_qty);
			$('#product_qty_hide').val(resp.product_base_qty);
			$('#sub_product_qty_hide').val(resp.product_base_qty);
			
			$('#product_conv_unit_name').val(resp.conv_unit_name);
			$('#product_conv_unit').val(resp.product_conv_unit);
			$('#product_conv_qty').val(resp.product_conv_qty);
			$('#sub_product_conv_qty').val(resp.product_conv_qty);
			$('#product_conv_qty_hide').val(resp.product_conv_qty);
			$('#sub_product_conv_qty_hide').val(resp.product_conv_qty);
			
			$('#product_spec_hid').val(resp.product_specification);
			$('#product_density').val(resp.m_type_density);

			$("#pro_base_unit").html(resp.base_unit_name);
			$("#pro_conv_unit").html(resp.conv_unit_name);
			
			
			if(resp.product_specification!=0)
			{
				
				$('#get_spec_div,#get_sub_spec_div').show();

				if($("#product_add_type").val()=="sub"){
					$('#get_spec_div').empty();
					$('#get_sub_spec_div').empty().prepend(resp.product_specification_code);
				}else{
					$('#get_sub_spec_div').empty()
					$('#get_spec_div').empty().prepend(resp.product_specification_code);
				}
				
				get_ms_kg();
				
				$('#product_kg').val('');
				
			}
			else
			{
				
				$('#get_spec_div,#get_sub_spec_div').hide();
				$('#product_kg').val('');
			}

			
			
		}
	});

	
}

function get_ms_kg(){
	var msid = $('#msid').val();
	var values = [];
	$('.get_ms_kg').each(function(){
		values.push({ name: this.name, value: this.value }); 
	});
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "get_product_specification_cal", values : values, msid : msid },
		success: function(response)
		{
			Unloading();
			$('#product_kg').val(response);
		}
	});
}
function set_kg_to_qty()
{
	
	var product_add_type = $("#product_add_type").val();
	var product_id=$('#product_id').val();
	var qty = $("#qty").val();

	if(product_add_type == "main"){
		product_id=$('#wo_product_id').val();
	}else if(product_add_type == "sub"){
		product_id=$('#wo_sub_product_id').val();
	}
	
	var product_qty=$('#product_qty').val();
	var product_kg=$('#product_kg').val().trim();
	
	// alert(product_id)
	//alert(product_kg);
	
	if($('#set_kg').is(":checked"))
	{
		// if(product_add_type == "main"){
		// 	$('#product_qty').val(product_kg);
		// }else if(product_add_type == "sub"){
		// 	alert(product_kg)
		// 	$('#sub_product_qty').val(product_kg);
		// }
		$('#product_qty').val(qty*product_kg);
		$('#sub_product_qty').val(qty*product_kg);
		$('#product_base_qty_hide').val(qty*product_kg);
		$('#product_conv_qty').val(qty*product_kg);
		$('#product_conv_qty_hide').val(qty*product_kg);
		
	}
	else
	{
		// alert(product_id)
		// load_product_detail(product_id);
	}
}

function show_current_stock_by_product(rp_id,product_id,purchase_unit,customer_id=""){
	
	var qty = $("#rp_req_qty").val();	
	var product_name=$("#po_product_name").val();
	var product_qty=$("#product_qty").val();
	var sales_order_date = $("#sales_order_date").val();
	var sales_order_no = $("#sales_order_no").val();
	var actions = 1; /* for insert and 2 for edit*/

	if(customer_id == '0'){
		customer_id = "";
	}
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "get_current_stock_by_product",rp_id:rp_id,product_id:product_id,purchase_unit:purchase_unit,actions:actions,customer_id:customer_id},
		success: function(response)
		{
			Unloading();
			$("#current_stock").modal("show");
			$("#current_pname").html(product_name);
			$("#show_current_sctock_form").html(response);
			return false;
		}
	});	
}

function current_stock_save(rp_id,product_id,action){	


	var godown_stock_arr=[];
	var godown_ids_arr=[];
	var reserveid_arr=[];		
	
	
	var j = 0;
	$('input.godown_stock').each(function(){ 	
		if($(this).val() != '')
		{
			godown_stock_arr[j]=$(this).val();
			godown_ids_arr[j] = this.id;
			reserveid_arr[j]=$(this).attr('data-reserveid');	
			
		}
		j++;
	}); 
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "reserve_stock_add",rp_id:rp_id,product_id:product_id,godown_stock:godown_stock_arr,godown_ids:godown_ids_arr,action:action,reserveid_arr:reserveid_arr},
		success: function(response)
		{
			Unloading();
			if(response.trim() == 'true')
			{
				$("#current_stock").modal("hide");	
				
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS")
				// Unloading();
				
				get_tree_request();
				return false;
			}
			else
			{
				toastr.warning("Something Went Wrong", "ERROR");
				return false;
			}
			
		}
	});	
}

function check_stock(ps)
{
	var product_stock_allocate = '';
	var product_stock = '';
	product_stock_allocate = $(ps).val();
	product_stock = $(ps).attr("data-main-stock");
	if(	parseInt(product_stock_allocate) >  parseInt(product_stock)  )
	{
		alert("Stock Not allocate more than Current Stock");
		$(ps).val('');
	}
	return false;	
}

function process_reset(){

	$("#prod_process_id").select2("val","");
	$("#process_rate").val('');
	$("#process_priority").val('');
	$("#edit_id_process").val('')
	$("#process_type").val('');
	$("#process_time").val('');
	$("#process_sel_product_id").val('');
	$("#direct_product_id").val('');
	/*$("#direct_product_id").val('');
	$("#direct_version_id").val('');*/
	// $("#add_process").val("Add");
	$("#resource_id").select2("val","");
	$("#process_loss").val('');
	$("#process_scrap_tolerance_plus").val('');
	$("#process_scrap_tolerance_minus").val('');
}



/* END JAYESH */



$("#preview_bom_add_process_modal").on('hidden.bs.modal', function(){
	get_tree_request();
});





function product_load(product_type){
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	// var product_type=$("#product_type").val();
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=production_pro_type&search=production_pro_search&product_type='+product_type;
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

function set_product_load_for_wo_product_id(type_id=""){
	$('#wo_product_id').select2({
		data: product_load(type_id),
		placeholder: 'search',
		multiple: false,
		width : '100%',
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


function set_product_load_for_wo_sub_product_id(type_id=""){
	$('#wo_sub_product_id').select2({
		data: product_load(type_id),
		placeholder: 'search',
		multiple: false,
		width : '100%',
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
   	var rp_id = $("#selected_rp_id").val();
   	$("#btProcessDesc").html("Save");
   	get_process_desc(rp_id,process_id);
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
     	// console.log(selectedOpts.length);
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
		// console.log($(this).attr('id'));
		$('#selected_process_ids').val($('#selected_process_ids').val() +  $(this).attr('id') + ",");
	});

	$('#process_ids').val('');
	$('#process_left li').each(function(index) {
		// console.log($(this).attr('id'));
		$('#process_ids').val($('#process_ids').val() + $(this).attr('id') + ",");
	});
}

function save_process_desc(rp_id){
	var process_id = $("#selected_process_id").val();
	// var desc = $("#process_desc").val()
	var desc = CKEDITOR.instances['process_desc'].getData();
	var eid = $("#selected_desc_id").val();
	Loading();

	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "save_process_desc",rp_id:rp_id,process_id:process_id,desc:desc,eid,eid},
		success: function(response)
		{
			Unloading();
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
			// Unloading();
			
		}
	});	

}

function get_process_desc(rp_id,process_id){
	Loading();

	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "get_process_desc",rp_id:rp_id,process_id:process_id},
		success: function(response)
		{
			var data=JSON.parse(response);
			if(data.desc !== ""){
				// console.log(response);
				CKEDITOR.instances['process_desc'].setData(data.desc.description);
				$("#selected_desc_id").val(data.desc.id);
				$("#btProcessDesc").html("Update");
			}else {
				CKEDITOR.instances['process_desc'].setData("");
				$("#selected_desc_id").val('');
				$("#btProcessDesc").html("Save");
			}

			if(data.is_process_start > 0){
				$("#moveLeft").hide();
			}else{
				$("#moveLeft").show();
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


//pathik start 16-12-2021
function add_reserve_temp()
{
	var branch_id = $('#branch_id').val();
	var st_godown_id = $('#st_godown_id').val();
	var st_stock_id = $('#st_stock_id').val();
	var st_stock_total = $('#st_stock_total').val();
	var st_stock_reserve = $('#st_stock_reserve').val();
	var rp_id = $('#rp_id_model').val();
	var unit_id = $('#unit_id_model').val();
	var product_id = $('#product_id_model').val();
	var customer_id = $('#customer_id').val();

	if(st_godown_id == ""){
		toastr.warning("Please Select Godown", "ERROR");
				return false;
	}

	if(st_stock_reserve == ""){
		toastr.warning("Please Enter Reserve Stock", "ERROR");
				return false;
	}

	if(parseFloat(st_stock_reserve) > parseFloat(st_stock_total)){
		toastr.warning("Stock not valid. Please check godown stock.", "ERROR");
		return false;
	}
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { 
			mode : "fieldadd",
			branch_id:branch_id,
			st_godown_id:st_godown_id,
			st_stock_id:st_stock_id,
			st_stock_total:st_stock_total,
			st_stock_reserve:st_stock_reserve,
			rp_id:rp_id,
			unit_id:unit_id,
			product_id:product_id,
			customer_id:customer_id
		},
		success: function(response)
		{
			Unloading();
				//console.log(response);
				//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
				$("#st_godown_id").select2("val","");
				$("#st_stock_id").select2("val","");
				$("#st_godown_id").val("");
				$("#st_stock_id").val("");
				
				$("#st_stock_total").val("");
				$("#st_stock_reserve").val("");
				$("#diff_st_stock_total").val("");
				$("#diff_st_stock_reserve").val("");
				$('#addrow').val('Add');
				
				show_reserve_temp_data();
				
			}
		});
}

function load_godown_wise_stock(){
	var st_godown_id=$("#st_godown_id").val();
	var product_id=$("#product_id_model").val();
	var unit_id=$("#unit_id_model").val();
	var batch_id=$("#st_stock_id").val();
	var customer_id=$("#customer_id").val();
	var branch_id=$("#branch_id_model").val();
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { 
			mode : "godown_stock",
			st_godown_id:st_godown_id,
			unit_id:unit_id,
			product_id:product_id,
			batch_id:batch_id,
			customer_id:customer_id,
			branch_id : branch_id
		},
		success: function(response)
		{
			Unloading();
			//alert(response);
			var data=JSON.parse(response);
			var current_stock=data.stock;
			var diff_stock=data.diff_stock;
			$('#st_stock_total').val(current_stock);
			$('#st_stock_reserve').attr('max', current_stock);
			$('#diff_st_stock_total').val(diff_stock);
			$('#diff_st_stock_reserve').attr('max', diff_stock);
		}
	});
}

function show_reserve_temp_data(extra_stock = 0)
{
	//Loading();
	var rp_id=$('#rp_id_model').val();
	var batch_wise_stock_manage=$('#batch_wise_stock_manage').val();
	var mode = "load_tempoutward";
	var ext_stock_vendor_id = $("#ext_stock_vendor_id").val()
	if(extra_stock == '1'){
		mode = "load_tempoutward_extra";
	}
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : mode,rp_id:rp_id,batch_wise_stock_manage:batch_wise_stock_manage,extra_stock:extra_stock,ext_stock_vendor_id:ext_stock_vendor_id},
		success: function(data){
				//console.log(data);
			Unloading();
				$('#sale_productdata').html(data);				
			}		
		});
}

function delete_data_stock(id)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/request_product/',
			data: { mode : "delete_data_stock",  eid : id },
			success: function(response)
			{
				//console.log(response)
				var data=jQuery.parseJSON(response)
				var response=data.res;
					Unloading();
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_reserve_temp_data()

				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}

function delete_data_extra_stock(id)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/request_product/',
			data: { mode : "delete_data_stock_extra",  eid : id },
			success: function(response)
			{
					//console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;

						Unloading();
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						show_reserve_temp_data(1)
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
	}

}

function load_batch_no(){
	var godwn_id=$("#st_godown_id").val();
	var product_id=$("#product_id_model").val();
	var unit_id=$("#unit_id_model").val();
	var branch_id=$("#branch_id_model").val();
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "load_batch_no",  godwn_id : godwn_id,product_id:product_id,unit_id:unit_id,branch_id:branch_id},
		success: function(responce){
			Unloading();
			$('#st_stock_id').html(responce);
			$("#st_stock_id").select2("val","");
		}
	});
}
//pathik end 16-12-2021

//pathik auto mrp start 21-01-2022 
function run_auto_mode(){
	var work_order_id=$("#work_order_id").val();
	//alert(work_order_id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/auto_mrp/',
		data: { mode : "run_auto_mode",  work_order_id : work_order_id},
		success: function(responce){
			Unloading();
			get_tree_request();
		}
	});
}
function auto_mrp_question(){
	var work_order_id=$("#work_order_id").val();
	//alert(work_order_id);
	//Loading();
	var que1="0";
	var que2="0";
	var que3="0";
	var que4="0";

	/* var question1=confirm("auto godown stock allocate ?");
	if(question1){
		 que1="1";
	}
	var question2=confirm("auto wip stock allocate ?");
	if(question2){
		que2="1";
	}
	var question3=confirm("auto process stock allocate ?");
	if(question3){
		que3="1";
	}
	var question4=confirm("auto purchase/process request ?");
	if(question4){
		que4="1";
	} */

	Swal.fire({
	  title: 'auto godown stock allocate ?',
	  icon: 'question',
	  showCancelButton: true,
	  confirmButtonColor: '#5cb85c',
	  cancelButtonColor: '#d9534f',
	  cancelButtonText: 'No',
	  confirmButtonText: 'Yes',
	  allowOutsideClick: false,
	  allowEscapeKey : false,
	    showClass: {
				    popup: 'animate__animated animate__fadeInDown'
				  },
				  hideClass: {
				    popup: 'animate__animated animate__fadeOutUp'
				  }
	}).then((result_q1) => {
		if (result_q1.isConfirmed) {
			que1="1";
		}
		Swal.fire({
			title: 'auto wip stock allocate ?',
			icon: 'question',
			showCancelButton: true,
			confirmButtonColor: '#5cb85c',
			cancelButtonColor: '#d9534f',
			cancelButtonText: 'No',
			confirmButtonText: 'Yes',
			allowOutsideClick: false,
			allowEscapeKey : false,
			showClass: {
				popup: 'animate__animated animate__fadeInDown'
			  },
			  hideClass: {
				popup: 'animate__animated animate__fadeOutUp'
			  }
		  }).then((result_q2) => {
			  if (result_q2.isConfirmed) {
				que2="1";
			  }
			  Swal.fire({
				title: 'auto process stock allocate ?',
				icon: 'question',
				showCancelButton: true,
				confirmButtonColor: '#5cb85c',
				cancelButtonColor: '#d9534f',
				cancelButtonText: 'No',
				confirmButtonText: 'Yes',
				allowOutsideClick: false,
				allowEscapeKey : false,
				showClass: {
				    popup: 'animate__animated animate__fadeInDown'
				  },
				  hideClass: {
				    popup: 'animate__animated animate__fadeOutUp'
				  }
			  }).then((result_q3) => {
				  if (result_q3.isConfirmed) {
					que3="1";
				  }
				  Swal.fire({
					title: 'auto purchase/process request ?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#5cb85c',
					cancelButtonColor: '#d9534f',
					cancelButtonText: 'No',
					confirmButtonText: 'Yes',
					allowOutsideClick: false,
					allowEscapeKey : false,
					showClass: {
						popup: 'animate__animated animate__fadeInDown'
					  },
					  hideClass: {
						popup: 'animate__animated animate__fadeOutUp'
					  }
				  }).then((result_q4) => {
					  if (result_q4.isConfirmed) {
						que4="1";
					  }
					  Loading()
					  $.ajax({
						type: "POST",
						url: root_domain+production_domain+'app/auto_mrp/',
						data: { mode : "auto_mrp_question",  work_order_id : work_order_id,que1:que1,que2:que2,que3:que3,que4:que4},
						success: function(responce){
							var resp =JSON.parse(responce);
							Unloading();
							if(resp.msg==1){
								run_auto_mode();	
							}
							
							//Unloading();
						}
					});
				  })
			  })
		  })
	})
}

function check_auto_mrp() {
	var work_order_id=$("#work_order_id").val();
	//alert(work_order_id);
	if(work_order_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/auto_mrp/',
			data: { mode : "check_auto_mrp",  work_order_id : work_order_id},
			success: function(responce){

				if(responce.trim()==1){
					$(".automrp").hide();

				}else{
					$(".automrp").show();
					//alert(responce);
				}
				Unloading();
			}
		});
	}
}
function add_lead_time()
{
	var product_lead_time=$("#product_lead_time").val();
	var product_id=$("#product_id").val();
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "product_lead_time",  product_lead_time : product_lead_time,product_id:product_id},
		success: function(responce){
			Unloading();
			alert("Product Lead Added Successfully");
			$("#product_lead_and_process").modal("hide");
			var rp_id_flag = $("#rp_id_flag").val();
			var stock_check_flag = $("#stock_check_flag_modal").val();
			var lead_time_process = $("#lead_time_process_modal").val();
			//add_product_request(rp_id_flag,stock_check_flag,'1');
			get_tree_request();
			return false;
			
		}
	});
}
//pathik auto mrp stop 21-01-2022 


function unrequest_product(rp_id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "unrequest_product",  rp_id : rp_id},
		success: function(response){
			Unloading();
			var perent_rp_id = $("#rp_row_"+rp_id).attr('data-perent_rp_id');
			get_tree_request_level_wise(rp_id);
			$(".child_rp_row"+rp_id).remove();
			toggle_process_stock_button($("#work_order_id").val(),$("#wo_rp_id").val());
		}
	});
}


function toggle_process_main_button(){
	var wo_type = $("#wo_type").val();
	var rp_id = "";
	var sp_id = $('#work_order_id').val();
	if(wo_type == "direct_jobcard"){
		rp_id = $("#job_rp_id").val();
	}

	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "check_main_product_process_allocation",  rp_id : rp_id, sp_id:sp_id, wo_type:wo_type},
		success: function(response){
			Unloading();
			if(response.trim() == '1'){
				$("#btn_process_main").hide();
			}else{

				if((sp_id == "" || sp_id == 0) && wo_type != "direct_jobcard"){
					$("#btn_process_main").hide();
				}else{
					$("#btn_process_main").show();	
				}
				
			}

			if(wo_type == "direct_jobcard"){
				$("#btn_process_main").hide();
			}
		}
	});
}


function load_batch_extra_stock(stock_id){
	var product_id=$("#product_id_model").val();
	var unit_id=$("#unit_id_model").val();
	var branch_id=$("#branch_id_model").val();
	var rp_id=$("#rp_id_model").val();
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { 
			mode : "get_batch_stock",
			unit_id:unit_id,
			product_id:product_id,
			stock_id:stock_id,
			branch_id : branch_id,
			rp_id : rp_id
		},
		success: function(response)
		{
			//alert(response);
			var current_stock=response.trim();
			Unloading();
			$('#st_stock_total').val(current_stock);
			$('#st_stock_reserve').attr('max', current_stock);
		}
	});
}


function add_extra_reserve_temp(){
	var branch_id = $('#branch_id').val();
	var st_stock_id = $('#st_stock_id').val();
	var st_stock_total = $('#st_stock_total').val();
	var st_stock_reserve = $('#st_stock_reserve').val();
	var rp_id = $('#rp_id_model').val();
	var unit_id = $('#unit_id_model').val();
	var product_id = $('#product_id_model').val();
	var customer_id = $('#customer_id').val();
	var batch_no = $('#st_stock_id').find(':selected').data('batch_no');

	if(st_stock_id == ""){
		toastr.warning("Please Select Batch", "ERROR");
				return false;
	}

	if(st_stock_reserve == ""){
		toastr.warning("Please Enter Reserve Stock", "ERROR");
				return false;
	}

	if(parseFloat(st_stock_reserve) > parseFloat(st_stock_total)){
		toastr.warning("Stock not valid. Please check Batch stock.", "ERROR");
		return false;
	}
	Loading()
	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { 
			mode : "extra_fieldadd",
			branch_id:branch_id,
			st_stock_id:st_stock_id,
			st_stock_total:st_stock_total,
			st_stock_reserve:st_stock_reserve,
			rp_id:rp_id,
			unit_id:unit_id,
			product_id:product_id,
			customer_id:customer_id,
			batch_no:batch_no
		},
		success: function(response)
		{
			Unloading();
			//console.log(response);
			//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
		
			$("#st_stock_id").select2("val","");
			$("#st_stock_id").val("");
			
			$("#st_stock_total").val("");
			$("#st_stock_reserve").val("");
			$('#addrow').val('Add');
			
			show_reserve_temp_data(1);
				
		}
	});
}

function add_product_request_extra(rp_id){
	var extra_stock = $("#extra_stock").val();
	
	var current_stock=parseFloat($('#current_stock'+rp_id).val());
	var req_qty=parseFloat($('#req_qty'+rp_id).val());
	var req_qty_one=parseFloat($('#req_qty_one'+rp_id).val());
	var reorder_qty=parseFloat($('#reorder_qty'+rp_id).val());
	var reorder_conv_qty=parseFloat($('#reorder_conv_qty'+rp_id).val());
	var res_qty=parseFloat($('#res_qty'+rp_id).val());
	var process_qty=parseFloat($('#process_qty'+rp_id).val());
	var po_qty=parseFloat($('#po_qty'+rp_id).val());
	var branch_id = $('#branch_id').val();

	var rp_po_base_qty =  parseFloat($('#base_po_qty'+rp_id).val());
	var in_process_conv_qty = parseFloat($('#conv_process_qty'+rp_id).val());


	var product_id = $('#req_product_id'+rp_id).val();
	var unit_id = $('#req_unitid'+rp_id).val();
	var customer_id = $("#customer_id").val();

	var res_qty_conv=parseFloat($('#res_qty_conv'+rp_id).val());
	var convtype=$('#convtype'+rp_id).val();
	     
	current_stock=getNum(current_stock);
	req_qty=getNum(req_qty);
	req_qty_one=getNum(req_qty_one);
	res_qty=getNum(res_qty);
	res_qty_conv=getNum(res_qty_conv);
	process_qty=getNum(process_qty);
	po_qty=getNum(po_qty);
	
	
	var total = 0;
	var total_process_stock = 0;

	var gstock_total=parseFloat($('#gstock_total').val());
	gstock_total=getNum(gstock_total);
	var tstock=total+gstock_total+total_process_stock;
	//alert(convtype);
	if(convtype=="conv_unit"){
		if(res_qty_conv>0){
			if(tstock!=res_qty_conv){
				toastr.warning("Increase Resverve Qty Please Enter currect Qty", "ERROR");
				return false;
			}
		}
	}else{
		if(res_qty>0){
			if(tstock!=res_qty){
				toastr.warning("Increase Resverve Qty Please Enter currect Qty", "ERROR");
				return false;
			}
		}
	}
	

	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { 
			mode : "add_product_request_extra",
			current_stock:current_stock,
			req_qty:req_qty,
			req_qty_one:req_qty_one,
			res_qty:res_qty,
			process_qty:process_qty,
			po_qty:po_qty,
			rp_id:rp_id,
			branch_id : branch_id,
			customer_id: customer_id,
			convtype:convtype,
			res_qty_conv:res_qty_conv,
			rp_po_base_qty:rp_po_base_qty,
			in_process_conv_qty:in_process_conv_qty,
			extra_stock:extra_stock
		},
		success: function(data){
			
			var resp =JSON.parse(data);
			Unloading();
				//console.log(resp);
				if(resp.trn_ids!=0)
				{
					var exp_trn_ids=(resp.trn_ids).split(",");
					var insert_id=resp.insert_id;
					var i;
					var inh_qty=Number($('#process_qty'+insert_id).val());
					for (i = 0; i < exp_trn_ids.length; ++i) {
						var chil=Number($('#req_qty_one'+exp_trn_ids[i]).val());
						//alert(inh_qty);
						//alert(chil);
						var req_qty1=parseFloat(chil)*parseFloat(inh_qty);
						req_qty1 = req_qty1.toFixed(4);
						$("#req_qty"+exp_trn_ids[i]).val(req_qty1);
						$("#basic_req_qty"+exp_trn_ids[i]).val(req_qty1);
						var pq=Number($("#process_qty"+exp_trn_ids[i]).val());
						//alert(pq);
						if(pq>0){
							$("#process_qty"+exp_trn_ids[i]).val(req_qty1);
						}else{
							$("#po_qty"+exp_trn_ids[i]).val(req_qty1);
						}
						var com1='<a class="btn btn-primary dispbtn" data-original-title="" id="reqest_btn'+exp_trn_ids[i]+'" data-toggle="tooltip" data-placement="top" onclick="add_product_request('+exp_trn_ids[i]+')" ><i class="fa fa-paper-plane"></i> Request</a>';
						$(".action"+exp_trn_ids[i]).html(com1);
					}
					var com='<a class="btn btn-danger dispbtn" data-original-title="" data-toggle="tooltip" data-placement="top" > Requested</a>';
					$(".action"+insert_id).html(com);
					$('#po_qty'+insert_id).attr("readonly",true);
					$('#process_qty'+insert_id).attr("readonly",true);
					$('#res_qty'+insert_id).attr("readonly",true);
					$('#req_qty'+insert_id).attr("readonly",true);
				}
				else
				{
					var com='<a class="btn btn-danger dispbtn" data-original-title="" data-toggle="tooltip" data-placement="top" > Requested</a>';
					$(".action"+resp.insert_id).html(com);
					$('#po_qty'+resp.insert_id).attr("readonly",true);
					$('#process_qty'+resp.insert_id).attr("readonly",true);
					$('#res_qty'+resp.insert_id).attr("readonly",true);
					$('#req_qty'+resp.insert_id).attr("readonly",true);
					//$('#current_stock'+insert_id).attr("readonly",true);		
				}
				work_order_submit_per();
				get_tree_request();
				$("#reserve_stock_entry_wo").modal("hide");
				// Unloading();
			}
		});
}





function save_workorder_attachments(){
	
	var image_name = $("#image_name").val();
	var img_msg = "ENTER IMAGE NAME";
	var img_len = $("#workorder_file")[0].files.length;
	

	if(image_name == ""){
		toastr.warning(img_msg, "ERROR");
	    return false;
	}

	if(img_len === 0){
		toastr.warning("PLEASE SELECT IMAGE", "ERROR");
	    return false;
	}

	var work_order_id = $("#work_order_id").val();

	if(work_order_id == ''){
	    return false;
	}

	Loading();
	var form_data = new FormData($('#product_request_add')[0]);

	 form_data.delete('mode');
	 form_data.append('mode','save_workorder_image');
	 //form_data.append('form',$('#product_request_add')[0]);
 
//Sending form

	$.ajax({
		cache:false,
		url: root_domain+production_domain+'app/request_product/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			
				Unloading();
			if(response.trim() == '1') {
				toastr.success("WORKORDER IMAGE ADDED SUCCESSFULLY", "SUCCESS");
				
					view_workorder_image(work_order_id)
					$("#image_name").val('');
					$("#workorder_file").val('');
				
			}
			else if(response.trim() == '-1') {
				toastr.warning("INVALID FILE", "ERROR");
				// Unloading();
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				// Unloading();
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
}


function view_workorder_image(work_order_id = "")
{
	if(work_order_id == ""){
		work_order_id = $("#work_order_id").val()	
	}
	
	Loading(true);
	 $.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "view_workorder_image", work_order_id : work_order_id },
		success: function(response)
		{
			$('#wo_image_list').html(response);
			
			Unloading();
		}
	});	
}



function delete_data_image(id){
	var r= confirm(" Are you want to delete attachment ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/request_product/',
			data: { mode : 'delete_image', id : id},
			success: function(data){
				Unloading();			
				if(data=='1'){
					toastr.success("IMAGE DELETE SUCCESSFULLY", "SUCCESS");
					view_workorder_image()
				}
			}		
				
		});
	}
 }



function view_documents(bom_id,bom_version_id)
{
	var id = $("#eid").val()
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : "view_document_data", bom_id : bom_id,bom_version_id : bom_version_id },
		success: function(response)
		{
			$('#documents_data_list').empty().html(response);
			$("#preview_bom_document_upload").modal("show");
			Unloading();
		}
	});	
}


function product_convert_qty(type){
	if(type==2){
		var conv_qty_hide=$("#product_qty").val();
		var s=parseFloat(conv_qty_hide);
		results = s.toFixed(3);

		var	num=$("#product_qty_hide").val();
		var d=parseFloat(num);
		resultb = d.toFixed(3);
		if(resultb===results){
			
			return false;
		}
		var product_conv_qty_hide=$("#product_conv_qty_hide").val();
	}else{
		var base_qty_hide=$("#product_conv_qty").val();
		var d=parseFloat(base_qty_hide);
		resultb = d.toFixed(3);
		
		var base_qty_hidess=$("#product_conv_qty_hide").val();
		var s=parseFloat(base_qty_hidess);
		results = s.toFixed(3);

		if(resultb===results){
			
			return false;
		}
		var conv_qty_hide=$("#product_qty").val();
	}
	//alert(base_qty_hide);
	//alert(conv_qty_hide);
	var base_qty=$("#product_qty").val();
	var conv_qty=$("#product_conv_qty").val();
	var product_id=$("#wo_product_id").val();
	
	if(product_id){
		Loading()
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/request_product/',
			data: { mode : "convert_qty",  type : type,base_qty:base_qty_hide,conv_qty:conv_qty_hide,product_id:product_id},
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);	
				Unloading();		
				if(type===1){
					$("#product_conv_qty_hide").val(conv_qty);
				}else if(type===2){
					$("#product_qty_hide").val(base_qty);
				}
				
				if(type===1){
					$("#product_qty").val(arr.show_qty);
					$("#product_qty_hide").val(arr.hide_qty);

				}else if(type===2){
					$("#product_conv_qty").val(arr.show_qty);
					$("#product_conv_qty_hide").val(arr.hide_qty);
					
				}else{
					$("#product_conv_qty").val(arr.show_qty);
					$("#product_conv_qty_hide").val(arr.hide_qty);
					$("#product_qty").val(arr.show_qty);
					$("#product_qty_hide").val(arr.hide_qty);
				}
				
			}
		});
	}else{
		toastr.warning("Select Product First", "WARNING");
		$("#product_conv_qty").val("0");
		$("#product_conv_qty_hide").val("0");
		$("#product_qty").val("0");
		$("#product_qty_hide").val("0");
	}
	
}


function sub_product_convert_qty(type){
	if(type==2){
		var conv_qty_hide=$("#sub_product_qty").val();
		var s=parseFloat(conv_qty_hide);
		results = s.toFixed(3);

		var	num=$("#sub_product_qty_hide").val();
		var d=parseFloat(num);
		resultb = d.toFixed(3);
		if(resultb===results){
			
			return false;
		}
		var product_conv_qty_hide=$("#sub_product_conv_qty_hide").val();
	}else{
		var base_qty_hide=$("#sub_product_conv_qty").val();
		var d=parseFloat(base_qty_hide);
		resultb = d.toFixed(3);
		
		var base_qty_hidess=$("#sub_product_conv_qty_hide").val();
		var s=parseFloat(base_qty_hidess);
		results = s.toFixed(3);

		if(resultb===results){
			
			return false;
		}
		var conv_qty_hide=$("#sub_product_qty").val();
	}
	//alert(base_qty_hide);
	//alert(conv_qty_hide);
	var base_qty=$("#sub_product_qty").val();
	var conv_qty=$("#sub_product_conv_qty").val();
	var product_id=$("#wo_sub_product_id").val();
	
	if(product_id){
		Loading()
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/request_product/',
			data: { mode : "convert_qty",  type : type,base_qty:base_qty_hide,conv_qty:conv_qty_hide,product_id:product_id},
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);			
				Unloading();
				if(type===1){
					$("#sub_product_conv_qty_hide").val(conv_qty);
				}else if(type===2){
					$("#sub_product_qty_hide").val(base_qty);
				}
				
				if(type===1){
					$("#sub_product_qty").val(arr.show_qty);
					$("#sub_product_qty_hide").val(arr.hide_qty);

				}else if(type===2){
					$("#sub_product_conv_qty").val(arr.show_qty);
					$("#sub_product_conv_qty_hide").val(arr.hide_qty);
					
				}else{
					$("#sub_product_conv_qty").val(arr.show_qty);
					$("#sub_product_conv_qty_hide").val(arr.hide_qty);
					$("#sub_product_qty").val(arr.show_qty);
					$("#sub_product_qty_hide").val(arr.hide_qty);
				}
				
			}
		});
	}else{
		toastr.warning("Select Product First", "WARNING");
		$("#sub_product_conv_qty").val("0");
		$("#sub_product_conv_qty_hide").val("0");
		$("#sub_product_qty").val("0");
		$("#sub_product_qty_hide").val("0");
	}
	
}



function reserve_stock_convert_qty(type){
	// alert('ok')
	var base_qty = 0;
	var conv_qty = 0;
	/*if(type==2){  // take base
		conv_qty  = $("#diff_st_stock_reserve").val();
	}else{
		 base_qty = $("#st_stock_reserve").val();
	}*/

	type = 2;

	conv_qty = $("#st_stock_reserve").val();

	var product_id=$("#product_id_model").val();
	Loading()
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/request_product/',
			data: { mode : "convert_qty",  type : type,base_qty:base_qty,conv_qty:conv_qty,product_id:product_id},
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);			

				Unloading();
				$("#diff_st_stock_reserve").val(arr.hide_qty);

				/*if(type==2){  // take base
					$("#st_stock_reserve").val(arr.hide_qty);
				}else{
					 $("#diff_st_stock_reserve").val(arr.hide_qty);
				}*/
					 $("#diff_st_stock_reserve").val(arr.hide_qty);
				
			}
		});	
	
}



function show_process_stock(sp_id,rp_id,qty){
	
	$("#process_reserve_stock_entry_wo").modal("show");
	Loading();
	var qty = $("#rp_req_qty").val();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { 
			mode : "show_process_stock",
			rp_id:rp_id,
			qty:qty
		},
		success: function(data){
			$("#process_sstock").empty().html(data);
			Unloading();
		}
	})
}


function toggle_process_stock_button(sp_id,rp_id){
	Loading();
	var wo_type = $("#wo_type").val();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { 
			mode : "check_child_product_requested",
			rp_id:rp_id,
			sp_id:sp_id
		},
		success: function(data){

			if(data == '2'){
				$("#btn_process_stock").hide();
				$("#add_wo_prd").hide();
				$("#btn_process_main").hide();
				$("#btn_unrequest_process_stock").show();
			}	
			else if(data == '1'){
				$("#btn_process_stock").hide();
				$("#btn_unrequest_process_stock").hide();
				$("#add_wo_prd").show();
				$("#btn_process_main").show();
			}else{
				$("#add_wo_prd").show();
				$("#btn_process_main").show();
				$("#btn_process_stock").show();
				$("#btn_unrequest_process_stock").hide();
			}
			if(wo_type == "direct_jobcard"){
				$("#btn_process_main").hide();
			}
			
			if((sp_id == "0" && rp_id == '0') || (sp_id == "" && rp_id == '')){
				$("#btn_process_stock").hide();
				$("#btn_process_stock").hide();
				$("#btn_unrequest_process_stock").hide();
			}
			Unloading();
		}
	})


}


function change_process_stock_qty(cnt){
	var qty = $("#res_process_stock"+cnt).val();
	if(qty != "" && qty > 0){
		$(".res_process_stock").val('0');
		$("#res_process_stock"+cnt).val(qty);	
	}
}

function add_process_reserve_stock(rp_id,qty){
	var total_stock = 0;
	var process_res_stock_arr=[];
	var process_godown_arr=[];
	var process_arr=[];
	var errorlog = 0;
	p = 0;
	$('input.res_process_stock').each(function(index){ 
		var cnt = index + 1;
		var stock = parseFloat($(this).val());
		var pr_stock = parseFloat($(this).attr('max'));

		if(isNaN(stock)){
			stock=0; 
		}
		if(isNaN(pr_stock)){ 
			pr_stock=0; 
		}	
		
		if(stock > 0 && stock > pr_stock){
			errorlog +=parseFloat(1);
		}else if(stock > 0) {
			process_res_stock_arr.push(stock);
			process_arr.push($("#res_process_id"+cnt).val());
			process_godown_arr.push($("#res_godown_id"+cnt).val());
			total_stock = total_stock + stock;
			/*process_res_stock_arr[p++]=$(this).val();	
			process_arr[p++]=$(this).val();
			process_godown_arr[p++]=$(this).val();*/
		}
		
	});
	

	if(total_stock > qty){
		toastr.warning("PROCESS STOCK CAN'T BE GREATER THAN REQUEST QTY", "ERROR");
		return false;
	}

	Loading();

	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { 
			mode : "save_process_stock",
			rp_id:rp_id,
			sp_id:$("#work_order_id").val(),
			process_res_stock : process_res_stock_arr,
			process_godown : process_godown_arr,
			process_id : process_arr
		},
		success: function(data){
			$("#process_reserve_stock_entry_wo").modal("hide");
			$("#btn_process_stock").hide();
			Unloading();
			get_tree_request();
			location.reload();
		}
	})

}


function unrequest_process_stock(sp_id, rp_id){
	var r= confirm(" Are you want to unreserve process stock?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/request_product/',
			data: { 
				mode : "unreserve_process_stock",
				rp_id:rp_id,
				sp_id:sp_id,
			},
			success: function(data){
				// location.reload();
				Unloading();
			}
		})
	}
}



function show_product_remark_modal(rp_id,status){
	var editor = CKEDITOR.instances['product_remark'];
	 if (!editor){
		    CKEDITOR.replace( 'product_remark', {
				enterMode: CKEDITOR.ENTER_BR
			});
		}

	$("#remark_rp_id").val(rp_id)

	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/request_product/',
			data: { 
				mode : "get_process_remark",
				rp_id:rp_id
			},
			success: function(data){
				CKEDITOR.instances['product_remark'].setData(data);
				if(status == 0){
					$("#btn_prod_remark").hide()
				}else{
					$("#btn_prod_remark").show()
				}
				$("#wo_product_wise_remark_modal").modal('show');
				Unloading();
			}
		})

	
}


function save_product_remark(){
	var remark = CKEDITOR.instances['product_remark'].getData();

	if(remark == ""){
		toastr.warning("ENTER PROCESS REMARK", "ERROR");
		return false;
	}
	var rp_id =	$("#remark_rp_id").val();
	Loading()
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/request_product/',
			data: { 
				mode : "save_process_remark",
				rp_id:rp_id,
				remark:remark,
			},
			success: function(data){
				if(data == '1'){
					toastr.success("PRODUCT REMARK UPDATE SUCCESSFULLY", "SUCCESS")
					$("#wo_product_wise_remark_modal").modal('hide');
				}else{
					toastr.warning("SOMETHING WRONG !!!", "ERROR");
				}
				
				Unloading();
			}
		})
	
}



function get_tree_request_level_wise(rp_id)
{
	var main_mode=$('#mode').val();
	var eid=$('#eid').val();//Product ID
	var pr_type=$('#pr_type').val();
	var bom_id=$('#bom_id').val();
	var po_req_no=$('#po_req_no').val();
	var sales_order_trn_id=$('#sales_order_trn_id').val();
	var bom_version_id = $('#bom_version_id').val();	
	var sp_id = $('#work_order_id').val();
	var wo_type = $("#wo_type").val();
	var extra_stock = $("#extra_stock").val();
	var ext_stock_vendor_id = $("#ext_stock_vendor_id").val();
	var jobwork_type = $('#job_work_type').val();
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : 'get_tree_request_level',eid:eid,pr_type:pr_type,bom_id:bom_id,po_req_no:po_req_no,sales_order_trn_id:sales_order_trn_id,bom_version_id:bom_version_id,sp_id:sp_id,main_mode:main_mode,wo_type:wo_type,rp_id:rp_id,extra_stock:extra_stock,ext_stock_vendor_id:ext_stock_vendor_id,jobwork_type:jobwork_type},
		success: function(data){	
		Unloading();	
			var prev_rp_row = $("#rp_row_"+rp_id).closest("tr").prev().attr('id');
			var perent_rp_id = $("#rp_row_"+rp_id).attr('data-perent_rp_id');
			$("#rp_row_"+rp_id).remove();
			
			if(prev_rp_row === undefined){
				$('#show_tree_request').prepend(data);
			}else{
				 $('#'+prev_rp_row).after(data);


				 if(perent_rp_id !== undefined || perent_rp_id != 0 || perent_rp_id > 0){
				 	toggle_unrequest_button(perent_rp_id);
				 }
				}
			work_order_submit_per()
			
		}		
	});
}


function toggle_unrequest_button(rp_id){
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_product/',
		data: { mode : 'check_unrequest_child',rp_id:rp_id},
		success: function(data){		
			
			if(data.trim() == '1'){
				// console.log('#unreqest_btn'+rp_id + '  hide');
				 $('#unreqest_btn'+rp_id).hide();	
			}else{
				// console.log('#unreqest_btn'+rp_id + '  show');
				 $('#unreqest_btn'+rp_id).show();
			}

			Unloading();
		}		
	});
}