<!-- Client Modal -->
<div id="client-modal" class="modal">
	<div class="modal-content">
		<div class="modal-header">
			<h3 id="client-modal-title">Müşteri Ekle</h3>
			<span class="modal-close" data-modal="client-modal">&times;</span>
		</div>
		<div class="modal-body">
			<div style="margin-bottom: 16px;">
				<button type="button" class="btn btn-info btn-sm" id="select-from-contacts-btn">📇 Kişilerden Seç</button>
			</div>
			<form id="client-form">
				<input type="hidden" id="client-id" name="id">
				<div class="form-group">
					<label for="client-name">Ad *</label>
					<input type="text" id="client-name" name="name" required class="form-control">
				</div>
				<div class="form-group">
					<label for="client-email">E-posta</label>
					<input type="email" id="client-email" name="email" class="form-control">
				</div>
				<div class="form-group">
					<label for="client-phone">Telefon</label>
					<input type="text" id="client-phone" name="phone" class="form-control">
				</div>
				<div class="form-group">
					<label for="client-notes">Notlar</label>
					<textarea id="client-notes" name="notes" class="form-control" rows="4"></textarea>
				</div>
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="client-modal">İptal</button>
					<button type="submit" class="btn btn-primary">Kaydet</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Contacts Selection Modal -->
<div id="contacts-modal" class="modal">
	<div class="modal-content modal-large">
		<div class="modal-header">
			<h3>Kişilerden Seç</h3>
			<span class="modal-close" data-modal="contacts-modal">&times;</span>
		</div>
		<div class="modal-body">
			<div class="form-group" style="margin-bottom: 16px;">
				<input type="text" id="contacts-search" class="form-control" placeholder="🔍 Kişi ara (ad, e-posta, telefon...)">
			</div>
			<div id="contacts-loading" style="text-align: center; padding: 20px;">
				<p>Kişiler yükleniyor...</p>
			</div>
			<div id="contacts-list" style="max-height: 500px; overflow-y: auto;"></div>
			<div id="contacts-empty" style="display: none; text-align: center; padding: 20px;">
				<p class="empty-message">Arama kriterinize uygun kişi bulunamadı</p>
			</div>
		</div>
	</div>
</div>

