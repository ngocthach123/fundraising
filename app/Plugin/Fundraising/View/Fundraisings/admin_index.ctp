<?php
echo $this->Html->css(array('footable.core.min'), null, array('inline' => false));
echo $this->Html->script(array('footable'), array('inline' => false));

echo $this->element('admin/adminnav', array("cmenu" => "campaigns"));
$this->Paginator->options(array('url' => $this->passedArgs));
?>

<?php $this->Html->scriptStart(array('inline' => false)); ?>
$(document).ready(function(){
	$('.footable').footable();
});
<?php  $this->Html->scriptEnd(); ?>

<div id="center">
	<form method="post" action="<?php echo $this->request->base?>/admin/campaigns">
	<?php echo $this->Form->text('keyword', array('style' => 'float:right', 'placeholder' => 'Search by title'));?>
	<?php echo $this->Form->submit('', array( 'style' => 'display:none' ));?>
	</form>
	
	<h1><?php echo __('Campaigns Manager');?></h1>
	<form method="post" action="<?php echo $this->request->base?>/admin/campaigns/delete" id="deleteForm">
	<table class="mooTable footable" cellpadding="0" cellspacing="0">
		<thead>
		<tr>
			<th><?php echo $this->Paginator->sort('id', __('ID')); ?></th>
			<th><?php echo $this->Paginator->sort('title', __('Title')); ?></th>
			<th data-hide="phone"><?php echo $this->Paginator->sort('User.name', __('Author')); ?></th>
			<th data-hide="phone"><?php echo $this->Paginator->sort('Category.name', __('Category')); ?></th>
			<th data-hide="phone"><?php echo $this->Paginator->sort('Group.name', __('Group')); ?></th>
			<?php if ( $cuser['Role']['is_super'] ): ?>
			<th width="30"><input type="checkbox" onclick="toggleCheckboxes(this)"></th>
			<?php endif; ?>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($campaigns as $campaign): ?>
		<tr>
			<td><?php echo $campaign['Campaign']['id']?></td>
			<td><a href="<?php echo $this->request->base?>/campaigns/create/<?php echo $campaign['Campaign']['id']?>" target="_blank"><?php echo $this->Text->truncate(h($campaign['Campaign']['title']), 100, array('eclipse' => '...')) ?></a></td>
			<td><a href="<?php echo $this->request->base?>/admin/users/edit/<?php echo $campaign['User']['id']?>"><?php echo h($campaign['User']['name'])?></a></td>
			<td><?php echo $campaign['Category']['name']?></td>
			<td><?php echo $campaign['Group']['name']?></td>
			<?php if ( $cuser['Role']['is_super'] ): ?>
			<td><input type="checkbox" name="campaigns[]" value="<?php echo $campaign['Campaign']['id']?>" class="check"></td>
			<?php endif; ?>
		</tr>
		<?php endforeach ?>
		</tbody>
	</table>
	
	<div style="float:right">
    	<select onchange="doModeration(this.value, 'campaigns')">
    	    <option value="">With selected...</option>
    	    <option value="move">Move to</option>
    	    <option value="delete">Delete</option>
    	</select>
    	<?php echo $this->Form->select('category_id', $categories, array( 'onchange' => "confirmSubmitForm('Are you sure you want to move these campaigns', 'deleteForm')", 'style' => 'display:none' ) ); ?>
	</div>
	</form>
	
	<div class="pagination">
        <?php echo $this->Paginator->prev('« '.__('Previous'), null, null, array('class' => 'disabled')); ?>
		<?php echo $this->Paginator->numbers(); ?>
		<?php echo $this->Paginator->next(__('Next').' »', null, null, array('class' => 'disabled')); ?> 
    </div>
</div>