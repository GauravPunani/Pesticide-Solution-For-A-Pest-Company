<?php

(new Navigation)->dissatisfied_clients(@$_GET['tab']);

// if(isset($_GET['tab']) && !empty($_GET['tab'])){
//     get_template_part('include/admin/client/disatisfied-clients');
// }
// else{
//     get_template_part('include/admin/client/disatisfied-clients');
// }
get_template_part('include/admin/client/disatisfied-clients');