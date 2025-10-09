$(document).ready(function() {
	
	$('#ledger_name').focus();
	show_led_attach_data();
	/*$("input").keypress(function(e) {
	  if (e.which == 13) {
		var index = $("input").index(this);
		$("input").eq(index + 1).focus();
		e.preventDefault();
	  }
	});
	
	$(".select2-selection").keypress(function(e) {
		//alert('hiii');
	  if (e.which == 13) {
		
		$(".select2-selection").select2('close');
		$(".select2-selection").click();
		$(".select2-selection").eq(index + 1).focus();
		e.preventDefault();
		
	  }
	  
	});
	
	$("select").keypress(function(e) {
	  if (e.which == 13) {
		var index = $("select").index(this);
		$("select").eq(index + 1).focus();
		e.preventDefault();
	  }
	}); */
	
	delete_ledger_popup_data();
	
	load_ledger_datatable();
	// validate vendor add form on keyup and submit
	show_bank_data();
	show_contact_data();
	show_tran_data();
	

	//Ledger group on load enable disable
	ledger_grp_change_Tax_type();
	ledger_grp_change_fix_assets();
	ledger_chequebank_change();
	ledger_monthly_budget_change();
	ledger_tcs_tds_change();
	load_billbybill_datatable();
	ledger_grp_change();
	changeGstField();
	

	//Monthly budget link show hide
	if($('#enable_monthly_budget').val()=='yes'){
		$("#checkMonthlyLink").show();
	}else{
		$("#checkMonthlyLink").hide();	
	}

	//Check deposite link show hide
	if($('#enable_cheque_deposit').val()=='yes'){
		$('#checkChequeDepositLink').show();
	}else{
		$('#checkChequeDepositLink').hide();
	}

	//Multi currency link show hide
	if($('#multi_currency').val()=='yes'){
		$("#checkMultiCurrLink").show();
	}else{
		$("#checkMultiCurrLink").hide();
	}

	//Multi branch link show hide
	if($('#multi_branch').val()=='yes'){
		$("#checkBranchLink").show();
	}else{
		$("#checkBranchLink").hide();	
	}
	
	//depreciation link show hide
	if($('#enable_depreciation').val() == 'yes'){
		$("#checkDepreciationLink").show();
	}else{
		$("#checkDepreciationLink").hide();
	}

	//bill sundry link show hide
	if($('#enable_bill_sunfry').val() == 'yes'){
		$("#checkBillSundryLink").show();
	}else{
		$("#checkBillSundryLink").hide();
	}
	
	//salesman link show hide
	if($('#enable_salesman').val() == 'yes'){
		$("#checkSalesmanLink").show();
	}else{
		$("#checkSalesmanLink").hide();
	}

	//Bill by bill opening show hide
	if($('#enable_billbybill_opening').val() == 'yes'){
		$("#checkBillbybillLink").show();
	}else{
		$("#checkBillbybillLink").hide();
	}
	
	//$('.depreciation').hide();
	//$('.monthly_budget').hide();
	//$('.ledgerTaxtype').hide();
	//$('.chequebank').hide();
	//$('.gstApplicable').hide();

	//$('[data-toggle="tooltip"]').tooltip();
	
	
	
	$("#ledger_add").validate({
            rules: {
			company_name: {
				required: true			
			},
			tax_value:{
					required: true,
					number: true,
					min:0,
					max:100
			},
			print_priority: {
				min:0,
				number: true
			},
			balance_typeid:{
				required:true
			},
			
			
			
        },
		messages: {
				company_name: {
						required: "Select Company."
				},
				tax_value: {
						required: "Enter Tax Percentage.",
						number: "Only Number"
				},
				print_priority: {
					number: "Only Number"
				},
				
			}
	
        }); 
	
});

function delete_ledger_popup_data(){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "delete_ledger_popup_data" },
		success: function(response)
		{	
			Unloading();
			//$('#opn_balance').val(response);	
		}
	});
}

function getPanNo(gstno){
	//alert(gstno);
	if(gstno.length == 15){
		var statecode = $('#stateid').find(':selected').data('statecode');
		//alert(statecode);
		var gstNumber = gstno.slice(0, 2);
		//alert(gstNumber);
		if(gstNumber != statecode){
			toastr.warning("Please insert GST no according state you have selected", "ERROR");
			return false;
		}else{
			//alert(gstno.slice(2, 12));
			$('#m_pan').val(gstno.slice(2, 12));
		}
	}
}

function get_opening_balance(op_type){
	
	var mode = $('#mode').val();

	if(op_type==1){
		$(".multiCurrency").show();
		$(".multiBranch").hide();
		$("#multi_currency").val('yes').trigger("change");
		$("#multi_currency").prop('required',true);
		$("#opn_balance").prop('readOnly',true);
	}else if(op_type==2){
		$(".multiBranch").show();
		$(".multiCurrency").hide();
		$("#multi_branch").val('yes').trigger("change");
		$("#multi_branch").prop('required',true);
		$("#opn_balance").prop('readOnly',true);
	}else{
		$(".multiCurrency").hide();
		$(".multiBranch").hide();
		$("#multi_currency").prop('required',false);
		$("#multi_branch").prop('required',false);
		$("#opn_balance").prop('readOnly',false);
	}
	
	if(mode!='Edit')
	{
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/ledger/',
			data: { mode : "get_op_balance",  op_type : op_type },
			success: function(response)
			{	
				Unloading();
				$('#opn_balance').val(response);	
			}
		});	
	}
	
}

// function getMonthlyBudgetPopup(monthlyId){
// 	if(monthlyId =='yes'){
// 		$("#modal-monthly-budget1").modal("show");
// 		$('#checkMonthlyLink').show();		
// 	}else{
// 		$('#checkMonthlyLink').hide();
// 	}
// }

function getMonthlyBudgetPopup(monthlyId){
	
	var ledger_id = $("#ledger_id").val();

	if(monthlyId =='yes'){

		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/ledger/',
			data: { mode : "monthly_budget_popup",  "ledger_id" : ledger_id },
			success: function(response)
			{
				$("#modal-monthly-budget1").modal("show");
				$(".monthly_bud").html(response);	
				$('#checkMonthlyLink').show();	
			}
		});
		
	}else{
		$('#checkMonthlyLink').hide();
	}
}

function getBankChequePopup(chequeDepId){
	if(chequeDepId =='yes'){
		$("#modal-bank-cheque").modal("show");		
		load_bank_cheque_datatable();
		show_bankcheque_total();
		$('#checkChequeDepositLink').show();
	}else{
		$('#checkChequeDepositLink').hide();
	}	
}

function changeAnnualBudget(){
	var temp=0;	
	$(".monthlyDivide").each(function() { 
		if($('.monthlyDivide').val() !==''){
			temp += Number($(this).val());
		}		
	});
	$('#annual_budget').val(temp.toFixed(2));
}

$("#budgetForm").on('submit',function(e) {
	
	
	if(!$('#annual_budget').val())
	{
		toastr.warning("Enter Your Monthly Budget", "ERROR");
		return false;
	}

	var ledger_id = $("#ledger_id").val();
	
	e.preventDefault();
	Loading();
	var form_data=new FormData(this);
	form_data.append( 'ledger_id', ledger_id );
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/ledger/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			if(response == '1'){
				toastr.success("BUDGET ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
			}else if(response == '2'){
				toastr.success("BUDGET UPDATED SUCCESSFULLY", "SUCCESS")
				Unloading();
			}else{
				 toastr.warning("Something went wrong please contact your admin", "ERROR");
                 Unloading();
			}
		}
	});
});

function changeMonthlyBudget(amount){
	var annual_budget = $('#annual_budget').val();
	var monthBud = (parseInt(annual_budget)/parseInt(12)).toFixed(2);
	$(".monthlyDivide").each(function() { 
		$(this).val(monthBud);
	});
}

function getMultiBranchPopup(multiId){

	if(multiId =='yes'){
		$("#modal-multi-branch").modal("show");
		load_multi_branch_datatable();
		$("#checkBranchLink").show();
		//show_multibranch_total();
	}else{
		$("#checkBranchLink").hide();
	}
}

function load_multi_branch_datatable(){

	var ledger_id = $('#ledger_id').val();
	
	//alert(ledger_id);
	
	datatable = $("#multi_branch_table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"bStateSave": false,
        "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('offersDataTables', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('offersDataTables'));
        },
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+administration_domain+'app/ledger/',
		"fnServerParams": function ( aoData2 ) {
			aoData2.push( { "name": "mode", "value": "fetch_multi_branch_table" } );
			aoData2.push( { "name": "edit_ledger_id", "value": ledger_id } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
			show_multibranch_total();
		},
		"fnFooterCallback": function ( oSettings ) {
			show_multibranch_total();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
	
}

function add_bank_cheque_field(){
	if(!$("#cheque_date").val()){		
		toastr.warning("Please Select Date", "ERROR");		
		return false;
	}
	else if(!$("#cheque_account").val()){		
		toastr.warning("Please insert Cheque Account", "ERROR");
		$("#cheque_account").select2('focus');
		return false;
	}
	else if(!$("#cheque_amount").val()){	
		toastr.warning("Please insert Cheque Ammount", "ERROR");	
		$("#cheque_amount").select2('focus');
		return false;
	}
	else if(!$("#cheque_entry_type").val()){	
		toastr.warning("Please Select Entry Type", "ERROR");	
		$("#cheque_entry_type").select2('focus');
		return false;
	}
	

	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "bankchaque_addfield",edit_id:$("#edit_id").val(),cheque_date:$("#cheque_date").val(),
		cheque_voucher_no:$("#cheque_voucher_no").val(),cheque_account:$("#cheque_account").val(),cheque_amount:$("#cheque_amount").val(),
		cheque_transaction_number:$("#cheque_transaction_number").val(),cheque_narration:$("#cheque_narration").val(),
		cheque_entry_type:$("#cheque_entry_type").val(),cheque_pay_mode:$("#cheque_pay_mode").val(),ledger_id:$('#ledger_id').val() },
		success: function(response)
		{
			//alert(response);
			
			if(response == '1'){
				
				$("#cheque_date").val("");
				$("#cheque_voucher_no").val("");
				$("#cheque_account").select2("val","");
				$("#cheque_amount").val("");
				$("#cheque_pay_mode").select2("val","");
				$("#cheque_transaction_number").val("");
				$("#cheque_narration").val("");		
				$("#cheque_entry_type").select2("val","");
				load_bank_cheque_datatable();
				show_bankcheque_total();
				Unloading();
				toastr.success("Cheque details added", "success");
            } else if(response == '2'){
              	
				$("#cheque_date").val("");
				$("#cheque_voucher_no").val("");
				$("#cheque_account").select2("val","");
				$("#cheque_amount").val("");
				$("#cheque_pay_mode").select2("val","");
				$("#cheque_transaction_number").val("");
				$("#cheque_narration").val("");		
				$("#cheque_entry_type").select2("val","");
				$("#edit_id").val("");
				$('#addbank_cheque').val('Add');				
				load_bank_cheque_datatable();
				show_bankcheque_total();
				Unloading();
				toastr.success("Cheque details updated", "success");
            } else if(response == '0'){
                toastr.warning("Something went wrong please contact your admin", "ERROR");
                Unloading();
            }
		    
		}
	});
}

function load_bank_cheque_datatable(){
var ledger_id = $('#ledger_id').val();
//alert(ledger_id);
	datatable = $("#bank_cheque_table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"bStateSave": false,
        "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('offersDataTables', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('offersDataTables'));
        },
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+administration_domain+'app/ledger/',
		"fnServerParams": function ( aoData2 ) {
			aoData2.push( { "name": "mode", "value": "fetch_bank_cheque_table" });
			aoData2.push( { "name": "edit_ledger_id", "value": ledger_id });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');

}

function edit_bank_cheque_field(id)
{
	//alert(id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "preedit_bankcheque",  id : id},
		success: function(response)
		{					
			var data = jQuery.parseJSON(response);
			$("#cheque_date").val(data.cheque_date);

			$("#cheque_voucher_no").val(data.cheque_voucher_no);
			$("#cheque_account").select2("val",data.cheque_account);

			$("#cheque_amount").val(data.cheque_amount);
			$("#cheque_pay_mode").select2("val",data.cheque_pay_mode);
			$("#cheque_transaction_number").val(data.cheque_transaction_number);
			$("#cheque_narration").val(data.cheque_narration);
	
			$("#cheque_entry_type").select2("val",data.cheque_entry_type);
			$("#edit_id").val(id);
			$('#addbank_cheque').val('Update');
			Unloading();
		}
	});
}

function delete_bank_cheque_field(id){
	var r= confirm("Do you want to delete it ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/ledger/',
			data: { mode : "delete_bank_cheque",  eid : id },
			success: function(response)
			{
				//alert(response);
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					load_bank_cheque_datatable();
					show_bankcheque_total();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
				}
		});	
	}
}

function show_bankcheque_total()
{
	var ledger_id = $('#ledger_id').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "get_bankcheque_total",edit_ledger_id:ledger_id },
		success: function(data){
			var res = data.split("-");
			$('#depo_total').text(res[0]);
			$('#issued_total').text(res[1]);				
			Unloading();
		}		
	});
}

function show_multicurrency_total(){

	var ledger_id = $('#ledger_id').val();
	
	Loading();
	
	var dsum = 0;
	$('.multi_currency_Debit').each(function(){
		dsum += parseFloat(this.value);
	});
	//alert(dsum);
	var csum = 0;
	$('.multi_currency_Credit').each(function(){
		csum += parseFloat(this.value);
	});
	
	var total = csum-dsum;
	
	if(total>=0)
	{
		var gtotal = total;
		var type_entry = " <strong style='color:green'>CR</strong>";
	}
	else
	{
		var gtotal = -(total);
		var type_entry = " <strong style='color:red'>DR</strong>";
	}
	
	$('#multicurrency_total').text(gtotal);
	$('#multicurrency_total_type').html(type_entry);
	$("#opn_balance").val(gtotal);
	Unloading();
	/*$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "get_multicurrency_total",edit_ledger_id:ledger_id },
		success: function(data){			
			$('#multicurrency_total').text(data);
			Unloading();
		}		
	});	*/
}

function show_multibranch_total(){
	var ledger_id = $('#ledger_id').val();
	
	Loading();
	
	var dsum = 0;
	$('.multi_branch_Debit').each(function(){
		dsum += parseFloat(this.value);
	});
	//alert(dsum);
	var csum = 0;
	$('.multi_branch_Credit').each(function(){
		csum += parseFloat(this.value);
	});
	
	var total = csum-dsum;
	
	if(total>=0)
	{
		var gtotal = total;
		var type_entry = " <strong style='color:green'>CR</strong>";
	}
	else
	{
		var gtotal = -(total);
		var type_entry = " <strong style='color:red'>DR</strong>";
	}
	
	$('#multibranch_total').text(gtotal);
	$('#multibranch_total_type').html(type_entry);
	$("#opn_balance").val(gtotal);
	Unloading();
	
	/*$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "get_multibranch_total",edit_ledger_id:ledger_id },
		success: function(data){			
			$('#multibranch_total').text(data);
			Unloading();
		}		
	});*/	
}

function show_billbybill_total(){
	var ledger_id = $('#ledger_id').val();
	Loading();
	
	var dsum = 0;
	$('.multi_bill_Debit').each(function(){
		dsum += parseFloat(this.value);
	});
	//alert(dsum);
	var csum = 0;
	$('.multi_bill_Credit').each(function(){
		csum += parseFloat(this.value);
	});
	
	var total = csum-dsum;
	
	if(total>=0)
	{
		var gtotal = total;
		var type_entry = " <strong style='color:green'>CR</strong>";
	}
	else
	{
		var gtotal = -(total);
		var type_entry = " <strong style='color:red'>DR</strong>";
	}
	
	$('#billbybill_total').text(gtotal);
	$('#billbybill_total_type').html(type_entry);
	Unloading();
	
	/*$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "get_billbybill_total",edit_ledger_id:ledger_id },
		success: function(data){			
			$('#billbybill_total').text(data);
			Unloading();
		}		
	});	*/
}

function add_multi_branch_field(){
	var ledger_id = $('#ledger_id').val();
	if(!$("#multi_branch_id").val()){		
		toastr.warning("Please Select Branch", "ERROR");
		$("#multi_branch_id").select2('focus');
		return false;
	}
	else if(!$("#branch_opening_balance").val() || parseFloat($("#branch_opening_balance").val())=='0'){		
		toastr.warning("Please insert branch openibng balance", "ERROR");
		return false;
	}
	else if(!$("#branch_entry_type").val()){	
		toastr.warning("Please Select balance type", "ERROR");	
		$("#branch_entry_type").select2('focus');
		return false;
	}
	

	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/ledger/',
			data: { mode : "multibranch_fieldadd",ledger_id:ledger_id,edit_id:$("#edit_id").val(),branch_id:$("#multi_branch_id").val(),
			branch_opening_balance:$("#branch_opening_balance").val(),branch_entry_type:$("#branch_entry_type").val() },
			success: function(response)
			{
				//alert(response);
				
				if(response == '1'){
					toastr.success("Branch added", "SUCCESS");
					$("#branch_opening_balance").val("");
					$("#multi_branch_id").select2("val","");
					$("#branch_entry_type").select2("val","");
					load_multi_branch_datatable();
					//show_multibranch_total();
					Unloading();
                } else if(response == '2'){
                  	toastr.success("Branch updated", "SUCCESS");
					$("#branch_opening_balance").val("");
					$("#multi_branch_id").select2("val","");
					$("#branch_entry_type").select2("val","");
					$("#edit_id").val("");
					$('#addrow_branch').val('Add');				
					load_multi_branch_datatable();
					//show_multibranch_total();
					Unloading();
                } else if(response == '0'){
                    toastr.warning("Something went wrong please contact your admin", "ERROR");
                    Unloading();
                }
			    
			}
		});
}

function delete_multi_branch_field(id){
	var r= confirm("Do you want to delete it ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/ledger/',
			data: { mode : "delete_multi_branch",  eid : id },
			success: function(response)
			{
				//alert(response);
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					load_multi_branch_datatable();
					//show_multibranch_total();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
				}
		});	
	}
}

function edit_multi_branch_field(id)
{
	//alert(id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "preedit_multibranch",  id : id},
		success: function(response)
		{					
			var data = jQuery.parseJSON(response);
			
			$("#multi_branch_id").select2("val",data.branch_id);

			$("#branch_opening_balance").val(data.branch_opening_balance);					
			$("#branch_entry_type").select2("val",data.branch_entry_type);
			$("#edit_id").val(id);
			$('#addrow_branch').val('Update');
			Unloading();
		}
	});
}


function getMultiCurrencyPopup(multiId){
	if(multiId =='yes'){
		$("#modal-multi-currency").modal("show");
		load_multi_currency_datatable();
		$("#checkMultiCurrLink").show();
		////show_multicurrency_total();
	}else{
		$("#checkMultiCurrLink").hide();
	}
}

function changeGstField(){
	var ledger_gst_applicable = $('#ledger_gst_applicable').val();
	//alert(ledger_gst_applicable);
	if(ledger_gst_applicable == 'yes'){
		$('.gstApplicable').show();
	}else{
		$('.gstApplicable').hide();
	}
}
	

function load_multi_currency_datatable(){

	var ledger_id = $('#ledger_id').val();

	datatable = $("#multi_currency_table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"bStateSave": false,
        "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('offersDataTables', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('offersDataTables'));
        },
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+administration_domain+'app/ledger/',
		"fnServerParams": function ( aoData2 ) {
			aoData2.push( { "name": "mode", "value": "multi_currency_table" } );
			aoData2.push( { "name": "edit_ledger_id", "value": ledger_id } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
			show_multicurrency_total();
		},
		"fnFooterCallback": function ( oSettings ) {
			show_multicurrency_total();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');

}

function add_multi_currency_field()
{
	var ledger_id = $('#ledger_id').val();

	if(!$("#currencyid").val()){		
		toastr.warning("Please Select Currency", "ERROR");
		$("#currencyid").select2('focus');
		return false;
	}
	else if(!$("#currency_opening_balance").val() || parseFloat($("#currency_opening_balance").val())=='0'){		
		toastr.warning("Please insert currency openibng balance", "ERROR");
		return false;
	}
	else if(!$("#currency_entry_type").val()){	
		toastr.warning("Please Select balance type", "ERROR");	
		$("#currency_entry_type").select2('focus');
		return false;
	}
	else if(!$("#curreency_opening_balance_rs").val() || parseFloat($("#curreency_opening_balance_rs").val())=='0'){		
		toastr.warning("Please insert currency openibng balance in Rs.", "ERROR");
		return false;
	}

	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/ledger/',
			data: { mode : "multicurrency_fieldadd",ledger_id:ledger_id,edit_id:$("#edit_id").val(),currencyid:$("#currencyid").val(),currency_opening_balance:$("#currency_opening_balance").val(),
			currency_entry_type:$("#currency_entry_type").val(),curreency_opening_balance_rs:$("#curreency_opening_balance_rs").val() },
			success: function(response)
			{
				//alert(response);
				
				if(response == '1'){
					toastr.success("Currency added", "SUCCESS");
					$("#currency_opening_balance").val("");
					$("#currencyid").select2("val","");
					$("#currency_entry_type").select2("val","");
					$("#curreency_opening_balance_rs").val("");					
					load_multi_currency_datatable();
					//show_multicurrency_total();
					Unloading();
                } else if(response == '2'){
                  	toastr.success("Currency updated", "SUCCESS");
					$("#currency_opening_balance").val("");
					$("#currencyid").select2("val","");
					$("#currency_entry_type").select2("val","");
					$("#curreency_opening_balance_rs").val("");	
					$("#edit_id").val("");
					$('#addrow_currency').val('Add');				
					load_multi_currency_datatable();
					//show_multicurrency_total();
					Unloading();
                } else if(response == '0'){
                    toastr.warning("Something went wrong please contact your admin", "ERROR");
                    Unloading();
                }
			    
			}
		});
}

function edit_multi_currency_field(id)
{
	//alert(id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "preedit_multicurrency",  id : id},
		success: function(response)
		{					
			var data = jQuery.parseJSON(response);
			
			$("#currencyid").select2("val",data.currency_id);
			$("#currency_opening_balance").val(data.currency_opening_balance);					
			$("#currency_entry_type").select2("val",data.currency_entry_type);
			$("#curreency_opening_balance_rs").val(data.curreency_opening_balance_rs);
			$("#edit_id").val(id);
			$('#addrow_currency').val('Update');
			Unloading();
		}
	});
}

function delete_multi_currency_field(id){
	var r= confirm("Do you want to delete it ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/ledger/',
			data: { mode : "delete_multi_currency",  eid : id },
			success: function(response)
			{
				//alert(response);
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					load_multi_currency_datatable();
					//show_multicurrency_total();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
				}
		});	
	}
}

function getBillByBillPopup(billbybillId){
	//alert(depId);
	if(billbybillId =='yes'){
		var ledger_id = $('#ledger_id').val();
		$("#modal-bill-by-bill").modal("show");
		$("#checkBillbybillLink").show();
		show_billbybill_total();
	}else{
		$("#checkBillbybillLink").hide();
	}
}

function add_billbybill_field(){
	var ledger_id = $('#ledger_id').val();

	var bill_date = $('#bill_opening_date').val();
	var dueDate = $('#bill_due_date').val();
	var bill_opening_date = new Date(bill_date.replace( /(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3") );
	var due_date = new Date(dueDate.replace( /(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3") );
	
	if(!$("#bill_ref_no").val()){		
		toastr.warning("Please Enter Ref No", "ERROR");
		$("#bill_ref_no").focus();
		return false;
	}
	else if(!$("#bill_opening_date").val()){	
		toastr.warning("Please Select Opening Date", "ERROR");	
		$("#bill_opening_date").focus();
		return false;
	}
	else if(!$("#bill_amount").val() || parseFloat($("#bill_amount").val())=='0'){		
		toastr.warning("Please Enter Opening Amount", "ERROR");
		return false;
	}
	else if(!$("#bill_entry_type").val()){	
		toastr.warning("Please Select balance type", "ERROR");	
		$("#bill_entry_type").select2('focus');
		return false;
	}
	else if(bill_opening_date > due_date){	
		toastr.warning("Bill due date can not be less then bill date", "ERROR");	
		$("#bill_due_date").select2('focus');
		return false;
	}
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "billbybill_fieldadd",ledger_id:ledger_id,edit_bill_id:$("#edit_bill_id").val(),bill_ref_no:$("#bill_ref_no").val(),
		bill_opening_date:$("#bill_opening_date").val(),bill_amount:$("#bill_amount").val(),bill_entry_type:$("#bill_entry_type").val(),
		bill_due_date:$("#bill_due_date").val() },
		success: function(response)
		{
			
			if(response == '1'){
				toastr.success("Bill details added", "SUCCESS");
				$("#bill_ref_no").val("");
				$("#bill_entry_type").select2("val","");
				$("#bill_opening_date").val("");
				$("#bill_amount").val("");
				$("#bill_due_date").val("");
				Unloading();
				load_billbybill_datatable();
				show_billbybill_total();
            } else if(response == '2'){
				toastr.success("Bill details Updated", "SUCCESS");
				$("#bill_ref_no").val("");
				$("#bill_entry_type").select2("val","");
				$("#bill_opening_date").val("");
				$("#bill_amount").val("");
				$("#bill_due_date").val("");
				$("#edit_bill_id").val("");
				$('#addrow_billbybill').val('Add');
				Unloading();
				load_billbybill_datatable();
				show_billbybill_total();
            }   
             else{
                toastr.warning("Something went wrong please contact your admin", "ERROR");
                Unloading();
            }
		    
		}
	});
}

function edit_billbybill_field(id)
{
	//alert(id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "preedit_billbybill",  id : id},
		success: function(response)
		{					
			var data = jQuery.parseJSON(response);
			
			$("#bill_ref_no").val(data.bill_ref_no);					
			$("#bill_entry_type").select2("val",data.bill_entry_type);
			$("#bill_opening_date").val(data.bill_opening_date);

			$("#bill_amount").val(data.bill_amount);
			$("#bill_due_date").val(data.bill_due_date);
			$("#edit_bill_id").val(id);
			$('#addrow_billbybill').val('Update');
			Unloading();
		}
	});
}

function delete_billbybill_field(id){
	var r= confirm("Do you want to delete it ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/ledger/',
			data: { mode : "delete_billbybill_field",  eid : id },
			success: function(response)
			{
				//alert(response);
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					load_billbybill_datatable();
					show_billbybill_total();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
				}
		});	
	}
}

function load_billbybill_datatable(){

	var ledger_id = $('#ledger_id').val();

	datatable = $("#billbybill_table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"bStateSave": false,
        "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('offersDataTables', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('offersDataTables'));
        },
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+administration_domain+'app/ledger/',
		"fnServerParams": function ( aoData2 ) {
			aoData2.push( { "name": "mode", "value": "fetch_billbybill_table" } );
			aoData2.push( { "name": "edit_ledger_id", "value": ledger_id } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
			show_billbybill_total();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');

}

function get_sundry_popup(sundryId){
	if(sundryId =='yes'){
		var ledger_id = $('#ledger_id').val();
		//alert(ledger_id);
		$("#modal-bill-sundry").modal("show");
		$("#checkBillSundryLink").show();
		isbasiccalculate($("#sundry_calculate_on").val());
	}else{
		$("#checkBillSundryLink").hide();
		
	}
	
}

function isbasiccalculate(sundry_calculate_val){
	if(sundry_calculate_val == 2){
		$(".applygst").show();
	}else{
		$(".applygst").hide();
		$('#apply_gst').val(1);
		$('#sundry_gst').select2("val",'');
	}
	showtax($("#apply_gst").val());

}

function showtax(apply_gst){
	if(apply_gst == 2){
		$(".taxcat").show();
	}else{
		$(".taxcat").hide();
	}
}

function add_bill_sundry_field(){

	if(!$("#sundry_type").val() || parseFloat($("#sundry_type").val())=='0')
	{
		toastr.warning("Please Select Sundry Type", "ERROR");
		return false;
	}

	var ledger_id = $('#ledger_id').val();
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "billsundry_fieldadd", ledger_id:ledger_id, edit_sundry_id:$("#edit_sundry_id").val(), sundry_type:$("#sundry_type").val(), sundry_nature:$("#sundry_nature").val(), sundry_default_value:$("#sundry_default_value").val(), sundry_amount_of:$("#sundry_amount_of").val(), sundry_calculate_on:$("#sundry_calculate_on").val(), apply_gst:$("#apply_gst").val(),sundry_hsn:$("#sundry_hsn").val(), sundry_gst:$("#sundry_gst").val() },
		success: function(response)
		{			
			var obj=jQuery.parseJSON(response);
			response = obj.msg;
			if(response.trim() == '1'){
				toastr.success("Bill Sundry added", "SUCCESS");
				Unloading();
				$('#add_sundry').val('Update');
				$("#ledger_hsn").val(obj.hsn_code);
            } else if(response.trim() == '2'){
				toastr.success("Bill Sundry Updated", "SUCCESS");
				Unloading();
				$("#ledger_hsn").val(obj.hsn_code);
            }   
             else{
                toastr.warning("Something went wrong please contact your admin", "ERROR");
                Unloading();
            }
		    
		}
	});
}

function getDepreciationPopup(depId){
	//alert(depId);
	if(depId =='yes'){
		var ledger_id = $('#ledger_id').val();
		//alert(ledger_id);
		//$("#edit_ledger_id").val(ledger_id);
		$("#modal-depreciation").modal("show");
		$("#checkDepreciationLink").show();
		if($('#it_act_check').is(':checked'))
		{
			$('.it_act').show();
		}
		else
		{
			$('.it_act').hide();
		}
	}else{
		$("#checkDepreciationLink").hide();
		
	}
}

function add_depreciation_field(){	
	
	if($('#it_act_check').is(':checked'))
	{
		if(!$("#depreciate_annual_rate").val())
		{
			toastr.warning("Enter Annual Rate", "ERROR");
			return false;
		}
	}
	if($("#it_act_check").prop('checked') == false)
	{
		if($('#depreciate_rate_wdv').val()=='')
		{
			toastr.warning("Enter Rate", "ERROR");
			return false;
		}
	}
	
	var ledger_id = $('#ledger_id').val();
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "depreciation_fieldadd",ledger_id:ledger_id,edit_id:$("#edit_dep_id").val(),depreciate_annual_rate:$("#depreciate_annual_rate").val(),
		depreciate_half_rate:$("#depreciate_half_rate").val(),depreciate_rate_wdv:$("#depreciate_rate_wdv").val(),
		depreciate_opening:$("#depreciate_opening").val(), },
		success: function(response)
		{
			//alert(response);
			
			if(response == '1'){
				toastr.success("Depreciation added", "SUCCESS");
				Unloading();
            } else if(response == '2'){
				toastr.success("Depreciation Updated", "SUCCESS");
				Unloading();
            }   
             else{
                toastr.warning("Something went wrong please contact your admin", "ERROR");
                Unloading();
            }
		    
		}
	});
}


function get_salesman_popup(depId){
	//alert(depId);
	if(depId =='yes'){
		var ledger_id = $('#ledger_id').val();
		//alert(ledger_id);
		//$("#edit_ledger_id").val(ledger_id);
		$("#modal-salesman").modal("show");
		$("#checkSalesmanLink").show();
		load_salesman_data();
	}else{
		$("#checkSalesmanLink").hide();
		
	}
}


$("#ledger_add").on('submit',function(e) {
	
    if($("#opn_balance").val() < 0){
		toastr.warning("Enter Opening Balance", "ERROR");
		$("#opn_balance").focus();
		return false;
	}  
    if($("#emp_profile_img").val()){
        var ext = $('#emp_profile_img').val().split('.').pop().toLowerCase();
        if($.inArray(ext, ['gif','png','jpg','jpeg']) === -1) {
                toastr.warning("Only image type jpg/png/jpeg/gif is allowed", "ERROR");
                $("#emp_profile_img").focus();
                return false;
        } 
    }        
        
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#ledger_add").valid()) {
		return false;
	}
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");	 
	
	var form_data=new FormData(this);
	var form_type=$('#form_type').val();
	form_data.append('file', $('#emp_profile_img').prop('files')[0]);
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/ledger/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);			
			var obj=jQuery.parseJSON(response);
			//alert(response);
			response=obj.res;
			if(response.trim() == '1') {
				toastr.success("LEDGER ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				// var ledgerfor = $("#ledgerfor").val();
				// if(ledgerfor == 'sc'){
				// 	var insertid = obj.inserid;
				// 	changeStatus(obj.inserid,1);
				// 	window.location=root_domain+purchase_domain+'purchase_add';
				// 	return;
				// }
				
				$('#countryid').select2('val','');
				$('#stateid').select2('val','');
				$('#cityid').select2('val','');
				$('#cust_gst_reg').select2('val','');
				$('#zone_id').select2('val','');
				$('#pay_terms').select2('val','');
				$('#bill_type').select2('val','');
				$('#ledger_grp').select2('val','');
				$('#emp_zone_id').select2('val','');
				$('#bankid').select2('val','');
				$('#balance_typeid').select2('val','');
				$('#branch_id_customer').select2('val','');
				$('#branch_id_emp').select2('val','');
				
				$("#"+form_type).addClass("ledger_forms");
				
				if(obj.direct_ledger_add == 1){
					if(obj.ledger_add_type == 'INVOICE'){
						$("#modal-add-ledger").modal("hide");
						$('#cust_id').append('<option value='+obj.inserid+'>'+obj.l_name+'</option>');	
						$('#cust_id').select2("val",obj.inserid);
						$("#cust_id").trigger('change');
					}else if(obj.ledger_add_type == 'PURCHASE'){
						$("#modal-add-ledger").modal("hide");
						$('#vender_id').append('<option value='+obj.inserid+'>'+obj.l_name+'</option>');	
						$('#vender_id').select2("val",obj.inserid);
						$("#vender_id").trigger('change');
					}else if(obj.ledger_add_type == 'PAYMENT'){
						$("#modal-add-ledger").modal("hide");
						$('#vender_id').append('<option value='+obj.inserid+'>'+obj.l_name+'</option>');	
						$('#vender_id').select2("val",obj.inserid);
						$("#vender_id").trigger('change');
					}else if(obj.ledger_add_type == 'JOURNAL'){
						$("#modal-add-ledger").modal("hide");
						$('#ledger_id').append('<option value='+obj.inserid+'>'+obj.l_name+'</option>');	
						$('#ledger_id').select2("val",obj.inserid);
						$("#ledger_id").trigger('change');
					}else if(obj.ledger_add_type == 'RECIPT'){
						$("#modal-add-ledger").modal("hide");
						$('#receiver_ledger').append('<option value='+obj.inserid+'>'+obj.l_name+'</option>');	
						$('#receiver_ledger').select2("val",obj.inserid);
						$("#receiver_ledger").trigger('change');
					}else if(obj.ledger_add_type == 'CONTRA'){
						$("#modal-add-ledger").modal("hide");
						$('#ledger_id').append('<option value='+obj.inserid+'>'+obj.l_name+'</option>');	
						$('#ledger_id').select2("val",obj.inserid);
						$("#ledger_id").trigger('change');
					}else if(obj.ledger_add_type == 'PURCHASE_CARD'){
						$("#modal-add-ledger").modal("hide");
						$('#vender_id').append('<option value='+obj.inserid+'>'+obj.l_name+'</option>');	
						$('#vender_id').select2("val",obj.inserid);
						$("#vender_id").trigger('change');
					}else if(obj.ledger_add_type == 'PURCHASE_CARD1'){
						$("#modal-add-ledger").modal("hide");
						$('#prod_id_vend').append('<option value='+obj.inserid+'>'+obj.l_name+'</option>');	
						$('#prod_id_vend').select2("val",obj.inserid);
						$("#prod_id_vend").trigger('change');
					}else if(obj.ledger_add_type == 'SALES_ORDER'){
						$("#modal-add-ledger").modal("hide");
						$('#cust_id').append('<option value='+obj.inserid+'>'+obj.l_name+'</option>');	
						$('#cust_id').select2("val",obj.inserid);
						$("#cust_id").trigger('change');
					}else if(obj.ledger_add_type == 'PROFORMA'){
						$("#modal-add-ledger").modal("hide");
						$('#cust_id').append('<option value='+obj.inserid+'>'+obj.l_name+'</option>');	
						$('#cust_id').select2("val",obj.inserid);
						$("#cust_id").trigger('change');
					}
				}else{
					$('#ledger_add').trigger('reset');
					window.location=root_domain+administration_domain+'ledger_list';
				}
				
			}
			else if(response.trim() == '2') {
				toastr.success("LEDGER ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-example-modal-city").modal("hide");
				$('#cityid').append('<option value='+obj.cityid+'>'+obj.city_name+'</option>');
				$("#cityid").trigger('change');
				$('#cityid').select2("val",obj.cityid);
				$('#ledger_add').trigger('reset');
				Unloading();
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response.trim() == '-1') {
				toastr.warning("LEDGER WITH SAME NAME ALREADY EXIST", "ERROR")
				Unloading();
			}
			else if(response.trim() == '3') {
				toastr.success("LEDGER UPDATED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+administration_domain+'ledger_list';
				Unloading();
			}
			
			$('#ledger_add').trigger('reset');	
			//$('#stateid').select2("val",state);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function load_ledger_datatable(){
        var branch_id = $('#branch_id').val();
        var gr_id = $('#gr_id').val(); 
	datatable = $("#ledger-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"bStateSave": true,
        "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('offersDataTables', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('offersDataTables'));
        },
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+administration_domain+'app/ledger/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },
                        { "name": "branch_id", "value": branch_id },
                        { "name": "gr_id", "value": gr_id }
                        );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}



function add_bank()
{
	if($("#bank_ac").val()==""){
		toastr.warning("Select Account Number", "ERROR");
		$("#bank_ac").focus();
		return false;
	}
	else if($("#bank_name").val()==""){
		toastr.warning("Select bank name", "ERROR");
		$("#bank_name").focus();
		return false;
	}
	
	var bank_ac=$('#bank_ac').val();
	var bank_name=$('#bank_name').val();
	var ac_name=$('#ac_name').val();
	var bank_ifsc=$('#bank_ifsc').val();
	var cust_id=$('#ledger_id').val();
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "add_bank_name",edit_id:$("#edit_id").val(),bank_ac:$("#bank_ac").val(),bank_name:$("#bank_name").val(),ac_name:$("#ac_name").val(),bank_ifsc:$("#bank_ifsc").val(),bank_open:$("#bank_open").val(),cust_id:cust_id },
		success: function(response)
		{
          if(response == '1'){
			//console.log(response);
			$("#bank_ac").val("");
			$("#bank_name").select2("val","");
			$("#ac_name").val("");
			$("#bank_ifsc").val("");
			$("#bank_open").val("");
			$("#add_bank_bt").val("Add");
			show_bank_data();
			if($("#edit_id").val() != ''){
				toastr.success("Bank detail Updated SUCCESSFULLY", "success");
				$("#edit_id").val('');
			}else{
				toastr.success("Bank detail Added SUCCESSFULLY", "success");
			}
          } else if(response == '2'){
               toastr.warning("Account with same bank already exist", "ERROR");
           }
		    Unloading();
		}
	});
	
}

function show_bank_data()
{
	var form_mode=$('#mode').val();
	var cust_id=$('#ledger_id').val();
	//alert(cust_id);
	//var mode=$('#mode').val();
	//alert(cust_id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "load_bank_detail", cust_id:cust_id,form_mode:form_mode },
		success: function(data){
			//console.log(data);
			$('#table_bank_details').html(data);				
			Unloading();
		}		
	});
}

function edit_data_bank(id)
{
	//var form_mode=$("#jobwork_outward_add #mode").val();
	//alert(id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "preedit_bank",  id : id },
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			var data = jQuery.parseJSON(response);
			$('#bank_ac').val(data.bank_ac);
			$('#ac_name').val(data.ac_name);
			$("#bank_ifsc").val(data.bank_ifsc);
			$("#bank_open").val(data.bank_open);
			$("#bank_name").select2("val",data.b_name);
			
			//$("#outward_product_amount").val(data.outward_product_amount);
			$("#edit_id").val(id);
			$("#add_bank_bt").val("Update");
			/*if(form_mode=='Edit'){
				load_stock(data.raw_product_id,data.outward_product_qty)
			}else{
				load_stock(data.raw_product_id,0)
			}*/
			show_bank_data();
			Unloading();

		}
	});
}


function delete_data_bank(id)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/ledger/',
			data: { mode : "delete_data_bank",  eid : id },
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_bank_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}

				show_bank_data();					
			}
		});	
	}
	
}

//contact person details 
function ledger_grp_change(){
	var id = $('#ledger_grp').val();
	var parent_group_id = $('#parent_group_id').val();
	//alert(id);
	//alert(parent_group_id);
	if(((id == 32 || id == 22 || id == 24 || id == 19 || id == 20 || id == 37 || id == 38 || id == 28 || id==16 || parent_group_id==32 || parent_group_id==22 || parent_group_id==19 || parent_group_id==37 || parent_group_id==38 || parent_group_id==28 || parent_group_id==16 || parent_group_id==20) && ($('#enable_tds').val() == 'yes')) )
	{
		$('.party_pay_cat_div').show();
		$(".party_pay_cat_div_sub").show();
		//$("#party_pay_cat").prop('required',true);
		if(id == 24 || id == 16 || id == 19){
			$(".party_pay_cat_div_sub").hide();
		}
	}else{
		$('.party_pay_cat_div').hide();
		$("#party_pay_cat").prop('required',false);
	}

	//For expence form
	var group_form = $("#ledger_grp").find('option:selected').attr('data-formgroup');
	//alert(group_form);
	if(group_form == 'expense_form' || group_form == 'income_form' || group_form =='loan_advance_form' || group_form =='tax_form'){
		$('.billSundry').show();
	}else{
		$('.billSundry').hide();
	}
}

function ledger_monthly_budget_change(){
	var id = $('#ledger_grp').val();
	var parent_group_id = $('#parent_group_id').val();
	//alert(parent_group_id);
	if(id == 18 || id == 21 || id == 19 || id == 20 || id == 93 || id==16 || parent_group_id == 18 || parent_group_id == 20 || parent_group_id == 21 || parent_group_id == 19 || parent_group_id == 93 || parent_group_id==16){
		$('.monthly_budget').show();
	}else{
		$('.monthly_budget').hide();
	}
}

function ledger_tcs_tds_change(){
	var id = $('#ledger_grp').val();
	var parent_group_id = $('#parent_group_id').val();
	if(id == 22 || id==24 || id == 32 || id == 37 || id == 38 || id == 19 || id == 20 || id == 28 || id==16 || parent_group_id==22 || parent_group_id == 32 || parent_group_id==37 || parent_group_id==38 || parent_group_id ==19 || parent_group_id ==28 || parent_group_id==16 || parent_group_id==20  ){
		$('.tds_tcs').show();
	}else{
		$('.tds_tcs').hide();
	}
}

function ledger_chequebank_change(){
	var group_form = $("#ledger_grp").find('option:selected').attr('data-formgroup');
	//var id = $('#ledger_grp').val();
	if(group_form == 'bank_form'){
		$('.chequebank').show();
	}else{
		$('.chequebank').hide();
	}
}

function ledger_grp_change_fix_assets(){
	//var group_form = $("#ledger_grp").find('option:selected').attr('data-formgroup');
	var id = $('#ledger_grp').val();
	var parent_group_id = $('#parent_group_id').val();
	if(id == 18 || parent_group_id==18){
		$('.depreciation').show();
	}else{
		$('.depreciation').hide();
	}
}

function ledger_grp_change_Tax_type(){
	var group_form = $("#ledger_grp").find('option:selected').attr('data-formgroup');
	//var id = $('#ledger_grp').val();
	if(group_form == 'tax_form'){
		$('.ledgerTaxtype').show();
	}else{
		$('.ledgerTaxtype').hide();
	}
}

function add_contact_person()
{
	if($("#con_name").val()==""){
		toastr.warning("Enter Name Of Person", "ERROR");
		$("#con_name").focus();
		return false;
	}
	// else if(!validemail($('#con_email').val())){
	// 	toastr.warning("Please insert valid email", "ERROR");
	// 	$("#con_email").focus();
	// 	return false;
	// }
	
	var con_name=$('#con_name').val();
	var con_mobile=$('#con_mobile').val();
	var con_email=$('#con_email').val();
	var job_title = $('#job_title').val();
	var cust_id=$('#ledger_id').val();
	
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { 
			mode : "add_contact_person",
			edit_id:$("#edit_id_contact").val(),
			con_name:$("#con_name").val(),
			con_isd_id : $("#con_isd_id").val(),
			con_mobile:$("#con_mobile").val(),
			con_email:$("#con_email").val(),
			cust_id:cust_id,
			job_title:job_title 
		},
		success: function(response)
		{
			
            if(response.trim() == '1'){
            	
				//console.log(response);
				$("#con_name").val("");
				$("#con_isd_id").select2('val','');
				$("#con_mobile").val("");
				$("#con_email").val("");
				$("#job_title").val("");
				$("#edit_id_contact").val("")
				$("#add_contact_bt").val("Add");
				show_contact_data();
            } else if(response.trim() == '2'){
                toastr.warning("Contact Person already exist", "ERROR");
            }
            Unloading();
			
		}
	});
	
}


function show_contact_data()
{
	var form_mode=$('#mode').val();
	var cust_id=$('#ledger_id').val();
	//alert(cust_id);
	//var mode=$('#mode').val();
	//alert(cust_id);
	//alert(form_mode);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "load_contact_detail", cust_id:cust_id,form_mode:form_mode },
		success: function(data){
			//console.log(data);
			//alert(data);
			$('#table_contact_details').html(data);				
			Unloading();
		}		
	});
}

function edit_contact_data(id)
{
	
	//var form_mode=$("#jobwork_outward_add #mode").val();
	//alert(id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "preedit_contact",  id : id },
		success: function(response)
		{
			//console.log(response);
			var data = jQuery.parseJSON(response);
			$('#con_name').val(data.cust_contact_person_name);
			$('#con_mobile').val(data.cust_contact_person_no);
			if(data.cust_contact_person_email != 0){
				$("#con_email").val(data.cust_contact_person_email);
			}else{
				$("#con_email").val('');
			}
			$("#job_title").val(data.cust_contact_person_designation);			
			$("#edit_id_contact").val(id);
			$("#add_contact_bt").val("Update");
			//show_contact_data();
			Unloading();
		}
	});
}


function delete_contact_data(id)
{
	var r= confirm(" Are you sure, you want to delete ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/ledger/',
				data: { mode : "delete_data_contact",  eid : id },
				success: function(response)
				{
					console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						show_contact_data();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}
					show_contact_data();
				
				}
			});	
		}
	
}

function delete_ledger(id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/ledger/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				var resp = JSON.parse(response);
				if(resp.msg == "-1") {
					swal("CURRENT RECORD ALREADY USED BELOW MODULES", ""+resp.table+"", "warning");
         		    load_ledger_datatable();
					Unloading();
				}else if(resp.msg == "1") {
					toastr.success("LEDGER DELETE SUCCESSFULLY", "SUCCESS");
					load_ledger_datatable();
					Unloading();
				}
				else if(resp.msg == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}

function alloc_sold_pro(cust_id){
	if(cust_id) {
		$("#alloc_sold_pro_modal").modal("show");
		$("#alloc_cust_id").val(cust_id);
		show_sold_pro(); 
	}
}
function add_sold_pro_field() {
	var cust_id=$("#alloc_cust_id").val();
	var sold_inv_foc_date=$("#sold_inv_foc_date").val();
	
	if($("#product_id").val()==""){
		toastr.warning("Select Product Name", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	else if(!sold_inv_foc_date){
		toastr.warning("Choose FOC Date.", "ERROR");
		$("#sold_inv_foc_date").focus();
		return false;
	}
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode:"add_sold_pro_field", edit_id:$("#edit_id1").val(), product_id:$("#product_id").val(), model_id:$("#model_id").val(), sold_pro_srl_no:$("#sold_pro_srl_no").val(), sold_inv_rmrk:$("#sold_inv_rmrk").val(), sold_inv_foc_date:sold_inv_foc_date, cust_id:cust_id },
		//contentType: false,
		//  processData:false,
		success: function(response)
		{
			console.log(response);
			var resp = JSON.parse(response);
			if(resp.res=='1'){
				$("#product_id").select2("val","");
				$("#sold_inv_foc_date").val("");
				$("#sold_pro_srl_no").val('');
				$("#sold_inv_rmrk").val('');
				$("#edit_id1").val('');
				$("#addcustrow").val("Add");
				Unloading(); 
				show_sold_pro();  
			}
			else if(resp.res=='-1'){
				toastr.info("Duplicate Record Found", "ERROR");
				Unloading(); 
			}
			else{
				toastr.warning("SOMETHING WENT WRONG!!!", "ERROR");
				Unloading(); 
			}
		}
	});
}
function show_sold_pro(){
	var cust_id=$("#alloc_cust_id").val(); 
	$("#sold-pro-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bDestroy": true,
		"bProcessing": true,
		"bServerSide" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 50, 100, -1], [10, 50, 100,"All"]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+administration_domain+'app/ledger/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "show_sold_pro" },{ "name": "cust_id", "value": cust_id } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}
function edit_sold_pro(cust_sold_pro_id) { 
	Loading(true);
	 $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "edit_sold_pro", cust_sold_pro_id : cust_sold_pro_id },
		//contentType: false,
		//processData:false,
		success: function(resnse)
		{
			//console.log(resnse);
			var resp = jQuery.parseJSON(resnse); 
			$("#product_id").select2("val",resp.product_id);
			$("#model_id").html(resp.model_resp_html);
			$("#model_id").select2("val",resp.model_id);
			$("#edit_id1").val(cust_sold_pro_id);
			$("#sold_inv_no").val(resp.sold_inv_no);
			$("#sold_pro_srl_no").val(resp.sold_pro_srl_no);
			$("#sold_inv_rmrk").val(resp.sold_inv_rmrk);
			$("#sold_inv_date").datepicker("setDate", resp.sold_inv_date);
			$("#sold_inv_foc_date").datepicker("setDate", resp.sold_inv_foc_date);
			$("#sold_inv_rate").val(resp.sold_inv_rate);
			$("#addcustrow").val("Update"); 
			Unloading();
		}
	});	 
}
function delete_sold_pro(cust_sold_pro_id)  {
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/ledger/',
			data: { mode : "delete_sold_pro",  cust_sold_pro_id : cust_sold_pro_id },
			success: function(resnse)
			{
				if(resnse.trim() == "1") {
					toastr.success("CUSTOMER PRODUCT DELETED SUCCESSFULLY", "SUCCESS");
					show_sold_pro();
					Unloading();
				}
				else if(resnse.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}

function changeStatus(lid,l_status)
{
	
		//alert(sp_id);
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/ledger/',
			data: { mode : "change_status", lid : lid,l_status:l_status },
			success: function(response)
			{
				toastr.success("STATUS CHANGED SUCCESSFULLY", "SUCCESS");
				Unloading();
				load_ledger_datatable();
			}
		});
	
}


function get_branch_by_zone(zid,sindex,bid)
{
	//alert(zid);
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "get_branch_by_zone",zid : zid,bid : bid,sindex:sindex },
		success: function(resnse)
		{
			//alert(resnse);
			$('#'+sindex).html(resnse);
			$('#'+sindex).select2('focus');
			$('#'+sindex).select2('val',bid);
			Unloading();			
		}
	});	
}

function report_ledger() 
{
	//alert('hello');
	var date=$("#rep_date").val();
	var g_id=$("#g_id").val();
	//alert(emp_id);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "generate_report_ledger",date:date,g_id:g_id },
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			$('#data_table').html(response);
			Unloading();
								
		}
	});	
	
}
function load_ledger(){
	var group_id=$("#l_id").val();
	Loading();
	//alert(group_id);
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "load_ledger",group_id:group_id },
		success: function(response)
		{
			$('#showledger_id').html(response);
			Unloading();
								
		}
	});
}
function report_ledger_detail() 
{
	
	var date=$("#rep_date").val();
	var l_id=$("#l_id").val();
	var showledger_id=$("#showledger_id").val();
	//alert(emp_id);
	//alert(l_id);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "generate_report_ledger_detail",date:date,l_id:l_id,showledger_id:showledger_id },
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			$('#data_table').html(response);
			Unloading();
								
		}
	});	
	
}

/*function report_ledger_form() 
{
	old
	var date=$("#rep_date").val();
	var emp_id=$("#l_id").val();
	//alert(emp_id);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/employee_expense/',
		data: { mode : "generate_report_emp_ledger",date:date,emp_id:emp_id },
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			$('#adv-table').html(response);
			Unloading();
								
		}
	});	
	
}*/
function report_ledger_form() 
{
	
	var date=$("#rep_date").val();
	var cust_id=$("#l_id").val();
	//alert(emp_id);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/cust_ledger/',
		data: { mode : "generate_report",date:date,cust_id:cust_id },
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			$('#adv-table').html(response);
			Unloading();
								
		}
	});	
	
}

function get_ledger_tree()
{
	 var treeData;
	   
	   $.ajax({
			type: "POST",  
			url: root_domain+administration_domain+'app/ledger/',
			data: { mode : "ledger_tree" },
		//	dataType: "json",			
			success: function(response)  
			{
				//alert(response);
				initTree(response);
				
			}   
	  });
	   
	  function initTree(treeData) {
		$('#treeview_json').treeview({data: treeData});
		
	  }
}

$(function() {
	var txt = $("input#emp_email");
	var func = function() {
		txt.val(txt.val().replace(/\s/g, ''));
	}
	txt.keyup(func).blur(func);
});

function checkUsername(uname)
{
	//alert(uname);
	var emp_email1=$('#emp_email_hid').val();
	
	if(emp_email1!=uname)
	{
		if(validemail(uname)){

			$.ajax({
					type: "POST",  
					url: root_domain+administration_domain+'app/ledger/',
					data: { mode : "check_username",uname:uname },
				//	dataType: "json",			
					success: function(response)  
					{
						//alert(response);
						if(response>0)
						{
							$('#user_error').html("<strong style='color:red'>Sorry.This Username Already Exist</strong><br>");
							$('#btn_submit').attr('disabled',true);
							
						}
						else
						{
							$('#user_error').html("<strong style='color:green'>Username Available</strong><br>");
							$('#btn_submit').attr('disabled',false);
						}
						//$('#user_error').html(response);
						
						//alert(response);
					}   
			  });
		}else{
			$('#user_error').html("<strong style='color:red'>Please insert valid email address</strong><br>");
			$('#btn_submit').attr('disabled',true);

		}
	}
}
function load_city_all(){
	var alloc_stateid = $('#alloc_stateid').val();
	
	$.ajax({
		type: "POST",  
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "load_city_all", alloc_stateid:alloc_stateid },
		success: function(response)  
		{
			//console.log(response);
			var resp=JSON.parse(response);
			$('#alloc_cityid').html(resp.html_resp);
			$('#alloc_cityid').select2("val","");
			
		}   
  });
}
function load_report_to_users(report_to_user_type){	
	$.ajax({
		type: "POST",  
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "load_report_to_users", report_to_user_type:report_to_user_type },
		success: function(response)  
		{
			//console.log(response);
			var resp=JSON.parse(response);
			$('#report_to_user_id').html(resp.html_resp);
			$('#report_to_user_id').select2("val","");
			
		}   
  	});
}

function changeGstText(cust_gst_reg){
	if(cust_gst_reg=='0'){
		$('#gst_div').show();
		$("#gst_no").prop('required',true);
	}
	else{
		$('#gst_div').hide();
		$('#gst_no').val("");
		$("#gst_no").prop('required',false);
	}
}

function add_tran_del()
{
	if($("#transport_id").val()==""){
		toastr.warning("Select Name", "ERROR");
		//$("#transport_id").focus();
		return false;
	}
	
	var transport_id=$('#transport_id').val();
	var cust_id=$('#ledger_id').val();
	
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "add_tran_del",edit_id:$("#edit_id_transport").val(),transport_id:$("#transport_id").val(),cust_id:cust_id },
		success: function(response)
		{
            if(response == '1'){
				//console.log(response);
				$("#edit_id_transport").val("");
				$("#transport_id").select2("val","");
				//$("#transport_id").val("");
				$("#add_tran_bt").val("Add");
				show_tran_data();
            } else if(response == '2'){
                toastr.warning("Trasportation exist", "ERROR");
            }
            Unloading();
			
		}
	});
	
}
function show_tran_data()
{
	var form_mode=$('#mode').val();
	var cust_id=$('#ledger_id').val();
	//alert(cust_id);
	//var mode=$('#mode').val();
	//alert(cust_id);
	//alert(form_mode);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "show_tran_data", cust_id:cust_id,form_mode:form_mode },
		success: function(data){
			//console.log(data);
			//alert(data);
			$('#table_trans_details').html(data);				
			Unloading();
		}		
	});
}
function delete_tran_data(id)
{
	var r= confirm(" Are you sure, you want to delete ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/ledger/',
				data: { mode : "delete_tran_data",  eid : id },
				success: function(response)
				{
					console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						show_tran_data();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}
					show_tran_data();
				
				}
			});	
		}
	
}
function edit_tran_data(id)
{
	
	//var form_mode=$("#jobwork_outward_add #mode").val();
	//alert(id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "edit_tran_data",  id : id },
		success: function(response)
		{
			//console.log(response);
			var data = jQuery.parseJSON(response);
			$("#transport_id").select2("val",data.transportation_id);
			$("#edit_id_transport").val(id);
			$("#add_tran_bt").val("Update");
			//show_contact_data();
			Unloading();
		}
	});
}

function get_depreciation_it_act()
{
	if($('#it_act_check').is(':checked'))
	{
		$('.it_act').show();
		$('.company_act').hide();
	}	
	else
	{
		$('.it_act').hide();
		$('.company_act').show();
	}
}

function add_salesman()
{
	var salesman_parent = $('#salesman_parent').val();
	var salesman_commision_mode = $('#salesman_commision_mode').val();
	var salesman_commision_percentage = $('#salesman_commision_percentage').val();
	var edit_salesman_id = $('#edit_salesman_id').val();
	var form_mode = $('#mode').val();
	var ledger_id = $('#ledger_id').val();
	
	if(salesman_commision_mode=='')
	{
		toastr.warning("Select Salesman Commision", "WARNING");
		return false;
	}
	else
	{
	
		$.ajax({
			
			type:'POST',
			data: { mode : "add_salesman",  salesman_parent : salesman_parent , salesman_commision_mode:salesman_commision_mode , salesman_commision_percentage:salesman_commision_percentage,ledger_id:ledger_id,edit_salesman_id:edit_salesman_id  },
			url:root_domain+administration_domain+'app/ledger/',
			success:function(result)
			{
				//alert(result);
				if(result==1)
				{
					toastr.success("INSERTED SUCCESSFULLY", "SUCCESS");
					$("#modal-salesman").modal("hide");
				}
				else if(result==3)
				{
					toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");
					$("#modal-salesman").modal("hide");
				}
				else
				{
					toastr.warning("Something Went Wrong", "ERROR");
				}
			}
		})
	}
	
}

function load_salesman_data()
{
	var ledger_id = $('#ledger_id').val();
	//alert(ledger_id);
	$.ajax({
		
		type:'POST',
		data: { mode:'load_salesman_data',ledger_id:ledger_id},
		url:root_domain+administration_domain+'app/ledger/',
		success:function(result)
		{
			//alert(result);
			var obj = JSON.parse(result);
			
			if(obj.count!=0)
			{
				$('#salesman_parent').select("val",obj.salesman_parent);
				$('#salesman_commision_mode').val(obj.salesman_commision_mode);
				$('#salesman_commision_percentage').val(obj.salesman_commision_percentage);
				$('#edit_salesman_id').val(obj.salesman_id);
			}
			else
			{
				$('#edit_salesman_id').val(0);
			}
		}
	})
}
function load_ledger_code(id,permis){
	
	if(permis == 0){
		$('#lcode_div').removeClass('hidden');
	}else if (permis == 1){
		$('#lcode_div').removeClass('hidden');
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/ledger/',
			data: { mode : "load_ledger_no", id : id},
			success: function(data){
				//console.log(data);
				var no = jQuery.parseJSON(data);				
				$('#ledger_code').val(no.ledgerno);
				$('#code_id').val(no.code_id);
				$("#ledger_code").attr('readonly','true');
			}
		});
	}else{
		$("#lcode_div").addClass("hidden");
	}
}

function check_manual_ledger_code(code){
	
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/ledger/',
		data: { mode : "check_manual_ledger_code", code : code},
		success: function(data){
			//console.log(data);
			var no = jQuery.parseJSON(data);
			if(no.error){
				toastr.warning(no.error, "ERROR");
				$('#ledger_code').val('');
				return false;

			}
		}
	});
	
}

function check_duplicate_ledger(ledger_name)
{
	//alert(ledger_name);
	var ledger_id = $('#ledger_id').val();

	$.ajax({

		type:'post',
		url:root_domain+administration_domain+'app/ledger/',
		data:{'ledger_name':ledger_name,'mode':'check_duplicate_ledger','ledger_id':ledger_id},
		success:function(result)
		{
			console.log(result);
			if(result==1)
			{
				$('.ledger_duplicate').html('Ledger With this name already exist');
				$('#btn_submit').prop('disabled',true);
			}
			else if(result==2)
			{
				$('.ledger_duplicate').html('This Name already exist in CRM Party Master');
				$('#btn_submit').prop('disabled',true);	
			}
			else
			{
				$('.ledger_duplicate').html('');	
				$('#btn_submit').prop('disabled',false);
			}
		}

	})
}
function get_party_by_ledger(tds_cat_id)
{
	//alert(tds_cat_id);
	var ledger_grp = $('#ledger_grp').val();
	var party_pay_cat = $('#party_pay_cat_text').val();
	$.ajax({

		type:'POST',
		data:{mode:'get_party_by_ledger',ledger_grp:ledger_grp,party_pay_cat:party_pay_cat,tds_cat_id:tds_cat_id},
		url:root_domain+administration_domain+'app/ledger/',
		success:function(result)
		{
			//alert(result);
			//console.log(result);
			$('#party_pay_cat').empty().append(result);
		}

	})
	//alert(ledger_grp);
}
function remove_vendor_pop(){
	$("#modal-add-ledger").modal('hide');
}

function get_tax_category_hsn_wise(id){
	var hsn_id = id;

	$.ajax({

		type:'POST',
		data:{mode:'get_tax_category',hsn_id:hsn_id},
		url:root_domain+administration_domain+'app/ledger/',
		success:function(result)
		{
			$('#sundry_gst').empty().append(result);
		}

	})
}

function add_ledger_doc_field() {
	if(!$("#led_doc_name").val()){		
		toastr.warning("Enter Document Name", "ERROR");
		$("#led_doc_name").focus();
		return false;
	}
	if(!$("#led_attch_file").val()){
		toastr.warning("Choose File", "ERROR");
		$("#led_attch_file").focus();
		return false;
	}
	
	Loading();
	var form_data = new FormData();
	form_data.append('mode', "add_ledger_doc_field");
	form_data.append('l_id', $("#ledger_id").val());
	form_data.append('led_doc_name', $("#led_doc_name").val());
	form_data.append("led_attch_file", document.getElementById('led_attch_file').files[0]);
	
	$.ajax({
		type: "POST",
		url: root_domain + administration_domain + 'app/ledger/',
		data: form_data,
		contentType: false,
		processData: false,
		success: function(response)
		{
			//console.log(response);
			$("#led_doc_name").val("").focus();
			$("#led_attch_file").val("");
			$('#led_attch_btn').val('Add');
			Unloading();
			show_led_attach_data();
		}
	});
}
function show_led_attach_data() {
	var eid = $('#ledger_id').val();
	var chkmode = $('#mode').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + administration_domain + 'app/ledger/',
		data: { mode : "show_led_attach_data", l_id:eid,modee:chkmode },
		success: function(resp){
			//console.log(resp);
			$('#led_attach_div').html(resp);
			Unloading();
		}		 
	}); 
}
function delete_led_attach_data(led_attach_id){
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + administration_domain + 'app/ledger/',
			data: { mode:"delete_led_attach_data", led_attach_id:led_attach_id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_led_attach_data();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
}


function load_typeswise_terms_dom(quot_type,ledger_id) 
{

	if(quot_type || quot_type==0) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+ administration_domain + 'app/ledger/',
			data: { mode : "load_typeswise_terms_dom", quot_type:quot_type, ledger_id:ledger_id },
			success: function(response)
			{
				var resp=JSON.parse(response);
				if(quot_type==1){
					$('#party_terms_cond_export_div').html(resp.resp_html);
				}else{
					$('#party_terms_cond_domestic_div').html(resp.resp_html);
				}
				Unloading();
			}
		});
	}
}

function load_typeswise_terms_exp(quot_type,ledger_id) 
{

	if(quot_type || quot_type==0) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+ administration_domain + 'app/ledger/',
			data: { mode : "load_typeswise_terms_exp", quot_type:quot_type, ledger_id:ledger_id },
			success: function(response)
			{
				var resp=JSON.parse(response);
				if(quot_type==1){
					$('#party_terms_cond_export_div').html(resp.resp_html);
				}else{
					$('#party_terms_cond_domestic_div').html(resp.resp_html);
				}
				Unloading();
			}
		});
	}
}

function terms_check_all_exp(obj){
	$('.terms_checkbox_exp').prop('checked', obj.checked);
}

function terms_check_all_dom(obj){
	$('.terms_checkbox_dom').prop('checked', obj.checked);
}