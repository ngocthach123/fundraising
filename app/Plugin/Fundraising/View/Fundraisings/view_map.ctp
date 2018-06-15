
<div class="title-modal">
    <?php echo __d('fundraising', 'Map');?>
    <button class="close" data-dismiss="modal" type="button">
        <span aria-hidden="true">×</span>
    </button>
</div>
<div class="modal-body">
    <?php if(!empty($lat)):?>
        <div id="map_canvas" address="<?php echo $address;?>" lat="<?php echo $lat;?>" lng="<?php echo $lng;?>" style="width: 100%; height: 300px; position: relative; overflow: hidden;"></div>
    <?php endif;?>
    <br/><br/>
    <button class="button button-action" href="javascript:void(0)" data-dismiss="modal">
        <?php echo __d('fundraising', 'Close');?>
    </button>
</div>

<?php if($this->request->is('ajax')): ?>
    <script type="text/javascript">
        require(["jquery","mooFundraising"], function($,mooFundraising) {
            mooFundraising.initViewMap();
        });
    </script>
<?php else: ?>
    <?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooFundraising'), 'object' => array('$', 'mooFundraising'))); ?>
    mooFundraising.initViewMap();
    <?php $this->Html->scriptEnd(); ?>
<?php endif; ?>