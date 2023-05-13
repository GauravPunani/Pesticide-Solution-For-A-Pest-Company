
<?php if(isset($_GET['technician_id']) && $_GET['technician_id']!=""): ?>
    <div class="chemical-reports-form">
	<h1>New York Chemical Report</h1>
    <button type="button" class="btn btn-primary openmodal" data-model-id="annualreport"><span><i class="fa fa-download"></i></span> Download Annual Report</button>

    <div id="annualreport" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Download Annual Report</h4>
                </div>
                <div class="modal-body">
                    <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" class="">
                        <input type="hidden" name="action" value="generate_newyork_report">
                        <input type="hidden" name="technician_id" value="<?= $_GET['technician_id']; ?>">

                        <?php    
                            global $wpdb;
                            $result = $wpdb->get_results("select reporting_year from wp_checmical_report_newyork RIGHT JOIN wp_chemicals_newyork ON  wp_checmical_report_newyork.id=wp_chemicals_newyork.report_id where technician_id=".$_GET['technician_id']." GROUP BY(reporting_year)");
                        ?>
                        <div class="form-group">
                            <label for="reporting year">Reporting Year</label>
                            <select class="form-control" name="reporting_year" id="">
                                <?php if( is_array($result) && !empty($result)): ?>
                                    <?php foreach($result as $key=>$val): ?>
                                        <option value="<?= $val->reporting_year; ?>"><?= $val->reporting_year; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="from date">From Date</label>
                            <input type="date" name="from_date" class="form-control" id="" required>
                        </div>
                        <div class="form-group">
                            <label for="to date">To Date</label>
                            <input type="date" name="to_date" class="form-control" id="" required>
                        </div>
                        <button class="btn btn-primary"><span><i class="fa fa-download"></i></span> Download</button>
                        
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php
            
            if (isset($_GET['pageno'])) {
                $pageno = $_GET['pageno'];
            } else {
                $pageno = 1;
            }
            
            $no_of_records_per_page =10;
            $offset = ($pageno-1) * $no_of_records_per_page; 

            $whereSearch=[];

            if(isset($_GET['search'])){

                $whereSearch=get_table_coloumn('wp_checmical_report_newyork');
                $temp2=get_table_coloumn('wp_chemicals_newyork');

                $whereSearch=array_merge($whereSearch,$temp2); // merge table columns
                $whereSearch=create_search_query_string($whereSearch,$_GET['search'],'and');  //genereate where query string
            }
            else{
                $whereSearch="";
            }
            
            // echo $whereSearch;wp_die();

            $total_pages_sql = "select COUNT(*) from wp_checmical_report_newyork RIGHT JOIN wp_chemicals_newyork ON  wp_checmical_report_newyork.id=wp_chemicals_newyork.report_id where technician_id=".$_GET['technician_id']." $whereSearch";
            
            $total_rows= $wpdb->get_var($total_pages_sql);
            
            $total_pages = ceil($total_rows / $no_of_records_per_page);


            $result = $wpdb->get_results("select wp_checmical_report_newyork.reporting_year, wp_chemicals_newyork.* from wp_checmical_report_newyork RIGHT JOIN wp_chemicals_newyork ON  wp_checmical_report_newyork.id=wp_chemicals_newyork.report_id where technician_id=".$_GET['technician_id']." $whereSearch  LIMIT $offset, $no_of_records_per_page");
            
    ?>
    
    <form action="<?= $_SERVER['REQUEST_URI']; ?>" class="search_form">
        <input type="hidden" name="page"  value="chemical-reports-newyork">
        <input type="hidden" name="technician_id"  value="<?= $_GET['technician_id']; ?>">
        <input name="search" placeholder="Enter Name,email etc.." type="text"><span><button class="btn btn-primary btn_search"><span><i class="fa fa-search"></i></span> Search</button></span>
    </form>

	</div>

    <?php if(isset($_GET['search'])): ?>
        <p class="alert alert-success"><?= $total_rows ?> Records Found for the search : <b><?= $_GET['search']; ?></b> <a class="btn btn-info" href="<?= admin_url('admin.php?page=chemical-reports-newyork&technician_id='.$_GET['technician_id']); ?>"><span><i class="fa fa-database"></i></span> Show All Records</a> </p>
    <?php endif; ?>
<div class="table-responsive">
    <table width="100%" class="gamex_table table-striped table-hover">
 
        <tr>
            <th>Reporting Year</th>
            <th>Product Name</th>
            <th>Product Quantity</th>
            <th>Unit of measurement</th>
            <th>Date</th>
            <th>Country Code</th>
            <th>Address</th>
            <th>City</th>
            <th>Zip</th>
            <th>Dossage Rate</th>
            <th>Method Of Application</th>
            <th>Target Organisms</th>
            <th>Place Of Application</th>
        </tr>

        <tr>
            <?php if( is_array($result) && !empty($result)): ?>

            <?php foreach($result as $key=>$val): ?>

                <tr>
                    <td><?= $val->reporting_year ?></td>
                    <td><?= $val->product_name ?></td>
                    <td><?= $val->product_quantity  ?></td>
                    <td><?= $val->unit_of_measurement ?></td>
                    <td><?= $val->date ?></td>
                    <td><?= $val->country_code ?></td>
                    <?php $address=json_decode($val->address_of_application);?>
                    <td><?= $address[0]; ?></td>
                    <td><?= $address[1]; ?></td>
                    <td><?= $address[2]; ?></td>
                    <td><?= $val->dosage_rate ?></td>
                    <td><?= $val->method_of_application ?></td>
                    <td><?= $val->target_organisms ?></td>
                    <td><?= $val->place_of_application ?></td>
                </tr>


            <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No Record found</td>
                </tr>

            <?php endif; ?>
        </tr>

    </table>
	</div>
<?php render_pagination($pageno,$total_pages); ?>

<?php else: ?>

        <div class="table-responsive">
           <table width="100%" class="gamex_table table-striped table-hover">

        <tr>
            <th>Certificate No</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Location</th>
            <th>Business Reg #</th>
            <th>Action</th>
        </tr>

        <tr>
        <?php
        global $wpdb;
        
        if (isset($_GET['pageno'])) {
            $pageno = $_GET['pageno'];
        } else {
            $pageno = 1;
        }
        
        $no_of_records_per_page = 10;
        $offset = ($pageno-1) * $no_of_records_per_page; 
        
        $total_pages_sql = "select COUNT(*) from wp_technician_details";
        
        $total_rows= $wpdb->get_var($total_pages_sql);
        
        $total_pages = ceil($total_rows / $no_of_records_per_page);




        $result = $wpdb->get_results("select * from wp_technician_details LIMIT $offset, $no_of_records_per_page");


        ?>

        <?php if( is_array($result) && !empty($result)): ?>

        <?php foreach($result as $key=>$val): ?>
            <?php if (!current_user_can( 'other_than_upstate' ) ) {
                if($val->state!="upstate"){
                    continue;
                }
            } ?>


            <tr>
                <td><?= $val->certification_id ?></td>
                <td><?= $val->first_name ?></td>
                <td><?= $val->last_name  ?></td>
                <td><?= $val->state ?></td>
                <td><?= $val->business_reg ?></td>
                <td><a class="btn btn-primary" href="<?= get_admin_url(); ?>/admin.php?page=chemical-reports-newyork&technician_id=<?= $val->id; ?>"><span><i class="fa fa-eye"></i></span> View Report</a></td>
            </tr>


        <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No Record found</td>
            </tr>

        <?php endif; ?>
        </tr>


</table>
</div>

<?php render_pagination($pageno,$total_pages); ?>

<?php endif; ?>

