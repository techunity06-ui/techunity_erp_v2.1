var datatable;
$(document).ready(function() {
	
	work_order_submit_per();
	
	load_salesno();
		
	return false;
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
	
	
		
			check_main_process_request();
			check_poreq_status();
		
}
function check_poreq_status(){
	
	var eid=$('#eid').val();
	var po_req_no=$('#po_req_no').val();
	var bom_version_id = $('#bom_version_id').val();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/direct_jobcard/',
		data: { mode : "check_poreq_status",eid:eid,po_req_no:po_req_no},
		success: function(data){
			
			
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
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/direct_jobcard/',
		data: { mode : "main_po_reqdata",eid:eid,po_req_no:po_req_no,rp_po_qty:rp_po_qty,sales_order_trn_id:sales_order_trn_id },
		success: function(data){
			//alert(data);
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
	//alert(eid);
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/direct_jobcard/',
		data: { 
				mode : "add_main_process_request_qty",
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
				bom_version_id:bom_version_id
		},
		success: function(data){
	
			if(data==1) {
				get_tree_request();
			}
			else if(data==2){
				toastr.warning("Product BOM Not Found !!!", "ERROR");
			}
			location.reload();
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
	$.ajax({
		type: "POST",
		url: root_domain+'app/direct_jobcard/',
		data: { mode : "check_main_process_request",po_req_no:po_req_no,eid:eid,sales_order_trn_id:sales_order_trn_id,bom_version_id:bom_version_id},
		success: function(response)
		{
			
			var data=JSON.parse(response);	
			if(data.count>0)
			{
				
				$('#set_process_btn').hide();
				
				setTimeout(function(){ get_tree_request(); }, 1000);
			}else{
				
				
				cal_po_qty();
			}
		}
	});
}
function error_check(rp_id,rname){
	var current_stock=parseFloat($('#current_stock'+rp_id).val());
	var req_qty=parseFloat($('#req_qty'+rp_id).val());
	var req_qty_one=parseFloat($('#req_qty_one'+rp_id).val());
	var res_qty=parseFloat($('#res_qty'+rp_id).val());
	var process_qty=parseFloat($('#process_qty'+rp_id).val());
	var po_qty=parseFloat($('#po_qty'+rp_id).val());
	var basic_req_qty=parseFloat($('#basic_req_qty'+rp_id).val());
		current_stock=getNum(current_stock);
		req_qty=getNum(req_qty);
		req_qty_one=getNum(req_qty_one);
		res_qty=getNum(res_qty);
		process_qty=getNum(process_qty);
		po_qty=getNum(po_qty);
		basic_req_qty=getNum(basic_req_qty);
	var reqbu=0;var reqbu1=0;var reqbu2=0;
	if(current_stock<res_qty){
		$("#res_qty_err"+rp_id).css("display","block");
		$("#res_qty_err"+rp_id).html("Not Add "+current_stock+" < "+res_qty+"");
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
	if(req_qty<used_qty){
		$("#reqest_btn"+rp_id).hide();
		 reqbu1=0;
		$("#"+rname+"_err"+rp_id).css("display","block");
		$("#"+rname+"_err"+rp_id).html("Not Add "+req_qty+" < "+used_qty+"");
	}else if(req_qty<used_qty){
		$("#reqest_btn"+rp_id).hide();
		reqbu1=0;
		$("#"+rname+"_err"+rp_id).css("display","block");
		$("#"+rname+"_err"+rp_id).html("Not Add "+req_qty+" > "+used_qty+"");
	}else if(req_qty===used_qty){
		$("#reqest_btn"+rp_id).show();
		reqbu1=1;
		$("#"+rname+"_err"+rp_id).css("display","none");
	}
	
	if(basic_req_qty>req_qty){
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
	if(ccc==3){
		$("#reqest_btn"+rp_id).show();
	}else{
		$("#reqest_btn"+rp_id).hide();
	}
}
function add_product_request(rp_id)
{
	var current_stock=parseFloat($('#current_stock'+rp_id).val());
	var req_qty=parseFloat($('#req_qty'+rp_id).val());
	var req_qty_one=parseFloat($('#req_qty_one'+rp_id).val());
	var res_qty=parseFloat($('#res_qty'+rp_id).val());
	var process_qty=parseFloat($('#process_qty'+rp_id).val());
	var po_qty=parseFloat($('#po_qty'+rp_id).val());
	var branch_id = $('#branch_id').val();
		
		current_stock=getNum(current_stock);
		req_qty=getNum(req_qty);
		req_qty_one=getNum(req_qty_one);
		res_qty=getNum(res_qty);
		process_qty=getNum(process_qty);
		po_qty=getNum(po_qty);
		
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+'app/direct_jobcard/',
	data: { 
		mode : "add_product_request",
		current_stock:current_stock,
		req_qty:req_qty,
		req_qty_one:req_qty_one,
		res_qty:res_qty,
		process_qty:process_qty,
		po_qty:po_qty,
		rp_id:rp_id,
		branch_id : branch_id
	},
	success: function(data){
			
			var resp =JSON.parse(data);
			//console.log(resp);
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
			get_tree_request();
			Unloading();
		}		
		
	});
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
	url: root_domain+'app/direct_jobcard/',
	data: { mode : "add_product_request",req_qty:req_qty,in_process_qty:in_process_qty,out_process_qty:out_process_qty,po_qty:po_qty,pr_id:pr_id,po_req_no:po_req_no,parent_product:parent_product,cnt:cnt,trn_id:trn_id,process_unit:process_unit,purchase_unit:purchase_unit,at_reserve:at_reserve,perent:perent,reserve_stock:atstock },
	success: function(data){
			
			var resp =JSON.parse(data);
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
			Unloading();
		}		
		
	});
	
}

function get_main_form_submit()
{

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
	
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+'app/direct_jobcard/',
	data: { cust_id : cust_id,sales_order_date:sales_order_date,po_no:po_no,po_date:po_date,sales_order_no:sales_order_no,po_req_no:po_req_no,po_req_date:po_req_date,po_product_name:po_product_name,rp_req_qty:rp_req_qty,in_process_qty:in_process_qty,rp_po_qty:rp_po_qty,main_poreq_status:main_poreq_status,branch_id:branch_id,category_name:category_name,remark:remark,smode:smode,mode:mode,eid:eid,pr_type:pr_type,bom_id:bom_id,process_status:process_status,work_order_id:work_order_id,bom_check:bom_check,sales_order_trn_id:sales_order_trn_id },
	success: function(msg){
		
	
			
			var redirect_url= $("#redirect_url").val();
			if(msg.trim() == '1') {
				toastr.success("INSERTED SUCCESSFULLY", "SUCCESS")
				Unloading();
				window.location=redirect_url; 
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
			else if(msg.trim() == '3') {
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");
				
				window.location=redirect_url;
				Unloading();
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
		url: root_domain+'app/direct_jobcard/',
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
				window.location=root_domain+'get_stock_detail';
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
	
	check_product_btn(po_req_no);
	
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+'app/direct_jobcard/',
	data: { mode : 'get_tree_request_new',eid:eid,pr_type:pr_type,bom_id:bom_id,po_req_no:po_req_no,sales_order_trn_id:sales_order_trn_id,bom_version_id:bom_version_id,sp_id:sp_id,main_mode:main_mode},
	success: function(data){
		
		
			//alert("cds");
			$('#show_tree_request').html(data);			
			get_all_requested_qty();			
            get_inhouse_request_qty($('#in_process_qty_main').val());
    		get_bom_request_qty($('#in_process_qty_main').val());
    		get_po_request_qty($('#in_process_qty_main').val());
			work_order_submit_per();
			Unloading();
		}		
		
	});
}

function get_all_requested_qty()
{

	var cnt=Number($('#counter_tree').val());
	var po_req_no=$('#po_req_no').val();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/direct_jobcard/',
		data: { mode : 'get_all_requested_qty',po_req_no:po_req_no },
		success: function(response){
				
				//console.log(response);
				var data=JSON.parse(response);
				//console.log(data);
				var array=data.data;
				
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
	url: root_domain+'app/direct_jobcard/',
	data: { mode : "get_under_tree",trn_id:trn_id,po_req_no:po_req_no },
	success: function(data){
			
			var resp =JSON.parse(data);
			//console.log(resp);
			
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
			Unloading();
		}		
		
	});
}


function lock_main_request()
{
	var po_req_no=$('#po_req_no').val();
	var eid=$('#eid').val();
	
	$.ajax({
	type: "POST",
	url: root_domain+'app/direct_jobcard/',
	data: { mode : 'lock_main_request',eid:eid,po_req_no:po_req_no },
	success: function(response){
			
			
			if(response==1)
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
	var work_order_id=$('#work_order_id').val();
	
	if(work_order_id){
		$.ajax({
		type: "POST",
		url: root_domain+'app/direct_jobcard/',
		data: { mode : 'work_order_submit_per',work_order_id:work_order_id },
		success: function(response){
			//alert(response);
				if(response==1)
				{
					$("#save").show();
				}else{
					$("#save").hide();
				}
				
			var bom_version_id = $("#add_bom_version_id").val();
			var in_process_qty_main=$('#in_process_qty_main').val();
			var rp_po_qty=$('#rp_po_qty').val();
		
				if(bom_version_id != '' )
				{
						check_child_product(work_order_id);
					}
				}
				
		});
	}else{
		$("#save").hide();
	}
}

/* START JAYESH */

function check_child_product(work_order_id)
{
			$.ajax({
				type: "POST",
				url: root_domain+'app/direct_jobcard/',
				data: { mode : 'check_child_product',work_order_id:work_order_id },
				success: function(response){
				
						if(response==1)
						{
							$("#save").show();
						}else{
							
							$("#save").hide();
						}
					}
			});
						
}


function add_work_order_product(product_id,qty){
	
	var qty = $("#rp_req_qty").val();	
	var product_name=$("#po_product_name").val();
	var product_qty=$("#product_qty").val();
	var sales_order_date = $("#sales_order_date").val();
	var sales_order_no = $("#sales_order_no").val();
	$("#mtype").text('Add');
	$.ajax({
		type: "POST",
		url: root_domain+'app/direct_jobcard/',
		data: { mode : "add_work_order_product",product_id:product_id,qty:qty,product_name:product_name,sales_order_date:sales_order_date,sales_order_no:sales_order_no,product_qty:product_qty},
		success: function(response)
		{
			$("#show_product_from").html(response);
			$("#add_workorder_product").modal("show");
			
			$(".select2").select2({
				width: '100%'
			});
		}
	});	
}



function add_sub_product(rp_id,sub_product_id,main_product_id,product_qty){
	var eid=$("#eid").val();
	var qty = $("#rp_req_qty").val();
	var sales_order_date = $("#sales_order_date").val();
	var sales_order_no = $("#sales_order_no").val();	
	var product_name=$("#po_product_name").val();
	$("#mtype").text('Add');
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/direct_jobcard/',
		data: { mode : "add_work_order_sub_product",sub_product_id:sub_product_id,rp_id:rp_id,qty:product_qty,main_product_id:main_product_id,product_name:product_name,sales_order_date:sales_order_date,sales_order_no:sales_order_no},
		success: function(response)
		{
			$("#show_sub_product_from").html(response);
			
			$("#add_workorder_sub_product").modal("show");
				$(".select3").select2({
				width: '100%'
			}); 
		
		}
	});	
}


function save_work_order_product()
{
	var main_product_id = $("#prod_id").val();
	var wo_product_id = $("#wo_product_id").val();
	var qty = $("#rp_req_qty").val();	
	var product_qty = $("#product_qty").val();	
	var bom_version_id = $("#add_bom_version_id").val();	
	var bom_version_id = $("#add_bom_version_id").val();
	var sp_id = $("#work_order_id").val();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/direct_jobcard/',
		data: { mode : "save_work_order_product",main_product_id:main_product_id,wo_product_id:wo_product_id,qty:qty,product_qty:product_qty,bom_version_id:bom_version_id,sp_id:sp_id},
		success: function(response)
		{
			if(response==="1"){
					toastr.success("ADDED SUCCESSFULLY", "SUCCESS");
					$("#add_workorder_product").modal("hide");
					
					var r= confirm(" Are you want to update process ?");
					if(r) {
						show_product_process(1,wo_product_id);
					}else{
						Loading();
						setTimeout(function(){
						Unloading();
						add_field();
						},300);
					get_tree_request();
					}
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
	var qty = $("#qty").val();
	
	
	var rp_id = $("#rp_id").val();
	var product_qty = $("#sub_product_qty").val();
	var bom_version_id = $("#add_bom_version_id").val();	
	var wo_product_type = $("#wo_product_type").val();
	
	if(sub_product_id == "")
	{
		toastr.warning("Select Product ", "ERROR");
		$("#wo_sub_product_id").select2("focus");
		return false;
	}
	if(bom_version_id == "")
	{
		toastr.warning("Select Bom Version", "ERROR");
		$("#add_bom_version_id").select2("focus");
		return false;
	}
	if(product_qty == "")
	{
		toastr.warning("Enter Qty", "ERROR");
		$("#product_qty").focus();
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
			
	$.ajax({
		type: "POST",
		url: root_domain+'app/direct_jobcard/',
		data: { mode : "save_work_order_sub_product",sub_product_id:sub_product_id,main_product_id:main_product_id,qty:qty,rp_id:rp_id,product_qty:product_qty,bom_version_id:bom_version_id},
		success: function(response)
		{
		
			if(response==="1"){
					toastr.success("ADDED SUCCESSFULLY", "SUCCESS");
					$("#add_workorder_sub_product").modal("hide");
					
					var r= confirm(" Are you want to update process ?");
					if(r) {
						
						show_product_process(1,sub_product_id,'','',rp_id);
					}else{
						Loading();
						setTimeout(function(){
						Unloading();
						add_field();
						},300);
					get_tree_request();
					}
					return false;
					get_tree_request();
					return false;
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
		$.ajax({
		type: "POST",
		url: root_domain+'app/direct_jobcard/',
		data: { mode : "delete_work_order_product",rp_id:rp_id,parent_delete_flag:parent_delete_flag,sp_id:sp_id},
		success: function(response)
		{
			if(response==="1"){
					toastr.success("DELETED  SUCCESSFULLY", "SUCCESS");
					get_tree_request();
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
		url: root_domain+'app/direct_jobcard/',
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
	
	var qty = $("#req_qty").val();
	$("#mtype").text('Edit');
	var product_name=$("#po_product_name").val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/direct_jobcard/',
		data: { mode : "edit_work_order_product",product_id:product_id,qty:qty,rp_pid:rp_pid,rp_id:rp_id,rp_pro_qty:rp_pro_qty,product_name:product_name},
		success: function(response)
		{
			
			$("#show_product_from").html(response);
			$("#add_workorder_product").modal("show");
			load_product_detail(rp_pid);
			$(".select2").select2({
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
	
	
		
	$.ajax({
		type: "POST",
		url: root_domain+'app/direct_jobcard/',
		data: { mode : "edit_save_work_order_product",main_product_id:main_product_id,wo_product_id:wo_product_id,qty:qty,rp_id:rp_id,rp_product_qty:rp_product_qty},
		success: function(response)
		{
			
			if(response==="1"){
					toastr.success("ADDED SUCCESSFULLY", "SUCCESS");
					$("#add_workorder_product").modal("hide");
						var r= confirm(" Are you want to update process ?");
		if(r) {
			direct_show_product_process(wo_product_id,rp_id);
		}else{
			Loading();
			setTimeout(function(){
				Unloading();
				add_field();
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

function load_product(type_id){
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/direct_jobcard/',
		data: { mode : "load_product", type_id : type_id},
		success: function(data){
			$('#wo_product_id').html(data);
			$('#wo_sub_product_id').html(data);				
			Unloading();
		}
	});
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
				url: root_domain+'app/direct_jobcard/',
				data: { mode : "check_bom_version_by_product",product_id:product_id,branch_id:branch_id},
				success: function(response)
				{
					if(response != 0)
					{
						$('#add_bom_version_id').html('');
						$('#add_bom_version_id').html(response);
						$("#add_bom_version_id").val("10000");
						$('#add_bom_version_id').trigger('change');
					}
					Unloading();
				}
		});
	
}

function direct_show_product_process(product_id,rp_id='')
						{
								var bom_version_id = $('#bom_version_id').val();
								$("#mask1").removeClass('hidden');
								$.ajax({
									type: "POST",
									url: root_domain+'app/direct_jobcard/',
									data: { 
										mode : 'get_product_process_data',
										product_id:product_id,
										rp_id : rp_id,
										bom_version_id:bom_version_id,
										edit_id:'1'
										
									},
									success: function(data){
									
										$('#mod_per_div_add_process').empty();
										$('#mod_per_div_add_process').html(data);
										$('#rp_id').val(rp_id);
										
										
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
													}
											}
										
										$("#mask1").addClass('hidden');
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

	console.log($("#multiple_value").val());
	// return false;

	if(counter == 0){
		add_field();
	}else{
		var pro_counter = $("#process_item :selected").length;

		if (pro_counter == 0) {
			toastr.warning("SELECT PROCESS", "ERROR");
			return false;
		}

		var form_data = new FormData();
		
		form_data.append('mode','bom_process_add');
		form_data.append('sel_process',sel_process);
		form_data.append('unsel_process',unsel_process);
		
		form_data.append('product_id',product_id);
		
		form_data.append('bom_id',$("#bom_id").val());
		form_data.append('bom_version_id',bom_version_id);
		form_data.append('multiple_value',$("#multiple_value").val());
		form_data.append('edit_id',edit_id);

		$.ajax({		
			url: root_domain+'app/direct_jobcard/',
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
		url: root_domain+'app/direct_jobcard/',
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
			if(arr.msg == '1') {

				var process_id = arr.process_id;
				toastr.success("PROCESS ADDED SUCCESSFULLY", "SUCCESS");
			if($("#direct_product_id").val()==""){
				
				show_product_process(1,product_id);
			}else{
				
				direct_show_product_process(product_id,rp_id);
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
		url: root_domain+'app/direct_jobcard/',
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
	

	var counter = $("#process_item").length;
	var sel_process = [];
	$("#process_item :selected").each(function (i) {
		sel_process[i] = $(this).val();
	});

	var unsel_process = [];
	$("#process_item :not(:selected)").each(function (i) {
		unsel_process[i] = $(this).val();
	});

	if(counter == 0){
		add_field();
	}else{
		var pro_counter = $("#process_item :selected").length;

		if (pro_counter == 0) {
			toastr.warning("SELECT PROCESS", "ERROR");
			return false;
		}


		var form_data = new FormData();
		var product_id = $("#direct_product_id").val();
		
		if(rp_id!= '')
		{
			var rp_id = rp_id;
		}
	
	
		form_data.append('mode','bom_process_add');
		form_data.append('sel_process',sel_process);
		form_data.append('unsel_process',unsel_process);
		form_data.append('rp_id',rp_id);
		if($('#process_sel_product_id').val() !=""){
			form_data.append('product_id',$('#process_sel_product_id').val());
			
		}else{
			form_data.append('product_id',product_id);
		}
		form_data.append('multiple_value',$("#multiple_value").val());
		var edit_id =  $('#edit_id').val();
		if(typeof edit_id != 'undefined')
		{
			form_data.append('edit_id',$('#edit_id').val());
		}
		

		$.ajax({		
			url: root_domain+'app/direct_jobcard/',
			type: "POST",
			data: form_data,
			contentType: false,
			cache: false,
			processData:false,	
			success: function(response)
			{
			
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				
				
				$('#preview_bom_add_process_modal').modal('hide');
				toastr.success("WORK ORDER PROCESS ADDED SUCCESSFULLY", "SUCCESS");
				$('#in_process_qty_main').attr("readonly", false); 
				//$('#add_wo_prd').css('display','block');
				// if($('#process_sel_product_id').val() ==""){
					//return false;
				add_field();
			// }
			process_reset();
			//location.href="";
				Unloading();

			}
			else if(arr.msg == 'update') {
				// if($('#process_sel_product_id').val() ==""){
				add_field();
			// }
				process_reset();
				$('#preview_bom_add_process_modal').modal('hide');
				toastr.success("WORK ORDER PROCESS UPDATED SUCCESSFULLY", "SUCCESS");
				$('#in_process_qty_main').attr("readonly", false); 
				$('#add_wo_prd').css('display','block');
				// add_field();
				Unloading();

			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			
			get_tree_request();
		
			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
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
		url: root_domain+'app/direct_jobcard/',
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
			
			Unloading();
			
		}
	});
}


function manage_resource(type){

							if(type=='2'){
								$('.resource_label_manage').addClass('hide');
								$('.processRate_label_manage').addClass('hide');
							}else{
								$('.resource_label_manage').removeClass('hide');
								$('.processRate_label_manage').removeClass('hide');

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
	// console.log($('#product_type').val());
	if(($('#product_type').val() == "3") || ($('#product_type').val() == "5")){

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
		url: root_domain+'app/direct_jobcard/',
		data: { mode : "fieldadd",
		tot_standrad_qty:tot_standrad_qty,
		invoicetype_id:$("#invoicetype_id").val(),
		product_type:$("#product_type").val(),
		edit_id:$("#edit_id").val(),
		product_id:$("#product_id").val(),
		product_base_unit:$("#product_base_unit").val(),
		product_base_qty:product_base_qty,
		product_conv_unit:$("#product_conv_unit").val(),
		product_conv_qty:product_conv_qty,
		p_bom_id:$("#p_bom_id").val(),
		bom_id:$("#bom_id").val(),
		sel_product_id:$("#sel_product_id").val(),
		base_qty:$("#base_qty").val(),
		conv_qty:$("#conv_qty").val(),
		base_unit:$("#base_unit").val(),
		conv_unit:$("#conv_unit").val(),
				/*product_width:$('#product_width').val(),
				product_height:$('#product_height').val(),
				product_thickness:$('#product_thickness').val(),
				product_density:$('#product_density').val(),*/

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

				/*Jayesh Added : 04-08-2021 */
				$('#addprocess').val('Add');
				$('#pro_version_id').empty().append('<option value">Select Product Version</>');

				Unloading();
				/*//load_bom_version_datatable();
				if(alloted==1){
					show_alloted_data();
				}else{
					show_data();
				}*/
					//show_data();
				}
			}
		});
}

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
				url: root_domain+'app/direct_jobcard/',
				data: { mode : "check_bom_version_by_product",product_id:product_id,branch_id:branch_id},
				success: function(response)
				{ 
					$('#add_bom_version_id').html('');
					if(response != 0)
					{
						$('#add_bom_version_id').html(response);
					}
					else
					{
						$('#add_bom_version_id').html('<option selected="selected" value="10000">R&D</option>');
						
					}
					$("#add_bom_version_id").val("10000");
					$('#add_bom_version_id').trigger('change');
					//$('#add_bom_version_id').html(response);
					Unloading();
					
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
							$("#direct_product_id").val('');
							
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
							
						
								$.ajax({
									type: "POST",
									url: root_domain+'app/direct_jobcard/',
									data: { 
										mode : 'get_product_process_data',
										product_id:product_id,
										rp_id:rp_id,
										bom_version_id:bom_version_id,
										edit_id :edit_id
									},
									success: function(data){

										$('#mod_per_div_add_process').empty();
										$('#mod_per_div_add_process').html(data);
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
												alert(selProcess);
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

function check_product_btn(po_req_no)
{
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+'app/direct_jobcard/',
	data: { mode : 'check_work_order_process',po_req_no:po_req_no},
	success: function(data){
		
	
		if(data== 1)
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
	url: root_domain+'app/direct_jobcard/',
	data: { mode : 'check_product_unit',product_id:product_id},
	success: function(data){
			
		if(data != 0)
		{
			if(id==1)
			{
				$('#product_unit').text(data);
			}
			else
			{
				$('#sub_product_unit').text(data);
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
	
	$.ajax({
	type: "POST",
	url: root_domain+'app/direct_jobcard/',
	data: { mode : 'get_requested_proudct_details',rp_id:rp_id},
	success: function(data){
		console.log(data);
		var arr = jQuery.parseJSON(data);
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
		url: root_domain+'app/direct_jobcard/',
		data: form_data,
		success: function(response)
		{
			
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
            
             Unloading(); return false;
         
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
		"sAjaxSource": root_domain+'app/direct_jobcard/',
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

	$.ajax({
			type: "POST",
			url: root_domain+'app/direct_jobcard/',
			data: { mode : "load_productdata",eid :pro_id },
			success: function(response)
			{
			
				var resp = jQuery.parseJSON(response); 
				
				$('#p_bom_id').val(resp.bom_id);
				$('#product_base_unit_name').val(resp.base_unit_name);
				$('#product_base_unit').val(resp.product_base_unit);
				$('#product_base_qty').val(resp.product_base_qty);
				$('#product_base_qty_hide').val(resp.product_base_qty);
				
				$('#product_conv_unit_name').val(resp.conv_unit_name);
				$('#product_conv_unit').val(resp.product_conv_unit);
				$('#product_conv_qty').val(resp.product_conv_qty);
				$('#product_conv_qty_hide').val(resp.product_conv_qty);
				
				$('#product_spec_hid').val(resp.product_specification);
				$('#product_density').val(resp.m_type_density);
				
				
				if(resp.product_specification!=0)
				{
					
					$('#get_spec_div').show();
					$('#get_spec_div').empty().prepend(resp.product_specification_code);
				
					get_ms_kg();
					
					$('#product_kg').val('');
					
				}
				else
				{
					
					$('#get_spec_div').hide();
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
	$.ajax({
		type: "POST",
		url: root_domain+'app/direct_jobcard/',
		data: { mode : "get_product_specification_cal", values : values, msid : msid },
		success: function(response)
		{
			
			$('#product_kg').val(response);
		}
	});
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
		$('#product_qty').val(product_kg);
		$('#sub_product_qty').val(product_kg);
		$('#product_base_qty_hide').val(product_kg);
		$('#product_conv_qty').val(product_kg);
		$('#product_conv_qty_hide').val(product_kg);
		
	}
	else
	{
		load_product_detail(product_id);
	}
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
						
						

/* END JAYESH */