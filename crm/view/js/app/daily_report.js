$(document).ready(function () {
    loadtable();

    $("#send_input").on('submit', function (e) {
        var form = this;
        for (instance in CKEDITOR.instances) {
            CKEDITOR.instances[instance].updateElement();
        }

        e.preventDefault();
        e.stopPropagation();
        if (!$("#send_input").valid()) {
            return false;
        }
        form.submitted = true;
        Loading(true);
        $(this).attr("disabled", "disabled");

        var form_data = new FormData(this);

        $.ajax({
            cache: false,
            url: root_domain + crm_domain + 'app/daily_report/',
            type: "POST",
            data: form_data,
            contentType: false,
            processData: false,
            success: function (response) {
                var resp = JSON.parse(response);
                var msg = resp.msg;
                if (msg.trim() == '1') {
                    toastr.success("REPORT ADDED SUCCESSFULLY", "SUCCESS")
                    Unloading();
                    loadtable();
                    window.location.href = root_domain + crm_domain + 'daily_report_list';
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);

            }
        });
    });

    $('#user_name').on('input', function () {
        // Get the current value
        var currentValue = $(this).val();
        var description = CKEDITOR.instances.user_input.getData();

        // Perform actions based on the current value
        Loading(true);

        // Define obj outside of the success callback
        var obj;

        $.ajax({
            type: "POST",
            url: root_domain + crm_domain + 'app/daily_report/',
            data: { mode: "preedit2", uid: currentValue },
            success: function (response) {
                obj = jQuery.parseJSON(response);
                CKEDITOR.instances['user_input'].setData(obj.description);
                Unloading();
            }
        });
    });
});

// Load Table Records
function loadtable() {
     var uid = $('#user_names').val();
    var start_date = $('#start_date').val();
    var end_date = $('#end_date').val();
    datatable = $("#daily_report-table").dataTable({
        "bAutoWidth": false,
        "bFilter": true,
        "bSort": true,
        "bProcessing": true,
        "bServerSide": true,
        "bDestroy": true,
        "oLanguage": {
            "sLengthMenu": "_MENU_",
            "sProcessing": "<img src='" + root_domain + "img/loading.gif'/> Loading ...",
            "sEmptyTable": "NO DATA ADDED YET !",
        },
        "aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
        "iDisplayLength": 10,
        "sAjaxSource": root_domain + crm_domain + 'app/daily_report/',
        "fnServerParams": function (aoData) {
            aoData.push(
                { "name": "mode", "value": "fetch" },
                { "name": "userid", "value": uid },
                { "name": "start_date", "value": start_date },
                { "name": "end_date", "value": end_date },
            );
        },
        "fnDrawCallback": function (oSettings) {
            $('.ttip, [data-toggle="tooltip"]').tooltip();
        }
    }).fnSetFilteringDelay();

    //Search input style
    $('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
    $('.dataTables_length select').addClass('form-control');
}

function edit_report(r_id) {
    Loading(true);
    $.ajax({
        type: "POST",
        url: root_domain + crm_domain + 'app/daily_report/',
        data: { mode: "preedit", eid: r_id },
        success: function (response) {
            var obj = jQuery.parseJSON(response);
            $("#ModalEditreport").modal("show");
            $("#edit_id").val(r_id);

            $("#file_attachment_name").val(obj.file);
            $("#file_attachment").val("");
            $("#btn-file-delete").addClass("hidden");
            if (obj.file) {
                $("#btn-file-delete").removeClass("hidden");
            }
            $("#add_file_name").text(obj.file);
            CKEDITOR.instances['edit_description'].setData(obj.description);
            $("#formeditreport").valid();
            Unloading();

        }
    });
}

$("#formeditreport").on('submit', function (e) {
    var form = this;
    for (var instance in CKEDITOR.instances) {
        CKEDITOR.instances[instance].updateElement();
    }

    e.preventDefault();
    e.stopPropagation();

    if (!$(this).valid()) {
        return false;
    }
    form.submitted = true;
    Loading(true);

    $(this).attr("disabled", "disabled");
    var form_data = new FormData(this);

    var fileInput = $("#file_attachment");
    var file = fileInput.prop("files")[0];
    form_data.append('file_attachment', file);
    form_data.append('file_attachment_name', $("#file_attachment_name").val());

    $.ajax({
        cache: false,
        url: root_domain + crm_domain + 'app/daily_report/',
        type: "POST",
        data: form_data,
        contentType: false,
        processData: false,
        success: function (response) {
            if (response.trim() == '1') {
                toastr.success("REPORT UPDATED SUCCESSFULLY", "SUCCESS");
                loadtable();
                Unloading();
            } else if (response.trim() == '0') {
                toastr.warning("SOMETHING WRONG", "ERROR");
                Unloading();
            } else if (response.trim() == '-1') {
                toastr.info("ALREADY EXISTS", "INFO");
                Unloading();
            }
            $("#ModalEditreport").modal("hide");
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
        }
    });
});


$('#user_names').on('change', function () {
    var selectedValue = $(this).val();
    var start_date = $('#start_date').val();
    var end_date = $('#end_date').val();
    datatable = $("#daily_report-table").dataTable({
        "bAutoWidth": false,
        "bFilter": true,
        "bSort": true,
        "bProcessing": true,
        "bServerSide": true,
        "bDestroy": true,
        "oLanguage": {
            "sLengthMenu": "_MENU_",
            "sProcessing": "<img src='" + root_domain + "img/loading.gif'/> Loading ...",
            "sEmptyTable": "NO DATA ADDED YET !",
        },
        "aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
        "iDisplayLength": 10,
        "sAjaxSource": root_domain + crm_domain + 'app/daily_report/',
        "fnServerParams": function (aoData) {
            aoData.push(
                { "name": "mode", "value": "fetch" },
                { "name": "userid", "value": selectedValue },
                { "name": "start_date", "value": start_date },
                { "name": "end_date", "value": end_date },
            );
        },
        "fnDrawCallback": function (oSettings) {
            $('.ttip, [data-toggle="tooltip"]').tooltip();
        }
    }).fnSetFilteringDelay();

    //Search input style
    $('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
    $('.dataTables_length select').addClass('form-control');
});

function exportCsv() {
    var uid = $('#user_names').val();
    var start_date = $('#start_date').val();
    var end_date = $('#end_date').val();
    var url = root_domain + 'generate_export?mode=crm_daily_report&uid=' + encodeURIComponent(uid) + '&start_date=' + encodeURIComponent(start_date) + '&end_date=' + encodeURIComponent(end_date);
    window.location.href = url;
}

$('#file_attachment').on('change', function () {
    $("#btn-file-delete").addClass("hidden");
    if ($(this).val()) {
        $("#btn-file-delete").removeClass("hidden");
    }
});

function delete_file() {
    $("#file_attachment").val("");
    $("#btn-file-delete").addClass("hidden");
}

function delete_editatble_file() {
    $("#file_attachment").val("");
    $("#file_attachment_name").val("");
    $("#add_file_name").text("");
    $("#btn-file-delete").addClass("hidden");
    var edit_id = $('#edit_id').val();
    Loading(true);
    $.ajax({
        type: "POST",
        url: root_domain + crm_domain + 'app/daily_report/',
        data: { mode: "deletefile", d_id: edit_id },
        success: function (response) {
            var obj = jQuery.parseJSON(response);
            if (response.trim() == '1') {
                toastr.success("REPORT UPDATED SUCCESSFULLY", "SUCCESS");
                loadtable();
            } else if (response.trim() == '0') {
                toastr.warning("SOMETHING WRONG", "ERROR");
            }
            Unloading();
        }
    });
}

function delete_report(r_id){
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/daily_report/',
			data: { mode:"delete", r_id:r_id },
			success: function(response)
			{
				if (response.trim() == '1') {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					loadtable();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
}