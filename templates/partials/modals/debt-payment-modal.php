<!-- Debt Payment Modal -->
<div id="debt-payment-modal" class="modal">
	<div class="modal-content">
		<div class="modal-header">
			<h3>Ödeme Ekle</h3>
			<span class="modal-close" data-modal="debt-payment-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="debt-payment-form">
				<input type="hidden" id="debt-payment-debt-id" name="debtId">
				<div class="form-row">
					<div class="form-group">
						<label for="debt-payment-amount">Ödeme Tutarı *</label>
						<input type="number" id="debt-payment-amount" name="amount" step="0.01" required class="form-control" placeholder="0.00">
					</div>
					<div class="form-group">
						<label for="debt-payment-date">Ödeme Tarihi *</label>
						<input type="date" id="debt-payment-date" name="paymentDate" required class="form-control">
					</div>
				</div>
				<div class="form-group">
					<label for="debt-payment-method">Ödeme Yöntemi</label>
					<select id="debt-payment-method" name="paymentMethod" class="form-control">
						<option value="">Seçin</option>
						<option value="cash">💵 Nakit</option>
						<option value="bank">🏦 Banka Transferi</option>
						<option value="credit_card">💳 Kredi Kartı</option>
						<option value="debit_card">💳 Banka Kartı</option>
						<option value="online">🌐 Online Ödeme</option>
						<option value="other">📋 Diğer</option>
					</select>
				</div>
				<div class="form-group">
					<label for="debt-payment-reference">Referans</label>
					<input type="text" id="debt-payment-reference" name="reference" class="form-control" placeholder="İşlem numarası, makbuz no...">
				</div>
				<div class="form-group">
					<label for="debt-payment-notes">Notlar</label>
					<textarea id="debt-payment-notes" name="notes" class="form-control" rows="2" placeholder="Ek notlar..."></textarea>
				</div>
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="debt-payment-modal">İptal</button>
					<button type="submit" class="btn btn-primary">Kaydet</button>
				</div>
			</form>
		</div>
	</div>
</div>

