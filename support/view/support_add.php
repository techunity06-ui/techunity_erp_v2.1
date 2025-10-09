<?php
session_start();
$path = '../../';
$include = '../../include/';
$incPath = $path . 'include/common_functions/';

include_once($path . 'config/config.php');
include_once($path . 'config/session.php');
include_once($incPath . 'common_functions.php');
include_once($incPath . 'function_database_query.php');

$infopage = pathinfo(__FILE__);
$_SESSION['page'] = $infopage['filename'];
$form = "Support";
$company_id = $company_name = '';
if (strpos($_SERVER[REQUEST_URI], "support_edit") == true) {
    $mode = "Edit";
} else if (strpos($_SERVER[REQUEST_URI], "support_view") == true) {
    $mode = "View";
    $id = $dbcon->real_escape_string($_REQUEST['id']);
//    $supportData = getSupportById($dbcon, $id);
    if (isset($_SESSION['LOGGED_IN']) && $_SESSION['LOGGED_IN'] == true && $_SESSION['domain'] == SUPPORT_URL) {
        $supportData = getSupportById($dbcon, $id);
        $support_url = ROOT;
    } else {
        $support_url = SUPPORT_URL;
        $info['id'] = $id;
        $info['mode'] = $mode;
        $json_data = json_encode($info);
        $supportData = curlData($json_data);
        $supportData = json_decode($supportData, true);
    }
} else {
    $mode = "Add";
    $form = "Create " . $form;

    //Amish Soni Start 05-02-2021
    $company_data = get_company_data($dbcon, $_SESSION['company_id']);
    if (isset($company_data) && $company_data) {
        $company_id = (isset($company_data['cmp_unique_id']) && $company_data['cmp_unique_id']) ? $company_data['cmp_unique_id'] : $company_id;
        $company_name = (isset($company_data['company_name']) && $company_data['company_name']) ? $company_data['company_name'] : $company_name;
    }
    //Amish Soni End 05-02-2021
}

function curlData($postData)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => SUPPORT_URL . "support/app/api/support.php",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => array(
            "content-type: application/json"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        $result = "cURL Error #:" . $err;
    } else {
        $result = $response;
    }

    return $result;
}

$set_mode = (!$supportData['cmp_unique_id'] && !$company_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>SUPPORT</title>
    <?php include_once($include . 'include_css_file.php'); ?>
</head>
<body>
<section id="container" class="sidebar-closed">
    <?php include_once($include . 'include_top_menu.php'); ?>
    <?php include_once($include . 'left_menu.php'); ?>
    <section id="main-content">
        <section class="wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <section class="panel">
                        <header class="panel-heading">
                            <h3><?= $mode . ' ' . $form ?></h3>
                        </header>
                        <div class="">
                            <ul class="breadcrumb">
                                <li><a href="<?= ROOT . 'dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
                                <li><a href="<?= ROOT . SUPPORT_ROOT . 'support_list' ?>"><?= $form ?></a></li>
                            </ul>
                        </div>
                    </section>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <section class="panel">
                        <header class="panel-heading">
                            <?= $form ?>
                        </header>
                        <div class="panel-body">
                            <form class="form-horizontal" role="form" id="support_add" action="javascript:;"
                                  method="post" name="support_add">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="department" class="col-md-3 control-label">Company ID *</label>
                                            <div class="<?php echo ($set_mode) ? 'col-md-5 col-xs-11' : 'col-md-8 col-xs-11' ?>">
                                                <input type="text" class="form-control" id="cmp_unique_id"
                                                       name="cmp_unique_id"
                                                       placeholder="Company ID" <?php echo ($mode == 'View') ? 'disabled' : 'readonly'; ?>
                                                       value="<?php echo ($mode == 'View') ? $supportData['cmp_unique_id'] : $company_id; ?>"/>
                                            </div>
                                            <?php if($set_mode) { ?>
                                            <div class="col-md-3">
                                                <a href="<?php echo ROOT.'setting/'.$_SESSION['company_id']; ?>" target="_blank">Set Company ID</a>
                                            </div>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="department" class="col-md-3 control-label">Company Name
                                                *</label>
                                            <div class="col-md-8 col-xs-11">
                                                <input type="text" class="form-control" id="company_name"
                                                       name="company_name"
                                                       placeholder="Company Name" <?php echo ($mode == 'View') ? 'disabled' : 'readonly'; ?>
                                                       value="<?php echo ($mode == 'View') ? $supportData['company_name'] : $company_name; ?>"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="department" class="col-md-3 control-label">Department *</label>
                                            <div class="col-md-8 col-xs-11">
                                                <input type="text" class="form-control" id="department"
                                                       name="department"
                                                       placeholder="Department" <?php echo ($mode == 'View') ? 'disabled' : ''; ?>
                                                       value="<?php echo ($mode == 'View') ? $supportData['department'] : ''; ?>"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="page_link" class="col-md-3 control-label">Page Link *</label>
                                            <div class="col-md-8 col-xs-11">
                                                <input type="text" class="form-control" id="page_link" name="page_link"
                                                       placeholder="Page Link" <?php echo ($mode == 'View') ? 'disabled' : ''; ?>
                                                       value="<?php echo ($mode == 'View') ? $supportData['page_link'] : ''; ?>"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="description" class="col-md-3 control-label">Description
                                                *</label>
                                            <div class="col-md-8 col-xs-11">
                                                <textarea class="form-control" name="description"
                                                          id="description"  <?php echo ($mode == 'View') ? 'disabled' : ''; ?>><?php echo ($mode == 'View') ? $supportData['description'] : ''; ?></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="description" class="col-md-3 control-label">Upload
                                                Documents </label>
                                            <div class="col-md-8 col-xs-11">
                                                <?php if ($mode == 'View') { ?>
                                                    <?php if ($supportData && $supportData['upload_document']) { ?>
                                                        <img src="<?php echo $support_url . SUPPORT_ROOT . 'view/upload/' . $supportData['upload_document']; ?>"
                                                             alt="" width="150" height="150"/>
                                                    <?php } else { ?>
                                                        No Document Found.
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <input type="file" class="form-control" id="documents"
                                                           name="documents" placeholder="Documents"/>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if ($mode == 'View') { ?>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="" class="col-md-3 control-label">Support Status</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <?php $support_status = getSupportStatusById($dbcon, $supportData['support_status_id']); ?>
                                                    <lable class="form-control"
                                                           disabled><?php echo $support_status['name']; ?></lable>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="" class="col-md-3 control-label">Due Date</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <lable class="form-control"
                                                           disabled><?php echo $supportData['due_date'] ? date('d-m-Y', strtotime($supportData['due_date'])) : ''; ?></lable>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="" class="col-md-3 control-label">Selected Employee</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <lable class="form-control"
                                                           disabled><?php echo find_user_name($dbcon, $supportData['emp_id']); ?></lable>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="" class="col-md-3 control-label">Approver Name</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <lable class="form-control"
                                                           disabled><?php echo $supportData['change_user']; ?></lable>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="" class="col-md-3 control-label">Approver Comment</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <lable class="form-control"
                                                           disabled><?php echo $supportData['change_comment']; ?></lable>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>

                                <div class="row">
                                    <?php if ($mode != 'View') { ?>
                                        <button type="submit" class="btn btn-success" id="save" name="save">Submit
                                        </button>
                                    <?php } ?>
                                    <a href="<?= ROOT . SUPPORT_ROOT . 'support_list' ?>" type="button"
                                       class="btn btn-danger">Cancel</a>
                                    <div class="col-md-3"></div>
                                </div>
                                <!--Vendor row end-->
                                <input type='hidden' name='mode' id='mode' value='<?= $mode ?>'/>
                                <input type="hidden" name="user_id" id="user_id"
                                       value="<?php echo $_SESSION['user_id']; ?>"/>
                                <input type="hidden" name="company_id" id="company_id"
                                       value="<?php echo $_SESSION['company_id']; ?>"/>
                                <input type="hidden" name="user_type" id="user_type"
                                       value="<?php echo $_SESSION['user_type']; ?>"/>
                            </form>
                        </div>
                    </section>
                </div>
            </div>
            <!--state overview end-->
        </section>
    </section>
    <!--main content end-->
    <!--footer start-->
    <?php include_once($include . 'footer.php'); ?>
    <!--footer end-->
</section>
<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include . 'include_js_file.php'); ?>
<script src="<?= ROOT ?><?= SUPPORT_ROOT ?>js/app/support.js?<?= time() ?>"></script>
<script>
    CKEDITOR.replace('description', {
        enterMode: CKEDITOR.ENTER_BR,
        toolbarGroups: [
            '/',
            {name: 'paragraph', groups: ['list', 'align']},
            {name: 'styles'},
            '/',
            {name: 'basicstyles'},
        ],
    });
</script>
</body>
</html>