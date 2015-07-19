<div class="container">
<?php
echo $this->data->iForm('company_edit','post');
echo $this->data->iHidden('id');
?>
		<table class="table">
			<tbody>
				<tr>
					<th><?php echo TR::html('form','name') ?></th>
					<td><?php echo $this->data->iText('name'); ?></td>
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