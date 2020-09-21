<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Administrator\Field;

defined('_JEXEC') or die;

\JFormHelper::loadFieldClass('list');

class SectionIdField extends \JFormFieldList
{
	protected $type = 'SectionId';

  protected function getOptions()
  {
    $db = \JFactory::getDBO();
    $query = 'SELECT s.id as value, s.title as text, s.alias as alias FROM #__minitek_faqbook_sections s WHERE state = 1 ORDER BY s.title';
    $db->setQuery($query);
    $sections = $db->loadObjectList();
		$options = array();

    foreach ($sections as $section)
    {
      $options[] = \JHTML::_('select.option', $section->value, $section->text.' ['.$section->alias.']');
    }

		$options = array_merge(parent::getOptions(), $options);

		return $options;
  }
}
