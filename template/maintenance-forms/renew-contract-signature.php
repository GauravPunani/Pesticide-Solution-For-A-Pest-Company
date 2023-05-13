<!-----contract date --->
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Start date</label>
            <input name="contract_start_date" value="<?= date('Y-m-d'); ?>" type="date" class="form-control" />
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">End date</label>
            <input name="contract_end_date" value="<?= date('Y-m-d', strtotime(date("Y-m-d", time()) . " + 365 day")); ?>" type="date" class="form-control" />
        </div>
    </div>
</div>

<!----------c-name+sign---->
<div class="form-group last-dsc notStaffField">
    <div class="row">
        <div class="col-75 col-md-offset-2 c-name">
            <div id="signArea">
                <label for="sign">Client Signature</label>
                <div class="sig sigWrapper" style="height:auto;">
                    <div class="typed"></div>
                    <canvas class="sign-pad" id="sign-pad" width="300" height="100"></canvas>
                    <a class="clear-canvas" onclick="clearCanvas()">Clear Signature</a>
                </div>
            </div>
        </div>
    </div>
</div>