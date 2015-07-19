<div class="container">
<?php
$base = $this->fc->request->thisUrl();
if ($id = $this->data->getUid()):
?>
	<table class="table">
		<tbody>
			<tr>
				<th><?php echo TR::html('form','name') ?></th>
				<td><?php echo $this->data->html('name'); ?></td>
			</tr>
			<tr>
				<th><?php echo TR::html('form','address') ?></th>
				<td><?php echo preg_replace('/\n/s', ', ', $this->data->html('address')); ?></td>
			</tr>
		</tbody>
	</table>
<?php
else:
?>
	<p class="empty"><?php echo TR::html('data','empty') ?></p>
<?php
endif;
?>
</div>