<!-- Debt Modal -->
<div id="debt-modal" class="modal">
	<div class="modal-content modal-large">
		<div class="modal-header">
			<h3 id="debt-modal-title">Borç/Alacak Ekle</h3>
			<span class="modal-close" data-modal="debt-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="debt-form">
				<input type="hidden" id="debt-id" name="id">
				<div class="form-row">
					<div class="form-group">
						<label for="debt-type">Tür *</label>
						<select id="debt-type" name="type" required class="form-control">
							<option value="">Seçin</option>
							<option value="debt">💸 Borç (Aldığım)</option>
							<option value="credit">💰 Alacak (Verdiğim)</option>
						</select>
					</div>
					<div class="form-group">
						<label for="debt-debt-type">Borç Türü *</label>
						<select id="debt-debt-type" name="debtType" required class="form-control">
							<option value="">Seçin</option>
							<option value="credit_card">💳 Kredi Kartı</option>
							<option value="loan">🏦 Kredi</option>
							<option value="physical">🤝 Fiziksel Borç</option>
							<option value="other">📋 Diğer</option>
						</select>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="debt-creditor-debtor-name">Alacaklı/Borçlu Adı *</label>
						<input type="text" id="debt-creditor-debtor-name" name="creditorDebtorName" class="form-control" placeholder="Banka, kişi, kurum adı...">
					</div>
					<div class="form-group">
						<label for="debt-client-id">Müşteri (Opsiyonel)</label>
						<select id="debt-client-id" name="clientId" class="form-control">
							<option value="">Müşteri Seçin</option>
						</select>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="debt-total-amount">Toplam Tutar *</label>
						<input type="number" id="debt-total-amount" name="totalAmount" step="0.01" required class="form-control" placeholder="0.00">
					</div>
					<div class="form-group">
						<label for="debt-currency">Para Birimi</label>
						<select id="debt-currency" name="currency" class="form-control">
							<option value="USD">$ USD</option>
							<option value="EUR">€ EUR</option>
							<option value="TRY">₺ TRY</option>
							<option value="AZN">₼ AZN</option>
						</select>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="debt-start-date">Başlangıç Tarihi</label>
						<input type="date" id="debt-start-date" name="startDate" class="form-control">
					</div>
					<div class="form-group">
						<label for="debt-due-date">Vade Tarihi</label>
						<input type="date" id="debt-due-date" name="dueDate" class="form-control">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="debt-next-payment-date">Sonraki Ödeme Tarihi</label>
						<input type="date" id="debt-next-payment-date" name="nextPaymentDate" class="form-control">
					</div>
					<div class="form-group">
						<label for="debt-payment-frequency">Ödeme Sıklığı</label>
						<select id="debt-payment-frequency" name="paymentFrequency" class="form-control">
							<option value="">Tek Seferlik</option>
							<option value="daily">Günlük</option>
							<option value="weekly">Haftalık</option>
							<option value="monthly">Aylık</option>
						</select>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="debt-payment-amount">Ödeme Tutarı (Taksit)</label>
						<input type="number" id="debt-payment-amount" name="paymentAmount" step="0.01" class="form-control" placeholder="0.00">
					</div>
					<div class="form-group">
						<label for="debt-interest-rate">Faiz Oranı (%)</label>
						<input type="number" id="debt-interest-rate" name="interestRate" step="0.01" class="form-control" placeholder="0.00">
					</div>
				</div>
				<div class="form-group">
					<label for="debt-description">Açıklama</label>
					<textarea id="debt-description" name="description" class="form-control" rows="3" placeholder="Borç/alacak açıklaması..."></textarea>
				</div>
				<div class="form-group">
					<label for="debt-status">Durum</label>
					<select id="debt-status" name="status" class="form-control">
						<option value="active">Aktif</option>
						<option value="paid">Ödendi</option>
						<option value="overdue">Gecikmiş</option>
						<option value="cancelled">İptal</option>
					</select>
				</div>
				<div class="form-group">
					<label for="debt-notes">Notlar</label>
					<textarea id="debt-notes" name="notes" class="form-control" rows="2" placeholder="Ek notlar..."></textarea>
				</div>
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="debt-modal">İptal</button>
					<button type="submit" class="btn btn-primary">Kaydet</button>
				</div>
			</form>
		</div>
	</div>
</div>

