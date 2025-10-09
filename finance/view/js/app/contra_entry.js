//var datatable;
$(document).ready(function() {
	load_purchase_datatable();

	var mode = $('#mode').val();

	if(mode=='Edit')
	{
		currency_change();
	}
	
// validate vendor add form on keyup and submit
 $("#contra_add").validate({
	rules: {
		contra_entry_no: {
			required: true			
		},
		contra_entry_date: {
			required: true			
		}
	},
	messages: {
		contra_entry_no: {
			required: "Select Customer"
		},
		contra_entry_date: {
			required: "Select Date"
		}
	}
}); 
});
$("#contra_add").on('submit',function(e) {
	

	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#contra_add").valid()) {
		return false;	
	}

	// alert($("#cr_amount").val());
	// alert($("#dr_amount").val());return false;
	if(($("#cr_amount").val() == undefined) && ($("#dr_amount").val() == undefined) ){
		toastr.warning("Please insert atleast one entry", "ERROR")
		return false;
	}
	
	if($("#cr_amount").val()!=$("#dr_amount").val()){
		toastr.warning("Amount Not Match", "ERROR")
		return false;
	}
	if ($('#currency_enable').is(":checked"))
	{
		if($("#currency_id").val()==""){
			toastr.warning("Select Currency", "ERROR")
			$("#currency_id").focus();
			return false;
		}
		if($("#currency_rate").val()==""){
			toastr.warning("Enter Currency Rate", "ERROR")
			$("#currency_rate").focus();
			return false;
		}
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data=new FormData(this);
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+finance_root_domain+'app/contra_entry/',
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
				toastr.success("ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+finance_root_domain+'contra_list';
							
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
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain+finance_root_domain+'contra_list';
				
			}
			$('#contra_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function showledger(){
	branch_id = $('#branch_id').val();
	if(!branch_id){
		toastr.warning("Choose Branch!!!", "ERROR");
		$('#branch_id').select2('focus');
		return false;
	}
	$.fn.modal.Constructor.prototype.enforceFocus = function() {};
	$('#modal-add-ledger').modal('show');
	get_opening_balance('0');
	$("#ledger_add_type").val('contra');
	$("#ledger_name").focus();
}

function delete_invoice(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+finance_root_domain+'app/contra_entry/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					//console.log(response)
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

function add_field()
{
	if($("#entry_type").val()==="")
	{		
		toastr.warning("Select Type", "ERROR")
		$("#entry_type").select2('focus')
		return false;
	}
	if($("#ledger_id").val()==="")
	{		
		toastr.warning("Select Ledger", "ERROR")
		$("#ledger_id").focus();
		return false;
	}
	if($("#amount").val()==="")
	{		
		toastr.warning("Enter Amount", "ERROR")
		$("#amount").select2('focus');
		return false;
	}
	var conf_form = new FormData();
	conf_form.append('mode', "fieldadd");
	conf_form.append('edit_id',$("#edit_id").val());
	conf_form.append('entry_type',$("#entry_type").val());
	conf_form.append('ledger_id',$("#ledger_id").val());
	conf_form.append('amount',$("#amount").val());
	conf_form.append('contra_id',$("#contra_id").val());

	$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/contra_entry/',
			data: conf_form,
			contentType: false,
			processData: false,
			/*data: { mode : "fieldadd",},*/
			success: function(response)
			{
				//console.log(response);
				//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
				//$("#entry_type").select2("val","")
				$("#ledger_id").select2('focus')
				$("#ledger_id").select2("val","")
				$("#amount").val("")
				$("#edit_id").val("")
				$('#addproduct').show();
				$('#addrow').val('Add');
				Unloading();
				show_data();
				add_genral_book();
			}
		});
}

function get_symbol(){

	$(".sp_cr").remove();
	$(".currency_icon").html('');

	var symbl = $("#currency_id").find(':selected').attr("data-currency-symbol");
	var textt = " (<i class='"+symbl+"'></i>)"; 
	$(".currency_icon").each(function() {
		$(this).append(textt);		
	});
}

function reload_data()
{
	//datatable.fnReloadAjax();
	load_purchase_datatable();
}	
function load_purchase_datatable()
{
	//var data=$('input[name=report]:Checked').val();
	var date=$('#rep_date').val();
    var branch_id = $('#branch_id').val();
	
	datatable = $("#purchase-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bDestroy": true,
			"bServerSide" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO DATA ADDED YET !"
			},
			"aLengthMenu": [[-1,10, 20, 30, 50], ["All",10, 20, 30, 50]],
			"iDisplayLength": -1,
			"sAjaxSource": root_domain+finance_root_domain+'app/contra_entry/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },{ "name": "date", "value": date },{ "name": "branch_id", "value": branch_id });
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
	var contra_id=$("#contra_id").val();
	//alert(journal_id);
	Loading()
	$.ajax({
	type: "POST",
	url: root_domain+finance_root_domain+'app/contra_entry/',
	data: { mode : "load_tempoutward",contra_id:contra_id},
	success: function(data){
			//console.log(data);
			 $('#sale_productdata').html(data);				 
			 Unloading();
		}		
		
	});
	
}

function edit_data(id)
{
	//alert(id);
	Loading();
			$.ajax({
				type: "POST",
				url: root_domain+finance_root_domain+'app/contra_entry/',
				data: { mode : "preedit",  id : id},
				success: function(response)
				{
					//console.log(response)
					var data = jQuery.parseJSON(response);
					$("#ledger_id").select2("val",data.ledger_id)
					$("#entry_type").select2("val",data.entry_type)
					$("#amount").val(data.amount)
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
				url: root_domain+finance_root_domain+'app/contra_entry/',
				data: { mode : "delete_data",  eid : id },
				success: function(response)
				{
					//console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
							show_data();
							add_genral_book();
							Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}
function add_genral_book(){
	var contra_id=$("#contra_id").val();
	//Loading()
	if(contra_id){
		$.ajax({
		type: "POST",
		url: root_domain+'app/contra_entry/',
		data: { mode : "add_genral_book",contra_id:contra_id},
		success: function(data){
					//console.log(data);
					// $('#sale_productdata').html(data);				
					 // get_amount()
					// Unloading();
			}		
		});
	}
}

function get_series_no(){
	
	$.ajax({
	type: "POST",
	url: root_domain+finance_root_domain+'app/contra_entry/',
	data: { mode : "get_series_no"},
	success: function(resp){
				//console.log(resp);
				$('#invoicetype_id').val(resp);	
				load_pono(resp)	
			}		
	});	
}
function load_pono(id)
{
	
	$.ajax({
	type: "POST",
	url: root_domain+finance_root_domain+'app/contra_entry/',
	data: { mode : "load_invoiceno", typeid : id},
	success: function(data){
				//console.log(data);
				var no = jQuery.parseJSON(data);
				$('#contra_entry_no').val(no.invoiceno);
				
	}
	});
}
