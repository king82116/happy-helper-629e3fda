const fs = require('fs');
const s = fs.readFileSync('assets/js/index-78f9aa25.js', 'utf8');
const keys = ['onMounted', 'push({', 'replace({', 'query.id', 'typeId', 'login', 'GetTypeList', 'redirect', 'AllLotteryGames'];
for (const k of keys) {
  let idx = 0, n = 0;
  while ((idx = s.indexOf(k, idx)) !== -1 && n < 2) {
    console.log('\n---', k, '---');
    console.log(s.substring(Math.max(0, idx - 60), idx + 200));
    idx += k.length;
    n++;
  }
}
