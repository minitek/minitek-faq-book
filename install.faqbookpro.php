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
use Joomla\Component\Installer\Administrator\Model\InstallModel;

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
		// Get new version
		$this->new_version = $parent->manifest->version;

		// Get Joomla version
		$version = new \JVersion();
		$sversion = $version->getShortVersion();

		if (is_object($this->getInstalledVersion()))
		{
			// Get installed version
			$this->installed_version = $this->getInstalledVersion()->version;

			// Abort if old version is older than 3.9.3.1
			if (isset($this->installed_version) && $this->installed_version && version_compare($this->installed_version, '3.9.3.1', '<'))
			{
				throw new GenericDataException('Cannot install version <strong>'.$this->new_version.'</strong> over version <strong>'.$this->installed_version.'</strong>. Please update to version 3.9.3.1 or a later 3.9.x version.', 500);
				
				return false;
			}
		}
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

	/*
	 * $parent is the class calling this method
	 * get installed version.
	 */
	private static function getInstalledVersion()
	{
		$db = Factory::getDBO();
		$query = 'SELECT '.$db->quoteName('manifest_cache').' FROM '.$db->quoteName('#__extensions');
		$query .= ' WHERE '.$db->quoteName('element').' = '.$db->quote('com_faqbookpro');
		$db->setQuery($query);

		if ($row = $db->loadObject())
		{
			$manifest_cache = json_decode($row->manifest_cache, false);

			return $manifest_cache;
		}
		
		return false;
	}
}
