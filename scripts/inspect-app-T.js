const fs = require('fs');
const s = fs.readFileSync('assets/js/index-56d54098.js', 'utf8');
// Find App component setup - search for T=_(!0) or T.value=!1
const patterns = ['T=_(!0)', 'T.value=!1', 'T.value=!0', 'getAllGame', 'allGameList', 'watchEffect', 'onMounted'];
for (const p of patterns) {
  let idx = 0, n = 0;
  while ((idx = s.indexOf(p, idx)) !== -1 && n < 2) {
    console.log('\n===', p, 'at', idx, '===');
    console.log(s.substring(idx - 120, idx + 200));
    idx += p.length; n++;
  }
}
