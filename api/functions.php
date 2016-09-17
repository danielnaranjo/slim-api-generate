<?php
// Make a database connection
@mysql_connect(DB_HOST,DB_USERNAME,DB_PASSWORD) or die('Error: Cant connect. Please, contact our support team. ');// . mysql_error()
@mysql_select_db(DB_NAME);
// http://apievangelist.com/2013/10/21/deploy-api-mysql-to-api/

function vAPI() {
    return filemtime('index.php').' '.date("m/d/Y H:i:s", filectime('index.php'));
}

function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);
    // setting response content type to json
    $app->contentType('application/json;charset=utf-8');
    echo json_encode($response);
}

function createFile($folder, $name, $content, $check) {
	if($check==false){
		$generateFile = fopen($folder.'/'.$name, "w") or die("Unable to open file: ".$folder.'/'.$name);
		fwrite($generateFile, $content);
		fclose($generateFile);
		$status = false;
	} else {
		$status = true;
	}
	return $status;
}

function createRoutes(){
	$folder = ROUTESFOLDER;
	$file_route = 'routes.php';
	$files = scandir($folder);
	// si existe, lo limpiamos
	if(file_exists($file_route)){
		file_put_contents($file_route,'');
	}
	$current_file = file_get_contents($file_route);
	$current_file.= "<?php\n";
	$current_file.= "/* dynamic routes */\n\r";
	foreach ($files as &$value) {
		// previene los archivos de directorio, como "." o ".."
		if ($value!='.' && $value!='..') { 
			$current_file .= "include('$folder$value');\n\r";
		}
	}
	// Escribe el contenido
	$current_file.= "/* dynamic routes */";
	file_put_contents($file_route, $current_file);
	return "OK";
}

function renameMethod(){
	$folder = ROUTESFOLDER;
	$files = scandir($folder);
	$status="";
	// scan folder and parser
	foreach ($files as &$value) {
		// check if file
		if ($value!='.' && $value!='..' && $value!='_commons.php') {
			replaceMethod($folder.$value);
		}// only files
	}//foreach
	return "OK";
}

function replaceMethod($filename){
	$originalContent = file_get_contents($filename);
	$newContent = str_replace('@', '$', $originalContent);
	$newContent1 = str_replace(',)', ')', $newContent);
	$newContent2 = str_replace(',  WHERE ', '  WHERE ', $newContent1);
	file_put_contents($filename, $newContent2);
	//return $status;
}
