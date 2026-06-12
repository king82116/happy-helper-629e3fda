const fs = require('fs');
const path = 'assets/js/page-home-other-ae227260.js';
let s = fs.readFileSync(path, 'utf8');
const old =
  'a=o=>{if(d(o))return u({...o,vendorCode:"ARLottery"});Ae.push({name:"AllLotteryGames-"+n[n.findIndex(l=>l.value===o.id)].path,query:{id:o.id}})}';
const neu =
  'a=o=>{if(d(o))return u({...o,vendorCode:"ARLottery"});const idx=n.findIndex(l=>Number(l.value)===Number(o.id));if(idx<0)return console.error("no lottery route",o.id);Ae.push({name:"AllLotteryGames-"+n[idx].path,query:{id:o.id}})}';
if (!s.includes(old)) {
  console.error('pattern not found');
  process.exit(1);
}
s = s.replace(old, neu);
fs.writeFileSync(path, s);
console.log('patched page-home-other-ae227260.js');
