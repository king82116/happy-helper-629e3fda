const fs = require('fs');
const path = 'assets/js/page-home-other-ae227260.js';
const s = fs.readFileSync(path, 'utf8');

console.log('file length:', s.length);
console.log('lines:', s.split('\n').length);

try {
  new Function(s);
  console.log('parse: OK (no syntax error in isolation)');
} catch (e) {
  console.log('parse error:', e.message);
}

const i = s.indexOf('__name:"lottery"');
console.log('\n--- lottery block ---');
console.log(s.substring(i - 50, i + 900));

// Check bracket balance around lottery
const start = s.indexOf('Ct=C({__name:"lottery"');
const end = s.indexOf('}});const Lt=L(Ct,');
console.log('\nstart:', start, 'end:', end);
if (start >= 0 && end >= 0) {
  const block = s.substring(start, end + 4);
  let depth = 0;
  for (const ch of block) {
    if (ch === '{') depth++;
    if (ch === '}') depth--;
  }
  console.log('brace depth at end:', depth, depth === 0 ? 'OK' : 'BROKEN');
}
