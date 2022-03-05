<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Component\FAQBookPro\Site\Helper\UtilitiesHelper;
use Joomla\CMS\Table\Table;
use Joomla\Component\FAQBookPro\Site\Helper\NavigationHelper;
use Joomla\Component\FAQBookPro\Site\Model\SectionModel;
use Joomla\Component\FAQBookPro\Site\Helper\RouteHelper;
use Joomla\Registry\Registry;

$sectionId = $displayData['sectionId'];
$visible = $displayData['visible'];
$section = Table::getInstance('SectionTable', 'Joomla\Component\FAQBookPro\Administrator\Table\\');
$section->load($sectionId);
$sectionParams = new Registry($section->attribs);
$show_icons = $sectionParams->get('leftnav_icons', 1);
$user = Factory::getUser();
$userid = $user->id;
$sectionModel = new SectionModel;
$items = $sectionModel->getSectionTopics($sectionId);
$app = Factory::getApplication();

if ($items)
{
  foreach ($items as $item)
  {
    $topicsTree = NavigationHelper::getTopicsTree($item, $show_icons);
    $topics_tree[] = $topicsTree;
  }
}

// Is the left nav minimized?
$is_minimized = $app->getUserState( 'com_faqbookpro.minimized_leftnav', false );
$fb_minimized = '';
$minimized_icon = '<a class="NavLeftUL_toggle" href="#"><i class="fas fa-angle-double-left"></i></a>';
$section_title_html = '';
$visible_class = $visible ? '' : 'leftnav-hidden';

if ((!$is_minimized && $sectionParams->get('leftnav', 1) == 2) || $is_minimized == 'on')
{
  $fb_minimized = 'fb-minimized';
  $minimized_icon = '<a class="NavLeftUL_toggle" href="#"><i class="fas fa-angle-double-right"></i></a>';
}

?><div class="fbLeftNavigation_core fb-hidden <?php echo $fb_minimized; ?> <?php echo $visible_class; ?>"><?php

  // Toggle left navigation
  ?><div class="fbLeftNavigation_toggle">
    <ul class="NavLeftUL_parent">
      <li class="NavLeftUL_item"><?php
        echo $minimized_icon;
      ?></li>
    </ul>
  </div><?php

  // Module position fb-nav-top
  $modules_top = ModuleHelper::getModules('fb-nav-top');

  if (count($modules_top))
  {
    ?><div class="fbLeftNavigation_top"><?php
      foreach ($modules_top as $module_top)
      {
        echo ModuleHelper::renderModule($module_top);
      }
    ?></div><?php
  }

  ?><div class="fbLeftNavigation_topics">
    <ul class="NavLeftUL_parent"><?php

      // Browse Topics
      if ($sectionParams->get('browse_topics', 1) && isset($topics_tree) && $topics_tree)
      {
        $browse_topics_class = '';

        if ($app->input->get('view', '') == 'section' && $app->input->get('tab', '') == 'topics')
        {
          $browse_topics_class = 'li_selected';
        }

        ?><li class="NavLeftUL_item NavLeftUL_tab <?php echo $browse_topics_class; ?>" data-tab="topics" data-text="<?php echo Text::_('COM_FAQBOOKPRO_BROWSE_TOPICS'); ?>">
          <a href="<?php echo Route::_(RouteHelper::getSectionRoute($sectionId, 'topics')); ?>" id="NavLeftUL_browseTopics" class="NavLeftUL_anchor">
            <i class="fas fa-folder-open"></i><?php
            ?><span class="NavLeftUL_text"><?php echo Text::_('COM_FAQBOOKPRO_BROWSE_TOPICS'); ?></span>
            <div class="NavLeftUL_endpointIcon">
				      <div class="spinner-border spinner-border-sm" role="status">
				        <span class="visually-hidden">Loading...</span>
				      </div>
			  	  </div>
          </a>
        </li><?php
      }

    ?></ul>

    <div class="fbLeftNavigation_wrap">
      <ul id="NavLeftUL" class="NavLeftUL_parent level0"><?php
        // Topics
        if (isset($topics_tree) && $topics_tree)
        {
          foreach ($topics_tree as $topic_tree)
          {
            echo $topic_tree;
          }
        }
      ?></ul>
    </div>
  </div><?php

  // Module position fb-nav-bottom
  $modules_bottom = ModuleHelper::getModules('fb-nav-bottom');

  if (count($modules_bottom))
  {
    ?><div class="fbLeftNavigation_bottom"><?php
      foreach ($modules_bottom as $module_bottom)
      {
        echo ModuleHelper::renderModule($module_bottom);
      }
    ?></div><?php
  }

?></div>
