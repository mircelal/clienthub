<template>
	<div v-if="isOpen" class="nc-modal-overlay" @click.self="closeModal">
		<div class="nc-modal">
			<div class="nc-modal-header">
				<h3 class="nc-modal-title">
					{{ translate('domaincontrol', 'Manage Categories') }}
				</h3>
				<button class="nc-modal-close" @click="closeModal">
					<Close :size="20" />
				</button>
			</div>

			<div class="nc-modal-body">
				<!-- Add Category Button -->
				<div class="category-header">
					<NcButton type="primary" @click="showAddCategory">
						<template #icon>
							<Plus :size="20" />
						</template>
						{{ translate('domaincontrol', 'Add Category') }}
					</NcButton>
				</div>

				<!-- Categories List -->
				<div class="categories-list">
					<!-- Income Categories -->
					<div class="category-section">
						<div class="section-header">
							<ArrowUp :size="18" />
							<h4 class="section-title">{{ translate('domaincontrol', 'Income Categories') }}</h4>
						</div>
						<div v-if="incomeCategories.length === 0" class="empty-state">
							{{ translate('domaincontrol', 'No income categories') }}
						</div>
						<div v-else class="nc-list">
							<div
								v-for="category in incomeCategories"
								:key="category.id"
								class="nc-list-item"
							>
								<span class="category-name">{{ category.name }}</span>
								<div class="item-actions">
									<NcButton
										type="tertiary"
										:aria-label="translate('domaincontrol', 'Edit')"
										@click="editCategory(category)"
									>
										<template #icon>
											<Pencil :size="18" />
										</template>
									</NcButton>
									<NcButton
										v-if="!category.isPredefined"
										type="tertiary"
										:aria-label="translate('domaincontrol', 'Delete')"
										@click="confirmDeleteCategory(category)"
									>
										<template #icon>
											<Delete :size="18" />
										</template>
									</NcButton>
								</div>
							</div>
						</div>
					</div>

					<!-- Expense Categories -->
					<div class="category-section">
						<div class="section-header">
							<ArrowDown :size="18" />
							<h4 class="section-title">{{ translate('domaincontrol', 'Expense Categories') }}</h4>
						</div>
						<div v-if="expenseCategories.length === 0" class="empty-state">
							{{ translate('domaincontrol', 'No expense categories') }}
						</div>
						<div v-else class="nc-list">
							<div
								v-for="category in expenseCategories"
								:key="category.id"
								class="nc-list-item"
							>
								<span class="category-name">{{ category.name }}</span>
								<div class="item-actions">
									<NcButton
										type="tertiary"
										:aria-label="translate('domaincontrol', 'Edit')"
										@click="editCategory(category)"
									>
										<template #icon>
											<Pencil :size="18" />
										</template>
									</NcButton>
									<NcButton
										v-if="!category.isPredefined"
										type="tertiary"
										:aria-label="translate('domaincontrol', 'Delete')"
										@click="confirmDeleteCategory(category)"
									>
										<template #icon>
											<Delete :size="18" />
										</template>
									</NcButton>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { NcButton } from '@nextcloud/vue'
import MaterialIcon from './MaterialIcon.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Close from 'vue-material-design-icons/Close.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import ArrowUp from 'vue-material-design-icons/ArrowUp.vue'
import ArrowDown from 'vue-material-design-icons/ArrowDown.vue'

export default {
	name: 'TransactionCategoryManagementModal',
	components: {
		MaterialIcon,
		NcButton,
		Plus,
		Close,
		Pencil,
		Delete,
		ArrowUp,
		ArrowDown,
	},
	props: {
		open: {
			type: Boolean,
			default: false,
		},
		categories: {
			type: Array,
			default: () => [],
		},
	},
	emits: ['close', 'add', 'edit', 'delete'],
	computed: {
		isOpen() {
			return this.open
		},
		incomeCategories() {
			return this.categories.filter(c => c.type === 'income')
		},
		expenseCategories() {
			return this.categories.filter(c => c.type === 'expense')
		},
	},
	methods: {
		showAddCategory() {
			this.$emit('add')
		},
		editCategory(category) {
			this.$emit('edit', category)
		},
		confirmDeleteCategory(category) {
			if (confirm(this.translate('domaincontrol', 'Are you sure you want to delete this category?'))) {
				this.$emit('delete', category)
			}
		},
		closeModal() {
			this.$emit('close')
		},
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
			} catch (e) {
				console.warn('Translation error:', e)
			}
			return text
		},
	},
}
</script>

<style scoped>
.nc-modal-overlay {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: rgba(0, 0, 0, 0.5);
	display: flex;
	align-items: center;
	justify-content: center;
	z-index: 10000;
	padding: 20px;
}

.nc-modal {
	background-color: var(--color-main-background);
	border-radius: var(--border-radius-large);
	box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
	max-width: 600px;
	width: 100%;
	max-height: 80vh;
	overflow: hidden;
	display: flex;
	flex-direction: column;
}

.nc-modal-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 20px;
	border-bottom: 1px solid var(--color-border);
}

.nc-modal-title {
	margin: 0;
	font-size: 20px;
	font-weight: 600;
	color: var(--color-main-text);
}

.nc-modal-close {
	background: none;
	border: none;
	color: var(--color-text-maxcontrast);
	cursor: pointer;
	padding: 4px;
	border-radius: var(--border-radius-small);
	transition: background-color 0.2s;
	display: flex;
	align-items: center;
	justify-content: center;
}

.nc-modal-close:hover {
	background-color: var(--color-background-hover);
}

.nc-modal-body {
	padding: 20px;
	overflow-y: auto;
	display: flex;
	flex-direction: column;
	gap: 24px;
}

.category-header {
	display: flex;
	justify-content: flex-end;
}

.categories-list {
	display: flex;
	flex-direction: column;
	gap: 24px;
}

.category-section {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.section-header {
	display: flex;
	align-items: center;
	gap: 8px;
	padding-bottom: 8px;
	border-bottom: 1px solid var(--color-border);
}

.section-title {
	margin: 0;
	font-size: 16px;
	font-weight: 600;
	color: var(--color-main-text);
}

.empty-state {
	padding: 16px;
	text-align: center;
	color: var(--color-text-maxcontrast);
	font-style: italic;
	font-size: 14px;
}

.nc-list {
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.nc-list-item {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 12px 16px;
	background-color: var(--color-background-hover);
	border-radius: var(--border-radius);
	border: 1px solid var(--color-border);
	transition: background-color 0.2s;
}

.nc-list-item:hover {
	background-color: var(--color-background-dark);
}

.category-name {
	font-size: 14px;
	color: var(--color-main-text);
	font-weight: 500;
}

.item-actions {
	display: flex;
	gap: 4px;
}
</style>
