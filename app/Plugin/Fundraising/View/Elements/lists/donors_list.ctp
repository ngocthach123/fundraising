<?php if(Configure::read('Fundraising.fundraising_enabled') == 1): ?>
	<?php
	 $currency = Configure::read('Config.currency');
	$fundraisingHelper = MooCore::getInstance()->getHelper('Fundraising_Fundraising');
	if (!empty($donors) && count($donors) > 0)
	{
		foreach ($donors as $donor):
			switch($donor['CampaignDonor']['method']){
				case 'paypal':
					$method = __('Paypal');
				break;
				default:
					$method = __('offline payment');
					break;
			}
	?>
		<li class="full_content p_m_10">
			<div class="donor-name"><a href="<?php echo $donor['User']['moo_href'];?>"><?php echo $donor['CampaignDonor']['name'];?></a></div>
			<div class="donor-info"><?php echo __('Donated %s via %s', $currency['Currency']['symbol'].$donor['CampaignDonor']['amount'], $method);?> <?php echo $this->Moo->getTime($donor['CampaignDonor']['created'], Configure::read('core.date_format'), $utz)?></div>
			<?php if($donor['CampaignDonor']['status']):?>
				<div class="donor-status"><?php echo __('Status');?>: <span class="receive"><?php echo __('received') ?></span></div>
			<?php else:?>
				<div class="donor-status"><?php echo __('Status');?>: <span class="pending"><?php echo __('pending') ?></span></div>
			<?php endif;?>
			<div class="donor-message"><span><?php echo __('Message from donor:')?></span> <?php echo $this->Text->truncate($donor['CampaignDonor']['message'], 150, array('exact' => false));?></div>
		</li>
	<?php
		endforeach;
	}
	else
		echo '<div class="clear text-center">' . __( 'No more results found') . '</div>';
	?>
	<?php if (isset($more_url)&& !empty($more_result)): ?>
		<?php $this->Html->viewMore($more_url) ?>
	<?php endif; ?>
<?php endif; ?>

<?php if($this->request->is('ajax')): ?>
<script type="text/javascript">
    require(["jquery","mooCampaign"], function($,mooFundraising) {
        mooFundraising.initOnDonorListing();
    });
</script>
<?php else: ?>
<?php $this->Html->scriptStart(array('inline' => false, 'domReady' => true, 'requires'=>array('jquery','mooFundraising'), 'object' => array('$', 'mooFundraising'))); ?>
    mooFundraising.initOnDonorListing();
<?php $this->Html->scriptEnd(); ?> 
<?php endif; ?>