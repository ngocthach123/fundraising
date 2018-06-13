<div class="bar-content">
    <div class="content_center">
        <div class="mo_breadcrumb">
            <h3><?php echo __d('fundraising','You can customize contain of email that send to donor when they donated here');?></h3>
        </div>
        <div id="msg_success" style="display: none" class="Metronic-alerts alert alert-success fade in"><?php echo __d('fundraising','Your changes have been saved');?></div>

        <div class="create_form">
            <div class="content_center">
                <div class="box3">
                    <form action="<?php echo  $this->request->base; ?>/fundraisings/email_setting" id="formMailSetting" method="post">
                        <?php echo $this->Form->hidden('target_id', array('value' => $campaign['Campaign']['id'])); ?>
                        <div class="full_content p_m_10">
                            <div class="form_content">
                                <ul>
                                    <li>
                                        <div class="col-md-2">
                                            <?php echo __('Subject');?>
                                        </div>
                                        <div class="col-md-10">
                                            <?php echo $this->Form->text('subject', array('value' => !empty($mail) ? $mail['FundraisingMail']['subject'] : '')); ?>
                                        </div>
                                        <div class="clear"></div>
                                    </li>
                                    <li>
                                        <div class="col-md-2">
                                            <?php echo __('Message');?>
                                        </div>
                                        <div class="col-md-10">
                                            <?php echo $this->Form->tinyMCE('content', array('value' => !empty($mail) ? $mail['FundraisingMail']['content'] : '')); ?>
                                            <p><?php echo __('Please do not change [donor_name] and [donation_url] because it will auto replace by system');?></p>
                                        </div>
                                        <div class="clear"></div>
                                    </li>
                                    <li>
                                        <div class="col-md-2"></div>
                                        <div class="col-md-10">
                                            <a id="btn_save" class='btn btn-action'><?php echo __d('fundraising' ,'Save')?></a>
                                        </div>
                                        <div class="clear"></div>
                                    </li>
                                    <li>
                                        <div class="error-message" id="errorMessage" style="display:none"></div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooFundraising"], function($,mooFundraising) {
        mooFundraising.initMailSetting();
    });
</script>
<?php else: ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooFundraising'), 'object' => array('$', 'mooFundraising'))); ?>
mooFundraising.initMailSetting();
<?php $this->Html->scriptEnd(); ?>
<?php endif; ?>