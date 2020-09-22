<?php

if($argc > 1) {
    parse_str(implode('&', array_slice($argv, 1)), $params_array);

    $csvfile = $params_array['--file'];
    $databasetable = $params_array['[--create_table'];
    $databasehost = $params_array['-h'];
    $databaseusername = $params_array['-u'];
    $databasepassword = $params_array['-p'];
    $help = $params_array['--help'];
    $dryrun = $params_array['--dryrun'];

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






