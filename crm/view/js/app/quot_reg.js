$(document).ready(function() {
	load_inquiry_reg_datatable();
	product_load();
});

function load_inquiry_reg_datatable(){
	var date = $("#rep_date").val();
	var cust_id  = $('#cust_id').val();
	var product_id =  $('#product_id').val();
	var quotation_id  = $('#quotation_id').val();
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
		"sAjaxSource": root_domain+crm_domain+'app/quot_reg/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "date", "value": date },{ "name": "cust_id", "value": cust_id },{ "name": "product_id", "value": product_id },{ "name": "quotation_id", "value": quotation_id } );
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

function product_load(){
	
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	var product_category='';
	var cat = '';

	if(comp_config.cat_wise_product_load==1){
		product_category = $("#cat_id").val();
		cat = '&product_category='+product_category;
	}

	if(inquiry_type == 2)
	{
		$('#product_rate').attr('readonly', true);
	}
	else
	{
		
		$('#product_rate').attr('readonly',false);
	}
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=crm_pro_type&search=crm_pro_search&product_category='+product_category;
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
	load_cat_product('product_id', testData)	
	// return testData;
}

function load_cat_product(id, testData){
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