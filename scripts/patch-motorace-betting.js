/**
 * MotoRace bet clicks silently fail when issueData.gameCode is missing.
 * Relax the guard so bets work when gameCode is absent (we are already on the game page).
 */
const fs = require('fs');
const path = require('path');

const fp = path.join(__dirname, '..', 'assets', 'js', 'page-saasLottery-MotoRace-37e89d19.js');
let s = fs.readFileSync(fp, 'utf8');
const MARK = '/*MR_BET_V1*/';
if (s.includes(MARK)) {
	console.log('MotoRace betting patch already applied');
	process.exit(0);
}

const replacements = [
	[
		'if(!B.value&&((s=N.value)==null?void 0:s.gameCode)===le.value){',
		MARK + 'if(!B.value&&(!((s=N.value)==null?void 0:s.gameCode)||((s=N.value)==null?void 0:s.gameCode)===le.value)){',
	],
	[
		'if(B.value||((g=N.value)==null?void 0:g.gameCode)!==le.value)return',
		MARK + 'if(B.value||(((g=N.value)==null?void 0:g.gameCode)&&((g=N.value)==null?void 0:g.gameCode)!==le.value))return',
	],
	[
		'if(b.playType&&((t=N.value)==null?void 0:t.gameCode)===le.value){',
		MARK + 'if(b.playType&&(!((t=N.value)==null?void 0:t.gameCode)||((t=N.value)==null?void 0:t.gameCode)===le.value)){',
	],
];

let changed = 0;
for (const [find, replace] of replacements) {
	if (s.includes(find)) {
		s = s.replace(find, replace);
		changed++;
		console.log('patched:', find.slice(0, 60));
	} else {
		console.warn('NOT FOUND:', find.slice(0, 60));
	}
}

if (changed) {
	fs.writeFileSync(fp, s);
	console.log('Done —', changed, 'MotoRace betting patch(es)');
} else {
	console.log('No changes made');
}
