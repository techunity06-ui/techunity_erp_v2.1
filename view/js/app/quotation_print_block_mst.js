$(document).ready(function() {
	load_quotation_print_block_mst_datatable();       

// validate vendor add form on keyup and submit
$("#quotation_print_block_mst_add").validate({
	rules: {
		block_name: {
			required: true
		},
		block_type:{
			required: true
		}
	},
	messages: {
		block_name: {
			required: "Enter Block Name"			
		},
		block_type: {
			required: "Choose Block Type"
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditquotation_print_block_mst").validate({
	rules: {
		e_block_name: {
			required: true
		},
		e_block_type:{
			required: true
		}
	},
	messages: {
		e_block_name: {
			required: "Enter Block Name"			
		},
		e_block_type: {
			required: "Choose Block Type"
		}
	}
});		

});
$("#quotation_print_block_mst_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#quotation_print_block_mst_add").valid()) {
		return false;
	}
	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		block_name: $("#block_name").val(),
		block_formate: $("#block_formate").val(),
		block_type: $("#block_type").val(),
		mode:"Add",
		quotation_print_block_mst_add : $("#quotation_print_block_mst_add").val(),
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain +'app/quotation_print_block_mst/',
		type: "POST",
		data: form_data,
		success: function(responses)
		{
			//console.log(response);
			var resp=JSON.parse(responses);
			var response = resp.resp;
			if(response.trim() == '1') {				
				toastr.success("QUOTATION PRINT BLOCK ADDED SUCCESSFULLY", "SUCCESS");
				Unloading();
				load_quotation_print_block_mst_datatable();
				$('#block_name').val('');
				$('#block_formate').val('');
				$("#block_type").select2("val", "");
			}
			else if(response.trim() == '2') {
				toastr.success("QUOTATION PRINT BLOCK ADDED SUCCESSFULLY", "SUCCESS");
				$("#FormEditquotation_print_block_mst").modal("hide");
				//$('#product_group').append('<option value='+resp.quotation_print_block_id+'>'+resp.block_name+'</option>');	
				//$('#block_name').val(resp.block_name);
				//$("#product_group").trigger('change');
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
			$('#quotation_print_block_mst_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditquotation_print_block_mst").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditquotation_print_block_mst").valid()) {
		return false;
	}
	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		e_block_formate: $("#e_block_formate").val(),
		e_block_name: $("#e_block_name").val(),
		e_block_type: $("#e_block_type").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain + 'app/quotation_print_block_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				toastr.success("QUOTATION PRINT BLOCK UPDATED SUCCESSFULLY", "SUCCESS");
				load_quotation_print_block_mst_datatable();
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
			$("#ModalEditquotation_print_block_mst").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_quotation_print_block_mst(id) 
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + 'app/quotation_print_block_mst/',
			data: { mode : "delete", eid : id },
			success: function(response)
			{
				
				if(response.trim() == "1") {
					toastr.success("QUOTATION PRINT BLOCK DELETE SUCCESSFULLY", "SUCCESS");
					load_quotation_print_block_mst_datatable();
				}
				else if(response.trim() == "-1") {
					toastr.error("USED QUOTATION PRINT BLOCK CAN'T BE DELETED !!!", "WARNING"); 
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
}
function edit_quotation_print_block_mst(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain + 'app/quotation_print_block_mst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#ModalEditquotation_print_block_mst").modal("show");
			$("#edit_id").val(obj.quotation_print_block_id);
			CKEDITOR.instances['e_block_formate'].setData(obj.block_formate);
			$("#e_block_type").select2("val", obj.block_type);
			$("#e_block_name").val(obj.block_name);
			Unloading();
		}
	});	
}
function load_quotation_print_block_mst_datatable(){
	$("#quotation_print_block_mst-datatable").dataTable({
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
		"sAjaxSource": root_domain + 'app/quotation_print_block_mst/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" }
				);
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function loadEditor(module_id, id) {
	if (CKEDITOR.instances.id) {
		CKEDITOR.instances.id.destroy();
	 }
	/*Email Content Section*/
	CKEDITOR.replace( id, {
		height: 300,
		toolbarGroups: [
			{ name: 'mode' },
			{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
			{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
			{ name: 'links' },
			{ name: 'insert' },
			{ name: 'forms' },
			{ name: 'tools' },
			{ name: 'document',       groups: [ 'mode', 'document', 'doctools' ] },
		    { name: 'others' },
			{ name: 'paragraph',   groups: [ 'list', 'align'] },
			{ name: 'styles' },
			{ name: 'colors' },
			{ name: 'about' },
			{ name: 'basicstyles' },
		],    
		on: {
			pluginsLoaded: function() {
				var editor = this,
					config = editor.config;
   
				var tags = [];
				
				$.ajax({
				  type: "POST",
				  url: root_domain+'app/quotation_print_block_mst/',
				  data: { mode : "get_insert_tags_data", 'module_id': module_id },
				  success: function(response)
				  {
					tags = jQuery.parseJSON(response);
				  }
				});  
				
				editor.ui.addRichCombo( 'insert_tag', {
					label: 'Insert Merge Fields',
					title: 'Insert Merge Fields',
					toolbar: 'basicstyles',
					className: 'brp_select',
			
					panel: {               
						css: [ CKEDITOR.skin.getPath( 'editor' ) ].concat( config.contentsCss ),
						multiSelect: false,
						attributes: { 'aria-label': 'Insert Merge Fields' }
					},
					
					init: function() {    
						this.startGroup( 'Insert Fields' );
						for (var this_tag in tags){
						  this.add(tags[this_tag][0], tags[this_tag][1]);
						}
					},
					onClick: function( value ) {
						editor.focus();
						editor.fire( 'saveSnapshot' );
						editor.insertHtml( value );
						editor.fire( 'saveSnapshot' );
					}
				} );        
			}        
		}
	} );
}