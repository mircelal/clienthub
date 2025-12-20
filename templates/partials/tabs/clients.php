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

