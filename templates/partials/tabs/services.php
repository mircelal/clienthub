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

