const fs = require('fs');
const s = fs.readFileSync('d:/NEW WORK/anoop sir/Josh.apk (1)/assets/css/page-wallet-WithdrawHistory-730b49d5.css', 'utf8');
['stateR', 'stateG', 'stateReject'].forEach((c) => {
  const i = s.indexOf(c);
  console.log(c, s.slice(i, i + 120));
});
