var datatable;
$(document).ready(function() {
    $('.subgroups').hide();
    /*datatable = $("#dynamic-table").dataTable({
            "bAutoWidth" : false,
            "bFilter" : true,
            "bSort" : true,
            "bProcessing": true,
            "bServerSide" : true,
            "oLanguage": {
                            "sLengthMenu": "_MENU_",
                            "sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
                            "sEmptyTable": "NO DATA ADDED YET !"
            },
            "aLengthMenu": [[10, 50, 100, 300, 500], [10, 50, 100, 300, 500]],
            "iDisplayLength": 10,
            "sAjaxSource": root_domain+'app/chart_account/',
            "fnServerParams": function ( aoData ) {
                    aoData.push( { "name": "mode", "value": "fetch" } );
            },
            "fnDrawCallback": function( oSettings ) {
                    $('.ttip, [data-toggle="tooltip"]').tooltip();
            }
    }).fnSetFilteringDelay();

    //Search input style
    $('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
    $('.dataTables_length select').addClass('form-control');*/

// validate the comment form when it is submitted        
// validate vendor add form on keyup and submit
$("#group_add").validate({
	rules: {
		g_name:{
			required:true
		}
	},
	messages: {
		g_name:{
			required:"Enter Group Name"	
		}
	}
}); 
});
$("#group_add").on('submit',function(e) {
        
        $("#g_name").val()
        
	//alert('hiii');
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#group_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var g_name=$("#g_name").val();
	var g_parent=$("#g_parent").val();
	var g_form=$("#g_form").val();
	
	//alert($("#mode").val());
	var form_data = {
		g_name: g_name,
		g_parent: g_parent,
		g_form: g_form,
		mode: 'add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+finance_root_domain+'app/chart_account/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			var resp = JSON.parse(response);
                        var msg= resp.msg;
			if(msg.trim() == '1') {				
				toastr.success("Group ADDED SUCCESSFULLY", "SUCCESS");
                                $("#subgroup_"+g_parent).html(resp.html);
                                $("#subgroup_"+g_parent).show();
                                /*if($("#subgroup_"+g_parent).length){
                                    $("#subgroup_"+g_parent).remove();
                                    $("#li_"+g_parent).after(resp.html);
                                    $("#subgroup_"+g_parent).show();
                                } else {
                                    //add_subgroup(g_parent);
                                    $("#li_"+g_parent).after(resp.html);
                                    $("#subgroup_"+g_parent).show();
                                }*/
                                Unloading();
			}
			else if(msg.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if(msg.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO");
				Unloading();				
			}
			$("#ModalAddAccount").modal("hide");
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

$("#group_edit").validate({
    rules: {
            e_g_name:{
                    required:true
            }
    },
    messages: {
            e_g_name:{
                    required:"Enter Group Name"	
            }
    }
});

$("#group_edit").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#group_edit").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");
        var group_id = $("#edit_id").val();
        var group_name = $("#e_g_name").val();
	var form_data = {
		eid : group_id,
		e_g_name: group_name,
		e_g_parent: $("#e_g_parent").val(),
		e_g_form: $("#e_g_form").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+finance_root_domain+'app/chart_account/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			var data = JSON.parse(response);
			var response = data.msg;
			if(response.trim() == '1') {
				toastr.success("GROUP UPDATED SUCCESSFULLY", "SUCCESS");
                                $('#group_name_'+group_id).html(group_name);
				Unloading();						
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response.trim() == '-1')
			{
				toastr.info("GROUP ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$("#ModalEditAccount").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function edit_group(id)
{
    Loading(true);
    $.ajax({
        type: "POST",
        url: root_domain+finance_root_domain+'app/chart_account/',
        data: { mode : "preedit", id : id },
        success: function(response)
        {
                //console.log(response);
                var obj = jQuery.parseJSON(response);
                $("#ModalEditAccount").modal("show");
                $("#edit_id").val(obj.g_id);
                $("#e_g_parent").val(obj.g_pid);
                $("#e_parent_name").html('<strong>'+obj.parent_name+'</strong>');
                //$("#e_g_parent").select2("val",obj.g_pid);
                //$("#e_g_parent").select2("readonly",true);
                $("#e_g_name").val(obj.g_name);
                $("#e_g_form").val(obj.g_id);	
                Unloading();
        }
    });	
}

function add_group(id){
    $("#ModalAddAccount").modal("show");
    $("#g_parent").val(id);
    parent_name = $('#group_name_'+id).text();
    $("#parent_name").html('<strong>'+parent_name+'</strong>');
}
function show_sub_group(obj,groupid){
    if($(obj).hasClass('fa-folder')){
        $('#subgroup_'+groupid).toggle();
        $(obj).removeClass('fa-folder');
        $(obj).addClass('fa-folder-open');
    }
    else if($(obj).hasClass('fa-folder-open')){
        $('#subgroup_'+groupid).toggle();
        $(obj).removeClass('fa-folder-open');
        $(obj).addClass('fa-folder');
    } 
    
    else {
    }
}

function delete_group(id){
    var auth = confirm(" Are you sure, want to delete ?");
    if(auth) {
            Loading(true);
            $.ajax({
                type: "POST",
                url: root_domain+finance_root_domain+'app/chart_account/',
                data: { mode : "delete", group_id : id },
                success: function(response)
                {
                    if(response.trim() == "1") {
                            toastr.success("Group Deleted Successfully", "SUCCESS");
                            $('#li_'+id).remove();
//                            $('#expand_'+g_parent).addClass('fa-minus');
//                            $('#expand_'+g_parent).removeClass('fa-caret-down');
//                            $('#expand_'+g_parent).removeAttr('onclick', 'show_sub_group(this,'+g_parent+');');
                    }
                    else if(response.trim() == "2") {
                        toastr.warning("Group is Associated with Ledger Or Having child group", "WARNING");
                        
                    }
                    else if(response.trim() == "0") {
                        toastr.warning("Oops.. Something went Wrong", "WARNING");
                    }
                    Unloading();
                }
            });	
    }
}
