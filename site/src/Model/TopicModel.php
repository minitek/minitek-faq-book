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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\GenericDataException;

/**
 * FAQ Book Component Topic Model
 *
 * @since  4.0.0
 */
class TopicModel extends BaseDatabaseModel
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
		$this->setState('topic.id', $pk);

		$user = Factory::getUser();

		// If $pk is set then authorise on complete asset, else on component only
		$asset = empty($pk) ? 'com_faqbookpro' : 'com_faqbookpro.topic.' . $pk;

		if ((!$user->authorise('core.edit.state', $asset)) && (!$user->authorise('core.edit', $asset)))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}

		$this->setState('filter.language', Multilanguage::isEnabled());
	}

	/**
	 * Method to get topic data.
	 *
	 * @param   integer  $pk  The id of the topic.
	 *
	 * @return  object|boolean|JException  Menu item data object on success, boolean false or JException instance on error
	 */
	public function getItem($pk = null)
	{
		$user = Factory::getUser();

		$pk = (!empty($pk)) ? $pk : (int) $this->getState('topic.id');

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
				$query->from('#__minitek_faqbook_topics AS a')
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
					$query->where('(a.published = ' . (int) $published . ' OR a.published =' . (int) $archived . ')');
				}

				$db->setQuery($query);

				$data = $db->loadObject();

				if (empty($data))
				{
					throw new GenericDataException(Text::_('COM_FAQBOOKPRO_ERROR_TOPIC_NOT_FOUND'), 404);
				}

				// Check for published state if filter set.
				if ((is_numeric($published) || is_numeric($archived)) && (($data->published != $published) && ($data->published != $archived)))
				{
					throw new GenericDataException(Text::_('COM_FAQBOOKPRO_ERROR_TOPIC_NOT_FOUND'), 404);
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
					throw new GenericDataException($e->getMessage(), 404);
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

	public function getTopicQuestions($topicId, $ordering, $ordering_dir, $page = 1, $merge)
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

		// Join over the votes for the question.
		$query->select('COUNT(DISTINCT vu.id) as votes_up, COUNT(DISTINCT vd.id) as votes_down, (COUNT(DISTINCT vu.id) - COUNT(DISTINCT vd.id)) as diff')
			->join('LEFT', '#__minitek_faqbook_votes AS vu ON vu.target_id = a.id AND vu.vote_up=1 AND vu.target_type="question"')
			->join('LEFT', '#__minitek_faqbook_votes AS vd ON vd.target_id = a.id AND vd.vote_down=1 AND vd.target_type="question"')
			->group('a.id');

		// Filter by access level
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where('a.access IN (' . $groups . ')')
			->where('c.access IN (' . $groups . ')');

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

		if ($merge)
		{
			// Filter by topic (including children topics)
			$topic_tbl = Table::getInstance('TopicTable', 'Joomla\Component\FAQBookPro\Administrator\Table\\');
			$topic_tbl->load($topicId);
			$rgt = $topic_tbl->rgt;
			$lft = $topic_tbl->lft;
			$baselevel = (int) $topic_tbl->level;
			$query->where('c.lft >= ' . (int) $lft)
				->where('c.rgt <= ' . (int) $rgt);
		}
		else
		{
			// Filter by topic (excluding children topics)
			$query->where('a.topicid = '.$db->quote($topicId).'');
		}

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

	public static function getQuestionVotes($questionId, $type)
	{
  	$db = Factory::getDBO();
  	$query = "SELECT COUNT(*) FROM "
      .$db->quoteName("#__minitek_faqbook_votes")
      ." WHERE " . $db->quoteName("target_id") . "=" . $db->Quote($questionId)
			." AND " . $db->quoteName("target_type") . "='question'"
      ." AND " . $db->quoteName($type) . "=" . $db->Quote('1');
		$db->setQuery($query);
		$vote_sum = $db->loadResult();

		return $vote_sum;
	}

	public static function addHit($id)
	{
    $db = Factory::getDBO();
		$query = " UPDATE `#__minitek_faqbook_topics` "
			." SET hits = hits + 1 "
			." WHERE id = ".$db->Quote($id)." ";
  	$db->setQuery($query);
  	$db->execute();
	}

	public function getAllTopics()
	{
		$app = Factory::getApplication();
		$db = Factory::getDBO();
		$user = Factory::getUser();
		$query = "SELECT id, title, parent_id	FROM #__minitek_faqbook_topics";

		if ($app->isSite())
		{
			$query .= " WHERE published=1 AND level>0 ";
			$query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).")";

			if ($app->getLanguageFilter())
			{
				$query .= " AND language IN(".$db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*').")";
			}
		}

		$query .= " ORDER BY parent_id ";
		$db->setQuery($query);
		$topics = $db->loadObjectList();
		$tree = array();

		return $this->buildTree($topics);
	}

	public function buildTree(array &$topics, $parent = 1)
	{
		$branch = array();

		foreach ($topics as &$topic)
		{
			if ($topic->parent_id == $parent)
			{
				$children = $this->buildTree($topics, $topic->id);

				if ($children)
				{
					$topic->children = $children;
				}

				$branch[$topic->id] = $topic;
			}
		}

		return $branch;
	}

	public function getTreePath($tree, $id)
	{
		if (array_key_exists($id, $tree))
		{
			return array($id);
		}
		else
		{
			foreach ($tree as $key => $root)
			{
				if (isset($root->children) && is_array($root->children))
				{
					$retry = $this->getTreePath($root->children, $id);

					if ($retry)
					{
						$retry[] = $key;
						return $retry;
					}
				}
			}
		}

		return null;
	}

	/* Get first-level children topics in topic */
	public static function getTopicChildren($topicId)
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
		$query->where('c.parent_id=' . (int) $topicId);
		$query->order('c.lft');

		// Get the results
		$db->setQuery($query);
		$topics = $db->loadObjectList('id');

		return $topics;
	}

	public function getTopicParentTopics($id, $topics)
	{
		$db = Factory::getDBO();
		$query = 'SELECT * FROM '. $db->quoteName( '#__minitek_faqbook_topics' );
		$query .= ' WHERE ' . $db->quoteName( 'id' ) . ' = '. $db->quote($id).' ';
		$db->setQuery($query);
		$row = $db->loadObject();

		if ($row)
		{
			if ($row->parent_id > 1)
			{
				$topics[] = $row->parent_id;
				$topics = self::getTopicParentTopics($row->parent_id, $topics);
			}
		}

		return $topics;
	}
}
