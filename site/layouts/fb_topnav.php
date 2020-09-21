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
use Joomla\Component\FAQBookPro\Site\Helper\UtilitiesHelper;
use Joomla\Component\FAQBookPro\Site\Model\SectionModel;
use Joomla\Component\FAQBookPro\Site\Helper\RouteHelper;

$sectionId = $displayData['sectionId'];
$params = UtilitiesHelper::getParams('com_faqbookpro');
$user = Factory::getUser();
$app = Factory::getApplication();
$view = $app->input->get('view', '');
$tab = $app->input->get('tab', '');

if ($sectionId)
{
  $sectionModel = new SectionModel;
  $section = $sectionModel->getItem($sectionId);
  $sectionTitle = $section->title;
  $sectionParams = json_decode($section->attribs, false);
  $menu = $app->getMenu();
  $activeMenu = $menu->getActive();
  $topnav_sections = isset($sectionParams->topnav_sections) ? $sectionParams->topnav_sections : false;
  $home_itemid = isset($sectionParams->topnav_root) ? $sectionParams->topnav_root : $activeMenu->id;
  $home_menuitem = $menu->getItem($home_itemid);
  $home_title = $home_menuitem->title;
}

if ($view == 'section' && $tab != 'topics')
{
  $class = 'NavTopUL_lastChild';
}
else
{
  $class = '';
}

?><div class="fbTopNavigation_core_outer">
  <div class="fbTopNavigation_core">
    <div class="fbTopNavigation_wrap">
      <ul class="fbTopNavigation_root fb-hidden-phone"><?php

        // Sections link
        if (($view != 'profile' || $sectionId) && $topnav_sections)
        {
          ?><li class="NavTopUL_home"><?php
            ?><a class="NavTopUL_link" href="<?php echo Route::_(RouteHelper::getSectionsRoute($home_itemid)); ?>">
              <i class="fas fa-home NavTopUL_homeIcon"></i>&nbsp;&nbsp;<?php
              echo $home_title;
            ?></a></li><?php
        }

        // Section link
        if ($sectionId)
        {
          ?><li id="top_liid_home" class="NavTopUL_item NavTopUL_section NavTopUL_firstChild <?php echo $class; ?>"><?php
            ?><a class="NavTopUL_link" href="<?php echo Route::_(RouteHelper::getSectionRoute($sectionId)); ?>" onclick="return false;"><?php
              if ($topnav_sections)
              {
                ?><i class="fas fa-caret-right NavTopUL_homeIcon"></i>&nbsp;&nbsp;<?php
              }
              else
              {
                ?><i class="fas fa-home NavTopUL_homeIcon"></i>&nbsp;&nbsp;<?php
              }
              echo $sectionTitle;
            ?></a></li><?php
        }

        // Browser Topics
        if ($view == 'section' && $tab == 'topics')
        {
          ?><li class="NavTopUL_item NavTopUL_topics NavTopUL_lastChild"><?php
            ?><a class="NavTopUL_link" href="#" onclick="return false;"><?php
              ?><i class="fas fa-caret-right"></i>&nbsp;&nbsp;<?php
              echo Text::_('COM_FAQBOOKPRO_BROWSE_TOPICS');
            ?></a></li><?php
        }

      ?></ul>
      <span class="NavTopUL_loading"></span>
    </div>

    <div class="NavTopUL_buttons"><?php

      // Menu icon
      if ((isset($sectionParams->leftnav) && $sectionParams->leftnav) || !$sectionId)
      {
        ?><div class="show_menu"><a href="#" onclick="return false;" class="btn btn-default"><i class="fas fa-bars"></i></a></div><?php
      }

    ?></div>
  </div>
  <div class="clearfix"> </div>
</div>
