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