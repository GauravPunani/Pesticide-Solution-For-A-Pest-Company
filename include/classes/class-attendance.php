<?php
    class OfficeAttendance extends GamFunctions{

    private $error_message = "Something went wrong, please try again later"; 

    function __construct(){

        add_action("admin_post_create_attendance", array($this, "create_attendance"));
        add_action("admin_post_nopriv_create_attendance", array($this, "create_attendance"));
        
        add_action("admin_post_create_cold_attendance", array($this, "create_cold_attendance"));
        add_action("admin_post_nopriv_create_cold_attendance", array($this, "create_cold_attendance"));

        add_action("admin_post_update_attendance", array($this, "update_attendance"));
        add_action("admin_post_nopriv_update_attendance", array($this, "update_attendance"));
        
        add_action("wp_ajax_delete_attendence", array($this, "delete_attendence"));
        add_action("wp_ajax_nopriv_delete_attendence", array($this, "delete_attendence"));
        
        add_action("admin_post_create_attendence", array($this, "create_attendence"));
        add_action("admin_post_nopriv_create_attendence", array($this, "create_attendence"));
        
        add_action("admin_post_update_attendence_ea", array($this, "update_attendence_ea"));
        add_action("admin_post_nopriv_update_attendence_ea", array($this, "update_attendence_ea"));

        add_action("wp_ajax_mark_employee_attendence", array($this, "mark_employee_attendence"));
        add_action("wp_ajax_nopriv_mark_employee_attendence", array($this, "mark_employee_attendence"));

        add_action("wp_ajax_view_attendance_logs",array($this,'view_attendance_logs'));
		add_action("wp_ajax_nopriv_view_attendance_logs",array($this,'view_attendance_logs'));

        // add_action("wp_ajax_update_attendance",array($this,'update_attendance'));
		// add_action("wp_ajax_nopriv_update_attendance",array($this,'update_attendance'));
    }



    // create cold call Attendance
    public function create_cold_attendance(){
        global $wpdb;

        $this->verify_nonce_field('create_cold_attendance');
        $page_url = $_POST['page_url'];
        $attendance_date =  date('Y-m-d');
        $data = [
            'employee_id'  =>  esc_html($_POST['employee_id']),
            'start_time' => esc_html($_POST['start_time']),
            'attendance_date' => esc_html($attendance_date),
        ];
        // pdie($data);
        $res = $wpdb->insert("wp_attendance", $data );  

        if(!$res) $this->sendErrorMessage($page_url);

        $message = "Attendance updated successfully";
        $this->setFlashMessage($message, "success");

        unset($_SESSION['redirect_to_attendance']);         
        wp_redirect($page_url);

    }

    // create Attendance
    public function create_attendance(){
        global $wpdb;

        $this->verify_nonce_field('create_attendance');
        $page_url = $_POST['page_url'];
        $attendance_date =  date('Y-m-d');
        $data = [
            'employee_id'  =>  esc_html($_POST['employee_edit_id']),
            'start_time' => esc_html($_POST['start_time']),
            'attendance_date' => esc_html($attendance_date),
        ];

        $res = $wpdb->insert("wp_attendance", $data );  
        
        if(!$res) $this->sendErrorMessage($page_url);

        $message = "Attendance updated successfully";
        $this->setFlashMessage($message, "success");
        
        unset($_SESSION['redirect_to_attendance']);      
        wp_redirect($page_url);

    }
    // Update Attendance
    public function update_attendance(){
        global $wpdb;

        $this->verify_nonce_field('update_attendance');
        $page_url = $_POST['page_url'];
        $close_time = $_POST['close_time'];
        $employee_id = $_POST['user_id'];
        $edit_id = $_POST['edit_id'];
        $data = [
            'close_time' => esc_html($_POST['close_time']),
        ];
        // pdie($data);

        $where = [ 'id' => $edit_id ];
        $response = $wpdb->update( $wpdb->prefix . 'attendance', $data, $where );
        // unset($_SESSION);
        session_destroy();
        // return $this;

        if(!$response) $this->sendErrorMessage($page_url);

        $message = "Account updated successfully";
        $this->setFlashMessage($message, "success");
                
        wp_redirect($page_url);

    }


    public function mark_employee_attendence(){
        global $wpdb;
        $this->verify_nonce_field('mark_employee_attendence');
        
        if(!isset($_POST['emp_id']) || empty($_POST['emp_id'])) $this->response('error', $this->error_message);
        if(!isset($_POST['tag']) || empty($_POST['tag'])) $this->response('error', $this->error_message);

        $employee_id = filter_var($_POST['emp_id'], FILTER_SANITIZE_STRING);
        $tag = filter_var($_POST['tag'], FILTER_SANITIZE_STRING);
        $current_date = date('Y-m-d');

        if($tag == "sign-in"){
            
            $check_exists = $wpdb->get_row("
                SELECT id FROM {$wpdb->prefix}attendance WHERE employee_id = '$employee_id' AND DATE(created_at)='{$current_date}'
            ");

            if(!empty($check_exists)) $this->response('error', $this->error_message);

            $data = [
                "employee_id" => $employee_id,
                "start_time" => date('h:i:s'),
                "close_time" => null,
                "attendance_date" =>  date('Y-m-d'),          
                "created_at" => date('Y-m-d h:i:s'),
                "updated_at" => date('Y-m-d h:i:s')
            ];

            $res = $wpdb->insert($wpdb->prefix."attendance", $data);

            if(!$res) $this->response('error', $this->error_message);

            $this->response('success', 'Attendence Marked Successfully');
        }else{
            
            $attend_id = $wpdb->get_row("
                SELECT id FROM {$wpdb->prefix}attendance WHERE employee_id = '$employee_id' AND DATE(created_at)='{$current_date}'
            ");

            if(empty($attend_id)) $this->response('error', $this->error_message);
            
            $data = [
                "close_time" => date('h:i:s'),
                "updated_at" => date('Y-m-d h:i:s')
            ];

            $response = $wpdb->update( $wpdb->prefix .'attendance', $data, ['id' => $attend_id->id] );

            if(!$response) $this->response('error', $this->error_message);

            $this->response('success', 'Attendence Marked Successfully');
        }
    
    
    }


    public function update_attendence_ea(){
        global $wpdb;
        $this->verify_nonce_field('update_attendence_ea');
        $page_url = esc_url_raw($_POST['page_url']);
        
        if(!isset($_POST['id']) || empty($_POST['id'])) $this->sendErrorMessage($page_url);
        if(!isset($_POST['start_time']) || empty($_POST['start_time'])) $this->sendErrorMessage($page_url);
        if(!isset($_POST['emp_id']) || empty($_POST['emp_id'])) $this->sendErrorMessage($page_url);
        if(!isset($_POST['old_attend_date']) || empty($_POST['old_attend_date'])) $this->sendErrorMessage($page_url);
        if(!isset($_POST['attendance_date']) || empty($_POST['attendance_date'])) $this->sendErrorMessage($page_url);
        
        $emp_id = $_POST['emp_id'];
        $start_time = date('H:i:s', strtotime($_POST['start_time'])); 
        
        if(empty($_POST['close_time'])){
            $close_time = null;
        }else{
            $close_time = date('H:i:s', strtotime($_POST['close_time']));
        }

        $attend_date = $_POST['attendance_date'];
        $old_attend_date = $_POST['old_attend_date'];

        /* Check changed date already exists */
        if($old_attend_date != $attend_date){
            
            /* Check attendance already exists with emp id and date */
            $match_ids = $wpdb->get_row("
                SELECT id FROM {$wpdb->prefix}attendance WHERE employee_id = '$emp_id' AND DATE(attendance_date)='{$attend_date}'
            ");
            
            if(!empty($match_ids)) $this->sendErrorMessage($page_url,'Attendence already exists for selected date.');

        }

        $data = [
            "start_time" => $start_time,
            "close_time" => $close_time,
            "attendance_date" => $attend_date,
            "updated_at" => date('Y-m-d h:i:s')
        ];

        $response = $wpdb->update( $wpdb->prefix .'attendance', $data, ['id' => $_POST['id']] );

        if(!$response) $this->sendErrorMessage($page_url);

        $message = "Employee Attendence Updated Successfully";
        $this->setFlashMessage($message, "success");
                
        wp_redirect($page_url);

    }


    public function delete_attendence(){

        $this->verify_nonce_field('delete_attendence');

        global $wpdb;
        // print_r($_POST); die;
        if(empty($_POST['id'])) $this->response('error', $this->error_message);

        $res=$wpdb->delete($wpdb->prefix."attendance",['id' => $_POST['id']]);

        if(!$res) $this->response('error', $this->error_message);

        $this->response('success', 'Attendence Marked Successfully');

    }


    public function create_attendence(){
        $this->verify_nonce_field('create_attendence');

        global $wpdb;
        
        $page_url = esc_url_raw($_POST['page_url']);
        
        if(empty($_POST['emp_id'])) $this->sendErrorMessage($page_url);
        if(empty($_POST['attendance_date'])) $this->sendErrorMessage($page_url);
        if(empty($_POST['start_time'])) $this->sendErrorMessage($page_url);
        
        $start_time = date('H:i:s', strtotime($_POST['start_time'])); 
        $attend_date = date('Y-m-d', strtotime($_POST['attendance_date'])); 
        
        if(empty($_POST['close_time'])){
            $close_time = null;
        }else{
            $close_time = date('H:i:s', strtotime($_POST['close_time']));
            
        }

        $emp_id = filter_var($_POST['emp_id'], FILTER_SANITIZE_STRING);

        /* Check attendance already exists with emp id and date */
        $check_exists = $wpdb->get_row("
            SELECT id FROM {$wpdb->prefix}attendance WHERE employee_id = '$emp_id' AND DATE(attendance_date)='{$attend_date}'
        ");
        
        if(!empty($check_exists)) $this->sendErrorMessage($page_url,'Attendence Already created selected date.');
        
        $data = [
            "employee_id" => $emp_id,
            "start_time" => $start_time,
            "close_time" => $close_time,
            "attendance_date" =>  date('Y-m-d'),
            "created_at" => date('Y-m-d h:i:s'),
            "updated_at" => date('Y-m-d h:i:s')
        ];

        $res = $wpdb->insert($wpdb->prefix."attendance", $data);

        if(!$res) $this->sendErrorMessage($page_url);

        $message = "Attendence Added Successfully";
        $this->setFlashMessage($message, "success");
                
        wp_redirect($page_url);
    }
    
    // Display Attendance data in Pop-up
    public function view_attendance_logs(){
        global $wpdb;
            $this->verify_nonce_field('view_attendance_logs');
            $s_id = $_POST['attendance_id'];
            $s_date = $_POST['attendance_date'];
         
            $deposit_proof = $wpdb->get_results("SELECT * FROM wp_attendance WHERE employee_id = '$s_id' AND attendance_date = '$s_date' ORDER by created_at DESC");
            // $sum = $wpdb->get_results("SELECT SUM(TIMEDIFF(close_time,start_time)) FROM wp_attendance WHERE employee_id = '123' ORDER by created_at DESC");
            // print_r($sum);

            if(!empty($deposit_proof)){
                $output = '<table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Start Time</th>
                        <th>Close Time</th>
                        <th>Attendance Date</th>
                        <th>Total Hours</th>
                    </tr>
                </thead>
                <tbody>';
                foreach($deposit_proof as $row): 
                                                  
                        $t1 = $row->start_time;
                        $t2 = $row->close_time;
                    
                        $time1 = new DateTime($t1);
                        $time2 = new DateTime($t2);
                        $interval = $time1->diff($time2);

                        $output .= '<tr>';
                        $output .= '<td>'.(!empty($row->start_time) ? $row->start_time : '').'</td>';
                        $output .= '<td>'.(!empty($row->close_time) ? $row->close_time : '').'</td>';
                        $output .= '<td>'.(!empty($row->attendance_date) ? $row->attendance_date : '').'</td>';
                        $output .= '<td>'.(!empty($interval->format('%h')." Hours ".$interval->format('%i')." Minutes") ? $interval->format('%h')." Hours ".$interval->format('%i')." Minutes" : '').'</td>';

                        $output .= '</tr>';
                endforeach;
                $output .= '</tbody></table>';
                echo $output;
            }else {
                echo '<h3 class="text-center text-danger">No Attendance Logs Found</h3>';
            }

            wp_die();
    }


}

new OfficeAttendance();