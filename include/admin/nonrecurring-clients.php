<?php
    $technicians = (new Technician_Details)->get_all_technicians();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3>Search for Nonrecurring Clients</h3>
                </div>
                <div class="card-body">
                    <form action="" id="nonrecurring_client_form">
                        <?php wp_nonce_field('nonrecurring_clients'); ?>
                        <input type="hidden" name="action" value="nonrecurring_clients">

                        <div class="form-group">
                            <label for="">From Date</label>
                            <input type="date" name="from_date"  class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="">To Date</label>
                            <input type="date" name="to_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="calendar_id">Select Technician</label>
                            <select name="calendar_id" id="calendar_id" class="form-control">
                                <?php foreach($technicians as $technician): ?>
                                    <?php 
                                    if (!current_user_can( 'other_than_upstate' ) ) {
                                        if($technician->state!="upstate"){
                                            continue;
                                        }
                                    }
                                ?>
                                    <option value="<?= $technician->id; ?>"><?= $technician->first_name; ?> <?= $technician->last_name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button class="btn btn-primary"><span><i class="fa fa-search"></i></span> Search Clients</button>
                    </form>

                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card full_width">
                <div class="card-header">
                    <h3 class="text-center">Nonrecurring Clients</h3>
                </div>
                <div class="card-body">
                    <button onclick="downloadCsv()" class="pull-right text-right btn btn-success download_btn hidden"><span><i class="fa fa-download"></i></span>  Download</button>
                    <?php
                        $lower=date('Y')-3;
                        $upper=date('Y')+3;
                    ?>
                    <h4 class="year-tags hidden" >Tags
                    <?php for($i=$lower;$i<=$upper;$i++): ?>
                         <span data-year="<?= $i; ?>" class="label label-primary year_selection"><?= $i; ?></span>
                    <?php endfor; ?>
                    </h4>
                    <table class="table table-striped">
                                    
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>Date</th>
                                <th>Location</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody class="nonrecurring_clients_data">
                                    
                        </tbody>
                        
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

var csv_data=[];

(function($){
    $(document).ready(function(){

        $('#nonrecurring_client_form').on('submit',function(e){
            e.preventDefault();
            getClients();

        });

        $('.year_selection').on('click',function(){

            // change the background color 
            $('.year_selection').removeClass('label-danger').addClass('label-primary');
            $(this).removeClass('label-primary').addClass('label-danger');

            // if isset fields then call getclients function 
            let from_date = $('input[name="from_date"]').val();
            let to_date = $('input[name="to_date"]').val();
            let technician_id = $('input[name="technician_id"]').val();

            if(from_date!="" && to_date!="" && technician_id!=""){
                let year=$(this).attr('data-year');

                let new_from_date=new Date(from_date);
                new_from_date.setFullYear(year);

                from_date=new_from_date.getFullYear()+"-"+('0' + (new_from_date.getMonth()+1)).slice(-2)+"-"+('0' + new_from_date.getDate()).slice(-2);

                let new_to_date=new Date(to_date);
                new_to_date.setFullYear(year);

                to_date=new_to_date.getFullYear()+"-"+('0' + (new_to_date.getMonth()+1)).slice(-2)+"-"+('0' + new_to_date.getDate()).slice(-2);

                console.log(from_date);
                console.log(to_date);
                

                $('input[name="from_date"]').val(from_date);
                $('input[name="to_date"]').val(to_date);

                // at last call getclients method to fetch records accordingly 
                getClients();

            }

        });
    });    

    function CSVtoArray(text) {
        var re_valid =  /^\s*(?:'[^'\\]*(?:\\[\S\s][^'\\]*)*'|"[^"\\]*(?:\\[\S\s][^"\\]*)*"|[^,'"\s\\]*(?:\s+[^,'"\s\\]+)*)\s*(?:,\s*(?:'[^'\\]*(?:\\[\S\s][^'\\]*)*'|"[^"\\]*(?:\\[\S\s][^"\\]*)*"|[^,'"\s\\]*(?:\s+[^,'"\s\\]+)*)\s*)*$/;
        var re_value =  /(?!\s*$)\s*(?:'([^'\\]*(?:\\[\S\s][^'\\]*)*)'|"([^"\\]*(?:\\[\S\s][^"\\]*)*)"|([^,'"\s\\]*(?:\s+[^,'"\s\\]+)*))\s*(?:,|$)/g;
        if (!re_valid.test(text)) return null;
        var a = [];                    
        text.replace(re_value,
            function(m0, m1, m2, m3) {
                if      (m1 !== undefined) a.push(m1.replace(/\\'/g, "'"));
                else if (m2 !== undefined) a.push(m2.replace(/\\"/g, '"'));
                else if (m3 !== undefined) a.push(m3);
                return ''; 
            });
        if (/,\s*$/.test(text)) a.push('');
        return a;
    }


    function getClients(){
            console.log('calling ajax');
            $('.nonrecurring_clients_data').html('<tr><td><div class="loader"></div></td></tr>');
            $('.year-tags').addClass('hidden');
            $('.download_btn').addClass('hidden');

            // reset the csv data array 
            csv_data=[];

            $.ajax({
                type:"post",
                url:"<?= admin_url( 'admin-ajax.php' ); ?>",
                data:$('#nonrecurring_client_form').serialize(),
                dataType:"json",
                success:function(data){
                    console.log(data);
                    // show the year tags as well 
                    $('.year-tags').removeClass('hidden');
                    $('.download_btn').removeClass('hidden');

                    
                    if(data.status=="success" && data.data!=null){

                        nonrecurring_html="";

                        $.each(data.data,function(key,value){

                            let temp_array=[value.summary,value.start.dateTime,value.location,value.description];

                            temp_array=temp_array.map(function(str){
                                if(!str){
                                    return "-";
                                }
                                str=str.toString().replace(/,/g,'');
                                str=str.toString().replace(/"/g,'\'');
                                str=str.toString().replace(/#/g,'');
                                str='"'+str+'"';
                                return str;
                            });

                            csv_data.push(temp_array);


                            nonrecurring_html+=`<tr>`;
                                nonrecurring_html+=`<td>${value.summary}</td>`;
                                nonrecurring_html+=`<td>${value.start.dateTime}</td>`;
                                nonrecurring_html+=`<td>${value.location}</td>`;
                                nonrecurring_html+=`<td>${value.description}</td>`;
                            nonrecurring_html+=`</tr>`;
                        });


                        $('.nonrecurring_clients_data').html(nonrecurring_html);

                    }
                    else if (data.status=="error"){
                        $('.nonrecurring_clients_data').html(`<tr><td class="text-danger" colspan="3">${data.message}</td></tr>`);

                    }
                }
            })
    }

})(jQuery);

function downloadCsv(){
        let csvContent = "data:text/csv;charset=utf-8,";

        csv_data.forEach(function(rowArray) {
            let row = rowArray.join(",");
            csvContent += row + "\r\n";
        });

        var encodedUri = encodeURI(csvContent);
        var link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "my_data.csv");
        document.body.appendChild(link); // Required for FF

        link.click();
    }



</script>