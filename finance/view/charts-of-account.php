<?php 
    session_start();
    $path = '../../';
    $include1 = '../include/';
    $include = '../../include/';
    include_once($path."config/config.php");
    include_once($path."config/session.php");
    include_once(COMMON_FUNCTION_PATH."common_functions.php");
    include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");

	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$info = pathinfo( __FILE__ );
	$_SESSION['page']=$info['filename'];
	$form='Charts of Account'; 
    //Ankit Sompura 09-01-2021
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
        FINANCE_CHARTS_OF_ACCOUNT_LIST,
        FINANCE_CHARTS_OF_ACCOUNT_CREATE,
        FINANCE_CHARTS_OF_ACCOUNT_EDIT,
        FINANCE_CHARTS_OF_ACCOUNT_DELETE
    ]);
    if(!in_array(FINANCE_CHARTS_OF_ACCOUNT_LIST,$bulkAccessArray)){
       header("Location: ".DOMAIN."permission_access");
    }
?>
<!DOCTYPE html> 
<html lang="en">
<head>
    <title>CHART OF ACCOUNT</title>
<link rel="stylesheet" type="text/css" href="<?= ROOT ?>assets/fuelux/css/tree-style.css" />
<?php include_once($include.'include_css_file.php');?>
</head>
<body>
    <style>
        .tree_li {
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .fa {
            font-size: larger !important;
        }
        .tree .tree-actions {
            right: 700px !important;
        }
        .tree .tree-actions .group-balance{
            right: 10px !important;
        }
        .flt-right{
            float: right;
        }
    </style>
  <section id="container" >
      <?php include_once($include.'include_top_menu.php');?>
      <!--sidebar start-->
      <?php include_once($include.'left_menu.php');?>
      <!--sidebar end-->
      <!--main content start-->
      <section id="main-content">
            <section class="wrapper">
                    <div class="row">
                            <div class="col-lg-12">
                                <!--breadcrumbs start -->
                                <section class="panel">
                                    <header class="panel-heading">
                                        <h3><?=$form?></h3>
                                    </header>	
                                    <div class="">
                                        <ul class="breadcrumb">
                                            <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                                            <li class="active">Chart of Accounts</li>
                                        </ul>
                                    </div>
                                </section>
                                <!--breadcrumbs end -->
                            </div>	
                    </div>
                    <!--formula overview start-->
                    <div class="row">
                        <div class="col-md-12">
                            <section class="panel">
                                    <header class="panel-heading">
                                        <?=$form?>
                                    </header>
				  <div class="panel-body">
<!--				  <div class="adv-table">
                                    <table  class="display table table-bordered table-striped" id="dynamic-table">
                                        <thead>
                                        <tr>
                                                      <th>Sr. NO.</th>
                                                      <th>Account Name</th>
                                                      <th>Parent Account</th>
                                                      <th class="hidden-phone">Action</th>					  
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
				  </div>-->
                                    <?php
                                        //trigger exception in a "try" block
                                        $html = '';
                                        $html .= '<div id="FlatTree4" class="tree tree-solid-line">';
                                        //$groups = array_column($dbcon->query("SELECT g_id FROM `tbl_group` WHERE g_status = 0 And `g_pid`= 0")->fetch_all(MYSQLI_ASSOC), 'g_id');
                                        $group_qry = "SELECT g_id, g_name FROM `tbl_group` WHERE g_status = 0 And `g_pid` = 0";
                                        $result = brp_mysqli_query($dbcon,$group_qry);
                                        $groups = brp_mysqli_fetch_all($result);
                                        //echo '<pre>'; print_r($groups); exit;
                                        if($groups){
                                            foreach ($groups as $group) {
                                                $html .= '<div class = "tree-folder" id="li_'.$group['g_id'].'">
                                                        <div class="tree-folder-header">
                                                            <i class="fa fa-folder" onClick="show_sub_group(this,'.$group['g_id'].');"></i>
                                                            <div class="tree-folder-name">';
                                                $html .= '<span id="group_name_'.$group['g_id'].'">'.brp_ucwords(brp_strtolower($group['g_name'])).'</span>';
                                                
                                                
                                                if(in_array(FINANCE_CHARTS_OF_ACCOUNT_EDIT,$bulkAccessArray)){
                                                    $edit_icon = '<a style="color: inherit;" onClick="edit_group('.$group['g_id'].');"><i class="fa fa-pencil"></i></a>';
                                                }
                                                $delete_icon = '<a style="color: inherit;" onClick="delete_group('.$group['g_id'].')"><i class="fa fa-trash-o"></i></a>';
                                                $has_child = $dbcon->query("select g_id FROM `tbl_group` WHERE g_status = 0 and g_pid =".$group['g_id']." and company_id=".$_SESSION['company_id'])
                                                    ->fetch_object()->g_id;
                                                
                                                //$active_icon = '<i class="fa fa-check-square-o"></i>';
                                                if($has_child){
                                                    $delete_icon = '';
                                                    $active_icon = '';
                                                }
                                                if(in_array(FINANCE_CHARTS_OF_ACCOUNT_CREATE,$bulkAccessArray)){
                                                    $add_icon = '<a style="color: inherit;" onClick="add_group('.$group['g_id'].')"><i class="fa fa-plus"></i></a>';
                                                }
                                                $html .= '<div class="tree-actions">
                                                        '.$add_icon.'
                                                        '.$edit_icon.'
                                                        '.$delete_icon.'
                                                        '.$active_icon;
                                                $html .= '</div>';
                                                $html .= '<span class="group-balance flt-right">'.indian_number(get_group_balance($dbcon,$group['g_id']),2).'</span>
                                                        </div>
                                                    </div>';
                                                if($has_child){
                                                    $html .= '<div class="tree-folder-content" id="subgroup_'.$group['g_id'].'" style="display:none">';
                                                    $html .= get_chart_of_account_tree($dbcon, $group['g_id']);
                                                    $html .= '</div>';
                                                }
                                                $html .= '</div>';
                                            }
                                            
                                        }
                                        $html .= '</div>';
                                        echo $html;
                                    ?>
                                    </div>
                                </section>
			</div>
		  </div>
		  
		  <!--formula overview end-->
          </section>
      </section>
      <!--main content end-->
      <!--footer start-->
	<?php include_once($include.'footer.php');?>
      <!--footer end-->
  </section>

<?php if(in_array(FINANCE_CHARTS_OF_ACCOUNT_CREATE,$bulkAccessArray)){ ?>
<!-- Add Modal start -->
<div class="modal colored-header info" id="ModalAddAccount" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
                            <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h3>Add Account</h3>
			</div>
			<div class="modal-body form">
                            <form id="group_add" role="form" method="post" novalidate>
                                <div class="col-md-12" style="margin-top: 20px;">
                                    <div class="form-group">
                                        <label for="unitid" class="col-md-4 control-label">Parent Group:</label>
                                        <div class="col-md-8" id="parent_name" name="parent_name">
                                        </div>
                                    </div>	
                                </div>
                                <div class="clearfix"></div>
                                <div class="col-md-12" style="margin-top: 20px;">
                                    <div class="form-group">
                                        <label for="unitid" class="col-md-4 control-label">Group Name*:</label>
                                        <div class="col-md-8">
                                            <input class="form-control" type='text' name='g_name' id='g_name' value='' />
                                        </div>
                                    </div>	
                                </div>
<!--				<div class="form-group">
                                    <label for="g_name">group Name*</label>
                                    <input class="form-control" type='text' name='g_name' id='g_name' value='' />
				</div>	

				<div class="form-group">
                                    <label for="g_parent">Parent group</label>
                                    <select class="select2" name="g_parent" id="g_parent">
                                         <?//= get_all_group($dbcon,''); ?>
                                    </select>
				</div>-->

				<div class="form-group">
                                    <input type="hidden" class="form-control" name="g_form" id="g_form" />
				</div>					
								
			
                                <div class="modal-footer" style="margin-top: 55px;">
                                    <input type="hidden" name="token" id="add_token" value="<?php echo $token; ?>" />
                                    <input type="hidden" name="g_parent" id="g_parent" value="" />
                                    <button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
                                    <button class="btn btn-info btn-flat" type="submit">Add</button>
                                </div>
                            </form>
                        </div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>
<!-- Add modal end -->    
<?php } ?>

<?php if(in_array(FINANCE_CHARTS_OF_ACCOUNT_EDIT,$bulkAccessArray)){ ?>
<!-- Edit Modal start -->
<div class="modal colored-header info" id="ModalEditAccount" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Edit Account</h3>
				
			</div>
			<div class="modal-body form">
			<form id="group_edit" role="form" method="post" novalidate>
                            <div class="col-md-12" style="margin-top: 20px;">
				<div class="form-group">
                                    <label for="unitid" class="col-md-4 control-label">Parent group</label>
                                    <div class="col-md-8" id="e_parent_name" name="e_parent_name">
                                    </div>
<!--                                    <div class="col-md-8">
                                        <select class="select2" name="e_g_parent" id="e_g_parent">
                                            <?//= get_all_group($dbcon,''); ?>
                                        </select>
                                    </div>-->
				</div>	
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-md-12" style="margin-top: 20px;">
				<div class="form-group">
                                    <label for="unitid" class="col-md-4 control-label">group Name*</label>
                                    <div class="col-md-8">
                                        <input class="form-control" type='text' name='e_g_name' id='e_g_name' value='' />
                                    </div>
				</div>	
                            </div>
                            
                            
                            
<!--				<div class="form-group">
				   <label for="unitid">Parent group</label>
				   <select class="select2" name="e_g_parent" id="e_g_parent">
					 <?//= get_all_group($dbcon,''); ?>
				   </select>
				</div>-->

				<div class="form-group">
				   <input type="hidden" class="form-control" name="e_g_form" id="e_g_form" />
				</div>					
								
			
                                <div class="modal-footer" style="margin-top: 55px;">
                                    <input type="hidden" name="token" id="edit_token" value="<?php echo $token; ?>" />
                                    <input type="hidden" name="edit_id" id="edit_id" value="" />
                                    <input type="hidden" name="e_g_parent" id="e_g_parent" value="" />
                                    <button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
                                    <button class="btn btn-info btn-flat" type="submit">Update</button>
                                </div>
                            </form>
                        </div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>
<!-- Edit modal end -->
<?php } ?>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'include_js_file.php');?>  
<script src="<?=ROOT.FINANCE_ROOT?>js/app/chart_of_account.js?<?=time()?>"></script>
<script>
$(".select2").select2({
		width: '100%'
});
</script>
</body>
</html>
