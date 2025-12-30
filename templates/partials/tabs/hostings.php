<!-- Hostings Tab -->
<div id="hostings-tab" class="tab-content">
	<!-- Hosting List View -->
	<div id="hostings-list-view">
		<div class="domaincontrol-actions">
			<div class="search-container">
				<input type="text" id="hosting-search-input" class="search-input" placeholder="Hosting ara...">
			</div>
			<button class="btn btn-secondary" id="manage-hosting-packages-btn">
				Paketleri Yönet
			</button>
			<button class="btn btn-primary" id="add-hosting-btn">
				Hosting Ekle
			</button>
		</div>
		<div id="hostings-list" class="domaincontrol-list"></div>
	</div>
	
	<!-- Hosting Packages View -->
	<div id="hosting-packages-view" style="display: none;">
		<div class="domaincontrol-actions">
			<button class="btn btn-back" id="back-to-hostings-list-btn">Geri</button>
			<button class="btn btn-primary" id="add-hosting-package-btn">
				Paket Ekle
			</button>
		</div>
		<div id="hosting-packages-list" class="hosting-packages-grid"></div>
	</div>
	
	<!-- Hosting Detail View -->
	<div id="hosting-detail-view" style="display: none;">
		<div class="detail-header">
			<button class="btn btn-back" id="back-to-hostings-btn">← Geri</button>
			<h2 id="hosting-detail-name"></h2>
			<div class="detail-actions">
				<button class="btn btn-success" id="hosting-detail-pay-btn">Ödeme Ekle</button>
				<button class="btn btn-secondary" id="hosting-detail-edit-btn">Düzenle</button>
				<button class="btn btn-danger" id="hosting-detail-delete-btn">Sil</button>
			</div>
		</div>
		
		<div class="detail-content">
			<div class="detail-stats">
				<div class="stat-card">
					<div class="stat-card__content">
						<div class="stat-card__label">Sonraki Ödeme</div>
						<div class="stat-card__value" id="hosting-detail-expiry"></div>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-card__content">
						<div class="stat-card__label">Kalan Gün</div>
						<div class="stat-card__value" id="hosting-detail-days-left"></div>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-card__content">
						<div class="stat-card__label">Fiyat</div>
						<div class="stat-card__value" id="hosting-detail-price"></div>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-card__content">
						<div class="stat-card__label">Sunucu Tipi</div>
						<div class="stat-card__value" id="hosting-detail-server-type"></div>
					</div>
				</div>
			</div>
			
			<div class="detail-info-grid">
				<div class="detail-info-card">
					<h3>Genel Bilgiler</h3>
					<table class="detail-table">
						<tr><td>Müşteri</td><td id="hosting-detail-client"></td></tr>
						<tr><td>Paket</td><td id="hosting-detail-plan"></td></tr>
						<tr><td>Sunucu IP</td><td id="hosting-detail-ip"></td></tr>
						<tr><td>Başlangıç</td><td id="hosting-detail-start"></td></tr>
						<tr><td>Son Ödeme</td><td id="hosting-detail-last-payment"></td></tr>
					</table>
				</div>
				
				<div class="detail-info-card">
					<h3>Panel Giriş Bilgileri</h3>
					<p id="hosting-detail-panel-url" style="margin-bottom: 8px;"></p>
					<pre id="hosting-detail-panel-notes" class="detail-notes"></pre>
				</div>
			</div>
			
			<div class="detail-info-grid">
				<div class="detail-info-card">
					<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
						<h3 style="margin: 0;">Bağlı Domainler</h3>
						<button id="add-domain-to-hosting-btn" class="btn btn-primary" style="padding: 6px 12px; font-size: 13px;">Domain Bağla</button>
					</div>
					<div id="hosting-domains-list" class="mini-list"></div>
				</div>
				<div class="detail-info-card">
					<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
						<h3 style="margin: 0;">Bağlı Websiteler</h3>
						<button id="add-website-to-hosting-btn" class="btn btn-primary" style="padding: 6px 12px; font-size: 13px;">Site Bağla</button>
					</div>
					<div id="hosting-websites-list" class="mini-list"></div>
				</div>
			</div>
			
			<div class="detail-history-card">
				<h3>Ödeme Geçmişi</h3>
				<div id="hosting-detail-payments" class="history-list"></div>
			</div>
		</div>
	</div>
</div>

