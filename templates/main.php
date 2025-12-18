<?php
script('domaincontrol', 'domaincontrol-main');
style('domaincontrol', 'domaincontrol');
?>

<div id="domaincontrol-app" class="domaincontrol-container">
	<div class="domaincontrol-header">
		<h2>Domain Control</h2>
		<p class="domaincontrol-subtitle">Manage your clients, domains, hosting, and websites</p>
	</div>

	<div class="domaincontrol-tabs">
		<button class="tab-button active" data-tab="dashboard">📊 Dashboard</button>
		<button class="tab-button" data-tab="clients">👥 Müşteriler</button>
		<button class="tab-button" data-tab="domains">🌐 Domainler</button>
		<button class="tab-button" data-tab="hostings">🖥️ Hosting</button>
		<button class="tab-button" data-tab="websites">🌍 Websiteler</button>
	</div>

	<!-- Dashboard Tab -->
	<div id="dashboard-tab" class="tab-content active">
		<div class="dashboard-grid">
			<div class="stat-card stat-card--primary">
				<div class="stat-card__icon">👥</div>
				<div class="stat-card__content">
					<div class="stat-card__value" id="stat-clients">0</div>
					<div class="stat-card__label">Müşteriler</div>
				</div>
			</div>
			<div class="stat-card stat-card--success">
				<div class="stat-card__icon">🌐</div>
				<div class="stat-card__content">
					<div class="stat-card__value" id="stat-domains">0</div>
					<div class="stat-card__label">Domainler</div>
				</div>
			</div>
			<div class="stat-card stat-card--info">
				<div class="stat-card__icon">🖥️</div>
				<div class="stat-card__content">
					<div class="stat-card__value" id="stat-hostings">0</div>
					<div class="stat-card__label">Hosting</div>
				</div>
			</div>
			<div class="stat-card stat-card--warning">
				<div class="stat-card__icon">🌍</div>
				<div class="stat-card__content">
					<div class="stat-card__value" id="stat-websites">0</div>
					<div class="stat-card__label">Websiteler</div>
				</div>
			</div>
		</div>

		<div class="dashboard-actions">
			<h3>Hızlı İşlemler</h3>
			<div class="button-group">
				<button class="btn btn-primary" id="quick-add-client">
					<span class="icon-add"></span> Müşteri Ekle
				</button>
				<button class="btn btn-secondary" id="quick-add-domain">
					<span class="icon-add"></span> Domain Ekle
				</button>
				<button class="btn btn-secondary" id="quick-add-hosting">
					<span class="icon-add"></span> Hosting Ekle
				</button>
				<button class="btn btn-secondary" id="quick-add-website">
					<span class="icon-add"></span> Website Ekle
				</button>
			</div>
		</div>

		<div class="dashboard-recent">
			<h3>Son Eklenenler</h3>
			<div id="recent-clients" class="domaincontrol-list"></div>
		</div>
	</div>

	<!-- Clients Tab -->
	<div id="clients-tab" class="tab-content">
		<!-- Client List View -->
		<div id="clients-list-view">
			<div class="domaincontrol-actions">
				<button class="btn btn-primary" id="add-client-btn">
					<span class="icon-add"></span> Müşteri Ekle
				</button>
			</div>
			<div id="clients-list" class="domaincontrol-list"></div>
		</div>
		
		<!-- Client Detail View -->
		<div id="client-detail-view" style="display: none;">
			<div class="detail-header">
				<button class="btn btn-back" id="back-to-clients-btn">← Geri</button>
				<h2 id="client-detail-name"></h2>
				<div class="detail-actions">
					<button class="btn btn-secondary" id="client-detail-edit-btn">Düzenle</button>
					<button class="btn btn-danger" id="client-detail-delete-btn">Sil</button>
				</div>
			</div>
			
			<div class="detail-content">
				<div class="detail-stats">
					<div class="stat-card">
						<div class="stat-card__icon">🌐</div>
						<div class="stat-card__content">
							<div class="stat-card__label">Domainler</div>
							<div class="stat-card__value" id="client-detail-domains-count">0</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon">🖥️</div>
						<div class="stat-card__content">
							<div class="stat-card__label">Hostingler</div>
							<div class="stat-card__value" id="client-detail-hostings-count">0</div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon">🌍</div>
						<div class="stat-card__content">
							<div class="stat-card__label">Websiteler</div>
							<div class="stat-card__value" id="client-detail-websites-count">0</div>
						</div>
					</div>
				</div>
				
				<div class="detail-info-grid">
					<div class="detail-info-card">
						<h3>İletişim Bilgileri</h3>
						<table class="detail-table">
							<tr><td>E-posta</td><td id="client-detail-email"></td></tr>
							<tr><td>Telefon</td><td id="client-detail-phone"></td></tr>
							<tr><td>Kayıt Tarihi</td><td id="client-detail-created"></td></tr>
						</table>
					</div>
					
					<div class="detail-info-card">
						<h3>Notlar</h3>
						<pre id="client-detail-notes" class="detail-notes"></pre>
					</div>
				</div>
				
				<div class="detail-services">
					<div class="detail-service-card">
						<h3>🌐 Domainler</h3>
						<div id="client-domains-list" class="mini-list"></div>
					</div>
					<div class="detail-service-card">
						<h3>🖥️ Hostingler</h3>
						<div id="client-hostings-list" class="mini-list"></div>
					</div>
					<div class="detail-service-card">
						<h3>🌍 Websiteler</h3>
						<div id="client-websites-list" class="mini-list"></div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Domains Tab -->
	<div id="domains-tab" class="tab-content">
		<!-- Domain List View -->
		<div id="domains-list-view">
			<div class="domaincontrol-actions">
				<button class="btn btn-primary" id="add-domain-btn">
					<span class="icon-add"></span> Domain Ekle
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
						</table>
					</div>
					
					<div class="detail-info-card">
						<h3>Panel Giriş Bilgileri</h3>
						<pre id="domain-detail-notes" class="detail-notes"></pre>
					</div>
				</div>
				
				<div class="detail-history-card">
					<h3>📜 Uzatma Geçmişi</h3>
					<div id="domain-detail-history" class="history-list"></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Hostings Tab -->
	<div id="hostings-tab" class="tab-content">
		<div class="domaincontrol-actions">
			<button class="btn btn-primary" id="add-hosting-btn">
				<span class="icon-add"></span> Add Hosting
			</button>
		</div>
		<div id="hostings-list" class="domaincontrol-list"></div>
	</div>

	<!-- Websites Tab -->
	<div id="websites-tab" class="tab-content">
		<div class="domaincontrol-actions">
			<button class="btn btn-primary" id="add-website-btn">
				<span class="icon-add"></span> Add Website
			</button>
		</div>
		<div id="websites-list" class="domaincontrol-list"></div>
	</div>
</div>

<!-- Client Modal -->
<div id="client-modal" class="modal">
	<div class="modal-content">
		<div class="modal-header">
			<h3 id="client-modal-title">Add Client</h3>
			<span class="modal-close" data-modal="client-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="client-form">
				<input type="hidden" id="client-id" name="id">
				<div class="form-group">
					<label for="client-name">Name *</label>
					<input type="text" id="client-name" name="name" required class="form-control">
				</div>
				<div class="form-group">
					<label for="client-email">Email</label>
					<input type="email" id="client-email" name="email" class="form-control">
				</div>
				<div class="form-group">
					<label for="client-phone">Phone</label>
					<input type="text" id="client-phone" name="phone" class="form-control">
				</div>
				<div class="form-group">
					<label for="client-notes">Notes</label>
					<textarea id="client-notes" name="notes" class="form-control" rows="4"></textarea>
				</div>
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="client-modal">Cancel</button>
					<button type="submit" class="btn btn-primary">Save</button>
				</div>
			</form>
		</div>
	</div>
</div>

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

<!-- Hosting Modal -->
<div id="hosting-modal" class="modal">
	<div class="modal-content">
		<div class="modal-header">
			<h3 id="hosting-modal-title">Add Hosting</h3>
			<span class="modal-close" data-modal="hosting-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="hosting-form">
				<input type="hidden" id="hosting-id" name="id">
				<div class="form-group">
					<label for="hosting-client-id">Client *</label>
					<select id="hosting-client-id" name="clientId" required class="form-control"></select>
				</div>
				<div class="form-group">
					<label for="hosting-provider">Provider *</label>
					<input type="text" id="hosting-provider" name="provider" required class="form-control">
				</div>
				<div class="form-group">
					<label for="hosting-plan">Plan</label>
					<input type="text" id="hosting-plan" name="plan" class="form-control">
				</div>
				<div class="form-group">
					<label for="hosting-server-ip">Server IP</label>
					<input type="text" id="hosting-server-ip" name="serverIp" class="form-control">
				</div>
				<div class="form-group">
					<label for="hosting-installation-date">Installation Date</label>
					<input type="date" id="hosting-installation-date" name="installationDate" class="form-control">
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="hosting-price">Price</label>
						<input type="number" id="hosting-price" name="price" step="0.01" class="form-control">
					</div>
					<div class="form-group">
						<label for="hosting-renewal-interval">Renewal Interval</label>
						<select id="hosting-renewal-interval" name="renewalInterval" class="form-control">
							<option value="monthly" selected>Monthly</option>
							<option value="yearly">Yearly</option>
							<option value="biennial">Biennial</option>
						</select>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label>
							<input type="checkbox" id="hosting-renewal-reminder" name="renewalReminder" checked>
							Enable Renewal Reminder
						</label>
					</div>
					<div class="form-group">
						<label for="hosting-reminder-days">Reminder Days Before</label>
						<input type="number" id="hosting-reminder-days" name="reminderDays" value="30" class="form-control">
					</div>
				</div>
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="hosting-modal">Cancel</button>
					<button type="submit" class="btn btn-primary">Save</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Website Modal -->
<div id="website-modal" class="modal">
	<div class="modal-content">
		<div class="modal-header">
			<h3 id="website-modal-title">Add Website</h3>
			<span class="modal-close" data-modal="website-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="website-form">
				<input type="hidden" id="website-id" name="id">
				<div class="form-group">
					<label for="website-client-id">Client *</label>
					<select id="website-client-id" name="clientId" required class="form-control"></select>
				</div>
				<div class="form-group">
					<label for="website-domain-id">Domain</label>
					<select id="website-domain-id" name="domainId" class="form-control">
						<option value="">None</option>
					</select>
				</div>
				<div class="form-group">
					<label for="website-hosting-id">Hosting</label>
					<select id="website-hosting-id" name="hostingId" class="form-control">
						<option value="">None</option>
					</select>
				</div>
				<div class="form-group">
					<label for="website-software">Software</label>
					<input type="text" id="website-software" name="software" class="form-control" placeholder="e.g., WordPress, Drupal, Custom">
				</div>
				<div class="form-group">
					<label for="website-installation-date">Installation Date</label>
					<input type="date" id="website-installation-date" name="installationDate" class="form-control">
				</div>
				<div class="form-group">
					<label for="website-notes">Notes</label>
					<textarea id="website-notes" name="notes" class="form-control" rows="4"></textarea>
				</div>
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="website-modal">Cancel</button>
					<button type="submit" class="btn btn-primary">Save</button>
				</div>
			</form>
		</div>
	</div>
</div>

