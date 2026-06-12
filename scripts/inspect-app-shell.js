const fs = require('fs');
const s = fs.readFileSync('assets/js/index-56d54098.js', 'utf8');
for (const k of ['LoadingView', 'isGame', 'router-view', 'RouterView', 'AllLotteryGames', 'loading.value']) {
  let i = 0, n = 0;
  while ((i = s.indexOf(k, i)) !== -1 && n < 3) {
    console.log('\n---', k, '---');
    console.log(s.substring(Math.max(0,i-100), i+250));
    i += k.length; n++;
  }
}
