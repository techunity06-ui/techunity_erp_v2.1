$(document).ready(function() {
	load_email_datatable();
	
	// validate vendor add form on keyup and submit
	$("#email_template_add").validate({
		rules: {
			template_title: {
				required: true			
			},
			email_module_id: {
				required: true			
			},
			email_subject: {
				required: true			
			},
			email_content: {
				required: true			
			},
			task_id: {
				required: function(){
					return $("#email_module_id").val() == 2 && $('#crm_auto_mail').val() != 'No';
				}
			},
			stage_id: {
				required: function(){
					return $("#email_module_id").val() == 2 && $('#crm_auto_mail').val() != 'No';
				}
			}
		},
		messages: {
			template_title: {
				required: "Enter Template Title"
			},
			email_module_id: {
				required: "Please Select Module"
			},
			email_subject: {
				required: "Enter Email Subject"
			},
			email_content: {
				required: "Enter Email Content"
			},
			task_id: {
				required: "Please Select Task"
			},
			stage_id: {
				required: "Please Select Stage"
			}
		}
	}); 
});

$("#email_template_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#email_template_add").valid()) {
		return false;
	}
	var email_content= (CKEDITOR.instances.email_content.getData());
	if(email_content==''){
		toastr.warning("PLEASE ADD EMAIL CONTENT.", "WARNING");
		$("#email_content").focus();
		return false;
	}
	
	for (instance in CKEDITOR.instances) 
	{
    	CKEDITOR.instances[instance].updateElement();
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+'app/email_template/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//console.log(response);	
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("TEMPLATE ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+arr.back;
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if(arr.msg == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO");
				Unloading();
			}
			else if(arr.msg== '2')
			{	
				toastr.success("TEMPLATE UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location=root_domain+'email_template_list';
				
			}
			$('#email_template_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
});

function load_email_datatable(){
	var module_id=$('#module_id').val();
	
	datatable = $("#dynamic-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bDestroy": true,
			"bServerSide" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO DATA ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/email_template/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },{ "name": "module_id", "value": module_id });
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}

function delete_email(id){
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/email_template/',
				data: { mode : "delete_email_template",  email_sms_id : id  },
				success: function(response)
				{
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						load_email_datatable()
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}	
}

function change_email_template_status(id, status){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/email_template/',
		async:false,
		data: { mode : 'change_email_template_status', email_sms_id : id, status : status },
		success: function(response){

			var data=jQuery.parseJSON(response)
			var response=data.res;
			if(response.trim() == "1") {
				toastr.success("STATUS UPDATED SUCCESSFULLY", "SUCCESS");
				load_email_datatable()
			}
			else if(response.trim() == "0") {
				toastr.warning("SOMETHING WRONG", "WARNING");
			}	
		}		
	});
	Unloading();
}

function loadEditor(module_id) {
	if (CKEDITOR.instances.email_content) {
		CKEDITOR.instances.email_content.destroy();
	 }
	/*Email Content Section*/
	CKEDITOR.replace( 'email_content', {
		height: 300,
		toolbarGroups: [
			// { name: 'mode' },
			// { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
			// { name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
			// { name: 'links' },
			// { name: 'insert' },
			// { name: 'forms' },
			// { name: 'tools' },
			// { name: 'document',       groups: [ 'mode', 'document', 'doctools' ] },
		   /* { name: 'others' },*/
			'/',
			{ name: 'paragraph',   groups: [ 'list', 'align'] },
			{ name: 'styles' },
			// { name: 'colors' },
			// { name: 'about' },
			 '/',
			{ name: 'basicstyles' },
		],    
		on: {
			pluginsLoaded: function() {
				var editor = this,
					config = editor.config;
   
				var tags = [];
				
				$.ajax({
				  type: "POST",
				  url: root_domain+'app/email_template/',
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
