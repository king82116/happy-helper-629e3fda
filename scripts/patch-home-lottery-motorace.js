/**
 * Inject a MotoRace card into the Home → Lottery section.
 * - Adds a synthetic 5th item (id=99) appended to the lottery list
 * - Increases slice(0,4) → slice(0,5)
 * - Click on the card calls openThirdGame with gameCode 'MotoRace_1M'
 * Idempotent via MR_HOME_V1 marker.
 */
const fs = require('fs');
const path = require('path');
const fp = path.join(__dirname, '..', 'assets/js/page-home-Lottery-b7784fd9.js');
let s = fs.readFileSync(fp, 'utf8');
const MARK = '/*MR_HOME_V1*/';
if (s.includes(MARK)) { console.log('already patched'); process.exit(0); }

// 1. Replace slice(0,4) → custom list with appended MotoRace item
const motoItem = `{id:99,categoryCode:'MotoRace',categoryImg:'/assets/png/car-b1876e4f.png',gameCode:'MotoRace_1M',vendorCode:'ARLottery'}`;
const sliceRe = /m\.value\.slice\(0,4\)/;
if (!sliceRe.test(s)) { console.error('slice not found'); process.exit(1); }
s = s.replace(sliceRe, `${MARK}[...m.value.slice(0,4),${motoItem}]`);

// 2. Patch S handler: prepend MotoRace id branch
const sHandlerRe = /S=o=>\{const i=m\.value\.find\(d=>o==d\.id\);if\(f\(i\)\)return g\(\{\.\.\.i,vendorCode:"ARLottery"\}\);/;
if (!sHandlerRe.test(s)) { console.error('S handler not found'); process.exit(1); }
s = s.replace(sHandlerRe, `S=o=>{if(o==99){return g({categoryCode:'MotoRace',categoryImg:'/assets/png/car-b1876e4f.png',gameCode:'MotoRace_1M',vendorCode:'ARLottery'});}const i=m.value.find(d=>o==d.id);if(f(i))return g({...i,vendorCode:"ARLottery"});`);

// 3. Add c[99] entry for fallback (in case openThirdGame fails)
s = s.replace(/8:\{title:e\("lotteryHintStr1"\),describe:e\("lotteryHintStr2"\),RouterName:"AllLotteryGames-4D"\}\}/,
  `8:{title:e("lotteryHintStr1"),describe:e("lotteryHintStr2"),RouterName:"AllLotteryGames-4D"},99:{title:"Moto Racing",describe:"1 min ranking 1-10",RouterName:"AllLotteryGames-MotoRace"}}`);

fs.writeFileSync(fp, s);
console.log('patched OK');
