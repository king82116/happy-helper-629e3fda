const fs = require("fs");
const path = require("path");

const root = path.join(__dirname, "..", "assets", "js");

function patch(file, replacements) {
  const fp = path.join(root, file);
  let s = fs.readFileSync(fp, "utf8");
  let n = 0;
  for (const [oldStr, newStr] of replacements) {
    if (!s.includes(oldStr)) {
      console.error(`[${file}] MISSING:`, oldStr.slice(0, 80) + "...");
      process.exitCode = 1;
      continue;
    }
    s = s.replace(oldStr, newStr);
    n++;
  }
  fs.writeFileSync(fp, s);
  console.log(`[${file}] applied ${n}/${replacements.length} patches`);
}

// WinGo
patch("index-78f9aa25.js", [
  [
    "const G=Te().query.typeId,k=()=>",
    "const G=Number(Te().query.typeId)||1,k=()=>",
  ],
  [
    "K=async(e=null)=>{await S.getWinGoData(),L=S.getWingo;const n=L.findIndex(d=>d.typeID==G);R.value==null&&!G?H(0):H(e??n)},H=e=>{S.getWinGoData(),i.value=e,R.value=L[e].typeID,X(R.value),ke(()=>{C.value.getData(R.value)})},X=async e=>{N.value==\"MyGameRecord\"&&de(e);const[n,d]=await Z(Pe({typeId:e}));a.value.gameNo=d.issueNumber,a.value.currentTime=d.serviceTime.replace(/-/g,\"/\"),a.value.beginTime=d.startTime.replace(/-/g,\"/\"),ve()}",
    "K=async(e=null)=>{await S.getWinGoData(),L=S.getWingo||[];let n=L.findIndex(d=>d.typeID==G);n<0&&(n=L.findIndex(d=>d.typeID==1),n<0&&(n=0)),R.value==null&&!G?H(0):H(e??n)},H=e=>{(!L||!L[e]||e<0)&&(e=0),S.getWinGoData(),i.value=e,R.value=L[e].typeID,X(R.value),ke(()=>{C.value&&C.value.getData&&C.value.getData(R.value)})},X=async e=>{N.value==\"MyGameRecord\"&&de(e);const[n,d]=await Z(Pe({typeId:e}));if(!d)return;a.value.gameNo=d.issueNumber||\"\",a.value.currentTime=(d.serviceTime||\"\").replace(/-/g,\"/\"),a.value.beginTime=(d.startTime||\"\").replace(/-/g,\"/\"),ve()}",
  ],
  [
    "ve=()=>{const e=new Date(a.value.currentTime).getTime(),n=new Date(a.value.beginTime).getTime();let d=(e-n)/1e3,g=L[i.value];if(d>g.intervalM*60",
    "ve=()=>{if(!L[i.value])return;const e=new Date(a.value.currentTime).getTime(),n=new Date(a.value.beginTime).getTime();let d=(e-n)/1e3,g=L[i.value];if(d>g.intervalM*60",
  ],
]);

// K3
patch("index-b9f36021.js", [
  [
    "findIndex(F=>F.typeID==z);M.value==null&&!z?k(0):k(w??A)},j=async",
    "findIndex(F=>F.typeID==z);A<0&&(A=c.findIndex(F=>F.typeID==9),A<0&&(A=0)),M.value==null&&!z?k(0):k(w??A)},j=async",
  ],
  [
    "k=w=>{d.getK3Data(),v.value=w,M.value=c[w].type",
    "k=w=>{(!c||!c[w]||w<0)&&(w=0),d.getK3Data(),v.value=w,M.value=c[w].type",
  ],
]);

// 5D - need exact strings from file
const s5d = fs.readFileSync(path.join(root, "index-49c0da85.js"), "utf8");
const idx5d = s5d.indexOf("findIndex(W=>W.typeID==p)");
console.log("5D context:", s5d.slice(idx5d - 30, idx5d + 250));

// WinTrx
const strx = fs.readFileSync(path.join(root, "index-e4bfbb38.js"), "utf8");
const idxTrx = strx.indexOf("findIndex(m=>m.typeID==M)");
console.log("Trx context:", strx.slice(idxTrx - 30, idxTrx + 350));
