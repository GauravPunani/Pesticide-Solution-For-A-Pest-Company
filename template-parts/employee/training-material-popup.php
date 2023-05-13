<style>
    #diplayTrainingMaterialVideoModal .modal-dialog {
    margin: 0px auto;
    top: 50%;
    transform: translateY(-50%);
    position: absolute;
    left: 0;
    width: 95%;
    max-width: 500px;
    right: 0;
}
</style>
<div id="diplayTrainingMaterialVideoModal" data-backdrop="static" data-keyboard="false" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" onclick="removeTrainingVideo()" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" onclick="removeTrainingVideo()" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>