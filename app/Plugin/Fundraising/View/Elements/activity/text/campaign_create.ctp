<?php
$subject = MooCore::getInstance()->getItemByType($activity['Activity']['type'], $activity['Activity']['target_id']);
$name = key($subject);
?>
<?php if ($activity['Activity']['target_id']): ?>

    <?php $show_subject = MooCore::getInstance()->checkShowSubjectActivity($subject); ?>

    <?php if ($show_subject): ?>
        &rsaquo; <a href="<?php echo $subject[$name]['moo_href'] ?>"><?php echo h($subject[$name]['moo_title']) ?></a>
    <?php else: ?>
        <?php echo __d('fundraising','created a new campaign'); ?>
    <?php endif; ?>

<?php else: ?>
    <?php echo __d('fundraising','created a new campaign'); ?>
<?php endif; ?>