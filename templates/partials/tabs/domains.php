<!-- Domains Tab -->
<div id="domains-tab" class="tab-content">
	<!-- Domain List View -->
	<div id="domains-list-view">
		<div class="domaincontrol-actions">
			<button class="btn btn-primary" id="add-domain-btn">
				<span class="icon-add"></span> Domain Ekle
			</button>
			<button class="btn btn-info" id="test-email-btn" title="Süresi yaklaşan domainler için test e-postası gönder">
				📧 E-posta Test Et
			</button>
		</div>
		<div id="domains-list" class="domaincontrol-list"></div>
	</div>
	
	<!-- Domain Detail View -->
	<div id="domain-detail-view" style="display: none;">
		<div class="detail-header">
			<button class="btn btn-back" id="back-to-domains-btn">← Geri</button>
			<h2 id="domain-detail-name"></h2>
			<div class="detail-actions">
				<button class="btn btn-success" id="domain-detail-extend-btn">Süreyi Uzat</button>
				<button class="btn btn-secondary" id="domain-detail-edit-btn">Düzenle</button>
				<button class="btn btn-danger" id="domain-detail-delete-btn">Sil</button>
			</div>
		</div>
		
		<div class="detail-content">
			<div class="detail-stats">
				<div class="stat-card">
					<div class="stat-card__icon">📅</div>
					<div class="stat-card__content">
						<div class="stat-card__label">Bitiş Tarihi</div>
						<div class="stat-card__value" id="domain-detail-expiry"></div>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-card__icon">⏳</div>
					<div class="stat-card__content">
						<div class="stat-card__label">Kalan Gün</div>
						<div class="stat-card__value" id="domain-detail-days-left"></div>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-card__icon">📊</div>
					<div class="stat-card__content">
						<div class="stat-card__label">Durum</div>
						<div class="stat-card__value" id="domain-detail-status"></div>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-card__icon">💰</div>
					<div class="stat-card__content">
						<div class="stat-card__label">Fiyat</div>
						<div class="stat-card__value" id="domain-detail-price"></div>
					</div>
				</div>
			</div>
			
			<div class="detail-info-grid">
				<div class="detail-info-card">
					<h3>Genel Bilgiler</h3>
					<table class="detail-table">
						<tr><td>Müşteri</td><td id="domain-detail-client"></td></tr>
						<tr><td>Registrar</td><td id="domain-detail-registrar"></td></tr>
						<tr><td>Kayıt Tarihi</td><td id="domain-detail-registration"></td></tr>
						<tr><td>Süre</td><td id="domain-detail-interval"></td></tr>
						<tr><td>Hosting</td><td id="domain-detail-hosting"></td></tr>
					</table>
				</div>
				
				<div class="detail-info-card">
					<h3>Panel Giriş Bilgileri</h3>
					<pre id="domain-detail-notes" class="detail-notes"></pre>
				</div>
			</div>
			
			<div class="detail-info-card">
				<h3>Bağlı Siteler</h3>
				<div id="domain-websites-list" class="mini-list"></div>
			</div>
			
			<div class="detail-history-card">
				<h3>📜 Uzatma Geçmişi</h3>
				<div id="domain-detail-history" class="history-list"></div>
			</div>
		</div>
	</div>
</div>

