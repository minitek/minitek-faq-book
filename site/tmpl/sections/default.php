<?php

/**
 * @title		Minitek FAQ Book
 * @copyright	Copyright (C) 2011-2023 Minitek, All rights reserved.
 * @license		GNU General Public License version 3 or later.
 * @author url	https://www.minitek.gr/
 * @developers	Minitek.gr
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\FAQBookPro\Site\Helper\RouteHelper;

?><div id="fbExtended" class="fbSectionExtended noleftnav clearfix"><?php

	if ($this->params->get('sections_top_navigation', true)) 
	{
		?><div class="fbTopNavigation_core_outer">
			<div class="fbTopNavigation_core">
				<div class="fbTopNavigation_wrap">
					<ul class="fbTopNavigation_root">
						<li class="NavTopUL_home">
							<a href="<?php echo Route::_(RouteHelper::getSectionsRoute($this->home_itemid)); ?>" class="NavTopUL_link">
								<i class="fas fa-home NavTopUL_homeIcon"></i><?php echo $this->home_title; ?>
							</a>
						</li>
					</ul>
				</div>
			</div>
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