$(document).ready(function() {
		reload_data();
		var product_id = $("#sel_product_id").val();
        var product_name = $("#product_name").val();
        
        if(product_id){
			  $("#prod_id").select2('data', { id:product_id, text: product_name});
			  $("#prod_id").trigger('change');
        }
});
function reload_data()
{
	generate_report();
}

function generate_report() 
{
	var date=$("#rep_date").val();
	var prod_id=$("#prod_id").val();
	if(prod_id!="")
	{
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+report_domain+'app/reserve_stock_report/',
		data: { mode : "generate_report", date :  date,prod_id:prod_id},
		success: function(response)
		{
			//console.log(response);
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
										
		}
	});	
	}
}


function product_load(){
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	// var product_type=$("#product_type").val();
	//$("#prod_id").html("");
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

$('#prod_id').select2({
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

        var product_id = $("#sel_product_id").val();
        var product_name = $("#product_name").val();
        
        if(product_id){
			  $("#prod_id").select2('data', { id:product_id, text: product_name});
        }
	},
});