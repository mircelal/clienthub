<!-- Domain Modal -->
<div id="domain-modal" class="modal">
	<div class="modal-content modal-large">
		<div class="modal-header">
			<h3 id="domain-modal-title">Domain Ekle</h3>
			<span class="modal-close" data-modal="domain-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="domain-form">
				<input type="hidden" id="domain-id" name="id">
				<div class="form-row">
					<div class="form-group">
						<label for="domain-client-id">Müşteri *</label>
						<select id="domain-client-id" name="clientId" required class="form-control">
							<option value="">Müşteri Seçin</option>
						</select>
					</div>
					<div class="form-group">
						<label for="domain-name">Domain Adı *</label>
						<input type="text" id="domain-name" name="domainName" required class="form-control" placeholder="ornek.com">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="domain-registrar">Kayıtçı (Registrar)</label>
						<input type="text" id="domain-registrar" name="registrar" class="form-control" placeholder="GoDaddy, Namecheap...">
					</div>
					<div class="form-group">
						<label for="domain-renewal-interval">Süre (Yıl)</label>
						<select id="domain-renewal-interval" name="renewalInterval" class="form-control">
							<option value="1">1 Yıl</option>
							<option value="2">2 Yıl</option>
							<option value="3">3 Yıl</option>
							<option value="5">5 Yıl</option>
							<option value="10">10 Yıl</option>
						</select>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="domain-registration-date">Kayıt Tarihi</label>
						<input type="date" id="domain-registration-date" name="registrationDate" class="form-control">
					</div>
					<div class="form-group">
						<label for="domain-expiration-date">Bitiş Tarihi</label>
						<input type="date" id="domain-expiration-date" name="expirationDate" class="form-control">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="domain-price">Fiyat</label>
						<input type="number" id="domain-price" name="price" step="0.01" class="form-control" placeholder="12.99">
					</div>
					<div class="form-group">
						<label for="domain-currency">Para Birimi</label>
						<select id="domain-currency" name="currency" class="form-control">
							<option value="USD">$ USD</option>
							<option value="EUR">€ EUR</option>
							<option value="TRY">₺ TRY</option>
							<option value="AZN">₼ AZN</option>
							<option value="GBP">£ GBP</option>
							<option value="RUB">₽ RUB</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="domain-panel-notes">Panel Giriş Bilgileri</label>
					<textarea id="domain-panel-notes" name="panelNotes" class="form-control" rows="3" placeholder="Domain paneli URL, kullanıcı adı, şifre notları..."></textarea>
				</div>
				<div class="form-group">
					<label for="domain-notes">Genel Notlar</label>
					<textarea id="domain-notes" name="notes" class="form-control" rows="2" placeholder="Diğer notlar..."></textarea>
				</div>
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="domain-modal">İptal</button>
					<button type="submit" class="btn btn-primary">Kaydet</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Domain Extend Modal -->
<div id="domain-extend-modal" class="modal">
	<div class="modal-content">
		<div class="modal-header">
			<h3 id="domain-extend-modal-title">Domain Süresini Uzat</h3>
			<span class="modal-close" data-modal="domain-extend-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="domain-extend-form">
				<input type="hidden" id="extend-domain-id" name="id">
				
				<div class="domain-extend-info">
					<p><strong>Domain:</strong> <span id="extend-domain-name"></span></p>
					<p><strong>Mevcut Bitiş:</strong> <span id="extend-current-expiry"></span></p>
					<p><strong>Yeni Bitiş:</strong> <span id="extend-new-expiry" class="text-success"></span></p>
				</div>
				
				<div class="form-group">
					<label for="extend-years">Uzatma Süresi</label>
					<select id="extend-years" name="years" class="form-control" required>
						<option value="1">1 Yıl</option>
						<option value="2">2 Yıl</option>
						<option value="3">3 Yıl</option>
						<option value="5">5 Yıl</option>
						<option value="10">10 Yıl</option>
					</select>
				</div>
				
				<div class="form-row">
					<div class="form-group">
						<label for="extend-price">Uzatma Ücreti</label>
						<input type="number" id="extend-price" name="price" step="0.01" class="form-control" placeholder="12.99">
					</div>
					<div class="form-group">
						<label for="extend-currency">Para Birimi</label>
						<select id="extend-currency" name="currency" class="form-control">
							<option value="USD">$ USD</option>
							<option value="EUR">€ EUR</option>
							<option value="TRY">₺ TRY</option>
							<option value="AZN">₼ AZN</option>
							<option value="GBP">£ GBP</option>
							<option value="RUB">₽ RUB</option>
						</select>
					</div>
				</div>
				
				<div class="form-group">
					<label for="extend-note">Uzatma Notu</label>
					<textarea id="extend-note" name="note" class="form-control" rows="2" placeholder="Uzatma hakkında not (isteğe bağlı)..."></textarea>
				</div>
				
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="domain-extend-modal">İptal</button>
					<button type="submit" class="btn btn-success">Süreyi Uzat</button>
				</div>
			</form>
		</div>
	</div>
</div>

