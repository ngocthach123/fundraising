<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooFundraising"], function($,mooFundraising) {
        mooFundraising.initReceiveDonor();
    });
</script>
<?php else: ?>
    <?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery', 'mooFundraising'), 'object' => array('$', 'mooFundraising'))); ?>
mooFundraising.initReceiveDonor();
    <?php $this->Html->scriptEnd(); ?>
<?php endif; ?>


<div class="title-modal">
    <?php echo __d('fundraising','Receive')?>
    <button style="width: inherit" type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
</div>
<div class="modal-body">
    <div class="create_form">
        <form id="formReceiveDonor">
            <input type="hidden" name="item_id" value="<?php echo $item_id;?>">
            <p><?php echo __d('fundraising','Are you sure you want to change status to received, email will send to notify donor');?></p>
            <ul class="list6 list6sm2" style="position: relative">
                <li>
                    <div class="col-md-12">
                        <?php echo __d('fundraising', 'Your message to donor is required')?>
                    </div>
                    <div class="col-md-12">
                        <?php echo $this->Form->textarea('message', array('value' => '')); ?>
                    </div>
                    <div class="clear"></div>
                </li>

                <li class="form-action">
                    <a href="javascript:void(0)" class="btn btn-action" id="btn_receive_donor"><?php echo __d('fundraising','Receive')?></a>
                    <button type="button" class="btn default" data-dismiss="modal"><?php echo __d('fundraising','Close')?></button>
                    <div class="clear"></div>
                </li>
            </ul>
        </form>

        <div class="error-message" style="display:none;margin-top:10px;"></div>
    </div>
</div>
