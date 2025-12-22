<template>
	<div class="project-detail-view">
		<ProjectHeader
			:project="project"
			:active-tab="activeTab"
			:popover-open="popoverOpen"
			:can-start-timer="canStartTimer"
			@back="$emit('back')"
			@edit="$emit('edit')"
			@delete="$emit('delete')"
			@toggle-popover="$emit('toggle-popover')"
			@close-popover="$emit('close-popover')"
			@start-timer="$emit('start-timer')"
			@tab-change="$emit('tab-change', $event)"
		/>

		<div class="detail-content">
			<slot />
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
	},
	emits: ['back', 'edit', 'delete', 'toggle-popover', 'close-popover', 'start-timer', 'tab-change'],
}
</script>

<style scoped>
.project-detail-view {
	width: 100%;
	height: 100%;
	display: flex;
	flex-direction: column;
}

.detail-content {
	flex: 1;
	overflow-y: auto;
}
</style>

