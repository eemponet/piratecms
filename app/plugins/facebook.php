<?php

namespace App\Plugins;

define('FACEBOOK_SDK_V4_SRC_DIR', '../libs/facebook-php-sdk-v4/src/Facebook/');
require __DIR__ . '../libs/facebook-php-sdk-v4/autoload.php';

// use Facebook\FacebookSession;
use \Facebook\FacebookRequest;
use \Facebook\GraphUser;
use \Facebook\FacebookRequestException;


class Facebook
{
	function getWall($username)
	{
		
		
		FacebookSession::setDefaultApplication('678478232256721','bbc8affa4208cff63b766341eb807c18');
		
		// Use one of the helper classes to get a FacebookSession object.
		//   FacebookRedirectLoginHelper
		//   FacebookCanvasLoginHelper
		//   FacebookJavaScriptLoginHelper
		// or create a FacebookSession with a valid access token:
		// $session = new FacebookSession('access-token-here');
		
		// Get the GraphUser object for the current user:
		
		try {
			$me = (new FacebookRequest(
				$session, 'GET', '/me'
				))->execute()->getGraphObject(GraphUser::className());
				echo $me->getName();
		} catch (FacebookRequestException $e) {
			// The Graph API returned an error
		} catch (\Exception $e) {
			// Some other error occurred
		}
	}


}