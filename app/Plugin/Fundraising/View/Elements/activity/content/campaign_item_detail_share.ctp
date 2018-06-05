<?php 
$fundraisingHelper = MooCore::getInstance()->getHelper('Fundraising_Fundraising');
$campaignModel = MooCore::getInstance()->getModel('Fundraising_Campaign');
$campaign = $campaignModel->findById($activity['Activity']['parent_id']);
?>


<div class="comment_message">
<?php echo $this->viewMore(h($activity['Activity']['content']),null, null, null, true, array('no_replace_ssl' => 1)); ?>
    <?php if(!empty($activity['UserTagging']['users_taggings'])) $this->MooPeople->with($activity['UserTagging']['id'], $activity['UserTagging']['users_taggings']); ?>
</div>


<div class="share-content">
    <div class="activity_left">
        <a href="<?php echo $campaign['Campaign']['moo_href']?>">
        <img width="150" class="thum_activity" src="<?php echo $fundraisingHelper->getImage($campaign, array('prefix' => '150_square'))?>"/>
        </a>
    </div>
    <div class="activity_right ">
        <div class="activity_header">
            <a class="feed_title" href="<?php echo  $this->request->base ?>/fundraisings/view/<?php echo  $campaign['Campaign']['id'] ?>/<?php echo  seoUrl($campaign['Campaign']['title']) ?>"><b><?php echo  h($campaign['Campaign']['title']) ?></b></a>
        </div>
        <div class="feed_detail_text">
            <?php echo  $this->Text->convert_clickable_links_for_hashtags($this->Text->truncate(strip_tags(str_replace(array('<br>', '&nbsp;'), array(' ', ''), $campaign['Campaign']['body'])), 200, array('exact' => false)), Configure::read('Fundraising.fundraising_hashtag_enabled')) ?>
        </div>
    </div>
    <div class="clear"></div>
</div>