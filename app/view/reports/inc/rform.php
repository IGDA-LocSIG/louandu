<?php
if ($this->showMargin) {
?>
<span class="label <?php if ($this->margin>0) echo 'label-success '; ?>pull-right">margin<br /><?php echo number_format($this->margin); ?></span>	
<?php
}
?>
<span class="label label-warning pull-right" style="margin-right:10px">vendors<br /><?php echo number_format($this->totvdr); ?></span>
<span class="label label-info pull-right" style="margin-right:10px">sales<br /><?php echo number_format($this->totclt); ?></span>
<span class="label label-inverse pull-right" style="margin-right:10px">tasks<br /><?php echo number_format($this->count); ?></span>
