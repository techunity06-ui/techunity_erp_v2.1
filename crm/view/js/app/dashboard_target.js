
function load_value_vise_target(){

    var month = $('#month').val();
    var user_id = $('#user_id').val();
    var state_id = $('#state_id').val();

    $("#details_table").dataTable({
        "bStateSave": true,
        "fixedHeader": true,
        "bAutoWidth" : false,
        "bFilter" : true,
        "bSort" : true,
        "bProcessing": true,
        "bDestroy": true,
        "bServerSide" : true,
        "oLanguage": {
            "sLengthMenu": "_MENU_",
            "sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
            "sEmptyTable": "NO DATA ADDED YET !"
        },
        "aLengthMenu": [[-1, 10, 20, 30, 50], ["All", 10, 20, 30, 50]],
        "iDisplayLength": 10,
        "sAjaxSource": root_domain + crm_domain + 'app/dashboard_target/',
        "fnServerParams": function ( aoData ) {
            aoData.push( {"name": "mode", "value": "load_value_vise_target"}, 
                {"name": "user_id", "value": user_id},
                {"name": "state_id", "value": state_id},
                {"name": "month", "value": month});
        },
        "fnDrawCallback": function( oSettings ) {
            $('.ttip, [data-toggle="tooltip"]').tooltip();
        },
        "fnFooterCallback": function ( nRow, aaData, iStart, iEnd, aiDisplay ) {
            var iPageMarket = 0;
            var iPageMarkets = 0;
            var iPageMarketi = 0;
            for ( var i=0 ; i<aaData.length ; i++ )
            {
                iPageMarket += aaData[i][3]*1;
                iPageMarkets += aaData[i][4]*1;
                iPageMarketi += aaData[i][5]*1;

            }
            var nCells = nRow.getElementsByTagName('th');
            nCells[1].innerHTML = parseFloat(iPageMarket ).toFixed(2);
            nCells[2].innerHTML = parseFloat(iPageMarkets ).toFixed(2);
            nCells[3].innerHTML = parseFloat(iPageMarketi ).toFixed(2);

            $('#quotamount').html('Count: '+parseFloat(iPageMarket ).toFixed(2));
            $('#soamount').html('Count: '+parseFloat(iPageMarkets ).toFixed(2));
            $('#oaamount').html('Count: '+parseFloat(iPageMarketi ).toFixed(2));

        }
    }).fnSetFilteringDelay();

    //Search input style
    $('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
    $('.dataTables_length select').addClass('form-control');

}


function load_product_vise_target(){

    $.ajax({

        type:'post',
        data:{"mode":"load_product_vise_target"},
        url:root_domain+crm_domain+'app/dashboard_target/',
        success:function(result)
        {
            //alert(result);
            //console.log(result);
            $('#product_details_table').html(result);
        }
    })

}

function add_followup(cust_id,month)
{
    //alert(cust_id);
    $.ajax({

        type:'POST',
        url:root_domain+crm_domain+'app/dashboard_target/',
        data:{mode:'add_followup_value_wise',cust_id:cust_id},
        success:function(result)
        {
            //alert(result);
            $("#modal-value-add-followup").modal("show");

            $('#month_id').val(month);
            $('#cust_id').val(cust_id);
            var max_followup_date = $('#max_followup_date').val();
            var date = new Date();
            var today = new Date(date.getFullYear(), date.getMonth(), date.getDate()); //start date is today
            var endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() + parseInt(max_followup_date)); //
            $(".form_datetime-meridian").datetimepicker({
                format: "dd-mm-yyyy HH:ii P",
                showMeridian: true,
                autoclose: true,
                todayBtn: true,
                pickerPosition: "bottom-left",
                startDate: today,
                endDate: endDate,

            });
            show_task_attach_data();
        }
    })
}

function add_value_followup()
{
    var task_type_id        = $('#task_type_id').val();
    var task_due_date       = $('#task_due_date').val();
    var followup_remark     = $('#followup_remark').val();
    var opp_id              = $('#opp_id').val();
    var sales_stage_id      = $('#sales_stage_id').val();
    var assign_user_ids     = $('#assign_user_ids').val();
    var task_priority_id    = $('#task_priority_id').val();
    var task_alert_id       = $('#task_alert_id').val();
    var email_template_id   = $('#email_template_id').val();
    
    var cust_id = $('#cust_id').val();
    var month_id = $('#month_id').val();

    if(task_type_id=='')
    {
        toastr.warning("SELECT TASK TYPE","warning");
        $('#task_type_id').focus();
        return false;
    }
    if(task_due_date=='')
    {
        toastr.warning("SELECT NEXT FOLLOUP DATE","warning");
        $('#task_due_date').focus();
        return false;   
    }
    if(followup_remark=='')
    {
        toastr.warning("SELECT REMARK","warning");
        $('#followup_remark').focus();
        return false;   
    }

    $.ajax({

        type:'POST',
        data:{mode:"add_value_followup",task_type_id:task_type_id,task_due_date:task_due_date,followup_remark:followup_remark,cust_id:cust_id,month_id:month_id,opp_id:opp_id,sales_stage_id:sales_stage_id,assign_user_ids:assign_user_ids,task_priority_id:task_priority_id,task_alert_id:task_alert_id,email_template_id:email_template_id},
        url:root_domain+crm_domain+'app/dashboard_target/',
        success:function(result){

            $("#modal-value-add-followup").modal("hide");
            if(result==1)
            {
                toastr.success("Followup Added Successfully","success");
            }
            else
            {
                toastr.warning("SOMETHING WENT WRONG","warning");                
            }

            $('#task_due_date').val('');
            $('#followup_remark').val('');
        }
    })
}
function followup_history_value(customer)
{
    //alert(customer);
    Loading();
    $.ajax({

        type:'POST',
        data:{mode:'followup_history_value',customer:customer},
        url:root_domain+crm_domain+'app/dashboard_target/',
        success:function(result)
        {
            $("#modal-value-followup-history").modal("show");
            $('#history_details').html(result);
            Unloading();
        }
    })


}

function add_followup_product(cust_id,product)
{
    //alert(cust_id);

    //alert(result);
    $("#modal-product-add-followup").modal("show");

    $('#f_product_id').val(product);
    $('#cust_id').val(cust_id);
    var max_followup_date = $('#max_followup_date').val();
    var date = new Date();
    var today = new Date(date.getFullYear(), date.getMonth(), date.getDate()); //start date is today
    var endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() + parseInt(max_followup_date)); //
    $(".form_datetime-meridian").datetimepicker({
        format: "dd-mm-yyyy HH:ii P",
        showMeridian: true,
        autoclose: true,
        todayBtn: true,
        pickerPosition: "bottom-left",
        startDate: today,
        endDate: endDate,

    });


}

function add_product_followup()
{
    var task_type_id = $('#task_type_id').val();
    var task_due_date = $('#task_due_date').val();
    var followup_remark = $('#followup_remark').val();
    var cust_id = $('#cust_id').val();
    var f_product_id = $('#f_product_id').val();

    if(task_type_id=='')
    {
        toastr.warning("SELECT TASK TYPE","warning");
        $('#task_type_id').focus();
        return false;
    }
    if(task_due_date=='')
    {
        toastr.warning("SELECT NEXT FOLLOUP DATE","warning");
        $('#task_due_date').focus();
        return false;   
    }
    if(followup_remark=='')
    {
        toastr.warning("SELECT REMARK","warning");
        $('#followup_remark').focus();
        return false;   
    }

    $.ajax({

        type:'POST',
        data:{mode:"add_product_followup",task_type_id:task_type_id,task_due_date:task_due_date,followup_remark:followup_remark,cust_id:cust_id,f_product_id:f_product_id},
        url:root_domain+crm_domain+'app/dashboard_target/',
        success:function(result){

            $("#modal-product-add-followup").modal("hide");
            if(result==1)
            {
                toastr.success("Followup Added Successfully","success");
            }
            else
            {
                toastr.warning("SOMETHING WENT WRONG","warning");                
            }

            $('#task_due_date').val('');
            $('#followup_remark').val('');
        }
    })
}

function followup_history_product(customer)
{
    //alert(customer);
    Loading();
    $.ajax({
        type:'POST',
        data:{mode:'followup_history_product',customer:customer},
        url:root_domain+crm_domain+'app/dashboard_target/',
        success:function(result)
        {
            $("#modal-product-followup-history").modal("show");
            $('#history_details_product').html(result);
            Unloading();
        }
    });
}

function add_task_attch_field() {
    if(!$("#inq_attch_doc_name").val()){        
        toastr.warning("Enter Document Name", "ERROR");
        $("#inq_attch_doc_name").focus();
        return false;
    }
    if(!$("#inq_attch_file").val()){
        toastr.warning("Choose File", "ERROR");
        $("#inq_attch_file").focus();
        return false;
    }
    
    Loading();
    var form_data = new FormData();
    form_data.append('mode', "add_task_attch_field");
    form_data.append('task_id', $("#eid").val());
    form_data.append('inquiry_id', $("#inquiry_id").val());
    form_data.append('inq_attch_doc_name', $("#inq_attch_doc_name").val());
    form_data.append("inq_attch_file", document.getElementById('inq_attch_file').files[0]);
    
    $.ajax({
        type: "POST",
        url: root_domain + crm_domain + 'app/task/',
        data: form_data,
        contentType: false,
        processData: false,
        success: function(response)
        {
            //console.log(response);
            $("#inq_attch_doc_name").val("").focus();
            $("#inq_attch_file").val("");
            $('#task_attch_btn').val('Add');
            Unloading();
            show_task_attach_data();
        }
    });
}

function show_task_attach_data() {
    var eid = $('#inquiry_id').val();
    var chkmode = $('#mode').val();
    Loading();
    $.ajax({
        type: "POST",
        url: root_domain + crm_domain + 'app/dashboard_target/',
        data: { mode : "show_task_attach_data", task_id:eid,modee:chkmode },
        success: function(resp){
            //console.log(resp);
            $('#task_attch_trn_div').html(resp);
            Unloading();
        }        
    }); 
}
function delete_task_attach_data(task_attach_id){
    var r= confirm(" Are you want to delete ?");
    if(r) {
        Loading();
        $.ajax({
            type: "POST",
            url: root_domain + crm_domain + 'app/task/',
            data: { mode:"delete_task_attach_data", task_attach_id:task_attach_id },
            success: function(response)
            {
                //console.log(response);
                var data=jQuery.parseJSON(response);
                var response=data.res;
                if(response.trim() == "1") {
                    toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
                    show_task_attach_data();
                }
                else if(response.trim() == "0") {
                    toastr.warning("SOMETHING WRONG", "WARNING");
                }   
                Unloading();                        
            }
        }); 
    }
}