<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

defined('_JEXEC') or die;

use Joomla\CMS\Layout\FileLayout;

if ($this->params->get('show_page_title')):
	?><h1 class="componentheading<?php echo $this->params->get('pageclass_sfx')?>"><?php
		echo $this->escape($this->params->get('page_title'));
	?></h1><?php
endif;

?><div id="fbExtended" class="fbSectionExtended"><?php

	if ($this->topnavigation)
	{
		$data = array();
		$data['sectionId'] = $this->sectionId;
		$topnav = new FileLayout('fb_topnav');
		echo $topnav->render($data);
	}

	if ($this->leftnavigation)
	{
		$data = array();
		$data['sectionId'] = $this->sectionId;
		$leftnav = new FileLayout('fb_leftnav');
		echo $leftnav->render($data);
	}

	?><div class="fbContent_core">
		<div class="fb_loader"><?php
			$page_placeholder = new FileLayout('fb_page_placeholder');
			echo $page_placeholder->render();
		?></div>
		<div class="fbContent_root"><?php
			switch ($this->tab)
			{
				case 'recent':
				case 'top':
				case 'featured':
				case 'unanswered':
				case 'resolved':
				case 'unresolved':
					echo $this->loadTemplate('content');
					break;
				case 'topics':
					echo $this->loadTemplate('topics');
					break;
				default:
					echo $this->loadTemplate('content');
			}
		?></div>
	</div>
</div>
