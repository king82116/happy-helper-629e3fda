const fs = require('fs');
const s = fs.readFileSync('assets/js/page-activity-ActivityDetail-f8bc6e8e.js', 'utf8');
const re = /"\.\.\/views\/home\/AllLotteryGames\/([^/]+)\/index\.vue"/g;
const routes = [];
let m;
while ((m = re.exec(s))) {
  const parts = m[0].replace(/"/g, '').split('/');
  // ../views/home/AllLotteryGames/WinGo/index.vue
  const p = parts.slice(2); // views, home, AllLotteryGames, WinGo, index.vue - wait
  const full = m[0];
  const segs = full.match(/\.\.\/views\/(.+)\/index\.vue/)[1].split('/');
  const path = '/' + segs.join('/');
  const name = segs.slice(-2).join('-'); // AllLotteryGames-WinGo if segs = home, AllLotteryGames, WinGo
  routes.push({ raw: segs, path: '/' + segs.join('/'), name: segs[1] + '-' + segs[2] });
}
console.log('sample routes:');
routes.filter(r => ['WinGo','K3','5D','WinTrx'].includes(r.raw[2])).forEach(r => console.log(r));
