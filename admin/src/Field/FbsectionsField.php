<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2021 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Administrator\Field;

defined('_JEXEC') or die;

\JFormHelper::loadFieldClass('list');

class FBSectionsField extends \JFormFieldList
{
	protected $type = 'FBSections';

	protected function getInput()
	{
		$db = \JFactory::getDbo();

		// Build the query.
		$query = $db->getQuery(true)
			->select('s.id, CONCAT(s.title, " [", s.alias, "]") AS title')
			->from($db->quoteName('#__minitek_faqbook_sections') . ' AS s')
			->where('s.state = 1')
			->order('s.title');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		$output = \JHTML::_('select.genericlist', $options, $this->name.'[]', 'class="form-select" multiple="multiple" size="10"', 'id', 'title', $this->value, $this->id);

		return $output;
	}
}
