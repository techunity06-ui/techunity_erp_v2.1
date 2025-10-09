$(document).ready(function() {
	load_inquiry_reg_datatable();
	product_load_pro_l();
});

function load_inquiry_reg_datatable(){
	var date = $("#rep_date").val();
	var cust_id = $("#cust_id").val();
	var product_id = $("#product_id").val();
	var sales_order_id = $("#sales_order_id").val();
	$("#inq-reg-datatable").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"oLanguage": {
				"sLengthMenu": "_MENU_",
				"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
				"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+crm_domain+'app/so_summary/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "date", "value": date },{ "name": "cust_id", "value": cust_id },{ "name": "product_id", "value": product_id },{ "name": "sales_order_id", "value": sales_order_id } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		},
		"fnFooterCallback": function ( nRow, aaData, iStart, iEnd, aiDisplay ) {
			var iQty = 0;
			var iRate = 0;
			var iValue = 0;
			for ( var i=0 ; i<aaData.length ; i++ )
			{
				iQty += aaData[i][7]*1;
				iRate += aaData[i][9]*1;
				iValue += aaData[i][10]*1;
			}

			var nCells = nRow.getElementsByTagName('th');
			nCells[1].innerHTML = parseFloat(iQty ).toFixed(4);
			nCells[3].innerHTML = parseFloat(iRate ).toFixed(2);
			nCells[4].innerHTML = parseFloat(iValue ).toFixed(2);
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function product_load_pro_l(){
	
	var testData = [];
	
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load';
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		var len=json[0].length;
			////console.log(len);
			
			for(var i=0;i<len;i++)
			{	
				testData.push({ id:json['0'][i] ,text: json['1'][i]});
				////alert(json['1'][i]);
			}
		});
	load_cat_product_so('product_id', testData);
	// return testData;
}

function load_cat_product_so(id, testData){
	
	$('#'+id).select2({
		data: testData,
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
}