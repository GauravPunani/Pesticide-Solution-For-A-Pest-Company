<?php

class DB {

    private static $table_name;
    private static $id_name = 'id';
    private $query;
    private $where = [];
    private $first = false;
    private $columns = [];
    private $id;

    private static $_instance = null;

    public static function table(string $table){
        global $wpdb;
        self::$table_name = $wpdb->prefix.$table; 
        return new DB;
    }

    public function create(array $data){
        global $wpdb;

        $response = $wpdb->insert(self::$table_name, $data);
        return $response ? [true, null] : [false, $wpdb->last_error];
    }

    public function delete(int $id){
        global $wpdb;
        $response = $wpdb->delete(self::$table_name, ['id' => $id]);
        return $response ? [true, null] : [false, $wpdb->last_error];
    }

    public function update(int $id, array $data){
        global $wpdb;
        $response = $wpdb->update(self::$table_name, $data, ['id' => $id]);
        return $response === false ? [false, $wpdb->last_error] : [true, null];
    }

    public function columns(array $columns){
        $this->columns = $columns;
        return $this;
    }

    public function all(){
        global $wpdb;

        $columns = count($this->columns) > 0 ? implode(',', $this->columns) : '*';

        return $wpdb->get_results("select $columns from ".self::$table_name);
    }

    public function getById(int $id, array $columns = []){
        global $wpdb;

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        $response = $wpdb->get_row("
            select $columns
            from ".self::$table_name."
            where id = '$id'
        ");

        if(!$response) return [null, $wpdb->last_error];

        return [$response, null];
    }


    public function where($column, $value = ''){

        if(is_callable($column)){
            $this->where[] = call_user_func($column);
        }
        else{
            $this->where[] = "$column = '$value'";
        }

        return $this;
    }

    public function first(){
        $this->first = true;
        return $this;
    }

    public function get(){
        global $wpdb;

        $columns = count($this->columns) > 0 ? implode(',', $this->columns) : '*';

        if(!empty($this->id))
            $where = "where id = '$this->id'";
        else
            $where = count($this->where) > 0 ? "where ".implode(" and ", $this->where) : '';

        $method = $this->first ? 'get_row' : 'get_results';

        return $wpdb->$method("
            select $columns
            from ".self::$table_name."
            $where
        ");
    }

    public function byId(int $id){
        $this->first = true;
        $this->id = $id;
        return $this;
    }
}