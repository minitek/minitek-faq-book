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

class SeparatorField extends \JFormField
{
	protected $type = 'Separator';

	protected function getLabel()
	{
		return '';
	}

	protected function getInput()
	{
		$text  	= (string) $this->element['text'];

		return '<div id="'.$this->id.'" class="mmSeparator'.(($text != '') ? ' hasText' : '').'" title="'. \JText::_($this->element['desc']) .'"><span>' . \JText::_($text) . '</span></div>';
	}
}
