//var datatable;
$(document).ready(function() {
		load_datatable();
		get_amount();
		alert("jjj");

 //$('.acptqty').on('keyup', function() {
 $('body').on('keyup', '.acptqty', function() {
 	//alert('ff');
    var el = $(this),
        val = Math.max((0, el.val())),
        max = parseInt(el.attr('max'));

    el.val(isNaN(max) ? val : Math.min(max, val));
 });

$('body').on('blur', '.acptqty', function() {
	//alert($(this).val());
	var tot_pro_qty=$("#product_qty").val();
	//alert(tot_pro_qty);
	//currentid= $(this).data("id")-1;
	// if(currentid!=0){
	// 	var maxqty=$('#acptqty'+currentid).attr('max');
	// 	var maxqty_val=$('#acptqty'+currentid).val();
	// 	//alert(maxqty_val);
		
	// 	if(maxqty==tot_pro_qty && maxqty_val==''){
	// 		alert('Please Choose Previous stage');
	// 		$(this).val('');
	// 	}else{
	// 		currentid= $(this).data("id")+1;
	// 	$('#acptqty'+currentid).attr('max',$(this).val());
	// 		// alert('Please Choose Previous stage');
	// 		// $(this).val('');+float($(this).val())
	// 	}
	// }else{
	// 	currentid= $(this).data("id")+1;
	// 	var maxqty=$('#acptqty'+currentid).attr('max');
	// 	alert(maxqty);
	// 	alert($(this).val());
	// 	var add=parseFloat(maxqty)+parseFloat($(this).val());
	// 	$('#acptqty'+currentid).attr('max',add);
	// }
	currentid= $(this).data("id")+1;
		var maxqty=$('#acptqty'+currentid).attr('max');
		//alert(maxqty);
		//alert($(this).val());
		var add=parseFloat(maxqty)+parseFloat($(this).val());
		$('#acptqty'+currentid).attr('max',add);
	
});
//$(".attribute").change(function(){
$('body').on('change', '.attribute', function() {
	var checkbox_this = $(this);
	if( checkbox_this.is(":checked") == true ) {
		currentid= $(this).data("id")+1;
		$('#attribute'+currentid).attr('disabled', false);
		currentid= $(this).data("id")-1;
		$('#attribute'+currentid).attr('disabled', 'disabled');
	}else{
		currentid= $(this).data("id")-1;
	    $('#attribute'+currentid).attr('disabled', false);
	  //  alert(currentid);
	    currentid= $(this).data("id")+1;
		$('#attribute'+currentid).attr('disabled','disabled');
	//	alert(currentid);
	}
});
// validate the comment form when it is submitted        
// validate vendor add form on keyup and submit
$("#sales_order_add").validate({
	rules: {
		sales_order_date: {
			required: true			
		},
		cust_id: {
			required: true
		}
	},
	messages: {
		sales_order_date: {
			required: "Enter date"
		},
		cust_id: {
			required: "Select Customer"
		}
		
	}
}); 

$("#sales_order_stage").validate({
	rules: {
		product_id: {
			required: true			
		},
		
	},
	messages: {
		product_id: {
			required: "Select Product"
		}
	}
}); 
});
// function generate_product_name(){
// 	// alert("hi");

// 	var i = 1;
// 	var c = ' ';
// 	var dynamic_field = $('#dynamic_field').val();

// 	for(i=1; i<=dynamic_field; i++){
// 		var name = $("#field_id"+i).find('option:selected').attr('data-pcode');
// 		if(name != ''){
// 		    var seprator = '';
// 		    if(i!=1){
// 		        seprator = '-';
// 		    }
// 		    c +=seprator+name;
// 		}
// 	}

// 	$("#product_name").val(product_code+c);
	

	
// }
function getstages(prid){
	var sales_order_id=$("#sales_order_id").val();
	$.ajax({
			type: "POST",
			url: root_domain+'app/sales_order/',
			data: { mode : "getstages",  prid : prid,sales_order_id:sales_order_id },
			success: function(response)
			{
				
										
			}
	});	
}
function invoice_submit(){
	$("#save_print").val(1);
	$("#sales_order_add").submit();	
}
$("#sales_order_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#sales_order_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	for (instance in CKEDITOR.instances) 
	{
    	CKEDITOR.instances[instance].updateElement();
	}
	var form_data=new FormData(this);	
	var dynamic_field = $('#dynamic_field').val();
var namesArray = [];

for (i = 1; i <= dynamic_field; i++) {
    var name = $("#field_id" + i).find('option:selected').attr('data-pcode');
    if (name != '') {
        namesArray.push(name);
    }
}
for (var i = 0; i < namesArray.length; i++) {
	form_data.append('names[]', namesArray[i]);
}
	$.ajax({
		cache:false,
		url: root_domain+'app/sales_order/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				Unloading();
				toastr.success("SALES ORDER ADDED SUCCESSFULLY", "SUCCESS");
				
				if ($("#save_print").val() == '1')
				{
					window.location=root_domain+'sales_order_print/'+arr.eid;
				}
				else
				{
					window.location=root_domain+'sales_order_list';
				}
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
				if ($("#save_print").val() == '1')
				{
					window.location=root_domain+'sales_order_print/'+arr.eid;
				}
				else
				{
					window.location=root_domain+'sales_order_list';
				}
			}
			$('#sales_order_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

$("#sales_order_stage").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#sales_order_stage").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	var chkarr=[];
	$('input[type="checkbox"]').each( function () {
		var checkbox_this = $(this);
		 if( checkbox_this.is(":checked") == true ) {
		 	var chekcval=1;
	        }else{
	        var chekcval=0;
	        }
		chkarr.push(chekcval);
	});
	var form_data=new FormData(this);	
	form_data.append('completedstatus',chkarr);
	$.ajax({
		cache:false,
		url: root_domain+'app/sales_order/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				Unloading();
				toastr.success("SALES ORDER Stage ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+'sales_order_list';
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
			}
			$('#sales_order_stage').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
});

function delete_sales_order(id) 
{
	var r= confirm(" Are you want to delete ?");
		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/sales_order/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
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
					url: root_domain+'app/purchase/',
					data: { mode : "getproduct_amount",  product_amount : total ,formulaid:formulaid},
					success: function(response)
					{
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
        // Dimple Panchal : start
	// if tax on total
	var formula =$("#formula_id").val();
	if(formula > 0)
	{
                get_tax_on_total(formula);
                tcs = $("#tcs_total").val();
                total = parseFloat(total) + parseFloat(tcs);
	} else {
                $('#tcs_total').val(0.00);
        }
	// Dimple Panchal : End
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
			url: root_domain+'app/sales_order/',
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
			url: root_domain+'app/sales_order/',
			data: { mode : "load_productdata",eid :val, cust_id:cust_id },
			success: function(response)
			{
				
				var obj =jQuery.parseJSON(response)
				//$('#product_des').val(obj.product_des);				
				/* $('#product_hsn_code').val(obj.product_hsn);				
				$('#product_rate').val(obj.product_sale_rate);	
				$('#unit_id').select2("val",obj.product_base_unit);
				
				if(obj.com_stateid==obj.cust_stateid){
					$('#formulaid').val(obj.intra_tax);
				}else{
					$('#formulaid').val(obj.inter_tax);
				} */
				
				$('#product_hsn_code').val(obj.product_hsn);
				$('#formulaid').val(obj.fom_id);
				$('#product_rate').val(obj.product_sale_rate);
				$('#unit_id').select2("val",obj.product_base_unit);
			}
		});
}

function add_field()
{
	
	if($("#product_id").val()==="")
	{		
		toastr.warning("Select Product Name", "ERROR")
		return false;
	}
	if($("#product_qty").val()==="")
	{		
		toastr.warning("Enter Qty", "ERROR")
		return false;
	}
	/*if($("#sqr_ft").val()==="")
	{		
		toastr.warning("Enter Sqr/Ft", "ERROR")
		return false;
	}*/
	if($("#product_rate").val()==="")
	{		
		toastr.warning("Enter Rate", "ERROR")
		return false;
	}
	
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain+'app/sales_order/',
			data: { mode : "fieldadd",edit_id:$("#edit_id").val(),product_id:$("#product_id").val(),product_des:$("#product_des").val(),product_hsn_code:$("#product_hsn_code").val(),product_qty:$("#product_qty").val(),product_rate:$("#product_rate").val(),unit_id:$("#unit_id").val(),formulaid:$("#formulaid").val(),product_discount:$("#product_discount").val(),discount_per:$("#discount_per").val(),product_amount:$("#product_amount").val(),sales_order_id:$("#eid").val(),taxable_value:$("#taxable_value").val() },
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
				$("#formulaid").val("")
				$("#product_discount").val("")
				$("#discount_per").val("")
				$("#taxable_value").val("")
				$("#product_rate").val('')
				$("#product_amount").val('')
				$("#edit_id").val('')
				$('#addproduct').show();
				$('#addrow').val('Add');
				Unloading();
			   show_data()
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
			"sAjaxSource": root_domain+'app/sales_order/',
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



// $(".attribute").onchange(){
// alert("f");
// });

function show_stage_data(prid)
{
	var sales_order_id=$("#sales_order_id").val();
	var so_id=$("#eid").val();
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+'app/sales_order/',
    //	data: { mode : "load_tempoutward",so_id:so_id},
	//data: { mode : "getstages",so_id:so_id},
	data: { mode : "getstages",  prid : prid,sales_order_id:sales_order_id },
	
	success: function(data){
				$('#sale_productdata').html(data);		
				$('.default-date-picker').datepicker({
						format: 'dd-mm-yyyy',
						autoclose: true
			    });		

				var chkarr=[];
				var i=1;
				var checkedcount_till=0;
				$('input[type="checkbox"]').each( function () {
					
					var checkbox_this = $(this);
					// if(checkedcount>0){
					// 	alert(i);
				 //        	checkedcount=i;
				 //        	//alert(checkedcount);
				 //        	//checkbox_this.attr('disabled', 'disabled');
				 //        	$('#attribute'+checkedcount).attr('disabled', 'disabled');
				 //     }
					if(checkbox_this.is(":checked") == true){
					 	//var chekcval=1;
					 	 checkedcount_till=i;
					 	//alert(checkedcount);
				        }else{
				        //	var chekcval=0;
				        }
				    //    alert(checkedcount);
				        
				        
					//chkarr.push(chekcval);
					i++;
				});
				unchecked_val=checkedcount_till+1;
				var j=1;
				$('input[type="checkbox"]').each(function () {
					if(j>unchecked_val){
						$('#attribute'+j).attr('disabled', 'disabled');
				     }
				     j++;
				});
				unchecked_val=checkedcount_till-1;
				var j=1;
				$('input[type="checkbox"]').each(function () {
					if(j<=unchecked_val){
						$('#attribute'+j).attr('disabled', 'disabled');
				     }
				     j++;
				});
				//$('.attribute').attr('disabled', 'disabled');
				 Unloading();
		}		
		
	});
}

function show_data()
{
	var so_id=$("#eid").val();
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+'app/sales_order/',
	data: { mode : "load_tempoutward",so_id:so_id},
	success: function(data){
				//console.log(data);
				 $('#sale_productdata').html(data);				
				  get_amount()
				 Unloading();
		}		
		
	});
	
}
function get_series_no(){
	$.ajax({
	type: "POST",
	url: root_domain+'app/sales_order/',
	data: { mode : "get_series_no"},
	success: function(resp){
			//console.log(resp);
			$('#invoicetype_id').val(resp);	
			load_sono(resp)	
		}		
	});	
}
function load_sono(id)
{
	$.ajax({
	type: "POST",
	url: root_domain+'app/sales_order/',
	data: { mode : "load_invoiceno", typeid : id},
		success: function(data){
			//console.log(data);
			var no = jQuery.parseJSON(data);
			$('#sales_order_no').val(no.invoiceno);
		}
	});
}

function edit_data(id)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/sales_order/',
			data: { mode : "preedit",  id : id},
			success: function(response)
			{
				console.log(response)
				var data = jQuery.parseJSON(response);
				$("#product_id").select2("val",data.product_id)
				$("#product_hsn_code").val(data.product_hsn_code)
				$("#product_des").val(data.description)
				$("#product_qty").val(data.product_qty)
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
function delete_data(id)
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/sales_order/',
				data: { mode : "delete_data",  eid : id},
				success: function(response)
				{
					console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						show_data();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}
function delete_attch(so_attch_id) {
	var conf = confirm("Are you sure want to Delete ?");
	if(conf){
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/sales_order/',
			data: { mode : "delete_attch", so_attch_id:so_attch_id },
			success: function(response){
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("ATTACHMENT DELETED SUCCESSFULLY", "SUCCESS");
					location.reload();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();
			}
		}); 
	}
}
// Dimple Panchal : start
function get_tax_on_total(formula_id){
    if(formula_id)//tax calculation on total 
        {
                var total= $("#g_total").val();
                var formulaid=$("#formula_id").val();
                $.ajax({
                        type: "POST",
                        async: false,
                        url: root_domain+'app/sales_order/',
                        data: { mode : "get_tax_on_total", total : total ,formulaid:formulaid},
                        success: function(response)
                        {
                                var obj=jQuery.parseJSON(response);
                                $('#tcs_total').val(obj.tax_value);
                        }
                });
        }
}
// Dimple Panchal : end
function load_quotation_details(id){
	//alert(id);
	//Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/sales_order/',
		data: { mode : "load_quotation_details",  id : id},
		success: function(response)
		{
			var data1 = jQuery.parseJSON(response);
			//console.log(data1.cust_data);
			$("#cust_id").html(data1.cust_data);
			$("#cust_id").select2("val",data1.cust_id);
			//$("#cust_id").attr("disabled","disabled");
			show_data();
			//alert(data1.cust_id);
			//Unloading();
		}
	});
}
function open_po_approv_payment(sales_order_id,sales_order_no){
	$('#preview_po_approval_hist_modal').modal('show');
	$('#apprv_po_ref_no').html(sales_order_no);
	$('#ref_ord_id').val(sales_order_id);
	load_po_hist_datatable();
	load_party_po_dtl();
}
function load_po_hist_datatable(){
	var sales_order_id = $('#ref_ord_id').val();
	
	$("#order-po-history-datatable").dataTable({
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
		"aLengthMenu": [[5, 10, 20, -1], [5, 10, 20,"All"]],
		"iDisplayLength": 5,
		"sAjaxSource": root_domain+'app/sales_order/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "load_po_hist_datatable" }, { "name": "sales_order_id", "value": sales_order_id }  );
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
function load_party_po_dtl(){
	var sales_order_id = $('#ref_ord_id').val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/sales_order/',
		data: { mode : "load_party_po_dtl", sales_order_id:sales_order_id },
		success: function(resp){
			//console.log(resp);
			var resp=JSON.parse(resp);
			$('#mod_po_comp_div_sec').html(resp.mod_po_comp_div_sec);
		}		 
	});
}
function add_po_apprv_hist(){
	
	var form_data = {
		mode:"add_po_apprv_hist",
		approve_status:$('#po_approve_status').val(),
		approve_remark:$('#po_approve_remark').val(),
		sales_order_id:$('#ref_ord_id').val()
	};
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+'app/sales_order/',
		data: form_data,
		success: function(response)
		{
			$('#po_approve_status').select2("val","0");
			$('#po_approve_remark').val("");
			load_po_hist_datatable();
			//load_order_confirm_datatable();
			load_datatable();
			Unloading();
		}
	});	
}
