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
			<div class="detail-stats client-stats">
				<div class="stat-card client-stat-card">
					<div class="stat-card__icon client-stat-icon" style="background: rgba(59, 130, 246, 0.08);">🌐</div>
					<div class="stat-card__content">
						<div class="stat-card__label">Domainler</div>
						<div class="stat-card__value" id="client-detail-domains-count">0</div>
					</div>
				</div>
				<div class="stat-card client-stat-card">
					<div class="stat-card__icon client-stat-icon" style="background: rgba(16, 185, 129, 0.08);">🖥️</div>
					<div class="stat-card__content">
						<div class="stat-card__label">Hostingler</div>
						<div class="stat-card__value" id="client-detail-hostings-count">0</div>
					</div>
				</div>
				<div class="stat-card client-stat-card">
					<div class="stat-card__icon client-stat-icon" style="background: rgba(139, 92, 246, 0.08);">🌍</div>
					<div class="stat-card__content">
						<div class="stat-card__label">Websiteler</div>
						<div class="stat-card__value" id="client-detail-websites-count">0</div>
					</div>
				</div>
			</div>
			
			<div class="detail-info-grid">
				<div class="detail-info-card client-info-card">
					<h3 class="client-info-title">İletişim Bilgileri</h3>
					<table class="detail-table client-detail-table">
						<tr>
							<td class="table-label">E-posta</td>
							<td class="table-value" id="client-detail-email">-</td>
						</tr>
						<tr>
							<td class="table-label">Telefon</td>
							<td class="table-value" id="client-detail-phone">-</td>
						</tr>
						<tr>
							<td class="table-label">Kayıt Tarihi</td>
							<td class="table-value" id="client-detail-created">-</td>
						</tr>
					</table>
				</div>
				
				<div class="detail-info-card client-info-card">
					<h3 class="client-info-title">Notlar</h3>
					<div id="client-detail-notes" class="detail-notes client-notes">Not bulunmuyor</div>
				</div>
			</div>
			
		<div class="detail-services client-services-grid">
			<div class="detail-service-card client-service-card">
				<div class="service-card-header">
					<h3 class="service-card-title">🌐 Domainler</h3>
					<span class="service-card-count" id="client-domains-count-badge">0</span>
				</div>
				<div id="client-domains-list" class="mini-list client-mini-list"></div>
			</div>
			<div class="detail-service-card client-service-card">
				<div class="service-card-header">
					<h3 class="service-card-title">🖥️ Hostingler</h3>
					<span class="service-card-count" id="client-hostings-count-badge">0</span>
				</div>
				<div id="client-hostings-list" class="mini-list client-mini-list"></div>
			</div>
			<div class="detail-service-card client-service-card">
				<div class="service-card-header">
					<h3 class="service-card-title">🌍 Websiteler</h3>
					<span class="service-card-count" id="client-websites-count-badge">0</span>
				</div>
				<div id="client-websites-list" class="mini-list client-mini-list"></div>
			</div>
			<div class="detail-service-card client-service-card">
				<div class="service-card-header">
					<h3 class="service-card-title">🛠️ Hizmetler</h3>
					<span class="service-card-count" id="client-services-count-badge">0</span>
				</div>
				<div id="client-services-list" class="mini-list client-mini-list"></div>
			</div>
			<div class="detail-service-card client-service-card">
				<div class="service-card-header">
					<h3 class="service-card-title">📄 Faturalar</h3>
					<span class="service-card-count" id="client-invoices-count-badge">0</span>
				</div>
				<div id="client-invoices-list" class="mini-list client-mini-list"></div>
			</div>
			<div class="detail-service-card client-service-card">
				<div class="service-card-header">
					<h3 class="service-card-title">💳 Ödemeler</h3>
					<span class="service-card-count" id="client-payments-count-badge">0</span>
				</div>
				<div id="client-payments-list" class="mini-list client-mini-list"></div>
			</div>
		</div>
		</div>
	</div>
</div>

