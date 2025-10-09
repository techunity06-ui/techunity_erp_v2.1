<?php
  session_start();
  include("../../config/config.php");
  if(isset($_SESSION['LOGGED_IN']) && $_SESSION['LOGGED_IN'] == true) {
        header("Location: ".DOMAIN_CHEQUE."home");
  }
  $token = md5(rand(1000,9999));
  $_SESSION['token'] = $token;
//echo encrypt(date('Y-m-d'));
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
        <h3 class="text-center">Cheque 360</h3>
      </div>
      <div>
        <form id="FormLogin" style="margin-bottom: 0px !important;" class="form-horizontal">
          <div class="content">
            <div class="form-group">
              <div class="col-sm-12">
                <div class="input-group"> <span class="input-group-addon"><i class="fa fa-user"></i></span>
                  <input type="text" placeholder="USERNAME" id="username" class="form-control" autocomplete="on" required />
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-12">
                <div class="input-group"> <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                  <input type="password" placeholder="PASSWORD" id="password" class="form-control" autocomplete="on" required />
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
            <input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />
            <input type='hidden' name='ip_addr' id='ip_addr' value='' />
            <input type='hidden' name='DOMAIN_CHEQUE' id='DOMAIN_CHEQUE' value='<?php echo DOMAIN_CHEQUE; ?>' />
            <input type='hidden' name='redirect' id='redirect' value='<?php echo (isset($_GET['redirect']))?$dbcon->real_escape_string($_GET['redirect']):''; ?>' />
            <input type="hidden" id="FailError" value="INVALID USERNAME AND / OR PASSWORD !" />
            <!--<a data-target="#forget_password" data-toggle="modal" class="pull-left cursor">Forget Passowrd</a>-->
            <button class="btn btn-primary" id="btnLogin" data-dismiss="modal" type="submit">LOGIN</button>
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
<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="<?php echo ROOT_CHEQUE; ?>js/jquery.js"></script>
<script src="<?php echo ROOT_CHEQUE; ?>js/bootstrap/dist/js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo ROOT_CHEQUE; ?>js/jquery.flot/jquery.flot.js"></script>
<script type="text/javascript" src="<?php echo ROOT_CHEQUE; ?>js/jquery.flot/jquery.flot.pie.js"></script>
<script type="text/javascript" src="<?php echo ROOT_CHEQUE; ?>js/jquery.flot/jquery.flot.resize.js"></script>
<script type="text/javascript" src="<?php echo ROOT_CHEQUE; ?>js/jquery.flot/jquery.flot.labels.js"></script>
<script type="text/javascript" src="<?php echo ROOT_CHEQUE; ?>js/app/login.js"></script>
<script type="text/javascript" src="<?php echo ROOT_CHEQUE; ?>js/app/forgetpassword.js"></script>
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
<!-- Modal FORGOT PASSWORD -->
<div class="modal" id="forget_password" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="FormForgetPassword" style="margin-bottom: 0px !important;" class="form-horizontal">
        <div class="modal-header">
          <h3>FORGET PASSWORD</h3>
          <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">x</button>
        </div>
        <div class="modal-body">
          <div class="form-box-content">
            <div class="form-group">
              <div class="col-sm-12">
                <div class="input-group"> <span class="input-group-addon"><i class="fa fa-user"></i></span>
                  <input type="text" placeholder="USERNAME" id="usernameF" class="form-control" autocomplete="on" required />
                </div>
              </div>
            </div>
			<div class="form-group">
              <div class="col-sm-12">
                <div id="messageF"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="form-actions">
            <input type='hidden' name='tokenF' id='tokenF' value='<?php echo $token; ?>' />
            <input type='hidden' name='ip_addrF' id='ip_addrF' value='' />
            <input type='hidden' name='DOMAIN_CHEQUEF' id='DOMAIN_CHEQUEF' value='<?php echo DOMAIN_CHEQUE; ?>' />
            <input type='hidden' name='redirectF' id='redirectF' value='<?php echo (isset($_GET['redirect']))?$dbcon->real_escape_string($_GET['redirect']):''; ?>' />
            <input type="hidden" id="FailErrorF" value="INVALID USERNAME !" />
			<button class="btn btn-danger" data-dismiss="modal">Close</button>
            <button class="btn btn-primary" id="btnForgetPassword" type="submit">Forget Password</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>