//var datatable;
$(document).ready(function() {
	  	load_datatable();
	
// validate vendor add form on keyup and submit
 $("#po_add").validate({
	rules: {
		cust_id: {
			required: true			
		},
		po_no: {
			required: true			
		},
		po_date:{
			required : true	
		}
	},
	messages: {
		cust_id: {
			required: "Select Customer"
		},
		po_no: {
			required: "Enter P.O no"
		},
		po_date:{
			required : "Enter P.O date"
		}
	}
}); 
$(document).on("keypress", "form", function(event) { 
    return event.keyCode != 13;
});

});
$("#po_add").on('submit',function(e) {
	if(event.keyCode == 13) {
      event.preventDefault();
      return false;
    }
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#po_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	
	var form_data=new FormData(this);
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+'app/po_vender/',
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
				toastr.success("PO ADDED SUCCESSFULLY", "SUCCESS");
				if (typeof arr.eid != 'undefined')
				{
					window.location=root_domain+'poreceipt/'+arr.eid;
				}
				else
				{
					window.location=root_domain+'po_venderlist';
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
			else if(arr.msg== 'update')
			{	
				toastr.success("BILL UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				if (typeof arr.eid != 'undefined')
				{
					window.location=root_domain+'poreceipt/'+arr.eid;
				}
				else
				{
					window.location=root_domain+'po_venderlist';
				}
			}
			$('#po_add').trigger('reset');	
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
				url: root_domain+'app/po_vender/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response)
					if(response.trim() == "1") {
						toastr.success("PO DELETE SUCCESSFULLY", "SUCCESS");
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
	var id=parseInt($('#fieldcnt').val());
	var t=0;
	var input_amount=(document.getElementsByName('product_amount[]'));
	var cnt=input_amount.length;
	for(var i=1;i<=id;i++)
	{
		if($("#product_qty"+(i)).val()>0)
		{
			var q=$("#product_qty"+(i)).val();
			var rate=$("#product_rate"+(i)).val();
			var disc=$("#product_disc"+(i)).val();
			var a=q*rate;
			var dis=a*disc/100;
			var discount=a-dis;
			$("#product_amount"+(i)).val(parseFloat(discount));
			t=parseInt(t)+parseInt($("#product_amount"+(i)).val());
		}
	}
	var t=get_gtotal($("#formulaid").val());
	return a;
	
}
function get_gtotal(eid)
{	
	var id=parseInt($('#fieldcnt').val());
	var t=0;
	var p=parseInt($('#paking').val());
	var input_amount=(document.getElementsByName('product_amount[]'));
	var cnt=input_amount.length;
	var total=0;
	for(var i=0;i<cnt;i++)
	{	
		var t=input_amount[i].value;
		if(t>0)
			total=parseInt(total)+parseInt(t);
	}
	if(p>0)
	{
		total=total+p;
	}
	$.ajax({
			type: "POST",
			url: root_domain+'app/po_vender/',
			data: { mode : "formulavalue",eid :eid,total : total,paking:p},
			success: function(response)
			{
				//console.log(response);
				$('#showformulatextbox').html(response);
				$("#g_total").val($('#rate').val());			
			}
	});	
	//return a;
	
}
function load_productdetail(val,i) {
	//console.log(val);
	$.ajax({
	type: "POST",
	url: root_domain+'app/einvoice/',
	data: { mode : "load_productdata", product_id : val},
	success: function(data){
				//console.log(data);
				var p_data = JSON.parse(data);
				if(val!=0)
				{
					$('#product_des'+i).val(p_data.product_des);
				}
				else
				{
					$('#product_des'+i).val('');
				}
	}		
	});
}
function add_field()
{
	var id=parseInt($('#fieldcnt').val())+1;
	if($("#product_id"+(id-1)).val()==="")
	{		
		toastr.warning("Plz enter the product name", "ERROR")
		return false;
	}
	if($("#product_qty"+(id-1)).val()==="")
	{		
		toastr.warning("Enter qty", "ERROR")
		return false;
	}
	if($("#product_rate"+(id-1)).val()==="")
	{		
		toastr.warning("Plz enter the rate", "ERROR")
		return false;
	}
	$.ajax({
			type: "POST",
			url: root_domain+'app/po_vender/',
			data: { mode : "fieldadd",eid :id },
			success: function(response)
			{
				//console.log(response);
				$('#product_list').append(response);				
				$('#fieldcnt').val(id);	
				$("#product_id"+id).select2({
					width: '100%'
				});
				return true;
			}
		});
}
function field_remove(id)
{
	$("#fieldtr"+id).html('');
	var t=get_amount();
}
function reload_data()
{
	//datatable.fnReloadAjax();
	load_datatable();
}	
function load_datatable()
{
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
			"sAjaxSource": root_domain+'app/po_vender/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" });
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}
