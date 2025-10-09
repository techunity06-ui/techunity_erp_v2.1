<?php
  session_start();
  include("../../config/config.php");
  if(isset($_SESSION['LOGGED_IN']) && $_SESSION['LOGGED_IN'] == true) {
   //     header("Location: ".DOMAIN_CHEQUE."home");
  }
  $token = md5(rand(1000,9999));
  $_SESSION['token'] = $token;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php
		$TITLE = "Cheque 360 Setup Page";
		include("../common/head.php");
	?>
</head>
<body class="texture">
<div id="cl-wrapper" class="login-container">
  <div class="middle-login" style="top:30%">
    <div class="block-flat">
      <div class="header">
        <h3 class="text-center">Cheque 360 Setup</h3>
      </div>
      <div>
        <form id="Frmsetup" style="margin-bottom: 0px !important;" role="form"  parsley-validate novalidate action="javascript:;">
          <div class="content">
            <div class="form-group">
                <label class="control-label">Company Name</label>
				<input type="text" id="company_name" name="company_name" class="form-control parsley-validated" parsley-trigger="change" required>
			</div>
			<div class="form-group">
                <label class="control-label">Address</label>
				<textarea id="address" name="address" class="form-control parsley-validated" parsley-trigger="change" required></textarea>
			</div>
			<div class="form-group">
                <label class="control-label">Contact No.</label>
				<input type="text" id="contact_no" name="contact_no" class="form-control parsley-validated" parsley-trigger="change" parsley-type="digits" required>
			</div>		
			<div class="form-group">
                <label class="control-label">Email</label>
				<input type="email" id="email" name="email" class="form-control parsley-validated" parsley-trigger="change" parsley-type="email" required>
			</div>
			<div class="form-group">
                <label class="control-label">Password</label>
				<input type="password" id="password" name="password" class="form-control parsley-validated" parsley-trigger="change" required>
			</div>
			<!--<div class="form-group">
                <label class="control-label">Company Logo</label>
				<input type="file" id="company_logo" name="company_logo" class="form-control parsley-validated" required accept="image/*" parsley-max-file-size="42">
			</div>-->
            <div class="form-group">
              <div class="col-sm-12">
                <div id="message"></div>
              </div>
            </div>
          </div>
          <div class="foot">
            <input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />
            <input type='hidden' name='ip_addr' id='ip_addr' value='' />
            <input type='hidden' name='DOMAIN_CHEQUE' id='DOMAIN_CHEQUE' value='<?php echo DOMAIN_CHEQUE; ?>' />
            <input type='hidden' name='type' id='type' value='add' />
            <input type="hidden" id="FailError" value="INVALID USERNAME AND / OR PASSWORD !" />
           <!-- <a data-target="#forget_password" data-toggle="modal" class="pull-left cursor">Forget Passowrd</a>-->
            <button class="btn btn-primary" id="btnLogin" data-dismiss="modal" type="submit">Setup</button>
          </div>
        </form>
      </div>
    </div>
    <div class="text-center out-links"><a href="<?php echo D_URL; ?>" target="_blank">&copy; <?php echo date("Y")." ".DEVELOPER; ?></a></div>
  </div>
</div>
<div id='mask'>
  <div style='position:fixed;left: 45%;margin-left: -25%px;'> <img   src='<?php echo DOMAIN_CHEQUE ; ?>images/loading_lg.gif' />
    <h1> Loading ... </h1>
  </div>
</div>
<?php include("../common/js.php"); ?>
<script type="text/javascript" src="<?php echo ROOT_CHEQUE; ?>js/app/setup.js"></script>
<script type="text/javascript">
$(function(){
  $("#cl-wrapper").css({opacity:1,'margin-left':0});
});
$('#mask').height($(document).height());
$(window).load(function () {
	$('#mask').fadeOut('slow');
});
</script>
<script type="text/javascript">
	$("#username").focus();
	$.getJSON("http://jsonip.com?callback=?", function (data) {
		$("#ip_addr").val(data.ip);
	});
</script>
</body>
</html>