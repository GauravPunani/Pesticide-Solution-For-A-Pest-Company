<?php

class Calendar extends GamFunctions
{

    private $service;
    private $client;
    private $from_date;
    private $to_date;

    function __construct()
    {


        add_action('wp_ajax_get_calendar_accounts', array($this, 'get_calendar_accounts'));
        add_action('wp_ajax_nopriv_get_calendar_accounts', array($this, 'get_calendar_accounts'));

        add_action('admin_post_add_new_calendar', array($this, 'add_new_calendar'));
        add_action('admin_post_nopriv_add_new_calendar', array($this, 'add_new_calendar'));

        add_action('admin_post_update_access_token', array($this, 'update_access_token'));
        add_action('admin_post_nopriv_update_access_token', array($this, 'update_access_token'));

        add_action('wp_ajax_check_for_calendar_slug', array($this, 'check_for_calendar_slug'));
        add_action('wp_ajax_nopriv_check_for_calendar_slug', array($this, 'check_for_calendar_slug'));

        add_action('wp_ajax_calendar_tech_address', array($this, 'calendar_tech_address'));
        add_action('wp_ajax_nopriv_calendar_tech_address', array($this, 'calendar_tech_address'));

        add_action('wp_ajax_getTechnicianCalendarEventsListing', array($this, 'getTechnicianCalendarEventsListing'));
        add_action('wp_ajax_nopriv_getTechnicianCalendarEventsListing', array($this, 'getTechnicianCalendarEventsListing'));

        // load sendgrid php sdk from vendor
        self::loadVendor();

        $this->client = new Google_Client();
        $this->client->setApplicationName('Google Calendar API PHP Quickstart');
        // $this->client->setScopes(Google_Service_Calendar::CALENDAR_READONLY);
        $this->client->setScopes(Google_Service_Calendar::CALENDAR);
        $this->client->setAuthConfig(get_template_directory() . '/google/credentials.json');
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');

        $this->service = new Google_Service_Calendar($this->client);

        add_action('admin_post_send_event_details_to_client', array($this, 'send_event_details_to_client'));
        add_action('admin_post_noprif_send_event_details_to_client', array($this, 'send_event_details_to_client'));
    }

    public function calendar_tech_address()
    {
        $this->verify_nonce_field('calendar_tech_address_nonce');
        $event_data = $event_all_data = [];

        $apiKey = "AIzaSyBkVmhrScUM6KYaexQDQY8Colf1bnwZ380";

        if (empty($_POST['event_type'])) $this->response('error');

        if ($_POST['event_type'] == 'all_events') {
            $technicians = (new Technician_details)->get_all_techincians();
            $start_date = date('Y-m-d', strtotime('-5 day'));
            $end_date = date('Y-m-d', strtotime('+5 day'));
        } else {
            if (empty($_POST['tech_id'])) $this->response('error');
            $technicians = (new Technician_details)->getSpecificTechnician($_POST['tech_id']);
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
        }

        if (count($technicians) <= 0) $this->response('error', 'No technician found');

        for ($x = 0; $x < count($technicians); $x++) {
            $calendar = new Calendar();
            $calendar_token_path = (new Technician_details)->getCalendarAccessToken($technicians[$x]->id);
            $calendar_id = $technicians[$x]->calendar_id;
            $events = $calendar->setAccessToken($calendar_token_path)
                ->getEventByDate($start_date, $end_date, $calendar_id);

            foreach ($events as $k => $value) {
                if (!empty($value->location)) {
                    //Formatted address
                    $formattedAddr = str_replace(' ', '+', $value->location);

                    $tech_name = $technicians[$x]->first_name . " " . $technicians[$x]->last_name;

                    // get the json response from url
                    $resp_json = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . $formattedAddr . '&sensor=true_or_false&key=' . $apiKey . '');

                    // decode the json response
                    $resp = json_decode($resp_json, true);
                    // response status will be 'OK', if able to geocode given address
                    if ($resp['status'] == 'OK') {

                        $event_data['event_name'] = $value->summary;

                        $event_data['tech_name'] = $tech_name;

                        $event_data['description'] = $value->description;

                        $event_data['date'] = date('D, M j', strtotime($value->start->dateTime));

                        $event_data['start_time'] = date('g:i', strtotime($value->start->dateTime));

                        $event_data['end_time'] = date('g:ia', strtotime($value->end->dateTime));

                        $event_data['formatted_address'] = isset($resp['results'][0]['formatted_address']) ? $resp['results'][0]['formatted_address'] : '';

                        $event_data['latitude'] = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : '';

                        $event_data['longitude'] = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : '';
                    }
                    array_push($event_all_data, $event_data);
                }
            }
        }

        // verify if data is exist
        if (count($event_data) > 0) {
            $this->response('success', 'Address fetch successfully', $event_all_data);
        } else {
            $this->response('error');
        }
    }

    public function update_access_token()
    {

        $this->verify_nonce_field('update_access_token');

        $page_url = esc_url_raw($_POST['page_url']);

        if (empty($_POST['auth_code'])) $this->sendErrorMessage($page_url);
        if (empty($_POST['calendar_id'])) $this->sendErrorMessage($page_url);

        $authCode = sanitize_text_field($_POST['auth_code']);
        $calendar_id = sanitize_text_field($_POST['calendar_id']);

        if (!$this->updateAccessToken($calendar_id, $authCode)) $this->sendErrorMessage($page_url);

        $message = "Access token updated successfully";
        $this->setFlashMessage($message, 'success');

        wp_redirect($page_url);
    }

    /*
        This method returns all the linked calendars in system
        Return Type : Array (Calendars)
        Date : 2021-07-21
    */
    public static function getSystemCalendars(array $columns = [])
    {
        global $wpdb;
        return $wpdb->get_results("select * from {$wpdb->prefix}google_calendars");
    }

    /*
        This method checks for calendar slug in system if already exist
        Return Type : String (true,false)
        Date : 2021-07-21
    */
    public function check_for_calendar_slug()
    {
        global $wpdb;

        if (isset($_POST['slug']) && !empty($_POST['slug'])) {
            $slug = esc_html($_POST['slug']);
            $res = $wpdb->get_var("select count(*) from {$wpdb->prefix}google_calendars where slug='{$_POST['slug']}'");

            if ($res) {
                echo "false";
            } else {
                echo "true";
            }
        } else {
            return "false";
        }

        wp_die();
    }

    public function add_new_calendar()
    {
        global $wpdb;
        $this->verify_nonce_field('add_new_calendar');
        $name = esc_html($_POST['name']);
        $email = esc_html($_POST['email']);
        $authCode = esc_html($_POST['auth_code']);

        $slug = $this->generate_calendar_slug($name);

        // generate and get token path
        $tokenPath = $this->generateAccessToken($slug, $authCode);

        if ($tokenPath) {
            $data = [
                'name'          =>  $name,
                'slug'          =>  $slug,
                'email'         =>  $email,
                'token_path'    =>  $tokenPath,
                'date'          =>  date('Y-m-d')
            ];

            $res = $wpdb->insert($wpdb->prefix . "google_calendars", $data);

            if ($res) {
                $message = "Calendar ac added to system successfully";
                $this->setFlashMessage($message, 'success');
            } else {
                $message = "Something went wrong, please try again later";
                $this->setFlashMessage($message, "danger");
            }
        } else {
            $message = "Token provided was incorrect, please make sure to copy and paste correct token";
            $this->setFlashMessage($message, "danger");
        }

        wp_redirect($_POST['page_url']);
    }

    public function generate_calendar_slug(string $calendar_name): string
    {
        global $wpdb;

        $basic_slug = $this->genereateSlug($calendar_name);

        // check if that slug already exist in system, if yest then generate slug with some random value 
        $count = $wpdb->get_var("select count(*) from {$wpdb->prefix}google_calendars where slug='$basic_slug'");

        if ($count) {
            $random_string = $this->quickRandom(6);
            $new_slug = $basic_slug . "_" . $random_string;
            return $this->generate_calendar_slug($new_slug);
        }

        return $basic_slug;
    }

    public function from_date($date = '')
    {
        $this->from_date = $date;
        return $this;
    }

    public function to_date($date = '')
    {
        $this->to_date = $date;
        return $this;
    }

    public function createAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function setAccessToken(string $tokenPath)
    {

        // set default timezone
        date_default_timezone_set('US/Eastern');

        // set timezone based on calendar 
        if ($tokenPath == get_template_directory() . '/google/tokens/houston/token.json') {
            date_default_timezone_set('America/Chicago');
        } elseif ($tokenPath == get_template_directory() . '/google/tokens/la/token.json') {
            date_default_timezone_set('America/Los_Angeles');
        }

        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $this->client->setAccessToken($accessToken);
        }

        if (!$this->client->isAccessTokenExpired()) return $this;

        if ($this->client->getRefreshToken())
            $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
        else
            $this->response('error', 'token expired or not found');

        if (!file_exists(dirname($tokenPath))) mkdir(dirname($tokenPath), 0700, true);

        file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));

        return $this;
    }

    /*
        This Metohd Generates the token for the given auth code and returns the token path
        Return Type : String (token path)
        Date : 2021-07-21
    */
    public function generateAccessToken(string $tokenSlug, string $authCode): string
    {

        try {
            $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);

            $this->client->setAccessToken($accessToken);

            $directory_path = "/google/tokens/" . $tokenSlug . ".json";

            $tokenPath = get_template_directory() . $directory_path;

            // if file does't exist creat the file at the path
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }

            file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));

            return $directory_path;
        } catch (Exception $e) {
            return false;
        }
    }

    public function updateAccessToken(int $calendar_id, string $authCode)
    {

        try {

            $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);

            $this->client->setAccessToken($accessToken);

            $calendar = $this->getCalendarById($calendar_id);

            if (!$calendar) return false;

            $tokenPath = get_template_directory() . $calendar->token_path;

            // if file does't exist creat the file at the path
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }

            file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getCalendarById(int $calendar_id)
    {
        global $wpdb;

        return $wpdb->get_row("
            select *
            from {$wpdb->prefix}google_calendars
            where id = '$calendar_id'
        ");
    }

    public function getClient()
    {

        $client = new Google_Client();
        $client->setApplicationName('Google Calendar API PHP Quickstart');
        $client->setScopes(Google_Service_Calendar::CALENDAR_READONLY);
        $client->setAuthConfig(get_template_directory() . '/google/credentials.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');


        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = get_template_directory() . '/google/tokens/miami/token.json';
        // echo json_encode([
        // 	'message'=>$tokenPath
        // ]);wp_die();
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }
        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // die('request for new one');
                // Request authorization from the user.
                // $authUrl = $client->createAuthUrl();
                // printf("Open the following link in your browser:\n%s\n", $authUrl);
                // print 'Enter verification code: ';
                // echo $authUrl;wp_die();
                $authCode = "4/2AGyA3e1QvPW2hQNsQBGzreNtiVZof5YYqUmEpjReYvOpK5CiD_rjas";

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                // echo "<pre>";print_r($accessToken);wp_die();
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    public function getAllEvents($calendarId = 'primary')
    {

        // Print the next 10 events on the user's calendar.
        $optParams = array(
            'maxResults'    =>    2500,
            'orderBy'         => 'startTime',
            'singleEvents'     => true,
        );

        if (!empty($this->from_date)) {
            $optParams['timeMin'] = date('c', strtotime($this->from_date));
        }

        if (!empty($this->to_date)) {
            $optParams['timeMax'] = date('c', strtotime($this->to_date));
        }

        $results = $this->service->events->listEvents($calendarId, $optParams);
        $events = $results->getItems();

        return (object)$events;
    }

    public function getYesterdayEventes($calendarId = "primary")
    {

        $day_start = date("Y-m-d 00:00:01", strtotime('-1 days'));
        $day_end = date("Y-m-d 23:59:00", strtotime('-1 days'));


        $date_inicio     = date('c', strtotime($day_start));
        $date_fin         = date('c', strtotime($day_end));
        // echo $date_fin;die;
        // echo json_encode(['message'=>$date_fin]);wp_die();

        $optParams = array(
            'orderBy' => 'startTime',
            'singleEvents' => TRUE,
            'timeMin' => $date_inicio,
            'timeMax' => $date_fin
        );


        $results = $this->service->events->listEvents($calendarId, $optParams);
        $events = $results->getItems();

        return (object)$events;
    }

    public function getTodayEvents($calendarId = "primary")
    {

        try {
            $day_start = date("Y-m-d 00:00:01");
            $day_end = date("Y-m-d 23:59:00");


            $date_inicio     = date('c', strtotime($day_start));
            $date_fin         = date('c', strtotime($day_end));

            $optParams = array(
                'orderBy' => 'startTime',
                'singleEvents' => TRUE,
                'timeMin' => $date_inicio,
                'timeMax' => $date_fin
            );


            $results = $this->service->events->listEvents($calendarId, $optParams);
            $events = $results->getItems();

            return (object)$events;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getEventByDate(string $start_date, string $end_date, string $calendarId)
    {

        $start_date = date("Y-m-d 00:00:01", strtotime($start_date));
        $end_date = date("Y-m-d 23:59:00", strtotime($end_date));

        try {
            $optParams = array(
                'orderBy' => 'startTime',
                'singleEvents' => TRUE,
                'timeMin' => date('c', strtotime($start_date)),
                'timeMax' => date('c', strtotime($end_date))
            );


            $results = $this->service->events->listEvents($calendarId, $optParams);
            $events = $results->getItems();

            return (object)$events;
        } catch (Exception $e) {
            return [];
        }
    }

    public function getCurrentDateEvent($date, $calendarId = "primary")
    {

        try {
            $dateMin = date('y-m-d 00:00:01', strtotime($date));
            $dateMax = date('y-m-d 23:59:00', strtotime($date));


            $optParams = array(
                'orderBy' => 'startTime',
                'singleEvents' => TRUE,
                'timeMin' => date('c', strtotime($dateMin)),
                'timeMax' => date('c', strtotime($dateMax)),
            );

            $results = $this->service->events->listEvents($calendarId, $optParams);
            $events = $results->getItems();

            return (object)$events;
        } catch (Exception $e) {
            $this->response('error', 'Calendar id not found or incorrect');
        }
    }

    public function getEventById($calendar_id, $eventId)
    {
        $event = $this->service->events->get($calendar_id, $eventId);
        return $event;
    }

    public function getRecurringEvents($calendarId = "primary", $eventId)
    {

        // $service->events->instances('primary', "eventId");
        $results = $this->service->events->instances($calendarId, $eventId);
        $events = $results->getItems();

        return (object)$events;
    }

    public function getNonrecuringClients($client_events)
    {

        foreach ($client_events as $key => $event) {
            if ($event->recurringEventId != null || $event->recurringEventId != "") {
                unset($client_events->$key);
            }
        }

        return $client_events;
    }

    public function create_calendar_event($calendarId = 'primary', $event_data)
    {
        $event = new Google_Service_Calendar_Event($event_data);
        $event = $this->service->events->insert($calendarId, $event);
    }

    public function send_event_details_to_client()
    {
        global $wpdb;

        $this->verify_nonce_field('send_event_details_to_client');

        $page_url = esc_url_raw($_POST['page_url']);

        if (
            empty($_POST['title']) ||
            empty($_POST['location']) ||
            empty($_POST['description']) ||
            empty($_POST['start_time']) ||
            empty($_POST['end_time']) ||
            empty($_POST['repeate_type']) ||
            empty($_POST['interval']) ||
            empty($_POST['ends_on']) ||
            empty($_POST['technician'])
        ) $this->sendErrorMessage($page_url, "1");

        $title = $this->sanitizeEscape($_POST['title']);
        $location = $this->sanitizeEscape($_POST['location']);
        $description = $this->sanitizeEscape($_POST['description'], 'textarea');
        $start_time = $this->sanitizeEscape($_POST['start_time']);
        $end_time = $this->sanitizeEscape($_POST['end_time']);
        $technician_id = $this->sanitizeEscape($_POST['technician']);
        $repeate_type = $this->sanitizeEscape($_POST['repeate_type']);
        $interval = $this->sanitizeEscape($_POST['interval']);
        $ends_on = $this->sanitizeEscape($_POST['ends_on']);

        $client_name = explode('-', $title)[0];

        $branch_id = (new Technician_details)->getTechnicianBranchId($technician_id);
        $review_link = $this->getReviewLink($branch_id);

        $tech_data = $wpdb->get_row("
            select T.id, T.calendar_id 
            from {$wpdb->prefix}technician_details T
            where T.id = '$technician_id'
        ");

        $calendar_token_path = (new Technician_details)->getCalendarAccessToken($tech_data->id);

        $timezone = "US/Eastern";

        if ($calendar_token_path == get_template_directory() . '/google/tokens/houston/token.json') {
            $timezone = "America/Chicago";
        } elseif ($calendar_token_path == get_template_directory() . '/google/tokens/la/token.json') {
            $timezone = "America/Los_Angeles";
        }

        $recurring_filter = "FREQ=" . $repeate_type . ";INTERVAL=" . $interval . ";";

        if (isset($_POST['repeats_on']) && is_array($_POST['repeats_on']) && count($_POST['repeats_on']) > 0) {
            $repeats_on = implode(',', $_POST['repeats_on']);
            $recurring_filter .= "BYDAY=" . $repeats_on . ";";
        }

        if ($_POST['ends'] == "on") {
            $recurring_filter .= "UNTIL:" . date('Ymd', strtotime($ends_on));
        }

        $event_data = array(
            'summary'       => $title,
            'location'      => $location,
            'description'   => $description,
            'start'         => array(
                'dateTime' => date('c', strtotime($start_time)),
                'timeZone' => $timezone,
            ),
            'end' => array(
                'dateTime' => date('c', strtotime($end_time)),
                'timeZone' => $timezone,
            ),
            'recurrence' => array(
                "RRULE:$recurring_filter"
            ),
        );

        try {
            $this->setAccessToken($calendar_token_path)
                ->create_calendar_event($tech_data->calendar_id, $event_data);

            $message = "Calendar event created successfully";
            $this->setFlashMessage($message, 'success');
        } catch (Google_Service_Exception $e) {
            $this->sendErrorMessage($page_url, "2");
        }

        // send email to client about the schedule of event

        if (!empty($_POST['client_email'])) {
            $client_email = sanitize_email($_POST['client_email']);
            $date_range = date('d M Y h:i:s', strtotime($_POST['start_time'])) . " To " . date('d M Y h:i A', strtotime($_POST['end_time']));

            $subject = "Appointment Scheduled With Gam Exterminating";
            $message = "
                <p>Thank you for booking your appointment with gam exterminating ($date_range)</p>
                <p>Office number 8777332057</p>
                <p>Please leave us 5 star review by <a href='$review_link'>clicking here</a></p>
            ";

            $tos = [];
            $tos[] = [
                'email' =>  $client_email,
                'name'  =>  $client_name
            ];

            (new Sendgrid_child)->sendTemplateEmail($tos, $subject, $message);
        }

        wp_redirect($page_url);
    }

    public function get_calendar_accounts()
    {

        $this->verify_nonce_field('get_calendar_accounts');

        if (empty($_POST['branch_id'])) $this->response('error', 'Branch id not found');

        $branch_id = sanitize_text_field($_POST['branch_id']);

        $calendar_access_token = (new Technician_details)->getCalendarTokenByBranchId($branch_id);

        if (is_null($calendar_access_token) || empty($calendar_access_token))
            $this->response('error', 'calendar access token not found');

        // set calendar access token
        $this->setAccessToken($calendar_access_token);

        $data = [];

        $calendarList = $this->service->calendarList->listCalendarList();

        while (true) {

            foreach ($calendarList->getItems() as $calendarListEntry) {
                $data[] = [
                    'id'  =>  $calendarListEntry->id,
                    'name'    =>  $calendarListEntry->summary
                ];
            }

            $pageToken = $calendarList->getNextPageToken();

            if ($pageToken) {
                $optParams = array('pageToken' => $pageToken);
                $calendarList = $this->service->calendarList->listCalendarList($optParams);
            } else {
                break;
            }
        }

        $this->response('success', '', $data);
    }

    public function getAllTechsCalendarEventsByDate(string $date): array
    {
        // get all technicians
        $technicians = (new Technician_details)->get_all_technicians(true);

        // loop on technicians and get their yesterday calendar events
        if (!is_array($technicians) || count($technicians) <= 0) return [];

        $calendar_events = [];
        foreach ($technicians as $key => $technician) {

            $calendar_token = (new Technician_details)->getCalendarAccessToken($technician->id);

            if (empty($calendar_token) || empty($technician->calendar_id)) continue;

            try {

                $obj = new Calendar();
                $events = $obj->setAccessToken($calendar_token)
                    ->getCurrentDateEvent($date, $technician->calendar_id);

                if (!is_array((array) $events) || count((array)$events) <= 0) continue;

                foreach ($events as $event) {
                    $calendar_events[] = [
                        'summary'       =>  $event->summary,
                        'location'      =>  $event->location,
                        'description'   =>  $event->description,
                        'technician_id' =>  $technician->id
                    ];
                }
            } catch (Exception $e) {
                continue;
            }
        }

        return $calendar_events;
    }

    public function getTechnicianCalendarEventsListing()
    {

        $this->verify_nonce_field('getTechnicianCalendarEventsListing');

        if (empty($_POST['date'])) {
            echo "Please select the date field";
            wp_die();
        }

        // event date 
        $event_date = $this->sanitizeEscape($_POST['date']);

        // current date to check if events are pending before that date
        $current_date = date('Y-m-d');

        $technician_id = (new Technician_details)->get_technician_id();
        $pending_events = (new Invoice)->checkPendingEvents($technician_id, $current_date);

        if (is_object($pending_events) && count((array) $pending_events) > 0) {
            echo "<p class='text-danger'>Please clear these events first before moving ahead.</p>";
            get_template_part('/template-parts/calendar/events-listing', null, ['events' => $pending_events]);
            wp_die();
        }

        $calendar_id = (new Technician_details)->getTechnicianCalendarId($technician_id);
        $calendar_token_path = (new Technician_details)->getCalendarAccessToken($technician_id);

        if (strtotime($event_date) >= strtotime(date('Y-m-d', strtotime('+2 days')))) {
            echo "<p class='text-danger'>You're not allowed to see events after tomorrow.</p>";
            wp_die();
        }

        // get the today events
        $obj = new Calendar();
        $calendar_events = $obj->setAccessToken($calendar_token_path)
            ->getEventByDate($event_date, $event_date, $calendar_id);

        get_template_part('/template-parts/calendar/events-listing', null, ['events' => $calendar_events]);
        wp_die();
    }

    public function getTechnicianEventsByWeek(int $employee_id, string $week)
    {

        $technician_id = (new Employee\Employee)->getReferenceIdByEmployeeId($employee_id);
        if (!$technician_id) throw new Exception('Employee ref id not found');

        $week_monday = date('Y-m-d', strtotime('this monday', strtotime($week)));
        $week_sunday = date('Y-m-d', strtotime('this sunday', strtotime($week)));

        try {
            $calendar_id = (new Technician_details)->getTechnicianCalendarId($technician_id);
            $calendar_token_path = (new Technician_details)->getCalendarAccessToken($technician_id);

            $obj = new Calendar();

            return $obj->setAccessToken($calendar_token_path)
                ->getEventByDate($week_monday, $week_sunday, $calendar_id);
        } catch (Exception $e) {
            return [];
        }
    }

    public function uploadNotesInEvent(int $technician_id, string $event_id, string $notes, string $date)
    {

        try {
            $calendar_token_path = (new Technician_details)->getCalendarAccessToken($technician_id);
            $calendar_id = (new Technician_details)->getTechnicianCalendarId($technician_id);

            $obj = $this->setAccessToken($calendar_token_path)
                ->getEventById($calendar_id, $event_id);

            $desc_notes = $obj->description . "\n----------------------------------------------\n Dated : " . $date . " " . $notes . "\n----------------------------------------------\n";

            $event = $this->service->events->get($calendar_id, $event_id);
            $event->setDescription($desc_notes);
            $this->service->events->update($calendar_id, $event->getId(), $event);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function updateCalendarEvent($calendar_id, $eventId, $desc)
    {
        try {
            // First retrieve the event from the API.
            $event = $this->service->events->get($calendar_id, $eventId);

            $event->setDescription($desc);

            $this->service->events->update($calendar_id, $event->getId(), $event);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getTechnicianCalendarEvents(int $technician_id, string $date)
    {

        $calendar_token_path = (new Technician_details)->getCalendarAccessToken($technician_id);
        $calendar_id = (new Technician_details)->getTechnicianCalendarId($technician_id);

        $calendar = new Calendar();
        return $calendar->setAccessToken($calendar_token_path)
            ->getCurrentDateEvent($date, $calendar_id);
    }

    public function getNormalNotesEvents(int $technician_id, string $date)
    {

        $calendar_events = $this->getTechnicianCalendarEvents($technician_id, $date);

        if (count((array)$calendar_events) <= 0) return [];

        foreach ($calendar_events as $key => $calendar_event) {
            if (strpos($calendar_event->description, '@sn') !== false) {
                unset($calendar_events->$key);
            }
        }

        return $calendar_events;
    }

    public function getSpecialNotesEvents(int $technician_id, string $date)
    {

        $calendar_events = $this->getTechnicianCalendarEvents($technician_id, $date);

        if (count((array)$calendar_events) <= 0) return [];

        foreach ($calendar_events as $key => $calendar_event) {
            if (strpos($calendar_event->description, '@sn') === false) {
                unset($calendar_events->$key);
            }
        }

        return $calendar_events;
    }

    public function getCalendarList()
    {
        $calendarList = $this->service->calendarList->listCalendarList();

        while (true) {
            $pageToken = $calendarList->getNextPageToken();
            if ($pageToken) {
                $optParams = array('pageToken' => $pageToken);
                $calendarList = $this->service->calendarList->listCalendarList($optParams);
            } else {
                break;
            }
        }

        return $calendarList;
    }

    public static function isCalendarInSystem(string $calendar_id)
    {
        global $wpdb;
        return $wpdb->get_var("select count(*) from {$wpdb->prefix}technician_details where calendar_id = '$calendar_id'");
    }
}

new Calendar();
