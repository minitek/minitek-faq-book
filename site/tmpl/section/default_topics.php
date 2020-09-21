<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if ($this->browse_topics_header)
{
	?><h2 class="fbContent_title"><?php echo Text::_('COM_FAQBOOKPRO_BROWSE_TOPICS'); ?></h2><?php
}

if ($this->first_level_topics)
{
	?><ul class="fbContent_topics"><?php
		foreach ($this->first_level_topics as $first_level_topic)
		{
			$topicsTree = $this->getTopicsIndex(
				$first_level_topic,
				$this->browse_topics_levels,
				$this->browse_topics_cols,
				$level = 1,
				$this->browse_topics_icons,
				$this->browse_topics_images
			);
			$topics_tree[] = $topicsTree;
		}

		foreach ($topics_tree as $topic_tree)
		{
			echo $topic_tree;
		}
	?></ul><?php
}
