const fs = require('fs');
const s = fs.readFileSync('assets/js/index-78f9aa25.js', 'utf8');
const start = s.indexOf('__name:"index",setup(l){const G=Te().query.typeId');
console.log(s.substring(start, start + 2000));
