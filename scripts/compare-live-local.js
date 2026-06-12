const fs = require('fs');
const live = fs.readFileSync('C:/Users/WIZARD/.cursor/projects/d-NEW-WORK-anoop-sir-Josh-apk-1/agent-tools/eaf4dc5a-045c-414a-a1c6-0e769330c0d8.txt', 'utf8');
const local = fs.readFileSync('assets/js/page-home-other-ae227260.js', 'utf8');
for (const [name, s] of [['LIVE', live], ['LOCAL', local]]) {
  const i = s.indexOf('__name:"lottery"');
  console.log('\n===', name, '===');
  console.log(s.substring(i - 20, i + 700));
}
