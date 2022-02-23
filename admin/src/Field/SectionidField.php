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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
\JFormHelper::loadFieldClass('list');

class SectionIdField extends \JFormFieldList
{
  protected $type = 'SectionId';

  protected function getOptions()
  {
    $db = Factory::getDBO();
    $app = Factory::getApplication();

    $query = $db->getQuery(true)
      ->select('s.id as value, s.title as text, s.alias as alias')
      ->from($db->quoteName('#__minitek_faqbook_sections').' AS s')
      ->where($db->quoteName('state').' = ' . $db->quote(1));

    if ($app->isClient('site'))
    {
      $query->where($db->quoteName('language').' IN('.$db->quote(Factory::getLanguage()->getTag()).', '.$db->quote('*').')');
    }

    $query->order('title ASC');

    $db->setQuery($query);

    $sections = $db->loadObjectList();
    $options = array();

    foreach ($sections as $section)
    {
      $options[] = HTMLHelper::_('select.option', $section->value, $section->text.' ['.$section->alias.']');
    }

    $options = array_merge(parent::getOptions(), $options);

    return $options;
  }
}
