# Multipurpose API generator

This script generate API RESTful from database, it means the script read tables and columms and create method on static file, that will be added to index.php as routes

Requeriments

- PHP 5.x
- MySQL 5.x
- Slim Framework v2.x [Download it from source](http://www.slimframework.com)

Configurations steps

1. Clone this repository (You should know about it)
2. Rename configuration file, ex. config.php.example
3. Add your credentials (ex. localhost, user, pass, database)
4. Create a empty folder api/routes/
5. Add Slim to root 
6. Run into your browser http://localhost/api/start/api/installer (You can replace localhost by your ip/domain, and also include mysql port)

That's all folks!

### TODO

- Add security (Token)
- Migrate MySQL functions to PDO, mysqli_ or maybe Eloquent ORM
- Add GUI installer like step-by-step
- Remove installer route
- Upgrade to Slim 3.x (This must be the first thing I have to do)

September 2016
