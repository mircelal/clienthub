<!-- Settings Tab -->
<div id="settings-tab" class="tab-content">
	<div class="section">
		<h2>Genel Ayarlar</h2>
		<p class="section-description">Uygulama genelinde kullanılacak ayarları buradan yapabilirsiniz.</p>
	</div>

	<div class="section">
		<h3>Para Birimi Ayarları</h3>
		<div class="settings-group">
			<div class="settings-item">
				<label for="default-currency" class="settings-label">
					<span class="settings-label__title">Varsayılan Para Birimi</span>
					<span class="settings-label__description">Yeni kayıtlarda (fatura, ödeme, işlem, borç/alacak) kullanılacak varsayılan para birimi</span>
				</label>
				<div class="settings-input">
					<select id="default-currency" class="form-control">
						<option value="USD">USD - US Dollar ($)</option>
						<option value="EUR">EUR - Euro (€)</option>
						<option value="GBP">GBP - British Pound (£)</option>
						<option value="TRY">TRY - Turkish Lira (₺)</option>
						<option value="AZN">AZN - Azerbaijani Manat (₼)</option>
						<option value="RUB">RUB - Russian Ruble (₽)</option>
					</select>
				</div>
			</div>
		</div>
		<div class="settings-actions">
			<button class="btn btn-primary" id="save-settings-btn">
				<span class="icon-checkmark"></span> Kaydet
			</button>
		</div>
	</div>
</div>

