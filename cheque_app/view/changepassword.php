<?php
  session_start();
  include("../../config/config.php");
  if(isset($_SESSION['LOGGED_IN']) && $_SESSION['LOGGED_IN'] == true) {
        header("Location: ".DOMAIN_CHEQUE."home");
  }
  $token = md5(rand(1000,9999));
  $_SESSION['token'] = $token;
  $query="select * from coro_users where user_key='".$_REQUEST['code']."'";
  $rs=$dbcon->query($query);
  $rel_data=mysqli_fetch_assoc($rs);
  
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php
		$TITLE = "Cheque 360";
		include("../common/head.php");
	?>
</head>
<body class="texture">
<div id="cl-wrapper" class="login-container">
  <div class="middle-login">
    <div class="block-flat">
      <div class="header">
        <h3 class="text-center">Change Password</h3>
      </div>
      <div>
	  
        <form id="FormChangepass" style="margin-bottom: 0px !important;" class="form-horizontal">
		<?phpif(mysqli_num_rows($rs)>0) {?>
          <div class="content">
			
            <div class="form-group">
              <div class="col-sm-12">
                <div class="input-group"> <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                  <input type="password" placeholder="NEW PASSWORD" id="new_pass" name="new_pass" class="form-control" minlength="7" maxlength="20" autocomplete="on" required />
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-12">
                <div class="input-group"> <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                  <input type="password" maxlength="20" placeholder="RETYPE PASSWORD" id="retype_pass" class="form-control" autocomplete="on" required />
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-12">
                <div id="message"></div>
              </div>
            </div>
          </div>
          <div class="foot">
			<input type='hidden' name='emailid' id='emailid' value='<?php echo $rel_data['user_mail']; ?>' />
            <input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />
            <input type='hidden' name='ip_addr' id='ip_addr' value='' />
            <input type='hidden' name='DOMAIN_CHEQUE' id='DOMAIN_CHEQUE' value='<?php echo DOMAIN_CHEQUE; ?>' />
            <input type='hidden' name='redirect' id='redirect' value='<?php echo (isset($_GET['redirect']))?$dbcon->real_escape_string($_GET['redirect']):''; ?>' />
            <input type="hidden" id="FailError" value="INVALID USERNAME AND / OR PASSWORD !" />            
            <button class="btn btn-primary" id="btnChangepass" data-dismiss="modal" type="submit">LOGIN</button>
          </div>
		  <?php}
		else
		{ ?>
			
			<div class="form-group">
              <div class="col-sm-12">
                <div id="message12" style="text-align:center;color:red"><h4>Invalid Link. Please Retry<h4></div>
              </div>
            </div>
			
		<?php}
		?>
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
<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="<?php echo ROOT_CHEQUE; ?>js/jquery.js"></script>
<script src="<?php echo ROOT_CHEQUE; ?>js/bootstrap/dist/js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo ROOT_CHEQUE; ?>js/jquery.flot/jquery.flot.js"></script>
<script type="text/javascript" src="<?php echo ROOT_CHEQUE; ?>js/jquery.flot/jquery.flot.pie.js"></script>
<script type="text/javascript" src="<?php echo ROOT_CHEQUE; ?>js/jquery.flot/jquery.flot.resize.js"></script>
<script type="text/javascript" src="<?php echo ROOT_CHEQUE; ?>js/jquery.flot/jquery.flot.labels.js"></script>
<script type="text/javascript" src="<?php echo ROOT_CHEQUE; ?>js/app/changepassword.js"></script>
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