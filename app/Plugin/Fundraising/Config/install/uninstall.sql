DROP TABLE IF EXISTS `{PREFIX}campaigns`;

DELETE FROM `{PREFIX}pages` WHERE `uri` = 'fundraisings.index';

DELETE FROM `{PREFIX}acos` WHERE `group`='fundraising';