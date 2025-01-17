<?php
require_once 'DBConnect.php';
require_once 'index.php';
if ($isTA) {
?>
<html>
   <head>
      <title><?php echo getCourseTitle();?> Help Queue</title>
      <link rel="stylesheet" href="static/css/bootstrap.min.css">
      <link rel="stylesheet" href="static/css/font-awesome.min.css">
      <link rel="stylesheet" href="static/css/myStyleSheet.css">
      <link rel="stylesheet" href="static/css/chatstyle.css"/>
      <!-- ----------------- TA Resources ----------------------->
      <link rel="stylesheet" href="static/css/bootstrap-datepicker.css">
      <link rel="stylesheet" href="static/css/jquery.timepicker.css">
      <link rel="stylesheet" href="static/css/datatables.min.css"/>
      <link rel="stylesheet" href="static/css/easy-autocomplete.min.css"/>
      <link rel="stylesheet" href="static/css/easy-autocomplete.themes.min.css"/>
      <!-- ----------------- End TA Resources ------------------->
      <script src="static/js/jquery-2.1.4.min.js"></script>
      <script src="static/js/bootstrap.min.js"></script>
      <script src="static/js/spin.min.js"></script>
      <!-- ----------------- TA Resources ----------------------->
      <script src="static/js/Chart.min.js"></script>
      <script src="static/js/bootstrap-datepicker.js"></script>
      <script src="static/js/jquery.timepicker.min.js"></script>
      <script src="static/js/datepair.min.js"></script>
      <script src="static/js/jquery.datepair.min.js"></script>
      <script src="static/js/datatables.min.js"></script>
      <script src="static/js/jquery.easy-autocomplete.min.js"></script>
      <!-- ----------------- End TA Resources ------------------->
      <script src="static/js/chat.js"></script>
      <script src="static/js/index.js"></script>
      <script>
            window.addEventListener("click", function() {
              if(menuDisplayed == true){
                  menuBox.style.display = "none";
              }
            }, true);
      </script>
      <style>
            .menu
            {
                width: 150px;
                box-shadow: 3px 3px 5px #888888;
                border-style: solid;
                border-width: 1px;
                border-color: grey;
                border-radius: 2px;
                padding-left: 5px;
                padding-right: 5px;
                padding-top: 3px;
                padding-bottom: 3px;
                position: fixed;
                background-color: #FFFFFF;
                display: none;
            }

            .menu-item
            {
                height: 20px;
            }

            .menu-item:hover
            {
                background-color: #6CB5FF;
                cursor: pointer;
            }
        </style>
   </head>
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <body>
      <?php echo "<script> var user = '" . phpCAS::getUser() . "'; var notifyThresholdNum = '" . getNotifyThreshold() . "';</script>";?>
      <nav class="navbar navbar-default" role="navigation">
         <div class="navbar-header">
            <button type="button" data-target="#navbarCollapse" data-toggle="collapse" class="navbar-toggle"> <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" ui-sref="home"><span id="courseTitle"></span> Help Queue</a>
         </div>
         <?php echo "<script> var pollTimer = '" . getTAPollTime() . "'; </script>";?>
         <div id="navbarCollapse" class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
               <li><a href="#" id="viewQueue">View Queue</a></li>
               <li><a><input type="checkbox" id="queueActive"/>
                  <span class="checkboxtext">  Queue Active </span>
                  </a>
               </li>
               <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                  More<span class="caret"></span>
                  </a>
                  <ul class="dropdown-menu">
                     <li><a href="#" id="viewAddTa">Edit TAs  <span class="fa fa-edit"></span></a></li>
                     <li><a href="#" id="viewStats">View Stats  <span class="fa fa-eye"></span></a></li>
                     <li><a href="#" id="extendedStatsButton">View Extended Stats  <span class="fa fa-info"></span></a></li>
                     <li><a href="#" id="clearButton">Clear the help queue  <span class="fa fa-times"></span></a></li>
                     <li><a href="getCSVstats.php" id="csvValues">Get student stats in CSV  <span class="fa fa-table"></span></a></li>
                     <li role="separator" class="divider"></li>
                     <li><a href="#" id="settings">Settings <span class="fa fa-cog"></span></a></li>
                     <li><a href="#" id="editRawDataButton"> Edit history <span class="fa fa-exclamation-triangle"></span></a></li>
                  </ul>
               </li>
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
      <!-- Modal for editing TA name -->
      <div class="modal fade" id="taNameEditModal" role="dialog">
         <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
               <div class="modal-header">
                  <h4 class="modal-title">A name please:</h4>
               </div>
               <div class="modal-body">
                  <center>
                     <p>Please enter a name:
                     </p>
                     <input type="text" id="editTaNameInput" placeholder="Enter the name here" width="70%"></input>
                     <div id="changeTaNameNetId" style="display: none;"></div>
                  </center>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-default" id="editTaNameSubmit">Submit</button>
               </div>
            </div>
         </div>
      </div>
      <!-- Modal for chat box -->
      <div class="modal fade" id="chatModal" role="dialog">
         <div class="modal-dialog chat-cont">
            <!-- Modal content-->
            <div class="modal-content page-wrap">
               <div class="modal-header">
                  <button type="button" class="close" id="close-chat" data-dismiss="modal">&times;</button>
                  <h3 id="chat-title">Chat</h3>
               </div>
               <div class="modal-body">
                  <div id="chat-wrap">
                     <div id="chat-area"></div>
                  </div>
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
      <div style="text-align:center;">
         <h2>Welcome TA to the Help Queue</h2>
         <!--MAIN QUEUE PAGE-->
         <!--SWAP THE ORDER OF THESE FOR CHANGING WHICH PAGE THIS IS FOR-->
         <div class="super_container container" id="superqueue">
          <div class="container queue main-queue" id="queue">
             <h3>IN LAB</h3>
             <div class="row queue-row">
                <div class="col-xs-3">
                   <strong>Name</strong>
                </div>
                <div class="col-xs-5">
                   <strong>Question</strong>
                </div>
                <div class="col-xs-2">
                   <strong>Time</strong>
                </div>
                <div class="col-xs-2">
                   <strong>Action</strong>
                </div>
             </div>
             <hr>
             <div id="list"></div>
             <div id="helped"></div>
          </div>
          <div class="container queue secondary-queue" id="other">
            <h3>ZOOM</h3>
            <div class="row queue-row">
               <div class="col-xs-3">
                  <strong>Name</strong>
               </div>
               <div class="col-xs-5">
                  <strong>Question</strong>
               </div>
               <div class="col-xs-2">
                  <strong>Time</strong>
               </div>
               <div class="col-xs-2">
                  <strong>Action</strong>
               </div>
            </div>
            <hr>
            <div id="other_list"></div>
            <div id="other_helped"></div>
         </div>
         </div>
         <div class="container" id="stats">
            <!-- STATS PAGE -->
            <!--<div class="row">
               <input type="radio" name="statsOrder" value="help"> Help Count
               <input type="radio" name="statsOrder" value="passOff"> Pass offs
               </div> I thought about doing this...seemed more work than it was worth.
               especially with the extended stats section-->
            <div class="row">
               <div class="col-xs-2">
               </div>
               <div class="col-xs-3">
                  <strong>Net Id</strong>
               </div>
               <div class="col-xs-3">
                  <strong>Name</strong>
               </div>
               <div class="col-xs-2">
                  <strong>Help Count</strong>
               </div>
               <div class="col-xs-2">
                  <strong>Pass Off Count</strong>
               </div>
            </div>
            <hr>
            <div id="statsList"></div>
         </div>
         <div class="container" id="editTAs">
            <!-- EDIT TAS -->
            <div class="row">
               <input type="text" id="taInput" placeholder="Enter TA net id to add" style="width:25%;"></input>
               <input type="text" id="taInputName" placeholder="Enter TA's name to add" style="width:25%;"></input>
               <button class="btn btn-success btn-lg fa fa-plus" id="addTaBtn"></button>
            </div>
            <div class="row">
               <div class="col-xs-3">
                  <strong>Net Id</strong>
               </div>
               <div class="col-xs-3">
                  <strong>Name</strong>
               </div>
               <div class="col-xs-2">
                  <strong>Help Count</strong>
               </div>
               <div class="col-xs-2">
                  <strong>Pass Off Count</strong>
               </div>
               <div class="col-xs-2">
                  <strong>Action</strong>
               </div>
            </div>
            <hr>
            <div id="taList"></div>
         </div>
         <div class="container" id="viewSettings">
            <!-- Settings -->
            <hr>
            <div class="row settingsRow">
               <div class="col-xs-9">
                  Reset the Database. All settings will be set to default and all TA and Student data will be permanently lost.
               </div>
               <div class="col-xs-3">
                  <button id="resetDBBtn" class="btn btn-danger fa fa-trash"></button>
               </div>
            </div>
            <div class="row settingsRow">
               <div class="col-xs-9">
                  Course title: <span id="courseTitleField"></span>
               </div>
               <div class="col-xs-3">
                  <button id="changeCourseTitleButton" class="btn btn-warning fa fa-edit"></button>
               </div>
            </div>
            <div class="row settingsRow">
               <div class="col-xs-9">
                  Polling time for students: <span id="studentPollTime"></span> miliseconds
               </div>
               <div class="col-xs-3">
                  <button id="changeStudentPollTime" class="btn btn-warning fa fa-edit"></button>
               </div>
            </div>
            <div class="row settingsRow">
               <div class="col-xs-9">
                  Polling time for TAs: <span id="taPollTime"></span> miliseconds
               </div>
               <div class="col-xs-3">
                  <button id="changeTAPollTime" class="btn btn-warning fa fa-edit"></button>
               </div>
            </div>
            <div class="row settingsRow">
               <div class="col-xs-9">
                  At what spot in line should students be notified to come to the TA office?: <span id="notifyThreshold"> </span><span id=notifyThresholdPostFix></span>
               </div>
               <div class="col-xs-3">
                  <button id="changeNotifySpotNumberBtn" class="btn btn-warning fa fa-edit"></button>
               </div>
            </div>
            <div class="row settingsRow">
               <div class="col-xs-9">
                  Show the number of enqueues and dequeues in the last: <span class="lastXMin"> </span> minutes
               </div>
               <div class="col-xs-3">
                  <button id="changeLastXMinBtn" class="btn btn-warning fa fa-edit"></button>
               </div>
            </div>
            <div class="row settingsRow">
               <div class="col-xs-9">
                  Current pass off highlight color:  <span id="currentPassOffHighlightColor" class="fa fa-square"></span>
               </div>
               <div class="col-xs-3">
                  <button id="changePassOffHighlighColorBtn" class="btn btn-warning fa fa-edit"></button>
               </div>
            </div>
            <div class="row settingsRow">
    					<div class="col-xs-9">
    						Change the number of questions students can ask per day: <span class="questionsPerDay"> </span> questions
    					</div>
    					<div class="col-xs-3">
    					 	<button id="changeQuestionsPerDayBtn" class="btn btn-warning fa fa-edit"></button>
    					</div>
    				</div>
            <div class="row settingsRow">
    					<div class="col-xs-9">
    						Change the number of questions students can ask per week: <span class="questionsPerWeek"> </span> questions
    					</div>
    					<div class="col-xs-3">
    					 	<button id="changeQuestionsPerWeekBtn" class="btn btn-warning fa fa-edit"></button>
    					</div>
    				</div>
            <div class="row settingsRow">
              <div class="col-xs-9">
                 Current message of the day:  <span id="currentMOTD"> </span>
              </div>
              <div class="col-xs-3">
                 <button id="changeMOTDBtn" class="btn btn-warning fa fa-edit"></button>
              </div>
            </div>
            <!-- <div class="row settingsRow">
               <div class="col-xs-9">
                  Change the time to auto disable the queue:
               </div>
               <div class="col-xs-3">
                  <button id="changeAutoTimeBtn" class="btn btn-warning fa fa-edit"></button>
               </div>
            </div>
            <div class="row settingsRow">
               <div class="col-xs-9">
                  Auto Disable the queue:
               </div>
               <div class="col-xs-3">
                  <input id="autoDisable" type="checkbox" style="margin-left:15px;"></input>
               </div>
            </div> -->
            <div class="row settingsRow">
               <div class="col-xs-9">
                  Require Students to enter in a question:
               </div>
               <div class="col-xs-3">
                  <input id="requireQuestion" type="checkbox" style="margin-left:15px;"></input>
               </div>
            </div>
         </div>
         <div class="container" id="editRawData">
            <!-- Update Raw Data -->
            <h3>
               Be careful! This will update the actual data in the database. It could ruin the integrity
               of the database. There is no value checking or verifying and there is no undo button. Once you
               click save the update is put into the database.
               Be cautious what you do here. This should only be to fix anomalies in the data.
            </h3>
            <div id="editRawDataRange">
               <p id="editRawDataDateInput">
                  Date: <input id="editRawDataStartDate" type="text" class="date start" />From
                  <input id="editRawDataStartTime" type="text" class="time start" /> to
                  <input id="editRawDataEndTime" type="text" class="time end" />
               </p>
               <button id="editRawDataGetDataBtn"> Submit </button>
            </div>
            <div id="editRawDataData"></div>
         </div>
         <div class="container" id="extendedStats">
            <!-- Extended Stats -->
            <!-- Extended Stats tabs -->
            <ul class="nav nav-tabs" role="tablist">
               <li role="presentation" class="active"><a id="graphsTab" role="tab" data-toggle="tab">Usage Graphs</a></li>
               <li role="presentation"><a id="tableTab" role="tab" data-toggle="tab">Ta to Student Help Table</a></li>
               <li role="presentation"><a id="questionsTabBtn" role="tab" data-toggle="tab">Questions</a></li>
               <li role="presentation"><a id="profileTabBtn" role="tab" data-toggle="tab">Student Profile</a></li>
            </ul>
            <!-- Tab panes -->
            <div class="tab-content">
               <!-- Wait time graphs -->
               <div role="tabpanel" class="tab-pane active" id="graphs">
                  <div class="row">
                     <br/>
                     <p id="extendedStatsDateInput">
                        Date: <input id="extendedStatsStartDate" type="text" class="date start" /><span id="graphFromWord"> From</span>
                        <input id="extendedStatsStartTime" type="text" class="time start" /> to
                        <input id="extendedStatsEndDate" type="text" class="date end" />
                        <input id="extendedStatsEndTime" type="text" class="time end" />
                     </p>
                     <p>
                        <input type="radio" name="graphSpanning" value="oneDay">Span One Day</input>
                        <input type="radio" name="graphSpanning" value="multipleDay">Span Multiple Days</input>
                     </p>
                     <button id="updateExtendedStatsViews" class="btn btn-success">Update to this date range</button>
                  </div>
                  <div id="graphsContent">
                     <div class="row">
                        <div style="text-align:center;"><strong> Average and median wait time per <span class="graphUnit"></span></strong></div>
                        <canvas id="averageWaitTimeGraph" width="500" height="400"></canvas>
                        <div style="text-align:center;"><span class="graphUnit"></div>
                     </div>
                     <br />
                     <div class="row">
                        <div style="text-align:center;"><strong> Dequeues Per <span class="graphUnit"></strong></div>
                        <canvas id="dequeuesPerHourGraph" width="500" height="400"></canvas>
                        <div style="text-align:center;"><span class="graphUnit"></div>
                     </div>
                     <br/>
                     <div class="row">
                        <div style="text-align:center;"><strong> Average Time Spent with TA <span class="graphUnit"></strong></div>
                        <canvas id="avgTimeWithTAGraph" width="500" height="400"></canvas>
                        <div style="text-align:center;">Minutes</div>
                     </div>
                     <br/>
                     <div class="row">
                        <div style="text-align:center;">
                           <strong> Raw Data </strong>
                           <center>
                              <div id="rawGraphData"></div>
                           </center>
                        </div>
                     </div>
                  </div>
               </div>
               <div role="tabpanel" class="tab-pane" id="tables">
                  <!-- TA to Student Help Table -->
                  <div class="row">
                     <br/>
                     <p id="extendedStatsDateInputTable">
                        Date: <input id="extendedStatsStartDateTable" type="text" class="date start" />
                        <input id="extendedStatsStartTimeTable" type="text" class="time start" /> to
                        <input id="extendedStatsEndDateTable" type="text" class="date end" />
                        <input id="extendedStatsEndTimeTable" type="text" class="time end" />
                        OR <input type="checkbox" id="studentTabAllDataCheckbox" style="margin-left: 10px; margin-right:5px;"> All semester</input>
                     </p>
                     <p>
                        <input type="radio" name="tableType" value="help"> Help Count
                        <input type="radio" name="tableType" value="passOff"> Pass offs
                        <input type="radio" name="tableType" value="all"> All
                     </p>
                     <button id="updateExtendedStatsViewsTable" class="btn btn-success">Update to this date range</button>
                  </div>
                  <!--<canvas id="studentDataTotalTableGraph" width="800" height="400"></canvas>-->
                  <table id="studentDataTotalTable" class="compact">
                     <thead></thead>
                     <tbody></tbody>
                     <tfoot id="studentDataTotalTableFooter">
                        <tr>
                           <th style="text-align:right">Total:</th>
                           <?php
                              //this is the easiest and fastest way to insert the <th> needed for the table footer
                              //it wasn't my first choice but adding them via JS caused a load of headaches and finally
                              //I had to move on and this would work so I went with it. The biggest weak point here is
                              //if a new TA is added and then the user goes to this table, the table will look wrong untill
                              //the page is refreshed.
                              $tas = getAllTAs(null);
                              for ($i = 0; $i < count($tas); $i++) {
                                  echo "<th></th>";
                              }
                              echo "<th></th>"; //Themselves column
                              echo "<th></th>"; //Row totals column
                              ?>
                     </tfoot>
                  </table>
               </div>
               <div role="tabpanel" class="tab-pane" id="questionsTab">
                  <!-- Qustions Table -->
                  <div class="row">
                     <br/>
                     <p id="extendedStatsDateInputQuestions">
                        Date: <input id="extendedStatsStartDateQuestionsTab" type="text" class="date start" />
                        to
                        <input id="extendedStatsEndDateQuestionsTab" type="text" class="date end" /> OR
                        <input type="checkbox" id="questionsTabAllDataCheckbox" style="margin-left: 5px; margin-right:5px;"> All semester</input>
                     </p>
                     <button id="updateExtendedStatsViewsQuestionsTable" class="btn btn-success">Update to this date range</button>
                  </div>
                  <table id="questionsTable" class="compact">
                     <thead>
                        <tr>
                           <th>Student</th>
                           <th>Removed By</th>
                           <th>Question</th>
                           <th>Removed at</th>
                           <th>Time Spent With TA</th>
                        </tr>
                     </thead>
                     <tbody></tbody>
                  </table>
               </div>
               <div role="tabpanel" class="tab-pane" id="profileTab">
                  <!-- Student Profile -->
                  <div style="padding-bottom:15px;"></div>
                  <div class="row">
                     <div class="col-xs-5">
                        <input placeholder="Student Name" class="typeahead" type="text" id="profileSearchInput"></input>
                     </div>
                     <div class="col-xs-7">
                        <button id="profileSearch" class="btn btn-lg btn-success fa fa-search" style="float:left;"></button>
                     </div>
                  </div>
                  <div style="padding-bottom:15px;"></div>
                  <div class="row">
                     <div class="col-xs-6" style="text-align:left; float:left">
                        Name: <span id="profileName"></span><br/>
                        NetId: <span id="profileNetId"></span>
                     </div>
                     <div class="col-xs-6" style="text-align:left; float:left">
                        Total Help Count: <span id="profileTotalHelpCount"></span><br/>
                        Total Pass Off Count: <span id="profileTotalPassOffCount"></span><br/>
                        This weeks Help Count: <span id="profileWeekHelpCount"></span><br/>
                        This weeks Pass off Count: <span id="profileWeekPassOffCount"></span><br/>
                        Todays Help Count: <span id="profileTodayHelpCount"></span><br/>
                        Todays Pass Off Count: <span id="profileTodayPassOffCount"></span><br/>
                        Average Time Spent With TA: <span id="profileAverageTimeSpentWithTA"></span><br/>
                     </div>
                  </div>
                  <br/>
                  <center>
                     <h4>TA Help Counts</h4>
                  </center>
                  <br/>
                  <div class="row">
                     <div id="profileTAHelpCount"></div>
                  </div>
                  <br/>
                  <center>
                     <h4>Questions asked</h4>
                  </center>
                  <br/>
                  <div class="row">
                     <div id="profileQuestions"></div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- Modal for extended stats end time wrongly formatted-->
      <div class="modal fade" id="startTimeAfterEndError" role="dialog">
         <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
               <div class="modal-header">
                  <h4 class="modal-title">ERROR</h4>
               </div>
               <div class="modal-body">
                  <center>
                     <div class="alert alert-danger">The End Time must be after the Start Time</div>
                  </center>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
               </div>
            </div>
         </div>
      </div>
      <!-- Modal for entering class name-->
      <div class="modal fade" id="courseNameChangeModal" role="dialog">
         <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
               <div class="modal-header">
                  <h4 class="modal-title">Course Title</h4>
               </div>
               <div class="modal-body">
                  <center>
                     <p>What is the name of the course this help queue is for?
                     </p>
                     <input type="text" id="courseNameInput" placeholder="Enter name here" width="70%"></input>
                  </center>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-default" id="courseSubmit">Submit</button>
               </div>
            </div>
         </div>
      </div>
      <!-- Modal for changing the MOTD-->
      <div class="modal fade" id="motdChangeModal" role="dialog">
         <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
               <div class="modal-header">
                  <h4 class="modal-title">Course Title</h4>
               </div>
               <div class="modal-body">
                  <center>
                     <p>What should the MOTD say?</p>
                     <input type="text" id="motdInput" placeholder="Enter message here" width="70%"></input>
                     <p>Where should it link (leave blank for no link)?</p>
                     <input type="text" id="motdLinkInput" placeholder="(optional)" width="70%"></input>
                  </center>
               </div>
               <div class="modal-footer">
                 <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                 <button type="button" class="btn btn-default" id="motdSubmit">Submit</button>
               </div>
            </div>
         </div>
      </div>
      <!-- Modal for changing student polling times-->
      <div class="modal fade" id="studentChangePollTimeModal" role="dialog">
         <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
               <div class="modal-header">
                  <h4 class="modal-title">Student Polling interval</h4>
               </div>
               <div class="modal-body">
                  <center>
                     <p>Enter the polling interval in miliseconds
                     </p>
                     <input type="text" id="studentPollTimeInput" placeholder="Enter value here" width="70%"></input>
                  </center>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  <button type="button" class="btn btn-success" id="studentPollTimeSubmit">Submit</button>
               </div>
            </div>
         </div>
      </div>
      <!-- Modal for changing TA polling times-->
      <div class="modal fade" id="taChangePollTimeModal" role="dialog">
         <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
               <div class="modal-header">
                  <h4 class="modal-title">TA Polling interval</h4>
               </div>
               <div class="modal-body">
                  <center>
                     <p>Enter the polling interval in miliseconds
                     </p>
                     <input type="text" id="taPollTimeInput" placeholder="Enter value here" width="70%"></input>
                  </center>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  <button type="button" class="btn btn-success" id="taPollTimeSubmit">Submit</button>
               </div>
            </div>
         </div>
      </div>
      <!-- Modal for changing notify threshold-->
      <div class="modal fade" id="notifyThresholdChangeModal" role="dialog">
         <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
               <div class="modal-header">
                  <h4 class="modal-title">Notify Threshold</h4>
               </div>
               <div class="modal-body">
                  <center>
                     <p>At what point should students be notified to come to the TA office?
                     </p>
                     <input type="text" id="notifyThresholdInput" placeholder="Enter value here" width="70%"></input>
                  </center>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  <button type="button" class="btn btn-success" id="notifyThresholdSubmit">Submit</button>
               </div>
            </div>
         </div>
      </div>
      <!-- Modal for changing last X min -->
      <div class="modal fade" id="changeLastXMinModal" role="dialog">
         <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
               <div class="modal-header">
                  <h4 class="modal-title">How many minutes back to go?</h4>
               </div>
               <div class="modal-body">
                  <center>
                     <p>Show number of enqueues and dequeues from how many minutes ago?
                     </p>
                     <input type="text" id="changeLastXMinInput" placeholder="Enter value here" width="70%"></input>
                  </center>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  <button type="button" class="btn btn-success" id="changeLastXMinSubmit">Submit</button>
               </div>
            </div>
         </div>
      </div>
      <div class="modal fade" id="changeQuestionPerDayModal" role="dialog">
  			<div class="modal-dialog">
  			  <!-- Modal content-->
  			  <div class="modal-content">
  				<div class="modal-header">
  				  <h4 class="modal-title">How many questions per day?</h4>
  				</div>
  				<div class="modal-body">
  					<center>
  				  <p>How many questions should students be allowed to ask per day? (-1 means no limit)
  				  </p>
  					<input type="text" id="changeQuestionPerDayInput" placeholder="Enter value here" width="70%"></input>
  					</center>
  				</div>
  				<div class="modal-footer">
  				  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
  				  <button type="button" class="btn btn-success" id="changeQuestionPerDaySubmit">Submit</button>
  				</div>
  			  </div>
  			</div>
		  </div>
      <div class="modal fade" id="changeQuestionPerWeekModal" role="dialog">
  			<div class="modal-dialog">

  			  <!-- Modal content-->
  			  <div class="modal-content">
  				<div class="modal-header">
  				  <h4 class="modal-title">How many questions per week?</h4>
  				</div>
  				<div class="modal-body">
  					<center>
  				  <p>How many questions should students be allowed to ask per week? (-1 means no limit)
  				  </p>
  					<input type="text" id="changeQuestionPerWeekInput" placeholder="Enter value here" width="70%"></input>
  					</center>
  				</div>
  				<div class="modal-footer">
  				  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
  				  <button type="button" class="btn btn-success" id="changeQuestionPerWeekSubmit">Submit</button>
  				</div>
  			  </div>

  			</div>
		  </div>
      <!-- <div class="modal fade" id="changeAutoTimesModal" role="dialog"> -->
         <!-- <div class="modal-dialog"> -->
            <!-- Modal content-->
            <!-- <div class="modal-content"> -->
               <!-- <div class="modal-header"> -->
                  <!-- <h4 class="modal-title">What time to auto disable the queue?</h4> -->
               <!-- </div> -->
               <!-- <div class="modal-body"> -->
                  <!-- <center> -->
                     <!-- <p>What time would you like to stop more students from getting on the queue?</p> -->
                     <!-- <?php //  echo '<input type="time" id="lastTime" name="appt-time" min="0:00" max="23:59" value = "'.getAutoDisableTime().'" required />' ?> -->
                  <!-- </center> -->
               <!-- </div> -->
               <!-- <div class="modal-footer"> -->
                  <!-- <button type="button" class="btn btn-default" data-dismiss="modal" onclick="closeAutoTimeModal()">Close</button> -->
                  <!-- <button type="button" class="btn btn-success" id="changeAutoTimeSubmit">Submit</button> -->
               <!-- </div> -->
            <!-- </div> -->
         <!-- </div> -->
      <!-- </div> -->
      <!-- Modal for changing passoff highlight color-->
      <div class="modal fade" id="changePassOffHighlighColorModal" role="dialog">
         <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
               <div class="modal-header">
                  <h4 class="modal-title">Pick a highlight color</h4>
               </div>
               <div class="modal-body">
                  <div class="row">
                     <div class="col-xs-4">
                        <span id="passOff_red" style="background-color:red;" class="aSquare passOffSelector"></span>
                     </div>
                     <div class="col-xs-4">
                        <span id="passOff_blue" style="background-color:blue;" class="aSquare passOffSelector"></span>
                     </div>
                     <div class="col-xs-4">
                        <span id="passOff_transparent" class="aSquare passOffSelector"></span>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-xs-4">
                        <span id="passOff_yellow" style="background-color:yellow;" class="aSquare passOffSelector"></span>
                     </div>
                     <div class="col-xs-4">
                        <span id="passOff_orange" style="background-color:orange;" class="aSquare passOffSelector"></span>
                     </div>
                     <div class="col-xs-4">
                        <span id="passOff_green" style="background-color:green;" class="aSquare passOffSelector"></span>
                     </div>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
               </div>
            </div>
         </div>
      </div>
      <p style="padding-left:10px; padding-top:30px;">
      <ul>
         <li>You are currently logged in as: <b><?php echo phpCAS::getUser();?></b>.</li>
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
      <div class="menu menu-main-queue">
        <div class="menu-item" onclick="removeNormal()">Remove</div>
        <div class="menu-item" onclick="removeFree()">Don't Count</div>
      </div>
      <div class="menu menu-other-queue">
        <div class="menu-item" onclick="removeNormalOtherQueue()">Remove</div>
        <div class="menu-item" onclick="removeFreeOtherQueue()">Don't Count</div>
      </div>
   </body>
</html>
<?php
}
else if($auth) {
  header('HTTP/1.0 403 Forbidden');
  readfile("static/html/HTTP403.html");
}
else {
  ?>
  <script language="javascript">
    	window.location.href = "?login="
	</script>
<?php
}
?>
