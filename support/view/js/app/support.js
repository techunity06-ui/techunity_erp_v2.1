$(document).ready(function () {
    load_support_list();

    // validate vendor add form on keyup and submit
    $("#support_add").validate({
        rules: {
            cmp_unique_id: {
                required: true
            },
            company_name: {
                required: true
            },
            department: {
                required: true
            },
            page_link: {
                required: true
            },
            description: {
                required: true
            }
        },
        messages: {
            cmp_unique_id: {
                required: "Set Company ID From Setting"
            },
            company_name: {
                required: "Set Company Name From Setting"
            },
            department: {
                required: "Enter Department"
            },
            page_link: {
                required: "Enter Page Link"
            },
            description: {
                required: "Enter Description"
            }
        }
    });

    $("#FormEditField").validate({
        rules: {
            support_status_id: {
                required: true
            },
            due_date: {
                required: function () {
                    return $("#support_status_id").val() == 1;
                }
            },
            emp_id: {
                required: function () {
                    return $("#support_status_id").val() == 1;
                }
            },
            change_user: {
                required: function () {
                    return $("#support_status_id").val() == 3;
                }
            },
            change_comment: {
                required: function () {
                    return $("#support_status_id").val() == 3;
                }
            },
        },
        messages: {
            support_status_id: {
                required: "Select Status"
            },
            due_date: {
                required: "Enter Due Date"
            },
            emp_id: {
                required: "Select Employee"
            },
            change_user: {
                required: "Enter Name"
            },
            change_comment: {
                required: "Enter Comment"
            },
        }
    });
});

$("#support_add").on('submit', function (e) {
    var form = this;
    e.preventDefault();
    e.stopPropagation();
    if (!$("#support_add").valid()) {
        return false;
    }
    var description = (CKEDITOR.instances.description.getData());

    if (description == '') {
        toastr.warning("PLEASE ENTER DESCRIPTION.", "WARNING");
        $("#description").focus();
        return false;
    }

    for (instance in CKEDITOR.instances) {
        CKEDITOR.instances[instance].updateElement();
    }

    form.submitted = true;
    Loading(true);
    $(this).attr("disabled", "disabled");

    var form_data = new FormData(this);
    //console.log(form_data);
    var new_url = is_session ? root_domain + support_domain + 'app/support/' : support_url + 'support/app/api/support.php';
    $.ajax({
        cache: false,
        // url: root_domain+support_domain+'app/support/',
        url: new_url,
        type: "POST",
        data: form_data,
        contentType: false,
        processData: false,
        success: function (response) {
            console.log(response);
            // var arr = jQuery.parseJSON(response);
            var arr = is_session ? jQuery.parseJSON(response) : response;
            if (arr.msg == '1') {
                Unloading();
                toastr.success("SUPPORT ADDED SUCCESSFULLY", "SUCCESS");
                window.location = root_domain + support_domain + 'support_list';
            } else if (arr.msg == '0') {
                toastr.warning("SOMETHING WRONG", "ERROR");
                Unloading();
            } else if (arr.msg == '-2') {
                toastr.warning("ADD COMPANY ID AND COMPANY NAME", "ERROR");
                Unloading();
            }
            $('#support_add').trigger('reset');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
        }
    });
});

function load_support_list(emp_id = '') {
    var ses_user_id = $('#ses_user_id').val();
    var company_id = $('#company_id').val();
    var user_type = $('#user_type').val();
    var new_url = is_session ? root_domain + support_domain + 'app/support/' : support_url + 'support/app/api/support.php';
    $("#dynamic-table").dataTable({
        "bStateSave": true,
        "bAutoWidth": false,
        "bFilter": true,
        "bSort": true,
        "bProcessing": true,
        "bDestroy": true,
        "bServerSide": true,
        "oLanguage": {
            "sLengthMenu": "_MENU_",
            "sProcessing": "<img src='" + root_domain + "img/loading.gif'/> Loading ...",
            "sEmptyTable": "NO DATA ADDED YET !",
        },
        "aoColumnDefs": [{
            'bSortable': false,
            'aTargets': [6, 7] // remove sorting from columns "user & Action"
        }],
        "aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
        "iDisplayLength": 10,
        "sAjaxSource": new_url,
        "fnServerParams": function (aoData) {
            aoData.push({"name": "mode", "value": "fetch"}, {"name": "emp_id", "value": emp_id}, {
                "name": "user_id",
                "value": ses_user_id
            }, {"name": "company_id", "value": company_id}, {"name": "user_type", "value": user_type});
        },
        "fnDrawCallback": function (oSettings) {
            $('.ttip, [data-toggle="tooltip"]').tooltip();
        }
    }).fnSetFilteringDelay();

    //Search input style
    $('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
    $('.dataTables_length select').addClass('form-control');
}

$("#FormEditField").on('submit', function (e) {
    var form = this;
    e.preventDefault();
    e.stopPropagation();
    if (!$("#FormEditField").valid()) {
        return false;
    }
    form.submitted = true;
    Loading(true);
    $(this).attr("disabled", "disabled");
    var form_data = {
        id: $("#edit_id").val(),
        support_status_id: $("#support_status_id").val(),
        due_date: $("#due_date").val(),
        emp_id: $("#emp_id").val(),
        change_user: $("#change_user").val(),
        change_comment: $("#change_comment").val(),
        user_id: $('#ses_user_id').val(),
        company_id: $('#company_id').val(),
        mode: 'change_status'
    };
    var new_url = is_session ? root_domain + support_domain + 'app/support/' : support_url + 'support/app/api/support.php';
    $.ajax({
        cache: false,
        // url: root_domain+support_domain+'app/support/',
        url: new_url,
        type: "POST",
        data: form_data,
        success: function (response) {
            if (response.trim() == '1') {
                toastr.success("STATUS CHANGED SUCCESSFULLY", "SUCCESS");
                load_support_list();
                Unloading();
            } else if (response.trim() == '0') {
                toastr.warning("SOMETHING WRONG", "ERROR")
                Unloading();
            }
            $("#modalChangeStatus").modal("hide");
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
        }
    });
});

function change_status(id) {
    $('.pendingCls, .approveCls').hide();
    var new_url = is_session ? root_domain + support_domain + 'app/support/' : support_url + 'support/app/api/support.php';
    $.ajax({
        type: "POST",
        // url: root_domain+support_domain+'app/support/',
        url: new_url,
        data: {mode: "preedit", id: id},
        success: function (response) {
            // var resp = jQuery.parseJSON(response);
            var resp = is_session ? jQuery.parseJSON(response) : response;
            // console.log(resp);
            $('#support_status_id').html(resp.support_status);
            if (resp.data) {
                var data = resp.data;

                var minDate = data.cdate ? data.cdate : new Date();
                var date = new Date(minDate);
                var month = date.getMonth() + 1;
                start_date = date.getDate() + '-' + month + '-' + date.getFullYear();
                $('#due_date').datepicker('setStartDate', start_date);

                var dueDate = (data.due_date) ? (data.due_date) : '';
                $('#due_date').datepicker('setDate', dueDate);

                if (data.change_user) {
                    $('#change_user').val(data.change_user);
                }
                if (data.change_comment) {
                    $('#change_comment').val(data.change_comment);
                }
                var support_status_id = data.support_status_id;
                if (support_status_id == '1') {
                    $('.pendingCls').show();
                } else if (support_status_id == '3') {
                    $('.approveCls').show();
                }

                $("#emp_id").select2("val", data.emp_id);
            }

            $('#edit_id').val(id);
            $("#modalChangeStatus").modal("show");
        }
    });
}

function setFields(support_status_id) {
    $('.pendingCls, .approveCls').hide();
    $('#due_date, #change_user, #change_comment').val('');

    if (support_status_id == '1') {
        $('.pendingCls').show();
    } else if (support_status_id == '3') {
        $('.approveCls').show();
    }
}