<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2021 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Administrator\View\Dashboard;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\URI\URI;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Dashboard view class for FAQ Book.
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		// Skip if view == update
		if (Factory::getApplication()->input->get('view') != 'update')
		{
			// Load dashboard.js
			Factory::getDocument()->addScript(URI::root(true).'/administrator/components/com_faqbookpro/assets/js/dashboard.js');

			$this->addToolbar();

			parent::display($tpl);
		}
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function addToolbar()
	{
		ToolbarHelper::title(\JText::_('COM_FAQBOOKPRO_DASHBOARD_TITLE'), '');
	}
}
