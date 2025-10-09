<div class="modal colored-header info " id="companylogin_modal" role="dialog" data-keyboard="false" data-backdrop="static" style="background-image: url(<?=DOMAIN?>view/img/brp_bg.png);background-size:cover;" >
	<div class="modal-dialog custom-width" style="width:450px">
		<div class="modal-content" >
			<div class="modal-header" >
			<!--style="background-color: #41a8ea;"<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				--><h3 style="padding: 0;margin: 0;color: #fff;">Password For ( <span id="login_company"></span> )</h3>
				
			</div>
			<div class="modal-body form">
			<div class="row">

				 <div class="col-md-12">
						<form class="form-horizontal" role="form" id="companylogin_add" action="javascript:;" method="post" name="companylogin_add">
							<!-- <div class="form-group">
							  <label class="col-md-4 control-label" style="text-align:left;line-height:25px">User Type *</label>
							  <div class="col-md-8 col-xs-11">
								<select class="form-control" id="loginusertype_id" name="loginusertype_id" required title="Select User Type" onchange="showAttendance(this.value)">
									<?//=getusertype($dbcon,0," and usertype_id!=1")?>
								</select>
                             </div>
							 </div> -->
							<div class="form-group">
							  <label class="col-md-4 control-label" style="text-align:left;line-height:25px">User Name *</label>
							  <div class="col-md-8 col-xs-11">
									<input type="text" class="form-control" placeholder="User name" name="loginusername" id="loginusername" required title="Enter User name" onchange="setUsertype(this.value)" onblur="setUsertype(this.value)" autocomplete="username"/>
								</div>
                             </div>
                             <div class="form-group" id="usertype" style="display: none;">
							  <label class="col-md-4 control-label" style="text-align:left;line-height:25px">User Type</label>
							  <div class="col-md-8 col-xs-11">
								<input type='hidden' name='loginusertype_id' id='loginusertype_id' value='' />
								<label name='loginusertype_label' id='loginusertype_label' style="margin-top:8px"></label>
                             </div>
							 </div>
							 <div class="form-group">
							  <label class="col-md-4 control-label" style="text-align:left;line-height:25px">Password *</label>
							  <div class="col-md-8 col-xs-11">
									<input type="password" class="form-control" placeholder="Password" name="login_password" id="login_password"  value="" autocomplete="current-password"/>
								</div>
                             </div>
                             <!-- <div class="form-group" id="ip_login1" style="display:none">
							  <label class="col-md-4 control-label" style="text-align:left;line-height:25px">IP Address *</label>
							  <div class="col-md-8 col-xs-11">
									<input type="text" class="form-control" placeholder="IP Address" name="login_password" id="login_password"  value=""/>
								</div>
                             </div> -->
							 <div class="form-group" id="emp_atten" style="display:none">
							  <label class="col-md-4 control-label" style="text-align:left;line-height:25px">Attendance *</label>
							  <div class="col-md-8 col-xs-11">
									<input type="radio" name="attendance" value="yes" />YES
									<input type="radio" name="attendance" value="no" />NO
								</div>
                             </div>
							 <div id="message"></div>
							<div class="col-md-4"></div>	  <div class="col-md-8 col-xs-11"> <a  href="javascript:;" onclick="open_forgetpass()" class="text-right"> Forgot Password?</a>
							 <button type="submit" class="btn btn-success">Submit</button>
							<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Back</button></div>
							<input type='hidden' name='ip_addr' id='ip_addr' value='' />
							<input type='hidden' name='logincompany_id' id='logincompany_id' value='' />
						
						</form>
					</div>
						  
				</div>
			</div>	
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>
<script type="text/javascript" src="<?=ROOT?>js/app/companylogin.js?<?=time()?>"></script>
<script type="text/javascript">
$("#username").focus();
<?if((basename($_SERVER['PHP_SELF']))=='login.php'){ ?>
	$.getJSON("http://jsonip.com?callback=?", function (data) {
		$("#ip_addr").val(data.ip);
	});
<?}?>
</script>
