//var datatable;
$(document).ready(function() {
		load_datatable();
		//get_amount();
// validate the comment form when it is submitted        
// validate vendor add form on keyup and submit
$("#estimate_add").validate({
	rules: {
		income_date: {
			required: true			
		},
		paymentmodeid:{
			required:true
		},
		paid_amount:{
			required: true
		}
		
	},
	messages: {
		income_date: {
			required: "Enter Income Date"
		},
		paymentmodeid:{
			required:"select Payment MOde"
		},
		paid_amount:{
			required: "Enter Paid Amount"
		}
		
	}
}); 
});
function submit_estimate()
{
	$("#save_new").val(1)
	//$("#estimate_add").submit();
}
$("#estimate_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#estimate_add").valid()) {
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
	$.ajax({
		cache:false,
		url: root_domain+'app/income/',
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
				toastr.success("INCOME ADDED SUCCESSFULLY", "SUCCESS");
				if ($("#save_new").val() != '1')
				{
					window.location=root_domain+'income_list';
				}
				location.reload();
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
				toastr.success("INCOME UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				if ($("#save_new").val() == '1')
				{	
					window.location=root_domain+'income-entry';
				}
				else
				{
					window.location=root_domain+'income_list';
				}
			}
				
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_income(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/income/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response)
					if(response.trim() == "1") {
						toastr.success("INCOME DELETE SUCCESSFULLY", "SUCCESS");
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

function get_amount()
{	
		var id=parseInt($('#fieldcnt').val())+1;
		var tax_type=$('input[name=tax_inclusive]:checked').val();
		if($("#income_amount").val()!="")
		{
			/*if(!$('input[name=tax_inclusive]:checked' ).val())
			{
				toastr.warning("Enter Amount or Select Amount Option", "ERROR");
				return false;
			}*/
			var amount=parseFloat($("#income_amount").val()).toFixed(2);
			$("#income_gtotal").val((amount));
			if($("#formulaid").val()!="")//tax calculation
			{
				$.ajax({
					type: "POST",
					url: root_domain+'app/income/',
					data: { mode : "getproduct_amount",  amount :amount,formulaid:$("#formulaid").val()},
					success: function(response)
					{
						var obj=jQuery.parseJSON(response);
						$("#income_gtotal").val(obj.total);
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
function get_gtotal()
{	
	//var id=parseInt($('#fieldcnt').val());
	var t=0;
	//var d=parseInt($('#discount').val());
	var input_amount=(document.getElementsByName('amount[]'));
	var cnt=input_amount.length;
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
	$("#g_total").val(parseFloat(total));
	$("#paid_amount").attr('max',parseFloat(total));
}
function load_productdetail(val,i) 
{
	if(val!=0){
		$('#addproduct').hide();
	}
	else{
		$('#addproduct').show();
	}
	$.ajax({
			type: "POST",
			url: root_domain+'app/income/',
			data: { mode : "load_productdata",eid :val },
			success: function(response)
			{
				//console.log(response);
				var obj =jQuery.parseJSON(response)
				$('#product_des').val(obj.product_des);		
				$('#product_rate').val(obj.product_mst_rate);			
				$('#unit_id').select2("val",obj.product_mst_unit_id);	
					
			}
		});
}

function add_field()
{
	
	if($("#accountid").val()==="")
	{		
		toastr.warning("Select Account", "ERROR")
		return false;
	}
	if($("#income_amount").val()==="")
	{		
		toastr.warning("Enter Amount", "ERROR")
		return false;
	}
	/*if($("#formulaid").val()==="")
	{		
		toastr.warning("Select Tax", "ERROR")
		return false;
	}*/
	Loading();	
	var tax_type=$('input[name=tax_inclusive]:checked').val();	
	$.ajax({
			type: "POST",
			url: root_domain+'app/income/',
			data: { mode : "fieldadd",edit_id:$("#edit_id").val(),accountid:$("#accountid").val(),income_amount:$("#income_amount").val(),formulaid:$("#formulaid").val(),income_notes:$("#income_notes").val(),income_gtotal:$("#income_gtotal").val(),incomeid:$("#eid").val(),tax_type:tax_type },
			success: function(response)
			{
				console.log(response);
				//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
				$("#accountid").select2("val","")
				$("#income_amount").val("")
				$("#income_notes").val("")
				//$("#formulaid").select2('val',"")
				$("#edit_id").val('')
				Unloading();
				show_data()
			}
		});
}
function load_paymentmode(val) {
	$.ajax({
	type: "POST",
	url: root_domain+'app/income/',
	data: { mode : "paymentmode", paymentmodeid : val},
	success: function(response){
				//console.log(response);
				$('#product_list').append(response);
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
	var data=$('#payment_status').val();
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
			"aLengthMenu": [[-1, 10, 20, 30, 50], ["All", 10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/income/',
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
function load_estimateno(id)
{
	$.ajax({
			type: "POST",
			url: root_domain+'app/income/',
			data: { mode : "load_estimateno", typeid : id},
			success: function(data){
					//console.log(data);
					var no = jQuery.parseJSON(data);
					$('#estimate_no').val(no.estimateno);
			}
	});
}

function show_data()
{
	Loading()
	$.ajax({
			type: "POST",
			url: root_domain+'app/income/',
			data: { mode : "load_tempoutward",incomeid:$("#eid").val() },
			success: function(data){
					//console.log(data);
					$('#sale_productdata').html(data);				
					get_amount();
					Unloading();
			}		
		
	});	
}

function edit_data(id,table,whereid)
{
	Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/income/',
				data: { mode : "preedit",  id : id ,table:table,whereid:whereid},
				success: function(response)
				{
					console.log(response)
					var data = jQuery.parseJSON(response);
					$("#accountid").select2("val",data.account_mst_id)
					$("#income_amount").val(data.income_amount)
					$("#formulaid").val(data.formulaid)
					$("#income_notes").val(data.income_notes)
					$("#income_gtotal").val(data.total)
					$("#edit_id").val(id)
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
				url: root_domain+'app/income/',
				data: { mode : "delete_data",  eid : id ,table:table,whereid:whereid,estimate_id:$("#eid").val() },
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
/**Payment Function***/
function get_chequeno(acc_id,refcontroll)
{
	if($("#paymentmodeid").val()==2)
	{
		Loading();
		editReq = $.ajax({
			type: "POST",
			url: root_domain+'app/purchasepayment/',
			data: { mode : "get_chequeno", acc_id :acc_id },
			success: function(response)
			{
				//console.log(response);
				response=response.trim();
				if(response!="")
				{
				$('#'+refcontroll).val(parseInt(response)+parseInt(1));
				}
				Unloading();
			}
		});	
	}
}
 function get_opening_bal(acc_id,amt_text,amt_err)
{
		Loading();
	
		editReq = $.ajax({
			type: "POST",
			url: root_domain+'app/purchasepayment/',
			data: { mode : "get_opn_bal", acc_id :acc_id },
			success: function(response)
			{
				//console.log(response);
				response=response.trim();
				$('.amtbalance').css('display','');
				$('#'+amt_text).val(response);
				$('#'+amt_err).html('Balance '+response);
				Unloading();
			}
		});	
}
function change_tax_type() 
{
	Loading();
	var tax_type=$('input[name=tax_inclusive]:checked').val();
	//var emode=$('#estimate_add #mode').val();
	var eid=$('#estimate_add #eid').val();
	console.log(eid);
	$.ajax({
			type: "POST",
			url: root_domain+'app/income/',
			data: { mode : "change_tax_type",eid:$('#estimate_add #eid').val(),tax_type:tax_type },
			success: function(response)
			{
				//console.log(response);
				show_data();
				Unloading();	
			}
		});
}