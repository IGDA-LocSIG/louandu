<?php
$mid = $this->data->getUid();

?>
<div class="container">
	<form action="<?php echo APP_WWW_URI.'task/invoice/edit/'.($mid?$mid:'new').'.html'; ?>" method="post">
		<?php
		 echo $this->data->iHidden('id');
		?>
		<table class="table">
			<tbody>
				<tr>
					<th><?php echo TR::html('form','code') ?></th>
					<td class="xxl"><?php echo $this->data->iText('code'); ?></td>
				</tr>
				<tr>
					<th><?php echo TR::html('form','invoice') ?></th>
					<td>
						<span class="input-append"><input type="text" name="invdate" value="<?php echo $this->data->html('invdate'); ?>" class="input-mini datepicker" placeholder="dd/mm" /><span class="add-on"><i class="icon-calendar"></i></span></span>
					</td>
				</tr>
				<tr>
					<th><?php echo TR::html('form','due') ?></th>
					<td>
						<span class="input-append"><input type="text" name="duedate" value="<?php echo $this->data->html('duedate'); ?>" class="input-mini datepicker" placeholder="dd/mm" /><span class="add-on"><i class="icon-calendar"></i></span></span>
					</td>
				</tr>
				<tr>
					<th><?php echo TR::html('form','payment') ?></th>
					<td>
						<span class="input-append"><input type="text" name="paydate" value="<?php echo $this->data->html('paydate'); ?>" class="input-mini datepicker" placeholder="dd/mm" /><span class="add-on"><i class="icon-calendar"></i></span></span>
					</td>
				</tr>
				<tr>
					<th><?php echo TR::html('form','status') ?></th>
					<td>
						<label class="radio">
							<input type="radio" name="status" value="2" <?php if ($this->status == 2) echo ' checked="checked"'; ?> />
							Invoiced
						</label>
						<label class="radio">
							<input type="radio" name="status" value="3" <?php if ($this->status == 3) echo ' checked="checked"'; ?> />
							Paid
						</label>
					</td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td>
						<label class="checkbox">
							<input type="checkbox" name="archived" value="1"<?php
								if ($this->archived) {
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
						<a href="<?php echo APP_WWW_URI.'task/invoice/delete/'.$mid.'.html'; ?>" onclick="return confirm('delete this invoice?')" 
							class="btn btn-danger" style="margin-left:15px"><i class="icon-trash icon-white"></i><?php echo ' '.TR::html('button', 'delete')?></a>
						<?php
						}
						?>
					</td>
				</tr>
			</tfoot>
		</table>
		
		<table class="table table-striped">
	  	<thead>
	  		<tr>
	  			<th></th>
	  			<th>Delivery</th>
	  			<th>Name</th>
	  			<th>Company</th>
	  			<th>Contact</th>
	  			<th>PO</th>
	  			<th>Words</th>
	  			<th>Rate</th>
	  			<th>Total</th>
	  		</tr>
	  	</thead>
	  	<tbody class="click-rows">
	  	<?php
	  	
		do {
			
	  		$obj = $this->tasks->get('member');
	  		$email = $obj->get('email');
	  		?>
	  		<tr>
	  			<td><?php
	  				echo $this->tasks->htmlContext();
	  			?></td>
	  			<td><?php echo $this->tasks->htmlDeadline(); //10/04 16:00 ?></td>
	  			<td><?php 
	  				echo $this->tasks->html('title'); 
	  			?></td>
	  			<td><?php echo $this->tasks->htmlCompany(); ?></td>
	  			<td><a href="mailto:<?php echo $email; ?>?subject=<?php echo rawurlencode($this->tasks->get('title')); ?>" title="<?php 
	  				echo $email; ?>" class="tip"><?php echo $this->tasks->get('member')->getLabel(); ?></a></td>
	  			<td><?php echo $this->tasks->htmlWorkPo(); ?></td>
	  			<td><?php echo $this->tasks->htmlWorkSpec(); ?></td>
	  			<td><?php echo $this->tasks->htmlWorkRate(); ?></td>
	  			<td><?php 
	  				if ($this->status == 2) {
		  				echo '<a href="'.APP_WWW_URI.'task/invoice/remove/'.$this->tasks->getUid().'.html" '
		  					.'class="btn btn-mini pull-right" onclick="return confirm(\'Remove task from invoice?\')"><i class="icon-minus-sign"></i></a>';
		  			}	
	  				echo $this->tasks->htmlWorkTotal();
	  			?></td>
	  		</tr>
	  	<?php
	  	} while ($this->tasks->next());
	  	?>
	  	</tbody>
	    </table>
	</form>
</div>