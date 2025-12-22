<template>
	<div class="rich-text-editor-wrapper">
		<div class="rich-text-toolbar">
			<button
				type="button"
				class="toolbar-btn"
				:class="{ active: isBold }"
				@click="execCommand('bold')"
				:title="translate('domaincontrol', 'Bold')"
			>
				<strong>B</strong>
			</button>
			<button
				type="button"
				class="toolbar-btn"
				:class="{ active: isItalic }"
				@click="execCommand('italic')"
				:title="translate('domaincontrol', 'Italic')"
			>
				<em>I</em>
			</button>
			<button
				type="button"
				class="toolbar-btn"
				:class="{ active: isUnderline }"
				@click="execCommand('underline')"
				:title="translate('domaincontrol', 'Underline')"
			>
				<u>U</u>
			</button>
			<div class="toolbar-separator"></div>
			<button
				type="button"
				class="toolbar-btn"
				@click="execCommand('insertUnorderedList')"
				:title="translate('domaincontrol', 'Bullet List')"
			>
				<MaterialIcon name="list" :size="16" />
			</button>
			<button
				type="button"
				class="toolbar-btn"
				@click="execCommand('insertOrderedList')"
				:title="translate('domaincontrol', 'Numbered List')"
			>
				<MaterialIcon name="format_list_numbered" :size="16" />
			</button>
			<div class="toolbar-separator"></div>
			<button
				type="button"
				class="toolbar-btn"
				@click="insertLineBreak"
				:title="translate('domaincontrol', 'Line Break')"
			>
				<MaterialIcon name="more_vert" :size="16" style="transform: rotate(90deg);" />
			</button>
		</div>
		<div
			ref="editor"
			class="rich-text-editor"
			contenteditable="true"
			:data-placeholder="placeholder"
			@input="handleInput"
			@focus="updateToolbarState"
			@blur="updateToolbarState"
			@keyup="updateToolbarState"
			@mouseup="updateToolbarState"
		></div>
	</div>
</template>

<script>
import MaterialIcon from './MaterialIcon.vue'

export default {
	name: 'RichTextEditor',
	components: {
		MaterialIcon,
	},
	props: {
		modelValue: {
			type: String,
			default: '',
		},
		placeholder: {
			type: String,
			default: '',
		},
	},
	emits: ['update:modelValue'],
	data() {
		return {
			isBold: false,
			isItalic: false,
			isUnderline: false,
		}
	},
	watch: {
		modelValue(newVal) {
			if (this.$refs.editor && this.$refs.editor.innerHTML !== newVal) {
				this.$refs.editor.innerHTML = newVal || ''
			}
		},
		open(newVal) {
			if (newVal && this.$refs.editor) {
				this.$nextTick(() => {
					this.$refs.editor.innerHTML = this.modelValue || ''
				})
			}
		},
	},
	mounted() {
		if (this.modelValue && this.$refs.editor) {
			this.$refs.editor.innerHTML = this.modelValue
		}
	},
	methods: {
		execCommand(command) {
			if (!this.$refs.editor) return
			this.$refs.editor.focus()
			document.execCommand(command, false, null)
			this.updateToolbarState()
			this.handleInput()
		},
		insertLineBreak() {
			if (!this.$refs.editor) return
			this.$refs.editor.focus()
			document.execCommand('insertHTML', false, '<br>')
			this.handleInput()
		},
		handleInput() {
			if (!this.$refs.editor) return
			const content = this.$refs.editor.innerHTML
			this.$emit('update:modelValue', content)
		},
		updateToolbarState() {
			if (!this.$refs.editor) return
			try {
				this.isBold = document.queryCommandState('bold')
				this.isItalic = document.queryCommandState('italic')
				this.isUnderline = document.queryCommandState('underline')
			} catch (e) {
				// Ignore errors
			}
		},
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

			const translations = {
				'Bold': 'Kalın',
				'Italic': 'İtalik',
				'Underline': 'Altı Çizili',
				'Bullet List': 'Madde İşareti',
				'Numbered List': 'Numaralı Liste',
				'Line Break': 'Satır',
			}

			return translations[text] || text
		},
	},
}
</script>

<style scoped>
.rich-text-editor-wrapper {
	display: block;
	width: 100%;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	background: var(--color-main-background);
	overflow: hidden;
	box-sizing: border-box;
}

.rich-text-toolbar {
	display: flex;
	align-items: center;
	gap: 4px;
	padding: 8px;
	background: var(--color-background-dark);
	border-bottom: 1px solid var(--color-border);
}

.toolbar-btn {
	background: transparent;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-small);
	padding: 6px 10px;
	cursor: pointer;
	font-size: 14px;
	color: var(--color-main-text);
	transition: all 0.2s;
	min-width: 32px;
	height: 32px;
	display: flex;
	align-items: center;
	justify-content: center;
}

.toolbar-btn:hover {
	background: var(--color-background-hover);
	border-color: var(--color-primary-element);
}

.toolbar-btn.active {
	background: var(--color-primary-element-light);
	border-color: var(--color-primary-element);
	color: var(--color-primary-element);
}

.toolbar-separator {
	width: 1px;
	height: 20px;
	background: var(--color-border);
	margin: 0 4px;
}

.rich-text-editor {
	display: block;
	width: 100%;
	min-height: 120px;
	max-height: 300px;
	overflow-y: auto;
	padding: 12px 14px;
	font-size: 14px;
	line-height: 1.6;
	color: var(--color-main-text);
	outline: none;
	box-sizing: border-box;
	font-family: inherit;
}

.rich-text-editor:empty:before {
	content: attr(data-placeholder);
	color: var(--color-text-maxcontrast);
	font-style: italic;
}

.rich-text-editor:focus {
	outline: none;
}

.rich-text-editor strong {
	font-weight: 600;
}

.rich-text-editor em {
	font-style: italic;
}

.rich-text-editor u {
	text-decoration: underline;
}

.rich-text-editor ul,
.rich-text-editor ol {
	margin: 8px 0;
	padding-left: 24px;
}

.rich-text-editor li {
	margin: 4px 0;
}

.rich-text-editor p {
	margin: 8px 0;
}

.rich-text-editor p:first-child {
	margin-top: 0;
}

.rich-text-editor p:last-child {
	margin-bottom: 0;
}
</style>

