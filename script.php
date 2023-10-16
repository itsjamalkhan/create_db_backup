<?php 

$host='DB_HOST';
        $user='DB_USERNAME';
        $pass='DB_PASSWORD';
        $name='DB_DATABASE';
        $tables = '*';
        try {
            $con = mysqli_connect($host,$user,$pass,$name);
        } catch (Exception $e) {
            echo $e;exit();
        }

        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: ".mysqli_connect_error();
            return 0;
        }

        if ($tables == '*') {
             $tables = array();
             $result = mysqli_query($con, 'SHOW TABLES');
            while ($row = mysqli_fetch_row($result)) {
                $tables[] = $row[0];
            }
        } else {
            $tables = is_array($tables) ? $tables : explode(',',$tables);
        }

        $return = '';
        foreach($tables as $table) {
            $result = mysqli_query($con, 'SELECT * FROM '.$table);
            $num_fields = mysqli_num_fields($result);


            $row2 = mysqli_fetch_row(mysqli_query($con, 'SHOW CREATE TABLE '.$table));
            $return.= "\n\n".str_replace("CREATE TABLE", "CREATE TABLE IF NOT EXISTS", $row2[1]).";\n\n";

            for ($i = 0; $i < $num_fields; $i++) {
                while ($row = mysqli_fetch_row($result)) {
                    $return.= 'INSERT INTO '.$table.' VALUES(';
                    for($j=0; $j < $num_fields; $j++) {
                        $row[$j] = addslashes($row[$j]);
                        $row[$j] = preg_replace("/\n/","\\n",$row[$j]);
                        if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                        if ($j < ($num_fields-1)) { $return.= ','; }
                    }
                    $return.= ");\n";
                }
            }

            $return.="\n\n\n";
        }
        try {
            $backup_name = date('Y-m-d-His').'.sql';

            $handle = fopen(public_path("uploads/db-backups").'/'.$backup_name,'w+');
            fwrite($handle,$return);
            fclose($handle);

            //echo "Back Name:".$backup_name;
        } catch (Exception $e) {

            echo "Exception: ".$e->getMessage();
        }


?>