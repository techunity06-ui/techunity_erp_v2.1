//var datatable;
$(document).ready(function() {
	load_datatable();
	get_amount();

// validate the comment form when it is submitted        
// validate vendor add form on keyup and submit
$("#planning_add").validate({
	rules: {
		planning_date: {
			required: true			
		},
		cust_id: {
			required: true
		}
	},
	messages: {
		planning_date: {
			required: "Enter date"
		},
		cust_id: {
			required: "Select Customer"
		}
		
	}
}); 
});
function submit_estimate()
{
	$("#save_print").val(1)
	//$("#planning_add").submit();
}
$("#planning_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#planning_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	for (instance in CKEDITOR.instances) 
	{
    	CKEDITOR.instances[instance].updateElement();
	}
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+'app/planning/',
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
				toastr.success("PLANNING ORDER ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+'planning_list';
				
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
			else if(arr.msg == 'update')
			{	
				toastr.success("SALES ORDER UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain+'planning_list';
				
			}
			$('#planning_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_planning(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/planning/',
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
					url: root_domain+'app/purchase_order/',
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
			url: root_domain+'app/planning/',
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
			url: root_domain+'app/planning/',
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
			url: root_domain+'app/purchase_order/',
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
			url: root_domain+'app/planning/',
			data: { mode : "fieldadd",edit_id:$("#edit_id").val(),product_type:$("#product_type").val(),product_id:$("#product_id").val(),product_des:$("#product_des").val(),product_hsn_code:$("#product_hsn_code").val(),product_qty:$("#product_qty").val(),unit_id:$("#unit_id").val(),planning_id:$("#eid").val() },
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
			url: root_domain+'app/planning/',
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
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO DATA ADDED YET !",
			},
			"aLengthMenu": [[-1, 10, 20, 30, 50], ["All", 10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/planning/',
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
	var planning_id=$('#eid').val();
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+'app/planning/',
	data: { mode : "load_tempoutward",planning_id:planning_id},
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
	var planning_id=$('#eid').val();
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+'app/planning/',
	data: { mode : "show_bom_product_data",planning_id:planning_id},
	success: function(data){
				//console.log(data);
				 $('#sale_productdata').html(data);				
				 Unloading();
		}		
		
	});
	
}
function show_bom_product_trn_data(planning_trn_id)
{
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+'app/planning/',
	data: { mode : "show_bom_product_trn_data",planning_trn_id:planning_trn_id},
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
function load_bom_data(planning_trn_id)
{
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+'app/planning/',
	data: { mode : "load_bom_data",planning_trn_id:planning_trn_id},
	success: function(data){
				//console.log(data);
			//$('#sale_productdata').html(data);
				show_bom_product_trn_data(planning_trn_id);			
				 Unloading();
		}		
		
	});
	
}

function edit_data(id,table,whereid)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/planning/',
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
				url: root_domain+'app/planning/',
				data: { mode : "delete_data",  eid : id ,table:table,whereid:whereid,planning_id:$("#eid").val() },
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
				url: root_domain+'app/planning/',
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
		url: root_domain+'app/planning/',
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
		url: root_domain+'app/planning/',
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
	
	//alert('hello');
	$.ajax({
		type: "POST",
		url: root_domain+'app/planning/',
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
		url: root_domain+'app/planning/',
		data: { mode : "load_invoiceno", typeid : id },
		success: function(data){
			//alert(data);
			//console.log(data);
			var no = jQuery.parseJSON(data);
			$('#pl_order_no').val(no.invoiceno);
		}
	});
}

function update_trn(num)
{
	var tid=$("#up_trn"+num).val();
	var qid=$("#up_qty"+num).val();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/planning/',
		data: { mode : "update_trn", bom_trn_id : tid,qty:qid },
		success: function(data){
			
		}
	});
}
