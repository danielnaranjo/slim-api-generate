 <?php
require 'Slim/Slim.php';
// El framework Slim tiene definido un namespace llamado Slim
// Por eso aparece \Slim\ antes del nombre de la clase.
\Slim\Slim::registerAutoloader();
 
// Creamos la aplicación.
$app = new \Slim\Slim();
$app->config('debug', true);
$app->log->setEnabled(true);

include('config.php');
// Make a database connection
@mysql_connect(DB_HOST,DB_USERNAME,DB_PASSWORD) or die('Error: Cant connect. Please, contact our support team. ');// . mysql_error()
@mysql_select_db(DB_NAME);
// http://apievangelist.com/2013/10/21/deploy-api-mysql-to-api/

function vAPI() {
    return filemtime('index.php').' '.@date("m/d/Y H:i:s", filectime('index.php'))||'';
}

function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);
    // setting response content type to json
    $app->contentType('application/json;charset=utf-8');
    echo json_encode($response);
}

function createFile($folder, $name, $content) {
	$generateFile = fopen($folder.'/'.$name, "w") or die("Unable to open file: ".$folder.'/'.$name);
	fwrite($generateFile, $content);
	fclose($generateFile);
	return "OK";
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
	return $status;
}

function replaceMethod($filename){
	$originalContent = file_get_contents($filename);
	$newContent = str_replace('@', '$', $originalContent);
	file_put_contents($filename, $newContent);
	//return $status;
}


$app->error(function (\Exception $e) use ($app) {
    $app->render('error.php');
});

$app->get('/', function () {
    echo '<h1>Welcome to our API</h1><p>More information: www.loultimoenlaweb.com</p>';
    echo '<h3>If you are view this page, please contact to Web administrator.</h3>';
	echo '<p>More information: www.loultimoenlaweb.com</p>';
	echo '<p>Last update: v.'.vAPI().'</p>';

});

$app->get('/tables', function () {
   
   	$sql = "SHOW tables";
   	$result = mysql_query($sql) or die('Query failed: '. $sql .'>'. mysql_error());
   	$Datos = array();
   	while ($row = mysql_fetch_array($result)){
   		$P = array();
   		$P = $row[0];
   		array_push($Datos, $P);	
   }
   echoResponse(200, $Datos);
});


$app->get('/column/:column', function ($column) {

   	$sql = "SHOW COLUMNS FROM $column";
   	$result = mysql_query($sql) or die('Query failed: '. $sql .'>'. mysql_error());
   	$Datos = array();
   	while ($row = mysql_fetch_array($result)){
   		$P = array();
   		$P = $row[0];
   		array_push($Datos, $P);	
   }
   echoResponse(200, $Datos);
});


$app->get('/v1/:table', function ($table) {
	$sql="SELECT * FROM $table ";
	$result = mysql_query($sql) or die('Query failed: '. $sql .' > '. mysql_error());
	$data = array();

	while ($row = mysql_fetch_assoc($result)) {
		array_push($data, $row);
	};
	// Return JSON
	echoResponse(200, $data);	
});

///start/api/installer
$app->get('/database', function () {
   	$sql = "SHOW tables";
   	$result_database = mysql_query($sql) or die('Query failed: '. $sql .'>'. mysql_error());
   	$total = mysql_num_rows($result_database);
   	$data = [];
   	$data['configuration'] = 'started';
   	$data['sql'] = "added: ". $total .' tables';

   	// parse tables
   	while ($row_database = mysql_fetch_array($result_database)){
   		$current_table = $row_database[0]; //$sql_table = "SHOW COLUMNS FROM ".$current_table;

		// parsing fields on current table
		$sql_table = "DESCRIBE ".$current_table;
	   	$result_table = mysql_query($sql_table) or die('Query failed: '. $sql_table .'>'. mysql_error());

	   	// encabezado
		$addLine = "<?php\n";
		$current_data = [];
	   	// comments on files
	   	$addLine.= "/* This file is auto generate by script */\n\n";
	   	$addLine.= "/* Daniel Naranjo */\n";
	   	$addLine.= "/* September 2016 */\n\n\n";
	   	$addLine.= "/* Table: $current_table (structure) */\n";

		// recorro los campos como registros
	   	while ($row_table = mysql_fetch_array($result_table)){
	   		$current_data = $row_table[0];
			// contenido del archivo
			$addLine.= "//    ".$current_data."\n";
	   	}

	   	// comments on files
	   	$addLine.= "\n\n";
	   	$addLine.= "/* DO NOT add, edit or remove any code */\n";
	   	$addLine.= "/* This will be remove on regenerate. */\n";
	   	$addLine.= "/* Attach custom routes on routes/_commons.php */\n";
	   	$addLine.= "\n\n\n";
	   	
	   	// HTTP methods
	   	$addLine.= "/* method get */\n";
		$addLine.= "@app->get('/v1/$current_table', function () { \n";
		$addLine.= "    @sql_query=\"SELECT * FROM $current_table\"; \n";
		$addLine.= "    @result = mysql_query(@sql_query) or die('Error: Can not execute $current_table action'); \n";
		$addLine.= "    while (@row = mysql_fetch_array(@result)) { \n";
		$addLine.= "        echoResponse(200,@row);\n";
		$addLine.= "    }\n";
	   	$addLine.= "});\n";

	   	$addLine.= "/* method getbyid */\n";
		$addLine.= "@app->get('/v1/$current_table/:id', function (@id) { \n";
		$addLine.= "    @sql_query=\"SELECT * FROM $current_table WHERE ".$current_table."_id='@id' \"; \n";
		$addLine.= "    @result = mysql_query(@sql_query) or die('Error: Can not execute $current_table action'); \n";
		$addLine.= "    while (@row = mysql_fetch_array(@result)) { \n";
		$addLine.= "        echoResponse(200,@row);\n";
		$addLine.= "    }\n";
	   	$addLine.= "});\n";

	   	$addLine.= "/* method delete */\n";
		$addLine.= "@app->delete('/v1/$current_table/:id', function (@id) { \n";
		$addLine.= "    @sql_query=\"DELETE FROM $current_table WHERE ".$current_table."_id='@id' \"; \n";
		$addLine.= "    mysql_query(@sql_query) or die('Error: Can not execute $current_table action'); \n";
		$addLine.= "    echoResponse(204,'Delete!');\n";
	   	$addLine.= "});\n";

	   	$addLine.= "/* method put */\n";
		$addLine.= "@app->put('/v1/$current_table/:id', function (@id) { \n";
		$addLine.= "    @sql_query=\"UPDATE $current_table SET WHERE ".$current_table."_id='@id' \"; \n";
		$addLine.= "    @result = mysql_query(@sql_query) or die('Error: Can not execute $current_table action'); \n";
		$addLine.= "    echoResponse(200,'Updated!');\n";
	   	$addLine.= "});\n";

	   	$addLine.= "/* method post */\n";
		$addLine.= "@app->post('/v1/$current_table', function () use (@app) { \n";
		$addLine.= "    @app->request();";
		$addLine.= "    //@body = @app->request()->getBody(); ";
		$addLine.= "    //@email = @app->request->post('email'); ";
		$addLine.= "    @sql_query=\"INSERT INTO ".$current_table." ()\"; \n";
		$addLine.= "    mysql_query(@sql_query) or die('Error: Can not execute $current_table action'); \n";
		$addLine.= "    echoResponse(200,'Added!');\n";
	   	$addLine.= "});\n";

	   	// Ultima linea del archivo con el nombre de la table
	   	$addLine.= "/* table: $current_table */\n\n\n";
	   	$addLine.= "/* DO NOT add, edit or remove any code */\n";
	   	$addLine.= "/* This will be remove on regenerate. */\n";
	   	$addLine.= "/* Attach custom routes on routes/_commons.php */\n";

		// genera el archivo por table
		createFile(ROUTESFOLDER, $current_table.'.php', $addLine);
   	}

   	$data['methods'] = 'done'; 
   	$data['mapping'] = 'done';
   	// Commons routes and associations routes
   	$data['commons'] = createFile('routes', '_commons.php',"<?php\n/* Commons routes and associations routes */");
   	// Create routes configuration file
   	$data['routing'] = createRoutes();
   	// replace namespaces on every route file
   	$data['renaming'] = renameMethod();
   	$data['status'] = 'success';
   	// done!

   	replaceMethod(ROUTESFOLDER.'task.php');
   	echoResponse(200, $data);
});

include('routes.php');

$app->run();
?>