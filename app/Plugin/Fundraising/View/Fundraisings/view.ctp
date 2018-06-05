<?php $fundraisingHelper = MooCore::getInstance()->getHelper('Fundraising_Fundraising');
    $currency = Configure::read('Config.currency');
?>

<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooFundraising'), 'object' => array('$', 'mooFundraising'))); ?>
mooFundraising.initOnView();
<?php $this->Html->scriptEnd(); ?> 

<?php $this->setNotEmpty('west');?>
<?php $this->start('west'); ?>
<div class="bar-content">
	<div class="box2">
        <img class="detail-campaign-thumb" src="<?php echo $fundraisingHelper->getImage($campaign, array('prefix' => '300_square'))?>">
        <h2><?php echo h($campaign['Campaign']['title'])?></h2>
        <?php echo $this->element( 'Fundraising.sidebar/detail_menu', array('cmenu' => 'info') ); ?>
	</div>

    <?php if(!empty($tags)): ?>
        <div class="box2">
            <h3><?php echo __( 'Tags')?></h3>
            <div class="box_content">
                <?php echo $this->element( 'blocks/tags_item_block' ); ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $this->end();?>

<!--Begin Center-->
<div class="bar-content full_content p_m_10">
    <div class="content_center">
	<div class="post_body topic_view_body">
        <?php if(!empty($uid)): ?>
            <div class="list_option">
                <div class="dropdown">
                    <button id="dLabel" type="button" data-toggle="dropdown" aria-haspopup="true" role="button" aria-expanded="false">
                        <i class="material-icons">more_vert</i>
                    </button>
                    <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                        <?php if ( ($campaign['Campaign']['user_id'] == $uid ) || ( !empty($cuser['Role']['is_admin']) ) ): ?>
                      <li><?php echo $this->Html->link(__( 'Edit Campaign'), array(
                          'plugin' => 'Campaign',
                          'controller' => 'topics',
                          'action' => 'create',
                          $campaign['Campaign']['id']
                      )); ?></li>
                      <li><a href="javascript:void(0);" class="deleteCampaign" data-id="<?php echo $campaign['Campaign']['id']?>"><?php echo __( 'Delete')?></a></li>
                        <li class="seperate"></li>
                        <?php endif; ?>
                        
                        <li>
                            <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "reports",
                                            "action" => "ajax_create",
                                            "plugin" => false,
                                            'Fundraising_Campaign',
                                            $campaign['Campaign']['id'],
                                        )),
             'title' => __( 'Report Campaign'),
             'innerHtml'=> __( 'Report Campaign'),
     ));
 ?>
                           </li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
        <div class="post_header">
            <div><span><?php echo __('Created by:');?> </span><?php echo $this->Moo->getName($campaign['User']);?></div>
            <div><span><?php echo __('Target:');?> </span><?php echo $currency['Currency']['symbol'].$campaign['Campaign']['target_amount'];?></div>
            <div><span><?php echo __('Total raised:');?></span> <?php echo '88';?></div>
            <div><span><?php echo __('Donations accepted util:');?></span> <?php echo $this->Time->format( $campaign['Campaign']['expire'], '%b %d, %Y', false, $utz);?></div>
            <a class="btn-action btn"><?php echo __('Donate now');?> </a>
        </div>
        <h3><?php echo ('Infomation about the campaign');?></h3>
        <div class="campaign-location"><?php echo __('Location:');?> <a><?php echo $campaign['Campaign']['location'];?></a></div>
        <div class="post_content">
            <?php echo $this->Moo->cleanHtml($this->Text->convert_clickable_links_for_hashtags( $campaign['Campaign']['body'] , Configure::read('Campaign.topic_hashtag_enabled')))?>
        </div>
	    <div class="extra_info"><?php echo __( 'Posted in')?> <a href="<?php echo $this->request->base?>/topics/index/<?php echo $campaign['Campaign']['category_id']?>/<?php echo seoUrl($campaign['Category']['name'])?>"><strong><?php echo $campaign['Category']['name']?></strong></a> <?php echo $this->Moo->getTime($campaign['Campaign']['created'], Configure::read('core.date_format'), $utz)?></div>
        <?php $this->Html->rating($campaign['Campaign']['id'],'topics', 'Campaign'); ?>

        <div class="clear"></div>
        </div>

	
    </div>
</div>

<?php if (!empty($cuser) ): ?>
<div class="bar-content full_content p_m_10">
    <div class="content_center">
        <?php echo $this->element('likes', array('shareUrl' => $this->Html->url(array(
                                    'plugin' => false,
                                    'controller' => 'share',
                                    'action' => 'ajax_share',
                                    'Fundraising_Campaign',
                                    'id' => $campaign['Campaign']['id'],
                                    'type' => 'campaign_item_detail'
                                ), true), 'item' => $campaign['Campaign'], 'type' => 'Fundraising_Campaign')); ?>
    </div>
</div>
<?php endif; ?>

<div class="bar-content full_content p_m_10 topic-comment">
    <?php echo $this->renderComment();?>
</div>
