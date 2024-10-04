<?php
/***************************************
** Filename.......: example.php
** Project........: Mime Decoder
** Last Modified..: 26 September 2001
***************************************/

/***************************************
** This script shows how to use the mime
** decoding class. All it does is take
** the input, decode it, and show it on
** screen.
***************************************/

	include('./mimeDecode.php');

	$filename = './example.email.txt';
	$message  = fread(fopen($filename, 'r'), filesize($filename));

	$tst_str = substr((string)$message,0,2048);
	if (strstr($test_str,"\r\n")) $crlf = "\r\n";
	else $crlf = "\n";
	
	header('Content-Type: text/plain');
	header('Content-Disposition: inline; filename="stuff.txt"');

	$params = array(
					'input'          => $message,
					'crlf'           => $crlf,
					'include_bodies' => TRUE,
					'decode_headers' => TRUE,
					'decode_bodies'  => TRUE
					);

	$Msg = Mail_mimeDecode::decode($params);
	
	// Look in Email for evidence of SR request
	// Determine Main body that content will be added to existing request.
	// Read contents down to From: line.
	
	if (is_array($Msg->parts)) {
		foreach($Msg->parts as $Part) {
			if ($Part->ctype_primary == "text" &&  $Part->ctype_secondary == "plain") {
				echo $Part->body;
			}
			if ($Part->ctype_primary == "text" &&  $Part->ctype_secondary == "html") {
				echo $Part->body;
			}
		}
	}
	
	
	
?>