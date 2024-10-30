<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['ODABSaid']==0)) {
  /*header('location:logout.php');*/
  } 
     ?>
<!DOCTYPE HTML>
<html>
<head>
<title>Meru | Admin Dashboard</title>

<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>

<link href="assets/css/bootstrap.css" rel='stylesheet' type='text/css' />

<link href="assets/css/style.css" rel='stylesheet' type='text/css' />


<link href="assets/css/font-awesome.css" rel="stylesheet"> 

<script src="assets/js/jquery-1.11.1.min.js"></script>
<script src="assets/js/modernizr.custom.js"></script>

<link href='//fonts.googleapis.com/css?family=Roboto+Condensed:400,300,300italic,400italic,700,700italic' rel='stylesheet' type='text/css'>

<link href="assets/css/animate.css" rel="stylesheet" type="text/css" media="all">
<script src="assets/js/wow.min.js"></script>
	<script>
		 new WOW().init();
	</script>

<script src="assets/js/Chart.js"></script>

<link rel="stylesheet" href="assets/css/clndr.css" type="text/css" />
<script src="assets/js/underscore-min.js" type="text/javascript"></script>
<script src= "assets/js/moment-2.2.1.js" type="text/javascript"></script>
<script src="assetsjs/clndr.js" type="text/javascript"></script>
<script src="assets/js/site.js" type="text/javascript"></script>

<script src="assets/js/metisMenu.min.js"></script>
<script src="assets/js/custom.js"></script>
<link href="assets/css/custom.css" rel="stylesheet">
<link href="assets/img/p.png" rel="icon">
<link href="assets/img/p.png" rel="apple-touch-icon">

</head> 
<body class="cbp-spmenu-push">
<div class="main-content">
		
		 <?php include_once('includes/sidebar.php');?>
		
	<?php include_once('includes/header.php');?>
		
		<div id="page-wrapper" class="row calender ">
			<div class="main-page">
				
			
				<div class="row calender">
					<div class="row-one">


					<div class="col-md-4 widget" style=" background-color: #263544;">
						<?php $query1=mysqli_query($con,"Select * from tblcustomers");
$totalcust=mysqli_num_rows($query1);
?>
						<div class="stats-left ">
							<h5>Total</h5>
							<h4>Client</h4>
						</div>
						<div class="stats-right">
							<label> <?php echo $totalcust;?></label>
						</div>
						<div class="clearfix"> </div>	
					</div>
					<div class="col-md-4 widget states-mdl">
						<?php $query2=mysqli_query($con,"Select * from tblappointment");
$totalappointment=mysqli_num_rows($query2);
?>
						<div class="stats-left">
							<h5>Total</h5>
							<h4>Appointment</h4>
						</div>
						<div class="stats-right">
							<label> <?php echo $totalappointment;?></label>
						</div>
						<div class="clearfix"> </div>	
					</div>
					<div class="col-md-4 widget states-last">
						<?php $query3=mysqli_query($con,"Select * from tblappointment where Status='1'");
$totalaccapt=mysqli_num_rows($query3);
?>
						<div class="stats-left">
							<h5>Total</h5>
							<h4>Accepted Apt</h4>
						</div>
						<div class="stats-right">
							<label><?php echo $totalaccapt;?></label>
						</div>
						<div class="clearfix"> </div>	
					</div>
					
				</div>
						
					

					
					
					
					
					
					
						
						<div class="clearfix"> </div>
							
					
					
			<div class="main-page">
				<div class="tables">
					<br><br>
				
					<br>
					
				<h4 class="title1">Accepted Appointment</h4>
					<div class="table-responsive bs-example widget-shadow">
						<h4>Accepted Appointment:</h4>
						<table class="table table-bordered"> <thead> <tr> <th>#</th> <th> Appointment Number</th> <th>Client Name</th><th>Mobile Number</th> <th>Appointment Date</th><th>Appointment Time</th><th>Action</th> </tr> </thead> <tbody>
<?php
$ret=mysqli_query($con,"select *from  tblappointment where Status='1'");
$cnt=1;
while ($row=mysqli_fetch_array($ret)) {

?>

						 <tr> <th scope="row"><?php echo $cnt;?></th> <td><?php  echo $row['AptNumber'];?></td> <td><?php  echo $row['Name'];?></td><td><?php  echo $row['PhoneNumber'];?></td><td><?php  echo $row['AptDate'];?></td> <td><?php  echo $row['AptTime'];?></td> <td><a href="view-appointment.php?viewid=<?php echo $row['ID'];?>">View</a></td> </tr>   <?php 
$cnt=$cnt+1;
}?></tbody> </table> 
					</div>
				</div>
			</div>
		</div>
						
					</div>
				</div>
				<div class="clearfix"> </div>
			</div>
		
	
		<?php include_once('includes/footer.php');?>
       
	</div>
	
	
		<script src="js/classie.js"></script>
		<script>
			var menuLeft = document.getElementById( 'cbp-spmenu-s1' ),
				showLeftPush = document.getElementById( 'showLeftPush' ),
				body = document.body;
				
			showLeftPush.onclick = function() {
				classie.toggle( this, 'active' );
				classie.toggle( body, 'cbp-spmenu-push-toright' );
				classie.toggle( menuLeft, 'cbp-spmenu-open' );
				disableOther( 'showLeftPush' );
			};
			

			function disableOther( button ) {
				if( button !== 'showLeftPush' ) {
					classie.toggle( showLeftPush, 'disabled' );
				}
			}
		</script>
	
	<script src="js/jquery.nicescroll.js"></script>
	<script src="js/scripts.js"></script>
	
	
   <script src="js/bootstrap.js"> </script>
</body>
</html>