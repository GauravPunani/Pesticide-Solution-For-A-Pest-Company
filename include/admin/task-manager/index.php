<?php

(new Navigation)->task_manager(@$_GET['tab']);

if(isset($_GET['tab']) && !empty($_GET['tab'])){
    switch ($_GET['tab']) {
        case 'tasks':
            get_template_part('/include/admin/task-manager/tasks');
        break;
        case 'create-task':
            get_template_part('/include/admin/task-manager/create-task');
        break;
        
        default:
            get_template_part('/include/admin/task-manager/tasks');
        break;
    }
}
else{
    get_template_part('/include/admin/task-manager/tasks');
}