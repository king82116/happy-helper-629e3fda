const fs = require('fs');
const s = fs.readFileSync('assets/js/index-56d54098.js', 'utf8');
const i = s.indexOf('__file","/usr/local/jenkins-prod/workspace/ar077-india-jalwa/entrance/ar077/App.vue"');
const chunk = s.substring(i - 4000, i);
// find T.value= or loading
const markers = ['T.value', 'g.value', 'isGame', 'getAllGame', 'd.value', 'type:"loading"'];
for (const m of markers) {
  const idx = chunk.lastIndexOf(m);
  if (idx >= 0) console.log(m + ':', chunk.substring(idx - 80, idx + 120));
}
