/**
 * V2 — re-patches the D5 bundle so MotoRace local fetches send the user's
 * Bearer token (read from localStorage['ar_token'] value field).
 * Idempotent: detects v2 marker and skips.
 */
const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..', 'assets', 'js');
const files = fs.readdirSync(root).filter((f) => /^page-saasLottery-D5-.*\.js$/.test(f));

// Helper IIFE injected into every fetch — reads token and returns headers obj
const authHdr = `(function(){try{var t=localStorage.getItem('ar_token');if(t){var p=JSON.parse(t);if(p&&p.value){return{Authorization:'Bearer '+p.value};}}}catch(e){}return{};})()`;

const MARK = '/*MR_V2*/';
const apiBase = `((typeof location!=='undefined'&&location.origin)?location.origin+'/application/api/webapi':'/application/api/webapi')`;
const toQS = `(function(o){var p=new URLSearchParams();for(var k in o){if(o[k]!=null)p.append(k,o[k]);}return p.toString();})`;
const isMoto = `(function(g){return typeof g==='string'&&g.indexOf('MotoRace')===0;})`;

// Replace any patched function bodies with the v2 versions (auth-aware)
const targets = [
	{
		// History (GET) — no auth required, but harmless to send
		name: 'Gt',
		re: /function Gt\(s\)\{[^]*?return oe\.get\("\/Lottery\/GetHistoryIssuePage",s\)\}/,
		build: () =>
			`${MARK}function Gt(s){if(s&&${isMoto}(s.gameCode)){return fetch(${apiBase}+'/GetMotoRaceHistoryIssuePage.php?'+${toQS}(s),{headers:${authHdr}}).then(function(r){return r.json();});}return oe.get("/Lottery/GetHistoryIssuePage",s)}`,
	},
	{
		name: 'Ro',
		re: /function Ro\(s\)\{[^]*?return oe\.get\("\/Lottery\/GetRecordPage",s\)\}/,
		build: () =>
			`${MARK}function Ro(s){if(s&&${isMoto}(s.gameCode)){return fetch(${apiBase}+'/GetMotoRaceRecordPage.php?'+${toQS}(s),{headers:${authHdr}}).then(function(r){return r.json();});}return oe.get("/Lottery/GetRecordPage",s)}`,
	},
	{
		name: 'Wd',
		re: /function Wd\(s\)\{return fetch\([^]*?\.then\(function\(r\)\{return r\.json\(\);\}\)\}/,
		build: () =>
			`${MARK}function Wd(s){var h=${authHdr};h['Content-Type']='application/json';return fetch(${apiBase}+'/MotoRaceBet.php',{method:'POST',headers:h,credentials:'include',body:JSON.stringify(s||{})}).then(function(r){return r.json();})}`,
	},
	{
		name: 'Mo',
		re: /function Mo\(s\)\{[^]*?return oe\.get\("\/Lottery\/GetBetLimit",\{gameCode:s\}\)\}/,
		build: () =>
			`${MARK}function Mo(s){if(${isMoto}(s)){return fetch(${apiBase}+'/GetMotoRaceBetLimit.php?gameCode='+encodeURIComponent(s),{headers:${authHdr}}).then(function(r){return r.json();});}return oe.get("/Lottery/GetBetLimit",{gameCode:s})}`,
	},
	{
		name: 'Fo',
		re: /function Fo\(s\)\{[^]*?return oe\.get\("\/Lottery\/GetWinLossResult",s\)\}/,
		build: () =>
			`${MARK}function Fo(s){if(s&&typeof s.issueNumber==='string'){return fetch(${apiBase}+'/GetMotoRaceWinLossResult.php?'+${toQS}(s),{headers:${authHdr}}).then(function(r){return r.json();}).catch(function(){return{result:true,data:{status:null,winAmount:0}};});}return oe.get("/Lottery/GetWinLossResult",s)}`,
	},
];

let total = 0;
for (const file of files) {
	const fp = path.join(root, file);
	let s = fs.readFileSync(fp, 'utf8');
	let changed = 0;
	for (const { name, re, build } of targets) {
		// Check if already v2: count MR_V2 markers near function name
		const v2Re = new RegExp(`${MARK.replace(/[*\/]/g, '\\$&')}function ${name}\\(s\\)`);
		if (v2Re.test(s)) {
			console.log(`[${file}] ${name}: already v2`);
			continue;
		}
		if (re.test(s)) {
			s = s.replace(re, build());
			changed++;
			console.log(`[${file}] ${name}: rewrote to v2`);
		} else {
			console.warn(`[${file}] ${name}: NOT FOUND — manual check needed`);
		}
	}
	if (changed) {
		fs.writeFileSync(fp, s);
		total += changed;
	}
}
console.log(`Done — ${total} total v2 patch(es) applied.`);