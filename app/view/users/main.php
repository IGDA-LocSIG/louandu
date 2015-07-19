<div class="container">
<?php

$canEdit = $this->fc->user->checkAcl('admin_user');

if ($cp = $this->data->count()) {
	$base = $this->fc->request->thisUrl();
?>
<table class="table table-striped table-vctr">
	<thead>
		<tr>
			<th><?php echo TR::html('form','username') ?></th>
			<th><?php echo TR::html('form','name') ?></th>
			<th><?php echo TR::html('form','email') ?></th>
			<th><?php echo TR::html('form','rate') ?></th>
		</tr>
	</thead>
	<tbody class="click-rows">
	<?php
	$current_company_name = '';
	while ($this->data->next()) {
		$id = $this->data->getUid();
		$company_name = $this->data->html('company__name');
		if ($current_company_name != $company_name): ?>
		<tr class="info">
			<th colspan="4">
				<i class="icon-briefcase"></i>
				<a href="<?php echo APP_WWW_URI."companies/details/".$this->data->html('company__id'); ?>">
					<?php echo strtoupper($company_name); $current_company_name = $company_name; ?>
				</a>
		 	</th>
		</tr>
		<?php 
		endif;
		?>
		<tr<?php echo ($this->data->get('enabled') ? '' : ' class="disabled"') ?>>
			<td>
				<a href="<?php echo APP_WWW_URI."users/details/".$id; ?>"><?php echo $this->data->html('username'); ?></a>
			</td>
			<td>
				<?php echo $this->data->html('nickname'); ?>
			</td>
			<td>
				<?php echo $this->data->html('email'); ?>
			</td>
			<td>
				<?php 
				if ($canEdit) {
					echo '<a href="'.APP_WWW_URI.'users/edit/'.$id.'" class="btn btn-mini pull-right"><i class="icon-edit"></i></a>';
				}
				
				if ($this->data->html('rate_translate') || $this->data->html('rate_review')): 
				?>
				<span class="badge">T</span> <?php echo $this->data->html('rate_translate'); ?>
				<span class="badge" style="margin-left: 1em">R</span> <?php echo $this->data->html('rate_review'); ?>
				<span class="badge" style="margin-left: 1em">H</span> <?php echo $this->data->html('rate_hourly'); ?>
				<?php 
				endif; 
				?>
			</td>
		</tr>		
		<?php
	}
	?>
	</tbody>
</table>

<div class="row">
	<div class="pagination span9">
		<?php
			echo $this->data->pagination($this->fc->request->thisUrl(true), 'pg'); 
		?>
	</div>
	<div class="pagination pagination-right span3">
		<ul><li class="active"><a href="#"><?php echo $this->data->total()." ".TR::html('data', ($this->data->total() > 1 ? 'items_found' : 'item_found')) ?></a></li></ul>
	</div>
</div>
<?php
} else {
?>
<p class="empty"><?php echo TR::html('data','empty') ?></p>
<?php
}
?>
</div>