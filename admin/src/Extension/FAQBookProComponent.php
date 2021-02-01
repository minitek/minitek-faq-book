<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Administrator\Extension;

defined('_JEXEC') or die;

if(!defined('DS')){ define('DS',DIRECTORY_SEPARATOR); }

use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Psr\Container\ContainerInterface;
use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;

/**
 * Component class for com_faqbookpro.
 *
 * @since  4.0.0
 */
class FAQBookProComponent extends MVCComponent implements BootableExtensionInterface, RouterServiceInterface
{
	use HTMLRegistryAwareTrait;
	use RouterServiceTrait;

	/**
	 * Booting the extension. This is the function to set up the environment of the extension like
	 * registering new class loaders, etc.
	 *
	 * If required, some initial set up can be done from services of the container, eg.
	 * registering HTML services.
	 *
	 * @param   ContainerInterface  $container  The container
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function boot(ContainerInterface $container)
	{
		$this->loadAssets();
	}

	/**
	 * Method to load admin assets
	 *
	 * @since   4.0.0
	 *
	 * @return  void
	 */
	protected function loadAssets()
	{
		$app = \JFactory::getApplication();
		$document = \JFactory::getDocument();

		if ($app->isClient('administrator'))
		{
			// Add stylesheets
			$document->addStyleSheet(\JURI::root(true).'/administrator/components/com_faqbookpro/assets/css/style.css');

			// Add js
			\JHtml::_('jquery.framework');
			$document->addScript(\JURI::root(true).'/administrator/components/com_faqbookpro/assets/js/script.js');
		}
	}

	/**
	 * Method to check component access permission
	 *
	 * @since   4.0.0
	 *
	 * @return  void
	 */
	protected function checkAccess()
	{
		// Access check
		if (!\JFactory::getUser()->authorise('core.manage', 'com_faqbookpro'))
		{
			throw new \Joomla\CMS\Access\Exception\Notallowed(\JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}
	}
}
