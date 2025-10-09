//var datatable;
$(document).ready(function() {
	load_debitnote_datatable();
	
	show_data();	
	// validate vendor add form on keyup and submit
	$("#debitnote_add").validate({
		rules: {
			vender_id: {
				required: true			
			},
			debitnote_no: {
				required: true			
			},
			debitnote_date:{
				required : true	
			}
		},
		messages: {
			vender_id: {
				required: "Select Vendor"
			},
			debitnote_no: {
				required: "Enter P.O no"
			},
			debitnote_date: {
				required : "Enter P.O date"
			}
		}
	}); 
});
$("#debitnote_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#debitnote_add").valid()) {
		return false;
	}
	
	if(parseFloat($('#total').val())<1){
		toastr.warning("Choose at least one Product!!!", "ERROR");
		return false;
	}
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	$('#save').prop("disabled",true);		
	
	var form_data=new FormData(this);
	
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+'app/debitnote/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);	
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("DEBIT NOTE ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+'debitnote_list';
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
			else if(arr.msg== 'update')
			{	
				toastr.success("DEBIT NOTE UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain+'debitnote_list';
				
			}
			$('#save').prop("disabled",false);		
			$('#debitnote_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_debitnote(id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/debitnote/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				console.log(response)
				if(response.trim() == "1") {
					toastr.success("DEBIT NOTE DELETE SUCCESSFULLY", "SUCCESS");
					load_debitnote_datatable();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
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
			var  disc1=disc.toFixed(2);			
			$('#discount_per').val(disc1);
		}
		else if(type=="per")
		{
			disc=((qty*rate)*parseFloat($('#discount_per').val()))/100;	
			var	disc1=disc.toFixed(2);
			$('#product_discount').val(disc1);
			
		}
	}
	else
	{
		$('#product_discount').val('');
		$('#discount_per').val('');
	}
	get_amount();
}
function get_amount()
{	
	var id=parseInt($('#fieldcnt').val())+1;
	if($("#product_qty").val()!="" && $("#product_rate").val()!="")
	{
		var q=$("#product_qty").val();
		var rate=$("#product_rate").val();
		var a=q*rate;
		/*if($("#product_discount").val()!="" )//discount calculation
			{	
			var discount=parseFloat($("#product_discount").val());
			a=a-discount; 
		}*/
		$("#product_amount").val(parseFloat(a));
		$("#taxable_value").val(parseFloat(a));
		if($("#formulaid").val()!="")//tax calculation
		{
			var total=a;
			var formulaid=$("#formulaid").val();
			$.ajax({
				type: "POST",
				url: root_domain+'app/purchase_order/',
				data: { mode : "getproduct_amount",  product_amount : total ,formulaid:formulaid},
				success: function(response)
				{
					//alert(response);
					var obj=jQuery.parseJSON(response);
					//alert(obj.tax_total);
					$('#product_amount').val(obj.total);
					$('#product_amount_tax').val(obj.tax_total_amount);
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
function get_gtotal(eid)
{	
	//alert(eid);
	var id=parseInt($('#fieldcnt').val());
	var t=0;
	var p=Number($('#paking').val());
	var d=Number($('#discount').val());
	var r=Number($('#round_off').val());
	//alert(r);
	var input_amount=(document.getElementsByName('amount[]'));
	var cnt=input_amount.length;
	if(cnt!=0)
	{
		//alert(cnt);
		var total=0;var c_total=0;
		if(total=="")
		{
			total=0;
		}
		for(var i=0;i<cnt;i++)
		{	
			var t=input_amount[i].value;
			if(t>0)
			total=Number(total)+Number(t);
			//alert(t);
		}
		$("#total").val(Number(total).toFixed(2));
		if(p>0)
		{
			total=total+p;
		}
		if(d>0)
		{
			total=Number(total)-Number(d);
		}
		if(r!=0)
		{
			total=Number(total)+r;
		}
	}
	else
	{
		total=0;
	}
	$("#g_total").val(total.toFixed(2));
	
	
}
function load_productdetail(pro_id) {
	var vender_id = $('#vender_id').val();
	if(vender_id==''){
		toastr.warning("Please Select Vender First","ERROR");
		$('#vender_id').select2('focus');
		return false;
	}
	
	
	if(grn_id){
		$.ajax({
			type: "POST",
			url: root_domain+'app/debitnote/',
			data: { mode : "loadpurchase_productdata",product_id :pro_id },
			success: function(response)
			{
				//console.log(response);
				var obj =jQuery.parseJSON(response);
				$('#product_des').val(obj.description);	
				$('#product_qty').attr("placeholder",obj.rejected_qty);
				$('#product_qty').attr("max",obj.rejected_qty);
				$('#product_rate').val(obj.product_rate);	
				$('#unitid').select2("val",obj.unit_id);
				$('#product_discount').val(obj.product_discount);
				$('#discount_per').val(obj.discount_per);
				$('#formulaid').val(obj.formulaid);
				get_amount();	
			}
		});
	}
	else{
		$.ajax({
			type: "POST",
			url: root_domain+'app/debitnote/',
			data: { mode : "load_productdata",eid :pro_id, vender_id : vender_id },
			success: function(response)
			{
				console.log(response);
				
				$("#rate_history").show();
				var obj =jQuery.parseJSON(response)
				//alert(obj.product_purchase_rate);
				$('#product_type').select2("val",obj.product_type);
				//$('#product_des').val(obj.product_des);				
				$('#product_hsn_code').val(obj.product_hsn);				
				$('#product_rate').val(obj.product_purchase_rate);				
				$('#unitid').select2("val",obj.product_base_unit);
				
				//load_last_rate(pro_id,obj.product_purchase_mst_rate);	
			}
		});
	}
	
}
function add_field()
{
	if(!$("#product_id").val()) {		
		toastr.warning("Select Product", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	else if(!$("#product_qty").val() || parseInt($("#product_qty").val())=='0') {		
		toastr.warning("Enter Qty", "ERROR");
		$("#product_qty").focus();
		return false;
	}
	else if(!$("#unitid").val()) {		
		toastr.warning("Select Unit", "ERROR");
		$("#unitid").select2('focus');
		return false;
	}
	else if(!$("#product_rate").val() || parseInt($("#product_rate").val())=='0') {		
		toastr.warning("Enter Rate", "ERROR");
		$("#product_rate").focus();
		return false;
	}
	
	if(parseFloat($("#product_qty").attr('max'))>0){
		if(parseFloat($("#product_qty").val()) > parseFloat($("#product_qty").attr('max'))) {		
			toastr.warning("GRN REJECT QTY Doesn't Matched", "ERROR");
			$("#product_qty").focus();
			return false;
		}
	}
	
	$('#addrow').attr("disabled",true);
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/debitnote/',
		data: { mode : "fieldadd",edit_id:$("#edit_id").val(),grn_id:$("#grn_id").val(),product_id:$("#product_id").val(),product_des:$("#product_des").val(),product_qty:$("#product_qty").val(),product_rate:$("#product_rate").val(),product_disc:$("#product_discount").val(),unit_id:$("#unitid").val(),formulaid:$("#formulaid").val(),product_discount:$("#product_discount").val(),discount_per:$("#discount_per").val(),product_amount:$("#product_amount").val(),taxable_value:$('#taxable_value').val(),sel_tax:$('#sel_tax').val(),debitnote_id:$("#eid").val() },
		success: function(response)
		{
			//console.log(response);
			//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
			$("#grn_id").select2("val","");
			$("#product_id").select2("val","");
			$("#product_id").select2('focus');
			$("#product_des").val("");
			$("#formulaid").val("");
			$("#discount_per").val("");
			$("#product_discount").val("");
			$("#product_qty").val("");
			$("#unit_id").select2('val',"");
			$("#product_rate").val('');
			$("#product_discount").val('');
			$("#sel_tax").val('');
			$("#taxable_value").val('');
			$("#product_amount").val('');
			$("#edit_id").val('');
			$('#addrow').val('Add');
			$('#addrow').attr("disabled",false);
			Unloading();
			show_data();
		}
	});
}

function load_debitnote_datatable()
{
	var data=$('input[name=report]:Checked').val();
	var date=$('#rep_date').val();
	
	datatable = $("#debit-note-table").dataTable({
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
		"sAjaxSource": root_domain+'app/debitnote/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "report", "value": data },{ "name": "date", "value": date });
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
	var eid = $('#eid').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/debitnote/',
		data: { mode : "load_tempoutward", debitnote_id:eid },
		success: function(resp){
			//console.log(resp);
			$('#sale_productdata').html(resp);				
			Unloading();
			get_amount();
		}	
	});
}

function edit_data(id)
{
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/debitnote/',
		data: { mode : "preedit",  id : id },
		success: function(response)
		{
			//console.log(response)
			var data = jQuery.parseJSON(response);
			$("#grn_id").select2("val",data.grn_id);
			//$('#product_id').html(data.producthtml);
			//$('#product_id').append('<option value="'+data.product_id+'">'+data.product_name+'</option>');
			$("#product_id").select2("val",data.product_id);
			$("#product_des").val(data.description);
			$("#product_hsn_code").val(data.product_hsn_code);
			$("#product_qty").val(data.product_qty);
			$("#product_rate").val(data.product_rate);
			$("#product_disc").val(data.product_disc);
			$("#unitid").select2("val",data.unit_id);
			$("#sel_tax").val(data.sel_tax);
			$("#formulaid").val(data.formulaid);
			$("#product_amount").val(data.total);
			$("#product_discount").val(data.product_discount);
			$("#discount_per").val(data.discount_per);
			$("#taxable_value").val(data.product_amount);
			$("#edit_id").val(id);
			$('#addrow').val('Update');
			Unloading();
		}
	});
}
function delete_data(id)
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/debitnote/',
			data: { mode : "delete_data",  eid : id },
			success: function(response)
			{
				console.log(response);
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

function load_ven_grn(vender_id,id){
	//alert(vender_id);
	
	if(vender_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/debitnote/',
			data: { mode : "load_ven_grn", vender_id : vender_id,id:id },
			success: function(response){
				//console.log(response);
				var resp = JSON.parse(response);
				$('#grn_id').html(resp.pro_html);
				$('#grn_id').select2('val',id);
				Unloading();
			}
			
		});
	}
}
function load_grn_data(grn_id){
	if(grn_id){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/debitnote/',
			data: { mode : "load_grn_data", grn_id:grn_id },
			success: function(response){
				//console.log(response);
				var resp = 	JSON.parse(response);
				$('#product_id').html(resp.pro_html);
				$('#product_id').select2('val','');
				Unloading();
			}
		});
	}
}

function load_product_tax(pid,tran_type)
{
	Loading();
	var vendor=$('#vender_id').val();
	
	if(vendor!=''){
		$.ajax({
			type: "POST",
			url: root_domain+'app/purchase_order/',
			data: { mode : "load_product_tax", pid : pid,tran_type:tran_type,vendor:vendor },
			success: function(response)
			{
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
function load_debit_srs_no(){
	$.ajax({
		type: "POST",
		url: root_domain+'app/debitnote/',
		data: { mode : "load_debit_srs_no" },
		success: function(response)
		{
			//console.log(response);
			var obj =jQuery.parseJSON(response);
			$('#debitnote_no').val(obj.debitnote_no);
		}
	});
}
function showtype(producttype){
	//alert(producttype);
	if(producttype== '2'){
		//grn hide
		$('.grn').attr("style","display:none");
	}else{
		//direct hide
		$('.grn').attr("style","display:block");
		$('.grn').attr("style","border: none");
	}
}


