const fs = require('fs');
const s = fs.readFileSync('assets/js/page-home-Lottery-b7784fd9.js', 'utf8');
const i = s.indexOf('AllLotteryGames');
console.log(s.substring(i - 200, i + 600));
