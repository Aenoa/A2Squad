<?php
	require_once 'functions.php';

	if(file_exists('config.inc.php'))
	{
		$pct.= "<div id=\"E_Toast\">
				<h1>A2Squad seems to be installed.</h1>
				Apparently, this website is already installed, or you didn't deleted the 'config.inc.php' file.
				Please delete it and return here to reinstall the website, or 
				<a href=\"../manager.php\">start using it now</a>.
				<br /><br />
				If the problem persist, please contact <a href=\"http://aenoa.net/\">the website developper</a>.
				</div>";
	}
	
?>
	
	<HTML>
		<HEAD>
			<TITLE>Install - A2Squad</TITLE>
			<LINK rel="stylesheet" type="text/css" href="../A2Squad.css" />
			<LINK rel="shortcut icon" href="favicon.ico" />
		</HEAD>
		<BODY>
			
			<div id="header"></div>
			
			<div id="global_container">
				<?php echo $pct; ?>
			</div>
			
			<div id="footer">
				<a href="http://dev.aenoa.net/A2Squad/1">Arma 2 Squad (A2Squad)</a> by 
				<a href="http://aenoa.net/">Hugo '_Aenoa' Regibo</a>
				<br />
				current version: 
				<b>1.0</b> (Private, edited for 
				<a href="http://sigclan.com/">SiG Clan</a>)
				<br />
				<a rel="license" href="http://creativecommons.org/licenses/by-nc/3.0/"><img alt="Licence Creative Commons" style="border-width:0" src="http://i.creativecommons.org/l/by-nc/3.0/80x15.png" /></a>
			</div>
		</BODY>
	</HTML>

