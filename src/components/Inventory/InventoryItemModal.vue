<template>
	<NcModal v-if="open" @close="closeModal" :title="isEdit ? translate('domaincontrol', 'Edit Item') : translate('domaincontrol', 'Add New Item')">
		<div class="modal-content">
			<form @submit.prevent="saveItem">
				<!-- Basic Info -->
				<div class="form-group-row">
					<div class="form-group half-width">
						<label>{{ translate('domaincontrol', 'Item Name') }} *</label>
						<input type="text" v-model="form.name" required class="full-width" :placeholder="translate('domaincontrol', 'e.g. Dell XPS 15')" />
					</div>
					<div class="form-group half-width">
						<label>{{ translate('domaincontrol', 'SKU') }}</label>
						<input type="text" v-model="form.sku" class="full-width" :placeholder="translate('domaincontrol', 'Internal Code')" />
					</div>
				</div>

				<div class="form-group-row">
					<div class="form-group half-width">
						<label>{{ translate('domaincontrol', 'Status') }}</label>
						<select v-model="form.status" class="full-width">
							<option value="available">{{ translate('domaincontrol', 'Available') }}</option>
							<option value="rented">{{ translate('domaincontrol', 'Rented') }}</option>
							<option value="maintenance">{{ translate('domaincontrol', 'Maintenance') }}</option>
							<option value="broken">{{ translate('domaincontrol', 'Broken') }}</option>
							<option value="retired">{{ translate('domaincontrol', 'Retired') }}</option>
							<option value="sold">{{ translate('domaincontrol', 'Sold') }}</option>
						</select>
					</div>
					<div class="form-group half-width">
						<label>{{ translate('domaincontrol', 'Serial Number') }}</label>
						<input type="text" v-model="form.serialNumber" class="full-width" />
					</div>
				</div>

				<!-- Categorization -->
				<div class="form-group-row">
					<div class="form-group half-width">
						<label>{{ translate('domaincontrol', 'Category') }}</label>
						<select v-model="form.categoryId" class="full-width">
							<option :value="0">{{ translate('domaincontrol', 'Select Category (Optional)') }}</option>
							<option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
						</select>
					</div>
					<div class="form-group half-width">
						<label>{{ translate('domaincontrol', 'Warehouse') }}</label>
						<select v-model="form.warehouseId" class="full-width">
							<option :value="0">{{ translate('domaincontrol', 'Select Warehouse (Optional)') }}</option>
							<option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
						</select>
					</div>
				</div>

				<!-- Financials -->
				<div class="form-group-row">
					<div class="form-group third-width">
						<label>{{ translate('domaincontrol', 'Purchase Price') }}</label>
						<input type="number" step="0.01" v-model="form.purchasePrice" class="full-width" />
					</div>
					<div class="form-group third-width">
						<label>{{ translate('domaincontrol', 'Sale Price') }}</label>
						<input type="number" step="0.01" v-model="form.salePrice" class="full-width" />
					</div>
					<div class="form-group third-width">
						<label>{{ translate('domaincontrol', 'Rental Price') }}</label>
						<input type="number" step="0.01" v-model="form.rentalPrice" class="full-width" :placeholder="translate('domaincontrol', 'Per day/project')" />
					</div>
				</div>

				<!-- Stock Management -->
				<div class="form-group-row">
					<div class="form-group half-width">
						<label>{{ translate('domaincontrol', 'Quantity') }}</label>
						<input type="number" step="1" v-model.number="form.quantity" class="full-width" min="0" />
						<small class="form-hint">{{ translate('domaincontrol', 'Current stock quantity') }}</small>
					</div>
					<div class="form-group half-width">
						<label>{{ translate('domaincontrol', 'Minimum Quantity') }}</label>
						<input type="number" step="1" v-model.number="form.minQuantity" class="full-width" min="0" />
						<small class="form-hint">{{ translate('domaincontrol', 'Alert when stock falls below this level') }}</small>
					</div>
				</div>

				<div class="form-group">
					<label>{{ translate('domaincontrol', 'Description') }}</label>
					<textarea v-model="form.description" class="full-width" rows="3"></textarea>
				</div>

				<!-- Images Section -->
				<div class="form-group">
					<label>{{ translate('domaincontrol', 'Images') }}</label>
					<div class="images-section">
						<div class="images-upload-area">
							<input
								ref="fileInput"
								type="file"
								accept="image/*"
								multiple
								style="display: none"
								@change="handleFileSelect"
							/>
							<NcButton type="secondary" @click.prevent="$refs.fileInput.click()" :disabled="uploadingImages">
								<template #icon>
									<Upload v-if="!uploadingImages" :size="18" />
									<Refresh v-else :size="18" class="spin-animation" />
								</template>
								{{ uploadingImages ? translate('domaincontrol', 'Uploading...') : translate('domaincontrol', 'Upload Images') }}
							</NcButton>
						</div>
						<div v-if="images.length > 0" class="images-grid">
							<div
								v-for="(img, index) in images"
								:key="img.id || index"
								class="image-item"
								:class="{ 'primary': img.isPrimary }"
							>
								<div class="image-preview">
									<img :src="getImageUrl(img.filePath)" :alt="`Image ${index + 1}`" />
									<div v-if="img.isPrimary" class="primary-badge">
										{{ translate('domaincontrol', 'Primary') }}
									</div>
								</div>
								<div class="image-actions">
									<NcButton
										v-if="!img.isPrimary"
										type="tertiary"
										@click="setPrimaryImage(img.id)"
										:disabled="saving"
									>
										{{ translate('domaincontrol', 'Set Primary') }}
									</NcButton>
									<NcButton
										type="tertiary"
										@click="deleteImage(img.id, index)"
										:disabled="saving"
									>
										<template #icon>
											<TrashCan :size="16" />
										</template>
									</NcButton>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="form-actions">
					<NcButton @click="closeModal" type="tertiary">{{ translate('domaincontrol', 'Cancel') }}</NcButton>
					<NcButton native-type="submit" type="primary" :disabled="saving">
						{{ saving ? translate('domaincontrol', 'Saving...') : translate('domaincontrol', 'Save Item') }}
					</NcButton>
				</div>
			</form>
		</div>
	</NcModal>
</template>

<script>
import { NcModal, NcButton } from '@nextcloud/vue'
import Upload from 'vue-material-design-icons/Upload.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import TrashCan from 'vue-material-design-icons/TrashCan.vue'
import api from '../../services/api'
import { generateUrl } from '@nextcloud/router'

export default {
	name: 'InventoryItemModal',
	components: {
		NcModal,
		NcButton,
		Upload,
		Refresh,
		TrashCan,
	},
	props: {
		open: {
			type: Boolean,
			default: false,
		},
		item: {
			type: Object,
			default: null,
		},
		categories: {
			type: Array,
			default: () => [],
		},
		warehouses: {
			type: Array,
			default: () => [],
		},
	},
	emits: ['close', 'save'],
		data() {
			return {
				saving: false,
				uploadingImages: false,
				images: [],
				pendingImages: [], // Images to upload after item is saved
				form: {
					name: '',
					sku: '',
					categoryId: 0,
					warehouseId: 0,
					status: 'available',
					serialNumber: '',
					purchasePrice: 0,
					salePrice: 0,
					rentalPrice: 0,
					description: '',
					imagePath: '',
					quantity: 0,
					minQuantity: 0,
				},
			}
		},
	computed: {
		isEdit() {
			return !!this.item
		},
	},
	watch: {
			open(val) {
			if (val) {
				this.initForm()
				if (this.item && this.item.id) {
					this.loadImages()
				} else {
					this.images = []
					this.pendingImages = []
				}
			}
		},
	},
	methods: {
		translate(appId, text, vars) {
			try {
				if (typeof window !== 'undefined') {
					if (typeof OC !== 'undefined' && OC.L10n && typeof OC.L10n.translate === 'function') {
						const translated = OC.L10n.translate(appId, text, vars || {})
						if (translated && translated !== text) return translated
					}
					if (typeof window.t === 'function') {
						const translated = window.t(appId, text, vars || {})
						if (translated && translated !== text) return translated
					}
				}
			} catch (e) { console.warn(e) }
			return text
		},
		initForm() {
			if (this.item) {
				this.form = { ...this.item }
			} else {
				this.form = {
					name: '',
					sku: '',
					categoryId: 0,
					warehouseId: 0,
					status: 'available',
					serialNumber: '',
					purchasePrice: 0,
					salePrice: 0,
					rentalPrice: 0,
					description: '',
					imagePath: '',
					quantity: 0,
					minQuantity: 0,
				}
			}
		},
		async loadImages() {
			if (!this.item || !this.item.id) return
			try {
				const response = await api.inventoryImages.getAll(this.item.id)
				this.images = response.data || []
			} catch (e) {
				console.error('Failed to load images', e)
				this.images = []
			}
		},
		getImageUrl(filePath) {
			if (!filePath) return ''
			// If it's a data URL (pending image), return as is
			if (filePath.startsWith('data:')) {
				return filePath
			}
			// Use Nextcloud's preview API for images
			const image = this.images.find(img => img.filePath === filePath)
			if (image && image.fileId) {
				return generateUrl(`/core/preview?fileId=${image.fileId}&x=300&y=300`)
			}
			// Fallback to direct file path
			return generateUrl(`/apps/files/?dir=/${encodeURIComponent(filePath)}`)
		},
		async handleFileSelect(event) {
			const files = Array.from(event.target.files)
			if (files.length === 0) return

			// If editing existing item, upload immediately
			if (this.item && this.item.id) {
				this.uploadingImages = true
				try {
					for (const file of files) {
						const response = await api.inventoryImages.upload(this.item.id, file)
						this.images.push(response.data)
					}
					event.target.value = ''
				} catch (e) {
					console.error('Failed to upload image', e)
					alert(this.translate('domaincontrol', 'Error uploading image'))
				} finally {
					this.uploadingImages = false
				}
			} else {
				// For new items, store files temporarily
				for (const file of files) {
					this.pendingImages.push(file)
					// Create preview
					const reader = new FileReader()
					reader.onload = (e) => {
						this.images.push({
							id: 'pending_' + Date.now() + '_' + Math.random(),
							filePath: e.target.result,
							isPrimary: this.images.length === 0,
							file: file, // Store file reference
						})
					}
					reader.readAsDataURL(file)
				}
				event.target.value = ''
			}
		},
		async setPrimaryImage(imageId) {
			// For pending images (new items), just update local state
			if (imageId.toString().startsWith('pending_')) {
				this.images = this.images.map(img => ({
					...img,
					isPrimary: img.id === imageId
				}))
				return
			}
			
			// For existing images, update on server
			try {
				await api.inventoryImages.setPrimary(imageId)
				// Update local images array
				this.images = this.images.map(img => ({
					...img,
					isPrimary: img.id === imageId
				}))
			} catch (e) {
				console.error('Failed to set primary image', e)
				alert(this.translate('domaincontrol', 'Error setting primary image'))
			}
		},
		async deleteImage(imageId, index) {
			if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this image?'))) {
				return
			}
			
			// If it's a pending image (new item), just remove from arrays
			if (imageId.toString().startsWith('pending_')) {
				// Get the image before removing it
				const image = this.images[index]
				// Remove from images array
				this.images.splice(index, 1)
				// Remove corresponding file from pendingImages
				if (image && image.file) {
					const fileIndex = this.pendingImages.findIndex(f => f === image.file)
					if (fileIndex !== -1) {
						this.pendingImages.splice(fileIndex, 1)
					}
				}
				return
			}
			
			// For existing images, delete from server
			try {
				await api.inventoryImages.delete(imageId)
				this.images.splice(index, 1)
			} catch (e) {
				console.error('Failed to delete image', e)
				alert(this.translate('domaincontrol', 'Error deleting image'))
			}
		},
		closeModal() {
			this.$emit('close')
		},
		async saveItem() {
			this.saving = true
			try {
				// Emit save event with form data and pending images
				await this.$emit('save', this.form, this.pendingImages)
				// Clear pending images after successful save
				this.pendingImages = []
			} finally {
				this.saving = false
			}
		},
	},
}
</script>

<style scoped>
.modal-content {
	padding: 20px;
}

.form-group {
	margin-bottom: 15px;
}

.form-group label {
	display: block;
	margin-bottom: 5px;
	font-weight: bold;
	color: var(--color-text-maxcontrast);
}

.full-width {
	width: 100%;
	padding: 8px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius);
	box-sizing: border-box;
	background-color: var(--color-main-background);
	color: var(--color-main-text);
}

.form-group-row {
	display: flex;
	gap: 15px;
}

.half-width {
	flex: 1;
}

.third-width {
	flex: 1;
}

.form-hint {
	display: block;
	margin-top: 4px;
	font-size: 11px;
	color: var(--color-text-maxcontrast);
	font-style: italic;
}

.form-actions {
	display: flex;
	justify-content: flex-end;
	gap: 10px;
	margin-top: 20px;
}

.images-section {
	margin-top: 10px;
}

.images-upload-area {
	margin-bottom: 15px;
}

.images-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
	gap: 15px;
	margin-top: 15px;
}

.image-item {
	position: relative;
	border: 2px solid var(--color-border);
	border-radius: var(--border-radius);
	overflow: hidden;
	background: var(--color-background-dark);
}

.image-item.primary {
	border-color: var(--color-primary-element);
}

.image-preview {
	position: relative;
	width: 100%;
	height: 120px;
	overflow: hidden;
	display: flex;
	align-items: center;
	justify-content: center;
	background: var(--color-background-dark);
}

.image-preview img {
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.primary-badge {
	position: absolute;
	top: 5px;
	right: 5px;
	background: var(--color-primary-element);
	color: white;
	padding: 2px 6px;
	border-radius: 4px;
	font-size: 10px;
	font-weight: 600;
	text-transform: uppercase;
}

.image-actions {
	display: flex;
	gap: 5px;
	padding: 8px;
	background: var(--color-main-background);
	justify-content: center;
}

.image-actions button {
	flex: 1;
	font-size: 11px;
	padding: 4px 8px;
}

.spin-animation {
	animation: spin 1s linear infinite;
}

@keyframes spin {
	100% { transform: rotate(360deg); }
}
</style>
