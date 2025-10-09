$(document).ready(function() {
	load_spare_pending_datatable();
}); 

function load_spare_pending_datatable(){
	var s_return_status=$('input[name="s_return_status"]:checked').val();//
	
	$("#pending-spare-datatable").dataTable({
		//Amish Soni 04-09-2020
		"bStateSave": true,
		
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide" : true,
		"bStateSave": true,
        "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('receive-spare-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('receive-spare-datatable'));
        },
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[ 10, 20, 50, 100, -1], [ 10, 20, 50, 100, "All"]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+service_domain+'app/return_old_spare/',
		"fnServerParams": function ( aoData ) {
			aoData.push( {"name": "mode", "value": "fetch" }, {"name": "s_return_status", "value": s_return_status } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}
