//var datatable;
$(document).ready(function() {
		load_datatable();
		//get_amount();
		show_data();
	//	get_all_complain($('#cust_id').val(),$('#comp_id_hid').val());

// validate the comment form when it is submitted        
// validate vendor add form on keyup and submit
$("#estimate_add").validate({
	rules: {
		expense_date: {
			required: true			
		},
		accountid:{
			required:true
		},
		expense_amount:{
			required: true
		},
		e_status:
		{
			required: true
		}
		
	},
	messages: {
		expense_date: {
			required: "Enter Expense Date"
		},
		accountid:{
			required:"select Expense Type",
		},
		expense_amount:{
			required: "Enter Paid Amount"
		},
		e_status:{
			required: "Select Status"
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
	$('#save').prop("disabled",true);
	$('#saveprint').prop("disabled",true);
	
	var token=  $("#token").val();	
	
	var form_data=new FormData(this);
	form_data.append('file', $('#file').prop('files')[0]);
	
	$.ajax({
		cache:false,
		url: root_domain+'app/expense/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				
				toastr.success("EXPENSE ADDED SUCCESSFULLY", "SUCCESS");
				$("#estimate_add")[0].reset();
				$("#accountid").select2("val","");
				$("#formulaid").select2("val","");
				$("#cust_id").select2("val","");
				$("#comp_id").select2("val","");
				$("#e_status").select2("val","");
				Unloading();
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
				toastr.success("EXPENSE UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				if ($("#save_new").val() == '1')
				{	
					window.location=root_domain+'expense-entry';
				}
				else
				{
					window.location=root_domain+'expense_detail';
				}
			}	
			$('#save').prop("disabled",false);
			$('#saveprint').prop("disabled",false);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_expense(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/expense/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response)
					if(response.trim() == "1") {
						toastr.success("EXPENSE DELETE SUCCESSFULLY", "SUCCESS");
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
		//alert('hiii');
		//var id=parseInt($('#fieldcnt').val())+1;
		//var tax_type=$('input[name=tax_inclusive]:checked').val();
		if($("#expense_amount").val()!="")
		{
			
			var amount=parseFloat($("#expense_amount").val()).toFixed(2);
			//$("#expense_gtotal").val((amount));
			//alert($("#formulaid").val());
			if($("#formulaid").val()!="")//tax calculation
			{
				$.ajax({
					type: "POST",
					url: root_domain+'app/expense/',
					data: { mode : "getproduct_amount",  amount :amount,formulaid:$("#formulaid").val()},
					success: function(response)
					{
						
						var obj=jQuery.parseJSON(response);
						//alert(obj.tax_total);
						$("#paid_amount").val(obj.total);
						$("#tax_amt").val(obj.tax_total);
						
					}
				});
			}
		}
		else
		{
			$("#paid_amount").val(0);
		}
		//get_gtotal();
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
	total=parseFloat(total).toFixed(2);
	$("#g_total").val(total);
	//$("#paid_amount").attr('max',total);
	$("#paid_amount").val(total);
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
			url: root_domain+'app/expense/',
			data: { mode : "change_tax_type",eid:$('#estimate_add #eid').val(),tax_type:tax_type },
			success: function(response)
			{
				//console.log(response);
				show_data();
				Unloading();	
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
	if($("#expense_amount").val()==="")
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
			url: root_domain+'app/expense/',
			data: { mode : "fieldadd",edit_id:$("#edit_id").val(),accountid:$("#accountid").val(),expense_amount:$("#expense_amount").val(),formulaid:$("#formulaid").val(),emp_transfer:$("#emp_transfer").val(),expense_notes:$("#expense_notes").val(),expense_gtotal:$("#expense_gtotal").val(),expenseid:$("#eid").val(),tax_type:tax_type },
			success: function(response)
			{
				console.log(response);
				//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
				$("#accountid").select2("val","")
				$("#expense_amount").val("")
				$("#expense_notes").val("")
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
	url: root_domain+'app/expense/',
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
			"sAjaxSource": root_domain+'app/expense/',
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
			url: root_domain+'app/expense/',
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
			url: root_domain+'app/expense/',
			data: { mode : "load_tempoutward",expenseid:$("#eid").val() },
			success: function(data){
					//console.log(data);
					$('#sale_productdata').html(data);				
					//get_amount();
					Unloading();
			}		
		
	});	
}

function edit_data(id,table,whereid)
{
	Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/expense/',
				data: { mode : "preedit",  id : id ,table:table,whereid:whereid},
				success: function(response)
				{
					console.log(response)
					var data = jQuery.parseJSON(response);
					$("#accountid").select2("val",data.account_mst_id)
					$("#expense_amount").val(data.expense_amount)
					$("#formulaid").val(data.formulaid)
					$("#expense_notes").val(data.expense_notes)
					$("#expense_gtotal").val(data.total)
					$("#emp_transfer").val(data.emp_transfer)
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
				url: root_domain+'app/expense/',
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
function get_cash_opening_bal(acc_id,amt_text,amt_err)
{
	$('.amtbalance').css('display','none');
	if(acc_id==1)
	{
		Loading();
		editReq = $.ajax({
			type: "POST",
			url: root_domain+'app/purchasepayment/',
			data: { mode : "get_opn_bal", acc_id :'0' },
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
}

function get_all_complain(customer,complain_id)
{
	//alert(complain_id);
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/expense_detail/',
			data: { mode : "get_complain", customer :customer,complain_id:complain_id },
			success: function(response)
			{
				//alert(response);
				$('#comp_id').html(response);
				$('#comp_id').select2("val",complain_id);
				//alert(response);
				Unloading();
			}
		});	
}

function get_cust_by_comp(comp_id)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/expense_detail/',
		data: { mode : "get_cust_by_comp", comp_id :comp_id },
		success: function(response)
		{
			var resp = JSON.parse(response);
			$('#cust_id').select2("val",resp.cust_id);
			Unloading();
		}
	});	
}
