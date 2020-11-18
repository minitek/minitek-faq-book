<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
	protected $default_view = 'dashboard';

	public function display($cachable = false, $urlparams = array())
	{
		return parent::display();
	}

	/**
	 * Method to check for latest version.
	 *
	 * @return  Section object
	 *
	 * @since   4.0.5
	 */
	public function checkForUpdate()
	{
		$app = \JFactory::getApplication();
		$input = $app->input;

		$type = $input->get('type', 'auto');
		$params = \JComponentHelper::getParams('com_faqbookpro');
		$version_check = $params->get('version_check', 1);

		// Don't allow auto if version checking is disabled
		if ($type == 'auto' && !$version_check)
		{
			$app->close();
		}

		$input->set('view', 'dashboard', 'STRING');
		$input->set('layout', 'update', 'STRING');

		// Display
		parent::display();

		// Exit
		$app->close();
	}
}
