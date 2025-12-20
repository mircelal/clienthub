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

