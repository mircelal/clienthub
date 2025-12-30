<!-- Transaction Category Modal -->
<div id="transaction-category-modal" class="modal">
	<div class="modal-content">
		<div class="modal-header">
			<h3 id="transaction-category-modal-title">Kategori Ekle</h3>
			<span class="modal-close" data-modal="transaction-category-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="transaction-category-form">
				<input type="hidden" id="transaction-category-id" name="id">
				<div class="form-group">
					<label for="transaction-category-type">Tür *</label>
					<select id="transaction-category-type" name="type" required class="form-control">
						<option value="">Seçin</option>
						<option value="income">💰 Gelir</option>
						<option value="expense">💸 Gider</option>
					</select>
				</div>
				<div class="form-group">
					<label for="transaction-category-name">Kategori Adı *</label>
					<input type="text" id="transaction-category-name" name="name" required class="form-control" placeholder="Örn: İnternet, Reklam Geliri...">
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="transaction-category-icon">İkon</label>
						<input type="text" id="transaction-category-icon" name="icon" class="form-control" placeholder="🌐">
					</div>
					<div class="form-group">
						<label for="transaction-category-color">Renk</label>
						<input type="color" id="transaction-category-color" name="color" class="form-control" value="#3b82f6">
					</div>
				</div>
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="transaction-category-modal">İptal</button>
					<button type="submit" class="btn btn-primary">Kaydet</button>
				</div>
			</form>
		</div>
	</div>
</div>

