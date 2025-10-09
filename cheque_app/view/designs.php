<?php
	session_start();
	include("../../config/config.php");
	include("../../config/session.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php
		$TITLE = "Cheque Designs";
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
			<div class="page-head">
				<h2><?php echo $TITLE; ?></h2>
				<ol class="breadcrumb">
				  <li><a href="<?php echo DOMAIN_CHEQUE.'home'; ?>">Home</a></li>
				  <li><a href="<?php echo DOMAIN_CHEQUE.'cheque'; ?>">Cheques</a></li>
				  <li class="active"><?php echo $TITLE; ?></li>
				</ol>
			</div>		
			<div class="row">
				<div class="hidden-xs hidden-sm col-md-12 col-lg-12">
					<div class="pull-left">
						<button onClick="window.location.href='<?php echo DOMAIN_CHEQUE; ?>cheque-design';" type="button" class="btn btn-success"><i class="fa fa-bars"></i> New Design</button>
						<input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
					<div class="block-flat">
						<div class="content">
							<div class="table-responsive">
								<table id="datatable" class="no-border red">
									<thead class="no-border">
										<tr>
											<th>SR</th>
											<th>BANK</th>
											<th>&nbsp;</th>
										</tr>
									</thead>
									<tbody class="no-border-x">
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>			
		</div>
		
		
	</div> 
</div>
	<?php include("../common/js.php"); ?>

	<script type="text/javascript">
	var designTable, preview_id=0;
		$(document).ready(function(){
			//initialize the javascript
			//Basic Instance
			designTable = $("#datatable").dataTable({
				"bAutoWidth" : false,
				"bFilter" : true,
				"bSort" : false,
				"bProcessing": true,
				"bServerSide" : true,
				"oLanguage": {
						"sLengthMenu": "_MENU_",
						"sProcessing": "<img src='"+root_domain+"images/loading.gif'/> Loading ...",
						"sEmptyTable": "NO DESIGN ADDED YET !",
				},
				"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
				"iDisplayLength": 10,
				"sAjaxSource": root_domain+'app/design/',
				"fnServerParams": function ( aoData ) {
					aoData.push( { "name": "type", "value": "fetch" } );
				},
				"fnDrawCallback": function( oSettings ) {
					$('.ttip, [data-toggle="tooltip"]').tooltip();
				}
			}).fnSetFilteringDelay();
			
			$('#ModalPreview').on('show', function () {
				console.log("Src loaded");
			});
		});
		
		
		var editReq = null;
		function preview_design(id) {
			$('#preview_frame').attr("src","");
			preview_id = id;
			if(editReq != null)
				editReq.abort();
			$('#ModalPreview').modal({show:true});
			$('#preview_frame').attr("src",root_domain+'preview-design/'+preview_id);
		}
		
		function delete_design(id) {
			bootbox.confirm("<h4 class='text-warning'><i class='fa fa-warning'></i> Are you sure ?</h4>",function(e) {
				if(e) {
					Loading(true);
					$.ajax({
						type: "POST",
						url: root_domain+'app/design/',
						data: { type : "delete", token :  $("#token").val(), id : id },
						success: function(response)
						{
							console.log(response);
							Unloading();
							response=response.trim();
							if(response == "1") {
								$.gritter.add({
									text: 'REMOVED SUCCESSFULLY !',
									class_name: 'success',
									time: 1500,
								});
								designTable.fnReloadAjax();
							}
							else if(response == "0") {
								$.gritter.add({
									text: 'ERROR, TRY AGAIN !',
									class_name: 'danger',
									time: 2000,
								});
							}
							else {
								$.gritter.add({
									text: 'OOPS, SERVER ERROR !',
									class_name: 'danger',
									time: 2000,
								});
							}
						}
					});	
				}
			});
		}
	</script>
	
	<!-- Modal -->
	<div class="modal colored-header default" id="ModalPreview" role="dialog">
		<div class="modal-dialog w_60">
			<div class="modal-content">
				<div class="modal-header">
					<h3>Showing Cheque Preview</h3>
					<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				</div>
				<div class="modal-body">
					<iframe id="preview_frame" src="" frameborder="0" height="320px"  width="705px"></iframe>
				</div>
				<div class="modal-footer">
					<button class="btn" data-dismiss="modal">OK</button>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
