<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2022 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class BatchTopicIdField
{
	public function getInput()
	{
		$groups = array();
		$published = array(0, 1);
		$db = Factory::getDbo();
		$app = Factory::getApplication();

		// Get sections 
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__minitek_faqbook_sections'))
			->where($db->quoteName('state').' IN (' . implode(',', $published) . ')')
			->order('title ASC');
		$db->setQuery($query);

		try
		{
			$sections = $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return;
		}

		// Build the sections groups
		foreach ($sections as $section)
		{
			// Get the topics for this section 
			$query = $db->getQuery(true)
				->select('DISTINCT a.id AS value, a.title AS text, a.level, a.published, a.lft, a.section_id');

			$subQuery = $db->getQuery(true)
				->select('id, title, level, published, parent_id, lft, rgt, section_id')
				->from($db->quoteName('#__minitek_faqbook_topics'))
				->where($db->quoteName('level').' > 0')
				->where($db->quoteName('section_id').' = '.$db->quote($section->id))
				->where($db->quoteName('published').' IN (' . implode(',', $published) . ')');

			$query->from('(' . $subQuery->__toString() . ') AS a')
				->join('LEFT', $db->quoteName('#__minitek_faqbook_topics') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt')
				->order('a.lft ASC');

			$db->setQuery($query);

			try
			{
				$topics = $db->loadObjectList();
			}
			catch (\RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return;
			}

			if (empty($topics) && $app->input->getCmd('view', '') == 'questions')
				continue;

			// Pad the topic text with spaces using depth level as a multiplier
			for ($i = 0, $n = count($topics); $i < $n; $i++)
			{
				if ($topics[$i]->published == 1)
				{
					$topics[$i]->text = str_repeat('- ', $topics[$i]->level) . $topics[$i]->text;
				}
				else
				{
					$topics[$i]->text = str_repeat('- ', $topics[$i]->level) . '[' . $topics[$i]->text . ']';			
					$topics[$i]->text .= ']';
				}
			}

			// Initialize the group
			$groups[$section->title] = array();
			$groups[$section->title]['items'] = array();

			if ($app->input->getCmd('view', '') == 'topics')
			{
				// 'Add to this section' option
				$groups[$section->title]['items'][] = HTMLHelper::_(
					'select.option', $section->id, Text::_('COM_FAQBOOKPRO_FIELD_PARENT_OPTION_ADD_TO_SECTION').''.$section->title, 'value', 'text'
				);
			}
			
			if ($app->input->getCmd('view', '') == 'questions')
			{
				// Build the topics
				foreach ($topics as $topic)
				{
					$groups[$section->title]['items'][] = HTMLHelper::_(
						'select.option', $topic->value, $topic->text, 'value', 'text'
					);
				}
			}
			else if ($app->input->getCmd('view', '') == 'topics')
			{
				// Build the topics
				foreach ($topics as $topic)
				{
					$groups[$section->title]['items'][] = HTMLHelper::_(
						'select.option', $topic->section_id.'.'.$topic->value, $topic->text, 'value', 'text'
					);
				}
			}
		}
		
		// Compute attributes for the grouped list
		$attr = ' class="form-select"';

		// Prepare HTML code
		$html = array();

		// Add - Select - option to array
		$groups[]['items'][] = HTMLHelper::_('select.option', '', Text::_('COM_FAQBOOKPRO_OPTION_SELECT'));

		// Remove - Select - option from end of array and add to the beginning or array
		$remove = array_pop($groups); 
		$select = array('0' => $remove);
		$groups = $select + $groups;

		// Add a grouped list
		$html[] = HTMLHelper::_(
			'select.groupedlist', $groups, 'batch[topic_id]',
			array('id' => 'batch-topic-id', 'group.id' => 'id', 'list.attr' => $attr)
		);

		return implode($html);
	}
}