var datatable;
$(document).ready(function() {
		datatable = $("#dynamic-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bServerSide" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO UserType ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/permissionmst/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" } );
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
	// validate the comment form when it is submitted        

// validate vendor add form on keyup and submit
$("#permission_add").validate({
	rules: {
	usertype_id: {
			required: true
		}
	},
	messages: {
	usertype_id: {
			required: "Select User Type Name"
	}
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditpermission").validate({
	rules: {
	usertype_id: {
			required: true
	},
	menu_id: {
			required: true
		},
	permission: {
			required: true
		}
	},
	messages: {
	usertype_id: {
			required: "Select User Type Name"
	},
	menu_id: {
			required: "Select Menu"
		},
	permission: {
			required: "Select Permission"
		}
	}
});		

});
$("#permission_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#permission_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = new FormData(this);
	$.ajax({
		cache:false,
		url: root_domain+'app/permissionmst/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {				
				toastr.success("Permission ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				window.location=root_domain+'user_permission';
				$('#permission_add').trigger('reset');
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
			$('#permission_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function load_menu(id)
{
		Loading(true);
		editReq = $.ajax({
			type: "POST",
			url: root_domain+'app/permissionmst/',
			data: { mode : "show_menu", id : id },
			success: function(response)
			{
				//console.log(response);
				$("#show_menu").html(response);
				Unloading();
			}
		});	
}
function submenuactive(i)
{
	var totalmenu = $("#totalmenu").val();
	//for(var i=0;i<=totalmenu;i++)
	{
		if($(".mainmenu"+i).prop("checked")==true)
		{
			$('.submenu'+i).prop("checked",true)
		}
		else
		{
			$('.submenu'+i).prop("checked",false)
		}
		
	}
}
function add_menuactive(i)
{
	var totalmenu = $("#totalmenu").val();
	{
		if($(".addmain"+i).prop("checked")==true)
		{
			$('.addsubmenu'+i).prop("checked",true)
		}
		else
		{
			$('.addsubmenu'+i).prop("checked",false)
		}
		
	}
}
function other_menuactive(i)
{
	var totalmenu = $("#totalmenu").val();
	{
		if($(".othermain"+i).prop("checked")==true)
		{
			$('.othersubmenu'+i).prop("checked",true)
		}
		else
		{
			$('.othersubmenu'+i).prop("checked",false)
		}
		
	}
}
function edit_menuactive(i)
{
	var totalmenu = $("#totalmenu").val();
	//for(var i=0;i<=totalmenu;i++)
	{
		if($(".editmain"+i).prop("checked")==true)
		{
			$('.editsubmenu'+i).prop("checked",true)
		}
		else
		{
			$('.editsubmenu'+i).prop("checked",false)
		}
		
	}
}
function delete_menuactive(i)
{
	var totalmenu = $("#totalmenu").val();
	//for(var i=0;i<=totalmenu;i++)
	{
		if($(".deletemain"+i).prop("checked")==true)
		{
			$('.deletesubmenu'+i).prop("checked",true)
		}
		else
		{
			$('.deletesubmenu'+i).prop("checked",false)
		}
		
	}
}
function aprv_menuactive(i)
{
	var totalmenu = $("#totalmenu").val();
	//for(var i=0;i<=totalmenu;i++)
	{
		if($(".aprvmain"+i).prop("checked")==true)
		{
			$('.aprvsubmenu'+i).prop("checked",true)
		}
		else
		{
			$('.aprvsubmenu'+i).prop("checked",false)
		}
		
	}
}
function final_menuactive(i)
{
	var totalmenu = $("#totalmenu").val();
	//for(var i=0;i<=totalmenu;i++)
	{
		if($(".finalmain"+i).prop("checked")==true)
		{
			$('.finalsubmenu'+i).prop("checked",true)
		}
		else
		{
			$('.finalsubmenu'+i).prop("checked",false)
		}
		
	}
}