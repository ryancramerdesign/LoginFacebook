<?php namespace ProcessWire;

/**
 * Class LoginFacebookInstaller
 * 
 */

class LoginFacebookInstaller extends WireData {

	/**
	 * @var LoginFacebook
	 * 
	 */
	protected $module;

	/**
	 * Construct
	 *
	 * @param LoginFacebook $module
	 * 
	 */
	public function __construct(LoginFacebook $module) {
		$this->module = $module;
	}
	
	/**
	 * Perform first time installation
	 *
	 * @return bool
	 *
	 */
	public function install() {
		return $this->execute(1);
	}

	/**
	 * Uninstall
	 *
	 * @return bool
	 *
	 */
	public function uninstall() {
		return $this->execute(-1);
	}

	/**
	 * Check/update installation to make sure everything needed is there
	 *
	 * @return bool
	 *
	 */
	public function check() {
		return $this->execute(0);
	}

	/**
	 * Generate a failure notice
	 * 
	 * @param string $msg
	 * @return bool
	 * 
	 */
	protected function fail($msg) {
		$this->error("Failed to install because: $msg"); 
		return false;
	}
	
	/**
	 * Module install, update, uninstall
	 *
	 * @param int $mode Specify 1 to install, 0 to update/check, or -1 to uninstall
	 * @throws WireException
	 * @return bool
	 *
	 */
	protected function execute($mode) {

		$paths = $this->wire('config')->paths;
		$page = $this->wire('pages')->get('/' . $this->module->pageName);
		$template = $this->wire('templates')->get($this->module->templateName);
		$templateBasename = $this->module->templateName . '.php';
		$templatePathname = $paths->templates . $templateBasename;
		$fieldgroup = $this->fieldgroups->get($this->module->templateName);
		$userFieldgroup = $this->wire('fieldgroups')->get('user');
		$field = $this->wire('fields')->get($this->module->fieldName);
		$role = $this->roles->get($this->module->roleName);
		
		if($mode === -1) {
			// uninstall
			if($field) {
				$userFieldgroup->remove($field);
				$userFieldgroup->save();
				$this->message("Removed field '$field->name' from user template/fieldgroup");
			}
		
			if($role && $role->id) {
				$users = $this->wire('users')->find("roles=$role, include=all");

				foreach($users as $u) {
					/** @var User $u */
					$u->of(false);
					$u->removeRole($role);
					$u->save('roles');
				}

				$this->message("Removed role '$role->name' from " . $users->count() . " user(s)");

				$this->wire('roles')->delete($role);
				$this->message("Deleted role - $role->name");
			}
		
			if($page && $page->id) {
				$this->wire('pages')->delete($page);
				$this->message("Deleted page - $page->path");
			}
		
			if($template) {
				$this->wire('templates')->delete($template);
				$this->message("Deleted template - $template->name");
			}
		
			if($fieldgroup) {
				$this->wire('fieldgroups')->delete($fieldgroup);
				$this->message("Deleted fieldgroup - $fieldgroup->name");
			}
		
			if($field) {
				$this->wire('fields')->delete($field);
				$this->message("Deleted field - $field->name");
			}
			
			return true;
			
		} else if($mode === 1) {
			// first install: check if resources already exist and fail if they do
			if($role && $role->id) return $this->fail("Role '$role->name' already exists");
			if($fieldgroup) return $this->fail("Fieldgroup '$fieldgroup->name' already exists");
			if($template) return $this->fail("Template '$template->name' already exists");
			if($page->id) return $this->fail("Page '$page->path' already exists"); 
			if(file_exists($templatePathname)) $this->warning("Template file '$templateBasename' already exists"); 
			
		} else {
			// regular update or check installation
		}

		// Role
		if(!$role || !$role->id) {
			$role = $this->wire('roles')->add($this->module->roleName);
			$this->message($this->_('Added role') . " - $role->name");
		}

		// Fieldgroup
		if(!$fieldgroup) {
			$fieldgroup = new Fieldgroup();
			$fieldgroup->name = $this->module->templateName;
			$title = $this->wire('fields')->get('title');
			if($title) $fieldgroup->add($title);
			$fieldgroup->save();
			$this->message($this->_('Added fieldgroup') . " - $fieldgroup->name");
		}

		// Template
		if($template && $template->id) {
			if($template->fieldgroup->id != $fieldgroup->id) {
				$template->fieldgroup = $fieldgroup;
				$template->save();
				$this->message($this->_('Added fieldgroup to template') . " - $fieldgroup->name");
			}
		} else {
			$template = new Template();
			$template->name = $this->module->templateName;
			$template->fieldgroup = $fieldgroup;
			$template->noParents = -1; // only allow 1 to exist
			$template->save();
			$this->message($this->_('Created template') . " - $template->name");
		} 

		// Page
		if(!$page->id) {
			$page = new Page();
			$page->template = $template;
			$page->parent = '/';
			$page->name = $this->module->pageName;
			$page->title = 'Login for Facebook';
			$page->addStatus(Page::statusHidden);
			$page->save();
			$this->message($this->_('Created page') . " - $page->path");
		}

		// Template file
		if(!file_exists($templatePathname)) {
			$src = dirname(__FILE__) . '/' . $templateBasename;
			$dst = $templatePathname;

			if(@copy($src, $dst)) {
				$this->message($this->_('Installed template file') . " - $templateBasename => /site/templates/$templateBasename");
				wireChmod($templatePathname); 
			} else {
				$this->warning($this->_('Unable to install template file (dir not writable?)'));
				$from = str_replace($paths->root, '/', $src);
				$to = str_replace($paths->root, '/', $dst);
				$this->warning(sprintf($this->_('Please copy %1$s to %2$s'), $from, $to));
			}
		}

		// Field (facebook ID)
		if(!$field) {
			$field = new Field();
			$field->type = $this->wire('modules')->get('FieldtypeText');
			$field->name = $this->module->fieldName;
			$field->label = 'Facebook ID';
			$field->description = 'Facebook ID as populated by LoginFacebook module.';
			$field->set('inputfield', 'InputfieldText');
			$field->set('collapsed', Inputfield::collapsedYes);
			$field->save();
			$this->message($this->_('Added field') . " - $field->name"); 
		}

		// User field(s)
		if(!$userFieldgroup->hasField($field)) {
			// Add the Facebook ID field to the user fieldgroup
			$userFieldgroup->add($field);
			$userFieldgroup->save();
			$this->message($this->_('Added field to user template') . " - $field->name");
		}
		
		return true;
	}

}