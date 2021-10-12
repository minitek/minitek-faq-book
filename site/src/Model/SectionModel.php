<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Component\FAQBookPro\Site\Helper\UtilitiesHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Component\ComponentHelper;

/**
 * FAQ Book Component Section Model
 *
 * @since  4.0.0
 */
class SectionModel extends BaseDatabaseModel
{
	var $_item = null;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   3.9.0
	 *
	 * @return void
	 */
	protected function populateState()
	{
		$app = Factory::getApplication('site');

		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('section.id', $pk);

		$user = Factory::getUser();

		// If $pk is set then authorise on complete asset, else on component only
		$asset = empty($pk) ? 'com_faqbookpro' : 'com_faqbookpro.section.' . $pk;

		if ((!$user->authorise('core.edit.state', $asset)) && (!$user->authorise('core.edit', $asset)))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}

		$this->setState('filter.language', Multilanguage::isEnabled());
	}

	/**
	 * Method to get section data.
	 *
	 * @param   integer  $pk  The id of the section.
	 *
	 * @return  object|boolean|JException  Menu item data object on success, boolean false or JException instance on error
	 */
	public function getItem($pk = null)
	{
		$user = Factory::getUser();

		$pk = (!empty($pk)) ? $pk : (int) $this->getState('section.id');

		if ($this->_item === null)
		{
			$this->_item = array();
		}

		if (!isset($this->_item[$pk]))
		{
			try
			{
				$db = $this->getDbo();
				$query = $db->getQuery(true)
					->select('a.*');
				$query->from('#__minitek_faqbook_sections AS a')
					->where('a.id = ' . (int) $pk);

				// Filter by language
				if ($this->getState('filter.language'))
				{
					$query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
				}

				// Filter by published state.
				$published = $this->getState('filter.published');
				$archived = $this->getState('filter.archived');

				if (is_numeric($published))
				{
					$query->where('(a.state = ' . (int) $published . ' OR a.state =' . (int) $archived . ')');
				}

				$db->setQuery($query);

				$data = $db->loadObject();

				if (empty($data))
				{
					throw new \Exception(Text::_('COM_FAQBOOKPRO_ERROR_SECTION_NOT_FOUND'), 404);
				}

				// Check for published state if filter set.
				if ((is_numeric($published) || is_numeric($archived)) && (($data->state != $published) && ($data->state != $archived)))
				{
					throw new \Exception(Text::_('COM_FAQBOOKPRO_ERROR_SECTION_NOT_FOUND'), 404);
				}

				// If no access filter is set, the layout takes some responsibility for display of limited information.
				$user = Factory::getUser();
				$groups = $user->getAuthorisedViewLevels();
				$data->access_view = in_array($data->access, $groups);

				$this->_item[$pk] = $data;
			}
			catch (\Exception $e)
			{
				if ($e->getCode() == 404)
				{
					// Need to go thru the error handler to allow Redirect to work.
					throw new \Exception($e->getMessage(), 404);
				}
				else
				{
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
		}

		return $this->_item[$pk];
	}

	/* Get first-level topics in section */
	public function getSectionTopics($sectionId)
	{
		$db = Factory::getDbo();
		$user = Factory::getUser();
		$query = $db->getQuery(true);

		// Right join with c for topic
		$query->select('c.id, c.asset_id, c.access, c.alias, c.checked_out, c.checked_out_time,
			c.created_time, c.created_user_id, c.description, c.hits, c.language, c.level,
			c.lft, c.metadata, c.metadesc, c.metakey, c.modified_time, c.params, c.parent_id, c.section_id,
			c.path, c.published, c.rgt, c.title, c.modified_user_id');
		$case_when = ' CASE WHEN ';
		$case_when .= $query->charLength('c.alias', '!=', '0');
		$case_when .= ' THEN ';
		$c_id = $query->castAsChar('c.id');
		$case_when .= $query->concatenate(array($c_id, 'c.alias'), ':');
		$case_when .= ' ELSE ';
		$case_when .= $c_id . ' END as slug';
		$query->select($case_when)
			->from('#__minitek_faqbook_topics as c');
		$query->where('c.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
		$query->where('c.published = 1');
		$query->where('c.level = 1');
		$query->where('c.section_id=' . (int) $sectionId);

		// Filter by language
		$query->where('c.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');

		$query->order('c.lft');

		// Get the results
		$db->setQuery($query);
		$topics = $db->loadObjectList();

		return $topics;
	}

	public function getSectionQuestions($sectionId, $ordering, $ordering_dir, $page = 1)
	{
		$db = Factory::getDbo();
		$user = Factory::getUser();
		$query = $db->getQuery(true);

		$query->select('a.id, a.title, a.alias, a.content, a.answers, a.checked_out, a.checked_out_time, a.state,
			a.topicid, a.created, a.created_by, a.created_by_name, a.created_by_email, a.created_by_alias, a.assigned_to, ' .
			// Use created if modified is 0
			'CASE WHEN a.modified = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.modified END as modified, ' .
			'a.modified_by,' .
			'a.images, a.attribs, a.metadata, a.metakey, a.metadesc, a.access, ' .
			'a.hits, a.featured, a.locked, a.pinned, a.private, a.resolved, a.publish_up, a.publish_down'
		);
		$query->from('#__minitek_faqbook_questions AS a');

		// Join over the topics
		$query->select('c.title AS topic_title, c.path AS topic_route, c.access AS topic_access, c.alias AS topic_alias')
			->join('LEFT', '#__minitek_faqbook_topics AS c ON c.id = a.topicid');

		// Join over the sections
		$query->select('section.title as section_title, section.id as section_id')
			->join('LEFT', '#__minitek_faqbook_sections as section ON section.id = c.section_id');

		// Filter by topic language
		$query->where('c.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');

		// Filter by access level
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where('a.access IN (' . $groups . ')')
			->where('c.access IN (' . $groups . ')')
			->where('section.access IN (' . $groups . ')');

		// Filter by start and end dates.
		$nullDate = $db->quote($db->getNullDate());
		$date = Factory::getDate();
		$nowDate = $db->quote($date->toSql());

		// Filter by state
		$editStateAuthorizedTopics = UtilitiesHelper::getAuthorisedTopics('core.edit.state');

		if (count($editStateAuthorizedTopics))
		{
			$editStateAuthorizedTopics = implode(',', $editStateAuthorizedTopics);
			$query->where('((a.state = 1 AND (a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ') AND (a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . '))
				OR (a.state IN (-2,0,1,2) AND a.topicid IN (' . $editStateAuthorizedTopics . ')))');
		}
		else
		{
			if ($user->id)
			{
				$query->where('((a.state = 1 AND (a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ') AND (a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . '))
					OR (a.state IN (-2,0,1,2) AND a.created_by = ' . $db->quote($user->id).'))');
			}
			else
			{
				$query->where('a.state = 1');
				$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ') AND (a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
			}
		}

		// Filter by topic state
		$query->where('c.published = 1');

		// Filter by private
		if ($user->id)
		{
			$authorizedTopics = UtilitiesHelper::getAuthorisedTopics('core.private.see');

			if (count($authorizedTopics))
			{
				$authorizedTopics = implode(',', $authorizedTopics);
				$query->where('(a.private = 0 OR (a.private = 1 AND a.created_by = ' . $db->quote($user->id).') OR (a.private = 1 AND a.topicid IN (' . $authorizedTopics . ')))');
			}
			else
			{
				$query->where('(a.private = 0 OR (a.private = 1 AND a.created_by = ' . $db->quote($user->id).'))');
			}
		}
		else
		{
			$query->where('a.private = 0');
		}

		// Filter by section
		$query->where('section.id = ' . $db->quote($sectionId));

		// Filter by featured
		if ($ordering == 'featured')
		{
			$query->where('a.featured = 1');
		}

		// Filter by unanswered
		if ($ordering == 'unanswered')
		{
			$query->having('answers = 0');
		}

		// Filter by resolved
		if ($ordering == 'resolved')
		{
			$query->where('a.resolved = 1');
		}

		// Filter by unresolved
		if ($ordering == 'unresolved')
		{
			$query->where('a.resolved IN (0,2)');
		}

		// Filter by open
		if ($ordering == 'open')
		{
			$query->where('a.resolved = '.$db->quote(0));
		}

		// Filter by pending
		if ($ordering == 'pending')
		{
			$query->where('a.resolved = 2');
		}

		// Get ordering
		switch ($ordering)
		{
			case 'recent':
				$order = 'a.pinned '.$ordering_dir.', a.created '.$ordering_dir.'';
				break;
			case 'top':
				$order = 'diff '.$ordering_dir.', resolved '.$ordering_dir.', answers '.$ordering_dir.', a.created '.$ordering_dir.'';
				break;
			case 'featured':
			case 'unanswered':
			case 'unresolved':
			case 'open':
			case 'pending':
			case 'resolved':
				$order = 'a.created '.$ordering_dir.'';
				break;
			// Static ordering
			default:
				$order = 'a.pinned DESC, a.'.$ordering.' '.$ordering_dir.'';
		}

		$query->order($order);

		// Page limit
		jimport( 'joomla.application.component.helper' );
		$params  = ComponentHelper::getParams('com_faqbookpro');
		$limitstart = $params->get('pagination_limit', 20);
		$db->setQuery($query, ($page - 1) * $limitstart, $limitstart + 1); // get 1 extra item to see if we need pagination

		// Get the results
		$questions = $db->loadObjectList();

		return $questions;
	}

	public function getPopularTopics($sectionId, $limit)
	{
		$db = Factory::getDbo();
		$user = Factory::getUser();

		$query = $db->getQuery(true);

		// Right join with c for topic
		$query->select('c.id, c.access, c.alias, c.created_time, c.created_user_id, c.params,
			c.description, c.hits, c.language, c.level, c.lft, c.parent_id, c.section_id,
			c.path, c.published, c.rgt, c.title');
		$case_when = ' CASE WHEN ';
		$case_when .= $query->charLength('c.alias', '!=', '0');
		$case_when .= ' THEN ';
		$c_id = $query->castAsChar('c.id');
		$case_when .= $query->concatenate(array($c_id, 'c.alias'), ':');
		$case_when .= ' ELSE ';
		$case_when .= $c_id . ' END as slug';
		$query->select($case_when)
			->from('#__minitek_faqbook_topics as c');
		$query->where('c.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
		$query->where('c.published = 1');
		$query->where('c.section_id=' . (int) $sectionId);

		// Filter by language
		$query->where('c.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');

		$query->order('c.hits DESC');
		$query->setLimit($limit);

		// Get the results
		$db->setQuery($query);
		$topics = $db->loadObjectList();

		return $topics;
	}

	public function getPopularQuestions($sectionIid, $limit)
	{
		$db = Factory::getDbo();
		$user = Factory::getUser();

		$query = $db->getQuery(true);

		$query->select('a.id, a.title, a.alias, a.content,
			a.topicid, a.created, a.created_by, ' .
			// Use created if modified is 0
			'CASE WHEN a.modified = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.modified END as modified, ' .
			'a.modified_by,' .
			// Use created if publish_up is 0
			'CASE WHEN a.publish_up = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.publish_up END as publish_up,' .
			'a.publish_down, a.access, a.hits, a.featured'
		);
		$query->from('#__minitek_faqbook_questions AS a');

		// Join over the topics
		$query->select('c.title AS topic_title, c.path AS topic_route, c.access AS topic_access, c.alias AS topic_alias')
			->join('LEFT', '#__minitek_faqbook_topics AS c ON c.id = a.topicid');

		// Join over the sections
		$query->select('section.title as section_title, section.id as section_id')
			->join('LEFT', '#__minitek_faqbook_sections as section ON section.id = c.section_id');

		// Group by
		$groupBy = array(
			'a.id',
			'a.title',
			'a.alias',
			'a.content',
			'a.topicid',
			'a.state',
			'a.access',
			'a.created',
			'a.created_by',
			'a.created_by_alias',
			'a.created_by_email',
			'a.modified',
			'a.modified_by',
			'a.images',
			'a.attribs',
			'a.metadata',
			'a.metakey',
			'a.metadesc',
			'a.ordering',
			'a.featured',
			'a.language',
			'a.hits',
			'a.publish_up',
			'a.publish_down',
			'c.title',
			'c.path',
			'c.access',
			'c.alias',
			'section.title',
			'section.id',
		);

		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where('a.access IN (' . $groups . ')')
			->where('c.access IN (' . $groups . ')')
			->where('section.access IN (' . $groups . ')');

		$query->where('a.state = 1')
			->where('c.published = 1')
			->where('section.state = 1');

		// Don't show private questions
		$query->where('a.private = 0');

		// Filter by start and end dates.
		$nullDate = $db->quote($db->getNullDate());
		$date = Factory::getDate();
		$nowDate = $db->quote($date->toSql());

		$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
			->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');

		// Filter by topic language
		$query->where('c.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');

		$query->where('section.id = ' . $db->quote($sectionIid));
		$query->order('a.hits DESC, a.title ASC');
		$query->setLimit($limit);

		// Get the results
		$db->setQuery($query);
		$questions = $db->loadObjectList();

		return $questions;
	}

	public function getSectionMenuItem($sectionid)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__menu')
			->where('published = 1')
			->where('link='.$db->quote('index.php?option=com_faqbookpro&view=section&id='.$sectionid));
		$db->setQuery($query);
		$count = $db->loadResult();

		return $count;
	}
}
