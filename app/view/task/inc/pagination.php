  <div id="actfoot" class="row">
  	<?php
  	$thisurl = $this->fc->request->thisUrl();
  	?>
  	<div class="span3">
  		<a href="javascript:{}" onclick="$('#tasklist input:checkbox').click()">&uarr;&nbsp;all</a>&nbsp;
  		<?php
	  		echo '<input type="hidden" id="i_mode" name="mode" value="" />';
	  		foreach ($this->buttons as $mode => $arr) {
	      		if (isset($arr['panel'])) {
		      		echo '<a href="#'.$arr['panel'].'" role="button" class="'.$arr['css'].'" '
		      			.'onclick="$(\'#i_mode\').val(\''.$mode.'\')" '
		      			.'data-toggle="modal">'.$arr['label'].'</a> ';
	      		} else {
	      			echo '<button class="'.$arr['css'].'" '
	      				.'onclick="$(\'#i_mode\').val(\''.$mode.'\')" '
		      			.'type="submit">'.$arr['label'].'</button> ';
	      		}
	  		}
	  	?>
  	</div>
    <div class="pagination span6">
    	<div class="pagination-centered"><?php
			echo $this->data->pagination($thisurl, 'pg'); 
		?></div>
	</div>
	<div class="span3">
		<div class="btn-group pull-right">
			<?php
			foreach ($GLOBALS['config']['task']['pagination'] as $lbl => $pgz) {
				echo '<a href="'.$thisurl.'/pgz,'.$pgz.'" class="btn';
				if ($pgz == $this->pagination) {
					echo ' active';
				}
				echo '">'.($pgz?$lbl:('all <small style="font-size:.85em">'.$this->data->total().' items</small>')).'</a>';
			}
			?>
		</div>
  	</div>
  </div>