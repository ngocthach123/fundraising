DROP TABLE IF EXISTS `{PREFIX}campaigns`;

DELETE FROM `{PREFIX}pages` WHERE `uri` = 'fundraisings.index';
DELETE FROM `{PREFIX}pages` WHERE `uri` = 'fundraisings.view';

DELETE FROM `{PREFIX}acos` WHERE `group`='fundraising';

DELETE FROM `{PREFIX}activities` WHERE `plugin`='Fundraising';

DELETE FROM `{PREFIX}comments` WHERE `type`='Fundraising_Campaign';

DELETE FROM `{PREFIX}likes` WHERE `type`='Fundraising_Campaign';

DELETE FROM `{PREFIX}hashtags` WHERE `item_table`='campaigns';

DELETE FROM `{PREFIX}tags` WHERE `type`='Fundraising_Campaign';