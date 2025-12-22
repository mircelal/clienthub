<template>
	<nav id="app-navigation-vue" class="app-navigation__content" aria-label="ClientHub">
		<div class="app-navigation__body">
			<ul class="app-navigation-list">
				<li
					v-for="item in menuItems"
					:key="item.id"
					class="app-navigation-entry-wrapper"
					:class="{ 'app-navigation-entry--opened': $props.currentTab === item.id }"
				>
					<div class="app-navigation-entry" :class="{ active: $props.currentTab === item.id }">
						<a
							href="#"
							:class="['app-navigation-entry-link', item.icon]"
							:aria-current="$props.currentTab === item.id ? 'page' : undefined"
							@click.prevent="handleTabClick(item.id)"
						>
					<div class="app-navigation-entry-icon">
						<span class="icon" :class="item.icon" aria-hidden="true"></span>
					</div>
							<span class="app-navigation-entry__name">
								{{ translate('domaincontrol', item.label) }}
							</span>
						</a>
					</div>
				</li>
			</ul>
		</div>
		<ul class="app-navigation-entry__settings">
			<li class="app-navigation-entry-wrapper">
				<div class="app-navigation-entry">
					<a
						href="#"
						class="app-navigation-entry-link"
						:class="{ active: $props.currentTab === 'settings' }"
						@click.prevent="handleTabClick('settings')"
					>
					<div class="app-navigation-entry-icon">
						<span class="icon icon-settings" aria-hidden="true"></span>
					</div>
						<span class="app-navigation-entry__name">
							{{ translate('domaincontrol', 'Settings') }}
						</span>
					</a>
				</div>
			</li>
		</ul>
	</nav>
</template>

<script>
export default {
	name: 'Navigation',
	data() {
		return {
			currentTab: 'dashboard',
			settingsOpen: false,
			menuItems: [
				{
					id: 'dashboard',
					label: 'Dashboard',
					icon: 'icon-home',
				},
				{
					id: 'clients',
					label: 'Clients',
					icon: 'icon-contacts',
				},
				{
					id: 'domains',
					label: 'Domains',
					icon: 'icon-public',
				},
				{
					id: 'hostings',
					label: 'Hosting',
					icon: 'icon-category-office',
				},
				{
					id: 'websites',
					label: 'Websites',
					icon: 'icon-link',
				},
				{
					id: 'services',
					label: 'Services',
					icon: 'icon-settings',
				},
				{
					id: 'invoices',
					label: 'Invoices',
					icon: 'icon-files',
				},
				{
					id: 'projects',
					label: 'Projects',
					icon: 'icon-folder',
				},
				{
					id: 'tasks',
					label: 'Tasks',
					icon: 'icon-checkmark',
				},
				{
					id: 'transactions',
					label: 'Income/Expense',
					icon: 'icon-category-office',
				},
				{
					id: 'debts',
					label: 'Debts/Receivables',
					icon: 'icon-files',
				},
				{
					id: 'reports',
					label: 'Reports',
					icon: 'icon-category-monitoring',
				},
			],
		}
	},
	mounted() {
		// Listen for tab changes from external code
		if (typeof window.DomainControl !== 'undefined') {
			// Store original switchTab method
			const originalSwitchTab = window.DomainControl.switchTab
			// Override to update Vue component
			window.DomainControl.switchTab = (tabName) => {
				this.$emit('switch-tab', tabName)
				if (originalSwitchTab) {
					originalSwitchTab.call(window.DomainControl, tabName)
				}
			}
		}
	},
	methods: {
		translate(appId, text, vars) {
			// Use Nextcloud's translation system
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

			// Fallback: Manual translation
			const translations = {
				'Dashboard': 'Dashboard',
				'Clients': 'Müşteriler',
				'Domains': 'Domainler',
				'Hosting': 'Hosting',
				'Websites': 'Websiteler',
				'Services': 'Hizmetler',
				'Invoices': 'Faturalar',
				'Projects': 'Projeler',
				'Tasks': 'Görevler',
				'Income/Expense': 'Gelir/Gider',
				'Debts/Receivables': 'Borç/Alacak',
				'Reports': 'Raporlar',
				'Settings': 'Ayarlar',
				'General Settings': 'Genel Ayarlar',
			}

			return translations[text] || text
		},
		handleTabClick(tabId) {
			// Emit event to parent App component
			this.$emit('switch-tab', tabId)
			// Call existing DomainControl.switchTab if available
			if (typeof window.DomainControl !== 'undefined' && window.DomainControl.switchTab) {
				window.DomainControl.switchTab(tabId)
			}
		},
		toggleSettings() {
			this.settingsOpen = !this.settingsOpen
		},
	},
}
</script>

<style scoped>
/* Nextcloud native navigation structure - Following official guidelines */
/* Reference: https://docs.nextcloud.com/server/latest/developer_manual/html_css_design/index.html */

.app-navigation__content {
	display: flex;
	flex-direction: column;
	height: 100%;
}

.app-navigation__body {
	flex: 1;
	overflow-y: auto;
	overflow-x: hidden;
}

.app-navigation-list {
	list-style: none;
	padding: 0;
	margin: 0;
}

.app-navigation-entry-wrapper {
	margin: 0;
}

.app-navigation-entry {
	position: relative;
}

.app-navigation-entry-link {
	display: flex;
	align-items: center;
	padding: 0 12px;
	color: var(--color-main-text);
	text-decoration: none;
	border-radius: var(--border-radius-large);
	transition: background-color 0.2s, color 0.2s;
	cursor: pointer;
	min-height: 44px;
	width: 100%;
	box-sizing: border-box;
}

.app-navigation-entry-link:hover,
.app-navigation-entry-link:focus {
	background-color: var(--color-background-hover);
}

.app-navigation-entry.active .app-navigation-entry-link,
.app-navigation-entry-link[aria-current='page'] {
	background-color: var(--color-primary-element);
	color: var(--color-primary-element-text);
}

.app-navigation-entry-icon {
	width: 20px;
	height: 20px;
	margin-right: 12px;
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
}

.app-navigation-entry-icon .icon {
	width: 20px;
	height: 20px;
	display: inline-block;
	background-size: 20px;
	background-position: center;
	background-repeat: no-repeat;
	opacity: 0.7;
	transition: opacity 0.2s;
}

.app-navigation-entry.active .app-navigation-entry-icon .icon,
.app-navigation-entry-link[aria-current='page'] .app-navigation-entry-icon .icon {
	opacity: 1;
	filter: brightness(0) invert(1);
}

.app-navigation-entry__name {
	flex: 1;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	font-size: var(--default-font-size);
	line-height: var(--default-line-height);
}

.app-navigation-entry__settings {
	list-style: none;
	padding: 0;
	margin: 0;
	border-top: 1px solid var(--color-border);
}
</style>

