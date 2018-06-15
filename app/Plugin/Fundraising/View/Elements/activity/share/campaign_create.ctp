<?php
$campaign = $object;
$campaignHelper = MooCore::getInstance()->getHelper('Fundraising_Fundraising');
?>
    <div class="activity_feed_content">
    
        <div class="activity_text">
            <?php echo $this->Moo->getName($campaign['User'], true, true) ?>
            <?php
            $subject = MooCore::getInstance()->getItemByType($activity['Activity']['type'], $activity['Activity']['target_id']);
            $name = key($subject);
            ?>
            <?php if ($activity['Activity']['target_id']): ?>

                <?php $show_subject = MooCore::getInstance()->checkShowSubjectActivity($subject); ?>

                <?php if ($show_subject): ?>
            &rsaquo; <a target="_blank" href="<?php echo $subject[$name]['moo_href'] ?>"><?php echo h($subject[$name]['moo_title']) ?></a>
                <?php else: ?>
                    <?php echo __d('fundraising','created a new campaign'); ?>
                <?php endif; ?>

            <?php else: ?>
                <?php echo __d('fundraising','created a new campaign'); ?>
            <?php endif; ?>
        </div>

        <div class="parent_feed_time">
            <span class="date"><?php echo $this->Moo->getTime($campaign['Campaign']['created'], Configure::read('core.date_format'), $utz) ?></span>
        </div>
        
    </div>
    <div class="clear"></div>
    <div class="activity_feed_content_text">
    <div class="activity_left">
        <img width="150" class="thum_activity" src="<?php echo $campaignHelper->getImage($campaign, array('prefix' => '150_square')) ?>"/>
    </div>
    <div class="activity_right ">
        <div class="activity_header">
            <a target="_blank" class="feed_title" href="<?php echo $this->request->base ?>/fundraisings/view/<?php echo $campaign['Campaign']['id'] ?>/<?php echo seoUrl($campaign['Campaign']['title']) ?>"><b><?php echo h($campaign['Campaign']['title']) ?></b></a>
        </div>
        <div class="feed_detail_text">
            <?php echo $this->Text->convert_clickable_links_for_hashtags($this->Text->truncate(strip_tags(str_replace(array('<br>', '&nbsp;'), array(' ', ''), $campaign['Campaign']['body'])), 200, array('exact' => false)), Configure::read('Fundraising.fundraising_hashtag_enabled')) ?>
        </div>
    </div>
    </div>
    <div class="clear"></div>
