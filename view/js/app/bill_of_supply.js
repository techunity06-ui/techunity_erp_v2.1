//var datatable;
$(document).ready(function() {
	load_datatable();
	show_data(); 
	
	// validate vendor add form on keyup and submit
	$("#bill_of_supply_add").validate({
		rules: {
			invoicetype_id:{
				required: true			
			},
			bill_of_supply_date: {
				required: true			
			},
			cust_id: {
				required: true
			}
		},
		messages: {
			invoicetype_id:{
				required: "Select Type"			
			},
			bill_of_supply_date: {
				required: "Enter date"
			},
			cust_id: {
				required: "Select Customer"
			}
			
		}
	}); 
});

function bos_submit()
{
	$("#save_print").val(1);
	$("#bill_of_supply_add").submit();	
}
$("#bill_of_supply_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#bill_of_supply_add").valid()) {
		return false;
	}
	/*else if(parseInt($('#total').val())<=0) {
		toastr.warning("AT LEAST ONE PRODUCT REQUIRE", "ERROR")
		return false;
	}*/
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+'app/bill_of_supply/',
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
				toastr.success("BILL OF SUPPLY ADDED SUCCESSFULLY", "SUCCESS");
				if ($("#save_print").val() == '1'){
					window.location=root_domain+'bill_of_supply_print/'+arr.eid+'/'+arr.printstatus;
				}
				else{
					window.location=root_domain+'bill_of_supply_list';
				}
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(arr.msg == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			else if(arr.msg == 'update') {	
				toastr.success("BILL OF SUPPLY UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				if ($("#save_print").val() == '1') {	
					window.location=root_domain+'bill_of_supply_print/'+arr.eid+'/'+arr.printstatus;
				}
				else {
					window.location=root_domain+'bill_of_supply_list';
				}		
			}
			$('#bill_of_supply_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_invoice(id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/bill_of_supply/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				console.log(response);
				if(response.trim() == "1") {
					toastr.success("BILL OF SUPPLY DELETE SUCCESSFULLY", "SUCCESS");
					load_datatable();
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
	document.getElementById('bill_value').value="";
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
			//alert('hi');
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
	//alert(id);
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
		var bill_value=$('#product_amount').val();
		if($("#formulaid").val()!="")//tax calculation
		{
			var total=a;
			var formulaid=$("#formulaid").val();
			//alert(formulaid);
			$.ajax({
				type: "POST",
				url: root_domain+'app/bill_of_supply/',
				data: { mode : "get_product_amount",  product_amount : bill_value ,formulaid:formulaid},
				success: function(response)
				{
					//alert(response);
					var obj=jQuery.parseJSON(response);
					$('#product_amount').val(obj.total);
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
		total=parseFloat(total)+parseFloat(t);
		
	}
	$("#total").val(parseFloat(total));
	
	var p=$("#packing").val();
	if(p>0)
	{
		total=parseFloat(total)+parseFloat(p);
	}
	var f=$("#freight").val();
	if(f>0)
	{
		total=parseFloat(total)+parseFloat(f);
	}
	var c=$("#cutting").val();
	if(c>0)
	{
		total=parseFloat(total)+parseFloat(c);
	}
	/*
		var d=$("#discount_amt").val();
		if(d>0)
		{
		c_total=parseFloat(c_total)-parseFloat(d);
		}
		var r=$("#round_off").val();
		if(r!=0)
		{
		c_total=parseFloat(c_total)+parseFloat(r);
	}*/
	
	g_total=total.toFixed(2);
	$("#g_total").val(g_total);
	/*$.ajax({
		type: "POST",
		url: root_domain+'app/bill_of_supply/',
		data: { mode : "formulavalue",eid :id,total : g_total, c_total:c_total},
		success: function(response)
		{
		//console.log(response);
		$('#showformulatextbox').html(response);
		g_total=Math.round($('#rate').val());
		//g_total=(g_total);
		$("#g_total").val(g_total);
		}
	});*/
	
}
function load_productdetail(val,i) {
	
	if(val!=0)
	{
		$('#addproduct').hide();
	}
	else
	{
		$('#addproduct').show();
	}
	var cust_id = $('#cust_id').val();
	if(cust_id==''){
		toastr.warning("Please Select Customer First","ERROR");
		$('#cust_id').select2('focus');
		return false;
	}
	$.ajax({
		type: "POST",
		url: root_domain+'app/bill_of_supply/',
		data: { mode : "load_productdata",eid :val, cust_id:cust_id },
		success: function(response)
		{
			//console.log(response);
			
			var obj =jQuery.parseJSON(response)
			//$('#product_rate').val(obj.product_mst_rate);	
			$('#product_hsn_code').val(obj.product_hsn_code);
			$('#product_rate').val(obj.product_rate);
			$('#unit_id').select2("val",obj.unitid);;
			// Load last customer rate function	
		}
	});
	//Load Stock Function 
	//load_stock_qty(val,0);
}

function load_stock_qty(product_id,old_qty){
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+'app/bill_of_supply/',
		data: { mode : "load_stock_qty", product_id:product_id },
		success: function(data){
			//console.log(data);
			$("#product_qty").attr("placeholder",data);
			$("#product_qty").attr("max",parseFloat(old_qty)+parseFloat(data));
			Unloading();
		}		
	});
}
function add_field()
{
	if(!$("#product_id").val()){		
		toastr.warning("Select Product Name", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	else if(!$("#product_qty").val()){		
		toastr.warning("Enter Qty", "ERROR");
		return false;
	}
	/*else if(!$("#product_rate").val() || parseFloat($("#product_rate").val())=='0'){		
		toastr.warning("Enter Rate", "ERROR");
		return false;
	}*/
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+'app/bill_of_supply/',
		data: { mode : "fieldadd",edit_id:$("#edit_id").val(),product_id:$("#product_id").val(),product_hsn_code:$("#product_hsn_code").val(),product_des:$("#product_des").val(),product_qty:$("#product_qty").val(),product_rate:$("#product_rate").val(),unit_id:$("#unit_id").val(),formulaid:$("#formulaid").val(),product_discount:$("#product_discount").val(),discount_per:$("#discount_per").val(),taxable_value:$('#taxable_value').val(),product_amount:$("#product_amount").val(),bill_of_supply_id:$("#eid").val() },
		success: function(response)
		{
			console.log(response);
			//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
			$("#product_id").select2("val","");
			$("#product_id").select2('focus');
			$("#product_des").val("");
			$("#product_hsn_code").val("");
			$("#formulaid").val("");
			$("#product_discount").val("");
			$("#discount_per").val("");
			$("#taxable_value").val("");
			$("#product_qty").val("");
			$("#product_qty").attr("max","").attr("placeholder","");
			$("#unit_id").select2('val',"");
			$("#product_rate").val('');
			$("#product_disc").val('');
			$("#product_amount").val('');
			$("#edit_id").val('');
			$('#addproduct').show();
			$('#addrow').val('Add');
			Unloading();
			show_data();
		}
	});
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
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+'app/bill_of_supply/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "report", "value": data },{ "name": "type_id", "value": type },{ "name": "date", "value": date } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}
function load_invoiceno()
{
	$.ajax({
		type: "POST",
		url: root_domain+'app/bill_of_supply/',
		data: { mode : "load_invoiceno" },
		success: function(data){
			//console.log(data);
			var no = jQuery.parseJSON(data);
			$('#bill_of_supply_no').val(no.invoiceno);
			
		}
	});
}

function show_data()
{
	var eid = $('#eid').val();
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain+'app/bill_of_supply/',
		data: { mode : "load_tempoutward", eid:eid },
		success: function(data){
			//alert(data);
			//console.log(data);
			$('#sale_productdata').html(data);				
			get_amount()
			Unloading();
		}
	});
}

function edit_data(id)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/bill_of_supply/',
		data: { mode : "preedit",  id : id},
		success: function(response)
		{
			
			//console.log(response);
			//alert(response);
			var data = jQuery.parseJSON(response);
			//alert(data.model_id);
			//$('#product_id').append('<option value="'+data.product_id+'">'+data.product_name+'</option>');
			$("#product_id").select2("val",data.product_id);
			//var load_product=load_prowise_model(data.product_id,data.model_id);
			//$("#model_id").html(load_produc);
			//$("#model_id").select2("val",data.model_id);
			$("#product_hsn_code").val(data.product_hsn_code);
			//Load Product STOCK
			//load_stock_qty(data.product_id,data.product_qty);
			
			$("#product_des").val(data.description);
			$("#product_qty").val(data.product_qty);
			$("#product_rate").val(data.product_rate);
			$("#product_disc").val(data.product_disc)
			$("#unit_id").select2("val",data.unit_id);
			$("#formulaid").val(data.formulaid);
			$("#product_amount").val(data.total)
			$("#product_discount").val(data.product_discount)
			$("#discount_per").val(data.discount_per)
			$("#taxable_value").val(data.taxable_value)
			$("#bill_value").val(data.bill_value)
			$("#product_amount").val(data.product_amount)
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
			url: root_domain+'app/bill_of_supply/',
			data: { mode : "delete_data",  eid : id ,bill_of_supply_id:$("#eid").val() },
			success: function(response)
			{
				console.log(response)
				var data=jQuery.parseJSON(response)
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
function load_qty(product_id,old_qty)
{
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/bill_of_supply/',
		data: { mode : "load_qty",product_id:product_id},
		success: function(resp){
			//console.log(resp);
			if(resp!=""){
				$('#product_qty').attr("placeholder",resp);
				//$('#product_qty').attr("max",resp);
				$("#product_qty").attr("max",parseFloat(old_qty)+parseFloat(resp));
			}
			Unloading();
		}		
	});
}

function load_product_typeiwse(type_id){
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/bill_of_supply/',
		data: { mode : "load_product_typeiwse", type_id : type_id},
		success: function(data){
			//console.log(data);
			$('#product_id').html(data);				
			Unloading();
		}
	});
}

function copy_comp_spare_trn_data(complaint_id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/bill_of_supply/',
		data: { mode:"copy_comp_spare_trn_data", complaint_id:complaint_id },
		success: function(response){
			//console.log(response); 
			Unloading();
			show_data();
		}
	});
}