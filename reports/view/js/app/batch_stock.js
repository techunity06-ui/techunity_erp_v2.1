//var datatable;
$(document).ready(function() {
	var product_id = $("#sel_product_id").val();
        var product_name = $("#product_name").val();
        
        if(product_id){
			  $("#product_id").select2('data', { id:product_id, text: product_name});
			  $("#product_id").trigger('change');
        }
	generate_report_stock(product_id);

});



function generate_report_stock(product_id = "") 
{
	var date=$("#rep_date").val();
	if(product_id == ""){
		product_id=$("#product_id").val();
	}
	
	var batch_no = $("#batch_no").val();
	
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+report_domain+'app/batch_stock/',
		data: { mode : "generate_report_stock", date :  date,product_id:product_id,date:date,batch_no:batch_no},
		success: function(response)
		{
			//console.log(response);
			if(response != "") {
				$('#adv-table').empty().html(response);
					$('.ttip, [data-toggle="tooltip"]').tooltip();
				Unloading();
			}
		}
	});	
}

/*function generate_report_stock() 
{
	
	var date=$("#rep_date").val();
	var product_id=$("#product_id").val();
	var batch_no = $("#batch_no").val();
	//alert(emp_id);
	
	var datatable = $("#stock-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : false,
			"bSort" : true,
			"bProcessing": true,
			"bDestroy": true,
			"bServerSide" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO DATA ADDED YET !",
			},
			 "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('offersDataTables'));
			},
			"oLanguage": {
				"sLengthMenu": "_MENU_",
				"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
				"sEmptyTable": "NO DATA ADDED YET !",
			},
			"aLengthMenu": [[20, 50, 100, -1], [20, 50, 100,"All"]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+report_domain+'app/batch_stock/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "generate_report_stock" },{ "name": "product_id", "value": product_id },{"name":"date","value":date},{"name":"batch_no","value":batch_no} );
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');

}*/

function load_batch_no(product_id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+report_domain+'app/batch_stock/',
		data: { mode : "load_batch_no", product_id:product_id},
		success: function(responce){
			$('#batch_no').html(responce);
			$("#batch_no").select2("val","");
			Unloading();
		}
	});
}

function product_load(){
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	// var product_type=$("#product_type").val();
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=production_pro_type&search=bom_pro_search';
	$.getJSON(mainurl, function(json) {
		// console.log(json);
		var arr=new Array();
		var len=json[0].length;
		// console.log(len);

		for(var i=0;i<len;i++)
		{	
			testData.push({ id:json['0'][i] ,text: json['1'][i]});
				//alert(json['1'][i]);
			}
		});

	return testData;
}

$('#product_id').select2({
	data: product_load(),
	placeholder: 'search',
	multiple: false,
    // query with pagination
    query: function(q) {
    	var pageSize,
    	results,
    	that = this;
      	pageSize = 20; // or whatever pagesize
      	results = [];
      	if (q.term && q.term !== '') {
        	// HEADS UP; for the _.filter function i use underscore (actually lo-dash) here
        	results = _.filter(that.data, function(e) {
        		return e.text.toUpperCase().indexOf(q.term.toUpperCase()) >= 0;
        	});
        } else if (q.term === '') {
        	results = that.data;
        }
        q.callback({
        	results: results.slice((q.page - 1) * pageSize, q.page * pageSize),
        	more: results.length >= q.page * pageSize,
        });
	  //$("#product_id").select2('data', { id:1, title: "UPS 3200B"});
	},
});