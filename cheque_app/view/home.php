<?php
	session_start();
	include("../../config/config.php");
	include("../../config/session.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php
		$TITLE = "Home";
		include("../common/head.php");
	?>
</head>
<body>
<div id="cl-wrapper" class="sb-collapsed">
	<?php
		include("../common/menu.php");
	?>
	<div class="container-fluid" id="pcont">
		<?php
			include("../common/header.php");
		?>
  
    <div class="cl-mcont">
	<?php
				include("../common/user_steps.php");
			?>	
	<div class="row">   
		<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
			<div class="block-flat">
				<div class="header">							
					<h3>Quick Search</h3>
				</div>
				<div class="content">
					<form id="FormSearch" class="form-horizontal" role="form" method="post">
						<div class="form-group">
							<div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
								<input type="text" name="q" id="q" class="form-control" placeholder="Search " required>
							</div>
							<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
								<button type="submit" class="btn btn-default"><i class="fa fa-eye"></i><span class="hidden-xs">Search</span></button>
							</div>
						</div>
					</form>
					<div id="results"></div>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
			<div class="col-xs-12 col-sm-12 col-md-6 col-md-6">
				<?php
					$query = $dbcon -> query("SELECT COUNT(`cheque_id`) as r FROM `coro_cheques` WHERE `cheque_of`= '$_SESSION[company_id]'");
					$data = $query -> fetch_assoc();
				?>
				<div class="fd-tile detail tile-blue">
					<div class="content"><h1 class="text-left"><?php echo $data['r']; ?></h1><p>Cheques</p></div>
					<div class="icon"><i class="fa fa-reply"></i></div>
					<a class="details" >Printed So Far <span><i class="fa fa-arrow-circle-right pull-right"></i></span></a>
				</div>
			</div>
			<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
				<div class="fd-tile detail tile-lemon" id="analog-clock">
					<div class="content"><h1 class="text-left"><span class="hour">00</span><span class="min">00</span><span class="sec">00</span><span class="meridiem">00</span></h1><p>Indian Standard Time</p></div>
					<div class="icon"><i class="fa fa-globe"></i></div>
					<a class="details" >Clock <span><i class="fa fa-arrow-circle-right pull-right"></i></span></a>
				</div>
			</div>
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
				<div class="block-flat">
					<table class="no-border red">
						<thead class="no-border">
							<tr>
								<th>Account</th>
								<th>Bank</th>
								<th>Cheques Remaining</th>
							</tr>
						</thead>
						<tbody class="no-border-x">
						<?php
						
							$query = $dbcon -> query("SELECT `acc_name`, `acc_number`, `bank_name`,`acc_chequeleft` FROM `account_mst` as acc INNER JOIN `bank_mst` as bank ON bank.`bankid` = acc.`bankid`  WHERE  `acc_status` = '0' and acc.company_id=".$_SESSION['company_id']." and acc_type!=1 ORDER BY `acc_name`,`bank_name`");
							while($r = $query -> fetch_assoc()) {
								if($r['acc_chequeleft'] == 0) {
									$color = "style='background-color:#F6CECE;color:#000;font-weight:bold;'";
								}
								else if($r['acc_chequeleft'] < 10) {
									$color = "style='background-color:#CAC0E2;color:#000;font-weight:bold;'";
								}
								else {
									$color = "";
								}
								echo '
								<tr>
									<td '.$color.'>'.($r['acc_name']).'</td>
									<td '.$color.'>'.($r['bank_name']).'</td>
									<td '.$color.'>'.$r['acc_chequeleft'].'</td>
								</tr>';
							}
						?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
    </div>
		
		</div>
	</div>
	
	
</div>
  
	<?php include("../common/js.php"); ?>
	
	<script type="text/javascript">
		$(document).ready(function() {
			$('#analog-clock').clock({offset: '+5.5', type: 'digital'});
			
			$("#FormSearch").submit(function(e) {
				e.preventDefault();
				Loading(true);
				var form_data = {
					type : "search",
					q : $("#q").val()
				};
				
				$.ajax({
					type: "POST",
					cache:false,
					url: root_domain+'app/home/',
					data: form_data,
					success: function(response)
					{
						Unloading();
						$("#results").html(response);
					}
				});
			});
		});
	</script>
</body>
</html>
