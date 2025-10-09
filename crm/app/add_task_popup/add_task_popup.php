<?php  
    session_start();
    //error_reporting(E_ALL);
    include('../../include/urlfileinner.php');
    include_once('../../include/include_css_file.php');
    
    $countryid='101';
    $stateid='1';
    $cityid='1';
    $user_name=$_SESSION['user_name'];
    $task_due_date = date('d-m-Y h:i A');
    $inquiry_id = $_POST['inquiry_id'];
    $entry_type = $_POST['entry_type'];
    $title = ($entry_type == '1') ? "Task" : "Appointment";
    $branch_id = $_SESSION['branch_id'];
    $query = $dbcon -> query("SELECT inquiry_id, inquiry_name, sales_stage_id, opp_id, stage_prob,branch_id, inq_desc FROM tbl_inquiry as inq WHERE inquiry_id = ".$inquiry_id);
    $inq_data = $query->fetch_assoc();
    
    $assign_user = ($inq_data['user_id']) ? $inq_data['user_id'] : $_SESSION['user_id'];
    $selected_branch_id = $inq_data['branch_id'];
	
    $query1 = $dbcon -> query("SELECT * FROM tbl_task as inq WHERE task_status=0 and entry_type=1 and inquiry_id = ".$inquiry_id);
    $inq_data1 = $query1->fetch_assoc();
	
    $prev_task_id=$inq_data1['task_id'];
    $email_template_id = $inq_data1['email_template_id'];
    //print_r($inq_data);

    $max_followup_date = MAX_FOLLOWUP_DATE;
    // Amish Soni Start 18-01-2021

    $crm_auto_mail = '';
    $companySettings = getCompanySettings($dbcon);
    if($companySettings) {
        $crm_auto_mail = $companySettings['crm_auto_mail'];
        if($companySettings['max_followup_date']!=0){
            $max_followup_date=(int)$companySettings['max_followup_date'];
        }
    }
    $showTemplate = ($crm_auto_mail == 'No');
    // Amish Soni End 18-01-2021

    
    

$html = '';
$html .= '
<div class="modal fade colored-header info" id="add_task_modal" role="dialog" style="z-index: 1041;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true"> &times;</button>
                <h3 class="modal-title">Add New '.$title.'</h3>
            </div>
            <div class="modal-body form">';
if($entry_type == '1'){
            $html .= '<form class="form-horizontal" role="form" id="task_add" action="javascript:;" method="post" name="task_add">
                        <div class="row">
                            <div class="clearfix"></div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Task</label>
                                    <div class="col-md-6"> 
                                        <select class="select2 form-control" id="task_type_id" name="task_type_id" required onchange="check_assign_user(this.value)">
                                            <option value="">Choose Task Type</option>
                                            '.get_master_category_dtl($dbcon,16,10,$inquiry_id,1).'
                                        </select>
                                    </div>
                                </div>	
                            </div>
                            <div class="col-md-12" style="display: none;">
                                '.getBranchBox($dbcon, '1', $selected_branch_id, false, true,'','2','6','form-control').'
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-md-4 control-label">Related To</label>
                                    <div class="col-md-6"> 
                                        <select class="select2 form-control" id="task_rel_id" name="task_rel_id" disabled>
                                            '.get_rel_task($dbcon,5).'
                                        </select>
                                    </div>
                                </div>	
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div id="inq_rel_div">
                                        <div class="col-md-8"> 
                                            <select class="select2 form-control" id="sel_inquiry_id" name="inquiry_id" disabled>
                                                '.get_inquiry($dbcon,$inquiry_id).'
                                            </select>
                                        </div>
                                    </div>
                                </div>	
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label col-md-4" style="text-align: right;">Stage :</label>
                                    <div class="col-md-6">
                                        <select class="select2 form-control" name="opp_id" id="opp_id" onchange="show_close_reason(this.value);change_inquiry_stage(this.value);">
                                            '.get_inquiry_stage($dbcon,$inq_data['opp_id']).'	
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="stage_prob" name="stage_prob" class="form-control" value="'.$inq_data['stage_prob'].'">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-md-4 control-label" style="text-align: right;">Sales Stage :</label>
                                    <div class="col-md-6"> 
                                        <select class="select2 form-control" id="sales_stage_id" name="sales_stage_id">
                                            <option value="">Choose Sales Stage</option>
                                            '.get_master_category_dtl($dbcon,$inq_data['sales_stage_id'],7).'
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-md-12 lost_reasons" id="lost_reason_div" style="display:none;">
                                <div class="form-group">
                                    <label class="col-md-2 control-label" style="text-align: right;">Reason*</label>
                                    <div class="col-md-3"> 
                                        <select class="select2 form-control reasonid" id="reason_id" name="reason_id[]">
                                            '.get_lost_reasons($dbcon,$id).'
                                        </select>
                                    </div>
                                    <label class="col-md-2 control-label">Reason Remark*</label>
                                    <div class="col-md-3"> 
                                        <textarea class="form-control reason_remark" name="lost_reason[]" id="lost_reason" style="resize:both;" placeholder="Lost Reason" rows="1"/></textarea>
                                    </div>	
                                    <div class="col-md-2"> 
                                        <button type="button" id="reason_btn" class="btn btn-primary" title="View Details" onclick="add_reason_div()"><i class="add_remove_reason fa fa-plus"></i></button>
                                    </div>
                                </div>
                                <input type="hidden" id="counter" name="counter" value="1">
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Remark*</label>
                                    <div class="col-md-6"> 
                                            <textarea class="form-control" id="task_remark" name="task_remark" style="resize:both;" placeholder="Remark" rows="3" required>'.$inq_data['inq_desc'].'</textarea>
                                    </div>
                                </div>	
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Assign To</label>
                                    <div class="col-md-6"> 
                                        <select class="select2" id="assign_user_ids" name="assign_user_ids" placeholder="Choose Assign User" onchange="no_of_inquiry(this)">
                                            '.get_assign_users($dbcon, $assign_user, "").'
                                        </select>
                                        <div id="no_of_inquiry" style="font-size: 12px; color: #337ab7;"></div>
                                    </div>
                                </div>	
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-md-4 control-label">Priority</label>
                                    <div class="col-md-6"> 
                                        <select class="select2 form-control" id="task_priority_id" name="task_priority_id">
                                            '.get_task_priority($dbcon,$rel['task_priority_id']).'
                                        </select>
                                    </div>
                                </div>	
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-md-4 control-label">Next Followup Date</label>
                                    <div class="col-md-8"> 
                                        <div data-date="'.$task_due_date.'" class="input-group date task_due_datepicker">
                                            <input type="text" class="form-control" value="'.$task_due_date.'" name="task_due_date" id="task_due_date" autocomplete="off">
                                            <div class="input-group-btn">
                                                <button type="button" class="btn btn-info date-set"><i class="fa fa-calendar"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>	
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Alert</label>
                                    <div class="col-md-6"> 
                                            <select class="select2 form-control" id="task_alert_id" name="task_alert_id">
                                                    '.get_task_alert_types($dbcon,2).'
                                            </select>
                                    </div>
                                </div>		
                            </div>';
                            // Amish Soni Start 18-01-2021
                            if($showTemplate) {
                                $html .= '<div class="clearfix"></div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Email Template</label>
                                        <div class="col-md-6"> 
                                                <select class="select2 form-control" id="email_template_id" name="email_template_id">
                                                        '.getAllEmailSMSTemplate($dbcon,2, $email_template_id).'
                                                </select>
                                        </div>
                                    </div>		
                                </div>';
                            }
                            // Amish Soni End 18-01-2021
                            $html .= '<hr/>
                            <div class="clearfix"></div>
                        <div class="clearfix"></div>
                        <div class="col-md-12 text-center">
                            <button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
                        </div>
                        <input type="hidden" name="mode" id="mode" value="add_task" />
                        <input type="hidden" name="inquiry_id" id="inquiry_id" value="'.$inquiry_id.'" />
						<input type="hidden" name="prev_task_id" id="prev_task_id" value="'.$prev_task_id.'" />
                    </form>
                </div>';
                
} else {
    $html .= '
    <form class="form-horizontal" role="form" id="appointment_add" action="javascript:;" method="post" name="appointment_add">
        <div class="row">
            <div class="clearfix"></div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-md-4 control-label">Location*</label>
                    <div class="col-md-8"> 
                        <input type="text" class="form-control" id="task_location" name="task_location" placeholder="Location" value="'.$rel['task_location'].'">
                    </div>
                </div>	
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-md-4 control-label">Full Day Event</label>
                    <div class="col-md-8"> 
                        <label class="col-md-4 col-sm-6 " style="font-weight:bold;">
                            <input type="radio" id="full_day_event_yes" name="full_day_event" style="width: 15px;height: 15px;" value="1"> 
                        YES</label>
                        <label class="col-md-4 col-sm-6" style="font-weight:bold;">
                            <input type="radio" checked id="full_day_event_no" name="full_day_event" style="width: 15px;height: 15px;" value="0"> 
                        No</label>
                    </div>
                </div>	
            </div>
            <div class="clearfix"></div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-md-4 control-label">Start Time*</label>
                    <div class="col-md-8"> 
                        <div data-date="" class="input-group date appointment_start">
                            <input type="text" class="form-control" value="" name="appointment_start_time" id="appointment_start_time">
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-info date-set"><i class="fa fa-calendar"></i></button>
                            </div>
                        </div>
                    </div>
                </div>	
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-md-4 control-label">End Time*</label>
                    <div class="col-md-8"> 
                        <div data-date="" class="input-group date appointment_end">
                            <input type="text" class="form-control" value="" name="appointment_end_time" id="appointment_end_time">
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-info date-set"><i class="fa fa-calendar"></i></button>
                            </div>
                        </div>
                    </div>
                </div>	
            </div>
            <div class="clearfix"></div>
            <div class="col-md-12">
                <div class="form-group">
                    <label class="col-md-2 control-label">Subject*</label>
                    <div class="col-md-6"> 
                        <input type="text" class="form-control" id="appointment_subject" name="appointment_subject" placeholder="Subject">
                    </div>
                </div>	
            </div>
            <div class="clearfix"></div>
            <div class="col-md-12">
                <div class="form-group">
                    <label class="col-md-2 control-label">Remark</label>
                    <div class="col-md-6"> 
                            <textarea class="form-control" id="task_remark" name="task_remark" style="resize:both;" placeholder="Remark" rows="5"></textarea>
                    </div>
                </div>	
            </div>
            <div class="clearfix"></div>
            <div class="col-md-12">
                <div class="form-group">
                    <label class="col-md-2 control-label">Invites To</label>
                    <div class="col-md-6"> 
                        <select class="select2" id="assign_user_ids" name="assign_user_ids[]" placeholder="Choose Assign User" multiple="multiple">
                            '.get_assign_users($dbcon, $assign_user).'
                        </select>
                    </div>
                </div>	
            </div>
            <div class="clearfix"></div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-md-4 control-label">Related To</label>
                    <div class="col-md-6"> 
                        <select class="select2 form-control" id="task_rel_id" name="task_rel_id" disabled>
                            '.get_rel_task($dbcon,5).'
                        </select>
                    </div>
                </div>	
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <div id="inq_rel_div">
                        <div class="col-md-10"> 
                            <select class="select2 form-control" id="sel_inquiry_id" name="inquiry_id" disabled>
                                '.get_inquiry($dbcon,$inquiry_id).'
                            </select>
                        </div>
                    </div>
                </div>	
            </div>
            <div class="clearfix"></div>
            <div class="col-md-12">
                <div class="form-group">
                    <label class="col-md-2 control-label">Alert</label>
                    <div class="col-md-6"> 
                        <select class="select2 form-control" id="task_alert_id" name="task_alert_id">
                            '.get_task_alert_types($dbcon,2).'
                        </select>
                    </div>
                </div>	
            </div>
            <hr/>
            <div class="clearfix"></div>
            <div class="col-md-12 text-center">
                <button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
            </div>
            <input type="hidden" name="mode" id="mode" value="add_appointment" />
            <input type="hidden" name="inquiry_id" id="inquiry_id" value="'.$inquiry_id.'" />
			<input type="hidden" name="prev_task_id" id="prev_task_id" value="'.$prev_task_id.'" />
        </div>
    </form>';
}
$html .=   '</div>
        </div>	
    </div>
</div>';
$html .= '
    <script src="'.ROOT.CRM_ROOT.'js/app/add_task_popup.js?'.time().'"></script>
    <script type="text/javascript">
    $("#inquiry_id").select2("readonly",true);
    // $("#branch_id").select2({
    //     width: "100%"
    // });
    $("#assign_user_ids").select2({
        width: "100%"
    });
    $(".default-date-picker").datepicker({
    	format: "dd-mm-yyyy",
    	autoclose: true
    });
    var date = new Date();
    var max_followup_date = '.$max_followup_date.';
    var today = new Date(date.getFullYear(), date.getMonth(), date.getDate()); //start date is today
    var endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate()+ parseInt(max_followup_date)); //end date should not greater than 15 days
    $(".task_due_datepicker").datetimepicker({
        format: "dd-mm-yyyy HH:ii P",
        showMeridian: true,
        autoclose: true,
        todayBtn: true,
        pickerPosition: "top-left",
        startDate: today,
        endDate: endDate
    });
    var startDate;
    $(".appointment_start").datetimepicker({
        format: "dd-mm-yyyy HH:ii P",
        showMeridian: true,
        autoclose: true,
        todayBtn: true,
        pickerPosition: "bottom-left",
        startDate: today
    }).on("change.dp", function (e) {
           startDate = e.target.value;
    });
    $(".appointment_end").datetimepicker({
        format: "dd-mm-yyyy HH:ii P",
        showMeridian: true,
        autoclose: true,
        todayBtn: true,
        pickerPosition: "bottom-left",
        startDate: today
    }).on("change.dp", function (e) {
           var endDate = e.target.value;
           if(startDate>endDate){
                alert("End datetime should be greater than start datetime.");
                $(".appointment_end").datetimepicker("update",startDate);
           }
    });
    // var task_type_id = $("#task_type_id").val();
    // if(task_type_id === "15" || task_type_id === "20"){
    //     $("#assign_user_ids").removeAttr("multiple");
    //     $("#assign_user_ids").select2({width: "100%"});
    // } else {
    //     $("#assign_user_ids").attr("multiple","true");
    //     $("#assign_user_ids").select2({width: "100%"});
    // }
</script>';

echo $html;

