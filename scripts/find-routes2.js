const fs = require('fs');
const files = fs.readdirSync('assets/js').filter((f) => f.endsWith('.js'));
const pattern = /AllLotteryGames-[A-Za-z0-9]+/g;
const names = new Set();
for (const f of files) {
  const s = fs.readFileSync('assets/js/' + f, 'utf8');
  let m;
  while ((m = pattern.exec(s))) names.add(m[0]);
}
console.log('count', names.size);
[...names].sort().forEach((n) => console.log(n));
