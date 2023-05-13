<?php
$events = $args['events'];
?>

<?php if(is_object($events) && count((array) $events) >0): ?>
    <?php foreach($events as $event_key => $event): ?>
        <?php
            $event_address = sanitize_text_field($event->location);
            $cages_records =  (new AnimalCageTracker)->getAllRecordsForAddress($event_address);
        ?>
        <div class="calendarEventBox__event">
            <h4 class="calendarEventBox__title"><?= $event->summary; ?></h4>

            <p><b>Start Time : </b> <?= date('d M Y h:i A',strtotime($event->start->dateTime)); ?></p>
            <p><b>End Time : </b> <?= date('d M Y h:i A',strtotime($event->end->dateTime)); ?></p>

            <p class="calendarEventBox__address"> <b>Address : </b> <a target="_blank" href="https://www.google.com/maps/search/<?= (new Invoice)->sanitizeAddressField($event->location) ?>"><?= (new Invoice)->sanitizeAddressField($event->location) ?></a></p>

            <?php if(is_array($cages_records) && count($cages_records) > 0): ?>              
                <div class="panel-group">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" href="#cage_<?= $event_key; ?>"><span><i class="fa fa-file-text"></i></span> Animal Cage Records</a>
                            </h4>
                        </div>
                        <div id="cage_<?= $event_key; ?>" class="panel-collapse collapse">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Raccon Cages</th>
                                        <th>Squirrel Cages</th>
                                        <th>Total Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($cages_records as $record): ?>
                                        <tr>
                                            <td><?= date('d M Y', strtotime($record->created_at)); ?></td>
                                            <td><?= $record->racoon_cages; ?></td>
                                            <td><?= $record->squirrel_cages; ?></td>
                                            <td><?= $record->racoon_cages + $record->squirrel_cages; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="panel-group">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" href="#description_<?= $event_key; ?>"><span><i class="fa fa-file-text"></i></span> Description</a>
                        </h4>
                    </div>
                    <div id="description_<?= $event_key; ?>" class="panel-collapse collapse">
                        <p><?= $event->description; ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No Calendar Event Found</p>
<?php endif; ?>

