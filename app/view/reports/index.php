
<div class="container">
	<div class="row">
		<div id="sidefix" class="span2 sidebar-nav" data-spy="affix" data-offset-top="200">
			<form id="tasklist" action="<?php echo APP_WWW_URI; ?>reports/index/main" method="get">
				<label>From</label>
				<div class="input-append"><input name="repbegin" type="text" value="<?php echo $this->begin; ?>" class="input-small datepicker" /><span class="add-on"><i class="icon-calendar"></i></span></div>
				<label>Until</label>
				<div class="input-append"><input name="repend" type="text" value="<?php echo $this->end; ?>" class="input-small datepicker" /><span class="add-on"><i class="icon-calendar"></i></span></div>
				<label class="checkbox">
			      <input type="checkbox" name="tsk[]" value="1"<?php if (in_array(1,$this->status)) echo ' checked="checked"'; ?> /> Todo
			    </label>
			    <label class="checkbox">
			      <input type="checkbox" name="tsk[]" value="2"<?php if (in_array(2,$this->status)) echo ' checked="checked"'; ?> /> Done
			    </label>
			    <label class="checkbox">
			      <input type="checkbox" name="tsk[]" value="3"<?php if (in_array(3,$this->status)) echo ' checked="checked"'; ?> /> Invoiced
			    </label>
			    <label class="checkbox">
			      <input type="checkbox" name="tsk[]" value="4"<?php if (in_array(4,$this->status)) echo ' checked="checked"'; ?> /> Paid
			    </label>
			    <label class="checkbox">
			      <input type="checkbox" name="reparc" value="1"<?php if ($this->archived) echo ' checked="checked"'; ?> /> include archives
			    </label>
			    <?php
				if ($this->fullReport) {
				?>
			    <p>
			    	<a data-toggle="modal" href="#usFilter" class="btn">Filter on user &nbsp; <span id="usercnt" class="badge badge-info"><?php echo $this->mbrc; ?></span></a>
			    	<br />&nbsp;<input type="hidden" id="userfld" name="mbrs" value="<?php echo $this->mbrs; ?>" />
			    </p>
			    <?php
			    }
			    ?>
				<button type="submit" name="show" value="show" class="btn btn-primary"
					onclick="showReport(this.form)"><i class="icon-tasks icon-white"></i> Report</button>
				<button type="submit" name="download" value="download" class="btn btn-inverse"
					onclick="showCsv(this.form)"><i class="icon-download icon-white"></i> CSV</button>
			</form>
		</div>
		<div class="span10 offset2">

<table class="table table-striped">
  	<thead>
  	<?php
  	switch ($this->filter) {
	  	case 'task':
	  	?>
  		<tr>
  			<th></th>
  			<th>Delivery</th>
  			<th>Name</th>
  			<th>Company / Contact</th>
  			<th>Words</th>
  			<th>Rate</th>
  			<th>Total</th>
  		</tr>
  		<?php
  			break;
  		default:
  		?>
  		<tr>
  			<th>Project</th>
  			<th>Company</th>
  			<th>Contact</th>
  			<th>Sales</th>
  			<?php
  			if ($this->showMargin) {
  			?>
  			<th>Margin</th>
  			<?php
  			}
  			?>
  		</tr>
  		<?php
  			break;
  	}
  	?>
  	</thead>
  	<tbody class="click-rows">
  	<?php
  	
  	$muid = $this->fc->user->getUid();
  	
  	$last = -1;

	while ($this->data->next()) {
	
		$info = $css = '';
		
		/*
		$diff = $this->data->chkDeadline();
		
		if ($diff < 0 && $this->alertOverdue) {
			// $css = 'error';
		} else if ($diff == 0) {
			// $css = 'today';
		}
		*/
		
		$po = $this->data->get('po');
		
		if ($last != $po) {
			if ($po) {
				$info = $po;
				$last = $po;
			} else {
				$info = 'OTHERS';
				$last = intval($po);
			}
		}
		
		if ($info) {
		
			$icon = 'icon-off';
			$marge = floatval($this->arrmar[$po]);
			if ($marge > 0) {
				$icon = 'icon-plus-sign';
			} else if ($marge < 0) {
				$icon = 'icon-minus-sign';
			}
			
			if ($this->filter == 'task') {
			
				// task view : project header
		?>
  		<tr class="info">
  			<th colspan="8">
  				<?php
  				if ($this->showMargin) {
  				?>
  				<span class="pull-right rgt" style="width:100px;text-align:right"><i class="<?php echo $icon; ?>"></i> <?php echo number_format($marge,3); ?></span>
  				<?php
  				}
  				?>
  				<span class="pull-right rgt" style="width:120px"><i class="icon-shopping-cart"></i> <?php echo number_format($this->arrtot[$po],3); ?></span>
  				<i class="icon-briefcase"></i> <?php echo $info; ?>
  			</th>
  		</tr>
  		<?php
  			
  			} else {
  			
  				// project view : project row
  				
  				$email = $this->data->get('member')->get('email');
	  	?>
	  	<tr class="<?php echo ($this->data->get('member')->getUid() == $muid?'highlight ':'').$css; ?>">
  			<td nowrap><i class="icon-briefcase"></i> <?php echo $info; ?></td>
  			<td nowrap><?php echo $this->data->htmlCompany(); ?></td>
  			<td nowrap><a href="mailto:<?php echo $email; ?>?subject=<?php echo rawurlencode($this->data->get('title')); ?>" title="<?php echo '<b>@'.$this->data->get('member')->get('username').'</b><br />'.$email; ?>" class="tip"><?php echo $this->data->get('member')->getLabel(); ?></a></td>
  			<td nowrap><i class="icon-shopping-cart"></i> <?php echo number_format($this->arrtot[$po],3); ?></td>
  			<?php if ($this->showMargin) { ?>
  			<td nowrap><i class="<?php echo $icon; ?>"></i> <?php echo number_format($marge,3); ?></td>
  			<?php } ?>
  		</tr>
	  	<?php		
  			}
  			
  		}
  		
  		if ($this->filter == 'task') {
	  		$email = $this->data->get('member')->get('email');
	  		
	  		?>
	  		<tr class="<?php echo ($this->data->get('member')->getUid() == $muid?'highlight ':'').$css; ?>">
	  			<td nowrap><?php
	  				echo $this->data->htmlContext();
	  			?></td>
	  			<td nowrap><?php
	  				echo $this->data->htmlDeadline();
	  			?></td>
	  			<td><?php 
	  				echo $this->data->html('title'); 
	  			?></td>
	  			<td nowrap>
	  				<?php echo $this->data->htmlCompany(); ?><br />
	  				<a href="mailto:<?php echo $email; ?>?subject=<?php echo rawurlencode($this->data->get('title')); ?>" title="<?php echo '<b>@'.$this->data->get('member')->get('username').'</b><br />'.$email; ?>" class="tip"><?php echo $this->data->get('member')->getLabel(); ?></a>
	  			</td>
	  			<td><?php echo $this->data->htmlWorkSpec(); ?></td>
	  			<td><?php echo $this->data->htmlWorkRate(); ?></td>
	  			<td style="text-align:right"><?php 
	  				echo $this->data->htmlWorkTotal();
	  			?></td>
	  		</tr>
  	<?php
  		}
  	}
  	?>
  	</tbody>
  </table>

  
		</div>
	</div>
</div>

<?php
if ($this->fullReport) {
?>
<form action="/task/project/new" method="post" class="modal hide fade" id="usFilter">
	<div class="modal-header">
		<a class="close" data-dismiss="modal">&#10006;</a>
		<h3>Filter on users</h3>
	</div>
	<div class="modal-body">
		<?php
		$i = 0;
		$ccy = -1;
		$c = $this->members->count() / 2;
		echo '<div class="pull-left">';
		
		while ($this->members->next()) {
		
			$mid = $this->members->getUid();
			$ncy = $this->members->get('company')->getUid();
			
			if ($ccy != $ncy) {
				if ($i > $c) {
					echo '</div><div class="pull-right">';
					$i = 0;
				}
				echo '<h4>'.$this->members->get('company')->get('name').'</h4>';
				$ccy = $ncy;
			}
			echo '<label class="checkbox">'
				.'<input type="checkbox" name="mbrs[]" value="'.$mid.'" '
				.(in_array($mid, $this->mbra)?'checked="checked" ':'')
				.'/> '.$this->members->getLabel().'</label>';
			$i++;
		}
		echo '</div>';
		?>
	</div>
	<div class="modal-footer">
		<a href="#" onclick="unselectAll()" class="pull-left"><i class="icon-cog"></i> deselect all</a>
		<a href="#" class="btn" data-dismiss="modal"><i class="icon-remove"></i> Close</a>
		<button type="button" class="btn btn-inverse"
			onclick="selectUsers()"><i class="icon-ok icon-white"></i> Confirm</button>
	</div>
</form>
<script>
function unselectAll() {
	$('#usFilter input:checked').each(function() {
       $(this).attr("checked", false);
    });
}
function selectUsers() {
	var allVals = [];
     $('#usFilter input:checked').each(function() {
       allVals.push($(this).val());
     });
     $('#userfld').val(allVals);
     $('#usercnt').html(allVals.length);
     $('#usFilter').modal('hide');
}
</script>
<?php
}
?>
<script>
function showReport(ff) {
	ff.action='<?php echo APP_WWW_URI; ?>reports/index/main';
	ff.target='';
}
function showCsv(ff) {
	ff.action='<?php echo APP_WWW_URI; ?>reports/index/download';
	ff.target='_blank';
}
</script>