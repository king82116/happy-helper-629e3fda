const fs = require('fs');
const s = fs.readFileSync('assets/js/index-56d54098.js', 'utf8');
const idx = s.indexOf('okwinHome2');
console.log('found at', idx);
console.log(s.substring(idx - 500, idx + 2000));
