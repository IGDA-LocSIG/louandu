<?php
$mid = $this->data->getUid();
?>
<div class="container">
	<form action="<?php echo APP_WWW_URI.'task/edit/'.($mid?$mid:'new').'.html'; ?>" method="post">
		<?php
		 echo $this->data->iHidden('id');
		?>
		<table class="table">
			<tbody>
				<tr>
					<th><?php echo TR::html('form','title') ?></th>
					<td class="xxl"><?php echo $this->data->iText('title'); ?></td>
				</tr>
				<tr>
					<th><?php echo TR::html('form','deadline') ?></th>
					<td>
						<span class="input-append"><input type="text" name="deadline" value="<?php echo $this->data->getDeadlineValue(); ?>" class="input-mini datepicker" placeholder="dd/mm" /><span class="add-on"><i class="icon-calendar"></i></span></span>
							&nbsp;
						<span class="input-append"><input type="text" name="deadtime" value="<?php echo $this->data->html('deadtime'); ?>" class="input-mini" placeholder="hh:mm" /><span class="add-on"><i class="icon-time"></i></span></span>
					</td>
				</tr>
				<tr>
					<th><?php echo TR::html('form','company') ?></th>
					<td><?php echo $this->companies->iSelectList('company', 0,'---','id="id_company" class="combrlist"'); ?></td>
				</tr>
				<tr>
					<th><?php echo TR::html('form','member') ?></th>
					<td><select name="member_id" id="id_company_member"><option value="<?php echo $this->data->get('member_id'); ?>">loading...</option></select></td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td>
						<label class="checkbox">
							<input type="checkbox" name="public" value="1"<?php
								if ($this->data->value('public')) {
									echo ' checked="checked"';
								}
							?> />
							Public (show to all users)
						</label>
					</td>
				</tr>
				<tr>
					<th><?php echo TR::html('form','status') ?></th>
					<td><?php echo $this->data->iSelect('status'); ?></td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td>
						<label class="checkbox">
							<input type="checkbox" name="archived" value="1"<?php
								if ($this->data->value('archived')) {
									echo ' checked="checked"';
								}
							?> />
							Archived
						</label>
					</td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<th></th>
					<td>
						<button name="save" value="1" type="submit" class="btn btn-success"><i class="icon-ok icon-white"></i><?php echo ' '.TR::html('button', 'save')?></button>
						<?php
						if ($mid) {
						?>
						<a href="<?php echo APP_WWW_URI.'task/delete/'.$mid.'.html'; ?>" onclick="return confirm('delete this task?')" 
							class="btn btn-danger" style="margin-left:15px"><i class="icon-trash icon-white"></i><?php echo ' '.TR::html('button', 'delete')?></a>
						<?php
						}
						?>
					</td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>