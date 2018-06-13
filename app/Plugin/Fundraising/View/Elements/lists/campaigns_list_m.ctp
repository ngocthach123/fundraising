<div class="content_center_home">
    <div class="mo_breadcrumb">
        <h1><?php echo __('Campaigns') ?></h1>
        <a href="<?php echo $this->request->base ?>/fundraisings/create" class="topButton button button-action button-mobi-top"><?php echo __('Create New Campaign') ?></a>

    </div>
    <ul class="list6 comment_wrapper" id="list-content">
        <?php echo $this->element('lists/campaigns_list'); ?>
    </ul>
</div>