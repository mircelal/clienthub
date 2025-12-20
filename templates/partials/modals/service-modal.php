<!-- Service Type Modal -->
<div id="service-type-modal" class="modal">
	<div class="modal-content">
		<div class="modal-header">
			<h3 id="service-type-modal-title">Hizmet Türü Ekle</h3>
			<span class="modal-close" data-modal="service-type-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="service-type-form">
				<input type="hidden" id="service-type-id" name="id">
				<div class="form-group">
					<label for="service-type-name">Hizmet Adı *</label>
					<input type="text" id="service-type-name" name="name" required class="form-control" placeholder="Örn: Bakım, SEO">
				</div>
				<div class="form-group">
					<label for="service-type-description">Açıklama</label>
					<textarea id="service-type-description" name="description" class="form-control" rows="2"></textarea>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="service-type-price">Varsayılan Fiyat</label>
						<input type="number" id="service-type-price" name="defaultPrice" step="0.01" class="form-control">
					</div>
					<div class="form-group">
						<label for="service-type-currency">Para Birimi</label>
						<select id="service-type-currency" name="defaultCurrency" class="form-control">
							<option value="USD">$ USD</option>
							<option value="EUR">€ EUR</option>
							<option value="TRY">₺ TRY</option>
							<option value="AZN">₼ AZN</option>
						</select>
					</div>
					<div class="form-group">
						<label for="service-type-interval">Yenileme Periyodu</label>
						<select id="service-type-interval" name="renewalInterval" class="form-control">
							<option value="one-time">🔄 Tek Seferlik</option>
							<option value="monthly">Aylık</option>
							<option value="quarterly">3 Aylık</option>
							<option value="yearly">Yıllık</option>
						</select>
					</div>
				</div>
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="service-type-modal">İptal</button>
					<button type="submit" class="btn btn-primary">Kaydet</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Service Modal -->
<div id="service-modal" class="modal">
	<div class="modal-content modal-large">
		<div class="modal-header">
			<h3 id="service-modal-title">Hizmet Ekle</h3>
			<span class="modal-close" data-modal="service-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="service-form">
				<input type="hidden" id="service-id" name="id">
				<div class="form-row">
					<div class="form-group">
						<label for="service-client-id">Müşteri *</label>
						<select id="service-client-id" name="clientId" required class="form-control">
							<option value="">Müşteri Seçin</option>
						</select>
					</div>
					<div class="form-group">
						<label for="service-type-id">Hizmet Türü</label>
						<select id="service-type-select" name="serviceTypeId" class="form-control">
							<option value="">Seçin veya özel girin</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="service-name">Hizmet Adı *</label>
					<input type="text" id="service-name" name="name" required class="form-control">
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="service-price">Fiyat</label>
						<input type="number" id="service-price" name="price" step="0.01" class="form-control">
					</div>
					<div class="form-group">
						<label for="service-currency">Para Birimi</label>
						<select id="service-currency" name="currency" class="form-control">
							<option value="USD">$ USD</option>
							<option value="EUR">€ EUR</option>
							<option value="TRY">₺ TRY</option>
							<option value="AZN">₼ AZN</option>
						</select>
					</div>
					<div class="form-group">
						<label for="service-interval">Yenileme</label>
						<select id="service-interval" name="renewalInterval" class="form-control">
							<option value="one-time">🔄 Tek Seferlik</option>
							<option value="monthly">Aylık</option>
							<option value="quarterly">3 Aylık</option>
							<option value="yearly">Yıllık</option>
						</select>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="service-start-date">Başlangıç</label>
						<input type="date" id="service-start-date" name="startDate" class="form-control">
					</div>
					<div class="form-group">
						<label for="service-expiration-date">Bitiş</label>
						<input type="date" id="service-expiration-date" name="expirationDate" class="form-control">
					</div>
					<div class="form-group">
						<label for="service-status">Durum</label>
						<select id="service-status" name="status" class="form-control">
							<option value="active">Aktif</option>
							<option value="paused">Durduruldu</option>
							<option value="cancelled">İptal</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="service-notes">Notlar</label>
					<textarea id="service-notes" name="notes" class="form-control" rows="2"></textarea>
				</div>
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="service-modal">İptal</button>
					<button type="submit" class="btn btn-primary">Kaydet</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Service Types List Modal -->
<div id="service-types-modal" class="modal">
	<div class="modal-content modal-large">
		<div class="modal-header">
			<h3>Hizmet Türlerini Yönet</h3>
			<span class="modal-close" data-modal="service-types-modal">&times;</span>
		</div>
		<div class="modal-body">
			<div class="domaincontrol-actions">
				<button class="btn btn-primary" id="add-service-type-btn">
					<span class="icon-add"></span> Yeni Tür Ekle
				</button>
				<button class="btn btn-secondary" id="init-predefined-btn">
					📋 Hazır Türleri Ekle
				</button>
			</div>
			<div id="service-types-list" class="domaincontrol-list"></div>
		</div>
	</div>
</div>

