<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

namespace Joomla\Component\FaqBookPro\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * FAQ Book Section controller.
 *
 * @since  4.0.0
 */
class SectionController extends BaseController
{
	public function getContent()
	{
		// Get input
		$app = Factory::getApplication();
		$input = $app->input;

		// Get variables
		$sectionId = $input->get('sectionId', '', 'INT');
    $topicId = $input->get('topicId', '', 'INT');
		$tab = $input->get('tab', '', 'STRING');
		$page = $input->get('page', '1', 'INT');
    $filter = $input->get('filter', '', 'STRING');

		// Set variables
		$input->set('view', 'section');
		$input->set('id', $sectionId);
    $input->set('topicId', $topicId);
		$input->set('page', $page);

		// Set layout
		switch ($tab)
		{
			case 'recent':
			case 'top':
			case 'featured':
			case 'unanswered':
			case 'unresolved':
			case 'resolved':
			case 'open':
			case 'pending':
				$layout = 'content';
				break;
			case 'topics':
				$layout = 'topics';
				break;
			default:
				$layout = 'content';
		}

		if ($layout == 'content' && $page > 1)
		{
			$layout = 'questions';
		}
    else if ($layout == 'content' && $filter == 'tab')
    {
      $layout = 'topic';
    }

		$input->set('layout', 'default_'.$layout);

		// Display
		parent::display();

    // Exit
		$app->close();
	}

	public function toggleLeftnav()
	{
		// Get input
		$app = Factory::getApplication();
		$input = $app->input;

		// Get variables
		$minimized = $input->get('minimized', 'off');

		// Set state variable
		$app->setUserState('com_faqbookpro.minimized_leftnav', $minimized);

    // Exit
		$app->close();
	}
}
