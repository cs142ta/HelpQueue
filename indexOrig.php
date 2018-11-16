<?php
// Load the settings from the central config file
//require_once 'config.php';
// Load the CAS lib
require_once 'CAS-1.3.4/CAS.php';

// Enable debugging
//phpCAS::setDebug();
// Enable verbose error messages. Disable in production!
phpCAS::setVerbose(false);

// Initialize phpCAS
phpCAS::client(CAS_VERSION_2_0,'cas.byu.edu',443,'cas');

// For production use set the CA certificate that is the issuer of the cert
// on the CAS server and uncomment the line below
// phpCAS::setCasServerCACert($cas_server_ca_cert_path);

// For quick testing you can disable SSL validation of the CAS server.
// THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION.
// VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL!
phpCAS::setNoCasServerValidation();

if (isset($_REQUEST['logout'])) {
    phpCAS::logout();
}
if (isset($_REQUEST['login'])) {
    phpCAS::forceAuthentication();
}

// check CAS authentication
$auth = phpCAS::checkAuthentication();

?>
<html>
  <head>
    <title>CS 240 Help Queue</title>
	<link rel="stylesheet" href="static/css/bootstrap.min.css">
	<link rel="stylesheet" href="static/css/font-awesome.min.css">
	<link rel="stylesheet" href="static/css/myStyleSheet.css">
	<script src="static/js/jquery-2.1.4.min.js"></script>
	<script src="static/js/bootstrap.min.js"></script>
	<script src="static/js/spin.min.js"></script>
	<script src="static/js/index.js"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1">
  </head>
  <body>
<?php
if ($auth) {
		echo "<script> var user = '".phpCAS::getUser()."'; </script>";
        ?>
		<nav class="navbar navbar-default" role="navigation">
			<div class="navbar-header">
			<a class="navbar-brand" ui-sref="home">CS 240 Help Queue</a>
			</div>
			<?php 
			require_once "DBConnect.php";
			if(verifyTA(phpCAS::getUser()))
			{	?>
				<ul class="nav navbar-nav">
					<li><a href="#" id="viewQueue">View Queue</a></li>
					<li><a><input type="checkbox" id="queueActive"/>
						<span class="checkboxtext">  Queue Active </span>
					</a></li>
					<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
						More<span class="caret"></span>
					</a>
						<ul class="dropdown-menu">
							<li><a href="#" id="viewAddTa">Add/Remove TAs  <span class="fa fa-edit"></span></a></li>
							<li><a href="#" id="viewStats">View Stats  <span class="fa fa-eye"></span></a></li>
							<li><a href="#" id="clearButton">Clear the help queue  <span class="fa fa-times"></span></a></li>
						</ul>
					</li>
				</ul>
			<?php } 
				else {?>
					<ul class="nav navbar-nav">
					<li><a><input type="checkbox" id="queueActive" disabled="true"/>
						<span class="checkboxtext">  Queue Active </span>
					</a></li>
					</ul>
				<?php } ?>
			<ul class="nav navbar-nav navbar-right">
			<li style="padding-right:20px;"><a href="?logout=">Logout</a></li>
		   </ul>
		</nav>
		<!-- Modal -->
		  <div class="modal fade" id="nameChangeModal" role="dialog">
			<div class="modal-dialog">
		
			  <!-- Modal content-->
			  <div class="modal-content">
				<div class="modal-header">
				  <h4 class="modal-title">Your name please:</h4>
				</div>
				<div class="modal-body">
					<center>
				  <p>We do not currently have your name in our database. Please provide us with your name.
				  </p>
					<input type="text" id="nameInput" placeholder="Enter your name here" width="70%"></input>
					</center>
				</div>
				<div class="modal-footer">
				  <button type="button" class="btn btn-default" id="nameSubmit">Submit</button>
				</div>
			  </div>
			  
			</div>
		   </div>

	
	<?php 
		require_once "DBConnect.php";
		if(verifyTA(phpCAS::getUser()))
		{
	?>
	<!-- TA -->
		<center>
			<h2>Welcome TA to the Help Queue</h2>
			<div class="container" id="queue"> <!-- MAIN QUEUE PAGE -->
				<div class="row">
					<div class="col-xs-3">
						<strong>Net Id</strong>
					</div>
					<div class="col-xs-3">
						<strong>Name</strong>
					</div>
					<div class="col-xs-2">
						<strong>Time Waiting</strong>
					</div>
					<div class="col-xs-2">
						<strong>Help Count</strong>
					</div>
					<div class="col-xs-2">
						<strong>Remove From Queue</strong>
					</div>
				</div>
				<hr>
				<div id="list"></div>
			</div>
			<div class="container" id="stats"> <!-- STATS PAGE -->
				<div class="row">
					<div class="col-xs-2">
						
					</div>					
					<div class="col-xs-4">
						<strong>Net Id</strong>
					</div>
					<div class="col-xs-4">
						<strong>Name</strong>
					</div>
					<div class="col-xs-2">
						<strong>Help Count</strong>
					</div>
				</div>
				<hr>
				<div id="statsList"></div>
			</div>
			<div class="container" id="editTAs"> <!-- EDIT TAS -->
				<div class="row">
					<input type="text" id="taInput" placeholder="Enter TA net id to add" style="width:25%;"></input>
					<input type="text" id="taInputName" placeholder="Enter TA's name to add" style="width:25%;"></input>
					<button class="btn btn-success btn-lg fa fa-plus" id="addTaBtn"></button>
				</div>				
				<div class="row">				
					<div class="col-xs-4">
						<strong>Net Id</strong>
					</div>
					<div class="col-xs-4">
						<strong>Name</strong>
					</div>
					<div class="col-xs-2">
						<strong>Help Count</strong>
					</div>
					<div class="col-xs-2">
						
					</div>	
				</div>
				<hr>
				<div id="taList"></div>
			</div>
		</center>

	<!-- END TA -->
	<?php } else { ?>
	<!-- STUDENT -->
    	<center>
		<div class="container">
			<div class="row">
				<button id="getHelpButton" class="btn btn-success" style="width:80%; height:50px;">Get in line for help</button>
			</div>
		</div>
		<br/>
		<div class="container">
			<div class="row">
				<div class="col-xs-6">
					<span id="queueNum"></span>
				</div>
				<div class="col-xs-6">
					Time waiting: <strong><span id="timeWaiting"></span></strong>
				</div>
			</div>
		</div>
		</center>
	<!-- END STUDENT -->
	<?php 
		//add to the DB
		require_once 'DBConnect.php';
		addStudent(phpCAS::getUser());
	} ?>
		<p style="padding-left:10px; padding-top:30px;">
			<ul>
				<li>You are currently logged in as: <b><?php echo phpCAS::getUser(); ?></b>.</li>
				<li>The current average wait time for the top <span id="topCount"></span> people on the queue is <span id="topAvg"></span></li>
				<li>The current length of the queue is <span id="queueLen"></span>.</li>
				<span id="refreshText"></span>
			</ul>
		</p>
<?php
} else { //go to login page!
?>
	<script language="javascript">
    	window.location.href = "?login="
	</script>    
<?php
}

?>
  </body>
</html>
