<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

use Joomla\CMS\Layout\FileLayout;

$data = array();
$data['tab'] = $this->tab;
$data['sectionId'] = $this->sectionId;
$data['topic'] = $this->topic;
$data['topic_params'] = $this->topic_params;
$data['questions_params'] = $this->questions_params;
$layout = new FileLayout('fb_topic');
echo $layout->render($data);
