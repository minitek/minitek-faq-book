<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\FAQBookPro\Site\Helper\RouteHelper;

$column_class = $this->params->get('sections_column_class', 'col-12 col-lg-6');
$card_class = $this->params->get('sections_card_class', 'border-0 shadow-sm');

?><div class="fbContent_sections"><?php

	if ($this->params->get('show_page_title')):
		?><h2 class="fbContent_title"><?php
			echo $this->escape($this->params->get('page_title'));
		?></h2><?php
	endif;

	if ($this->params->get('sections_page_description', false) && $this->params->get('menu-meta_description'))
	{
		?><div class="fbContent_sections_desc"><?php
			echo $this->params->get('menu-meta_description');
		?></div><?php
	}

	if ($this->sections)
	{
		?><div class="fb_columns row g-4"><?php
			$i = 0;
			foreach ($this->sections as $key=>$section)
			{
				$sectionParams = json_decode($section->attribs, false);
				$section_class = (isset($sectionParams->section_class)) ? $sectionParams->section_class : '';
				$section_image = (isset($sectionParams->section_image)) ? $sectionParams->section_image : false;

				?><div class="fb_column <?php echo $column_class; ?> <?php echo $section_class; ?>">
					<div class="card h-100 <?php echo $card_class; ?>">
						<div class="card-body"><?php

							if ($section_image && $this->params->get('sections_image', true))
							{
								?><a href="<?php echo Route::_(RouteHelper::getSectionRoute($section->id)); ?>" class="fb_column_image">
									<img src="<?php echo $section_image; ?>" alt="<?php echo $section->title; ?>" /></a><?php
							}

							if ($this->params->get('sections_title', true))
							{
								?><h4 class="fb_column_header fs-5">
									<a href="<?php echo Route::_(RouteHelper::getSectionRoute($section->id)); ?>"><?php
										echo $section->title;
									?></a><?php
									if ($this->params->get('sections_section_count', true))
									{
										?> <small>(<?php echo (int)$section->q_count; ?>)</small><?php
									}
								?></h4><?php
							}

							if ($this->params->get('sections_description', true) && $section->description)
							{
								?><div class="fb_column_desc"><?php
									echo HTMLHelper::_('content.prepare', $section->description);
								?></div><?php
							}

							if ($this->params->get('sections_topics', true) && $section->topics)
							{
								?><div class="fb_column_topics"><?php
									foreach ($section->topics as $topic)
									{
										?><div class="fb_column_topic">
											<a href="<?php echo Route::_(RouteHelper::getTopicRoute($topic->id)); ?>"><?php
												if ($topic->icon_class)
												{
													?><i class="fas fa-<?php echo $topic->icon_class; ?>"></i><?php
												}
												echo $topic->title;
											?></a><?php
											if ($this->params->get('sections_topics_count', true))
											{
												?> <small>(<?php echo (int)$topic->q_count; ?>)</small><?php
											}
										?></div><?php
									}
								?></div><?php
							}
						?></div>
					</div>
				</div><?php
			}
		?></div><?php
	}

	if ($this->params->get('sections_custom', ''))
	{
		?><div class="fbContent_sections_custom"><?php
			echo $this->params->get('sections_custom');
		?></div><?php
	}
?></div>
