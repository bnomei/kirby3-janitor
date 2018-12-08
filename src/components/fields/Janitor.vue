<template>
  <k-button class="janitor" :class="status" @click="janitor()" :job="job">{{ label }}</k-button>
</template>

<script>
export default {
  name: 'Janitor',
  props: {
    label: String,
    progress: String,
    job: String,
    cooldown: Number,
    status: String
  },
  methods: {
    janitor() {
      this.getRequest(this.job)
    },
    getRequest (url) {
      let that = this
      let oldlabel = this.label
      this.label = this.progress.length > 0 ? this.progress : this.label + '...'
      this.status = 'doing-job'
      this.$api.get(url)
        .then(response => {
            // console.log(response)
            if(response.label !== undefined) {
              that.label = response.label
            }
            if(response.status !== undefined) {
              that.status = response.status == 200 ? 'is-success' : 'has-error'
            } else {
              that.status = 'has-response'
            }
            setTimeout(function(){
              that.label = oldlabel
              that.status = ''
            }, that.cooldown)
        })
    }
  }
}
</script>

<style lang="postcss">
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
</style>
