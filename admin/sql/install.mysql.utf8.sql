CREATE TABLE IF NOT EXISTS `#__minitek_faqbook_attachments` (
 `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
 `parent_id` int(10) unsigned NOT NULL,
 `parent_type` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
 `name` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
 `encoded_name` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
 `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'application/octet-stream',
 `state` tinyint(3) NOT NULL DEFAULT '0',
 `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `created_by` int(10) unsigned NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__minitek_faqbook_question_types` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `color` varchar(10) DEFAULT '#5bc0de',
  `params` mediumtext,
  `state` tinyint(3) NOT NULL DEFAULT '1',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__minitek_faqbook_customfields` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `description` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `show` varchar(100) NOT NULL DEFAULT 'all',
  `type` varchar(100) NOT NULL DEFAULT 'text',
  `options` mediumtext,
  `default` varchar(255) DEFAULT '',
  `required` tinyint(3) NOT NULL DEFAULT '0',
  `valid_label` varchar(255) DEFAULT '',
  `invalid_label` varchar(255) DEFAULT '',
  `params` mediumtext,
  `state` tinyint(3) NOT NULL DEFAULT '1',
  `ordering` int(11) unsigned NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__minitek_faqbook_customfields_topics` (
  `customfield_id` int(11) NOT NULL,
  `topicid` int(11) NOT NULL,
  PRIMARY KEY (`customfield_id`,`topicid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__minitek_faqbook_answers` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
 `question_id` int(10) unsigned NOT NULL DEFAULT '0',
 `content` mediumtext NOT NULL,
 `selected` tinyint(3) NOT NULL DEFAULT '0',
 `state` tinyint(3) NOT NULL DEFAULT '0',
 `seen` tinyint(3) NOT NULL DEFAULT '1',
 `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `created_by` int(10) unsigned NOT NULL DEFAULT '0',
 `created_by_name` varchar(128) NOT NULL DEFAULT '',
 `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
 `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
 `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__minitek_faqbook_email_templates` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
 `template_key` varchar(255) NOT NULL DEFAULT '',
 `title` varchar(255) NOT NULL DEFAULT '',
 `subject` varchar(500) NOT NULL DEFAULT '',
 `content` mediumtext NOT NULL,
 `state` tinyint(3) NOT NULL DEFAULT '0',
 `language` char(7) NOT NULL COMMENT 'The language code for the email template.',
 `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
 `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

INSERT INTO `#__minitek_faqbook_email_templates` (template_key,title,subject,content,state,language)
VALUES ('new-question-admin','New Question - Moderator notification','New Question posted: [QUESTION_TITLE]','<div style="background-color:#f0f0f0;padding:10px;text-align: left;"> <div style="background-color:#f9f9f9;padding:10px 15px"> Hello [RECIPIENT_NAME],<br><br> A new question has been posted in the topic <a target="_blank" style="font-weight:bold" href="[TOPIC_URL]">[TOPIC_TITLE]</a> by [AUTHOR_NAME].<br><br><div style="background: #fff; padding: 15px; border: 1px solid #999;"><a target="_blank" style="font-weight:bold" href="[QUESTION_URL]">[QUESTION_TITLE]</a><div>[QUESTION_CONTENT]</div></div><br>	</div> </div>','1','*'),
('new-answer-author','New Answer - Author notification','Answer posted: [QUESTION_TITLE]','<div style="background-color:#f0f0f0;padding:10px;text-align: left;"> <div style="background-color:#f9f9f9;padding:10px 15px"> Hello [RECIPIENT_NAME],<br><br> A new answer has been posted to your question <a target="_blank" style="font-weight:bold" href="[QUESTION_URL]">[QUESTION_TITLE]</a>.<br><br><div style="background: #fff; padding: 15px; border: 1px solid #999;"><div>[ANSWER_CONTENT]</div></div><br>	</div> </div>','1','*'),
('new-answer-admin','New Answer - Moderator notification','Answer posted: [QUESTION_TITLE]','<div style="background-color:#f0f0f0;padding:10px;text-align: left;"> <div style="background-color:#f9f9f9;padding:10px 15px"> Hello [RECIPIENT_NAME],<br><br> A new answer has been posted to the question <a target="_blank" style="font-weight:bold" href="[QUESTION_URL]">[QUESTION_TITLE]</a>.<br><br><div style="background: #fff; padding: 15px; border: 1px solid #999;"><div>[ANSWER_CONTENT]</div></div><br>	</div> </div>','1','*'),
('selected-answer-author','Selected Answer - Author notification','Your answer was selected: [QUESTION_TITLE]','<div style="background-color:#f0f0f0;padding:10px;text-align: left;"> <div style="background-color:#f9f9f9;padding:10px 15px"> Hello [RECIPIENT_NAME],<br><br> Your answer to the question <a target="_blank" style="font-weight:bold" href="[QUESTION_URL]">[QUESTION_TITLE]</a> was selected as the best answer.<br><br><div style="background: #fff; padding: 15px; border: 1px solid #999;"><div>[ANSWER_CONTENT]</div></div><br>	</div> </div>','1','*'),
('new-question-hash','New Question Hash - Author notification','New Question posted: [QUESTION_TITLE]','<div style="background-color:#f0f0f0;padding:10px;text-align: left;"><div style="background-color:#f9f9f9;padding:10px 15px"> Hello [RECIPIENT_NAME],<br><br> Your question has been posted in the topic <a target="_blank" style="font-weight:bold" href="[TOPIC_URL]">[TOPIC_TITLE]</a>.<br><br><div style="background: #fff; padding: 15px; border: 1px solid #999;"><a target="_blank" style="font-weight:bold" href="[QUESTION_URL]">[QUESTION_TITLE]</a><div>[QUESTION_CONTENT]</div><br><br><div>Question hash:</div><div>[QUESTION_HASH]</div></div><br></div></div>','1','*'),
('assign-question-admin','New assigned Question - Moderator notification','New assigned question: [QUESTION_TITLE]','<div style="background-color:#f0f0f0;padding:10px;text-align: left;"> <div style="background-color:#f9f9f9;padding:10px 15px"> Hello [RECIPIENT_NAME],<br><br> A new question has been assigned to you.<br><br><div style="background: #fff; padding: 15px; border: 1px solid #999;"><a target="_blank" style="font-weight:bold" href="[QUESTION_URL]">[QUESTION_TITLE]</a><div>[QUESTION_CONTENT]</div></div><br>	</div> </div>','1','*');

CREATE TABLE IF NOT EXISTS `#__minitek_faqbook_questions` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
 `title` varchar(500) NOT NULL DEFAULT '',
 `alias` varchar(500) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
 `content` mediumtext NOT NULL,
 `answers` int(10) unsigned NOT NULL DEFAULT '0',
 `last_answer` int(10) unsigned NOT NULL DEFAULT '0',
 `state` tinyint(3) NOT NULL DEFAULT '0',
 `locked` tinyint(3) NOT NULL DEFAULT '0',
 `private` tinyint(3) NOT NULL DEFAULT '0',
 `pinned` tinyint(3) NOT NULL DEFAULT '0',
 `resolved` tinyint(3) NOT NULL DEFAULT '0',
 `question_type` varchar(255) NOT NULL DEFAULT '',
 `topicid` int(10) unsigned NOT NULL DEFAULT '0',
 `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `created_by` int(10) unsigned NOT NULL DEFAULT '0',
 `created_by_name` varchar(128) NOT NULL DEFAULT '',
 `created_by_email` varchar(128) NOT NULL DEFAULT '',
 `created_by_alias` varchar(255) NOT NULL DEFAULT '',
 `assigned_to` int(10) unsigned NOT NULL DEFAULT '0',
 `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
 `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
 `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `images` text NOT NULL,
 `customfields` mediumtext NOT NULL,
 `attribs` varchar(5120) NOT NULL,
 `ordering` int(11) NOT NULL DEFAULT '0',
 `metakey` text NOT NULL,
 `metadesc` text NOT NULL,
 `access` int(10) unsigned NOT NULL DEFAULT '0',
 `hits` int(10) unsigned NOT NULL DEFAULT '0',
 `metadata` text NOT NULL,
 `featured` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Set if question is featured.',
 `language` char(7) NOT NULL COMMENT 'The language code for the question.',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__minitek_faqbook_sections` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
 `title` varchar(255) NOT NULL,
 `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
 `description` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
 `state` tinyint(3) NOT NULL DEFAULT '0',
 `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
 `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `access` int(10) unsigned NOT NULL DEFAULT '0',
 `attribs` text NOT NULL,
 `ordering` int(11) NOT NULL DEFAULT '0',
 `metadesc` varchar(1024) NOT NULL,
 `metakey` varchar(1024) NOT NULL,
 `metadata` varchar(2048) NOT NULL,
 `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
 `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `hits` int(10) unsigned NOT NULL DEFAULT '0',
 `language` char(7) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__minitek_faqbook_topics` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
 `section_id` int(10) unsigned NOT NULL DEFAULT '0',
 `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
 `lft` int(11) NOT NULL DEFAULT '0',
 `rgt` int(11) NOT NULL DEFAULT '0',
 `level` int(10) unsigned NOT NULL DEFAULT '0',
 `path` varchar(255) NOT NULL DEFAULT '',
 `title` varchar(255) NOT NULL,
 `alias` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
 `description` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
 `published` tinyint(1) NOT NULL DEFAULT '0',
 `qvisibility` tinyint(1) NOT NULL DEFAULT '0',
 `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
 `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `access` int(10) unsigned NOT NULL DEFAULT '0',
 `params` text NOT NULL,
 `metadesc` varchar(1024) NOT NULL DEFAULT '' COMMENT 'The meta description for the page.',
 `metakey` varchar(1024) NOT NULL DEFAULT '' COMMENT 'The meta keywords for the page.',
 `metadata` varchar(2048) NOT NULL DEFAULT '' COMMENT 'JSON encoded metadata properties.',
 `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
 `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
 `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `hits` int(10) unsigned NOT NULL DEFAULT '0',
 `language` char(7) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

INSERT INTO `#__minitek_faqbook_topics` (id,section_id,parent_id,lft,rgt,level,title,alias,description,published,access,params,metadesc,metakey,metadata,language)
VALUES (1,0,0,0,0,0,'ROOT','root','',1,1,'','','','','*');

CREATE TABLE IF NOT EXISTS `#__minitek_faqbook_votes` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `target_type` varchar(50) NOT NULL DEFAULT 'question',
 `target_id` int(11) unsigned NOT NULL,
 `user_id` int(10) unsigned DEFAULT NULL,
 `user_ip` varchar(128) NOT NULL,
 `vote_up` smallint(5) unsigned NOT NULL DEFAULT '0',
 `vote_down` smallint(5) unsigned NOT NULL DEFAULT '0',
 `creation_date` datetime NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
