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
				<th><?php echo TR::html('security','account_enabled') ?></th>
				<td>
					<?php 
					if ($this->data->isEmpty('hidden')) {
						echo '<i class="'.($this->data->get('enabled')?'icon-ok':'icon-remove').'"></i> ';
						echo TR::html('data', ($this->data->get('enabled') ? 'yes' : 'no'));
					} else {
						echo '<i class="icon-remove-circle"></i> <span style="color:#c00">account is deleted</a>';
					}
					?>
				</td>
			</tr>
			<tr>
				<th><?php echo TR::html('form','class') ?></th>
				<td><?php echo $this->data->html('class'); ?></td>
			</tr>
			<tr>
				<th><?php echo TR::html('form','nick_name') ?></th>
				<td><?php echo $this->data->html('nickname'); ?></td>
			</tr>
			<tr>
				<th><?php echo TR::html('form','email') ?></th>
				<td><?php echo $this->data->html('email'); ?></td>
			</tr>
			<tr>
				<th><?php echo TR::html('form','address') ?></th>
				<td><?php echo preg_replace('/\n/s', ', ', $this->data->html('address')); ?></td>
			</tr>
			<tr>
				<th><?php echo TR::html('form','company') ?></th>
				<td><?php echo $this->data->html('company__name'); ?></td>
			</tr>
			<tr>
				<th><?php echo TR::html('form','translation_rate') ?></th>
				<td><?php echo $this->data->get('rate_translate'); ?></td>
			</tr>
			<tr>
				<th><?php echo TR::html('form','review_rate') ?></th>
				<td><?php echo $this->data->html('rate_review'); ?></td>
			</tr>
			<tr>
				<th><?php echo TR::html('form','hourly_rate') ?></th>
				<td><?php echo $this->data->html('rate_hourly'); ?></td>
			</tr>
			<tr>
					<th><?php echo TR::html('form','payment_terms') ?></th>
					<td><?php echo $this->data->html('payterms'); ?></td>
				</tr>
			<tr>
				<th><?php echo TR::html('form','language') ?></th>
				<td><?php echo $GLOBALS['config']['lang']['options'][$this->data->html('language')]; ?></td>
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