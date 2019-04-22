<?php
header("Access-Control-Allow-Origin: *");
// Load the settings from the central config file
//require_once 'config.php';
// Load the CAS lib
require_once 'CAS-1.3.4/CAS.php';
require_once 'DBConnect.php';

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
$isTA = $auth ? verifyTA(phpCAS::getUser()) : false;
if ($auth)
{
  if ($isTA)
  {
    require "TAs.php";
  }
  else if (!$isTA)
  {
    require "students.php";
  }
}
else
{
?>
  <script language="javascript">
    	window.location.href = "?login="
	</script>
<?php
}
?>
