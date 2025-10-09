<?php 
	session_start();
	include_once("../config/config.php");
	//include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Company";
	if(strpos($_SERVER[REQUEST_URI], "companyedit")==false)
	{
		$mode="Add";
	}
	else
	{
		$mode="Edit";
		$custid=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_company as cmp inner join users as usr on usr.user_rid=cmp.company_id where cmp.company_id=$custid";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>COMPANY</title>
<?php include_once('../include/include_css_file.php');?>
</head>
<body>
  <section id="container" >
      <?php include_once('../include/include_top_menu.php');?>
      <!--sidebar start-->
      <?php 
		include_once('../include/left_menu.php');
	  ?>
      <!--sidebar end-->
      <!--main content start-->
           <section id="main-content">
          <section class="wrapper">			 
			<div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
				  <section class="panel">
					  <header class="panel-heading">
						  <h3><?=$mode.' '.$form?></h3>
						</header>	
							<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							 
						  </ul>
						 </div>
					</section>
				  <!--breadcrumbs end -->
			  </div>	
             </div>
              <!--state overview start-->
		  <div class="row">			
			<div class="col-sm-12">
				<section class="panel">
				  <header class="panel-heading">
					  New <?=$form?>
					</header>	
					<div class="panel-body ">
	<form class="form-horizontal" role="form" id="company_name" action="javascript:;" method="post" name="company_name">
							<div class="row">
							<div class="col-md-10">
							<div class="form-group">
								<label class="col-md-3 control-label">Company ID *</label>
								<div class="col-md-6 col-xs-11">
									<input type="text" class="form-control" placeholder="Company ID" name="cmp_unique_id" id="cmp_unique_id"  value="<?=$rel['cmp_unique_id']?>" required title="Enter Company ID" />
								</div>
                             </div>
							<div class="form-group">
							  <label class="col-md-3 control-label">Company Name *</label>
							  <div class="col-md-6 col-xs-11">
									<input type="text" class="form-control" placeholder="Company Name" name="company_name" id="company_name"  value="<?=$rel['company_name']?>" required title="Enter Company Name" />
								</div>
                             </div>
							<div class="form-group">
							  <label class="col-md-3 control-label">Address *</label>
									<div class="col-md-6	 col-xs-11">
									<textarea id="address" name="address" class="ckeditor form-control" rows="10"><?=stripslashes($rel['address'])?></textarea> 
								</div>
                             </div>
							  <div class="form-group">
							  <label class="col-md-3 control-label">User Name *</label>
							  <div class="col-md-6 col-xs-11">
									<input type="text" class="form-control" placeholder="Username" name="username" id="username"  value="<?=$rel['user_mail']?>" <?=$mode=="Add" ? "required" : ""?>   title="Enter User name" />
								</div>
                             </div>
							 <div class="form-group">
							  <label class="col-md-3 control-label">Password *</label>
							  <div class="col-md-6 col-xs-11">
									<input type="password" class="form-control" placeholder="Password" name="password" id="password"  value="" <?=$mode=="Add" ? "required" : ""?> title="Password" />
								</div>
                             </div>
							 <div class="form-group">
							  <label class="col-md-3 control-label">Conform Password *</label>
							  <div class="col-md-6 col-xs-11">
									<input type="password" class="form-control" placeholder="Conform Password" name="c_password" id="c_password"  value="" <?=$mode=="Add" ? "required" : ""?> title="Conform Password" />
								</div>
                             </div>
							  <div class="form-group">
							  <label class="col-md-3 control-label">Head Logo</label>
							  <div class="col-md-6 col-xs-11">
							  	<input type="file" class="form-control" placeholder="Logo" name="logo" id="logo" accept="image/*" <?=$mode=="Add" ? "required" : "" ?> title="Select logo" />
								</div>
								<div class="col-md-3 col-xs-11">
								<?php 
									if($mode=="Edit" && !empty($rel['logo']))
									{
										echo '<img src="'.ROOT.LOGO.$rel['logo'].'" style="width:120px"/>';
									}
								?>
								</div>
								</div>
							
							<div class="form-group">
							  <label class="col-md-3 control-label">Bank Name</label>
							  <div class="col-md-6 col-xs-11">
									<input type="text" class="form-control" placeholder="Bank Name" name="bank_name" id="bank_name"  value="<?=$rel['bank_name']?>" />
								</div>
                             </div>
							 <div class="form-group">
							  <label class="col-md-3 control-label">A/c No</label>
							  <div class="col-md-6 col-xs-11">
									<input type="text" class="form-control" placeholder="A/c No" name="ac_no" id="ac_no"  value="<?=$rel['ac_no']?>" />
								</div>
                             </div>
							 <div class="form-group">
							  <label class="col-md-3 control-label">IFCS </label>
							  <div class="col-md-6 col-xs-11">
									<input type="text" class="form-control" placeholder="IFCS" name="ifcs" id="ifcs"  value="<?=$rel['ifcs']?>" />
								</div>
                             </div>
							 <div class="form-group">
							  <label class="col-md-3 control-label">Branch Name</label>
							  <div class="col-md-6 col-xs-11">
									<input type="text" class="form-control" placeholder="Branch Name" name="branch_name" id="branch_name"  value="<?=$rel['branch_name']?>" />
								</div>
                             </div>
							  <div class="form-group">
							  <label class="col-md-3 control-label">Pan Card No</label>
							  <div class="col-md-6 col-xs-11">
									<input type="text" class="form-control" placeholder="Pan Card No" name="pan_no" id="pan_no"  value="<?=$rel['pan_no']?>" />
								</div>
                             </div>
							 <div class="form-group">
							  <label class="col-md-3 control-label">Choose Security Question </label>
							  <div class="col-md-6 col-xs-11">
									<select class="form-control" name="question_id" id="question_id" required title="Select Question">
										 <?=getquestion($dbcon,$rel['question_id'])?> 
									<select>
								</div>
                             </div>
							 <div class="form-group">
							  <label class="col-md-3 control-label">Give Answer</label>
							  <div class="col-md-6 col-xs-11">
									<input type="text" class="form-control" placeholder="Give Answer" name="give_answer" id="give_answer"  value="" required title="Enter Give Answer" /> <?php /* ?> <?=decrypt($rel['answer'],$key)?> <?php */ ?>
								</div> 
                             </div>
                             <div class="form-group">
							  <label class="col-md-3 control-label">Currency</label>
							  <div class="col-md-6 col-xs-11">
									<select class="select2" name="currency_id" id="currency_id"  required title="Select Currency">
                                     <?=getcurrency($dbcon,$rel['currency_id']);?> 
                                     </select>
								</div>
                             </div>
							<button type="submit" class="btn btn-danger">Submit</button> &nbsp;
							<a href="<?=ROOT.'company_list'?>" type="button" class="btn btn-success">Cancel</a><div class="col-md-3"></div></div>
						</div><!--Vendor row end-->	
							<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
							<input type='hidden' name='eid' id='eid' value='<?=$rel['company_id']?>' />			  
							
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
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
	<script src="<?=ROOT?>js/app/checkcompany.js?<?=time()?>"></script>
   <script src="<?=ROOT?>js/app/company.js?<?=time()?>"></script>
    <script>
	
	$(".select2").select2({
		width: '100%'
	});
	$('.default-date-picker').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true
        });</script>
	

  </body>
</html>
