$(document).ready(function() {
	load_state_mst_datatable();	        
dm();
select_data();
// validate vendor add form on keyup and submit
$("#state_add").validate({
	rules: {
		
		countryid: {
			required: true
		},
		state_name: {
			required: true,
			minlength: 3
		}
	},
	messages: {
		countryid: {
			required: "Select Country"			
		},
		state_name: {
			required: "Enter State Name",
			minlength: "Your State Name must consist of at least 3 characters"
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditstate").validate({
	rules: {
		countryid: {
			required: true
		},
		state_name: {
			required: true,
			minlength: 3
		}

	},
	messages: {
		countryid: {
			required: "Select Country"			
		},
		state_name: {
			required: "Enter State Name",
			minlength: "Your State Name must consist of at least 3 characters"
		}
	}
});		

});
$("#state_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#state_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading();	
	$(this).attr("disabled","disabled");		
	
	var token=  $("#token").val();
	var country=$("#countryid").val();
	var branch_id=$("#abranch_id").val();
	var product_id=$("#product_id").val();

	var form_data = {
		state_name: $("#state_name").val(),
		gst_state_code: $("#gst_state_code").val(),
		countryid: country,
		branch_id: branch_id,
		model: $("#model").val(),	
		token:token,
		product_id:product_id,
		mode:$("#mode").val(),
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/droupdown_data_manage_test/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			var obj=jQuery.parseJSON(response);
				response=obj.res;
			if(response.trim() == '1') {				
				toastr.success("STATE ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				load_state_mst_datatable();
			}
			else if(response.trim() == '2') {
				toastr.success("STATE ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-example-modal-state").modal("hide");
				$('#stateid').append('<option value='+obj.stateid+'>'+obj.state_name+'</option>');
				$('#stateid').select2("val",obj.stateid);
				$("#stateid").trigger('change')
				$('#state_add').trigger('reset');
				Unloading();
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response.trim() == '-1')
			{
				$("#bs-example-modal-state").modal("hide");
				$('#state_add').trigger('reset');
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#state_add').trigger('reset');
			$("#product_id").trigger('change')
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditState").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditState").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading();	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		countryid: $("#edit_countryid").val(),
		state_name: $("#edit_state_name").val(),
		gst_state_code: $("#edit_gst_state_code").val(),
		branch_id: $("#e_branch_id").val(), 
		token:$("#edit_token").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/droupdown_data_manage_test/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("STATE UPDATED SUCCESSFULLY", "SUCCESS");
				load_state_mst_datatable();	
				Unloading();						
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$("#ModalEditAccount").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function delete_reload()
{
	load_state_mst_datatable();	
}
function delete_state(id) 
{
	var r= confirm(" Are you sure want to delete ?");
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/droupdown_data_manage_test/',
			data: { mode : "delete", token :  $("#token").val(), eid : id },
			success: function(response)
			{
				
				if(response.trim() == "1") {
					toastr.success("STATE DELETE SUCCESSFULLY", "SUCCESS");
					delete_reload();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function edit_test(id)
{
	Loading();
	editReq = $.ajax({
		type: "POST",
		url: root_domain+'app/droupdown_data_manage_test/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(id);				
			$("#edit_countryid").select2("val",obj.countryid);
			$("#edit_state_name").val(obj.state_name);
			$("#edit_gst_state_code").val(obj.gst_state_code);
			$("#e_branch_id").select2("val", obj.branch_id);
			//$("#product_id").select2("val", obj.gst_state_code);
			$("#product_id").val(obj.gst_state_code);
			
			Unloading();
		}
	});	
}

function load_state_mst_datatable(){
	var branch_id = $('#branch_id').val();

	datatable = $("#dynamic-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bServerSide" : true,
			"bDestroy" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO STATE ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
			"iDisplayLength": 30,
			"sAjaxSource": root_domain+'app/droupdown_data_manage_test/',
			"fnServerParams": function ( aoData ) {
				aoData.push( 
					{ "name": "mode", "value": "fetch" },
					{"name": "branch_id", "value": branch_id }
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




// Function to shuffle the demo data
function shuffle(str) {
  return str
    .split('')
    .sort(function() {
      return 0.5 - Math.random();
    })
    .join('');
}

// For demonstration purposes we first make
// a huge array of demo data (20 000 items)
// HEADS UP; for the _.map function i use underscore (actually lo-dash) here
function mockData() {
  return _.map(_.range(1, 20000), function(i) {
	 // console.log(abc);
    return {
      id: i,
      text: shuffle('te ststr ing to shuffle') + ' ' + i,
    };
  });
}



function dm(){
	var testData = [];
	var mainurl = root_domain+'app/droupdown_data_manage_test/index.php?mode=product_load&c_year=1';
		$.getJSON(mainurl, function(json) {
		var arr=new Array();
			var len=json[0].length;
			console.log(len);
			
			for(var i=0;i<len;i++)
			{	
				testData.push({ id:json['0'][i] ,text: json['1'][i]});
				//alert(json['1'][i]);
			}
		});
		
	return testData;
}

$('#product_id').select2({
	data: dm(),
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
  
 
function select_product(){
alert("ds");
	$('#product_id').val(1);
		//$("#product_id").select2('data', { id:1, title: "UPS 3200B"});
}
