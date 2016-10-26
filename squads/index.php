<?php
ob_start();
// --- DEBUG VAR ---
	ini_set('display_errors', 'On');
	error_reporting(-1);
	// -----------------
	if(!file_exists('../files/config.inc.php'))
	{
		header('location: ../files/install.php');
	}

	require_once '../files/config.inc.php';

	global $cn_host;
	global $cn_data;
	global $cn_user;
	global $cn_pass;
	global $prefix;

	// Declaring an error squad function
	function show_error_squad()
	{
		echo	"<squad nick=\"NULL\">\n";
		echo	"\t<name>A2Squad - Invalid ID</name>\n";
		echo	"\t<email>hugo.r@aenoa.net</email>\n";
		echo	"\t<web>http://aenoa.net/</web>\n";
		echo	"\t<picture>err.paa</picture>\n";
		echo	"\t<title>N/A</title>\n";
		echo	"\t\t<member id=\"0\" nick=\"Invalid\">\n";
		echo	"\t\t\t<name>No member in squad</name>\n";
		echo	"\t\t\t<email></email>\n";
		echo	"\t\t\t<icq></icq>\n";
		echo	"\t\t\t<remark>Maybe you badly enterred the name / ID ?</remark>\n";
		echo	"\t\t</member>\n";
		echo	"</squad>";
		return true;
	}

	// Content type
	header('content-type: text/xml');

	// XML Headers
	echo "<?xml version=\"1.0\"?>
	<!DOCTYPE squad SYSTEM \"A2Squad.dtd\">
	<?xml-stylesheet href=\"A2Squad.xsl\" type=\"text/xsl\"?>\n\n";

	// Checking if url is valid
	$tid = !empty($_GET['tid']) ? $_GET['tid'] : false;

	if($tid != false)
	{
		$is_id = is_numeric($tid) && $tid > 0;
		// MySQL Connector
		try
		{
			$connector	=	new PDO('mysql:host='.$cn_host.';dbname='.$cn_data, $cn_user, $cn_pass);
			// Checking if 
			$cn_rq_p1 =	"SELECT * FROM {$prefix}squads WHERE ". ($is_id ? "id=:st LIMIT 0,1" : "sqd_alias=:st LIMIT 0,1");
			$cn_rq_1 = $connector->prepare($cn_rq_p1);
			$cn_rq_1->bindParam(":st", $tid, $is_id ? PDO::PARAM_INT : PDO::PARAM_STR, $is_id ? 10 : 20);
			$cn_rq_1->execute();

			if($cn_rq_1->rowCount())
			{
				// Team found !
				$cn_ft_1 = $cn_rq_1->fetch(PDO::FETCH_OBJ);
				// Executing the squad XML
				echo	"<squad nick=\"".$cn_ft_1->sqd_tag."\">\n";
				echo	"\t<name>".$cn_ft_1->sqd_name."</name>\n";
				echo	"\t<email>".$cn_ft_1->sqd_mail."</email>\n";
				echo	"\t<web>".$cn_ft_1->sqd_web."</web>\n";
				echo	"\t<picture>".$cn_ft_1->id.".paa</picture>\n";
				echo	"\t<title>".$cn_ft_1->sqd_title."</title>\n\n";

				// Getting data for members
				$cn_rq_2 = $connector->prepare("SELECT * FROM {$prefix}members 
												LEFT JOIN {$prefix}sqdrels ON {$prefix}members.id={$prefix}sqdrels.mbr_id 
												WHERE {$prefix}sqdrels.sqd_id=:st");
				$cn_rq_2->bindParam(":st", $cn_ft_1->id, PDO::PARAM_INT, 10);
				$cn_rq_2->execute();
				$cn_rq_1->closeCursor();

				// Counting members
				$cn_ct_2 = $cn_rq_2->rowCount();

				if($cn_ct_2 > 0)
				{
					// Showing all members of the group
					while($cn_ft_2 = $cn_rq_2->fetch(PDO::FETCH_OBJ))
					{
						echo "\t<member id=\"".$cn_ft_2->mbr_uid."\" nick=\"".$cn_ft_2->mbr_nick."\">\n";
						echo "\t	<name>".$cn_ft_2->mbr_name."</name>\n";
						echo "\t	<email>".$cn_ft_2->mbr_email."</email>\n";
						echo "\t	<icq>".$cn_ft_2->mbr_icq."</icq>\n";
						echo "\t	<remark>".$cn_ft_2->mbr_remark."</remark>\n";
						echo "\t</member>\n";
					}
					$cn_rq_2->closeCursor();
				}
				$connector = null;
				echo	"</squad>";
			}
			else
			{
				// No team found
				$cn_rq_1->closeCursor();
				show_error_squad();
			}

		}
		catch(PDOException $e)
		{
			die("Erreur! ligne ".$e->getLine().":<br />".$e->getMessage());
		}

	}
	else
	{
		show_error_squad();
	}
ob_end_flush();
?>
