const fs = require('fs');

const homePath = 'assets/js/page-home-other-ae227260.js';
let home = fs.readFileSync(homePath, 'utf8');

const oldHandler =
  'a=o=>{if(d(o))return u({...o,vendorCode:"ARLottery"});const idx=n.findIndex(l=>Number(l.value)===Number(o.id));if(idx<0)return console.error("no lottery route",o.id);const typeMap={1:1,2:9,3:5,4:13};Ae.push({name:"AllLotteryGames-"+n[idx].path,query:{id:o.id,typeId:o.typeId||typeMap[Number(o.id)]}})}';

const newHandler =
  'a=o=>{const id=Number(o.id||o.categoryId);if(d(o))return u({...o,vendorCode:"ARLottery"});const idx=n.findIndex(l=>Number(l.value)===id);if(idx<0)return console.error("no lottery route",o);const typeMap={1:1,2:9,3:5,4:13},path=n[idx].path,query={id,typeId:o.typeId||typeMap[id]};const go=()=>{location.hash="#/AllLotteryGames/"+path+"?id="+query.id+"&typeId="+query.typeId};V.push({name:"AllLotteryGames-"+path,query}).catch(go).then(r=>{(!r||r&&typeof r==="object"&&r.message)&&go()})}';

const oldSetupPrefix =
  '__name:"lottery",setup(v){const{homeState:c,isAlowGame:p,isSassLotteryGame:d,openThirdGame:u}=j()';
const newSetupPrefix =
  '__name:"lottery",setup(v){const V=E(),{homeState:c,isSassLotteryGame:d,openThirdGame:u}=j()';

if (!home.includes(oldHandler)) {
  console.error('handler not found');
  process.exit(1);
}
if (!home.includes(oldSetupPrefix)) {
  console.error('setup prefix not found');
  process.exit(1);
}

home = home.replace(oldHandler, newHandler).replace(oldSetupPrefix, newSetupPrefix);
fs.writeFileSync(homePath, home);
console.log('patched', homePath);
