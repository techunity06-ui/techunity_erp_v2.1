<?php
   		$html = '<style>
				div {
				 padding:1em;
				 margin-bottom: 1em;
				 text-align:justify;
				
				}
				.verticle { position: fixed;
					 overflow: auto;					 
					 width: 705px;
					 height:310px;
					 border: 1px solid #880000;								 
					 padding: 0.5em;
					 font-family:sans;
					 margin: auto;
					 rotate: 90;
					  top: 0;
					left: 25%;
					-webkit-transform: translate(-25%,-25%);
					-moz-transform: translate(-25%,-25%);
					-ms-transform: translate(-25%,-25%);
					-o-transform: translate(-25%,-25%);
					transform: translate(-25%,-25%);

					}
				.payee {
					position: absolute;
					 left:0.5em;
					 top: 0.14em;
					 width:0.3em;
					 border: 1px solid #880000;					
					 padding: 1.5em;
					 font-family:sans;
					height:auto;
					
				}
				.date {
					position: absolute;
					left:3.57em;
					 top: 0.14em;
					 width:0.3em;					 				
					 padding: 1.5em;
					 font-family:sans;
					 height:auto
			
				}
				</style>
				<body>
				<div class="verticle">
				<div class="payee" >Hiten </div><div class="date" >27-06-2015 </div></div></body>';	
		include("mpdf/mpdf.php");
		$mpdf=new mPDF('s');
		$mpdf->SetDisplayMode('fullpage');
		$mpdf->WriteHTML($html); // Separate Paragraphs defined by font
		$mpdf->Output();		
		exit;
				
		//echo $html;exit;

?>
