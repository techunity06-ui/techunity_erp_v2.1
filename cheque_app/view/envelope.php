<?php 
	session_start();
	include("../../config/config.php");
	include("../../config/session.php");
		
		$_SESSION['token'] = $token;
		$form="Envelop Print";
		$mode="Print";
		$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from coro_customers as cust where cust_id=$invoiceid";
		$rel=mysqli_fetch_assoc($dbcon->query($query));		
	
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include("../common/head.php");?>

</head>
<body>
  <section id="container" >
      <!--sidebar start-->
      <!--sidebar end-->
      <!--main content start-->
           <section id="main-content">
          <section class="wrapper">
			
              <!--state overview start-->
			<div class="col-md-12">
				<section class="panel">
				  <header class="panel-heading">
					  New <?=$form?>
					</header>	
						<div class="panel-body">
								<button type="submit" class="btn btn-danger" onClick="PrintMe('receipt_print');">Print</button>
								
								
								<br>
								<br><br><br>
							<div class="col-lg-12 table-responsive" id="receipt_print">
							<div class="col-md-12" style="" id="print1">
					
							<div id="client_address"  style="" >
						<strong>	To<br>
							<?=$rel['cust_name']?><br>
							<?=$rel['cust_city']?><br>
							<?php 
							if(!empty($rel['cust_phone']))
							{
							?>
							<?=$rel['cust_phone']?> 
							<?php }?></strong>
							
							</div>
						<div id="self_address"  style="">
							<strong>From:-<br>
							Bhattacharya<br>
							12, Small Sacle Indl. Estate,<br>
							Nr. Gujrat Botting Co.,<br>
							Rakhial,AHMEDABAD - 380023<br>
							(Ph) 079-22910924</strong>
							
							</div>
							</div>
							
						</div>
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
	<?php include("../common/js.php");?>   
  
    <!--<script src="js/count.js"></script>-->
<script>
	$(".select2").select2({
		width: '100%'
	});
	$('.default-date-picker').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true
        });
</script>
<script type="text/javascript"> 
function PrintMe(DivID) {

$("#client_address").css("margin-left","454px")
$("#client_address").css("position","absolute")
$("#client_address").css("width","200%")
$("#client_address").css("margin-top","100px")	
$("#self_address").css("margin-left","0px")
$("#self_address").css("margin-top","185px")

//var duplicate = $("#receipt_data").clone().appendTo("#receipt_duplicate");
var disp_setting="toolbar=yes,location=no,";
disp_setting+="directories=yes,menubar=yes,";
disp_setting+="scrollbars=yes,width=800, height=600, left=100, top=25";
  var content_vlue = document.getElementById(DivID).innerHTML;
  var docprint=window.open("","",disp_setting);
  docprint.document.open();
 docprint.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"');
  docprint.document.write('"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
  docprint.document.write('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">');
  docprint.document.write('<head><title><?php echo TITLE;?></title>');
  docprint.document.write('<style type="text/css">body { ');
  docprint.document.write('font-family:Tahoma;color:#000;');
  docprint.document.write('font-family:Tahoma,Verdana; font-size:15px;} .dataTables_length, .dataTables_filter , .dataTables_paginate { display:none; }');
  docprint.document.write('#mainpart table,#mainpart tr,#mainpart td,#mainpart th {border:1px #eee solid;padding:2px 5px 2px 5px;}');
  docprint.document.write('a{color:#000;text-decoration:none;} h1 {font-size:25px; line-height:25px;} b { font-weight:normal; } @page {	size:landscape;} div.page { page-break-before: always; page-break-inside: avoid; }#print1{position:absolute;}.address{position:absolute;} </style>');
  docprint.document.write('</head><body onLoad="self.print()">');
  docprint.document.write(content_vlue);
  docprint.document.write('</body></html>');
  docprint.document.close();
  docprint.focus();

}
interact('.address-item')
  .draggable({
    // enable inertial throwing
    inertia: true,
    // keep the element within the area of it's parent
    restrict: {
      restriction: "parent",
      endOnly: true,
      elementRect: { top: 0, left: 0, bottom: 1, right: 1 }
    },
    // enable autoScroll
    autoScroll: true,

    // call this function on every dragmove event
    onmove: dragMoveListener,
    // call this function on every dragend event
    onend: function (event) {
      var textEl = event.target.querySelector('p');

      textEl && (textEl.textContent =
        'moved a distance of '
        + (Math.sqrt(event.dx * event.dx +
                     event.dy * event.dy)|0) + 'px');
    }
  });

  function dragMoveListener (event) {
    var target = event.target,
        // keep the dragged position in the data-x/data-y attributes
        x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx,
        y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

    // translate the element
    target.style.webkitTransform =
    target.style.transform =
      'translate(' + x + 'px, ' + y + 'px)';

    // update the posiion attributes
    target.setAttribute('data-x', x);
    target.setAttribute('data-y', y);
  }

	
</script>


  </body>
</html>
