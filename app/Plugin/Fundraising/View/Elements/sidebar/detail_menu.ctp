<ul class="list2 menu-list"  id="browse">
    <li id="cp_info" class="<?php echo ($type != 'mail' && $type != 'donor') ? 'current' : '';?>"><a class="json-view no-ajax" href="<?php echo $campaign['Campaign']['moo_href'];?>"><?php echo __('Info');?></a></li>
    <li id="cp_email" class="<?php echo $type == 'mail' ? 'current' : '';?>"><a class="json-view no-ajax" href="<?php echo $this->request->base ?>/fundraisings/view/<?php echo $campaign['Campaign']['id'];?>/mail"><?php echo __('Thank you email settings');?></a></li>
    <li id="cp_donor" class="<?php echo $type == 'donor' ? 'current' : '';?>"><a class="json-view no-ajax" href="<?php echo $this->request->base ?>/fundraisings/view/<?php echo $campaign['Campaign']['id'];?>/donor"><?php echo __('Donor');?></a></li>
</ul>