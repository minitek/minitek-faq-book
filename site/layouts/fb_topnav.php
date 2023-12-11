<?php

/**
 * @title        Minitek FAQ Book
 * @copyright    Copyright (C) 2011-2023 Minitek, All rights reserved.
 * @license      GNU General Public License version 3 or later.
 * @author url	 https://www.minitek.gr/
 * @developers	 Minitek.gr
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\FAQBookPro\Site\Helper\UtilitiesHelper;
use Joomla\CMS\Table\Table;
use Joomla\Component\FAQBookPro\Site\Helper\RouteHelper;
use Joomla\Registry\Registry;

$sectionId = $displayData['sectionId'];
$params = UtilitiesHelper::getParams('com_faqbookpro');
$user = Factory::getUser();
$app = Factory::getApplication();
$view = $app->input->get('view', '');
$tab = $app->input->get('tab', '');

if ($sectionId) {
    $section = Table::getInstance('SectionTable', 'Joomla\Component\FAQBookPro\Administrator\Table\\');
    $section->load($sectionId);
    $sectionTitle = $section->title;
    $sectionParams = new Registry($section->attribs);
    $menu = $app->getMenu();
    $activeMenu = $menu->getActive();
    $home_itemid = $sectionParams->get('topnav_root', '') ? $sectionParams->get('topnav_root', '') : $activeMenu->id;
    $home_menuitem = $menu->getItem($home_itemid);
    $home_title = $home_menuitem->title;
}

if ($view == 'section' && $tab != 'topics')
    $class = 'NavTopUL_lastChild';
else
    $class = ''; ?>
<div class="fbTopNavigation_core_outer">
    <div class="fbTopNavigation_core clearfix">
        <div class="fbTopNavigation_wrap">
            <ul class="fbTopNavigation_root fb-hidden-phone">
                <?php
                // Sections link
                if (($view != 'profile' || $sectionId) && $sectionParams->get('topnav_sections', 0)) { ?>
                    <li class="NavTopUL_home">
                        <a class="NavTopUL_link NavTopUL_sections" href="<?php echo Route::_(RouteHelper::getSectionsRoute($home_itemid)); ?>">
                            <i class="fas fa-home NavTopUL_homeIcon"></i><?php echo $home_title; ?>
                        </a>
                    </li>
                <?php }

                // Section link
                if ($sectionId) { ?>
                    <li id="top_liid_home" class="NavTopUL_item NavTopUL_section NavTopUL_firstChild <?php echo $class; ?>">
                        <a class="NavTopUL_link" href="<?php echo Route::_(RouteHelper::getSectionRoute($sectionId, false, false, $section->language)); ?>" onclick="return false;">
                            <?php if ($sectionParams->get('topnav_sections', 0)) { ?>
                                <i class="fas fa-caret-right NavTopUL_homeIcon"></i>
                            <?php echo $sectionTitle;
                            } else { ?>
                                <i class="fas fa-home NavTopUL_homeIcon"></i>
                            <?php echo $sectionTitle;
                            } ?>
                        </a>
                    </li>
                <?php }

                // Browse Topics
                if ($view == 'section' && $tab == 'topics') { ?>
                    <li class="NavTopUL_item NavTopUL_topics NavTopUL_lastChild">
                        <a class="NavTopUL_link" href="#" onclick="return false;">
                            <i class="fas fa-caret-right"></i>
                            <?php echo Text::_('COM_FAQBOOKPRO_BROWSE_TOPICS'); ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
            <div class="NavTopUL_loading">
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>

        <div class="NavTopUL_buttons">
            <?php
            // Menu button
            if ($view != 'profile' && ($sectionParams->get('leftnav', 1) || !$sectionId)) { ?>
                <a href="#" onclick="return false;" class="show_menu btn btn-outline-primary btn-mfb"><i class="fas fa-bars"></i></a>
            <?php } ?>
        </div>
    </div>
    <div class="clearfix"> </div>
</div>