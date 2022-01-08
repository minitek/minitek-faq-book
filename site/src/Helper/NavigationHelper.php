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
use Joomla\Component\FAQBookPro\Site\Model\SectionModel;
use Joomla\Component\FAQBookPro\Site\Helper\RouteHelper;

/**
 * FAQ Book Component Navigation Helper.
 *
 * @since  4.0.0
 */
abstract class NavigationHelper
{
	public static function getTopicsTree($item, $level = 1)
	{
		$topicModel = new TopicModel;
		$sectionModel = new SectionModel;
		$topicParams = json_decode($item->params, false);
  		$output = '';
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
		  		$item_class = '';
				$icon = '<span class="NavLeftUL_navIcon fas fa-chevron-right"></span>';
			}

			$output .= '<li id="liid'.$item->id.'" class="NavLeftUL_item NavLeftUL_topic '.$item_class.'">';
			$output .= '<a href="'.Route::_(RouteHelper::getTopicRoute($item->id)).'" class="NavLeftUL_anchor" onclick="return false;">';
			$output .= '<span class="topicTitle">';

			if (isset($topicParams->topic_icon_class) && $topicParams->topic_icon_class)
			{
				$output .= '<i class="NavLeftUL_topicIcon fas fa-'.$topicParams->topic_icon_class.'"></i>';
			}

			$output .= $item->title;
			$output .= '</span>';
			$output .= $icon;
			$output .= '</a>';
			$output .= '<ul class="NavLeftUL_sublist level'.$level.'">';

			foreach ($subitems as $subitem)
			{
		  		$output .= self::getTopicsTree($subitem, $level + 1);
			}

			$back_href = $item->parent_id > 1 
				? Route::_(RouteHelper::getTopicRoute($item->parent_id)) 
				: Route::_(RouteHelper::getSectionRoute($item->section_id));
			$data_title = $item->parent_id > 1 
				? $topicModel->getItem($item->parent_id)->title 
				: $sectionModel->getItem($item->section_id)->title;
			
			$output .= '<li data-parent="'.$item->parent_id.'" class="NavLeftUL_backItem">';
			$output .= '<a href="'.$back_href.'" class="NavLeftUL_anchor" rel="nofollow" onclick="return false;" data-title="'.$data_title.'">';
			$output .= '<span>'.Text::_('COM_FAQBOOKPRO_BACK').'<span class="NavLeftUL_navBackIcon fas fa-arrow-left"></span></span>';
			$output .= '</a></li>';
			$output .= '</ul>';
			$output .= '</li>';
		}
		else
		{
	  		$output .= '<li id="liid'.$item->id.'" class="NavLeftUL_item NavLeftUL_topic NavLeftUL_endpoint">';
			$output .= '<a href="'.Route::_(RouteHelper::getTopicRoute($item->id)).'" class="NavLeftUL_anchor" onclick="return false;">';
			$output .= '<span class="topicTitle">';

			if (isset($topicParams->topic_icon_class) && $topicParams->topic_icon_class)
			{
				$output .= '<i class="NavLeftUL_topicIcon fas fa-'.$topicParams->topic_icon_class.'"></i>';
			}

			$icon = '<div class="NavLeftUL_endpointIcon">'.
				'<div class="spinner-border spinner-border-sm" role="status">'.
				'<span class="visually-hidden">Loading...</span>'.
				'</div>'.
				'</div>';

			$output .= $item->title;
			$output .= '</span>';
			$output .= $icon;
			$output .= '</a>';
			$output .= '</li>';
		}

		return $output;
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
