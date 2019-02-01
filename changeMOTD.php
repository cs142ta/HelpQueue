<?php

	if(isset($_POST["motd"]) && isset($_POST["motdLink"]))
	{
		require_once 'CAS-1.3.4/CAS.php';

		// Initialize phpCAS
		phpCAS::client(CAS_VERSION_2_0,'cas.byu.edu',443,'cas');
		$auth = phpCAS::checkAuthentication();
		require_once 'DBConnect.php';
		if(verifyTA(phpCAS::getUser()))
		{
      setMOTD($_POST["motd"]);
      setMOTDLink($_POST["motdLink"]);
			echo json_encode(getSettings());
		}
		else
		{
			echo json_encode(array("status"=>"error", "message"=>"not authorized"));
		}
	}
	else
	{
		echo json_encode(array("status"=>"error", "message"=>"not authorized"));
	}

?>
