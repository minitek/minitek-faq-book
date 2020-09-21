<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

namespace Joomla\Component\FaqBookPro\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * FAQ Book Topic controller.
 *
 * @since  4.0.0
 */
class TopicController extends BaseController
{
	public function getContent()
	{
		// Get input
		$app = Factory::getApplication();
		$input = $app->input;

		// Get variables
		$id = $input->get('id', '', 'INT');
		$tab = $input->get('tab', 'recent', 'STRING');
		$page = $input->get('page', '1', 'INT');
		$filter = $input->get('filter', '', 'STRING');

		// Set variables
		$input->set('view', 'topic');
		$input->set('id', $id);
		$input->set('tab', $tab);
		$input->set('filter', $filter);

		// Set layout
		if ($page > 1)
		{
			$layout = 'questions';
		}
		else
		{
			$layout = 'topic';
		}

		$input->set('layout', 'default_'.$layout);

		// Display
		parent::display();

		// Exit
		$app->close();
	}
}
