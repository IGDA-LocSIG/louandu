<div class="container" id="login-box">
	<div class="row">
		<div class="offset4 span4">
			<form action="<?php echo $this->fc->request->getUrl('ident/login'); ?>"	method="post" class="well">
				<fieldset>
					<label for="i_username"><?php TR::phtml('form','username'); ?></label>
					<?php echo $this->fc->user->iText('username'); ?>
					<label for="i_password"><?php TR::phtml('form','password'); ?></label>
					<?php echo $this->fc->user->iPass('password'); ?>
					<label class="checkbox"><input type="checkbox" name="remember" value="1" /> Remember me on this computer</label>
				</fieldset>
				<button type="submit" name="login" value="1" class="btn">
					<?php TR::phtml('button','login'); ?>
				</button>
			</form>
			<?php
				if ($str = $this->fc->user->getAuthError()) {
					echo '<div class="alert error">'.TR::html('error','login_failed').TR::html('punctuation', ':').TR::html('error',$str).'</div>';
				}
			?>			
		</div>
	</div>
</div>

<?php

if ($GLOBALS['config']['log_debug'] == 2) {
	$this->fc->user->htmlAllErrors();
	echo $this->fc->user;
}

?>