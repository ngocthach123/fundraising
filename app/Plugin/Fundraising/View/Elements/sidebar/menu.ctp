<ul class="list2 menu-list" id="browse">
    <li <?php if (empty($this->request->named['category_id'])): ?>class="current"<?php endif; ?> id="browse_all"><a class="json-view" data-url="<?php echo $this->request->base ?>/fundraisings/browse/open" href="<?php echo $this->request->base ?>/fundraisings"><?php echo __d('fundraising','Open Campaigns') ?></a></li>
    <li <?php if (!empty($type) && $type == 'expire'): ?>class="current"<?php endif; ?> id="browse_all"><a class="json-view" data-url="<?php echo $this->request->base ?>/fundraisings/browse/expire" href="<?php echo $this->request->base ?>/fundraisings"><?php echo __d('fundraising','Expired Campaigns') ?></a></li>
    <?php if (!empty($uid)): ?>
        <li id="my_fundraising"><a data-url="<?php echo $this->request->base ?>/fundraisings/browse/my" href="<?php echo $this->request->base ?>/fundraisings/browse/my"><?php echo __d('fundraising','My Campaigns') ?></a></li>
        <li id="friend_fundraising"><a data-url="<?php echo $this->request->base ?>/fundraisings/browse/friends" href="<?php echo $this->request->base ?>/fundraisings/browse/friends"><?php echo __d('fundraising',"Friends' Campaigns") ?></a></li>
    <?php endif; ?>
    <li class="separate"></li>
</ul>