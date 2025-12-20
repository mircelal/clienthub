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

