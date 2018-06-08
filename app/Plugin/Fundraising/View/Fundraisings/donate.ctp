<div class="bar-content">
    <div class="content_center">
        <div class="create_form">
            <div class="content_center">
                <div class="box3">
                    <form action="" id="formDonation" method="post">
                        <?php echo $this->Form->hidden( 'target_id', array( 'value' => $campaign['Campaign']['id'] ) ); ?>
                        <div class="full_content p_m_10">
                            <div class="form_content">
                                <ul id="pay_step_1">
                                    <li>
                                        <span class="form-title"><?php echo __('How much do you want to donate?');?></span>
                                        <?php if(!empty($campaign['Campaign']['predefined'])):?>
                                            <div class="wrap-predefined">
                                                <?php foreach (explode(',',$campaign['Campaign']['predefined']) as $price):?>
                                                    <a class="pre-item" data-value="<?php echo $price;?>"><?php echo $price;?></a>
                                                <?php endforeach;?>
                                            </div>
                                        <?php endif;?>
                                        <label class="form-label"><?php echo __('Your Donation');?></label>:
                                        <?php echo $this->Form->text( 'amount', array( 'value' => '' ) ); ?>
                                    </li>
                                    <li>
                                        <?php
                                            echo $this->Form->checkbox('anonymous', array(
                                            'hiddenField' => false,
                                            'class' => 'checkbox-donate',
                                            'checked' => false
                                            ));
                                            echo __('Donate as anonymous');
                                        ?>
                                    </li>
                                    <li>
                                        <?php
                                            echo $this->Form->checkbox('show_feed', array(
                                        'hiddenField' => false,
                                        'class' => 'checkbox-donate',
                                        'checked' => false
                                        ));
                                        echo __('Do not show my donation at main feed');
                                        ?>
                                    </li>
                                    <li>
                                        <label class="form-label"><?php echo __('Leave your message (optional)');?></label>:
                                        <?php echo $this->Form->textarea( 'message', array( 'value' => '') ); ?>
                                        <span class="form-note"><?php echo __('Let them know why you donated, to honour a loved one or send a word of encouragement. Your comment will appear on the campiagn page');?></span>
                                    </li>
                                    <li>
                                        <?php
                                            echo $this->Form->checkbox('accept_term', array(
                                        'hiddenField' => false,
                                        'class' => 'checkbox-donate',
                                        'checked' => false
                                        ));
                                        echo __('I have read and accepted all the ');
                                        ?>
                                        <a id="view_term" href="#"><?php echo __('terms and conditions');?></a>
                                        <div id="term_content"><?php echo $campaign['Campaign']['term'];?></div>
                                    </li>
                                    <?php if($campaign['Campaign']['bank']):?>
                                        <li>
                                            <p><?php echo __('For offline payment please send money to the following details:');?></p>
                                            <p><?php echo $campaign['Campaign']['bank_info'];?></p>
                                        </li>
                                    <?php endif;?>
                                    <li>
                                        <?php if($campaign['Campaign']['paypal']):?>
                                            <a id="btn_pay_paypal" class='btn btn-action'><?php echo __d('fundraising' ,'Paypal')?></a>
                                        <?php endif;?>
                                        <?php if($campaign['Campaign']['bank']):?>
                                            <a id="btn_pay_offline" class='btn btn-action'><?php echo __d('fundraising' ,'Pay Offline')?></a>
                                        <?php endif;?>
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
        mooFundraising.initDonation();
    });
</script>
<?php else: ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooFundraising'), 'object' => array('$', 'mooFundraising'))); ?>
mooFundraising.initDonation();
<?php $this->Html->scriptEnd(); ?>
<?php endif; ?>