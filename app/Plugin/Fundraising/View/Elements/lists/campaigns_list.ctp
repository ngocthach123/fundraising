

<?php if(Configure::read('Fundraising.fundraising_enabled') == 1): ?>
    <ul class="campaign-content-list" id="list-content">
    <?php
     $currency = Configure::read('Config.currency');
    $fundraisingHelper = MooCore::getInstance()->getHelper('Fundraising_Fundraising');
    if (!empty($campaigns) && count($campaigns) > 0)
    {
        $i = 1;
        foreach ($campaigns as $campaign):
    ?>
        <li class="full_content p_m_10" <?php if( $i == count($campaigns) ) echo 'style="border-bottom:0"'; ?>>
            <a href="<?php echo  $this->request->base ?>/fundraisings/view/<?php echo  $campaign['Campaign']['id'] ?>/<?php echo  seoUrl($campaign['Campaign']['title']) ?>">
                <img width="140" src="<?php echo $fundraisingHelper->getImage($campaign, array('prefix' => '150_square'))?>" class="campaign-thumb" />
            </a>
            <?php if(!empty($uid) && (($campaign['Campaign']['user_id'] == $uid ) ||  (!empty($cuser) && $cuser['Role']['is_admin']) ) ): ?>
            <div class="list_option">
                    <div class="dropdown">
                        <button id="dLabel" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <i class="material-icons">more_vert</i>
                        </button>

                        <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
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


                        </ul>
                    </div>
                </div>
            <?php endif; ?>
            <div class="campaign-info">
                <a class="title" href="<?php echo  $this->request->base ?>/fundraisings/view/<?php echo  $campaign['Campaign']['id'] ?>/<?php echo  seoUrl($campaign['Campaign']['title']) ?>"><?php echo  h($campaign['Campaign']['title']) ?></a>
                <div class="extra_info">
                    <?php if(!empty($topic['LastDonor'])):?>
                        <?php echo __( 'Last donated by %s', $this->Moo->getName($campaign['LastDonor'], false))?>
                        <?php echo $this->Moo->getTime( $campaign['Campaign']['last_donate'], Configure::read('core.date_format'), $utz )?>
                    <?php else:?>
                        <?php echo __( 'Posted by %s', $this->Moo->getName($campaign['User'], false))?>
                        <?php echo $this->Moo->getTime( $campaign['Campaign']['created'], Configure::read('core.date_format'), $utz )?>
                    <?php endif;?>
                </div>
                <div class="list-campaign-info">
                    <span><?php echo __('Target:');?></span> <?php echo $campaign['Campaign']['target_amount'] ? $currency['Currency']['symbol'].$campaign['Campaign']['target_amount'] : __('Unlimited');?>,
                    <span><?php echo __('Total raised:');?></span> <?php echo $currency['Currency']['symbol'].$campaign['Campaign']['raised_amount'];?>,
                    <span><?php echo __('Donation expired until:');?></span>
                        <?php if(!empty($campaign['Campaign']['expire'])):?>
                            <?php echo $this->Time->format( $campaign['Campaign']['expire'], '%b %d, %Y', false, $utz);?>
                        <?php else: echo __('Unlimited'); endif;?>
                </div>
                <div class="campaign-description-truncate">
                                <div>
                                <?php echo $this->Text->convert_clickable_links_for_hashtags($this->Text->truncate(strip_tags(str_replace(array('<br>','&nbsp;'), array(' ',''), $campaign['Campaign']['body'])), 200, array('exact' => false)), Configure::read('Fundraising.fundraising_hashtag_enabled')) ?>
                                </div>
                                <div class="like-section">
                                    <div class="like-action">

                                        <a href="<?php echo  $this->request->base ?>/fundraisings/view/<?php echo  $campaign['Campaign']['id'] ?>/<?php echo seoUrl($campaign['Campaign']['title'])?>">
                                            <i class='material-icons'>comment</i>
                                        </a>
                                        <a href="<?php echo  $this->request->base ?>/fundraisings/view/<?php echo  $campaign['Campaign']['id'] ?>/<?php echo seoUrl($campaign['Campaign']['title'])?>">
                                            <span><?php echo $campaign['Campaign']['comment_count']?></span>
                                        </a>
                                        <a data-type="Campaign_Campaign" data-id="<?php echo $campaign['Campaign']['id']?>" data-status="1" href="javascript:void(0)" class="likeItem <?php if (!empty($uid) && !empty($campaign['Like'][0]['thumb_up'])): ?>active<?php endif; ?>">
                                            <i class="material-icons">thumb_up</i>
                                        </a>
                                        <?php
          $this->MooPopup->tag(array(
                 'href'=>$this->Html->url(array("controller" => "likes",
                                                "action" => "ajax_show",
                                                "plugin" => false,
                                                'Campaign_Campaign',
                                                $campaign['Campaign']['id'],
                                            )),
                 'title' => __('People Who Like This'),
                 'innerHtml'=> '<span class="likeCount">' . $campaign['Campaign']['like_count'] . '</span>',
         ));
     ?>
                                        <?php if(empty($hide_dislike)): ?>
                                        <a data-type="Campaign_Campaign" data-id="<?php echo $campaign['Campaign']['id']?>" data-status="0" href="javascript:void(0)" class="likeItem <?php if (!empty($uid) && isset($campaign['Like'][0]['thumb_up']) && $campaign['Like'][0]['thumb_up'] == false): ?>active<?php endif; ?>">
                                            <i class="material-icons">thumb_down</i>
                                        </a>

                                        <?php
                                        $this->MooPopup->tag(array(
                                                 'href'=>$this->Html->url(array("controller" => "likes",
                                                                                "action" => "ajax_show",
                                                                                "plugin" => false,
                                                                                'Campaign_Campaign',
                                                                                $campaign['Campaign']['id'], 1
                                                                            )),
                                                 'title' => __('People Who DisLike This'),
                                                 'innerHtml'=>  '<span class="dislikeCount">' . $campaign['Campaign']['dislike_count'] . '</span>',
                                        ));
                                        ?>
                                        <?php endif; ?>
     <a href="<?php echo  $this->request->base ?>/fundraisings/view/<?php echo  $campaign['Campaign']['id'] ?>/<?php echo seoUrl($campaign['Campaign']['title'])?>">
                                            <i class="material-icons">share</i> <span><?php echo  $campaign['Campaign']['share_count'] ?></span>
                                        </a>

                                    </div>


                                </div>
                            </div>
                <div class="clear"></div>
                <div class="extra_info">
                    <?php $this->Html->rating($campaign['Campaign']['id'],'fundraising', 'Campaign'); ?>
                </div>
            </div>
        </li>
    <?php
        $i++;
        endforeach;
    }
    else
        echo '<div class="clear text-center">' . __( 'No more results found') . '</div>';
    ?>
    <?php if (isset($more_url)&& !empty($more_result)): ?>
        <?php $this->Html->viewMore($more_url) ?>
    <?php endif; ?>
    </ul>
<?php endif; ?>

<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooCampaign"], function($,mooFundraising) {
        mooFundraising.initOnListing();
    });
</script>
<?php else: ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooFundraising'), 'object' => array('$', 'mooFundraising'))); ?>
mooFundraising.initOnListing();
<?php $this->Html->scriptEnd(); ?> 
<?php endif; ?>