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

