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

