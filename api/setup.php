<?php
  $app->get('/start/api/installer', function () {
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
	   	$addLine.= "/* This file is auto generate by script on ".GENERATED." */\n\n";
	   	$addLine.= "/* Daniel Naranjo */\n";
	   	$addLine.= "/* September 2016 */\n\n\n";
	   	$addLine.= "/* Table: $current_table (structure) */\n";

		// recorro los campos como registros
		$fieldstoadd="";
		$fieldspost="";
		$fieldstoupdate="";
	   	while ($row_table = mysql_fetch_array($result_table)){
	   		$current_data = $row_table[0];
			// contenido del archivo
			$addLine.= "//    ".$current_data."\n";
			if($current_table."_id"==$current_data){
				// insert
				$fieldstoadd.="'NULL',";
			} else {
				// insert values
				$fieldstoadd.="'@$current_data',";
				// form post request
				$fieldspost .= "@$current_data = @app->request->params('$current_data');\n\r";
			}
			// agrego al update
			$fieldstoupdate.="$current_data='@$current_data', ";
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
		//$addLine.= "    @row = mysql_fetch_assoc(@result);//@data=array();\n";
		//$addLine.= "    @total = mysql_num_rows(@result);//@data=array();\n";
		$addLine.= "    @data=array();\n";
		$addLine.= "    while (@row = mysql_fetch_assoc(@result)) { \n";
		//$addLine.= "        @data_row=array();\n";
		//$addLine.= "        @data_row['results']=@row;\n";
		$addLine.= "        array_push(@data, @row);\n";//echoResponse(200,@row);\n";
		//$addLine.= "        echoResponse(200,@row);\n";
		$addLine.= "    }\n";
		//$addLine.= "    @data['total']=@total;\n";
		$addLine.= "    echoResponse(200,@data);\n";
	   	$addLine.= "});\n";

	   	$addLine.= "/* method getbyid */\n";
		$addLine.= "@app->get('/v1/$current_table/:id', function (@id) { \n";
		$addLine.= "    @sql_query=\"SELECT * FROM $current_table WHERE ".$current_table."_id='@id' \"; \n";
		$addLine.= "    @result = mysql_query(@sql_query) or die('Error: Can not execute $current_table action'); \n";
		$addLine.= "    while (@row = mysql_fetch_assoc(@result)) { \n";
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
		$addLine.= "    @sql_query=\"UPDATE $current_table SET ".$fieldstoupdate." WHERE ".$current_table."_id='@id' \"; \n";
		$addLine.= "    @result = mysql_query(@sql_query) or die('Error: Can not execute $current_table action'); \n";
		$addLine.= "    echoResponse(200,'Updated!');\n";
	   	$addLine.= "});\n";

	   	$addLine.= "/* method post */\n";
		$addLine.= "@app->post('/v1/$current_table', function () use (@app) { \n";
		$addLine.= "    @app->request();\n";
		$addLine.= "    @body = @app->request()->getBody(); \n";
		$addLine.= "    ".$fieldspost."\n";
		$addLine.= "    @sql_query=\"INSERT INTO ".$current_table." VALUES (".$fieldstoadd.")\"; \n";
		$addLine.= "    mysql_query(@sql_query) or die('Error: Can not execute $current_table action'); \n";
		$addLine.= "    echoResponse(200,'Added!');\n";
	   	$addLine.= "});\n";

	   	// Ultima linea del archivo con el nombre de la table
	   	$addLine.= "/* table: $current_table */\n\n\n";
	   	$addLine.= "/* DO NOT add, edit or remove any code */\n";
	   	$addLine.= "/* This file is auto generate by script on ".GENERATED." */\n\n";
	   	$addLine.= "/* This will be remove on regenerate. */\n";
	   	$addLine.= "/* Attach custom routes on routes/_commons.php */\n";

		// genera el archivo por table
		createFile(ROUTESFOLDER, $current_table.'.php', $addLine,false);
   	}

   	$data['methods'] = 'done'; 
   	$data['mapping'] = 'done';
   	// Commons routes and associations routes
   	$data['commons'] = createFile('routes', '_commons.php',"<?php\n/* Commons routes and associations routes */",true);
   	// Create routes configuration file
   	$data['routing'] = createRoutes();
   	// replace namespaces on every route file
   	$data['renaming'] = renameMethod();
   	$data['status'] = 'success';
   	$data['finished'] = GENERATED;
   	// done!

   	//replaceMethod(ROUTESFOLDER.'task.php');
   	echoResponse(200, $data);
});
