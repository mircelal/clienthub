<!-- Project Share Modal -->
<div id="project-share-modal" class="modal">
	<div class="modal-content">
		<div class="modal-header">
			<h3>Projeyi Paylaş</h3>
			<span class="modal-close" data-modal="project-share-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="project-share-form">
				<div class="form-group">
					<label for="share-user-search">Kullanıcı Ara</label>
					<input type="text" id="share-user-search" class="form-control" placeholder="Kullanıcı adı veya e-posta ile ara...">
				</div>
				<div class="form-group">
					<label for="share-user-select">Kullanıcı Seç</label>
					<select id="share-user-select" name="sharedWithUserId" required class="form-control">
						<option value="">Kullanıcı seçin...</option>
					</select>
				</div>
				<div class="form-group">
					<label for="share-permission-level">İzin Seviyesi</label>
					<select id="share-permission-level" name="permissionLevel" required class="form-control">
						<option value="read">Sadece Görüntüleme (Read)</option>
						<option value="write">Düzenleme (Write)</option>
					</select>
					<small class="text-muted" style="font-size: 11px; margin-top: 4px; display: block;">
						Read: Projeyi görüntüleyebilir, görevleri tamamlayabilir<br>
						Write: Projeyi düzenleyebilir, görev oluşturabilir/düzenleyebilir
					</small>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-modal="project-share-modal">İptal</button>
					<button type="submit" class="btn btn-primary">Paylaş</button>
				</div>
			</form>
		</div>
	</div>
</div>

