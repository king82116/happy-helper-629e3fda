const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');

const files = [
  'assets/js/page-saasLottery-D5-75bb272d.js',
  'assets/js/WinningTips-98fc5cc5.js',
  'assets/js/index-e4c6eda2.js',
  'assets/js/index-b9f36021.js',
  'assets/js/index-49c0da85.js',
  'assets/js/page-saasLottery-K3-3be3460c.js',
  'assets/js/page-activity-ActivityDetail-f8bc6e8e.js',
  'assets/js/en-4d7027b5.js',
];

const replacements = [
  [
    /\{pattern:\/\^\(\?:\[1-9\]\|\[1-9\]\\d\{1,2\}\|1000\)\$\/,/g,
    '{pattern:/^[1-9]\\d*$/',
  ],
  [/\{pattern:\/\^\[1-9\]\\d\*\$\//g, '{pattern:/^[1-9]\\d*$/'],
  ['maxlength:4,onInput', 'maxlength:10,onInput'],
  ['type:"digit",maxlength:4', 'type:"digit",maxlength:10'],
  [':maxlength="4"', ':maxlength="10"'],
  [
    'Round must be a positive integer between 1 and 1000',
    'Round must be a positive integer',
  ],
];

for (const rel of files) {
  const file = path.join(root, rel);
  if (!fs.existsSync(file)) {
    console.log('Skip missing', rel);
    continue;
  }

  let content = fs.readFileSync(file, 'utf8');
  let changed = false;

  for (const [from, to] of replacements) {
    if (typeof from === 'string') {
      if (content.includes(from) && content !== content.split(from).join(to)) {
        content = content.split(from).join(to);
        changed = true;
      }
    } else if (from.test(content)) {
      content = content.replace(from, to);
      changed = true;
    }
  }

  if (changed) {
    fs.writeFileSync(file, content);
    console.log('Updated', rel);
  } else {
    console.log('No changes', rel);
  }
}
