CREATE TABLE IF NOT EXISTS `zoom_integration_settings` (
  `setting_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `setting_value` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'app',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `setting_name` (`setting_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;#

INSERT INTO `zoom_integration_settings` (`setting_name`, `setting_value`, `deleted`) VALUES ('zoom_integration_item_purchase_code', 'Zoom_Integration-ITEM-PURCHASE-CODE', 0);#

CREATE TABLE IF NOT EXISTS `zoom_meetings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text COLLATE utf8_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `start_time` datetime NOT NULL,
  `duration` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `share_with_team_members` mediumtext COLLATE utf8_unicode_ci,
  `share_with_client_contacts` mediumtext COLLATE utf8_unicode_ci,
  `zoom_meeting_id` text COLLATE utf8_unicode_ci NOT NULL,
  `join_url` text COLLATE utf8_unicode_ci NOT NULL,
  `waiting_room` int(1) NOT NULL DEFAULT '0',
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;#

INSERT INTO `notification_settings` (`event`, `category`, `enable_email`, `enable_web`, `notify_to_team`, `notify_to_team_members`, `notify_to_terms`, `sort`, `deleted`) VALUES
('zoom_integration_new_meeting_scheduled', 'zoom_meeting', 0, 1, '', '', 'recipient', 80, 1),
('zoom_integration_meeting_updated', 'zoom_meeting', 0, 1, '', '', 'recipient', 81, 1);#

ALTER TABLE `notifications` ADD `plugin_zoom_meeting_id` INT(11) NOT NULL AFTER `deleted`;#

INSERT INTO `email_templates` (`template_name`, `email_subject`, `default_message`, `custom_message`, `template_type`, `language`, `deleted`) VALUES 
('zoom_integration_new_meeting_scheduled', 'New meeting scheduled!', '<div style="background-color:rgb(4, 4, 4); padding: 50px 0; "><div style="max-width:640px; margin:0 auto; "> <div style="color: #fff; text-align: center; background-color:#33333e; padding: 30px; border-top-left-radius: 3px; border-top-right-radius: 3px; margin: 0;">  <h1>{MEETING_CREATED_BY} created a new meeting</h1></div><div style="padding: 20px; background-color: rgb(255, 255, 255);"><p style="color: rgb(85, 85, 85); font-size: 14px;">You are requested to join this meeting. The details are given below:</p><p style="color: rgb(85, 85, 85); font-size: 14px;">Topic: {MEETING_TOPIC}</p><p style="color: rgb(85, 85, 85); font-size: 14px;">Meeting time: {MEETING_TIME}</p><p style="color: rgb(85, 85, 85); font-size: 14px;">Join URL:&nbsp;<a href="{JOIN_URL}" target="_blank">{JOIN_URL}</a></p><p style="color: rgb(85, 85, 85); font-size: 14px;"><br></p>            <p style="color: rgb(85, 85, 85); font-size: 14px;">{SIGNATURE}</p>        </div>    </div></div>', '', 'default', '', 0),
('zoom_integration_meeting_updated', 'Meeting updated!', '<div style="background-color: #eeeeef; padding: 50px 0; "><div style="max-width:640px; margin:0 auto; "> <div style="color: #fff; text-align: center; background-color:#33333e; padding: 30px; border-top-left-radius: 3px; border-top-right-radius: 3px; margin: 0;">  <h1>Meeting updated!</h1></div><div style="padding: 20px; background-color: rgb(255, 255, 255);">            <p style="color: rgb(85, 85, 85); font-size: 14px;"><span style="font-weight: var(--bs-body-font-weight); text-align: var(--bs-body-text-align);">A meeting of you has been updated. This is the new details:</span><br></p><p style="color: rgb(85, 85, 85); font-size: 14px;">Topic: {MEETING_TOPIC}</p><p style="color: rgb(85, 85, 85); font-size: 14px;">Meeting time: {MEETING_TIME}</p><p style="color: rgb(85, 85, 85); font-size: 14px;">Join URL:&nbsp;<a href="{JOIN_URL}" target="_blank">{JOIN_URL}</a></p><p style="color: rgb(85, 85, 85); font-size: 14px;"><br></p>            <p style="color: rgb(85, 85, 85); font-size: 14px;">{SIGNATURE}</p>        </div>    </div></div>', '', 'default', '', 0);#
