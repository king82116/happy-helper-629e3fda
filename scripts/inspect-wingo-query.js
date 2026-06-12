const fs = require('fs');
const s = fs.readFileSync('assets/js/index-78f9aa25.js', 'utf8');
for (const k of ['typeId', 'currentRoute', 'query', 'AllLotteryGames']) {
  let i = 0, n = 0;
  while ((i = s.indexOf(k, i)) !== -1 && n < 5) {
    console.log('\n---', k, '---');
    console.log(s.substring(Math.max(0, i - 80), i + 200));
    i += k.length; n++;
  }
}
