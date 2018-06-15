<?php $this->setNotEmpty('west');?>
<?php $this->start('west'); ?>
	<div class="box2 filter_block">
            <h3 class="visible-xs visible-sm"><?php echo __d('fundraising', 'Browse')?></h3>
            <div class="box_content">
		<?php echo $this->element('sidebar/menu'); ?>
                <?php echo $this->element('lists/categories_list')?>
		<?php echo $this->element('sidebar/search'); ?>
            </div>
	</div>
<?php $this->end(); ?>


<div class="bar-content">  
    <div class="content_center">
    
        <div class="mo_breadcrumb">
            <h1><?php echo __d('fundraising', 'Campaigns')?></h1>
            <?php 
            if (!empty($uid)):
            ?>
                <?php
                echo $this->Html->link(__d('fundraising','Create New Campaign'), array(
                    'plugin' => 'Fundraising',
                    'controller' => 'fundraisings',
                    'action' => 'create'
                ), array(
                    'class' => 'button button-action topButton button-mobi-top'
                ));
                ?>

            <?php
            endif;
            ?>
        </div>

		<?php 
		if ( !empty( $this->request->named['category_id'] )  || !empty($cat_id)){
                    if (empty($cat_id)){
                        $cat_id = $this->request->named['category_id'];
                    }
                    echo $this->element( 'lists/campaigns_list', array( 'more_url' => '/fundraisings/browse/category/' . $cat_id . '/page:2' ) );
                }
		else {
                    echo $this->element( 'lists/campaigns_list', array( 'more_url' => '/fundraisings/browse/all/page:2' ) );
                }
		?>
    </div>
</div>
