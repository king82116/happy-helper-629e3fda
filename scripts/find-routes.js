const fs = require('fs');
const s = fs.readFileSync('assets/js/page-activity-ActivityDetail-f8bc6e8e.js', 'utf8');
const re = /name:"AllLotteryGames[^"]+"/g;
let m;
const names = new Set();
while ((m = re.exec(s))) names.add(m[0]);
console.log('count', names.size);
[...names].sort().forEach((n) => console.log(n));
