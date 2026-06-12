const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..', 'assets', 'js');
const oldStr =
	'function Do({lotteryCode:s,gameCode:i}){return Ft.get(`/${s}/${i}.json`)}';
const newStr =
	'function Do({lotteryCode:s,gameCode:i}){const b=typeof location!=="undefined"&&location.origin?location.origin+"/application/api/webapi":"/application/api/webapi";return fetch(b+"/GetWingoDrawIssue.php?lotteryCode="+encodeURIComponent(s)+"&gameCode="+encodeURIComponent(i)).then(r=>r.json())}';

const files = ['page-saasLottery-D5-75bb272d.js'];

for (const file of files) {
	const fp = path.join(root, file);
	let s = fs.readFileSync(fp, 'utf8');
	if (!s.includes(oldStr)) {
		if (s.includes('GetWingoDrawIssue.php')) {
			console.log(`[${file}] already patched`);
			continue;
		}
		console.error(`[${file}] pattern not found`);
		process.exitCode = 1;
		continue;
	}
	s = s.replace(oldStr, newStr);
	fs.writeFileSync(fp, s);
	console.log(`[${file}] patched period display`);
}
