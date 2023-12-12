<?php

/**
 * @title        Minitek FAQ Book
 * @copyright    Copyright (C) 2011-2023 Minitek, All rights reserved.
 * @license      GNU General Public License version 3 or later.
 * @author url   https://www.minitek.gr/
 * @developers   Minitek.gr
 */

namespace Joomla\Component\FAQBookPro\Site\Dispatcher;

defined('JPATH_PLATFORM') or die;

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

use Joomla\CMS\Factory;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\URI\URI;
use Joomla\CMS\Component\ComponentHelper;

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
		$params = ComponentHelper::getParams('com_faqbookpro');
		$document = Factory::getDocument();
		$wa = $document->getWebAssetManager();

		// Fix relative links
		if ($params->get('fix_relative', false))
			$document->base = URI::root();

		// Load Bootstrap css
		if ($params->get('load_bootstrap', 0))
			HTMLHelper::_('bootstrap.loadCss');

		// Add main stylesheet
		$wa->useStyle('com_faqbookpro.faqbookpro');

		// Load FontAwesome
		if ($params->get('load_fontawesome', 1))
			$wa->useScript('com_faqbookpro.fontawesome');

		// Load main script
		$view = Factory::getApplication()->input->get('view');

		if ($view && $view != 'sections')
			$wa->useScript('com_faqbookpro.faqbookpro');

		parent::dispatch();
	}
}
