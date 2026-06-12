/**
 * Guard race-animation code against missing statistics / premium data.
 */
const fs = require('fs');
const path = require('path');

const fp = path.join(__dirname, '..', 'assets', 'js', 'page-saasLottery-MotoRace-37e89d19.js');
let s = fs.readFileSync(fp, 'utf8');
const MARK = '/*MR_ANIM_V1*/';
const MARK2 = '/*MR_ANIM_V2*/';
if (s.includes(MARK) && s.includes(MARK2)) {
	console.log('MotoRace animation patch already applied');
	process.exit(0);
}

const replacements = [
	[
		'const p=ue.value[F.value][h]',
		MARK + 'const p=((ue.value[F.value]||[0,0,0])[h]??0)',
	],
	[
		'I.rank=e.premium.split(",").map(l=>parseInt(l))',
		MARK + 'I.rank=(e.premium||"").split(",").filter(Boolean).map(l=>parseInt(l,10)).filter(n=>!isNaN(n))',
	],
	[
		'I.lastRank=((l=te.value[1].premium.split(","))==null?void 0:l.map(t=>parseInt(t)))||[]',
		MARK + 'I.lastRank=((l=(te.value[1]&&te.value[1].premium||"").split(",")).map(t=>parseInt(t,10)).filter(n=>!isNaN(n)))||[]',
	],
	[
		'(e=te.value[0].premium.split(","))==null?void 0:e.map(a=>parseInt(a))',
		MARK + '(e=(te.value[0]&&te.value[0].premium||"").split(",").filter(Boolean))==null?void 0:e.map(a=>parseInt(a,10)).filter(n=>!isNaN(n))',
	],
];

const v2Replacements = [
	[
		'statistics:ue,visibilityStatus:K,issue:Z,getIssue:ge}=C',
		MARK2 +
			'statistics:ue,visibilityStatus:K,issue:Z,getIssue:ge}=C;const _mrStats=(h,n)=>{var a=(ue.value||{})[String(h)];return Array.isArray(a)?(a[n]??0):0}',
	],
	[
		'/*MR_ANIM_V1*/const p=((ue.value[F.value]||[0,0,0])[h]??0)',
		'/*MR_ANIM_V2*/const p=_mrStats(F.value,h)',
	],
];

let changed = 0;
for (const [find, replace] of replacements) {
	if (s.includes(find)) {
		if (s.includes(replace.slice(0, 20))) continue;
		s = s.replace(find, replace);
		changed++;
		console.log('patched:', find.slice(0, 55));
	} else if (s.includes(MARK) && find.includes('const p=ue.value')) {
		console.log('already patched:', find.slice(0, 55));
	} else {
		console.warn('NOT FOUND:', find.slice(0, 55));
	}
}
for (const [find, replace] of v2Replacements) {
	if (s.includes(find) && !s.includes(MARK2)) {
		s = s.replace(find, replace);
		changed++;
		console.log('patched v2:', find.slice(0, 55));
	} else if (s.includes(MARK2)) {
		console.log('v2 already applied');
	}
}

if (changed) {
	fs.writeFileSync(fp, s);
	console.log('Done —', changed, 'animation guard(s)');
}
