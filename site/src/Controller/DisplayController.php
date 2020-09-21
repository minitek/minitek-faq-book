<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * FAQ Book Component Controller
 *
 * @since  4.0.0
 */
class DisplayController extends \Joomla\CMS\MVC\Controller\BaseController
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The JApplication for the dispatcher
	 * @param   \JInput              $input    Input
	 *
	 * @since   3.0.1
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);
	}

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached.
	 * @param   boolean  $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  \Joomla\CMS\MVC\Controller\BaseController  This object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function display($cachable = false, $urlparams = false)
 	{
 		$cachable = true;

 		/**
 		 * Set the default view name and format from the Request.
 		 */
 		$id    = $this->input->getInt('id');
 		$vName = $this->input->getCmd('view', 'section');
 		$this->input->set('view', $vName);

 		$safeurlparams = array(
 			'id' => 'INT',
 			'lang' => 'CMD',
 			'Itemid' => 'INT');

 		parent::display($cachable, $safeurlparams);

 		return $this;
 	}
}
