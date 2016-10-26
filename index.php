<?php
	ob_start();
	session_start();
	
	// --- DEBUG VAR ---
	ini_set('display_errors', 'On');
	error_reporting(-1);
	// -----------------
	
	if(!file_exists('files/config.inc.php'))
	{
		header('location: files/install.php');
	}
	
	require_once 'files/config.inc.php';
	require_once 'files/functions.php';
	
	
	global $cn_host;
	global $cn_data;
	global $cn_user;
	global $cn_pass;
	global $prefix;
	$err = false;
	
	
	$pct = "";
	
	try
	{
		$link = new PDO('mysql:host='.$cn_host.';dbname='.$cn_data, $cn_user, $cn_pass);
		
		// TODO: Récupérer le MDP crypté dans la BDD sous manager_pwd
		$request = $link->prepare("SELECT value FROM {$prefix}settings WHERE cvar=:a LIMIT 0,1");
		$request->bindValue(':a', 'A2S_MANAGERPWD', PDO::PARAM_STR);
		$request->execute();
		$resultat = $request->fetch(PDO::FETCH_OBJ);
		$manager_pwd = $resultat->value;
	}
	catch(PDOException $e)
	{
		die($e->getMessage());
	}
	
	
	if(empty($_SESSION['token']) || $_SESSION['token'] != CreateToken($prefix.$manager_pwd))
	{
		if(!empty($_POST['passwd']))
		{
			$is_valid = $manager_pwd == PwdCrypt($_POST['passwd']);
			if($is_valid)
			{
				$_SESSION['token'] = CreateToken($prefix.$manager_pwd);
				header('location: index.php?dir=home');
			}
			else
			{
				$pct.="	<div id=\"E_Toast\"><h1>Input error</h1>
						You enterred the wrong password. Please try again.</div>";
			}
		}
		
		$title = "Authorization required";
		$pct.=	"
				<div id=\"N_Toast\"><h1>Information</h1>
				Want to access the Squad list? <a href='squads/'>Click here !</a></div>
				
				<form style=\"text-align:center;\" action=\"index.php\" method=\"post\">
				<label for=\"passwd\">Enter the password:<br />
				<input type=\"password\" style=\"text-align:center;\" name=\"passwd\" /><br />
				<input type=\"submit\" value=\"Log-on\" /></form>";
	}
	// MAIN MENU - HOME
	elseif(empty($_GET['dir']) || $_GET['dir'] == 'home')
	{
		$title = "Home";
		$pct.= "<div id=\"icons_container\">
					<a href=\"index.php?dir=list&amp;item=members\">
						<p class=\"icon memberlist\">Members list</p>
					</a>
					
					<a href=\"index.php?dir=add&amp;item=members\">
						<p class=\"icon memberadd\">Add a member</p>
					</a>
					
					<a href=\"index.php?dir=list&amp;item=squads\">
						<p class=\"icon squadlist\">Squads list</p>
					</a>
					
					<a href=\"index.php?dir=add&amp;item=squads\">
						<p class=\"icon squadadd\">Add a Squad</p>
					</a>
					
					<a href=\"index.php?dir=maintenance&amp;item=import\">
						<p class=\"icon import_xml\">Import a XML file to A2Squad</p>
					</a>
					
					<a href=\"index.php?dir=maintenance&amp;item=dbcleaner\">
						<p class=\"icon dbcleaner\">clean orphan members</p>
					</a>
					
					<a href=\"index.php?dir=maintenance&amp;item=settings\">
						<p class=\"icon settings\">A2S Settings</p>
					</a>
					
					<a href=\"index.php?dir=maintenance&amp;item=uninstall\">
						<p class=\"icon uninstall\">delete A2Squad</p>
					</a>
				</div>";
		
	}
	// LIST
	elseif($_GET['dir'] == 'list')
	{
		// NO SUBMENU - LIST
		if(empty($_GET['item']) || $_GET['item'] == null)
		{
			$title = "Lists";
			$pct.=	"
					<div id=\"icons_container\">
					<a href=\"index.php?dir=list&amp;item=squads\">
						<p class=\"icon squadlist\">Squads list</p>
					</a>
					
					<a href=\"index.php?dir=list&amp;item=members\">
						<p class=\"icon memberlist\">Members list</p>
					</a>
					</div>";
		}
		// LIST - MEMBERS
		elseif($_GET['item'] == 'members')
		{
			$title = "Members list";
			$pct.= "<form action=\"index.php?dir=edit&amp;item=squads&amp;via=POST\" method=\"post\">
					<p>
						<input type=\"button\" value=\"Check all\" onclick=\"this.value=CheckAll(this.value, 'member[]');\" />
						<input type=\"submit\" name=\"sendform\" value=\"Edit selecteds\" /> 
						<input type=\"submit\" name=\"sendform\" value=\"Delete selecteds\" />
					</p>
					
					<table>
					<tr>
						<th></th>
						<th width='50'>UID</th>
						<th width='150'>Username</th>
						<th width='200'>Email</th>
						<th width='150'>Remark</th>
						<th width='100'>Options</th>
					</tr>";
			
			$req = $link->prepare("SELECT * FROM {$prefix}members");
			$req->execute();
			
			if($req->rowCount() != 0)
			{
				// Members
				$in = 0;
				while($fetch = $req->fetch(PDO::FETCH_OBJ))
				{
					$out = $in % 2 == 1 ? " class=\"tainted\"" : "";
					$in++;
					$pct.= "<tr".$out.">
								<td><input type='checkbox' name='member[]' value='{$fetch->mbr_uid}' /></td>
								<td>{$fetch->mbr_uid}</td>	
								<td>{$fetch->mbr_name}</td>
								<td>{$fetch->mbr_email}</td>	
								<td>{$fetch->mbr_remark}</td>
								<td>
									<a href=''>[Edit]</a>
									<a href=''>[Delete]</a>
								</td>
							</tr>";
				}
			}
			else
			{
				// No members
			}
			$pct.= "</table></form>";
		}
		// LIST - SQUADS
		elseif($_GET['item'] == 'squads')
		{
			$title = "Squads list";
			$pct.= "<table cellspacing=\"0\" width=\"800\">
					<tr class=\"tainted\">
						<th width=\"450\">	Squad Name	</th>
						<th width=\"75\">	Members		</th>
						<th width=\"105\">	TAG			</th>
						<th width=\"170\">	Options		</th>
					</tr>";
			
			try
			{
				$req = $link->prepare("	SELECT sqd.* , COUNT( rel.id ) AS membres
										FROM {$prefix}squads AS sqd
										INNER JOIN {$prefix}sqdrels AS rel ON rel.sqd_id = sqd.id
										GROUP BY rel.sqd_id");
				$req->execute();
				if($req->rowCount() !== 0)
				{
					$in = 0;
					while($fetch = $req->fetch(PDO::FETCH_OBJ))
					{
						$out = $in % 2 == 1 ? " class=\"tainted\"" : "";
						$in++;
						$pct.= "<tr".$out.">
									<td><a href=\"index.php?dir=edit&amp;item=squads&amp;sqdid={$fetch->id}\">".$fetch->sqd_name."</a></td>
									<td>".$fetch->membres."</td>	
									<td>".$fetch->sqd_tag."</td>
									<td>
										<a href=\"index.php?dir=edit&amp;item=squads&amp;sqdid={$fetch->id}\">[Edit]</a> 
										<a href=\"index.php?dir=delete&amp;item=squads&amp;sqdid={$fetch->id}\">[Delete]</a>
									</td>
								</tr>";
					}
				}				
				
				if($in == 0)
				{
					$pct.= "<tr><td colspan=\"4\">No squads on database.</td></tr>";
				}
				
				$req->closeCursor();
			}
			catch (PDOException $e)
			{
				echo $e->getMessage();
			}
			
			$pct.= "</table>";
		}
		else
		{
			$err = true;
		}
	}
	// MAINTENANCE
	elseif($_GET['dir'] == 'maintenance')
	{
		// NO SUBFOLDER
		if(empty($_GET['item']) || $_GET['item'] == null)
		{
			$title = "Maintenance";
			$pct.= "
					<div id='icons_container'>
					<a href=\"index.php?dir=maintenance&amp;item=import\">
						<p class=\"icon import_xml\">Import a XML file to A2Squad</p>
					</a>
					
					<a href=\"index.php?dir=maintenance&amp;item=dbcleaner\">
						<p class=\"icon dbcleaner\">clean orphan members</p>
					</a>
					
					<a href=\"index.php?dir=maintenance&amp;item=settings\">
						<p class=\"icon settings\">A2S Settings</p>
					</a>
					
					<a href=\"index.php?dir=maintenance&amp;item=uninstall\">
						<p class=\"icon uninstall\">delete A2Squad</p>
					</a>
					</div>";
		}
		elseif($_GET['item'] == 'import')
		{
			$title = "Import a XML file";
			
			$show_form = false;
			if(!empty($_POST['file_url'])) // formulaire envoyé
			{
				$data = import_xml_to_array($_POST['file_url']);
				if($data == NULL || $data == false)
				{
					// error parsing the XML file OR XML file generated errors
					$pct.= "<div id=\"E_Toast\"><h1>Invalid XML URL</h1>
							It seems that the URL you given doesn't match with an XML file. 
							Make sure that the entered URL is valid and is readable.<br />
							{$data['errors']}
							</div>";
					$show_form = true;
				}
				elseif(!empty($data['errors']))
				{
					$pct.= "<div id=\"E_Toast\"><h1>XML File corrupted</h1>
							It seems that the URL you given doesn't match with an XML file <b>OR</b> 
							your XML contains errors. Please see this error report to know what is the problem with your XML file.<br />
							{$data['errors']}
							</div>";
					$show_form = true;
				}
				else
				{
					$pct.= "<form action='index.php?dir=maintenance&amp;item=import' name='import' method='post'>
							<h2>Squad info:</h2>
							<table width='100%' border='0'>
								<tr>
									<th class='left top' width='20%'>Squad Name</th>
									<td class='left top' width='40%'>{$data['clan_NAME']}</td>
									<td rowspan='4'>
										<h2>What to do?</h2>
										Select the members you want to keep and import to database and check the &quot;import Squad to database&quot;
										checkbox under this message if you want to import this squad (and automaticly add these members
										to it).<br /><br />
										<input type=\"button\" value=\"Uncheck All\" onclick=\"this.value=CheckAll(this.value, 'mbr[]');\" />
										
										<br />
										<input type='checkbox' name='squad_save' checked='checked' /> Import Squad to database<br />
										<input type='hidden' name='step2' value='{$_POST['file_url']}' />
										<br /><br />
										<input type='submit' value='Import to database' />
									</td>
								</tr>
								<tr>
									<th class='left top'>Squad website</th>
									<td class='left top'><a href='{$data['clan_URL']}' target='_BLANK'>{$data['clan_URL']}</a></td>
								</tr>
								<tr>
									<th class='left top'>Squad Tag</th>
									<td class='left top'>{$data['clan_TAG']}</td>
								</tr>
								<tr>
									<th class='left top'>Squad Mail contact</th>
									<td class='left top'>{$data['clan_MAIL']}</td>
								</tr></table><br />";
					$pct.= "<h2>Member list:</h2>
							
								<table width='100%'>
								<tr>
									<th width='5%'></th>
									<th width='10%'>UID</th>
									<th width='20%'>Username</th>
									<th width='30%'>Email</th>
									<th width='10%'>ICQ</th>
									<th width='30%'>Remark</th>
								</tr>";
					$loopcount = 0;
					foreach($data['member'] as $mbr)
					{
						$pct.= "<tr".($loopcount % 2 == 1 ? " class='tainted'" : "")."><td><input type='checkbox' class='checkbox' name='mbr[]' value='{$mbr['ID']}' checked='checked' /></td><td>{$mbr['ID']}</td><td>{$mbr['NAME']}</td><td>{$mbr['EMAIL']}</td><td>{$mbr['ICQ']}</td><td>{$mbr['REMARK']}</td></tr>";
						$loopcount++;
					}
					$pct.= "</table>
							</form>";
				}
			}
			elseif(!empty($_POST['step2'])) // squad / membres selectionnés
			{
				$members_selected = $_POST['mbr'];
				$member_count = !empty($_POST['mbr']) ? count($_POST['mbr']) : false;
				$squad_selected = isset($_POST['squad_save']) ? true : false;
				$xml_data = import_xml_to_array($_POST['step2']);
				
				$total_members = 0;
				$squad_valided = 0;
				$members_linked = 0;
								
				if(!empty($xml_data['errors']))
				{
					$pct.= "<div id=\"E_Toast\"><h1>XML File corrupted</h1>
							It seems that the URL you given doesn't match with an XML file <b>OR</b> 
							your XML contains errors. Please see this error report to know what is the problem with your XML file.<br />
							{$xml_data['errors']}
							</div>";
							echo "error.";
				}
				elseif(!empty($xml_data) && is_array($xml_data))
				{
					if($squad_selected)
					{
						try
						{
							$sqd_request = $link->prepare("INSERT INTO {$prefix}squads (sqd_name, sqd_tag, sqd_web, sqd_mail, sqd_title) VALUES ( :nom, :tag, :url, :mail, :title)");
							$sqd_request->bindParam(':nom',		$xml_data['clan_NAME']); 
							$sqd_request->bindParam(':tag',		$xml_data['clan_TAG']);
							$sqd_request->bindParam(':url',		$xml_data['clan_URL']);
							$sqd_request->bindParam(':mail',	$xml_data['clan_MAIL']);
							$sqd_request->bindParam(':title',	$xml_data['clan_TITLE']);
							$sqd_request->execute();
							
							$sqd_inserted_id = $link->lastInsertId();
							$sqd_request->closeCursor();
							
							$squad_valided = 1;
						}
						catch(PDOException $e)
						{
							$pct.= "<div id=\"E_Toast\"><h1>XML File corrupted</h1>
									Fatal PDO error when adding a squad: <br />{$e->getMessage()}</div>";
						}
					}
					
					if($member_count != 0 && $member_count != false)
					{
						foreach($xml_data['member'] as $membre)
						{
							if(in_array($membre['ID'], $members_selected))
							{
								try
								{
									$mbr_request = $link->prepare("INSERT INTO {$prefix}members 
										(mbr_uid, mbr_name, mbr_email, mbr_icq, mbr_remark, mbr_nick) 
										VALUES 
										( :uid, :name, :mail, :icq, :remark, :nick)");
									$mbr_request->bindParam(':uid',		$membre['ID']); 
									$mbr_request->bindParam(':name',	$membre['NAME']);
									$mbr_request->bindParam(':mail',	$membre['EMAIL']);
									$mbr_request->bindParam(':icq',		$membre['ICQ']);
									$mbr_request->bindParam(':remark',	$membre['REMARK']);
									$mbr_request->bindParam(':nick',	$membre['NICK']);
									$mbr_result = $mbr_request->execute();
							
									$mbr_inserted_id = $link->lastInsertId();
									
									if($squad_valided && !empty($sqd_inserted_id) && $mbr_result)
									{
										try
										{
											$rel_request =	$link->prepare("INSERT INTO {$prefix}sqdrels 
															(sqd_id, mbr_id) VALUES ( :sqdid, :mbrid)");
											$rel_request->bindParam(':sqdid',	$sqd_inserted_id); 
											$rel_request->bindParam(':mbrid',	$mbr_inserted_id);
											
											if($rel_request->execute())
											{
												$members_linked++;
											}
											
											
										}
										catch(PDOException $e)
										{
											$pct.= "<div id=\"E_Toast\"><h1>XML File corrupted</h1>
													Fatal PDO error when adding a member to the squad: <br />{$e->getMessage()}</div>";
										}
									}
									
									$total_members ++;
									
								}
								catch(PDOException $e)
								{
									$pct.= "<div id=\"E_Toast\"><h1>XML File corrupted</h1>
											Fatal PDO error when adding a member: <br />{$e->getMessage()}</div>";
								}
							}
						}
					}
				}
				else
				{
					die("Uncaught error (#-1) on line ".__LINE__." from file ".__FILE__);
				}
				
				$ctt = $cttb = "";
				if($squad_valided)
				{
					$ctt = "The squad &quot;<b>{$xml_data['clan_NAME']}</b>&quot; have been added to the database.<br />";
					$cttb = "<a href='index.php?dir=list&amp;item=squads&amp;id={$sqd_inserted_id}'>Edit this squad now (define ALIAS, etc.)</a> 
						or ";
				}
				
				$pct.= "<div id=\"V_Toast\"><h1>XML File finished</h1>
						".$ctt."A total of <b>{$total_members}</b> members have been added to the database.<br />
						In these members, <b>{$members_linked}</b> have been linked to the squad &quot;<b>{$xml_data['clan_NAME']}</b>&quot;
						<br /><br />".$cttb."
						<a href='index.php?dir=home'>go back Home</a>.</div>";
				
			}
			else
			{
				$pct.= draw_import_form();
			}
			
			if($show_form)
			{
				$pct.= draw_import_form();
			}
			
			$pct.= "";
		}
		// MAINTENANCE - DBCLEANER
		elseif($_GET['item'] == 'dbcleaner')
		{
			$title = "Database cleaner";
			$pct.= "";
			
			try
			{
				// POSTCODE
				if(!empty($_POST['member_id']))
				{
					$orphan_cleared = 0;
					foreach($_POST['member_id'] as $udbid)
					{
						$rmvfdb = $link->prepare("DELETE FROM {$prefix}members WHERE id=:a");
						$rmvfdb->bindValue(':a', $udbid);
						$rmvfdb->execute();
						$orphan_cleared++;
					}
					
					$pct.= "<div id='V_toast'>
							<h1>Operation successed.</h1>
							<b>{$orphan_cleared}</b> member(s) have been removed from the database.
							</div>
							<br /><br />";
				}
				
				// GLOBAL CODE
				$req = $link->prepare("	SELECT	{$prefix}members . * 
										FROM	{$prefix}members
										LEFT JOIN {$prefix}sqdrels ON {$prefix}members.id = {$prefix}sqdrels.mbr_id
										WHERE {$prefix}sqdrels.mbr_id IS NULL
										ORDER BY mbr_name ASC");
				$req->execute();
				$tbl_ctt = "";
				$orphan_count = 0;
				
				while($fetch = $req->fetch(PDO::FETCH_OBJ))
				{
					$tbl_ctt.= "<tr>
								<td><input name='member_id[]' checked='checked' value='{$fetch->id}' type='checkbox' /></td>
								<td>{$fetch->id}</td><td>{$fetch->mbr_uid}</td><td>{$fetch->mbr_name}</td>
							</tr>";
					$orphan_count ++;
				}
				
				if($orphan_count === 0)
				{
					$tbl_ctt.="<tr><td colspan='4'>There is no orphan members</td></tr>";
				}
				
				$pct.= "<form action='index.php?dir=maintenance&amp;item=dbcleaner' method='post'>
						<div id='N_toast'>
						Welcome to the Database Cleaner. In this section, you can remove all members without any squad. 
						You have to select which players you want to remove, then click on the button below.
						<br /><br />
						<input type=\"button\" ".($orphan_count == 0 ? "disabled='disabled'" : "")." value=\"Uncheck all\" onclick=\"this.value=CheckAll(this.value, 'member_id[]');\" />
						<input type='submit' ".($orphan_count == 0 ? "disabled='disabled'" : "")." value='Remove selecteds from Database' /></div><br />
						
						<table>
						<tr>
							<th width='50'></th>
							<th width='70'>ID</th>
							<th width='100'>UID</th>
							<th width='200'>Player Name</th>
						</tr>{$tbl_ctt}</table></form>";
			}
			catch(PDOException $e)
			{
				die($e->getMessage());
			}
			
			
		}
		elseif($_GET['item'] == 'settings')
		{
			$title = "Settings";
			
			try
			{
				$req_params = $link->prepare("SELECT * FROM {$prefix}settings");
				$req_params->execute();
				
				if($req_params->rowCount() > 0)
				{
					$cvarlist = array(array());
					$i = 0;
					while($fetch_params = $req_params->fetch(PDO::FETCH_OBJ))
					{
						$cvarlist[$i]['CVAR'] = $fetch_params->cvar;
						$cvarlist[$i]['VALUE'] = $fetch_params->value;
						$i++;
					}
				}
				$req_params->closeCursor();
				
				// Traitement formulaires
				
				
				// Affichage
				foreach($cvarlist as $param)
				{
					$pct.= "<br /><b>".TransformToReadable($param['CVAR'])."</b>: {$param['VALUE']}";
				}
			}
			catch(PDOException $e)
			{
				die($e->getMessage());
			}
			
			$pct.= "";
		}
		// MAINTENANCE - UNINSTALL
		elseif($_GET['item'] == 'uninstall')
		{
			$title = "removing A2Squad";
			$pct.= "";
		}
		else
		{
			$err = true;
		}
	}
	else
	{
		$err = true;
	}
	
	if($err)
	{
		$title = "Invalid request";
		$pct.=	"<div id=\"E_Toast\"><h1>Invalid request.</h1>
				It seems that you entered a wrong URL or the website had a problem while searching the page.<br />
				Please try again later.</div>";
	}
?>

<html>
		<head>
			<title><?php echo $title ?> :: Team Manager Panel - A2Squad</title>
			<link rel="stylesheet" type="text/css" href="squads/A2Squad.css" />
			<script type="text/javascript" src="files/functions.js"></script>
			<link rel="shortcut icon" href="favicon.ico" />
		</head>
		<body>
			
			<div id="header"><a href="index.php?dir=home"><img src="images/spacer.png" width="980" height="150" /></a></div>
			
			<div id="global_container">
				<?php echo $pct; ?>
			</div>
			
			<div id="footer">
				<a href="http://dev.aenoa.net/A2Squad/">Arma 2 Squad (A2Squad)</a> by 
				<a href="http://aenoa.net/">Hugo '_Aenoa' Regibo</a>
				<br />
				current version: 
				<b>1.0</b> (Private, edited for <a href="http://sigclan.com/">SiG Clan</a>)
				<br />
				<a rel="license" href="http://creativecommons.org/licenses/by-nc/3.0/"><img alt="Licence Creative Commons" style="border-width:0" src="http://i.creativecommons.org/l/by-nc/3.0/80x15.png" /></a>
			</div>
		</body>
	</html>
	
	<?php
	ob_end_flush();
	?>