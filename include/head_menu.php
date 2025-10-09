<?php 
	$arr=explode("/",$_SERVER['PHP_SELF']);
	$page_name=end($arr);
	$page_name=basename($page_name, '.php');
?>
<style>
ul.summary-list > li {
	width:15%;
}
</style>
<div style="text-align:right" class="hidden-phone">
						<ul class="summary-list" >
							<?phpif($page_name!="invoice")
							{?>
							<li class="">
								<a href="<?=ROOT.FINANCE_ROOT.'invoice'?>">
									<i class="fa fa-pencil text-primary"></i>
										Create Invoice
								</a>
                            </li>
                            <?php}
							if($page_name!="invoice_list")
							{?>
							<li>
								<a href="<?=ROOT.FINANCE_ROOT.'invoice_list'?>">
									<i class="fa fa-envelope text-info"></i>
										Invoice List
                                </a>
                            </li>
							<?php} ?>
							
                     </ul>
		</div>
						