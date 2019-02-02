<?php
require_once 'DBConnect.php';
require_once 'index.php';
if($isTA) {
  header('HTTP/1.0 403 Forbidden');
  readfile("static/html/HTTP403.html");
}
else if ($auth){
?>
  <html>
    <head>
      <title><?php echo getCourseTitle(); ?> Help Queue</title>
  	<link rel="stylesheet" href="static/css/bootstrap.min.css">
  	<link rel="stylesheet" href="static/css/font-awesome.min.css">
  	<link rel="stylesheet" href="static/css/myStyleSheet.css">
  	<link rel="stylesheet" href="static/css/chatstyle.css"/>

  	<script src="static/js/jquery-2.1.4.min.js"></script>
  	<script src="static/js/bootstrap.min.js"></script>
  	<script src="static/js/spin.min.js"></script>

  	<script src="static/js/chat.js"></script>
  	<script src="static/js/index_student.js"></script>
  </head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <body>
    <?php echo "<script> var user = '".phpCAS::getUser()."'; var notifyThresholdNum = '". getNotifyThreshold() ."' </script>";?>
    <nav class="navbar navbar-default" role="navigation">
			<div class="navbar-header">
				<button type="button" data-target="#navbarCollapse" data-toggle="collapse" class="navbar-toggle"> <span class="sr-only">Toggle navigation</span>
				  <span class="icon-bar"></span>
				  <span class="icon-bar"></span>
				  <span class="icon-bar"></span>

				</button>
					<a class="navbar-brand" ui-sref="home"><span id="courseTitle"></span> Help Queue</a>
			</div>
      <?php echo "<script> var pollTimer = '" . getStudentPollTime() ."'; </script>";
            echo "<script> Notification.requestPermission(function (permission) {}); </script>";
            echo "<script> chat = new Chat(user); chat.popfunc = stuChat; </script>";?>
      <div id="navbarCollapse" class="collapse navbar-collapse">
        <ul class="nav navbar-nav">
          <li><a><input type="checkbox" id="queueActive" disabled="true"/>
            <span class="checkboxtext">  Queue Active </span>
          </a></li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
          <li style="padding-right:20px;"><a href="?logout=">Logout</a></li>
         </ul>
      </div>
    </nav>
    <div class="modal fade" id="nameChangeModal" role="dialog">
      <div class="modal-dialog">

      <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Your name please:</h4>
          </div>
          <div class="modal-body">
            <center>
              <p>We do not currently have your name in our database. Please provide us with your name.</p>
              <input type="text" id="nameInput" placeholder="Enter your name here" width="70%"></input>
            </center>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" id="nameSubmit">Submit</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="chatModal" role="dialog">
      <div class="modal-dialog chat-cont">

      <!-- Modal content-->
        <div class="modal-content page-wrap">
          <div class="modal-header">
            <button type="button" class="close" id="close-chat" data-dismiss="modal">&times;</button>
            <h3 id="chat-title">Chat</h3>
          </div>
          <div class="modal-body">
            <div id="chat-wrap"><div id="chat-area"></div></div>
            </div>
          <div class="modal-footer">
            <form id="send-message-area">
              <p>Your message: </p>
              <textarea id="sendie" maxlength = '500' style="resize:none"></textarea>
            </form>
          </div>
        </div>
      </div>
    </div>
    <center>
  <div class="container">
    <div class="row" style="width:80%;">
      <div id="questionsRequired">	<!-- This is for when questions are required -->
        <!--<div class="col-xs-9"> -->
        <div class="col-xs-12">
          <h1 style="font-size:250%;"><strong><?php if(isMOTDLink()){ echo ("<a href='".getMOTDLink()."'>");}?><?php echo getMOTD()?><?php if (isMOTDLink()) {echo "</a>";}?></strong></h1>

          <div class="row">
        <div class="col-xs-1" style="width:80%;">
          <input class="form-control" type="text" id="questionInput" placeholder="Enter your question here" maxlength="200">
        <span style="float:left; font-size:75%;" id="questionInputLengthLeft">200 characters remaining</span></div>
        <div class="col-xs-1" style="width:20%;vertical-align:middle;">
            <select id="roomSelector">
                <option selected hidden value="invalid">ROOM #</option>
                <option value="1119">1119</option>
                <option value="1121">1121</option>
                <option value="1138">1138</option>
                <option value="1102">1102</option>
            </select>
        <div>
    </div>

        </div></div>
        </div>
        <!--
        <div class="col-xs-1">
          <strong>OR</strong>
        </div>
        <div class="col-xs-2">
          <input type="checkbox" id="passOffCheckBox"/>
            <span class="checkboxtext">  Pass Off </span>
        </div><br/>
        -->
        <div style="padding-top: 20px;"></div>
        <div class="row"> <!-- wont initally be disabled -->
          <button id="getHelpButton" class="btn btn-success" style="width:80%; height:50px;" disabled>Get in line for help</button>
        </div>
      </div>
      <div id="questionsNotRequired">
        <button id="getHelpButtonNoQuestion" class="btn btn-success noQuestionRequiredButtons" style="width:45%; height:50px;">Get Help</button>
        <button id="passOffButtonNoQuestion" class="btn btn-info noQuestionRequiredButtons" style="width:45%; height:50px;">Pass Off</button>
      </div>
    </div>
  </div>
  <br/>
  <div class="container">
    <div class="row">
      <div class="col-xs-6">
        <span id="queueNum"></span>
      </div>
      <div class="col-xs-6">
        <span id="timeWaitingLabel"></span><strong><span id="timeWaiting"></span></strong>
      </div>
    </div>
  </div>
  </center>
  <p style="padding-left:10px; padding-top:30px;">
    <ul>
      <li>You are currently logged in as: <b><?php echo phpCAS::getUser(); ?></b>.</li>
      <?php if(!$isThisATA){ ?>
        <?php require_once 'DBConnect.php'; if(shouldLimitDaily()){?>
          <li>You have <b><span id="questionsRemainingDay"></span> of <span id="questionsDaily"></span></b> daily questions remaining</li>
        <?php }?>
        <?php require_once 'DBConnect.php'; if(shouldLimitWeekly()){?>
          <li>You have <b><span id="questionsRemainingWeek"></span> of <span id="questionsWeekly"></span></b> weekly questions remaining</li>
        <?php }?>
      <?php }?>
      <li>The current average wait time for the top <span id="topCount"></span> people on the queue is <span id="topAvg"></span></li>
      <li>The current length of the queue is <span id="queueLen"></span>.</li>
      <li>The current number of people being helped is <span id="currentlyBeingHelped"></span>.</li>
      <li>Number of people who got in line in the last <span class="lastXMin"></span> min: <span id="enqueueInLastXMin"></span>.</li>
      <li>Number of people who finished receiving help in the last <span class="lastXMin"></span> min: <span id="dequeueInLastXMin"></span>.</li>
      <span id="refreshText"></span>
    </ul>
  </p>
  <div id="sexyRexy"></div>
  <div id="easterEggTrigger" style="position:fixed; bottom:0; color:#fefefe; cursor:pointer;">Credits</div>
</body>
</html>

<?php
}
else {
?>
  <script language="javascript">
    	window.location.href = "?login="
	</script>
<?php
}
?>
