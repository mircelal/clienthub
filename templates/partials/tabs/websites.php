<!-- Websites Tab -->
<div id="websites-tab" class="tab-content">
	<!-- Website List View -->
	<div id="websites-list-view">
		<div class="domaincontrol-actions">
			<button class="btn btn-primary" id="add-website-btn">
				<span class="icon-add"></span> Website Ekle
			</button>
		</div>
		<div id="websites-list" class="domaincontrol-list"></div>
	</div>
	
	<!-- Website Detail View -->
	<div id="website-detail-view" style="display: none;">
		<div class="detail-header">
			<button class="btn btn-back" id="back-to-websites-btn">← Geri</button>
			<h2 id="website-detail-name"></h2>
			<div class="detail-actions">
				<button class="btn btn-secondary" id="website-detail-edit-btn">Düzenle</button>
				<button class="btn btn-danger" id="website-detail-delete-btn">Sil</button>
			</div>
		</div>
		
		<div class="detail-content">
			<div class="detail-stats">
				<div class="stat-card">
					<div class="stat-card__icon">📦</div>
					<div class="stat-card__content">
						<div class="stat-card__label">Yazılım</div>
						<div class="stat-card__value" id="website-detail-software"></div>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-card__icon">📌</div>
					<div class="stat-card__content">
						<div class="stat-card__label">Versiyon</div>
						<div class="stat-card__value" id="website-detail-version"></div>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-card__icon">📊</div>
					<div class="stat-card__content">
						<div class="stat-card__label">Durum</div>
						<div class="stat-card__value" id="website-detail-status"></div>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-card__icon">📅</div>
					<div class="stat-card__content">
						<div class="stat-card__label">Kurulum</div>
						<div class="stat-card__value" id="website-detail-install-date"></div>
					</div>
				</div>
			</div>
			
			<div class="detail-info-grid">
				<div class="detail-info-card">
					<h3>Genel Bilgiler</h3>
					<table class="detail-table">
						<tr><td>Müşteri</td><td id="website-detail-client"></td></tr>
						<tr><td>Domain</td><td id="website-detail-domain"></td></tr>
						<tr><td>Hosting</td><td id="website-detail-hosting"></td></tr>
						<tr><td>URL</td><td id="website-detail-url"></td></tr>
					</table>
				</div>
				
				<div class="detail-info-card">
					<h3>Admin Panel Bilgileri</h3>
					<p id="website-detail-admin-url" style="margin-bottom: 8px;"></p>
					<pre id="website-detail-admin-notes" class="detail-notes"></pre>
				</div>
			</div>
			
			<div class="detail-info-grid">
				<div class="detail-info-card">
					<h3>Bağlı Domain</h3>
					<div id="website-domain-info" class="mini-list"></div>
				</div>
				<div class="detail-info-card">
					<h3>Bağlı Hosting</h3>
					<div id="website-hosting-info" class="mini-list"></div>
				</div>
			</div>
			
			<div class="detail-info-card">
				<h3>Notlar</h3>
				<div id="website-detail-notes" class="rich-text-content"></div>
			</div>
			
			<div class="detail-info-card">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
					<h3 style="margin: 0;">📁 Dosyalar</h3>
					<button class="btn btn-primary btn-sm" id="website-upload-file-btn">📤 Dosya Yükle</button>
				</div>
				<input type="file" id="website-file-input" multiple style="display: none;">
				<div id="website-files-list" class="files-list"></div>
			</div>
		</div>
	</div>
</div>

