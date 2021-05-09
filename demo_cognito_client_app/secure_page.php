<?php
namespace AWSCognitoApp;
require_once('vendor/autoload.php');
use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
?>


<!DOCTYPE html>
<html>
<head>
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;400&display=swap" rel="stylesheet">
	<style>
		body{
			font-family: 'Roboto', sans-serif;
		}
	</style>
</head>
<body>
<h1>DEMO PHP COGNITO CLIENT APPLICATION</h1>


<?php
if(!isset($_GET["access_token"]))
{
	
?>
<script>
	var url_str = window.location.href;
	//On successful authentication, AWS Cognito will redirect to Call-back URL and pass the access_token as a request parameter. 
	//If you notice the URL, a “#” symbol is used to separate the query parameters instead of the “?” symbol. 
	//So we need to replace the “#” with “?” in the URL and call the page again.
	
	if(url_str.includes("#")){
		var url_str_hash_replaced = url_str.replace("#", "?");
		window.location.href = url_str_hash_replaced;
	}
	
</script>

<?php
}
else{
?>

<?php

$access_token = $_GET["access_token"];

$region = '<AWS_REGION>';
$version = 'latest';

//Authenticate with AWS Acess Key and Secret
$client = new CognitoIdentityProviderClient([
    'version' => $version,
    'region' => $region,
	'credentials' => [
                    'key'    => '<AWS_ACCESS_KEY>',
                    'secret' => '<AWS_SECRET>',
                ],
]);

try {
	//Get the User data by passing the access token received from Cognito
    $result = $client->getUser([
        'AccessToken' => $access_token,
    ]);
	
	//print_r($result);
	
	$user_email = "";
	$user_phone_number = "";
		
	//Iterate all the user attributes and get email and phone number
	$userAttributesArray = $result["UserAttributes"];
	foreach ($userAttributesArray as $key => $val) {
		if($val["Name"] == "email"){
			$user_email = $val["Value"];
		}
		if($val["Name"] == "phone_number"){
			$user_phone_number = $val["Value"];
		}
	}	
	echo '<h2>Logged-In User Attributes</h2>';
	echo '<p>User E-Mail : ' . $user_email . '</p>';
	echo '<p>User Phone Number : ' . $user_phone_number . '</p>';
	echo "<a href='secure_page.php?logout=true&access_token=$access_token'>SIGN OUT</a>";
	
	if(isset($_GET["logout"]) && $_GET["logout"] == 'true'){
		//This will invalidate the access token
		$result = $client->globalSignOut([
			'AccessToken' => $access_token,
		]);
		
		header("Location: <COGNITO_HOSTED_UI_URL>");
		
	}
	
	
} catch (\Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException $e) {
    echo 'FAILED TO VALIDATE THE ACCESS TOKEN. ERROR = ' . $e->getMessage();
	}
catch (\Aws\Exception\CredentialsException $e) {
    echo 'FAILED TO AUTHENTICATE AWS KEY AND SECRET. ERROR = ' . $e->getMessage();
	}

}
?>

</body>
</html>
