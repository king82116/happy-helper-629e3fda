const fs = require('fs');
const s = fs.readFileSync('assets/js/page-home-other-ae227260.js', 'utf8');
const i = s.indexOf('__name:"lottery"');
console.log('index:', i);
console.log(s.substring(i - 80, i + 1500));
