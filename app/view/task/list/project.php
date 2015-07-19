<div class="container">
<?php

$muid = $this->fc->user->getUid();

if ($cp = $this->data->count()) {
?>
<form id="tasklist" action="<?php echo $this->url; ?>" method="post">
  <table class="table table-striped">
  	<thead>
  		<tr>
  			<th><a href="javascript:{}" onclick="$('#tasklist input:checkbox').click()" style="font-weight:normal">&darr;&nbsp;all</a></th>
  			<th>Delivery</th>
  			<th>Name</th>
  			<th>Company</th>
  			<th>Contact</th>
  			<th>PO</th>
  			<th>Words</th>
  			<th>Rate</th>
  			<th>Total</th>
  			<th></th>
  		</tr>
  	</thead>
  	<tbody class="click-rows">
  	<?php
  	$last = -1;

	while ($this->data->next()) {
	
		$info = $css = '';
	
		$diff = $this->data->chkDeadline();
		
		if ($diff < 0 && $this->alertOverdue) {
			$css = 'error';
		} else if ($diff == 0) {
			$css = 'today';
		}
		
		$po = $this->data->get('po');
		
		if ($last != $po) {
			if ($po) {
				$info = $po;
				$last = $po;
			} else{
				$info = 'OTHERS';
				$last = intval($po);
			}
		}
		
		if ($info) {
		?>
  		<tr class="info">
  			<th colspan="10"><i class="icon-briefcase"></i> <?php echo $info; ?></th>
  		</tr>
  		<?php
  		}
  		
  		$email = $this->data->get('member')->get('email');
  		
  		$canEdit = ($this->canEditAll || ($this->data->get('author_id') == $muid));
  		
  		?>
  		<tr class="<?php echo ($this->data->get('member')->getUid() == $muid?'highlight ':'').$css; ?>">
  			<td nowrap><?php
  				if ($canEdit || $this->data->get('member')->getUid() == $muid) {
	  				echo '<input name="task[]" value="'.$this->data->getUid().'" type="checkbox" class="click-link" /> ';
	  			} else {
		  			echo '<input type="checkbox" disabled="disabled" /> ';
	  			}
  				echo $this->data->htmlContext();
  			?></td>
  			<td><?php echo $this->data->htmlDeadline(); //10/04 16:00 ?></td>
  			<td><?php 
  				if ($this->data->value('public')) {
	  				echo '<i class="icon-bullhorn pull-right"></i>';
  				}
  				echo $this->data->html('title'); 
  			?></td>
  			<td><?php echo $this->data->htmlCompany(); ?></td>
  			<td><a href="mailto:<?php echo $email; ?>?subject=<?php echo rawurlencode($this->data->get('title')); ?>" title="<?php echo '<b>@'.$this->data->get('member')->get('username').'</b><br />'.$email; ?>" class="tip"><?php echo $this->data->get('member')->getLabel(); ?></a></td>
  			<td><?php echo $this->data->htmlWorkPo(); ?></td>
  			<td><?php echo $this->data->htmlWorkSpec(); ?></td>
  			<td><?php echo $this->data->htmlWorkRate(); ?></td>
  			<td><?php 
  				echo $this->data->htmlWorkTotal();
  			?></td>
  			<td><?php 
  				if ($canEdit) {
  					echo '<a href="'.$this->data->getEditUrl().'" class="btn btn-mini"><i class="icon-edit"></i></a>';
  				}
  			?></td>
  		</tr>
  	<?php
  	}
  	?>
  	</tbody>
  </table>
  <?php
  
  // modal for invoincing
  if ($this->invoices) {
  ?>
  	<div class="modal hide fade" id="myInvoices">
	  <div class="modal-header">
	    <a class="close" data-dismiss="modal">&#10006;</a>
	    <h3>Invoice selected tasks</h3>
	  </div>
	  <div class="modal-body">
	  	<?php
	  	if ($this->invoices->count()) {
		?>
	  	<label>Select invoice to add tasks to</label>
		<select name="selinvoice">
			<option value="">- new -</option>
			<?php
			while ($this->invoices->next()) {
				echo '<option value="'.$this->invoices->getUid().'">'.$this->invoices->html('code').'</option>'."\n";
			}
			?>
		</select>
	  	<label>OR enter invoice number to create new one</label>
	  	<input type="text" name="newinvoice" class="input-small" placeholder="Invoice number" onchange="this.form.elements['selinvoice'].selectedIndex=0" />
	  	<?php
	  	} else {
		?>
		<label>Enter invoice number to create new one</label>
		<input type="text" name="newinvoice" class="input-small" placeholder="Invoice number" />
		<?php
	  	}
	  	?>
	  </div>
	  <div class="modal-footer">
	    <a href="#" class="btn" data-dismiss="modal">Close</a>
	    <button type="submit" class="btn btn-primary">Save changes</button>
	  </div>
	</div>
  <?php
  }
  
  // pagination
  $this->incView('inc/pagination');
  
  ?>
</form>
<?php
} else {
?>
<p class="empty">Aucun &eacute;l&eacute;ment trouv&eacute;</p>
<?php
}
?>
</div>
<?php
$this->incView('inc/qforms');