<template>
    <div class="janitor-wrapper">
        <k-button
            class="janitor"
            :class="status"
            @click="janitor()"
            :job="job"
        >{{ label }}
        </k-button>
        <a ref="download" class="hidden" :href="downloadRequest" download></a>
        <textarea class="hidden" ref="clipboard">{{ clipboardRequest }}</textarea>
    </div>
</template>

<script>
    export default {
        name: 'Janitor',
        props: {
            label: String,
            progress: String,
            job: String,
            cooldown: Number,
            status: String,
            data: String,
            pageURI: String,
            clipboard: Boolean,
        },
        data() {
            return {
                oldlabel: '',
                downloadRequest: '',
                clipboardRequest: '',
            }
        },
        methods: {
            janitor() {
                if (this.clipboard === true) {
                    // console.log(this.data)
                    this.clipboardRequest = this.data
                    this.oldlabel = this.label
                    this.label = this.progress
                    this.status = 'is-success'
                    let vm = this
                    setTimeout(function () {
                        vm.label = vm.oldlabel
                        vm.status = ''
                    }, this.cooldown)
                    this.$nextTick(function() {
                        vm.copyToClipboard(vm.$refs.clipboard)
                    })
                    return
                }

                if (this.clipboardRequest !== '') {
                    this.copyToClipboard(this.$refs.clipboard)
                    this.label = this.oldlabel
                    this.status = ''
                    this.clipboardRequest = ''
                    return
                }

                let url = this.job
                if (true) {
                    url = url + '/' + encodeURIComponent(this.pageURI)
                }
                if (this.data != undefined) {
                    let data = this.data
                    url = url + '/' + encodeURIComponent(data)
                }
                this.getRequest(url)
            },
            getRequest(url) {
                let that = this
                this.oldlabel = this.label
                this.label = this.progress != undefined && this.progress.length > 0 ? this.progress : this.label + '...'
                this.status = 'doing-job'
                this.$api.get(url)
                    .then(response => {
                            if (response.label !== undefined) {
                                that.label = response.label
                            }
                            if (response.status !== undefined) {
                                that.status = response.status == 200 ? 'is-success' : 'has-error'
                            } else {
                                that.status = 'has-response'
                            }

                            if (response.reload !== undefined && response.reload === true) {
                                location.reload()
                            }
                            if (response.href !== undefined) {
                                location.href = response.href
                            }
                            if (response.download !== undefined) {
                                this.downloadRequest = response.download
                                let vm = this
                                this.$nextTick(function () {
                                    vm.simulateClick(vm.$refs.download)
                                })
                            }
                            if (response.clipboard !== undefined) {
                                this.clipboardRequest = response.clipboard
                            } else {
                                setTimeout(function () {
                                    that.label = that.oldlabel
                                    that.status = ''
                                }, that.cooldown)
                            }
                        }
                    )
            },
            simulateClick(elem) {
                /* https://gomakethings.com/how-to-simulate-a-click-event-with-javascript/ */
                // Create our event (with options)
                let evt = new MouseEvent('click', {
                    bubbles: true,
                    cancelable: true,
                    view: window
                });
                // If cancelled, don't dispatch our event
                let canceled = !elem.dispatchEvent(evt);
            },
            copyToClipboard (elem) {
                var currentFocus, e, isInput, origSelectionEnd, origSelectionStart, succeed, target, targetId;
                targetId = '_hiddenCopyText_';
                isInput = elem.tagName === 'INPUT' || elem.tagName === 'TEXTAREA';
                origSelectionStart = void 0;
                origSelectionEnd = void 0;
                if (isInput) {
                    target = elem;
                    origSelectionStart = elem.selectionStart;
                    origSelectionEnd = elem.selectionEnd;
                } else {
                    target = document.getElementById(targetId);
                    if (!target) {
                        target = document.createElement('textarea');
                        target.style.position = 'absolute';
                        target.style.left = '-9999px';
                        target.style.top = '0';
                        target.id = targetId;
                        document.body.appendChild(target);
                    }
                    target.textContent = elem.textContent;
                }
                currentFocus = document.activeElement;
                target.focus();
                target.setSelectionRange(0, target.value.length);
                succeed = void 0;
                try {
                    succeed = document.execCommand('copy');
                } catch (_error) {
                    e = _error;
                    succeed = false;
                }
                if (currentFocus && typeof currentFocus.focus === 'function') {
                    currentFocus.focus();
                }
                if (isInput) {
                    elem.setSelectionRange(origSelectionStart, origSelectionEnd);
                } else {
                    target.textContent = '';
                }
                return succeed;
            }
        }
    }
</script>

<style lang="postcss" scoped>
    .janitor {
        background-color: black;
        color: white;
        font-weight: bold;
        border-radius: 5px;
        padding: 5px 10px 7px 10px;
        min-width: 200px;
    }

    .janitor:hover {
        opacity: 0.75;
    }

    .janitor .k-button-text {
        opacity: 1;
    }

    .janitor.doing-job {
        background-color: #444;
    }

    .janitor.has-response {
        background-color: #999;
    }

    .janitor.is-success {
        background-color: #5d800d;
    }

    .janitor.has-error {
        background-color: #d16464;
    }

    .hidden {
        position: absolute;
        left: -9999px;
        top: 0px;
    }
</style>
