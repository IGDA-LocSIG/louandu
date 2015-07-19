<div class="container">
<?php
$base = $this->fc->request->thisUrl();
if ($id = $this->data->getUid()):
?>
	<table class="table">
		<tbody>
			<tr>
				<th class="span3"><?php echo TR::html('form','username') ?></th>
				<td><?php echo $this->data->html('username'); ?></td>
			</tr>
			<tr>
				<th class="span3"><?php echo TR::html('form','password') ?></th>
				<td>●●●●●●●●</td>
			</tr>
			<tr>
				<th><?php echo TR::html('form','name') ?></th>
				<td><?php echo $this->data->html('nickname'); ?></td>
			</tr>
			<tr>
				<th><?php echo TR::html('form','language') ?></th>
				<td><?php echo $GLOBALS['config']['lang']['options'][$this->data->html('language')]; ?></td>
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