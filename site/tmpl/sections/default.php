<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\FAQBookPro\Site\Helper\RouteHelper;

?><div id="fbExtended" class="fbSectionExtended clearfix"><?php
  if ($this->params->get('sections_top_navigation', true))
  {
    ?><div class="fbTopNavigation_core_outer">
  		<div class="fbTopNavigation_core">
  			<div class="fbTopNavigation_wrap">
  				<ul class="fbTopNavigation_root">
  					<li class="NavTopUL_home">
  						<a href="<?php echo Route::_(RouteHelper::getSectionsRoute($this->home_itemid)); ?>" class="NavTopUL_link">
  							<i class="fas fa-home NavTopUL_homeIcon"></i>&nbsp;&nbsp;<?php echo $this->home_title; ?>
  						</a>
  					</li>
  				</ul>
  			</div><?php

  			if ($this->params->get('profile_link', false) && Factory::getUser()->id)
        {
  				?><div class="NavTopUL_buttons">
  					<a href="<?php echo Route::_(RouteHelper::getFullProfileRoute(Factory::getUser()->id)); ?>" class="fbTopNavigation_profile btn btn-default">
  						<i class="fas fa-user"></i>&nbsp;&nbsp;<?php echo Text::_('COM_FAQBOOKPRO_VIEW_PROFILE'); ?>
  					</a>
  				</div><?php
  			}
  		?></div>
  		<div class="clearfix"> </div>
  	</div><?php
  }

	?><div class="fbContent_core">
		<div class="fbContent_root"><?php
			if ($this->params->get('sections_layout', 'columns') == 'columns')
      {
				echo $this->loadTemplate('columns');
			}
			else
			{
				echo $this->loadTemplate('forum');
			}
		?></div>
	</div>
</div>
