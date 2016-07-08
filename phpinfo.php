<?php

//pokud jsem na vyvojovem stroji
$ISDEV = ($_SERVER["SERVER_NAME"] == 'localhost'
		 )?true:false;

if($ISDEV)
	{
	//vyvojova masina
	if ($_SERVER["SERVER_NAME"] == 'vodni.poutnicikolin.cz')
		{
		//define('ROOT_DIR',"../");
  	  	//define('ROOT_DIR', "/home/www/poutnicikolin.cz/subdomains/dev/admin/");
		define('HTTP_DIR','http://'.$_SERVER['HTTP_HOST']."/vodni/srazvs/");
		//echo ROOT_DIR;
  		}
	else {
		 define('ROOT_DIR',$_SERVER['DOCUMENT_ROOT'].'/vodni/srazvs/');
		 define('HTTP_DIR','http://'.$_SERVER['HTTP_HOST'].'/vodni/');
		 }
	}
//ostra masina
else {
	// na ostrem stroji musi byt vzdy za ROOT_DIR slash "/"
	define('ROOT_DIR', $_SERVER['DOCUMENT_ROOT'].'/srazvs/');
	define('HTTP_DIR', 'http://'.$_SERVER['HTTP_HOST'].'/');
	echo "ostrej<br />";
}

echo $_SERVER['DOCUMENT_ROOT']."<BR />";
echo $_SERVER['HTTP_HOST']."<br />";
echo ROOT_DIR."<br />";
echo HTTP_DIR."<br />";

phpinfo();
?>
