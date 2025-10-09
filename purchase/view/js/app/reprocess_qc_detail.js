$(document).ready(function() {

	//var datatable;

	load_datatable();
	$session_qc_work_type = $('#session_qc_work_type').val();
	if($session_qc_work_type!=''){
		// manage_qc_work_type(); // hide sanat
		manage_qc_work_type_new(); // add sanat
	}
	//get_amount();

// validate the comment form when it is submitted        
// validate vendor add form on keyup and submit
$("#qc_add").validate({
	rules: {
		qc_no: {
			required: true			
		},
		qc_date: {
			required: true
		}
		
	},
	messages: {
		qc_no: {
			required: "Enter QC No"
		},
		qc_date: {
			required: "Enter Date"
		}
		
		
	}
}); 
});

$("#qc_add").on('submit',function(e) {
	var acc = 	$('#qty_accept').val();
    var rej = $('#qty_reject').val();
    var repro = $('#qty_reprocess').val();
	var sum = parseFloat(acc) + parseFloat(rej) + parseFloat(repro) ;
    var pending_qty = parseFloat($("total_pending_qty").val());
	if(sum > pending_qty){
		toastr.warning("QTY GREATER THAN PENDING QTY, PLEASE ENTER PROPER VALUE IN ACCEPT & REJECT QTY", "ERROR");
		return false;
	}
	
	if(parseFloat(rej) > 0 && $('#new_product').val()==''){
		toastr.warning("PLEASE SELECT NEW PRODUCT", "ERROR");
		return false;
	}

	if(parseFloat(repro) > 0 && $('#new_process').val()==''){
		toastr.warning("PLEASE SELECT NEW PROCESS", "ERROR");
		return false;
	}

	if(parseFloat(rej) > 0){
		var qty = $("#total_new_qty").val()

		if(parseFloat(rej) != parseFloat(qty)){
			toastr.warning("PLEASE CHECK NEW PRODUCT QTY NOT MATCH AS REJECT QTY", "ERROR");
			return false;
		}
	}
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	
	/* var qty_accept=$("#qty_accept").val();
	var qty_reject=$("#qty_reject").val();
	var qty_reprocess=$("#qty_reprocess").val();
	
	var grn_pqty=$("#grn_pqty").val();
	
	
	
	if(){
		
	} */
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	$.ajax({
		cache:false,
		url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{

			
			//return false;
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				Unloading();
				toastr.success("ADDED SUCCESSFULLY", "SUCCESS");
				//window.location=root_domain+purchase_domain+'qc_done_list';
				//alert(arr.back);
				window.location=root_domain+arr.back;
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
				window.location=root_domain+purchase_domain+'qc_list';
			}
			//Unloading();
			$('#qc_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_qc_detail(id) 
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				console.log(response)
				if(response.trim() == "1") {
					toastr.success("SALES ORDER DELETE SUCCESSFULLY", "SUCCESS");
					datatable.fnReloadAjax();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}

function add_discount(type)
{
	var total=$("#total").val();
	var discount_amt=0; var discount_per=0;
	if(total!="")
	{
		if(type=="amt")
		{
			discount_amt=$('#discount_amt').val();
			discount_per=((discount_amt*100)/total).toFixed(2);
			$("#discount_per").val(discount_per);
		}
		else if(type=="per")	
		{
			discount_per=$('#discount_per').val();
			discount_amt=((total*discount_per)/100).toFixed(2);
			$("#discount_amt").val(discount_amt);
		}
		get_gtotal($('#formulaid').val());
	}
}
function get_discount(type)
{
	var qty=parseFloat($('#product_qty').val());
	var rate=parseFloat($('#product_rate').val());
	var disc=0;
	if(qty!="" && rate !="")
	{	
		if(type=="amt")
		{
			disc=100*parseFloat($('#product_discount').val())/(qty*rate);	
			$('#discount_per').val(disc);
		}
		else if(type=="per")
		{
			disc=((qty*rate)*parseFloat($('#discount_per').val()))/100;	
			$('#product_discount').val(disc);
		}
	}
	else
	{
		$('#product_discount').val('');
		$('#discount_per').val('');
	}
	get_amount();
}
function add_freight()
{
	get_gtotal($('#formulaid').val());
}
function cal_discount()
{
	get_gtotal($('#formulaid').val());
}
function get_amount()
{	
	var id=parseInt($('#fieldcnt').val())+1;
	if($("#product_qty").val()!="" && $("#product_rate").val()!="")
	{
		var q=$("#product_qty").val();
		var rate=$("#product_rate").val();
		var a=q*rate;
			if($("#product_discount").val()!="" )//discount calculation
			{	
				var discount=parseFloat($("#product_discount").val());
				a=a-discount; 
			}
			$("#product_amount").val(parseFloat(a));
			$("#taxable_value").val(parseFloat(a));
			if($("#formulaid").val()!="")//tax calculation
			{
				var total=a;
				var formulaid=$("#formulaid").val();
				$.ajax({
					type: "POST",
					url: root_domain+purchase_domain+'app/purchase_order/',
					data: { mode : "getproduct_amount",  product_amount : total ,formulaid:formulaid},
					success: function(response)
					{
						//alert(response);
						var obj=jQuery.parseJSON(response);
						//alert(obj.tax_total);
						$('#product_amount').val(obj.total);
						$('#product_amount_tax').val(obj.tax_total);
					}
				});
			}
		}
		else
		{
			$("#product_amount").val(0);
		}
		get_gtotal();
	}
	function get_gtotal(id)
	{	
		var input_amount=(document.getElementsByName('amount[]'));
		var cnt=input_amount.length;
		var total=$("#product_amount").val();
		var c_total=0;
		if(total=="")
		{
			total=0;
		}
		for(var i=0;i<cnt;i++)
		{	
			var t=input_amount[i].value;
			if(t>0)
				total=parseFloat(total)+parseFloat(t);
		}

		$("#total").val(parseFloat(total));
		var p=$("#packing").val();
		if(p>0)
		{
			total=parseFloat(total)+parseFloat(p);
		}
	/*
	var f=$("#freight").val();
	if(f>0)
	{
		c_total=parseFloat(c_total)+parseFloat(f);
	}
	
	var d=$("#discount_amt").val();
	if(d>0)
	{
		c_total=parseFloat(c_total)-parseFloat(d);
	}*/
	
	g_total=total;
	$("#g_total").val(total);
	/*
	$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
			data: { mode : "formulavalue",eid :id,total : g_total, c_total:c_total},
			success: function(response)
			{
				//console.log(response);
				$('#showformulatextbox').html(response);
				g_total=parseFloat($('#rate').val());
				
				$("#g_total").val(g_total);
				
			}
	});
	*/
}
function load_productdetail(val) {
	
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
		data: { mode : "load_productdata",eid :val },
		success: function(response)
		{
				//console.log(response);
				var obj =jQuery.parseJSON(response)
				$('#product_des').val(obj.product_desc);				
				$('#product_hsn_code').val(obj.product_hsn);				
				$('#product_rate').val(obj.product_sale_rate);	
				$('#unit_id').select2("val",obj.product_base_unit);
				load_product_tax(obj.product_id,'purchase');
			}
		});
}


function load_product_tax(pid,tran_type)
{
	//alert(pid);
	Loading();
	
	var vendor=$('#vender_id').val();
	
	if(vendor!=''){
		
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/purchase_order/',
			data: { mode : "load_product_tax", pid : pid,tran_type:tran_type,vendor:vendor },
			success: function(response)
			{
				//alert(response);
				
				console.log(response);
				var resp = JSON.parse(response);
				
				$('#sel_tax').val(resp.name);
				$('#formulaid').val(resp.id);
				$('#formula_tax_id').val(resp.tax_id);
				
				Unloading();
			}
		});
		
	}
	Unloading();
}

function add_field()
{
	if($("#product_type").val()==""){
		toastr.warning("Select Product Type", "ERROR")
		$("#product_type").select2('focus');
		return false;
	}
	else if($("#product_id").val()===""){
		toastr.warning("Select Product Name", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	else if($("#product_qty").val()===""){
		toastr.warning("Enter Qty", "ERROR")
		return false;
	}
	else if($("#product_rate").val()===""){		
		toastr.warning("Enter Rate", "ERROR")
		return false;
	}
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
		data: { mode : "fieldadd",edit_id:$("#edit_id").val(),product_type:$("#product_type").val(),product_id:$("#product_id").val(),product_des:$("#product_des").val(),product_hsn_code:$("#product_hsn_code").val(),product_qty:$("#product_qty").val(),unit_id:$("#unit_id").val(),qc_detail_id:$("#eid").val() },
		success: function(response)
		{
			console.log(response);
				//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
				$("#product_id").select2("val","")
				$("#product_des").val("")
				$("#product_hsn_code").val("")
				$("#product_qty").val("")
				//$("#sqr_ft").val("")
				$("#unit_id").select2('val',"")
				$("#edit_id").val('')
				$('#addproduct').show();
				$('#addrow').val('Add');
				
				Unloading();
				if($("#eid").val()=="")
				{
					show_data()
				}
				else
				{
					location.reload();
				}
			}
		});
}

function add_bom_field()
{
	if($("#up_pro_type").val()==""){
		toastr.warning("Select Product Type", "ERROR")
		$("#up_pro_type").select2('focus');
		return false;
	}
	else if($("#up_pro").val()===""){
		toastr.warning("Select Product Name", "ERROR");
		$("#up_pro").select2('focus');
		return false;
	}
	else if($("#up_add_qty").val()===""){
		toastr.warning("Enter Qty", "ERROR")
		return false;
	}
	else if($("#bom_sotrn_id").val()===""){		
		toastr.warning("Enter Product", "ERROR")
		return false;
	}
	var trnid=$("#bom_sotrn_id").val();
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
		data: { mode : "fieldbomadd",bom_sotrn_id:$("#bom_sotrn_id").val(),up_main_pro:$("#up_main_pro").val(),up_pro_type:$("#up_pro_type").val(),up_pro:$("#up_pro").val(),up_add_qty:$("#up_add_qty").val()},
		success: function(response)
		{
				//console.log(response);
				//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
				$("#bom_sotrn_id").select2("val","")
				$("#up_main_pro").select2("val","")
				
				$("#up_pro_type").val("")
				$("#up_pro").val("")
				$("#product_qty").val("")
				$("#up_add_qty").val("")
				show_bom_product_trn_data(trnid);
				Unloading();
				
			}
		});
}

function reload_data()
{
	//datatable.fnReloadAjax();
	load_datatable();
}	
function load_datatable()
{
	var data=$('input[name=report]:Checked').val();
	var date=$('#rep_date').val();
	var type=$('#type_id').val();
	datatable = $("#dynamic-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+purchase_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[-1, 10, 20, 30, 50], ["All", 10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+purchase_domain+'app/reprocess_qc_detail/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "date", "value": date } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
	}

	function show_data()
	{
		var qc_detail_id=$('#eid').val();
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
			data: { mode : "load_tempoutward",qc_detail_id:qc_detail_id},
			success: function(data){
				//console.log(data);
				$('#sale_productdata').html(data);				
				get_amount()
				Unloading();
			}		

		});

	}
	function show_bom_product_data()
	{
		var qc_detail_id=$('#eid').val();
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
			data: { mode : "show_bom_product_data",qc_detail_id:qc_detail_id},
			success: function(data){
				//console.log(data);
				$('#sale_productdata').html(data);				
				Unloading();
			}		

		});

	}
	function show_bom_product_trn_data(qc_detail_trn_id)
	{
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
			data: { mode : "show_bom_product_trn_data",qc_detail_trn_id:qc_detail_trn_id},
			success: function(data){
				//console.log(data);
				$('#sale_productdata').html(data);	
				$("#up_main_pro").select2({
					width: '100%'
				});
				$("#up_pro").select2({
					width: '100%'
				});
				$("#up_pro_type").select2({
					width: '100%'
				});
				Unloading();
			}		

		});

	}
	function load_bom_data(qc_detail_trn_id)
	{
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
			data: { mode : "load_bom_data",qc_detail_trn_id:qc_detail_trn_id},
			success: function(data){
				//console.log(data);
			//$('#sale_productdata').html(data);
			show_bom_product_trn_data(qc_detail_trn_id);			
			Unloading();
		}		
		
	});

	}

	function edit_data(id,table,whereid)
	{
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
			data: { mode : "preedit",  id : id ,table:table,whereid:whereid},
			success: function(response)
			{
				console.log(response)
				var data = jQuery.parseJSON(response);
				$('#product_id').html(data.producthtml);
				//$('#product_id').append('<option value="'+data.product_id+'">'+data.product_name+'</option>');
				$("#product_type").select2("val",data.product_type)
				$("#product_id").select2("val",data.product_id)
				$("#product_hsn_code").val(data.product_hsn_code)
				$("#product_des").val(data.description)
				$("#product_qty").val(data.product_qty)
				//$("#sqr_ft").val(data.sqr_ft)
				$("#product_rate").val(data.product_rate)
				$("#product_disc").val(data.product_disc)
				$("#unit_id").select2("val",data.unit_id);
				$("#formulaid").val(data.formulaid);
				$("#product_amount").val(data.total)
				$("#product_discount").val(data.product_discount)
				$("#discount_per").val(data.discount_per)
				$("#taxable_value").val(data.product_amount)
				$("#edit_id").val(id)
				$('#addrow').val('Update');
				Unloading();
			}
		});
	}
	function delete_data(id,table,whereid)
	{
		var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
				data: { mode : "delete_data",  eid : id ,table:table,whereid:whereid,qc_detail_id:$("#eid").val() },
				success: function(response)
				{
					console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						if($("#eid").val()=="")
						{	
						//$('#product_id').html(data.producthtml);
						show_data()
					}
					else
					{
						location.reload();
					}
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
		}
	}
	function delete_bom_data(bomtrnid,sotrnid)
	{
		var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
				data: { mode : "delete_bom_data",  bomtrnid : bomtrnid},
				success: function(response)
				{

					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						show_bom_product_trn_data(sotrnid);
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	}

	function load_product(type_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
			data: { mode : "load_product", type_id : type_id},
			success: function(data){
			//console.log(data);
			$('#product_id').html(data);				
			Unloading();
		}
	});
	}
	function load_product_bom(type_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
			data: { mode : "load_product", type_id : type_id},
			success: function(data){
			//console.log(data);
			$('#up_pro').html(data);				
			Unloading();
		}
	});
	}
	function load_pro_detail(){
		var product_id = $('#product_id').val();
		if(product_id){
			load_productdetail(product_id);
		}
	}
	function get_series_no(){

		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
			data: { mode : "get_series_no"},
			success: function(resp){
			//alert(resp);
			//console.log(resp);
			$('#invoicetype_id').val(resp);	
			load_salesno(resp);	
		}		
	});	
	}
	function load_salesno(id){

		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
			data: { mode : "load_invoiceno", typeid : id },
			success: function(data){
			//alert(data);
			//console.log(data);
			var no = jQuery.parseJSON(data);
			$('#qc_no').val(no.invoiceno);
		}
	});
	}
	function update_trn(num)
	{
		var tid=$("#up_trn"+num).val();
		var qid=$("#up_qty"+num).val();

		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
			data: { mode : "update_trn", bom_trn_id : tid,qty:qid },
			success: function(data){

			}
		});
	}

	function get_grn_product()
	{
	//alert(grn_id);
	var eid=$('#eid').val();
	//alert(eid);
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
		data: { mode : "get_grn_product", eid:eid },
		success: function(data){
			//alert(data);
			$('#qc_productdata').html(data);
		}
	});
}


function get_po_no(grn_id)
{
	//alert(grn_id);
	var eid=$('#eid').val();
	//alert(eid);
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
		data: { mode : "get_po_no", grn_id : grn_id,eid:eid },
		success: function(data){
			//alert(data);
			$('#po_id').val(data);
		}
	});
}

function add_qc_param(pid,pname,grn)
{
	
	$('#table_add_qc_param').modal('show');
	$('#pid').html(pname);
	$('#pid_text').val(pid);
	$('#grn_text').val(grn);
	show_qc_param_details(pid,grn);
}

function get_mrn(qid)
{
	//alert(qid);
	$('#table_show_mrn').modal('show');
	show_mrn_details(qid);
}

function show_qc_param_details()
{
	var eid=$('#eid').val();
	//var pid=$('#grn_product').val();
	//alert(pid);
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
		data: { mode : "show_qc_param_details",eid:eid },
		success: function(data){
			$('#qc_productdata_parameter').html(data);
		}
	});
}

function show_mrn_details(qid)
{
	//alert(pid);
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
		data: { mode : "show_mrn_details", qid : qid },
		success: function(data){
			$('#mrn_div').html(data);
		}
	});
}



function add_qc_param_data()
{
	Loading();	

	var qc_pname_arr=[];
	var qc_param_value_arr=[];
	var tested_value_arr=[];
	
	var qc_pname = $('input[name="qc_pname[]"]').val();
	var qc_param_value = $('input[name="qc_param_value[]"]').val();
	var tested_value = $('input[name="tested_value[]"]').val();
	var form_mode = $('#mode').val();
	var pid = $('#pid_text').val();
	var eid = $('#eid').val();
	var grn_no = $('#grn_no').val();
	//var branch_mode=$('#branch_mode').val();
	i = 0;
	$('input.qc_pname').each(function(){ 

		qc_pname_arr[i++]=$(this).val();

	});

	j = 0;
	$('input.qc_param_value').each(function(){ 


		qc_param_value_arr[j++]=$(this).val();

	});

	k = 0;
	$('input.tested_value').each(function(){ 


		tested_value_arr[k++]=$(this).val();

	});
	
	//alert(bstock_arr);
	//alert(pid);
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
		data: { mode : "add_qc_param_data",qc_pname:qc_pname_arr,qc_param_value:qc_param_value_arr,tested_value:tested_value_arr,form_mode:form_mode,pid:pid,eid:eid,grn_no:grn_no },
		success: function(response)
		{
			console.log(response);
			$('#table_add_qc_param').modal('hide');
			toastr.success("PARAMETER ADDED SUCCESSFULLY", "SUCCESS");
			//alert(response);
			Unloading();
			
		}
	}); 
	
	//alert(bstock);
	//var data_to_send = $.serialize();
	
}


function delete_qc(id) 
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				console.log(response)
				if(response.trim() == "1") {
					toastr.success("DELETE SUCCESSFULLY", "SUCCESS");
					datatable.fnReloadAjax();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}

function sub_accept_value()
{
	//alert(cnt);
	var grn_pqty = Number($('#grn_pqty').val());
	
	var qty_accept = Number($('#qty_accept').val());
	var qty_reject = Number($('#qty_reject').val());
	var qty_reprocess = Number($('#qty_reprocess').val());
	//alert(grn_pqty);
	var remain_qty1=grn_pqty-(qty_accept+qty_reprocess);
	if(remain_qty1<0){
		remain_qty1=0;
	}
	var remain_qty=(qty_accept+qty_reprocess+remain_qty1);
	//var remain_qty1=grn_pqty-(qty_accept+qty_reject+qty_reprocess);
	//alert(remain_qty);
	$('#qty_accept_hid').val(remain_qty1);
	$('#qty_reject').val(remain_qty1);
	if(remain_qty<grn_pqty)
	{
		$('#qty_error').html('Value Not More Than Total Qty');
		$('#save').prop('disabled',true);
	}else if(remain_qty>grn_pqty){
		$('#qty_error').html('Value Not More Than Total Qty');
		$('#save').prop('disabled',true);
	}
	else
	{
		//$('#qty_reject'+cnt).val('0');
		$('#qty_error').html('');
		$('#save').prop('disabled',false);
	}

	var chk_reject_qty = Number($('#qty_reject').val());
	if(chk_reject_qty > 0){
		$(".reject_product_add").show();
		load_products();
		load_new_product_data();
		$("#new_productdata").show();

	}else{
		$(".reject_product_add").hide();
		$("#new_productdata").hide();
	}
}


function toggle_add_reject_product(){
	var qty_reject = Number($('#item_rejected_qty').val());
	if(qty_reject > 0){
		$(".reject_product_add").show();
	}else{
		$(".reject_product_add").hide();
	}
}

function sub_reject_value(cnt)
{
	//alert(cnt);
	var qty_accept_hid = Number($('#qty_accept_hid'+cnt).val());
	var qty_reject = Number($('#qty_reject'+cnt).val());
	var qty_reject = Number($('#qty_reject'+cnt).val());
	//alert(grn_pqty);
	var remain_qty=qty_accept_hid-qty_reject;
	//alert(remain_qty);
	$('#qty_reject_hid'+cnt).val(remain_qty);
	
	if(remain_qty<0)
	{
		$('#qty_error_reject'+cnt).html('Value Not More Than Total Qty');
		$('#save').prop('disabled',true);
	}
	else
	{
		$('#qty_error_reject'+cnt).html('');
		$('#save').prop('disabled',false);
	}
}

function manage_qc_work_type() {
	
	var qc_work_type = $('#qc_work_type').val();
	var total_pending_qty = $('#total_pending_qty').val();
	var grn_product = $('#grn_product').val();
	var grn_trn_id = $('#grn_trn_id').val();
	var branch_id = $('#branch_id').val();
	var process_show = $('#process_show').val();
	var current_process_id = $('#current_process_id').val();
	//alert("fds1");
	if(qc_work_type!=''){

		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
			data: { 
				mode : "get_each_qty_qc_param",
				qc_work_type : qc_work_type, 
				total_pending_qty: total_pending_qty,
				grn_product : grn_product,
				grn_trn_id : grn_trn_id,
				branch_id : branch_id,
				process_show : process_show,
				current_process_id : current_process_id
			},
			success: function(response)
			{
				//$('#final_submit_div').hide();
				$('#get_each_qty_qc_param1').empty().prepend(response);
				//alert("fds");
			}
		});
		Unloading();
	}else{
		toastr.warning("Please select qc type", "WARNING");
		$("#qc_work_type").select2('focus');
		return false;
	}	
}

function manage_qc_work_type_new() {
	
	var qc_work_type = $('#qc_work_type').val();
	var total_pending_qty = $('#total_pending_qty').val();
	var grn_product = $('#grn_product').val();
	var grn_trn_id = $('#grn_trn_id').val();
	var branch_id = $('#branch_id').val();
	var process_show = $('#process_show').val();
	var current_process_id = $('#current_process_id').val();
	var qc_qty = $("#qc_qty").val();

	if(qc_qty < 1){
		qc_qty = 1;
	}
	//alert("fds1");
	if(qc_work_type!=''){

		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
			data: { 
				mode : "get_each_qty_qc_param_new",
				qc_work_type : qc_work_type, 
				total_pending_qty: total_pending_qty,
				grn_product : grn_product,
				grn_trn_id : grn_trn_id,
				branch_id : branch_id,
				process_show : process_show,
				current_process_id : current_process_id,
				qc_qty:qc_qty
			},
			success: function(response)
			{
				//$('#final_submit_div').hide();
				$('#get_each_qty_qc_param1_new').empty().prepend(response);
				//alert("fds");
			}
		});
		Unloading();
	}else{
		toastr.warning("Please select qc type", "WARNING");
		$("#qc_work_type").select2('focus');
		return false;
	}	
}

// $(document).on('blur','.claculate_status', function(){
	
// 	var thisdata = $(this);
// 	var product_id = $(this).data('product_id');
// 	var pr_param_id = $(this).data('pr_param_id');
// 	var item_id = $(this).data('item_id');
// 	var status_id = $(this).data('status_id'); // add sanat
// 	var param_value = $(this).data('param_value');
// 	var field_key = $(this).data('field_key');
// 	var item_qc_id = $(this).attr('data-item_qc_id');
// 	var entered_value = $(this).val();
// 	var branch_id = $('#branch_id').val();
// 	var qc_work_type = $('#qc_work_type').val();
// 	var grn_trn_id = $('#grn_trn_id').val();

// 	if(entered_value!=''){
// 		Loading(true);
// 		$.ajax({
// 			type: "POST",
// 			url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
// 			data: { 				item_id : item_id,

// 				mode : "insert_item_data",
// 				product_id : product_id, 
// 				pr_param_id: pr_param_id,
// 				param_value : param_value,
// 				field_key : field_key,
// 				item_qc_id : item_qc_id,
// 				entered_value : entered_value,
// 				qc_work_type : qc_work_type,
// 				branch_id : branch_id,
// 				grn_trn_id : grn_trn_id
// 			},
// 			success: function(response)
// 			{
// 				Unloading();
// 				thisdata.attr('data-item_qc_id', response);
// 				check_item_status(product_id, pr_param_id, grn_trn_id, item_id, item_qc_id, qc_work_type, branch_id,status_id);
// 			}
// 		});
		
// 	}
// });

/*function check_item_status(product_id, pr_param_id, grn_trn_id, item_id, item_qc_id, qc_work_type, branch_id,status_id){
	var grn_pqty = $('#grn_pqty').val();	
	var current_process_id = $('#current_process_id').val();	
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
		data: { 
			mode : "get_item_status",
			product_id : product_id, 
			pr_param_id: pr_param_id,
			grn_trn_id : grn_trn_id,
			item_id : item_id,
			item_qc_id : item_qc_id,
			qc_work_type : qc_work_type,
			branch_id : branch_id,
			grn_pqty : grn_pqty,
			current_process_id : current_process_id
		},
		success: function(response)
		{	
			var obj = jQuery.parseJSON(response);
			
			if(obj.msg=='1'){
				// console.log('status_'+status_id+'_'+item_id)
				var status='';
				if(obj.status=='1'){
					$('#status_'+status_id+'_'+item_id).removeClass('show').addClass('hide');
					status='<span class="label label-success">QC Pass</span>';
				}else{
					status='<span class="label label-danger">QC Fail</span>';
					$('#status_'+status_id+'_'+item_id).prop('selectedIndex',0);
					$('#status_'+status_id+'_'+item_id).removeClass('hide').addClass('show');
				}
				$('#item_status_'+status_id+'_'+item_id).html(status);
				$('#status_'+status_id+'_'+item_id).attr('data-status_id', obj.item_status_id);

				$('#item_accepted_qty').val(obj.accepted);
				$('#item_rejected_qty').val(obj.rejected);
				$('#item_reprocess_qty').val(obj.reprocessed);
			}
		}
	});
}*/

/*$(document).on('change','.reject_reprocess_qty', function(){
	var status_type = $(this).val();
	var status_id = $(this).attr('data-status_id');
	var qc_work_type = $('#qc_work_type').val();
	var grn_pqty = $('#grn_pqty').val();	
	var grn_trn_id = $('#grn_trn_id').val();
	var grn_product = $('#grn_product').val();
	

	Loading(true);

	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
		data: { 
			mode : "update_item_status",
			status_type : status_type, 
			status_id: status_id,
			qc_work_type : qc_work_type,
			grn_pqty : grn_pqty,
			grn_trn_id : grn_trn_id,
			grn_product : grn_product
		},
		success: function(response)
		{	
			Unloading();
			var obj = jQuery.parseJSON(response);
			$('#item_accepted_qty').val(obj.accepted);
			$('#item_rejected_qty').val(obj.rejected);
			$('#item_reprocess_qty').val(obj.reprocessed);
		}
	});
});*/

$(document).on('click',"#caluclate_qty",function(e) {
	var fail = false;
	var fail_log = '';
	var name;
	$( '#get_each_qty_qc_param' ).find( 'select, textarea, input' ).each(function(){
		if( ! $( this ).prop( 'required' )){
		} else {
			if ( ! $( this ).val() &&  ! $(this).hasClass("hide")) {
				fail = true;
				name = $( this ).attr( 'name' );
				fail_log += name + " is required \n";
			}
		}
	});

    //submit if fail never got set to true
    if ( ! fail ) {
        //process form here.
        var item_accepted_qty = $('#item_accepted_qty').val();
        var item_qc_accepted_godown = $('#item_qc_accepted_godown').val();
        var item_rejected_qty = $('#item_rejected_qty').val();
        var item_qc_rejected_godown = $('#item_qc_rejected_godown').val();
        var item_reprocess_qty = $('#item_reprocess_qty').val();
        var item_qc_reprocess_godown = $('#item_qc_reprocess_godown').val();

        $('#qty_accept').val(item_accepted_qty);
        $('#qty_reject').val(item_rejected_qty);
        $('#qty_reprocess').val(item_reprocess_qty);

        $('#qc_godown').val(item_qc_accepted_godown);
        $('#qc_reject_godown').val(item_qc_rejected_godown);
        $('#qc_reporcess_godown').val(item_qc_reprocess_godown);

        $('#final_submit_div').show();
    } else {
    	$('#final_submit_div').hide();
        //alert( fail_log );
    }

});

$( document ).on('click','.view_toggle_image',function() {
	$('.image_div').slideToggle('slow');		
   //$( ".image_div" ).slideDown();
});

function view_revision_image(id)
{
	if(id){
		Loading(true);
		editReq = $.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/drawing/',
			data: { mode : "view_revision_image", id : id },
			success: function(response)
			{
				$('#revision_image_list').html(response);
				$("#Modal_view_revision_image").modal("show");
				
				Unloading();
			}
		});	
	}
}
function get_qc_qty(total, qty, product_name){
	total = parseFloat(total);
	qty = parseFloat(qty);
	if(total < qty){
		//console.log("123");
		// $('#qc_qtys').html('QC Qty must be less than Total Qty').css('color','red');
		qty = 1;
	}else if(qty <= 0){
		//console.log("456");
		$('#qc_qtys').html('QC Qty atleast 1').css('color','red');
	}else if(qty > 0 || total > qty){
		//console.log("789");
		$('#qc_qtys').html('');
		myFunction(qty, product_name);
	}
}
function myFunction(qty, product_name) {
	// console.log(qty);
	// var res = manage_qc_work_type();  // hide sanat
	 var res = manage_qc_work_type_new(); // add sanat
	var btn = document.createElement("div");
	btn.className += 'row';
	for(var i=1; i<=qty; i++){
		btn.innerHTML+='<div class="form-group col-lg-12 clearfix"><div class="form-group col-lg-3"><label class="control-label col-lg-12" for="product'+i+'"><strong>'+product_name+' '+i+'</strong></label></div><div class="form-group col-lg-5"><label class="col-lg-4 control-label ">QC</lable><div class="col-lg-8"><input type="text" class="form-control" value="" readonly></div></div><div class="form-group col-lg-2"><div class="col-lg-12"><input type="text" class="form-control" value="" readonly></div></div><div class="form-group col-lg-2"><div class="col-lg-12"><input type="text" class="form-control" value="" readonly></div></div></div>';
	}
	document.getElementById("productloop").appendChild(btn);
}

function qty_byphargation(){
	var fail = false;
	var fail_log = '';
	var name;
	$( '#get_each_qty_qc_param' ).find( 'select, textarea, input' ).each(function(){
		if( ! $( this ).prop( 'required' )){
		} else {
			if ( ! $( this ).val() &&  ! $(this).hasClass("hide")) {
				fail = true;
				name = $( this ).attr( 'name' );
				fail_log += name + " is required \n";
			}
		}
	});

    //submit if fail never got set to true
    if ( ! fail ) {
        //process form here.
        var item_accepted_qty = $('#item_accepted_qty').val();
        var item_qc_accepted_godown = $('#item_qc_accepted_godown').val();
        var item_rejected_qty = $('#item_rejected_qty').val();
        var item_qc_rejected_godown = $('#item_qc_rejected_godown').val();
        var item_reprocess_qty = $('#item_reprocess_qty').val();
        var item_qc_reprocess_godown = $('#item_qc_reprocess_godown').val();
        console.log(item_accepted_qty);
        $('#qty_accept').val(item_accepted_qty);
        $('#qty_reject').val(item_rejected_qty);
        $('#qty_reprocess').val(item_reprocess_qty);

        $('#qc_godown').val(item_qc_accepted_godown);
        $('#qc_reject_godown').val(item_qc_rejected_godown);
        $('#qc_reporcess_godown').val(item_qc_reprocess_godown);

        $('#final_submit_div').show();
    } else {

    	$('#final_submit_div').hide();
        //alert( fail_log );
    }
}

function qc_submit()
{
	$("#qc_add").submit();	
}

function load_qc_perameter(){
	var process_id=$("#current_process_id").val();
	var product_id=$("#grn_product").val();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
		data: { mode : "load_qc_perameter", process_id : process_id,product_id:product_id },
		success: function(data){
			$('#qc_pera_show').html(data);
		}
	});
}

$(document).on('blur','.calculate_qc_status', function(){
	
	var thisdata = $(this);
	var product_id = $(this).data('product_id');
	var pr_param_id = $(this).data('pr_param_id');
	var item_id = $(this).data('item_id');
	var status_id = $(this).data('status_id'); // add sanat
	var param_value = $(this).data('param_value');
	var field_key = $(this).data('field_key');
	var item_qc_id = $(this).attr('data-item_qc_id');
	var entered_value = $(this).val();
	var branch_id = $('#branch_id').val();
	var qc_work_type = $('#qc_work_type').val();
	var grn_trn_id = $('#grn_trn_id').val();

	var total_pending_qty = $("#total_pending_qty").val();
	var qc_qty = $("#qc_qty").val();
	
	var qc_fail= 0;
	var qc_reprocess= 0;

	$('input.claculate_status_'+status_id).each(function(e){
		var param_value = $(this).data('param_value');
		var entered_value = $(this).val();
		var tolerance_minus = $(this).data('tolerance_minus');
		var tolerance_plus = $(this).data('tolerance_plus');
		
		/*qc_check_tol_plus = parseFloat(param_value) + parseFloat(tolerance_plus);
		qc_check_tol_minus = parseFloat(param_value) - parseFloat(tolerance_minus); 

		if(entered_value == "" || param_value != entered_value){
		 	qc_fail +=parseFloat(1);
		}
		if(entered_value == "" ||  qc_check_tol_plus  < entered_value || qc_check_tol_minus > entered_value){
		 	qc_fail +=parseFloat(1);
		 }*/

		  if($.isNumeric(param_value)){
			 qc_check_tol_plus = parseFloat(param_value) + parseFloat(tolerance_plus);
			 qc_check_tol_minus = parseFloat(param_value) - parseFloat(tolerance_minus);
			/* console.log('qc_check_tol_plus : '+ qc_check_tol_plus)
			 console.log('qc_check_tol_minus : '+ qc_check_tol_minus)
			 console.log('entered_value : '+ entered_value)*/

			 if(entered_value == "" ||  qc_check_tol_plus  < entered_value || qc_check_tol_minus > entered_value){
				qc_fail +=parseFloat(1);
			 }
		 }else{
			if(entered_value != param_value){
				qc_fail +=parseFloat(1);
			}
		 }
	});
	
	var process_type = $("#status_"+status_id).val();
	if(qc_fail > 0){
		if(process_type == 3){
			qc_reprocess +=parseFloat(1);
			qc_fail -=parseFloat(1);
		}
	}

	if(qc_fail > 0){
		status='<span class="label label-danger">QC Fail</span>';
		// $('#status_'+status_id).prop('selectedIndex',0);
		$('#status_'+status_id).removeClass('hide').addClass('show');
		$('#item_status_'+status_id).removeClass('qc-pass');
		$('#item_status_'+status_id).removeClass('qc-reprocess');
		$('#item_status_'+status_id).addClass('qc-failed');

	}else if(qc_reprocess > 0){
		status='<span class="label label-danger">QC Fail</span>';
		$('#status_'+status_id).removeClass('hide').addClass('show');
		$('#item_status_'+status_id).removeClass('qc-pass');
		$('#item_status_'+status_id).removeClass('qc-failed');
		$('#item_status_'+status_id).addClass('qc-reprocess');
	}
	else{
		$('#status_'+status_id).removeClass('show').addClass('hide');
		status='<span class="label label-success">QC Pass</span>';
		$('#item_status_'+status_id).removeClass('qc-reprocess');
		$('#item_status_'+status_id).removeClass('qc-failed').addClass('qc-pass');
	}
	$('#item_status_'+status_id).html(status);
	// $('#status_'+status_id).attr('data-status_id', obj.item_status_id);

	// $('#item_accepted_qty').val(obj.accepted);
	// $('#item_rejected_qty').val(obj.rejected);
	// $('#item_reprocess_qty').val(obj.reprocessed);

	var qc_qty_for_one = total_pending_qty / qc_qty;
	var qc_pass =  $('.qc-pass').length;
	var qc_failed =  $('.qc-failed').length;
	var qc_reprocess =  $('.qc-reprocess').length;

	if(qc_qty < 1){
		qc_qty_for_one = qc_qty;
	}
	if(qc_qty == 1){
		qc_qty_for_one = total_pending_qty;
	}
	/*console.log(total_pending_qty);
	console.log(qc_qty);
	console.log(qc_qty_for_one);
	console.log(qc_pass);
*/
	$('#item_accepted_qty').val((qc_pass * qc_qty_for_one));
	$('#item_rejected_qty').val((qc_failed * qc_qty_for_one));
	// $('#item_reprocess_qty').val(obj.reprocessed);

	
});

function calculate_reject_reprocess(status_id){

	var total_pending_qty = $("#total_pending_qty").val();
	var qc_qty = $("#qc_qty").val();
	var qc_fail= 0;
	var qc_reprocess= 0;

	$('input.claculate_status_'+status_id).each(function(e){
		 var param_value = $(this).data('param_value');
		 var entered_value = $(this).val();
		 // console.log('claculate_status_'+param_value)

		 if(entered_value == "" || param_value != entered_value){
		 	qc_fail +=parseFloat(1);
		 }
	});

	var process_type = $("#status_"+status_id).val();
	

	if(qc_fail > 0){
		if(process_type == 3){
			qc_reprocess +=parseFloat(1);
			qc_fail -=parseFloat(1);
		}
	}

	if(qc_fail > 0){
		status='<span class="label label-danger">QC Fail</span>';
		// $('#status_'+status_id).prop('selectedIndex',0);
		$('#status_'+status_id).removeClass('hide').addClass('show');
		$('#item_status_'+status_id).removeClass('qc-pass');
		$('#item_status_'+status_id).removeClass('qc-reprocess');
		$('#item_status_'+status_id).addClass('qc-failed');

	}else if(qc_reprocess > 0){
		status='<span class="label label-danger">QC Fail</span>';
		$('#status_'+status_id).removeClass('hide').addClass('show');
		$('#item_status_'+status_id).removeClass('qc-pass');
		$('#item_status_'+status_id).removeClass('qc-failed');
		$('#item_status_'+status_id).addClass('qc-reprocess');
	}
	else{
		$('#status_'+status_id).removeClass('show').addClass('hide');
		status='<span class="label label-success">QC Pass</span>';
		$('#item_status_'+status_id).removeClass('qc-reprocess');
		$('#item_status_'+status_id).removeClass('qc-failed').addClass('qc-pass');
	}
	$('#item_status_'+status_id).html(status);
	

	var qc_qty_for_one = total_pending_qty / qc_qty;
	var qc_pass =  $('.qc-pass').length;
	var qc_failed =  $('.qc-failed').length;
	var qc_reprocess =  $('.qc-reprocess').length;

	if(qc_qty < 1){
		qc_qty_for_one = qc_qty;
	}
	if(qc_qty == 1){
		qc_qty_for_one = total_pending_qty;
	}
	// console.log(total_pending_qty);
	// console.log(qc_qty);
	// console.log(qc_qty_for_one);
	// console.log(qc_pass);

	$('#item_accepted_qty').val((qc_pass * qc_qty_for_one));
	$('#item_rejected_qty').val((qc_failed * qc_qty_for_one));
	$('#item_reprocess_qty').val((qc_reprocess * qc_qty_for_one));
}



function load_product_unit(product_id){
	Loading();

	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/reprocess_qc_detail/',
		data: { mode : "get_product_unit", product_id:product_id },
		success: function(data){
			$('#new_unit_id').empty().html(data);
			$('#new_unit_id').select2({
				width: "100%"
			})
			Unloading();
		}
	});
}


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
function load_products($po_type = '')
{
	po_type = $("#rejection_pro_type").val();
	$('#new_product_id').select2({
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


function add_reject_product(){
	var reject_qty = $("#qty_reject").val();

	if(reject_qty == '0'){
		toastr.warning("Please check reject qty", "WARNING");
		$("#qty_reject").focus()
		return false;
	}else if($("#new_product_id").val() == ""){
		toastr.warning("Please Select New Product", "WARNING");
		$("#new_product_id").focus()
		return false;
	}else if($("#new_qty").val() == ""){
		toastr.warning("Please Enter Quantity", "WARNING");
		$("#new_qty").focus()
		return false;
	}else if($("#new_unit_id").val() == ""){
		toastr.warning("Please Select Product Unit", "WARNING");
		$("#new_unit_id").focus()
		return false;
	}


	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
		data: {
				mode : "add_new_product",
				edit_id:$("#edit_id").val(),
				product_id:$("#new_product_id").val(),
				qty:$("#new_qty").val(),
				unit_id:$("#new_unit_id").val(),
				batch_id : $("#batch_id").val(),
			},
		success: function(response)
		{
			
				//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
				$("#new_product_id").select2("val","")
				$("#new_qty").val("")
				$("#new_unit_id").select2('val',"")
				$("#edit_id").val('')
				$('#addproduct').val('Add');
				
				Unloading();
				load_new_product_data();
			}
		});

}


function load_new_product_data(reject_qty = ""){
	var qty = 0;
	if(reject_qty !=""){
		qty = reject_qty;
	}else{
		qty = $("#qty_reject").val();	
	}
	

	if(qty > 0){
		$("#new_productdata").show();
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
			data: { mode : "load_new_product_tempoutward"},
			success: function(data){
				//console.log(data);
				$('#new_productdata').html(data);				
				Unloading();
			}		
		});
	}
}


function delete_new_product_data(id)
	{
		var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+purchase_domain+'app/reprocess_qc_detail/',
				data: { mode : "delete_new_product_data",  id : id},
				success: function(response)
				{
					
					if(response.trim() == "1") {
						Unloading();
						load_new_product_data();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	}


	
function toggle_add_reject_product(){
	var qty_reject = Number($('#item_rejected_qty').val());

	if(qty_reject > 0){
		$(".reject_product_add").show();
		$("#new_productdata").show();
		load_products();
		load_new_product_data(qty_reject);
	}else{
		$(".reject_product_add").hide();
		$("#new_productdata").hide();
	}
}
