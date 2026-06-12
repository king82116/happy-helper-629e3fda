/**
 * MotoRace race/result fixes:
 * - Set currentResult when history list[0] matches live issue
 * - Guard getOpenLottery on empty list
 * - Normalize statistics to { "1":[0,0,0], ... } shape
 */
const fs = require('fs');
const path = require('path');

const fp = path.join(__dirname, '..', 'assets', 'js', 'page-saasLottery-MotoRace-37e89d19.js');
let s = fs.readFileSync(fp, 'utf8');
const MARK = '/*MR_RESULT_V1*/';

if (s.includes(MARK)) {
	console.log('MotoRace results patch already applied');
	process.exit(0);
}

const normStats =
	MARK +
	'(function(st){var o={};for(var i=1;i<=10;i++)o[String(i)]=[0,0,0];if(st&&typeof st==="object"){for(var k in st){if(Array.isArray(st[k]))o[k]=st[k];}}return o;})';

const replacements = [
	[
		'j.value=u.statistics||{},b.historyIssuesTotalPage=u.totalPage||0',
		`j.value=${normStats}(u.statistics);b.historyIssuesTotalPage=u.totalPage||0;if(!I.value&&U.value){var _mr0=(u.list||[])[0];if(_mr0&&_mr0.issueNumber===U.value)I.value=_mr0}`,
	],
	[
		'const g=u[0];return g.issueNumber!==t?{list:r.list,item:null}:{item:g,list:r.list}',
		MARK +
			'const g=u[0];if(!g||g.issueNumber!==t)return{list:r.list,item:null};return{item:g,list:r.list}',
	],
	[
		'oe.value&&(r=te.value[1].issueNumber)',
		MARK + 'oe.value&&te.value[1]&&(r=te.value[1].issueNumber)',
	],
	[
		'S&&(S.text=te.value[0].issueNumber)',
		MARK + 'S&&te.value[0]&&(S.text=te.value[0].issueNumber)',
	],
];

let changed = 0;
for (const [find, replace] of replacements) {
	if (s.includes(find)) {
		s = s.replace(find, replace);
		changed++;
		console.log('patched:', find.slice(0, 55));
	} else {
		console.warn('NOT FOUND:', find.slice(0, 55));
	}
}

if (changed) {
	fs.writeFileSync(fp, s);
	console.log('Done —', changed, 'result patch(es)');
}
