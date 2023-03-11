(function() {
  "use strict";
  var render = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c("div", { staticClass: "janitor-wrapper" }, [_c("k-button", { class: ["janitor", _vm.button.state], style: _vm.buttonStyle, attrs: { "id": _vm.id, "icon": _vm.currentIcon, "command": _vm.command, "disabled": !_vm.unsaved && !_vm.isUnsaved && _vm.hasChanges }, on: { "click": _vm.runJanitor } }, [_vm._v(" " + _vm._s(_vm.button.label || _vm.label) + " ")]), _vm.button.help || _vm.help ? _c("k-text", { staticClass: "k-field-help", attrs: { "theme": "help", "html": _vm.button.help || _vm.help } }) : _vm._e(), _c("a", { directives: [{ name: "show", rawName: "v-show", value: _vm.downloadRequest, expression: "downloadRequest" }], ref: "downloadAnchor", staticClass: "visually-hidden", attrs: { "href": _vm.downloadRequest, "download": "" } }), _c("a", { directives: [{ name: "show", rawName: "v-show", value: _vm.urlRequest, expression: "urlRequest" }], ref: "tabAnchor", staticClass: "visually-hidden", attrs: { "href": _vm.urlRequest, "target": "_blank" } })], 1);
  };
  var staticRenderFns = [];
  render._withStripped = true;
  var Janitor_vue_vue_type_style_index_0_lang = "";
  function normalizeComponent(scriptExports, render2, staticRenderFns2, functionalTemplate, injectStyles2, scopeId, moduleIdentifier, shadowMode) {
    var options = typeof scriptExports === "function" ? scriptExports.options : scriptExports;
    if (render2) {
      options.render = render2;
      options.staticRenderFns = staticRenderFns2;
      options._compiled = true;
    }
    if (functionalTemplate) {
      options.functional = true;
    }
    if (scopeId) {
      options._scopeId = "data-v-" + scopeId;
    }
    var hook;
    if (moduleIdentifier) {
      hook = function(context) {
        context = context || this.$vnode && this.$vnode.ssrContext || this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext;
        if (!context && typeof __VUE_SSR_CONTEXT__ !== "undefined") {
          context = __VUE_SSR_CONTEXT__;
        }
        if (injectStyles2) {
          injectStyles2.call(this, context);
        }
        if (context && context._registeredComponents) {
          context._registeredComponents.add(moduleIdentifier);
        }
      };
      options._ssrRegister = hook;
    } else if (injectStyles2) {
      hook = shadowMode ? function() {
        injectStyles2.call(this, (options.functional ? this.parent : this).$root.$options.shadowRoot);
      } : injectStyles2;
    }
    if (hook) {
      if (options.functional) {
        options._injectStyles = hook;
        var originalRender = options.render;
        options.render = function renderWithStyleInjection(h, context) {
          hook.call(context);
          return originalRender(h, context);
        };
      } else {
        var existing = options.beforeCreate;
        options.beforeCreate = existing ? [].concat(existing, hook) : [hook];
      }
    }
    return {
      exports: scriptExports,
      options
    };
  }
  const STORAGE_ID = "janitor.runAfterAutosave";
  const script = {
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
        return this.button.style || {
          color: this.color,
          backgroundColor: this.backgroundColor
        };
      },
      currentIcon() {
        var _a;
        return (_a = this.icons[this.status]) != null ? _a : this.icon;
      },
      id() {
        var _a;
        return "janitor-" + this.hashCode(this.command + ((_a = this.button.label) != null ? _a : "") + this.label);
      },
      hasChanges() {
        return this.$store.getters["content/hasChanges"]();
      }
    },
    created() {
      this.$events.$on("model.update", () => sessionStorage.getItem(STORAGE_ID) && location.reload());
      if (sessionStorage.getItem(STORAGE_ID) === this.id) {
        sessionStorage.removeItem(STORAGE_ID);
        this.runJanitor();
      }
    },
    methods: {
      hashCode(str) {
        let hash = 0;
        if (str.length === 0) {
          return hash;
        }
        for (const i of str) {
          hash = (hash << 5) - hash + str.charCodeAt(i);
          hash = hash & hash;
        }
        return hash;
      },
      async runJanitor() {
        if (this.confirm && !window.confirm(this.confirm)) {
          return;
        }
        if (this.autosave && this.hasChanges) {
          const saveButton = document.querySelector(".k-panel .k-form-buttons .k-view").lastChild;
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
        var _a;
        this.button.label = (_a = this.progress) != null ? _a : `${this.label} \u2026`;
        this.button.state = "is-running";
        const { label, message, status, reload, open, download, clipboard, success, error, icon, help, color, backgroundColor, resetStyle } = await this.$api.post(path, data);
        if (status === 200) {
          this.button.label = success != null ? success : this.success;
        } else {
          this.button.label = error != null ? error : this.error;
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
          this.button.style.backgroundColor = status === 200 ? "var(--color-positive)" : "var(--color-negative-light)";
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
        this.button.style = this.button.style.reset ? null : this.button.style;
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
  const __cssModules = {};
  var __component__ = /* @__PURE__ */ normalizeComponent(script, render, staticRenderFns, false, injectStyles, null, null, null);
  function injectStyles(context) {
    for (let o in __cssModules) {
      this[o] = __cssModules[o];
    }
  }
  __component__.options.__file = "src/components/fields/Janitor.vue";
  var Janitor = /* @__PURE__ */ function() {
    return __component__.exports;
  }();
  window.panel.plugin("bnomei/janitor", {
    fields: {
      janitor: Janitor
    },
    icons: {
      janitorLoader: '<g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="1.75"><circle cx="7" cy="7" r="7.2" stroke="#000" stroke-opacity=".2"/><path d="M14.2,7c0-4-3.2-7.2-7.2-7.2" stroke="#000"><animateTransform attributeName="transform" type="rotate" from="0 7 7" to="360 7 7" dur="1s" repeatCount="indefinite"/></path></g></g>'
    }
  });
})();
