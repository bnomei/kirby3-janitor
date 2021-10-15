<template>
  <div class="janitor-wrapper">
    <k-button
      :id="id"
      :class="['janitor', btnClass]"
      :icon="currentIcon"
      :job="job"
      :disabled="!isUnsaved && hasChanges"
      @click="runJanitor"
    >
      {{ btnLabel || label }}
    </k-button>

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
const STORAGE_ID = "janitor.clickAfterAutosave";

export default {
  props: {
    label: String,
    progress: String,
    job: String,
    cooldown: Number,
    status: String,
    data: String,
    pageURI: String,
    clipboard: Boolean,
    unsaved: Boolean,
    autosave: Boolean,
    intab: Boolean,
    confirm: String,
    icon: {
      type: [Boolean, String],
      default: false,
    },
  },

  data() {
    return {
      btnLabel: null,
      btnClass: null,
      downloadRequest: null,
      clipboardRequest: null,
      urlRequest: null,
      isUnsaved: false,
      icons: {
        "is-running": "janitorLoader",
        "is-success": "check",
        "has-error": "alert",
      },
    };
  },

  computed: {
    id() {
      return (
        "janitor-" +
        this.hashCode(this.job + (this.btnLabel ?? "") + this.pageURI)
      );
    },

    hasChanges() {
      return this.$store.getters["content/hasChanges"]();
    },

    currentIcon() {
      return this.icons[this.status] ?? this.icon;
    },
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

      if (this.clipboard) {
        this.clipboardRequest = this.data;
        this.btnLabel = this.progress;
        this.btnClass = "is-success";

        setTimeout(this.resetBtnState, this.cooldown);

        this.$nextTick(() => {
          this.copyToClipboard(this.data);
        });

        return;
      }

      if (this.clipboardRequest) {
        await this.copyToClipboard(this.clipboardRequest);
        this.resetBtnState();
        this.clipboardRequest = null;
        return;
      }

      if (this.status) {
        return;
      }

      let url = this.job + "/" + encodeURIComponent(this.pageURI);

      if (this.data) {
        url = url + "/" + encodeURIComponent(this.data);
      }

      this.getRequest(url);
    },

    async getRequest(url) {
      this.btnLabel = this.progress ?? `${this.label} …`;
      this.btnClass = "is-running";

      const { label, status, reload, href, download, clipboard } =
        await this.$api.get(url);

      if (label) {
        this.btnLabel = label;
      }

      if (status) {
        this.btnClass = status === 200 ? "is-success" : "has-error";
      } else {
        this.btnClass = "has-response";
      }

      if (reload) {
        location.reload();
      }

      if (href) {
        if (this.intab) {
          this.urlRequest = href;
          this.$nextTick(() => {
            this.simulateClick(this.$refs.tabAnchor);
          });
        } else {
          location.href = href;
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
      } else {
        setTimeout(this.resetBtnState, this.cooldown);
      }
    },

    resetBtnState() {
      this.btnLabel = null;
      this.btnClass = null;
    },

    simulateClick(element) {
      const evt = new MouseEvent("click", {
        bubbles: true,
        cancelable: true,
        view: window,
      });

      element.dispatchEvent(evt);
    },

    async copyToClipboard(content) {
      try {
        await navigator.clipboard.writeText(content);
      } catch (err) {
        console.error("navigator.clipboard is not available");
      }
    },
  },
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
  background-color: var(--color-border);
}

.janitor.is-running .k-button-text {
  color: var(--color-text);
}

.janitor.has-response {
  background-color: var(--color-text);
}

.janitor.is-success {
  background-color: var(--color-positive);
}

.janitor.has-error {
  background-color: var(--color-negative-light);
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
