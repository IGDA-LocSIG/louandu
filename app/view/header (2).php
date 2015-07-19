<header class="header-fixed">
	<div class="navbar">
		<div class="navbar-inner">
			<div class="container">
				<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</a>
				<a class="brand" href="<?php echo APP_WWW_URI ?>"> GLOC </a>
				<ul class="nav pull-right">
					<li class="hidden-phone"><a href="#"> <i
							class="icon-time icon-white"></i> <span id="curtime"><?php echo date('H:m:s') ?></span>
					</a></li>
					<li class="dropdown"><a data-toggle="dropdown"
						class="dropdown-toggle" href="#"> <i class="icon-user icon-white"></i>
							<?php echo $_SESSION['appNickName']; ?><b class="caret"></b>
							</a>
						<ul class="dropdown-menu">
							<li><a href="<?php echo APP_WWW_URI.'account' ?>"><?php echo TR::get('security','account') ?></a>
							</li>
							<?php
								if ($this->fc->user->checkAcl('admin_user,view_user')): 
							?>
							<li class="divider"></li>
							<li><a href="<?php echo APP_WWW_URI.'users' ?>"><?php echo TR::get('security','users') ?></a>
							</li>
							<?php 
								endif;
								if ($this->fc->user->checkAcl('admin_user')):
							?>
							<li><a href="<?php echo APP_WWW_URI.'companies' ?>"><?php echo TR::get('security','companies') ?></a>
							</li>
							<?php
								endif;
							?>
							<li class="divider"></li>
							<li><a href="<?php echo APP_WWW_URI.'ident/logout' ?>"><?php echo TR::get('security','logout') ?></a>
							</li>
						</ul>
					</li>
				</ul>
				<div class="nav-collapse">
					<ul class="nav">
						<?php
						$myurl = $this->fc->request->thisUrl();
						foreach ($GLOBALS['config']['pages'] as $label => $url) {
							if (empty($url)) {
								echo '<li class="divider-vertical"></li>';
								continue;
							}
							echo '<li';
							if (preg_match('/^'.str_replace('/','\\/',APP_WWW_URI.$url).'/',$myurl)) {
								echo ' class="active"';
							}
							echo '><a href="'.APP_WWW_URI.$url.'">'.ucfirst(TR::get('pages', $label)).'</a></li>';
						}
						
						/*
						
						<li><a href="#">Reports</a></li>
						*/
						?>
					</ul>
					<form action="<?php echo $this->url ?>" method="get"
						class="navbar-search">
						<input type="text" name="q" class="search-query"
							placeholder="<?php echo TR::get('data', 'search') ?>"
							value="<?php if (isset($this->search)) { echo str_replace('"', '&quot;', $this->search); } ?>" />
					</form>
				</div>
			</div>
		</div>
	</div>
<?php // note: </header> is in breadcrumb.php ?>	