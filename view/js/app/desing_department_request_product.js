var datatable;
$(document).ready(function() {
	work_order_submit_per();
	//get_tree_request();
	//show_data();
// validate the comment form when it is submitted
load_salesno();   
//alert("fdsa");
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
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/design_department_request_product/',
		data: { mode : "load_invoiceno" },
		success: function(data){
		//alert("da");
			//console.log(data);
			var no = jQuery.parseJSON(data);
		
			$('#po_req_no').val(no.invoiceno);
			check_main_process_request();
			check_poreq_status();
		}
	});
}
function check_poreq_status(){
	
	var eid=$('#eid').val();
	var po_req_no=$('#po_req_no').val();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/design_department_request_product/',
		data: { mode : "check_poreq_status",eid:eid,po_req_no:po_req_no },
		success: function(data){
			//alert(data);
			if(data==="0"){
				$(".mainRequest").show();
				$(".mainRequested").hide();
				$("#main_poreq_status").val("0");
				$('#rp_po_qty').attr('readonly',false);
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
		url: root_domain+'app/design_department_request_product/',
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
	//alert(eid);
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/design_department_request_product/',
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
				sales_order_trn_id:sales_order_trn_id  
		},
		success: function(data){
			//return false;
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
	//alert(eid);
	//alert(sales_order_trn_id);
	$.ajax({
		type: "POST",
		url: root_domain+'app/design_department_request_product/',
		data: { mode : "check_main_process_request",po_req_no:po_req_no,eid:eid,sales_order_trn_id:sales_order_trn_id },
		success: function(response)
		{
	
			var data=JSON.parse(response);
			
			if(data.count>0)
			{
				$('#rp_req_qty').val(data.req_qty);
				$('#in_process_qty_main').val(data.process_qty);
				$('#rp_po_qty').val(data.po_qty);
				$('#po_req_no').val(data.po_req_no);
				
				//if(data.sp_status==0)
				//{
					$('#rp_req_qty').attr('readonly',true);
					$('#in_process_qty_main').attr('readonly',true);
					$('#rp_po_qty').attr('readonly',true);
					$('#set_process_btn').hide();
				//}
				//Modified bcz late delay
				setTimeout(function(){ get_tree_request(); }, 1000);
			}else{
				//alert("fa");
				cal_po_qty();
				//alert("dsa");
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
	url: root_domain+'app/design_department_request_product/',
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
	url: root_domain+'app/design_department_request_product/',
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
	url: root_domain+'app/design_department_request_product/',
	data: { cust_id : cust_id,sales_order_date:sales_order_date,po_no:po_no,po_date:po_date,sales_order_no:sales_order_no,po_req_no:po_req_no,po_req_date:po_req_date,po_product_name:po_product_name,rp_req_qty:rp_req_qty,in_process_qty:in_process_qty,rp_po_qty:rp_po_qty,main_poreq_status:main_poreq_status,branch_id:branch_id,category_name:category_name,remark:remark,smode:smode,mode:mode,eid:eid,pr_type:pr_type,bom_id:bom_id,process_status:process_status,work_order_id:work_order_id,bom_check:bom_check,sales_order_trn_id:sales_order_trn_id },
	success: function(msg){
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
		url: root_domain+'app/design_department_request_product/',
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
	var mode=$('#mode').val();
	var eid=$('#eid').val();//Product ID
	var pr_type=$('#pr_type').val();
	var bom_id=$('#bom_id').val();
	var po_req_no=$('#po_req_no').val();
	var sales_order_trn_id=$('#sales_order_trn_id').val();
	//alert("12");
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+'app/design_department_request_product/',
	data: { mode : 'get_tree_request_new',eid:eid,pr_type:pr_type,bom_id:bom_id,po_req_no:po_req_no,sales_order_trn_id:sales_order_trn_id },
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
		url: root_domain+'app/design_department_request_product/',
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
	url: root_domain+'app/design_department_request_product/',
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
	url: root_domain+'app/design_department_request_product/',
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
					//pathik start date : 12-12-2020 
				// bom check if yes process qty show other wise hidden and purchase qty only show 
				
			/* }else{
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
					//pathik start date : 12-12-2020 
				// bom check if yes process qty show other wise hidden and purchase qty only show 
				
			} */

	}else{
		$("#in_process_qty_main").val("0");
		$("#rp_po_qty").val(rp_req_qty);
		$('#set_process_btn').hide();
		$('.proc1').hide();
		$('#req_val').html("<u><center><span style='color:red;font-size:20px;'>Note : This  is Only Purchase Product</br> If Your Process Product Create BOM First</span></center></u>");
		$('#save').show();
		$('#mode').val("purchase_mode");
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
	//alert(work_order_id);
	if(work_order_id){
		$.ajax({
		type: "POST",
		url: root_domain+'app/design_department_request_product/',
		data: { mode : 'work_order_submit_per',work_order_id:work_order_id },
		success: function(response){
			//alert(response);
				if(response==1)
				{
					$("#save").show();
				}else{
					$("#save").hide();
				}
			}		
		});
	}else{
		$("#save").hide();
	}
}