<!--Begin Center-->
<div class="bar-content full_content p_m_10">
    <div class="content_center">
        <div class="post_body campaign_view_body">
            <?php if(!empty($uid)): ?>
            <div class="list_option">
                <div class="dropdown">
                    <button id="dLabel" type="button" data-toggle="dropdown" aria-haspopup="true" role="button" aria-expanded="false">
                        <i class="material-icons">more_vert</i>
                    </button>
                    <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                        <li>
                            <?php
                                $this->MooPopup->tag(array(
                            'href'=>$this->Html->url(array("controller" => "fundraisings",
                            "action" => "ajax_invite",
                            "plugin" => 'fundraising',
                            $campaign['Campaign']['id'],

                            )),
                            'title' => __( 'Invite Friends'),
                            'innerHtml'=> __( 'Invite Friends'),
                            ));
                            ?>
                        </li>
                        <?php if ( ($campaign['Campaign']['user_id'] == $uid ) || ( !empty($cuser['Role']['is_admin']) ) ): ?>
                        <li><?php echo $this->Html->link(__( 'Edit Campaign'), array(
                            'plugin' => 'Fundraising',
                            'controller' => 'fundraisings',
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
                <div><span><?php echo __('Target:');?> </span><?php echo $campaign['Campaign']['target_amount'] ? $currency['Currency']['symbol'].$campaign['Campaign']['target_amount'] : __('Unlimited');?></div>
                <div><span><?php echo __('Total raised:');?></span> <?php echo $currency['Currency']['symbol'].$campaign['Campaign']['raised_amount'];?></div>
                <div><span><?php echo __('Donations accepted util:');?></span>
                    <?php if(!empty($campaign['Campaign']['expire'])):?>
                    <?php echo $this->Time->format( $campaign['Campaign']['expire'], '%b %d, %Y', false, $utz);?>
                    <?php else: echo __('Unlimited'); endif;?>
                </div>
                <div class="col-md-4">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $percent;?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $percent;?>%">
                            <span class="sr-only">70</span>
                        </div>
                    </div>
                </div>
                <div class="clear"></div>
                <?php if(empty($campaign['Campaign']['expire']) || strtotime($campaign['Campaign']['expire']) > time() ):?>
                <a class="btn-action btn" href="<?php echo $this->request->base .'/fundraisings/donate/'.$campaign['Campaign']['id']?>"><?php echo __('Donate now');?> </a>
                <?php endif;?>
            </div>
            <h3><?php echo ('Infomation about the campaign');?></h3>
            <div class="campaign-location"><?php echo __('Location:');?> <a><?php echo $campaign['Campaign']['location'];?></a></div>
            <div class="post_content">
                <?php echo $this->Moo->cleanHtml($this->Text->convert_clickable_links_for_hashtags( $campaign['Campaign']['body'] , Configure::read('Fundraising.fundraising_hashtag_enabled')))?>
            </div>
            <div class="extra_info"><?php echo __( 'Posted in')?> <a href="<?php echo $this->request->base?>/fundraisings/index/<?php echo $campaign['Campaign']['category_id']?>/<?php echo seoUrl($campaign['Category']['name'])?>"><strong><?php echo $campaign['Category']['name']?></strong></a> <?php echo $this->Moo->getTime($campaign['Campaign']['created'], Configure::read('core.date_format'), $utz)?></div>
            <?php $this->Html->rating($campaign['Campaign']['id'],'campaigns', 'Campaign'); ?>

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

<div class="bar-content full_content p_m_10 campaign-comment">
    <?php echo $this->renderComment();?>
</div>
