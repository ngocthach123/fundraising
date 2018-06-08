<li>
    <p><?php echo __('Thank for your donation! Please send us your details such as bank account so that we can verify and update into campaign. Your information will not appear if you selected donate as anonymous.');?></p>
</li>
<li>
    <div class="col-md-2">
        <label><?php echo __( 'Your name')?></label>
    </div>
    <div class="col-md-10">
        <?php echo $this->Form->text('name', array( 'value' => !empty($cuser['name']) ? $cuser['name'] : '' ) ); ?>
    </div>
    <div class="clear"></div>
</li>
<li>
    <div class="col-md-2">
        <label><?php echo __( 'Your email')?></label>
    </div>
    <div class="col-md-10">
        <?php echo $this->Form->text('email', array( 'value' => !empty($cuser['email']) ? $cuser['email'] : '' ) ); ?>
    </div>
    <div class="clear"></div>
</li>
<li>
    <div class="col-md-2">
        <label><?php echo __( 'Other details like bank account...(optinal)')?></label>
    </div>
    <div class="col-md-10">
        <?php echo $this->Form->textarea('other_detail', array( 'value' => '' ) ); ?>
    </div>
    <div class="clear"></div>
</li>
<?php if ($this->Moo->isRecaptchaEnabled()): ?>
    <li>
        <div class="col-md-2"></div>
        <div class="col-md-10">
            <div class="captcha_box">
                <script src='<?php echo $this->Moo->getRecaptchaJavascript();?>'></script>
                <div class="g-recaptcha" data-sitekey="<?php echo $this->Moo->getRecaptchaPublickey()?>"></div>
            </div>
        </div>
    </li>
<?php endif; ?>
<li>
    <a id="btn_send_payoffline" class='btn btn-action'><?php echo __d('fundraising', 'Send')?></a>
</li>
<li>
    <div class="error-message" id="errorMessage" style="display:none"></div>
</li>