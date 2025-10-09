<?php
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
?>
<!DOCTYPE html>
<html lang="en">

<!-- Mirrored from thevectorlab.net/flatlab/500.html by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 13 May 2015 05:58:47 GMT -->
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Mosaddek">
    <meta name="keyword" content="FlatLab, Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">
    <link rel="shortcut icon" href="img/favicon.html">

    <title>Permission</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-reset.css" rel="stylesheet">
    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <!-- Custom styles for this template -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/style-responsive.css" rel="stylesheet" />

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 tooltipss and media queries -->
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
</head>

  <body class="body-500">

    <div class="container">

      <section class="error-wrapper">
          <i class="icon-500"></i>
          <h1>Oops!</h1>
          <h2>You don't have permission to this Page.</h2>
          <p class="page-500">
              <button type="button" class="btn btn-lg btn-danger"><a href="javascript:history.back(1);">Go Back</a></button>
          </p>
      </section>

    </div>


  </body>

<!-- Mirrored from thevectorlab.net/flatlab/500.html by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 13 May 2015 05:58:47 GMT -->
</html>
