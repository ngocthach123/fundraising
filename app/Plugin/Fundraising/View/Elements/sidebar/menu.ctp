<ul class="list2 menu-list" id="browse">
    <li <?php if (empty($this->request->named['category_id'])): ?>class="current"<?php endif; ?> id="browse_all"><a class="json-view" data-url="<?php echo $this->request->base ?>/fundraisings/browse/all" href="<?php echo $this->request->base ?>/fundraising"><?php echo __('All Campaigns') ?></a></li>
    <?php if (!empty($uid)): ?>
        <li id="my_fundraising"><a data-url="<?php echo $this->request->base ?>/fundraisings/browse/my" href="<?php echo $this->request->base ?>/fundraisings/browse/my"><?php echo __('My Campaigns') ?></a></li>
        <li id="friend_fundraising"><a data-url="<?php echo $this->request->base ?>/fundraisings/browse/friends" href="<?php echo $this->request->base ?>/fundraisings/browse/friends"><?php echo __("Friends' Campaigns") ?></a></li>
    <?php endif; ?>
    <li class="separate"></li>
</ul>