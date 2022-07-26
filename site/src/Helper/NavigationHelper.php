<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2021 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\FAQBookPro\Site\Model\TopicModel;
use Joomla\CMS\Table\Table;
use Joomla\Component\FAQBookPro\Site\Helper\RouteHelper;
use Joomla\Registry\Registry;

/**
 * FAQ Book Component Navigation Helper.
 *
 * @since  4.0.0
 */
abstract class NavigationHelper
{
	public static function getTopicsSlider($item, $show_icons, $level = 1)
	{
		$topicModel = new TopicModel;
		$topicParams = new Registry($item->params);
  		$html = '';
		$subitems = $topicModel->getTopicChildren($item->id);

		if (count($subitems))
		{
			$depth = self::getTopicDepth($item, $levels = 0);

			if ($depth === 0)
			{
		  		$item_class = 'NavLeftUL_endpoint';
				$icon = '<div class="NavLeftUL_endpointIcon">'.
				'<div class="spinner-border spinner-border-sm" role="status">'.
				'<span class="visually-hidden">Loading...</span>'.
				'</div>'.
			  	'</div>';
			}
			else
			{
		  		$item_class = 'NavLeftUL_parent';
				$icon = '<span class="NavLeftUL_navIcon fas fa-chevron-right"></span>';
			}

			$html .= '<li id="liid'.$item->id.'" class="NavLeftUL_item NavLeftUL_topic '.$item_class.'">';
			$html .= '<a href="'.Route::_(RouteHelper::getTopicRoute($item->id, false, false, $item->language)).'" class="NavLeftUL_anchor" onclick="return false;">';
			$html .= '<span class="topicTitle">';

			if ($show_icons && $topicParams->get('topic_icon_class', ''))
			{
				$html .= '<i class="NavLeftUL_topicIcon '.$topicParams->get('topic_icon_class', '').'"></i>';
			}

			$html .= $item->title;
			$html .= '</span>';
			$html .= $icon;
			$html .= '</a>';
			$html .= '<ul class="NavLeftUL_sublist level'.$level.'">';

			foreach ($subitems as $subitem)
			{
		  		$html .= self::getTopicsSlider($subitem, $show_icons, $level + 1);
			}

			$back_href = $item->parent_id > 1 
				? Route::_(RouteHelper::getTopicRoute($item->parent_id)) 
				: Route::_(RouteHelper::getSectionRoute($item->section_id));

			if ($item->parent_id > 1 )
			{
				$parent = Table::getInstance('TopicTable', 'Joomla\Component\FAQBookPro\Administrator\Table\\');
				$parent->load($item->parent_id);
				$data_title = $parent->title;
			}
			else 
			{
				$section = Table::getInstance('SectionTable', 'Joomla\Component\FAQBookPro\Administrator\Table\\');
				$section->load($item->section_id);
				$data_title = $section->title;
			}
			
			$html .= '<li data-parent="'.$item->parent_id.'" class="NavLeftUL_backItem">';
			$html .= '<a href="'.$back_href.'" class="NavLeftUL_anchor" rel="nofollow" onclick="return false;" data-title="'.$data_title.'">';
			$html .= '<span>'.Text::_('COM_FAQBOOKPRO_BACK').'<span class="NavLeftUL_navBackIcon fas fa-arrow-left"></span></span>';
			$html .= '</a></li>';
			$html .= '</ul>';
			$html .= '</li>';
		}
		else
		{
	  		$html .= '<li id="liid'.$item->id.'" class="NavLeftUL_item NavLeftUL_topic NavLeftUL_endpoint">';
			$html .= '<a href="'.Route::_(RouteHelper::getTopicRoute($item->id, false, false, $item->language)).'" class="NavLeftUL_anchor" onclick="return false;">';
			$html .= '<span class="topicTitle">';

			if ($show_icons && $topicParams->get('topic_icon_class', ''))
			{
				$html .= '<i class="NavLeftUL_topicIcon '.$topicParams->get('topic_icon_class', '').'"></i>';
			}

			$icon = '<div class="NavLeftUL_endpointIcon">'.
				'<div class="spinner-border spinner-border-sm" role="status">'.
				'<span class="visually-hidden">Loading...</span>'.
				'</div>'.
				'</div>';

			$html .= $item->title;
			$html .= '</span>';
			$html .= $icon;
			$html .= '</a>';
			$html .= '</li>';
		}

		return $html;
	}

	public static function getTopicDepth($item, $levels)
	{
		$topicModel = new TopicModel;
		$children = $topicModel->getTopicChildren($item->id);

		if (count($children))
		{
	  		$levels++;

	  		foreach ($children as $child)
			{
				if (count($topicModel->getTopicChildren($child->id)))
				{
					return;
				}
			}
		}

		return $levels;
	}
}
