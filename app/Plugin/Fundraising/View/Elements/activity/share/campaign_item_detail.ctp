<?php
$campaign = $object;
$campaignHelper = MooCore::getInstance()->getHelper('Fundraising_Fundraising');
?>

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
    <div class="clear"></div>
