<?php
$technicians = (new Technician_details)->get_all_techincians();
// $coords='';

//if($_SERVER['REQUEST_METHOD']=="POST"){
   // pdie($_POST);
//     $technicians = (new Technician_details)->get_all_techincians();
//     $calendar=new Calendar();
//     if(count($technicians) <= 0) return;

//     foreach ($technicians as $key => $technician) {
//         $calendar_token_path = (new Technician_details)->getCalendarAccessToken($technician->id);
//         $calendar_id = $technician->calendar_id;
//         $events = $calendar->setAccessToken($calendar_token_path)
//             ->getEventByDate($start_date,$end_date,$calendar_id);
//         foreach ($events as $k => $value) {
//             if(!empty($value->location)){
//                 $event_data[$k]['name'] = $technician->first_name;
//                 $event_data[$k]['event_name'] = $value->summary;
//                 $event_data[$k]['location'] = $value->location;
//                 $event_data[$k]['date'] = date('d-m-Y', strtotime($value->start->dateTime));
//             }
//         }
//     }
//}

?>
<style>
    #map_canvas{
        width:100%;
        height:100vh;
    }
</style>
<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Search Technician</h3>
                    <form method="post" id="tech_calendar_events_address">
                        <input type="hidden" name="event_type" value="<?= ($_SERVER['REQUEST_METHOD']=="POST" ? 'filter_event' : 'all_events');?>">
                        <div class="form-group">
                            <label for="">Select Technician</label>
                            <select name="tech_ids[]" multiple class="form-control select2-field">
                                <option value="">Select</option>
                                <?php if(is_array($technicians) && count($technicians)>0): ?>
                                    <?php foreach($technicians as $technician): ?>
                                        <option <?= (isset($_POST['tech_ids']) && in_array($technician->id, $_POST['tech_ids']) ? "selected=selected" : '');?> value="<?= $technician->id; ?>"><?= $technician->first_name." ".$technician->last_name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Select Start Date</label>
                            <input type="date" class="form-control" name="start_date" value="<?= (isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d'));?>">
                        </div>

                        <div class="form-group">
                            <label for="">Select End Date</label>
                            <input type="date" class="form-control" name="end_date" value="<?= (isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d'));?>">
                        </div>

                        <button class="btn btn-primary"><span><i class="fa fa-map-marker"></i></span> Search</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Technician Address View</h3>
                    <div id="map_canvas"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function init_map(data) {
        let locations = data.data;
        let last_coord = locations.length-1;
        console.log(locations[last_coord].latitude);
        console.log(locations[last_coord].longitude);
        //return false;
        var map_options = {
            zoom: 10,
            center: new google.maps.LatLng(locations[last_coord].latitude, locations[last_coord].longitude)
        }
        map = new google.maps.Map(document.getElementById("map_canvas"), map_options);
        var infowindow = new google.maps.InfoWindow();
        var marker, i;
        for (i = 0; i < locations.length; i++) {  
            console.log(locations[i].latitude);
            console.log(locations[i].longitude);
            marker = new google.maps.Marker({
                position: new google.maps.LatLng(locations[i].latitude, locations[i].longitude),
                map: map
            });

            google.maps.event.addListener(marker, 'click', (function(marker, i) {
                return function() {
                infowindow.setContent(
                    '<div id="content">'+
                    '<div id="siteNotice">'+
                    '</div>'+
                    '<div id="bodyContent">'+
                        '<p><b>Technician : </b>'+locations[i].tech_name+'</p>'+
                        '<p><b>Client Name : </b>'+locations[i].event_name+'</p>'+
                        '<p><b>Address : </b>'+locations[i].formatted_address+'</p>'+
                        '<p><b>Timing : </b>'+locations[i].date+' <b>|</b> '+locations[i].start_time+' - '+locations[i].end_time+'</p>'+
                    '</div>'+
                    '</div>'
                );
                infowindow.open(map, marker);
                }
            })(marker, i));
        }
    }
</script>
<?php //if($_SERVER['REQUEST_METHOD']=="POST"): ?>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBkVmhrScUM6KYaexQDQY8Colf1bnwZ380&callback=getData" async defer></script>
<script>
    var map;
    function getData() {
        jQuery.ajax({
        type: "post",
        url: "<?= admin_url('admin-ajax.php'); ?>",
        beforeSend: function () {
            showLoader('Processing request, please wait...');
        },
        data: {
            "_wpnonce": "<?= wp_create_nonce('calendar_tech_address_nonce') ?>",
            action: 'calendar_tech_address',
            tech_id: "<?= (isset($_POST['tech_ids']) ? implode(',',$_POST['tech_ids']) : '');?>",
            start_date: "<?= $_POST['start_date'] ?? '';?>",
            end_date: "<?= $_POST['end_date'] ?? ''?>",
            event_type : jQuery("input[name='event_type']").val()
        },
        async: true,
        dataType: 'json',
        success: function (response) {
            if (response.status === "success") {
                swal.close();
                console.log(response);
                //load map
                init_map(response);
            } else {
                new Swal('Oops!', response.message, 'error');
            }
        }
        });  
    }
</script>
<?php //endif; ?>