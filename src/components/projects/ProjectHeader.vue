<template>
	<div class="project-header">
		<div class="header-left">
			<button class="button-vue button-vue--tertiary" @click="$emit('back')">
				<MaterialIcon name="arrow-left" :size="20" />
				{{ translate('domaincontrol', 'Back') }}
			</button>
			<h2 class="project-title">{{ project?.name || '' }}</h2>
		</div>
		<div class="header-right">
			<button
				v-if="canStartTimer"
				class="button-vue button-vue--primary"
				@click="$emit('start-timer')"
			>
				<MaterialIcon name="play" :size="18" />
				{{ translate('domaincontrol', 'Start Timer') }}
			</button>
			<div class="popover-menu-wrapper" @click.stop>
				<button
					class="button-vue button-vue--secondary action-button--more"
					@click.stop="$emit('toggle-popover')"
					:title="translate('domaincontrol', 'More options')"
				>
					<MaterialIcon name="more-vertical" :size="20" />
				</button>
				<div
					v-if="popoverOpen"
					class="popover-menu"
					@click.stop
				>
					<button
						class="popover-menu-item"
						@click="$emit('edit'); $emit('close-popover')"
					>
						<MaterialIcon name="edit" :size="16" />
						{{ translate('domaincontrol', 'Edit') }}
					</button>
					<div class="popover-menu-separator"></div>
					<button
						class="popover-menu-item popover-menu-item--danger"
						@click="$emit('delete'); $emit('close-popover')"
					>
						<MaterialIcon name="delete" :size="16" />
						{{ translate('domaincontrol', 'Delete') }}
					</button>
				</div>
			</div>
		</div>
	</div>
	<div class="project-tabs">
		<button
			v-for="tab in tabs"
			:key="tab.id"
			class="project-tab"
			:class="{ 'project-tab--active': activeTab === tab.id }"
			@click="$emit('tab-change', tab.id)"
		>
			<MaterialIcon v-if="tab.icon" :name="tab.icon" :size="18" />
			{{ translate('domaincontrol', tab.label) }}
		</button>
	</div>
</template>

<script>
import MaterialIcon from '../MaterialIcon.vue'

export default {
	name: 'ProjectHeader',
	components: {
		MaterialIcon,
	},
	props: {
		project: {
			type: Object,
			default: null,
		},
		activeTab: {
			type: String,
			default: 'overview',
		},
		popoverOpen: {
			type: Boolean,
			default: false,
		},
		canStartTimer: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['back', 'edit', 'delete', 'toggle-popover', 'close-popover', 'start-timer', 'tab-change'],
	data() {
		return {
			tabs: [
				{ id: 'overview', label: 'Overview', icon: 'dashboard' },
				{ id: 'tasks', label: 'Tasks', icon: 'checkmark' },
				{ id: 'time', label: 'Time Tracking', icon: 'monitoring' },
				{ id: 'files', label: 'Files', icon: 'folder' },
				{ id: 'notes', label: 'Notes', icon: 'note' },
				{ id: 'requirements', label: 'Requirements', icon: 'list' },
				{ id: 'challenges', label: 'Challenges', icon: 'warning' },
				{ id: 'research', label: 'Research', icon: 'search' },
				{ id: 'financials', label: 'Financials', icon: 'accounting' },
				{ id: 'linked', label: 'Linked Items', icon: 'link' },
				{ id: 'sharing', label: 'Sharing', icon: 'share' },
				{ id: 'activity', label: 'Activity', icon: 'history' },
			],
		}
	},
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
	},
}
</script>

<style scoped>
.project-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 16px 20px;
	border-bottom: 1px solid var(--color-border);
	background-color: var(--color-main-background);
}

.header-left {
	display: flex;
	align-items: center;
	gap: 12px;
}

.project-title {
	margin: 0;
	font-size: 20px;
	font-weight: 600;
	color: var(--color-main-text);
}

.header-right {
	display: flex;
	align-items: center;
	gap: 8px;
}

.project-tabs {
	display: flex;
	align-items: center;
	gap: 4px;
	padding: 0 20px;
	border-bottom: 1px solid var(--color-border);
	background-color: var(--color-main-background);
	overflow-x: auto;
}

.project-tab {
	display: flex;
	align-items: center;
	gap: 6px;
	padding: 12px 16px;
	border: none;
	background: transparent;
	color: var(--color-text-maxcontrast);
	font-size: 14px;
	cursor: pointer;
	border-bottom: 2px solid transparent;
	transition: all 0.2s;
	white-space: nowrap;
}

.project-tab:hover {
	color: var(--color-main-text);
	background-color: var(--color-background-dark);
}

.project-tab--active {
	color: var(--color-primary-element);
	border-bottom-color: var(--color-primary-element);
	font-weight: 500;
}
</style>

