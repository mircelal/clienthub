<!-- Hosting Modal -->
<div id="hosting-modal" class="modal">
	<div class="modal-content modal-large">
		<div class="modal-header">
			<h3 id="hosting-modal-title">Hosting Ekle</h3>
			<span class="modal-close" data-modal="hosting-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="hosting-form">
				<input type="hidden" id="hosting-id" name="id">
				<div class="form-row">
					<div class="form-group">
						<label for="hosting-client-id">Müşteri *</label>
						<select id="hosting-client-id" name="clientId" required class="form-control">
							<option value="">Müşteri Seçin</option>
						</select>
					</div>
					<div class="form-group">
						<label for="hosting-provider">Sağlayıcı *</label>
						<input type="text" id="hosting-provider" name="provider" required class="form-control" placeholder="Vultr, Hetzner, DigitalOcean...">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="hosting-plan">Paket</label>
						<input type="text" id="hosting-plan" name="plan" class="form-control" placeholder="VPS 4GB, Shared Pro...">
					</div>
					<div class="form-group">
						<label for="hosting-server-type">Sunucu Tipi</label>
						<select id="hosting-server-type" name="serverType" class="form-control">
							<option value="own">🏠 Kendi Sunucum</option>
							<option value="external" selected>🌐 Harici Sunucu</option>
						</select>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="hosting-server-ip">Sunucu IP</label>
						<input type="text" id="hosting-server-ip" name="serverIp" class="form-control" placeholder="192.168.1.1">
					</div>
					<div class="form-group">
						<label for="hosting-renewal-interval">Ödeme Periyodu</label>
						<select id="hosting-renewal-interval" name="renewalInterval" class="form-control">
							<option value="monthly">Aylık</option>
							<option value="quarterly">3 Aylık</option>
							<option value="yearly" selected>Yıllık</option>
							<option value="biennial">2 Yıllık</option>
						</select>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="hosting-start-date">Başlangıç Tarihi</label>
						<input type="date" id="hosting-start-date" name="startDate" class="form-control">
					</div>
					<div class="form-group">
						<label for="hosting-expiration-date">Sonraki Ödeme Tarihi</label>
						<input type="date" id="hosting-expiration-date" name="expirationDate" class="form-control">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="hosting-price">Fiyat</label>
						<input type="number" id="hosting-price" name="price" step="0.01" class="form-control" placeholder="9.99">
					</div>
					<div class="form-group">
						<label for="hosting-currency">Para Birimi</label>
						<select id="hosting-currency" name="currency" class="form-control">
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
					<label for="hosting-panel-url">Panel URL</label>
					<input type="text" id="hosting-panel-url" name="panelUrl" class="form-control" placeholder="https://panel.provider.com">
				</div>
				<div class="form-group">
					<label for="hosting-panel-notes">Panel Giriş Bilgileri</label>
					<textarea id="hosting-panel-notes" name="panelNotes" class="form-control" rows="2" placeholder="Kullanıcı: admin&#10;Şifre: ****"></textarea>
				</div>
				<div class="form-group">
					<label for="hosting-notes">Genel Notlar</label>
					<textarea id="hosting-notes" name="notes" class="form-control" rows="2" placeholder="Diğer notlar..."></textarea>
				</div>
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="hosting-modal">İptal</button>
					<button type="submit" class="btn btn-primary">Kaydet</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Hosting Payment Modal -->
<div id="hosting-payment-modal" class="modal">
	<div class="modal-content">
		<div class="modal-header">
			<h3>💳 Hosting Ödeme Ekle</h3>
			<span class="modal-close" data-modal="hosting-payment-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="hosting-payment-form">
				<input type="hidden" id="hp-hosting-id" name="hostingId">
				
				<div class="domain-extend-info">
					<p><strong>Hosting:</strong> <span id="hp-hosting-name"></span></p>
					<p><strong>Mevcut Bitiş:</strong> <span id="hp-current-expiry"></span></p>
					<p><strong>Yeni Bitiş:</strong> <span id="hp-new-expiry" class="text-success"></span></p>
				</div>
				
				<div class="form-row">
					<div class="form-group">
						<label for="hp-amount">Ödeme Tutarı</label>
						<input type="number" id="hp-amount" name="amount" step="0.01" class="form-control" placeholder="9.99">
					</div>
					<div class="form-group">
						<label for="hp-currency">Para Birimi</label>
						<select id="hp-currency" name="currency" class="form-control">
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
					<label for="hp-period">Ödeme Periyodu</label>
					<select id="hp-period" name="period" class="form-control">
						<option value="1">1 Ay</option>
						<option value="3">3 Ay</option>
						<option value="6">6 Ay</option>
						<option value="12" selected>1 Yıl</option>
						<option value="24">2 Yıl</option>
					</select>
				</div>
				
				<div class="form-group">
					<label for="hp-note">Not</label>
					<textarea id="hp-note" name="note" class="form-control" rows="2" placeholder="Ödeme notu..."></textarea>
				</div>
				
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="hosting-payment-modal">İptal</button>
					<button type="submit" class="btn btn-success">Ödemeyi Kaydet</button>
				</div>
			</form>
		</div>
	</div>
</div>

