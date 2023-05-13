<?php


function get_table_coloumn($table_name){
    
    global $wpdb;

    $sql ="SHOW COLUMNS FROM $table_name";
    $coloumns= $wpdb->get_results($sql);

    foreach ($coloumns as $key => $value) {
        if($value->Field=="id"){
            unset($coloumns[$key]);
        }
    }

    // echo "<pre>";print_r($coloumns);wp_die();

    return $coloumns;

}

function create_search_query_string($coloumns,$search_string,$type="where",$table_name=''){

    $whereString=[];
    foreach ($coloumns as $key => $value) {
        if(!empty($table_name)){
            $whereString[]="$table_name.$value->Field LIKE '%$search_string%' ";
        }else{
            $whereString[]="$value->Field LIKE '%$search_string%' ";
        }
    }

    $whereString=implode(' OR ',$whereString);

    if($type=="and")
        $whereSearch="and ($whereString)";
    else
        $whereSearch="where ($whereString)";

    return $whereSearch;

}



function render_pagination($pageno,$total_pages){
    $page_url=$_SERVER['PHP_SELF'];
    $url_parameters=$_GET;

    if(isset($url_parameters['pageno']))
        unset($url_parameters['pageno']);
    
    $page_url=$page_url."?".http_build_query($url_parameters);
    ?>
	<div class="pagination_wrapper">
    <ul class="pagination">
        <li class="<?= $total_pages=="1" ? 'disabled': ''; ?>"><a href=<?= $page_url."&pageno=1"; ?>>First</a></li>
        <li class="<?php if($pageno <= 1){ echo 'disabled'; } ?>">
            <a href="<?php if($pageno <= 1){ echo '#'; } else { echo $page_url."&pageno=".($pageno - 1); } ?>">Prev</a>
        </li>
        <li class="<?php if($pageno >= $total_pages){ echo 'disabled'; } ?>">
            <a href="<?php if($pageno >= $total_pages){ echo '#'; } else { echo $page_url."&pageno=".($pageno + 1); } ?>">Next</a>
        </li>
        <li class="<?= $total_pages=="1" ? 'disabled': ''; ?>"><a href="<?= $page_url."&pageno=".$total_pages; ?>">Last</a></li>
    </ul>
	</div>
    <?php
}

if(!function_exists('generate_search_string')){
    function generate_search_string($search_array=[]){
        $whereSearch="";
        if(count((array)$search_array)>0){
            $i=0;
            foreach ($search_array as $key => $value) {
                if($i==0){
                    $whereSearch.="where $key='$value' ";
                }else{
                    $whereSearch.=" and $key='$value' ";
                }
                $i++;
            }
        }
        return (string)$whereSearch;
    }
}


if(!function_exists('billing_tabs')){
    function billing_tabs($page,$active_tab=''){
        $total_no_email=(new Autobilling)->count_no_email_unpaid_invoices();
        $total_error_mini_statement=(new Autobilling)->count_mini_statement_log();
        ?>
            <h2 class="nav-tab-wrapper">
                <a href="?page=<?= $page; ?>&tab=billing" class="nav-tab <?= $active_tab=="billing" || $active_tab=="" ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-file"></i></span> Billing</a>
                <a href="?page=<?= $page; ?>&tab=no_email" class="nav-tab <?= $active_tab=="no_email"  ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-envelope"></i></span> No Email <span class="badge"><?= $total_no_email; ?></span></a>
                <a href="?page=<?= $page; ?>&tab=mini_statement_log" class="nav-tab <?= $active_tab=="mini_statement_log"  ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-tasks"></i></span> Mini Statement Log</a>
                <a href="?page=<?= $page; ?>&tab=error_log" class="nav-tab <?= $active_tab=="error_log"  ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-exclamation-triangle"></i></span> Error Log <span class="badge"><?= $total_error_mini_statement; ?></span></a>
                <a href="?page=<?= $page; ?>&tab=billing_no_email" class="nav-tab <?= $active_tab=="billing_no_email"  ? 'nav-tab-active' : ''; ?> "><span><i class="fa fa-file"></i></span> Billing - No Email</a>
            </h2>

        <?php
    }
}