<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Site\Dispatcher;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Dispatcher\ComponentDispatcher;

/**
 * ComponentDispatcher class for com_faqbookpro
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
	/**
	 * Dispatch a controller task. Redirecting the user if appropriate.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function dispatch()
	{
		// Get component params
		$params = \JComponentHelper::getParams('com_faqbookpro');
		$document = Factory::getDocument();

		// Fix relative links
		if ($params->get('fix_relative', false))
			$document->base = \JURI::root();

		// Add main stylesheet
		$document->addStyleSheet(\JURI::base().'components/com_faqbookpro/assets/css/style.css');

		// Load bootstrap css
		if ($params->get('load_bootstrap_css', 0))
			\JHtmlBootstrap::loadCss();

		// Load jQuery
		if ($params->get('load_jquery', 1))
			\JHtml::_('jquery.framework');

		// Load FontAwesome
		if ($params->get('load_fontawesome', 1))
			$document->addScript('https://kit.fontawesome.com/ddbd3bdfdd.js');

		// Load spinner
		$document->addScript(\JURI::base().'components/com_faqbookpro/assets/js/spin.min.js');

		// Load main script
		$view = Factory::getApplication()->input->get('view');
		if ($view && $view != 'sections' && $view != 'profile' && $view != 'questions')
			$document->addScript(\JURI::base().'components/com_faqbookpro/assets/js/script.js');

		parent::dispatch();
	}
}
