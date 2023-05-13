<?php
$technicians=(new Technician_details)->get_all_technicians(true);

$coords='';

if($_SERVER['REQUEST_METHOD']=="POST"){

    $conditions=[];

    $conditions[]=" technician_id = '{$_POST['technician']}'";
    $conditions[]=" DATE(created_at) = '{$_POST['date']}'";

    if(!current_user_can('other_than_upstate')){
        $accessible_branches=(new Branches)->partner_accessible_branches(true);
        $accessible_branches="'" . implode ( "', '", $accessible_branches ) . "'";
    
        $conditions[]=" branch_id IN ($accessible_branches)";
    }

    if(count($conditions)>0){
        $conditions=(new GamFunctions)->generate_query($conditions);
    }
    else{
        $conditions="";
    }


    $coords=$wpdb->get_row("
        select updated_at,coordinates from 
        {$wpdb->prefix}tracking_technician 
        $conditions
    ");
}

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
                    <form method="post" id="track_technician">
                        <input type="hidden" name="action" value="track_technician">
                        <div class="form-group">
                            <label for="">Select Technician</label>
                            <select name="technician" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php if(is_array($technicians) && count($technicians)>0): ?>
                                    <?php foreach($technicians as $technician): ?>
                                        <option value="<?= $technician->id; ?>"><?= $technician->first_name." ".$technician->last_name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Select Date</label>
                            <input type="date" class="form-control" name="date" value="<?= date('Y-m-d'); ?>">
                        </div>

                        <button class="btn btn-primary"><span><i class="fa fa-map-marker"></i></span> Search</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Technician Map View</h3>
                    <?php if(isset($coords->updated_at) && !empty($coords->updated_at)): ?>
                        <p>Last Updated : <?= date('h:i:A', strtotime($coords->updated_at)); ?></p>
                    <?php endif; ?>
                    <div id="map_canvas">
                        <?php if(empty($coords)): ?>
                            <?php if($_SERVER['REQUEST_METHOD']=="POST" && empty($coords)): ?>
                                <p class="text-danger">No Tracking data available for the day</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if($_SERVER['REQUEST_METHOD']=="POST" && !empty($coords)): ?>
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyBkVmhrScUM6KYaexQDQY8Colf1bnwZ380"></script>
<script>

    //Intialize the Direction Service
    var service = new google.maps.DirectionsService();
    var directionsDisplay = new google.maps.DirectionsRenderer();
    var bounds = new google.maps.LatLngBounds();
    var delayFactor = 0;
    var counter=0;
    var map;

    (function($){
        $(document).on('ready',function(){


            var data=JSON.parse('<?= $coords->coordinates; ?>');
            console.log('before print');
            console.log(data);

            let last_coord=data[data.length-1];

            map = new google.maps.Map(
                document.getElementById("map_canvas"), {
                center: new google.maps.LatLng(last_coord.lat,last_coord.lng ),
                zoom: 15,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            });
        
            new google.maps.Marker({
                position: new google.maps.LatLng(last_coord.lat,last_coord.lng ),
                map,
                title: "Technician Last Known Location",
            });

            let lat_lng=[];

            for(var key in data){
                lat_lng.push(new google.maps.LatLng(data[key].lat, data[key].lng))
            }

            for (var t = 0;(t + 1) < lat_lng.length; t++) {

                if ((t + 1) < lat_lng.length) {
                    var src = lat_lng[t];
                    var des = lat_lng[t + 1];

                    var request = {
                        origin: src,
                        destination: des,
                        travelMode: google.maps.DirectionsTravelMode.DRIVING
                    };

                    m_get_directions_route (request);

                }
            }

        });
    })(jQuery);

    function m_get_directions_route (request) {
        counter++;

        service.route(request, function(result, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                // new path for the next result
                var path = new google.maps.MVCArray();
                //Set the Path Stroke Color
                // new polyline for the next result
                var poly = new google.maps.Polyline({
                    map: map,
                    strokeColor: '#4986E7'
                });
                poly.setPath(path);
                for (var k = 0, len = result.routes[0].overview_path.length; k < len; k++) {
                    path.push(result.routes[0].overview_path[k]);
                    bounds.extend(result.routes[0].overview_path[k]);
                    map.fitBounds(bounds);
                }
            }
            else if (status === google.maps.DirectionsStatus.OVER_QUERY_LIMIT) {
                console.log('in there for counter' +counter);
                delayFactor++;
                setTimeout(function () {
                    m_get_directions_route(request);
                }, delayFactor * 1000);
            }
            else{
                console.log("Route: " + status);
            }                                    

        });
    }    
</script>

<?php endif; ?>