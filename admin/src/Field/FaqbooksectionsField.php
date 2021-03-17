<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Administrator\Field;

defined('_JEXEC') or die;

\JFormHelper::loadFieldClass('list');

class FAQBookSectionsField extends \JFormFieldList
{
	protected $type = 'FAQBookSections';

	protected function getOptions()
	{
		$options = array();
		$topicLevel = $this->form->getValue('level');

		if ($topicLevel > 1)
		{
			\Joomla\CMS\Factory::getApplication()->getDocument()->addScriptDeclaration('
			(function() {
				document.addEventListener(\'DOMContentLoaded\', function() {
					var jform_section_id = document.querySelector(\'#jform_section_id\');
					var current_section = jform_section_id.options[jform_section_id.selectedIndex].text;
					jform_section_id.classList.add(\'disabled\');
					jform_section_id.style.display = \'none\';
					
					var section_id_duplicate = document.querySelector(\'#section_id_duplicate\');
					section_id_duplicate.style.display = \'block\';
					var jform_section_id_duplicate = document.querySelector(\'#jform_section_id_duplicate\');
					jform_section_id_duplicate.value = current_section;
				});
			})();
			');
		}

		$db = \JFactory::getDBO();
		$query = $db->getQuery(true)
			->select('s.id as value, s.title as text, s.alias as alias')
			->from($db->quoteName('#__minitek_faqbook_sections') . ' AS s')
			->where('s.state = 1');

		// Filter by sections - Questions menu item
		$app = \JFactory::getApplication();

		if ($app->isClient('site'))
		{
			$params = $app->getParams('com_faqbookpro');

			if ($sections = $params->get('filter_sections', []))
			{
				\JArrayHelper::toInteger($sections);
				$sections = implode(',', $sections);

				$query->where('s.id IN ('.$sections.')');
			}
		}

		$query->order('s.title');
		$db->setQuery($query);
		$sections = $db->loadObjectList();

		foreach ($sections as $section)
		{
			$options[] = \JHTML::_('select.option', $section->value, $section->text.' ['.$section->alias.']');
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
  	}
}
