<div class="container">
<?php
echo $this->data->iForm('user_edit','post');
echo $this->data->iHidden('id');
?>
		<table class="table">
			<tbody>
				<tr>
					<th class="span3"><?php echo TR::html('form','username') ?></th>
					<td><?php echo $this->data->html('username'); ?></td>
				</tr>
				<tr>
					<th><?php echo TR::html('form','password_change') ?></th>
					<td>
						<?php 
						if ($this->data->get('password')) { // don't display encoded password !
							$this->data->set('password', ''); 
						}
						echo $this->data->iText('password', false); // no autocomplete ?> 
						<i style="color: #999"><?php echo TR::html('form', 'blank_no_change')?></i>
					</td>
				</tr>
				<tr>
					<th><?php echo TR::html('form','nickname') ?></th>
					<td><?php echo $this->data->iText('nickname'); ?></td>
				</tr>
				<tr>
					<th><?php echo TR::html('form','language') ?></th>
					<td><?php echo $this->data->iSelect('language'); ?></td>
				</tr>
				<tr>
					<th><?php echo TR::html('form','address') ?></th>
					<td><?php echo $this->data->iTextarea('address'); ?></td>
				</tr>
				<tr>
					<th></th>
					<td><button name="save" value="1" type="submit" class="btn btn-success"><?php echo TR::html('button', 'save')?></button></td>
				</tr>
			</tbody>
		</table>
	</form>
</div>