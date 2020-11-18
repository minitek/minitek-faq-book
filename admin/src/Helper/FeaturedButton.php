<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Administrator\Helper;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Button\ActionButton;

/**
 * The FeaturedButton class.
 *
 * @since  4.0.0
 */
class FeaturedButton extends ActionButton
{
	/**
	 * Configure this object.
	 *
	 * @return  void
	 *
	 * @since  4.0.0
	 */
	protected function preprocess()
	{
		$this->addState(0, 'questions.featured', 'color-unfeatured far fa-star',
			Text::_('COM_FAQBOOKPRO_UNFEATURED'), ['tip_title' => Text::_('JGLOBAL_TOGGLE_FEATURED')]
		);
		
		$this->addState(1, 'questions.unfeatured', 'color-featured fas fa-star',
			Text::_('COM_FAQBOOKPRO_FEATURED'), ['tip_title' => Text::_('JGLOBAL_TOGGLE_FEATURED')]
		);
	}
}
