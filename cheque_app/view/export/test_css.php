<?php
$html = '
<style>
.gradient {
 border:0.1mm solid #220044;
 background-color: #f0f2ff;
 background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;
}
h4 {
 font-family: sans;
 font-weight: bold;
 margin-top: 1em;
 margin-bottom: 0.5em;
}
div {
 padding:1em;
 margin-bottom: 1em;
 text-align:justify;
}
.myfixed1 { position: absolute;
 overflow: visible;
 left: 0;
 bottom: 0;
 border: 1px solid #880000;
 background-color: #FFEEDD;
 background-gradient: linear #dec7cd #fff0f2 0 1 0 0.5;
 padding: 1.5em;
 font-family:sans;
 margin: 0;
}
.myfixed2 { position: fixed;
 overflow: auto;
 right: 0;
 bottom: 0mm;
 width: 65mm;
 border: 1px solid #880000;
 background-color: #FFEEDD;
 background-gradient: linear #dec7cd #fff0f2 0 1 0 0.5;
 padding: 0.5em;
 font-family:sans;
 margin: 0;
 rotate: 90;
}
</style>
<body>
<h1>mPDF</h1>
<h2>Floating & Fixed Position elements</h2>
<div class="myfixed1">1 Praesent pharetra nulla in turpis. Sed ipsum nulla, sodales nec, vulputate in,
scelerisque vitae, magna. Praesent pharetra nulla in turpis. Sed ipsum nulla, sodales nec, vulputate
in, scelerisque vitae, magna. Sed egestas justo nec ipsum. Nulla facilisi. Praesent sit amet pede quis
metus aliquet vulputate. Donec luctus. Cras euismod tellus vel leo. Sed egestas justo nec ipsum. Nulla
facilisi. Praesent sit amet pede quis metus aliquet vulputate. Donec luctus. Cras euismod tellus vel
leo.</div>
<div class="myfixed2">2 Praesent pharetra nulla in turpis. Sed ipsum nulla, sodales nec, vulputate in,
scelerisque vitae, magna. Sed egestas justo nec ipsum. Nulla facilisi. Praesent sit amet pede quis
metus aliquet vulputate. Donec luctus. Cras euismod tellus vel leo.</div>
';
//==============================================================
//==============================================================
//==============================================================
include("/mpdf/mpdf.php");
$mpdf=new mPDF('s');
$mpdf->SetDisplayMode('fullpage');
$mpdf->WriteHTML($html); // Separate Paragraphs defined by font
$mpdf->Output();
exit;
//==============================================================
//==============================================================
//==============================================================
?>