<?php

/**
 * @title		Minitek FAQ Book
 * @copyright	Copyright (C) 2011-2021 Minitek, All rights reserved.
 * @license		GNU General Public License version 3 or later.
 * @author url	https://www.minitek.gr/
 * @developers	Minitek.gr
 */

namespace Joomla\Component\FAQBookPro\Site\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\Router\RouterView;

class Router extends RouterView
{
	/* Builds the URL
	 * Transforms an array of URL parameters
	 * into an array of segments that will form the SEF URL
	 */
	public function build(&$query)
	{
		// Initialize
		$segments = array();

		// If there is only the option and Itemid, let Joomla! decide on the naming scheme
		if (
			isset($query['option']) && isset($query['Itemid']) &&
			!isset($query['view']) && !isset($query['id'])
		) {
			return $segments;
		}

		// Get the menu
		$menu = Factory::getApplication()->getMenu();

		// Detect the active menu item
		if (empty($query['Itemid'])) {
			$menuItem = $menu->getActive();
			$menuItemGiven = false;
		} else {
			$menuItem = $menu->getItem($query['Itemid']);
			$menuItemGiven = true;
		}

		// Check again
		if ($menuItemGiven && isset($menuItem) && $menuItem->component != 'com_faqbookpro') {
			$menuItemGiven = false;
			unset($query['Itemid']);
		}

		if (isset($query['view'])) {
			$view = $query['view'];
		} else {
			// We need to have a view in the query or it is an invalid URL
			return $segments;
		}

		// Are we dealing with a page that is directly attached to a menu item? If yes, just return segments
		if (($menuItem instanceof \stdClass)
			&& $menuItem->query['view'] == $query['view']
			&& isset($query['id'])
			&& $menuItem->query['id'] == (int) $query['id']
		) {
			unset($query['view']);

			if (isset($query['id'])) {
				unset($query['id']);
			}

			return $segments;
		}

		// Sections
		if ($view == 'sections') {
			if (!$menuItemGiven) {
				$segments[] = $view;
			}

			unset($query['view']);
		}

		// Profile
		if ($view == 'profile') {
			if (isset($query['type'])) {
				if ($query['type'] == 'questions') {
					$segments[] = 'questions';
				} else if ($query['type'] == 'answers') {
					$segments[] = 'answers';
				} else if ($query['type'] == 'assigned') {
					$segments[] = 'assigned';
				}
			}

			unset($query['view']);
			unset($query['type']);

			if (isset($query['userid']) && $query['userid'] == Factory::getUser()->id) {
				unset($query['userid']);
			}
		}

		// Questions
		if ($view == 'questions') {
			unset($query['view']);
		}

		// Question form - New question
		if ($view == 'myquestion' && isset($query['layout']) && $query['layout'] == 'edit' && !isset($query['id'])) {
			// Add segments only if it's not a myquestion menu item
			if ($menuItem->query['view'] != 'myquestion') {
				// If Itemid is not for section or topic page, we need the section segment
				if ($menuItem->query['view'] != 'section' && $menuItem->query['view'] != 'topic') {
					// Get the section alias
					$db = Factory::getDbo();
					$dbQuery = $db->getQuery(true)
						->select('alias')
						->from('#__minitek_faqbook_sections')
						->where($db->quoteName('id') . '=' . (int) $query['section']);
					$db->setQuery($dbQuery);
					$section = $db->loadObject();
					$section_alias = $section->alias;
					$segments[] = $section_alias;
				}

				$segments[] = 'new';
				$segments[] = 'question';
			}

			unset($query['view']);
			unset($query['layout']);
			unset($query['section']);
		}

		// Question form - Edit question
		if ($view == 'myquestion' && isset($query['layout']) && $query['layout'] == 'edit' && isset($query['id']) && $query['id']) {
			// If Itemid is not for section or topic page, we need the section segment
			if ($menuItem->query['view'] != 'section' && $menuItem->query['view'] != 'topic') {
				// Get the section alias
				$db = Factory::getDbo();
				$dbQuery = $db->getQuery(true)
					->select('alias')
					->from('#__minitek_faqbook_sections')
					->where($db->quoteName('id') . '=' . (int) $query['section']);
				$db->setQuery($dbQuery);
				$section = $db->loadObject();
				$section_alias = $section->alias;
				$segments[] = $section_alias;
			}

			$segments[] = 'edit';
			$segments[] = 'question';
			unset($query['view']);
			unset($query['layout']);
			unset($query['section']);
		}

		// Answer form - New answer
		if ($view == 'myanswer' && isset($query['layout']) && $query['layout'] == 'edit' && !isset($query['id'])) {
			// If Itemid is not for section or topic page, we need the section segment
			if ($menuItem->query['view'] != 'section' && $menuItem->query['view'] != 'topic') {
				// Get the section alias
				$db = Factory::getDbo();
				$dbQuery = $db->getQuery(true)
					->select('c.id as c_id, c.alias as alias')
					->from('#__minitek_faqbook_questions AS a')
					->join('INNER', $db->quoteName('#__minitek_faqbook_topics', 'b') . ' ON (' . $db->quoteName('a.topicid') . ' = ' . $db->quoteName('b.id') . ')')
					->join('INNER', $db->quoteName('#__minitek_faqbook_sections', 'c') . ' ON (' . $db->quoteName('b.section_id') . ' = ' . $db->quoteName('c.id') . ')')
					->where($db->quoteName('a.id') . '=' . (int) $query['question']);
				$db->setQuery($dbQuery);
				$section = $db->loadObject();
				$section_alias = $section->alias;
				$section_id = $section->c_id;
				$segments[] = $section_alias;
			}

			$segments[] = 'new';
			$segments[] = 'answer';
			unset($query['view']);
			unset($query['layout']);
		}

		// Answer form - Edit answer
		if ($view == 'myanswer' && isset($query['layout']) && $query['layout'] == 'edit' && isset($query['id']) && $query['id']) {
			// If Itemid is not for section or topic page, we need the section segment
			if ($menuItem->query['view'] != 'section' && $menuItem->query['view'] != 'topic') {
				// Get the section alias
				$db = Factory::getDbo();
				$dbQuery = $db->getQuery(true)
					->select('s.id as s_id, s.alias as s_alias')
					->from('#__minitek_faqbook_answers AS a')
					->join('INNER', $db->quoteName('#__minitek_faqbook_questions', 'q') . ' ON (' . $db->quoteName('q.id') . ' = ' . $db->quoteName('a.question_id') . ')')
					->join('INNER', $db->quoteName('#__minitek_faqbook_topics', 't') . ' ON (' . $db->quoteName('t.id') . ' = ' . $db->quoteName('q.topicid') . ')')
					->join('INNER', $db->quoteName('#__minitek_faqbook_sections', 's') . ' ON (' . $db->quoteName('s.id') . ' = ' . $db->quoteName('t.section_id') . ')')
					->where($db->quoteName('a.id') . '=' . (int) $query['id']);
				$db->setQuery($dbQuery);
				$section = $db->loadObject();
				$section_alias = $section->s_alias;
				$section_id = $section->s_id;
				$segments[] = $section_alias;
			}

			$segments[] = 'edit';
			$segments[] = 'answer';
			unset($query['view']);
			unset($query['layout']);
		}

		// Section
		if ($view == 'section') {
			if (isset($query['view'])) {
				unset($query['view']);
			}

			if (isset($query['id'])) {
				// Add section alias segment only if it's not a section menu item
				if ($menuItem->query['view'] != 'section') {
					// Get the section alias
					$db = Factory::getDbo();
					$dbQuery = $db->getQuery(true)
						->select('alias')
						->from('#__minitek_faqbook_sections')
						->where('id=' . (int) $query['id']);
					$db->setQuery($dbQuery);
					$alias = $db->loadResult();
					$segments[] = $alias;
				}
				unset($query['id']);
			};
		}

		// Topic
		if ($view == 'topic') {
			if (isset($query['view'])) {
				unset($query['view']);
			}

			if (isset($query['id'])) {
				// If Itemid is for sections page, we don't have a menu item for section or topic, therefore we need the section segment
				if ($menuItem->query['view'] == 'sections') {
					// Get the section alias
					$db = Factory::getDbo();
					$dbQuery = $db->getQuery(true)
						->select('b.alias as alias')
						->from('#__minitek_faqbook_topics AS a')
						->join('INNER', $db->quoteName('#__minitek_faqbook_sections', 'b') . ' ON (' . $db->quoteName('a.section_id') . ' = ' . $db->quoteName('b.id') . ')')
						->where($db->quoteName('a.id') . '=' . (int) $query['id']);
					$db->setQuery($dbQuery);
					$alias = $db->loadResult();
					$segments[] = $alias;
				}

				// Get the topic path
				$db = Factory::getDbo();
				$dbQuery = $db->getQuery(true)
					->select('path')
					->from('#__minitek_faqbook_topics')
					->where('id=' . (int) $query['id']);
				$db->setQuery($dbQuery);
				$path = $db->loadResult();

				// If Itemid is for parent topic, we must remove the parent topic path from the topic path
				if ($menuItem->query['view'] == 'topic') {
					// Get the parent topic path
					$db = Factory::getDbo();
					$dbQuery = $db->getQuery(true)
						->select('path')
						->from('#__minitek_faqbook_topics')
						->where('id=' . (int) $menuItem->query['id']);
					$db->setQuery($dbQuery);
					$parentPath = $db->loadResult();
					$path = str_replace($parentPath . '/', '', $path . '/');
					$path = rtrim($path, '/');
				}

				$segments[] = $path;
				unset($query['id']);
			};
		}

		// Question
		if ($view == 'question') {
			if (isset($query['view'])) {
				unset($query['view']);
			}

			if (isset($query['id'])) {
				// If Itemid is for sections page, we don't have a menu item for section or topic, therefore we need the section segment
				if ($menuItem->query['view'] == 'sections') {
					// Get the section alias
					$db = Factory::getDbo();
					$dbQuery = $db->getQuery(true)
						->select('c.alias as alias')
						->from('#__minitek_faqbook_questions AS a')
						->join('INNER', $db->quoteName('#__minitek_faqbook_topics', 'b') . ' ON (' . $db->quoteName('a.topicid') . ' = ' . $db->quoteName('b.id') . ')')
						->join('INNER', $db->quoteName('#__minitek_faqbook_sections', 'c') . ' ON (' . $db->quoteName('b.section_id') . ' = ' . $db->quoteName('c.id') . ')')
						->where($db->quoteName('a.id') . '=' . (int) $query['id']);
					$db->setQuery($dbQuery);
					$alias = $db->loadResult();
					$segments[] = $alias;
				}

				// Get the topic path
				$db = Factory::getDbo();
				$dbQuery = $db->getQuery(true)
					->select('b.path as path')
					->from('#__minitek_faqbook_questions AS a')
					->join('INNER', $db->quoteName('#__minitek_faqbook_topics', 'b') . ' ON (' . $db->quoteName('a.topicid') . ' = ' . $db->quoteName('b.id') . ')')
					->where($db->quoteName('a.id') . '=' . (int) $query['id']);
				$db->setQuery($dbQuery);
				$path = $db->loadResult();

				// If Itemid is for parent topic, we must remove the parent topic path from the topic path
				if ($menuItem->query['view'] == 'topic') {
					// Get the parent topic path
					$db = Factory::getDbo();
					$dbQuery = $db->getQuery(true)
						->select('path')
						->from('#__minitek_faqbook_topics')
						->where('id=' . (int) $menuItem->query['id']);
					$db->setQuery($dbQuery);
					$parentPath = $db->loadResult();
					$path = str_replace($parentPath . '/', '', $path . '/');
					$path = rtrim($path, '/');
				}

				$segments[] = $path;

				// Get the question alias
				$db = Factory::getDbo();
				$dbQuery = $db->getQuery(true)
					->select('alias')
					->from('#__minitek_faqbook_questions')
					->where($db->quoteName('id') . '=' . (int) $query['id']);
				$db->setQuery($dbQuery);
				$questionAlias = $db->loadResult();
				$segments[] = $questionAlias;
				unset($query['id']);
			};
		}

		return $segments;
	}

	/* Parses the URL
	 * Transforms an array of segments
	 * back into an array of URL parameters
	 */
	public function parse(&$segments)
	{
		$vars = array();
		$menus = \JMenu::getInstance('site');
		$menu = $menus->getActive();

		// Count route segments
		$count = count($segments);
		$lastSegment = str_replace(':', '-', $segments[$count - 1]);

		// Profile page
		if ($lastSegment == 'questions' || $lastSegment == 'answers' || $lastSegment == 'assigned') {
			if ($menu->query['view'] == 'profile') {
				$vars['view'] = 'profile';

				if ($lastSegment == 'questions') {
					$vars['type'] = 'questions';
				} else if ($lastSegment == 'answers') {
					$vars['type'] = 'answers';
				} else if ($lastSegment == 'assigned') {
					$vars['type'] = 'assigned';
				}

				$segments = array();

				return $vars;
			}
		}

		// Question form
		// else if ($lastSegment == 'question')
		if ($lastSegment == 'question') {
			// We have 3 segments (with section segment - no menu item)
			if (count($segments) === 3) {
				$penultimateSegment = str_replace(':', '-', $segments[$count - 2]);

				if ($penultimateSegment == 'new' || $penultimateSegment == 'edit') {
					$vars['view'] = 'myquestion';
					$vars['layout'] = 'edit';
				}

				// Get section id
				$section_segment = explode(':', $segments[0], 2);
				$section_id = $section_segment[0];
				$section_alias = $section_segment[1];

				// Check that section exists
				$dbQuery = $db->getQuery(true)
					->select('id, alias')
					->from('#__minitek_faqbook_sections')
					->where($db->quoteName('id') . '=' . $db->quote($section_id))
					->where($db->quoteName('alias') . '=' . $db->quote($section_alias));
				$db->setQuery($dbQuery);
				$isSection = $db->loadObject();

				if ($isSection) {
					$vars['section'] = $isSection->id;
				}

				$segments = array();

				return $vars;
			}
		}

		if (is_null($menu) || $menu->query['view'] == 'sections') {
			// No menu or we have only a Sections menu item. The segments are section_alias/topic_path/question_alias

			// Something went wrong (invalid url) and the menu item is translated as 'sections'. Let it go back to sections menu item.
			if ($menu->query['view'] == 'sections') {
				$app = Factory::getApplication();
				$app->enqueueMessage(Text::_('You must create a menu item for this specific section (of type <b>FAQ Book Pro: Section</b>).'), 'error');

				return $vars;
			}

			// If there is only 1 segment (alias) then it is a section
			// If there are more than 1 segments then it could be a topic or a question
			if ($count == 1) {
				$vars['view'] = 'section';

				// We must find the section id from the alias
				$sectionAlias = str_replace(':', '-', $segments[0]);
				$sectionId = self::getSectionId($sectionAlias);
				$vars['id'] = (int) $sectionId;
			} else if ($count > 1) {
				// We must decide if it a topic or a question
				// *** This fails if a question has the same alias as topic ***
				// *** It fails only if the resulting url is the same, so it's not really a problem ***
				// Check for question first
				$lastSegment = $segments[$count - 1];
				$lastSegment = str_replace(':', '-', $lastSegment);
				$db = Factory::getDbo();
				$dbQuery = $db->getQuery(true)
					->select('id')
					->from('#__minitek_faqbook_questions')
					->where($db->quoteName('alias') . '=' . $db->quote($lastSegment));
				$db->setQuery($dbQuery);
				$isQuestion = $db->loadResult();

				if ($isQuestion) {
					$vars['view'] = 'question';

					// We must find the question id from the alias
					$questionId = self::getQuestionId($lastSegment);
					$vars['id'] = (int) $questionId;
				} else {
					// It is a topic
					$vars['view'] = 'topic';

					$topicId = self::getTopicId($lastSegment); // we also need the parent topic id
					$vars['id'] = (int) $topicId;
				}
			}
		} else {
			// We don't mind about a section page because we have a menu item for it

			// We must check whether it is a topic or a question
			// We get the last segment and check its alias.
			if ($count > 1) {
				// Question form
				// We have 2 segments (no section segment)
				$lastSegment = $segments[$count - 1];
				$penultimateSegment = str_replace(':', '-', $segments[$count - 2]);

				if ($lastSegment == 'question' && ($penultimateSegment == 'new' || $penultimateSegment == 'edit')) {
					if ($penultimateSegment == 'new') {
						$vars['view'] = 'myquestion';
						$vars['layout'] = 'edit';
					} else if ($penultimateSegment == 'edit') {
						$vars['view'] = 'myquestion';
						$vars['layout'] = 'edit';
						$vars['id'] = $menu->query['id'];
					}

					if ($menu->query['view'] == 'section') {
						$section_id = $menu->query['id'];
						$vars['section'] = $section_id;
					}

					$segments = array();

					return $vars;
				}

				// Answer form
				// We have 2 segments (no section segment)
				$lastSegment = $segments[$count - 1];
				$penultimateSegment = str_replace(':', '-', $segments[$count - 2]);

				if ($lastSegment == 'answer' && ($penultimateSegment == 'new' || $penultimateSegment == 'edit')) {
					if ($penultimateSegment == 'new') {
						$vars['view'] = 'myanswer';
						$vars['layout'] = 'edit';
					} else if ($penultimateSegment == 'edit') {
						$vars['view'] = 'myanswer';
						$vars['layout'] = 'edit';
						$vars['id'] = $menu->query['id'];
					}

					$segments = array();

					return $vars;
				}

				if ($menu->query['view'] == 'section') {
					$sectionId = $menu->query['id'];
				} else if ($menu->query['view'] == 'topic') {
					$sectionId = self::getTopicSection($menu->query['id']);
				}

				// Check for question first
				// If we have enough segments, we check for parent topic to avoid duplicate question aliases and make sure we have the correct question
				// Get parent topic id

				$topicPath = str_replace(':', '-', $segments[0]);
				foreach ($segments as $seg_key => $segment) {
					if ($seg_key == '0' || $seg_key == count($segments) - 1) {
						continue;
					}
					$topicPath .= '/' . str_replace(':', '-', $segment);
				}

				$topicId = self::getTopicIdfromPath($topicPath, $sectionId);
				$lastSegment = $segments[$count - 1];
				$lastSegment = str_replace(':', '-', $lastSegment);
				$db = Factory::getDbo();
				$dbQuery = $db->getQuery(true)
					->select('id')
					->from('#__minitek_faqbook_questions')
					->where($db->quoteName('alias') . '=' . $db->quote($lastSegment))
					->where($db->quoteName('topicid') . '=' . $db->quote($topicId));
				$db->setQuery($dbQuery);
				$isQuestion = $db->loadResult();

				if ($isQuestion) {
					$vars['view'] = 'question';
					$vars['id'] = (int) $isQuestion;
					$segments = array();
				} else {
					// It is a topic
					$vars['view'] = 'topic';

					// We check for parent topic to avoid duplicate topic aliases and make sure we have the correct topic
					// Get parent topic id
					$parentTopicPath = str_replace(':', '-', $segments[0]);

					foreach ($segments as $seg_key => $segment) {
						if ($seg_key == '0' || $seg_key == count($segments) - 1) {
							continue;
						}

						$parentTopicPath .= '/' . str_replace(':', '-', $segment);
					}

					// If it's a topic menu item, check parent alias
					if ($menu->query['view'] == 'topic') {
						$parentTopicAlias = str_replace(':', '-', $segments[0]);
						$parentTopicId = self::getTopicIdfromAlias($parentTopicAlias, $sectionId);
					}
					// Else, check the parent path
					else {
						$parentTopicId = self::getTopicIdfromPath($parentTopicPath, $sectionId);
					}

					$topicAlias = str_replace(':', '-', $segments[$count - 1]);
					$db = Factory::getDbo();
					$dbQuery = $db->getQuery(true)
						->select('id')
						->from('#__minitek_faqbook_topics')
						->where('alias=' . $db->quote($topicAlias))
						->where('parent_id=' . $db->quote($parentTopicId));
					$db->setQuery($dbQuery);
					$topicId = $db->loadResult();
					$vars['id'] = (int) $topicId;
					$segments = array();
				}
			} else {
				// We don't have enough segments to check for parent topics, so we get the parent topic from the active menu
				// If the active menu is a section, we don't mind about the parent topic because it is a first level topic

				// If active menu is a section, then alias is a topic
				if ($menu->query['view'] == 'section') {
					$sectionId = $menu->query['id'];
					$vars['view'] = 'topic';
					$topicPath = str_replace(':', '-', $segments[$count - 1]);
					$topicId = self::getTopicIdfromPath($topicPath, $sectionId);
					$vars['id'] = (int) $topicId;
					$segments = array();
				}

				// If active menu is a topic, alias can be a topic or a question
				else if ($menu->query['view'] == 'topic') {
					// Check for question first
					// We check for parent topic first to avoid duplicate question aliases and make sure we have the correct question
					$topicId = $menu->query['id'];
					$lastSegment = $segments[$count - 1];
					$lastSegment = str_replace(':', '-', $lastSegment);
					$db = Factory::getDbo();
					$dbQuery = $db->getQuery(true)
						->select('id')
						->from('#__minitek_faqbook_questions')
						->where($db->quoteName('alias') . '=' . $db->quote($lastSegment))
						->where($db->quoteName('topicid') . '=' . $db->quote($topicId));
					$db->setQuery($dbQuery);
					$isQuestion = $db->loadResult();

					if ($isQuestion) {
						$vars['view'] = 'question';
						$vars['id'] = (int) $isQuestion;
						$segments = array();
					} else {
						// It is a topic
						$vars['view'] = 'topic';

						// We check for parent topic to avoid duplicate topic aliases and make sure we have the correct topic
						// Get parent topic id
						$parentTopicId = $menu->query['id'];
						$topicAlias = str_replace(':', '-', $segments[$count - 1]);
						$db = Factory::getDbo();
						$dbQuery = $db->getQuery(true)
							->select('id')
							->from('#__minitek_faqbook_topics')
							->where('alias=' . $db->quote($topicAlias))
							->where('parent_id=' . $db->quote($parentTopicId));
						$db->setQuery($dbQuery);
						$topicId = $db->loadResult();
						$vars['id'] = (int) $topicId;
						$segments = array();
					}
				}
			}
		}

		return $vars;
	}

	public function getSectionId($alias)
	{
		$db = Factory::getDbo();
		$dbQuery = $db->getQuery(true)
			->select('id')
			->from('#__minitek_faqbook_sections')
			->where('alias=' . $db->quote($alias));
		$db->setQuery($dbQuery);
		$id = $db->loadResult();

		return $id;
	}

	public function getTopicId($alias, $sectionId = false)
	{
		$db = Factory::getDbo();
		$dbQuery = $db->getQuery(true)
			->select('id')
			->from('#__minitek_faqbook_topics')
			->where('alias=' . $db->quote($alias));

		if ($sectionId)
			$dbQuery->where('section_id=' . $db->quote($sectionId));

		$db->setQuery($dbQuery);
		$id = $db->loadResult();

		return $id;
	}

	public function getTopicSection($id)
	{
		$db = Factory::getDbo();
		$dbQuery = $db->getQuery(true)
			->select('section_id')
			->from('#__minitek_faqbook_topics')
			->where('id=' . $db->quote($id));

		$db->setQuery($dbQuery);
		$sectionId = $db->loadResult();

		return $sectionId;
	}

	public function getTopicIdfromPath($path, $sectionId = false)
	{
		$db = Factory::getDbo();
		$dbQuery = $db->getQuery(true)
			->select('id')
			->from('#__minitek_faqbook_topics')
			->where('path=' . $db->quote($path));

		if ($sectionId)
			$dbQuery->where('section_id=' . $db->quote($sectionId));

		$db->setQuery($dbQuery);
		$id = $db->loadResult();

		return $id;
	}

	public function getTopicIdfromAlias($alias, $sectionId = false)
	{
		$db = Factory::getDbo();
		$dbQuery = $db->getQuery(true)
			->select('id')
			->from('#__minitek_faqbook_topics')
			->where('alias=' . $db->quote($alias));

		if ($sectionId)
			$dbQuery->where('section_id=' . $db->quote($sectionId));

		$db->setQuery($dbQuery);
		$id = $db->loadResult();

		return $id;
	}

	public function getQuestionId($alias)
	{
		$db = Factory::getDbo();
		$dbQuery = $db->getQuery(true)
			->select('id')
			->from('#__minitek_faqbook_questions')
			->where('alias=' . $db->quote($alias));
		$db->setQuery($dbQuery);
		$id = $db->loadResult();

		return $id;
	}
}
