/**
 * Patches the shared D5 saas bundle so that MotoRace API calls hit local PHP
 * endpoints instead of https://h5.ar-lottery01.com/api/Lottery/*.
 *
 * Targets inside page-saasLottery-D5-*.js (minified):
 *   function Gt(s){return oe.get("/Lottery/GetHistoryIssuePage",s)}
 *   function Ro(s){return oe.get("/Lottery/GetRecordPage",s)}
 *   function Wd(s){return oe.post("/Lottery/MotoRaceBet",s)}
 *   function Mo(s){return oe.get("/Lottery/GetBetLimit",{gameCode:s})}
 *   function Fo(s){return oe.get("/Lottery/GetWinLossResult",s)}
 *
 * Each is wrapped to detect a MotoRace gameCode and reroute to
 *   /application/api/webapi/<LocalEndpoint>.php
 * Non-MotoRace calls fall through to the original SaaS request unchanged.
 */
const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..', 'assets', 'js');
const files = fs.readdirSync(root).filter(f => /^page-saasLottery-D5-.*\.js$/.test(f));

const isMoto = `(function(g){return typeof g==='string'&&g.indexOf('MotoRace')===0;})`;
const apiBase = `((typeof location!=='undefined'&&location.origin)?location.origin+'/application/api/webapi':'/application/api/webapi')`;
const toQS = `(function(o){var p=new URLSearchParams();for(var k in o){if(o[k]!=null)p.append(k,o[k]);}return p.toString();})`;
const getTok = `(function(){try{return localStorage.getItem('ar_token')||'';}catch(e){return'';}})`;
const authHdr = `(function(){var t=${getTok}();return t?{Authorization:'Bearer '+t}:{};})`;

const patches = [
	{
		find: 'function Gt(s){return oe.get("/Lottery/GetHistoryIssuePage",s)}',
		replace:
			`function Gt(s){if(s&&${isMoto}(s.gameCode)){return fetch(${apiBase}+'/GetMotoRaceHistoryIssuePage.php?'+${toQS}(s)).then(function(r){return r.json();}).then(function(d){return d;});}return oe.get("/Lottery/GetHistoryIssuePage",s)}`,
	},
	{
		find: 'function Ro(s){return oe.get("/Lottery/GetRecordPage",s)}',
		replace:
			`function Ro(s){if(s&&${isMoto}(s.gameCode)){return fetch(${apiBase}+'/GetMotoRaceRecordPage.php?'+${toQS}(s),{headers:${authHdr}()}).then(function(r){return r.json();});}return oe.get("/Lottery/GetRecordPage",s)}`,
	},
	{
		find: 'function Wd(s){return oe.post("/Lottery/MotoRaceBet",s)}',
		replace:
			`function Wd(s){var h={'Content-Type':'application/json'};var a=${authHdr}();for(var k in a)h[k]=a[k];return fetch(${apiBase}+'/MotoRaceBet.php',{method:'POST',headers:h,credentials:'include',body:JSON.stringify(s||{})}).then(function(r){return r.json();})}`,
	},
	{
		find: 'function Mo(s){return oe.get("/Lottery/GetBetLimit",{gameCode:s})}',
		replace:
			`function Mo(s){if(${isMoto}(s)){return fetch(${apiBase}+'/GetMotoRaceBetLimit.php?gameCode='+encodeURIComponent(s)).then(function(r){return r.json();});}return oe.get("/Lottery/GetBetLimit",{gameCode:s})}`,
	},
	{
		find: 'function Fo(s){return oe.get("/Lottery/GetWinLossResult",s)}',
		replace:
			`function Fo(s){if(s&&typeof s.issueNumber==='string'){return fetch(${apiBase}+'/GetMotoRaceWinLossResult.php?issueNumber='+encodeURIComponent(s.issueNumber)).then(function(r){return r.json();}).catch(function(){return{result:true,data:{status:null,winAmount:0}};});}return oe.get("/Lottery/GetWinLossResult",s)}`,
	},
	{
		find: 'function Go(){return oe.get("/Lottery/GetGameList")}',
		replace:
			`function Go(){return fetch(${apiBase}+'/GetMotoRaceGameList.php').then(function(r){return r.json();}).catch(function(){return{result:true,data:[]};})}`,
	},
	{
		find: 'function xo(s){return oe.get("/Lottery/GetGameInfo",s)}',
		replace:
			`function xo(s){if(s&&${isMoto}(s.gameCode)){return fetch(${apiBase}+'/GetMotoRaceGameInfo.php?'+${toQS}(s)).then(function(r){return r.json();}).catch(function(){return{result:false,data:{}};});}return oe.get("/Lottery/GetGameInfo",s)}`,
	},
	{
		find: 'function Xo(){return oe.get("/Lottery/GetBalance")}',
		replace:
			`function Xo(){return fetch(${apiBase}+'/GetMotoRaceBalance.php',{headers:${authHdr}()}).then(function(r){return r.json();}).catch(function(){return{result:true,data:{balance:0},serviceTime:Date.now()};})}`,
	},
	{
		// MotoRace page uses Ct (not Gt) for history — must hit local PHP
		find: 'function Ct({lotteryCode:s,gameCode:i}){return Ft.get(`/${s}/${i}/GetHistoryIssuePage.json`)}',
		replace:
			'function Ct({lotteryCode:s,gameCode:i}){if(' + isMoto + '(i)){return fetch(' + apiBase + '+\'/GetMotoRaceHistoryIssuePage.php?\'+' + toQS + '({gameCode:i,lotteryCode:s})).then(function(r){return r.json();});}return Ft.get(`/${s}/${i}/GetHistoryIssuePage.json`)}',
	},
	{
		// Fall back to bet-limit rates when gameInfo has none (odds display)
		find: 'L=B(()=>{var R;return((R=d.value)==null?void 0:R.rates)||[]})',
		replace:
			'L=B(()=>{var R,BL;return((R=d.value)==null?void 0:R.rates)||((BL=f.betLimit)==null?void 0:BL.rates)||[]})',
	},
	{
		find: 'g=B(()=>{var R;return((R=d.value)==null?void 0:R.betScopes)||[]})',
		replace:
			'g=B(()=>{var R,BL;return((R=d.value)==null?void 0:R.betScopes)||((BL=f.betLimit)==null?void 0:BL.betScopes)||[]})',
	},
	{
		find: 'T=B(()=>{var R;return((R=d.value)==null?void 0:R.betMultiples)||[]})',
		replace:
			'T=B(()=>{var R,BL;return((R=d.value)==null?void 0:R.betMultiples)||((BL=f.betLimit)==null?void 0:BL.betMultiples)||[]})',
	},
	{
		// MotoRace bet handlers require issueData.gameCode — inject from active gameCode
		find: 'f.issueData=ee,f.interval=we,await ae(Ue,we)',
		replace:
			'f.issueData=Object.assign({},ee,{gameCode:l.value,lotteryCode:r.value}),f.interval=we,await ae(Ue,we)',
	},
];

let total = 0;
for (const file of files) {
	const fp = path.join(root, file);
	let s = fs.readFileSync(fp, 'utf8');
	let changed = 0;
	for (const { find, replace } of patches) {
		if (s.includes(find)) {
			s = s.replace(find, replace);
			changed++;
		} else if (s.includes(replace.slice(0, 60))) {
			console.log(`[${file}] already patched: ${find.slice(0, 40)}…`);
		} else {
			console.warn(`[${file}] NOT FOUND: ${find.slice(0, 60)}…`);
		}
	}
	if (changed) {
		fs.writeFileSync(fp, s);
		console.log(`[${file}] patched ${changed} route(s)`);
		total += changed;
	}
}
console.log(`Done — ${total} total patch(es) applied.`);