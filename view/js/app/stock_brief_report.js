$(document).ready(function () {
	generate_report_stock();
});

function generate_report_stock() {
	var date = $("#rep_date").val();
	var product_type = $("#product_type").val();
	var product_id = $("#product_id").val();
	var product_category = $("#product_category").val();

	var datatable = $("#stock-table").dataTable({
		"bAutoWidth": false,
		"bFilter": false,
		"bSort": false,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide": true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='" + root_domain + "img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"fnStateLoad": function (oSettings) {
			return JSON.parse(localStorage.getItem('offersDataTables'));
		},
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='" + root_domain + "img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[20, 50, 100, -1], [20, 50, 100, "All"]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + 'app/stock_brief_report/',
		"fnServerParams": function (aoData) {
			aoData.push(
				{ "name": "mode", "value": "generate_report_stock" },
				{ "name": "product_id", "value": product_id },
				{ "name": "product_type", "value": product_type },
				{ "name": "product_category", "value": product_category }
			);
		},
		"fnDrawCallback": function (oSettings) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
	$('.dataTables_length select').addClass('form-control');

	//Unloading();
	/*$.ajax({
		type: "POST",
		url: root_domain+'app/stock_brief_report/',
		data: { mode : "generate_report_stock",date:date,product_id:product_id },
		success: function(response)
		{
			//alert(response);
			console.log(response);
			$('#adv-table').html(response);
			Unloading();
								
		}
	}); */

}


function product_load(product_type = "") {

	var product_category = $("#product_category").val()
	var testData = [];
	$('#product_id').select2({
		data: testData
	})
	var inquiry_type = $("#inquiry_type").val();
	// var product_type=$("#product_type").val();
	//$("#product_id").html("");
	var mainurl = root_domain + crm_domain + 'app/product_load/index.php?mode=product_load&inquiry_type=' + inquiry_type + '&type=production_pro_type&search=bom_pro_search&product_type=' + product_type + '&product_category=' + product_category;
	$.getJSON(mainurl, function (json) {
		// console.log(json);
		var arr = new Array();
		var len = json[0].length;
		// console.log(len);

		for (var i = 0; i < len; i++) {
			testData.push({ id: json['0'][i], text: json['1'][i] });
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
	query: function (q) {
		var pageSize,
			results,
			that = this;
		pageSize = 20; // or whatever pagesize
		results = [];
		if (q.term && q.term !== '') {
			// HEADS UP; for the _.filter function i use underscore (actually lo-dash) here
			results = _.filter(that.data, function (e) {
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


function exportCsv() {
	var product_type = $("#product_type").val();
	var product_id = $("#product_id").val();
	var product_category = $("#product_category").val();

	var url = root_domain + 'generate_export?mode=report_stock&product_type=' + encodeURIComponent(product_type) + "&product_id=" + encodeURIComponent(product_id) + "&product_category=" + encodeURIComponent(product_category);
	window.location.href = url;
}
