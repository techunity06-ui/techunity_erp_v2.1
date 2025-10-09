<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Company Preference";
	$mode="Edit";
	$company_id=$dbcon->real_escape_string($_REQUEST['id']);
        $query="select * from tbl_company where company_id=".$company_id;
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	
	$query2="select * from users where company_id=".$company_id;
	$rel2=mysqli_fetch_assoc($dbcon->query($query2));
	
        // Amish Soni Start 12-01-2021
	$setting_id = $crm_auto_mail = '';
	$comp_query = "SELECT * FROM `tbl_company_settings` where status = 0 and company_id=".$company_id;
	$companySettings = mysqli_fetch_assoc($dbcon->query($comp_query));
        
        $crm_auto_mail = '';
	if($companySettings) {
		$setting_id = $companySettings['id'];
		$crm_auto_mail = $companySettings['crm_auto_mail'];
                //$finance_settings = json_decode($companySettings['finance_settings'],true);
	}
	// Amish Soni End 12-01-2021
	
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>
<style>

</style>
</head>
<body>
<section id="container" >
        <?php include_once('../include/include_top_menu.php');?>
        <!--sidebar start-->
        <?php include_once('../include/left_menu.php');?>
        <!--sidebar end-->
        <!--main content start-->
        <section id="main-content">
            <section class="wrapper">
                <div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
				  <section class="panel">
					  <header class="panel-heading">
						  <h3><?= $rel['company_name'] ?></h3>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li class="active"><?=$form?></li>
						  </ul>
						 </div>
					</section>
				  <!--breadcrumbs end -->
			  </div>	
             </div>
             </div>
              <!--state overview start-->
		  <div class="row">
			
			<div class="col-md-12">
			<section class="panel">
				  <header class="panel-heading">
					  <?=$form?>
					<!-- Dimple Panchal Start 17-03-2021 -->
                                        <span class="tools pull-right">
                                                <a href="javascript:;" class="fa fa-chevron-down"></a>
                                        </span>
                                        <!-- Dimple Panchal End 17-03-2021  -->   
				  </header>
	  <div class="panel-body">
		<form class="form-horizontal" role="form" id="company_pref_add" action="javascript:;" method="post" name="company_pref_add">
			<div class="row">
				<div class="col-md-12">
					<div class="col-md-6">
					<div class="form-group">
					  <label class="col-md-4 control-label">Show Discount</label>
						<div class="col-md-7 col-xs-11">
							<input type="radio" class="" style="margin-left: 10px;width: 17px;height: 15px;" id="show_disc_yes" name="show_disc" value="1" <?php if($rel['show_disc']=='1'){echo 'checked';} ?>/> <label for="show_disc_yes" style="font-weight: bold;" title="Show Discount in Invoice Print">Yes</label>
							<input type="radio" class="" style="margin-left: 10px;width: 17px;height: 15px;" id="show_disc_no" name="show_disc" value="2" <?php if($rel['show_disc']=='2'){echo 'checked';} ?>/> <label for="show_disc_no" style="font-weight: bold;" title="Hide Discount in Invoice Print">No</label>
						</div>
					</div>
					</div>
					<div class="col-md-6">
					<div class="form-group">
					  <label class="col-md-3 control-label">Packing Charges</label>
						<div class="col-md-7 col-xs-11">
							<input type="radio" class="" style="margin-left: 10px;width: 17px;height: 15px;" id="show_charges_withouttax" name="show_charges" value="1" <?php if($rel['show_charges']=='1'){echo 'checked';} ?>/> <label for="show_charges_withouttax" style="font-weight: bold;" title="Show Packing Charges Without Tax">Without Tax</label>
							<input type="radio" class="" style="margin-left: 10px;width: 17px;height: 15px;" id="show_charges_withtax" name="show_charges" value="2" <?php if($rel['show_charges']=='2'){echo 'checked';} ?>/> <label for="show_charges_withtax" style="font-weight: bold;" title="Packing Charge is Taxable">
							Taxable</label>
						</div>
					</div>
					</div>
					<div class="col-md-6">
					<div class="form-group">
					  <label class="col-md-4 control-label">Letter Head Top Margin</label>
						<div class="col-md-7 col-xs-11">
							<input class="form-control" type="number" min="0" id="letter_head_top_margin" name="letter_head_top_margin" value="<?=$rel['letter_head_top_margin']?>" step="0.01"/> 
							<label for="letter_head_top_margin" style="font-weight: bold;" title="Please Specify Value in Inch only.">
							Note : Specify "Inch" Value.</label>
						</div>
					</div>
					</div>
					<div class="col-md-6">
					<div class="form-group">
					  <label class="col-md-3 control-label">Letter Head Bottom Margin</label>
						<div class="col-md-7 col-xs-11">
							<input class="form-control" type="number" min="0" id="letter_head_bottom_margin" name="letter_head_bottom_margin" value="<?=$rel['letter_head_bottom_margin']?>" step="0.01"/> 
							<label for="letter_head_bottom_margin" style="font-weight: bold;" title="Please Specify Value in Inch only.">
							Note : Specify "Inch" Value.</label>
						</div>
					</div>
					</div>
					
					<div class="col-md-6">
					<div class="form-group">
					  <label class="col-md-4 control-label">Letter Head Left Margin</label>
						<div class="col-md-7 col-xs-11">
							<input class="form-control" type="number" min="0" id="letter_head_left_margin" name="letter_head_left_margin" value="<?=$rel['letter_head_left_margin']?>" step="0.01"/> 
							<label for="letter_head_left_margin" style="font-weight: bold;" title="Please Specify Value in Inch only.">
							Note : Specify "Inch" Value.</label>
						</div>
					</div>
					</div>
					<div class="col-md-6">
                                            <div class="form-group">
                                                    <label class="col-md-3 control-label">Letter Head Right Margin</label>
                                                    <div class="col-md-7 col-xs-11">
                                                            <input class="form-control" type="number" min="0" id="letter_head_right_margin" name="letter_head_right_margin" value="<?=$rel['letter_head_right_margin']?>" step="0.01"/> 
                                                            <label for="letter_head_right_margin" style="font-weight: bold;" title="Please Specify Value in Inch only.">
                                                            Note : Specify "Inch" Value.</label>
                                                    </div>
                                            </div>
					</div>
					
					<div class="col-md-6">
					  <div class="form-group">
						<label class="col-md-4 control-label">Software Type</label>
						<div class="col-md-7 col-xs-11">
							<?php //var_dump($rel2); ?>
							<select id="soft_type" name="soft_type" class="select2">
								<option value="0" <?php if($rel['software_type']=="0"){ echo "selected='selected'";}?> >Invoice</option>
								<option value="1" <?php if($rel['software_type']=="1"){ echo "selected='selected'";}?>>Invoice&Purchase </option>
								<option value="2" <?php if($rel['software_type']=="2"){echo "selected='selected'";}?>>Invoice,Purchase&Passbook </option>
								<option value="3" <?php if($rel['software_type']=="3"){ echo "selected='selected'";}?>>Invoice,Purchase,Passbook&Stock </option>
							</select>
						<input type="hidden" value="<?=$rel['software_type']?>"/>
						</div>
					  </div>
					</div>
					<div class="clearfix"></div>
					
					<button type="submit" class="btn btn-success">Submit</button> &nbsp;
					<!--<a href="<?=ROOT.'dashboard/'?>" type="button" class="btn btn-info">cancel</a><div class="col-md-3">-->
					<a  type="button" class="btn btn-info" href="<?=ROOT.'company_list'?>">cancel</a>
					<div class="col-md-3">
				</div>
			</div><!--Vendor row end-->	
			<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
			<input type='hidden' name='eid' id='eid' value='<?=$company_id?>' />
			<input type='hidden' name='user_id' id='user_id' value='<?=$rel2['user_id']?>' />
				
		</form>
	  </div>
				  </section>
			</div>
		  
                
                <!-- Dimple Panchal Start 17-03-2021 -->
                <!-- CRM Settings here -->
                <div class="col-sm-12">
                        <section class="panel">
                                <header class="panel-heading">
                                        CRM Settings
                                        <span class="tools pull-right">
                                                <a href="javascript:;" class="fa fa-chevron-down"></a>
                                        </span>
                                </header>	
                                <div class="panel-body ">
                                        <form class="form-horizontal" role="form" id="crm_settings" action="javascript:;" method="post" name="crm_settings">
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <div class="form-group">
                                                            <label class="col-md-3 control-label">Send Mail Automatically *</label>
                                                            <div class="col-md-6 col-xs-11">
                                                                <select class="select2" name="crm_auto_mail" id="crm_auto_mail">
                                                                    <option value="">Select Option</option>
                                                                    <option value="Yes" <?=((strtolower($crm_auto_mail) == 'yes')? "selected='selected'" : '')?>>Yes</option>
                                                                    <option value="No" <?=((strtolower($crm_auto_mail) == 'no')? "selected='selected'" : '')?>>No</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-3 control-label">Quotation Validity*</label>
                                                            <div class="col-md-6 col-xs-11">
                                                                <select class="select2" name="quot_validity" id="quot_validity">
                                                                    <option value="">Select Option</option>
                                                                    <?php for($i=1;$i <= 100; $i++){ ?>
                                                                    <option value="<?= $i ?>" <?=(($rel['quot_validity'] == $i)? "selected='selected'" : '')?>><?= $i ?> Days</option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3"></div>
                                                        <button type="submit" class="btn btn-success">Submit</button> &nbsp;
                                                        <div class="col-md-3"></div>
                                                        </div>
                                                </div><!--Vendor row end-->	
                                                <input type='hidden' name='mode' id='mode' value='crm_settings' />
                                                <input type='hidden' name='eid' id='eid' value='<?=$company_id?>' />
                                                <input type='hidden' name='setting_id' id='setting_id' value='<?php echo $setting_id; ?>' />				  

                                        </form>
                                </div>
                        </section>
                </div>
                
                
                <!-- Finance Settings here -->
                <?php
                $finance_query = "SELECT * FROM `tbl_finance_setting` where status = 0 and company_id=".$company_id;
                $finance_settings = brp_mysqli_fetch_assoc($dbcon->query($finance_query));
                ?>
                <div class="col-sm-12">
                        <section class="panel">
                                <header class="panel-heading">
                                        Finance Settings
                                        <span class="tools pull-right">
                                                <a href="javascript:;" class="fa fa-chevron-down"></a>
                                        </span>
                                </header>	
                                <div class="panel-body ">
                                        <form class="form-horizontal" role="form" id="finance_settings" action="javascript:;" method="post" name="finance_settings">
                                                <div class="row">
                                                        <div class="col-md-10">
                                                            <div class="form-group">
                                                                  <label class="col-md-3 control-label">Cheque Printer Align</label>
                                                                  <div class="col-md-6 col-xs-11">
                                                                          <select id="print_align" name="print_align" class="select2">
                                                                                  <option value="0" <?= ($finance_settings['print_align']=="0") ? "selected" : "" ?> >Default</option>
                                                                                  <option value="1" <?= ($finance_settings['print_align']=="1") ? "selected" : "" ?> >Left</option>
                                                                                  <option value="2" <?= ($finance_settings['print_align']=="2") ? "selected" : ""  ?> >Right</option>
                                                                          </select>
                                                                          <label for="print_align" style="font-weight: bold;" title="Re- Log in To Change this setting">
                                                                          Note : Re-Log in To Change Printer setting.</label>
                                                                  </div>
                                                            </div>
                                                            <div class="form-group">
                                                              <label class="col-md-3 control-label">Invoice Series Same</label>
                                                                    <div class="col-md-6 col-xs-11">
                                                                            <input type="radio" class="" style="margin-left: 10px;width: 17px;height: 15px;" id="series_same_yes" name="series_same" value="1" <?= ($finance_settings['series_same']=='1')? 'checked' : '' ?>/> <label for="series_same_yes" style="font-weight: bold;" title="Invoice Series Same">Yes</label>
                                                                            <input type="radio" class="" style="margin-left: 10px;width: 17px;height: 15px;" id="series_same_no" name="series_same" value="2" <?= ($finance_settings['series_same']=='2') ? 'checked' : '' ?>/> <label for="series_same_no" style="font-weight: bold;" title="Invoice Series Diffrent">No</label>
                                                                    </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="col-md-3 control-label">Manage Inventory *</label>
                                                                <div class="col-md-6 col-xs-11">
                                                                    <select class="select2" name="inventory_management" id="inventory_management" required>
                                                                        <option value="">Select Option</option>
                                                                        <option value="1" <?=(($finance_settings['inventory_management'] == 1)? "selected='selected'" : '')?>>Yes</option>
                                                                        <option value="0" <?=(($finance_settings['inventory_management'] == 0)? "selected='selected'" : '')?>>No</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="col-md-3 control-label">TCS Applicable*</label>
                                                                <div class="col-md-6 col-xs-11">
                                                                    <select class="select2" name="tcs_applicable" id="tcs_applicable" required>
                                                                        <option value="">Select Option</option>
                                                                        <option value="1" <?=(($finance_settings['tcs_applicable'] == 1)? "selected='selected'" : '')?>>Yes</option>
                                                                        <option value="0" <?=(($finance_settings['tcs_applicable'] == 0)? "selected='selected'" : '')?>>No</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="col-md-3 control-label">Invoice Condition Content</label>
                                                                <div class="col-md-9 col-xs-11">
                                                                        <textarea class="form-control" placeholder="Invoice Condition" name="condition" id="condition" ><?= $finance_settings['invoice_conditions'] ?></textarea>
                                                                </div>
                                                            </div>
                                                        <div class="col-md-3"></div>
                                                        <button type="submit" class="btn btn-success">Submit</button> &nbsp;
                                                        <div class="col-md-3"></div>
                                                        </div>
                                                </div><!--Vendor row end-->	
                                                <input type='hidden' name='mode' id='mode' value='finance_settings' />
                                                <input type='hidden' name='eid' id='eid' value='<?=$company_id?>' />
                                                <input type='hidden' name='setting_id' id='setting_id' value='<?= $finance_settings['id']; ?>' />				  

                                        </form>
                                </div>
                        </section>
                </div>
                <!-- Dimple Panchal End 17-03-2021 -->
                
                </div>
                <!--state overview end-->
          </section>
      </section>
      <!--main content end-->
      <!--footer start-->
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>
<!-- Modal -->
<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>
<script src="<?=ROOT?>js/app/company_pref.js"></script>
<script>
$(".select2").select2({
	width: '100%'
});
CKEDITOR.replace( 'condition', {
	enterMode: CKEDITOR.ENTER_BR
});
</script>
</body>
</html>
