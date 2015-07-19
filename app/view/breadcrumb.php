<?php 
// note: enclosing <header> is in header.php

?>
	<div class="subnav subnav-fixed">
		<div class="container">
			<div class="row">
				<div class="span8">
				<?php
				// expected this->breadcrumb is of the form
				// 'order' => ('foo' => 'order by foo', 'bar' => 'order by bar')
				// 		outputs <a href="order,foo">order by foo</a>
				// or
				// '/path/order' => ('foo' => 'order by foo', 'bar' => 'order by bar')
				// 		outputs <a href="/path/order,foo">order by foo</a>
				// or
				// '0' => ('foo' => 'go to foo', '/path/bar' => 'go to bar') <-- non-assoc array
				// 		outputs <a href="foo">go to foo</a>
				
				// but first, check if we already have '/order,param' in current url :
				
				$baseWithParams = $this->fc->request->thisUrl(true);
				
				foreach ($this->breadcrumb as $buttonsGroup => $buttonsArray) {
				
					$buttonsGroupRegex = preg_quote($buttonsGroup, '/');
					$base = preg_replace("/\/$buttonsGroupRegex,[^\/]*/", '', $baseWithParams); // weed out existing param
					
					?>
					<div class="btn-group pull-left" style="margin-right:20px">
						<?php		
						foreach ($buttonsArray as $param => $label) {
							echo '<a class="btn';
							if (isset($this->filter) && $param == $this->filter	|| isset($this->order) && $param == $this->order) {
								echo ' active';
							}
							echo '" href="';
							if (!is_numeric($buttonsGroup)) {
								echo (substr($buttonsGroup,0,1)=='/' ? '' : "$base/"). // absolute vs relative
 										"$buttonsGroup,";
							}
							echo $param.'">'.TR::html((is_numeric($buttonsGroup) ? 'button' : $buttonsGroup), $label)."</a>\n";
						}
						?>
					</div>
				<?php
				}
				
				?>
				</div>
				<div class="span4">
					<?php
					if ($this->specifix) {
			
						$this->incView($this->specifix);
						
					} else {
					?>
					<div class="pull-right">
						<?php

						if ($this->plusbutton) {
							foreach ($this->plusbutton as $k => $v) {
							?>
								<a class="btn btn-inverse" href="<?php echo $v ?>">
									<?php echo TR::html('button', $k) ?>
								</a>
							<?php
							}
						}
						if ($this->plusmemo) {
						?>
						<a class="btn btn-inverse" data-toggle="modal" href="#myMemo">New Memo</a>
						<?php
						}
						if ($this->plusproject) {
						?>
						<a class="btn btn-primary" data-toggle="modal" href="#myProject">New Project</a>
						<?php
						}
						?>
					</div>
					<?php
					} // end specifix
					?>
				</div>
			</div>
		</div>
	</div>
</header>
<?php
$error = '';
if ($str = $this->fc->getHelper('messaging')->getMessages($error, true, true)): ?>
	<div class="container">
		<div class="span5 offset3 alert alert-<?php echo ($error ? "error" : "info") ?>">
			<a class="close" data-dismiss="alert" href="#">&times;</a>
			<?php echo TR::html('security',$str) ?>
		</div>
	</div>
<?php
endif;

if ($this->sinfo) {
	echo '<div class="container"><div class="span5 offset3 alert alert-info"><a href="'.$this->url.'?q=" class="close">&times;</a>'.$this->sinfo.'</div></div>';
}
