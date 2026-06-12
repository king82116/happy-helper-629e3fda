const fs = require('fs');
const path = 'assets/js/page-home-other-ae227260.js';
let s = fs.readFileSync(path, 'utf8');

const start = 'Ct=C({__name:"lottery",setup(v){';
const end = '}});const Lt=L(Ct,';
const si = s.indexOf(start);
const ei = s.indexOf(end);
if (si < 0 || ei < 0) {
  console.error('markers not found', si, ei);
  process.exit(1);
}

const newLottery =
  'Ct=C({__name:"lottery",setup(v){const{homeState:c,isAlowGame:p,isSassLotteryGame:d,openThirdGame:u}=j(),r=x(()=>{var o;return((o=c==null?void 0:c.allGameList)==null?void 0:o.lottery)||[]}),n=[{value:1,path:"WinGo"},{value:3,path:"5D"},{value:2,path:"K3"},{value:4,path:"WinTrx"},{value:5,path:"XoSo"},{value:6,path:"XoSo"},{value:7,path:"Binguo"},{value:8,path:"4D"}],a=o=>{if(d(o))return u({...o,vendorCode:"ARLottery"});const idx=n.findIndex(l=>Number(l.value)===Number(o.id||o.categoryId));if(idx<0)return console.error("no lottery route",o.id);Ae.push({path:"/AllLotteryGames/"+n[idx].path,query:{id:o.id||o.categoryId}})};return(o,l)=>{const m=P("lazy");return s(),i("div",wt,[e("div",kt,_(o.$t("lottery")),1),e("div",yt,[(s(!0),i(w,null,$(r.value,(g,b)=>k((s(),i("img",{key:b,onClick:z=>t(p)(g,a)},null,8,$t)),[[m,g.categoryImg]])),128))])])}});';

s = s.slice(0, si) + newLottery + s.slice(ei + '}});'.length);
fs.writeFileSync(path, s);
console.log('patched lottery component');
