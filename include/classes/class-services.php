<?php

class GamServices extends GamFunctions{
    
    public function getFindinds(){
        global $wpdb;

        return $wpdb->get_results("
            select *
            from {$wpdb->prefix}findings
        ");
    }

    public function getTypeOfServices(){
        global $wpdb;

        return $wpdb->get_results("
            select *
            from {$wpdb->prefix}type_of_service
        ");
    }

    public function getServiceDescriptions(){
        global $wpdb;

        return $wpdb->get_results("
            select *
            from {$wpdb->prefix}service_description
        ");
    }

    public function getAreaOfService(){
        global $wpdb;

        return $wpdb->get_results("
            select *
            from {$wpdb->prefix}area_of_service
        ");
    }

}

new GamServices();