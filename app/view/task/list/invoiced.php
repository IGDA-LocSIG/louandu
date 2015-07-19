<div class="container">
<?php

$muid = $this->fc->user->getUid();

$canInvoice = $this->fc->user->checkAcl('invoicing');
$canEdit = $this->fc->user->checkAcl('task_edit_all');

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
  		</tr>
  	</thead>
  	<tbody>
  	<?php
  	$last = -1;

	while ($this->data->next()) {
		
		$objInvoice = $this->data->get('invoice');
		
		$current = $objInvoice->getUid();
		
		if ($current && $current != $last) {
		?>
  		<tr class="info">
  			<th colspan="9">
  				<?php
  				if ($canInvoice) {
  				?>
  				<span class="pull-right"><i class="icon-print"></i> <a href="<?php echo $objInvoice->getPdfUrl(); ?>" target="_blank">print invoice</a></span>
  				<span class="pull-right"><i class="icon-edit"></i> <a href="<?php echo $objInvoice->getEditUrl(); ?>">edit</a></span>
  				<?php
  				}
  				
  				if ($canInvoice) {
	  				echo '<input id="invoice-'.$current.'" name="invoice[]" value="'.$current.'" type="checkbox" class="click-link pull-left" /> ';
	  				echo '<label for="invoice-'.$current.'" style="margin:0 0 0 10px;width:120px;cursor:pointer" class="pull-left"><i class="icon-file"></i> '
	  					.$objInvoice->html('code').'</label>';
	  			} else {
		  			echo '<input type="checkbox" disabled="disabled" class="pull-left" /> ';
		  			echo '<span style="margin:0 0 0 10px;width:120px;" class="pull-left"><i class="icon-file"></i> '
	  					.$objInvoice->html('code').'</span>';
	  			}
	  			?>
  				<span style="margin-left:10px;width:130px" class="pull-left"><i class="icon-calendar"></i> <?php echo $objInvoice->htmlDate(true, $this->order); ?></span>
  				<span style="margin-left:10px;"><i class="icon-shopping-cart"></i> &euro; <?php 
  					echo $objInvoice->htmlAmount(true); 
  				?></span>
  			</th>
  		</tr>
  		<?php
  			$last = $objInvoice->getUid();
  			
  		} else if ($current != $last) {
  		?>
  		<tr class="info">
  			<th colspan="9"><i class="icon-file"></i> OTHER TASKS</th>
  		</tr>
  		<?php
	  		$last = $current;
	  		
  		}
  		  		
  		$email = $this->data->get('member')->get('email');
  		
  		?>
  		<tr class="<?php echo ($this->data->get('member')->getUid() == $muid?'highlight ':''); ?>">
  			<td><?php
	  			if (!$last) {
	  				if ($canEdit || $this->data->get('member')->getUid() == $muid) {
		  				echo '<input name="task[]" value="'.$this->data->getUid().'" type="checkbox" class="click-link" /> ';
		  			} else {
			  			echo '<input type="checkbox" disabled="disabled" /> ';
		  			}
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
  			<td><a href="mailto:<?php echo $email; ?>?subject=<?php echo rawurlencode($this->data->get('title')); ?>" title="<?php 
  				echo '<b>@'.$this->data->get('member')->get('username').'</b><br />'.$email; ?>" class="tip"><?php echo $this->data->get('member')->getLabel(); ?></a></td>
  			<td><?php echo $this->data->htmlWorkPo(); ?></td>
  			<td><?php echo $this->data->htmlWorkSpec(); ?></td>
  			<td><?php echo $this->data->htmlWorkRate(); ?></td>
  			<td><?php 
  				if (!$last && $canEdit) {
  					echo '<a href="'.$this->data->getEditUrl().'" class="btn btn-mini pull-right"><i class="icon-edit"></i></a>';
  				}
  				echo $this->data->htmlWorkTotal();
  			?></td>
  		</tr>
  	<?php
  	}
  	?>
  	</tbody>
  </table>
  <?php
  
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
