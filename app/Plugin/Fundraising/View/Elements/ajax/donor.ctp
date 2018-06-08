<?php
    $target = $campaign['Campaign']['target_amount'] ? $currency['Currency']['symbol'].$campaign['Campaign']['target_amount'] : __('Unlimited');
?>
<div class="bar-content full_content p_m_10">
    <div class="content_center">
        <h3 class="donor-title"><?php echo __('Donors (%s donors, total raised %s, target %s)', $campaign['Campaign']['donor_count'], $campaign['Campaign']['raised_amount'],$target)?></h3>
        <ul class="campaign-donor-list" id="list-content">
            <?php echo $this->element( 'lists/donors_list', array() );?>
        </ul>
    </div>
</div>

