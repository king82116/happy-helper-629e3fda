import{V as v,m as d,Z as a,ad as c,p as t,N as p,ap as n,aq as m,ar as f,o as r}from"./common.modules-3e3c160a.js";import{g as u,_ as w}from"./page-activity-ActivityDetail-f8bc6e8e.js";window.getBuildInfo=function(){return{buildTime:"4/19/2025, 2:01:23 PM",branch:" commitId:8f9a6b77f997bcd1b23c3d9fa0b70bf5488d0b97"}};const _=e=>(m("data-v-d0cbe577"),e=e(),f(),e),h={class:"wallet_header"},g={key:0,class:"no_active"},b={class:"ar_logo"},C={class:"logo"},A=["src"],I={class:"ar_amount_txt"},B={class:"ar_amount"},W=_(()=>t("span",null,"ARB",-1)),V=_(()=>t("div",{class:"divider"},null,-1)),k=v({__name:"walletInfo",props:{isActive:{type:Boolean,default:!1},arWallet:{type:Object,default:()=>{}}},emits:["goWallet"],setup(e,{emit:l}){return(s,o)=>{var i;return r(),d("div",h,[e.isActive?c("v-if",!0):(r(),d("div",g,a(s.$t("arNoActive2")),1)),t("div",b,[t("div",C,[t("img",{src:p(u)("common","ar_wallet"),alt:""},null,8,A),n(" AR"+a(s.$t("wallet")),1)]),t("div",{class:"go_wallet",onClick:o[0]||(o[0]=y=>l("goWallet"))},a(s.$t("comminWallet")),1)]),t("div",I,a(s.$t("walletBalance")),1),t("div",B,[n(a(e.isActive?(i=e.arWallet)==null?void 0:i.balance:0)+" ",1),W]),V,c(` <div class="ar_address">
			<div class="ad_title">
				<div>{{$t('walletAddress')}}:</div>
				<div>{{ isActive? arWallet?.walletAddress : '' }} </div>
			</div>

			<svg xmlns="http://www.w3.org/2000/svg" width="14" height="16" viewBox="0 0 14 16" fill="none" @click="copy(arWallet?.walletAddress)">
				<path
					fill-rule="evenodd"
					clip-rule="evenodd"
					d="M3.5 1.125C3.5 0.572716 3.94772 0.125 4.5 0.125H13C13.5523 0.125 14 0.572715 14 1.125V11.375C14 11.9273 13.5523 12.375 13 12.375H11.375V4.25C11.375 3.42157 10.7034 2.75 9.875 2.75H3.5V1.125ZM1 3.625C0.447715 3.625 0 4.07272 0 4.625V14.875C0 15.4273 0.447715 15.875 1 15.875H9.5C10.0523 15.875 10.5 15.4273 10.5 14.875V4.625C10.5 4.07272 10.0523 3.625 9.5 3.625H1Z"
					fill="#B6BAC5"
				/>
			</svg>
		</div> `)])}}});const N=w(k,[["__scopeId","data-v-d0cbe577"],["__file","/usr/local/jenkins-prod/workspace/ar077-india-jalwa/src/views/arWallet/components/walletInfo.vue"]]);export{N as W};
//# sourceMappingURL=page-arWallet-components-5bd2bdf5.js.map
