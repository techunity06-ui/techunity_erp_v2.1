//var datatable;
$(document).ready(function() {
	delete_all_journal_entry();
	show_data();
	load_purchase_datatable();

	var mode = $('#mode').val();

	get_data_description($('#gst_nature').val());
	
	if(mode=='Edit')
	{
		currency_change();
	}
	
// validate vendor add form on keyup and submit
 $("#journal_add").validate({
	rules: {
		journal_entry_no: {
			required: true			
		},
		journal_entry_date: {
			required: true			
		}
	},
	messages: {
		journal_entry_no: {
			required: "Select Journal Voucher No"
		},
		journal_entry_date: {
			required: "Enter Date"
		}
	}
}); 
});
$("#journal_add").on('submit',function(e) {

	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#journal_add").valid()) {
		return false;
	}	
	if($("#cr_amount").val()!=$("#dr_amount").val()){
		toastr.warning("Amount Not Match", "ERROR")
		return false;
	}
	var trn_cnt = get_check_trn_entry();
	if(trn_cnt == 0){
		toastr.warning("Please Enter at least one transaction", "ERROR")
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
		url: root_domain+finance_root_domain+'app/journal_entry/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//alert(response);
			//console.log(response);	
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+finance_root_domain+'journal_list';
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
				window.location=root_domain+finance_root_domain+'journal_list';
			}
			$('#journal_add').trigger('reset');	
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
	$("#ledger_add_type").val('journal');
	$("#ledger_name").focus();
}

function get_check_trn_entry(){
	var journal_id = $("#journal_id").val();
	var cnt = "";
	$.ajax({
		async: false,
		type: "POST",
		url: root_domain+finance_root_domain+'app/journal_entry/',
		data: { mode : "get_check_trn_entry",  journal_id : journal_id },
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			cnt = data.trn_count;
		}
	});
	return (cnt);
}
function delete_invoice(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+finance_root_domain+'app/journal_entry/',
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

function add_tds_details(ledgr,amt,tds_per){

	var entry_type = 1;	
	var ledger_id = ledgr;
	var entry_amount = amt;
	//var payment_type = $("#payment_type_reciept_pmt_trn").val();
	var edit_id = $("#edit_id").val();
	var journal_id = $("#journal_id").val();
	var istds = 'yes';
	var tds_per = tds_per;
	Loading(true);
	$.ajax({
			
		type:'POST',
		url: root_domain+finance_root_domain+'app/journal_entry/',
		data: { mode : "fieldadd","entry_type":entry_type,"ledger_id":ledger_id,
		"amount":entry_amount,"edit_id":edit_id,"journal_id":journal_id,"istds":istds,"tds_per":tds_per},
		success:function(response)
		{
			//alert(response);
			//var data = jQuery.parseJSON(response);
			//if(data.msg == '1') {
				//toastr.success("INSERTED SUCCESSFULLY", "SUCCESS");
			//$("#amount").val(Number($("#amount").val()) - Number(entry_amount));	
			$('#ModalAdvancePymentTds').modal('toggle');
			show_data();	
			add_field();		
			Unloading();

			// }			
			// else if(data.msg == '0') {
			// 	toastr.warning("SOMETHING WRONG", "ERROR")
			// 	Unloading();
			// }			
			
		}
	})
	
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
		toastr.warning("Please select Ledger", "ERROR")
		$("#ledger_id").focus();
		return false;
	}
	if($("#amount").val()==="")
	{		
		toastr.warning("Select Amount", "ERROR")
		$("#amount").select2('focus');
		return false;
	}
	if($("#gst_nature").val()==="")
	{		
		toastr.warning("Select GST nature first", "ERROR")
		$("#gst_nature").select2('focus');
		return false;
	}
	var party_form = $("#ledger_id").find('option:selected').attr('data-formgroup');
	var conf_form = new FormData();
	conf_form.append('mode', "fieldadd");
	conf_form.append('edit_id',$("#edit_id").val());
	conf_form.append('entry_type',$("#entry_type").val());
	conf_form.append('ledger_id',$("#ledger_id").val());
	conf_form.append('amount',$("#amount").val());
	conf_form.append('journal_id',$("#journal_id").val());

	var field_count=0;
	$(".fieldcount").each(function() {
		field_count = Number(field_count) + Number(1);
	});
	// if(($("#gst_nature").val() == 94) && (party_form!='expense_form') && (field_count == 0) ){
	// 	toastr.warning("PLEASE SELECT EXPENCE PARTY LEDGER FIRST", "ERROR")
	// 	return false;
	// }

	$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/journal_entry/',
			data: conf_form,
			contentType: false,
			processData: false,
			/*data: { mode : "fieldadd",},*/
			success: function(response)
			{
				//console.log(response);
				if($("#ledger_Tax_type").val() == '9891' && $("#entry_type").val() == 2){
					get_tds_reference_popup($("#ledger_id").val(),$("#amount").val());
				}else if($("#ledger_Tax_type").val() == '9892' && $("#entry_type").val() == 2){
					get_tcs_reference_popup($("#ledger_id").val(),$("#amount").val());
				}

				$("#edit_id").val("");
				$('#addproduct').show();
				$('#addrow').val('Add');
				Unloading();
				show_data();
				add_genral_book();

				var field_c=0;
				$(".fieldcount").each(function() {
					field_c = Number(field_c) + Number(1);
				});

				if(field_c != 0){
					$("#entry_type").select2("val","");
					$("#entry_type").select2('focus');
					$("#ledger_id").select2("val","0");
					$("#amount").val("");
				}
				
				if(field_c == 0){

					if($("#gst_nature").val() == 92){
						get_payment_gov_popup(92);
					}else if($("#gst_nature").val() == 79 || $("#gst_nature").val() == 80){
						var r= confirm(" Do you want to add credit debit details ?");
						if(r){
							get_debit_credit_note_popup($("#gst_nature").val());
						}
					}else if($("#gst_nature").val() == 94){
						var y= confirm(" Do you want to add Expense details ?");
						if(y){
							get_registered_expence_popup(94,'ledger_id','amount');
						}
					}
					showHideLink($("#gst_nature").val());
				}

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
			"sAjaxSource": root_domain+finance_root_domain+'app/journal_entry/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },{ "name": "date", "value": date },
				{ "name": "branch_id", "value": branch_id });
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
	var journal_id=$("#journal_id").val();
	var gst_nature = $("#gst_nature").val();
	//alert(journal_id);
	Loading()
	$.ajax({
	type: "POST",
	url: root_domain+finance_root_domain+'app/journal_entry/',
	data: { mode : "load_tempoutward",journal_id:journal_id,gst_nature:gst_nature},
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
				url: root_domain+finance_root_domain+'app/journal_entry/',
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
					get_bill_by_bill(data.ledger_id);
					Unloading();
				}
			});
}
function delete_data(id)
{
	var gst_nature = $("#gst_nature").val();
	if(gst_nature == 94){
		var r1= confirm("It will delete all Expence entry, Are you sure , you want to delete this ?");
		if(r1){
			delete_all_journal_expence_entry();
		}
		
	}else{
		var r= confirm(" Are you want to delete ?");

			if(r) {
				Loading();
				$.ajax({
					type: "POST",
					url: root_domain+finance_root_domain+'app/journal_entry/',
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
	
}

function delete_all_journal_expence_entry(){
	
		var bill_voucher_type = $("#payment_voucher").val();
		var journal_id = $("#journal_id").val();
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/journal_entry/',
			data: { mode : "delete_all_journal_expence_entry",bill_voucher_type:bill_voucher_type,journal_id:journal_id },
			success: function(response)
			{	
				show_data();
				Unloading();

			}
		});	
}

function delete_all_journal_entry(){
	
		var bill_voucher_type = $("#payment_voucher").val();
		var journal_id = $("#journal_id").val();
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+finance_root_domain+'app/journal_entry/',
			data: { mode : "delete_all_journal_entry",bill_voucher_type:bill_voucher_type,journal_id:journal_id },
			success: function(response)
			{	
				Unloading();						
			}
		});	
}


function add_genral_book(){
	var journal_id=$("#journal_id").val();
	//Loading()
	if(journal_id){
		$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/journal_entry/',
		data: { mode : "add_genral_book",journal_id:journal_id},
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
	url: root_domain+finance_root_domain+'app/journal_entry/',
	data: { mode : "get_series_no"},
	success: function(resp){
				//console.log(resp);
				$('#invoicetype_id').val(resp);	
				load_pono(resp);	
			}		
	});	
}
function load_pono(id)
{
	
	$.ajax({
	type: "POST",
	url: root_domain+finance_root_domain+'app/journal_entry/',
	data: { mode : "load_invoiceno", typeid : id},
	success: function(data){
				//console.log(data);
				var no = jQuery.parseJSON(data);
				$('#journal_entry_no').val(no.invoiceno);
				$('#receipt_no_reference').val(no.invoiceno);
				
	}
	});
}

function get_ledger_details(ledger_id)
{
	if($("#entry_type").val() == 2){
		$.ajax({
			
			type:'POST',
			url: root_domain + finance_root_domain +'app/payment_new/',
			data : { mode:"get_ledger_details",ledger_id:ledger_id },
			success:function(result)
			{
				var obj = JSON.parse(result);
				//check whether it is TDS entry(added by dhruv)
				if((obj.ledger_Tax_type == 9891 || obj.ledger_Tax_type == 9892) && $("#entry_type").val() == 2){
					var gst_nature = $("#gst_nature").val();
					if(gst_nature != '96'){
						toastr.warning("Please select GST Nature as 'NOT APPLICABLE'", "ERROR")
						$('#ledger_id').select2("val","");
					}else{
						$("#ledger_Tax_type").val(obj.ledger_Tax_type);
					}
					
				}else{
					$("#ledger_Tax_type").val('0');
				}
				
			}
		})
	}
	
}
function get_bill_by_bill(ledger)
{
	//alert(ledger);
	$.ajax({

		type:'POST',
		url:root_domain+finance_root_domain+'app/journal_entry/',
		data:{mode:'check_bill_by_bill',ledger:ledger},
		success:function(result)
		{
			//alert(result);
			if(result>0)
			{
				$('.check_bill_adjustment').show();
			}
			else
			{
				$('.check_bill_adjustment').hide();
			}
		}
	})
}
function check_pl_ledger(ledger_id)
{
	//alert(ledger_id);
	$.ajax({

		type:'POST',
		url:root_domain+finance_root_domain+'app/journal_entry/',
		data:{mode:'check_pl_ledger',ledger_id:ledger_id},
		success:function(result)
		{
			//alert(result);
			if(result=='')
			{
				$('.pl_amount').html(result);	
			}
			else
			{
				$('.pl_amount').html('Balance : '+result);
			}
			
		}

	})
}