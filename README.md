# ImportCSV
PHP Script to import CSV into MySql DB

This script gives you the ability to quickly test PHP code locally. A local apache server is required.

Run user_upload.php with provided directive:

- --file [csv file name] – this is the name of the CSV to be parsed
- --create_table – this will cause the MySQL users table to be built (and no further
 action will be taken)
- --dry_run – this will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered
- -u – MySQL username
- -p – MySQL password
- -h – MySQL host
- -db – MySQL DB (optional if not provided a new db with name 'demo_db' will be created)
- --help – which will output the above list of directives with details.

NOTE: 
- Please note that I have added an extra command line directive called --db. Its optional. If not provided then it will create and use demo_db.
- The command line directive's values should be provided with "=". It will not accept space as directive values. 
For example -  
                php user_upload.php --file=users.csv -u=DBuser -p=DBpassowrd -h=DBhostname --create_table --dry_run  --db=DBname
                
                
                
                
                

 
