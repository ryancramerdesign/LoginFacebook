<?php namespace ProcessWire;

/**
 * Template file for LoginFacebook module
 *
 * Place this file in /site/templates/login-facebook.php and modify as needed
 *
 */

/** @var User $user */
/** @var Config $config */
/** @var Modules $modules */
/** @var Sanitizer $sanitizer */
/** @var LoginFacebook $loginFacebook */

$facebook = $modules->get('LoginFacebook');
if(!$facebook) throw new WireException('LoginFacebook module is not available');

try {
	$facebook->execute();
	// the following code applies only if no redirect success URL configured with module
	if($facebook->isLoggedIn()) {
		echo "<h2>Welcome $facebook->name</h2>";	
	}
} catch(WireException $e) {
	if(wireClassName($e) != 'LoginFacebookException') throw $e;
	$errorMessage = $sanitizer->entities($e->getMessage());
	// Replace with your own error output if desired.
	// Example: echo <p class='error'>$errorMessage</p>
	echo str_replace(
		array('{message}', '{why}'),
		array($errorMessage, 'Facebook Login'),
		$config->fatalErrorHTML
	);
}

