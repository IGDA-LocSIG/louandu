<?php

$pid = ($this->data->isLoaded())?$this->data->pid:0;

$i = $j = 0;

$ctot = $vtot = 0;

?>
<div class="container">
	<form id="conform" action="<?php echo APP_WWW_URI.'task/project/confirm/'.($pid?$pid:'new').'.html'; ?>" method="post">
	  <h2><?php
			echo ($pid)?'Confirm project changes':'Confirm new project';
		?></h2>
	  <hr />
	  <input type="hidden" name="po" value="<?php echo $this->data->po; ?>" />
	  <input type="hidden" name="title" value="<?php echo $this->data->title; ?>" />
	  <input type="hidden" name="clientmr" value="<?php echo $this->client->getUid(); ?>" />
	  <table class="data">
	  	<tbody>
	  		<tr>
	  			<th>PO</th>
	  			<th>Name</th>
	  		</tr>
	  		<tr>
	  			<td><?php echo $this->data->po; ?></td>
	  			<td><?php echo $this->data->title; ?></td>
	  		</tr>
	  	</tbody>
	  </table>
	  <hr />
	  <h4>Client</h4>
	  <div class="row">
	  	<div class="span8">
	  	  <table class="data">
		  	<tbody>
		  		<tr>
		  			<th><i class="icon-envelope"></i></th>
		  			<th>Company</th>
		  			<th>Contact</th>
		  		</tr>
		  		<tr>
		  			<td><input type="checkbox" name="email2client" value="1" /></td>
		  			<td><?php echo $this->client->get('company')->html('name'); ?></td>
		  			<td><a href="mailto:<?php echo $this->client->html('email'); ?>" title="<?php echo $this->client->html('email'); ?>"><?php 
		  				echo $this->client->html('nickname'); ?></a></td>
		  		</tr>
		  	</tbody>
		  </table><br />
		  <table class="data">
		  	<tbody>
		  		<tr>
		  			<th>Date/Time</th>
		  			<th>Work</th>
		  			<th>Words</th>
		  			<th>Rate</th>
		  			<th>Total</th>
		  		</tr>
		  		<?php
		  		foreach ($this->data->ctasks as $idx => $obj) {
		  		?>
		  		<tr>
		  			<td><?php echo $obj->htmlDeadline(false); ?></td>
		  			<td><?php echo $GLOBALS['config']['task']['work']['options'][$obj->get('work')]; ?></td>
		  			<td><?php echo $obj->html('words'); ?></td>
		  			<td><?php echo $obj->htmlRate(true); ?></td>
		  			<td class="total"><?php
		  				echo $obj->htmlTotal(true); 
			  			echo '<input type="hidden" name="codeadline['.$idx.']" value="'.$obj->value('deadline').'" />';
			  			echo '<input type="hidden" name="codeadtime['.$idx.']" value="'.$obj->value('deadtime').'" />';
			  			echo '<input type="hidden" name="cowork['.$idx.']" value="'.$obj->value('work').'" />';
			  			echo '<input type="hidden" name="cowords['.$idx.']" value="'.$obj->value('words').'" />';
			  			echo '<input type="hidden" name="corate['.$idx.']" value="'.$obj->value('rate').'" />';
			  			if ($oid = $obj->getUid()) {
				  			echo '<input type="hidden" name="coid['.$idx.']" value="'.$oid.'" />';
			  			}
		  			?></td>
		  		</tr>
		  		<?php
		  			$ctot += $obj->getWorkTotal();
		  		}
		  		?>
		  	</tbody>
		  </table>
	  	</div>
	  	<div class="span4">
            <label>&larr; Email to client</label>
            <textarea class="input-xlarge" id="textarea" name="mess2client" rows="3"></textarea>
	  	</div>
	 </div>
	 <hr />
     <h4>Vendors</h4>
     <div class="row">
       <div class="span8">
	     <table class="data">
		  	<tbody>
		  		<tr>
		  			<th><i class="icon-envelope"></i></th>
		  			<th>Company</th>
		  			<th>Contact</th>
		  			<th>Date/Time</th>
		  			<th>Work</th>
		  			<th>Words</th>
		  			<th>Rate</th>
		  			<th>Total</th>
		  		</tr>
		  		<?php
		  		foreach ($this->data->vtasks as $idx => $obj) {
		  		?>
		  		<tr>
		  			<td><input type="checkbox" name="email2vendor[]" value="<?php echo $idx; ?>" /></td>
		  			<td><?php echo $this->vendors[$idx]->get('company')->html('name'); ?></td>
		  			<td><a href="mailto:<?php echo $this->vendors[$idx]->html('email'); ?>" title="<?php echo $this->vendors[$idx]->html('email'); ?>"><?php 
		  				echo $this->vendors[$idx]->html('nickname'); ?></a></td>
		  			<td><?php echo $obj->htmlDeadline(false); ?></td>
		  			<td><?php echo $GLOBALS['config']['task']['work']['options'][$obj->get('work')]; ?></td>
		  			<td><?php echo $obj->html('words'); ?></td>
		  			<td><?php echo $obj->htmlRate(true); ?></td>
		  			<td class="total"><?php 
		  				echo $obj->htmlTotal(true); 
		  				echo '<input type="hidden" name="deadline['.$idx.']" value="'.$obj->value('deadline').'" />';
			  			echo '<input type="hidden" name="deadtime['.$idx.']" value="'.$obj->value('deadtime').'" />';
			  			echo '<input type="hidden" name="work['.$idx.']" value="'.$obj->value('work').'" />';
			  			echo '<input type="hidden" name="words['.$idx.']" value="'.$obj->value('words').'" />';
			  			echo '<input type="hidden" name="rate['.$idx.']" value="'.$obj->value('rate').'" />';
			  			echo '<input type="hidden" name="member['.$idx.']" value="'.$obj->value('member_id').'" />';
			  			if ($oid = $obj->getUid()) {
				  			echo '<input type="hidden" name="vid['.$idx.']" value="'.$oid.'" />';
			  			}
		  			?></td>
		  		</tr>
		  		<?php
		  			$vtot += $obj->getWorkTotal();
		  		}
		  		?>
		  	</tbody>
		 </table>
	  </div>
	  <div class="span4">
	  		<label>&larr; Email to vendors</label>
            <textarea class="input-xlarge" id="textarea" name="mess2vendor" rows="3"></textarea>
	  </div>
	</div>
	<?php
	/*
	<hr />
     <h4>Manager</h4>
     <table class="data">
	  	<tbody>
	  		<tr>
	  			<th>Company</th>
	  			<th>Contact</th>
	  			<th>Date/Time</th>
	  			<th>Total</th>
	  		</tr>
	  		<tr>
	  			<td>Gloc Co., Ltd.</td>
	  			<td>me@myself.com</td>
	  			<td>16/04/12 18:00</td>
	  			<td class="total">&euro; 70</td>
	  		</tr>
	  	</tbody>
	 </table>
	 */

	$atype = '';
	$mtype = 'ZERO'; 
	if ($ctot > $vtot){
		$atype = ' alert-success';
		$mtype = 'POSITIVE';
	} else if ($ctot < $vtot) {
		$atype = ' alert-error';
		$mtype = 'NEGATIVE';
	}	
	 ?>
	 <hr />
		<div class="alert<?php echo $atype; ?>">
			<strong><?php echo $mtype; ?></strong> manager margin : <strong><?php echo '&euro; '.number_format($ctot - $vtot, 2); ?></strong>
		</div>
		<div class="well form-inline">
			<label>Status :</label>
		 	<select name="status" class="input-medium">
		 		<option value="">- Unchanged -</option>
		 		<option value="0">Todo</option>
		 		<option value="1">Done</option>
		 		<!-- option value="2">Invoiced</option>
		 		<option value="3">Paid</option -->
		 	</select>
		 	<label class="checkbox" style="margin-left:20px"><input type="checkbox" name="archived" value="1" /> Archived</label>
		</div>
		<button type="submit" name="save" value="1" class="btn btn-success"><i class="icon-ok icon-white"></i> Save tasks</button>
		<span style="margin-left:15px"><a href="<?php echo APP_WWW_URL; ?>" class="btn"><i class="icon-remove"></i> Cancel</a></span>
		<button type="submit" name="reedit" class="btn btn-primary" style="float:left;margin-right:20px" onclick="$('#conform').attr('action','<?php echo APP_WWW_URI.'task/project/reedit/'.($pid?$pid:'new').'.html'; ?>');"><i class="icon-chevron-left icon-white"></i> Modify</button>
	</form>
</div>