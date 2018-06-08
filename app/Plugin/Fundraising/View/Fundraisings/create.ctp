<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooFundraising'), 'object' => array('$', 'mooFundraising'))); ?>
    mooFundraising.initOnCreate();
<?php $this->Html->scriptEnd(); ?>

<?php $this->setCurrentStyle(4) ?>
<?php
    $fundraisingHelper = MooCore::getInstance()->getHelper('Fundraising_Fundraising');
    $tags_value = '';
    if (!empty($tags)){
        $tags_value = implode(', ', $tags);
    }
?>

<div class="create_form">
    <div class="bar-content">
        <div class="content_center">
            <div class="box3">
                <form id="createForm">
                <?php
                    $currency = Configure::read('Config.currency');
                    echo $this->Form->hidden('thumbnail', array('value' => $campaign['Campaign']['thumbnail']));
                    echo $this->Form->hidden('campaign_photo_ids');
                    if (!empty($campaign['Campaign']['id']))
                        echo $this->Form->hidden('id', array('value' => $campaign['Campaign']['id']));
                ?>
                    <div class="mo_breadcrumb">
                        <h1><?php if (empty($campaign['Campaign']['id'])) echo __( 'Create New Campaign'); else echo __( 'Edit Campaign');?></h1>
                    </div>
                    <div class="full_content p_m_10">
                            <div class="form_content">
                                <ul>
                                    <li>
                                        <div class="col-md-2">
                                            <label><?php echo __( 'Campaign Title')?></label>
                                        </div>
                                        <div class="col-md-10">
                                            <?php echo $this->Form->text( 'title', array( 'value' => $campaign['Campaign']['title'] ) ); ?>
                                        </div>
                                        <div class="clear"></div>
                                    </li>

                                    <li>
                                        <div class="col-md-2">
                                        <label><?php echo __( 'Category')?></label>
                                        </div>
                                        <div class="col-md-10">
                                        <?php echo $this->Form->select( 'category_id', $cats, array( 'value' => $campaign['Campaign']['category_id'] ) ); ?>
                                        </div>
                                        <div class="clear"></div>
                                    </li>
                                    <li>
                                        <div class="col-md-2">
                                        <label><?php echo __( 'Description')?></label>
                                        </div>
                                        <div class="col-md-10">
                                            <?php echo $this->Form->tinyMCE( 'body', array( 'value' => $campaign['Campaign']['body'], 'id' => 'editor' ) ); ?>
                                        </div>
                                        <div class="clear"></div>
                                    </li>
                                    <li>
                                        <div class="col-md-2">
                                            <label><?php echo __( 'Upload photos to description')?></label>
                                        </div>
                                        <div class="col-md-10">
                                            <div id="images-uploader">
                                                <div id="attachments_upload"></div>
                                                <a href="javascript:void(0)" class="button button-primary" id="triggerUpload"><?php echo __( 'Upload Queued Files')?></a>
                                            </div>
                                        </div>
                                        <div class="clear"></div>
                                    </li>
                                    <li>
                                        <div class="col-md-2">
                                            <label><?php echo __( 'Target amount')?>
                                                (<a data-html="true" href="javascript:void(0);" class="tip" original-title="<?php echo __('Enter 0 for unlimited goal');?>">?</a>)
                                            </label>
                                        </div>
                                        <div class="col-md-10">
                                            <?php echo $this->Form->text( 'target_amount', array( 'value' => $campaign['Campaign']['target_amount'] ) ); ?>
                                        </div>
                                        <div class="clear"></div>
                                    </li>
                                    <li>
                                        <div class="col-md-2">
                                            <label><?php echo __( 'Expiration date')?></label>
                                        </div>
                                        <div class="col-md-10">
                                            <?php echo $this->Form->text( 'expire', array( 'class' => 'datepicker', 'value' => $campaign['Campaign']['expire'] ) ); ?>
                                            <?php
                                            echo $this->Form->checkbox('unlimited', array(
                                                'hiddenField' => false,
                                                'class' => 'checkbox-campaign',
                                                'checked' => (empty($campaign['Campaign']['expire']) && $campaign['Campaign']['id']) ? true : false
                                                ));
                                                echo __('Set this to unlimited time');
                                            ?>
                                        </div>
                                        <div class="clear"></div>
                                    </li>
                                    <li>
                                        <div class="col-md-2">
                                            <label><?php echo __( 'Predefined donation amounts')?>
                                                (<a data-html="true" href="javascript:void(0);" class="tip" original-title="<?php echo __('Define list of amount of money that people can select to donate instead of typing. Please enter number and separate by comma');?>">?</a>)
                                            </label>
                                        </div>
                                        <div class="col-md-10">
                                            <?php echo $this->Form->text( 'predefined', array( 'value' => $campaign['Campaign']['predefined'] ) ); ?>
                                            <?php echo $currency['Currency']['symbol'];?>
                                        </div>
                                        <div class="clear"></div>
                                    </li>
                                    <li>
                                        <div class="col-md-2">
                                            <label><?php echo __( 'Location')?></label>
                                        </div>
                                        <div class="col-md-10">
                                            <?php echo $this->Form->text( 'location', array( 'value' => $campaign['Campaign']['location'] ) ); ?>
                                        </div>
                                        <div class="clear"></div>
                                    </li>
                                    <li>
                                        <div class="col-md-2">
                                            <label><?php echo __( 'Payment methods')?></label>
                                        </div>
                                        <div class="col-md-10">
                                            <div>
                                                <?php
                                                    echo $this->Form->checkbox('paypal', array(
                                                        'hiddenField' => false,
                                                        'class' => 'checkbox-campaign',
                                                        'checked' => $campaign['Campaign']['paypal'] ? true : false
                                                    ));
                                                    echo __('Paypal');
                                                ?>
                                                <?php echo $this->Form->text('paypal_email', array('placeholder' => __('Paypal email'), 'value' => $campaign['Campaign']['paypal_email'] ) ); ?>
                                            </div>
                                            <div>
                                                <?php
                                                    echo $this->Form->checkbox('bank', array(
                                                    'hiddenField' => false,
                                                    'class' => 'checkbox-campaign',
                                                    'checked' => $campaign['Campaign']['bank'] ? true : false
                                                    ));
                                                    echo __('Bank transfer. Please provide details into the field below');
                                                ?>
                                            </div>
                                            <div>
                                                <?php echo $this->Form->textarea('bank_info', array('value' => $campaign['Campaign']['bank_info'] ) ); ?>
                                            </div>
                                        </div>
                                        <div class="clear"></div>
                                    </li>
                                    <li>
                                        <div class="col-md-2">
                                            <label><?php echo __( 'Term and conditions')?></label>
                                        </div>
                                        <div class="col-md-10">
                                            <?php echo $this->Form->textarea( 'term', array( 'value' => $campaign['Campaign']['term'] ) ); ?>
                                        </div>
                                        <div class="clear"></div>
                                    </li>

                                    <li>
                                        <div class="col-md-2">
                                            <label><?php echo __( 'Thumbnail')?>(<a original-title="<?php echo __( 'Thumbnail only display on campaign listing and share campaign to facebook')?>" class="tip" href="javascript:void(0);">?</a>)</label>
                                        </div>
                                        <div class="col-md-10">
                                            <div id="campaign_thumnail"></div>
                                            <div id="campaign_thumnail_preview">
                                                <?php if (!empty($campaign['Campaign']['thumbnail'])): ?>
                                                    <img width="150" src="<?php echo $fundraisingHelper->getImage($campaign, array('prefix' => '150_square'))?>" />
                                                <?php else: ?>
                                                    <img width="150" src="" style="display: none;" />
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="clear"></div>
                                    </li>
                                    <li>
                                        <div class="col-md-2">
                                        <label><?php echo __( 'Tags')?></label>
                                        </div>
                                        <div class="col-md-10">
                                            <?php echo $this->Form->text( 'tags', array( 'value' => $tags_value ) ); ?> <a href="javascript:void(0)" class="tip profile-tip" title="<?php echo __( 'Separated by commas or space')?>">(?)</a>
                                        </div>
                                        <div class="clear"></div>
                                   </li>

                                </ul>
                                <div class="col-md-2">&nbsp;</div>
                                <div class="col-md-10">
                                    <div style="margin:20px 0">
                                        <button type='button' class='btn btn-action' id="saveBtn"><?php echo __( 'Save')?></button>

                                        <?php if ( !empty( $campaign['Campaign']['id'] ) ): ?>
                                        <a href="<?php echo $this->request->base?>/fundraisings/view/<?php echo $campaign['Campaign']['id']?>" class="button"><?php echo __( 'Cancel')?></a>
                                        <?php endif; ?>
                                        <?php if ( ($campaign['Campaign']['user_id'] == $uid ) || ( !empty( $campaign['Campaign']['id'] ) && $cuser['Role']['is_admin'] ) ): ?>
                                        <a href="javascript:void(0)" data-id="<?php echo $campaign['Campaign']['id']?>" class="button deleteCampaign"><?php echo __( 'Delete')?></a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="error-message" id="errorMessage" style="display:none"></div>
                                </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>