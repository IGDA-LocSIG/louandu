<?php
function selectWork($name, $value) {
	$str = '<select name="'.$name.'" class="input-small">';
	foreach ($GLOBALS['config']['task']['work']['options'] as $v => $l) {
		$str .= '<option value="'.$v.'"';
		if ($v == $value) {
			$str .= ' selected="selected"';
		}
		$str .= '>'.$l.'</option>';
	}
	$str .= '</select>';
	return $str;
}

$pid = ($this->data->isLoaded())?$this->data->pid:0;
$i = $j = 0;
?>
<div class="container">
	<form id="ediform" action="<?php echo APP_WWW_URI.'task/project/edit/'.($pid?$pid:'new').'.html'; ?>" method="post">
		<h2><?php
			echo ($pid)?'Edit project':'New project';
		?></h2>
	  <hr />
	  <table class="form">
	  	<tbody>
	  		<tr>
	  			<th>PO</th>
	  			<th>Name</th>
	  		</tr>
	  		<tr>
	  			<td><input type="text" name="po" value="<?php echo $this->data->po; ?>" class="input-small" /></td>
	  			<td><input type="text" name="title" value="<?php echo str_replace('"', '&quot;', $this->data->title); ?>" class="input-xlarge" /></td>
	  		</tr>
	  	</tbody>
	  </table>
	  <hr />
	  <h3>Client</h3>
	  <table class="form">
	  	<tbody>
	  		<tr>
	  			<th>Company</th>
	  			<th>Contact</th>
	  		</tr>
	  		<tr>
	  			<td><?php echo $this->companies->iSelectList('clientco', 0,'---','id="client_company" class="combrlist"'); ?></td>
	  			<td><select name="clientmr" id="client_company_member"><option value="<?php echo ($this->data->client)?$this->data->client['id']:0; ?>">loading...</option></select></td>
	  		</tr>
	  	</tbody>
	  </table>
	  <table class="form">
	  	<tbody>
	  		<tr>
	  			<th>Date</th>
	  			<th>Time</th>
	  			<th>Work</th>
	  			<th>Words</th>
	  			<th>Rate</th>
	  		</tr>
	  		<?php
	  		$ct = 0;
	  		foreach ($this->data->ctasks as $idx => $obj) {
	  		?>
	  		<tr>
	  			<td class="input-append"><input name="codeadline[<?php echo $idx; ?>]" type="text" value="<?php echo $obj->getDeadlineValue(); ?>" class="input-mini datepicker" /><span class="add-on"><i class="icon-calendar"></i></span></td>
	  			<td class="input-append"><input name="codeadtime[<?php echo $idx; ?>]" type="text" value="<?php echo $obj->value('deadtime'); ?>" class="input-mini" /><span class="add-on"><i class="icon-time"></i></span></td>
	  			<td class="input-pad"><?php
	  				echo selectWork('cowork['.$idx.']', $obj->value('work'));
		  			if ($oid = $obj->getUid()) {
			  			echo '<input type="hidden" name="coid['.$idx.']" value="'.$oid.'" />';
		  			}
	  			?></td>
	  			<td class="input-pad"><input name="cowords[<?php echo $idx; ?>]" type="text" value="<?php echo $obj->value('words'); ?>" class="input-mini" /></td>
	  			<td class="input-prepend">
	  				<span class="add-on">&euro;</span><input id="prependedInput" name="corate[<?php echo $idx; ?>]" class="input-mini" size="6" type="text" value="<?php echo $obj->value('rate'); ?>" />
	  				<?php
	  				if ($ct) {
	  				?><a href="#" onclick="$('#i_mode').val('del2client');$('#i_modo').val('<?php echo $idx; ?>');$('#ediform').submit()">&times;</a><?php
	  				}
	  				?>
	  			</td>
	  		</tr>
	  		<?php
	  			$ct++;
	  		}
	  		
	  		$ct = 0;
	  		?>
	  	</tbody>
	  </table>
	  <a href="javascript:{}" onclick="$('#i_mode').val('add2client');$('#ediform').submit()">add new client task</a>
     <hr />
     <h3>Vendors</h3>
     <table class="form">
	  	<tbody>
	  		<tr>
	  			<th>Company</th>
	  			<th>Contact</th>
	  			<th>Date</th>
	  			<th>Time</th>
	  			<th>Work</th>
	  			<th>Words</th>
	  			<th>Rate</th>
	  		</tr>
	  		<?php
	  		foreach ($this->data->vtasks as $idx => $obj) {
	  		?>
	  		<tr>
	  			<td class="input-pad"><?php echo $this->companies->iSelectList('company['.$idx.']', 0,'---','id="company_'.$idx.'" class="combrlist"'); ?></td>
	  			<td class="input-pad"><select name="member[<?php echo $idx; ?>]" id="company_<?php echo $idx; ?>_member"><option value="<?php echo $obj->get('member_id'); ?>">loading...</option></select></td>
	  			<td class="input-append"><input type="text" name="deadline[<?php echo $idx; ?>]" value="<?php echo $obj->getDeadlineValue(); ?>" class="input-mini datepicker" /><span class="add-on"><i class="icon-calendar"></i></span></td>
	  			<td class="input-append"><input type="text" name="deadtime[<?php echo $idx; ?>]" value="<?php echo $obj->value('deadtime'); ?>" class="input-mini" /><span class="add-on"><i class="icon-time"></i></span></td>
	  			<td class="input-pad"><?php
	  				echo selectWork('work['.$idx.']', $obj->value('work')); 
	  				if ($oid = $obj->getUid()) {
			  			echo '<input type="hidden" name="vid['.$idx.']" value="'.$oid.'" />';
		  			}
	  			?></td>
	  			<td class="input-pad"><input name="words[<?php echo $idx; ?>]" type="text" value="<?php echo $obj->value('words'); ?>" class="input-mini" /></td>
	  			<td class="input-prepend">
	  				<span class="add-on">&euro;</span><input id="prependedInput" name="rate[<?php echo $idx; ?>]" class="input-mini" size="6" type="text" value="<?php echo $obj->value('rate'); ?>" />
	  				<a href="#" onclick="$('#i_mode').val('del2vendor');$('#i_modo').val('<?php echo $idx; ?>');$('#ediform').submit()">&times;</a>
	  			</td>
	  		</tr>
	  		<?php
	  			$ct++;
	  		}
	  		?>
	  	</tbody>
	  </table>
	  <a href="javascript:{}" onclick="$('#i_mode').val('add2vendor');$('#ediform').submit()">add new vendor task</a>
	  <?php
	  /*
     <hr />
     <h3>Manager</h3>
     <table class="form">
	  	<tbody>
	  		<tr>
	  			<!--th>Type</th-->
	  			<th>Company</th>
	  			<th>Contact</th>
	  			<th>Date</th>
	  			<th>Time</th>
	  		</tr>
	  		<tr>
	  			<!--td class="input-pad"><select class="input-small"><option>manager</option></td-->
	  			<td class="input-pad"><select class="input-medium"><option>Gloc Co., Ltd.</option></select></td>
	  			<td class="input-pad"><select class="input-medium"><option>me@myself.com</option></select></td>
	  			<td class="input-append"><input type="text" value="16/04/12" class="input-mini" /><span class="add-on"><i class="icon-calendar"></i></span></td>
	  			<td class="input-append"><input type="text" value="18:00" class="input-mini" /><span class="add-on"><i class="icon-time"></i></span></td>
	  		</tr>
	  	</tbody>
	 </table>
	 	*/
	 ?>
     <hr />
     <input type="hidden" id="i_mode" name="mode" value="" />
     <input type="hidden" id="i_modo" name="modo" value="" />
     <button type="submit" class="btn btn-primary"><i class="icon-chevron-right icon-white"></i> Confirm</button>
     <?php
		if ($pid) {
		?>
		<a href="<?php echo APP_WWW_URI.'task/project/delete/'.$pid.'.html'; ?>" onclick="return confirm('delete this project and all its tasks?')" class="btn btn-danger" style="margin-left:15px"><i class="icon-trash icon-white"></i><?php echo ' '.TR::html('button', 'delete'); ?></a>
		<?php
		}
	 ?>
     <span style="margin-left:15px"><a href="<?php echo APP_WWW_URI; ?>" class="btn"><i class="icon-remove"></i> Cancel</a></span>
   </form>
</div>