<div class="cc_details_box">
    <!-----Credit card  --->
    <div class="form-group">
        <div class="row">
            <div class="col-sm-4">
                <label for="ccnum">Credit card number </label>
                <input maxlength="16" type="text" class="form-control cc_field numberonly"  id="ccnum" name="card_details[creditcardnumber]" placeholder="1111-2222-3333-4444">
            </div>
            <div class="col-sm-2">
                <label for="month">Month</label>
                <select name="card_details[cc_month]" id="cc_month" class="form-control cc_field">
                <option value="01">1</option>
                <option value="02">2</option>
                <option value="03">3</option>
                <option value="04">4</option>
                <option value="05">5</option>
                <option value="06">6</option>
                <option value="07">7</option>
                <option value="08">8</option>
                <option value="09">9</option>
                <option value="10">10</option>
                <option value="11">11</option>
                <option value="12">12</option>
                </select>
            </div>
            <div class="col-sm-3">
                <label for="month">Year</label>
                <select name="card_details[cc_year]" class="form-control cc_field" id="cc_year">
                <option value="20"> 2020</option>
                <option value="21"> 2021</option>
                <option value="22"> 2022</option>
                <option value="23"> 2023</option>
                <option value="24"> 2024</option>
                <option value="25"> 2025</option>
                <option value="26"> 2026</option>
                <option value="27"> 2027</option>
                <option value="28"> 2028</option>
                <option value="29"> 2029</option>
                <option value="30"> 2030</option>
                <option value="31"> 2031</option>
                <option value="32"> 2032</option>
                <option value="33"> 2033</option>
                <option value="34"> 2034</option>
                <option value="35"> 2035</option>
                </select>
            </div>
            <div class="col-sm-3">
                <label for="ccnum">Security code</label>
                <input maxlength="4" type="text" class="form-control cc_field numberonly"  id="secuity_code" name="card_details[cccode]" placeholder="00000">
            </div>
        </div>
    </div>

    <!----------c-name+sign---->
    <div class="form-group last-dsc notStaffField">
        <div class="row">
            <div class="col-75 col-md-offset-2 c-name">
                <div id="signArea" >
                <label for="sign">Client Signature</label>
                <div class="sig sigWrapper" style="height:auto;">
                    <div class="typed"></div>
                    <canvas class="sign-pad" id="sign-pad" width="300" height="100"></canvas>
                    <a class="clear-canvas"  onclick="clearCanvas()">Clear Signature</a>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>


