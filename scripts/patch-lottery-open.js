const fs = require('fs');

// 1. Bypass isAlowGame on home lottery cards + pass default typeId
const homePath = 'assets/js/page-home-other-ae227260.js';
let home = fs.readFileSync(homePath, 'utf8');

const oldHandler =
  'a=o=>{if(d(o))return u({...o,vendorCode:"ARLottery"});const idx=n.findIndex(l=>Number(l.value)===Number(o.id));if(idx<0)return console.error("no lottery route",o.id);Ae.push({name:"AllLotteryGames-"+n[idx].path,query:{id:o.id}})}';

const newHandler =
  'a=o=>{if(d(o))return u({...o,vendorCode:"ARLottery"});const idx=n.findIndex(l=>Number(l.value)===Number(o.id));if(idx<0)return console.error("no lottery route",o.id);const typeMap={1:1,2:9,3:5,4:13};Ae.push({name:"AllLotteryGames-"+n[idx].path,query:{id:o.id,typeId:o.typeId||typeMap[Number(o.id)]}})}';

if (!home.includes(oldHandler)) {
  console.error('home handler pattern not found');
  process.exit(1);
}
home = home.replace(oldHandler, newHandler);

const oldClick = 'onClick:z=>t(p)(g,a)';
const newClick = 'onClick:z=>a(g)';
if (!home.includes(oldClick)) {
  console.error('home click pattern not found');
  process.exit(1);
}
home = home.replace(oldClick, newClick);
fs.writeFileSync(homePath, home);
console.log('patched', homePath);

// 2. Skip recharge gate for built-in lottery categories in isAlowGame
const actPath = 'assets/js/page-activity-ActivityDetail-f8bc6e8e.js';
let act = fs.readFileSync(actPath, 'utf8');
const oldAlow =
  'B = async (k, F) => {\n        if (z.value) {';
const newAlow =
  'B = async (k, F) => {\n        if (k && !k.gameCode && Number(k.id) >= 1 && Number(k.id) <= 8) return F(k);\n        if (z.value) {';
if (!act.includes(oldAlow)) {
  console.error('isAlowGame pattern not found');
  process.exit(1);
}
act = act.replace(oldAlow, newAlow);
fs.writeFileSync(actPath, act);
console.log('patched', actPath);
