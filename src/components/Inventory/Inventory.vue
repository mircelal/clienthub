<template>
	<div class="inventory-container">
		<!-- Categories Management -->
		<Categories v-if="showCategories" @close="showCategories = false" />

		<!-- Warehouses Management -->
		<Warehouses v-else-if="showWarehouses" @close="showWarehouses = false" />

		<!-- POS Screen -->
		<POS v-else-if="showPOS" @close="showPOS = false" :items="items" :clients="clients" />

		<!-- Main Inventory View -->
		<template v-else>
			<!-- Left: List -->
			<div class="inventory-list-pane" :class="{ 'mobile-hidden': isMobile && selectedItem }">
				<InventoryList
					:items="filteredItems"
					:loading="loading"
					:current-filter="currentFilter"
					:search-query="searchQuery"
					:selected-item="selectedItem"
					@filter="handleFilter"
					@update:searchQuery="searchQuery = $event"
					@select="handleSelect"
					@add="handleAdd"
					@manage-categories="handleManageCategories"
					@manage-warehouses="handleManageWarehouses"
					@open-pos="handleOpenPOS"
				/>
			</div>

			<!-- Right: Detail -->
			<div class="inventory-detail-pane" v-if="selectedItem || (!isMobile && !loading)">
				<div v-if="!selectedItem" class="no-selection-placeholder">
					<PackageVariant :size="64" />
					<p>{{ translate('domaincontrol', 'Select an item to view details') }}</p>
				</div>
				
				<InventoryDetail
					v-else
					:item="selectedItem"
					:categories="categories"
					:warehouses="warehouses"
					@edit="handleEdit"
					@delete="handleDelete"
					@action="handleAction"
					@back="selectedItem = null"
				/>
			</div>
		</template>
	</div>
	
	<!-- Add/Edit Modal -->
	<InventoryItemModal
		:open="showModal"
		:item="modalItem"
		:categories="categories"
		:warehouses="warehouses"
		@close="showModal = false"
		@save="handleSave"
	/>
</template>

<script>
import api from '../../services/api'
import InventoryList from './InventoryList.vue'
import InventoryDetail from './InventoryDetail.vue'
import InventoryItemModal from './InventoryItemModal.vue'
import Categories from './Categories.vue'
import Warehouses from './Warehouses.vue'
import POS from './POS.vue'
import PackageVariant from 'vue-material-design-icons/PackageVariant.vue'

export default {
	name: 'Inventory',
	components: {
		InventoryList,
		InventoryDetail,
		InventoryItemModal,
		Categories,
		Warehouses,
		POS,
		PackageVariant,
	},
	data() {
		return {
			loading: false,
			showModal: false,
			items: [],
			categories: [],
			warehouses: [], 
			searchQuery: '',
			currentFilter: 'all',
			selectedItem: null,
			modalItem: null, // Separate item for modal editing to avoid direct mutation
			isMobile: window.innerWidth < 768,
			showCategories: false,
			showWarehouses: false,
			showPOS: false,
			clients: [],
		}
	},
    // ... computed ...
    computed: {
		filteredItems() {
			let result = this.items

			// Apply Status Filter
			if (this.currentFilter !== 'all') {
				result = result.filter(item => item.status === this.currentFilter)
			}

			// Apply Search
			if (this.searchQuery) {
				const query = this.searchQuery.toLowerCase()
				result = result.filter(item => 
					item.name.toLowerCase().includes(query) ||
					(item.sku && item.sku.toLowerCase().includes(query)) ||
					(item.serialNumber && item.serialNumber.toLowerCase().includes(query))
				)
			}

			return result
		},
	},
	mounted() {
		this.fetchData()
		window.addEventListener('resize', this.handleResize)
	},
	beforeUnmount() {
		window.removeEventListener('resize', this.handleResize)
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
		async fetchData() {
			this.loading = true
			try {
				const [itemsResponse, categoriesResponse, warehousesResponse, clientsResponse] = await Promise.all([
					api.inventory.getAll(),
					api.inventoryCategories.getAll(),
					api.inventoryWarehouses.getAll(),
					api.clients.getAll().catch(() => ({ data: [] })), // Clients optional
				])
				this.items = itemsResponse.data || []
				this.categories = categoriesResponse.data || []
				this.warehouses = warehousesResponse.data || []
				this.clients = clientsResponse.data || []
			} catch (e) {
				console.error('Failed to fetch inventory', e)
				if (e.response) {
					console.error('Error Status:', e.response.status)
					console.error('Error Data:', e.response.data)
				}
			} finally {
				this.loading = false
			}
		},
		handleFilter(filter) {
			this.currentFilter = filter
		},
		handleSelect(item) {
			this.selectedItem = item
		},
		async selectItem(itemId) {
			// First try to find in existing items
			let item = this.items.find(i => i.id === itemId)
			
			// If not found, fetch from API
			if (!item) {
				try {
					const response = await api.inventory.get(itemId)
					item = response.data
					// Add to items list if not already there
					if (!this.items.find(i => i.id === itemId)) {
						this.items.push(item)
					}
				} catch (e) {
					console.error('Failed to fetch item', e)
					return
				}
			}
			
			if (item) {
				this.selectedItem = item
			}
		},
		handleAdd() {
			this.modalItem = null
			this.showModal = true
		},
		handleEdit(item) {
			this.modalItem = item
			this.showModal = true
		},
		async handleSave(formData, pendingImages = []) {
			try {
				let savedItem
				if (this.modalItem) {
					// Update
					const response = await api.inventory.update(this.modalItem.id, formData)
					savedItem = response.data
					// Update local list
					const index = this.items.findIndex(i => i.id === this.modalItem.id)
					if (index !== -1) {
						this.items[index] = savedItem
					}
					// Update selected item if it's the one being edited
					if (this.selectedItem && this.selectedItem.id === this.modalItem.id) {
						this.selectedItem = savedItem
					}
				} else {
					// Create
					const response = await api.inventory.create(formData)
					savedItem = response.data
					// Add to local list
					this.items.push(savedItem)
					// Select the new item immediately
					this.selectedItem = savedItem
					
					// Upload pending images after item is created
					if (pendingImages && pendingImages.length > 0 && savedItem.id) {
						try {
							let primarySet = false
							for (const file of pendingImages) {
								const imageResponse = await api.inventoryImages.upload(savedItem.id, file)
								// Set first image as primary
								if (!primarySet && imageResponse.data && imageResponse.data.id) {
									await api.inventoryImages.setPrimary(imageResponse.data.id)
									primarySet = true
								}
							}
							// Reload item to get updated images
							const updatedResponse = await api.inventory.get(savedItem.id)
							const updatedItem = updatedResponse.data
							const index = this.items.findIndex(i => i.id === savedItem.id)
							if (index !== -1) {
								this.items[index] = updatedItem
							}
							if (this.selectedItem && this.selectedItem.id === savedItem.id) {
								this.selectedItem = updatedItem
							}
						} catch (e) {
							console.error('Failed to upload pending images', e)
							// Don't show error to user as item was saved successfully
						}
					}
				}
				this.showModal = false
			} catch (e) {
				console.error('Failed to save item', e)
				console.error('Error details:', e.response?.data)
				alert(this.translate('domaincontrol', 'Error saving item'))
			}
		},
        // ... (remaining methods: handleDelete, handleAction, handleResize)
		async handleDelete(item) {
			if (confirm(this.translate('domaincontrol', 'Are you sure you want to delete this item?'))) {
				try {
					await api.inventory.delete(item.id)
					this.items = this.items.filter(i => i.id !== item.id)
					this.selectedItem = null
				} catch (e) {
					console.error('Failed to delete item', e)
					alert(this.translate('domaincontrol', 'Error deleting item'))
				}
			}
		},
		handleAction(actionType) {
			console.log('Action:', actionType)
			// Handle Rent/Return actions
		},
		handleResize() {
			this.isMobile = window.innerWidth < 768
		},
		handleManageCategories() {
			this.showCategories = true
			this.showWarehouses = false
		},
		handleManageWarehouses() {
			this.showCategories = false
			this.showWarehouses = true
			this.showPOS = false
		},
		handleOpenPOS() {
			this.showCategories = false
			this.showWarehouses = false
			this.showPOS = true
		},
	},
}
</script>

<style scoped>
.inventory-container {
	display: flex;
	height: 100vh; /* Or calculated height */
	overflow: hidden;
	background: var(--color-background-dark);
}

.inventory-list-pane {
	width: 350px;
	flex-shrink: 0;
	height: 100%;
	background: var(--color-main-background);
	z-index: 2;
}

.inventory-detail-pane {
	flex: 1;
	height: 100%;
	overflow: hidden;
	background: var(--color-background-dark);
	position: relative;
}

.no-selection-placeholder {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	height: 100%;
	color: var(--color-text-maxcontrast);
	gap: 16px;
}

/* Mobile Responsive */
@media (max-width: 768px) {
	.inventory-list-pane {
		width: 100%;
	}
	
	.mobile-hidden {
		display: none;
	}

	.inventory-detail-pane {
		width: 100%;
		position: absolute;
		top: 0;
		left: 0;
		background: var(--color-main-background);
	}
}
</style>
