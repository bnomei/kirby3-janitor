<template>
	<div class="janitor-wrapper">
		<k-button
			:id="id"
			:class="['janitor', button.state]"
			:icon="currentIcon"
			:command="command"
			:disabled="!unsaved && !isUnsaved && hasChanges"
			:style="buttonStyle"
			@click="runJanitor"
		>
			{{ button.label || label }}
		</k-button>
		<k-text
			v-if="button.help || help"
			theme="help"
			class="k-field-help"
			:html="button.help || help"
		/>
		<a
			v-show="downloadRequest"
			ref="downloadAnchor"
			class="visually-hidden"
			:href="downloadRequest"
			download
		/>
		<a
			v-show="urlRequest"
			ref="tabAnchor"
			class="visually-hidden"
			:href="urlRequest"
			target="_blank"
		/>
	</div>
</template>

<script>
const STORAGE_ID = "janitor.runAfterAutosave";

export default {
	props: {
		autosave: Boolean,
		backgroundColor: String,
		clipboard: Boolean,
		color: String,
		confirm: String,
		command: String,
		cooldown: Number,
		error: String,
		icon: String,
		intab: Boolean,
		help: String,
		label: String,
		progress: String,
		success: String,
		status: String,
		unsaved: Boolean
	},

	data() {
		return {
			button: {
				label: null,
				state: null,
				help: null,
				style: null
			},
			clipboardRequest: null,
			downloadRequest: null,
			icons: {
				"is-running": "janitorLoader",
				"is-success": "check",
				"has-error": "alert"
			},
			isUnsaved: false,
			urlRequest: null
		};
	},

	computed: {
		buttonStyle() {
			return (
				this.button.style || {
					color: this.color,
					backgroundColor: this.backgroundColor
				}
			);
		},
		currentIcon() {
			return this.icons[this.status] ?? this.icon;
		},
		id() {
			return (
				"janitor-" +
				this.hashCode(
					this.command + (this.button.label ?? "") + this.label
				)
			);
		},
		hasChanges() {
			return this.$store.getters["content/hasChanges"]();
		}
	},

	created() {
		this.$events.$on(
			"model.update",
			() => sessionStorage.getItem(STORAGE_ID) && location.reload()
		);

		if (sessionStorage.getItem(STORAGE_ID) === this.id) {
			sessionStorage.removeItem(STORAGE_ID);
			this.runJanitor();
		}
	},

	methods: {
		/**
		 * Source: https://stackoverflow.com/a/8831937
		 */
		hashCode(str) {
			let hash = 0;

			if (str.length === 0) {
				return hash;
			}

			for (const i of str) {
				hash = (hash << 5) - hash + str.charCodeAt(i);
				// convert to 32bit integer
				hash = hash & hash;
			}

			return hash;
		},

		async runJanitor() {
			if (this.confirm && !window.confirm(this.confirm)) {
				return;
			}

			if (this.autosave && this.hasChanges) {
				// lock janitor button, press save and listen to `model.update` event
				const saveButton = document.querySelector(
					".k-panel .k-form-buttons .k-view"
				).lastChild;

				// revert & save
				if (saveButton) {
					this.isUnsaved = false;
					sessionStorage.setItem(STORAGE_ID, this.id);
					this.simulateClick(saveButton);
					return;
				}
			}

			if (this.clipboardRequest) {
				await this.copyToClipboard(this.clipboardRequest);
				this.resetButton();
				this.clipboardRequest = null;
				return;
			}

			if (this.status) {
				return;
			}

			this.postRequest("plugin-janitor", { command: this.command });
		},

		async postRequest(path, data) {
			this.button.label = this.progress ?? `${this.label} …`;
			this.button.state = "is-running";

			const {
				label,
				message,
				status,
				reload,
				open,
				download,
				clipboard,
				success,
				error,
				icon,
				help,
				color,
				backgroundColor,
				resetStyle
			} = await this.$api.post(path, data);

			if (status === 200) {
				this.button.label = success ?? this.success;
			} else {
				this.button.label = error ?? this.error;
			}

			if (label) {
				this.label = label;
			}

			if (message) {
				this.button.label = message;
			}

			if (help) {
				this.button.help = help;
			}

			if (icon) {
				this.icon = icon;
			}

			this.button.style = {
				color: "white",
				reset: true
			};
			if (status) {
				this.button.state = status === 200 ? "is-success" : "has-error";
				this.button.style.backgroundColor =
					status === 200
						? "var(--color-positive)"
						: "var(--color-negative-light)";
			} else {
				this.button.state = "has-response";
				this.button.style.backgroundColor = "var(--color-text)";
			}

			if (color) {
				this.button.style.reset = false;
				this.button.style.color = color;
			}

			if (backgroundColor) {
				this.button.style.reset = false;
				this.button.style.backgroundColor = backgroundColor;
			}

			if (resetStyle) {
				this.button.style.reset = resetStyle;
			}

			if (reload) {
				location.reload();
			}

			if (open) {
				if (this.intab) {
					this.urlRequest = open;
					this.$nextTick(() => {
						this.simulateClick(this.$refs.tabAnchor);
					});
				} else {
					location.href = open;
				}
			}

			if (download) {
				this.downloadRequest = download;
				this.$nextTick(() => {
					this.simulateClick(this.$refs.downloadAnchor);
				});
			}

			if (clipboard) {
				this.clipboardRequest = clipboard;
				this.button.label = this.progress;
				this.button.state = "is-success";

				setTimeout(this.resetButton, this.cooldown);

				this.$nextTick(() => {
					this.copyToClipboard(this.clipboardRequest);
				});
			} else {
				setTimeout(this.resetButton, this.cooldown);
			}
		},

		resetButton() {
			this.button.label = null;
			this.button.state = null;
			this.button.style = this.button.style?.reset
				? null
				: this.button.style;
		},

		simulateClick(element) {
			const evt = new MouseEvent("click", {
				bubbles: true,
				cancelable: true,
				view: window
			});

			element.dispatchEvent(evt);
		},

		async copyToClipboard(content) {
			try {
				await navigator.clipboard.writeText(content);
			} catch (err) {
				console.error("navigator.clipboard is not available");
			}
		}
	}
};
</script>

<style>
.janitor {
	background-color: var(--color-text);
	color: white;
	border-radius: 3px;
	padding: 0.5rem 1rem;
	line-height: 1.25rem;
	text-align: left;
}

.janitor:hover {
	background-color: #222;
}

.janitor .k-button-text {
	opacity: 1;
}

.janitor.is-running {
	background-color: var(--color-border) !important;
	color: white;
	cursor: wait;
}

.janitor[aria-disabled="true"] {
	background-color: var(--color-border) !important;
}

.visually-hidden {
	position: absolute;
	width: 1px;
	height: 1px;
	border: 0;
	padding: 0;
	margin: 0;
	clip-path: inset(50%);
	overflow: hidden;
	white-space: nowrap;
}
</style>
