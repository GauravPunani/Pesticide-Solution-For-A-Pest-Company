<?php

/**
 * This trait define methods for input validation
 */
trait GamValidation
{
    public function requiredValidation(array $fields, array $input){
        foreach($fields as $field){
            if(empty($input[$field])) return [false, $field." is required"];
        }

        return [true, null];
    }

    public function isNumericValidation(array $fields, array $input){
        foreach($fields as $field){
            if(!array_key_exists($field, $input) || !is_numeric($input[$field])) return [false, $field." is required"];
        }

        return [true, null];
    }

    public function arrayValidation(array $fields, array $input){
        foreach($fields as $field){
            if(
                !array_key_exists($field, $input) ||
                !is_array($input[$field]) ||
                count($input[$field]) <= 0
            ) return [false, $field." is required and should contain atleast one value"];
        }

        return [true, null];
    }

    public function singleFileValidation(array $input, string $name){
        if(
            empty($input[$name]) ||
            empty($input[$name]['tmp_name'])
        ) return [false, 'File not found'];

        switch($input[$name]['error']){
            case 1:
                return [false, 'Uploaded filsize exceed the maximum allowed upload size'];
            break;
            case 2:
                return [false, 'uploaded filsize exceed the maximum allowed upload size'];
            break;
            case 3:
                return [false, 'File was only partialy uploaded'];
            break;
            case 4:
                return [false, 'No file was uploaded'];
            break;
            case 6:
                return [false, 'Missing temporary folder'];
            break;
            case 7:
                return [false, 'Failed to write to disk'];
            break;
            case 8:
                return [false, 'A php extension stopped the file upload'];
            break;
        }

        return [true, null];
    }

}
