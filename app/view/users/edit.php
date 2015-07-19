<div class="container">
<?php
echo $this->data->iForm('user_edit','post');
echo $this->data->iHidden('id');
?>
		<table class="table">
			<tbody>
				<tr>
					<th class="span3"><?php echo TR::html('form','username') ?></th>
					<td><?php echo $this->data->iText('username'); ?></td>
				</tr>
				<?php
				if ($this->fc->user->checkAcl('admin_user')) {
				?>
				<tr>
					<th><?php echo TR::html('security','account_enabled') ?></th>
					<td><?php echo $this->data->iCheckBox('enabled'); ?></td>
				</tr>
				<tr>
					<th>
						<?php
						if ($this->data->get('id')) {
							echo TR::html('form','password_change');
						} else {
							echo TR::html('form','password');
						}
						?>
					</th>
					<td>
						<?php 
						if ($this->data->get('password')) { // don't display encoded password !
							$this->data->set('password', ''); 
						}
						echo $this->data->iText('password', false); // no autocomplete ?>
						<?php if ($this->data->get('id')): ?> 
						<i style="color: #999"><?php echo TR::html('form', 'blank_no_change')?></i>
						<?php endif; ?>
					</td>
				</tr>
				<?php
				}
				?>
				<tr>
					<th><?php echo TR::html('form','class') ?></th>
					<td><?php echo $this->data->iSelect('class','','filter'); ?></td>
				</tr>
				<tr>
					<th><?php echo TR::html('form','nick_name') ?></th>
					<td><?php echo $this->data->iText('nickname'); ?></td>
				</tr>
				<tr>
					<th><?php echo TR::html('form','email') ?></th>
					<td><?php echo $this->data->iText('email'); ?></td>
				</tr>
				<tr>
					<th><?php echo TR::html('form','address') ?></th>
					<td><?php echo $this->data->iTextarea('address'); ?></td>
				</tr>
				<tr>
					<th><?php echo TR::html('form','company') ?></th>
					<td><?php
						echo $this->data->iSelectDb('company__id', 'name', $this->companies, $this->data->get('company__id'),'-');
					?></td>
				</tr>
				<tr>
					<th><?php echo TR::html('form','translation_rate') ?></th>
					<td><?php echo $this->data->iText('rate_translate'); ?></td>
				</tr>
				<tr>
					<th><?php echo TR::html('form','review_rate') ?></th>
					<td><?php echo $this->data->iText('rate_review'); ?></td>
				</tr>
				<tr>
					<th><?php echo TR::html('form','hourly_rate') ?></th>
					<td><?php echo $this->data->iText('rate_hourly'); ?></td>
				</tr>
				<tr>
					<th><?php echo TR::html('form','payment_terms') ?></th>
					<td><?php echo $this->data->iSelect('payterms','','filter'); ?></td>
				</tr>
				<tr>
					<th><?php echo TR::html('form','language') ?></th>
					<td><?php echo $this->data->iSelect('language'); ?></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<th></th>
					<td>
						<button name="save" value="1" type="submit" class="btn btn-success"><i class="icon-ok icon-white"></i> <?php echo TR::html('button', 'save')?></button>
						<?php
						if ($this->data->getUid()) {
							if ($this->data->isEmpty('hidden')) {
							?>
							<a href="<?php echo APP_WWW_URI.'users/edit/delete/'.$this->data->getUid().'.html'; ?>" class="btn btn-danger" onclick="return confirm('Really delete this account?')" style="margin-left:15px"><i class="icon-trash icon-white"></i> delete</a>
							<?php
							} else {
							?>
							<a href="<?php echo APP_WWW_URI.'users/edit/restore/'.$this->data->getUid().'.html'; ?>" class="btn btn-warning" onclick="return confirm('Really restore this account?')" style="margin-left:15px"><i class="icon-share icon-white"></i> restore</a>
							<?php
							}
						}
						?>
					</td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>