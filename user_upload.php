<?php

// Check if email already exit
function is_exist($con, $table, $_email){
    $email_exist= '';
    $_STATEMENT = $con->prepare("SELECT email FROM $table WHERE email = ?;");

    $_STATEMENT->bind_param( 's' , $_email );

    $_STATEMENT->execute();
    $_STATEMENT->store_result();

    $_STATEMENT->bind_result( $email_exist);
    $result = $_STATEMENT->fetch();

    $_STATEMENT->free_result();
    $_STATEMENT->close();

    return (strlen($email_exist) > 0) ? true: false;
}

try {

    if($argc > 1) {



        parse_str(implode('&', array_slice($argv, 1)), $params_array);

        if (array_key_exists('--help', $params_array)) {
            printf("List of directives :
            --file [csv file name] – this is the name of the CSV to be parsed\n
            --create_table – this will cause the MySQL users table to be built (and no further action will be taken)\n
            --dry_run – this will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered\n
            -u – MySQL username\n
            -p – MySQL password\n
            -h – MySQL host\n
            -db – MySQL DB (optional if not provided a new db with name 'demo_db' will be created)\n
            --help – which will output the list of directives with details.\n");

            exit;
        }


        if (array_key_exists('--dry_run', $params_array)) {
            $dryrun = true;
        } else {
            $dryrun = false;
        }

        if (array_key_exists('--create_table', $params_array)) {
            $create_table = true;
        } else {
            $create_table = false;
        }

        if(!empty($params_array['--file'])) {
            $file_parts = pathinfo($params_array['--file']);

            if(strtolower($file_parts['extension']) != 'csv'){
                printf("Wrong format, Provide CSV file only\n");
                exit;

            }

            $csvfile = $params_array['--file'];
        } else {
            printf("No CSV file provided\n");
            exit;
        }

        if(!empty($params_array['-h'])) {
            $databasehost = $params_array['-h'];
        } else {
            printf("No MySQL Host provided\n");
            exit;
        }

        if(!empty($params_array['-u'])) {
            $username = $params_array['-u'];
        } else {
            printf("No MySQL username provided\n");
            exit;
        }

        if(!empty($params_array['-p'])) {
            $password = $params_array['-p'];
        } else {
            printf("No MySQL password provided\n");
            exit;
        }

        if(!empty($params_array['-db'])) {
            $dbName = $params_array['-db'];
        } else {
            $dbName = 'demo_db';
        }


        $databasetable = 'users';
        $_inserted_data = array();
        $_not_inserted_data = array();
        $_error_description = '';
        $lines =0;

        // Start processing file

        if (!file_exists($csvfile)) {
            printf("File not found. Make sure you specified the correct path.\n");
            exit;
        }


        try {
            // Create connection

            // Handle error if connection fails
            try {
                // Create connection
                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

                $con = new mysqli($databasehost, $username, $password);
            } catch (Exception $e) {
                // Handl exception here and stop the execution if db connection fails
                printf('Connection error:'.$e->getMessage().'\n');
                exit;
            }

            // DRY RUN Option to avoid DB query
            if (!$dryrun) {
                // If database is not exist create one
                if (!mysqli_select_db($con, $dbName)) {
                    $sql = "CREATE DATABASE " . $dbName;
                    if ($con->query($sql) === TRUE) {
                        echo "Database created successfully";
                    } else {
                        echo "Error creating database: " . $con->error;
                    }
                }

                // Use that new created db
                $use_sql = "USE $dbName";
                if ($con->query($use_sql) === TRUE) {
                    printf("USING $dbName\n");
                } else {
                    printf("Error using database: " . $con->error . "\n");
                }

                // check if table users already exist
                $table_exist = false;
                if ($result = $con->query("SHOW TABLES LIKE '" . $databasetable . "'")) {
                    if ($result->num_rows == 1) {
                        $table_exist = true;
                    }
                }

                if ($table_exist) {
                    // Do nothing if table already exist

                } elseif ($create_table) {
                    // Create Table
                    $create_table_sql = "CREATE TABLE IF NOT EXISTS $databasetable (
                    name VARCHAR(250) NOT NULL,
                    surname VARCHAR(250) NOT NULL,
                    email VARCHAR(100) NOT NULL , 
                    UNIQUE email_unique (email)
                    )";
                    if ($con->query($create_table_sql) === TRUE) {
                        printf("Database table $databasetable created if not exist \n");
                    } else {
                        printf("Error creating table: " . $con->error . "\n");
                    }
                } else {
                    printf("No users table exist, provide --create_table \n");
                    exit;
                }
            } else {
                $use_sql = "USE $dbName";
                if ($con->query($use_sql) === TRUE) {
                    printf("USING $dbName\n");
                } else {
                    printf("Error using database: " . $con->error . "\n");
                }
            }

            // open the file to read
            $file = fopen($csvfile, "r");

            if (!$file) {
                printf("Error opening data file. Please check \n");
                exit;
            }

            // Check the file size
            $size = filesize($csvfile);

            if (!$size) {
                printf("CSV File is empty.\n");
                exit;
            }


            // Skip the first line
            fgetcsv($file);
            // Parse data from CSV file line by line
            while (($line = fgetcsv($file)) !== FALSE) {
                // Get row data

                $name = ucfirst(strtolower((trim($line[0]))));
                $surname = ucfirst(strtolower(trim($line[1])));
                $email = strtolower(trim($line[2]));

                $_allow_insert = true;

                /*$name = str_replace("'", "''", "$name");
                $surname = str_replace("'", "''", "$surname");
                $email = str_replace("'", "''", $email);*/

                if ($email != null) {

                    // check if e-mail address is well-formed
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $_error_description = "$email Invalid email format ";
                        $_allow_insert = false;
                        // check if e-mail address already exist
                    } elseif (is_exist($con, $databasetable, $email)) {
                        $_error_description = "$email This email already exist";
                        $_allow_insert = false;
                    } else {
                        $_allow_insert = true;
                    }

                }


                if ($_allow_insert) {
                    if (!$dryrun) {
                        $stmt = $con->prepare("INSERT INTO $databasetable VALUES (?, ?, ?)");

                        /* bind parameters for markers */
                        $stmt->bind_param("sss", $name, $surname, $email);

                        /* execute query */
                        $stmt->execute();
                        $stmt->close();

                    }
                    $_inserted_data[] = array($name, $surname, $email);

                } else {
                    $_not_inserted_data[] = array( 'ERROR -'.$_error_description.':', $name, $surname, $email);
                }
                $lines++;
            }

            $con->close();


            printf("\n RESULTS: ");
            printf("\n Found a total of $lines records in this csv file.");
            printf("\n Total " . count($_inserted_data) . " records inserted ");
            printf("\n Total " . count($_not_inserted_data) . " records ignored due to validation issue \n ");

            if (is_array($_not_inserted_data) && count($_not_inserted_data) > 0) {
                printf("\n Following data Ignored - \n");

                foreach ($_not_inserted_data as $_key =>  $_data) {
                    print_r( $_data );
                }
            }


        } catch(Exception $e) {
            printf( "ERROR: ".$e->getMessage());
        }



    } else {
        printf("No parameter provided. Please provide below parametrs : \n");
        printf("--file [csv file name] – this is the name of the CSV to be parsed\n
     --create_table – this will cause the MySQL users table to be built (and no further action will be taken)\n
     --dry_run – this will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered\n
     -u – MySQL username\n
     -p – MySQL password\n
     -h – MySQL host\n
     --help – which will output the above list of directives with details.\n");
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
catch (InvalidArgumentException $e) {
    echo $e->getMessage();
}






