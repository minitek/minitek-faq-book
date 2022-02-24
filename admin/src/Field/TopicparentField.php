<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2022 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class TopicParentField extends FormField
{
	public $type = 'TopicParent';

	protected function getInput()
	{
		$groups = array();
		$published = $this->element['published'] ? (string) $this->element['published'] : array(0, 1);
		$db = Factory::getDbo();
		$app = Factory::getApplication();
		$topicid = $app->isClient('administrator') ? $app->input->getInt('id', 0) : $app->input->getInt('topicid', 0);

		if ($app->isClient('administrator') && $app->input->getCmd('option', '') == 'com_menus')
		{
			$topicid = 0;
		}

		$name = (string) $this->element['name'];
		$parentid = $this->form->getValue($name, 0);		
		$user = Factory::getUser();

		// Get sections 
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__minitek_faqbook_sections'));

		// Filter on the published state
		if ($user->authorise('core.edit.state', 'com_faqbookpro'))
		{
			if (is_numeric($published))
			{
				$query->where($db->quoteName('state').' = ' . (int) $published);
			}
			elseif (is_array($published))
			{
				ArrayHelper::toInteger($published);
				$query->where($db->quoteName('state').' IN (' . implode(',', $published) . ')');
			}
			else 
			{
				$published = explode(',', $published);
				ArrayHelper::toInteger($published);
				$query->where($db->quoteName('state').' IN (' . implode(',', $published) . ')');
			}
		}
		else 
		{
			$query->where($db->quoteName('state').' = ' . $db->quote(1));
		}

		// Filter by section id in back-end
		if ($app->isClient('administrator') && $app->input->getCmd('view', '') == 'section' && $app->input->getInt('id', 0))
		{
			$query->where($db->quoteName('id').' = ' . $db->quote($app->input->getInt('id', 0)));
		}

		// Filter by section id in front-end
		if ($app->isClient('site') && $app->input->getCmd('view', '') == 'myquestion')
		{
			$sectionid = $app->input->getInt('section', 0);

			if ($user->authorise('core.edit.state', 'com_faqbookpro') != true)
			{
				$query->where($db->quoteName('id').' = ' . $db->quote($sectionid));
			}
		}
		else 
		{
			$sectionid = $this->form->getValue('section_id', 0);
		}

		// Filter by language
		if ($app->isClient('site'))
		{
			$query->where($db->quoteName('language').' IN('.$db->quote(Factory::getLanguage()->getTag()).', '.$db->quote('*').')');
		}
		
		$query->order('title ASC');

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

		if ($app->isClient('administrator') && $app->input->getCmd('view', '') == 'section' && !$app->input->getInt('id', 0))
		{}
		else 
		{
			// Build the sections groups
			foreach ($sections as $section)
			{
				// Get the topics for this section 
				$query = $db->getQuery(true)
					->select('DISTINCT a.id AS value, a.title AS text, a.level, a.published, a.lft, a.qvisibility, a.section_id');

				$subQuery = $db->getQuery(true)
					->select('id, title, level, published, parent_id, lft, rgt, qvisibility, section_id')
					->from($db->quoteName('#__minitek_faqbook_topics'))
					->where($db->quoteName('level').' > 0')
					->where($db->quoteName('section_id').' = '.$db->quote($section->id));

				if ($app->isClient('site') && $topicid)
				{
					// If there is a topic in the url, select only this topic 
					$subQuery->where($db->quoteName('id').' = '.$db->quote($topicid));
				}

				// Filter on the published state
				if (is_numeric($published))
				{
					$subQuery->where($db->quoteName('published').' = ' . (int) $published);
				}
				elseif (is_array($published))
				{
					ArrayHelper::toInteger($published);
					$subQuery->where($db->quoteName('published').' IN (' . implode(',', $published) . ')');
				}
				else 
				{
					$published = explode(',', $published);
					ArrayHelper::toInteger($published);
					$subQuery->where($db->quoteName('published').' IN (' . implode(',', $published) . ')');
				}

				// Filter by language
				if ($app->isClient('site'))
				{
					$subQuery->where($db->quoteName('language').' IN('.$db->quote(Factory::getLanguage()->getTag()).', '.$db->quote('*').')');
				}

				$query->from('(' . $subQuery->__toString() . ') AS a')
					->join('LEFT', $db->quoteName('#__minitek_faqbook_topics') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt')
					->order('a.lft ASC');

				// Prevent parenting to children of this topic
				if ($app->isClient('administrator') && $app->input->getCmd('view', '') == 'topic')
				{
					if ($topicid != 0)
					{
						$query->join('LEFT', $db->quoteName('#__minitek_faqbook_topics') . ' AS p ON p.id = ' . (int) $topicid)
							->where('NOT(a.lft >= p.lft AND a.rgt <= p.rgt)');
					}
				}

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

				// Stop if there are not topics in this section 
				if ($app->isClient('site')
					|| ($app->isClient('administrator') 
						&& ($app->input->getCmd('view', '') == 'questions' || $app->input->getCmd('view', '') == 'question')))
				{
					if (empty($topics))
						continue;
				}

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

				if ($app->isClient('administrator') && $app->input->getCmd('view', '') == 'topic')
				{
					// For new items we want a list of topics you are allowed to create in.
					if ($topicid == 0)
					{
						foreach ($topics as $i => $topic)
						{
							/* To take save or create in a topic you need to have create rights for that topic
							* unless the item is already in that topic.
							* Unset the option if the user isn't authorised for it. In this field assets are always topics.
							*/
							if ($user->authorise('core.create', 'com_faqbookpro.topic.' . $topic->value) != true && $topic->level != 0)
							{
								unset($topics[$i]);
							}
						}
					}
					// If you have an existing topic id things are more complex.
					else
					{
						/* If you are only allowed to edit in this topic but not edit.state, you should not get any
						* option to change the topic parent for a topic or the topic for a content item,
						* but you should be able to save in that topic.
						*/
						foreach ($topics as $i => $topic)
						{
							if ($user->authorise('core.edit.state', 'com_faqbookpro.topic.' . $topicid) != true && !isset($parentid))
							{
								if ($topic->value != $topicid)
								{
									unset($topic[$i]);
								}
							}

							if ($user->authorise('core.edit.state', 'com_faqbookpro.topic.' . $topicid) != true
								&& (isset($parentid))
								&& $topic->value != $parentid)
							{
								unset($topics[$i]);
							}

							// However, if you can edit.state you can also move this to another topic for which you have
							// create permission and you should also still be able to save in the current topic.
							if (($user->authorise('core.create', 'com_faqbookpro.topic.' . $topic->value) != true)
								&& ($topic->value != $topicid && !isset($parentid)))
							{
								{
									unset($topics[$i]);
								}
							}

							if (($user->authorise('core.create', 'com_faqbookpro.topic.' . $topic->value) != true)
								&& (isset($parentid))
								&& $topic->value != $parentid)
							{
								{
									unset($topics[$i]);
								}
							}
						}
					}
				}

				if ($app->isClient('site'))
				{
					// Remove topics where user has no permission to create 
					foreach ($topics as $i => $topic)
					{
						if ($user->authorise('core.create', 'com_faqbookpro.topic.' . $topic->value) != true)
						{
							unset($topics[$i]);
						}
					}
				}
		
				// Filter by qvisibility and permissions
				// Users without permissions to create private can not select/see topics of private only questions
				foreach ($topics as $i => $topic)
				{
					if (!$user->authorise('core.private.create', 'com_faqbookpro.topic.' . $topic->value)
						&& $topic->qvisibility == 2)
					{
						unset($topics[$i]);
					}
				}

				// Initialize the group
				$title = $section->title;

				if (array_key_exists($title, $groups))
					$title .= ' ['.$section->alias.']';

				$groups[$title] = array();
				$groups[$title]['id'] = $section->id;
				$groups[$title]['items'] = array();

				// 'Add to section' option
				if ($app->isClient('administrator') && $app->input->getCmd('view', '') == 'topic')
				{
					$groups[$title]['items'][] = HTMLHelper::_(
						'select.option', 'section.'.$section->id.':1', Text::_('COM_FAQBOOKPRO_FIELD_PARENT_OPTION_ADD_TO_SECTION').''.$section->title, 'value', 'text'
					);
				}
			
				// Build the topics
				foreach ($topics as $topic)
				{
					$groups[$title]['items'][] = HTMLHelper::_(
						'select.option', $topic->value, $topic->text, 'value', 'text'
					);
				}
			}
		}
		
		// Compute attributes for the grouped list
		$attr = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$attr .= $this->element['multiple'] && $this->element['multiple'] == 'true' ? ' multiple' : '';
		$attr .= $this->element['onchange'] ? ' onchange="' . $this->element['onchange'] . '"' : '';
		$attr .= $this->element['required'] ? ' required="required"' : '';

		// Prepare HTML code
		$html = array();

		// Compute the current selected values
		if ($app->input->getCmd('view', '') == 'customfield')
		{
			$selected = array();

			if ($customfield_id = $app->input->get('id'))
			{
				$_topics = $this->getSelectedTopics($customfield_id);

				foreach ($_topics as $topic)
				{
					$selected[] = $topic->topicid;
				}
			}
		}
		else 
		{
			if ($this->value == 1)
				$selected = array('section.'.$sectionid.':1');
			else 
				$selected = array($this->value);
		}

		// Add - Select - option to array
		if ($app->isClient('administrator'))
		{
			if ($app->input->getCmd('view', '') == 'questions' 
				|| $app->input->getCmd('view', '') == 'question'
				|| $app->input->getCmd('view', '') == 'section'
				|| $app->input->getCmd('option', '') == 'com_menus') 
			{
				$groups[]['items'][] = HTMLHelper::_('select.option', '', Text::_('COM_FAQBOOKPRO_OPTION_SELECT_TOPIC'));
			}
			else if ($app->input->getCmd('view', '') == 'customfield') 
			{
				$groups[]['items'][] = HTMLHelper::_('select.option', '', Text::_('COM_FAQBOOKPRO_OPTION_SELECT_TOPICS'));
			}
			else if ($app->input->getCmd('view', '') == 'topic')
			{
				$groups[]['items'][] = HTMLHelper::_('select.option', '', Text::_('COM_FAQBOOKPRO_OPTION_SELECT_PARENT'));
			}

			// Remove - Select - option from end of array and add to the beginning or array
			$remove = array_pop($groups); 
			$select = array('0' => $remove);
			$groups = $select + $groups;
		}

		if ($app->isClient('site') && ($app->input->getCmd('view', '') == 'questions' || $app->input->getCmd('view', '') == 'myquestion'))
		{
			$groups[]['items'][] = HTMLHelper::_('select.option', '', Text::_('COM_FAQBOOKPRO_OPTION_SELECT_TOPIC'));

			// Remove - Select - option from end of array and add to the beginning or array
			$remove = array_pop($groups); 
			$select = array('0' => $remove);
			$groups = $select + $groups;
		}

		// Add a grouped list
		$html[] = HTMLHelper::_(
			'select.groupedlist', $groups, $this->name,
			array('id' => $this->id, 'group.id' => 'id', 'list.attr' => $attr, 'list.select' => $selected)
		);

		if ($app->isClient('administrator'))
		{
			$app->getDocument()->getWebAssetManager()
				->usePreset('choicesjs')
				->useScript('webcomponent.field-fancy-select');

			return '<joomla-field-fancy-select>'.implode($html).'</joomla-field-fancy-select>';
		}

		return implode($html);
	}

	private function getSelectedTopics($customfield_id)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('topicid'))
			->from($db->quoteName('#__minitek_faqbook_customfields_topics'))
			->where($db->quoteName('customfield_id').' = '.(int)$customfield_id);
		$db->setQuery($query);
		$topics = $db->loadObjectList();

		return $topics;
	}
}