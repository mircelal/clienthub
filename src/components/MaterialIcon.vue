<template>
	<span class="material-icon" :class="iconClass" :style="iconStyle" aria-hidden="true">
		<svg :width="size" :height="size" viewBox="0 0 24 24" fill="currentColor">
			<path :d="iconPath" />
		</svg>
	</span>
</template>

<script>
// Material Design Icons - Common icons used in Nextcloud
const iconPaths = {
	'add': 'M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z',
	'edit': 'M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z',
	'delete': 'M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z',
	'arrow-left': 'M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z',
	'arrow-right': 'M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z',
	'close': 'M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z',
	'home': 'M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z',
	'contacts': 'M20 0H4v2h16V0zM4 24h16v-2H4V24zM20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-8 2.75c1.24 0 2.25 1.01 2.25 2.25S13.24 11.25 12 11.25 9.75 10.24 9.75 9 10.76 6.75 12 6.75zM17 17H7v-1.5c0-1.67 3.33-2.5 5-2.5s5 .83 5 2.5V17z',
	'public': 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z',
	'link': 'M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z',
	'settings': 'M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94L14.4 2.81c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.07.62-.07.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z',
	'folder': 'M10 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2h-8l-2-2z',
	'checkmark': 'M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z',
	'files': 'M10 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2h-8l-2-2z',
	'calendar': 'M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z',
	'mail': 'M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z',
	'monitoring': 'M19.36 2.72L20.78 4.14 15.06 9.85C16.13 11.39 16.28 13.24 15.38 14.89L20.78 20.29L19.36 21.71L13.96 16.31C12.31 17.21 10.46 17.06 8.92 15.99L3.64 21.27L2.22 19.85L7.5 14.57C6.43 13.03 6.28 11.18 7.18 9.53L1.78 4.12L3.19 2.71L8.59 8.11C10.24 7.21 12.09 7.36 13.63 8.43L18.91 3.15L19.33 2.72H19.36Z',
	'office': 'M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z',
	'loading': 'M12,4V2A10,10 0 0,0 2,12H4A8,8 0 0,1 12,4Z',
}

export default {
	name: 'MaterialIcon',
	props: {
		name: {
			type: String,
			required: true,
		},
		size: {
			type: [Number, String],
			default: 20,
		},
		color: {
			type: String,
			default: 'currentColor',
		},
	},
	computed: {
		iconPath() {
			return iconPaths[this.name] || iconPaths['close']
		},
		iconClass() {
			return `material-icon--${this.name}`
		},
		iconStyle() {
			return {
				width: typeof this.size === 'number' ? `${this.size}px` : this.size,
				height: typeof this.size === 'number' ? `${this.size}px` : this.size,
				color: this.color,
			}
		},
	},
}
</script>

<style scoped>
.material-icon {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
	vertical-align: middle;
}

.material-icon svg {
	display: block;
	width: 100%;
	height: 100%;
}
</style>

