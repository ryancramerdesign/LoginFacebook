<?php namespace ProcessWire;

/**
 * Configuration helper class for LoginFacebook module
 * 
 * CONFIG PROPERTIES (mirrored from LoginFacebook module): 
 * 
 * @property string $appID
 * @property string $appSecret
 * @property bool|int $createUsers
 * @property int $afterLoginPageID
 * @property int $errorLoginPageID
 * @property array $requestPermissions
 * @property array $requestFields
 * @property int $commonUserName
 * @property string $userNameFormat
 * @property array $addRoles
 * @property array $disallowRoles
 * @property array $disallowPermissions
 *
 * @property string $pageName
 * @property string $templateName
 * @property string $fieldName
 * @property string $roleName
 * 
 */

class LoginFacebookConfigure extends WireData {
	
	/**
	 * Permissions that can be requested from Facebook
	 *
	 * @var array
	 *
	 */
	protected $facebookPermissions = array(
		'public_profile',
		'user_friends',
		'email',
		'user_about_me',
		'user_actions.books',
		'user_actions.fitness',
		'user_actions.music',
		'user_actions.news',
		'user_actions.video',
		'user_birthday',
		'user_education_history',
		'user_events',
		'user_games_activity',
		'user_hometown',
		'user_likes',
		'user_location',
		'user_managed_groups',
		'user_photos',
		'user_posts',
		'user_relationships',
		'user_relationship_details',
		'user_religion_politics',
		'user_tagged_places',
		'user_videos',
		'user_website',
		'user_work_history',
		'read_custom_friendlists',
		'read_insights',
		'read_audience_network_insights',
		'read_page_mailboxes',
		'manage_pages',
		'publish_pages',
		'publish_actions',
		'rsvp_event',
		'pages_show_list',
		'pages_manage_cta',
		'pages_manage_instant_articles',
		'ads_read',
		'ads_management',
		'business_management',
		'pages_messaging',
		'pages_messaging_subscriptions',
		'pages_messaging_payments',
		'pages_messaging_phone_number',
	);

	
	protected $module;
	
	public function __construct(LoginFacebook $module) {
		$this->module = $module;
	}
	
	public function get($key) {
		return $this->module->get($key);
	}
	
	public function getInputfields(InputfieldWrapper $inputfields) {

		$modules = $this->wire('modules');
		
		// OAuth fieldset -----------------------------------------------------------------------

		$fieldset = $modules->get('InputfieldFieldset');
		$fieldset->label = $this->_('OAuth configuration for Facebook');
		$fieldset->icon = 'key';
		if($this->appID && $this->appSecret) $fieldset->collapsed = Inputfield::collapsedYes;
		$inputfields->add($fieldset);

		$f = $modules->get('InputfieldText');
		$f->attr('name', 'appID');
		$f->attr('value', $this->appID);
		$f->label = $this->_('Facebook App ID');
		$f->description = $this->_('App ID for your website, which you can obtain from [here](https://developers.facebook.com/apps/).');
		$fieldset->add($f);

		$f = $modules->get('InputfieldText');
		$f->attr('name', 'appSecret');
		$f->attr('value', $this->appSecret);
		$f->label = $this->_('Facebook App Secret');
		$f->description = $this->_('App Secret for your website. Provided by Facebook after you have created your app.');
		$fieldset->add($f);
		
		// Users, roles and access fieldset ----------------------------------------------------

		$fieldset = $modules->get('InputfieldFieldset');
		$fieldset->label = $this->_('Users, roles and access');
		$inputfields->add($fieldset);

		$f = $modules->get('InputfieldRadios');
		$f->attr('name', 'createUsers');
		$f->label = $this->_('How should Facebook user(s) be managed?');
		$f->icon = 'user';
		$f->addOption(1, $this->_('Create separate ProcessWire users for each Facebook user'));
		$f->addOption(0, $this->_('Make all Facebook users point to the same ProcessWire user'), array('disabled' => 'disabled'));
		$f->attr('value', $this->createUsers);
		$fieldset->add($f);

		$f = $modules->get('InputfieldName');
		$f->attr('name', 'commonUserName');
		$f->label = $this->_('Enter the user name to be shared for all Facebook logins');
		$f->attr('value', $this->commonUserName);
		$f->showIf = 'createUsers=0';
		$fieldset->add($f);

		$f = $modules->get('InputfieldRadios');
		$f->attr('name', 'userNameFormat');
		$f->label = $this->_('Name format for newly created users');
		$f->notes = $this->_('Note that if a duplicate user name occurs, numbers append to it until it is unique.');
		$f->addOption('full', $this->_('First name + last name'));
		$f->addOption('rfull', $this->_('Last name + first name'));
		$f->addOption('first', $this->_('First name only'));
		$f->addOption('last', $this->_('Last name only'));
		$f->attr('value', $this->userNameFormat);
		$f->showIf = 'createUsers=1';
		$fieldset->add($f);

		$f = $modules->get('InputfieldAsmSelect');
		$f->attr('name', 'addRoles');
		$f->label = $this->_('Roles to add to users that have Facebook login');
		$addRoles = $this->addRoles;
		foreach($this->wire('roles') as $role) {
			if($role->name == 'superuser' || $role->name == 'guest') continue;
			$label = $role->name;
			if($role->name == $this->roleName) {
				if(!in_array($role->name, $addRoles)) $addRoles[] = $role->name;
				$label .= ' ' . $this->_('(required)');
			}
			$f->addOption($role->name, $label);
		}
		$f->collapsed = Inputfield::collapsedYes;
		$f->attr('value', $addRoles);
		$fieldset->add($f);

		$f = $modules->get('InputfieldAsmSelect');
		$f->attr('name', 'disallowRoles');
		$f->label = $this->_('Disallow Facebook login for roles');
		$f->description = $this->_('Prevent users with any selected roles from logging into ProcessWire with Facebook only.');
		$f->description .= ' ' . $this->_('Facebook data can still be used, but users having these roles can not login to their ProcessWire account from Facebook.');
		$f->notes = $this->_('We recommend preventing roles with admin access from using Facebook login, for added security.');
		foreach($this->wire('roles') as $role) {
			$f->addOption($role->name);
		}
		$f->attr('value', $this->disallowRoles);
		$f->collapsed = Inputfield::collapsedYes;
		$fieldset->add($f);

		$f = $modules->get('InputfieldAsmSelect');
		$f->attr('name', 'disallowPermissions');
		$f->label = $this->_('Disallow facebook login for users having permissions');
		$f->description = $this->_('Prevent users with any selected permissions from logging into ProcessWire with Facebook only.');
		$f->description .= ' ' . $this->_('Facebook data can still be used, but users having these permissions cannot login to their ProcessWire account from Facebook.');
		$f->notes = $this->_('Selecting page-edit permission here is recommended, as it is the prerequisite to most admin related permissions.');
		foreach($this->wire('permissions') as $permission) {
			$f->addOption($permission->name);
		}
		$f->collapsed = Inputfield::collapsedYes;
		$f->attr('value', $this->disallowPermissions);
		$fieldset->add($f);

		// Flow control and Facebook -----------------------------------------------------------------------
		
		$fieldset = $modules->get('InputfieldFieldset');
		$fieldset->label = $this->_('Flow control and Facebook data'); 
		$inputfields->add($fieldset);

		$f = $modules->get('InputfieldPageListSelect');
		$f->attr('name', 'afterLoginPageID');
		$f->attr('value', $this->afterLoginPageID);
		$f->label = $this->_('Page where user is redirected after successful login');
		$f->description = $this->_('This can be any public page or any access controlled page viewable by the “login-facebook” role.');
		$f->description .= ' ' . $this->_('If not specified, user will remain on the /login-facebook/ page after login.'); 
		$fieldset->add($f);

		$f = $modules->get('InputfieldPageListSelect');
		$f->attr('name', 'errorLoginPageID');
		$f->attr('value', $this->errorLoginPageID);
		$f->label = $this->_('Page to redirect to on failed login');
		$f->description = $this->_('When user does not approve the login from Facebook, they are redirected to this page.'); 
		$f->description .= ' ' . $this->_('If not specified, a LoginFacebookException will be thrown on the /login-facebook/ page.');
		$fieldset->add($f);

		$f = $modules->get('InputfieldAsmSelect');
		$f->attr('name', 'requestPermissions');
		$f->label = $this->_('Permissions to request from Facebook');
		$f->description = sprintf($this->_('See the Facebook [permissions reference](%s) for details.'),
			'https://developers.facebook.com/docs/facebook-login/permissions');
		foreach($this->facebookPermissions as $name) {
			$f->addOption($name);
		}
		$f->attr('value', $this->requestPermissions);
		$fieldset->add($f);

		$f = $modules->get('InputfieldAsmSelect');
		$f->attr('name', 'requestFields');
		$f->label = $this->_('Fields to request from Facebook');
		$f->description = sprintf($this->_('See the Facebook [user reference](%s) for field descriptions.'),
			'https://developers.facebook.com/docs/graph-api/reference/user');
		$f->description .= ' ' . $this->_('Some fields require specific permissions above. For instance, the “email” field requires “email” permission.');
		$f->notes = $this->_('You can access the value of any of these Facebook fields at runtime like below.') . " " .
			$this->_('Replace “field_name” with any Facebook field name.') . "\n\n" .
			'`$facebook = $modules->get("' . $this->className() . '");`' . "\n" .
			'`$value = $facebook->field_name;`' . "\n\n" .
			$this->_('If you attempt to access a Facebook field when user is not logged in, it will initiate a Facebook login.');
		foreach($this->module->getFacebookFields() as $name) {
			$f->addOption($name);
		}
		$f->attr('value', $this->requestFields);
		$fieldset->add($f);
		
		// Field mirroring fieldset -----------------------------------------------------------------------

		$fieldset = $modules->get('InputfieldFieldset');
		$fieldset->label = $this->_('Mirror Facebook fields to ProcessWire fields');
		$fieldset->icon = 'exchange';
		$fieldset->description = $this->_('This enables you to automatically update ProcessWire fields for the current user with values from Facebook fields.');
		$fieldset->description .= ' ' . $this->_('This applies to Facebook text-based fields and saves them for the current user (which uses the “user” template).');
		$fieldset->description .= ' ' . $this->_('If you just selected new “fields to request” above, Save and come back here to configure those fields.');
		$fieldset->description .= ' ' . sprintf(
				$this->_('If you need to add more fields to your “user” template, you may [edit the user template here](%s).'),
				$this->wire('config')->urls->admin . 'setup/template/edit?id=' . $this->wire('config')->userTemplateID
			);

		$fieldset->showIf = 'createUsers=1';
		$fieldset->collapsed = Inputfield::collapsedYes;
		$inputfields->add($fieldset);

		$userFields = array();

		foreach($this->wire('config')->userTemplateIDs as $templateID) {
			$userTemplate = $this->wire('templates')->get($templateID);
			foreach($userTemplate->fieldgroup as $field) {
				if($field->name == 'roles' || $field->name == 'pass' || $field->name == $this->fieldName) continue;
				$userFields[$field->name] = $field;
			}
		}

		ksort($userFields);

		foreach($this->requestFields as $name) {
			if($name == 'id') continue;
			$f = $modules->get('InputfieldSelect');
			$f->attr('name', "fb_$name");
			//$f->label = sprintf($this->_('Facebook field: %s'), $name);
			$f->label = $name;
			$f->icon = 'facebook-square';
			$f->description = sprintf($this->_('Facebook field “%s” populates which ProcessWire user field?'), $name);
			foreach($userFields as $field) {
				$f->addOption("$field->id:$field->name", "$field->name ($field->label)");
			}
			$f->attr('value', $this->get("fb_$name"));
			$f->collapsed = Inputfield::collapsedBlank;
			$fieldset->add($f);
		}
		
		return $inputfields;
	}
}
