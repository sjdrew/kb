<?

for($i = 0; $i < 100; ++$i) {

	$link = mssql_connect('cgysqlp3\cgysqlp3', "KBApp", 'kb$Zz01$02', true);
	
	if (!$link) {
		echo "Failed: " . mssql_get_last_message() . "<br>\n";
		exit;
	}
	else {
	
	echo "Connected OK<br>\n";
	flush();
	
	mssql_close($link);
	
	}
}
?>