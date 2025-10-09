//var datatable;
$(document).ready(function() {
	load_datatable();
		// validate the comment form when it is submitted        

// validate vendor add form on keyup and submit
$("#invoicepayment_add").validate({
	rules: {
		cust_id: {
			required: true			
		},
		invoice_id: {
			required: true			
		},
		paid_amount: {
			required: true
		},
		pur_acc_id:{
			required: true
		}
	},
	messages: {
		cust_id: {
			required: "Choose Customer"
		},
		invoice_id: {
			required: "Choose invoice number"
		},
		paid_amount: {
			required: "Paid amount required",
			max:"Can't Enter more than Due Amount"
		},
		pur_acc_id:{
			required: "Choose Bank Account"
		}
	
	}
}); 

});
function reload_data()
{
	load_datatable();
}	
function load_datatable()
{
	var date=$("#rep_date").val();
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
					"sEmptyTable": "NO Receipt ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/invoicepaymentreceipt/',
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
$("#invoicepayment_add").on('submit',function(e) {
	var form = this;	
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#invoicepayment_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+'app/invoicepaymentreceipt/',
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
				toastr.success("Payment ADDED SUCCESSFULLY", "SUCCESS");
				if (typeof arr.eid != 'undefined')
				{
					window.location=root_domain+'invoicepaymentreceipt/'+arr.eid;
				}
				else
				{
					window.location=root_domain+'invoicepaymentreceipt_list';
				}
			}
			if(arr.msg == '2') {
				Unloading();
				toastr.success("Payment ADDED SUCCESSFULLY", "SUCCESS");
				if (typeof arr.eid != 'undefined')
				{
					window.location=root_domain+'invoicepaymentreceipt/'+arr.eid;
				}
				else
				{
					window.location=root_domain+'invoice_list';
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
				toastr.success("Payment UPDATED SUCCESSFULLY", "SUCCESS");		
			
				Unloading();
				if (typeof arr.eid != 'undefined')
				{
					window.location=root_domain+'invoicepaymentreceipt/'+arr.eid;
				}
				else
				{
					window.location=root_domain+'invoicepaymentreceipt_list';
				}
			//	toastr.success("SLIDER UPDATED SUCCESSFULLY", "SUCCESS");		
			}
			$('#invoicepayment_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_payment(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/invoicepaymentreceipt/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response)
					if(response.trim() == "1") {
						toastr.success("Payment DELETE SUCCESSFULLY", "SUCCESS");
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
function load_invoicedata(val) 
{
 	var payment_type=$('input[name=payment_type]:Checked').val();
 		$('#paid_amount').attr('max',0);
	$.ajax({
	type: "POST",
	url: root_domain+'app/invoicepaymentreceipt/',
	data: { mode : "load_data", cust_id : val,payment_type:payment_type},
	success: function(data){
			//	console.log(data);
				if(payment_type==1)
				{
				$('#invoice_id').html(data);
				}
				else
				{
					//alert(data);
					$('#due_payment').val(parseInt(data));
				}
	}
	});
}
function load_data(val) {
//	console.log(val);
	$.ajax({
	type: "POST",
	url: root_domain+'app/invoicepaymentreceipt/',
	data: { mode : "load_totaldata", invoice_id : val},
	success: function(data){
//console.log(data);
				var data = JSON.parse(data);
				var due=(data.g_total)-(data.paid_amount);
				$('#due_payment').val(due);
				var payment_type=$('input[name=payment_type]:Checked').val();
				if(payment_type==1)
				{	$('#paid_amount').attr('max',due);
				}
	}
	});
}
 
