<?php

(new Navigation)->calendar_navigation(@$_GET['tab']);


if(isset($_GET['tab']) && !empty($_GET['tab'])){
    switch ($_GET['tab']) {
        case 'create-event':
            get_template_part('/include/admin/calendar/create-calendar-event');
        break;
        case 'events':
            get_template_part('/include/admin/calendar/calendar-events');
        break;
        case 'add-new-calendar':
            get_template_part('/include/admin/calendar/add-new-calendar');
        break;
        case 'system-calendars':
            get_template_part('/include/admin/calendar/system-calendars');
        break;
        
        default:
            get_template_part('/include/admin/calendar/create-calendar-event');
        break;
    }
}
else{
    get_template_part('/include/admin/calendar/create-calendar-event');
}