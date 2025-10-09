$(document).ready(function() {
	product_load();
	inqiuiry_not_followup_detail_report();
});


function inqiuiry_not_followup_detail_report() {
	var date = $('#fil_due_date').val();
	var cust_id = $("#crm_cust_id").val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/inquiry_not_followup_report/',
		data: { mode : 'inqiuiry_not_followup_detail_report',date:date, cust_id:cust_id},		
	   success: function(response)
		{
			$('#inquiry_notfollowup_detail').html(response);
		}
	});	
}

function product_load(){
	
	var testData = [];
	var inquiry_type=1;
	var product_category='';
	

	
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=crm_pro_type&search=crm_pro_search';
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

function load_more(){
	var row = Number($('#row').val());
    var allcount = Number($('#all').val());
    var date = $('#fil_due_date').val();
	var cust_id = $("#crm_cust_id").val();
    row = row + 30;
    if(row <= allcount){
        $("#row").val(row);

        $.ajax({
            url: root_domain + crm_domain +'app/inquiry_not_followup_report/',
            type: 'POST',
            data: {mode : 'load_more_inqiuiry_not_followup_detail_report',row:row,date:date,cust_id:cust_id},
            beforeSend:function(){
                $(".load-more").text("Loading...");
            },
            success: function(response){

                // Setting little delay while displaying new content
                setTimeout(function() {
                    // appending posts after last post with class="post"
                    $(".post:last").after(response).show().fadeIn("slow");

                    var rowno = row + 30;

                    // checking row value is greater than allcount or not
                    if(rowno > allcount){

                        // Change the text and background
                        $('.load-more').text("Hide");
                        $('.load-more').css("background","darkorchid");
                    }else{
                        $(".load-more").text("Load more");
                    }
                }, 2000);


            }
        });
    }else{
        $('.load-more').text("Loading...");

        // Setting little delay while removing contents
        setTimeout(function() {

            // When row is greater than allcount then remove all class='post' element after 3 element
            $('.post:nth-child(30)').nextAll('.post').remove().fadeIn("slow");

            // Reset the value of row
            $("#row").val(0);

            // Change the text and background
            $('.load-more').text("Load more");
            $('.load-more').css("background","#15a9ce");

        }, 2000);


    }
}