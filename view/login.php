<?php 
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	if(isset($_SESSION['LOGGED_IN']) && $_SESSION['LOGGED_IN'] == true && $_SESSION['domain']==DOMAIN) {
        header("Location: ".DOMAIN."dashboard");
	}
	else if(isset($_SESSION['LOGGED_IN']) && $_SESSION['domain']!=DOMAIN) {
        header("Location: ".DOMAIN."logout");
	
	}
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	// error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>LOGIN</title>
<?php include_once('../include/include_css_file.php');?>
<style>
    .modal-backdrop {
        background-color: inherit;
    }
</style>
</head>
 <body class="login-body">	
    
          <!-- Modal -->
          <div class="modal colored-header info " id="myModal" role="dialog" data-keyboard="false" data-backdrop="static">
              <div class="modal-dialog">
                  <div class="modal-content">
                      <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Password Forgot </h4>
                      </div>
                      <div class="modal-body">
                          <p>Enter Question.</p>
									<select class="form-control" name="forgotquestion_id" id="forgotquestion_id">	
										<?php //=getquestion($dbcon,$rel['question_id'])?>
									<select>
									<label class="error" id="error_companyid"></label>
						  <p>Your Answer.</p>
									<input type="text" class="form-control" placeholder="Give Answer" name="forgotgive_answer" id="forgotgive_answer"  value="" />
							<label class="error" id="error_answer"></label>
							<p><div id="forgot_message"></div></p>
                      </div>
					  
                      <div class="modal-footer">
                          
				<input type="hidden" name="forgot_companyid" id="forgot_companyid"  value="" />
				<input type="hidden" name="forgot_usertype" id="forgot_usertype"  value="" />
							
						  <button data-dismiss="modal" class="btn btn-default" onclick="close_forgetpass()" type="button">Cancel</button>
                          <button class="btn btn-success" onclick="check_forgotpass()" type="button">Submit</button>
                      </div> 
                  </div>
              </div>
          </div>
          <!-- modal -->
      
    </div>
	<?php include_once('../include/include_js_file.php');?> 
</body>
<script src="<?=ROOT?>js/app/login.js"></script>

</html>
