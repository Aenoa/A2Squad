<?php
	
	/**
	 * this function explode an xml file as an multidimentional array.
	 * @param string $xml_address the URL to load
	 * @return array The xml, as an array
	 */
	function import_xml_to_array($xml_address)
	{
		libxml_use_internal_errors(true); 
		$schema = "A2Squad.xsd";
		$dom = new DOMDocument("1.0");
		$dom->load($xml_address);
		$is_valid_url = filter_var($xml_address, FILTER_VALIDATE_URL) ? true : false;
		if(!$is_valid_url){return false;}
		
		$errlog = "";
		$returned_var = array();
		$validate = $dom->schemaValidate($schema); 
		
		foreach(libxml_get_errors() as $error) {$errlog.=  "<br /> ". $error->message;}
		// transmission errorlog
		$returned_var['errors'] = $errlog;
		
		if(!$validate)
		{
			return $returned_var;
		}
		
		$xmlstr = simplexml_load_string($dom->saveXML());
		$returned_var['clan_NAME']	= (string) $xmlstr->name;
		$returned_var['clan_URL']	= (string) $xmlstr->web;
		$returned_var['clan_MAIL']	= (string) $xmlstr->email;
		$returned_var['clan_TITLE']	= (string) $xmlstr->title;
		$returned_var['clan_TAG']	= (string) $xmlstr['nick'];
		
		$returned_var['member'] = array();
		$i = 0;
		foreach($xmlstr->member as $member)
		{
			$returned_var['member'][$i]				= array();
			$returned_var['member'][$i]['ID']		= (int) $member['id'];
			$returned_var['member'][$i]['NICK']		= (string) $member['nick'];
			$returned_var['member'][$i]['NAME']		= (string) $member->name;
			$returned_var['member'][$i]['EMAIL']	= (string) $member->email;
			$returned_var['member'][$i]['ICQ']		= (string) $member->icq;
			$returned_var['member'][$i]['REMARK']	= (string) $member->remark;
			$i ++;
		}
		
		return $returned_var;
	}
	
	function libxml_display_error($error) 
	{
		$return = "<br/>\n";
		switch ($error->level) 
		{
		   case LIBXML_ERR_WARNING:
			   $return .= "<b>Warning $error->code</b>: ";
			   break;
		   case LIBXML_ERR_ERROR:
			   $return .= "<b>Error $error->code</b>: ";
			   break;
		   case LIBXML_ERR_FATAL:
			   $return .= "<b>Fatal Error $error->code</b>: ";
			   break;
		}
		$return .= trim($error->message);
		if ($error->file) 
		{
			$return .= " in <b>$error->file</b>";
		}
		
		$return .= " on line <b>$error->line</b>\n";

		return $return;
	}
 
	function libxml_display_errors($display_errors = true) 
	{
		$errors = libxml_get_errors();
		$chain_errors = "";

		foreach ($errors as $error) 
		{
			$chain_errors .= preg_replace('/( in\ \/(.*))/', '', strip_tags(libxml_display_error($error)))."\n";
			
			if ($display_errors) 
			{
				trigger_error(libxml_display_error($error), E_USER_WARNING);
			}
		}
		libxml_clear_errors();
		return $chain_errors;
	}
 



	function PwdCrypt($x0b)
	{
		$x0e="\x63ryp\x74"; 
		return $x0e($x0b, '$1$x0c.CryptedPwd.$2$3$x0d$');
	}
	
	
	function CreateToken($string)
	{
		$length = strlen($string) -1;
		$thread = $string;
		
		$init = 0;
		for($i = 0; $i < $length; $i ++)
		{
			$init += ord(substr($string, $i, 1));
		}
		
		$med_length = floor($length / 4);
		$med_init = floor(strlen($init) / 4);
		
		$final =	substr($string, 0,					$med_length). substr($init, 0,				$med_init) . 
					substr($string, $med_length,		$med_length). substr($init, $med_init,		$med_init) .
					substr($string, $med_length * 2,	$med_length). substr($init, $med_init * 2,	$med_init) .
					substr($string, $med_length * 3,	$med_length). substr($init, $med_init * 3,	$med_init) .
					$thread . $med_init . $med_length;
		
		return $final;
	}
	
	function TransformToReadable($string)
	{
		switch($string)
		{
			case 'A2S_MANAGERPWD':
				return 'Manager Password';
				break;
			
			case 'A2S_CONTACTADDRESS':
				return 'Admin e-Mail';
				break;
			
			case 'A2S_CONNECTNOTIFY':
				return 'Notify by mail on connect';
				break;
			
			default:
				return '&gt;Untranslated cvar&rt;';
				break;
		}
	}
	
	function draw_import_form()
	{
		return "Welcome to the XML Import section of A2Squad. In this section, you can import, from an existing URL, 
				a XML file who contains a Squad and members of a squad. After validating the URL, you will be able to 
				select the squad, the members, or both of them in the database.<br /><br />
				
				<form action='manager.php?dir=maintenance&amp;item=import' method='post'>
				<h2>Please insert the XML URL</h2>
				<input type='text' name='file_url' maxlength='255' class='form_url' />
				<br />
				<input type='submit' value='load this XML' />
				</form>";
	}
	
	function draw_squad_form($name = "", $mail = "", $title = "", $tag = "", $alias = "", $website = "", $id = 0)
	{
		return "<table>
					<tr>
						<td>Squad name:</td>
						<td><input type='text' name='sqd_name[]' value='{$name}' />
							<input type='hidden' name='sqd_id[]' value='{$id}' /></td>
					</tr>
					<tr>
						<td>Squad mail:</td>
						<td><input type='text' name='sqd_mail[]' value='{$mail}' /></td>
					</tr>
					<tr>
						<td>Squad title</td>
						<td><input type='text' name='sqd_title[]' value='{$title}' /></td>
					</tr>
					<tr>
						<td>Squad TAG</td>
						<td><input type='text' name='sqd_tag[]' value='{$tag}' /></td>
					</tr>
					<tr>
						<td>Squad Alias</td>
						<td><input type='text' name='sqd_alias[]' value='{$alias}'' /></td>
					</tr>
					<tr>
						<td>Squad Website</td>
						<td><input type='text' name='sqd_web[]' value='{$website}' /></td>
					</tr>
				</table>";
	}
	
	function draw_member_form($name = "", $mail = "", $uid = "", $icq = "", $nick = "", $rmq = "", $id = 0)
	{
		return "<table>
					<tr>
						<td>Member name:</td>
						<td><input type='text' name='mbr_name[]' value='{$name}' />
							<input type='hidden' name='mbr_id[]' value='{$id}' /></td>
					</tr>
					<tr>
						<td>Member mail:</td>
						<td><input type='text' name='mbr_mail[]' value='{$mail}' /></td>
					</tr>
					<tr>
						<td>Member UID</td>
						<td><input type='text' name='mbr_uid[]' value='{$uid}' /></td>
					</tr>
					<tr>
						<td>Member ICQ</td>
						<td><input type='text' name='mbr_icq[]' value='{$icq}' /></td>
					</tr>
					<tr>
						<td>Member Nick</td>
						<td><input type='text' name='mbr_nick[]' value='{$nick}' /></td>
					</tr>
					<tr>
						<td>Member Remark</td>
						<td><input type='text' name='mbr_rmq[]' value='{$rmq}' /></td>
					</tr>
				</table>";
	}
?>