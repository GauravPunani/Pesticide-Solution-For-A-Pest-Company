<?php

class TechnicianTracker extends GamFunctions{
    function __construct(){
        add_action("wp_ajax_track_technician",array($this,"track_technician"));
        add_action("wp_ajax_nopriv_track_technician",array($this,"track_technician"));

		add_action( 'wp_ajax_save_cordinates', array($this,'save_cordinates') );
		add_action( 'wp_ajax_nopriv_save_cordinates', array($this,'save_cordinates') );

    }

    public function track_technician(){

        global $wpdb;

        if(isset($_POST['technician']) && isset($_POST['date']) && !empty($_POST['technician']) && !empty($_POST['date'])){
            $coords=$wpdb->get_var("select coordinates from {$wpdb->prefix}tracking_technician where technician_id='{$_POST['technician']}' and DATE(date)='{$_POST['date']}'");

            if(!empty($coords) && !is_null($coords)){
                $coords=json_decode($coords,true);

                $this->response('success','data found',$coords);
            }
            else{
                $this->response('error','No Location data for the date');
            }

        }
        else{
            $this->response('error','field is not set or empty');
        }
    }

	public function save_cordinates(){
		global $wpdb;

		$this->verify_nonce_field('save_cordinates');

		if(
			empty($_POST['lat']) ||
			empty($_POST['lng'])
		) $this->response('error');

		$technician_id = (new Technician_details)->get_technician_id();
		$today_date = date('Y-m-d');
		$data=[];

		$data[]=[
			'lat' => $this->sanitizeEscape($_POST['lat']),
			'lng' => $this->sanitizeEscape($_POST['lng']),
		];

		$cords = $this->getRecord($technician_id, $today_date);

		if($cords){

			$coordinates = json_decode($cords->coordinates,true);
			$new_cords = array_merge($coordinates,$data);

			$tracker_data = ['coordinates' => json_encode($new_cords)];
			$res = $wpdb->update($wpdb->prefix."tracking_technician",$tracker_data,['id'=>$cords->id]);
			if($res === false) $this->response('error');

			$this->response('success','in if',$new_cords);
		}
		else{
			$tracker_data=[
				'technician_id'	=>	$technician_id,
				'coordinates'	=>	json_encode($data)
			];

			$wpdb->insert($wpdb->prefix."tracking_technician",$tracker_data);
			$this->response('success','in else',$tracker_data);
		}
	}

	public function getRecord(int $technician_id, string $date){
		global $wpdb;

		return $wpdb->get_var("
			select *
			from {$wpdb->prefix}tracking_technician
			where technician_id = '$technician_id'
			and DATE(created_at) = '$date'
		");
	}	

}

new TechnicianTracker();