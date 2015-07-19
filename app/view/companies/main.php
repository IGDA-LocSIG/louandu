<div class="container">
<?php
if ($cp = $this->data->count()) {
	$base = $this->fc->request->thisUrl();
?>
<table class="table table-striped table-vctr">
	<thead>
		<tr>
			<th class="span5"><?php echo TR::html('form','name') ?></th>
			<th><?php echo TR::html('form','address') ?></th>
		</tr>
	</thead>
	<tbody class="click-rows">
	<?php
	while ($this->data->next()) {
		$id = $this->data->getUid();
		$company_name = $this->data->html('name');
		?>
		<tr>
			<td><a href="<?php echo APP_WWW_URI."companies/edit/".$id; ?>"><?php echo $this->data->html('name'); ?></a></td>
			<td><?php echo preg_replace('/\n/s', ', ', $this->data->html('address')); ?></td>
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