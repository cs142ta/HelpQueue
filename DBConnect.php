<?php

	class MyDB extends SQLite3
   {
      function __construct()
      {
		$dbName = 'database.sqlite';
		$initDB = false;
		$initDB = !file_exists($dbName);

		$this->open($dbName);
		$this->busyTimeout(15000);

		if($initDB)
		{
			chmod($dbName, 0777);
			DBInit($this);

			//Insert a TA to get things up and going
			$this->exec("BEGIN");
			$sql = "INSERT INTO TAS (NetId, Counter) VALUES (:netId, 0);";
			try
			{
				$stmt = $this->prepare($sql);
				$stmt->bindValue(":netId", phpCAS::getUser());
				$stmt->execute();
				$stmt->close();
				$this->exec("COMMIT");
			}
			catch(CAS_OutOfSequenceBeforeClientException $e)
			{
				$this->exec("ROLLBACK");
				echo $e;
				echo "error adding initial TA";
			}
		}
      }
   }

	function DBInit($db)
	{
		if($db == null)
		{
			$db = new MyDB();
		}
		else
		{
			$createUsers = "CREATE TABLE IF NOT EXISTS Students(NetId TEXT Not NULL,name TEXT, Counter INT NOT NULL DEFAULT 0, PassOffCounter Integer DEFAULT 0, UNIQUE(NetId))";
			$createTA = "CREATE TABLE IF NOT EXISTS TAS(NetId TEXT Not NULL, name TEXT, Counter INT Not NULL DEFAULT 0, PassOffCounter INT DEFAULT 0, Active Bit Default 1, UNIQUE(NetId))";
			$createQueue = "CREATE TABLE IF NOT EXISTS QUEUE(NetId TEXT Not NULL, ENQUEUETIME INT Not NULL, QUEUENUM INT Not NULL, QUESTION TEXT, PASSOFF BIT, STARTEDGETTINGHELPTIME INT, BeingHelpedBy TEXT, queue_id STRING NOT NULL DEFAULT 'inlab', UNIQUE(NetId))";
			$createSettings = "CREATE TABLE IF NOT EXISTS SETTINGS(name TEXT Not NULL, value TEXT NOT NULL, UNIQUE(name))";
			$createQueueHistory = "CREATE TABLE IF NOT EXISTS QUEUEHISTORY(id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,NetId TEXT NOT NULL, removedBy TEXT NOT NULL,enqueueTime INTEGER NOT NULL,	dequeueTime INTEGER NOT NULL, QUESTION TEXT, PASSOFF Bit, DoneGettingHelpTime INTEGER, helpedBy TEXT NOT NULL DEFAULT '', conceptualQuestion BOOLEAN, queue_id STRING NOT NULL DEFAULT 'inlab')";
			//$onDeleteQueueTrigger = "CREATE TRIGGER IF NOT EXISTS updateRowonQueueTrigger AFTER UPDATE OF QUEUENUM ON QUEUE BEGIN UPDATE QUEUE SET QUEUENUM = QUEUENUM - 1 WHERE QUEUENUM > Old.QUEUENUM;  END;";
			$onDeleteQueueTrigger = "CREATE TRIGGER IF NOT EXISTS deleteRowonQueueTrigger AFTER DELETE ON QUEUE BEGIN UPDATE QUEUE SET QUEUENUM = QUEUENUM - 1 WHERE QUEUENUM > Old.QUEUENUM;  END;";

			//create an after delete trigger on the queue table (get the person who was just deleted and start there and minus one to every spot in line)
			//insert the settings
			$insertActiveQueue = "INSERT OR IGNORE INTO SETTINGS (name, value) VALUES ('queueActive', 'true')";
			$insertName = "INSERT OR IGNORE INTO SETTINGS (name, value) VALUES ('courseTitle', '')";
			$insertStudentPollTime = "INSERT OR IGNORE INTO SETTINGS (name, value) VALUES ('studentPollTime', '5000')";
			$insertTAPollTime = "INSERT OR IGNORE INTO SETTINGS (name, value) VALUES ('taPollTime', '3000')";
			$insertNotifyThreshold = "INSERT OR IGNORE INTO SETTINGS (name, value) VALUES ('notifyThreshold', '3')";
			$insertPassOffColor = "INSERT OR IGNORE INTO SETTINGS (name, value) VALUES ('passOffHighlightColor', 'yellow')";
			$insertLastXMin = "INSERT OR IGNORE INTO SETTINGS (name, value) VALUES ('lastXMin', '5')";
			$insertRequireQuestion = "INSERT OR IGNORE INTO SETTINGS (name, value) VALUES ('requireQuestion', 'true')";
			$insertQuestionsPerDay = "INSERT OR IGNORE INTO SETTINGS (name, value) VALUES ('questionsPerDay', '4')";
			$insertQuestionsPerWeek = "INSERT OR IGNORE INTO SETTINGS (name, value) VALUES ('questionsPerWeek', '-1')";
			$insertMOTD = "INSERT OR IGNORE INTO SETTINGS (name, value) VALUES ('motd', 'Enter your question below')";
			$insertMOTDLink = "INSERT OR IGNORE INTO SETTINGS (name, value) VALUES ('motdLink', 'https://docs.google.com/document/d/1iv0NTeHQRJWtglRjoellSI-ILkTQlUBd3k8ob9u3KzE/edit')";

			$extendedView = "CREATE VIEW IF NOT EXISTS EXTENDEDQUEUE AS SELECT * FROM QUEUE JOIN Students USING (NetId) WHERE queue_id != 'zoom' ORDER BY QUEUENUM";
			$extendedViewOther = "CREATE VIEW IF NOT EXISTS EXTENDEDOTHERQUEUE AS SELECT * FROM QUEUE JOIN Students USING (NetId) WHERE queue_id == 'zoom' ORDER BY QUEUENUM";
			$helpedView = "CREATE VIEW IF NOT EXISTS HELPED AS SELECT * FROM QUEUE JOIN Students USING (NetId) WHERE STARTEDGETTINGHELPTIME IS NOT NULL ORDER BY QUEUENUM";
			$waitingView = "CREATE VIEW IF NOT EXISTS WAITING AS SELECT * FROM QUEUE JOIN Students USING (NetId) WHERE STARTEDGETTINGHELPTIME IS NULL ORDER BY QUEUENUM";

			$db->exec("BEGIN;");
			$statement1 = $db->prepare($createUsers);
			$statement2 = $db->prepare($createTA);
			$statement3 = $db->prepare($createQueue);
			$statement5 = $db->prepare($createSettings);
			$statementQueueHistory = $db->prepare($createQueueHistory);

			//$statement->bindValue(':id', $id);

			$statement1->execute();
			$statement2->execute();
			$statement3->execute();
			$statement5->execute();
			$statementQueueHistory->execute();

			$statement1->close();
			$statement2->close();
			$statement3->close();
			$statement5->close();
			$statementQueueHistory->close();


			$statement4 = $db->prepare($onDeleteQueueTrigger);
			$statement4->execute();
			$statement4->close();

			$statement7 = $db->prepare($extendedView);
			$statement7->execute();
			$statement7->close();
			$statement7 = $db->prepare($extendedViewOther);
			$statement7->execute();
			$statement7->close();
			$statement7 = $db->prepare($helpedView);
			$statement7->execute();
			$statement7->close();
			$statement7 = $db->prepare($waitingView);
			$statement7->execute();
			$statement7->close();

			$statement6 = $db->prepare($insertActiveQueue);
			$statement6->execute();
			$statement6->close();

			$statement8 = $db->prepare($insertName);
			$statement8->execute();
			$statement8->close();

			$statement9 = $db->prepare($insertStudentPollTime);
			$statement9->execute();
			$statement9->close();

			$statement10 = $db->prepare($insertTAPollTime);
			$statement10->execute();
			$statement10->close();

			$statement11 = $db->prepare($insertNotifyThreshold);
			$statement11->execute();
			$statement11->close();

			$statement12 = $db->prepare($insertPassOffColor);
			$statement12->execute();
			$statement12->close();

			$statement13 = $db->prepare($insertLastXMin);
			$statement13->execute();
			$statement13->close();

			$statement14 = $db->prepare($insertRequireQuestion);
			$statement14->execute();
			$statement14->close();
			$statement14 = $db->prepare($motd);
			$statement14->execute();
			$statement14->close();
			$statement14 = $db->prepare($motdLink);
			$statement14->execute();
			$statement14->close();
			$statement14 = $db->prepare($insertQuestionsPerDay);
			$statement14->execute();
			$statement14->close();
			$statement14 = $db->prepare($insertQuestionsPerWeek);
			$statement14->execute();
			$statement14->close();



			$db->exec('COMMIT');
			//dont close $db because its going to be used by whom ever calle this function
		}
		//return "statement1: " + var_dump($statement1);
	}

	function enqueue($userId, $question, $passOff, $queueId)
	{

		//see if queue is active
		if (isQueueActive() == false)
		{
			return getUserStatus($userId);
		}

		$db = new MyDB();

		$db->exec("BEGIN;");
		/*$onDeleteQueueTrigger = "CREATE TRIGGER IF NOT EXISTS deleteRowonQueueTrigger AFTER DELETE ON QUEUE BEGIN UPDATE QUEUE SET QUEUENUM = QUEUENUM - 1 WHERE QUEUENUM > Old.QUEUENUM;  END;";
		$statement4 = $db->prepare($onDeleteQueueTrigger);
			$statement4->execute();
			$statement4->close();*/

		//check if person is already on the list
		$findDups = "Select * From Queue WHERE NetId = :id";
		$findDupsStmt = $db->prepare($findDups);
		$findDupsStmt->bindValue(':id',$userId);


		$findDupsResult = $findDupsStmt->execute();

		if($findDupsRow = $findDupsResult->fetchArray(SQLITE3_ASSOC) )
		{
			$findDupsResult->finalize();
			$findDupsStmt->close();

			//return data already in DB
			$db->exec("COMMIT");
			$db->close();
			unset($db);
			if ($findDupsRow["queue_id"] == $queueId)
			{
				return array("status"=>"success", "userId"=>$findDupsRow["NetId"], "spot"=>$findDupsRow["QUEUENUM"], "enqueueTime"=>$findDupsRow["ENQUEUETIME"], "question"=>$findDupsRow["QUESTION"], "passOff"=>$findDupsRow["PASSOFF"], "gettingHelpTime"=>$findDupsRow["STARTEDGETTINGHELPTIME"], "beingHelpedBy"=>$findDupsRow["BeingHelpedBy"]);
			}
			else {
				return array("status"=>"error", "message"=>"You can only be in one queue at a time. If you would like to get into this one, please remove yourself from the other queue.");
			}
		}
		else
		{
			$findDupsResult->finalize();
			$findDupsStmt->close();
		}
/*
		$getTopQueue = "SELECT MAX(QUEUENUM) AS MAXNUM FROM QUEUE";
		//$getTopQueue = "SELECT Count(*) AS MAXNUM FROM QUEUE";
		$statement2 = $db->prepare($getTopQueue);
		$result1 = $statement2->execute();

		//$getQueueNum = "SELECT Count(*) FROM QUEUE";
		//$statement2 = $db->prepare($getQueueNum);
		//$queueNumResult = $statement2->excecute();
		if($topResultRow = $result1->fetchArray(SQLITE3_ASSOC) )
		{
			$queueNum = $topResultRow["MAXNUM"] + 1;
		}
		else
		{
			$queueNum = 1;
		}
		$result1->finalize();
		$statement2->close();*/
		$getTopQueue = "SELECT MAX(QUEUENUM) AS MAXNUM FROM QUEUE WHERE queue_id = :queueId";

		//$getTopQueue = "SELECT Count(*) AS MAXNUM FROM QUEUE";
		$statement2 = $db->prepare($getTopQueue);
		$statement2->bindValue(":queueId", $queueId);
		$result1 = $statement2->execute();

		if($topResultRow = $result1->fetchArray(SQLITE3_ASSOC) )
		{
			$queueNum = $topResultRow["MAXNUM"] + 1;
		}
		else
		{
			$queueNum = 1;
		}
		$result1->finalize();
		$statement2->close();

		require_once 'CAS-1.3.4/CAS.php';
		phpCAS::client(CAS_VERSION_2_0,'cas.byu.edu',443,'cas');
		$auth = phpCAS::checkAuthentication();
//here we could check auth. if it is false send back a json {status:loggedOut}
//uncomment this only during testing
//$auth = true;
		if(!$auth)
		{
			return array("status"=>"loggedOut");
		}
		else
		{
			try
			{
				$toReturn;
				//check the supplied userID is the same as the logged in user
	//*******************************************************************
//comment this only during testing
				if(phpCAS::getUser() == $userId)
				{
					//Clean up any injecting jank
					$invalid_characters = array("$", "%", "#", "<", ">", "|");
					$question = str_replace($invalid_characters, "", $question);
					$passOff = str_replace($invalid_characters, "", $passOff);

					if($passOff == "true")
					{
						$question = "PASS OFF";
					}
					else //not passing off
					{
						if($question == "PASS OFF")
						{
							return array("status"=>"error", "message"=>"Not a valid question. If you want to pass off click the check box");
						}
					}

					if(strlen($question) > 200)
					{
						return array("status"=>"error", "message"=>"Question length is too long");

					}

					$stmt = "SELECT value from SETTINGS WHERE name = 'questionsPerDay'";
					$stmt = $db->prepare($stmt);
					$result = $stmt->execute();
					$questionsPerDay = $result->fetchArray(SQLITE3_ASSOC)["value"];
					$result->finalize();
					$stmt->close();

					$stmt = "SELECT value from SETTINGS WHERE name = 'questionsPerWeek'";
					$stmt = $db->prepare($stmt);
					$result = $stmt->execute();
					$questionsPerWeek = $result->fetchArray(SQLITE3_ASSOC)["value"];
					$result->finalize();
					$stmt->close();

					$queueAbuseData = "SELECT COUNT(*) AS COUNT FROM QUEUEHISTORY WHERE NetId = :netId and removedBy is not :helper and enqueueTime >= :curTime and conceptualQuestion is not 'true'";
					$queueAbuseStmt = $db->prepare($queueAbuseData);
					$queueAbuseStmt->bindValue(':netId', $userId);
					$queueAbuseStmt->bindValue(':helper', $userId);
					$queueAbuseStmt->bindValue(':curTime', strtotime("today") * 1000);
					$result = $queueAbuseStmt->execute();
					$timesInPreviousDay = $result->fetchArray(SQLITE3_ASSOC);
					$timesInPreviousDay = $timesInPreviousDay["COUNT"];
					$result->finalize();
					$queueAbuseStmt->close();

					$queueAbuseData = "SELECT COUNT(*) AS COUNT FROM QUEUEHISTORY WHERE NetId = :netId and removedBy is not :helper and enqueueTime >= :curTime  and conceptualQuestion is not 'true'";
					$queueAbuseStmt = $db->prepare($queueAbuseData);
					$queueAbuseStmt->bindValue(':netId', $userId);
					$queueAbuseStmt->bindValue(':helper', $userId);
					$queueAbuseStmt->bindValue(':curTime', strtotime("sunday last week") * 1000);
					$result = $queueAbuseStmt->execute();
					$timesInPreviousWeek = $result->fetchArray(SQLITE3_ASSOC);
					$timesInPreviousWeek = $timesInPreviousWeek["COUNT"];
					$result->finalize();
					$queueAbuseStmt->close();

					if ($questionsPerDay != -1 && $timesInPreviousDay > $questionsPerDay - 1)
					{
						return array("status"=>"error", "message"=>"You have exceeded the daily question limit.");
					}

					if ($questionsPerWeek != -1 && $timesInPreviousWeek > $questionsPerWeek - 1)
					{
						return array("status"=>"error", "message"=>"You have exceeded the weekly question limit.");
					}

					//enter the data in
					$insertData = "INSERT INTO QUEUE (NetId, ENQUEUETIME, QUEUENUM, QUESTION, PASSOFF, queue_id) VALUES (:netId, :time, :spot, :question, :passoff, :queueId);";
					$insertDupsStmt = $db->prepare($insertData);
					$insertDupsStmt->bindValue(':netId',$userId);
					$insertDupsStmt->bindValue(':time',time()*1000);
					$insertDupsStmt->bindValue(':spot', $queueNum);
					$insertDupsStmt->bindValue(':question',$question);
					$insertDupsStmt->bindValue(':passoff', $passOff);
					$insertDupsStmt->bindValue(':queueabuse', $queueAbuse);
					$insertDupsStmt->bindValue(':queueId', $queueId);

					//add the student to the student table
					$insertToStudent = "INSERT OR IGNORE INTO STUDENTS (NetId, counter) VALUES (:netId, 0)";
					$insertToStudentStmt = $db->prepare($insertToStudent);
					$insertToStudentStmt->bindValue(':netId',$userId);


					$insertDupsStmt->execute();
					$insertToStudentStmt->execute();

					$insertDupsStmt->close();
					$insertToStudentStmt->close();

					$toReturn = array("status"=>"success", "enqueueTime"=>time()*1000, "spot"=>$queueNum, "question"=>$question, "passOff"=>$passOff);
				}
				else
				{
					$toReturn = array("status"=>"error", "message"=>"not authorized");
				}

				$db->exec("COMMIT");
				$db->close();
				unset($db);
				return $toReturn;
			}
			catch(CAS_OutOfSequenceBeforeClientException $e)
			{
				//return error
				$db->exec("ROLLBACK");
				$db->close();
				unset($db);
				return array("status"=>"error", "message"=>"not authorized");
			}
		}
	}

	function shouldLimitDaily()
	{
		$db = new MyDB();
		$db->exec("BEGIN;");

		$stmt = "SELECT value from SETTINGS WHERE name = 'questionsPerDay'";
		$stmt = $db->prepare($stmt);
		$result = $stmt->execute();
		$questionsPerDay = $result->fetchArray(SQLITE3_ASSOC)["value"];
		$result->finalize();
		$stmt->close();
		$db->exec("COMMIT");
		$db->close();
		unset($db);
		return $questionsPerDay != -1;
	}

	function shouldLimitWeekly()
	{
		$db = new MyDB();
		$db->exec("BEGIN;");

		$stmt = "SELECT value from SETTINGS WHERE name = 'questionsPerWeek'";
		$stmt = $db->prepare($stmt);
		$result = $stmt->execute();
		$questionsPerWeek = $result->fetchArray(SQLITE3_ASSOC)["value"];
		$result->finalize();
		$stmt->close();
		$db->exec("COMMIT");
		$db->close();
		unset($db);
		return $questionsPerWeek != -1;
	}

	function finishHelping($userIdToRemove, $isConceptual)
	{
		try
		{
			require_once 'CAS-1.3.4/CAS.php';
			phpCAS::client(CAS_VERSION_2_0,'cas.byu.edu',443,'cas');
			$auth = phpCAS::checkAuthentication();
			$thisUsersID = phpCAS::getUser();

			//only uncomment during testing
			//$thisUsersID = $userIdToRemove; //this means someones removing themselves
			//$thisUsersID = $userIdToRemove . "NOT"; //this means a TA is removing someone
			$settings = getSettings();
			$db = new MyDB();
			$db->exec("BEGIN;");
			$arrayToReturn;

			$enqueueDetails = getEnqueueDetails($db, $userIdToRemove);
			//the student is removing themselves
			if($thisUsersID == $userIdToRemove)
			{
				//check if they were being helped, if so treat is as if the TA removed them, otherwise just remove them
				if($enqueueDetails["startedGettingHelpTime"] == null)
				{
					//just remove student from queue no mas
					/*$updateQueue = "UPDATE QUEUE SET QUEUENUM = QUEUENUM - 1 WHERE QUEUENUM > Old.QUEUENUM";
					$updateStmt = $db->prepare($updateQueue);
					$updateStmt->excecute();
					$updateStmt->close();*/
					$removeUser = "DELETE FROM QUEUE WHERE NetId = :netId";
					$removeUserStmt = $db->prepare($removeUser);
					$removeUserStmt->bindValue(':netId',$userIdToRemove);
					$removeUserStmt->execute();
					$removeUserStmt->close();

					if($db->changes() > 0)
					{
						insertHistory($db, $userIdToRemove, $enqueueDetails["beingHelpedBy"], $enqueueDetails, "inlab");
					}
				}
				else //the student was being helped by a TA, so act like a TA removed them
				{
					dequeueHelperForTa($db, $userIdToRemove, $enqueueDetails["beingHelpedBy"], $enqueueDetails, "inlab");
				}

				//update
				$avgs = getAverages($db);
				$arrayToReturn = array("status"=>'success', "userId"=>$thisUsersID, "spot"=>-1, "enqueueTime"=>0, "settings"=>$settings, "avgs"=>$avgs);
			}
			else //it could be a TA. Lets check
			{
				$findTA = "SELECT COUNT(*) AS NUM FROM TAS WHERE NetId = :netId";
				$findTAStmt = $db->prepare($findTA);
				$findTAStmt->bindValue(':netId',$thisUsersID);
				$findTARslt = $findTAStmt->execute();
				//A TA was found
				$findTARow = $findTARslt->fetchArray(SQLITE3_ASSOC);
				if($findTARow["NUM"] == 1)
				{
					$findTARslt->finalize();
					$findTAStmt->close();
					//
					// //Check if STARTEDGETTINGHELPTIME is empty, if so, update QUEUE and set STARTEDGETTINGHELPTIME to current time
					// if($enqueueDetails["startedGettingHelpTime"] == null)
					// {
					// 	$sql = "UPDATE QUEUE Set startedGettingHelpTime = :time, BeingHelpedBy = :taId, QUEUENUM = 0 WHERE netId = :netId"; //Q
					// 	$updateStmt = $db->prepare($sql);
					// 	$updateStmt->bindValue(":netId", $userIdToRemove);
					// 	$updateStmt->bindValue(":taId", $thisUsersID);
					// 	$updateStmt->bindValue("time", time()*1000);
					// 	$updateStmt->execute();
					// 	$updateStmt->close();
					// }
					// else //remove them from the queue and update the history
					// {
					dequeueHelperForTaConceptual($db, $userIdToRemove, $enqueueDetails["beingHelpedBy"], $enqueueDetails, $isConceptual, "inlab");
					// }
					$avgs = getAverages($db);
					$arrayToReturn = array("status"=>"success", "list"=>getQueue($db, "inlab"), "helped"=>getHelped($db, "inlab"), "other_list"=>getQueue($db, "zoom"), "other_helped"=>getHelped($db, "zoom"), "avgs"=>$avgs);
				}
				else
				{
					$findTARslt->finalize();
					$findTAStmt->close();
					//return error
					$arrayToReturn = array("status"=>"error", "message"=>"not authorized to remove that person");
				}
			}

			$db->exec("COMMIT");

		}
		catch(CAS_OutOfSequenceBeforeClientException $e)
		{
			//return error
			$db->exec("ROLLBACK");
			$arrayToReturn = array("status"=>"error", "message"=>"not authorized");
		}

		$db->close();
		unset($db);
		return $arrayToReturn;
	}

	function finishHelpingOtherQueue($userIdToRemove, $isConceptual)
	{
		try
		{
			require_once 'CAS-1.3.4/CAS.php';
			phpCAS::client(CAS_VERSION_2_0,'cas.byu.edu',443,'cas');
			$auth = phpCAS::checkAuthentication();
			$thisUsersID = phpCAS::getUser();

			//only uncomment during testing
			//$thisUsersID = $userIdToRemove; //this means someones removing themselves
			//$thisUsersID = $userIdToRemove . "NOT"; //this means a TA is removing someone
			$settings = getSettings();
			$db = new MyDB();
			$db->exec("BEGIN;");
			$arrayToReturn;

			$enqueueDetails = getEnqueueDetails($db, $userIdToRemove);
			//the student is removing themselves
			if($thisUsersID == $userIdToRemove)
			{
				//check if they were being helped, if so treat is as if the TA removed them, otherwise just remove them
				if($enqueueDetails["startedGettingHelpTime"] == null)
				{
					//just remove student from queue no mas
					/*$updateQueue = "UPDATE QUEUE SET QUEUENUM = QUEUENUM - 1 WHERE QUEUENUM > Old.QUEUENUM";
					$updateStmt = $db->prepare($updateQueue);
					$updateStmt->excecute();
					$updateStmt->close();*/
					$removeUser = "DELETE FROM QUEUE WHERE NetId = :netId and queue_id = :queueId";
					$removeUserStmt = $db->prepare($removeUser);
					$removeUserStmt->bindValue(':netId',$userIdToRemove);
					$removeUserStmt->bindValue(':netId',"zoom");
					$removeUserStmt->execute();
					$removeUserStmt->close();

					if($db->changes() > 0)
					{
						insertHistory($db, $userIdToRemove, $enqueueDetails["beingHelpedBy"], $enqueueDetails, "zoom");
					}
				}
				else //the student was being helped by a TA, so act like a TA removed them
				{
					dequeueHelperForTa($db, $userIdToRemove, $enqueueDetails["beingHelpedBy"], $enqueueDetails, "zoom");
				}

				//update
				$avgs = getAverages($db);
				$arrayToReturn = array("status"=>'success', "userId"=>$thisUsersID, "spot"=>-1, "enqueueTime"=>0, "settings"=>$settings, "avgs"=>$avgs);
			}
			else //it could be a TA. Lets check
			{
				$findTA = "SELECT COUNT(*) AS NUM FROM TAS WHERE NetId = :netId";
				$findTAStmt = $db->prepare($findTA);
				$findTAStmt->bindValue(':netId',$thisUsersID);
				$findTARslt = $findTAStmt->execute();
				//A TA was found
				$findTARow = $findTARslt->fetchArray(SQLITE3_ASSOC);
				if($findTARow["NUM"] == 1)
				{
					$findTARslt->finalize();
					$findTAStmt->close();
					dequeueHelperForTaConceptual($db, $userIdToRemove, $enqueueDetails["beingHelpedBy"], $enqueueDetails, $isConceptual, "zoom");
					$avgs = getAverages($db);
					$arrayToReturn = array("status"=>"success", "list"=>getQueue($db, "inlab"), "helped"=>getHelped($db, "inlab"), "other_list"=>getQueue($db, "zoom"), "other_helped"=>getHelped($db, "zoom"), "avgs"=>$avgs);
				}
				else
				{
					$findTARslt->finalize();
					$findTAStmt->close();
					//return error
					$arrayToReturn = array("status"=>"error", "message"=>"not authorized to remove that person");
				}
			}

			$db->exec("COMMIT");

		}
		catch(CAS_OutOfSequenceBeforeClientException $e)
		{
			//return error
			$db->exec("ROLLBACK");
			$arrayToReturn = array("status"=>"error", "message"=>"not authorized");
		}

		$db->close();
		unset($db);
		return $arrayToReturn;
	}

	function dequeueUser($userIdToRemove)
	{


		try
		{
			require_once 'CAS-1.3.4/CAS.php';
			phpCAS::client(CAS_VERSION_2_0,'cas.byu.edu',443,'cas');
			$auth = phpCAS::checkAuthentication();
			$thisUsersID = phpCAS::getUser();

			//only uncomment during testing
			//$thisUsersID = $userIdToRemove; //this means someones removing themselves
			//$thisUsersID = $userIdToRemove . "NOT"; //this means a TA is removing someone
			$settings = getSettings();
			$db = new MyDB();
			$db->exec("BEGIN;");
			$arrayToReturn;

			$enqueueDetails = getEnqueueDetails($db, $userIdToRemove);
			//the student is removing themselves
			if($thisUsersID == $userIdToRemove)
			{
				//check if they were being helped, if so treat is as if the TA removed them, otherwise just remove them
				if($enqueueDetails["startedGettingHelpTime"] == null)
				{
					//just remove student from queue no mas
					/*$updateQueue = "UPDATE QUEUE SET QUEUENUM = QUEUENUM - 1 WHERE QUEUENUM > Old.QUEUENUM";
					$updateStmt = $db->prepare($updateQueue);
					$updateStmt->excecute();
					$updateStmt->close();*/
					$removeUser = "DELETE FROM QUEUE WHERE NetId = :netId";
					$removeUserStmt = $db->prepare($removeUser);
					$removeUserStmt->bindValue(':netId',$userIdToRemove);
					$removeUserStmt->execute();
					$removeUserStmt->close();

					if($db->changes() > 0)
					{
						insertHistory($db, $userIdToRemove, $userIdToRemove, $enqueueDetails, "inlab");
					}
				}
				else //the student was being helped by a TA, so act like a TA removed them
				{
					dequeueHelperForTa($db, $userIdToRemove, $enqueueDetails["beingHelpedBy"], $enqueueDetails, "inlab");
				}

				//update
				$avgs = getAverages($db);
				$arrayToReturn = array("status"=>'success', "userId"=>$thisUsersID, "spot"=>-1, "enqueueTime"=>0, "settings"=>$settings, "avgs"=>$avgs);
			}
			else //it could be a TA. Lets check
			{
				$findTA = "SELECT COUNT(*) AS NUM FROM TAS WHERE NetId = :netId";
				$findTAStmt = $db->prepare($findTA);
				$findTAStmt->bindValue(':netId',$thisUsersID);
				$findTARslt = $findTAStmt->execute();
				//A TA was found
				$findTARow = $findTARslt->fetchArray(SQLITE3_ASSOC);
				if($findTARow["NUM"] == 1)
				{
					$findTARslt->finalize();
					$findTAStmt->close();

					//Check if STARTEDGETTINGHELPTIME is empty, if so, update QUEUE and set STARTEDGETTINGHELPTIME to current time
					if($enqueueDetails["startedGettingHelpTime"] == null)
					{
						$sql = "UPDATE QUEUE Set startedGettingHelpTime = :time, BeingHelpedBy = :taId, QUEUENUM = 0 WHERE netId = :netId"; //Q
						$updateStmt = $db->prepare($sql);
						$updateStmt->bindValue(":netId", $userIdToRemove);
						$updateStmt->bindValue(":taId", $thisUsersID);
						$updateStmt->bindValue("time", time()*1000);
						$updateStmt->execute();
						$updateStmt->close();
					}
					// else //remove them from the queue and update the history
					// {
					// 	dequeueHelperForTa($db, $userIdToRemove, $thisUsersID, $enqueueDetails);
					// }
					$avgs = getAverages($db);
					$arrayToReturn = array("status"=>"success", "list"=>getQueue($db, "inlab"), "helped"=>getHelped($db, "inlab"), "other_list"=>getQueue($db, "zoom"), "other_helped"=>getHelped($db, "zoom"), "avgs"=>$avgs);

				}
				else
				{
					$findTARslt->finalize();
					$findTAStmt->close();
					//return error
					$arrayToReturn = array("status"=>"error", "message"=>"not authorized to remove that person");
				}
			}

			$db->exec("COMMIT");

		}
		catch(CAS_OutOfSequenceBeforeClientException $e)
		{
			//return error
			$db->exec("ROLLBACK");
			$arrayToReturn = array("status"=>"error", "message"=>"not authorized");
		}

		$db->close();
		unset($db);
		return $arrayToReturn;
	}

	function dequeueUserOtherQueue($userIdToRemove)
	{


		try
		{
			require_once 'CAS-1.3.4/CAS.php';
			phpCAS::client(CAS_VERSION_2_0,'cas.byu.edu',443,'cas');
			$auth = phpCAS::checkAuthentication();
			$thisUsersID = phpCAS::getUser();

			//only uncomment during testing
			//$thisUsersID = $userIdToRemove; //this means someones removing themselves
			//$thisUsersID = $userIdToRemove . "NOT"; //this means a TA is removing someone
			$settings = getSettings();
			$db = new MyDB();
			$db->exec("BEGIN;");
			$arrayToReturn;

			$enqueueDetails = getEnqueueDetails($db, $userIdToRemove);
			//the student is removing themselves
			if($thisUsersID == $userIdToRemove)
			{
				//check if they were being helped, if so treat is as if the TA removed them, otherwise just remove them
				if($enqueueDetails["startedGettingHelpTime"] == null)
				{
					//just remove student from queue no mas
					/*$updateQueue = "UPDATE QUEUE SET QUEUENUM = QUEUENUM - 1 WHERE QUEUENUM > Old.QUEUENUM";
					$updateStmt = $db->prepare($updateQueue);
					$updateStmt->excecute();
					$updateStmt->close();*/
					$removeUser = "DELETE FROM QUEUE WHERE NetId = :netId AND queue_id == 'zoom'";
					$removeUserStmt = $db->prepare($removeUser);
					$removeUserStmt->bindValue(':netId',$userIdToRemove);
					$removeUserStmt->execute();
					$removeUserStmt->close();

					if($db->changes() > 0)
					{
						insertHistory($db, $userIdToRemove, $userIdToRemove, $enqueueDetails, "zoom");
					}
				}
				else //the student was being helped by a TA, so act like a TA removed them
				{
					dequeueHelperForTa($db, $userIdToRemove, $enqueueDetails["beingHelpedBy"], $enqueueDetails, "zoom");
				}

				//update
				$avgs = getAverages($db);
				$arrayToReturn = array("status"=>'success', "userId"=>$thisUsersID, "spot"=>-1, "enqueueTime"=>0, "settings"=>$settings, "avgs"=>$avgs);
			}
			else //it could be a TA. Lets check
			{
				$findTA = "SELECT COUNT(*) AS NUM FROM TAS WHERE NetId = :netId";
				$findTAStmt = $db->prepare($findTA);
				$findTAStmt->bindValue(':netId',$thisUsersID);
				$findTARslt = $findTAStmt->execute();
				//A TA was found
				$findTARow = $findTARslt->fetchArray(SQLITE3_ASSOC);
				if($findTARow["NUM"] == 1)
				{
					$findTARslt->finalize();
					$findTAStmt->close();

					//Check if STARTEDGETTINGHELPTIME is empty, if so, update QUEUE and set STARTEDGETTINGHELPTIME to current time
					if($enqueueDetails["startedGettingHelpTime"] == null)
					{
						$sql = "UPDATE QUEUE Set startedGettingHelpTime = :time, BeingHelpedBy = :taId, QUEUENUM = 0 WHERE netId = :netId and queue_id = :queudId"; //Q
						$updateStmt = $db->prepare($sql);
						$updateStmt->bindValue(":netId", $userIdToRemove);
						$updateStmt->bindValue(":taId", $thisUsersID);
						$updateStmt->bindValue("time", time()*1000);
						$updateStmt->bindValue(":queueId", "zoom");
						$updateStmt->execute();
						$updateStmt->close();
					}
					// else //remove them from the queue and update the history
					// {
					// 	dequeueHelperForTa($db, $userIdToRemove, $thisUsersID, $enqueueDetails);
					// }
					$avgs = getAverages($db);
					$arrayToReturn = array("status"=>"success", "list"=>getQueue($db, "inlab"), "helped"=>getHelped($db, "inlab"), "other_list"=>getQueue($db, "zoom"), "other_helped"=>getHelped($db, "zoom"), "avgs"=>$avgs);

				}
				else
				{
					$findTARslt->finalize();
					$findTAStmt->close();
					//return error
					$arrayToReturn = array("status"=>"error", "message"=>"not authorized to remove that person");
				}
			}

			$db->exec("COMMIT");

		}
		catch(CAS_OutOfSequenceBeforeClientException $e)
		{
			//return error
			$db->exec("ROLLBACK");
			$arrayToReturn = array("status"=>"error", "message"=>"not authorized");
		}

		$db->close();
		unset($db);
		return $arrayToReturn;
	}

	function dequeueHelperForTa($db, $userIdToRemove, $thisUsersID, $enqueueDetails, $queueId)
	{
		dequeueHelperForTaConceptual($db, $userIdToRemove, $thisUsersID, $enqueueDetails, false, $queueId);
	}

	function dequeueHelperForTaConceptual($db, $userIdToRemove, $thisUsersID, $enqueueDetails, $isConceptual, $queueId)
	{
		//remove the student from the queue (a trigger will update everyone elses spot in line)
		$removeStudentSQL = "DELETE FROM QUEUE WHERE NetId = :netId AND queue_id = :queueId";
		$removeStudentStmt = $db->prepare($removeStudentSQL);
		$removeStudentStmt->bindValue(':netId',$userIdToRemove);
		$removeStudentStmt->bindValue(':queueId', $queueId);
		$removeStudentStmt->execute();
		$removeStudentStmt->close();

		if($db->changes() > 0)
		{
			$incrementStudentCounter;
			$incrementTACounter;
			if($enqueueDetails["passOff"] == "true")
			{
				$incrementTACounter = "UPDATE TAS SET PassOffCounter = PassOffCounter + 1 WHERE NetId = :netId";
				$incrementStudentCounter = "UPDATE Students SET PassOffCOUNTER = PassOffCOUNTER + 1 WHERE NetId = :netId";
			}
			else
			{
				$incrementTACounter = "UPDATE TAS SET Counter = Counter + 1 WHERE NetId = :netId";
				$incrementStudentCounter = "UPDATE Students SET COUNTER = COUNTER + 1 WHERE NetId = :netId";
			}
			//increment the TAs counter
			$incrementTACounterStmt = $db->prepare($incrementTACounter);
			$incrementTACounterStmt->bindValue(':netId',$thisUsersID);
			$incrementTACounterStmt->execute();
			$incrementTACounterStmt->close();

			//increment the Students counter
			$incrementStudentCounterStmt = $db->prepare($incrementStudentCounter);
			$incrementStudentCounterStmt->bindValue(':netId',$userIdToRemove);
			$incrementStudentCounterStmt->execute();
			$incrementStudentCounterStmt->close();

			insertHistoryConceptual($db, $userIdToRemove, $thisUsersID, $enqueueDetails, $isConceptual, $queueId);
		}
	}

	function getEnqueueDetails($db, $netId)
	{
		$getTimeStamp = "Select * FROM QUEUE WHERE NetId = :netId";
		$getTimeStampStmt = $db->prepare($getTimeStamp);
		$getTimeStampStmt->bindValue(':netId', $netId);
		$getTimeStampResult = $getTimeStampStmt->execute();

		$toReturn = array();
		if($getTimeStampRow = $getTimeStampResult->fetchArray(SQLITE3_ASSOC) )
		{
			$toReturn = array("enqueueTime"=>$getTimeStampRow["ENQUEUETIME"], "question"=>$getTimeStampRow["QUESTION"], "passOff"=>$getTimeStampRow["PASSOFF"], "startedGettingHelpTime"=>$getTimeStampRow["STARTEDGETTINGHELPTIME"], "beingHelpedBy"=>$getTimeStampRow["BeingHelpedBy"]);
		}
		$getTimeStampResult->finalize();
		$getTimeStampStmt->close();

		return $toReturn;
	}

	function insertHistory($db, $netId, $removedBy, $enqueueDetails, $queueId)
	{
		insertHistoryConceptual($db, $netId, $removedBy, $enqueueDetails, false, $queueId);
	}

	function insertHistoryConceptual($db, $netId, $removedBy, $enqueueDetails, $isConceptual, $queueId)
	{

		$insertHistory = "Insert Into QUEUEHISTORY (NetId, removedBy, enqueueTime, dequeueTime, QUESTION, PASSOFF, DoneGettingHelpTime, conceptualQuestion, queue_id) VALUES (:netId, :removedBy, :enqueueTime, :dequeueTime, :question, :passoff, :doneGettingHelpTime, :isConceptual, :queueId)";

		$dequeueHistoryStmt = $db->prepare($insertHistory);
		$dequeueHistoryStmt->bindValue(':netId', $netId);
		$dequeueHistoryStmt->bindValue(':removedBy', $removedBy);
		$dequeueHistoryStmt->bindValue(':enqueueTime', $enqueueDetails["enqueueTime"]);
		//if startedGettingHelpTime is null and were here it means a student got out of line without getting help. Just insert the current time
		$dequeueHistoryStmt->bindValue(':dequeueTime', ($enqueueDetails["startedGettingHelpTime"] == null ? time()*1000 : $enqueueDetails["startedGettingHelpTime"]));
		$dequeueHistoryStmt->bindValue(':question', $enqueueDetails["question"]);
		$dequeueHistoryStmt->bindValue(':passoff', $enqueueDetails["passOff"]);
		//if startedGettingHelpTime is null they didn't get help so just put null here, other wise put in the current time
		$dequeueHistoryStmt->bindValue(':doneGettingHelpTime', ($enqueueDetails["startedGettingHelpTime"] == null ? $enqueueDetails["startedGettingHelpTime"] : time()*1000));
		$dequeueHistoryStmt->bindValue(':isConceptual', $isConceptual);
		$dequeueHistoryStmt->bindValue(':queueId', $queueId);
		$dequeueHistoryStmt->execute();
		$dequeueHistoryStmt->close();

	}

	function clearQueue()
	{
		$toReturn;
		try
		{
			require_once 'CAS-1.3.4/CAS.php';
			phpCAS::client(CAS_VERSION_2_0,'cas.byu.edu',443,'cas');
			$auth = phpCAS::checkAuthentication();

			//only a TA can do this
			if(verifyTA(phpCAS::getUser()))
			{
				$db = new MyDB();
				$db->exec("BEGIN;");
				//get all entrys in the queue, have to insert them into queuehistory table
//After thinking it over I decided to comment this out. My reasoning is that if someone clears the
//queue, no ones stats are increased, so why should the QueueHistory reflect that
/*				$getAllQueue = "Select * FROM QUEUE";
				$getAllQueueStmt = $db->prepare($getAllQueue);
				$getAllQueueRslt = $getAllQueueStmt->execute();

				$insertHistory = "Insert Into QUEUEHISTORY (NetId, removedBy, enqueueTime, dequeueTime) VALUES (:netId, :removedBy, :enqueueTime, :dequeueTime)";
				$dequeueHistoryStmt = $db->prepare($insertHistory);

				while($row = $getAllQueueRslt->fetchArray(SQLITE3_ASSOC) )
				{
					$dequeueHistoryStmt->bindValue(':netId', $row["NetId"]);
					$dequeueHistoryStmt->bindValue(':removedBy', phpCAS::getUser());
					$dequeueHistoryStmt->bindValue(':enqueueTime', $row["ENQUEUETIME"]);
					$dequeueHistoryStmt->bindValue(':dequeueTime', time()*1000);
					$dequeueHistoryStmt->execute();
				}

				$getAllQueueRslt->finalize();
				$getAllQueueStmt->close();
				$dequeueHistoryStmt->close();
*/
				$sql = "DELETE FROM QUEUE";
				$stmt = $db->prepare($sql);
				$stmt->execute();
				$stmt->close();

				$avgs = getAverages($db);
				$toReturn = array("status"=>"success", "list"=>getQueue($db, "inlab"), "helped"=>getHelped($db, "inlab"), "other_list"=>getQueue($db, "zoom"), "other_helped"=>getHelped($db, "zoom"), "avgs"=>$avgs);
				$db->exec("COMMIT");
				$db->close();
				unset($db);
			}
			else
				$toReturn = array("status"=>"No authorized to do that");


		}
		catch(CAS_OutOfSequenceBeforeClientException $e)
		{
			$toReturn = array("status"=>"error", "message"=>"login error");
		}

		return $toReturn;
	}

	function getQuestionStatus($db, $userId)
	{
		$stmt = "SELECT value from SETTINGS WHERE name = 'questionsPerDay'";
		$stmt = $db->prepare($stmt);
		$result = $stmt->execute();
		$questionsPerDay = $result->fetchArray(SQLITE3_ASSOC)["value"];
		$result->finalize();
		$stmt->close();

		$stmt = "SELECT value from SETTINGS WHERE name = 'questionsPerWeek'";
		$stmt = $db->prepare($stmt);
		$result = $stmt->execute();
		$questionsPerWeek = $result->fetchArray(SQLITE3_ASSOC)["value"];
		$result->finalize();
		$stmt->close();

		$queueAbuseData = "SELECT COUNT(*) AS COUNT FROM QUEUEHISTORY WHERE NetId is :netId and removedBy is not :helper and enqueueTime >= :curTime and conceptualQuestion is not 'true'";
		$queueAbuseStmt = $db->prepare($queueAbuseData);
		$queueAbuseStmt->bindValue(':netId', $userId);
		$queueAbuseStmt->bindValue(':helper', $userId);
		$queueAbuseStmt->bindValue(':curTime', strtotime("today") * 1000);
		$result = $queueAbuseStmt->execute();
		$timesInPreviousDay = $result->fetchArray(SQLITE3_ASSOC);
		$timesInPreviousDay = $timesInPreviousDay["COUNT"];
		$result->finalize();
		$queueAbuseStmt->close();

		$queueAbuseData = "SELECT COUNT(*) AS COUNT FROM QUEUEHISTORY WHERE NetId is :netId and removedBy is not :helper and enqueueTime >= :curTime and conceptualQuestion is not 'true'";
		$queueAbuseStmt = $db->prepare($queueAbuseData);
		$queueAbuseStmt->bindValue(':netId', $userId);
		$queueAbuseStmt->bindValue(':helper', $userId);
		$queueAbuseStmt->bindValue(':curTime', strtotime("sunday last week") * 1000);
		$result = $queueAbuseStmt->execute();
		$timesInPreviousWeek = $result->fetchArray(SQLITE3_ASSOC);
		$timesInPreviousWeek = $timesInPreviousWeek["COUNT"];
		$result->finalize();
		$queueAbuseStmt->close();
		$dailyQuestionsRemaining = $questionsPerDay - $timesInPreviousDay;
		$weeklyQuestionsRemaining = $questionsPerWeek - $timesInPreviousWeek;
		return array("questionsRemainingDay"=>$dailyQuestionsRemaining, "questionsRemainingWeek"=>$weeklyQuestionsRemaining);
	}

	function getUserStatus($userId, $queueId)
	{
		$settings = getSettings();
		$db = new MyDB();

		$db->exec('BEGIN');
		$avgs = getAverages($db);
		$questionStatus = getQuestionStatus($db, $userId);
		if ($queueId == "zoom")
		{
			$sql = "SELECT * FROM EXTENDEDOTHERQUEUE WHERE NetId = :netId";
		}
		else {
			$sql = "SELECT * FROM EXTENDEDQUEUE WHERE NetId = :netId"; //swap these declarations based on which queue page this will be used for use this one for the zoom queue page
		}
		$stmt = $db->prepare($sql);
		$stmt->bindValue("netId", $userId);
		$result = $stmt->execute();

		$itemInQueue;

		if($row = $result->fetchArray(SQLITE3_ASSOC) )
		{
			$TAName = getNameStatus($row["BeingHelpedBy"])["name"];
			$itemInQueue = array("status"=>"success", "userId"=>$row["NetId"], "name"=>$row["name"], "spot"=>$row["QUEUENUM"], "enqueueTime"=>$row["ENQUEUETIME"], "helpScore"=>$row["Counter"],"settings"=>$settings, "avgs"=>$avgs, "questionStatus"=>$questionStatus, "question"=>$row["QUESTION"], "passOff"=>$row["PASSOFF"], "startedGettingHelpTime"=>$row["STARTEDGETTINGHELPTIME"], "beingHelpedBy"=>$TAName);
		}
		else
		{
			$itemInQueue = array("status"=>'success', "userId"=>$userId, "spot"=>-1, "enqueueTime"=>0, "settings"=>$settings, "avgs"=>$avgs, "questionStatus"=>$questionStatus);
		}
		$result->finalize();
		$stmt->close();
		$db->exec("COMMIT");
		$db->close();
		unset($db);
		return $itemInQueue;
	}

	function verifyTA($userId)
	{
		$db = new MyDB();
		$db->exec("BEGIN;");
		$findTA = "SELECT * FROM TAS WHERE NetId = :netId and active = 1";
		$findTAStmt = $db->prepare($findTA);
		$findTAStmt->bindValue(":netId", $userId);
		$findTAResult = $findTAStmt->execute();

		$result = "";
		if($findTARow = $findTAResult->fetchArray(SQLITE3_ASSOC) )
		{
			$result = $findTARow["NetId"] == $userId;
		}
		$findTAResult->finalize();
		$findTAStmt->close();
		$db->exec("COMMIT;");
		$db->close();
		unset($db);
		return $result;
	}

	function inTATable($userId)
	{
		$db = new MyDB();
		$db->exec("BEGIN;");
		$findTA = "SELECT * FROM TAS WHERE NetId = :netId";
		$findTAStmt = $db->prepare($findTA);
		$findTAStmt->bindValue(":netId", $userId);
		$findTAResult = $findTAStmt->execute();

		$result = "";
		if($findTARow = $findTAResult->fetchArray(SQLITE3_ASSOC) )
		{
			$result = $findTARow["NetId"] == $userId;
		}
		$findTAResult->finalize();
		$findTAStmt->close();
		$db->exec("COMMIT;");
		$db->close();
		unset($db);
		return $result;
	}

	function getQueue($db, $queueId)
	{
		$manageDB = false;
		if($db == null)
		{
			$manageDB = true;
			$db = new MyDB();

			$db->exec('BEGIN');
		}
		$sql = "SELECT * FROM WAITING where queue_id = :queueId";
		$stmt = $db->prepare($sql);
		$stmt->bindValue(":queueId", $queueId);
		$result = $stmt->execute();

		$listToReturn = array();
		//******************************************
		while($row = $result->fetchArray(SQLITE3_ASSOC) )
		{
			$itemInQueue = array("netId"=>$row["NetId"], "name"=>$row["name"], "spot"=>$row["QUEUENUM"], "enqueueTime"=>$row["ENQUEUETIME"], "helpScore"=>$row["Counter"], "question"=>$row["QUESTION"], "passOff"=>$row["PASSOFF"], "startedGettingHelpTime"=>$row["STARTEDGETTINGHELPTIME"], "beingHelpedBy"=>$row["BeingHelpedBy"]);
			array_push($listToReturn, $itemInQueue);
		}
		$result->finalize();
		$stmt->close();

		if($manageDB)
		{
			$db->exec("COMMIT");
			$db->close();
			unset($db);
		}
		return $listToReturn;
	}

	function getHelped($db, $queueId)
	{
		$manageDB = false;
		if($db == null)
		{
			$manageDB = true;
			$db = new MyDB();

			$db->exec('BEGIN');
		}
		$sql = "SELECT * FROM HELPED where queue_id = :queueId";
		$stmt = $db->prepare($sql);
		$stmt->bindValue(":queueId", $queueId);
		$result = $stmt->execute();

		$listToReturn = array();
		//******************************************
		while($row = $result->fetchArray(SQLITE3_ASSOC) )
		{
			$itemInQueue = array("netId"=>$row["NetId"], "name"=>$row["name"], "spot"=>$row["QUEUENUM"], "enqueueTime"=>$row["ENQUEUETIME"], "helpScore"=>$row["Counter"], "question"=>$row["QUESTION"], "passOff"=>$row["PASSOFF"], "startedGettingHelpTime"=>$row["STARTEDGETTINGHELPTIME"], "beingHelpedBy"=>$row["BeingHelpedBy"]);
			array_push($listToReturn, $itemInQueue);
		}
		$result->finalize();
		$stmt->close();

		if($manageDB)
		{
			$db->exec("COMMIT");
			$db->close();
			unset($db);
		}
		return $listToReturn;
	}

	function isMOTDLink()
	{
		$db = new MyDB();
		$db->exec("BEGIN");
		$sql = "SELECT value FROM SETTINGS WHERE name='motdLink'";
		$stmt = $db->prepare($sql);
		$result = $stmt->execute();
		$value = $result->fetchArray(SQLITE3_ASSOC);
		$result->finalize();
		$stmt->close();
		$db->close();
		unset($db);
		return ($value['value'] != "");
	}

	function getMOTDLink()
	{
		$db = new MyDB();
		$db->exec("BEGIN");
		$sql = "SELECT value FROM SETTINGS WHERE name='motdLink'";
		$stmt = $db->prepare($sql);
		$result = $stmt->execute();
		$value = $result->fetchArray(SQLITE3_ASSOC);
		$result->finalize();
		$stmt->close();
		$db->close();
		unset($db);
		return $value['value'];
	}

	function getMOTD()
	{
		$db = new MyDB();
		$db->exec("BEGIN");
		$sql = "SELECT value FROM SETTINGS WHERE name='motd'";
		$stmt = $db->prepare($sql);
		$result = $stmt->execute();
		$value = $result->fetchArray(SQLITE3_ASSOC);
		$result->finalize();
		$stmt->close();
		$db->close();
		unset($db);
		return $value["value"];
	}

	function setMOTD($motd)
	{
			$db = new MyDB();
			$db->exec("BEGIN");
			$sql = "UPDATE SETTINGS SET value = :msg WHERE name = 'motd'";
			$stmt = $db->prepare($sql);
			$stmt->bindValue(":msg", $motd);
			$stmt->execute();
			$stmt->close();
			$db->exec("COMMIT");
			$db->close();
			unset($db);
	}

	function setMOTDLink($link)
	{
		$db = new MyDB();
		$db->exec("BEGIN");
		$sql2 = "UPDATE SETTINGS SET value = :link WHERE name ='motdLink'";
		$stmt2 = $db->prepare($sql2);
		$stmt2->bindValue(":link", $link);
		$stmt2->execute();
		$stmt2->close();
		$db->exec("COMMIT");
		$db->close();
		unset($db);
	}

	function getStats()
	{
		$listToReturn = array("tas"=>array(),"students"=>array());
		$db = new MyDB();
		$db->exec("BEGIN");
		$sql = "SELECT * FROM STUDENTS ORDER BY COUNTER DESC";
		$stmt = $db->prepare($sql);

		$result = $stmt->execute();
		//***********************************
		while($row = $result->fetchArray(SQLITE3_ASSOC))
		{
			array_push($listToReturn["students"], array("netId"=>$row["NetId"], "name"=>$row["name"], "helpCounter"=>$row["Counter"], 'passOffCounter'=>$row["PassOffCounter"]));
		}
		$result->finalize();
		$stmt->close();

		$sqlT = "SELECT * FROM TAS ORDER BY COUNTER DESC";
		$stmtT = $db->prepare($sqlT);
		$resultT = $stmtT->execute();

		//********************************************
		while($row = $resultT->fetchArray(SQLITE3_ASSOC) )
		{
			array_push($listToReturn["tas"], array("netId"=>$row["NetId"], "name"=>$row["name"], "helpCounter"=>$row["Counter"], 'passOffCounter'=>$row["PassOffCounter"], "active"=>$row["Active"]));
		}
		$resultT->finalize();
		$stmtT->close();
		$db->exec("COMMIT");
		$db->close();
		unset($db);
		return $listToReturn;
	}

	function getExtendedStats($netId, $startTime, $endTime)
	{
		$sql = "Select * From QUEUEHISTORY";
		$db = new MyDB();
		$db->exec("BEGIN");
		$stmt;

		if($netId != null && $startTime != null && $endTime != null)
		{
			$endTime = $endTime + 3600000; //3600000 is one hour in miliseconds
			$sql = $sql . " WHERE netId = :netId and dequeueTime >= :startTime and dequeueTime <= :endTime order by dequeueTime asc";
			$stmt = $db->prepare($sql);
			$stmt->bindValue(":netId", $netId);
			$stmt->bindValue(":startTime", $startTime);
			$stmt->bindValue(":endTime", $endTime);

		}
		else if($netId != null)
		{
			$sql = $sql . " WHERE netId = :netId order by dequeueTime asc";
			$stmt = $db->prepare($sql);
			$stmt->bindValue(":netId", $netId);
		}
		else if($startTime != null && $endTime != null)
		{
			$endTime = $endTime + 3600000; //3600000 is one hour in miliseconds
			$sql = $sql . " WHERE dequeueTime >= :startTime and dequeueTime <= :endTime order by dequeueTime asc";
			$stmt = $db->prepare($sql);
			$stmt->bindValue(":startTime", $startTime);
			$stmt->bindValue(":endTime", $endTime);
		}
		else
		{
			$stmt = $db->prepare($sql);
		}

		$rslt = $stmt->execute();

		$studentData = array();

		while($row = $rslt->fetchArray(SQLITE3_ASSOC) )
		{
			array_push($studentData, array("id"=>$row["id"], "netId"=>$row["NetId"],"removedBy"=>$row["removedBy"], "enqueueTime"=>$row["enqueueTime"], "dequeueTime"=>$row["dequeueTime"], "question"=>$row["QUESTION"], "passOff"=>$row["PASSOFF"], "doneGettingHelp"=>$row["DoneGettingHelpTime"]));
		}
		$rslt->finalize();
		$stmt->close();

		//get netId => names to print names instead of netIds on client side
		$sql = "Select NetId, name from Students";
		$stmt = $db->prepare($sql);
		$rslt = $stmt->execute();

		$studentNames = array();
		while($row = $rslt->fetchArray(SQLITE3_ASSOC) )
		{
			$name = $row["name"] == null ? 'No name' : $row["name"];
			array_push($studentNames, array("netId"=>$row["NetId"],"name"=>$name));
		}
		$rslt->finalize();
		$stmt->close();

		$taData = getAllTAs($db);

		$db->exec("COMMIT");
		$db->close();
		unset($db);
		return array("studentData"=>$studentData, "tas"=>$taData, "studentNames"=>$studentNames);
	}

	function updateHistory($thisUser, $data)
	{
		if(verifyTA($thisUser))
		{
			$db = new MyDB();
			$db->exec("BEGIN;");

			$initRowSql = "Select netId, removedBy, PASSOFF from QUEUEHISTORY Where id = :id";

			$stmt = $db->prepare($initRowSql);
			$stmt->bindValue(":id", $data["id"]);
			$initRowRslt = $stmt->execute();

			$oldNetId = null;
			$oldRemovedBy = null;
			$oldPassOff = null;
			if($row = $initRowRslt->fetchArray(SQLITE3_ASSOC) )
			{
				$oldNetId = $row["NetId"];
				$oldRemovedBy = $row["removedBy"];
				$oldPassOff = $row["PASSOFF"];
			}

			$initRowRslt->finalize();
			$stmt->close();

			$sql = "UPDATE QUEUEHISTORY SET NetId = :netId, removedBy = :removedBy, enqueueTime = :enqueueTime, dequeueTime = :dequeueTime, QUESTION = :question, PASSOFF = :passOff, DoneGettingHelpTime = :doneGettingHelpTime WHERE id = :id";

			$stmt = $db->prepare($sql);
			$stmt->bindValue(":id", $data["id"]);
			$stmt->bindValue(":netId", $data["netId"]);
			$stmt->bindValue(":removedBy", $data["removedBy"]);
			$stmt->bindValue(":enqueueTime", $data["enqueueTime"]);
			$stmt->bindValue(":dequeueTime", $data["dequeueTime"]);
			$stmt->bindValue(":question", $data["question"]);
			$stmt->bindValue(":passOff", $data["passOff"]);
			$stmt->bindValue(":doneGettingHelpTime", $data["doneGettingHelpTime"]);
			$stmt->execute();

			$stmt->close();

			$updateOldValuesNetId = "Update Students set " . ($oldPassOff == "true" ? "passOffCounter" : "counter") . " = (Select count(*) from QueueHistory where netId = :oldNetId and passOff = '" . $oldPassOff . "') where netId = :oldNetId";
			$updateOldValuesRemovedBy = "Update " . (inTATable($oldRemovedBy) ? "TAs" : "Students") . " set " . ($oldPassOff == "true" ? "passOffCounter" : "counter") . " = (Select Count(*) from QueueHistory where removedBy = :oldRemovedBy and passOff ='" . $oldPassOff . "') Where netId = :oldRemovedBy";
			$updateNewValuesNetId = "Update Students set " . ($data["passOff"] == "true" ? "passOffCounter" : "counter") . " = (Select count(*) from QueueHistory where netId = :newNetId and passOff = '" . $data["passOff"] . "') where netId = :newNetId";
			$updateNewValuesRemovedBy = "Update " . (inTATable($data["removedBy"]) ? "TAs" : "Students") . " set " . ($data["passOff"] == "true" ? "passOffCounter" : "counter") . " = (Select Count(*) from QueueHistory where removedBy = :newRemovedBy and passOff ='" . $data["passOff"] . "') Where netId = :newRemovedBy";

			$stmtOldValuesNewId = $db->prepare($updateOldValuesNetId);
			$stmtOldValuesRemovedBy = $db->prepare($updateOldValuesRemovedBy);
			$stmtNewValuesNewId = $db->prepare($updateNewValuesNetId);
			$stmtNewValuesRemovedBy = $db->prepare($updateNewValuesRemovedBy);

			$stmtOldValuesNewId->bindValue(":oldNetId", $oldNetId);
			$stmtOldValuesRemovedBy->bindValue(":oldRemovedBy", $oldRemovedBy);
			$stmtNewValuesNewId->bindValue(":newNetId", $data["netId"]);
			$stmtNewValuesRemovedBy->bindValue("newRemovedBy", $data["removedBy"]);

			$stmtOldValuesNewId->execute();
			$stmtOldValuesRemovedBy->execute();
			$stmtNewValuesNewId->execute();
			$stmtNewValuesRemovedBy->execute();

			$stmtOldValuesNewId->close();
			$stmtOldValuesRemovedBy->close();
			$stmtNewValuesNewId->close();
			$stmtNewValuesRemovedBy->close();


			$db->exec("COMMIT");
			$db->close();
			unset($db);

			return array("status"=>"success");

		}
		else
			return array("status"=>"not authorized");
	}

	function getAllTAs($db)
	{
		$selfDB = false;
		if($db == null)
		{
			$selfDB = true;
			$db = new MyDB();
			$db->exec("BEGIN;");
		}

		$sql = "Select * from TAS";
		$stmt = $db->prepare($sql);
		$rslt = $stmt->execute();

		$taData = array();
		while($row = $rslt->fetchArray(SQLITE3_ASSOC) )
		{
			array_push($taData, array("netId"=>$row["NetId"],"name"=>$row["name"]));
		}
		$rslt->finalize();
		$stmt->close();

		if($selfDB)
		{
			$db->exec("COMMIT");
			$db->close();
			unset($db);
		}

		return $taData;
	}

	function toggleTAActive($userId)
	{
		$db = new MyDB();
		$db->exec("BEGIN");
		$sql = "UPDATE TAs SET Active = ~Active WHERE NetId = :netId";
		$deleteStmt = $db->prepare($sql);
		$deleteStmt->bindValue(':netId', $userId);
		$deleteStmt->execute();
		$deleteStmt->close();
		$db->exec("COMMIT");
		$db->close();
		unset($db);

		return getStats();
	}

	function addTA($userId, $name)
	{
		$db = new MyDB();
		$db->exec("BEGIN");
		$insertToTAs = "INSERT OR IGNORE INTO TAS (NetId, name, counter, active) VALUES (:netId, :nameIn, 0, 1)";
		$insertToTAsStmt = $db->prepare($insertToTAs);
		$insertToTAsStmt->bindValue(':netId',$userId);
		$insertToTAsStmt->bindValue(':nameIn',$name);
		$insertToTAsStmt->execute();

		$insertToTAsStmt->close();

		$db->exec("COMMIT");
		$db->close();
		unset($db);

		return getStats();
	}

	function addStudent($userId)
	{
		$db = new MyDB();

		$db->exec("BEGIN;");
		$insertToStudent = "INSERT OR IGNORE INTO STUDENTS (NetId, counter) VALUES (:netId, 0)";
		$insertToStudentStmt = $db->prepare($insertToStudent);
		$insertToStudentStmt->bindValue(':netId',$userId);
		$insertToStudentStmt->execute();
		$insertToStudentStmt->close();
		$db->exec("COMMIT;");
		$db->close();
		unset($db);
	}

	function getAverages($db)
	{
		$selfDB = false;
		if($db == null)
		{
			$selfDB = true;
			$db = new MyDB();
			$db->exec("BEGIN;");
		}
		$sql = "SELECT AVG(ENQUEUETIME) AS AVRG, COUNT(*) AS NUM FROM QUEUE WHERE QUEUENUM < (Select Min(QUEUENUM)+5 FROM QUEUE WHERE STARTEDGETTINGHELPTIME is null) AND STARTEDGETTINGHELPTIME is null";
		$sql2 = "SELECT COUNT(*) AS LEN FROM QUEUE WHERE STARTEDGETTINGHELPTIME is null";
		$sql2_5 = "SELECT COUNT(*) AS LEN FROM QUEUE WHERE STARTEDGETTINGHELPTIME > 1";

		//get the number of people who got in line and the number of people who got helped in the lastXMin
		$sql3 = "SELECT COUNT(*) AS EnqueueCount FROM QUEUE WHERE ENQUEUETIME >= (((SELECT strftime('%s', 'now')) - (Select value * 60 from settings where name = 'lastXMin')) * 1000)";
		$sql4 = "SELECT COUNT(*) AS DequeueCount FROM QUEUEHISTORY WHERE DEQUEUETIME >= (((SELECT strftime('%s', 'now')) - (Select value * 60 from settings where name = 'lastXMin')) * 1000)";
		$sql5 = "SELECT COUNT(*) AS EnqueueCount FROM QUEUEHISTORY WHERE ENQUEUETIME >= (((SELECT strftime('%s', 'now')) - (Select value * 60 from settings where name = 'lastXMin')) * 1000)";


		$stmt = $db->prepare($sql);
		$stmt2 = $db->prepare($sql2);
		$stmt2_5 = $db->prepare($sql2_5);
		$stmt3 = $db->prepare($sql3);
		$stmt4 = $db->prepare($sql4);
		$stmt5 = $db->prepare($sql5);

		$result = $stmt->execute();
		$result2 = $stmt2->execute();
		$result2_5 = $stmt2_5->execute();
		$result3 = $stmt3->execute();
		$result4 = $stmt4->execute();
		$result5 = $stmt5->execute();

		$enqueueCount = 0;
		$dequeueCount = 0;
		$avg = 0;
		$avgLen = 0;
		$queueLen = 0;
		$currentlyBeingHelped = 0;

		if($row = $result->fetchArray(SQLITE3_ASSOC) )
		{
			$avg = $row["AVRG"] == null ? 0 : $row["AVRG"];
			$avgLen = $row["NUM"] == 0 ? 0 : $row["NUM"];
		}

		if($row = $result2->fetchArray(SQLITE3_ASSOC) )
		{
			$queueLen = $row["LEN"];
		}

		if($row = $result2_5->fetchArray(SQLITE3_ASSOC) )
		{
			$currentlyBeingHelped = $row["LEN"];
		}

		if($row = $result3->fetchArray(SQLITE3_ASSOC) )
		{
			$enqueueCount = $enqueueCount + $row["EnqueueCount"];
		}

		if($row = $result4->fetchArray(SQLITE3_ASSOC) )
		{
			$dequeueCount = $dequeueCount + $row["DequeueCount"];
		}

		if($row = $result5->fetchArray(SQLITE3_ASSOC) )
		{
			$enqueueCount = $enqueueCount + $row["EnqueueCount"];
		}

		$toReturn = array("avg"=>$avg, "avgLen"=>$avgLen, "queueLen"=>$queueLen, "enqueueInLastXMin"=>$enqueueCount, "dequeueInLastXMin"=>$dequeueCount, "currentlyBeingHelpedCount"=>$currentlyBeingHelped);

		$result->finalize();
		$result2->finalize();
		$result2_5->finalize();
		$result3->finalize();
		$result4->finalize();
		$result5->finalize();

		$stmt->close();
		$stmt2->close();
		$stmt2_5->close();
		$stmt3->close();
		$stmt4->close();
		$stmt5->close();

		if($selfDB)
		{
			$db->exec("COMMIT");
			$db->close();
			unset($db);
		}
		return $toReturn;
	}

	function getNameStatus($userId)
	{
		$db = new MyDB();
		$db->exec("BEGIN;");
		$sql;
		if(verifyTA($userId))
		{
			$sql = "SELECT name FROM TAS WHERE NetId = :netId";
		}
		else
		{
			$sql = "SELECT name FROM Students WHERE NetId = :netId";
		}

		$stmt = $db->prepare($sql);
		$stmt->bindValue(":netId",$userId);
		$result = $stmt->execute();
		$toReturn;

		if ($row = $result->fetchArray(SQLITE3_ASSOC) )
		{
			$toReturn = array('status'=>'success', 'name'=>$row['name']);
		}
		else
		{
			$toReturn = array('status'=>'error', 'name'=> null);
		}
		$result->finalize();
		$stmt->close();

		$db->exec("COMMIT");
		$db->close();
		unset($db);
		return $toReturn;
	}

	function updateName($userId, $name, $netId)
	{
		if(strlen($name) <= 0)
			return array("status"=>"noname");
		if (preg_match('/[^A-Za-z ]+/', $name))
  			return array("status"=>"notValidName");

		if($netId == null)
			$netId = $userId;

		$db = new MyDB();
		$db->exec("BEGIN");

		$sql;
		if(verifyTA($userId))
		{
			//The way insert or replace works would wipe out any data in that row unless we specifically add it back in, hence the long gross looking SQL statement
			$sql = "INSERT OR Replace INTO TAS (NetId, name, Counter, PassOffCounter, Active) VALUES (:netId, :name, (SELECT Counter FROM TAs WHERE netId = :netId), (SELECT PassOffCounter FROM TAs WHERE netId = :netId),	(SELECT Active FROM TAs WHERE netId = :netId))";
		}
		else
		{
			$sql = "INSERT OR REPLACE INTO Students (NetId, name) VALUES (:netId, :name)";
		}

		$stmt = $db->prepare($sql);
		$stmt->bindValue(":netId",$netId);
		$stmt->bindValue(":name", $name);
		$result = $stmt->execute();

		$result->finalize();
		$stmt->close();

		$db->exec("COMMIT");
		$db->close();
		unset($db);
		if(verifyTA($userId))
			return array("status"=>"success", "stats"=>getStats());
		else
			return array("status"=>"success");
	}

	function getSettings()
	{
		$db = new MyDB();
		$db->exec("BEGIN;");
		$sql = "SELECT * FROM SETTINGS";
		$stmt = $db->prepare($sql);
		$result = $stmt->execute();

		$list = array();

		while ($row = $result->fetchArray(SQLITE3_ASSOC) )
		{
			array_push($list, array($row["name"]=>$row["value"]));
		}
		$result->finalize();

		$stmt->close();
		$sql = "SELECT * FROM SETTINGS WHERE name = 'queueActive'";
		$stmt = $db->prepare($sql);
		$result = $stmt->execute();
		$active = $result->fetchArray(SQLITE3_ASSOC);
		$result->finalize();
		$stmt->close();
		// $sql = "SELECT * FROM SETTINGS WHERE name = 'lastTime'";
		// $stmt = $db->prepare($sql);
		// $result = $stmt->execute();
		// $disableTime = $result->fetchArray(SQLITE3_ASSOC);
		// $result->finalize();
		// $stmt->close();
		// $sql = "SELECT * FROM SETTINGS WHERE name = 'autoDisable'";
		// $stmt = $db->prepare($sql);
		// $result = $stmt->execute();
		// $autoDisable = $result->fetchArray(SQLITE3_ASSOC);
		// $result->finalize();
		// $stmt->close();

		// if ($autoDisable["value"] == "true") {
		// 	$curTime = date('Gi');
		// 		if ($curTime >= $disableTime["value"]) {
		// 			$sql = "UPDATE SETTINGS SET value = 'false' WHERE name = 'queueActive'";
		// 			$stmt = $db->prepare($sql);
		// 			$result = $stmt->execute();
		//
		// 			$result->finalize();
		// 			$stmt->close();
		// 		}
		// 	// }
		// }

		$db->exec("COMMIT");
		$db->close();
		unset($db);
		return $list;
	}

	function getSetting($settingName)
	{
		$db = new MyDB();
		$db->exec("BEGIN;");
		$sql = "SELECT * FROM SETTINGS WHERE name = :nameIn";
		$stmt = $db->prepare($sql);
		$stmt->bindValue(":nameIn", $settingName);
		$result = $stmt->execute();

		$list = array($settingName=>null);

		if ($row = $result->fetchArray(SQLITE3_ASSOC) )
		{
			$list = array($row["name"]=>$row["value"]);
		}

		$result->finalize();
		$stmt->close();

		$db->exec("COMMIT");
		$db->close();
		unset($db);
		return $list;
	}

	function changeSetting($keyIn, $valueIn)
	{
			$db = new MyDB();
			$db->exec("BEGIN");
			$sql = "UPDATE SETTINGS SET value = :valueIn WHERE name = :keyIn";
			$stmt = $db->prepare($sql);
			$stmt->bindValue(":valueIn", $valueIn);
			$stmt->bindValue(":keyIn", $keyIn);

			$result = $stmt->execute();

			$result->finalize();
			$stmt->close();
			$db->exec("COMMIT");
			$db->close();
			unset($db);
		//return array($key=>$value);
		return getSettings();
	}

	function isQueueActive()
	{
		$temp = getSetting("queueActive");
		return $temp["queueActive"] == 'true' ? true : false;
	}

	function getCourseTitle()
	{
		$temp = getSetting("courseTitle");
		return $temp["courseTitle"];
	}

	function getStudentPollTime()
	{
		$temp = getSetting("studentPollTime");
		return $temp["studentPollTime"];
	}

	function getTAPollTime()
	{
		$temp = getSetting("taPollTime");
		return $temp["taPollTime"];
	}

	function getNotifyThreshold()
	{
		$temp = getSetting("notifyThreshold");
		return $temp["notifyThreshold"];
	}


	//$db = new MyDB();
	//echo var_dump (getSettings());
	//echo var_dump(isQueueActive());
   //$db = new MyDB();
   //if(!$db){
   //   echo $db->lastErrorMsg();
   //} else {
    //  echo "Opened database successfully\n";
	//	echo json_encode(enqueue("bmcgary"));

   //}

?>
