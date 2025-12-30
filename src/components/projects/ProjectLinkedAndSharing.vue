<template>
	<div class="project-tab-content">
		<!-- Linked Items Section -->
		<div class="detail-section">
			<div class="section-header">
				<h3 class="section-title">
					<Link :size="20" class="section-title-icon" />
					{{ translate('domaincontrol', 'Linked Items') }}
				</h3>
				<NcButton type="secondary" @click="$emit('link-item')">
					<template #icon>
						<Plus :size="18" />
					</template>
					{{ translate('domaincontrol', 'Link Item') }}
				</NcButton>
			</div>
			<div class="section-content">
				<div v-if="itemsLoading" class="loading-content">
					<Loading :size="32" class="loading-icon" />
					<p>{{ translate('domaincontrol', 'Loading linked items...') }}</p>
				</div>
				<div v-else-if="items.length === 0" class="empty-content">
					<Link :size="48" class="empty-content__icon" />
					<p class="empty-content__text">{{ translate('domaincontrol', 'No linked items yet') }}</p>
				</div>
				<div v-else class="linked-items-list">
					<div
						v-for="item in items"
						:key="item.id"
						class="linked-item"
					>
						<div class="linked-item-content">
							<component :is="getItemTypeIcon(item.itemType)" :size="20" class="item-type-icon" />
							<span class="linked-item-type">{{ getItemTypeLabel(item.itemType) }}</span>
							<span class="linked-item-name">{{ getItemName(item) }}</span>
						</div>
						<button
							class="action-button action-button--delete transition"
							@click="$emit('remove-item', item.id)"
							:title="translate('domaincontrol', 'Remove')"
						>
							<TrashCanOutline :size="16" />
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Sharing Section -->
		<div v-if="isProjectOwner" class="detail-section">
			<div class="section-header">
				<h3 class="section-title">
					<ShareVariant :size="20" class="section-title-icon" />
					{{ translate('domaincontrol', 'Share Project') }}
				</h3>
				<NcButton type="primary" @click="$emit('share')">
					<template #icon>
						<AccountPlus :size="18" />
					</template>
					{{ translate('domaincontrol', 'Share with user') }}
				</NcButton>
			</div>
			<div class="section-content">
				<div v-if="sharesLoading" class="loading-content">
					<Loading :size="32" class="loading-icon" />
					<p>{{ translate('domaincontrol', 'Loading...') }}</p>
				</div>
				<div v-else-if="shares.length === 0" class="empty-content">
					<ShareVariant :size="48" class="empty-content__icon" />
					<p class="empty-content__text">{{ translate('domaincontrol', 'No users shared') }}</p>
				</div>
				<div v-else class="project-shares-list">
					<div
						v-for="share in shares"
						:key="share.id"
						class="project-share-item"
					>
						<div class="share-info">
							<span class="share-user">{{ getUserDisplayName(share.sharedWithUserId) }}</span>
							<span class="share-permission">{{ share.permissionLevel === 'write' ? translate('domaincontrol', 'Write') : translate('domaincontrol', 'Read') }}</span>
						</div>
						<button
							class="action-button action-button--delete transition"
							@click.stop="$emit('unshare', share.sharedWithUserId)"
							:title="translate('domaincontrol', 'Unshare')"
						>
							<TrashCanOutline :size="16" />
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { NcButton } from '@nextcloud/vue'
import Link from 'vue-material-design-icons/Link.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import ShareVariant from 'vue-material-design-icons/ShareVariant.vue'
import AccountPlus from 'vue-material-design-icons/AccountPlus.vue'
import Loading from 'vue-material-design-icons/Loading.vue'
import Earth from 'vue-material-design-icons/Earth.vue'
import Server from 'vue-material-design-icons/Server.vue'
import Web from 'vue-material-design-icons/Web.vue'
import Cog from 'vue-material-design-icons/Cog.vue'

export default {
	name: 'ProjectLinkedAndSharing',
	components: {
		NcButton,
		Link,
		Plus,
		TrashCanOutline,
		ShareVariant,
		AccountPlus,
		Loading,
		Earth,
		Server,
		Web,
		Cog,
	},
	props: {
		items: {
			type: Array,
			default: () => [],
		},
		itemsLoading: {
			type: Boolean,
			default: false,
		},
		shares: {
			type: Array,
			default: () => [],
		},
		sharesLoading: {
			type: Boolean,
			default: false,
		},
		isProjectOwner: {
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
		availableUsers: {
			type: Array,
			default: () => [],
		},
	},
	emits: ['link-item', 'remove-item', 'share', 'unshare'],
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
				domain: 'Earth',
				hosting: 'Server',
				website: 'Web',
				service: 'Cog',
			}
			return icons[type] || 'Link'
		},
		getUserDisplayName(userId) {
			const user = this.availableUsers.find(u => u.userId === userId)
			if (user) {
				return user.displayName || userId
			}
			return userId
		},
	},
}
</script>

<style scoped>
.project-tab-content {
	padding: 0;
}

.detail-section {
	margin-bottom: 24px;
}

.detail-section:last-child {
	margin-bottom: 0;
}

.section-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	margin-bottom: 16px;
	padding-bottom: 12px;
	border-bottom: 1px solid var(--color-border);
}

.section-title {
	display: flex;
	align-items: center;
	gap: 8px;
	margin: 0;
	font-size: 16px;
	font-weight: 600;
	color: var(--color-main-text);
	line-height: 1.4;
}

.section-content {
	margin-top: 16px;
}

.loading-content {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	gap: 12px;
}

.loading-icon {
	animation: spin 1s linear infinite;
}

@keyframes spin {
	from { transform: rotate(0deg); }
	to { transform: rotate(360deg); }
}


.linked-items-list,
.project-shares-list {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.linked-item,
.project-share-item {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 12px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius);
	transition: all 0.2s ease;
}

.linked-item:hover,
.project-share-item:hover {
	background-color: var(--color-background-hover);
	border-color: var(--color-primary-element-element);
}

.linked-item-content,
.share-info {
	display: flex;
	align-items: center;
	gap: 12px;
}

.linked-item-type,
.share-permission {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
	padding: 2px 8px;
	background-color: var(--color-background-dark);
	border-radius: 12px;
}

.linked-item-name,
.share-user {
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
}

.action-button {
	background: transparent;
	border: none;
	cursor: pointer;
	padding: 4px;
	display: flex;
	align-items: center;
	justify-content: center;
	color: var(--color-text-maxcontrast);
	border-radius: var(--border-radius);
	transition: all 0.2s ease;
}

.action-button:hover {
	background-color: var(--color-background-hover);
	color: var(--color-main-text);
}

.action-button--delete:hover {
	background-color: var(--color-element-error-background);
	color: var(--color-element-error);
}

.empty-content {
	text-align: center;
}

.empty-content__icon {
	margin-bottom: 12px;
	opacity: 0.5;
}

.empty-content__text {
	color: var(--color-text-maxcontrast);
	font-size: 14px;
}
</style>

