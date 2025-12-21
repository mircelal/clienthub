<!-- Invoice Modal -->
<div id="invoice-modal" class="modal">
	<div class="modal-content modal-large">
		<div class="modal-header">
			<h3 id="invoice-modal-title">Fatura Oluştur</h3>
			<span class="modal-close" data-modal="invoice-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="invoice-form">
				<input type="hidden" id="invoice-id" name="id">
				<div class="form-row">
					<div class="form-group">
						<label for="invoice-client-id">Müşteri *</label>
						<select id="invoice-client-id" name="clientId" required class="form-control">
							<option value="">Müşteri Seçin</option>
						</select>
					</div>
					<div class="form-group">
						<label for="invoice-number">Fatura No</label>
						<input type="text" id="invoice-number" name="invoiceNumber" class="form-control"
							placeholder="Otomatik oluşturulur" autocomplete="off">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="invoice-issue-date">Düzenleme Tarihi</label>
						<input type="date" id="invoice-issue-date" name="issueDate" class="form-control">
					</div>
					<div class="form-group">
						<label for="invoice-due-date">Vade Tarihi</label>
						<input type="date" id="invoice-due-date" name="dueDate" class="form-control">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="invoice-total">Toplam Tutar</label>
						<input type="number" id="invoice-total" name="totalAmount" step="0.01" class="form-control">
					</div>
					<div class="form-group">
						<label for="invoice-currency">Para Birimi</label>
						<select id="invoice-currency" name="currency" class="form-control">
							<option value="USD">$ USD</option>
							<option value="EUR">€ EUR</option>
							<option value="TRY">₺ TRY</option>
							<option value="AZN">₼ AZN</option>
						</select>
					</div>
					<div class="form-group">
						<label for="invoice-status">Durum</label>
						<select id="invoice-status" name="status" class="form-control">
							<option value="draft">Taslak</option>
							<option value="sent">Gönderildi</option>
							<option value="paid">Ödendi</option>
							<option value="overdue">Gecikmiş</option>
							<option value="cancelled">İptal</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="invoice-notes">Notlar</label>
					<textarea id="invoice-notes" name="notes" class="form-control" rows="2"></textarea>
				</div>
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel"
						data-modal="invoice-modal">İptal</button>
					<button type="submit" class="btn btn-primary">Kaydet</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Payment Modal -->
<div id="payment-modal" class="modal">
	<div class="modal-content">
		<div class="modal-header">
			<h3 id="payment-modal-title">Ödeme Ekle</h3>
			<span class="modal-close" data-modal="payment-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="payment-form">
				<input type="hidden" id="payment-id" name="id">
				<input type="hidden" id="payment-invoice-id" name="invoiceId">
				<div class="form-group">
					<label for="payment-client-id">Müşteri *</label>
					<select id="payment-client-id" name="clientId" required class="form-control">
						<option value="">Müşteri Seçin</option>
					</select>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="payment-amount">Tutar *</label>
						<input type="number" id="payment-amount" name="amount" step="0.01" required
							class="form-control">
					</div>
					<div class="form-group">
						<label for="payment-currency">Para Birimi</label>
						<select id="payment-currency" name="currency" class="form-control">
							<option value="USD">$ USD</option>
							<option value="EUR">€ EUR</option>
							<option value="TRY">₺ TRY</option>
							<option value="AZN">₼ AZN</option>
						</select>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="payment-date">Ödeme Tarihi</label>
						<input type="date" id="payment-date" name="paymentDate" class="form-control">
					</div>
					<div class="form-group">
						<label for="payment-method">Ödeme Yöntemi</label>
						<select id="payment-method" name="paymentMethod" class="form-control">
							<option value="cash">Nakit</option>
							<option value="bank">Banka Havalesi</option>
							<option value="card">Kart</option>
							<option value="other">Diğer</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="payment-reference">Referans No</label>
					<input type="text" id="payment-reference" name="reference" class="form-control"
						placeholder="Dekont/Fiş no">
				</div>
				<div class="form-group">
					<label for="payment-notes">Notlar</label>
					<textarea id="payment-notes" name="notes" class="form-control" rows="2"></textarea>
				</div>
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel"
						data-modal="payment-modal">İptal</button>
					<button type="submit" class="btn btn-primary">Kaydet</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Invoice Item Modal -->
<div id="invoice-item-modal" class="modal modal-nested">
	<div class="modal-content">
		<div class="modal-header">
			<h3 id="invoice-item-modal-title">Fatura Kalemi Ekle</h3>
			<span class="modal-close" data-modal="invoice-item-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="invoice-item-form">
				<input type="hidden" id="invoice-item-id" name="id">
				<input type="hidden" id="invoice-item-invoice-id" name="invoiceId">

				<div class="form-group">
					<label for="invoice-item-type">Kalem Türü</label>
					<select id="invoice-item-type" name="itemType" class="form-control">
						<option value="manual">Manuel Giriş</option>
						<option value="domain">Domain</option>
						<option value="hosting">Hosting</option>
						<option value="website">Website</option>
						<option value="service">Hizmet</option>
						<option value="project">Proje</option>
					</select>
				</div>

				<div class="form-group" id="invoice-item-ref-group" style="display: none;">
					<label for="invoice-item-ref-id">İlişkili Öğe</label>
					<select id="invoice-item-ref-id" name="itemId" class="form-control">
						<option value="">Seçin</option>
					</select>
				</div>

				<div class="form-group">
					<label for="invoice-item-description">Açıklama *</label>
					<input type="text" id="invoice-item-description" name="description" required class="form-control"
						placeholder="Örn: Yıllık domain yenileme">
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="invoice-item-quantity">Miktar</label>
						<input type="number" id="invoice-item-quantity" name="quantity" value="1" min="1"
							class="form-control">
					</div>
					<div class="form-group">
						<label for="invoice-item-unit-price">Birim Fiyat</label>
						<input type="number" id="invoice-item-unit-price" name="unitPrice" step="0.01"
							class="form-control">
					</div>
					<div class="form-group">
						<label for="invoice-item-currency">Para Birimi</label>
						<select id="invoice-item-currency" name="currency" class="form-control">
							<option value="USD">$ USD</option>
							<option value="EUR">€ EUR</option>
							<option value="TRY">₺ TRY</option>
							<option value="AZN">₼ AZN</option>
						</select>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="invoice-item-start-date">Dönem Başlangıç</label>
						<input type="date" id="invoice-item-start-date" name="periodStart" class="form-control">
					</div>
					<div class="form-group">
						<label for="invoice-item-end-date">Dönem Bitiş</label>
						<input type="date" id="invoice-item-end-date" name="periodEnd" class="form-control">
					</div>
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="invoice-item-discount">İndirim</label>
						<input type="number" id="invoice-item-discount" name="discount" step="0.01" class="form-control"
							placeholder="0">
					</div>
					<div class="form-group">
						<label for="invoice-item-discount-type">İndirim Türü</label>
						<select id="invoice-item-discount-type" name="discountType" class="form-control">
							<option value="fixed">Sabit Tutar</option>
							<option value="percent">Yüzde (%)</option>
						</select>
					</div>
				</div>

				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel"
						data-modal="invoice-item-modal">İptal</button>
					<button type="submit" class="btn btn-primary">Kaydet</button>
				</div>
			</form>
		</div>
	</div>
</div>