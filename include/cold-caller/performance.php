<?php

global $wpdb;
$cold_callers=$wpdb->get_results("select * from {$wpdb->prefix}cold_callers");

?>

<div class="row">
    <div class="col-md-5">
        <div class="card card-border">
            <div class="card-body">
                <form id="performance_form" action="">
                    <input type="hidden" name="action" value="calculate_cold_caller_performance">
                    <input type="hidden" name="cold_caller" value="<?php echo $_SESSION['cold_caller_id']?>">
                    
                    <div class="form-group">
                        <label for="">Select Date Type</label>
                        <br>
                        <label for="" class="radio-inline"><input type="radio" value="date_range" name="date_type" checked>Date Range</label><br>
                        <label for="" class="radio-inline"><input type="radio" value="year_month" name="date_type" >Year/Month</label>
                    </div>

                    <div class="date_range">
                        <div class="form-group">
                            <label for="">From Date</label>
                            <input type="date" class="form-control" name="from_date">
                        </div>
                        <div class="form-group">
                            <label for="">To Date</label>
                            <input type="date" class="form-control" name="to_date">
                        </div>
                    </div>

                    <div class="year_month hidden">
                        <div class="form-group">
                            <label for="">Select Year</label>
                            <select name="year" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php for($i="2021";$i<=date('Y');$i++): ?>
                                    <option value="<?= $i; ?>"><?= $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Select Month</label>
                            <select name="month" class="form-group select2-field">
                                <option value="">Select</option>
                                <?php for ($m=1; $m<=12; $m++): ?>
                                    <?php $month = date('F', mktime(0,0,0,$m, 1, date('Y')));?>
                                    <option value="<?= sprintf('%02d', $m) ?>"><?= $month; ?></option>
                                <?php endfor; ?>        
                            </select>
                        </div>
                    </div>

                    <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> Submit</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card card-border full_width table-responsive">
            <div class="card-body">
                <h4 class="page-header">Cold Caller Performance</h4>
                <div class="perfomance_html"></div>
            </div>
        </div>
    </div>
</div>