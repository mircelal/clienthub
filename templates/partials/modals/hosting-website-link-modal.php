<!-- Hosting Website Link Modal -->
<div id="hosting-website-link-modal" class="modal">
	<div class="modal-content">
		<div class="modal-header">
			<h3>Hosting'e Site Bağla</h3>
			<span class="modal-close" data-modal="hosting-website-link-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="hosting-website-link-form">
				<input type="hidden" id="hwl-hosting-id" name="hostingId">
				<div class="form-group">
					<label for="hwl-website-id">Site Seçin *</label>
					<select id="hwl-website-id" name="websiteId" required class="form-control">
						<option value="">Site Seçin</option>
					</select>
					<small class="form-text">Bu hosting hesabına bağlamak istediğiniz siteyi seçin</small>
				</div>
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="hosting-website-link-modal">İptal</button>
					<button type="submit" class="btn btn-primary">Bağla</button>
				</div>
			</form>
		</div>
	</div>
</div>

