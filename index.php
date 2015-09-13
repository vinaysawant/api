<?php

/**
 * Created by PhpStorm.
 * User: vinay
 * Date: 13/9/15
 * Time: 2:37 PM
 */
class Rest
{

    protected $endpoint;
    protected $method;
    protected $args;

    public function __construct($request)
    {

        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");

        $this->args = explode('/', rtrim($request, '/'));

        $this->endpoint = sizeof($this->args) > 0 ? $this->args[1] : "";
        $this->method = $_SERVER['REQUEST_METHOD'];

        if ((int)method_exists($this, $this->endpoint) > 0) {
            $this->{$this->endpoint}($this->args);
        } else {
            echo "Method not allowed";
        }

    }

    public function result_to_array($result, $fields)
    {
        $data = array();
        if (is_resource($result)) {
            while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {

                $arr = array();
                foreach ($fields as $key => $value){
                    $arr[$row[$key]] = $value;
                }

                $data[] = array_flip($arr);
            }

            mysql_free_result($result);

        }

        return $data;
    }

    public function query()
    {
        if ($this->method == 'POST') {
            $q = $this->args[2];

            $q = urldecode($q);

            $count = 0;
            $array = array();
            $records = array();
            $fields = array();

            $conn = mysql_connect("127.0.0.1", "root", "root");
            @mysql_select_db("mysql") or die("Please check back later. ");

            $result = mysql_query($q);
            if (is_resource($result)) {
                if (mysql_num_rows($result) > 0) {
                    $i = 0;
                    while ($i < mysql_num_fields($result)) {
                        $meta = mysql_fetch_field($result, $i);
                        if ($meta) {
                            $fields[$meta->name] = $i;
                        }
                        $i++;
                    }
                    mysql_free_result($result);
                }

            }

            $result = mysql_query($q);
            if (is_resource($result)) {
                if (mysql_num_rows($result) > 0) {
                    $count = mysql_num_rows($result);
                    $records = $this->result_to_array($result, $fields);
                } else {
                    echo "No records found for query";
                }
            } else {
                echo "Query is wrong";
            }


            array_push($array,
                array("count" => $count,
                    "records" => $records,
                    "fields" => $fields)
            );

            echo json_encode($array[0]);
        } else {
            echo "Accepts only post request";
        }
    }

}

$call = new Rest($_SERVER['REQUEST_URI']);