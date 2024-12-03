(function(){"use strict";function p(e,t,s,a,i,u,r,l){var o=typeof e=="function"?e.options:e;return t&&(o.render=t,o.staticRenderFns=s,o._compiled=!0),{exports:e,options:o}}const n="janitor.runAfterAutosave",m={props:{autosave:Boolean,backgroundColor:String,clipboard:Boolean,color:String,confirm:String,command:String,cooldown:Number,error:String,icon:String,intab:Boolean,help:String,label:String,progress:String,success:String,status:String,unsaved:Boolean},data(){return{button:{label:null,state:null,help:null,style:null},clipboardRequest:null,downloadRequest:null,icons:{"is-running":"janitorLoader","is-success":"check","has-error":"alert"},isUnsaved:!1,urlRequest:null}},computed:{buttonStyle(){return this.button.style||{color:this.color,backgroundColor:this.backgroundColor}},currentIcon(){return this.icons[this.status]??this.icon},id(){return"janitor-"+this.hashCode(this.command+(this.button.label??"")+this.label)},hasChanges(){return Object.keys(this.$panel.content.changes()).length>0}},created(){this.$events.$on("model.update",()=>sessionStorage.getItem(n)&&location.reload()),sessionStorage.getItem(n)===this.id&&(sessionStorage.removeItem(n),this.runJanitor())},methods:{hashCode(e){let t=0;if(e.length===0)return t;for(const s of e)t=(t<<5)-t+e.charCodeAt(s),t=t&t;return t},async runJanitor(){if(!(this.confirm&&!window.confirm(this.confirm))){if(this.autosave&&this.hasChanges){const e=document.querySelector(".k-panel .k-header-buttons .k-form-controls button:nth-child(2)");if(e){this.isUnsaved=!1,sessionStorage.setItem(n,this.id),this.simulateClick(e);return}}if(this.clipboardRequest){await this.copyToClipboard(this.clipboardRequest),this.resetButton(),this.clipboardRequest=null;return}this.status||await this.postRequest("plugin-janitor",{command:this.command})}},async postRequest(e,t){this.button.label=this.progress??`${this.label} …`,this.button.state="is-running";const{label:s,message:a,status:i,reload:u,open:r,download:l,clipboard:o,success:k,error:C,icon:c,help:h,color:d,backgroundColor:b,resetStyle:f}=await this.$api.post(e,t);i===200?this.button.label=k??this.success:this.button.label=C??this.error,s&&(this.label=s),a&&(this.button.label=a),h&&(this.button.help=h),c&&(this.icon=c),this.button.style={color:"white",reset:!0},i?(this.button.state=i===200?"is-success":"has-error",this.button.style.backgroundColor=i===200?"var(--color-positive)":"var(--color-negative-light)"):this.button.state="has-response",d&&(this.button.style.reset=!1,this.button.style.color=d),b&&(this.button.style.reset=!1,this.button.style.backgroundColor=b),f&&(this.button.style.reset=f),u&&location.reload(),r&&(this.intab?(this.urlRequest=r,this.$nextTick(()=>{this.simulateClick(this.$refs.tabAnchor)})):location.href=r),l&&(this.downloadRequest=l,this.$nextTick(()=>{this.simulateClick(this.$refs.downloadAnchor)})),o?(this.clipboardRequest=o,this.button.label=this.progress,this.button.state="is-success",setTimeout(this.resetButton,this.cooldown),this.$nextTick(()=>{this.copyToClipboard(this.clipboardRequest)})):setTimeout(this.resetButton,this.cooldown)},resetButton(){var e;this.button.label=null,this.button.state=null,this.button.style=(e=this.button.style)!=null&&e.reset?null:this.button.style},simulateClick(e){const t=new MouseEvent("click",{bubbles:!0,cancelable:!0,view:window});e.dispatchEvent(t)},async copyToClipboard(e){try{await navigator.clipboard.writeText(e)}catch{console.error("navigator.clipboard is not available")}}}};var g=function(){var t=this,s=t._self._c;return s("div",{staticClass:"janitor-wrapper"},[s("k-button",{class:["k-button janitor",t.button.state],style:t.buttonStyle,attrs:{id:t.id,icon:t.currentIcon,command:t.command,disabled:!t.unsaved&&!t.isUnsaved&&t.hasChanges,variant:"filled"},on:{click:t.runJanitor}},[t._v(" "+t._s(t.button.label||t.label)+" ")]),t.button.help||t.help?s("k-text",{staticClass:"k-field-help",attrs:{theme:"help",html:t.button.help||t.help}}):t._e(),s("a",{directives:[{name:"show",rawName:"v-show",value:t.downloadRequest,expression:"downloadRequest"}],ref:"downloadAnchor",staticClass:"visually-hidden",attrs:{href:t.downloadRequest,download:""}}),s("a",{directives:[{name:"show",rawName:"v-show",value:t.urlRequest,expression:"urlRequest"}],ref:"tabAnchor",staticClass:"visually-hidden",attrs:{href:t.urlRequest,target:"_blank"}})],1)},v=[],w=p(m,g,v);const y=w.exports;window.panel.plugin("bnomei/janitor",{fields:{janitor:y},icons:{janitorLoader:'<svg viewBox="0 0 24 24" version="1.1" id="svg2" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd" id="g2" transform="matrix(1.2373578,0,0,1.2393776,2.1011378,2.0846672)"><g transform="translate(1,1)" stroke-width="1.75" id="g1"><circle cx="7" cy="7" r="7.2" stroke="#000" stroke-opacity=".2" id="circle1"/><path d="M 14.2,7 C 14.2,3 11,-0.2 7,-0.2" stroke="#000" id="path1"><animateTransform attributeName="transform" type="rotate" from="0 7 7" to="360 7 7" dur="1s" repeatCount="indefinite"/></path></g></g></svg>'}})})();
