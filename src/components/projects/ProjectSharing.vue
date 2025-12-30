<template>
	<div class="project-tab-content">
		<div class="detail-section">
			<div class="section-header">
				<h3 class="section-title">
					<MaterialIcon name="share" :size="24" />
					{{ translate('domaincontrol', 'Share Project') }}
				</h3>
				<button class="button-vue button-vue--primary" @click="$emit('share')">
					<MaterialIcon name="add" :size="18" />
					{{ translate('domaincontrol', 'Share with user') }}
				</button>
			</div>
			<div class="section-content">
				<div v-if="loading" class="loading-content">
					<MaterialIcon name="loading" :size="32" class="loading-icon" />
					<p>{{ translate('domaincontrol', 'Loading...') }}</p>
				</div>
				<div v-else-if="shares.length === 0" class="empty-content">
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
							class="action-button action-button--delete"
							@click.stop="$emit('unshare', share.sharedWithUserId)"
							:title="translate('domaincontrol', 'Unshare')"
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
	name: 'ProjectSharing',
	components: {
		MaterialIcon,
	},
	props: {
		shares: {
			type: Array,
			default: () => [],
		},
		loading: {
			type: Boolean,
			default: false,
		},
		availableUsers: {
			type: Array,
			default: () => [],
		},
	},
	emits: ['share', 'unshare'],
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

.project-shares-list {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.project-share-item {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 12px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
}

.share-info {
	display: flex;
	align-items: center;
	gap: 12px;
}

.share-user {
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
}

.share-permission {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
	padding: 2px 8px;
	background-color: var(--color-background-dark);
	border-radius: 12px;
}
</style>

