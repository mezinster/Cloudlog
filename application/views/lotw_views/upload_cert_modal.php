<!-- LOTW Certificate Upload Modal -->
<div class="modal fade" id="uploadCertModal" tabindex="-1" aria-labelledby="uploadCertModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header border-bottom">
				<h5 class="modal-title" id="uploadCertModalLabel">
					<i class="fas fa-cloud-upload-alt me-2"></i><?php echo lang('lotw_btn_upload_certificate'); ?>
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">
				<!-- Success/Error Messages -->
				<div id="cert-upload-messages" class="mb-3"></div>

				<!-- Upload Container -->
				<div id="uploadContainer">
					<!-- Instructions Card -->
					<div class="alert alert-info" role="alert">
						<h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i><?php echo lang('lotw_title_export_p12_file_instruction'); ?></h6>
						<ol class="mb-0 ps-3">
							<li><?php echo lang('lotw_p12_export_step_one'); ?></li>
							<li><?php echo lang('lotw_p12_export_step_two'); ?></li>
							<li><?php echo lang('lotw_p12_export_step_three'); ?></li>
							<li><?php echo lang('lotw_p12_export_step_four'); ?></li>
						</ol>
					</div>

					<!-- Upload Form -->
					<form id="certUploadForm" 
						hx-post="<?php echo site_url('lotw/do_cert_upload'); ?>"
						hx-encoding='multipart/form-data'
						hx-target="#cert-upload-messages"
						hx-swap="innerHTML">
						
						<div class="mb-4">
							<label for="userfile" class="form-label fw-semibold"><?php echo lang('lotw_title_upload_p12_cert'); ?></label>
							
							<!-- Drag & Drop Zone -->
							<div id="dropZone" class="border-2 border-dashed border-primary rounded p-4 text-center bg-light mb-3 cursor-pointer" style="border-width: 2px !important; transition: all 0.3s ease;">
								<i class="fas fa-file-import text-primary fa-2x mb-2 d-block"></i>
								<p class="mb-2 text-muted">Drag and drop your .p12 file here</p>
								<p class="mb-0 text-muted small">or click to browse</p>
							</div>

							<input type="file" name="userfile" id="userfile" accept=".p12" required style="display: none; visibility: hidden; height: 0; width: 0; margin: 0; padding: 0; border: none;">
							<small class="form-text text-muted d-block mt-2"><i class="fas fa-file-certificate me-1"></i>Only .p12 certificate files are accepted</small>
						</div>
					</form>
				</div>
			</div>

			<div class="modal-footer border-top">
				<button type="submit" id="certUploadBtn" form="certUploadForm" class="btn btn-primary">
					<i class="fas fa-cloud-upload-alt me-2"></i><?php echo lang('lotw_btn_upload_file'); ?>
				</button>
			</div>
		</div>
	</div>
</div>

<script>
// Handle upload start
document.addEventListener('htmx:beforeRequest', function(evt) {
	if (evt.detail.target && evt.detail.target.id === 'certUploadForm') {
		const btn = document.getElementById('certUploadBtn');
		if (btn) {
			btn.disabled = true;
			btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
		}
	}
}, true);

// Handle upload complete - using document-level delegation
document.addEventListener('htmx:afterSwap', function(evt) {
	if (evt.detail.target && evt.detail.target.id === 'cert-upload-messages') {
		handleUploadSuccess();
	}
}, true);

// Handle upload success
function handleUploadSuccess() {
	const messagesDiv = document.getElementById('cert-upload-messages');
	const btn = document.getElementById('certUploadBtn');
	const uploadContainer = document.getElementById('uploadContainer');
	
	// Reset button
	if (btn) {
		btn.disabled = false;
		btn.innerHTML = '<i class="fas fa-cloud-upload-alt me-2"></i><?php echo lang('lotw_btn_upload_file'); ?>';
	}
	
	// Check for any alert message
	if (messagesDiv) {
		const hasDangerAlert = messagesDiv.querySelector('.alert-danger');
		const hasSuccessAlert = messagesDiv.querySelector('.alert-success');
		const hasInfoAlert = messagesDiv.querySelector('.alert-info');
		
		// If there's a success or info alert (not error), hide the form
		if ((hasSuccessAlert || hasInfoAlert) && !hasDangerAlert) {
			// Refresh the certificate table
			htmx.ajax('GET', '<?php echo site_url('lotw/cert_table_refresh'); ?>', '#lotw-cert-list');
			
			// Hide the upload container and button
			if (uploadContainer) {
				uploadContainer.style.display = 'none';
			}
			if (btn) {
				btn.style.display = 'none';
			}
			
			// Add a completion message
			const completionMsg = document.createElement('div');
			completionMsg.className = 'alert alert-info alert-dismissible fade show mt-3';
			completionMsg.innerHTML = '<i class="fas fa-info-circle me-2"></i><strong>Certificate uploaded successfully!</strong> The certificate table has been updated. You can close this modal.';
			messagesDiv.appendChild(completionMsg);
		}
	}
}

// Setup drag and drop - no DOMContentLoaded needed
function setupDragDrop() {
	const dropZone = document.getElementById('dropZone');
	const fileInput = document.getElementById('userfile');
	
	if (!dropZone || !fileInput) {
		setTimeout(setupDragDrop, 100);
		return;
	}

	// Click to browse
	dropZone.addEventListener('click', () => fileInput.click(), { once: false });

	// Drag over
	dropZone.addEventListener('dragover', (e) => {
		e.preventDefault();
		dropZone.style.opacity = '0.7';
		dropZone.style.backgroundColor = 'rgba(13, 110, 253, 0.1)';
	});

	// Drag leave
	dropZone.addEventListener('dragleave', () => {
		dropZone.style.opacity = '1';
		dropZone.style.backgroundColor = '';
	});

	// Drop
	dropZone.addEventListener('drop', (e) => {
		e.preventDefault();
		dropZone.style.opacity = '1';
		dropZone.style.backgroundColor = '';
		
		if (e.dataTransfer.files.length > 0) {
			fileInput.files = e.dataTransfer.files;
			updateFileDisplay();
		}
	});

	// File input change
	fileInput.addEventListener('change', updateFileDisplay);

	// Update display when file selected
	function updateFileDisplay() {
		if (fileInput.files.length > 0) {
			const fileName = fileInput.files[0].name;
			dropZone.innerHTML = `<i class="fas fa-check-circle text-success fa-2x mb-2 d-block"></i><p class="mb-0 text-success fw-semibold">${fileName}</p>`;
		}
	}
}

// Setup when modal is first shown
document.addEventListener('DOMContentLoaded', function() {
	setupDragDrop();
	
	// Also setup modal reset listener
	const modal = document.getElementById('uploadCertModal');
	if (modal) {
		modal.addEventListener('hidden.bs.modal', function() {
			const fileInput = document.getElementById('userfile');
			const dropZone = document.getElementById('dropZone');
			const uploadContainer = document.getElementById('uploadContainer');
			const certUploadBtn = document.getElementById('certUploadBtn');
			const messagesDiv = document.getElementById('cert-upload-messages');
			
			if (fileInput) fileInput.value = '';
			if (dropZone) {
				dropZone.innerHTML = `<i class="fas fa-file-import text-primary fa-2x mb-2 d-block"></i><p class="mb-2 text-muted">Drag and drop your .p12 file here</p><p class="mb-0 text-muted small">or click to browse</p>`;
			}
			if (uploadContainer) uploadContainer.style.display = 'block';
			if (certUploadBtn) certUploadBtn.style.display = 'block';
			if (messagesDiv) messagesDiv.innerHTML = '';
		});
	}
});
</script>

<style>
#dropZone {
	cursor: pointer;
	transition: all 0.3s ease;
}

#dropZone:hover {
	background-color: rgba(13, 110, 253, 0.05) !important;
	border-color: #0d6efd !important;
}

#certUploadBtn {
	min-width: 140px;
}

.fa-spin {
	animation: spin 1s linear infinite;
}

@keyframes spin {
	0% { transform: rotate(0deg); }
	100% { transform: rotate(360deg); }
}
</style>
