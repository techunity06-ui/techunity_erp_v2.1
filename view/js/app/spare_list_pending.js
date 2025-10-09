$(document).ready(function() {
	load_spare_pending_datatable();
}); 

function load_spare_pending_datatable(){
	var sp_sent_status=$('input[name="sp_sent_status"]:checked').val();//
	var s_inv_status=$('#s_inv_status').val();//
	
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
            localStorage.setItem('pending-spare-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('pending-spare-datatable'));
        },
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[ 10, 20, 50, 100, -1], [ 10, 20, 50, 100, "All"]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+'app/spare_list_pending/',
		"fnServerParams": function ( aoData ) {
			aoData.push( {"name": "mode", "value": "fetch" }, {"name": "sp_sent_status", "value": sp_sent_status }, {"name": "s_inv_status", "value": s_inv_status } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}
