<template>
	<div class="project-detail-view">
		<ProjectHeader
			:project="project"
			:active-tab="activeTab"
			:popover-open="popoverOpen"
			:can-start-timer="canStartTimer"
			:current-running-entry="currentRunningEntry"
			:is-mobile="isMobile"
			@back="$emit('back')"
			@edit="$emit('edit')"
			@delete="$emit('delete')"
			@toggle-popover="$emit('toggle-popover')"
			@close-popover="$emit('close-popover')"
			@start-timer="$emit('start-timer')"
			@stop-timer="$emit('stop-timer')"
			@tab-change="$emit('tab-change', $event)"
		/>

		<div class="detail-content-wrapper">
			<div class="detail-content">
				<slot />
			</div>
		</div>
	</div>
</template>

<script>
import ProjectHeader from './ProjectHeader.vue'

export default {
	name: 'ProjectDetail',
	components: {
		ProjectHeader,
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
		currentRunningEntry: {
			type: Object,
			default: null,
		},
		isMobile: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['back', 'edit', 'delete', 'toggle-popover', 'close-popover', 'start-timer', 'stop-timer', 'tab-change'],
}
</script>

<style scoped>
.project-detail-view {
	width: 100%;
	height: 100%;
	display: flex;
	flex-direction: column;
	background-color: transparent;
	overflow: hidden;
}

.detail-content-wrapper {
	flex: 1;
	overflow-y: hidden; /* Scroll is handled by .crm-content-scroll in Projects.vue */
	width: 100%;
	position: relative;
	display: flex;
	flex-direction: column;
	min-height: 0;
}

.detail-content {
	flex: 1;
	width: 100%;
	display: flex;
	flex-direction: column;
	min-height: 0;
}

@media (max-width: 768px) {
	.detail-content {
		padding: 16px;
	}
}
</style>

