<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

defined('_JEXEC') or die;

if(!defined('DS')){ define('DS',DIRECTORY_SEPARATOR); }

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\GenericDataException;

jimport('joomla.filesystem.folder');

class com_faqbookproInstallerScript
{
	/*
	 * $parent is the class calling this method.
	 * $type is the type of change (install, update or discover_install, not uninstall).
	 * preflight runs before anything else and while the extracted files are in the uploaded temp folder.
	 * If preflight returns false, Joomla will abort the update and undo everything already done.
	 */
	function preflight($type, $parent)
	{
		if (is_object($this->getOldVersion()))
		{
			// Get old version
			$this->old_version = $this->getOldVersion()->version;

			// Get new version
			$this->new_version = $parent->manifest->version;

			// Abort if old version is older than 3.9.3.1
			if (isset($this->old_version) && $this->old_version && version_compare($this->old_version, '3.9.3.1', '<'))
			{
				throw new GenericDataException('Cannot install version <strong>'.$this->new_version.'</strong> over version <strong>'.$this->old_version.'</strong>. Please update to version 3.9.3.1 first.', 500);
				return false;
			}

			// Abort if old version is 4.0.0 up to 4.0.4 (alpha)
			if (isset($this->old_version) && $this->old_version && version_compare($this->old_version, '4.0.0', '>=') && version_compare($this->old_version, '4.0.5', '<'))
			{
				throw new GenericDataException('Cannot install version <strong>'.$this->new_version.'</strong> over version <strong>'.$this->old_version.' alpha</strong>. Please uninstall version <strong>'.$this->old_version.' alpha</strong> first.', 500);
				return false;
			}

			// Run update script if old release is older than 4.0.0
			if (isset($this->old_version) && $this->old_version && version_compare($this->old_version, '4.0.0', '<'))
			{
				self::update405($parent);
			}
		}
	}

	/*
	 * $parent is the class calling this method.
	 * update runs if old version is older than 4.0.0.
	 */
	function update405($parent)
	{
		// Delete folder admin/controllers
		$admin_controllers = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_faqbookpro'.DS.'controllers';
		if (\JFolder::exists($admin_controllers)) {
			\JFolder::delete($admin_controllers);
		};

		// Delete folder admin/helpers/html
		$admin_helpers_html = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_faqbookpro'.DS.'helpers'.DS.'html';
		if (\JFolder::exists($admin_helpers_html)) {
			\JFolder::delete($admin_helpers_html);
		};

		// Delete file admin/helpers/faqbookpro.php
		$admin_helpers_faqbookpro_php = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_faqbookpro'.DS.'helpers'.DS.'faqbookpro.php';
		if (file_exists($admin_helpers_faqbookpro_php)) {
			\JFile::delete($admin_helpers_faqbookpro_php);
		};

		// Delete folder admin/models
		$admin_models = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_faqbookpro'.DS.'models';
		if (\JFolder::exists($admin_models)) {
			\JFolder::delete($admin_models);
		};

		// Delete folder admin/tables
		$admin_tables = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_faqbookpro'.DS.'tables';
		if (\JFolder::exists($admin_tables)) {
			\JFolder::delete($admin_tables);
		};

		// Delete folder admin/views
		$admin_views = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_faqbookpro'.DS.'views';
		if (\JFolder::exists($admin_views)) {
			\JFolder::delete($admin_views);
		};

		// Delete file admin/controller.php
		$admin_controller_php = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_faqbookpro'.DS.'controller.php';
		if (file_exists($admin_controller_php)) {
			\JFile::delete($admin_controller_php);
		};

		// Delete file admin/faqbookpro.php
		$admin_faqbookpro_php = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_faqbookpro'.DS.'faqbookpro.php';
		if (file_exists($admin_faqbookpro_php)) {
			\JFile::delete($admin_faqbookpro_php);
		};

		// Delete folder site/controllers
		$site_controllers = JPATH_SITE.DS.'components'.DS.'com_faqbookpro'.DS.'controllers';
		if (\JFolder::exists($site_controllers)) {
			\JFolder::delete($site_controllers);
		};

		// Delete folder site/helpers
		$site_helpers = JPATH_SITE.DS.'components'.DS.'com_faqbookpro'.DS.'helpers';
		if (\JFolder::exists($site_helpers)) {
			\JFolder::delete($site_helpers);
		};

		// Delete folder site/libraries
		$site_libraries = JPATH_SITE.DS.'components'.DS.'com_faqbookpro'.DS.'libraries';
		if (\JFolder::exists($site_libraries)) {
			\JFolder::delete($site_libraries);
		};

		// Delete folder site/models
		$site_models = JPATH_SITE.DS.'components'.DS.'com_faqbookpro'.DS.'models';
		if (\JFolder::exists($site_models)) {
			\JFolder::delete($site_models);
		};

		// Delete folder site/views
		$site_views = JPATH_SITE.DS.'components'.DS.'com_faqbookpro'.DS.'views';
		if (\JFolder::exists($site_views)) {
			\JFolder::delete($site_views);
		};

		// Delete file site/controller.php
		$site_controller_php = JPATH_SITE.DS.'components'.DS.'com_faqbookpro'.DS.'controller.php';
		if (file_exists($site_controller_php)) {
			\JFile::delete($site_controller_php);
		};

		// Delete file site/faqbookpro.php
		$site_faqbookpro_php = JPATH_SITE.DS.'components'.DS.'com_faqbookpro'.DS.'faqbookpro.php';
		if (file_exists($site_faqbookpro_php)) {
			\JFile::delete($site_faqbookpro_php);
		};

		// Delete file site/router.php
		$site_router_php = JPATH_SITE.DS.'components'.DS.'com_faqbookpro'.DS.'router.php';
		if (file_exists($site_router_php)) {
			\JFile::delete($site_router_php);
		};
	}

	/*
	 * $parent is the class calling this method.
	 * install runs after the database scripts are executed.
	 * If the extension is new, the install method is run.
	 * If install returns false, Joomla will abort the install and undo everything already done.
	 */
	function install($parent)
	{}

	/*
	 * $parent is the class calling this method.
	 * update runs after the database scripts are executed.
	 * If the extension exists, then the update method is run.
	 * If this returns false, Joomla will abort the update and undo everything already done.
	 */
	function update($parent)
	{}

	/*
	 * $parent is the class calling this method.
	 * $type is the type of change (install, update or discover_install, not uninstall).
	 * postflight is run after the extension is registered in the database.
	 */
	function postflight($type, $parent)
	{}

	/*
	 * $parent is the class calling this method
	 * uninstall runs before any other action is taken (file removal or database processing).
	 */
	function uninstall($parent)
	{}

	private static function getOldVersion()
	{
		$db = Factory::getDBO();
		$query = 'SELECT manifest_cache FROM '. $db->quoteName('#__extensions');
		$query .= ' WHERE '.$db->quoteName('element').' = '.$db->quote('com_faqbookpro').' ';
		$db->setQuery($query);
		$row = $db->loadObject();

		if ($row)
		{
			$manifest_cache = json_decode($row->manifest_cache, false);
			return $manifest_cache;
		}
		else
		{
			return false;
		}
	}
}
