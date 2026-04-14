import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const s = fs.readFileSync(path.join(__dirname, "script.js"), "utf8");
const stack = [];
for (let i=0;i<s.length;i++){
  const c = s[i];
  if (c === '\\') { i++; continue; }
  if (c === '`') { if (stack.length && stack[stack.length-1].ch === '`') stack.pop(); else stack.push({ch:'`',i}); continue; }
  if (c === '"' || c === "'") { const q=c; i++; while(i<s.length){ if (s[i]==='\\') { i+=2; continue; } if (s[i]===q) break; i++; } continue; }
  if (c === '{' || c === '(' || c === '[') stack.push({ch:c,i});
  if (c === '}' || c === ')' || c === ']'){
    const o = stack.pop();
    if (!o){ console.log('Unmatched closing', c, 'at', i); process.exit(0); }
    const match = o.ch==='{'?'}':(o.ch==='(')?')':']';
    if (match !== c){ console.log('Mismatched', o.ch, 'with', c, 'at', i); process.exit(0); }
  }
}
if (stack.length) console.log('Unclosed at end (last 5):', stack.slice(-5));
else console.log('All balanced');
