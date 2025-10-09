var datatable;
$(document).ready(function() {
    load_closing_balance_data();

    // validate form before submit
    $("#add_closing_balance").validate({
            rules: {
            closing_balance: {
                            required: true
                    }
            },
            messages: {
                    closing_balance: {
                            required: "Enter Closing Balance"			
                    }
            }
    });		

});
function load_closing_balance_data(){
        var to_date = $('#to_date').val();
        
        $("#dynamic-table").dataTable({
            "bStateSave": true,
            "fixedHeader": true,
            "bDestroy": true,
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
            "aLengthMenu": [[10, 20, 30], [10, 20, 30]],
            "iDisplayLength": 10,
            "sAjaxSource": root_domain+'app/closing_balance_mst/',
            "fnServerParams": function ( aoData ) {
                    aoData.push( { "name": "mode", "value": "fetch" },{"name": "to_date", "value" : to_date} );
            },
            "fnDrawCallback": function( oSettings ) {
                    $('.ttip, [data-toggle="tooltip"]').tooltip();
            }
    }).fnSetFilteringDelay();

    //Search input style
    $('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
    $('.dataTables_length select').addClass('form-control');
}
function reload_data(){
    load_closing_balance_data();
}
$("#add_closing_balance").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#add_closing_balance").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	$.ajax({
		cache:false,
		url: root_domain + 'app/closing_balance_mst/',
		type: "POST",
		data: $("#add_closing_balance").serialize(),
		success: function(response)
		{
			var resp = JSON.parse(response);
                        //console.log(resp);
                        var msg= resp.msg;
			if(msg.trim() === '1') {				
				toastr.success("Closing Balance Updated Successfully", "SUCCESS");
				reload_data();
                                Unloading();
			}
			else if(msg.trim() === '0') {
				toastr.warning("Something Went Wrong", "ERROR");
                                Unloading();
			}
                        
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function show_closing_balance_history(obj,ledger_id){
    if($(obj).attr('data-attr') === 'expand'){
        $(obj).attr('data-attr','collapse');
        if(ledger_id){
            //Loading(true);
            $.ajax({
                url: root_domain + 'app/closing_balance_mst/',
                type: "POST",
                data: { ledger_id : ledger_id, mode : 'show_history' },
                success: function(response)
                {
                    var resp = JSON.parse(response);
                    $(obj).closest('tr').after('<tr id="history'+ ledger_id+'"><td colspan="4">'+ resp.html +'</td></tr>');
                    //Unloading();
                }
            });
        }
    } else {
        $("#history"+ledger_id).hide();
        $(obj).attr('data-attr','expand');
    }
}
function edit_closing_balance_history(cb_id){
    if(cb_id){
        //Loading(true);
        $.ajax({
            url: root_domain + 'app/closing_balance_mst/',
            type: "POST",
            data: { cb_id : cb_id, mode : 'show_edit_history_popup' },
            success: function(response)
            {
                var resp = JSON.parse(response);
                $('#closing_balance_edit_modal').modal('show');
                $("#closing_balance_edit_form").html(resp.html);
                $("#closing_balance_edit_modal").after(resp.script);
                //Unloading();
            }
        });
    }
}

$("#edit_closing_balance_form").validate({
	rules: {
            closing_bal_date: {
			required: true
		},
            closing_bal: {
			required: true
		}  
	},
        messages: {
            closing_bal_date: {
                    required: "Select Date"			
            },
            closing_bal: {
                    required: "Enter Amount"			
            }
	}
});

$("#edit_closing_balance_form").on('submit',function(e) {
        var form = this;
	e.preventDefault();
	e.stopPropagation();	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");	
        var ledger_id = $("#ledger_id").val();
        
	$.ajax({
		cache:false,
		url: root_domain+'app/closing_balance_mst/',
		type: "POST",
		data: $("#edit_closing_balance_form").serialize(),
		success: function(response)
		{
			$("#closing_balance_edit_modal").modal("hide");
                        reload_data();
                        setTimeout(function(){ 
                            $("#accordion"+ledger_id).click(); 
                        }, 300);
                        
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_closing_balance_history(cb_id){
    if(cb_id){
        if(confirm('Sure, you want to delete?')){
            var ledger_id = $("#ledger_id").val();
            $.ajax({
                url: root_domain + 'app/closing_balance_mst/',
                type: "POST",
                data: { cb_id : cb_id, mode : 'delete_history' },
                success: function(response)
                {
                    if(response.trim() === '1'){
                        reload_data();
                        toastr.success("Deleted", "SUCCESS");
                        setTimeout(function(){ 
                            $("#accordion"+ledger_id).click(); 
                        }, 300);
                    }
                }
            });
        }
    }
}