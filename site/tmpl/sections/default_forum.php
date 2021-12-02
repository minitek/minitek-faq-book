<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2021 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\FAQBookPro\Site\Helper\RouteHelper;

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
		?><div class="fb_forums"><?php
			foreach ($this->sections as $key=>$section)
			{
				$sectionParams = json_decode($section->attribs, false);
				$section_class = (isset($sectionParams->section_class)) ? $sectionParams->section_class : '';
				$section_image = (isset($sectionParams->section_image)) ? $sectionParams->section_image : false;

				?><div class="fb_forum <?php echo $section_class; ?>">
					<div class="fb_forum_header"><?php
						if ($this->params->get('sections_title', true))
						{
							?><h2 class="fbContent_title">
								<a href="<?php echo Route::_(RouteHelper::getSectionRoute($section->id)); ?>"><?php
									echo $section->title;
								?></a><?php
								if ($this->params->get('sections_forum_section_count', true))
								{
									?> <small>(<?php echo (int)$section->q_count; ?>)</small><?php
								}
							?></h2><?php
						}

						if ($this->params->get('sections_forum_section_description', true))
						{
							?><div class="fb_forum_desc"><?php
								echo HTMLHelper::_('content.prepare', $section->description);
							?></div><?php
						}
					?></div><?php

					if ($this->params->get('sections_forum_topics', true))
					{
						if (count($section->topics))
						{
							?><div class="fb_forum_body">
								<div class="fb_forum_body_header">
									<div class="fb_table_row">
										<div class="fb_table_cell <?php echo $this->topic_col_class; ?>">
											<div class="fb_table_cell_padding">
												<div><?php echo $this->params->get('sections_forum_topic_text', 'Topic'); ?></div>
											</div>
										</div><?php
										if ($this->params->get('sections_forum_last_question', true))
										{
											?><div class="fb_table_cell fb-col-4">
												<div class="fb_table_cell_padding">
													<div><?php echo $this->params->get('sections_forum_question_text', 'Latest question'); ?></div>
												</div>
											</div><?php
										}
									?></div>
								</div><?php
								foreach ($section->topics as $topic)
								{
									?><div class="fb_forum_body_container">
										<div class="fb_forum_row">
											<div class="fb_table_row">
												<div class="fb_table_cell fb-col-8">
													<div class="fb_table_cell_padding"><?php
														$params = json_decode($topic->params, false);
														$topic_image = $params->image;
														$topic_image_alt = $params->image_alt;

														if ($this->params->get('sections_forum_topics_image', false) && $topic_image)
														{
															?><div class="fb_forum_topic_image">
																<a href="<?php echo Route::_(RouteHelper::getTopicRoute($topic->id)); ?>">
																	<img src="<?php echo Uri::root().$topic_image; ?>" alt="<?php echo $topic_image_alt; ?>">
																</a>
															</div><?php
														}
														else if ($this->params->get('sections_forum_topics_icon', true) && $topic->icon_class)
														{
															?><div class="fb_forum_topic_icon">
																<a href="<?php echo Route::_(RouteHelper::getTopicRoute($topic->id)); ?>">
																	<i class="fas fa-<?php echo $topic->icon_class; ?>"></i>
																</a>
															</div><?php
														}

														?><div class="fb_forum_topic_body">
															<h3 class="fb_forum_topic_title">
																<a href="<?php echo Route::_(RouteHelper::getTopicRoute($topic->id)); ?>"><?php
																	echo $topic->title;
																?></a><?php
																if ($this->params->get('sections_forum_topics_count', true))
																{
																	?> <small>(<?php echo (int)$topic->q_count; ?>)</small><?php
																}
															?></h3><?php

															if ($this->params->get('sections_forum_topics_description', true))
															{
																?><div class="fb_forum_topic_desc"><?php
																	echo HTMLHelper::_('content.prepare', $topic->description); ?>
																</div><?php
															}

															if ($this->params->get('sections_forum_topics_children', true))
															{
																if ($topic->children)
																{
																	?><div class="fb_forum_topic_children"><?php
																		foreach ($topic->children as $child)
																		{
																			?><div class="fb_forum_topic_child">
																				<a href="<?php echo Route::_(RouteHelper::getTopicRoute($child->id)); ?>"><?php
																					if ($this->params->get('sections_forum_topics_icon', true) && $child->icon_class)
																					{
																						?><i class="fas fa-<?php echo $child->icon_class; ?>"></i><?php
																					}
																					echo $child->title;
																				?></a><?php
																				if ($this->params->get('sections_forum_topics_children_count', true))
																				{
																					?> <small>(<?php echo (int)$child->q_count; ?>)</small><?php
																				}
																			?></div><?php
																		}
																	?></div><?php
																}
															}
														?></div>
													</div>
												</div><?php
												if ($this->params->get('sections_forum_last_question', true))
												{
													?><div class="fb_table_cell fb-col-4">
														<div class="fb_table_cell_padding"><?php
															if ($topic->lastpost)
															{
																?><div class="fb_forum_question_title"><?php
																	echo $topic->lastpost->title;
																?></div><?php
																if ($this->params->get('sections_forum_question_date', true) || $this->params->get('sections_forum_question_author', true))
																{
																	?><div class="fb_forum_question_meta"><?php
																		if ($this->params->get('sections_forum_question_date', true))
																		{
																			?><span><?php
																				echo Text::_('COM_FAQBOOKPRO_ASKED');
																				?>&nbsp;<?php
																				echo $topic->lastpost->time_since;
																				?>&nbsp;<?php
																				echo Text::_('COM_FAQBOOKPRO_ASKED_AGO');
																			?></span><?php
																		}

																		if ($this->params->get('sections_forum_question_author', true))
																		{
																			if ($topic->lastpost->created_by)
																			{
																				?><span><?php
																					echo Text::_('COM_FAQBOOKPRO_BY');
																					?>&nbsp;<?php
																						echo $topic->lastpost->author_name;
																					?></span><?php
																			}
																			else
																			{
																				if (isset($topic->lastpost->created_by_name) && $topic->lastpost->created_by_name)
																				{
																					?><span><?php
																						echo Text::_('COM_FAQBOOKPRO_BY');
																						?>&nbsp;<?php
																						echo $topic->lastpost->created_by_name;
																					?></span><?php
																				}
																			}
																		}
																	?></div><?php
																}
															}
															else
															{
																?><div class="fb_forum_question_title"><?php
																	echo $this->params->get('sections_forum_no_questions_text', 'No questions');
																?></div><?php
															}
														?></div>
													</div><?php
												}
											?></div>
										</div>
									</div><?php
								}
							?></div><?php
						}
					}
				?></div><?php
			}
		?></div><?php
	}
?></div>
