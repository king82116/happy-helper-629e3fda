const fs = require('fs');
const home = fs.readFileSync('assets/js/page-home-other-ae227260.js', 'utf8');
const i = home.indexOf('__name:"lottery"');
console.log('LOTTERY BLOCK:\n', home.substring(i - 30, i + 950));

const act = fs.readFileSync('assets/js/page-activity-ActivityDetail-f8bc6e8e.js', 'utf8');
const j = act.indexOf('B = async (k, F)');
console.log('\nISALOWGAME:\n', act.substring(j, j + 900));

// brace check lottery
const start = home.indexOf('Ct=C({__name:"lottery"');
const end = home.indexOf('}}});const Lt=L(Ct,');
console.log('\nbrace end marker found:', end > start, 'depth check...');
if (start >= 0 && end >= 0) {
  const block = home.substring(start, end + 5);
  let d = 0;
  for (const ch of block) { if (ch === '{') d++; if (ch === '}') d--; }
  console.log('brace balance:', d);
}
