<template>
	<div class="tab-content">
		<div class="detail-section">
			<div class="section-header">
				<h3 class="section-title">
					<MaterialIcon name="link" :size="24" />
					{{ translate('domaincontrol', 'Linked Items') }}
				</h3>
				<button class="button-vue button-vue--secondary" @click="$emit('link-item')">
					<MaterialIcon name="add" :size="18" />
					{{ translate('domaincontrol', 'Link Item') }}
				</button>
			</div>
			<div class="section-content">
				<div v-if="loading" class="loading-content">
					<MaterialIcon name="loading" :size="32" class="loading-icon" />
					<p>{{ translate('domaincontrol', 'Loading linked items...') }}</p>
				</div>
				<div v-else-if="items.length === 0" class="empty-content">
					<p class="empty-content__text">{{ translate('domaincontrol', 'No linked items yet') }}</p>
				</div>
				<div v-else class="linked-items-list">
					<div
						v-for="item in items"
						:key="item.id"
						class="linked-item"
					>
						<div class="linked-item-content">
							<MaterialIcon :name="getItemTypeIcon(item.itemType)" :size="20" />
							<span class="linked-item-type">{{ getItemTypeLabel(item.itemType) }}</span>
							<span class="linked-item-name">{{ getItemName(item) }}</span>
						</div>
						<button
							class="action-button action-button--delete"
							@click="$emit('remove-item', item.id)"
							:title="translate('domaincontrol', 'Remove')"
						>
							<MaterialIcon name="delete" :size="16" />
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import MaterialIcon from '../MaterialIcon.vue'

export default {
	name: 'ProjectLinkedItems',
	components: {
		MaterialIcon,
	},
	props: {
		items: {
			type: Array,
			default: () => [],
		},
		loading: {
			type: Boolean,
			default: false,
		},
		domains: {
			type: Array,
			default: () => [],
		},
		hostings: {
			type: Array,
			default: () => [],
		},
		websites: {
			type: Array,
			default: () => [],
		},
		services: {
			type: Array,
			default: () => [],
		},
	},
	emits: ['link-item', 'remove-item'],
	methods: {
		translate(appId, text, vars) {
			try {
				if (typeof window !== 'undefined') {
					if (typeof OC !== 'undefined' && OC.L10n && typeof OC.L10n.translate === 'function') {
						const translated = OC.L10n.translate(appId, text, vars || {})
						if (translated && translated !== text) {
							return translated
						}
					}
					if (typeof window.t === 'function') {
						const translated = window.t(appId, text, vars || {})
						if (translated && translated !== text) {
							return translated
						}
					}
				}
			} catch (e) {
				console.warn('Translation error:', e)
			}
			return text
		},
		getItemName(item) {
			let itemData = null
			if (item.itemType === 'domain') {
				itemData = this.domains.find(d => d.id === item.itemId)
				return itemData ? itemData.domainName : 'N/A'
			} else if (item.itemType === 'hosting') {
				itemData = this.hostings.find(h => h.id === item.itemId)
				return itemData ? itemData.provider : 'N/A'
			} else if (item.itemType === 'website') {
				itemData = this.websites.find(w => w.id === item.itemId)
				return itemData ? itemData.name : 'N/A'
			} else if (item.itemType === 'service') {
				itemData = this.services.find(s => s.id === item.itemId)
				return itemData ? itemData.name : 'N/A'
			}
			return 'N/A'
		},
		getItemTypeLabel(type) {
			const labels = {
				domain: this.translate('domaincontrol', 'Domain'),
				hosting: this.translate('domaincontrol', 'Hosting'),
				website: this.translate('domaincontrol', 'Website'),
				service: this.translate('domaincontrol', 'Service'),
			}
			return labels[type] || type
		},
		getItemTypeIcon(type) {
			const icons = {
				domain: 'public',
				hosting: 'category-office',
				website: 'link',
				service: 'settings',
			}
			return icons[type] || 'link'
		},
	},
}
</script>

<style scoped>
.detail-section {
	margin-bottom: 24px;
}

.section-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	margin-bottom: 16px;
}

.section-title {
	display: flex;
	align-items: center;
	gap: 8px;
	margin: 0;
	font-size: 18px;
	font-weight: 600;
	color: var(--color-main-text);
}

.linked-items-list {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.linked-item {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 12px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
}

.linked-item-content {
	display: flex;
	align-items: center;
	gap: 12px;
}

.linked-item-type {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
	padding: 2px 8px;
	background-color: var(--color-background-dark);
	border-radius: 12px;
}

.linked-item-name {
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
}
</style>

