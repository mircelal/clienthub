<?php
script('domaincontrol', 'domaincontrol-main');
style('domaincontrol', 'domaincontrol');
?>

<div id="domaincontrol-app" class="domaincontrol-container">
	<div class="domaincontrol-header">
		<h2>ClientHub</h2>
		<p class="domaincontrol-subtitle">Kapsamlı müşteri, proje ve iş yönetim sistemi</p>
	</div>

	<div class="domaincontrol-tabs">
		<button class="tab-button active" data-tab="dashboard">📊 Dashboard</button>
		<button class="tab-button" data-tab="clients">👥 Müşteriler</button>
		<button class="tab-button" data-tab="domains">🌐 Domainler</button>
		<button class="tab-button" data-tab="hostings">🖥️ Hosting</button>
		<button class="tab-button" data-tab="websites">🌍 Websiteler</button>
		<button class="tab-button" data-tab="services">🛠️ Hizmetler</button>
		<button class="tab-button" data-tab="invoices">📄 Faturalar</button>
		<button class="tab-button" data-tab="projects">📁 Projeler</button>
		<button class="tab-button" data-tab="tasks">✅ Görevler</button>
	</div>

	<!-- Dashboard Tab -->
	<div id="dashboard-tab" class="tab-content active">
		<!-- Ana İstatistikler -->
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
			<div class="stat-card stat-card--purple">
				<div class="stat-card__icon">📁</div>
				<div class="stat-card__content">
					<div class="stat-card__value" id="stat-projects">0</div>
					<div class="stat-card__label">Aktif Projeler</div>
				</div>
			</div>
			<div class="stat-card stat-card--teal">
				<div class="stat-card__icon">✅</div>
				<div class="stat-card__content">
					<div class="stat-card__value" id="stat-tasks">0</div>
					<div class="stat-card__label">Bekleyen Görevler</div>
				</div>
			</div>
			<div class="stat-card stat-card--danger">
				<div class="stat-card__icon">📄</div>
				<div class="stat-card__content">
					<div class="stat-card__value" id="stat-unpaid-invoices">0</div>
					<div class="stat-card__label">Ödenmemiş Fatura</div>
				</div>
			</div>
			<div class="stat-card stat-card--gold">
				<div class="stat-card__icon">💰</div>
				<div class="stat-card__content">
					<div class="stat-card__value" id="stat-monthly-income">0</div>
					<div class="stat-card__label">Bu Ay Gelir</div>
				</div>
			</div>
		</div>

		<!-- Uyarı Panelleri -->
		<div class="dashboard-alerts">
			<!-- Geciken Ödemeler -->
			<div class="alert-panel alert-panel--danger">
				<div class="alert-panel__header">
					<h4>🚨 Geciken Ödemeler</h4>
					<span class="alert-panel__count" id="overdue-count">0</span>
				</div>
				<div class="alert-panel__body" id="overdue-invoices-list">
					<p class="empty-message">Geciken ödeme yok</p>
				</div>
			</div>

			<!-- Yaklaşan Ödemeler -->
			<div class="alert-panel alert-panel--warning">
				<div class="alert-panel__header">
					<h4>⏰ Yaklaşan Ödemeler (30 gün)</h4>
					<span class="alert-panel__count" id="upcoming-count">0</span>
				</div>
				<div class="alert-panel__body" id="upcoming-payments-list">
					<p class="empty-message">Yaklaşan ödeme yok</p>
				</div>
			</div>

			<!-- Yaklaşan Görevler -->
			<div class="alert-panel alert-panel--info">
				<div class="alert-panel__header">
					<h4>📋 Yaklaşan Görevler (7 gün)</h4>
					<span class="alert-panel__count" id="upcoming-tasks-count">0</span>
				</div>
				<div class="alert-panel__body" id="upcoming-tasks-list">
					<p class="empty-message">Yaklaşan görev yok</p>
				</div>
			</div>
		</div>

		<!-- Hızlı İşlemler -->
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
				<button class="btn btn-success" id="quick-add-invoice">
					<span class="icon-add"></span> Fatura Oluştur
				</button>
				<button class="btn btn-info" id="quick-add-payment">
					<span class="icon-add"></span> Ödeme Ekle
				</button>
				<button class="btn btn-purple" id="quick-add-project">
					<span class="icon-add"></span> Proje Ekle
				</button>
				<button class="btn btn-teal" id="quick-add-task">
					<span class="icon-add"></span> Görev Ekle
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
				<div class="detail-service-card">
					<h3>🛠️ Hizmetler</h3>
					<div id="client-services-list" class="mini-list"></div>
				</div>
				<div class="detail-service-card">
					<h3>📄 Faturalar</h3>
					<div id="client-invoices-list" class="mini-list"></div>
				</div>
				<div class="detail-service-card">
					<h3>💳 Ödemeler</h3>
					<div id="client-payments-list" class="mini-list"></div>
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
		<!-- Hosting List View -->
		<div id="hostings-list-view">
			<div class="domaincontrol-actions">
				<button class="btn btn-primary" id="add-hosting-btn">
					<span class="icon-add"></span> Hosting Ekle
				</button>
			</div>
			<div id="hostings-list" class="domaincontrol-list"></div>
		</div>
		
		<!-- Hosting Detail View -->
		<div id="hosting-detail-view" style="display: none;">
			<div class="detail-header">
				<button class="btn btn-back" id="back-to-hostings-btn">← Geri</button>
				<h2 id="hosting-detail-name"></h2>
				<div class="detail-actions">
					<button class="btn btn-success" id="hosting-detail-pay-btn">💳 Ödeme Ekle</button>
					<button class="btn btn-secondary" id="hosting-detail-edit-btn">Düzenle</button>
					<button class="btn btn-danger" id="hosting-detail-delete-btn">Sil</button>
				</div>
			</div>
			
			<div class="detail-content">
				<div class="detail-stats">
					<div class="stat-card">
						<div class="stat-card__icon">📅</div>
						<div class="stat-card__content">
							<div class="stat-card__label">Sonraki Ödeme</div>
							<div class="stat-card__value" id="hosting-detail-expiry"></div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon">⏳</div>
						<div class="stat-card__content">
							<div class="stat-card__label">Kalan Gün</div>
							<div class="stat-card__value" id="hosting-detail-days-left"></div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon">💰</div>
						<div class="stat-card__content">
							<div class="stat-card__label">Fiyat</div>
							<div class="stat-card__value" id="hosting-detail-price"></div>
						</div>
					</div>
					<div class="stat-card">
						<div class="stat-card__icon">🖥️</div>
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
						<h3>🌐 Bağlı Domainler</h3>
						<div id="hosting-domains-list" class="mini-list"></div>
					</div>
					<div class="detail-info-card">
						<h3>🌍 Bağlı Websiteler</h3>
						<div id="hosting-websites-list" class="mini-list"></div>
					</div>
				</div>
				
				<div class="detail-history-card">
					<h3>💳 Ödeme Geçmişi</h3>
					<div id="hosting-detail-payments" class="history-list"></div>
				</div>
			</div>
		</div>
	</div>

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

	<!-- Services Tab -->
	<div id="services-tab" class="tab-content">
		<div id="services-list-view">
			<div class="domaincontrol-actions">
				<button class="btn btn-primary" id="add-service-btn">
					<span class="icon-add"></span> Hizmet Ekle
				</button>
				<button class="btn btn-secondary" id="manage-service-types-btn">
					⚙️ Hizmet Türlerini Yönet
				</button>
			</div>
			<div id="services-list" class="domaincontrol-list"></div>
		</div>
		
	<div id="service-detail-view" style="display: none;">
		<div class="detail-header">
			<button class="btn btn-back" id="back-to-services-btn">← Geri</button>
			<h2 id="service-detail-name"></h2>
			<div class="detail-actions">
				<button class="btn btn-success" id="service-create-invoice-btn">📄 Fatura Oluştur</button>
				<button class="btn btn-info" id="service-extend-btn">⏳ Süre Uzat</button>
				<button class="btn btn-secondary" id="service-detail-edit-btn">Düzenle</button>
				<button class="btn btn-danger" id="service-detail-delete-btn">Sil</button>
			</div>
		</div>
		<div class="detail-content">
			<div class="detail-stats">
				<div class="stat-card"><div class="stat-card__label">Müşteri</div><div class="stat-card__value" id="service-detail-client"></div></div>
				<div class="stat-card"><div class="stat-card__label">Fiyat</div><div class="stat-card__value" id="service-detail-price"></div></div>
				<div class="stat-card"><div class="stat-card__label">Bitiş Tarihi</div><div class="stat-card__value" id="service-detail-expiry"></div></div>
				<div class="stat-card"><div class="stat-card__label">Durum</div><div class="stat-card__value" id="service-detail-status"></div></div>
			</div>
			<div class="detail-info-grid">
				<div class="detail-info-card">
					<h3>Hizmet Bilgileri</h3>
					<table class="detail-table">
						<tr><td>Başlangıç Tarihi</td><td id="service-detail-start"></td></tr>
						<tr><td>Yenileme Periyodu</td><td id="service-detail-interval"></td></tr>
						<tr><td>Hizmet Türü</td><td id="service-detail-type"></td></tr>
					</table>
				</div>
				<div class="detail-info-card">
					<h3>Notlar</h3>
					<pre id="service-detail-notes" class="detail-notes"></pre>
				</div>
			</div>
		</div>
	</div>
	</div>

	<!-- Invoices Tab -->
	<div id="invoices-tab" class="tab-content">
		<div id="invoices-list-view">
			<div class="domaincontrol-actions">
				<button class="btn btn-primary" id="add-invoice-btn">
					<span class="icon-add"></span> Fatura Oluştur
				</button>
				<div class="filter-buttons">
					<button class="btn btn-filter active" data-filter="all">Tümü</button>
					<button class="btn btn-filter" data-filter="unpaid">Ödenmemiş</button>
					<button class="btn btn-filter" data-filter="overdue">Gecikmiş</button>
					<button class="btn btn-filter" data-filter="paid">Ödendi</button>
				</div>
			</div>
			<div id="invoices-list" class="domaincontrol-list"></div>
		</div>
		
	<div id="invoice-detail-view" style="display: none;">
		<div class="detail-header">
			<button class="btn btn-back" id="back-to-invoices-btn">← Geri</button>
			<h2 id="invoice-detail-number"></h2>
			<div class="detail-actions">
				<button class="btn btn-success" id="invoice-add-payment-btn">💳 Ödeme Ekle</button>
				<button class="btn btn-info" id="invoice-add-item-btn">+ Kalem Ekle</button>
				<button class="btn btn-secondary" id="invoice-detail-edit-btn">Düzenle</button>
				<button class="btn btn-danger" id="invoice-detail-delete-btn">Sil</button>
			</div>
		</div>
		<div class="detail-content">
			<div class="detail-stats">
				<div class="stat-card"><div class="stat-card__label">Müşteri</div><div class="stat-card__value" id="invoice-detail-client"></div></div>
				<div class="stat-card"><div class="stat-card__label">Toplam</div><div class="stat-card__value" id="invoice-detail-total"></div></div>
				<div class="stat-card"><div class="stat-card__label">Ödenen</div><div class="stat-card__value" id="invoice-detail-paid"></div></div>
				<div class="stat-card"><div class="stat-card__label">Kalan</div><div class="stat-card__value" id="invoice-detail-remaining"></div></div>
			</div>
			
			<!-- Payment Progress -->
			<div class="detail-info-card" style="margin-bottom: 20px;">
				<div id="invoice-payment-progress"></div>
			</div>
			
			<!-- Status Change Buttons -->
			<div class="invoice-status-actions" style="margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
				<button class="btn btn-sm btn-outline change-invoice-status-btn" data-status="draft">📝 Taslak</button>
				<button class="btn btn-sm btn-outline change-invoice-status-btn" data-status="sent">📤 Gönderildi</button>
				<button class="btn btn-sm btn-outline change-invoice-status-btn" data-status="paid">✅ Ödendi</button>
				<button class="btn btn-sm btn-outline change-invoice-status-btn" data-status="overdue">⚠️ Gecikmiş</button>
				<button class="btn btn-sm btn-outline change-invoice-status-btn" data-status="cancelled">❌ İptal</button>
			</div>
			
			<div class="detail-grid">
				<div class="detail-info-card">
					<h3>Fatura Bilgileri</h3>
					<p><strong>Düzenleme Tarihi:</strong> <span id="invoice-detail-issue-date"></span></p>
					<p><strong>Vade Tarihi:</strong> <span id="invoice-detail-due-date"></span></p>
					<p><strong>Durum:</strong> <span id="invoice-detail-status"></span></p>
					<p><strong>Notlar:</strong> <span id="invoice-detail-notes">-</span></p>
				</div>
				<div class="detail-info-card">
					<h3>Fatura Kalemleri</h3>
					<div id="invoice-detail-items"></div>
				</div>
			</div>
			<div class="detail-info-card">
				<h3>Ödeme Geçmişi</h3>
				<div id="invoice-detail-payments"></div>
			</div>
			
			<!-- Invoice Files Section -->
			<div class="detail-info-card">
				<h3>📎 Fatura Dosyaları</h3>
				<p class="text-muted" style="margin-bottom: 15px;">Faturaya ait belgeler, ödeme makbuzları, sözleşmeler ve diğer dosyalar</p>
				
				<div class="form-group" style="margin-bottom: 20px;">
					<label for="invoice-file-input">Dosya Yükle</label>
					<input type="file" id="invoice-file-input" multiple class="form-control" style="padding: 8px;">
					<button type="button" class="btn btn-primary" id="invoice-upload-files-btn" style="margin-top: 10px;">
						📤 Dosyaları Yükle
					</button>
				</div>
				
				<div id="invoice-files-list" class="file-list"></div>
			</div>
		</div>
	</div>
	</div>

	<!-- Projects Tab -->
	<div id="projects-tab" class="tab-content">
		<div id="projects-list-view">
			<div class="domaincontrol-actions">
				<button class="btn btn-primary" id="add-project-btn">
					<span class="icon-add"></span> Proje Ekle
				</button>
				<div class="filter-buttons">
					<button class="btn btn-filter active" data-filter="all">Tümü</button>
					<button class="btn btn-filter" data-filter="active">Aktif</button>
					<button class="btn btn-filter" data-filter="completed">Tamamlandı</button>
					<button class="btn btn-filter" data-filter="on_hold">Beklemede</button>
				</div>
			</div>
			<div id="projects-list" class="domaincontrol-list"></div>
		</div>
		
		<div id="project-detail-view" style="display: none;">
			<div class="detail-header">
				<button class="btn btn-back" id="back-to-projects-btn">← Geri</button>
				<h2 id="project-detail-name"></h2>
				<div class="detail-actions">
					<button class="btn btn-success" id="project-add-task-btn">✅ Görev Ekle</button>
					<button class="btn btn-info" id="project-add-item-btn">🔗 Öğe Bağla</button>
					<button class="btn btn-secondary" id="project-detail-edit-btn">Düzenle</button>
					<button class="btn btn-danger" id="project-detail-delete-btn">Sil</button>
				</div>
			</div>
			<div class="detail-content">
				<div class="detail-stats">
					<div class="stat-card"><div class="stat-card__label">Müşteri</div><div class="stat-card__value" id="project-detail-client"></div></div>
					<div class="stat-card"><div class="stat-card__label">Proje Türü</div><div class="stat-card__value" id="project-detail-type"></div></div>
					<div class="stat-card"><div class="stat-card__label">Durum</div><div class="stat-card__value" id="project-detail-status"></div></div>
					<div class="stat-card"><div class="stat-card__label">Başlangıç</div><div class="stat-card__value" id="project-detail-start"></div></div>
					<div class="stat-card"><div class="stat-card__label">Deadline</div><div class="stat-card__value" id="project-detail-deadline"></div></div>
					<div class="stat-card"><div class="stat-card__label">Bütçe</div><div class="stat-card__value" id="project-detail-budget"></div></div>
				</div>
				
				<div class="detail-grid">
					<div class="detail-info-card">
						<h3>📝 Proje Açıklaması</h3>
						<p id="project-detail-description"></p>
					</div>
					<div class="detail-info-card">
						<h3>📋 Notlar</h3>
						<p id="project-detail-notes"></p>
					</div>
				</div>
				
				<div class="detail-grid">
					<div class="detail-info-card">
						<h3>🔗 Bağlı Öğeler</h3>
						<p class="text-muted" style="font-size: 12px; margin-bottom: 10px;">Domain, hosting, website ve hizmetleri projeye bağlayın</p>
						<div id="project-linked-items"></div>
					</div>
					<div class="detail-info-card">
						<h3>💰 Finansal Bilgiler</h3>
						<div id="project-financials"></div>
					</div>
				</div>
				
				<div class="detail-info-card">
					<h3>✅ Görevler</h3>
					<div id="project-detail-tasks"></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Tasks Tab -->
	<div id="tasks-tab" class="tab-content">
		<div id="tasks-list-view">
			<div class="domaincontrol-actions">
				<button class="btn btn-primary" id="add-task-btn">
					<span class="icon-add"></span> Görev Ekle
				</button>
				<div class="filter-buttons">
					<button class="btn btn-filter active" data-filter="all">Tümü</button>
					<button class="btn btn-filter" data-filter="todo">Yapılacak</button>
					<button class="btn btn-filter" data-filter="in_progress">Devam Ediyor</button>
					<button class="btn btn-filter" data-filter="done">Tamamlandı</button>
				</div>
			</div>
			<div id="tasks-list" class="domaincontrol-list"></div>
		</div>
		
		<div id="task-detail-view" style="display: none;">
			<div class="detail-header">
				<button class="btn btn-back" id="back-to-tasks-btn">← Geri</button>
				<h2 id="task-detail-title"></h2>
				<div class="detail-actions">
					<button class="btn btn-success" id="task-toggle-btn">✅ Durumu Değiştir</button>
					<button class="btn btn-secondary" id="task-detail-edit-btn">Düzenle</button>
					<button class="btn btn-danger" id="task-detail-delete-btn">Sil</button>
				</div>
			</div>
			<div class="detail-content">
				<div class="detail-stats">
					<div class="stat-card"><div class="stat-card__label">Proje</div><div class="stat-card__value" id="task-detail-project"></div></div>
					<div class="stat-card"><div class="stat-card__label">Durum</div><div class="stat-card__value" id="task-detail-status"></div></div>
					<div class="stat-card"><div class="stat-card__label">Öncelik</div><div class="stat-card__value" id="task-detail-priority"></div></div>
					<div class="stat-card"><div class="stat-card__label">Bitiş Tarihi</div><div class="stat-card__value" id="task-detail-due-date"></div></div>
				</div>
				<div class="detail-info-card">
					<h3>Açıklama</h3>
					<p id="task-detail-description"></p>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Client Modal -->
<div id="client-modal" class="modal">
	<div class="modal-content">
		<div class="modal-header">
			<h3 id="client-modal-title">Müşteri Ekle</h3>
			<span class="modal-close" data-modal="client-modal">&times;</span>
		</div>
		<div class="modal-body">
			<div style="margin-bottom: 16px;">
				<button type="button" class="btn btn-info btn-sm" id="select-from-contacts-btn">📇 Kişilerden Seç</button>
			</div>
			<form id="client-form">
				<input type="hidden" id="client-id" name="id">
				<div class="form-group">
					<label for="client-name">Ad *</label>
					<input type="text" id="client-name" name="name" required class="form-control">
				</div>
				<div class="form-group">
					<label for="client-email">E-posta</label>
					<input type="email" id="client-email" name="email" class="form-control">
				</div>
				<div class="form-group">
					<label for="client-phone">Telefon</label>
					<input type="text" id="client-phone" name="phone" class="form-control">
				</div>
				<div class="form-group">
					<label for="client-notes">Notlar</label>
					<textarea id="client-notes" name="notes" class="form-control" rows="4"></textarea>
				</div>
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="client-modal">İptal</button>
					<button type="submit" class="btn btn-primary">Kaydet</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Contacts Selection Modal -->
<div id="contacts-modal" class="modal">
	<div class="modal-content modal-large">
		<div class="modal-header">
			<h3>Kişilerden Seç</h3>
			<span class="modal-close" data-modal="contacts-modal">&times;</span>
		</div>
		<div class="modal-body">
			<div class="form-group" style="margin-bottom: 16px;">
				<input type="text" id="contacts-search" class="form-control" placeholder="🔍 Kişi ara (ad, e-posta, telefon...)">
			</div>
			<div id="contacts-loading" style="text-align: center; padding: 20px;">
				<p>Kişiler yükleniyor...</p>
			</div>
			<div id="contacts-list" style="max-height: 500px; overflow-y: auto;"></div>
			<div id="contacts-empty" style="display: none; text-align: center; padding: 20px;">
				<p class="empty-message">Arama kriterinize uygun kişi bulunamadı</p>
			</div>
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

<!-- Website Modal -->
<div id="website-modal" class="modal">
	<div class="modal-content modal-large">
		<div class="modal-header">
			<h3 id="website-modal-title">Website Ekle</h3>
			<span class="modal-close" data-modal="website-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="website-form">
				<input type="hidden" id="website-id" name="id">
				<div class="form-row">
					<div class="form-group">
						<label for="website-name">Website Adı *</label>
						<input type="text" id="website-name" name="name" required class="form-control" placeholder="Örn: Müşteri Sitesi">
					</div>
					<div class="form-group">
						<label for="website-client-id">Müşteri *</label>
						<select id="website-client-id" name="clientId" required class="form-control">
							<option value="">Müşteri Seçin</option>
						</select>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="website-domain-id">Domain</label>
						<select id="website-domain-id" name="domainId" class="form-control">
							<option value="">Domain Seçin (opsiyonel)</option>
						</select>
					</div>
					<div class="form-group">
						<label for="website-hosting-id">Hosting</label>
						<select id="website-hosting-id" name="hostingId" class="form-control">
							<option value="">Hosting Seçin (opsiyonel)</option>
						</select>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="website-software">Yazılım</label>
						<input type="text" id="website-software" name="software" class="form-control" placeholder="WordPress, Laravel, Custom...">
					</div>
					<div class="form-group">
						<label for="website-version">Versiyon</label>
						<input type="text" id="website-version" name="version" class="form-control" placeholder="6.4.2">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="website-status">Durum</label>
						<select id="website-status" name="status" class="form-control">
							<option value="active">🟢 Aktif</option>
							<option value="maintenance">🟡 Bakımda</option>
							<option value="development">🔵 Geliştirmede</option>
							<option value="inactive">🔴 Pasif</option>
						</select>
					</div>
					<div class="form-group">
						<label for="website-installation-date">Kurulum Tarihi</label>
						<input type="date" id="website-installation-date" name="installationDate" class="form-control">
					</div>
				</div>
				<div class="form-group">
					<label for="website-url">Site URL</label>
					<input type="text" id="website-url" name="url" class="form-control" placeholder="https://example.com">
				</div>
				<div class="form-group">
					<label for="website-admin-url">Admin Panel URL</label>
					<input type="text" id="website-admin-url" name="adminUrl" class="form-control" placeholder="https://example.com/wp-admin">
				</div>
				<div class="form-group">
					<label for="website-admin-notes">Admin Giriş Bilgileri</label>
					<textarea id="website-admin-notes" name="adminNotes" class="form-control" rows="2" placeholder="Kullanıcı: admin&#10;Şifre: ****"></textarea>
				</div>
				<div class="form-group">
					<label for="website-notes">Genel Notlar</label>
					<div class="rich-text-editor-wrapper">
						<div class="rich-text-toolbar">
							<button type="button" class="toolbar-btn" data-command="bold" title="Kalın">
								<strong>B</strong>
							</button>
							<button type="button" class="toolbar-btn" data-command="italic" title="İtalik">
								<em>I</em>
							</button>
							<button type="button" class="toolbar-btn" data-command="underline" title="Altı Çizili">
								<u>U</u>
							</button>
							<button type="button" class="toolbar-btn" data-command="insertEmoji" title="Emoji">
								😊
							</button>
							<button type="button" class="toolbar-btn" data-command="insertLineBreak" title="Satır">
								↵
							</button>
						</div>
						<div id="website-notes" class="rich-text-editor" contenteditable="true" data-placeholder="Diğer notlar..."></div>
						<input type="hidden" id="website-notes-hidden" name="notes">
					</div>
				</div>
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="website-modal">İptal</button>
					<button type="submit" class="btn btn-primary">Kaydet</button>
				</div>
			</form>
		</div>
	</div>
</div>

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
						<input type="text" id="invoice-number" name="invoiceNumber" class="form-control" placeholder="Otomatik oluşturulur">
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
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="invoice-modal">İptal</button>
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
						<input type="number" id="payment-amount" name="amount" step="0.01" required class="form-control">
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
					<input type="text" id="payment-reference" name="reference" class="form-control" placeholder="Dekont/Fiş no">
				</div>
				<div class="form-group">
					<label for="payment-notes">Notlar</label>
					<textarea id="payment-notes" name="notes" class="form-control" rows="2"></textarea>
				</div>
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="payment-modal">İptal</button>
					<button type="submit" class="btn btn-primary">Kaydet</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Project Modal -->
<div id="project-modal" class="modal">
	<div class="modal-content modal-large">
		<div class="modal-header">
			<h3 id="project-modal-title">Proje Ekle</h3>
			<span class="modal-close" data-modal="project-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="project-form">
				<input type="hidden" id="project-id" name="id">
				<div class="form-row">
					<div class="form-group">
						<label for="project-client-id">Müşteri *</label>
						<select id="project-client-id" name="clientId" required class="form-control">
							<option value="">Müşteri Seçin</option>
						</select>
					</div>
					<div class="form-group">
						<label for="project-type">Proje Türü</label>
						<select id="project-type" name="projectType" class="form-control">
							<option value="">Seçin</option>
							<option value="website">🌐 Web Sitesi</option>
							<option value="ecommerce">🛒 E-Ticaret</option>
							<option value="webapp">📱 Web Uygulaması</option>
							<option value="theme">🎨 Tema / Modül</option>
							<option value="design">🖼️ Grafik Tasarım</option>
							<option value="server">🖥️ Sunucu Kurulumu</option>
							<option value="email">📧 Mail Kurulumu</option>
							<option value="hosting">☁️ Hosting</option>
							<option value="device">📟 Cihaz Kurulumu</option>
							<option value="support">🛠️ Teknik Destek</option>
							<option value="seo">📈 SEO / Pazarlama</option>
							<option value="other">📦 Diğer</option>
						</select>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="project-name">Proje Adı *</label>
						<input type="text" id="project-name" name="name" required class="form-control">
					</div>
					<div class="form-group">
						<label for="project-status">Durum</label>
						<select id="project-status" name="status" class="form-control">
							<option value="active">Aktif</option>
							<option value="on_hold">Beklemede</option>
							<option value="completed">Tamamlandı</option>
							<option value="cancelled">İptal</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="project-description">Açıklama</label>
					<textarea id="project-description" name="description" class="form-control" rows="3" placeholder="Proje detayları, gereksinimler, özel notlar..."></textarea>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="project-start-date">Başlangıç</label>
						<input type="date" id="project-start-date" name="startDate" class="form-control">
					</div>
					<div class="form-group">
						<label for="project-deadline">Deadline</label>
						<input type="date" id="project-deadline" name="deadline" class="form-control">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="project-budget">Bütçe</label>
						<input type="number" id="project-budget" name="budget" step="0.01" class="form-control" placeholder="0.00">
					</div>
					<div class="form-group">
						<label for="project-currency">Para Birimi</label>
						<select id="project-currency" name="currency" class="form-control">
							<option value="USD">$ USD</option>
							<option value="EUR">€ EUR</option>
							<option value="TRY">₺ TRY</option>
							<option value="AZN">₼ AZN</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="project-notes">Notlar</label>
					<textarea id="project-notes" name="notes" class="form-control" rows="2" placeholder="Ek bilgiler, anlaşma detayları..."></textarea>
				</div>
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="project-modal">İptal</button>
					<button type="submit" class="btn btn-primary">Kaydet</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Project Item Modal -->
<div id="project-item-modal" class="modal">
	<div class="modal-content">
		<div class="modal-header">
			<h3>Projeye Öğe Bağla</h3>
			<span class="modal-close" data-modal="project-item-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="project-item-form">
				<input type="hidden" id="project-item-project-id" name="projectId">
				<div class="form-group">
					<label for="project-item-type">Öğe Türü *</label>
					<select id="project-item-type" name="itemType" required class="form-control">
						<option value="">Seçin</option>
						<option value="domain">Domain</option>
						<option value="hosting">Hosting</option>
						<option value="website">Website</option>
						<option value="service">Hizmet</option>
					</select>
				</div>
				<div class="form-group">
					<label for="project-item-id">Öğe *</label>
					<select id="project-item-select" name="itemId" required class="form-control">
						<option value="">Önce türü seçin</option>
					</select>
				</div>
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="project-item-modal">İptal</button>
					<button type="submit" class="btn btn-primary">Bağla</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Task Modal -->
<div id="task-modal" class="modal">
	<div class="modal-content">
		<div class="modal-header">
			<h3 id="task-modal-title">Görev Ekle</h3>
			<span class="modal-close" data-modal="task-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="task-form">
				<input type="hidden" id="task-id" name="id">
				<div class="form-row">
					<div class="form-group">
						<label for="task-project-id">Proje</label>
						<select id="task-project-id" name="projectId" class="form-control">
							<option value="">Proje Seçin (opsiyonel)</option>
						</select>
					</div>
					<div class="form-group">
						<label for="task-client-id">Müşteri</label>
						<select id="task-client-id" name="clientId" class="form-control">
							<option value="">Müşteri Seçin (opsiyonel)</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="task-title">Başlık *</label>
					<input type="text" id="task-title" name="title" required class="form-control">
				</div>
				<div class="form-group">
					<label for="task-description">Açıklama</label>
					<textarea id="task-description" name="description" class="form-control" rows="3"></textarea>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label for="task-status">Durum</label>
						<select id="task-status" name="status" class="form-control">
							<option value="todo">Yapılacak</option>
							<option value="in_progress">Devam Ediyor</option>
							<option value="done">Tamamlandı</option>
						</select>
					</div>
					<div class="form-group">
						<label for="task-priority">Öncelik</label>
						<select id="task-priority" name="priority" class="form-control">
							<option value="low">Düşük</option>
							<option value="medium">Orta</option>
							<option value="high">Yüksek</option>
						</select>
					</div>
					<div class="form-group">
						<label for="task-due-date">Bitiş Tarihi</label>
						<input type="date" id="task-due-date" name="dueDate" class="form-control">
					</div>
				</div>
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="task-modal">İptal</button>
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
					<input type="text" id="invoice-item-description" name="description" required class="form-control" placeholder="Örn: Yıllık domain yenileme">
				</div>
				
				<div class="form-row">
					<div class="form-group">
						<label for="invoice-item-quantity">Miktar</label>
						<input type="number" id="invoice-item-quantity" name="quantity" value="1" min="1" class="form-control">
					</div>
					<div class="form-group">
						<label for="invoice-item-unit-price">Birim Fiyat</label>
						<input type="number" id="invoice-item-unit-price" name="unitPrice" step="0.01" class="form-control">
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
						<input type="number" id="invoice-item-discount" name="discount" step="0.01" class="form-control" placeholder="0">
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
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="invoice-item-modal">İptal</button>
					<button type="submit" class="btn btn-primary">Kaydet</button>
				</div>
			</form>
		</div>
	</div>
</div>

