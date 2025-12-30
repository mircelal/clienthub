<!-- Transaction Modal -->
<div id="transaction-modal" class="modal">
	<div class="modal-content modal-large">
		<div class="modal-header">
			<h3 id="transaction-modal-title">İşlem Ekle</h3>
			<span class="modal-close" data-modal="transaction-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="transaction-form">
				<input type="hidden" id="transaction-id" name="id">
				<div class="form-row">
					<div class="form-group">
						<label for="transaction-type">Tür *</label>
						<select id="transaction-type" name="type" required class="form-control">
							<option value="">Seçin</option>
							<option value="income">💰 Gelir</option>
							<option value="expense">💸 Gider</option>
						</select>
					</div>
					<div class="form-group">
						<label for="transaction-category-id">Kategori</label>
						<select id="transaction-category-id" name="categoryId" class="form-control">
							<option value="">Kategori Seçin</option>
						</select>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="transaction-amount">Tutar *</label>
						<input type="number" id="transaction-amount" name="amount" step="0.01" required class="form-control" placeholder="0.00">
					</div>
					<div class="form-group">
						<label for="transaction-currency">Para Birimi</label>
						<select id="transaction-currency" name="currency" class="form-control">
							<option value="USD">$ USD</option>
							<option value="EUR">€ EUR</option>
							<option value="TRY">₺ TRY</option>
							<option value="AZN">₼ AZN</option>
						</select>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="transaction-date">İşlem Tarihi *</label>
						<input type="date" id="transaction-date" name="transactionDate" required class="form-control">
					</div>
					<div class="form-group">
						<label for="transaction-payment-method">Ödeme Yöntemi</label>
						<select id="transaction-payment-method" name="paymentMethod" class="form-control">
							<option value="">Seçin</option>
							<option value="cash">💵 Nakit</option>
							<option value="bank">🏦 Banka Transferi</option>
							<option value="credit_card">💳 Kredi Kartı</option>
							<option value="debit_card">💳 Banka Kartı</option>
							<option value="online">🌐 Online Ödeme</option>
							<option value="other">📋 Diğer</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="transaction-description">Açıklama</label>
					<textarea id="transaction-description" name="description" class="form-control" rows="3" placeholder="İşlem açıklaması..."></textarea>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="transaction-client-id">Müşteri (Opsiyonel)</label>
						<select id="transaction-client-id" name="clientId" class="form-control">
							<option value="">Müşteri Seçin</option>
						</select>
					</div>
					<div class="form-group">
						<label for="transaction-project-id">Proje (Opsiyonel)</label>
						<select id="transaction-project-id" name="projectId" class="form-control">
							<option value="">Proje Seçin</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="transaction-reference">Referans / Fatura No</label>
					<input type="text" id="transaction-reference" name="reference" class="form-control" placeholder="Fatura numarası, işlem referansı...">
				</div>
				<div class="form-group">
					<label for="transaction-notes">Notlar</label>
					<textarea id="transaction-notes" name="notes" class="form-control" rows="2" placeholder="Ek notlar..."></textarea>
				</div>
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="transaction-modal">İptal</button>
					<button type="submit" class="btn btn-primary">Kaydet</button>
				</div>
			</form>
		</div>
	</div>
</div>

