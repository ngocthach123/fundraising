<?php $fundraisingHelper = MooCore::getInstance()->getHelper('Fundraising_Fundraising');
    $currency = Configure::read('Config.currency');
?>

<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooFundraising'), 'object' => array('$', 'mooFundraising'))); ?>
mooFundraising.initOnView();
<?php $this->Html->scriptEnd(); ?> 

<?php $this->setNotEmpty('west');?>
<?php $this->start('west'); ?>
	<div class="box2">
        <img class="detail-campaign-thumb" src="<?php echo $fundraisingHelper->getImage($campaign, array('prefix' => '300_square'))?>">
        <h2><?php echo h($campaign['Campaign']['title'])?></h2>
        <?php echo $this->element( 'Fundraising.sidebar/detail_menu', array() ); ?>
	</div>

    <?php if(!empty($tags)): ?>
        <div class="box2">
            <h3><?php echo __( 'Tags')?></h3>
            <div class="box_content">
                <?php echo $this->element( 'blocks/tags_item_block' ); ?>
            </div>
        </div>
    <?php endif; ?>
<?php $this->end();?>

<?php
switch($type){
    case 'mail':
        echo $this->element('ajax/email_setting', array());
        break;
    case 'donor':
        echo $this->element('ajax/donor', array('currency'=>$currency));
        break;
    default:
        echo $this->element('ajax/detail', array('currency'=>$currency));
        break;
}
?>