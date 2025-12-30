<template>
	<div class="app-content-wrapper">
			<!-- Left: List -->
			<div class="app-content-list">
				<div class="app-content-list-header">
					<div class="back-button-wrapper">
						<NcButton 
							type="tertiary" 
							@click="handleBack"
							:title="translate('domaincontrol', 'Back to Inventory')">
							<template #icon>
								<ArrowLeft :size="18" />
							</template>
						</NcButton>
					</div>
					
					<div class="search-wrapper">
						<div class="search-wrapper-inner">
							<Magnify :size="20" class="search-icon" />
							<input
								type="text"
								v-model="searchQuery"
								:placeholder="translate('domaincontrol', 'Search categories...')"
								class="search-input"
							/>
						</div>
					</div>
					
					<div class="header-actions">
						<NcButton 
							type="secondary" 
							:wide="true"
							@click="handleAdd">
							<template #icon>
								<Plus :size="20" />
							</template>
							{{ translate('domaincontrol', 'Add Category') }}
						</NcButton>
					</div>
				</div>

				<div class="app-content-list-wrapper">
					<div v-if="loading" class="loading-container">
						<Refresh :size="32" class="spin-animation" />
					</div>
					
					<div v-else-if="filteredCategories.length === 0" class="empty-list">
						<div class="empty-text">{{ translate('domaincontrol', 'No categories found') }}</div>
					</div>

					<ul v-else class="app-navigation-list">
						<template v-for="category in hierarchicalCategories" :key="category.id">
							<li 
								class="app-navigation-entry" 
								:class="{ 
									'active': selectedCategory && selectedCategory.id === category.id,
									'has-children': category.children && category.children.length > 0
								}" 
								:style="{ paddingLeft: (category.level * 20) + 'px' }"
								@click="handleSelect(category)"
							>
								<div class="app-navigation-entry-icon">
									<div class="avatar-circle category-avatar" :style="{ backgroundColor: getCategoryColor(category) }">
										<Folder :size="20" />
									</div>
								</div>
								<div class="app-navigation-entry-content">
									<div class="app-navigation-entry-name">{{ category.name }}</div>
									<div class="app-navigation-entry-details">
										<span v-if="category.itemCount !== undefined" class="item-count">
											{{ category.itemCount }} {{ translate('domaincontrol', 'items') }}
										</span>
										<span v-else-if="category.description">{{ category.description }}</span>
										<span v-else class="text-muted">{{ translate('domaincontrol', 'No description') }}</span>
									</div>
								</div>
							</li>
						</template>
					</ul>
				</div>
			</div>

			<!-- Right: Detail -->
			<div class="app-content-details" v-if="selectedCategory || !loading">
				<div v-if="!selectedCategory" class="no-selection-placeholder">
					<Folder :size="64" />
					<p>{{ translate('domaincontrol', 'Select a category to view details') }}</p>
				</div>
				
				<div v-else class="category-detail-container">
					<div class="category-header">
						<div class="category-header-top">
							<div class="category-profile-info">
								<div class="category-avatar-xl" :style="{ backgroundColor: getCategoryColor(selectedCategory) }">
									<Folder :size="32" />
								</div>
								<div class="category-profile-text">
									<h1 class="category-name">{{ selectedCategory.name }}</h1>
								</div>
							</div>
							<div class="category-header-actions">
								<NcActions :primary="true">
									<NcActionButton @click="handleEdit(selectedCategory)">
										<template #icon>
											<Pencil :size="20" />
										</template>
										{{ translate('domaincontrol', 'Edit') }}
									</NcActionButton>
									<NcActionSeparator />
									<NcActionButton @click="handleDelete(selectedCategory)">
										<template #icon>
											<TrashCan :size="20" />
										</template>
										{{ translate('domaincontrol', 'Delete') }}
									</NcActionButton>
								</NcActions>
							</div>
						</div>
					</div>

					<div class="category-detail-content">
						<!-- Statistics Card -->
						<div class="card stats-card" v-if="categoryStats">
							<h3>{{ translate('domaincontrol', 'Statistics') }}</h3>
							<div class="stats-grid">
								<div class="stat-item">
									<div class="stat-value">{{ categoryStats.itemCount || 0 }}</div>
									<div class="stat-label">{{ translate('domaincontrol', 'Total Items') }}</div>
								</div>
								<div class="stat-item">
									<div class="stat-value">{{ formatCurrency(categoryStats.totalPurchaseValue || 0) }}</div>
									<div class="stat-label">{{ translate('domaincontrol', 'Purchase Value') }}</div>
								</div>
								<div class="stat-item">
									<div class="stat-value">{{ formatCurrency(categoryStats.totalSaleValue || 0) }}</div>
									<div class="stat-label">{{ translate('domaincontrol', 'Sale Value') }}</div>
								</div>
								<div class="stat-item">
									<div class="stat-value">{{ categoryStats.totalQuantity || 0 }}</div>
									<div class="stat-label">{{ translate('domaincontrol', 'Total Quantity') }}</div>
								</div>
							</div>
						</div>

						<!-- Information Card -->
						<div class="card info-card">
							<h3>{{ translate('domaincontrol', 'Information') }}</h3>
							<div class="info-row">
								<span class="label">{{ translate('domaincontrol', 'Name') }}</span>
								<span class="value">{{ selectedCategory.name }}</span>
							</div>
							<div class="info-row">
								<span class="label">{{ translate('domaincontrol', 'Description') }}</span>
								<span class="value">{{ selectedCategory.description || '-' }}</span>
							</div>
							<div class="info-row">
								<span class="label">{{ translate('domaincontrol', 'Parent Category') }}</span>
								<span class="value">{{ getParentCategoryName(selectedCategory.parentId) }}</span>
							</div>
						</div>

						<!-- Items List -->
						<div class="card items-card" v-if="categoryItems.length > 0">
							<h3>{{ translate('domaincontrol', 'Items in this Category and Subcategories') }}</h3>
							<ul class="items-list">
								<li v-for="item in categoryItems" :key="item.id" class="item-entry" @click="handleItemClick(item)">
									<div class="item-info">
										<div class="item-name">{{ item.name }}</div>
										<div class="item-details">
											<span v-if="item.sku" class="item-sku">#{{ item.sku }}</span>
											<span v-if="item.salePrice" class="item-price">{{ formatCurrency(item.salePrice) }}</span>
											<span v-if="item.quantity !== undefined" class="item-quantity">Qty: {{ item.quantity }}</span>
										</div>
									</div>
									<div class="item-status">
										<span class="status-badge" :class="getStatusClass(item.status)">
											{{ getStatusText(item.status) }}
										</span>
									</div>
								</li>
							</ul>
						</div>
						<div v-else-if="loadingItems" class="card">
							<div class="loading-container">
								<Refresh :size="24" class="spin-animation" />
							</div>
						</div>
						<div v-else class="card">
							<p class="empty-text">{{ translate('domaincontrol', 'No items in this category') }}</p>
						</div>
					</div>
				</div>
			</div>
		
		<!-- Add/Edit Modal -->
		<CategoryModal
			:open="showModal"
			:category="modalCategory"
			:categories="categories"
			@close="showModal = false"
			@save="handleSave"
		/>
	</div>
</template>

<script>
import api from '../../services/api'
import { NcButton, NcActions, NcActionButton, NcActionSeparator } from '@nextcloud/vue'
import Folder from 'vue-material-design-icons/Folder.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCan from 'vue-material-design-icons/TrashCan.vue'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'
import CategoryModal from './CategoryModal.vue'

export default {
	name: 'Categories',
	emits: ['close'],
	components: {
		NcButton,
		NcActions,
		NcActionButton,
		NcActionSeparator,
		Folder,
		Plus,
		Magnify,
		Refresh,
		Pencil,
		TrashCan,
		ArrowLeft,
		CategoryModal,
	},
	data() {
		return {
			loading: false,
			loadingItems: false,
			showModal: false,
			categories: [],
			searchQuery: '',
			selectedCategory: null,
			modalCategory: null,
			categoryItems: [],
			categoryStats: null,
		}
	},
	computed: {
		filteredCategories() {
			if (!this.searchQuery) return this.categories
			const query = this.searchQuery.toLowerCase()
			return this.categories.filter(cat => 
				cat.name.toLowerCase().includes(query) ||
				(cat.description && cat.description.toLowerCase().includes(query))
			)
		},
		hierarchicalCategories() {
			const buildTree = (categories, parentId = 0, level = 0) => {
				const result = []
				for (const cat of categories) {
					if ((cat.parentId || 0) === parentId) {
						const category = { ...cat, level, children: [] }
						category.children = buildTree(categories, cat.id, level + 1)
						result.push(category)
					}
				}
				return result.sort((a, b) => a.name.localeCompare(b.name))
			}
			
			const flat = (tree) => {
				let result = []
				for (const node of tree) {
					result.push(node)
					if (node.children && node.children.length > 0) {
						result = result.concat(flat(node.children))
					}
				}
				return result
			}
			
			const tree = buildTree(this.filteredCategories)
			return flat(tree)
		},
	},
	watch: {
		selectedCategory(newVal) {
			if (newVal) {
				this.loadCategoryItems()
			} else {
				this.categoryItems = []
				this.categoryStats = null
			}
		},
	},
	mounted() {
		this.fetchData()
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
				const response = await api.inventoryCategories.getAll()
				this.categories = response.data || []
			} catch (e) {
				console.error('Failed to fetch categories', e)
			} finally {
				this.loading = false
			}
		},
		handleBack() {
			this.$emit('close')
		},
		handleSelect(category) {
			this.selectedCategory = category
		},
		getAllSubcategoryIds(categoryId) {
			const subcategoryIds = [categoryId]
			const findChildren = (parentId) => {
				for (const cat of this.categories) {
					if ((cat.parentId || 0) === parentId) {
						subcategoryIds.push(cat.id)
						findChildren(cat.id) // Recursive call for nested subcategories
					}
				}
			}
			findChildren(categoryId)
			return subcategoryIds
		},
		async loadCategoryItems() {
			if (!this.selectedCategory) return
			this.loadingItems = true
			try {
				// Get all subcategory IDs including the selected category itself
				const categoryIds = this.getAllSubcategoryIds(this.selectedCategory.id)
				
				// Load items for all categories (selected + subcategories)
				const allItemsPromises = categoryIds.map(categoryId => 
					api.inventory.byCategory(categoryId).catch(e => {
						console.error(`Failed to load items for category ${categoryId}`, e)
						return { data: [] }
					})
				)
				
				const responses = await Promise.all(allItemsPromises)
				
				// Combine all items and remove duplicates (in case an item appears in multiple categories)
				const itemsMap = new Map()
				responses.forEach(response => {
					const items = response.data || []
					items.forEach(item => {
						if (!itemsMap.has(item.id)) {
							itemsMap.set(item.id, item)
						}
					})
				})
				
				this.categoryItems = Array.from(itemsMap.values())
				this.calculateStats()
			} catch (e) {
				console.error('Failed to load category items', e)
				this.categoryItems = []
			} finally {
				this.loadingItems = false
			}
		},
		calculateStats() {
			if (!this.categoryItems || this.categoryItems.length === 0) {
				this.categoryStats = {
					itemCount: 0,
					totalPurchaseValue: 0,
					totalSaleValue: 0,
					totalQuantity: 0,
				}
				return
			}
			
			this.categoryStats = {
				itemCount: this.categoryItems.length,
				totalPurchaseValue: this.categoryItems.reduce((sum, item) => {
					const price = (item.purchasePrice || 0) * (item.quantity || 0)
					return sum + price
				}, 0),
				totalSaleValue: this.categoryItems.reduce((sum, item) => {
					const price = (item.salePrice || 0) * (item.quantity || 0)
					return sum + price
				}, 0),
				totalQuantity: this.categoryItems.reduce((sum, item) => sum + (item.quantity || 0), 0),
			}
		},
		formatCurrency(value) {
			return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value)
		},
		getStatusClass(status) {
			const classes = {
				available: 'status-success',
				rented: 'status-warning',
				maintenance: 'status-warning',
				broken: 'status-error',
				retired: 'status-neutral',
				sold: 'status-neutral',
			}
			return classes[status] || 'status-neutral'
		},
		getStatusText(status) {
			const texts = {
				available: this.translate('domaincontrol', 'Available'),
				rented: this.translate('domaincontrol', 'Rented'),
				maintenance: this.translate('domaincontrol', 'Maintenance'),
				broken: this.translate('domaincontrol', 'Broken'),
				retired: this.translate('domaincontrol', 'Retired'),
				sold: this.translate('domaincontrol', 'Sold'),
			}
			return texts[status] || status
		},
		handleItemClick(item) {
			// Navigate to inventory page and select the item
			if (window.DomainControl && typeof window.DomainControl.switchTab === 'function') {
				window.DomainControl.switchTab('inventory')
				// Use multiple nextTick and timeout to ensure component is ready
				this.$nextTick(() => {
					setTimeout(() => {
						if (window.DomainControl && typeof window.DomainControl.selectInventoryItem === 'function') {
							window.DomainControl.selectInventoryItem(item.id)
						}
					}, 100)
				})
			}
		},
		handleAdd() {
			this.modalCategory = null
			this.showModal = true
		},
		handleEdit(category) {
			this.modalCategory = category
			this.showModal = true
		},
		async handleSave(formData) {
			try {
				if (this.modalCategory) {
					// Update
					const response = await api.inventoryCategories.update(this.modalCategory.id, formData)
					const index = this.categories.findIndex(c => c.id === this.modalCategory.id)
					if (index !== -1) {
						this.categories[index] = response.data
					}
					if (this.selectedCategory && this.selectedCategory.id === this.modalCategory.id) {
						this.selectedCategory = response.data
					}
				} else {
					// Create
					const response = await api.inventoryCategories.create(formData)
					this.categories.push(response.data)
					this.selectedCategory = response.data
				}
				this.showModal = false
			} catch (e) {
				console.error('Failed to save category', e)
				alert(this.translate('domaincontrol', 'Error saving category'))
			}
		},
		async handleDelete(category) {
			if (!confirm(this.translate('domaincontrol', 'Are you sure you want to delete this category?'))) {
				return
			}
			try {
				await api.inventoryCategories.delete(category.id)
				this.categories = this.categories.filter(c => c.id !== category.id)
				if (this.selectedCategory && this.selectedCategory.id === category.id) {
					this.selectedCategory = null
				}
			} catch (e) {
				console.error('Failed to delete category', e)
				alert(this.translate('domaincontrol', 'Error deleting category'))
			}
		},
		getCategoryColor(category) {
			if (!category.name) return '#0082c9'
			const colors = ['#0082c9', '#46ba61', '#f0ad4e', '#e3322d', '#5bc0de', '#9b59b6', '#e67e22', '#3498db']
			let hash = 0
			for (let i = 0; i < category.name.length; i++) {
				hash = category.name.charCodeAt(i) + ((hash << 5) - hash)
			}
			return colors[Math.abs(hash) % colors.length]
		},
		getParentCategoryName(parentId) {
			if (!parentId || parentId === 0) return '-'
			const parent = this.categories.find(c => c.id === parentId)
			return parent ? parent.name : '-'
		},
	},
}
</script>

<style scoped>
.app-content-wrapper {
	display: flex;
	height: 100%;
	width: 100%;
	background-color: var(--color-main-background);
	overflow: hidden;
	color: var(--color-main-text);
}

.app-content-list {
	width: 300px;
	min-width: 300px;
	flex-shrink: 0;
	height: 100%;
	background: var(--color-main-background);
	border-right: 1px solid var(--color-border);
	display: flex;
	flex-direction: column;
	z-index: 50;
}

.app-content-list-header {
	padding: 0;
	display: flex;
	flex-direction: column;
	border-bottom: 1px solid var(--color-border);
}

.back-button-wrapper {
	padding: 10px 12px 12px 50px;
	border-bottom: 1px solid var(--color-border);
	background: var(--color-background-dark);
}

.search-wrapper {
	position: relative;
	padding: 11px 19px;
	border-bottom: 1px solid var(--color-border);
}

.search-wrapper-inner {
	margin-left: 25px;
}

.search-icon {
	position: absolute;
	left: 50px;
	top: 50%;
	transform: translateY(-50%);
	opacity: 0.5;
	pointer-events: none;
	color: var(--color-text-maxcontrast);
}

.search-input {
	width: 100%;
	padding: 8px 12px 8px 34px !important;
	border: 1px solid transparent !important;
	border-radius: 8px !important;
	background-color: var(--color-background-dark) !important;
	color: var(--color-main-text);
	box-sizing: border-box;
	transition: all 0.2s ease;
}

.search-input:focus {
	background-color: var(--color-main-background) !important;
	border-color: var(--color-primary-element) !important;
	outline: none;
	box-shadow: 0 0 0 2px var(--color-primary-element-light);
}

.header-actions {
	padding: 12px 16px;
	border-bottom: 1px solid var(--color-border);
}

.app-content-list-wrapper {
	flex: 1;
	overflow-y: auto;
}

.app-navigation-list {
	list-style: none;
	padding: 0;
	margin: 0;
}

.app-navigation-entry {
	display: flex;
	align-items: center;
	padding: 12px 15px;
	cursor: pointer;
	border-bottom: 1px solid var(--color-border);
	transition: background-color 0.15s ease;
}

.app-navigation-entry:hover {
	background-color: var(--color-background-hover);
}

.app-navigation-entry.active {
	background-color: var(--color-primary-element-light);
	border-left: 3px solid var(--color-primary-element);
}

.avatar-circle {
	width: 40px;
	height: 40px;
	border-radius: 8px;
	color: white;
	display: flex;
	align-items: center;
	justify-content: center;
}

.app-navigation-entry-content {
	margin-left: 12px;
	flex: 1;
	min-width: 0;
}

.app-navigation-entry-name {
	font-weight: 600;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	color: var(--color-main-text);
	font-size: 14px;
}

.app-navigation-entry-details {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
	opacity: 0.8;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	margin-top: 2px;
}

.text-muted {
	font-style: italic;
}

.app-content-details {
	flex: 1;
	height: 100%;
	overflow-y: auto;
	background: var(--color-background-hover);
	display: flex;
	flex-direction: column;
	min-width: 0;
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

.category-detail-container {
	display: flex;
	flex-direction: column;
	height: 100%;
	width: 100%;
}

.category-header {
	background-color: var(--color-main-background);
	padding: 25px 25px 0 25px;
	border-bottom: 1px solid var(--color-border);
	flex-shrink: 0;
}

.category-header-top {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 25px;
}

.category-profile-info {
	display: flex;
	align-items: center;
	gap: 20px;
}

.category-avatar-xl {
	width: 72px;
	height: 72px;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	color: white;
	box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.category-profile-text {
	display: flex;
	flex-direction: column;
}

.category-name {
	margin: 0;
	font-size: 24px;
	font-weight: bold;
	line-height: 1.2;
	color: var(--color-main-text);
}

.category-header-actions {
	display: flex;
	align-items: center;
	gap: 12px;
}

.category-detail-content {
	padding: 25px;
	width: 100%;
	max-width: none;
	box-sizing: border-box;
	flex: 1;
	overflow-y: auto;
	min-width: 0;
}

.card {
	background: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
	padding: 20px;
	margin-bottom: 24px;
	width: 100%;
	box-sizing: border-box;
}

.card h3 {
	margin-top: 0;
	margin-bottom: 16px;
	font-size: 16px;
	border-bottom: 1px solid var(--color-border);
	padding-bottom: 8px;
}

.info-row {
	display: flex;
	justify-content: space-between;
	padding: 8px 0;
	border-bottom: 1px solid var(--color-border-light);
}

.info-row:last-child {
	border-bottom: none;
}

.label {
	color: var(--color-text-maxcontrast);
}

.value {
	font-weight: 500;
}

.loading-container {
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 40px;
}

.spin-animation {
	animation: spin 1s linear infinite;
}

@keyframes spin {
	100% { transform: rotate(360deg); }
}

.empty-list {
	padding: 40px 20px;
	text-align: center;
}

.empty-text {
	color: var(--color-text-maxcontrast);
	font-style: italic;
}

.item-count {
	font-weight: 500;
	color: var(--color-primary-element);
}

.stats-card {
	margin-bottom: 24px;
}

.stats-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
	gap: 16px;
	margin-top: 16px;
}

.stat-item {
	text-align: center;
	padding: 12px;
	background: var(--color-background-dark);
	border-radius: var(--border-radius);
}

.stat-value {
	font-size: 20px;
	font-weight: bold;
	color: var(--color-main-text);
	margin-bottom: 4px;
}

.stat-label {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
}

.items-card {
	margin-top: 24px;
}

.items-list {
	list-style: none;
	padding: 0;
	margin: 0;
}

.item-entry {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 12px;
	border-bottom: 1px solid var(--color-border-light);
	cursor: pointer;
	transition: background-color 0.15s ease;
	border-radius: var(--border-radius);
	margin-bottom: 4px;
}

.item-entry:hover {
	background-color: var(--color-background-hover);
}

.item-entry:last-child {
	border-bottom: none;
}

.item-info {
	flex: 1;
}

.item-name {
	font-weight: 500;
	color: var(--color-main-text);
	margin-bottom: 4px;
}

.item-details {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
	display: flex;
	gap: 8px;
	align-items: center;
}

.item-sku {
	font-family: monospace;
}

.item-price {
	font-weight: 500;
	color: var(--color-primary-element);
}

.item-quantity {
	color: var(--color-text-maxcontrast);
}

.item-status {
	margin-left: 12px;
}

.status-success { background-color: var(--color-success); color: white; }
.status-warning { background-color: var(--color-warning); color: black; }
.status-error { background-color: var(--color-error); color: white; }
.status-neutral { background-color: var(--color-text-maxcontrast); color: white; }
</style>


