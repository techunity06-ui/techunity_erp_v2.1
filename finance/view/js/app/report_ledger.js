$(document).ready(function() {
	
	report_ledger();
	load_all_ledger_datatable();
});
	

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

function report_ledger_form() 
{
	
	var date=$("#rep_date").val();
	var cust_id=$("#l_id").val();
	//alert(emp_id);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/cust_ledger/',
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

function load_all_ledger_datatable(){
	
	var date=$("#rep_date").val();
	var showledger_id=$("#showledger_id").val();
	//alert(date);
	
	datatable = $("#all_ledger").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bServerSide" : true,
			"bDestroy" : true,
			"bStateSave": true,
			"aoColumnDefs": [
			  {
			     "bSortable": false,
			     "aTargets": [0, -1,-2 ]
			  }
			],
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO DATA ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50, -1], [10, 20, 30, 50, "All"]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+administration_domain+'app/ledger/',
			"fnServerParams": function ( aoData ) {
				aoData.push( 
					{ "name": "mode", "value": "fetch_all_ledger" },
					{ "name": "date", "value": date },
					{ "name": "showledger_id", "value": showledger_id }
				);
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
