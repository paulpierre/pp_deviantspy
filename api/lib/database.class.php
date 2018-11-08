<?php
class Database {
	public function db_update($table_name,$db_columns,$db_conditions,$isOr=false)
	{

        $values = ''; $column_count = 0;$i = 0; $value = null; $db_column = '';

        $column_count = count($db_columns);
        $db_conn = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME,DATABASE_PORT);
        //lets iterate through all the columns and values we want to update
        foreach($db_columns as $columnKey=>$columnVal)
        {

            $db_value = (is_numeric($columnVal))? $columnVal : '\''.$db_conn->real_escape_string($columnVal).'\'';
            $db_column = $columnKey;
            $db_separator = ($i<=$column_count && $i > 0)?',':'';
            $values .= $db_separator . $db_column . '=' . $db_value;
            $i++;
        }


        $i=0;
        $db_condition = $db_conditions;                             //overwrite our final output in case its a flat string
        if(is_array($db_condition))                                  //if its not a flat string, lets iterate and build a string
        {
            $db_condition = '';                                     //lets reset the final output
            $condition_count = count($db_conditions);
            foreach($db_conditions as $conditionKey=>$conditionVal)
            {
                if($condition_count>1 && $i>0){ $db_separator = ($isOr ? ' OR ' : ' AND ');}         //by default we assume these will be an inclusive chain so we set our separator to AND
                else { $db_separator = '';}
                $conditionValStr = (is_numeric($conditionVal))?$conditionVal:'\'' . $conditionVal . '\'';
                $db_condition .= $db_separator . $conditionKey . '=' . $conditionValStr; //add each column
                $i++;
            }
            $i=0;
        }



        $q = ('UPDATE ' . $table_name .' SET '. $values . ' WHERE ' . $db_condition);


        if (mysqli_connect_errno()) {       ////throw an error if there is a connection problem
            error_log('DB: ' . $q . ' ERROR INSERTING. BAD QUERY.'. PHP_EOL);

            return false; //mysqli_connect_error());
        } else {   //transaction successful
            $result = $db_conn->query($q); //or $this->db_error($result);
            $db_conn->close();
            ds_error('DB: ' . $q . PHP_EOL . ' result:' . $result);

            return $result;
        }
	}


    public function db_create($table_name,$db_columns)
    {
        //print_r($db_columns);
        $values = ''; $column_count = count($db_columns);$i=0; $value = null; $db_column = '';$columns='';

        //lets iterate through all the columns and values we want to update
        $db_conn = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME,DATABASE_PORT);
        //print_r($db_columns);exit();
        foreach($db_columns as $columnKey=>$columnVal)
        {
            $db_value = $db_conn->real_escape_string($columnVal);
            $db_column = $columnKey;
            $separator = ($i<=$column_count && $i > 0)?',':'';
            $columns .= $separator . $db_column;
            $values .= $separator . (is_numeric($db_value) ? $db_value: '\''.$db_value.'\'');
            $i++;

        }
        $q = ('INSERT INTO ' . $table_name . '('  . $columns . ')' .
            ' VALUES( '.$values .')');
        $db_conn = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME,DATABASE_PORT);
        if (mysqli_connect_errno()) {       ////throw an error if there is a connection problem
            return false; //mysqli_connect_error());
        } else {   //transaction successful
            //print $q;
            $result = $db_conn->query($q); //or $this->db_error($result);
            $id = $db_conn->insert_id;
            $db_conn->close();
            ds_error('DB: ' . $q . PHP_EOL . ' result:' . $id);

            return $id;//$result;
        }
    }


    public function db_retrieve($table_name,$db_columns,$db_conditions='',$db_extra=null,$isOr=false)
    {

        $i = 0; $db_column = '';$db_condition = '';$condition_count = 0;
        $db_condition = $db_conditions;                             //overwrite our final output in case its a flat string
        if(is_array($db_condition))                                  //if its not a flat string, lets iterate and build a string
        {
            $db_condition = '';                                     //lets reset the final output
            $condition_count = count($db_conditions);
            foreach($db_conditions as $conditionKey=>$conditionVal)
            {
                if($condition_count>1 && $i>0){ $db_separator = ($isOr ? ' OR ' : ' AND ');}         //by default we assume these will be an inclusive chain so we set our separator to AND
                else { $db_separator = '';}
                $conditionValStr = (is_numeric($conditionVal))?$conditionVal:'\'' . $conditionVal . '\'';
                $db_condition .= $db_separator . $conditionKey . '=' . $conditionValStr; //add each column
                $i++;
            }
            $i=0;
        }

        $q = ('SELECT ' . implode(', ',$db_columns) . ' FROM ' . $table_name .
            (empty($db_condition)?'':' WHERE '.$db_condition)) .
            (empty($db_extra)?'':$db_extra);


        $cache_instance = \phpFastCache\CacheManager::Files();
        $key = md5($q);
        $data = $cache_instance->get($key);
        ds_error('Checking for key in cache: ' . $key);
        if(!is_null($data))
        {
            ds_error('Cache found: ' . $key);
            unset($cache_instance);
            return $data;
        }


        $db_conn = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME,DATABASE_PORT);
        if (mysqli_connect_errno()) {
            return false; //mysqli_connect_error());
        } else {   //transaction successful
            //print $q;
            $result = $db_conn->query($q); //or $this->db_error($result);
            //if($this->db_error($result)) return false;
            $result_set = array();
            while ($row = mysqli_fetch_assoc($result)) {
               array_push($result_set,$row);
            }
            ds_error('DB: ' . $q . PHP_EOL .  print_r($result_set,true));

            if(!empty($result_set)) $cache_instance->set($key, $result_set, CACHE_DURATION);

            $db_conn->close();

            return $result_set;
        }
    }

    public function db_query($q,$db_database=DATABASE_NAME)
    {


        $cache_instance = \phpFastCache\CacheManager::Files();
        $key = md5($q);
        $rows = $cache_instance->get($key);
        $row_count = $cache_instance->get($key . 'row_count');
        ds_error('Checking for key in cache: ' . $key);
        if(!is_null($rows) && !is_null($row_count))
        {
            ds_error('Cache found: ' . $key);
            unset($cache_instance);
            return $rows;
        }

        $db_conn = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, $db_database,DATABASE_PORT);
        if (mysqli_connect_errno()) {
            return false;
        } else {   //transaction successful

            if(strpos($q,'SQL_CALC_FOUND_ROWS'))
            {
                $q.=';SELECT FOUND_ROWS() as row_count;';
                $result = mysqli_multi_query($db_conn, $q);
                if ($result) {
                    do {

                        if (($result = mysqli_store_result($db_conn)) === false && mysqli_error($db_conn) != '') {
                            $db_conn->close();
                            return false;//echo "Query failed: " . mysqli_error($db_conn);
                        }

                        // grab the result of the next query
                        $result_set = array();
                        while ($row = mysqli_fetch_assoc($result)) {
                            array_push($result_set,$row);
                        }

                        //error_log(print_r($result_set));
                        if(isset($result_set[0]['row_count']))
                        {
                            $row_count = intval($result_set[0]['row_count']);
                            $cache_instance->set($key . 'row_count', $row_count, CACHE_DURATION);
                            ds_error('row_count in multiquery found: row_count=' . $row_count);

                        } else
                        {
                            if(!empty($result_set)) {
                                ds_error('main result set found: ' . print_r($result_set,true));

                                $cache_instance->set($key, $result_set, CACHE_DURATION);

                                $data = $result_set;
                            }
                        }

                        //error_log('Multi-query results:' . print_r($result_set,true));

                    } while (mysqli_more_results($db_conn) && mysqli_next_result($db_conn)); // while there are more results
                } else {
                    return false;//echo "First query failed..." . mysqli_error($db_conn);
                }
                $db_conn->close();
                return $data;


            } else {

                $result = $db_conn->query($q);

                $result_set = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    array_push($result_set,$row);
                }
            }

            //ds_error('DB: ' . $q . PHP_EOL .  print_r($result_set,true));
            //ds_error('DB: ' . $q );

            $db_conn->close();
            if(!empty($result_set)) {
                $cache_instance->set($key, $result_set, CACHE_DURATION);
                return $result_set;
            } else return false;


        }
    }

}



?>