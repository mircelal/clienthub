<!-- Hosting Domain Link Modal -->
<div id="hosting-domain-link-modal" class="modal">
	<div class="modal-content">
		<div class="modal-header">
			<h3>Hosting'e Domain Bağla</h3>
			<span class="modal-close" data-modal="hosting-domain-link-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="hosting-domain-link-form">
				<input type="hidden" id="hdl-hosting-id" name="hostingId">
				<div class="form-group">
					<label for="hdl-domain-id">Domain Seçin *</label>
					<select id="hdl-domain-id" name="domainId" required class="form-control">
						<option value="">Domain Seçin</option>
					</select>
					<small class="form-text">Bu hosting hesabına bağlamak istediğiniz domaini seçin</small>
				</div>
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="hosting-domain-link-modal">İptal</button>
					<button type="submit" class="btn btn-primary">Bağla</button>
				</div>
			</form>
		</div>
	</div>
</div>

