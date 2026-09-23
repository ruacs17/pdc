<?php require_once('templ_up.php');?>
<?php
$p_id = (isset($_REQUEST['pid']) && !empty($_REQUEST['pid']) ) ? functions::decode($_REQUEST['pid']) : 0;

$highlight_id = 0;
$error_message = '';
$error_vt_id = 0;

// Handle Add New Voucher Type Submission with Duplicate Check
if(isset($_POST['action']) && $_POST['action'] == 'add_vtype') {
    $vt_name = trim($_POST['vt_name']);
    $vt_desc = trim($_POST['vt_desc']);
    
    if(!empty($vt_name)) {
        $existing = $db->select('voucher_type', 'vt_id', array('vt_name' => $vt_name));
        if($db->num_rows($existing) > 0) {
            $_SESSION['error_message'] = 'Voucher Type name already exists!';
        } else {
            $new_id = $db->insert('voucher_type', array('vt_name' => $vt_name, 'vt_desc' => $vt_desc));
            $_SESSION['notif_warning'] = 'Voucher Type Added Successfully!';
            $_SESSION['highlight_vt_id'] = $new_id;
        }
    }
    functions::sendTo(functions::pageName());
    die();
}

// Handle Inline Update Submission with Duplicate Check
if(isset($_POST['action']) && $_POST['action'] == 'update_vtype') {
    $vt_id = functions::decode($_POST['vt_id']);
    $vt_name = trim($_POST['vt_name']);
    $vt_desc = trim($_POST['vt_desc']);
    
    if(!empty($vt_id) && !empty($vt_name)) {
        $existing = $db->select('voucher_type', 'vt_id', array('vt_name' => $vt_name));
        
        $is_duplicate = false;
        if($db->num_rows($existing) > 0) {
            while($row = $db->fetch_array($existing)) {
                if($row['vt_id'] != $vt_id) {
                    $is_duplicate = true;
                    break;
                }
            }
        }

        if($is_duplicate) {
            $_SESSION['error_message'] = 'Voucher Type name already exists!';
            $_SESSION['error_vt_id'] = $vt_id; // Save ID to scroll back to this row
        } else {
            $db->update('voucher_type', array('vt_name' => $vt_name, 'vt_desc' => $vt_desc), array('vt_id' => $vt_id));
            $_SESSION['notif_warning'] = 'Voucher Type Updated Successfully!';
            $_SESSION['highlight_vt_id'] = $vt_id;
        }
    }
    functions::sendTo(functions::pageName());
    die();
}

// Retrieve and clear the highlight ID from session if present
if(isset($_SESSION['highlight_vt_id'])) {
    $highlight_id = $_SESSION['highlight_vt_id'];
    unset($_SESSION['highlight_vt_id']);
}

// Retrieve and clear error message and target row ID from session if present
if(isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
if(isset($_SESSION['error_vt_id'])) {
    $error_vt_id = $_SESSION['error_vt_id'];
    unset($_SESSION['error_vt_id']);
}

$startrow = (isset($_REQUEST['startrow']) && !empty($_REQUEST['startrow']) ) ? $_REQUEST['startrow'] : 0;
$rowdisplay = 20;
$arrVal = array();
if($p_id){
	$arrVal = array('vt_id'=>$p_id);
}
?>
<!-- body content: start here-->
<style>
    .box-content { background: #fff; padding: 20px !important; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    
    .vtype-wrapper {
        max-width: 700px;
        margin: 0 auto;
    }
    .vtype-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .vtype-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 14px 18px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        transition: all 0.2s ease-in-out;
    }
    .vtype-item:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    /* Highlight class for newly added or updated item (Success) */
    .vtype-item.highlight-new {
        animation: flashHighlight 2s ease-in-out;
    }
    @keyframes flashHighlight {
        0% { background-color: #fef08a; border-color: #facc15; box-shadow: 0 0 15px rgba(250, 204, 21, 0.6); }
        50% { background-color: #fef08a; border-color: #facc15; box-shadow: 0 0 15px rgba(250, 204, 21, 0.6); }
        100% { background-color: #fff; border-color: #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
    }

    /* Highlight class for error/warning item */
    .vtype-item.highlight-warning {
        animation: flashWarning 2s ease-in-out;
    }
    @keyframes flashWarning {
        0% { background-color: #fee2e2; border-color: #ef4444; box-shadow: 0 0 15px rgba(239, 68, 68, 0.6); }
        50% { background-color: #fee2e2; border-color: #ef4444; box-shadow: 0 0 15px rgba(239, 68, 68, 0.6); }
        100% { background-color: #fff; border-color: #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
    }

    .vtype-content {
        flex-grow: 1;
        padding-right: 20px;
    }
    .view-mode {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .vtype-name {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.2;
    }
    .vtype-desc {
        font-size: 13px;
        color: #64748b;
        line-height: 1.3;
    }
    .vtype-action {
        flex-shrink: 0;
        display: flex;
        gap: 5px;
        margin-top: 2px;
    }
    .btn-edit-orange {
        background-color: #f97316 !important;
        background-image: none !important;
        border-color: #ea580c !important;
        color: #fff !important;
    }
    .btn-edit-orange:hover {
        background-color: #ea580c !important;
        border-color: #c2410c !important;
    }
    .edit-mode-inputs {
        display: none;
        flex-direction: column;
        gap: 4px;
        width: 100%;
    }
    .edit-mode-inputs input {
        width: 100%;
        height: 36px;
        padding: 5px 12px;
        font-size: 14px;
        margin-bottom: 0 !important;
        box-sizing: border-box;
    }
    .edit-mode-inputs textarea {
        width: 100%;
        height: 65px;
        padding: 6px 12px;
        font-size: 13px;
        margin-bottom: 0 !important;
        box-sizing: border-box;
        resize: vertical;
        line-height: 1.4;
    }

    /* Modern Modal Styling */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(2px);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        animation: fadeIn 0.2s ease-in-out;
    }
    .modal-container {
        background: #fff;
        width: 100%;
        max-width: 450px;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        animation: slideUp 0.2s ease-in-out;
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
    }
    .modal-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
    }
    .modal-close {
        background: none;
        border: none;
        font-size: 20px;
        color: #64748b;
        cursor: pointer;
    }
    .modal-close:hover {
        color: #0f172a;
    }
    .modal-body {
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .form-group label {
        font-size: 13px;
        font-weight: 600;
        color: #475569;
    }
    .form-group input {
        width: 100%;
        height: 38px;
        box-sizing: border-box;
        padding: 6px 12px;
        font-size: 14px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
    }
    .form-group textarea {
        width: 100%;
        box-sizing: border-box;
        padding: 8px 12px;
        font-size: 14px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        height: 80px;
        resize: vertical;
    }
    .modal-footer {
        padding: 12px 20px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes slideUp {
        from { transform: translateY(15px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style>

<div class="row-fluid">
	<div class="box span12">
		<div class="box-header" data-original-title>
			<h2><i class="halflings-icon white list-alt"></i><span class="break"></span>Voucher Type</h2>
		</div>
		<div class="box-content">
            <div class="vtype-wrapper">
                <!-- Add Button triggering Modal -->
                <div style="display: flex; justify-content: flex-end; margin-bottom: 12px;">
                    <a id="adc" href="#" class="btn btn-info btn-small" onclick="openAddModal(); return false;">
                        <i class="halflings-icon white plus"></i> Add New Voucher Type
                    </a>
                </div>

                <div class="vtype-list">
                    <?php
                    $qDisp = $db->select('voucher_type','*',$arrVal,'ORDER BY vt_name');
                    while($rDisp = $db->fetch_array($qDisp)):
                    $encoded_id = functions::encode($rDisp['vt_id']);
                    ?>
                    <form method="post" class="vtype-item-form" style="margin:0;" onsubmit="interceptUpdateForm(event, this);">
                        <input type="hidden" name="action" value="update_vtype">
                        <input type="hidden" name="vt_id" value="<?php echo $encoded_id; ?>">
                        
                        <div class="vtype-item <?php echo ($highlight_id == $rDisp['vt_id']) ? 'highlight-new' : ''; ?>" id="row-<?php echo $rDisp['vt_id']; ?>">
                            <!-- View Mode -->
                            <div class="vtype-content view-mode">
                                <div class="vtype-name"><?php echo htmlspecialchars($rDisp['vt_name']); ?></div>
                                <div class="vtype-desc">
                                    <?php echo !empty($rDisp['vt_desc']) ? htmlspecialchars($rDisp['vt_desc']) : '<span style="color:#94a3b8; font-style:italic;">No description provided.</span>'; ?>
                                </div>
                            </div>

                            <!-- Edit Mode Inputs (Hidden by default) -->
                            <div class="vtype-content edit-mode-inputs">
                                <input type="text" name="vt_name" value="<?php echo htmlspecialchars($rDisp['vt_name']); ?>" class="input-xlarge" required placeholder="Voucher Type Name">
                                <textarea name="vt_desc" class="input-xlarge" placeholder="Description"><?php echo htmlspecialchars($rDisp['vt_desc']); ?></textarea>
                            </div>

                            <!-- Actions -->
                            <div class="vtype-action">
                                <!-- View Mode Buttons -->
                                <button type="button" class="btn btn-mini btn-edit-orange btn-edit-toggle" onclick="toggleEdit(<?php echo $rDisp['vt_id']; ?>)" title="Modify this Voucher Type">
                                    <i class="halflings-icon white pencil"></i> Edit
                                </button>
                                
                                <!-- Edit Mode Buttons (Hidden by default) -->
                                <button type="submit" class="btn btn-mini btn-success btn-save-inline" style="display:none;" title="Save changes">
                                    <i class="halflings-icon white ok"></i> Save
                                </button>
                                <button type="button" class="btn btn-mini btn-danger btn-cancel-inline" style="display:none;" onclick="cancelEdit(<?php echo $rDisp['vt_id']; ?>)" title="Cancel">
                                    <i class="halflings-icon white remove"></i> Cancel
                                </button>
                            </div>
                        </div>
                    </form>
                    <?php endwhile;?>
                </div>
            </div>
		</div>
	</div><!--/span-->
</div><!--/row-->

<!-- Modern Add Modal Box -->
<div id="addModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Add New Voucher Type</h3>
            <button type="button" class="modal-close" onclick="closeAddModal()">&times;</button>
        </div>
        <form id="addVoucherForm" method="post" action="" style="margin: 0;" onsubmit="interceptAddForm(event);">
            <input type="hidden" name="action" value="add_vtype">
            <div class="modal-body">
                <div class="form-group">
                    <label for="vt_name">Voucher Type Name</label>
                    <input type="text" id="vt_name" name="vt_name" required placeholder="e.g., Payment Voucher">
                </div>
                <div class="form-group">
                    <label for="vt_desc">Description</label>
                    <textarea id="vt_desc" name="vt_desc" placeholder="Brief description of this voucher type..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn btn-success">
                    <i class="halflings-icon white ok"></i> Save Voucher Type
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modern Confirmation Modal -->
<div id="confirmModal" class="modal-overlay" style="display: none; z-index: 10005;">
    <div class="modal-container" style="max-width: 380px;">
        <div class="modal-header">
            <h3>Confirm Action</h3>
        </div>
        <div class="modal-body" style="text-align: center; padding: 24px 20px;">
            <p id="confirmText" style="margin: 0; font-size: 14px; color: #475569; line-height: 1.4;">Are you sure you want to perform this action?</p>
        </div>
        <div class="modal-footer" style="justify-content: center;">
            <button type="button" class="btn btn-default" onclick="closeConfirmModal()">Cancel</button>
            <button type="button" id="confirmYesBtn" class="btn btn-success">Yes, Proceed</button>
        </div>
    </div>
</div>

<!-- Modern Error Modal Box -->
<div id="errorModal" class="modal-overlay" style="display: none; z-index: 10010;">
    <div class="modal-container" style="max-width: 380px;">
        <div class="modal-header" style="background: #fef2f2; border-bottom: 1px solid #fee2e2;">
            <h3 style="color: #991b1b;"><i class="halflings-icon warning-sign" style="color: #dc2626;"></i> Error</h3>
            <button type="button" class="modal-close" onclick="closeErrorModal()">&times;</button>
        </div>
        <div class="modal-body" style="text-align: center; padding: 24px 20px;">
            <p id="errorMessageText" style="margin: 0; font-size: 14px; color: #475569; line-height: 1.4;"></p>
        </div>
        <div class="modal-footer" style="justify-content: center;">
            <button type="button" class="btn btn-danger" onclick="closeErrorModal()">OK</button>
        </div>
    </div>
</div>
<!-- body content: end here-->

<?php require_once('templ_down.php');?>
<script>
let pendingFormEvent = null;
let errorTargetId = <?php echo $error_vt_id; ?>;

// Automatically scroll to and center the newly added or successfully updated item if it exists
<?php if($highlight_id > 0): ?>
$(document).ready(function() {
    var targetRow = $('#row-<?php echo $highlight_id; ?>');
    if (targetRow.length) {
        $('html, body').animate({
            scrollTop: targetRow.offset().top - ($(window).height() / 2) + (targetRow.outerHeight() / 2)
        }, 500);
    }
});
<?php endif; ?>

// Automatically show error modal if an error message exists
<?php if(!empty($error_message)): ?>
$(document).ready(function() {
    $('#errorMessageText').text("<?php echo addslashes($error_message); ?>");
    $('#errorModal').fadeIn(200);
});
<?php endif; ?>

function toggleEdit(id) {
    var row = $('#row-' + id);
    row.find('.view-mode').hide();
    row.find('.edit-mode-inputs').css('display', 'flex');
    row.find('.btn-edit-toggle').hide();
    row.find('.btn-save-inline, .btn-cancel-inline').show();
}

function cancelEdit(id) {
    var row = $('#row-' + id);
    row.find('.edit-mode-inputs').hide();
    row.find('.view-mode').show();
    row.find('.btn-save-inline, .btn-cancel-inline').hide();
    row.find('.btn-edit-toggle').show();
}

function openAddModal() {
    $('#addModal').fadeIn(200);
}
function closeAddModal() {
    $('#addModal').fadeOut(200);
}

function closeConfirmModal() {
    $('#confirmModal').fadeOut(200);
    pendingFormEvent = null;
}

function closeErrorModal() {
    $('#errorModal').fadeOut(200, function() {
        if (errorTargetId > 0) {
            var targetRow = $('#row-' + errorTargetId);
            if (targetRow.length) {
                // Add the warning flash highlight class
                targetRow.addClass('highlight-warning');
                
                // Scroll to center
                $('html, body').animate({
                    scrollTop: targetRow.offset().top - ($(window).height() / 2) + (targetRow.outerHeight() / 2)
                }, 500, function() {
                    // Clean up warning class after animation completes
                    setTimeout(function() {
                        targetRow.removeClass('highlight-warning');
                    }, 2000);
                });
            }
        }
    });
}

// Intercept Add form submission to prompt confirmation modal
function interceptAddForm(event) {
    event.preventDefault();
    pendingFormEvent = event.target;
    $('#confirmText').text('Are you sure you want to add this new voucher type?');
    $('#confirmModal').fadeIn(200);
}

// Intercept Update form submission to prompt confirmation modal
function interceptUpdateForm(event, form) {
    event.preventDefault();
    pendingFormEvent = form;
    $('#confirmText').text('Are you sure you want to save these changes?');
    $('#confirmModal').fadeIn(200);
}

// Handle Yes click on modern confirmation modal
$(document).on('click', '#confirmYesBtn', function() {
    if (pendingFormEvent) {
        let form = pendingFormEvent;
        pendingFormEvent = null;
        $('#confirmModal').fadeOut(200);
        $('#addModal').fadeOut(200);
        form.submit();
    }
});

// Close modals when clicking outside container boxes
$(document).click(function(event) {
    if ($(event.target).is('#addModal')) {
        closeAddModal();
    }
    if ($(event.target).is('#confirmModal')) {
        closeConfirmModal();
    }
    if ($(event.target).is('#errorModal')) {
        closeErrorModal();
    }
});

function delt(){
	if(confirm('Do you want to remove this?'))
		return true;
	else
		return false;
}
</script>
<?php if(isset($_SESSION['notif_warning'])){?>
<script src="../js/notify.min.js"></script>
<script type="text/javascript">
$.notify("<?php echo $_SESSION['notif_warning'] ?>", {className: "success",autoHideDelay: 3500,globalPosition: 'bottom right'});
</script>
<?php unset($_SESSION['notif_warning']);} ?>