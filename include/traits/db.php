<?php

trait DB {

    public function table(string $table_name){
        global $wpdb;
        $this->table_name = $wpdb->prefix.$table_name;
        return $this;
    }

    public function create(array $data){
        global $wpdb;

        $response = $wpdb->insert($this->table_name, $data);
        return $response ? [true, null] : [false, $wpdb->last_error];
    }

    public function delete(int $id){
        global $wpdb;
        $response = $wpdb->delete($this->table_name, ['id' => $id]);
        return $response ? [true, null] : [false, $wpdb->last_error];
    }

    public function update(int $id, array $data){
        global $wpdb;
        $response = $wpdb->update($this->table_name, $data, ['id' => $id]);
        return $response === false ? [false, $wpdb->last_error] : [true, null];
    }

    public function all(){
        global $wpdb;
        return $wpdb->get_results($this->table_name);
    }

    public function getById(int $id, array $columns = []){
        global $wpdb;

        $columns = count($columns) > 0 ? implode(',', $columns) : '*';

        $response = $wpdb->get_row("
            select $columns
            from $this->table_name
            where id = '$id'
        ");

        if(!$response) return [null, $wpdb->last_error];

        return [$response, null];
    }
}