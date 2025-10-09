$(document).ready(function() {
    // alert("asdsf");
    delete_temp_data();
    load_po_grn_datatable();
});

function reload_data()
{
    load_po_grn_datatable();
    load_po();
}

function load_po_grn_datatable()
{
    var vender_id = $('#vender_id').val();
    var po_id = $('#po_id').val();
    
    if (!po_id)
        $('#po_id').select2("val","");

    $("#overdue-po-req-datatable").dataTable({
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
        "sAjaxSource": root_domain+purchase_domain+'app/po_grn_report/',
        "fnServerParams": function ( aoData ) {
            aoData.push( { "name": "mode", "value": "fetch" },{ "name": "vender_id", "value": vender_id }, {  "name": "po_id", "value":po_id} );
        },
        "fnDrawCallback": function( oSettings ) {
            //alert(oSettings);
            $('.ttip, [data-toggle="tooltip"]').tooltip();
        }
    }).fnSetFilteringDelay();

    //Search input style
    $('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
    $('.dataTables_length select').addClass('form-control');
}

function load_po(){
    var vender_id = $('#vender_id').val();
    $.ajax({
        type: "POST",
        url: root_domain+purchase_domain+'app/po_grn_report/',
        data: { mode : "po_fetch", vender_id:vender_id },
        success: function(resp){
            var resp=JSON.parse(resp);
            $('#po_id').empty().html(resp);
        }        
    });
}

function delete_temp_data(){
    $.ajax({
        type: "POST",
        url: root_domain+purchase_domain+'app/purchase/',
        data: { mode : "delete_temp_data" },
        success: function(response)
        {
                                        
        }
    }); 
}