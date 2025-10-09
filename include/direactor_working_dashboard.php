<link href="assets/morris.js-0.4.3/morris.css" rel="stylesheet" />
<style type="text/css">
.count , .count2
{
  margin:0px !important;
  padding:0px !important

}
.cc_count
{
  margin-left:5%;
}

.panel-heading
{
  text-align:center;
  font-weight:bold;
  FONT-SIZE:16px;
}

.border_line
{
  border-bottom:dotted blue 2px;
}

.link_dash
{
  border-bottom:dotted blue thin;
}

</style>
<style>
.icons{
    width: 13%;
    float: left;
    margin: 25px 0px;
    text-align: center;
    position:relative;

}
.icons12{
    background-color:#fff;
    padding-top:15px;
    border: 8px;
}
.icons p{
 text-align:center;
 font-size:15px;
 font-weight:600;
 padding-top:5px;
 font-color:white

}

.icon1 fa{

}
.icon1.success{background-color: #5cb85c;}
.icon1.primary{background-color: #0275d8;}
.icon1.warning{background-color: #f0ad4e;}
.icon1.info{background-color: #5bc0de;}
.icon1.danger{background-color: #d9534f;}
.icon1.terques{background-color: #6ccac9;}
.icon1.yellow{background-color: #f8d347;}
.icon1.pink{background-color:#E5649A;}
.icon1.mustard{background-color:#F0BD23;}
.icon1.success,.icon1.primary,.icon1.warning,.icon1.danger,.icon1.info,.icon1.terques,.icon1.yellow,.icon1.pink,.icon1.mustard{
    width: 130px;
    height:130px;
    border-radius: 8px;
    text-align:center;
}
.icon1.success i,.icon1.primary i,.icon1.warning i,.icon1.danger i,.icon1.info i,.icon1.terques i,.icon1.yellow i,.icon1.pink i,.icon1.mustard i{
 text-align:center;
 color:#fff;
 padding-top: 27%;
 font-size: 37px;
}
@media (max-width:767px){
    .icons {
        width:265px;
        float: left;
        margin: 30px 4px 25px;
        position:relative;
    }

}
@media (min-width:768px) and (max-width:980px)
{
 .icons12{
    background-color:#fff;
    padding-top:20px;
    padding-bottom:20px;
    border-radius: 8px;
}
.icons {
    width: 17%;
    float: left;
    margin: 30px 4px 25px;
    text-align: center;
    position:relative;
}

}
.icons .badge {
    position: absolute;
    right: 25px;
    top: 0px;
    z-index: 100;
}
.hh {
	font-family: "Segoe UI",Arial,sans-serif;
    font-weight: 400;
    margin: 10px 0;
    font-size: 25px;
    box-sizing: inherit;
    margin-block-start: -0.5em;
    margin-block-end: 0.0em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    color: #fff!important;
    background-color: #009688!important;
}
</style>
<style>
/* ====================================================
 Recreating the email field from https://webflow.com/cms. Just an experiment - not as cross-browser friendly as the original.
 Changed:
 - animated gradient bar to :after element
 - flexbox for layout
 ==================================================== */
 html {
  box-sizing: border-box;
  font-size: 10px;
}
*, *:before, *:after {
  box-sizing: inherit;
}
body, ul, li {
  margin: 0;
  padding: 0;
}
li {
  list-style: none;
}
p, h1, h2, h3, h4, h5, h6 {
  margin-top: 0;
}
a {
  text-decoration: none;
}
input {
  border-style: none;
  background: transparent;
  outline: none;
}
button {
  padding: 0;
  background: none;
  border: none;
  outline: none;
}
.pnew {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
  background-image: radial-gradient(circle at 0% 0%, #373b52, #252736 51%, #1d1e26);
}
h1.demo {
  text-align: center;
  font-size: 2.4rem;
  font-weight: normal;
  margin-bottom: 1rem;
  color: #f5f6ff;
}
a.demo {
  text-align: center;
  font-size: 1.6rem;
  font-weight: normal;
  color: rgba(202, 205, 239, 0.8);
  margin-bottom: 3rem;
}
.demo-flex-spacer {
  flex-grow: 1;
}
.container {
  display: flex;
  flex-direction: column;
  height: 100vh;
  max-width: 1600px;
  padding: 0 15px;
  margin: 0 auto;
}
@keyframes gradient {
  0% {
    background-position: 0 0;
}
100% {
    background-position: 100% 0;
}
}
.webflow-style-input {
  position: relative;
  display: flex;
  flex-direction: row;
  width: 500%; 
  max-width: 500px; 
  /* margin: 0 auto; */
  border-radius: 6px;
  padding: 1.4rem 2rem 1.6rem;
  rgb(248 248 249 / 80%);
}
.webflow-style-input:after {
  content: "";
  position: absolute;
  left: 0px;
  right: 0px;
  bottom: 0px;
  z-index: 999;
  height: 2px;
  /* border-bottom-left-radius: 2px; */
  /* border-bottom-right-radius: 2px; */
  background-position: 0% 0%;
  background: linear-gradient(to right, #020202, #020202, #020202, #020202, #020202, #020202);
  animation: gradient 3s cubic-bezier(0, 0, 1, 0.99) infinite;
}
.webflow-style-input input, .webflow-style-input strike{
  flex-grow: 1;
  color: #d9534f;
  font-size: 1.8rem;
  line-height: 2.4rem;
  vertical-align: middle;
}
.webflow-style-input input::-webkit-input-placeholder {
  color: #7881A1;
}
.webflow-style-input button {
  color: #7881A1;
  font-size: 2.4rem;
  line-height: 2.4rem;
  vertical-align: middle;
  transition: color 0.25s;
}
.webflow-style-input button:hover {
  color: #BFD2FF;
}
.webflow-style-input .btn1 {
  color: #5cb85c;
  font-size: 2.4rem;
  line-height: 2.4rem;
  vertical-align: middle;
  transition: color 0.25s;
}
.webflow-style-input .btn1:hover{
  color: #a0eba0;
}
.webflow-style-input .btn2 {
  color: #d9534f;
  font-size: 2.4rem;
  line-height: 2.4rem;
  vertical-align: middle;
  transition: color 0.25s;
}
.webflow-style-input .btn2:hover{
  color: #f59b98;
}
textarea {
  /*margin-top: 10px;
  margin-left: 50px;*/
  width: 500px;
  height: 100px;
  -moz-border-bottom-colors: none;
  -moz-border-left-colors: none;
  -moz-border-right-colors: none;
  -moz-border-top-colors: none;
  background: none repeat scroll 0 0 rgba(0, 0, 0, 0.07);
  border-color: -moz-use-text-color #FFFFFF #FFFFFF -moz-use-text-color;
  border-image: none;
  border-radius: 6px 6px 6px 6px;
  border-style: none solid solid none;
  border-width: medium 1px 1px medium;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.12) inset;
  color: #555555;
  font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
  font-size: 1em;
  line-height: 1.4em;
  padding: 5px 8px;
  transition: background-color 0.2s ease 0s;
}


textarea:focus {
    background: none repeat scroll 0 0 #FFFFFF;
    outline-width: 0;
}

.tbox {
  /*margin-top: 10px;
  margin-left: 50px;*/
  width: 500px;
  height: 40px;
  -moz-border-bottom-colors: none;
  -moz-border-left-colors: none;
  -moz-border-right-colors: none;
  -moz-border-top-colors: none;
  background: none repeat scroll 0 0 rgba(0, 0, 0, 0.07);
  border-color: -moz-use-text-color #FFFFFF #FFFFFF -moz-use-text-color;
  border-image: none;
  border-radius: 6px 6px 6px 6px;
  border-style: none solid solid none;
  border-width: medium 1px 1px medium;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.12) inset;
  color: #555555;
  font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
  font-size: 1em;
  line-height: 1.4em;
  padding: 5px 8px;
  transition: background-color 0.2s ease 0s;
}


.tbox:focus {
    background: none repeat scroll 0 0 #FFFFFF;
    outline-width: 0;
}

select {
   -webkit-appearance:none;
   -moz-appearance:none;
   -ms-appearance:none;
   appearance:none;
   outline:0;
   box-shadow:none;
   border:0!important;
   background: #ededed;
   background-image: none;
   flex: 1;
   padding: 0 .5em;
   color:#2f2d2d;
   cursor:pointer;
   font-size: 1em;
   font-family: 'Open Sans', sans-serif;
}
select::-ms-expand {
   display: none;
}
.select {
   position: relative;
   display: flex;
   /*width: 20em;*/
   height: 3em;
   line-height: 3;
   background: #ededed;
   overflow: hidden;
   border-radius: .25em;
}
.select::after {
   content: '\25BC';
   position: absolute;
   top: 0;
   right: 0;
   padding: 0 1em;
   background: #a9a9a9;
   cursor:pointer;
   pointer-events:none;
   transition:.25s all ease;
}
.select:hover::after {
   color: #23b499;
}
</style>
<style>
.btn {
  box-sizing: border-box;
  appearance: none;
  background-color: transparent;
  border: 2px solid #e74c3c;
  border-radius: 0.6em;
  color: #e74c3c;
  cursor: pointer;
  display: flex;
  align-self: center;
  font-size: 1rem;
  font-weight: 400;
  line-height: 1;
  margin: 20px;
  padding: 1.2em 2.8em;
  text-decoration: none;
  text-align: center;
  text-transform: uppercase;
  font-family: 'Montserrat', sans-serif;
  font-weight: 700;
}
.btn:hover, .btn:focus {
  color: #fff;
  outline: 0;
}

.third {
  border-color: #3498db;
  color: black;
  box-shadow: 0 0 40px 40px #3498db inset, 0 0 0 0 #3498db;
  transition: all 150ms ease-in-out;
}
.third:hover {
  box-shadow: 0 0 10px 0 #3498db inset, 0 0 10px 4px #3498db;
  border-color: #3498db;
  color: black;
}

</style>
<style>
.time {
    color: black;
    padding: 8px;
    text-align: left;
    width: 300px;
}
.hms {
    font-size: 20pt;
    font-weight: 200;
}
.ampm {
    font-size: 12pt;
}
.date {
    font-size: 10pt;
}

</style>
<?php
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
        PURCHASE_ORDER_APPROVAL,
        PURCHASE_ORDER_FINANCE_APPROVAL,
        DASHBOARD_PO_REQUEST_LIST_APPROVE,
        DASHBOARD_PENDING_TASK_LIST_SALES_ORDER_LIST,
        DASHBOARD_PENDING_TASK_LIST_QUOTATION_LIST,
        DASHBOARD_INDENT_LIST,
        DASHBOARD_PENDING_INVOICE_APPROVAL
    ]);
?>

<section class="panel">
	<div class="panel-body ">
		<div class="row">
			<div class="col-md-12 hh" style="text-align:center;/*border-left-style: groove;border-right-style: groove;border-top-style: groove;*/"> Pending Approval </div>
            <div class="col-md-12" style="/*border-left-style: groove;border-right-style: groove;border-top-style: groove;border-bottom-style: groove;*/">
                <div class="col-lg-12 centeral-align" style="text-align:center;">
                    <?php if(in_array(DASHBOARD_PENDING_TASK_LIST_QUOTATION_LIST,$bulkAccessArray)){ ?>
                    <div class="icons">
                        <a class="" data-original-title="" data-toggle="tooltip" data-placement="top" href="<?php echo CRM_ROOT.'quotation_list' ?>" target="new">
                            <div class="icon1 danger" >
                                <p style="color:white;padding-top:5px;">Quotation Approve</p>
                                <h3 style="font-size:18px;color:white;padding-top:2px;" id="quotamount"></h3>
                                <p style="color:white;">Count : <span id="quotcount" style="font-size:14px;color:white;"></span></p>
                            </div>
                        </a>
                    </div>
                    <?php } ?>
                    <?php if(in_array(DASHBOARD_PENDING_TASK_LIST_SALES_ORDER_LIST,$bulkAccessArray)){ ?>
                    <div class="icons">
                        <a class="" data-original-title="" data-toggle="tooltip" data-placement="top" href="<?php echo ROOT.'pending_so_approve_list';?>" target="new">
                            <div class="icon1 primary" >
                                <p style="color:white;padding-top:5px;">Sales Order Approve</p>
                                <h3 style="font-size:18px;color:white;padding-top:2px;" id="soamount"></h3>
                                <p style="color:white;">Count : <span id="socount" style="font-size:14px;color:white;"></span></p>
                            </div>
                        </a>
                    </div>
                    <?php } ?>
                    <?php if(in_array(DASHBOARD_PENDING_TASK_LIST_SALES_ORDER_LIST,$bulkAccessArray)){ ?>
                    <div class="icons">
                        <a class="" data-original-title="" data-toggle="tooltip" data-placement="top" href="<?php echo CRM_ROOT.'order_acceptance_list';?>" target="new">
                            <div class="icon1 warning" >
                                <p style="color:white;padding-top:5px;">Order Acceptance Approve</p>
                                <h3 style="font-size:18px;color:white;padding-top:2px;" id="oaamount"></h3>
                                <p style="color:white;">Count : <span id="oacount" style="font-size:14px;color:white;"></span></p>
                            </div>
                        </a>
                    </div>
                    <?php } ?>
                    <?php if(in_array(DASHBOARD_INDENT_LIST,$bulkAccessArray)) { ?>
                    <div class="icons">
                        <a class="" data-original-title="" data-toggle="tooltip" data-placement="top" href="<?=ROOT.PURCHASE_ROOT.'indent_list'?>" target="new">
                            <div class="icon1 terques" >
                                <p style="color:white;padding-top:5px;">Indent Approve</p>
                                <h3 style="font-size:18px;color:white;padding-top:2px;" id="indentamount"></h3>
                                <p style="color:white;">Count : <span id="indentcount" style="font-size:14px;color:white;"></span></p>
                            </div>
                        </a>
                    </div>
                    <?php } ?>
                    <?php if(in_array(PURCHASE_ORDER_APPROVAL,$bulkAccessArray)) { ?>
                    <div class="icons">
                        <a class="" data-original-title="" data-toggle="tooltip" data-placement="top" href="<?=ROOT.PURCHASE_ROOT.'po_approve_pending_list'?>" target="new">
                            <div class="icon1 success" >
                                <p style="color:white;padding-top:5px;">Purchase Order Approve</p>
                                <h3 style="font-size:18px;color:white;padding-top:2px;" id="poamount"></h3>
                                <p style="color:white;">Count : <span id="pocount" style="font-size:14px;color:white;"></span></p>
                            </div>
                        </a>
                    </div>
                    <?php } ?>
                    <?php if(in_array(PURCHASE_ORDER_FINANCE_APPROVAL,$bulkAccessArray)) { ?>
                    <div class="icons">
                        <a class="" data-original-title="" data-toggle="tooltip" data-placement="top" href="<?=ROOT.PURCHASE_ROOT.'po_aprooval_finance'?>" target="new">
                            <div class="icon1 info" >
                                <p style="color:white;padding-top:5px;">Purchase Order Finance Approve</p>
                                <h3 style="font-size:18px;color:white;padding-top:2px;" id="pofiamount"></h3>
                                <p style="color:white;">Count : <span id="poficount" style="font-size:14px;color:white;"></span></p>
                            </div>
                        </a>
                    </div>
                    <?php } ?>
                    <?php if(in_array(DASHBOARD_PENDING_INVOICE_APPROVAL,$bulkAccessArray)){ ?> 
                    <div class="icons">
                        <a class="" data-original-title="" data-toggle="tooltip" data-placement="top" href="<?=ROOT.FINANCE_ROOT.'unapproved_invoice_list'?>" target="new">
                            <div class="icon1 yellow" >
                                <p style="color:white;padding-top:5px;">Invoice Approve</p>
                                <h3 style="font-size:18px;color:white;padding-top:2px;" id="invoiceamount"></h3>
                                <p style="color:white;">Count : <span id="invoicecount" style="font-size:14px;color:white;"></span></p>
                            </div>
                        </a>
                    </div>
                    <?php } ?>
				</div>
			</div>
		</div>
	</div>
</section>
<div class="col-md-6">
    <section class="panel "> 
        <div class="panel-body ">
            <div class="row" >
                <div class="col-md-4 hh" style="text-align:left;max-height: 35px;/*border-left-style: groove;border-right-style: groove;border-top-style: groove;*/">
                    <img src="<?=DOMAIN?>\view\img\pencil.png" style="width:40%;height:20%;vertical-align: top;text-align:left;" alt="" >
                </div>
                <div class="col-md-8 hh" style="text-align:left;max-height: 35px;/*border-left-style: groove;border-right-style: groove;border-top-style: groove;*/">
                Note </div>
                <div class="container1" id="container1" style="padding-top: 45px;">
                    <div class="demo-flex-spacer">
                    </div>
                    <div class="webflow-style-input" >
                        <input class="" type="date" value="<?=date('Y-m-d')?>" name="start_date" id="start_date" placeholder="Start Date" onChange = "load_notes()"/>
                        <a class="btn btn-primary" onclick="addnext()">Add Next</a>
                    </div>
                    <div id="showhtml"></div>
                </div>
            </div>
        </div>
    </section>
</div>
<div class="col-md-6">
 <section class="panel "> 
  <div class="panel-body ">
    <form class="form-horizontal" role="form" id="direactor_working_dashboard" action="javascript:;" method="post" name="direactor_working_dashboard">
        <div class="row" >
            <div class="col-md-12 hh" style="text-align:center;max-height: 51px;/*border-left-style: groove;border-right-style: groove;border-top-style: groove;*/">Task</div>
            <div class="col-md-12" style="padding-top: 20px;">
                <textarea placeholder="This is an awesome comment box" rows="20" name="task_remark" id="task_remark" cols="40" class="ui-autocomplete-input" autocomplete="off" role="textbox" aria-autocomplete="list" aria-haspopup="true"></textarea>
            </div>
            <div class="col-md-12" style="padding-top: 20px;">
                <input type="text" placeholder="Task Name" class="ui-autocomplete-input tbox" value="" role="textbox" aria-autocomplete="list" aria-haspopup="true" name="task_name" id="task_name" autocomplete="off">
            </div>
            <div class="col-md-12 cenetr" style="padding-top: 10px;">
                <div class="select">
                    <select name="assign_user_ids" id="assign_user_ids">
                        <?= get_assign_users_inq($dbcon,$_SESSION['user_id']); ?>
                    </select>
                </div>
            </div>
            <div class="col-md-12 cenetr" style="padding-top: 10px;">
                <div class="select">
                    <select name="gt_id" id="gt_id">
                        <?=get_generaltask_all($dbcon,'7');?>
                    </select>
                </div>
            </div>
            <div class="col-md-12">
                <center>
                    <button class="btn third" type="submit" id="save" name="save">Save</button>
                </center>
            </div>
            <input type="hidden" name="mode" id="mode" value="task_add">
        </div>
    </form>
</div>
</section>
</div>
<script type="text/javascript">
    function updateTime() {
      var dateInfo = new Date();

      /* time */
      var hr,
      _min = (dateInfo.getMinutes() < 10) ? "0" + dateInfo.getMinutes() : dateInfo.getMinutes(),
      sec = (dateInfo.getSeconds() < 10) ? "0" + dateInfo.getSeconds() : dateInfo.getSeconds(),
      ampm = (dateInfo.getHours() >= 12) ? "PM" : "AM";

  // replace 0 with 12 at midnight, subtract 12 from hour if 13–23
  if (dateInfo.getHours() == 0) {
    hr = 12;
} else if (dateInfo.getHours() > 12) {
    hr = dateInfo.getHours() - 12;
} else {
    hr = dateInfo.getHours();
}

var currentTime = hr + ":" + _min + ":" + sec;

  // print time
  document.getElementsByClassName("hms")[0].innerHTML = currentTime;
  document.getElementsByClassName("ampm")[0].innerHTML = ampm;

  /* date */
  var dow = [
  "Sunday",
  "Monday",
  "Tuesday",
  "Wednesday",
  "Thursday",
  "Friday",
  "Saturday"
  ],
  month = [
  "January",
  "February",
  "March",
  "April",
  "May",
  "June",
  "July",
  "August",
  "September",
  "October",
  "November",
  "December"
  ],
  day = dateInfo.getDate();

  // store date
  var currentDate = dow[dateInfo.getDay()] + ", " + month[dateInfo.getMonth()] + " " + day;

  document.getElementsByClassName("date")[0].innerHTML = currentDate;
};

// print time and date once, then update them every second
updateTime();
setInterval(function() {
  updateTime()
}, 1000);
</script>
