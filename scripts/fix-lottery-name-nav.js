const fs = require('fs');
const path = 'assets/js/page-home-other-ae227260.js';
let s = fs.readFileSync(path, 'utf8');

const wrong =
  'Ae.push({path:"/AllLotteryGames/"+n[idx].path,query:{id:o.id||o.categoryId}})';
const right =
  'Ae.push({name:"AllLotteryGames-"+n[idx].path,query:{id:o.id||o.categoryId}})';

if (!s.includes(wrong)) {
  console.error('pattern not found');
  process.exit(1);
}
s = s.replace(wrong, right);
fs.writeFileSync(path, s);
console.log('fixed lottery navigation to use route name');
