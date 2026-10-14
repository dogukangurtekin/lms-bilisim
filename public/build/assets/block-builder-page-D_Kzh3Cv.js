function Bl(n){const e=Object.create(null);for(const t of n.split(","))e[t]=1;return t=>t in e}const dt={},gs=[],Ln=()=>{},_h=()=>!1,Ea=n=>n.charCodeAt(0)===111&&n.charCodeAt(1)===110&&(n.charCodeAt(2)>122||n.charCodeAt(2)<97),Ta=n=>n.startsWith("onUpdate:"),Ot=Object.assign,zl=(n,e)=>{const t=n.indexOf(e);t>-1&&n.splice(t,1)},md=Object.prototype.hasOwnProperty,st=(n,e)=>md.call(n,e),ze=Array.isArray,_s=n=>mr(n)==="[object Map]",xh=n=>mr(n)==="[object Set]",Cc=n=>mr(n)==="[object Date]",je=n=>typeof n=="function",St=n=>typeof n=="string",Nn=n=>typeof n=="symbol",ot=n=>n!==null&&typeof n=="object",vh=n=>(ot(n)||je(n))&&je(n.then)&&je(n.catch),Mh=Object.prototype.toString,mr=n=>Mh.call(n),gd=n=>mr(n).slice(8,-1),Sh=n=>mr(n)==="[object Object]",Hl=n=>St(n)&&n!=="NaN"&&n[0]!=="-"&&""+parseInt(n,10)===n,Zs=Bl(",key,ref,ref_for,ref_key,onVnodeBeforeMount,onVnodeMounted,onVnodeBeforeUpdate,onVnodeUpdated,onVnodeBeforeUnmount,onVnodeUnmounted"),Aa=n=>{const e=Object.create(null);return(t=>e[t]||(e[t]=n(t)))},_d=/-\w/g,gn=Aa(n=>n.replace(_d,e=>e.slice(1).toUpperCase())),xd=/\B([A-Z])/g,Xi=Aa(n=>n.replace(xd,"-$1").toLowerCase()),yh=Aa(n=>n.charAt(0).toUpperCase()+n.slice(1)),Ha=Aa(n=>n?`on${yh(n)}`:""),Rn=(n,e)=>!Object.is(n,e),Jr=(n,...e)=>{for(let t=0;t<n.length;t++)n[t](...e)},bh=(n,e,t,i=!1)=>{Object.defineProperty(n,e,{configurable:!0,enumerable:!1,writable:i,value:t})},Vl=n=>{const e=parseFloat(n);return isNaN(e)?n:e};let Rc;const wa=()=>Rc||(Rc=typeof globalThis<"u"?globalThis:typeof self<"u"?self:typeof window<"u"?window:typeof global<"u"?global:{});function sr(n){if(ze(n)){const e={};for(let t=0;t<n.length;t++){const i=n[t],s=St(i)?yd(i):sr(i);if(s)for(const r in s)e[r]=s[r]}return e}else if(St(n)||ot(n))return n}const vd=/;(?![^(]*\))/g,Md=/:([^]+)/,Sd=/\/\*[^]*?\*\//g;function yd(n){const e={};return n.replace(Sd,"").split(vd).forEach(t=>{if(t){const i=t.split(Md);i.length>1&&(e[i[0].trim()]=i[1].trim())}}),e}function Mi(n){let e="";if(St(n))e=n;else if(ze(n))for(let t=0;t<n.length;t++){const i=Mi(n[t]);i&&(e+=i+" ")}else if(ot(n))for(const t in n)n[t]&&(e+=t+" ");return e.trim()}const bd="itemscope,allowfullscreen,formnovalidate,ismap,nomodule,novalidate,readonly",Ed=Bl(bd);function Eh(n){return!!n||n===""}function Td(n,e){if(n.length!==e.length)return!1;let t=!0;for(let i=0;t&&i<n.length;i++)t=kl(n[i],e[i]);return t}function kl(n,e){if(n===e)return!0;let t=Cc(n),i=Cc(e);if(t||i)return t&&i?n.getTime()===e.getTime():!1;if(t=Nn(n),i=Nn(e),t||i)return n===e;if(t=ze(n),i=ze(e),t||i)return t&&i?Td(n,e):!1;if(t=ot(n),i=ot(e),t||i){if(!t||!i)return!1;const s=Object.keys(n).length,r=Object.keys(e).length;if(s!==r)return!1;for(const a in n){const o=n.hasOwnProperty(a),l=e.hasOwnProperty(a);if(o&&!l||!o&&l||!kl(n[a],e[a]))return!1}}return String(n)===String(e)}const Th=n=>!!(n&&n.__v_isRef===!0),jn=n=>St(n)?n:n==null?"":ze(n)||ot(n)&&(n.toString===Mh||!je(n.toString))?Th(n)?jn(n.value):JSON.stringify(n,Ah,2):String(n),Ah=(n,e)=>Th(e)?Ah(n,e.value):_s(e)?{[`Map(${e.size})`]:[...e.entries()].reduce((t,[i,s],r)=>(t[Va(i,r)+" =>"]=s,t),{})}:xh(e)?{[`Set(${e.size})`]:[...e.values()].map(t=>Va(t))}:Nn(e)?Va(e):ot(e)&&!ze(e)&&!Sh(e)?String(e):e,Va=(n,e="")=>{var t;return Nn(n)?`Symbol(${(t=n.description)!=null?t:e})`:n};let Xt;class Ad{constructor(e=!1){this.detached=e,this._active=!0,this._on=0,this.effects=[],this.cleanups=[],this._isPaused=!1,this.__v_skip=!0,this.parent=Xt,!e&&Xt&&(this.index=(Xt.scopes||(Xt.scopes=[])).push(this)-1)}get active(){return this._active}pause(){if(this._active){this._isPaused=!0;let e,t;if(this.scopes)for(e=0,t=this.scopes.length;e<t;e++)this.scopes[e].pause();for(e=0,t=this.effects.length;e<t;e++)this.effects[e].pause()}}resume(){if(this._active&&this._isPaused){this._isPaused=!1;let e,t;if(this.scopes)for(e=0,t=this.scopes.length;e<t;e++)this.scopes[e].resume();for(e=0,t=this.effects.length;e<t;e++)this.effects[e].resume()}}run(e){if(this._active){const t=Xt;try{return Xt=this,e()}finally{Xt=t}}}on(){++this._on===1&&(this.prevScope=Xt,Xt=this)}off(){this._on>0&&--this._on===0&&(Xt=this.prevScope,this.prevScope=void 0)}stop(e){if(this._active){this._active=!1;let t,i;for(t=0,i=this.effects.length;t<i;t++)this.effects[t].stop();for(this.effects.length=0,t=0,i=this.cleanups.length;t<i;t++)this.cleanups[t]();if(this.cleanups.length=0,this.scopes){for(t=0,i=this.scopes.length;t<i;t++)this.scopes[t].stop(!0);this.scopes.length=0}if(!this.detached&&this.parent&&!e){const s=this.parent.scopes.pop();s&&s!==this&&(this.parent.scopes[this.index]=s,s.index=this.index)}this.parent=void 0}}}function wd(){return Xt}let mt;const ka=new WeakSet;class wh{constructor(e){this.fn=e,this.deps=void 0,this.depsTail=void 0,this.flags=5,this.next=void 0,this.cleanup=void 0,this.scheduler=void 0,Xt&&Xt.active&&Xt.effects.push(this)}pause(){this.flags|=64}resume(){this.flags&64&&(this.flags&=-65,ka.has(this)&&(ka.delete(this),this.trigger()))}notify(){this.flags&2&&!(this.flags&32)||this.flags&8||Rh(this)}run(){if(!(this.flags&1))return this.fn();this.flags|=2,Pc(this),Ph(this);const e=mt,t=_n;mt=this,_n=!0;try{return this.fn()}finally{Dh(this),mt=e,_n=t,this.flags&=-3}}stop(){if(this.flags&1){for(let e=this.deps;e;e=e.nextDep)Xl(e);this.deps=this.depsTail=void 0,Pc(this),this.onStop&&this.onStop(),this.flags&=-2}}trigger(){this.flags&64?ka.add(this):this.scheduler?this.scheduler():this.runIfDirty()}runIfDirty(){No(this)&&this.run()}get dirty(){return No(this)}}let Ch=0,Js,Qs;function Rh(n,e=!1){if(n.flags|=8,e){n.next=Qs,Qs=n;return}n.next=Js,Js=n}function Gl(){Ch++}function Wl(){if(--Ch>0)return;if(Qs){let e=Qs;for(Qs=void 0;e;){const t=e.next;e.next=void 0,e.flags&=-9,e=t}}let n;for(;Js;){let e=Js;for(Js=void 0;e;){const t=e.next;if(e.next=void 0,e.flags&=-9,e.flags&1)try{e.trigger()}catch(i){n||(n=i)}e=t}}if(n)throw n}function Ph(n){for(let e=n.deps;e;e=e.nextDep)e.version=-1,e.prevActiveLink=e.dep.activeLink,e.dep.activeLink=e}function Dh(n){let e,t=n.depsTail,i=t;for(;i;){const s=i.prevDep;i.version===-1?(i===t&&(t=s),Xl(i),Cd(i)):e=i,i.dep.activeLink=i.prevActiveLink,i.prevActiveLink=void 0,i=s}n.deps=e,n.depsTail=t}function No(n){for(let e=n.deps;e;e=e.nextDep)if(e.dep.version!==e.version||e.dep.computed&&(Lh(e.dep.computed)||e.dep.version!==e.version))return!0;return!!n._dirty}function Lh(n){if(n.flags&4&&!(n.flags&16)||(n.flags&=-17,n.globalVersion===rr)||(n.globalVersion=rr,!n.isSSR&&n.flags&128&&(!n.deps&&!n._dirty||!No(n))))return;n.flags|=2;const e=n.dep,t=mt,i=_n;mt=n,_n=!0;try{Ph(n);const s=n.fn(n._value);(e.version===0||Rn(s,n._value))&&(n.flags|=128,n._value=s,e.version++)}catch(s){throw e.version++,s}finally{mt=t,_n=i,Dh(n),n.flags&=-3}}function Xl(n,e=!1){const{dep:t,prevSub:i,nextSub:s}=n;if(i&&(i.nextSub=s,n.prevSub=void 0),s&&(s.prevSub=i,n.nextSub=void 0),t.subs===n&&(t.subs=i,!i&&t.computed)){t.computed.flags&=-5;for(let r=t.computed.deps;r;r=r.nextDep)Xl(r,!0)}!e&&!--t.sc&&t.map&&t.map.delete(t.key)}function Cd(n){const{prevDep:e,nextDep:t}=n;e&&(e.nextDep=t,n.prevDep=void 0),t&&(t.prevDep=e,n.nextDep=void 0)}let _n=!0;const Ih=[];function ni(){Ih.push(_n),_n=!1}function ii(){const n=Ih.pop();_n=n===void 0?!0:n}function Pc(n){const{cleanup:e}=n;if(n.cleanup=void 0,e){const t=mt;mt=void 0;try{e()}finally{mt=t}}}let rr=0;class Rd{constructor(e,t){this.sub=e,this.dep=t,this.version=t.version,this.nextDep=this.prevDep=this.nextSub=this.prevSub=this.prevActiveLink=void 0}}class Yl{constructor(e){this.computed=e,this.version=0,this.activeLink=void 0,this.subs=void 0,this.map=void 0,this.key=void 0,this.sc=0,this.__v_skip=!0}track(e){if(!mt||!_n||mt===this.computed)return;let t=this.activeLink;if(t===void 0||t.sub!==mt)t=this.activeLink=new Rd(mt,this),mt.deps?(t.prevDep=mt.depsTail,mt.depsTail.nextDep=t,mt.depsTail=t):mt.deps=mt.depsTail=t,Uh(t);else if(t.version===-1&&(t.version=this.version,t.nextDep)){const i=t.nextDep;i.prevDep=t.prevDep,t.prevDep&&(t.prevDep.nextDep=i),t.prevDep=mt.depsTail,t.nextDep=void 0,mt.depsTail.nextDep=t,mt.depsTail=t,mt.deps===t&&(mt.deps=i)}return t}trigger(e){this.version++,rr++,this.notify(e)}notify(e){Gl();try{for(let t=this.subs;t;t=t.prevSub)t.sub.notify()&&t.sub.dep.notify()}finally{Wl()}}}function Uh(n){if(n.dep.sc++,n.sub.flags&4){const e=n.dep.computed;if(e&&!n.dep.subs){e.flags|=20;for(let i=e.deps;i;i=i.nextDep)Uh(i)}const t=n.dep.subs;t!==n&&(n.prevSub=t,t&&(t.nextSub=n)),n.dep.subs=n}}const Fo=new WeakMap,ki=Symbol(""),Oo=Symbol(""),ar=Symbol("");function It(n,e,t){if(_n&&mt){let i=Fo.get(n);i||Fo.set(n,i=new Map);let s=i.get(t);s||(i.set(t,s=new Yl),s.map=i,s.key=t),s.track()}}function Zn(n,e,t,i,s,r){const a=Fo.get(n);if(!a){rr++;return}const o=l=>{l&&l.trigger()};if(Gl(),e==="clear")a.forEach(o);else{const l=ze(n),c=l&&Hl(t);if(l&&t==="length"){const u=Number(i);a.forEach((f,h)=>{(h==="length"||h===ar||!Nn(h)&&h>=u)&&o(f)})}else switch((t!==void 0||a.has(void 0))&&o(a.get(t)),c&&o(a.get(ar)),e){case"add":l?c&&o(a.get("length")):(o(a.get(ki)),_s(n)&&o(a.get(Oo)));break;case"delete":l||(o(a.get(ki)),_s(n)&&o(a.get(Oo)));break;case"set":_s(n)&&o(a.get(ki));break}}Wl()}function $i(n){const e=it(n);return e===n?e:(It(e,"iterate",ar),ln(n)?e:e.map(vn))}function Ca(n){return It(n=it(n),"iterate",ar),n}function An(n,e){return si(n)?bs(Gi(n)?vn(e):e):vn(e)}const Pd={__proto__:null,[Symbol.iterator](){return Ga(this,Symbol.iterator,n=>An(this,n))},concat(...n){return $i(this).concat(...n.map(e=>ze(e)?$i(e):e))},entries(){return Ga(this,"entries",n=>(n[1]=An(this,n[1]),n))},every(n,e){return kn(this,"every",n,e,void 0,arguments)},filter(n,e){return kn(this,"filter",n,e,t=>t.map(i=>An(this,i)),arguments)},find(n,e){return kn(this,"find",n,e,t=>An(this,t),arguments)},findIndex(n,e){return kn(this,"findIndex",n,e,void 0,arguments)},findLast(n,e){return kn(this,"findLast",n,e,t=>An(this,t),arguments)},findLastIndex(n,e){return kn(this,"findLastIndex",n,e,void 0,arguments)},forEach(n,e){return kn(this,"forEach",n,e,void 0,arguments)},includes(...n){return Wa(this,"includes",n)},indexOf(...n){return Wa(this,"indexOf",n)},join(n){return $i(this).join(n)},lastIndexOf(...n){return Wa(this,"lastIndexOf",n)},map(n,e){return kn(this,"map",n,e,void 0,arguments)},pop(){return Is(this,"pop")},push(...n){return Is(this,"push",n)},reduce(n,...e){return Dc(this,"reduce",n,e)},reduceRight(n,...e){return Dc(this,"reduceRight",n,e)},shift(){return Is(this,"shift")},some(n,e){return kn(this,"some",n,e,void 0,arguments)},splice(...n){return Is(this,"splice",n)},toReversed(){return $i(this).toReversed()},toSorted(n){return $i(this).toSorted(n)},toSpliced(...n){return $i(this).toSpliced(...n)},unshift(...n){return Is(this,"unshift",n)},values(){return Ga(this,"values",n=>An(this,n))}};function Ga(n,e,t){const i=Ca(n),s=i[e]();return i!==n&&!ln(n)&&(s._next=s.next,s.next=()=>{const r=s._next();return r.done||(r.value=t(r.value)),r}),s}const Dd=Array.prototype;function kn(n,e,t,i,s,r){const a=Ca(n),o=a!==n&&!ln(n),l=a[e];if(l!==Dd[e]){const f=l.apply(n,r);return o?vn(f):f}let c=t;a!==n&&(o?c=function(f,h){return t.call(this,An(n,f),h,n)}:t.length>2&&(c=function(f,h){return t.call(this,f,h,n)}));const u=l.call(a,c,i);return o&&s?s(u):u}function Dc(n,e,t,i){const s=Ca(n),r=s!==n&&!ln(n);let a=t,o=!1;s!==n&&(r?(o=i.length===0,a=function(c,u,f){return o&&(o=!1,c=An(n,c)),t.call(this,c,An(n,u),f,n)}):t.length>3&&(a=function(c,u,f){return t.call(this,c,u,f,n)}));const l=s[e](a,...i);return o?An(n,l):l}function Wa(n,e,t){const i=it(n);It(i,"iterate",ar);const s=i[e](...t);return(s===-1||s===!1)&&$l(t[0])?(t[0]=it(t[0]),i[e](...t)):s}function Is(n,e,t=[]){ni(),Gl();const i=it(n)[e].apply(n,t);return Wl(),ii(),i}const Ld=Bl("__proto__,__v_isRef,__isVue"),Nh=new Set(Object.getOwnPropertyNames(Symbol).filter(n=>n!=="arguments"&&n!=="caller").map(n=>Symbol[n]).filter(Nn));function Id(n){Nn(n)||(n=String(n));const e=it(this);return It(e,"has",n),e.hasOwnProperty(n)}class Fh{constructor(e=!1,t=!1){this._isReadonly=e,this._isShallow=t}get(e,t,i){if(t==="__v_skip")return e.__v_skip;const s=this._isReadonly,r=this._isShallow;if(t==="__v_isReactive")return!s;if(t==="__v_isReadonly")return s;if(t==="__v_isShallow")return r;if(t==="__v_raw")return i===(s?r?Gd:Hh:r?zh:Bh).get(e)||Object.getPrototypeOf(e)===Object.getPrototypeOf(i)?e:void 0;const a=ze(e);if(!s){let l;if(a&&(l=Pd[t]))return l;if(t==="hasOwnProperty")return Id}const o=Reflect.get(e,t,Nt(e)?e:i);if((Nn(t)?Nh.has(t):Ld(t))||(s||It(e,"get",t),r))return o;if(Nt(o)){const l=a&&Hl(t)?o:o.value;return s&&ot(l)?zo(l):l}return ot(o)?s?zo(o):jl(o):o}}class Oh extends Fh{constructor(e=!1){super(!1,e)}set(e,t,i,s){let r=e[t];const a=ze(e)&&Hl(t);if(!this._isShallow){const c=si(r);if(!ln(i)&&!si(i)&&(r=it(r),i=it(i)),!a&&Nt(r)&&!Nt(i))return c||(r.value=i),!0}const o=a?Number(t)<e.length:st(e,t),l=Reflect.set(e,t,i,Nt(e)?e:s);return e===it(s)&&(o?Rn(i,r)&&Zn(e,"set",t,i):Zn(e,"add",t,i)),l}deleteProperty(e,t){const i=st(e,t);e[t];const s=Reflect.deleteProperty(e,t);return s&&i&&Zn(e,"delete",t,void 0),s}has(e,t){const i=Reflect.has(e,t);return(!Nn(t)||!Nh.has(t))&&It(e,"has",t),i}ownKeys(e){return It(e,"iterate",ze(e)?"length":ki),Reflect.ownKeys(e)}}class Ud extends Fh{constructor(e=!1){super(!0,e)}set(e,t){return!0}deleteProperty(e,t){return!0}}const Nd=new Oh,Fd=new Ud,Od=new Oh(!0);const Bo=n=>n,yr=n=>Reflect.getPrototypeOf(n);function Bd(n,e,t){return function(...i){const s=this.__v_raw,r=it(s),a=_s(r),o=n==="entries"||n===Symbol.iterator&&a,l=n==="keys"&&a,c=s[n](...i),u=t?Bo:e?bs:vn;return!e&&It(r,"iterate",l?Oo:ki),Ot(Object.create(c),{next(){const{value:f,done:h}=c.next();return h?{value:f,done:h}:{value:o?[u(f[0]),u(f[1])]:u(f),done:h}}})}}function br(n){return function(...e){return n==="delete"?!1:n==="clear"?void 0:this}}function zd(n,e){const t={get(s){const r=this.__v_raw,a=it(r),o=it(s);n||(Rn(s,o)&&It(a,"get",s),It(a,"get",o));const{has:l}=yr(a),c=e?Bo:n?bs:vn;if(l.call(a,s))return c(r.get(s));if(l.call(a,o))return c(r.get(o));r!==a&&r.get(s)},get size(){const s=this.__v_raw;return!n&&It(it(s),"iterate",ki),s.size},has(s){const r=this.__v_raw,a=it(r),o=it(s);return n||(Rn(s,o)&&It(a,"has",s),It(a,"has",o)),s===o?r.has(s):r.has(s)||r.has(o)},forEach(s,r){const a=this,o=a.__v_raw,l=it(o),c=e?Bo:n?bs:vn;return!n&&It(l,"iterate",ki),o.forEach((u,f)=>s.call(r,c(u),c(f),a))}};return Ot(t,n?{add:br("add"),set:br("set"),delete:br("delete"),clear:br("clear")}:{add(s){const r=it(this),a=yr(r),o=it(s),l=!e&&!ln(s)&&!si(s)?o:s;return a.has.call(r,l)||Rn(s,l)&&a.has.call(r,s)||Rn(o,l)&&a.has.call(r,o)||(r.add(l),Zn(r,"add",l,l)),this},set(s,r){!e&&!ln(r)&&!si(r)&&(r=it(r));const a=it(this),{has:o,get:l}=yr(a);let c=o.call(a,s);c||(s=it(s),c=o.call(a,s));const u=l.call(a,s);return a.set(s,r),c?Rn(r,u)&&Zn(a,"set",s,r):Zn(a,"add",s,r),this},delete(s){const r=it(this),{has:a,get:o}=yr(r);let l=a.call(r,s);l||(s=it(s),l=a.call(r,s)),o&&o.call(r,s);const c=r.delete(s);return l&&Zn(r,"delete",s,void 0),c},clear(){const s=it(this),r=s.size!==0,a=s.clear();return r&&Zn(s,"clear",void 0,void 0),a}}),["keys","values","entries",Symbol.iterator].forEach(s=>{t[s]=Bd(s,n,e)}),t}function ql(n,e){const t=zd(n,e);return(i,s,r)=>s==="__v_isReactive"?!n:s==="__v_isReadonly"?n:s==="__v_raw"?i:Reflect.get(st(t,s)&&s in i?t:i,s,r)}const Hd={get:ql(!1,!1)},Vd={get:ql(!1,!0)},kd={get:ql(!0,!1)};const Bh=new WeakMap,zh=new WeakMap,Hh=new WeakMap,Gd=new WeakMap;function Wd(n){switch(n){case"Object":case"Array":return 1;case"Map":case"Set":case"WeakMap":case"WeakSet":return 2;default:return 0}}function Xd(n){return n.__v_skip||!Object.isExtensible(n)?0:Wd(gd(n))}function jl(n){return si(n)?n:Kl(n,!1,Nd,Hd,Bh)}function Yd(n){return Kl(n,!1,Od,Vd,zh)}function zo(n){return Kl(n,!0,Fd,kd,Hh)}function Kl(n,e,t,i,s){if(!ot(n)||n.__v_raw&&!(e&&n.__v_isReactive))return n;const r=Xd(n);if(r===0)return n;const a=s.get(n);if(a)return a;const o=new Proxy(n,r===2?i:t);return s.set(n,o),o}function Gi(n){return si(n)?Gi(n.__v_raw):!!(n&&n.__v_isReactive)}function si(n){return!!(n&&n.__v_isReadonly)}function ln(n){return!!(n&&n.__v_isShallow)}function $l(n){return n?!!n.__v_raw:!1}function it(n){const e=n&&n.__v_raw;return e?it(e):n}function qd(n){return!st(n,"__v_skip")&&Object.isExtensible(n)&&bh(n,"__v_skip",!0),n}const vn=n=>ot(n)?jl(n):n,bs=n=>ot(n)?zo(n):n;function Nt(n){return n?n.__v_isRef===!0:!1}function nn(n){return jd(n,!1)}function jd(n,e){return Nt(n)?n:new Kd(n,e)}class Kd{constructor(e,t){this.dep=new Yl,this.__v_isRef=!0,this.__v_isShallow=!1,this._rawValue=t?e:it(e),this._value=t?e:vn(e),this.__v_isShallow=t}get value(){return this.dep.track(),this._value}set value(e){const t=this._rawValue,i=this.__v_isShallow||ln(e)||si(e);e=i?e:it(e),Rn(e,t)&&(this._rawValue=e,this._value=i?e:vn(e),this.dep.trigger())}}function $d(n){return Nt(n)?n.value:n}const Zd={get:(n,e,t)=>e==="__v_raw"?n:$d(Reflect.get(n,e,t)),set:(n,e,t,i)=>{const s=n[e];return Nt(s)&&!Nt(t)?(s.value=t,!0):Reflect.set(n,e,t,i)}};function Vh(n){return Gi(n)?n:new Proxy(n,Zd)}class Jd{constructor(e,t,i){this.fn=e,this.setter=t,this._value=void 0,this.dep=new Yl(this),this.__v_isRef=!0,this.deps=void 0,this.depsTail=void 0,this.flags=16,this.globalVersion=rr-1,this.next=void 0,this.effect=this,this.__v_isReadonly=!t,this.isSSR=i}notify(){if(this.flags|=16,!(this.flags&8)&&mt!==this)return Rh(this,!0),!0}get value(){const e=this.dep.track();return Lh(this),e&&(e.version=this.dep.version),this._value}set value(e){this.setter&&this.setter(e)}}function Qd(n,e,t=!1){let i,s;return je(n)?i=n:(i=n.get,s=n.set),new Jd(i,s,t)}const Er={},ua=new WeakMap;let Fi;function ep(n,e=!1,t=Fi){if(t){let i=ua.get(t);i||ua.set(t,i=[]),i.push(n)}}function tp(n,e,t=dt){const{immediate:i,deep:s,once:r,scheduler:a,augmentJob:o,call:l}=t,c=b=>s?b:ln(b)||s===!1||s===0?Jn(b,1):Jn(b);let u,f,h,p,_=!1,M=!1;if(Nt(n)?(f=()=>n.value,_=ln(n)):Gi(n)?(f=()=>c(n),_=!0):ze(n)?(M=!0,_=n.some(b=>Gi(b)||ln(b)),f=()=>n.map(b=>{if(Nt(b))return b.value;if(Gi(b))return c(b);if(je(b))return l?l(b,2):b()})):je(n)?e?f=l?()=>l(n,2):n:f=()=>{if(h){ni();try{h()}finally{ii()}}const b=Fi;Fi=u;try{return l?l(n,3,[p]):n(p)}finally{Fi=b}}:f=Ln,e&&s){const b=f,P=s===!0?1/0:s;f=()=>Jn(b(),P)}const g=wd(),d=()=>{u.stop(),g&&g.active&&zl(g.effects,u)};if(r&&e){const b=e;e=(...P)=>{b(...P),d()}}let y=M?new Array(n.length).fill(Er):Er;const A=b=>{if(!(!(u.flags&1)||!u.dirty&&!b))if(e){const P=u.run();if(s||_||(M?P.some((L,I)=>Rn(L,y[I])):Rn(P,y))){h&&h();const L=Fi;Fi=u;try{const I=[P,y===Er?void 0:M&&y[0]===Er?[]:y,p];y=P,l?l(e,3,I):e(...I)}finally{Fi=L}}}else u.run()};return o&&o(A),u=new wh(f),u.scheduler=a?()=>a(A,!1):A,p=b=>ep(b,!1,u),h=u.onStop=()=>{const b=ua.get(u);if(b){if(l)l(b,4);else for(const P of b)P();ua.delete(u)}},e?i?A(!0):y=u.run():a?a(A.bind(null,!0),!0):u.run(),d.pause=u.pause.bind(u),d.resume=u.resume.bind(u),d.stop=d,d}function Jn(n,e=1/0,t){if(e<=0||!ot(n)||n.__v_skip||(t=t||new Map,(t.get(n)||0)>=e))return n;if(t.set(n,e),e--,Nt(n))Jn(n.value,e,t);else if(ze(n))for(let i=0;i<n.length;i++)Jn(n[i],e,t);else if(xh(n)||_s(n))n.forEach(i=>{Jn(i,e,t)});else if(Sh(n)){for(const i in n)Jn(n[i],e,t);for(const i of Object.getOwnPropertySymbols(n))Object.prototype.propertyIsEnumerable.call(n,i)&&Jn(n[i],e,t)}return n}function gr(n,e,t,i){try{return i?n(...i):n()}catch(s){Ra(s,e,t)}}function Fn(n,e,t,i){if(je(n)){const s=gr(n,e,t,i);return s&&vh(s)&&s.catch(r=>{Ra(r,e,t)}),s}if(ze(n)){const s=[];for(let r=0;r<n.length;r++)s.push(Fn(n[r],e,t,i));return s}}function Ra(n,e,t,i=!0){const s=e?e.vnode:null,{errorHandler:r,throwUnhandledErrorInProduction:a}=e&&e.appContext.config||dt;if(e){let o=e.parent;const l=e.proxy,c=`https://vuejs.org/error-reference/#runtime-${t}`;for(;o;){const u=o.ec;if(u){for(let f=0;f<u.length;f++)if(u[f](n,l,c)===!1)return}o=o.parent}if(r){ni(),gr(r,null,10,[n,l,c]),ii();return}}np(n,t,s,i,a)}function np(n,e,t,i=!0,s=!1){if(s)throw n;console.error(n)}const Ht=[];let Tn=-1;const xs=[];let xi=null,ds=0;const kh=Promise.resolve();let ha=null;function Gh(n){const e=ha||kh;return n?e.then(this?n.bind(this):n):e}function ip(n){let e=Tn+1,t=Ht.length;for(;e<t;){const i=e+t>>>1,s=Ht[i],r=or(s);r<n||r===n&&s.flags&2?e=i+1:t=i}return e}function Zl(n){if(!(n.flags&1)){const e=or(n),t=Ht[Ht.length-1];!t||!(n.flags&2)&&e>=or(t)?Ht.push(n):Ht.splice(ip(e),0,n),n.flags|=1,Wh()}}function Wh(){ha||(ha=kh.then(Yh))}function sp(n){ze(n)?xs.push(...n):xi&&n.id===-1?xi.splice(ds+1,0,n):n.flags&1||(xs.push(n),n.flags|=1),Wh()}function Lc(n,e,t=Tn+1){for(;t<Ht.length;t++){const i=Ht[t];if(i&&i.flags&2){if(n&&i.id!==n.uid)continue;Ht.splice(t,1),t--,i.flags&4&&(i.flags&=-2),i(),i.flags&4||(i.flags&=-2)}}}function Xh(n){if(xs.length){const e=[...new Set(xs)].sort((t,i)=>or(t)-or(i));if(xs.length=0,xi){xi.push(...e);return}for(xi=e,ds=0;ds<xi.length;ds++){const t=xi[ds];t.flags&4&&(t.flags&=-2),t.flags&8||t(),t.flags&=-2}xi=null,ds=0}}const or=n=>n.id==null?n.flags&2?-1:1/0:n.id;function Yh(n){try{for(Tn=0;Tn<Ht.length;Tn++){const e=Ht[Tn];e&&!(e.flags&8)&&(e.flags&4&&(e.flags&=-2),gr(e,e.i,e.i?15:14),e.flags&4||(e.flags&=-2))}}finally{for(;Tn<Ht.length;Tn++){const e=Ht[Tn];e&&(e.flags&=-2)}Tn=-1,Ht.length=0,Xh(),ha=null,(Ht.length||xs.length)&&Yh()}}let on=null,qh=null;function fa(n){const e=on;return on=n,qh=n&&n.type.__scopeId||null,e}function rp(n,e=on,t){if(!e||n._n)return n;const i=(...s)=>{i._d&&ma(-1);const r=fa(e);let a;try{a=n(...s)}finally{fa(r),i._d&&ma(1)}return a};return i._n=!0,i._c=!0,i._d=!0,i}function Xa(n,e){if(on===null)return n;const t=Ia(on),i=n.dirs||(n.dirs=[]);for(let s=0;s<e.length;s++){let[r,a,o,l=dt]=e[s];r&&(je(r)&&(r={mounted:r,updated:r}),r.deep&&Jn(a),i.push({dir:r,instance:t,value:a,oldValue:void 0,arg:o,modifiers:l}))}return n}function Ci(n,e,t,i){const s=n.dirs,r=e&&e.dirs;for(let a=0;a<s.length;a++){const o=s[a];r&&(o.oldValue=r[a].value);let l=o.dir[i];l&&(ni(),Fn(l,t,8,[n.el,o,n,e]),ii())}}function ap(n,e){if(Vt){let t=Vt.provides;const i=Vt.parent&&Vt.parent.provides;i===t&&(t=Vt.provides=Object.create(i)),t[n]=e}}function Qr(n,e,t=!1){const i=om();if(i||vs){let s=vs?vs._context.provides:i?i.parent==null||i.ce?i.vnode.appContext&&i.vnode.appContext.provides:i.parent.provides:void 0;if(s&&n in s)return s[n];if(arguments.length>1)return t&&je(e)?e.call(i&&i.proxy):e}}const op=Symbol.for("v-scx"),lp=()=>Qr(op);function ea(n,e,t){return jh(n,e,t)}function jh(n,e,t=dt){const{immediate:i,deep:s,flush:r,once:a}=t,o=Ot({},t),l=e&&i||!e&&r!=="post";let c;if(cr){if(r==="sync"){const p=lp();c=p.__watcherHandles||(p.__watcherHandles=[])}else if(!l){const p=()=>{};return p.stop=Ln,p.resume=Ln,p.pause=Ln,p}}const u=Vt;o.call=(p,_,M)=>Fn(p,u,_,M);let f=!1;r==="post"?o.scheduler=p=>{Wt(p,u&&u.suspense)}:r!=="sync"&&(f=!0,o.scheduler=(p,_)=>{_?p():Zl(p)}),o.augmentJob=p=>{e&&(p.flags|=4),f&&(p.flags|=2,u&&(p.id=u.uid,p.i=u))};const h=tp(n,e,o);return cr&&(c?c.push(h):l&&h()),h}function cp(n,e,t){const i=this.proxy,s=St(n)?n.includes(".")?Kh(i,n):()=>i[n]:n.bind(i,i);let r;je(e)?r=e:(r=e.handler,t=e);const a=_r(this),o=jh(s,r.bind(i),t);return a(),o}function Kh(n,e){const t=e.split(".");return()=>{let i=n;for(let s=0;s<t.length&&i;s++)i=i[t[s]];return i}}const up=Symbol("_vte"),hp=n=>n.__isTeleport,fp=Symbol("_leaveCb");function Jl(n,e){n.shapeFlag&6&&n.component?(n.transition=e,Jl(n.component.subTree,e)):n.shapeFlag&128?(n.ssContent.transition=e.clone(n.ssContent),n.ssFallback.transition=e.clone(n.ssFallback)):n.transition=e}function $h(n){n.ids=[n.ids[0]+n.ids[2]+++"-",0,0]}function Ic(n,e){let t;return!!((t=Object.getOwnPropertyDescriptor(n,e))&&!t.configurable)}const da=new WeakMap;function er(n,e,t,i,s=!1){if(ze(n)){n.forEach((M,g)=>er(M,e&&(ze(e)?e[g]:e),t,i,s));return}if(tr(i)&&!s){i.shapeFlag&512&&i.type.__asyncResolved&&i.component.subTree.component&&er(n,e,t,i.component.subTree);return}const r=i.shapeFlag&4?Ia(i.component):i.el,a=s?null:r,{i:o,r:l}=n,c=e&&e.r,u=o.refs===dt?o.refs={}:o.refs,f=o.setupState,h=it(f),p=f===dt?_h:M=>Ic(u,M)?!1:st(h,M),_=(M,g)=>!(g&&Ic(u,g));if(c!=null&&c!==l){if(Uc(e),St(c))u[c]=null,p(c)&&(f[c]=null);else if(Nt(c)){const M=e;_(c,M.k)&&(c.value=null),M.k&&(u[M.k]=null)}}if(je(l))gr(l,o,12,[a,u]);else{const M=St(l),g=Nt(l);if(M||g){const d=()=>{if(n.f){const y=M?p(l)?f[l]:u[l]:_()||!n.k?l.value:u[n.k];if(s)ze(y)&&zl(y,r);else if(ze(y))y.includes(r)||y.push(r);else if(M)u[l]=[r],p(l)&&(f[l]=u[l]);else{const A=[r];_(l,n.k)&&(l.value=A),n.k&&(u[n.k]=A)}}else M?(u[l]=a,p(l)&&(f[l]=a)):g&&(_(l,n.k)&&(l.value=a),n.k&&(u[n.k]=a))};if(a){const y=()=>{d(),da.delete(n)};y.id=-1,da.set(n,y),Wt(y,t)}else Uc(n),d()}}}function Uc(n){const e=da.get(n);e&&(e.flags|=8,da.delete(n))}wa().requestIdleCallback;wa().cancelIdleCallback;const tr=n=>!!n.type.__asyncLoader,Zh=n=>n.type.__isKeepAlive;function dp(n,e){Jh(n,"a",e)}function pp(n,e){Jh(n,"da",e)}function Jh(n,e,t=Vt){const i=n.__wdc||(n.__wdc=()=>{let s=t;for(;s;){if(s.isDeactivated)return;s=s.parent}return n()});if(Pa(e,i,t),t){let s=t.parent;for(;s&&s.parent;)Zh(s.parent.vnode)&&mp(i,e,t,s),s=s.parent}}function mp(n,e,t,i){const s=Pa(e,n,i,!0);ef(()=>{zl(i[e],s)},t)}function Pa(n,e,t=Vt,i=!1){if(t){const s=t[n]||(t[n]=[]),r=e.__weh||(e.__weh=(...a)=>{ni();const o=_r(t),l=Fn(e,t,n,a);return o(),ii(),l});return i?s.unshift(r):s.push(r),r}}const li=n=>(e,t=Vt)=>{(!cr||n==="sp")&&Pa(n,(...i)=>e(...i),t)},gp=li("bm"),_p=li("m"),xp=li("bu"),vp=li("u"),Qh=li("bum"),ef=li("um"),Mp=li("sp"),Sp=li("rtg"),yp=li("rtc");function bp(n,e=Vt){Pa("ec",n,e)}const Ep=Symbol.for("v-ndc");function Ya(n,e,t,i){let s;const r=t,a=ze(n);if(a||St(n)){const o=a&&Gi(n);let l=!1,c=!1;o&&(l=!ln(n),c=si(n),n=Ca(n)),s=new Array(n.length);for(let u=0,f=n.length;u<f;u++)s[u]=e(l?c?bs(vn(n[u])):vn(n[u]):n[u],u,void 0,r)}else if(typeof n=="number"){s=new Array(n);for(let o=0;o<n;o++)s[o]=e(o+1,o,void 0,r)}else if(ot(n))if(n[Symbol.iterator])s=Array.from(n,(o,l)=>e(o,l,void 0,r));else{const o=Object.keys(n);s=new Array(o.length);for(let l=0,c=o.length;l<c;l++){const u=o[l];s[l]=e(n[u],u,l,r)}}else s=[];return s}const Ho=n=>n?Sf(n)?Ia(n):Ho(n.parent):null,nr=Ot(Object.create(null),{$:n=>n,$el:n=>n.vnode.el,$data:n=>n.data,$props:n=>n.props,$attrs:n=>n.attrs,$slots:n=>n.slots,$refs:n=>n.refs,$parent:n=>Ho(n.parent),$root:n=>Ho(n.root),$host:n=>n.ce,$emit:n=>n.emit,$options:n=>nf(n),$forceUpdate:n=>n.f||(n.f=()=>{Zl(n.update)}),$nextTick:n=>n.n||(n.n=Gh.bind(n.proxy)),$watch:n=>cp.bind(n)}),qa=(n,e)=>n!==dt&&!n.__isScriptSetup&&st(n,e),Tp={get({_:n},e){if(e==="__v_skip")return!0;const{ctx:t,setupState:i,data:s,props:r,accessCache:a,type:o,appContext:l}=n;if(e[0]!=="$"){const h=a[e];if(h!==void 0)switch(h){case 1:return i[e];case 2:return s[e];case 4:return t[e];case 3:return r[e]}else{if(qa(i,e))return a[e]=1,i[e];if(s!==dt&&st(s,e))return a[e]=2,s[e];if(st(r,e))return a[e]=3,r[e];if(t!==dt&&st(t,e))return a[e]=4,t[e];Vo&&(a[e]=0)}}const c=nr[e];let u,f;if(c)return e==="$attrs"&&It(n.attrs,"get",""),c(n);if((u=o.__cssModules)&&(u=u[e]))return u;if(t!==dt&&st(t,e))return a[e]=4,t[e];if(f=l.config.globalProperties,st(f,e))return f[e]},set({_:n},e,t){const{data:i,setupState:s,ctx:r}=n;return qa(s,e)?(s[e]=t,!0):i!==dt&&st(i,e)?(i[e]=t,!0):st(n.props,e)||e[0]==="$"&&e.slice(1)in n?!1:(r[e]=t,!0)},has({_:{data:n,setupState:e,accessCache:t,ctx:i,appContext:s,props:r,type:a}},o){let l;return!!(t[o]||n!==dt&&o[0]!=="$"&&st(n,o)||qa(e,o)||st(r,o)||st(i,o)||st(nr,o)||st(s.config.globalProperties,o)||(l=a.__cssModules)&&l[o])},defineProperty(n,e,t){return t.get!=null?n._.accessCache[e]=0:st(t,"value")&&this.set(n,e,t.value,null),Reflect.defineProperty(n,e,t)}};function Nc(n){return ze(n)?n.reduce((e,t)=>(e[t]=null,e),{}):n}let Vo=!0;function Ap(n){const e=nf(n),t=n.proxy,i=n.ctx;Vo=!1,e.beforeCreate&&Fc(e.beforeCreate,n,"bc");const{data:s,computed:r,methods:a,watch:o,provide:l,inject:c,created:u,beforeMount:f,mounted:h,beforeUpdate:p,updated:_,activated:M,deactivated:g,beforeDestroy:d,beforeUnmount:y,destroyed:A,unmounted:b,render:P,renderTracked:L,renderTriggered:I,errorCaptured:x,serverPrefetch:T,expose:Q,inheritAttrs:D,components:X,directives:Y,filters:$}=e;if(c&&wp(c,i,null),a)for(const N in a){const ie=a[N];je(ie)&&(i[N]=ie.bind(t))}if(s){const N=s.call(t,t);ot(N)&&(n.data=jl(N))}if(Vo=!0,r)for(const N in r){const ie=r[N],le=je(ie)?ie.bind(t,t):je(ie.get)?ie.get.bind(t,t):Ln,ve=!je(ie)&&je(ie.set)?ie.set.bind(t):Ln,se=bf({get:le,set:ve});Object.defineProperty(i,N,{enumerable:!0,configurable:!0,get:()=>se.value,set:oe=>se.value=oe})}if(o)for(const N in o)tf(o[N],i,t,N);if(l){const N=je(l)?l.call(t):l;Reflect.ownKeys(N).forEach(ie=>{ap(ie,N[ie])})}u&&Fc(u,n,"c");function B(N,ie){ze(ie)?ie.forEach(le=>N(le.bind(t))):ie&&N(ie.bind(t))}if(B(gp,f),B(_p,h),B(xp,p),B(vp,_),B(dp,M),B(pp,g),B(bp,x),B(yp,L),B(Sp,I),B(Qh,y),B(ef,b),B(Mp,T),ze(Q))if(Q.length){const N=n.exposed||(n.exposed={});Q.forEach(ie=>{Object.defineProperty(N,ie,{get:()=>t[ie],set:le=>t[ie]=le,enumerable:!0})})}else n.exposed||(n.exposed={});P&&n.render===Ln&&(n.render=P),D!=null&&(n.inheritAttrs=D),X&&(n.components=X),Y&&(n.directives=Y),T&&$h(n)}function wp(n,e,t=Ln){ze(n)&&(n=ko(n));for(const i in n){const s=n[i];let r;ot(s)?"default"in s?r=Qr(s.from||i,s.default,!0):r=Qr(s.from||i):r=Qr(s),Nt(r)?Object.defineProperty(e,i,{enumerable:!0,configurable:!0,get:()=>r.value,set:a=>r.value=a}):e[i]=r}}function Fc(n,e,t){Fn(ze(n)?n.map(i=>i.bind(e.proxy)):n.bind(e.proxy),e,t)}function tf(n,e,t,i){let s=i.includes(".")?Kh(t,i):()=>t[i];if(St(n)){const r=e[n];je(r)&&ea(s,r)}else if(je(n))ea(s,n.bind(t));else if(ot(n))if(ze(n))n.forEach(r=>tf(r,e,t,i));else{const r=je(n.handler)?n.handler.bind(t):e[n.handler];je(r)&&ea(s,r,n)}}function nf(n){const e=n.type,{mixins:t,extends:i}=e,{mixins:s,optionsCache:r,config:{optionMergeStrategies:a}}=n.appContext,o=r.get(e);let l;return o?l=o:!s.length&&!t&&!i?l=e:(l={},s.length&&s.forEach(c=>pa(l,c,a,!0)),pa(l,e,a)),ot(e)&&r.set(e,l),l}function pa(n,e,t,i=!1){const{mixins:s,extends:r}=e;r&&pa(n,r,t,!0),s&&s.forEach(a=>pa(n,a,t,!0));for(const a in e)if(!(i&&a==="expose")){const o=Cp[a]||t&&t[a];n[a]=o?o(n[a],e[a]):e[a]}return n}const Cp={data:Oc,props:Bc,emits:Bc,methods:qs,computed:qs,beforeCreate:Bt,created:Bt,beforeMount:Bt,mounted:Bt,beforeUpdate:Bt,updated:Bt,beforeDestroy:Bt,beforeUnmount:Bt,destroyed:Bt,unmounted:Bt,activated:Bt,deactivated:Bt,errorCaptured:Bt,serverPrefetch:Bt,components:qs,directives:qs,watch:Pp,provide:Oc,inject:Rp};function Oc(n,e){return e?n?function(){return Ot(je(n)?n.call(this,this):n,je(e)?e.call(this,this):e)}:e:n}function Rp(n,e){return qs(ko(n),ko(e))}function ko(n){if(ze(n)){const e={};for(let t=0;t<n.length;t++)e[n[t]]=n[t];return e}return n}function Bt(n,e){return n?[...new Set([].concat(n,e))]:e}function qs(n,e){return n?Ot(Object.create(null),n,e):e}function Bc(n,e){return n?ze(n)&&ze(e)?[...new Set([...n,...e])]:Ot(Object.create(null),Nc(n),Nc(e??{})):e}function Pp(n,e){if(!n)return e;if(!e)return n;const t=Ot(Object.create(null),n);for(const i in e)t[i]=Bt(n[i],e[i]);return t}function sf(){return{app:null,config:{isNativeTag:_h,performance:!1,globalProperties:{},optionMergeStrategies:{},errorHandler:void 0,warnHandler:void 0,compilerOptions:{}},mixins:[],components:{},directives:{},provides:Object.create(null),optionsCache:new WeakMap,propsCache:new WeakMap,emitsCache:new WeakMap}}let Dp=0;function Lp(n,e){return function(i,s=null){je(i)||(i=Ot({},i)),s!=null&&!ot(s)&&(s=null);const r=sf(),a=new WeakSet,o=[];let l=!1;const c=r.app={_uid:Dp++,_component:i,_props:s,_container:null,_context:r,_instance:null,version:pm,get config(){return r.config},set config(u){},use(u,...f){return a.has(u)||(u&&je(u.install)?(a.add(u),u.install(c,...f)):je(u)&&(a.add(u),u(c,...f))),c},mixin(u){return r.mixins.includes(u)||r.mixins.push(u),c},component(u,f){return f?(r.components[u]=f,c):r.components[u]},directive(u,f){return f?(r.directives[u]=f,c):r.directives[u]},mount(u,f,h){if(!l){const p=c._ceVNode||en(i,s);return p.appContext=r,h===!0?h="svg":h===!1&&(h=void 0),n(p,u,h),l=!0,c._container=u,u.__vue_app__=c,Ia(p.component)}},onUnmount(u){o.push(u)},unmount(){l&&(Fn(o,c._instance,16),n(null,c._container),delete c._container.__vue_app__)},provide(u,f){return r.provides[u]=f,c},runWithContext(u){const f=vs;vs=c;try{return u()}finally{vs=f}}};return c}}let vs=null;const Ip=(n,e)=>e==="modelValue"||e==="model-value"?n.modelModifiers:n[`${e}Modifiers`]||n[`${gn(e)}Modifiers`]||n[`${Xi(e)}Modifiers`];function Up(n,e,...t){if(n.isUnmounted)return;const i=n.vnode.props||dt;let s=t;const r=e.startsWith("update:"),a=r&&Ip(i,e.slice(7));a&&(a.trim&&(s=t.map(u=>St(u)?u.trim():u)),a.number&&(s=t.map(Vl)));let o,l=i[o=Ha(e)]||i[o=Ha(gn(e))];!l&&r&&(l=i[o=Ha(Xi(e))]),l&&Fn(l,n,6,s);const c=i[o+"Once"];if(c){if(!n.emitted)n.emitted={};else if(n.emitted[o])return;n.emitted[o]=!0,Fn(c,n,6,s)}}const Np=new WeakMap;function rf(n,e,t=!1){const i=t?Np:e.emitsCache,s=i.get(n);if(s!==void 0)return s;const r=n.emits;let a={},o=!1;if(!je(n)){const l=c=>{const u=rf(c,e,!0);u&&(o=!0,Ot(a,u))};!t&&e.mixins.length&&e.mixins.forEach(l),n.extends&&l(n.extends),n.mixins&&n.mixins.forEach(l)}return!r&&!o?(ot(n)&&i.set(n,null),null):(ze(r)?r.forEach(l=>a[l]=null):Ot(a,r),ot(n)&&i.set(n,a),a)}function Da(n,e){return!n||!Ea(e)?!1:(e=e.slice(2).replace(/Once$/,""),st(n,e[0].toLowerCase()+e.slice(1))||st(n,Xi(e))||st(n,e))}function zc(n){const{type:e,vnode:t,proxy:i,withProxy:s,propsOptions:[r],slots:a,attrs:o,emit:l,render:c,renderCache:u,props:f,data:h,setupState:p,ctx:_,inheritAttrs:M}=n,g=fa(n);let d,y;try{if(t.shapeFlag&4){const b=s||i,P=b;d=wn(c.call(P,b,u,f,p,h,_)),y=o}else{const b=e;d=wn(b.length>1?b(f,{attrs:o,slots:a,emit:l}):b(f,null)),y=e.props?o:Fp(o)}}catch(b){ir.length=0,Ra(b,n,1),d=en(Ei)}let A=d;if(y&&M!==!1){const b=Object.keys(y),{shapeFlag:P}=A;b.length&&P&7&&(r&&b.some(Ta)&&(y=Op(y,r)),A=Es(A,y,!1,!0))}return t.dirs&&(A=Es(A,null,!1,!0),A.dirs=A.dirs?A.dirs.concat(t.dirs):t.dirs),t.transition&&Jl(A,t.transition),d=A,fa(g),d}const Fp=n=>{let e;for(const t in n)(t==="class"||t==="style"||Ea(t))&&((e||(e={}))[t]=n[t]);return e},Op=(n,e)=>{const t={};for(const i in n)(!Ta(i)||!(i.slice(9)in e))&&(t[i]=n[i]);return t};function Bp(n,e,t){const{props:i,children:s,component:r}=n,{props:a,children:o,patchFlag:l}=e,c=r.emitsOptions;if(e.dirs||e.transition)return!0;if(t&&l>=0){if(l&1024)return!0;if(l&16)return i?Hc(i,a,c):!!a;if(l&8){const u=e.dynamicProps;for(let f=0;f<u.length;f++){const h=u[f];if(af(a,i,h)&&!Da(c,h))return!0}}}else return(s||o)&&(!o||!o.$stable)?!0:i===a?!1:i?a?Hc(i,a,c):!0:!!a;return!1}function Hc(n,e,t){const i=Object.keys(e);if(i.length!==Object.keys(n).length)return!0;for(let s=0;s<i.length;s++){const r=i[s];if(af(e,n,r)&&!Da(t,r))return!0}return!1}function af(n,e,t){const i=n[t],s=e[t];return t==="style"&&ot(i)&&ot(s)?!kl(i,s):i!==s}function zp({vnode:n,parent:e,suspense:t},i){for(;e;){const s=e.subTree;if(s.suspense&&s.suspense.activeBranch===n&&(s.suspense.vnode.el=s.el=i,n=s),s===n)(n=e.vnode).el=i,e=e.parent;else break}t&&t.activeBranch===n&&(t.vnode.el=i)}const of={},lf=()=>Object.create(of),cf=n=>Object.getPrototypeOf(n)===of;function Hp(n,e,t,i=!1){const s={},r=lf();n.propsDefaults=Object.create(null),uf(n,e,s,r);for(const a in n.propsOptions[0])a in s||(s[a]=void 0);t?n.props=i?s:Yd(s):n.type.props?n.props=s:n.props=r,n.attrs=r}function Vp(n,e,t,i){const{props:s,attrs:r,vnode:{patchFlag:a}}=n,o=it(s),[l]=n.propsOptions;let c=!1;if((i||a>0)&&!(a&16)){if(a&8){const u=n.vnode.dynamicProps;for(let f=0;f<u.length;f++){let h=u[f];if(Da(n.emitsOptions,h))continue;const p=e[h];if(l)if(st(r,h))p!==r[h]&&(r[h]=p,c=!0);else{const _=gn(h);s[_]=Go(l,o,_,p,n,!1)}else p!==r[h]&&(r[h]=p,c=!0)}}}else{uf(n,e,s,r)&&(c=!0);let u;for(const f in o)(!e||!st(e,f)&&((u=Xi(f))===f||!st(e,u)))&&(l?t&&(t[f]!==void 0||t[u]!==void 0)&&(s[f]=Go(l,o,f,void 0,n,!0)):delete s[f]);if(r!==o)for(const f in r)(!e||!st(e,f))&&(delete r[f],c=!0)}c&&Zn(n.attrs,"set","")}function uf(n,e,t,i){const[s,r]=n.propsOptions;let a=!1,o;if(e)for(let l in e){if(Zs(l))continue;const c=e[l];let u;s&&st(s,u=gn(l))?!r||!r.includes(u)?t[u]=c:(o||(o={}))[u]=c:Da(n.emitsOptions,l)||(!(l in i)||c!==i[l])&&(i[l]=c,a=!0)}if(r){const l=it(t),c=o||dt;for(let u=0;u<r.length;u++){const f=r[u];t[f]=Go(s,l,f,c[f],n,!st(c,f))}}return a}function Go(n,e,t,i,s,r){const a=n[t];if(a!=null){const o=st(a,"default");if(o&&i===void 0){const l=a.default;if(a.type!==Function&&!a.skipFactory&&je(l)){const{propsDefaults:c}=s;if(t in c)i=c[t];else{const u=_r(s);i=c[t]=l.call(null,e),u()}}else i=l;s.ce&&s.ce._setProp(t,i)}a[0]&&(r&&!o?i=!1:a[1]&&(i===""||i===Xi(t))&&(i=!0))}return i}const kp=new WeakMap;function hf(n,e,t=!1){const i=t?kp:e.propsCache,s=i.get(n);if(s)return s;const r=n.props,a={},o=[];let l=!1;if(!je(n)){const u=f=>{l=!0;const[h,p]=hf(f,e,!0);Ot(a,h),p&&o.push(...p)};!t&&e.mixins.length&&e.mixins.forEach(u),n.extends&&u(n.extends),n.mixins&&n.mixins.forEach(u)}if(!r&&!l)return ot(n)&&i.set(n,gs),gs;if(ze(r))for(let u=0;u<r.length;u++){const f=gn(r[u]);Vc(f)&&(a[f]=dt)}else if(r)for(const u in r){const f=gn(u);if(Vc(f)){const h=r[u],p=a[f]=ze(h)||je(h)?{type:h}:Ot({},h),_=p.type;let M=!1,g=!0;if(ze(_))for(let d=0;d<_.length;++d){const y=_[d],A=je(y)&&y.name;if(A==="Boolean"){M=!0;break}else A==="String"&&(g=!1)}else M=je(_)&&_.name==="Boolean";p[0]=M,p[1]=g,(M||st(p,"default"))&&o.push(f)}}const c=[a,o];return ot(n)&&i.set(n,c),c}function Vc(n){return n[0]!=="$"&&!Zs(n)}const Ql=n=>n==="_"||n==="_ctx"||n==="$stable",ec=n=>ze(n)?n.map(wn):[wn(n)],Gp=(n,e,t)=>{if(e._n)return e;const i=rp((...s)=>ec(e(...s)),t);return i._c=!1,i},ff=(n,e,t)=>{const i=n._ctx;for(const s in n){if(Ql(s))continue;const r=n[s];if(je(r))e[s]=Gp(s,r,i);else if(r!=null){const a=ec(r);e[s]=()=>a}}},df=(n,e)=>{const t=ec(e);n.slots.default=()=>t},pf=(n,e,t)=>{for(const i in e)(t||!Ql(i))&&(n[i]=e[i])},Wp=(n,e,t)=>{const i=n.slots=lf();if(n.vnode.shapeFlag&32){const s=e._;s?(pf(i,e,t),t&&bh(i,"_",s,!0)):ff(e,i)}else e&&df(n,e)},Xp=(n,e,t)=>{const{vnode:i,slots:s}=n;let r=!0,a=dt;if(i.shapeFlag&32){const o=e._;o?t&&o===1?r=!1:pf(s,e,t):(r=!e.$stable,ff(e,s)),a=e}else e&&(df(n,e),a={default:1});if(r)for(const o in s)!Ql(o)&&a[o]==null&&delete s[o]},Wt=$p;function Yp(n){return qp(n)}function qp(n,e){const t=wa();t.__VUE__=!0;const{insert:i,remove:s,patchProp:r,createElement:a,createText:o,createComment:l,setText:c,setElementText:u,parentNode:f,nextSibling:h,setScopeId:p=Ln,insertStaticContent:_}=n,M=(w,R,z,ee=null,q=null,ne=null,E=void 0,ue=null,re=!!R.dynamicChildren)=>{if(w===R)return;w&&!Us(w,R)&&(ee=pe(w),oe(w,q,ne,!0),w=null),R.patchFlag===-2&&(re=!1,R.dynamicChildren=null);const{type:J,ref:ae,shapeFlag:v}=R;switch(J){case La:g(w,R,z,ee);break;case Ei:d(w,R,z,ee);break;case Ka:w==null&&y(R,z,ee,E);break;case rn:X(w,R,z,ee,q,ne,E,ue,re);break;default:v&1?P(w,R,z,ee,q,ne,E,ue,re):v&6?Y(w,R,z,ee,q,ne,E,ue,re):(v&64||v&128)&&J.process(w,R,z,ee,q,ne,E,ue,re,Fe)}ae!=null&&q?er(ae,w&&w.ref,ne,R||w,!R):ae==null&&w&&w.ref!=null&&er(w.ref,null,ne,w,!0)},g=(w,R,z,ee)=>{if(w==null)i(R.el=o(R.children),z,ee);else{const q=R.el=w.el;R.children!==w.children&&c(q,R.children)}},d=(w,R,z,ee)=>{w==null?i(R.el=l(R.children||""),z,ee):R.el=w.el},y=(w,R,z,ee)=>{[w.el,w.anchor]=_(w.children,R,z,ee,w.el,w.anchor)},A=({el:w,anchor:R},z,ee)=>{let q;for(;w&&w!==R;)q=h(w),i(w,z,ee),w=q;i(R,z,ee)},b=({el:w,anchor:R})=>{let z;for(;w&&w!==R;)z=h(w),s(w),w=z;s(R)},P=(w,R,z,ee,q,ne,E,ue,re)=>{if(R.type==="svg"?E="svg":R.type==="math"&&(E="mathml"),w==null)L(R,z,ee,q,ne,E,ue,re);else{const J=w.el&&w.el._isVueCE?w.el:null;try{J&&J._beginPatch(),T(w,R,q,ne,E,ue,re)}finally{J&&J._endPatch()}}},L=(w,R,z,ee,q,ne,E,ue)=>{let re,J;const{props:ae,shapeFlag:v,transition:m,dirs:C}=w;if(re=w.el=a(w.type,ne,ae&&ae.is,ae),v&8?u(re,w.children):v&16&&x(w.children,re,null,ee,q,ja(w,ne),E,ue),C&&Ci(w,null,ee,"created"),I(re,w,w.scopeId,E,ee),ae){for(const K in ae)K!=="value"&&!Zs(K)&&r(re,K,null,ae[K],ne,ee);"value"in ae&&r(re,"value",null,ae.value,ne),(J=ae.onVnodeBeforeMount)&&yn(J,ee,w)}C&&Ci(w,null,ee,"beforeMount");const V=jp(q,m);V&&m.beforeEnter(re),i(re,R,z),((J=ae&&ae.onVnodeMounted)||V||C)&&Wt(()=>{J&&yn(J,ee,w),V&&m.enter(re),C&&Ci(w,null,ee,"mounted")},q)},I=(w,R,z,ee,q)=>{if(z&&p(w,z),ee)for(let ne=0;ne<ee.length;ne++)p(w,ee[ne]);if(q){let ne=q.subTree;if(R===ne||xf(ne.type)&&(ne.ssContent===R||ne.ssFallback===R)){const E=q.vnode;I(w,E,E.scopeId,E.slotScopeIds,q.parent)}}},x=(w,R,z,ee,q,ne,E,ue,re=0)=>{for(let J=re;J<w.length;J++){const ae=w[J]=ue?$n(w[J]):wn(w[J]);M(null,ae,R,z,ee,q,ne,E,ue)}},T=(w,R,z,ee,q,ne,E)=>{const ue=R.el=w.el;let{patchFlag:re,dynamicChildren:J,dirs:ae}=R;re|=w.patchFlag&16;const v=w.props||dt,m=R.props||dt;let C;if(z&&Ri(z,!1),(C=m.onVnodeBeforeUpdate)&&yn(C,z,R,w),ae&&Ci(R,w,z,"beforeUpdate"),z&&Ri(z,!0),(v.innerHTML&&m.innerHTML==null||v.textContent&&m.textContent==null)&&u(ue,""),J?Q(w.dynamicChildren,J,ue,z,ee,ja(R,q),ne):E||ie(w,R,ue,null,z,ee,ja(R,q),ne,!1),re>0){if(re&16)D(ue,v,m,z,q);else if(re&2&&v.class!==m.class&&r(ue,"class",null,m.class,q),re&4&&r(ue,"style",v.style,m.style,q),re&8){const V=R.dynamicProps;for(let K=0;K<V.length;K++){const k=V[K],_e=v[k],ce=m[k];(ce!==_e||k==="value")&&r(ue,k,_e,ce,q,z)}}re&1&&w.children!==R.children&&u(ue,R.children)}else!E&&J==null&&D(ue,v,m,z,q);((C=m.onVnodeUpdated)||ae)&&Wt(()=>{C&&yn(C,z,R,w),ae&&Ci(R,w,z,"updated")},ee)},Q=(w,R,z,ee,q,ne,E)=>{for(let ue=0;ue<R.length;ue++){const re=w[ue],J=R[ue],ae=re.el&&(re.type===rn||!Us(re,J)||re.shapeFlag&198)?f(re.el):z;M(re,J,ae,null,ee,q,ne,E,!0)}},D=(w,R,z,ee,q)=>{if(R!==z){if(R!==dt)for(const ne in R)!Zs(ne)&&!(ne in z)&&r(w,ne,R[ne],null,q,ee);for(const ne in z){if(Zs(ne))continue;const E=z[ne],ue=R[ne];E!==ue&&ne!=="value"&&r(w,ne,ue,E,q,ee)}"value"in z&&r(w,"value",R.value,z.value,q)}},X=(w,R,z,ee,q,ne,E,ue,re)=>{const J=R.el=w?w.el:o(""),ae=R.anchor=w?w.anchor:o("");let{patchFlag:v,dynamicChildren:m,slotScopeIds:C}=R;C&&(ue=ue?ue.concat(C):C),w==null?(i(J,z,ee),i(ae,z,ee),x(R.children||[],z,ae,q,ne,E,ue,re)):v>0&&v&64&&m&&w.dynamicChildren&&w.dynamicChildren.length===m.length?(Q(w.dynamicChildren,m,z,q,ne,E,ue),(R.key!=null||q&&R===q.subTree)&&mf(w,R,!0)):ie(w,R,z,ae,q,ne,E,ue,re)},Y=(w,R,z,ee,q,ne,E,ue,re)=>{R.slotScopeIds=ue,w==null?R.shapeFlag&512?q.ctx.activate(R,z,ee,E,re):$(R,z,ee,q,ne,E,re):W(w,R,re)},$=(w,R,z,ee,q,ne,E)=>{const ue=w.component=am(w,ee,q);if(Zh(w)&&(ue.ctx.renderer=Fe),lm(ue,!1,E),ue.asyncDep){if(q&&q.registerDep(ue,B,E),!w.el){const re=ue.subTree=en(Ei);d(null,re,R,z),w.placeholder=re.el}}else B(ue,w,R,z,q,ne,E)},W=(w,R,z)=>{const ee=R.component=w.component;if(Bp(w,R,z))if(ee.asyncDep&&!ee.asyncResolved){N(ee,R,z);return}else ee.next=R,ee.update();else R.el=w.el,ee.vnode=R},B=(w,R,z,ee,q,ne,E)=>{const ue=()=>{if(w.isMounted){let{next:v,bu:m,u:C,parent:V,vnode:K}=w;{const Le=gf(w);if(Le){v&&(v.el=K.el,N(w,v,E)),Le.asyncDep.then(()=>{Wt(()=>{w.isUnmounted||J()},q)});return}}let k=v,_e;Ri(w,!1),v?(v.el=K.el,N(w,v,E)):v=K,m&&Jr(m),(_e=v.props&&v.props.onVnodeBeforeUpdate)&&yn(_e,V,v,K),Ri(w,!0);const ce=zc(w),Ce=w.subTree;w.subTree=ce,M(Ce,ce,f(Ce.el),pe(Ce),w,q,ne),v.el=ce.el,k===null&&zp(w,ce.el),C&&Wt(C,q),(_e=v.props&&v.props.onVnodeUpdated)&&Wt(()=>yn(_e,V,v,K),q)}else{let v;const{el:m,props:C}=R,{bm:V,m:K,parent:k,root:_e,type:ce}=w,Ce=tr(R);Ri(w,!1),V&&Jr(V),!Ce&&(v=C&&C.onVnodeBeforeMount)&&yn(v,k,R),Ri(w,!0);{_e.ce&&_e.ce._hasShadowRoot()&&_e.ce._injectChildStyle(ce,w.parent?w.parent.type:void 0);const Le=w.subTree=zc(w);M(null,Le,z,ee,w,q,ne),R.el=Le.el}if(K&&Wt(K,q),!Ce&&(v=C&&C.onVnodeMounted)){const Le=R;Wt(()=>yn(v,k,Le),q)}(R.shapeFlag&256||k&&tr(k.vnode)&&k.vnode.shapeFlag&256)&&w.a&&Wt(w.a,q),w.isMounted=!0,R=z=ee=null}};w.scope.on();const re=w.effect=new wh(ue);w.scope.off();const J=w.update=re.run.bind(re),ae=w.job=re.runIfDirty.bind(re);ae.i=w,ae.id=w.uid,re.scheduler=()=>Zl(ae),Ri(w,!0),J()},N=(w,R,z)=>{R.component=w;const ee=w.vnode.props;w.vnode=R,w.next=null,Vp(w,R.props,ee,z),Xp(w,R.children,z),ni(),Lc(w),ii()},ie=(w,R,z,ee,q,ne,E,ue,re=!1)=>{const J=w&&w.children,ae=w?w.shapeFlag:0,v=R.children,{patchFlag:m,shapeFlag:C}=R;if(m>0){if(m&128){ve(J,v,z,ee,q,ne,E,ue,re);return}else if(m&256){le(J,v,z,ee,q,ne,E,ue,re);return}}C&8?(ae&16&&te(J,q,ne),v!==J&&u(z,v)):ae&16?C&16?ve(J,v,z,ee,q,ne,E,ue,re):te(J,q,ne,!0):(ae&8&&u(z,""),C&16&&x(v,z,ee,q,ne,E,ue,re))},le=(w,R,z,ee,q,ne,E,ue,re)=>{w=w||gs,R=R||gs;const J=w.length,ae=R.length,v=Math.min(J,ae);let m;for(m=0;m<v;m++){const C=R[m]=re?$n(R[m]):wn(R[m]);M(w[m],C,z,null,q,ne,E,ue,re)}J>ae?te(w,q,ne,!0,!1,v):x(R,z,ee,q,ne,E,ue,re,v)},ve=(w,R,z,ee,q,ne,E,ue,re)=>{let J=0;const ae=R.length;let v=w.length-1,m=ae-1;for(;J<=v&&J<=m;){const C=w[J],V=R[J]=re?$n(R[J]):wn(R[J]);if(Us(C,V))M(C,V,z,null,q,ne,E,ue,re);else break;J++}for(;J<=v&&J<=m;){const C=w[v],V=R[m]=re?$n(R[m]):wn(R[m]);if(Us(C,V))M(C,V,z,null,q,ne,E,ue,re);else break;v--,m--}if(J>v){if(J<=m){const C=m+1,V=C<ae?R[C].el:ee;for(;J<=m;)M(null,R[J]=re?$n(R[J]):wn(R[J]),z,V,q,ne,E,ue,re),J++}}else if(J>m)for(;J<=v;)oe(w[J],q,ne,!0),J++;else{const C=J,V=J,K=new Map;for(J=V;J<=m;J++){const Me=R[J]=re?$n(R[J]):wn(R[J]);Me.key!=null&&K.set(Me.key,J)}let k,_e=0;const ce=m-V+1;let Ce=!1,Le=0;const he=new Array(ce);for(J=0;J<ce;J++)he[J]=0;for(J=C;J<=v;J++){const Me=w[J];if(_e>=ce){oe(Me,q,ne,!0);continue}let Ee;if(Me.key!=null)Ee=K.get(Me.key);else for(k=V;k<=m;k++)if(he[k-V]===0&&Us(Me,R[k])){Ee=k;break}Ee===void 0?oe(Me,q,ne,!0):(he[Ee-V]=J+1,Ee>=Le?Le=Ee:Ce=!0,M(Me,R[Ee],z,null,q,ne,E,ue,re),_e++)}const de=Ce?Kp(he):gs;for(k=de.length-1,J=ce-1;J>=0;J--){const Me=V+J,Ee=R[Me],Te=R[Me+1],We=Me+1<ae?Te.el||_f(Te):ee;he[J]===0?M(null,Ee,z,We,q,ne,E,ue,re):Ce&&(k<0||J!==de[k]?se(Ee,z,We,2):k--)}}},se=(w,R,z,ee,q=null)=>{const{el:ne,type:E,transition:ue,children:re,shapeFlag:J}=w;if(J&6){se(w.component.subTree,R,z,ee);return}if(J&128){w.suspense.move(R,z,ee);return}if(J&64){E.move(w,R,z,Fe);return}if(E===rn){i(ne,R,z);for(let v=0;v<re.length;v++)se(re[v],R,z,ee);i(w.anchor,R,z);return}if(E===Ka){A(w,R,z);return}if(ee!==2&&J&1&&ue)if(ee===0)ue.beforeEnter(ne),i(ne,R,z),Wt(()=>ue.enter(ne),q);else{const{leave:v,delayLeave:m,afterLeave:C}=ue,V=()=>{w.ctx.isUnmounted?s(ne):i(ne,R,z)},K=()=>{ne._isLeaving&&ne[fp](!0),v(ne,()=>{V(),C&&C()})};m?m(ne,V,K):K()}else i(ne,R,z)},oe=(w,R,z,ee=!1,q=!1)=>{const{type:ne,props:E,ref:ue,children:re,dynamicChildren:J,shapeFlag:ae,patchFlag:v,dirs:m,cacheIndex:C,memo:V}=w;if(v===-2&&(q=!1),ue!=null&&(ni(),er(ue,null,z,w,!0),ii()),C!=null&&(R.renderCache[C]=void 0),ae&256){R.ctx.deactivate(w);return}const K=ae&1&&m,k=!tr(w);let _e;if(k&&(_e=E&&E.onVnodeBeforeUnmount)&&yn(_e,R,w),ae&6)nt(w.component,z,ee);else{if(ae&128){w.suspense.unmount(z,ee);return}K&&Ci(w,null,R,"beforeUnmount"),ae&64?w.type.remove(w,R,z,Fe,ee):J&&!J.hasOnce&&(ne!==rn||v>0&&v&64)?te(J,R,z,!1,!0):(ne===rn&&v&384||!q&&ae&16)&&te(re,R,z),ee&&we(w)}const ce=V!=null&&C==null;(k&&(_e=E&&E.onVnodeUnmounted)||K||ce)&&Wt(()=>{_e&&yn(_e,R,w),K&&Ci(w,null,R,"unmounted"),ce&&(w.el=null)},z)},we=w=>{const{type:R,el:z,anchor:ee,transition:q}=w;if(R===rn){Ge(z,ee);return}if(R===Ka){b(w);return}const ne=()=>{s(z),q&&!q.persisted&&q.afterLeave&&q.afterLeave()};if(w.shapeFlag&1&&q&&!q.persisted){const{leave:E,delayLeave:ue}=q,re=()=>E(z,ne);ue?ue(w.el,ne,re):re()}else ne()},Ge=(w,R)=>{let z;for(;w!==R;)z=h(w),s(w),w=z;s(R)},nt=(w,R,z)=>{const{bum:ee,scope:q,job:ne,subTree:E,um:ue,m:re,a:J}=w;kc(re),kc(J),ee&&Jr(ee),q.stop(),ne&&(ne.flags|=8,oe(E,w,R,z)),ue&&Wt(ue,R),Wt(()=>{w.isUnmounted=!0},R)},te=(w,R,z,ee=!1,q=!1,ne=0)=>{for(let E=ne;E<w.length;E++)oe(w[E],R,z,ee,q)},pe=w=>{if(w.shapeFlag&6)return pe(w.component.subTree);if(w.shapeFlag&128)return w.suspense.next();const R=h(w.anchor||w.el),z=R&&R[up];return z?h(z):R};let ge=!1;const He=(w,R,z)=>{let ee;w==null?R._vnode&&(oe(R._vnode,null,null,!0),ee=R._vnode.component):M(R._vnode||null,w,R,null,null,null,z),R._vnode=w,ge||(ge=!0,Lc(ee),Xh(),ge=!1)},Fe={p:M,um:oe,m:se,r:we,mt:$,mc:x,pc:ie,pbc:Q,n:pe,o:n};return{render:He,hydrate:void 0,createApp:Lp(He)}}function ja({type:n,props:e},t){return t==="svg"&&n==="foreignObject"||t==="mathml"&&n==="annotation-xml"&&e&&e.encoding&&e.encoding.includes("html")?void 0:t}function Ri({effect:n,job:e},t){t?(n.flags|=32,e.flags|=4):(n.flags&=-33,e.flags&=-5)}function jp(n,e){return(!n||n&&!n.pendingBranch)&&e&&!e.persisted}function mf(n,e,t=!1){const i=n.children,s=e.children;if(ze(i)&&ze(s))for(let r=0;r<i.length;r++){const a=i[r];let o=s[r];o.shapeFlag&1&&!o.dynamicChildren&&((o.patchFlag<=0||o.patchFlag===32)&&(o=s[r]=$n(s[r]),o.el=a.el),!t&&o.patchFlag!==-2&&mf(a,o)),o.type===La&&(o.patchFlag===-1&&(o=s[r]=$n(o)),o.el=a.el),o.type===Ei&&!o.el&&(o.el=a.el)}}function Kp(n){const e=n.slice(),t=[0];let i,s,r,a,o;const l=n.length;for(i=0;i<l;i++){const c=n[i];if(c!==0){if(s=t[t.length-1],n[s]<c){e[i]=s,t.push(i);continue}for(r=0,a=t.length-1;r<a;)o=r+a>>1,n[t[o]]<c?r=o+1:a=o;c<n[t[r]]&&(r>0&&(e[i]=t[r-1]),t[r]=i)}}for(r=t.length,a=t[r-1];r-- >0;)t[r]=a,a=e[a];return t}function gf(n){const e=n.subTree.component;if(e)return e.asyncDep&&!e.asyncResolved?e:gf(e)}function kc(n){if(n)for(let e=0;e<n.length;e++)n[e].flags|=8}function _f(n){if(n.placeholder)return n.placeholder;const e=n.component;return e?_f(e.subTree):null}const xf=n=>n.__isSuspense;function $p(n,e){e&&e.pendingBranch?ze(n)?e.effects.push(...n):e.effects.push(n):sp(n)}const rn=Symbol.for("v-fgt"),La=Symbol.for("v-txt"),Ei=Symbol.for("v-cmt"),Ka=Symbol.for("v-stc"),ir=[];let Qt=null;function Oi(n=!1){ir.push(Qt=n?null:[])}function Zp(){ir.pop(),Qt=ir[ir.length-1]||null}let lr=1;function ma(n,e=!1){lr+=n,n<0&&Qt&&e&&(Qt.hasOnce=!0)}function vf(n){return n.dynamicChildren=lr>0?Qt||gs:null,Zp(),lr>0&&Qt&&Qt.push(n),n}function Zi(n,e,t,i,s,r){return vf(Ye(n,e,t,i,s,r,!0))}function Jp(n,e,t,i,s){return vf(en(n,e,t,i,s,!0))}function ga(n){return n?n.__v_isVNode===!0:!1}function Us(n,e){return n.type===e.type&&n.key===e.key}const Mf=({key:n})=>n??null,ta=({ref:n,ref_key:e,ref_for:t})=>(typeof n=="number"&&(n=""+n),n!=null?St(n)||Nt(n)||je(n)?{i:on,r:n,k:e,f:!!t}:n:null);function Ye(n,e=null,t=null,i=0,s=null,r=n===rn?0:1,a=!1,o=!1){const l={__v_isVNode:!0,__v_skip:!0,type:n,props:e,key:e&&Mf(e),ref:e&&ta(e),scopeId:qh,slotScopeIds:null,children:t,component:null,suspense:null,ssContent:null,ssFallback:null,dirs:null,transition:null,el:null,anchor:null,target:null,targetStart:null,targetAnchor:null,staticCount:0,shapeFlag:r,patchFlag:i,dynamicProps:s,dynamicChildren:null,appContext:null,ctx:on};return o?(tc(l,t),r&128&&n.normalize(l)):t&&(l.shapeFlag|=St(t)?8:16),lr>0&&!a&&Qt&&(l.patchFlag>0||r&6)&&l.patchFlag!==32&&Qt.push(l),l}const en=Qp;function Qp(n,e=null,t=null,i=0,s=null,r=!1){if((!n||n===Ep)&&(n=Ei),ga(n)){const o=Es(n,e,!0);return t&&tc(o,t),lr>0&&!r&&Qt&&(o.shapeFlag&6?Qt[Qt.indexOf(n)]=o:Qt.push(o)),o.patchFlag=-2,o}if(fm(n)&&(n=n.__vccOpts),e){e=em(e);let{class:o,style:l}=e;o&&!St(o)&&(e.class=Mi(o)),ot(l)&&($l(l)&&!ze(l)&&(l=Ot({},l)),e.style=sr(l))}const a=St(n)?1:xf(n)?128:hp(n)?64:ot(n)?4:je(n)?2:0;return Ye(n,e,t,i,s,a,r,!0)}function em(n){return n?$l(n)||cf(n)?Ot({},n):n:null}function Es(n,e,t=!1,i=!1){const{props:s,ref:r,patchFlag:a,children:o,transition:l}=n,c=e?im(s||{},e):s,u={__v_isVNode:!0,__v_skip:!0,type:n.type,props:c,key:c&&Mf(c),ref:e&&e.ref?t&&r?ze(r)?r.concat(ta(e)):[r,ta(e)]:ta(e):r,scopeId:n.scopeId,slotScopeIds:n.slotScopeIds,children:o,target:n.target,targetStart:n.targetStart,targetAnchor:n.targetAnchor,staticCount:n.staticCount,shapeFlag:n.shapeFlag,patchFlag:e&&n.type!==rn?a===-1?16:a|16:a,dynamicProps:n.dynamicProps,dynamicChildren:n.dynamicChildren,appContext:n.appContext,dirs:n.dirs,transition:l,component:n.component,suspense:n.suspense,ssContent:n.ssContent&&Es(n.ssContent),ssFallback:n.ssFallback&&Es(n.ssFallback),placeholder:n.placeholder,el:n.el,anchor:n.anchor,ctx:n.ctx,ce:n.ce};return l&&i&&Jl(u,l.clone(u)),u}function tm(n=" ",e=0){return en(La,null,n,e)}function nm(n="",e=!1){return e?(Oi(),Jp(Ei,null,n)):en(Ei,null,n)}function wn(n){return n==null||typeof n=="boolean"?en(Ei):ze(n)?en(rn,null,n.slice()):ga(n)?$n(n):en(La,null,String(n))}function $n(n){return n.el===null&&n.patchFlag!==-1||n.memo?n:Es(n)}function tc(n,e){let t=0;const{shapeFlag:i}=n;if(e==null)e=null;else if(ze(e))t=16;else if(typeof e=="object")if(i&65){const s=e.default;s&&(s._c&&(s._d=!1),tc(n,s()),s._c&&(s._d=!0));return}else{t=32;const s=e._;!s&&!cf(e)?e._ctx=on:s===3&&on&&(on.slots._===1?e._=1:(e._=2,n.patchFlag|=1024))}else je(e)?(e={default:e,_ctx:on},t=32):(e=String(e),i&64?(t=16,e=[tm(e)]):t=8);n.children=e,n.shapeFlag|=t}function im(...n){const e={};for(let t=0;t<n.length;t++){const i=n[t];for(const s in i)if(s==="class")e.class!==i.class&&(e.class=Mi([e.class,i.class]));else if(s==="style")e.style=sr([e.style,i.style]);else if(Ea(s)){const r=e[s],a=i[s];a&&r!==a&&!(ze(r)&&r.includes(a))?e[s]=r?[].concat(r,a):a:a==null&&r==null&&!Ta(s)&&(e[s]=a)}else s!==""&&(e[s]=i[s])}return e}function yn(n,e,t,i=null){Fn(n,e,7,[t,i])}const sm=sf();let rm=0;function am(n,e,t){const i=n.type,s=(e?e.appContext:n.appContext)||sm,r={uid:rm++,vnode:n,type:i,parent:e,appContext:s,root:null,next:null,subTree:null,effect:null,update:null,job:null,scope:new Ad(!0),render:null,proxy:null,exposed:null,exposeProxy:null,withProxy:null,provides:e?e.provides:Object.create(s.provides),ids:e?e.ids:["",0,0],accessCache:null,renderCache:[],components:null,directives:null,propsOptions:hf(i,s),emitsOptions:rf(i,s),emit:null,emitted:null,propsDefaults:dt,inheritAttrs:i.inheritAttrs,ctx:dt,data:dt,props:dt,attrs:dt,slots:dt,refs:dt,setupState:dt,setupContext:null,suspense:t,suspenseId:t?t.pendingId:0,asyncDep:null,asyncResolved:!1,isMounted:!1,isUnmounted:!1,isDeactivated:!1,bc:null,c:null,bm:null,m:null,bu:null,u:null,um:null,bum:null,da:null,a:null,rtg:null,rtc:null,ec:null,sp:null};return r.ctx={_:r},r.root=e?e.root:r,r.emit=Up.bind(null,r),n.ce&&n.ce(r),r}let Vt=null;const om=()=>Vt||on;let _a,Wo;{const n=wa(),e=(t,i)=>{let s;return(s=n[t])||(s=n[t]=[]),s.push(i),r=>{s.length>1?s.forEach(a=>a(r)):s[0](r)}};_a=e("__VUE_INSTANCE_SETTERS__",t=>Vt=t),Wo=e("__VUE_SSR_SETTERS__",t=>cr=t)}const _r=n=>{const e=Vt;return _a(n),n.scope.on(),()=>{n.scope.off(),_a(e)}},Gc=()=>{Vt&&Vt.scope.off(),_a(null)};function Sf(n){return n.vnode.shapeFlag&4}let cr=!1;function lm(n,e=!1,t=!1){e&&Wo(e);const{props:i,children:s}=n.vnode,r=Sf(n);Hp(n,i,r,e),Wp(n,s,t||e);const a=r?cm(n,e):void 0;return e&&Wo(!1),a}function cm(n,e){const t=n.type;n.accessCache=Object.create(null),n.proxy=new Proxy(n.ctx,Tp);const{setup:i}=t;if(i){ni();const s=n.setupContext=i.length>1?hm(n):null,r=_r(n),a=gr(i,n,0,[n.props,s]),o=vh(a);if(ii(),r(),(o||n.sp)&&!tr(n)&&$h(n),o){if(a.then(Gc,Gc),e)return a.then(l=>{Wc(n,l)}).catch(l=>{Ra(l,n,0)});n.asyncDep=a}else Wc(n,a)}else yf(n)}function Wc(n,e,t){je(e)?n.type.__ssrInlineRender?n.ssrRender=e:n.render=e:ot(e)&&(n.setupState=Vh(e)),yf(n)}function yf(n,e,t){const i=n.type;n.render||(n.render=i.render||Ln);{const s=_r(n);ni();try{Ap(n)}finally{ii(),s()}}}const um={get(n,e){return It(n,"get",""),n[e]}};function hm(n){const e=t=>{n.exposed=t||{}};return{attrs:new Proxy(n.attrs,um),slots:n.slots,emit:n.emit,expose:e}}function Ia(n){return n.exposed?n.exposeProxy||(n.exposeProxy=new Proxy(Vh(qd(n.exposed)),{get(e,t){if(t in e)return e[t];if(t in nr)return nr[t](n)},has(e,t){return t in e||t in nr}})):n.proxy}function fm(n){return je(n)&&"__vccOpts"in n}const bf=(n,e)=>Qd(n,e,cr);function dm(n,e,t){try{ma(-1);const i=arguments.length;return i===2?ot(e)&&!ze(e)?ga(e)?en(n,null,[e]):en(n,e):en(n,null,e):(i>3?t=Array.prototype.slice.call(arguments,2):i===3&&ga(t)&&(t=[t]),en(n,e,t))}finally{ma(1)}}const pm="3.5.32";let Xo;const Xc=typeof window<"u"&&window.trustedTypes;if(Xc)try{Xo=Xc.createPolicy("vue",{createHTML:n=>n})}catch{}const Ef=Xo?n=>Xo.createHTML(n):n=>n,mm="http://www.w3.org/2000/svg",gm="http://www.w3.org/1998/Math/MathML",Kn=typeof document<"u"?document:null,Yc=Kn&&Kn.createElement("template"),_m={insert:(n,e,t)=>{e.insertBefore(n,t||null)},remove:n=>{const e=n.parentNode;e&&e.removeChild(n)},createElement:(n,e,t,i)=>{const s=e==="svg"?Kn.createElementNS(mm,n):e==="mathml"?Kn.createElementNS(gm,n):t?Kn.createElement(n,{is:t}):Kn.createElement(n);return n==="select"&&i&&i.multiple!=null&&s.setAttribute("multiple",i.multiple),s},createText:n=>Kn.createTextNode(n),createComment:n=>Kn.createComment(n),setText:(n,e)=>{n.nodeValue=e},setElementText:(n,e)=>{n.textContent=e},parentNode:n=>n.parentNode,nextSibling:n=>n.nextSibling,querySelector:n=>Kn.querySelector(n),setScopeId(n,e){n.setAttribute(e,"")},insertStaticContent(n,e,t,i,s,r){const a=t?t.previousSibling:e.lastChild;if(s&&(s===r||s.nextSibling))for(;e.insertBefore(s.cloneNode(!0),t),!(s===r||!(s=s.nextSibling)););else{Yc.innerHTML=Ef(i==="svg"?`<svg>${n}</svg>`:i==="mathml"?`<math>${n}</math>`:n);const o=Yc.content;if(i==="svg"||i==="mathml"){const l=o.firstChild;for(;l.firstChild;)o.appendChild(l.firstChild);o.removeChild(l)}e.insertBefore(o,t)}return[a?a.nextSibling:e.firstChild,t?t.previousSibling:e.lastChild]}},xm=Symbol("_vtc");function vm(n,e,t){const i=n[xm];i&&(e=(e?[e,...i]:[...i]).join(" ")),e==null?n.removeAttribute("class"):t?n.setAttribute("class",e):n.className=e}const xa=Symbol("_vod"),Tf=Symbol("_vsh"),Mm={name:"show",beforeMount(n,{value:e},{transition:t}){n[xa]=n.style.display==="none"?"":n.style.display,t&&e?t.beforeEnter(n):Ns(n,e)},mounted(n,{value:e},{transition:t}){t&&e&&t.enter(n)},updated(n,{value:e,oldValue:t},{transition:i}){!e!=!t&&(i?e?(i.beforeEnter(n),Ns(n,!0),i.enter(n)):i.leave(n,()=>{Ns(n,!1)}):Ns(n,e))},beforeUnmount(n,{value:e}){Ns(n,e)}};function Ns(n,e){n.style.display=e?n[xa]:"none",n[Tf]=!e}const Sm=Symbol(""),ym=/(?:^|;)\s*display\s*:/;function bm(n,e,t){const i=n.style,s=St(t);let r=!1;if(t&&!s){if(e)if(St(e))for(const a of e.split(";")){const o=a.slice(0,a.indexOf(":")).trim();t[o]==null&&na(i,o,"")}else for(const a in e)t[a]==null&&na(i,a,"");for(const a in t)a==="display"&&(r=!0),na(i,a,t[a])}else if(s){if(e!==t){const a=i[Sm];a&&(t+=";"+a),i.cssText=t,r=ym.test(t)}}else e&&n.removeAttribute("style");xa in n&&(n[xa]=r?i.display:"",n[Tf]&&(i.display="none"))}const qc=/\s*!important$/;function na(n,e,t){if(ze(t))t.forEach(i=>na(n,e,i));else if(t==null&&(t=""),e.startsWith("--"))n.setProperty(e,t);else{const i=Em(n,e);qc.test(t)?n.setProperty(Xi(i),t.replace(qc,""),"important"):n[i]=t}}const jc=["Webkit","Moz","ms"],$a={};function Em(n,e){const t=$a[e];if(t)return t;let i=gn(e);if(i!=="filter"&&i in n)return $a[e]=i;i=yh(i);for(let s=0;s<jc.length;s++){const r=jc[s]+i;if(r in n)return $a[e]=r}return e}const Kc="http://www.w3.org/1999/xlink";function $c(n,e,t,i,s,r=Ed(e)){i&&e.startsWith("xlink:")?t==null?n.removeAttributeNS(Kc,e.slice(6,e.length)):n.setAttributeNS(Kc,e,t):t==null||r&&!Eh(t)?n.removeAttribute(e):n.setAttribute(e,r?"":Nn(t)?String(t):t)}function Zc(n,e,t,i,s){if(e==="innerHTML"||e==="textContent"){t!=null&&(n[e]=e==="innerHTML"?Ef(t):t);return}const r=n.tagName;if(e==="value"&&r!=="PROGRESS"&&!r.includes("-")){const o=r==="OPTION"?n.getAttribute("value")||"":n.value,l=t==null?n.type==="checkbox"?"on":"":String(t);(o!==l||!("_value"in n))&&(n.value=l),t==null&&n.removeAttribute(e),n._value=t;return}let a=!1;if(t===""||t==null){const o=typeof n[e];o==="boolean"?t=Eh(t):t==null&&o==="string"?(t="",a=!0):o==="number"&&(t=0,a=!0)}try{n[e]=t}catch{}a&&n.removeAttribute(s||e)}function ps(n,e,t,i){n.addEventListener(e,t,i)}function Tm(n,e,t,i){n.removeEventListener(e,t,i)}const Jc=Symbol("_vei");function Am(n,e,t,i,s=null){const r=n[Jc]||(n[Jc]={}),a=r[e];if(i&&a)a.value=i;else{const[o,l]=wm(e);if(i){const c=r[e]=Pm(i,s);ps(n,o,c,l)}else a&&(Tm(n,o,a,l),r[e]=void 0)}}const Qc=/(?:Once|Passive|Capture)$/;function wm(n){let e;if(Qc.test(n)){e={};let i;for(;i=n.match(Qc);)n=n.slice(0,n.length-i[0].length),e[i[0].toLowerCase()]=!0}return[n[2]===":"?n.slice(3):Xi(n.slice(2)),e]}let Za=0;const Cm=Promise.resolve(),Rm=()=>Za||(Cm.then(()=>Za=0),Za=Date.now());function Pm(n,e){const t=i=>{if(!i._vts)i._vts=Date.now();else if(i._vts<=t.attached)return;Fn(Dm(i,t.value),e,5,[i])};return t.value=n,t.attached=Rm(),t}function Dm(n,e){if(ze(e)){const t=n.stopImmediatePropagation;return n.stopImmediatePropagation=()=>{t.call(n),n._stopped=!0},e.map(i=>s=>!s._stopped&&i&&i(s))}else return e}const eu=n=>n.charCodeAt(0)===111&&n.charCodeAt(1)===110&&n.charCodeAt(2)>96&&n.charCodeAt(2)<123,Lm=(n,e,t,i,s,r)=>{const a=s==="svg";e==="class"?vm(n,i,a):e==="style"?bm(n,t,i):Ea(e)?Ta(e)||Am(n,e,t,i,r):(e[0]==="."?(e=e.slice(1),!0):e[0]==="^"?(e=e.slice(1),!1):Im(n,e,i,a))?(Zc(n,e,i),!n.tagName.includes("-")&&(e==="value"||e==="checked"||e==="selected")&&$c(n,e,i,a,r,e!=="value")):n._isVueCE&&(Um(n,e)||n._def.__asyncLoader&&(/[A-Z]/.test(e)||!St(i)))?Zc(n,gn(e),i,r,e):(e==="true-value"?n._trueValue=i:e==="false-value"&&(n._falseValue=i),$c(n,e,i,a))};function Im(n,e,t,i){if(i)return!!(e==="innerHTML"||e==="textContent"||e in n&&eu(e)&&je(t));if(e==="spellcheck"||e==="draggable"||e==="translate"||e==="autocorrect"||e==="sandbox"&&n.tagName==="IFRAME"||e==="form"||e==="list"&&n.tagName==="INPUT"||e==="type"&&n.tagName==="TEXTAREA")return!1;if(e==="width"||e==="height"){const s=n.tagName;if(s==="IMG"||s==="VIDEO"||s==="CANVAS"||s==="SOURCE")return!1}return eu(e)&&St(t)?!1:e in n}function Um(n,e){const t=n._def.props;if(!t)return!1;const i=gn(e);return Array.isArray(t)?t.some(s=>gn(s)===i):Object.keys(t).some(s=>gn(s)===i)}const tu=n=>{const e=n.props["onUpdate:modelValue"]||!1;return ze(e)?t=>Jr(e,t):e};function Nm(n){n.target.composing=!0}function nu(n){const e=n.target;e.composing&&(e.composing=!1,e.dispatchEvent(new Event("input")))}const Ja=Symbol("_assign");function iu(n,e,t){return e&&(n=n.trim()),t&&(n=Vl(n)),n}const su={created(n,{modifiers:{lazy:e,trim:t,number:i}},s){n[Ja]=tu(s);const r=i||s.props&&s.props.type==="number";ps(n,e?"change":"input",a=>{a.target.composing||n[Ja](iu(n.value,t,r))}),(t||r)&&ps(n,"change",()=>{n.value=iu(n.value,t,r)}),e||(ps(n,"compositionstart",Nm),ps(n,"compositionend",nu),ps(n,"change",nu))},mounted(n,{value:e}){n.value=e??""},beforeUpdate(n,{value:e,oldValue:t,modifiers:{lazy:i,trim:s,number:r}},a){if(n[Ja]=tu(a),n.composing)return;const o=(r||n.type==="number")&&!/^0\d/.test(n.value)?Vl(n.value):n.value,l=e??"";if(o===l)return;const c=n.getRootNode();(c instanceof Document||c instanceof ShadowRoot)&&c.activeElement===n&&n.type!=="range"&&(i&&e===t||s&&n.value.trim()===l)||(n.value=l)}},Fm=Ot({patchProp:Lm},_m);let ru;function Om(){return ru||(ru=Yp(Fm))}const Bm=((...n)=>{const e=Om().createApp(...n),{mount:t}=e;return e.mount=i=>{const s=Hm(i);if(!s)return;const r=e._component;!je(r)&&!r.render&&!r.template&&(r.template=s.innerHTML),s.nodeType===1&&(s.textContent="");const a=t(s,!1,zm(s));return s instanceof Element&&(s.removeAttribute("v-cloak"),s.setAttribute("data-v-app","")),a},e});function zm(n){if(n instanceof SVGElement)return"svg";if(typeof MathMLElement=="function"&&n instanceof MathMLElement)return"mathml"}function Hm(n){return St(n)?document.querySelector(n):n}const nc="183",Ms={ROTATE:0,DOLLY:1,PAN:2},ms={ROTATE:0,PAN:1,DOLLY_PAN:2,DOLLY_ROTATE:3},Vm=0,au=1,km=2,ia=1,Af=2,js=3,Ti=0,Yt=1,Pn=2,ei=0,Ss=1,ou=2,lu=3,cu=4,Gm=5,Bi=100,Wm=101,Xm=102,Ym=103,qm=104,jm=200,Km=201,$m=202,Zm=203,Yo=204,qo=205,Jm=206,Qm=207,eg=208,tg=209,ng=210,ig=211,sg=212,rg=213,ag=214,jo=0,Ko=1,$o=2,Ts=3,Zo=4,Jo=5,Qo=6,el=7,wf=0,og=1,lg=2,In=0,Cf=1,Rf=2,Pf=3,Df=4,Lf=5,If=6,Uf=7,Nf=300,Wi=301,As=302,Qa=303,eo=304,Ua=306,ur=1e3,Qn=1001,tl=1002,Ct=1003,cg=1004,Tr=1005,Ut=1006,to=1007,Hi=1008,Jt=1009,Ff=1010,Of=1011,hr=1012,ic=1013,On=1014,pn=1015,ri=1016,sc=1017,rc=1018,fr=1020,Bf=35902,zf=35899,Hf=1021,Vf=1022,mn=1023,ai=1026,Vi=1027,ac=1028,oc=1029,ws=1030,lc=1031,cc=1033,sa=33776,ra=33777,aa=33778,oa=33779,nl=35840,il=35841,sl=35842,rl=35843,al=36196,ol=37492,ll=37496,cl=37488,ul=37489,hl=37490,fl=37491,dl=37808,pl=37809,ml=37810,gl=37811,_l=37812,xl=37813,vl=37814,Ml=37815,Sl=37816,yl=37817,bl=37818,El=37819,Tl=37820,Al=37821,wl=36492,Cl=36494,Rl=36495,Pl=36283,Dl=36284,Ll=36285,Il=36286,ug=3200,kf=0,hg=1,Si="",Zt="srgb",Cs="srgb-linear",va="linear",at="srgb",Ji=7680,uu=519,fg=512,dg=513,pg=514,uc=515,mg=516,gg=517,hc=518,_g=519,hu=35044,fu="300 es",Dn=2e3,dr=2001;function xg(n){for(let e=n.length-1;e>=0;--e)if(n[e]>=65535)return!0;return!1}function Ma(n){return document.createElementNS("http://www.w3.org/1999/xhtml",n)}function vg(){const n=Ma("canvas");return n.style.display="block",n}const du={};function pu(...n){const e="THREE."+n.shift();console.log(e,...n)}function Gf(n){const e=n[0];if(typeof e=="string"&&e.startsWith("TSL:")){const t=n[1];t&&t.isStackTrace?n[0]+=" "+t.getLocation():n[1]='Stack trace not available. Enable "THREE.Node.captureStackTrace" to capture stack traces.'}return n}function Oe(...n){n=Gf(n);const e="THREE."+n.shift();{const t=n[0];t&&t.isStackTrace?console.warn(t.getError(e)):console.warn(e,...n)}}function Qe(...n){n=Gf(n);const e="THREE."+n.shift();{const t=n[0];t&&t.isStackTrace?console.error(t.getError(e)):console.error(e,...n)}}function Sa(...n){const e=n.join(" ");e in du||(du[e]=!0,Oe(...n))}function Mg(n,e,t){return new Promise(function(i,s){function r(){switch(n.clientWaitSync(e,n.SYNC_FLUSH_COMMANDS_BIT,0)){case n.WAIT_FAILED:s();break;case n.TIMEOUT_EXPIRED:setTimeout(r,t);break;default:i()}}setTimeout(r,t)})}const Sg={[jo]:Ko,[$o]:Qo,[Zo]:el,[Ts]:Jo,[Ko]:jo,[Qo]:$o,[el]:Zo,[Jo]:Ts};class Yi{addEventListener(e,t){this._listeners===void 0&&(this._listeners={});const i=this._listeners;i[e]===void 0&&(i[e]=[]),i[e].indexOf(t)===-1&&i[e].push(t)}hasEventListener(e,t){const i=this._listeners;return i===void 0?!1:i[e]!==void 0&&i[e].indexOf(t)!==-1}removeEventListener(e,t){const i=this._listeners;if(i===void 0)return;const s=i[e];if(s!==void 0){const r=s.indexOf(t);r!==-1&&s.splice(r,1)}}dispatchEvent(e){const t=this._listeners;if(t===void 0)return;const i=t[e.type];if(i!==void 0){e.target=this;const s=i.slice(0);for(let r=0,a=s.length;r<a;r++)s[r].call(this,e);e.target=null}}}const Pt=["00","01","02","03","04","05","06","07","08","09","0a","0b","0c","0d","0e","0f","10","11","12","13","14","15","16","17","18","19","1a","1b","1c","1d","1e","1f","20","21","22","23","24","25","26","27","28","29","2a","2b","2c","2d","2e","2f","30","31","32","33","34","35","36","37","38","39","3a","3b","3c","3d","3e","3f","40","41","42","43","44","45","46","47","48","49","4a","4b","4c","4d","4e","4f","50","51","52","53","54","55","56","57","58","59","5a","5b","5c","5d","5e","5f","60","61","62","63","64","65","66","67","68","69","6a","6b","6c","6d","6e","6f","70","71","72","73","74","75","76","77","78","79","7a","7b","7c","7d","7e","7f","80","81","82","83","84","85","86","87","88","89","8a","8b","8c","8d","8e","8f","90","91","92","93","94","95","96","97","98","99","9a","9b","9c","9d","9e","9f","a0","a1","a2","a3","a4","a5","a6","a7","a8","a9","aa","ab","ac","ad","ae","af","b0","b1","b2","b3","b4","b5","b6","b7","b8","b9","ba","bb","bc","bd","be","bf","c0","c1","c2","c3","c4","c5","c6","c7","c8","c9","ca","cb","cc","cd","ce","cf","d0","d1","d2","d3","d4","d5","d6","d7","d8","d9","da","db","dc","dd","de","df","e0","e1","e2","e3","e4","e5","e6","e7","e8","e9","ea","eb","ec","ed","ee","ef","f0","f1","f2","f3","f4","f5","f6","f7","f8","f9","fa","fb","fc","fd","fe","ff"],la=Math.PI/180,Ul=180/Math.PI;function xr(){const n=Math.random()*4294967295|0,e=Math.random()*4294967295|0,t=Math.random()*4294967295|0,i=Math.random()*4294967295|0;return(Pt[n&255]+Pt[n>>8&255]+Pt[n>>16&255]+Pt[n>>24&255]+"-"+Pt[e&255]+Pt[e>>8&255]+"-"+Pt[e>>16&15|64]+Pt[e>>24&255]+"-"+Pt[t&63|128]+Pt[t>>8&255]+"-"+Pt[t>>16&255]+Pt[t>>24&255]+Pt[i&255]+Pt[i>>8&255]+Pt[i>>16&255]+Pt[i>>24&255]).toLowerCase()}function Je(n,e,t){return Math.max(e,Math.min(t,n))}function yg(n,e){return(n%e+e)%e}function no(n,e,t){return(1-t)*n+t*e}function Fs(n,e){switch(e.constructor){case Float32Array:return n;case Uint32Array:return n/4294967295;case Uint16Array:return n/65535;case Uint8Array:return n/255;case Int32Array:return Math.max(n/2147483647,-1);case Int16Array:return Math.max(n/32767,-1);case Int8Array:return Math.max(n/127,-1);default:throw new Error("Invalid component type.")}}function kt(n,e){switch(e.constructor){case Float32Array:return n;case Uint32Array:return Math.round(n*4294967295);case Uint16Array:return Math.round(n*65535);case Uint8Array:return Math.round(n*255);case Int32Array:return Math.round(n*2147483647);case Int16Array:return Math.round(n*32767);case Int8Array:return Math.round(n*127);default:throw new Error("Invalid component type.")}}const bg={DEG2RAD:la};class ke{constructor(e=0,t=0){ke.prototype.isVector2=!0,this.x=e,this.y=t}get width(){return this.x}set width(e){this.x=e}get height(){return this.y}set height(e){this.y=e}set(e,t){return this.x=e,this.y=t,this}setScalar(e){return this.x=e,this.y=e,this}setX(e){return this.x=e,this}setY(e){return this.y=e,this}setComponent(e,t){switch(e){case 0:this.x=t;break;case 1:this.y=t;break;default:throw new Error("index is out of range: "+e)}return this}getComponent(e){switch(e){case 0:return this.x;case 1:return this.y;default:throw new Error("index is out of range: "+e)}}clone(){return new this.constructor(this.x,this.y)}copy(e){return this.x=e.x,this.y=e.y,this}add(e){return this.x+=e.x,this.y+=e.y,this}addScalar(e){return this.x+=e,this.y+=e,this}addVectors(e,t){return this.x=e.x+t.x,this.y=e.y+t.y,this}addScaledVector(e,t){return this.x+=e.x*t,this.y+=e.y*t,this}sub(e){return this.x-=e.x,this.y-=e.y,this}subScalar(e){return this.x-=e,this.y-=e,this}subVectors(e,t){return this.x=e.x-t.x,this.y=e.y-t.y,this}multiply(e){return this.x*=e.x,this.y*=e.y,this}multiplyScalar(e){return this.x*=e,this.y*=e,this}divide(e){return this.x/=e.x,this.y/=e.y,this}divideScalar(e){return this.multiplyScalar(1/e)}applyMatrix3(e){const t=this.x,i=this.y,s=e.elements;return this.x=s[0]*t+s[3]*i+s[6],this.y=s[1]*t+s[4]*i+s[7],this}min(e){return this.x=Math.min(this.x,e.x),this.y=Math.min(this.y,e.y),this}max(e){return this.x=Math.max(this.x,e.x),this.y=Math.max(this.y,e.y),this}clamp(e,t){return this.x=Je(this.x,e.x,t.x),this.y=Je(this.y,e.y,t.y),this}clampScalar(e,t){return this.x=Je(this.x,e,t),this.y=Je(this.y,e,t),this}clampLength(e,t){const i=this.length();return this.divideScalar(i||1).multiplyScalar(Je(i,e,t))}floor(){return this.x=Math.floor(this.x),this.y=Math.floor(this.y),this}ceil(){return this.x=Math.ceil(this.x),this.y=Math.ceil(this.y),this}round(){return this.x=Math.round(this.x),this.y=Math.round(this.y),this}roundToZero(){return this.x=Math.trunc(this.x),this.y=Math.trunc(this.y),this}negate(){return this.x=-this.x,this.y=-this.y,this}dot(e){return this.x*e.x+this.y*e.y}cross(e){return this.x*e.y-this.y*e.x}lengthSq(){return this.x*this.x+this.y*this.y}length(){return Math.sqrt(this.x*this.x+this.y*this.y)}manhattanLength(){return Math.abs(this.x)+Math.abs(this.y)}normalize(){return this.divideScalar(this.length()||1)}angle(){return Math.atan2(-this.y,-this.x)+Math.PI}angleTo(e){const t=Math.sqrt(this.lengthSq()*e.lengthSq());if(t===0)return Math.PI/2;const i=this.dot(e)/t;return Math.acos(Je(i,-1,1))}distanceTo(e){return Math.sqrt(this.distanceToSquared(e))}distanceToSquared(e){const t=this.x-e.x,i=this.y-e.y;return t*t+i*i}manhattanDistanceTo(e){return Math.abs(this.x-e.x)+Math.abs(this.y-e.y)}setLength(e){return this.normalize().multiplyScalar(e)}lerp(e,t){return this.x+=(e.x-this.x)*t,this.y+=(e.y-this.y)*t,this}lerpVectors(e,t,i){return this.x=e.x+(t.x-e.x)*i,this.y=e.y+(t.y-e.y)*i,this}equals(e){return e.x===this.x&&e.y===this.y}fromArray(e,t=0){return this.x=e[t],this.y=e[t+1],this}toArray(e=[],t=0){return e[t]=this.x,e[t+1]=this.y,e}fromBufferAttribute(e,t){return this.x=e.getX(t),this.y=e.getY(t),this}rotateAround(e,t){const i=Math.cos(t),s=Math.sin(t),r=this.x-e.x,a=this.y-e.y;return this.x=r*i-a*s+e.x,this.y=r*s+a*i+e.y,this}random(){return this.x=Math.random(),this.y=Math.random(),this}*[Symbol.iterator](){yield this.x,yield this.y}}class oi{constructor(e=0,t=0,i=0,s=1){this.isQuaternion=!0,this._x=e,this._y=t,this._z=i,this._w=s}static slerpFlat(e,t,i,s,r,a,o){let l=i[s+0],c=i[s+1],u=i[s+2],f=i[s+3],h=r[a+0],p=r[a+1],_=r[a+2],M=r[a+3];if(f!==M||l!==h||c!==p||u!==_){let g=l*h+c*p+u*_+f*M;g<0&&(h=-h,p=-p,_=-_,M=-M,g=-g);let d=1-o;if(g<.9995){const y=Math.acos(g),A=Math.sin(y);d=Math.sin(d*y)/A,o=Math.sin(o*y)/A,l=l*d+h*o,c=c*d+p*o,u=u*d+_*o,f=f*d+M*o}else{l=l*d+h*o,c=c*d+p*o,u=u*d+_*o,f=f*d+M*o;const y=1/Math.sqrt(l*l+c*c+u*u+f*f);l*=y,c*=y,u*=y,f*=y}}e[t]=l,e[t+1]=c,e[t+2]=u,e[t+3]=f}static multiplyQuaternionsFlat(e,t,i,s,r,a){const o=i[s],l=i[s+1],c=i[s+2],u=i[s+3],f=r[a],h=r[a+1],p=r[a+2],_=r[a+3];return e[t]=o*_+u*f+l*p-c*h,e[t+1]=l*_+u*h+c*f-o*p,e[t+2]=c*_+u*p+o*h-l*f,e[t+3]=u*_-o*f-l*h-c*p,e}get x(){return this._x}set x(e){this._x=e,this._onChangeCallback()}get y(){return this._y}set y(e){this._y=e,this._onChangeCallback()}get z(){return this._z}set z(e){this._z=e,this._onChangeCallback()}get w(){return this._w}set w(e){this._w=e,this._onChangeCallback()}set(e,t,i,s){return this._x=e,this._y=t,this._z=i,this._w=s,this._onChangeCallback(),this}clone(){return new this.constructor(this._x,this._y,this._z,this._w)}copy(e){return this._x=e.x,this._y=e.y,this._z=e.z,this._w=e.w,this._onChangeCallback(),this}setFromEuler(e,t=!0){const i=e._x,s=e._y,r=e._z,a=e._order,o=Math.cos,l=Math.sin,c=o(i/2),u=o(s/2),f=o(r/2),h=l(i/2),p=l(s/2),_=l(r/2);switch(a){case"XYZ":this._x=h*u*f+c*p*_,this._y=c*p*f-h*u*_,this._z=c*u*_+h*p*f,this._w=c*u*f-h*p*_;break;case"YXZ":this._x=h*u*f+c*p*_,this._y=c*p*f-h*u*_,this._z=c*u*_-h*p*f,this._w=c*u*f+h*p*_;break;case"ZXY":this._x=h*u*f-c*p*_,this._y=c*p*f+h*u*_,this._z=c*u*_+h*p*f,this._w=c*u*f-h*p*_;break;case"ZYX":this._x=h*u*f-c*p*_,this._y=c*p*f+h*u*_,this._z=c*u*_-h*p*f,this._w=c*u*f+h*p*_;break;case"YZX":this._x=h*u*f+c*p*_,this._y=c*p*f+h*u*_,this._z=c*u*_-h*p*f,this._w=c*u*f-h*p*_;break;case"XZY":this._x=h*u*f-c*p*_,this._y=c*p*f-h*u*_,this._z=c*u*_+h*p*f,this._w=c*u*f+h*p*_;break;default:Oe("Quaternion: .setFromEuler() encountered an unknown order: "+a)}return t===!0&&this._onChangeCallback(),this}setFromAxisAngle(e,t){const i=t/2,s=Math.sin(i);return this._x=e.x*s,this._y=e.y*s,this._z=e.z*s,this._w=Math.cos(i),this._onChangeCallback(),this}setFromRotationMatrix(e){const t=e.elements,i=t[0],s=t[4],r=t[8],a=t[1],o=t[5],l=t[9],c=t[2],u=t[6],f=t[10],h=i+o+f;if(h>0){const p=.5/Math.sqrt(h+1);this._w=.25/p,this._x=(u-l)*p,this._y=(r-c)*p,this._z=(a-s)*p}else if(i>o&&i>f){const p=2*Math.sqrt(1+i-o-f);this._w=(u-l)/p,this._x=.25*p,this._y=(s+a)/p,this._z=(r+c)/p}else if(o>f){const p=2*Math.sqrt(1+o-i-f);this._w=(r-c)/p,this._x=(s+a)/p,this._y=.25*p,this._z=(l+u)/p}else{const p=2*Math.sqrt(1+f-i-o);this._w=(a-s)/p,this._x=(r+c)/p,this._y=(l+u)/p,this._z=.25*p}return this._onChangeCallback(),this}setFromUnitVectors(e,t){let i=e.dot(t)+1;return i<1e-8?(i=0,Math.abs(e.x)>Math.abs(e.z)?(this._x=-e.y,this._y=e.x,this._z=0,this._w=i):(this._x=0,this._y=-e.z,this._z=e.y,this._w=i)):(this._x=e.y*t.z-e.z*t.y,this._y=e.z*t.x-e.x*t.z,this._z=e.x*t.y-e.y*t.x,this._w=i),this.normalize()}angleTo(e){return 2*Math.acos(Math.abs(Je(this.dot(e),-1,1)))}rotateTowards(e,t){const i=this.angleTo(e);if(i===0)return this;const s=Math.min(1,t/i);return this.slerp(e,s),this}identity(){return this.set(0,0,0,1)}invert(){return this.conjugate()}conjugate(){return this._x*=-1,this._y*=-1,this._z*=-1,this._onChangeCallback(),this}dot(e){return this._x*e._x+this._y*e._y+this._z*e._z+this._w*e._w}lengthSq(){return this._x*this._x+this._y*this._y+this._z*this._z+this._w*this._w}length(){return Math.sqrt(this._x*this._x+this._y*this._y+this._z*this._z+this._w*this._w)}normalize(){let e=this.length();return e===0?(this._x=0,this._y=0,this._z=0,this._w=1):(e=1/e,this._x=this._x*e,this._y=this._y*e,this._z=this._z*e,this._w=this._w*e),this._onChangeCallback(),this}multiply(e){return this.multiplyQuaternions(this,e)}premultiply(e){return this.multiplyQuaternions(e,this)}multiplyQuaternions(e,t){const i=e._x,s=e._y,r=e._z,a=e._w,o=t._x,l=t._y,c=t._z,u=t._w;return this._x=i*u+a*o+s*c-r*l,this._y=s*u+a*l+r*o-i*c,this._z=r*u+a*c+i*l-s*o,this._w=a*u-i*o-s*l-r*c,this._onChangeCallback(),this}slerp(e,t){let i=e._x,s=e._y,r=e._z,a=e._w,o=this.dot(e);o<0&&(i=-i,s=-s,r=-r,a=-a,o=-o);let l=1-t;if(o<.9995){const c=Math.acos(o),u=Math.sin(c);l=Math.sin(l*c)/u,t=Math.sin(t*c)/u,this._x=this._x*l+i*t,this._y=this._y*l+s*t,this._z=this._z*l+r*t,this._w=this._w*l+a*t,this._onChangeCallback()}else this._x=this._x*l+i*t,this._y=this._y*l+s*t,this._z=this._z*l+r*t,this._w=this._w*l+a*t,this.normalize();return this}slerpQuaternions(e,t,i){return this.copy(e).slerp(t,i)}random(){const e=2*Math.PI*Math.random(),t=2*Math.PI*Math.random(),i=Math.random(),s=Math.sqrt(1-i),r=Math.sqrt(i);return this.set(s*Math.sin(e),s*Math.cos(e),r*Math.sin(t),r*Math.cos(t))}equals(e){return e._x===this._x&&e._y===this._y&&e._z===this._z&&e._w===this._w}fromArray(e,t=0){return this._x=e[t],this._y=e[t+1],this._z=e[t+2],this._w=e[t+3],this._onChangeCallback(),this}toArray(e=[],t=0){return e[t]=this._x,e[t+1]=this._y,e[t+2]=this._z,e[t+3]=this._w,e}fromBufferAttribute(e,t){return this._x=e.getX(t),this._y=e.getY(t),this._z=e.getZ(t),this._w=e.getW(t),this._onChangeCallback(),this}toJSON(){return this.toArray()}_onChange(e){return this._onChangeCallback=e,this}_onChangeCallback(){}*[Symbol.iterator](){yield this._x,yield this._y,yield this._z,yield this._w}}class O{constructor(e=0,t=0,i=0){O.prototype.isVector3=!0,this.x=e,this.y=t,this.z=i}set(e,t,i){return i===void 0&&(i=this.z),this.x=e,this.y=t,this.z=i,this}setScalar(e){return this.x=e,this.y=e,this.z=e,this}setX(e){return this.x=e,this}setY(e){return this.y=e,this}setZ(e){return this.z=e,this}setComponent(e,t){switch(e){case 0:this.x=t;break;case 1:this.y=t;break;case 2:this.z=t;break;default:throw new Error("index is out of range: "+e)}return this}getComponent(e){switch(e){case 0:return this.x;case 1:return this.y;case 2:return this.z;default:throw new Error("index is out of range: "+e)}}clone(){return new this.constructor(this.x,this.y,this.z)}copy(e){return this.x=e.x,this.y=e.y,this.z=e.z,this}add(e){return this.x+=e.x,this.y+=e.y,this.z+=e.z,this}addScalar(e){return this.x+=e,this.y+=e,this.z+=e,this}addVectors(e,t){return this.x=e.x+t.x,this.y=e.y+t.y,this.z=e.z+t.z,this}addScaledVector(e,t){return this.x+=e.x*t,this.y+=e.y*t,this.z+=e.z*t,this}sub(e){return this.x-=e.x,this.y-=e.y,this.z-=e.z,this}subScalar(e){return this.x-=e,this.y-=e,this.z-=e,this}subVectors(e,t){return this.x=e.x-t.x,this.y=e.y-t.y,this.z=e.z-t.z,this}multiply(e){return this.x*=e.x,this.y*=e.y,this.z*=e.z,this}multiplyScalar(e){return this.x*=e,this.y*=e,this.z*=e,this}multiplyVectors(e,t){return this.x=e.x*t.x,this.y=e.y*t.y,this.z=e.z*t.z,this}applyEuler(e){return this.applyQuaternion(mu.setFromEuler(e))}applyAxisAngle(e,t){return this.applyQuaternion(mu.setFromAxisAngle(e,t))}applyMatrix3(e){const t=this.x,i=this.y,s=this.z,r=e.elements;return this.x=r[0]*t+r[3]*i+r[6]*s,this.y=r[1]*t+r[4]*i+r[7]*s,this.z=r[2]*t+r[5]*i+r[8]*s,this}applyNormalMatrix(e){return this.applyMatrix3(e).normalize()}applyMatrix4(e){const t=this.x,i=this.y,s=this.z,r=e.elements,a=1/(r[3]*t+r[7]*i+r[11]*s+r[15]);return this.x=(r[0]*t+r[4]*i+r[8]*s+r[12])*a,this.y=(r[1]*t+r[5]*i+r[9]*s+r[13])*a,this.z=(r[2]*t+r[6]*i+r[10]*s+r[14])*a,this}applyQuaternion(e){const t=this.x,i=this.y,s=this.z,r=e.x,a=e.y,o=e.z,l=e.w,c=2*(a*s-o*i),u=2*(o*t-r*s),f=2*(r*i-a*t);return this.x=t+l*c+a*f-o*u,this.y=i+l*u+o*c-r*f,this.z=s+l*f+r*u-a*c,this}project(e){return this.applyMatrix4(e.matrixWorldInverse).applyMatrix4(e.projectionMatrix)}unproject(e){return this.applyMatrix4(e.projectionMatrixInverse).applyMatrix4(e.matrixWorld)}transformDirection(e){const t=this.x,i=this.y,s=this.z,r=e.elements;return this.x=r[0]*t+r[4]*i+r[8]*s,this.y=r[1]*t+r[5]*i+r[9]*s,this.z=r[2]*t+r[6]*i+r[10]*s,this.normalize()}divide(e){return this.x/=e.x,this.y/=e.y,this.z/=e.z,this}divideScalar(e){return this.multiplyScalar(1/e)}min(e){return this.x=Math.min(this.x,e.x),this.y=Math.min(this.y,e.y),this.z=Math.min(this.z,e.z),this}max(e){return this.x=Math.max(this.x,e.x),this.y=Math.max(this.y,e.y),this.z=Math.max(this.z,e.z),this}clamp(e,t){return this.x=Je(this.x,e.x,t.x),this.y=Je(this.y,e.y,t.y),this.z=Je(this.z,e.z,t.z),this}clampScalar(e,t){return this.x=Je(this.x,e,t),this.y=Je(this.y,e,t),this.z=Je(this.z,e,t),this}clampLength(e,t){const i=this.length();return this.divideScalar(i||1).multiplyScalar(Je(i,e,t))}floor(){return this.x=Math.floor(this.x),this.y=Math.floor(this.y),this.z=Math.floor(this.z),this}ceil(){return this.x=Math.ceil(this.x),this.y=Math.ceil(this.y),this.z=Math.ceil(this.z),this}round(){return this.x=Math.round(this.x),this.y=Math.round(this.y),this.z=Math.round(this.z),this}roundToZero(){return this.x=Math.trunc(this.x),this.y=Math.trunc(this.y),this.z=Math.trunc(this.z),this}negate(){return this.x=-this.x,this.y=-this.y,this.z=-this.z,this}dot(e){return this.x*e.x+this.y*e.y+this.z*e.z}lengthSq(){return this.x*this.x+this.y*this.y+this.z*this.z}length(){return Math.sqrt(this.x*this.x+this.y*this.y+this.z*this.z)}manhattanLength(){return Math.abs(this.x)+Math.abs(this.y)+Math.abs(this.z)}normalize(){return this.divideScalar(this.length()||1)}setLength(e){return this.normalize().multiplyScalar(e)}lerp(e,t){return this.x+=(e.x-this.x)*t,this.y+=(e.y-this.y)*t,this.z+=(e.z-this.z)*t,this}lerpVectors(e,t,i){return this.x=e.x+(t.x-e.x)*i,this.y=e.y+(t.y-e.y)*i,this.z=e.z+(t.z-e.z)*i,this}cross(e){return this.crossVectors(this,e)}crossVectors(e,t){const i=e.x,s=e.y,r=e.z,a=t.x,o=t.y,l=t.z;return this.x=s*l-r*o,this.y=r*a-i*l,this.z=i*o-s*a,this}projectOnVector(e){const t=e.lengthSq();if(t===0)return this.set(0,0,0);const i=e.dot(this)/t;return this.copy(e).multiplyScalar(i)}projectOnPlane(e){return io.copy(this).projectOnVector(e),this.sub(io)}reflect(e){return this.sub(io.copy(e).multiplyScalar(2*this.dot(e)))}angleTo(e){const t=Math.sqrt(this.lengthSq()*e.lengthSq());if(t===0)return Math.PI/2;const i=this.dot(e)/t;return Math.acos(Je(i,-1,1))}distanceTo(e){return Math.sqrt(this.distanceToSquared(e))}distanceToSquared(e){const t=this.x-e.x,i=this.y-e.y,s=this.z-e.z;return t*t+i*i+s*s}manhattanDistanceTo(e){return Math.abs(this.x-e.x)+Math.abs(this.y-e.y)+Math.abs(this.z-e.z)}setFromSpherical(e){return this.setFromSphericalCoords(e.radius,e.phi,e.theta)}setFromSphericalCoords(e,t,i){const s=Math.sin(t)*e;return this.x=s*Math.sin(i),this.y=Math.cos(t)*e,this.z=s*Math.cos(i),this}setFromCylindrical(e){return this.setFromCylindricalCoords(e.radius,e.theta,e.y)}setFromCylindricalCoords(e,t,i){return this.x=e*Math.sin(t),this.y=i,this.z=e*Math.cos(t),this}setFromMatrixPosition(e){const t=e.elements;return this.x=t[12],this.y=t[13],this.z=t[14],this}setFromMatrixScale(e){const t=this.setFromMatrixColumn(e,0).length(),i=this.setFromMatrixColumn(e,1).length(),s=this.setFromMatrixColumn(e,2).length();return this.x=t,this.y=i,this.z=s,this}setFromMatrixColumn(e,t){return this.fromArray(e.elements,t*4)}setFromMatrix3Column(e,t){return this.fromArray(e.elements,t*3)}setFromEuler(e){return this.x=e._x,this.y=e._y,this.z=e._z,this}setFromColor(e){return this.x=e.r,this.y=e.g,this.z=e.b,this}equals(e){return e.x===this.x&&e.y===this.y&&e.z===this.z}fromArray(e,t=0){return this.x=e[t],this.y=e[t+1],this.z=e[t+2],this}toArray(e=[],t=0){return e[t]=this.x,e[t+1]=this.y,e[t+2]=this.z,e}fromBufferAttribute(e,t){return this.x=e.getX(t),this.y=e.getY(t),this.z=e.getZ(t),this}random(){return this.x=Math.random(),this.y=Math.random(),this.z=Math.random(),this}randomDirection(){const e=Math.random()*Math.PI*2,t=Math.random()*2-1,i=Math.sqrt(1-t*t);return this.x=i*Math.cos(e),this.y=t,this.z=i*Math.sin(e),this}*[Symbol.iterator](){yield this.x,yield this.y,yield this.z}}const io=new O,mu=new oi;class qe{constructor(e,t,i,s,r,a,o,l,c){qe.prototype.isMatrix3=!0,this.elements=[1,0,0,0,1,0,0,0,1],e!==void 0&&this.set(e,t,i,s,r,a,o,l,c)}set(e,t,i,s,r,a,o,l,c){const u=this.elements;return u[0]=e,u[1]=s,u[2]=o,u[3]=t,u[4]=r,u[5]=l,u[6]=i,u[7]=a,u[8]=c,this}identity(){return this.set(1,0,0,0,1,0,0,0,1),this}copy(e){const t=this.elements,i=e.elements;return t[0]=i[0],t[1]=i[1],t[2]=i[2],t[3]=i[3],t[4]=i[4],t[5]=i[5],t[6]=i[6],t[7]=i[7],t[8]=i[8],this}extractBasis(e,t,i){return e.setFromMatrix3Column(this,0),t.setFromMatrix3Column(this,1),i.setFromMatrix3Column(this,2),this}setFromMatrix4(e){const t=e.elements;return this.set(t[0],t[4],t[8],t[1],t[5],t[9],t[2],t[6],t[10]),this}multiply(e){return this.multiplyMatrices(this,e)}premultiply(e){return this.multiplyMatrices(e,this)}multiplyMatrices(e,t){const i=e.elements,s=t.elements,r=this.elements,a=i[0],o=i[3],l=i[6],c=i[1],u=i[4],f=i[7],h=i[2],p=i[5],_=i[8],M=s[0],g=s[3],d=s[6],y=s[1],A=s[4],b=s[7],P=s[2],L=s[5],I=s[8];return r[0]=a*M+o*y+l*P,r[3]=a*g+o*A+l*L,r[6]=a*d+o*b+l*I,r[1]=c*M+u*y+f*P,r[4]=c*g+u*A+f*L,r[7]=c*d+u*b+f*I,r[2]=h*M+p*y+_*P,r[5]=h*g+p*A+_*L,r[8]=h*d+p*b+_*I,this}multiplyScalar(e){const t=this.elements;return t[0]*=e,t[3]*=e,t[6]*=e,t[1]*=e,t[4]*=e,t[7]*=e,t[2]*=e,t[5]*=e,t[8]*=e,this}determinant(){const e=this.elements,t=e[0],i=e[1],s=e[2],r=e[3],a=e[4],o=e[5],l=e[6],c=e[7],u=e[8];return t*a*u-t*o*c-i*r*u+i*o*l+s*r*c-s*a*l}invert(){const e=this.elements,t=e[0],i=e[1],s=e[2],r=e[3],a=e[4],o=e[5],l=e[6],c=e[7],u=e[8],f=u*a-o*c,h=o*l-u*r,p=c*r-a*l,_=t*f+i*h+s*p;if(_===0)return this.set(0,0,0,0,0,0,0,0,0);const M=1/_;return e[0]=f*M,e[1]=(s*c-u*i)*M,e[2]=(o*i-s*a)*M,e[3]=h*M,e[4]=(u*t-s*l)*M,e[5]=(s*r-o*t)*M,e[6]=p*M,e[7]=(i*l-c*t)*M,e[8]=(a*t-i*r)*M,this}transpose(){let e;const t=this.elements;return e=t[1],t[1]=t[3],t[3]=e,e=t[2],t[2]=t[6],t[6]=e,e=t[5],t[5]=t[7],t[7]=e,this}getNormalMatrix(e){return this.setFromMatrix4(e).invert().transpose()}transposeIntoArray(e){const t=this.elements;return e[0]=t[0],e[1]=t[3],e[2]=t[6],e[3]=t[1],e[4]=t[4],e[5]=t[7],e[6]=t[2],e[7]=t[5],e[8]=t[8],this}setUvTransform(e,t,i,s,r,a,o){const l=Math.cos(r),c=Math.sin(r);return this.set(i*l,i*c,-i*(l*a+c*o)+a+e,-s*c,s*l,-s*(-c*a+l*o)+o+t,0,0,1),this}scale(e,t){return this.premultiply(so.makeScale(e,t)),this}rotate(e){return this.premultiply(so.makeRotation(-e)),this}translate(e,t){return this.premultiply(so.makeTranslation(e,t)),this}makeTranslation(e,t){return e.isVector2?this.set(1,0,e.x,0,1,e.y,0,0,1):this.set(1,0,e,0,1,t,0,0,1),this}makeRotation(e){const t=Math.cos(e),i=Math.sin(e);return this.set(t,-i,0,i,t,0,0,0,1),this}makeScale(e,t){return this.set(e,0,0,0,t,0,0,0,1),this}equals(e){const t=this.elements,i=e.elements;for(let s=0;s<9;s++)if(t[s]!==i[s])return!1;return!0}fromArray(e,t=0){for(let i=0;i<9;i++)this.elements[i]=e[i+t];return this}toArray(e=[],t=0){const i=this.elements;return e[t]=i[0],e[t+1]=i[1],e[t+2]=i[2],e[t+3]=i[3],e[t+4]=i[4],e[t+5]=i[5],e[t+6]=i[6],e[t+7]=i[7],e[t+8]=i[8],e}clone(){return new this.constructor().fromArray(this.elements)}}const so=new qe,gu=new qe().set(.4123908,.3575843,.1804808,.212639,.7151687,.0721923,.0193308,.1191948,.9505322),_u=new qe().set(3.2409699,-1.5373832,-.4986108,-.9692436,1.8759675,.0415551,.0556301,-.203977,1.0569715);function Eg(){const n={enabled:!0,workingColorSpace:Cs,spaces:{},convert:function(s,r,a){return this.enabled===!1||r===a||!r||!a||(this.spaces[r].transfer===at&&(s.r=ti(s.r),s.g=ti(s.g),s.b=ti(s.b)),this.spaces[r].primaries!==this.spaces[a].primaries&&(s.applyMatrix3(this.spaces[r].toXYZ),s.applyMatrix3(this.spaces[a].fromXYZ)),this.spaces[a].transfer===at&&(s.r=ys(s.r),s.g=ys(s.g),s.b=ys(s.b))),s},workingToColorSpace:function(s,r){return this.convert(s,this.workingColorSpace,r)},colorSpaceToWorking:function(s,r){return this.convert(s,r,this.workingColorSpace)},getPrimaries:function(s){return this.spaces[s].primaries},getTransfer:function(s){return s===Si?va:this.spaces[s].transfer},getToneMappingMode:function(s){return this.spaces[s].outputColorSpaceConfig.toneMappingMode||"standard"},getLuminanceCoefficients:function(s,r=this.workingColorSpace){return s.fromArray(this.spaces[r].luminanceCoefficients)},define:function(s){Object.assign(this.spaces,s)},_getMatrix:function(s,r,a){return s.copy(this.spaces[r].toXYZ).multiply(this.spaces[a].fromXYZ)},_getDrawingBufferColorSpace:function(s){return this.spaces[s].outputColorSpaceConfig.drawingBufferColorSpace},_getUnpackColorSpace:function(s=this.workingColorSpace){return this.spaces[s].workingColorSpaceConfig.unpackColorSpace},fromWorkingColorSpace:function(s,r){return Sa("ColorManagement: .fromWorkingColorSpace() has been renamed to .workingToColorSpace()."),n.workingToColorSpace(s,r)},toWorkingColorSpace:function(s,r){return Sa("ColorManagement: .toWorkingColorSpace() has been renamed to .colorSpaceToWorking()."),n.colorSpaceToWorking(s,r)}},e=[.64,.33,.3,.6,.15,.06],t=[.2126,.7152,.0722],i=[.3127,.329];return n.define({[Cs]:{primaries:e,whitePoint:i,transfer:va,toXYZ:gu,fromXYZ:_u,luminanceCoefficients:t,workingColorSpaceConfig:{unpackColorSpace:Zt},outputColorSpaceConfig:{drawingBufferColorSpace:Zt}},[Zt]:{primaries:e,whitePoint:i,transfer:at,toXYZ:gu,fromXYZ:_u,luminanceCoefficients:t,outputColorSpaceConfig:{drawingBufferColorSpace:Zt}}}),n}const et=Eg();function ti(n){return n<.04045?n*.0773993808:Math.pow(n*.9478672986+.0521327014,2.4)}function ys(n){return n<.0031308?n*12.92:1.055*Math.pow(n,.41666)-.055}let Qi;class Tg{static getDataURL(e,t="image/png"){if(/^data:/i.test(e.src)||typeof HTMLCanvasElement>"u")return e.src;let i;if(e instanceof HTMLCanvasElement)i=e;else{Qi===void 0&&(Qi=Ma("canvas")),Qi.width=e.width,Qi.height=e.height;const s=Qi.getContext("2d");e instanceof ImageData?s.putImageData(e,0,0):s.drawImage(e,0,0,e.width,e.height),i=Qi}return i.toDataURL(t)}static sRGBToLinear(e){if(typeof HTMLImageElement<"u"&&e instanceof HTMLImageElement||typeof HTMLCanvasElement<"u"&&e instanceof HTMLCanvasElement||typeof ImageBitmap<"u"&&e instanceof ImageBitmap){const t=Ma("canvas");t.width=e.width,t.height=e.height;const i=t.getContext("2d");i.drawImage(e,0,0,e.width,e.height);const s=i.getImageData(0,0,e.width,e.height),r=s.data;for(let a=0;a<r.length;a++)r[a]=ti(r[a]/255)*255;return i.putImageData(s,0,0),t}else if(e.data){const t=e.data.slice(0);for(let i=0;i<t.length;i++)t instanceof Uint8Array||t instanceof Uint8ClampedArray?t[i]=Math.floor(ti(t[i]/255)*255):t[i]=ti(t[i]);return{data:t,width:e.width,height:e.height}}else return Oe("ImageUtils.sRGBToLinear(): Unsupported image type. No color space conversion applied."),e}}let Ag=0;class fc{constructor(e=null){this.isSource=!0,Object.defineProperty(this,"id",{value:Ag++}),this.uuid=xr(),this.data=e,this.dataReady=!0,this.version=0}getSize(e){const t=this.data;return typeof HTMLVideoElement<"u"&&t instanceof HTMLVideoElement?e.set(t.videoWidth,t.videoHeight,0):typeof VideoFrame<"u"&&t instanceof VideoFrame?e.set(t.displayHeight,t.displayWidth,0):t!==null?e.set(t.width,t.height,t.depth||0):e.set(0,0,0),e}set needsUpdate(e){e===!0&&this.version++}toJSON(e){const t=e===void 0||typeof e=="string";if(!t&&e.images[this.uuid]!==void 0)return e.images[this.uuid];const i={uuid:this.uuid,url:""},s=this.data;if(s!==null){let r;if(Array.isArray(s)){r=[];for(let a=0,o=s.length;a<o;a++)s[a].isDataTexture?r.push(ro(s[a].image)):r.push(ro(s[a]))}else r=ro(s);i.url=r}return t||(e.images[this.uuid]=i),i}}function ro(n){return typeof HTMLImageElement<"u"&&n instanceof HTMLImageElement||typeof HTMLCanvasElement<"u"&&n instanceof HTMLCanvasElement||typeof ImageBitmap<"u"&&n instanceof ImageBitmap?Tg.getDataURL(n):n.data?{data:Array.from(n.data),width:n.width,height:n.height,type:n.data.constructor.name}:(Oe("Texture: Unable to serialize Texture."),{})}let wg=0;const ao=new O;class Ft extends Yi{constructor(e=Ft.DEFAULT_IMAGE,t=Ft.DEFAULT_MAPPING,i=Qn,s=Qn,r=Ut,a=Hi,o=mn,l=Jt,c=Ft.DEFAULT_ANISOTROPY,u=Si){super(),this.isTexture=!0,Object.defineProperty(this,"id",{value:wg++}),this.uuid=xr(),this.name="",this.source=new fc(e),this.mipmaps=[],this.mapping=t,this.channel=0,this.wrapS=i,this.wrapT=s,this.magFilter=r,this.minFilter=a,this.anisotropy=c,this.format=o,this.internalFormat=null,this.type=l,this.offset=new ke(0,0),this.repeat=new ke(1,1),this.center=new ke(0,0),this.rotation=0,this.matrixAutoUpdate=!0,this.matrix=new qe,this.generateMipmaps=!0,this.premultiplyAlpha=!1,this.flipY=!0,this.unpackAlignment=4,this.colorSpace=u,this.userData={},this.updateRanges=[],this.version=0,this.onUpdate=null,this.renderTarget=null,this.isRenderTargetTexture=!1,this.isArrayTexture=!!(e&&e.depth&&e.depth>1),this.pmremVersion=0}get width(){return this.source.getSize(ao).x}get height(){return this.source.getSize(ao).y}get depth(){return this.source.getSize(ao).z}get image(){return this.source.data}set image(e=null){this.source.data=e}updateMatrix(){this.matrix.setUvTransform(this.offset.x,this.offset.y,this.repeat.x,this.repeat.y,this.rotation,this.center.x,this.center.y)}addUpdateRange(e,t){this.updateRanges.push({start:e,count:t})}clearUpdateRanges(){this.updateRanges.length=0}clone(){return new this.constructor().copy(this)}copy(e){return this.name=e.name,this.source=e.source,this.mipmaps=e.mipmaps.slice(0),this.mapping=e.mapping,this.channel=e.channel,this.wrapS=e.wrapS,this.wrapT=e.wrapT,this.magFilter=e.magFilter,this.minFilter=e.minFilter,this.anisotropy=e.anisotropy,this.format=e.format,this.internalFormat=e.internalFormat,this.type=e.type,this.offset.copy(e.offset),this.repeat.copy(e.repeat),this.center.copy(e.center),this.rotation=e.rotation,this.matrixAutoUpdate=e.matrixAutoUpdate,this.matrix.copy(e.matrix),this.generateMipmaps=e.generateMipmaps,this.premultiplyAlpha=e.premultiplyAlpha,this.flipY=e.flipY,this.unpackAlignment=e.unpackAlignment,this.colorSpace=e.colorSpace,this.renderTarget=e.renderTarget,this.isRenderTargetTexture=e.isRenderTargetTexture,this.isArrayTexture=e.isArrayTexture,this.userData=JSON.parse(JSON.stringify(e.userData)),this.needsUpdate=!0,this}setValues(e){for(const t in e){const i=e[t];if(i===void 0){Oe(`Texture.setValues(): parameter '${t}' has value of undefined.`);continue}const s=this[t];if(s===void 0){Oe(`Texture.setValues(): property '${t}' does not exist.`);continue}s&&i&&s.isVector2&&i.isVector2||s&&i&&s.isVector3&&i.isVector3||s&&i&&s.isMatrix3&&i.isMatrix3?s.copy(i):this[t]=i}}toJSON(e){const t=e===void 0||typeof e=="string";if(!t&&e.textures[this.uuid]!==void 0)return e.textures[this.uuid];const i={metadata:{version:4.7,type:"Texture",generator:"Texture.toJSON"},uuid:this.uuid,name:this.name,image:this.source.toJSON(e).uuid,mapping:this.mapping,channel:this.channel,repeat:[this.repeat.x,this.repeat.y],offset:[this.offset.x,this.offset.y],center:[this.center.x,this.center.y],rotation:this.rotation,wrap:[this.wrapS,this.wrapT],format:this.format,internalFormat:this.internalFormat,type:this.type,colorSpace:this.colorSpace,minFilter:this.minFilter,magFilter:this.magFilter,anisotropy:this.anisotropy,flipY:this.flipY,generateMipmaps:this.generateMipmaps,premultiplyAlpha:this.premultiplyAlpha,unpackAlignment:this.unpackAlignment};return Object.keys(this.userData).length>0&&(i.userData=this.userData),t||(e.textures[this.uuid]=i),i}dispose(){this.dispatchEvent({type:"dispose"})}transformUv(e){if(this.mapping!==Nf)return e;if(e.applyMatrix3(this.matrix),e.x<0||e.x>1)switch(this.wrapS){case ur:e.x=e.x-Math.floor(e.x);break;case Qn:e.x=e.x<0?0:1;break;case tl:Math.abs(Math.floor(e.x)%2)===1?e.x=Math.ceil(e.x)-e.x:e.x=e.x-Math.floor(e.x);break}if(e.y<0||e.y>1)switch(this.wrapT){case ur:e.y=e.y-Math.floor(e.y);break;case Qn:e.y=e.y<0?0:1;break;case tl:Math.abs(Math.floor(e.y)%2)===1?e.y=Math.ceil(e.y)-e.y:e.y=e.y-Math.floor(e.y);break}return this.flipY&&(e.y=1-e.y),e}set needsUpdate(e){e===!0&&(this.version++,this.source.needsUpdate=!0)}set needsPMREMUpdate(e){e===!0&&this.pmremVersion++}}Ft.DEFAULT_IMAGE=null;Ft.DEFAULT_MAPPING=Nf;Ft.DEFAULT_ANISOTROPY=1;class _t{constructor(e=0,t=0,i=0,s=1){_t.prototype.isVector4=!0,this.x=e,this.y=t,this.z=i,this.w=s}get width(){return this.z}set width(e){this.z=e}get height(){return this.w}set height(e){this.w=e}set(e,t,i,s){return this.x=e,this.y=t,this.z=i,this.w=s,this}setScalar(e){return this.x=e,this.y=e,this.z=e,this.w=e,this}setX(e){return this.x=e,this}setY(e){return this.y=e,this}setZ(e){return this.z=e,this}setW(e){return this.w=e,this}setComponent(e,t){switch(e){case 0:this.x=t;break;case 1:this.y=t;break;case 2:this.z=t;break;case 3:this.w=t;break;default:throw new Error("index is out of range: "+e)}return this}getComponent(e){switch(e){case 0:return this.x;case 1:return this.y;case 2:return this.z;case 3:return this.w;default:throw new Error("index is out of range: "+e)}}clone(){return new this.constructor(this.x,this.y,this.z,this.w)}copy(e){return this.x=e.x,this.y=e.y,this.z=e.z,this.w=e.w!==void 0?e.w:1,this}add(e){return this.x+=e.x,this.y+=e.y,this.z+=e.z,this.w+=e.w,this}addScalar(e){return this.x+=e,this.y+=e,this.z+=e,this.w+=e,this}addVectors(e,t){return this.x=e.x+t.x,this.y=e.y+t.y,this.z=e.z+t.z,this.w=e.w+t.w,this}addScaledVector(e,t){return this.x+=e.x*t,this.y+=e.y*t,this.z+=e.z*t,this.w+=e.w*t,this}sub(e){return this.x-=e.x,this.y-=e.y,this.z-=e.z,this.w-=e.w,this}subScalar(e){return this.x-=e,this.y-=e,this.z-=e,this.w-=e,this}subVectors(e,t){return this.x=e.x-t.x,this.y=e.y-t.y,this.z=e.z-t.z,this.w=e.w-t.w,this}multiply(e){return this.x*=e.x,this.y*=e.y,this.z*=e.z,this.w*=e.w,this}multiplyScalar(e){return this.x*=e,this.y*=e,this.z*=e,this.w*=e,this}applyMatrix4(e){const t=this.x,i=this.y,s=this.z,r=this.w,a=e.elements;return this.x=a[0]*t+a[4]*i+a[8]*s+a[12]*r,this.y=a[1]*t+a[5]*i+a[9]*s+a[13]*r,this.z=a[2]*t+a[6]*i+a[10]*s+a[14]*r,this.w=a[3]*t+a[7]*i+a[11]*s+a[15]*r,this}divide(e){return this.x/=e.x,this.y/=e.y,this.z/=e.z,this.w/=e.w,this}divideScalar(e){return this.multiplyScalar(1/e)}setAxisAngleFromQuaternion(e){this.w=2*Math.acos(e.w);const t=Math.sqrt(1-e.w*e.w);return t<1e-4?(this.x=1,this.y=0,this.z=0):(this.x=e.x/t,this.y=e.y/t,this.z=e.z/t),this}setAxisAngleFromRotationMatrix(e){let t,i,s,r;const l=e.elements,c=l[0],u=l[4],f=l[8],h=l[1],p=l[5],_=l[9],M=l[2],g=l[6],d=l[10];if(Math.abs(u-h)<.01&&Math.abs(f-M)<.01&&Math.abs(_-g)<.01){if(Math.abs(u+h)<.1&&Math.abs(f+M)<.1&&Math.abs(_+g)<.1&&Math.abs(c+p+d-3)<.1)return this.set(1,0,0,0),this;t=Math.PI;const A=(c+1)/2,b=(p+1)/2,P=(d+1)/2,L=(u+h)/4,I=(f+M)/4,x=(_+g)/4;return A>b&&A>P?A<.01?(i=0,s=.707106781,r=.707106781):(i=Math.sqrt(A),s=L/i,r=I/i):b>P?b<.01?(i=.707106781,s=0,r=.707106781):(s=Math.sqrt(b),i=L/s,r=x/s):P<.01?(i=.707106781,s=.707106781,r=0):(r=Math.sqrt(P),i=I/r,s=x/r),this.set(i,s,r,t),this}let y=Math.sqrt((g-_)*(g-_)+(f-M)*(f-M)+(h-u)*(h-u));return Math.abs(y)<.001&&(y=1),this.x=(g-_)/y,this.y=(f-M)/y,this.z=(h-u)/y,this.w=Math.acos((c+p+d-1)/2),this}setFromMatrixPosition(e){const t=e.elements;return this.x=t[12],this.y=t[13],this.z=t[14],this.w=t[15],this}min(e){return this.x=Math.min(this.x,e.x),this.y=Math.min(this.y,e.y),this.z=Math.min(this.z,e.z),this.w=Math.min(this.w,e.w),this}max(e){return this.x=Math.max(this.x,e.x),this.y=Math.max(this.y,e.y),this.z=Math.max(this.z,e.z),this.w=Math.max(this.w,e.w),this}clamp(e,t){return this.x=Je(this.x,e.x,t.x),this.y=Je(this.y,e.y,t.y),this.z=Je(this.z,e.z,t.z),this.w=Je(this.w,e.w,t.w),this}clampScalar(e,t){return this.x=Je(this.x,e,t),this.y=Je(this.y,e,t),this.z=Je(this.z,e,t),this.w=Je(this.w,e,t),this}clampLength(e,t){const i=this.length();return this.divideScalar(i||1).multiplyScalar(Je(i,e,t))}floor(){return this.x=Math.floor(this.x),this.y=Math.floor(this.y),this.z=Math.floor(this.z),this.w=Math.floor(this.w),this}ceil(){return this.x=Math.ceil(this.x),this.y=Math.ceil(this.y),this.z=Math.ceil(this.z),this.w=Math.ceil(this.w),this}round(){return this.x=Math.round(this.x),this.y=Math.round(this.y),this.z=Math.round(this.z),this.w=Math.round(this.w),this}roundToZero(){return this.x=Math.trunc(this.x),this.y=Math.trunc(this.y),this.z=Math.trunc(this.z),this.w=Math.trunc(this.w),this}negate(){return this.x=-this.x,this.y=-this.y,this.z=-this.z,this.w=-this.w,this}dot(e){return this.x*e.x+this.y*e.y+this.z*e.z+this.w*e.w}lengthSq(){return this.x*this.x+this.y*this.y+this.z*this.z+this.w*this.w}length(){return Math.sqrt(this.x*this.x+this.y*this.y+this.z*this.z+this.w*this.w)}manhattanLength(){return Math.abs(this.x)+Math.abs(this.y)+Math.abs(this.z)+Math.abs(this.w)}normalize(){return this.divideScalar(this.length()||1)}setLength(e){return this.normalize().multiplyScalar(e)}lerp(e,t){return this.x+=(e.x-this.x)*t,this.y+=(e.y-this.y)*t,this.z+=(e.z-this.z)*t,this.w+=(e.w-this.w)*t,this}lerpVectors(e,t,i){return this.x=e.x+(t.x-e.x)*i,this.y=e.y+(t.y-e.y)*i,this.z=e.z+(t.z-e.z)*i,this.w=e.w+(t.w-e.w)*i,this}equals(e){return e.x===this.x&&e.y===this.y&&e.z===this.z&&e.w===this.w}fromArray(e,t=0){return this.x=e[t],this.y=e[t+1],this.z=e[t+2],this.w=e[t+3],this}toArray(e=[],t=0){return e[t]=this.x,e[t+1]=this.y,e[t+2]=this.z,e[t+3]=this.w,e}fromBufferAttribute(e,t){return this.x=e.getX(t),this.y=e.getY(t),this.z=e.getZ(t),this.w=e.getW(t),this}random(){return this.x=Math.random(),this.y=Math.random(),this.z=Math.random(),this.w=Math.random(),this}*[Symbol.iterator](){yield this.x,yield this.y,yield this.z,yield this.w}}class Cg extends Yi{constructor(e=1,t=1,i={}){super(),i=Object.assign({generateMipmaps:!1,internalFormat:null,minFilter:Ut,depthBuffer:!0,stencilBuffer:!1,resolveDepthBuffer:!0,resolveStencilBuffer:!0,depthTexture:null,samples:0,count:1,depth:1,multiview:!1},i),this.isRenderTarget=!0,this.width=e,this.height=t,this.depth=i.depth,this.scissor=new _t(0,0,e,t),this.scissorTest=!1,this.viewport=new _t(0,0,e,t),this.textures=[];const s={width:e,height:t,depth:i.depth},r=new Ft(s),a=i.count;for(let o=0;o<a;o++)this.textures[o]=r.clone(),this.textures[o].isRenderTargetTexture=!0,this.textures[o].renderTarget=this;this._setTextureOptions(i),this.depthBuffer=i.depthBuffer,this.stencilBuffer=i.stencilBuffer,this.resolveDepthBuffer=i.resolveDepthBuffer,this.resolveStencilBuffer=i.resolveStencilBuffer,this._depthTexture=null,this.depthTexture=i.depthTexture,this.samples=i.samples,this.multiview=i.multiview}_setTextureOptions(e={}){const t={minFilter:Ut,generateMipmaps:!1,flipY:!1,internalFormat:null};e.mapping!==void 0&&(t.mapping=e.mapping),e.wrapS!==void 0&&(t.wrapS=e.wrapS),e.wrapT!==void 0&&(t.wrapT=e.wrapT),e.wrapR!==void 0&&(t.wrapR=e.wrapR),e.magFilter!==void 0&&(t.magFilter=e.magFilter),e.minFilter!==void 0&&(t.minFilter=e.minFilter),e.format!==void 0&&(t.format=e.format),e.type!==void 0&&(t.type=e.type),e.anisotropy!==void 0&&(t.anisotropy=e.anisotropy),e.colorSpace!==void 0&&(t.colorSpace=e.colorSpace),e.flipY!==void 0&&(t.flipY=e.flipY),e.generateMipmaps!==void 0&&(t.generateMipmaps=e.generateMipmaps),e.internalFormat!==void 0&&(t.internalFormat=e.internalFormat);for(let i=0;i<this.textures.length;i++)this.textures[i].setValues(t)}get texture(){return this.textures[0]}set texture(e){this.textures[0]=e}set depthTexture(e){this._depthTexture!==null&&(this._depthTexture.renderTarget=null),e!==null&&(e.renderTarget=this),this._depthTexture=e}get depthTexture(){return this._depthTexture}setSize(e,t,i=1){if(this.width!==e||this.height!==t||this.depth!==i){this.width=e,this.height=t,this.depth=i;for(let s=0,r=this.textures.length;s<r;s++)this.textures[s].image.width=e,this.textures[s].image.height=t,this.textures[s].image.depth=i,this.textures[s].isData3DTexture!==!0&&(this.textures[s].isArrayTexture=this.textures[s].image.depth>1);this.dispose()}this.viewport.set(0,0,e,t),this.scissor.set(0,0,e,t)}clone(){return new this.constructor().copy(this)}copy(e){this.width=e.width,this.height=e.height,this.depth=e.depth,this.scissor.copy(e.scissor),this.scissorTest=e.scissorTest,this.viewport.copy(e.viewport),this.textures.length=0;for(let t=0,i=e.textures.length;t<i;t++){this.textures[t]=e.textures[t].clone(),this.textures[t].isRenderTargetTexture=!0,this.textures[t].renderTarget=this;const s=Object.assign({},e.textures[t].image);this.textures[t].source=new fc(s)}return this.depthBuffer=e.depthBuffer,this.stencilBuffer=e.stencilBuffer,this.resolveDepthBuffer=e.resolveDepthBuffer,this.resolveStencilBuffer=e.resolveStencilBuffer,e.depthTexture!==null&&(this.depthTexture=e.depthTexture.clone()),this.samples=e.samples,this}dispose(){this.dispatchEvent({type:"dispose"})}}class Un extends Cg{constructor(e=1,t=1,i={}){super(e,t,i),this.isWebGLRenderTarget=!0}}class Wf extends Ft{constructor(e=null,t=1,i=1,s=1){super(null),this.isDataArrayTexture=!0,this.image={data:e,width:t,height:i,depth:s},this.magFilter=Ct,this.minFilter=Ct,this.wrapR=Qn,this.generateMipmaps=!1,this.flipY=!1,this.unpackAlignment=1,this.layerUpdates=new Set}addLayerUpdate(e){this.layerUpdates.add(e)}clearLayerUpdates(){this.layerUpdates.clear()}}class Rg extends Ft{constructor(e=null,t=1,i=1,s=1){super(null),this.isData3DTexture=!0,this.image={data:e,width:t,height:i,depth:s},this.magFilter=Ct,this.minFilter=Ct,this.wrapR=Qn,this.generateMipmaps=!1,this.flipY=!1,this.unpackAlignment=1}}class ht{constructor(e,t,i,s,r,a,o,l,c,u,f,h,p,_,M,g){ht.prototype.isMatrix4=!0,this.elements=[1,0,0,0,0,1,0,0,0,0,1,0,0,0,0,1],e!==void 0&&this.set(e,t,i,s,r,a,o,l,c,u,f,h,p,_,M,g)}set(e,t,i,s,r,a,o,l,c,u,f,h,p,_,M,g){const d=this.elements;return d[0]=e,d[4]=t,d[8]=i,d[12]=s,d[1]=r,d[5]=a,d[9]=o,d[13]=l,d[2]=c,d[6]=u,d[10]=f,d[14]=h,d[3]=p,d[7]=_,d[11]=M,d[15]=g,this}identity(){return this.set(1,0,0,0,0,1,0,0,0,0,1,0,0,0,0,1),this}clone(){return new ht().fromArray(this.elements)}copy(e){const t=this.elements,i=e.elements;return t[0]=i[0],t[1]=i[1],t[2]=i[2],t[3]=i[3],t[4]=i[4],t[5]=i[5],t[6]=i[6],t[7]=i[7],t[8]=i[8],t[9]=i[9],t[10]=i[10],t[11]=i[11],t[12]=i[12],t[13]=i[13],t[14]=i[14],t[15]=i[15],this}copyPosition(e){const t=this.elements,i=e.elements;return t[12]=i[12],t[13]=i[13],t[14]=i[14],this}setFromMatrix3(e){const t=e.elements;return this.set(t[0],t[3],t[6],0,t[1],t[4],t[7],0,t[2],t[5],t[8],0,0,0,0,1),this}extractBasis(e,t,i){return this.determinant()===0?(e.set(1,0,0),t.set(0,1,0),i.set(0,0,1),this):(e.setFromMatrixColumn(this,0),t.setFromMatrixColumn(this,1),i.setFromMatrixColumn(this,2),this)}makeBasis(e,t,i){return this.set(e.x,t.x,i.x,0,e.y,t.y,i.y,0,e.z,t.z,i.z,0,0,0,0,1),this}extractRotation(e){if(e.determinant()===0)return this.identity();const t=this.elements,i=e.elements,s=1/es.setFromMatrixColumn(e,0).length(),r=1/es.setFromMatrixColumn(e,1).length(),a=1/es.setFromMatrixColumn(e,2).length();return t[0]=i[0]*s,t[1]=i[1]*s,t[2]=i[2]*s,t[3]=0,t[4]=i[4]*r,t[5]=i[5]*r,t[6]=i[6]*r,t[7]=0,t[8]=i[8]*a,t[9]=i[9]*a,t[10]=i[10]*a,t[11]=0,t[12]=0,t[13]=0,t[14]=0,t[15]=1,this}makeRotationFromEuler(e){const t=this.elements,i=e.x,s=e.y,r=e.z,a=Math.cos(i),o=Math.sin(i),l=Math.cos(s),c=Math.sin(s),u=Math.cos(r),f=Math.sin(r);if(e.order==="XYZ"){const h=a*u,p=a*f,_=o*u,M=o*f;t[0]=l*u,t[4]=-l*f,t[8]=c,t[1]=p+_*c,t[5]=h-M*c,t[9]=-o*l,t[2]=M-h*c,t[6]=_+p*c,t[10]=a*l}else if(e.order==="YXZ"){const h=l*u,p=l*f,_=c*u,M=c*f;t[0]=h+M*o,t[4]=_*o-p,t[8]=a*c,t[1]=a*f,t[5]=a*u,t[9]=-o,t[2]=p*o-_,t[6]=M+h*o,t[10]=a*l}else if(e.order==="ZXY"){const h=l*u,p=l*f,_=c*u,M=c*f;t[0]=h-M*o,t[4]=-a*f,t[8]=_+p*o,t[1]=p+_*o,t[5]=a*u,t[9]=M-h*o,t[2]=-a*c,t[6]=o,t[10]=a*l}else if(e.order==="ZYX"){const h=a*u,p=a*f,_=o*u,M=o*f;t[0]=l*u,t[4]=_*c-p,t[8]=h*c+M,t[1]=l*f,t[5]=M*c+h,t[9]=p*c-_,t[2]=-c,t[6]=o*l,t[10]=a*l}else if(e.order==="YZX"){const h=a*l,p=a*c,_=o*l,M=o*c;t[0]=l*u,t[4]=M-h*f,t[8]=_*f+p,t[1]=f,t[5]=a*u,t[9]=-o*u,t[2]=-c*u,t[6]=p*f+_,t[10]=h-M*f}else if(e.order==="XZY"){const h=a*l,p=a*c,_=o*l,M=o*c;t[0]=l*u,t[4]=-f,t[8]=c*u,t[1]=h*f+M,t[5]=a*u,t[9]=p*f-_,t[2]=_*f-p,t[6]=o*u,t[10]=M*f+h}return t[3]=0,t[7]=0,t[11]=0,t[12]=0,t[13]=0,t[14]=0,t[15]=1,this}makeRotationFromQuaternion(e){return this.compose(Pg,e,Dg)}lookAt(e,t,i){const s=this.elements;return Kt.subVectors(e,t),Kt.lengthSq()===0&&(Kt.z=1),Kt.normalize(),hi.crossVectors(i,Kt),hi.lengthSq()===0&&(Math.abs(i.z)===1?Kt.x+=1e-4:Kt.z+=1e-4,Kt.normalize(),hi.crossVectors(i,Kt)),hi.normalize(),Ar.crossVectors(Kt,hi),s[0]=hi.x,s[4]=Ar.x,s[8]=Kt.x,s[1]=hi.y,s[5]=Ar.y,s[9]=Kt.y,s[2]=hi.z,s[6]=Ar.z,s[10]=Kt.z,this}multiply(e){return this.multiplyMatrices(this,e)}premultiply(e){return this.multiplyMatrices(e,this)}multiplyMatrices(e,t){const i=e.elements,s=t.elements,r=this.elements,a=i[0],o=i[4],l=i[8],c=i[12],u=i[1],f=i[5],h=i[9],p=i[13],_=i[2],M=i[6],g=i[10],d=i[14],y=i[3],A=i[7],b=i[11],P=i[15],L=s[0],I=s[4],x=s[8],T=s[12],Q=s[1],D=s[5],X=s[9],Y=s[13],$=s[2],W=s[6],B=s[10],N=s[14],ie=s[3],le=s[7],ve=s[11],se=s[15];return r[0]=a*L+o*Q+l*$+c*ie,r[4]=a*I+o*D+l*W+c*le,r[8]=a*x+o*X+l*B+c*ve,r[12]=a*T+o*Y+l*N+c*se,r[1]=u*L+f*Q+h*$+p*ie,r[5]=u*I+f*D+h*W+p*le,r[9]=u*x+f*X+h*B+p*ve,r[13]=u*T+f*Y+h*N+p*se,r[2]=_*L+M*Q+g*$+d*ie,r[6]=_*I+M*D+g*W+d*le,r[10]=_*x+M*X+g*B+d*ve,r[14]=_*T+M*Y+g*N+d*se,r[3]=y*L+A*Q+b*$+P*ie,r[7]=y*I+A*D+b*W+P*le,r[11]=y*x+A*X+b*B+P*ve,r[15]=y*T+A*Y+b*N+P*se,this}multiplyScalar(e){const t=this.elements;return t[0]*=e,t[4]*=e,t[8]*=e,t[12]*=e,t[1]*=e,t[5]*=e,t[9]*=e,t[13]*=e,t[2]*=e,t[6]*=e,t[10]*=e,t[14]*=e,t[3]*=e,t[7]*=e,t[11]*=e,t[15]*=e,this}determinant(){const e=this.elements,t=e[0],i=e[4],s=e[8],r=e[12],a=e[1],o=e[5],l=e[9],c=e[13],u=e[2],f=e[6],h=e[10],p=e[14],_=e[3],M=e[7],g=e[11],d=e[15],y=l*p-c*h,A=o*p-c*f,b=o*h-l*f,P=a*p-c*u,L=a*h-l*u,I=a*f-o*u;return t*(M*y-g*A+d*b)-i*(_*y-g*P+d*L)+s*(_*A-M*P+d*I)-r*(_*b-M*L+g*I)}transpose(){const e=this.elements;let t;return t=e[1],e[1]=e[4],e[4]=t,t=e[2],e[2]=e[8],e[8]=t,t=e[6],e[6]=e[9],e[9]=t,t=e[3],e[3]=e[12],e[12]=t,t=e[7],e[7]=e[13],e[13]=t,t=e[11],e[11]=e[14],e[14]=t,this}setPosition(e,t,i){const s=this.elements;return e.isVector3?(s[12]=e.x,s[13]=e.y,s[14]=e.z):(s[12]=e,s[13]=t,s[14]=i),this}invert(){const e=this.elements,t=e[0],i=e[1],s=e[2],r=e[3],a=e[4],o=e[5],l=e[6],c=e[7],u=e[8],f=e[9],h=e[10],p=e[11],_=e[12],M=e[13],g=e[14],d=e[15],y=t*o-i*a,A=t*l-s*a,b=t*c-r*a,P=i*l-s*o,L=i*c-r*o,I=s*c-r*l,x=u*M-f*_,T=u*g-h*_,Q=u*d-p*_,D=f*g-h*M,X=f*d-p*M,Y=h*d-p*g,$=y*Y-A*X+b*D+P*Q-L*T+I*x;if($===0)return this.set(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);const W=1/$;return e[0]=(o*Y-l*X+c*D)*W,e[1]=(s*X-i*Y-r*D)*W,e[2]=(M*I-g*L+d*P)*W,e[3]=(h*L-f*I-p*P)*W,e[4]=(l*Q-a*Y-c*T)*W,e[5]=(t*Y-s*Q+r*T)*W,e[6]=(g*b-_*I-d*A)*W,e[7]=(u*I-h*b+p*A)*W,e[8]=(a*X-o*Q+c*x)*W,e[9]=(i*Q-t*X-r*x)*W,e[10]=(_*L-M*b+d*y)*W,e[11]=(f*b-u*L-p*y)*W,e[12]=(o*T-a*D-l*x)*W,e[13]=(t*D-i*T+s*x)*W,e[14]=(M*A-_*P-g*y)*W,e[15]=(u*P-f*A+h*y)*W,this}scale(e){const t=this.elements,i=e.x,s=e.y,r=e.z;return t[0]*=i,t[4]*=s,t[8]*=r,t[1]*=i,t[5]*=s,t[9]*=r,t[2]*=i,t[6]*=s,t[10]*=r,t[3]*=i,t[7]*=s,t[11]*=r,this}getMaxScaleOnAxis(){const e=this.elements,t=e[0]*e[0]+e[1]*e[1]+e[2]*e[2],i=e[4]*e[4]+e[5]*e[5]+e[6]*e[6],s=e[8]*e[8]+e[9]*e[9]+e[10]*e[10];return Math.sqrt(Math.max(t,i,s))}makeTranslation(e,t,i){return e.isVector3?this.set(1,0,0,e.x,0,1,0,e.y,0,0,1,e.z,0,0,0,1):this.set(1,0,0,e,0,1,0,t,0,0,1,i,0,0,0,1),this}makeRotationX(e){const t=Math.cos(e),i=Math.sin(e);return this.set(1,0,0,0,0,t,-i,0,0,i,t,0,0,0,0,1),this}makeRotationY(e){const t=Math.cos(e),i=Math.sin(e);return this.set(t,0,i,0,0,1,0,0,-i,0,t,0,0,0,0,1),this}makeRotationZ(e){const t=Math.cos(e),i=Math.sin(e);return this.set(t,-i,0,0,i,t,0,0,0,0,1,0,0,0,0,1),this}makeRotationAxis(e,t){const i=Math.cos(t),s=Math.sin(t),r=1-i,a=e.x,o=e.y,l=e.z,c=r*a,u=r*o;return this.set(c*a+i,c*o-s*l,c*l+s*o,0,c*o+s*l,u*o+i,u*l-s*a,0,c*l-s*o,u*l+s*a,r*l*l+i,0,0,0,0,1),this}makeScale(e,t,i){return this.set(e,0,0,0,0,t,0,0,0,0,i,0,0,0,0,1),this}makeShear(e,t,i,s,r,a){return this.set(1,i,r,0,e,1,a,0,t,s,1,0,0,0,0,1),this}compose(e,t,i){const s=this.elements,r=t._x,a=t._y,o=t._z,l=t._w,c=r+r,u=a+a,f=o+o,h=r*c,p=r*u,_=r*f,M=a*u,g=a*f,d=o*f,y=l*c,A=l*u,b=l*f,P=i.x,L=i.y,I=i.z;return s[0]=(1-(M+d))*P,s[1]=(p+b)*P,s[2]=(_-A)*P,s[3]=0,s[4]=(p-b)*L,s[5]=(1-(h+d))*L,s[6]=(g+y)*L,s[7]=0,s[8]=(_+A)*I,s[9]=(g-y)*I,s[10]=(1-(h+M))*I,s[11]=0,s[12]=e.x,s[13]=e.y,s[14]=e.z,s[15]=1,this}decompose(e,t,i){const s=this.elements;e.x=s[12],e.y=s[13],e.z=s[14];const r=this.determinant();if(r===0)return i.set(1,1,1),t.identity(),this;let a=es.set(s[0],s[1],s[2]).length();const o=es.set(s[4],s[5],s[6]).length(),l=es.set(s[8],s[9],s[10]).length();r<0&&(a=-a),un.copy(this);const c=1/a,u=1/o,f=1/l;return un.elements[0]*=c,un.elements[1]*=c,un.elements[2]*=c,un.elements[4]*=u,un.elements[5]*=u,un.elements[6]*=u,un.elements[8]*=f,un.elements[9]*=f,un.elements[10]*=f,t.setFromRotationMatrix(un),i.x=a,i.y=o,i.z=l,this}makePerspective(e,t,i,s,r,a,o=Dn,l=!1){const c=this.elements,u=2*r/(t-e),f=2*r/(i-s),h=(t+e)/(t-e),p=(i+s)/(i-s);let _,M;if(l)_=r/(a-r),M=a*r/(a-r);else if(o===Dn)_=-(a+r)/(a-r),M=-2*a*r/(a-r);else if(o===dr)_=-a/(a-r),M=-a*r/(a-r);else throw new Error("THREE.Matrix4.makePerspective(): Invalid coordinate system: "+o);return c[0]=u,c[4]=0,c[8]=h,c[12]=0,c[1]=0,c[5]=f,c[9]=p,c[13]=0,c[2]=0,c[6]=0,c[10]=_,c[14]=M,c[3]=0,c[7]=0,c[11]=-1,c[15]=0,this}makeOrthographic(e,t,i,s,r,a,o=Dn,l=!1){const c=this.elements,u=2/(t-e),f=2/(i-s),h=-(t+e)/(t-e),p=-(i+s)/(i-s);let _,M;if(l)_=1/(a-r),M=a/(a-r);else if(o===Dn)_=-2/(a-r),M=-(a+r)/(a-r);else if(o===dr)_=-1/(a-r),M=-r/(a-r);else throw new Error("THREE.Matrix4.makeOrthographic(): Invalid coordinate system: "+o);return c[0]=u,c[4]=0,c[8]=0,c[12]=h,c[1]=0,c[5]=f,c[9]=0,c[13]=p,c[2]=0,c[6]=0,c[10]=_,c[14]=M,c[3]=0,c[7]=0,c[11]=0,c[15]=1,this}equals(e){const t=this.elements,i=e.elements;for(let s=0;s<16;s++)if(t[s]!==i[s])return!1;return!0}fromArray(e,t=0){for(let i=0;i<16;i++)this.elements[i]=e[i+t];return this}toArray(e=[],t=0){const i=this.elements;return e[t]=i[0],e[t+1]=i[1],e[t+2]=i[2],e[t+3]=i[3],e[t+4]=i[4],e[t+5]=i[5],e[t+6]=i[6],e[t+7]=i[7],e[t+8]=i[8],e[t+9]=i[9],e[t+10]=i[10],e[t+11]=i[11],e[t+12]=i[12],e[t+13]=i[13],e[t+14]=i[14],e[t+15]=i[15],e}}const es=new O,un=new ht,Pg=new O(0,0,0),Dg=new O(1,1,1),hi=new O,Ar=new O,Kt=new O,xu=new ht,vu=new oi;class Bn{constructor(e=0,t=0,i=0,s=Bn.DEFAULT_ORDER){this.isEuler=!0,this._x=e,this._y=t,this._z=i,this._order=s}get x(){return this._x}set x(e){this._x=e,this._onChangeCallback()}get y(){return this._y}set y(e){this._y=e,this._onChangeCallback()}get z(){return this._z}set z(e){this._z=e,this._onChangeCallback()}get order(){return this._order}set order(e){this._order=e,this._onChangeCallback()}set(e,t,i,s=this._order){return this._x=e,this._y=t,this._z=i,this._order=s,this._onChangeCallback(),this}clone(){return new this.constructor(this._x,this._y,this._z,this._order)}copy(e){return this._x=e._x,this._y=e._y,this._z=e._z,this._order=e._order,this._onChangeCallback(),this}setFromRotationMatrix(e,t=this._order,i=!0){const s=e.elements,r=s[0],a=s[4],o=s[8],l=s[1],c=s[5],u=s[9],f=s[2],h=s[6],p=s[10];switch(t){case"XYZ":this._y=Math.asin(Je(o,-1,1)),Math.abs(o)<.9999999?(this._x=Math.atan2(-u,p),this._z=Math.atan2(-a,r)):(this._x=Math.atan2(h,c),this._z=0);break;case"YXZ":this._x=Math.asin(-Je(u,-1,1)),Math.abs(u)<.9999999?(this._y=Math.atan2(o,p),this._z=Math.atan2(l,c)):(this._y=Math.atan2(-f,r),this._z=0);break;case"ZXY":this._x=Math.asin(Je(h,-1,1)),Math.abs(h)<.9999999?(this._y=Math.atan2(-f,p),this._z=Math.atan2(-a,c)):(this._y=0,this._z=Math.atan2(l,r));break;case"ZYX":this._y=Math.asin(-Je(f,-1,1)),Math.abs(f)<.9999999?(this._x=Math.atan2(h,p),this._z=Math.atan2(l,r)):(this._x=0,this._z=Math.atan2(-a,c));break;case"YZX":this._z=Math.asin(Je(l,-1,1)),Math.abs(l)<.9999999?(this._x=Math.atan2(-u,c),this._y=Math.atan2(-f,r)):(this._x=0,this._y=Math.atan2(o,p));break;case"XZY":this._z=Math.asin(-Je(a,-1,1)),Math.abs(a)<.9999999?(this._x=Math.atan2(h,c),this._y=Math.atan2(o,r)):(this._x=Math.atan2(-u,p),this._y=0);break;default:Oe("Euler: .setFromRotationMatrix() encountered an unknown order: "+t)}return this._order=t,i===!0&&this._onChangeCallback(),this}setFromQuaternion(e,t,i){return xu.makeRotationFromQuaternion(e),this.setFromRotationMatrix(xu,t,i)}setFromVector3(e,t=this._order){return this.set(e.x,e.y,e.z,t)}reorder(e){return vu.setFromEuler(this),this.setFromQuaternion(vu,e)}equals(e){return e._x===this._x&&e._y===this._y&&e._z===this._z&&e._order===this._order}fromArray(e){return this._x=e[0],this._y=e[1],this._z=e[2],e[3]!==void 0&&(this._order=e[3]),this._onChangeCallback(),this}toArray(e=[],t=0){return e[t]=this._x,e[t+1]=this._y,e[t+2]=this._z,e[t+3]=this._order,e}_onChange(e){return this._onChangeCallback=e,this}_onChangeCallback(){}*[Symbol.iterator](){yield this._x,yield this._y,yield this._z,yield this._order}}Bn.DEFAULT_ORDER="XYZ";class dc{constructor(){this.mask=1}set(e){this.mask=(1<<e|0)>>>0}enable(e){this.mask|=1<<e|0}enableAll(){this.mask=-1}toggle(e){this.mask^=1<<e|0}disable(e){this.mask&=~(1<<e|0)}disableAll(){this.mask=0}test(e){return(this.mask&e.mask)!==0}isEnabled(e){return(this.mask&(1<<e|0))!==0}}let Lg=0;const Mu=new O,ts=new oi,Gn=new ht,wr=new O,Os=new O,Ig=new O,Ug=new oi,Su=new O(1,0,0),yu=new O(0,1,0),bu=new O(0,0,1),Eu={type:"added"},Ng={type:"removed"},ns={type:"childadded",child:null},oo={type:"childremoved",child:null};class Mt extends Yi{constructor(){super(),this.isObject3D=!0,Object.defineProperty(this,"id",{value:Lg++}),this.uuid=xr(),this.name="",this.type="Object3D",this.parent=null,this.children=[],this.up=Mt.DEFAULT_UP.clone();const e=new O,t=new Bn,i=new oi,s=new O(1,1,1);function r(){i.setFromEuler(t,!1)}function a(){t.setFromQuaternion(i,void 0,!1)}t._onChange(r),i._onChange(a),Object.defineProperties(this,{position:{configurable:!0,enumerable:!0,value:e},rotation:{configurable:!0,enumerable:!0,value:t},quaternion:{configurable:!0,enumerable:!0,value:i},scale:{configurable:!0,enumerable:!0,value:s},modelViewMatrix:{value:new ht},normalMatrix:{value:new qe}}),this.matrix=new ht,this.matrixWorld=new ht,this.matrixAutoUpdate=Mt.DEFAULT_MATRIX_AUTO_UPDATE,this.matrixWorldAutoUpdate=Mt.DEFAULT_MATRIX_WORLD_AUTO_UPDATE,this.matrixWorldNeedsUpdate=!1,this.layers=new dc,this.visible=!0,this.castShadow=!1,this.receiveShadow=!1,this.frustumCulled=!0,this.renderOrder=0,this.animations=[],this.customDepthMaterial=void 0,this.customDistanceMaterial=void 0,this.static=!1,this.userData={},this.pivot=null}onBeforeShadow(){}onAfterShadow(){}onBeforeRender(){}onAfterRender(){}applyMatrix4(e){this.matrixAutoUpdate&&this.updateMatrix(),this.matrix.premultiply(e),this.matrix.decompose(this.position,this.quaternion,this.scale)}applyQuaternion(e){return this.quaternion.premultiply(e),this}setRotationFromAxisAngle(e,t){this.quaternion.setFromAxisAngle(e,t)}setRotationFromEuler(e){this.quaternion.setFromEuler(e,!0)}setRotationFromMatrix(e){this.quaternion.setFromRotationMatrix(e)}setRotationFromQuaternion(e){this.quaternion.copy(e)}rotateOnAxis(e,t){return ts.setFromAxisAngle(e,t),this.quaternion.multiply(ts),this}rotateOnWorldAxis(e,t){return ts.setFromAxisAngle(e,t),this.quaternion.premultiply(ts),this}rotateX(e){return this.rotateOnAxis(Su,e)}rotateY(e){return this.rotateOnAxis(yu,e)}rotateZ(e){return this.rotateOnAxis(bu,e)}translateOnAxis(e,t){return Mu.copy(e).applyQuaternion(this.quaternion),this.position.add(Mu.multiplyScalar(t)),this}translateX(e){return this.translateOnAxis(Su,e)}translateY(e){return this.translateOnAxis(yu,e)}translateZ(e){return this.translateOnAxis(bu,e)}localToWorld(e){return this.updateWorldMatrix(!0,!1),e.applyMatrix4(this.matrixWorld)}worldToLocal(e){return this.updateWorldMatrix(!0,!1),e.applyMatrix4(Gn.copy(this.matrixWorld).invert())}lookAt(e,t,i){e.isVector3?wr.copy(e):wr.set(e,t,i);const s=this.parent;this.updateWorldMatrix(!0,!1),Os.setFromMatrixPosition(this.matrixWorld),this.isCamera||this.isLight?Gn.lookAt(Os,wr,this.up):Gn.lookAt(wr,Os,this.up),this.quaternion.setFromRotationMatrix(Gn),s&&(Gn.extractRotation(s.matrixWorld),ts.setFromRotationMatrix(Gn),this.quaternion.premultiply(ts.invert()))}add(e){if(arguments.length>1){for(let t=0;t<arguments.length;t++)this.add(arguments[t]);return this}return e===this?(Qe("Object3D.add: object can't be added as a child of itself.",e),this):(e&&e.isObject3D?(e.removeFromParent(),e.parent=this,this.children.push(e),e.dispatchEvent(Eu),ns.child=e,this.dispatchEvent(ns),ns.child=null):Qe("Object3D.add: object not an instance of THREE.Object3D.",e),this)}remove(e){if(arguments.length>1){for(let i=0;i<arguments.length;i++)this.remove(arguments[i]);return this}const t=this.children.indexOf(e);return t!==-1&&(e.parent=null,this.children.splice(t,1),e.dispatchEvent(Ng),oo.child=e,this.dispatchEvent(oo),oo.child=null),this}removeFromParent(){const e=this.parent;return e!==null&&e.remove(this),this}clear(){return this.remove(...this.children)}attach(e){return this.updateWorldMatrix(!0,!1),Gn.copy(this.matrixWorld).invert(),e.parent!==null&&(e.parent.updateWorldMatrix(!0,!1),Gn.multiply(e.parent.matrixWorld)),e.applyMatrix4(Gn),e.removeFromParent(),e.parent=this,this.children.push(e),e.updateWorldMatrix(!1,!0),e.dispatchEvent(Eu),ns.child=e,this.dispatchEvent(ns),ns.child=null,this}getObjectById(e){return this.getObjectByProperty("id",e)}getObjectByName(e){return this.getObjectByProperty("name",e)}getObjectByProperty(e,t){if(this[e]===t)return this;for(let i=0,s=this.children.length;i<s;i++){const a=this.children[i].getObjectByProperty(e,t);if(a!==void 0)return a}}getObjectsByProperty(e,t,i=[]){this[e]===t&&i.push(this);const s=this.children;for(let r=0,a=s.length;r<a;r++)s[r].getObjectsByProperty(e,t,i);return i}getWorldPosition(e){return this.updateWorldMatrix(!0,!1),e.setFromMatrixPosition(this.matrixWorld)}getWorldQuaternion(e){return this.updateWorldMatrix(!0,!1),this.matrixWorld.decompose(Os,e,Ig),e}getWorldScale(e){return this.updateWorldMatrix(!0,!1),this.matrixWorld.decompose(Os,Ug,e),e}getWorldDirection(e){this.updateWorldMatrix(!0,!1);const t=this.matrixWorld.elements;return e.set(t[8],t[9],t[10]).normalize()}raycast(){}traverse(e){e(this);const t=this.children;for(let i=0,s=t.length;i<s;i++)t[i].traverse(e)}traverseVisible(e){if(this.visible===!1)return;e(this);const t=this.children;for(let i=0,s=t.length;i<s;i++)t[i].traverseVisible(e)}traverseAncestors(e){const t=this.parent;t!==null&&(e(t),t.traverseAncestors(e))}updateMatrix(){this.matrix.compose(this.position,this.quaternion,this.scale);const e=this.pivot;if(e!==null){const t=e.x,i=e.y,s=e.z,r=this.matrix.elements;r[12]+=t-r[0]*t-r[4]*i-r[8]*s,r[13]+=i-r[1]*t-r[5]*i-r[9]*s,r[14]+=s-r[2]*t-r[6]*i-r[10]*s}this.matrixWorldNeedsUpdate=!0}updateMatrixWorld(e){this.matrixAutoUpdate&&this.updateMatrix(),(this.matrixWorldNeedsUpdate||e)&&(this.matrixWorldAutoUpdate===!0&&(this.parent===null?this.matrixWorld.copy(this.matrix):this.matrixWorld.multiplyMatrices(this.parent.matrixWorld,this.matrix)),this.matrixWorldNeedsUpdate=!1,e=!0);const t=this.children;for(let i=0,s=t.length;i<s;i++)t[i].updateMatrixWorld(e)}updateWorldMatrix(e,t){const i=this.parent;if(e===!0&&i!==null&&i.updateWorldMatrix(!0,!1),this.matrixAutoUpdate&&this.updateMatrix(),this.matrixWorldAutoUpdate===!0&&(this.parent===null?this.matrixWorld.copy(this.matrix):this.matrixWorld.multiplyMatrices(this.parent.matrixWorld,this.matrix)),t===!0){const s=this.children;for(let r=0,a=s.length;r<a;r++)s[r].updateWorldMatrix(!1,!0)}}toJSON(e){const t=e===void 0||typeof e=="string",i={};t&&(e={geometries:{},materials:{},textures:{},images:{},shapes:{},skeletons:{},animations:{},nodes:{}},i.metadata={version:4.7,type:"Object",generator:"Object3D.toJSON"});const s={};s.uuid=this.uuid,s.type=this.type,this.name!==""&&(s.name=this.name),this.castShadow===!0&&(s.castShadow=!0),this.receiveShadow===!0&&(s.receiveShadow=!0),this.visible===!1&&(s.visible=!1),this.frustumCulled===!1&&(s.frustumCulled=!1),this.renderOrder!==0&&(s.renderOrder=this.renderOrder),this.static!==!1&&(s.static=this.static),Object.keys(this.userData).length>0&&(s.userData=this.userData),s.layers=this.layers.mask,s.matrix=this.matrix.toArray(),s.up=this.up.toArray(),this.pivot!==null&&(s.pivot=this.pivot.toArray()),this.matrixAutoUpdate===!1&&(s.matrixAutoUpdate=!1),this.morphTargetDictionary!==void 0&&(s.morphTargetDictionary=Object.assign({},this.morphTargetDictionary)),this.morphTargetInfluences!==void 0&&(s.morphTargetInfluences=this.morphTargetInfluences.slice()),this.isInstancedMesh&&(s.type="InstancedMesh",s.count=this.count,s.instanceMatrix=this.instanceMatrix.toJSON(),this.instanceColor!==null&&(s.instanceColor=this.instanceColor.toJSON())),this.isBatchedMesh&&(s.type="BatchedMesh",s.perObjectFrustumCulled=this.perObjectFrustumCulled,s.sortObjects=this.sortObjects,s.drawRanges=this._drawRanges,s.reservedRanges=this._reservedRanges,s.geometryInfo=this._geometryInfo.map(o=>({...o,boundingBox:o.boundingBox?o.boundingBox.toJSON():void 0,boundingSphere:o.boundingSphere?o.boundingSphere.toJSON():void 0})),s.instanceInfo=this._instanceInfo.map(o=>({...o})),s.availableInstanceIds=this._availableInstanceIds.slice(),s.availableGeometryIds=this._availableGeometryIds.slice(),s.nextIndexStart=this._nextIndexStart,s.nextVertexStart=this._nextVertexStart,s.geometryCount=this._geometryCount,s.maxInstanceCount=this._maxInstanceCount,s.maxVertexCount=this._maxVertexCount,s.maxIndexCount=this._maxIndexCount,s.geometryInitialized=this._geometryInitialized,s.matricesTexture=this._matricesTexture.toJSON(e),s.indirectTexture=this._indirectTexture.toJSON(e),this._colorsTexture!==null&&(s.colorsTexture=this._colorsTexture.toJSON(e)),this.boundingSphere!==null&&(s.boundingSphere=this.boundingSphere.toJSON()),this.boundingBox!==null&&(s.boundingBox=this.boundingBox.toJSON()));function r(o,l){return o[l.uuid]===void 0&&(o[l.uuid]=l.toJSON(e)),l.uuid}if(this.isScene)this.background&&(this.background.isColor?s.background=this.background.toJSON():this.background.isTexture&&(s.background=this.background.toJSON(e).uuid)),this.environment&&this.environment.isTexture&&this.environment.isRenderTargetTexture!==!0&&(s.environment=this.environment.toJSON(e).uuid);else if(this.isMesh||this.isLine||this.isPoints){s.geometry=r(e.geometries,this.geometry);const o=this.geometry.parameters;if(o!==void 0&&o.shapes!==void 0){const l=o.shapes;if(Array.isArray(l))for(let c=0,u=l.length;c<u;c++){const f=l[c];r(e.shapes,f)}else r(e.shapes,l)}}if(this.isSkinnedMesh&&(s.bindMode=this.bindMode,s.bindMatrix=this.bindMatrix.toArray(),this.skeleton!==void 0&&(r(e.skeletons,this.skeleton),s.skeleton=this.skeleton.uuid)),this.material!==void 0)if(Array.isArray(this.material)){const o=[];for(let l=0,c=this.material.length;l<c;l++)o.push(r(e.materials,this.material[l]));s.material=o}else s.material=r(e.materials,this.material);if(this.children.length>0){s.children=[];for(let o=0;o<this.children.length;o++)s.children.push(this.children[o].toJSON(e).object)}if(this.animations.length>0){s.animations=[];for(let o=0;o<this.animations.length;o++){const l=this.animations[o];s.animations.push(r(e.animations,l))}}if(t){const o=a(e.geometries),l=a(e.materials),c=a(e.textures),u=a(e.images),f=a(e.shapes),h=a(e.skeletons),p=a(e.animations),_=a(e.nodes);o.length>0&&(i.geometries=o),l.length>0&&(i.materials=l),c.length>0&&(i.textures=c),u.length>0&&(i.images=u),f.length>0&&(i.shapes=f),h.length>0&&(i.skeletons=h),p.length>0&&(i.animations=p),_.length>0&&(i.nodes=_)}return i.object=s,i;function a(o){const l=[];for(const c in o){const u=o[c];delete u.metadata,l.push(u)}return l}}clone(e){return new this.constructor().copy(this,e)}copy(e,t=!0){if(this.name=e.name,this.up.copy(e.up),this.position.copy(e.position),this.rotation.order=e.rotation.order,this.quaternion.copy(e.quaternion),this.scale.copy(e.scale),e.pivot!==null&&(this.pivot=e.pivot.clone()),this.matrix.copy(e.matrix),this.matrixWorld.copy(e.matrixWorld),this.matrixAutoUpdate=e.matrixAutoUpdate,this.matrixWorldAutoUpdate=e.matrixWorldAutoUpdate,this.matrixWorldNeedsUpdate=e.matrixWorldNeedsUpdate,this.layers.mask=e.layers.mask,this.visible=e.visible,this.castShadow=e.castShadow,this.receiveShadow=e.receiveShadow,this.frustumCulled=e.frustumCulled,this.renderOrder=e.renderOrder,this.static=e.static,this.animations=e.animations.slice(),this.userData=JSON.parse(JSON.stringify(e.userData)),t===!0)for(let i=0;i<e.children.length;i++){const s=e.children[i];this.add(s.clone())}return this}}Mt.DEFAULT_UP=new O(0,1,0);Mt.DEFAULT_MATRIX_AUTO_UPDATE=!0;Mt.DEFAULT_MATRIX_WORLD_AUTO_UPDATE=!0;class Ks extends Mt{constructor(){super(),this.isGroup=!0,this.type="Group"}}const Fg={type:"move"};class lo{constructor(){this._targetRay=null,this._grip=null,this._hand=null}getHandSpace(){return this._hand===null&&(this._hand=new Ks,this._hand.matrixAutoUpdate=!1,this._hand.visible=!1,this._hand.joints={},this._hand.inputState={pinching:!1}),this._hand}getTargetRaySpace(){return this._targetRay===null&&(this._targetRay=new Ks,this._targetRay.matrixAutoUpdate=!1,this._targetRay.visible=!1,this._targetRay.hasLinearVelocity=!1,this._targetRay.linearVelocity=new O,this._targetRay.hasAngularVelocity=!1,this._targetRay.angularVelocity=new O),this._targetRay}getGripSpace(){return this._grip===null&&(this._grip=new Ks,this._grip.matrixAutoUpdate=!1,this._grip.visible=!1,this._grip.hasLinearVelocity=!1,this._grip.linearVelocity=new O,this._grip.hasAngularVelocity=!1,this._grip.angularVelocity=new O),this._grip}dispatchEvent(e){return this._targetRay!==null&&this._targetRay.dispatchEvent(e),this._grip!==null&&this._grip.dispatchEvent(e),this._hand!==null&&this._hand.dispatchEvent(e),this}connect(e){if(e&&e.hand){const t=this._hand;if(t)for(const i of e.hand.values())this._getHandJoint(t,i)}return this.dispatchEvent({type:"connected",data:e}),this}disconnect(e){return this.dispatchEvent({type:"disconnected",data:e}),this._targetRay!==null&&(this._targetRay.visible=!1),this._grip!==null&&(this._grip.visible=!1),this._hand!==null&&(this._hand.visible=!1),this}update(e,t,i){let s=null,r=null,a=null;const o=this._targetRay,l=this._grip,c=this._hand;if(e&&t.session.visibilityState!=="visible-blurred"){if(c&&e.hand){a=!0;for(const M of e.hand.values()){const g=t.getJointPose(M,i),d=this._getHandJoint(c,M);g!==null&&(d.matrix.fromArray(g.transform.matrix),d.matrix.decompose(d.position,d.rotation,d.scale),d.matrixWorldNeedsUpdate=!0,d.jointRadius=g.radius),d.visible=g!==null}const u=c.joints["index-finger-tip"],f=c.joints["thumb-tip"],h=u.position.distanceTo(f.position),p=.02,_=.005;c.inputState.pinching&&h>p+_?(c.inputState.pinching=!1,this.dispatchEvent({type:"pinchend",handedness:e.handedness,target:this})):!c.inputState.pinching&&h<=p-_&&(c.inputState.pinching=!0,this.dispatchEvent({type:"pinchstart",handedness:e.handedness,target:this}))}else l!==null&&e.gripSpace&&(r=t.getPose(e.gripSpace,i),r!==null&&(l.matrix.fromArray(r.transform.matrix),l.matrix.decompose(l.position,l.rotation,l.scale),l.matrixWorldNeedsUpdate=!0,r.linearVelocity?(l.hasLinearVelocity=!0,l.linearVelocity.copy(r.linearVelocity)):l.hasLinearVelocity=!1,r.angularVelocity?(l.hasAngularVelocity=!0,l.angularVelocity.copy(r.angularVelocity)):l.hasAngularVelocity=!1));o!==null&&(s=t.getPose(e.targetRaySpace,i),s===null&&r!==null&&(s=r),s!==null&&(o.matrix.fromArray(s.transform.matrix),o.matrix.decompose(o.position,o.rotation,o.scale),o.matrixWorldNeedsUpdate=!0,s.linearVelocity?(o.hasLinearVelocity=!0,o.linearVelocity.copy(s.linearVelocity)):o.hasLinearVelocity=!1,s.angularVelocity?(o.hasAngularVelocity=!0,o.angularVelocity.copy(s.angularVelocity)):o.hasAngularVelocity=!1,this.dispatchEvent(Fg)))}return o!==null&&(o.visible=s!==null),l!==null&&(l.visible=r!==null),c!==null&&(c.visible=a!==null),this}_getHandJoint(e,t){if(e.joints[t.jointName]===void 0){const i=new Ks;i.matrixAutoUpdate=!1,i.visible=!1,e.joints[t.jointName]=i,e.add(i)}return e.joints[t.jointName]}}const Xf={aliceblue:15792383,antiquewhite:16444375,aqua:65535,aquamarine:8388564,azure:15794175,beige:16119260,bisque:16770244,black:0,blanchedalmond:16772045,blue:255,blueviolet:9055202,brown:10824234,burlywood:14596231,cadetblue:6266528,chartreuse:8388352,chocolate:13789470,coral:16744272,cornflowerblue:6591981,cornsilk:16775388,crimson:14423100,cyan:65535,darkblue:139,darkcyan:35723,darkgoldenrod:12092939,darkgray:11119017,darkgreen:25600,darkgrey:11119017,darkkhaki:12433259,darkmagenta:9109643,darkolivegreen:5597999,darkorange:16747520,darkorchid:10040012,darkred:9109504,darksalmon:15308410,darkseagreen:9419919,darkslateblue:4734347,darkslategray:3100495,darkslategrey:3100495,darkturquoise:52945,darkviolet:9699539,deeppink:16716947,deepskyblue:49151,dimgray:6908265,dimgrey:6908265,dodgerblue:2003199,firebrick:11674146,floralwhite:16775920,forestgreen:2263842,fuchsia:16711935,gainsboro:14474460,ghostwhite:16316671,gold:16766720,goldenrod:14329120,gray:8421504,green:32768,greenyellow:11403055,grey:8421504,honeydew:15794160,hotpink:16738740,indianred:13458524,indigo:4915330,ivory:16777200,khaki:15787660,lavender:15132410,lavenderblush:16773365,lawngreen:8190976,lemonchiffon:16775885,lightblue:11393254,lightcoral:15761536,lightcyan:14745599,lightgoldenrodyellow:16448210,lightgray:13882323,lightgreen:9498256,lightgrey:13882323,lightpink:16758465,lightsalmon:16752762,lightseagreen:2142890,lightskyblue:8900346,lightslategray:7833753,lightslategrey:7833753,lightsteelblue:11584734,lightyellow:16777184,lime:65280,limegreen:3329330,linen:16445670,magenta:16711935,maroon:8388608,mediumaquamarine:6737322,mediumblue:205,mediumorchid:12211667,mediumpurple:9662683,mediumseagreen:3978097,mediumslateblue:8087790,mediumspringgreen:64154,mediumturquoise:4772300,mediumvioletred:13047173,midnightblue:1644912,mintcream:16121850,mistyrose:16770273,moccasin:16770229,navajowhite:16768685,navy:128,oldlace:16643558,olive:8421376,olivedrab:7048739,orange:16753920,orangered:16729344,orchid:14315734,palegoldenrod:15657130,palegreen:10025880,paleturquoise:11529966,palevioletred:14381203,papayawhip:16773077,peachpuff:16767673,peru:13468991,pink:16761035,plum:14524637,powderblue:11591910,purple:8388736,rebeccapurple:6697881,red:16711680,rosybrown:12357519,royalblue:4286945,saddlebrown:9127187,salmon:16416882,sandybrown:16032864,seagreen:3050327,seashell:16774638,sienna:10506797,silver:12632256,skyblue:8900331,slateblue:6970061,slategray:7372944,slategrey:7372944,snow:16775930,springgreen:65407,steelblue:4620980,tan:13808780,teal:32896,thistle:14204888,tomato:16737095,turquoise:4251856,violet:15631086,wheat:16113331,white:16777215,whitesmoke:16119285,yellow:16776960,yellowgreen:10145074},fi={h:0,s:0,l:0},Cr={h:0,s:0,l:0};function co(n,e,t){return t<0&&(t+=1),t>1&&(t-=1),t<1/6?n+(e-n)*6*t:t<1/2?e:t<2/3?n+(e-n)*6*(2/3-t):n}class Ze{constructor(e,t,i){return this.isColor=!0,this.r=1,this.g=1,this.b=1,this.set(e,t,i)}set(e,t,i){if(t===void 0&&i===void 0){const s=e;s&&s.isColor?this.copy(s):typeof s=="number"?this.setHex(s):typeof s=="string"&&this.setStyle(s)}else this.setRGB(e,t,i);return this}setScalar(e){return this.r=e,this.g=e,this.b=e,this}setHex(e,t=Zt){return e=Math.floor(e),this.r=(e>>16&255)/255,this.g=(e>>8&255)/255,this.b=(e&255)/255,et.colorSpaceToWorking(this,t),this}setRGB(e,t,i,s=et.workingColorSpace){return this.r=e,this.g=t,this.b=i,et.colorSpaceToWorking(this,s),this}setHSL(e,t,i,s=et.workingColorSpace){if(e=yg(e,1),t=Je(t,0,1),i=Je(i,0,1),t===0)this.r=this.g=this.b=i;else{const r=i<=.5?i*(1+t):i+t-i*t,a=2*i-r;this.r=co(a,r,e+1/3),this.g=co(a,r,e),this.b=co(a,r,e-1/3)}return et.colorSpaceToWorking(this,s),this}setStyle(e,t=Zt){function i(r){r!==void 0&&parseFloat(r)<1&&Oe("Color: Alpha component of "+e+" will be ignored.")}let s;if(s=/^(\w+)\(([^\)]*)\)/.exec(e)){let r;const a=s[1],o=s[2];switch(a){case"rgb":case"rgba":if(r=/^\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(?:,\s*(\d*\.?\d+)\s*)?$/.exec(o))return i(r[4]),this.setRGB(Math.min(255,parseInt(r[1],10))/255,Math.min(255,parseInt(r[2],10))/255,Math.min(255,parseInt(r[3],10))/255,t);if(r=/^\s*(\d+)\%\s*,\s*(\d+)\%\s*,\s*(\d+)\%\s*(?:,\s*(\d*\.?\d+)\s*)?$/.exec(o))return i(r[4]),this.setRGB(Math.min(100,parseInt(r[1],10))/100,Math.min(100,parseInt(r[2],10))/100,Math.min(100,parseInt(r[3],10))/100,t);break;case"hsl":case"hsla":if(r=/^\s*(\d*\.?\d+)\s*,\s*(\d*\.?\d+)\%\s*,\s*(\d*\.?\d+)\%\s*(?:,\s*(\d*\.?\d+)\s*)?$/.exec(o))return i(r[4]),this.setHSL(parseFloat(r[1])/360,parseFloat(r[2])/100,parseFloat(r[3])/100,t);break;default:Oe("Color: Unknown color model "+e)}}else if(s=/^\#([A-Fa-f\d]+)$/.exec(e)){const r=s[1],a=r.length;if(a===3)return this.setRGB(parseInt(r.charAt(0),16)/15,parseInt(r.charAt(1),16)/15,parseInt(r.charAt(2),16)/15,t);if(a===6)return this.setHex(parseInt(r,16),t);Oe("Color: Invalid hex color "+e)}else if(e&&e.length>0)return this.setColorName(e,t);return this}setColorName(e,t=Zt){const i=Xf[e.toLowerCase()];return i!==void 0?this.setHex(i,t):Oe("Color: Unknown color "+e),this}clone(){return new this.constructor(this.r,this.g,this.b)}copy(e){return this.r=e.r,this.g=e.g,this.b=e.b,this}copySRGBToLinear(e){return this.r=ti(e.r),this.g=ti(e.g),this.b=ti(e.b),this}copyLinearToSRGB(e){return this.r=ys(e.r),this.g=ys(e.g),this.b=ys(e.b),this}convertSRGBToLinear(){return this.copySRGBToLinear(this),this}convertLinearToSRGB(){return this.copyLinearToSRGB(this),this}getHex(e=Zt){return et.workingToColorSpace(Dt.copy(this),e),Math.round(Je(Dt.r*255,0,255))*65536+Math.round(Je(Dt.g*255,0,255))*256+Math.round(Je(Dt.b*255,0,255))}getHexString(e=Zt){return("000000"+this.getHex(e).toString(16)).slice(-6)}getHSL(e,t=et.workingColorSpace){et.workingToColorSpace(Dt.copy(this),t);const i=Dt.r,s=Dt.g,r=Dt.b,a=Math.max(i,s,r),o=Math.min(i,s,r);let l,c;const u=(o+a)/2;if(o===a)l=0,c=0;else{const f=a-o;switch(c=u<=.5?f/(a+o):f/(2-a-o),a){case i:l=(s-r)/f+(s<r?6:0);break;case s:l=(r-i)/f+2;break;case r:l=(i-s)/f+4;break}l/=6}return e.h=l,e.s=c,e.l=u,e}getRGB(e,t=et.workingColorSpace){return et.workingToColorSpace(Dt.copy(this),t),e.r=Dt.r,e.g=Dt.g,e.b=Dt.b,e}getStyle(e=Zt){et.workingToColorSpace(Dt.copy(this),e);const t=Dt.r,i=Dt.g,s=Dt.b;return e!==Zt?`color(${e} ${t.toFixed(3)} ${i.toFixed(3)} ${s.toFixed(3)})`:`rgb(${Math.round(t*255)},${Math.round(i*255)},${Math.round(s*255)})`}offsetHSL(e,t,i){return this.getHSL(fi),this.setHSL(fi.h+e,fi.s+t,fi.l+i)}add(e){return this.r+=e.r,this.g+=e.g,this.b+=e.b,this}addColors(e,t){return this.r=e.r+t.r,this.g=e.g+t.g,this.b=e.b+t.b,this}addScalar(e){return this.r+=e,this.g+=e,this.b+=e,this}sub(e){return this.r=Math.max(0,this.r-e.r),this.g=Math.max(0,this.g-e.g),this.b=Math.max(0,this.b-e.b),this}multiply(e){return this.r*=e.r,this.g*=e.g,this.b*=e.b,this}multiplyScalar(e){return this.r*=e,this.g*=e,this.b*=e,this}lerp(e,t){return this.r+=(e.r-this.r)*t,this.g+=(e.g-this.g)*t,this.b+=(e.b-this.b)*t,this}lerpColors(e,t,i){return this.r=e.r+(t.r-e.r)*i,this.g=e.g+(t.g-e.g)*i,this.b=e.b+(t.b-e.b)*i,this}lerpHSL(e,t){this.getHSL(fi),e.getHSL(Cr);const i=no(fi.h,Cr.h,t),s=no(fi.s,Cr.s,t),r=no(fi.l,Cr.l,t);return this.setHSL(i,s,r),this}setFromVector3(e){return this.r=e.x,this.g=e.y,this.b=e.z,this}applyMatrix3(e){const t=this.r,i=this.g,s=this.b,r=e.elements;return this.r=r[0]*t+r[3]*i+r[6]*s,this.g=r[1]*t+r[4]*i+r[7]*s,this.b=r[2]*t+r[5]*i+r[8]*s,this}equals(e){return e.r===this.r&&e.g===this.g&&e.b===this.b}fromArray(e,t=0){return this.r=e[t],this.g=e[t+1],this.b=e[t+2],this}toArray(e=[],t=0){return e[t]=this.r,e[t+1]=this.g,e[t+2]=this.b,e}fromBufferAttribute(e,t){return this.r=e.getX(t),this.g=e.getY(t),this.b=e.getZ(t),this}toJSON(){return this.getHex()}*[Symbol.iterator](){yield this.r,yield this.g,yield this.b}}const Dt=new Ze;Ze.NAMES=Xf;class pc{constructor(e,t=1,i=1e3){this.isFog=!0,this.name="",this.color=new Ze(e),this.near=t,this.far=i}clone(){return new pc(this.color,this.near,this.far)}toJSON(){return{type:"Fog",name:this.name,color:this.color.getHex(),near:this.near,far:this.far}}}class Og extends Mt{constructor(){super(),this.isScene=!0,this.type="Scene",this.background=null,this.environment=null,this.fog=null,this.backgroundBlurriness=0,this.backgroundIntensity=1,this.backgroundRotation=new Bn,this.environmentIntensity=1,this.environmentRotation=new Bn,this.overrideMaterial=null,typeof __THREE_DEVTOOLS__<"u"&&__THREE_DEVTOOLS__.dispatchEvent(new CustomEvent("observe",{detail:this}))}copy(e,t){return super.copy(e,t),e.background!==null&&(this.background=e.background.clone()),e.environment!==null&&(this.environment=e.environment.clone()),e.fog!==null&&(this.fog=e.fog.clone()),this.backgroundBlurriness=e.backgroundBlurriness,this.backgroundIntensity=e.backgroundIntensity,this.backgroundRotation.copy(e.backgroundRotation),this.environmentIntensity=e.environmentIntensity,this.environmentRotation.copy(e.environmentRotation),e.overrideMaterial!==null&&(this.overrideMaterial=e.overrideMaterial.clone()),this.matrixAutoUpdate=e.matrixAutoUpdate,this}toJSON(e){const t=super.toJSON(e);return this.fog!==null&&(t.object.fog=this.fog.toJSON()),this.backgroundBlurriness>0&&(t.object.backgroundBlurriness=this.backgroundBlurriness),this.backgroundIntensity!==1&&(t.object.backgroundIntensity=this.backgroundIntensity),t.object.backgroundRotation=this.backgroundRotation.toArray(),this.environmentIntensity!==1&&(t.object.environmentIntensity=this.environmentIntensity),t.object.environmentRotation=this.environmentRotation.toArray(),t}}const hn=new O,Wn=new O,uo=new O,Xn=new O,is=new O,ss=new O,Tu=new O,ho=new O,fo=new O,po=new O,mo=new _t,go=new _t,_o=new _t;class dn{constructor(e=new O,t=new O,i=new O){this.a=e,this.b=t,this.c=i}static getNormal(e,t,i,s){s.subVectors(i,t),hn.subVectors(e,t),s.cross(hn);const r=s.lengthSq();return r>0?s.multiplyScalar(1/Math.sqrt(r)):s.set(0,0,0)}static getBarycoord(e,t,i,s,r){hn.subVectors(s,t),Wn.subVectors(i,t),uo.subVectors(e,t);const a=hn.dot(hn),o=hn.dot(Wn),l=hn.dot(uo),c=Wn.dot(Wn),u=Wn.dot(uo),f=a*c-o*o;if(f===0)return r.set(0,0,0),null;const h=1/f,p=(c*l-o*u)*h,_=(a*u-o*l)*h;return r.set(1-p-_,_,p)}static containsPoint(e,t,i,s){return this.getBarycoord(e,t,i,s,Xn)===null?!1:Xn.x>=0&&Xn.y>=0&&Xn.x+Xn.y<=1}static getInterpolation(e,t,i,s,r,a,o,l){return this.getBarycoord(e,t,i,s,Xn)===null?(l.x=0,l.y=0,"z"in l&&(l.z=0),"w"in l&&(l.w=0),null):(l.setScalar(0),l.addScaledVector(r,Xn.x),l.addScaledVector(a,Xn.y),l.addScaledVector(o,Xn.z),l)}static getInterpolatedAttribute(e,t,i,s,r,a){return mo.setScalar(0),go.setScalar(0),_o.setScalar(0),mo.fromBufferAttribute(e,t),go.fromBufferAttribute(e,i),_o.fromBufferAttribute(e,s),a.setScalar(0),a.addScaledVector(mo,r.x),a.addScaledVector(go,r.y),a.addScaledVector(_o,r.z),a}static isFrontFacing(e,t,i,s){return hn.subVectors(i,t),Wn.subVectors(e,t),hn.cross(Wn).dot(s)<0}set(e,t,i){return this.a.copy(e),this.b.copy(t),this.c.copy(i),this}setFromPointsAndIndices(e,t,i,s){return this.a.copy(e[t]),this.b.copy(e[i]),this.c.copy(e[s]),this}setFromAttributeAndIndices(e,t,i,s){return this.a.fromBufferAttribute(e,t),this.b.fromBufferAttribute(e,i),this.c.fromBufferAttribute(e,s),this}clone(){return new this.constructor().copy(this)}copy(e){return this.a.copy(e.a),this.b.copy(e.b),this.c.copy(e.c),this}getArea(){return hn.subVectors(this.c,this.b),Wn.subVectors(this.a,this.b),hn.cross(Wn).length()*.5}getMidpoint(e){return e.addVectors(this.a,this.b).add(this.c).multiplyScalar(1/3)}getNormal(e){return dn.getNormal(this.a,this.b,this.c,e)}getPlane(e){return e.setFromCoplanarPoints(this.a,this.b,this.c)}getBarycoord(e,t){return dn.getBarycoord(e,this.a,this.b,this.c,t)}getInterpolation(e,t,i,s,r){return dn.getInterpolation(e,this.a,this.b,this.c,t,i,s,r)}containsPoint(e){return dn.containsPoint(e,this.a,this.b,this.c)}isFrontFacing(e){return dn.isFrontFacing(this.a,this.b,this.c,e)}intersectsBox(e){return e.intersectsTriangle(this)}closestPointToPoint(e,t){const i=this.a,s=this.b,r=this.c;let a,o;is.subVectors(s,i),ss.subVectors(r,i),ho.subVectors(e,i);const l=is.dot(ho),c=ss.dot(ho);if(l<=0&&c<=0)return t.copy(i);fo.subVectors(e,s);const u=is.dot(fo),f=ss.dot(fo);if(u>=0&&f<=u)return t.copy(s);const h=l*f-u*c;if(h<=0&&l>=0&&u<=0)return a=l/(l-u),t.copy(i).addScaledVector(is,a);po.subVectors(e,r);const p=is.dot(po),_=ss.dot(po);if(_>=0&&p<=_)return t.copy(r);const M=p*c-l*_;if(M<=0&&c>=0&&_<=0)return o=c/(c-_),t.copy(i).addScaledVector(ss,o);const g=u*_-p*f;if(g<=0&&f-u>=0&&p-_>=0)return Tu.subVectors(r,s),o=(f-u)/(f-u+(p-_)),t.copy(s).addScaledVector(Tu,o);const d=1/(g+M+h);return a=M*d,o=h*d,t.copy(i).addScaledVector(is,a).addScaledVector(ss,o)}equals(e){return e.a.equals(this.a)&&e.b.equals(this.b)&&e.c.equals(this.c)}}class qi{constructor(e=new O(1/0,1/0,1/0),t=new O(-1/0,-1/0,-1/0)){this.isBox3=!0,this.min=e,this.max=t}set(e,t){return this.min.copy(e),this.max.copy(t),this}setFromArray(e){this.makeEmpty();for(let t=0,i=e.length;t<i;t+=3)this.expandByPoint(fn.fromArray(e,t));return this}setFromBufferAttribute(e){this.makeEmpty();for(let t=0,i=e.count;t<i;t++)this.expandByPoint(fn.fromBufferAttribute(e,t));return this}setFromPoints(e){this.makeEmpty();for(let t=0,i=e.length;t<i;t++)this.expandByPoint(e[t]);return this}setFromCenterAndSize(e,t){const i=fn.copy(t).multiplyScalar(.5);return this.min.copy(e).sub(i),this.max.copy(e).add(i),this}setFromObject(e,t=!1){return this.makeEmpty(),this.expandByObject(e,t)}clone(){return new this.constructor().copy(this)}copy(e){return this.min.copy(e.min),this.max.copy(e.max),this}makeEmpty(){return this.min.x=this.min.y=this.min.z=1/0,this.max.x=this.max.y=this.max.z=-1/0,this}isEmpty(){return this.max.x<this.min.x||this.max.y<this.min.y||this.max.z<this.min.z}getCenter(e){return this.isEmpty()?e.set(0,0,0):e.addVectors(this.min,this.max).multiplyScalar(.5)}getSize(e){return this.isEmpty()?e.set(0,0,0):e.subVectors(this.max,this.min)}expandByPoint(e){return this.min.min(e),this.max.max(e),this}expandByVector(e){return this.min.sub(e),this.max.add(e),this}expandByScalar(e){return this.min.addScalar(-e),this.max.addScalar(e),this}expandByObject(e,t=!1){e.updateWorldMatrix(!1,!1);const i=e.geometry;if(i!==void 0){const r=i.getAttribute("position");if(t===!0&&r!==void 0&&e.isInstancedMesh!==!0)for(let a=0,o=r.count;a<o;a++)e.isMesh===!0?e.getVertexPosition(a,fn):fn.fromBufferAttribute(r,a),fn.applyMatrix4(e.matrixWorld),this.expandByPoint(fn);else e.boundingBox!==void 0?(e.boundingBox===null&&e.computeBoundingBox(),Rr.copy(e.boundingBox)):(i.boundingBox===null&&i.computeBoundingBox(),Rr.copy(i.boundingBox)),Rr.applyMatrix4(e.matrixWorld),this.union(Rr)}const s=e.children;for(let r=0,a=s.length;r<a;r++)this.expandByObject(s[r],t);return this}containsPoint(e){return e.x>=this.min.x&&e.x<=this.max.x&&e.y>=this.min.y&&e.y<=this.max.y&&e.z>=this.min.z&&e.z<=this.max.z}containsBox(e){return this.min.x<=e.min.x&&e.max.x<=this.max.x&&this.min.y<=e.min.y&&e.max.y<=this.max.y&&this.min.z<=e.min.z&&e.max.z<=this.max.z}getParameter(e,t){return t.set((e.x-this.min.x)/(this.max.x-this.min.x),(e.y-this.min.y)/(this.max.y-this.min.y),(e.z-this.min.z)/(this.max.z-this.min.z))}intersectsBox(e){return e.max.x>=this.min.x&&e.min.x<=this.max.x&&e.max.y>=this.min.y&&e.min.y<=this.max.y&&e.max.z>=this.min.z&&e.min.z<=this.max.z}intersectsSphere(e){return this.clampPoint(e.center,fn),fn.distanceToSquared(e.center)<=e.radius*e.radius}intersectsPlane(e){let t,i;return e.normal.x>0?(t=e.normal.x*this.min.x,i=e.normal.x*this.max.x):(t=e.normal.x*this.max.x,i=e.normal.x*this.min.x),e.normal.y>0?(t+=e.normal.y*this.min.y,i+=e.normal.y*this.max.y):(t+=e.normal.y*this.max.y,i+=e.normal.y*this.min.y),e.normal.z>0?(t+=e.normal.z*this.min.z,i+=e.normal.z*this.max.z):(t+=e.normal.z*this.max.z,i+=e.normal.z*this.min.z),t<=-e.constant&&i>=-e.constant}intersectsTriangle(e){if(this.isEmpty())return!1;this.getCenter(Bs),Pr.subVectors(this.max,Bs),rs.subVectors(e.a,Bs),as.subVectors(e.b,Bs),os.subVectors(e.c,Bs),di.subVectors(as,rs),pi.subVectors(os,as),Pi.subVectors(rs,os);let t=[0,-di.z,di.y,0,-pi.z,pi.y,0,-Pi.z,Pi.y,di.z,0,-di.x,pi.z,0,-pi.x,Pi.z,0,-Pi.x,-di.y,di.x,0,-pi.y,pi.x,0,-Pi.y,Pi.x,0];return!xo(t,rs,as,os,Pr)||(t=[1,0,0,0,1,0,0,0,1],!xo(t,rs,as,os,Pr))?!1:(Dr.crossVectors(di,pi),t=[Dr.x,Dr.y,Dr.z],xo(t,rs,as,os,Pr))}clampPoint(e,t){return t.copy(e).clamp(this.min,this.max)}distanceToPoint(e){return this.clampPoint(e,fn).distanceTo(e)}getBoundingSphere(e){return this.isEmpty()?e.makeEmpty():(this.getCenter(e.center),e.radius=this.getSize(fn).length()*.5),e}intersect(e){return this.min.max(e.min),this.max.min(e.max),this.isEmpty()&&this.makeEmpty(),this}union(e){return this.min.min(e.min),this.max.max(e.max),this}applyMatrix4(e){return this.isEmpty()?this:(Yn[0].set(this.min.x,this.min.y,this.min.z).applyMatrix4(e),Yn[1].set(this.min.x,this.min.y,this.max.z).applyMatrix4(e),Yn[2].set(this.min.x,this.max.y,this.min.z).applyMatrix4(e),Yn[3].set(this.min.x,this.max.y,this.max.z).applyMatrix4(e),Yn[4].set(this.max.x,this.min.y,this.min.z).applyMatrix4(e),Yn[5].set(this.max.x,this.min.y,this.max.z).applyMatrix4(e),Yn[6].set(this.max.x,this.max.y,this.min.z).applyMatrix4(e),Yn[7].set(this.max.x,this.max.y,this.max.z).applyMatrix4(e),this.setFromPoints(Yn),this)}translate(e){return this.min.add(e),this.max.add(e),this}equals(e){return e.min.equals(this.min)&&e.max.equals(this.max)}toJSON(){return{min:this.min.toArray(),max:this.max.toArray()}}fromJSON(e){return this.min.fromArray(e.min),this.max.fromArray(e.max),this}}const Yn=[new O,new O,new O,new O,new O,new O,new O,new O],fn=new O,Rr=new qi,rs=new O,as=new O,os=new O,di=new O,pi=new O,Pi=new O,Bs=new O,Pr=new O,Dr=new O,Di=new O;function xo(n,e,t,i,s){for(let r=0,a=n.length-3;r<=a;r+=3){Di.fromArray(n,r);const o=s.x*Math.abs(Di.x)+s.y*Math.abs(Di.y)+s.z*Math.abs(Di.z),l=e.dot(Di),c=t.dot(Di),u=i.dot(Di);if(Math.max(-Math.max(l,c,u),Math.min(l,c,u))>o)return!1}return!0}const vt=new O,Lr=new ke;let Bg=0;class xn{constructor(e,t,i=!1){if(Array.isArray(e))throw new TypeError("THREE.BufferAttribute: array should be a Typed Array.");this.isBufferAttribute=!0,Object.defineProperty(this,"id",{value:Bg++}),this.name="",this.array=e,this.itemSize=t,this.count=e!==void 0?e.length/t:0,this.normalized=i,this.usage=hu,this.updateRanges=[],this.gpuType=pn,this.version=0}onUploadCallback(){}set needsUpdate(e){e===!0&&this.version++}setUsage(e){return this.usage=e,this}addUpdateRange(e,t){this.updateRanges.push({start:e,count:t})}clearUpdateRanges(){this.updateRanges.length=0}copy(e){return this.name=e.name,this.array=new e.array.constructor(e.array),this.itemSize=e.itemSize,this.count=e.count,this.normalized=e.normalized,this.usage=e.usage,this.gpuType=e.gpuType,this}copyAt(e,t,i){e*=this.itemSize,i*=t.itemSize;for(let s=0,r=this.itemSize;s<r;s++)this.array[e+s]=t.array[i+s];return this}copyArray(e){return this.array.set(e),this}applyMatrix3(e){if(this.itemSize===2)for(let t=0,i=this.count;t<i;t++)Lr.fromBufferAttribute(this,t),Lr.applyMatrix3(e),this.setXY(t,Lr.x,Lr.y);else if(this.itemSize===3)for(let t=0,i=this.count;t<i;t++)vt.fromBufferAttribute(this,t),vt.applyMatrix3(e),this.setXYZ(t,vt.x,vt.y,vt.z);return this}applyMatrix4(e){for(let t=0,i=this.count;t<i;t++)vt.fromBufferAttribute(this,t),vt.applyMatrix4(e),this.setXYZ(t,vt.x,vt.y,vt.z);return this}applyNormalMatrix(e){for(let t=0,i=this.count;t<i;t++)vt.fromBufferAttribute(this,t),vt.applyNormalMatrix(e),this.setXYZ(t,vt.x,vt.y,vt.z);return this}transformDirection(e){for(let t=0,i=this.count;t<i;t++)vt.fromBufferAttribute(this,t),vt.transformDirection(e),this.setXYZ(t,vt.x,vt.y,vt.z);return this}set(e,t=0){return this.array.set(e,t),this}getComponent(e,t){let i=this.array[e*this.itemSize+t];return this.normalized&&(i=Fs(i,this.array)),i}setComponent(e,t,i){return this.normalized&&(i=kt(i,this.array)),this.array[e*this.itemSize+t]=i,this}getX(e){let t=this.array[e*this.itemSize];return this.normalized&&(t=Fs(t,this.array)),t}setX(e,t){return this.normalized&&(t=kt(t,this.array)),this.array[e*this.itemSize]=t,this}getY(e){let t=this.array[e*this.itemSize+1];return this.normalized&&(t=Fs(t,this.array)),t}setY(e,t){return this.normalized&&(t=kt(t,this.array)),this.array[e*this.itemSize+1]=t,this}getZ(e){let t=this.array[e*this.itemSize+2];return this.normalized&&(t=Fs(t,this.array)),t}setZ(e,t){return this.normalized&&(t=kt(t,this.array)),this.array[e*this.itemSize+2]=t,this}getW(e){let t=this.array[e*this.itemSize+3];return this.normalized&&(t=Fs(t,this.array)),t}setW(e,t){return this.normalized&&(t=kt(t,this.array)),this.array[e*this.itemSize+3]=t,this}setXY(e,t,i){return e*=this.itemSize,this.normalized&&(t=kt(t,this.array),i=kt(i,this.array)),this.array[e+0]=t,this.array[e+1]=i,this}setXYZ(e,t,i,s){return e*=this.itemSize,this.normalized&&(t=kt(t,this.array),i=kt(i,this.array),s=kt(s,this.array)),this.array[e+0]=t,this.array[e+1]=i,this.array[e+2]=s,this}setXYZW(e,t,i,s,r){return e*=this.itemSize,this.normalized&&(t=kt(t,this.array),i=kt(i,this.array),s=kt(s,this.array),r=kt(r,this.array)),this.array[e+0]=t,this.array[e+1]=i,this.array[e+2]=s,this.array[e+3]=r,this}onUpload(e){return this.onUploadCallback=e,this}clone(){return new this.constructor(this.array,this.itemSize).copy(this)}toJSON(){const e={itemSize:this.itemSize,type:this.array.constructor.name,array:Array.from(this.array),normalized:this.normalized};return this.name!==""&&(e.name=this.name),this.usage!==hu&&(e.usage=this.usage),e}}class Yf extends xn{constructor(e,t,i){super(new Uint16Array(e),t,i)}}class qf extends xn{constructor(e,t,i){super(new Uint32Array(e),t,i)}}class tn extends xn{constructor(e,t,i){super(new Float32Array(e),t,i)}}const zg=new qi,zs=new O,vo=new O;class Ps{constructor(e=new O,t=-1){this.isSphere=!0,this.center=e,this.radius=t}set(e,t){return this.center.copy(e),this.radius=t,this}setFromPoints(e,t){const i=this.center;t!==void 0?i.copy(t):zg.setFromPoints(e).getCenter(i);let s=0;for(let r=0,a=e.length;r<a;r++)s=Math.max(s,i.distanceToSquared(e[r]));return this.radius=Math.sqrt(s),this}copy(e){return this.center.copy(e.center),this.radius=e.radius,this}isEmpty(){return this.radius<0}makeEmpty(){return this.center.set(0,0,0),this.radius=-1,this}containsPoint(e){return e.distanceToSquared(this.center)<=this.radius*this.radius}distanceToPoint(e){return e.distanceTo(this.center)-this.radius}intersectsSphere(e){const t=this.radius+e.radius;return e.center.distanceToSquared(this.center)<=t*t}intersectsBox(e){return e.intersectsSphere(this)}intersectsPlane(e){return Math.abs(e.distanceToPoint(this.center))<=this.radius}clampPoint(e,t){const i=this.center.distanceToSquared(e);return t.copy(e),i>this.radius*this.radius&&(t.sub(this.center).normalize(),t.multiplyScalar(this.radius).add(this.center)),t}getBoundingBox(e){return this.isEmpty()?(e.makeEmpty(),e):(e.set(this.center,this.center),e.expandByScalar(this.radius),e)}applyMatrix4(e){return this.center.applyMatrix4(e),this.radius=this.radius*e.getMaxScaleOnAxis(),this}translate(e){return this.center.add(e),this}expandByPoint(e){if(this.isEmpty())return this.center.copy(e),this.radius=0,this;zs.subVectors(e,this.center);const t=zs.lengthSq();if(t>this.radius*this.radius){const i=Math.sqrt(t),s=(i-this.radius)*.5;this.center.addScaledVector(zs,s/i),this.radius+=s}return this}union(e){return e.isEmpty()?this:this.isEmpty()?(this.copy(e),this):(this.center.equals(e.center)===!0?this.radius=Math.max(this.radius,e.radius):(vo.subVectors(e.center,this.center).setLength(e.radius),this.expandByPoint(zs.copy(e.center).add(vo)),this.expandByPoint(zs.copy(e.center).sub(vo))),this)}equals(e){return e.center.equals(this.center)&&e.radius===this.radius}clone(){return new this.constructor().copy(this)}toJSON(){return{radius:this.radius,center:this.center.toArray()}}fromJSON(e){return this.radius=e.radius,this.center.fromArray(e.center),this}}let Hg=0;const sn=new ht,Mo=new Mt,ls=new O,$t=new qi,Hs=new qi,At=new O;class Mn extends Yi{constructor(){super(),this.isBufferGeometry=!0,Object.defineProperty(this,"id",{value:Hg++}),this.uuid=xr(),this.name="",this.type="BufferGeometry",this.index=null,this.indirect=null,this.indirectOffset=0,this.attributes={},this.morphAttributes={},this.morphTargetsRelative=!1,this.groups=[],this.boundingBox=null,this.boundingSphere=null,this.drawRange={start:0,count:1/0},this.userData={}}getIndex(){return this.index}setIndex(e){return Array.isArray(e)?this.index=new(xg(e)?qf:Yf)(e,1):this.index=e,this}setIndirect(e,t=0){return this.indirect=e,this.indirectOffset=t,this}getIndirect(){return this.indirect}getAttribute(e){return this.attributes[e]}setAttribute(e,t){return this.attributes[e]=t,this}deleteAttribute(e){return delete this.attributes[e],this}hasAttribute(e){return this.attributes[e]!==void 0}addGroup(e,t,i=0){this.groups.push({start:e,count:t,materialIndex:i})}clearGroups(){this.groups=[]}setDrawRange(e,t){this.drawRange.start=e,this.drawRange.count=t}applyMatrix4(e){const t=this.attributes.position;t!==void 0&&(t.applyMatrix4(e),t.needsUpdate=!0);const i=this.attributes.normal;if(i!==void 0){const r=new qe().getNormalMatrix(e);i.applyNormalMatrix(r),i.needsUpdate=!0}const s=this.attributes.tangent;return s!==void 0&&(s.transformDirection(e),s.needsUpdate=!0),this.boundingBox!==null&&this.computeBoundingBox(),this.boundingSphere!==null&&this.computeBoundingSphere(),this}applyQuaternion(e){return sn.makeRotationFromQuaternion(e),this.applyMatrix4(sn),this}rotateX(e){return sn.makeRotationX(e),this.applyMatrix4(sn),this}rotateY(e){return sn.makeRotationY(e),this.applyMatrix4(sn),this}rotateZ(e){return sn.makeRotationZ(e),this.applyMatrix4(sn),this}translate(e,t,i){return sn.makeTranslation(e,t,i),this.applyMatrix4(sn),this}scale(e,t,i){return sn.makeScale(e,t,i),this.applyMatrix4(sn),this}lookAt(e){return Mo.lookAt(e),Mo.updateMatrix(),this.applyMatrix4(Mo.matrix),this}center(){return this.computeBoundingBox(),this.boundingBox.getCenter(ls).negate(),this.translate(ls.x,ls.y,ls.z),this}setFromPoints(e){const t=this.getAttribute("position");if(t===void 0){const i=[];for(let s=0,r=e.length;s<r;s++){const a=e[s];i.push(a.x,a.y,a.z||0)}this.setAttribute("position",new tn(i,3))}else{const i=Math.min(e.length,t.count);for(let s=0;s<i;s++){const r=e[s];t.setXYZ(s,r.x,r.y,r.z||0)}e.length>t.count&&Oe("BufferGeometry: Buffer size too small for points data. Use .dispose() and create a new geometry."),t.needsUpdate=!0}return this}computeBoundingBox(){this.boundingBox===null&&(this.boundingBox=new qi);const e=this.attributes.position,t=this.morphAttributes.position;if(e&&e.isGLBufferAttribute){Qe("BufferGeometry.computeBoundingBox(): GLBufferAttribute requires a manual bounding box.",this),this.boundingBox.set(new O(-1/0,-1/0,-1/0),new O(1/0,1/0,1/0));return}if(e!==void 0){if(this.boundingBox.setFromBufferAttribute(e),t)for(let i=0,s=t.length;i<s;i++){const r=t[i];$t.setFromBufferAttribute(r),this.morphTargetsRelative?(At.addVectors(this.boundingBox.min,$t.min),this.boundingBox.expandByPoint(At),At.addVectors(this.boundingBox.max,$t.max),this.boundingBox.expandByPoint(At)):(this.boundingBox.expandByPoint($t.min),this.boundingBox.expandByPoint($t.max))}}else this.boundingBox.makeEmpty();(isNaN(this.boundingBox.min.x)||isNaN(this.boundingBox.min.y)||isNaN(this.boundingBox.min.z))&&Qe('BufferGeometry.computeBoundingBox(): Computed min/max have NaN values. The "position" attribute is likely to have NaN values.',this)}computeBoundingSphere(){this.boundingSphere===null&&(this.boundingSphere=new Ps);const e=this.attributes.position,t=this.morphAttributes.position;if(e&&e.isGLBufferAttribute){Qe("BufferGeometry.computeBoundingSphere(): GLBufferAttribute requires a manual bounding sphere.",this),this.boundingSphere.set(new O,1/0);return}if(e){const i=this.boundingSphere.center;if($t.setFromBufferAttribute(e),t)for(let r=0,a=t.length;r<a;r++){const o=t[r];Hs.setFromBufferAttribute(o),this.morphTargetsRelative?(At.addVectors($t.min,Hs.min),$t.expandByPoint(At),At.addVectors($t.max,Hs.max),$t.expandByPoint(At)):($t.expandByPoint(Hs.min),$t.expandByPoint(Hs.max))}$t.getCenter(i);let s=0;for(let r=0,a=e.count;r<a;r++)At.fromBufferAttribute(e,r),s=Math.max(s,i.distanceToSquared(At));if(t)for(let r=0,a=t.length;r<a;r++){const o=t[r],l=this.morphTargetsRelative;for(let c=0,u=o.count;c<u;c++)At.fromBufferAttribute(o,c),l&&(ls.fromBufferAttribute(e,c),At.add(ls)),s=Math.max(s,i.distanceToSquared(At))}this.boundingSphere.radius=Math.sqrt(s),isNaN(this.boundingSphere.radius)&&Qe('BufferGeometry.computeBoundingSphere(): Computed radius is NaN. The "position" attribute is likely to have NaN values.',this)}}computeTangents(){const e=this.index,t=this.attributes;if(e===null||t.position===void 0||t.normal===void 0||t.uv===void 0){Qe("BufferGeometry: .computeTangents() failed. Missing required attributes (index, position, normal or uv)");return}const i=t.position,s=t.normal,r=t.uv;this.hasAttribute("tangent")===!1&&this.setAttribute("tangent",new xn(new Float32Array(4*i.count),4));const a=this.getAttribute("tangent"),o=[],l=[];for(let x=0;x<i.count;x++)o[x]=new O,l[x]=new O;const c=new O,u=new O,f=new O,h=new ke,p=new ke,_=new ke,M=new O,g=new O;function d(x,T,Q){c.fromBufferAttribute(i,x),u.fromBufferAttribute(i,T),f.fromBufferAttribute(i,Q),h.fromBufferAttribute(r,x),p.fromBufferAttribute(r,T),_.fromBufferAttribute(r,Q),u.sub(c),f.sub(c),p.sub(h),_.sub(h);const D=1/(p.x*_.y-_.x*p.y);isFinite(D)&&(M.copy(u).multiplyScalar(_.y).addScaledVector(f,-p.y).multiplyScalar(D),g.copy(f).multiplyScalar(p.x).addScaledVector(u,-_.x).multiplyScalar(D),o[x].add(M),o[T].add(M),o[Q].add(M),l[x].add(g),l[T].add(g),l[Q].add(g))}let y=this.groups;y.length===0&&(y=[{start:0,count:e.count}]);for(let x=0,T=y.length;x<T;++x){const Q=y[x],D=Q.start,X=Q.count;for(let Y=D,$=D+X;Y<$;Y+=3)d(e.getX(Y+0),e.getX(Y+1),e.getX(Y+2))}const A=new O,b=new O,P=new O,L=new O;function I(x){P.fromBufferAttribute(s,x),L.copy(P);const T=o[x];A.copy(T),A.sub(P.multiplyScalar(P.dot(T))).normalize(),b.crossVectors(L,T);const D=b.dot(l[x])<0?-1:1;a.setXYZW(x,A.x,A.y,A.z,D)}for(let x=0,T=y.length;x<T;++x){const Q=y[x],D=Q.start,X=Q.count;for(let Y=D,$=D+X;Y<$;Y+=3)I(e.getX(Y+0)),I(e.getX(Y+1)),I(e.getX(Y+2))}}computeVertexNormals(){const e=this.index,t=this.getAttribute("position");if(t!==void 0){let i=this.getAttribute("normal");if(i===void 0)i=new xn(new Float32Array(t.count*3),3),this.setAttribute("normal",i);else for(let h=0,p=i.count;h<p;h++)i.setXYZ(h,0,0,0);const s=new O,r=new O,a=new O,o=new O,l=new O,c=new O,u=new O,f=new O;if(e)for(let h=0,p=e.count;h<p;h+=3){const _=e.getX(h+0),M=e.getX(h+1),g=e.getX(h+2);s.fromBufferAttribute(t,_),r.fromBufferAttribute(t,M),a.fromBufferAttribute(t,g),u.subVectors(a,r),f.subVectors(s,r),u.cross(f),o.fromBufferAttribute(i,_),l.fromBufferAttribute(i,M),c.fromBufferAttribute(i,g),o.add(u),l.add(u),c.add(u),i.setXYZ(_,o.x,o.y,o.z),i.setXYZ(M,l.x,l.y,l.z),i.setXYZ(g,c.x,c.y,c.z)}else for(let h=0,p=t.count;h<p;h+=3)s.fromBufferAttribute(t,h+0),r.fromBufferAttribute(t,h+1),a.fromBufferAttribute(t,h+2),u.subVectors(a,r),f.subVectors(s,r),u.cross(f),i.setXYZ(h+0,u.x,u.y,u.z),i.setXYZ(h+1,u.x,u.y,u.z),i.setXYZ(h+2,u.x,u.y,u.z);this.normalizeNormals(),i.needsUpdate=!0}}normalizeNormals(){const e=this.attributes.normal;for(let t=0,i=e.count;t<i;t++)At.fromBufferAttribute(e,t),At.normalize(),e.setXYZ(t,At.x,At.y,At.z)}toNonIndexed(){function e(o,l){const c=o.array,u=o.itemSize,f=o.normalized,h=new c.constructor(l.length*u);let p=0,_=0;for(let M=0,g=l.length;M<g;M++){o.isInterleavedBufferAttribute?p=l[M]*o.data.stride+o.offset:p=l[M]*u;for(let d=0;d<u;d++)h[_++]=c[p++]}return new xn(h,u,f)}if(this.index===null)return Oe("BufferGeometry.toNonIndexed(): BufferGeometry is already non-indexed."),this;const t=new Mn,i=this.index.array,s=this.attributes;for(const o in s){const l=s[o],c=e(l,i);t.setAttribute(o,c)}const r=this.morphAttributes;for(const o in r){const l=[],c=r[o];for(let u=0,f=c.length;u<f;u++){const h=c[u],p=e(h,i);l.push(p)}t.morphAttributes[o]=l}t.morphTargetsRelative=this.morphTargetsRelative;const a=this.groups;for(let o=0,l=a.length;o<l;o++){const c=a[o];t.addGroup(c.start,c.count,c.materialIndex)}return t}toJSON(){const e={metadata:{version:4.7,type:"BufferGeometry",generator:"BufferGeometry.toJSON"}};if(e.uuid=this.uuid,e.type=this.type,this.name!==""&&(e.name=this.name),Object.keys(this.userData).length>0&&(e.userData=this.userData),this.parameters!==void 0){const l=this.parameters;for(const c in l)l[c]!==void 0&&(e[c]=l[c]);return e}e.data={attributes:{}};const t=this.index;t!==null&&(e.data.index={type:t.array.constructor.name,array:Array.prototype.slice.call(t.array)});const i=this.attributes;for(const l in i){const c=i[l];e.data.attributes[l]=c.toJSON(e.data)}const s={};let r=!1;for(const l in this.morphAttributes){const c=this.morphAttributes[l],u=[];for(let f=0,h=c.length;f<h;f++){const p=c[f];u.push(p.toJSON(e.data))}u.length>0&&(s[l]=u,r=!0)}r&&(e.data.morphAttributes=s,e.data.morphTargetsRelative=this.morphTargetsRelative);const a=this.groups;a.length>0&&(e.data.groups=JSON.parse(JSON.stringify(a)));const o=this.boundingSphere;return o!==null&&(e.data.boundingSphere=o.toJSON()),e}clone(){return new this.constructor().copy(this)}copy(e){this.index=null,this.attributes={},this.morphAttributes={},this.groups=[],this.boundingBox=null,this.boundingSphere=null;const t={};this.name=e.name;const i=e.index;i!==null&&this.setIndex(i.clone());const s=e.attributes;for(const c in s){const u=s[c];this.setAttribute(c,u.clone(t))}const r=e.morphAttributes;for(const c in r){const u=[],f=r[c];for(let h=0,p=f.length;h<p;h++)u.push(f[h].clone(t));this.morphAttributes[c]=u}this.morphTargetsRelative=e.morphTargetsRelative;const a=e.groups;for(let c=0,u=a.length;c<u;c++){const f=a[c];this.addGroup(f.start,f.count,f.materialIndex)}const o=e.boundingBox;o!==null&&(this.boundingBox=o.clone());const l=e.boundingSphere;return l!==null&&(this.boundingSphere=l.clone()),this.drawRange.start=e.drawRange.start,this.drawRange.count=e.drawRange.count,this.userData=e.userData,this}dispose(){this.dispatchEvent({type:"dispose"})}}let Vg=0;class Ds extends Yi{constructor(){super(),this.isMaterial=!0,Object.defineProperty(this,"id",{value:Vg++}),this.uuid=xr(),this.name="",this.type="Material",this.blending=Ss,this.side=Ti,this.vertexColors=!1,this.opacity=1,this.transparent=!1,this.alphaHash=!1,this.blendSrc=Yo,this.blendDst=qo,this.blendEquation=Bi,this.blendSrcAlpha=null,this.blendDstAlpha=null,this.blendEquationAlpha=null,this.blendColor=new Ze(0,0,0),this.blendAlpha=0,this.depthFunc=Ts,this.depthTest=!0,this.depthWrite=!0,this.stencilWriteMask=255,this.stencilFunc=uu,this.stencilRef=0,this.stencilFuncMask=255,this.stencilFail=Ji,this.stencilZFail=Ji,this.stencilZPass=Ji,this.stencilWrite=!1,this.clippingPlanes=null,this.clipIntersection=!1,this.clipShadows=!1,this.shadowSide=null,this.colorWrite=!0,this.precision=null,this.polygonOffset=!1,this.polygonOffsetFactor=0,this.polygonOffsetUnits=0,this.dithering=!1,this.alphaToCoverage=!1,this.premultipliedAlpha=!1,this.forceSinglePass=!1,this.allowOverride=!0,this.visible=!0,this.toneMapped=!0,this.userData={},this.version=0,this._alphaTest=0}get alphaTest(){return this._alphaTest}set alphaTest(e){this._alphaTest>0!=e>0&&this.version++,this._alphaTest=e}onBeforeRender(){}onBeforeCompile(){}customProgramCacheKey(){return this.onBeforeCompile.toString()}setValues(e){if(e!==void 0)for(const t in e){const i=e[t];if(i===void 0){Oe(`Material: parameter '${t}' has value of undefined.`);continue}const s=this[t];if(s===void 0){Oe(`Material: '${t}' is not a property of THREE.${this.type}.`);continue}s&&s.isColor?s.set(i):s&&s.isVector3&&i&&i.isVector3?s.copy(i):this[t]=i}}toJSON(e){const t=e===void 0||typeof e=="string";t&&(e={textures:{},images:{}});const i={metadata:{version:4.7,type:"Material",generator:"Material.toJSON"}};i.uuid=this.uuid,i.type=this.type,this.name!==""&&(i.name=this.name),this.color&&this.color.isColor&&(i.color=this.color.getHex()),this.roughness!==void 0&&(i.roughness=this.roughness),this.metalness!==void 0&&(i.metalness=this.metalness),this.sheen!==void 0&&(i.sheen=this.sheen),this.sheenColor&&this.sheenColor.isColor&&(i.sheenColor=this.sheenColor.getHex()),this.sheenRoughness!==void 0&&(i.sheenRoughness=this.sheenRoughness),this.emissive&&this.emissive.isColor&&(i.emissive=this.emissive.getHex()),this.emissiveIntensity!==void 0&&this.emissiveIntensity!==1&&(i.emissiveIntensity=this.emissiveIntensity),this.specular&&this.specular.isColor&&(i.specular=this.specular.getHex()),this.specularIntensity!==void 0&&(i.specularIntensity=this.specularIntensity),this.specularColor&&this.specularColor.isColor&&(i.specularColor=this.specularColor.getHex()),this.shininess!==void 0&&(i.shininess=this.shininess),this.clearcoat!==void 0&&(i.clearcoat=this.clearcoat),this.clearcoatRoughness!==void 0&&(i.clearcoatRoughness=this.clearcoatRoughness),this.clearcoatMap&&this.clearcoatMap.isTexture&&(i.clearcoatMap=this.clearcoatMap.toJSON(e).uuid),this.clearcoatRoughnessMap&&this.clearcoatRoughnessMap.isTexture&&(i.clearcoatRoughnessMap=this.clearcoatRoughnessMap.toJSON(e).uuid),this.clearcoatNormalMap&&this.clearcoatNormalMap.isTexture&&(i.clearcoatNormalMap=this.clearcoatNormalMap.toJSON(e).uuid,i.clearcoatNormalScale=this.clearcoatNormalScale.toArray()),this.sheenColorMap&&this.sheenColorMap.isTexture&&(i.sheenColorMap=this.sheenColorMap.toJSON(e).uuid),this.sheenRoughnessMap&&this.sheenRoughnessMap.isTexture&&(i.sheenRoughnessMap=this.sheenRoughnessMap.toJSON(e).uuid),this.dispersion!==void 0&&(i.dispersion=this.dispersion),this.iridescence!==void 0&&(i.iridescence=this.iridescence),this.iridescenceIOR!==void 0&&(i.iridescenceIOR=this.iridescenceIOR),this.iridescenceThicknessRange!==void 0&&(i.iridescenceThicknessRange=this.iridescenceThicknessRange),this.iridescenceMap&&this.iridescenceMap.isTexture&&(i.iridescenceMap=this.iridescenceMap.toJSON(e).uuid),this.iridescenceThicknessMap&&this.iridescenceThicknessMap.isTexture&&(i.iridescenceThicknessMap=this.iridescenceThicknessMap.toJSON(e).uuid),this.anisotropy!==void 0&&(i.anisotropy=this.anisotropy),this.anisotropyRotation!==void 0&&(i.anisotropyRotation=this.anisotropyRotation),this.anisotropyMap&&this.anisotropyMap.isTexture&&(i.anisotropyMap=this.anisotropyMap.toJSON(e).uuid),this.map&&this.map.isTexture&&(i.map=this.map.toJSON(e).uuid),this.matcap&&this.matcap.isTexture&&(i.matcap=this.matcap.toJSON(e).uuid),this.alphaMap&&this.alphaMap.isTexture&&(i.alphaMap=this.alphaMap.toJSON(e).uuid),this.lightMap&&this.lightMap.isTexture&&(i.lightMap=this.lightMap.toJSON(e).uuid,i.lightMapIntensity=this.lightMapIntensity),this.aoMap&&this.aoMap.isTexture&&(i.aoMap=this.aoMap.toJSON(e).uuid,i.aoMapIntensity=this.aoMapIntensity),this.bumpMap&&this.bumpMap.isTexture&&(i.bumpMap=this.bumpMap.toJSON(e).uuid,i.bumpScale=this.bumpScale),this.normalMap&&this.normalMap.isTexture&&(i.normalMap=this.normalMap.toJSON(e).uuid,i.normalMapType=this.normalMapType,i.normalScale=this.normalScale.toArray()),this.displacementMap&&this.displacementMap.isTexture&&(i.displacementMap=this.displacementMap.toJSON(e).uuid,i.displacementScale=this.displacementScale,i.displacementBias=this.displacementBias),this.roughnessMap&&this.roughnessMap.isTexture&&(i.roughnessMap=this.roughnessMap.toJSON(e).uuid),this.metalnessMap&&this.metalnessMap.isTexture&&(i.metalnessMap=this.metalnessMap.toJSON(e).uuid),this.emissiveMap&&this.emissiveMap.isTexture&&(i.emissiveMap=this.emissiveMap.toJSON(e).uuid),this.specularMap&&this.specularMap.isTexture&&(i.specularMap=this.specularMap.toJSON(e).uuid),this.specularIntensityMap&&this.specularIntensityMap.isTexture&&(i.specularIntensityMap=this.specularIntensityMap.toJSON(e).uuid),this.specularColorMap&&this.specularColorMap.isTexture&&(i.specularColorMap=this.specularColorMap.toJSON(e).uuid),this.envMap&&this.envMap.isTexture&&(i.envMap=this.envMap.toJSON(e).uuid,this.combine!==void 0&&(i.combine=this.combine)),this.envMapRotation!==void 0&&(i.envMapRotation=this.envMapRotation.toArray()),this.envMapIntensity!==void 0&&(i.envMapIntensity=this.envMapIntensity),this.reflectivity!==void 0&&(i.reflectivity=this.reflectivity),this.refractionRatio!==void 0&&(i.refractionRatio=this.refractionRatio),this.gradientMap&&this.gradientMap.isTexture&&(i.gradientMap=this.gradientMap.toJSON(e).uuid),this.transmission!==void 0&&(i.transmission=this.transmission),this.transmissionMap&&this.transmissionMap.isTexture&&(i.transmissionMap=this.transmissionMap.toJSON(e).uuid),this.thickness!==void 0&&(i.thickness=this.thickness),this.thicknessMap&&this.thicknessMap.isTexture&&(i.thicknessMap=this.thicknessMap.toJSON(e).uuid),this.attenuationDistance!==void 0&&this.attenuationDistance!==1/0&&(i.attenuationDistance=this.attenuationDistance),this.attenuationColor!==void 0&&(i.attenuationColor=this.attenuationColor.getHex()),this.size!==void 0&&(i.size=this.size),this.shadowSide!==null&&(i.shadowSide=this.shadowSide),this.sizeAttenuation!==void 0&&(i.sizeAttenuation=this.sizeAttenuation),this.blending!==Ss&&(i.blending=this.blending),this.side!==Ti&&(i.side=this.side),this.vertexColors===!0&&(i.vertexColors=!0),this.opacity<1&&(i.opacity=this.opacity),this.transparent===!0&&(i.transparent=!0),this.blendSrc!==Yo&&(i.blendSrc=this.blendSrc),this.blendDst!==qo&&(i.blendDst=this.blendDst),this.blendEquation!==Bi&&(i.blendEquation=this.blendEquation),this.blendSrcAlpha!==null&&(i.blendSrcAlpha=this.blendSrcAlpha),this.blendDstAlpha!==null&&(i.blendDstAlpha=this.blendDstAlpha),this.blendEquationAlpha!==null&&(i.blendEquationAlpha=this.blendEquationAlpha),this.blendColor&&this.blendColor.isColor&&(i.blendColor=this.blendColor.getHex()),this.blendAlpha!==0&&(i.blendAlpha=this.blendAlpha),this.depthFunc!==Ts&&(i.depthFunc=this.depthFunc),this.depthTest===!1&&(i.depthTest=this.depthTest),this.depthWrite===!1&&(i.depthWrite=this.depthWrite),this.colorWrite===!1&&(i.colorWrite=this.colorWrite),this.stencilWriteMask!==255&&(i.stencilWriteMask=this.stencilWriteMask),this.stencilFunc!==uu&&(i.stencilFunc=this.stencilFunc),this.stencilRef!==0&&(i.stencilRef=this.stencilRef),this.stencilFuncMask!==255&&(i.stencilFuncMask=this.stencilFuncMask),this.stencilFail!==Ji&&(i.stencilFail=this.stencilFail),this.stencilZFail!==Ji&&(i.stencilZFail=this.stencilZFail),this.stencilZPass!==Ji&&(i.stencilZPass=this.stencilZPass),this.stencilWrite===!0&&(i.stencilWrite=this.stencilWrite),this.rotation!==void 0&&this.rotation!==0&&(i.rotation=this.rotation),this.polygonOffset===!0&&(i.polygonOffset=!0),this.polygonOffsetFactor!==0&&(i.polygonOffsetFactor=this.polygonOffsetFactor),this.polygonOffsetUnits!==0&&(i.polygonOffsetUnits=this.polygonOffsetUnits),this.linewidth!==void 0&&this.linewidth!==1&&(i.linewidth=this.linewidth),this.dashSize!==void 0&&(i.dashSize=this.dashSize),this.gapSize!==void 0&&(i.gapSize=this.gapSize),this.scale!==void 0&&(i.scale=this.scale),this.dithering===!0&&(i.dithering=!0),this.alphaTest>0&&(i.alphaTest=this.alphaTest),this.alphaHash===!0&&(i.alphaHash=!0),this.alphaToCoverage===!0&&(i.alphaToCoverage=!0),this.premultipliedAlpha===!0&&(i.premultipliedAlpha=!0),this.forceSinglePass===!0&&(i.forceSinglePass=!0),this.allowOverride===!1&&(i.allowOverride=!1),this.wireframe===!0&&(i.wireframe=!0),this.wireframeLinewidth>1&&(i.wireframeLinewidth=this.wireframeLinewidth),this.wireframeLinecap!=="round"&&(i.wireframeLinecap=this.wireframeLinecap),this.wireframeLinejoin!=="round"&&(i.wireframeLinejoin=this.wireframeLinejoin),this.flatShading===!0&&(i.flatShading=!0),this.visible===!1&&(i.visible=!1),this.toneMapped===!1&&(i.toneMapped=!1),this.fog===!1&&(i.fog=!1),Object.keys(this.userData).length>0&&(i.userData=this.userData);function s(r){const a=[];for(const o in r){const l=r[o];delete l.metadata,a.push(l)}return a}if(t){const r=s(e.textures),a=s(e.images);r.length>0&&(i.textures=r),a.length>0&&(i.images=a)}return i}clone(){return new this.constructor().copy(this)}copy(e){this.name=e.name,this.blending=e.blending,this.side=e.side,this.vertexColors=e.vertexColors,this.opacity=e.opacity,this.transparent=e.transparent,this.blendSrc=e.blendSrc,this.blendDst=e.blendDst,this.blendEquation=e.blendEquation,this.blendSrcAlpha=e.blendSrcAlpha,this.blendDstAlpha=e.blendDstAlpha,this.blendEquationAlpha=e.blendEquationAlpha,this.blendColor.copy(e.blendColor),this.blendAlpha=e.blendAlpha,this.depthFunc=e.depthFunc,this.depthTest=e.depthTest,this.depthWrite=e.depthWrite,this.stencilWriteMask=e.stencilWriteMask,this.stencilFunc=e.stencilFunc,this.stencilRef=e.stencilRef,this.stencilFuncMask=e.stencilFuncMask,this.stencilFail=e.stencilFail,this.stencilZFail=e.stencilZFail,this.stencilZPass=e.stencilZPass,this.stencilWrite=e.stencilWrite;const t=e.clippingPlanes;let i=null;if(t!==null){const s=t.length;i=new Array(s);for(let r=0;r!==s;++r)i[r]=t[r].clone()}return this.clippingPlanes=i,this.clipIntersection=e.clipIntersection,this.clipShadows=e.clipShadows,this.shadowSide=e.shadowSide,this.colorWrite=e.colorWrite,this.precision=e.precision,this.polygonOffset=e.polygonOffset,this.polygonOffsetFactor=e.polygonOffsetFactor,this.polygonOffsetUnits=e.polygonOffsetUnits,this.dithering=e.dithering,this.alphaTest=e.alphaTest,this.alphaHash=e.alphaHash,this.alphaToCoverage=e.alphaToCoverage,this.premultipliedAlpha=e.premultipliedAlpha,this.forceSinglePass=e.forceSinglePass,this.allowOverride=e.allowOverride,this.visible=e.visible,this.toneMapped=e.toneMapped,this.userData=JSON.parse(JSON.stringify(e.userData)),this}dispose(){this.dispatchEvent({type:"dispose"})}set needsUpdate(e){e===!0&&this.version++}}const qn=new O,So=new O,Ir=new O,mi=new O,yo=new O,Ur=new O,bo=new O;class Na{constructor(e=new O,t=new O(0,0,-1)){this.origin=e,this.direction=t}set(e,t){return this.origin.copy(e),this.direction.copy(t),this}copy(e){return this.origin.copy(e.origin),this.direction.copy(e.direction),this}at(e,t){return t.copy(this.origin).addScaledVector(this.direction,e)}lookAt(e){return this.direction.copy(e).sub(this.origin).normalize(),this}recast(e){return this.origin.copy(this.at(e,qn)),this}closestPointToPoint(e,t){t.subVectors(e,this.origin);const i=t.dot(this.direction);return i<0?t.copy(this.origin):t.copy(this.origin).addScaledVector(this.direction,i)}distanceToPoint(e){return Math.sqrt(this.distanceSqToPoint(e))}distanceSqToPoint(e){const t=qn.subVectors(e,this.origin).dot(this.direction);return t<0?this.origin.distanceToSquared(e):(qn.copy(this.origin).addScaledVector(this.direction,t),qn.distanceToSquared(e))}distanceSqToSegment(e,t,i,s){So.copy(e).add(t).multiplyScalar(.5),Ir.copy(t).sub(e).normalize(),mi.copy(this.origin).sub(So);const r=e.distanceTo(t)*.5,a=-this.direction.dot(Ir),o=mi.dot(this.direction),l=-mi.dot(Ir),c=mi.lengthSq(),u=Math.abs(1-a*a);let f,h,p,_;if(u>0)if(f=a*l-o,h=a*o-l,_=r*u,f>=0)if(h>=-_)if(h<=_){const M=1/u;f*=M,h*=M,p=f*(f+a*h+2*o)+h*(a*f+h+2*l)+c}else h=r,f=Math.max(0,-(a*h+o)),p=-f*f+h*(h+2*l)+c;else h=-r,f=Math.max(0,-(a*h+o)),p=-f*f+h*(h+2*l)+c;else h<=-_?(f=Math.max(0,-(-a*r+o)),h=f>0?-r:Math.min(Math.max(-r,-l),r),p=-f*f+h*(h+2*l)+c):h<=_?(f=0,h=Math.min(Math.max(-r,-l),r),p=h*(h+2*l)+c):(f=Math.max(0,-(a*r+o)),h=f>0?r:Math.min(Math.max(-r,-l),r),p=-f*f+h*(h+2*l)+c);else h=a>0?-r:r,f=Math.max(0,-(a*h+o)),p=-f*f+h*(h+2*l)+c;return i&&i.copy(this.origin).addScaledVector(this.direction,f),s&&s.copy(So).addScaledVector(Ir,h),p}intersectSphere(e,t){qn.subVectors(e.center,this.origin);const i=qn.dot(this.direction),s=qn.dot(qn)-i*i,r=e.radius*e.radius;if(s>r)return null;const a=Math.sqrt(r-s),o=i-a,l=i+a;return l<0?null:o<0?this.at(l,t):this.at(o,t)}intersectsSphere(e){return e.radius<0?!1:this.distanceSqToPoint(e.center)<=e.radius*e.radius}distanceToPlane(e){const t=e.normal.dot(this.direction);if(t===0)return e.distanceToPoint(this.origin)===0?0:null;const i=-(this.origin.dot(e.normal)+e.constant)/t;return i>=0?i:null}intersectPlane(e,t){const i=this.distanceToPlane(e);return i===null?null:this.at(i,t)}intersectsPlane(e){const t=e.distanceToPoint(this.origin);return t===0||e.normal.dot(this.direction)*t<0}intersectBox(e,t){let i,s,r,a,o,l;const c=1/this.direction.x,u=1/this.direction.y,f=1/this.direction.z,h=this.origin;return c>=0?(i=(e.min.x-h.x)*c,s=(e.max.x-h.x)*c):(i=(e.max.x-h.x)*c,s=(e.min.x-h.x)*c),u>=0?(r=(e.min.y-h.y)*u,a=(e.max.y-h.y)*u):(r=(e.max.y-h.y)*u,a=(e.min.y-h.y)*u),i>a||r>s||((r>i||isNaN(i))&&(i=r),(a<s||isNaN(s))&&(s=a),f>=0?(o=(e.min.z-h.z)*f,l=(e.max.z-h.z)*f):(o=(e.max.z-h.z)*f,l=(e.min.z-h.z)*f),i>l||o>s)||((o>i||i!==i)&&(i=o),(l<s||s!==s)&&(s=l),s<0)?null:this.at(i>=0?i:s,t)}intersectsBox(e){return this.intersectBox(e,qn)!==null}intersectTriangle(e,t,i,s,r){yo.subVectors(t,e),Ur.subVectors(i,e),bo.crossVectors(yo,Ur);let a=this.direction.dot(bo),o;if(a>0){if(s)return null;o=1}else if(a<0)o=-1,a=-a;else return null;mi.subVectors(this.origin,e);const l=o*this.direction.dot(Ur.crossVectors(mi,Ur));if(l<0)return null;const c=o*this.direction.dot(yo.cross(mi));if(c<0||l+c>a)return null;const u=-o*mi.dot(bo);return u<0?null:this.at(u/a,r)}applyMatrix4(e){return this.origin.applyMatrix4(e),this.direction.transformDirection(e),this}equals(e){return e.origin.equals(this.origin)&&e.direction.equals(this.direction)}clone(){return new this.constructor().copy(this)}}class jf extends Ds{constructor(e){super(),this.isMeshBasicMaterial=!0,this.type="MeshBasicMaterial",this.color=new Ze(16777215),this.map=null,this.lightMap=null,this.lightMapIntensity=1,this.aoMap=null,this.aoMapIntensity=1,this.specularMap=null,this.alphaMap=null,this.envMap=null,this.envMapRotation=new Bn,this.combine=wf,this.reflectivity=1,this.refractionRatio=.98,this.wireframe=!1,this.wireframeLinewidth=1,this.wireframeLinecap="round",this.wireframeLinejoin="round",this.fog=!0,this.setValues(e)}copy(e){return super.copy(e),this.color.copy(e.color),this.map=e.map,this.lightMap=e.lightMap,this.lightMapIntensity=e.lightMapIntensity,this.aoMap=e.aoMap,this.aoMapIntensity=e.aoMapIntensity,this.specularMap=e.specularMap,this.alphaMap=e.alphaMap,this.envMap=e.envMap,this.envMapRotation.copy(e.envMapRotation),this.combine=e.combine,this.reflectivity=e.reflectivity,this.refractionRatio=e.refractionRatio,this.wireframe=e.wireframe,this.wireframeLinewidth=e.wireframeLinewidth,this.wireframeLinecap=e.wireframeLinecap,this.wireframeLinejoin=e.wireframeLinejoin,this.fog=e.fog,this}}const Au=new ht,Li=new Na,Nr=new Ps,wu=new O,Fr=new O,Or=new O,Br=new O,Eo=new O,zr=new O,Cu=new O,Hr=new O;class qt extends Mt{constructor(e=new Mn,t=new jf){super(),this.isMesh=!0,this.type="Mesh",this.geometry=e,this.material=t,this.morphTargetDictionary=void 0,this.morphTargetInfluences=void 0,this.count=1,this.updateMorphTargets()}copy(e,t){return super.copy(e,t),e.morphTargetInfluences!==void 0&&(this.morphTargetInfluences=e.morphTargetInfluences.slice()),e.morphTargetDictionary!==void 0&&(this.morphTargetDictionary=Object.assign({},e.morphTargetDictionary)),this.material=Array.isArray(e.material)?e.material.slice():e.material,this.geometry=e.geometry,this}updateMorphTargets(){const t=this.geometry.morphAttributes,i=Object.keys(t);if(i.length>0){const s=t[i[0]];if(s!==void 0){this.morphTargetInfluences=[],this.morphTargetDictionary={};for(let r=0,a=s.length;r<a;r++){const o=s[r].name||String(r);this.morphTargetInfluences.push(0),this.morphTargetDictionary[o]=r}}}}getVertexPosition(e,t){const i=this.geometry,s=i.attributes.position,r=i.morphAttributes.position,a=i.morphTargetsRelative;t.fromBufferAttribute(s,e);const o=this.morphTargetInfluences;if(r&&o){zr.set(0,0,0);for(let l=0,c=r.length;l<c;l++){const u=o[l],f=r[l];u!==0&&(Eo.fromBufferAttribute(f,e),a?zr.addScaledVector(Eo,u):zr.addScaledVector(Eo.sub(t),u))}t.add(zr)}return t}raycast(e,t){const i=this.geometry,s=this.material,r=this.matrixWorld;s!==void 0&&(i.boundingSphere===null&&i.computeBoundingSphere(),Nr.copy(i.boundingSphere),Nr.applyMatrix4(r),Li.copy(e.ray).recast(e.near),!(Nr.containsPoint(Li.origin)===!1&&(Li.intersectSphere(Nr,wu)===null||Li.origin.distanceToSquared(wu)>(e.far-e.near)**2))&&(Au.copy(r).invert(),Li.copy(e.ray).applyMatrix4(Au),!(i.boundingBox!==null&&Li.intersectsBox(i.boundingBox)===!1)&&this._computeIntersections(e,t,Li)))}_computeIntersections(e,t,i){let s;const r=this.geometry,a=this.material,o=r.index,l=r.attributes.position,c=r.attributes.uv,u=r.attributes.uv1,f=r.attributes.normal,h=r.groups,p=r.drawRange;if(o!==null)if(Array.isArray(a))for(let _=0,M=h.length;_<M;_++){const g=h[_],d=a[g.materialIndex],y=Math.max(g.start,p.start),A=Math.min(o.count,Math.min(g.start+g.count,p.start+p.count));for(let b=y,P=A;b<P;b+=3){const L=o.getX(b),I=o.getX(b+1),x=o.getX(b+2);s=Vr(this,d,e,i,c,u,f,L,I,x),s&&(s.faceIndex=Math.floor(b/3),s.face.materialIndex=g.materialIndex,t.push(s))}}else{const _=Math.max(0,p.start),M=Math.min(o.count,p.start+p.count);for(let g=_,d=M;g<d;g+=3){const y=o.getX(g),A=o.getX(g+1),b=o.getX(g+2);s=Vr(this,a,e,i,c,u,f,y,A,b),s&&(s.faceIndex=Math.floor(g/3),t.push(s))}}else if(l!==void 0)if(Array.isArray(a))for(let _=0,M=h.length;_<M;_++){const g=h[_],d=a[g.materialIndex],y=Math.max(g.start,p.start),A=Math.min(l.count,Math.min(g.start+g.count,p.start+p.count));for(let b=y,P=A;b<P;b+=3){const L=b,I=b+1,x=b+2;s=Vr(this,d,e,i,c,u,f,L,I,x),s&&(s.faceIndex=Math.floor(b/3),s.face.materialIndex=g.materialIndex,t.push(s))}}else{const _=Math.max(0,p.start),M=Math.min(l.count,p.start+p.count);for(let g=_,d=M;g<d;g+=3){const y=g,A=g+1,b=g+2;s=Vr(this,a,e,i,c,u,f,y,A,b),s&&(s.faceIndex=Math.floor(g/3),t.push(s))}}}}function kg(n,e,t,i,s,r,a,o){let l;if(e.side===Yt?l=i.intersectTriangle(a,r,s,!0,o):l=i.intersectTriangle(s,r,a,e.side===Ti,o),l===null)return null;Hr.copy(o),Hr.applyMatrix4(n.matrixWorld);const c=t.ray.origin.distanceTo(Hr);return c<t.near||c>t.far?null:{distance:c,point:Hr.clone(),object:n}}function Vr(n,e,t,i,s,r,a,o,l,c){n.getVertexPosition(o,Fr),n.getVertexPosition(l,Or),n.getVertexPosition(c,Br);const u=kg(n,e,t,i,Fr,Or,Br,Cu);if(u){const f=new O;dn.getBarycoord(Cu,Fr,Or,Br,f),s&&(u.uv=dn.getInterpolatedAttribute(s,o,l,c,f,new ke)),r&&(u.uv1=dn.getInterpolatedAttribute(r,o,l,c,f,new ke)),a&&(u.normal=dn.getInterpolatedAttribute(a,o,l,c,f,new O),u.normal.dot(i.direction)>0&&u.normal.multiplyScalar(-1));const h={a:o,b:l,c,normal:new O,materialIndex:0};dn.getNormal(Fr,Or,Br,h.normal),u.face=h,u.barycoord=f}return u}class Kf extends Ft{constructor(e=null,t=1,i=1,s,r,a,o,l,c=Ct,u=Ct,f,h){super(null,a,o,l,c,u,s,r,f,h),this.isDataTexture=!0,this.image={data:e,width:t,height:i},this.generateMipmaps=!1,this.flipY=!1,this.unpackAlignment=1}}class Ru extends xn{constructor(e,t,i,s=1){super(e,t,i),this.isInstancedBufferAttribute=!0,this.meshPerAttribute=s}copy(e){return super.copy(e),this.meshPerAttribute=e.meshPerAttribute,this}toJSON(){const e=super.toJSON();return e.meshPerAttribute=this.meshPerAttribute,e.isInstancedBufferAttribute=!0,e}}const cs=new ht,Pu=new ht,kr=[],Du=new qi,Gg=new ht,Vs=new qt,ks=new Ps;class Lu extends qt{constructor(e,t,i){super(e,t),this.isInstancedMesh=!0,this.instanceMatrix=new Ru(new Float32Array(i*16),16),this.previousInstanceMatrix=null,this.instanceColor=null,this.morphTexture=null,this.count=i,this.boundingBox=null,this.boundingSphere=null;for(let s=0;s<i;s++)this.setMatrixAt(s,Gg)}computeBoundingBox(){const e=this.geometry,t=this.count;this.boundingBox===null&&(this.boundingBox=new qi),e.boundingBox===null&&e.computeBoundingBox(),this.boundingBox.makeEmpty();for(let i=0;i<t;i++)this.getMatrixAt(i,cs),Du.copy(e.boundingBox).applyMatrix4(cs),this.boundingBox.union(Du)}computeBoundingSphere(){const e=this.geometry,t=this.count;this.boundingSphere===null&&(this.boundingSphere=new Ps),e.boundingSphere===null&&e.computeBoundingSphere(),this.boundingSphere.makeEmpty();for(let i=0;i<t;i++)this.getMatrixAt(i,cs),ks.copy(e.boundingSphere).applyMatrix4(cs),this.boundingSphere.union(ks)}copy(e,t){return super.copy(e,t),this.instanceMatrix.copy(e.instanceMatrix),e.previousInstanceMatrix!==null&&(this.previousInstanceMatrix=e.previousInstanceMatrix.clone()),e.morphTexture!==null&&(this.morphTexture=e.morphTexture.clone()),e.instanceColor!==null&&(this.instanceColor=e.instanceColor.clone()),this.count=e.count,e.boundingBox!==null&&(this.boundingBox=e.boundingBox.clone()),e.boundingSphere!==null&&(this.boundingSphere=e.boundingSphere.clone()),this}getColorAt(e,t){t.fromArray(this.instanceColor.array,e*3)}getMatrixAt(e,t){t.fromArray(this.instanceMatrix.array,e*16)}getMorphAt(e,t){const i=t.morphTargetInfluences,s=this.morphTexture.source.data.data,r=i.length+1,a=e*r+1;for(let o=0;o<i.length;o++)i[o]=s[a+o]}raycast(e,t){const i=this.matrixWorld,s=this.count;if(Vs.geometry=this.geometry,Vs.material=this.material,Vs.material!==void 0&&(this.boundingSphere===null&&this.computeBoundingSphere(),ks.copy(this.boundingSphere),ks.applyMatrix4(i),e.ray.intersectsSphere(ks)!==!1))for(let r=0;r<s;r++){this.getMatrixAt(r,cs),Pu.multiplyMatrices(i,cs),Vs.matrixWorld=Pu,Vs.raycast(e,kr);for(let a=0,o=kr.length;a<o;a++){const l=kr[a];l.instanceId=r,l.object=this,t.push(l)}kr.length=0}}setColorAt(e,t){this.instanceColor===null&&(this.instanceColor=new Ru(new Float32Array(this.instanceMatrix.count*3).fill(1),3)),t.toArray(this.instanceColor.array,e*3)}setMatrixAt(e,t){t.toArray(this.instanceMatrix.array,e*16)}setMorphAt(e,t){const i=t.morphTargetInfluences,s=i.length+1;this.morphTexture===null&&(this.morphTexture=new Kf(new Float32Array(s*this.count),s,this.count,ac,pn));const r=this.morphTexture.source.data.data;let a=0;for(let c=0;c<i.length;c++)a+=i[c];const o=this.geometry.morphTargetsRelative?1:1-a,l=s*e;r[l]=o,r.set(i,l+1)}updateMorphTargets(){}dispose(){this.dispatchEvent({type:"dispose"}),this.morphTexture!==null&&(this.morphTexture.dispose(),this.morphTexture=null)}}const To=new O,Wg=new O,Xg=new qe;class vi{constructor(e=new O(1,0,0),t=0){this.isPlane=!0,this.normal=e,this.constant=t}set(e,t){return this.normal.copy(e),this.constant=t,this}setComponents(e,t,i,s){return this.normal.set(e,t,i),this.constant=s,this}setFromNormalAndCoplanarPoint(e,t){return this.normal.copy(e),this.constant=-t.dot(this.normal),this}setFromCoplanarPoints(e,t,i){const s=To.subVectors(i,t).cross(Wg.subVectors(e,t)).normalize();return this.setFromNormalAndCoplanarPoint(s,e),this}copy(e){return this.normal.copy(e.normal),this.constant=e.constant,this}normalize(){const e=1/this.normal.length();return this.normal.multiplyScalar(e),this.constant*=e,this}negate(){return this.constant*=-1,this.normal.negate(),this}distanceToPoint(e){return this.normal.dot(e)+this.constant}distanceToSphere(e){return this.distanceToPoint(e.center)-e.radius}projectPoint(e,t){return t.copy(e).addScaledVector(this.normal,-this.distanceToPoint(e))}intersectLine(e,t){const i=e.delta(To),s=this.normal.dot(i);if(s===0)return this.distanceToPoint(e.start)===0?t.copy(e.start):null;const r=-(e.start.dot(this.normal)+this.constant)/s;return r<0||r>1?null:t.copy(e.start).addScaledVector(i,r)}intersectsLine(e){const t=this.distanceToPoint(e.start),i=this.distanceToPoint(e.end);return t<0&&i>0||i<0&&t>0}intersectsBox(e){return e.intersectsPlane(this)}intersectsSphere(e){return e.intersectsPlane(this)}coplanarPoint(e){return e.copy(this.normal).multiplyScalar(-this.constant)}applyMatrix4(e,t){const i=t||Xg.getNormalMatrix(e),s=this.coplanarPoint(To).applyMatrix4(e),r=this.normal.applyMatrix3(i).normalize();return this.constant=-s.dot(r),this}translate(e){return this.constant-=e.dot(this.normal),this}equals(e){return e.normal.equals(this.normal)&&e.constant===this.constant}clone(){return new this.constructor().copy(this)}}const Ii=new Ps,Yg=new ke(.5,.5),Gr=new O;class mc{constructor(e=new vi,t=new vi,i=new vi,s=new vi,r=new vi,a=new vi){this.planes=[e,t,i,s,r,a]}set(e,t,i,s,r,a){const o=this.planes;return o[0].copy(e),o[1].copy(t),o[2].copy(i),o[3].copy(s),o[4].copy(r),o[5].copy(a),this}copy(e){const t=this.planes;for(let i=0;i<6;i++)t[i].copy(e.planes[i]);return this}setFromProjectionMatrix(e,t=Dn,i=!1){const s=this.planes,r=e.elements,a=r[0],o=r[1],l=r[2],c=r[3],u=r[4],f=r[5],h=r[6],p=r[7],_=r[8],M=r[9],g=r[10],d=r[11],y=r[12],A=r[13],b=r[14],P=r[15];if(s[0].setComponents(c-a,p-u,d-_,P-y).normalize(),s[1].setComponents(c+a,p+u,d+_,P+y).normalize(),s[2].setComponents(c+o,p+f,d+M,P+A).normalize(),s[3].setComponents(c-o,p-f,d-M,P-A).normalize(),i)s[4].setComponents(l,h,g,b).normalize(),s[5].setComponents(c-l,p-h,d-g,P-b).normalize();else if(s[4].setComponents(c-l,p-h,d-g,P-b).normalize(),t===Dn)s[5].setComponents(c+l,p+h,d+g,P+b).normalize();else if(t===dr)s[5].setComponents(l,h,g,b).normalize();else throw new Error("THREE.Frustum.setFromProjectionMatrix(): Invalid coordinate system: "+t);return this}intersectsObject(e){if(e.boundingSphere!==void 0)e.boundingSphere===null&&e.computeBoundingSphere(),Ii.copy(e.boundingSphere).applyMatrix4(e.matrixWorld);else{const t=e.geometry;t.boundingSphere===null&&t.computeBoundingSphere(),Ii.copy(t.boundingSphere).applyMatrix4(e.matrixWorld)}return this.intersectsSphere(Ii)}intersectsSprite(e){Ii.center.set(0,0,0);const t=Yg.distanceTo(e.center);return Ii.radius=.7071067811865476+t,Ii.applyMatrix4(e.matrixWorld),this.intersectsSphere(Ii)}intersectsSphere(e){const t=this.planes,i=e.center,s=-e.radius;for(let r=0;r<6;r++)if(t[r].distanceToPoint(i)<s)return!1;return!0}intersectsBox(e){const t=this.planes;for(let i=0;i<6;i++){const s=t[i];if(Gr.x=s.normal.x>0?e.max.x:e.min.x,Gr.y=s.normal.y>0?e.max.y:e.min.y,Gr.z=s.normal.z>0?e.max.z:e.min.z,s.distanceToPoint(Gr)<0)return!1}return!0}containsPoint(e){const t=this.planes;for(let i=0;i<6;i++)if(t[i].distanceToPoint(e)<0)return!1;return!0}clone(){return new this.constructor().copy(this)}}class $f extends Ds{constructor(e){super(),this.isLineBasicMaterial=!0,this.type="LineBasicMaterial",this.color=new Ze(16777215),this.map=null,this.linewidth=1,this.linecap="round",this.linejoin="round",this.fog=!0,this.setValues(e)}copy(e){return super.copy(e),this.color.copy(e.color),this.map=e.map,this.linewidth=e.linewidth,this.linecap=e.linecap,this.linejoin=e.linejoin,this.fog=e.fog,this}}const ya=new O,ba=new O,Iu=new ht,Gs=new Na,Wr=new Ps,Ao=new O,Uu=new O;class qg extends Mt{constructor(e=new Mn,t=new $f){super(),this.isLine=!0,this.type="Line",this.geometry=e,this.material=t,this.morphTargetDictionary=void 0,this.morphTargetInfluences=void 0,this.updateMorphTargets()}copy(e,t){return super.copy(e,t),this.material=Array.isArray(e.material)?e.material.slice():e.material,this.geometry=e.geometry,this}computeLineDistances(){const e=this.geometry;if(e.index===null){const t=e.attributes.position,i=[0];for(let s=1,r=t.count;s<r;s++)ya.fromBufferAttribute(t,s-1),ba.fromBufferAttribute(t,s),i[s]=i[s-1],i[s]+=ya.distanceTo(ba);e.setAttribute("lineDistance",new tn(i,1))}else Oe("Line.computeLineDistances(): Computation only possible with non-indexed BufferGeometry.");return this}raycast(e,t){const i=this.geometry,s=this.matrixWorld,r=e.params.Line.threshold,a=i.drawRange;if(i.boundingSphere===null&&i.computeBoundingSphere(),Wr.copy(i.boundingSphere),Wr.applyMatrix4(s),Wr.radius+=r,e.ray.intersectsSphere(Wr)===!1)return;Iu.copy(s).invert(),Gs.copy(e.ray).applyMatrix4(Iu);const o=r/((this.scale.x+this.scale.y+this.scale.z)/3),l=o*o,c=this.isLineSegments?2:1,u=i.index,h=i.attributes.position;if(u!==null){const p=Math.max(0,a.start),_=Math.min(u.count,a.start+a.count);for(let M=p,g=_-1;M<g;M+=c){const d=u.getX(M),y=u.getX(M+1),A=Xr(this,e,Gs,l,d,y,M);A&&t.push(A)}if(this.isLineLoop){const M=u.getX(_-1),g=u.getX(p),d=Xr(this,e,Gs,l,M,g,_-1);d&&t.push(d)}}else{const p=Math.max(0,a.start),_=Math.min(h.count,a.start+a.count);for(let M=p,g=_-1;M<g;M+=c){const d=Xr(this,e,Gs,l,M,M+1,M);d&&t.push(d)}if(this.isLineLoop){const M=Xr(this,e,Gs,l,_-1,p,_-1);M&&t.push(M)}}}updateMorphTargets(){const t=this.geometry.morphAttributes,i=Object.keys(t);if(i.length>0){const s=t[i[0]];if(s!==void 0){this.morphTargetInfluences=[],this.morphTargetDictionary={};for(let r=0,a=s.length;r<a;r++){const o=s[r].name||String(r);this.morphTargetInfluences.push(0),this.morphTargetDictionary[o]=r}}}}}function Xr(n,e,t,i,s,r,a){const o=n.geometry.attributes.position;if(ya.fromBufferAttribute(o,s),ba.fromBufferAttribute(o,r),t.distanceSqToSegment(ya,ba,Ao,Uu)>i)return;Ao.applyMatrix4(n.matrixWorld);const c=e.ray.origin.distanceTo(Ao);if(!(c<e.near||c>e.far))return{distance:c,point:Uu.clone().applyMatrix4(n.matrixWorld),index:a,face:null,faceIndex:null,barycoord:null,object:n}}const Nu=new O,Fu=new O;class jg extends qg{constructor(e,t){super(e,t),this.isLineSegments=!0,this.type="LineSegments"}computeLineDistances(){const e=this.geometry;if(e.index===null){const t=e.attributes.position,i=[];for(let s=0,r=t.count;s<r;s+=2)Nu.fromBufferAttribute(t,s),Fu.fromBufferAttribute(t,s+1),i[s]=s===0?0:i[s-1],i[s+1]=i[s]+Nu.distanceTo(Fu);e.setAttribute("lineDistance",new tn(i,1))}else Oe("LineSegments.computeLineDistances(): Computation only possible with non-indexed BufferGeometry.");return this}}class Zf extends Ft{constructor(e=[],t=Wi,i,s,r,a,o,l,c,u){super(e,t,i,s,r,a,o,l,c,u),this.isCubeTexture=!0,this.flipY=!1}get images(){return this.image}set images(e){this.image=e}}class Kg extends Ft{constructor(e,t,i,s,r,a,o,l,c){super(e,t,i,s,r,a,o,l,c),this.isCanvasTexture=!0,this.needsUpdate=!0}}class pr extends Ft{constructor(e,t,i=On,s,r,a,o=Ct,l=Ct,c,u=ai,f=1){if(u!==ai&&u!==Vi)throw new Error("DepthTexture format must be either THREE.DepthFormat or THREE.DepthStencilFormat");const h={width:e,height:t,depth:f};super(h,s,r,a,o,l,u,i,c),this.isDepthTexture=!0,this.flipY=!1,this.generateMipmaps=!1,this.compareFunction=null}copy(e){return super.copy(e),this.source=new fc(Object.assign({},e.image)),this.compareFunction=e.compareFunction,this}toJSON(e){const t=super.toJSON(e);return this.compareFunction!==null&&(t.compareFunction=this.compareFunction),t}}class $g extends pr{constructor(e,t=On,i=Wi,s,r,a=Ct,o=Ct,l,c=ai){const u={width:e,height:e,depth:1},f=[u,u,u,u,u,u];super(e,e,t,i,s,r,a,o,l,c),this.image=f,this.isCubeDepthTexture=!0,this.isCubeTexture=!0}get images(){return this.image}set images(e){this.image=e}}class Jf extends Ft{constructor(e=null){super(),this.sourceTexture=e,this.isExternalTexture=!0}copy(e){return super.copy(e),this.sourceTexture=e.sourceTexture,this}}class bi extends Mn{constructor(e=1,t=1,i=1,s=1,r=1,a=1){super(),this.type="BoxGeometry",this.parameters={width:e,height:t,depth:i,widthSegments:s,heightSegments:r,depthSegments:a};const o=this;s=Math.floor(s),r=Math.floor(r),a=Math.floor(a);const l=[],c=[],u=[],f=[];let h=0,p=0;_("z","y","x",-1,-1,i,t,e,a,r,0),_("z","y","x",1,-1,i,t,-e,a,r,1),_("x","z","y",1,1,e,i,t,s,a,2),_("x","z","y",1,-1,e,i,-t,s,a,3),_("x","y","z",1,-1,e,t,i,s,r,4),_("x","y","z",-1,-1,e,t,-i,s,r,5),this.setIndex(l),this.setAttribute("position",new tn(c,3)),this.setAttribute("normal",new tn(u,3)),this.setAttribute("uv",new tn(f,2));function _(M,g,d,y,A,b,P,L,I,x,T){const Q=b/I,D=P/x,X=b/2,Y=P/2,$=L/2,W=I+1,B=x+1;let N=0,ie=0;const le=new O;for(let ve=0;ve<B;ve++){const se=ve*D-Y;for(let oe=0;oe<W;oe++){const we=oe*Q-X;le[M]=we*y,le[g]=se*A,le[d]=$,c.push(le.x,le.y,le.z),le[M]=0,le[g]=0,le[d]=L>0?1:-1,u.push(le.x,le.y,le.z),f.push(oe/I),f.push(1-ve/x),N+=1}}for(let ve=0;ve<x;ve++)for(let se=0;se<I;se++){const oe=h+se+W*ve,we=h+se+W*(ve+1),Ge=h+(se+1)+W*(ve+1),nt=h+(se+1)+W*ve;l.push(oe,we,nt),l.push(we,Ge,nt),ie+=6}o.addGroup(p,ie,T),p+=ie,h+=N}}copy(e){return super.copy(e),this.parameters=Object.assign({},e.parameters),this}static fromJSON(e){return new bi(e.width,e.height,e.depth,e.widthSegments,e.heightSegments,e.depthSegments)}}class vr extends Mn{constructor(e=1,t=1,i=1,s=1){super(),this.type="PlaneGeometry",this.parameters={width:e,height:t,widthSegments:i,heightSegments:s};const r=e/2,a=t/2,o=Math.floor(i),l=Math.floor(s),c=o+1,u=l+1,f=e/o,h=t/l,p=[],_=[],M=[],g=[];for(let d=0;d<u;d++){const y=d*h-a;for(let A=0;A<c;A++){const b=A*f-r;_.push(b,-y,0),M.push(0,0,1),g.push(A/o),g.push(1-d/l)}}for(let d=0;d<l;d++)for(let y=0;y<o;y++){const A=y+c*d,b=y+c*(d+1),P=y+1+c*(d+1),L=y+1+c*d;p.push(A,b,L),p.push(b,P,L)}this.setIndex(p),this.setAttribute("position",new tn(_,3)),this.setAttribute("normal",new tn(M,3)),this.setAttribute("uv",new tn(g,2))}copy(e){return super.copy(e),this.parameters=Object.assign({},e.parameters),this}static fromJSON(e){return new vr(e.width,e.height,e.widthSegments,e.heightSegments)}}function Rs(n){const e={};for(const t in n){e[t]={};for(const i in n[t]){const s=n[t][i];s&&(s.isColor||s.isMatrix3||s.isMatrix4||s.isVector2||s.isVector3||s.isVector4||s.isTexture||s.isQuaternion)?s.isRenderTargetTexture?(Oe("UniformsUtils: Textures of render targets cannot be cloned via cloneUniforms() or mergeUniforms()."),e[t][i]=null):e[t][i]=s.clone():Array.isArray(s)?e[t][i]=s.slice():e[t][i]=s}}return e}function zt(n){const e={};for(let t=0;t<n.length;t++){const i=Rs(n[t]);for(const s in i)e[s]=i[s]}return e}function Zg(n){const e=[];for(let t=0;t<n.length;t++)e.push(n[t].clone());return e}function Qf(n){const e=n.getRenderTarget();return e===null?n.outputColorSpace:e.isXRRenderTarget===!0?e.texture.colorSpace:et.workingColorSpace}const Jg={clone:Rs,merge:zt};var Qg=`void main() {
	gl_Position = projectionMatrix * modelViewMatrix * vec4( position, 1.0 );
}`,e_=`void main() {
	gl_FragColor = vec4( 1.0, 0.0, 0.0, 1.0 );
}`;class zn extends Ds{constructor(e){super(),this.isShaderMaterial=!0,this.type="ShaderMaterial",this.defines={},this.uniforms={},this.uniformsGroups=[],this.vertexShader=Qg,this.fragmentShader=e_,this.linewidth=1,this.wireframe=!1,this.wireframeLinewidth=1,this.fog=!1,this.lights=!1,this.clipping=!1,this.forceSinglePass=!0,this.extensions={clipCullDistance:!1,multiDraw:!1},this.defaultAttributeValues={color:[1,1,1],uv:[0,0],uv1:[0,0]},this.index0AttributeName=void 0,this.uniformsNeedUpdate=!1,this.glslVersion=null,e!==void 0&&this.setValues(e)}copy(e){return super.copy(e),this.fragmentShader=e.fragmentShader,this.vertexShader=e.vertexShader,this.uniforms=Rs(e.uniforms),this.uniformsGroups=Zg(e.uniformsGroups),this.defines=Object.assign({},e.defines),this.wireframe=e.wireframe,this.wireframeLinewidth=e.wireframeLinewidth,this.fog=e.fog,this.lights=e.lights,this.clipping=e.clipping,this.extensions=Object.assign({},e.extensions),this.glslVersion=e.glslVersion,this.defaultAttributeValues=Object.assign({},e.defaultAttributeValues),this.index0AttributeName=e.index0AttributeName,this.uniformsNeedUpdate=e.uniformsNeedUpdate,this}toJSON(e){const t=super.toJSON(e);t.glslVersion=this.glslVersion,t.uniforms={};for(const s in this.uniforms){const a=this.uniforms[s].value;a&&a.isTexture?t.uniforms[s]={type:"t",value:a.toJSON(e).uuid}:a&&a.isColor?t.uniforms[s]={type:"c",value:a.getHex()}:a&&a.isVector2?t.uniforms[s]={type:"v2",value:a.toArray()}:a&&a.isVector3?t.uniforms[s]={type:"v3",value:a.toArray()}:a&&a.isVector4?t.uniforms[s]={type:"v4",value:a.toArray()}:a&&a.isMatrix3?t.uniforms[s]={type:"m3",value:a.toArray()}:a&&a.isMatrix4?t.uniforms[s]={type:"m4",value:a.toArray()}:t.uniforms[s]={value:a}}Object.keys(this.defines).length>0&&(t.defines=this.defines),t.vertexShader=this.vertexShader,t.fragmentShader=this.fragmentShader,t.lights=this.lights,t.clipping=this.clipping;const i={};for(const s in this.extensions)this.extensions[s]===!0&&(i[s]=!0);return Object.keys(i).length>0&&(t.extensions=i),t}}class t_ extends zn{constructor(e){super(e),this.isRawShaderMaterial=!0,this.type="RawShaderMaterial"}}class Yr extends Ds{constructor(e){super(),this.isMeshStandardMaterial=!0,this.type="MeshStandardMaterial",this.defines={STANDARD:""},this.color=new Ze(16777215),this.roughness=1,this.metalness=0,this.map=null,this.lightMap=null,this.lightMapIntensity=1,this.aoMap=null,this.aoMapIntensity=1,this.emissive=new Ze(0),this.emissiveIntensity=1,this.emissiveMap=null,this.bumpMap=null,this.bumpScale=1,this.normalMap=null,this.normalMapType=kf,this.normalScale=new ke(1,1),this.displacementMap=null,this.displacementScale=1,this.displacementBias=0,this.roughnessMap=null,this.metalnessMap=null,this.alphaMap=null,this.envMap=null,this.envMapRotation=new Bn,this.envMapIntensity=1,this.wireframe=!1,this.wireframeLinewidth=1,this.wireframeLinecap="round",this.wireframeLinejoin="round",this.flatShading=!1,this.fog=!0,this.setValues(e)}copy(e){return super.copy(e),this.defines={STANDARD:""},this.color.copy(e.color),this.roughness=e.roughness,this.metalness=e.metalness,this.map=e.map,this.lightMap=e.lightMap,this.lightMapIntensity=e.lightMapIntensity,this.aoMap=e.aoMap,this.aoMapIntensity=e.aoMapIntensity,this.emissive.copy(e.emissive),this.emissiveMap=e.emissiveMap,this.emissiveIntensity=e.emissiveIntensity,this.bumpMap=e.bumpMap,this.bumpScale=e.bumpScale,this.normalMap=e.normalMap,this.normalMapType=e.normalMapType,this.normalScale.copy(e.normalScale),this.displacementMap=e.displacementMap,this.displacementScale=e.displacementScale,this.displacementBias=e.displacementBias,this.roughnessMap=e.roughnessMap,this.metalnessMap=e.metalnessMap,this.alphaMap=e.alphaMap,this.envMap=e.envMap,this.envMapRotation.copy(e.envMapRotation),this.envMapIntensity=e.envMapIntensity,this.wireframe=e.wireframe,this.wireframeLinewidth=e.wireframeLinewidth,this.wireframeLinecap=e.wireframeLinecap,this.wireframeLinejoin=e.wireframeLinejoin,this.flatShading=e.flatShading,this.fog=e.fog,this}}class n_ extends Ds{constructor(e){super(),this.isMeshDepthMaterial=!0,this.type="MeshDepthMaterial",this.depthPacking=ug,this.map=null,this.alphaMap=null,this.displacementMap=null,this.displacementScale=1,this.displacementBias=0,this.wireframe=!1,this.wireframeLinewidth=1,this.setValues(e)}copy(e){return super.copy(e),this.depthPacking=e.depthPacking,this.map=e.map,this.alphaMap=e.alphaMap,this.displacementMap=e.displacementMap,this.displacementScale=e.displacementScale,this.displacementBias=e.displacementBias,this.wireframe=e.wireframe,this.wireframeLinewidth=e.wireframeLinewidth,this}}class i_ extends Ds{constructor(e){super(),this.isMeshDistanceMaterial=!0,this.type="MeshDistanceMaterial",this.map=null,this.alphaMap=null,this.displacementMap=null,this.displacementScale=1,this.displacementBias=0,this.setValues(e)}copy(e){return super.copy(e),this.map=e.map,this.alphaMap=e.alphaMap,this.displacementMap=e.displacementMap,this.displacementScale=e.displacementScale,this.displacementBias=e.displacementBias,this}}class gc extends Mt{constructor(e,t=1){super(),this.isLight=!0,this.type="Light",this.color=new Ze(e),this.intensity=t}dispose(){this.dispatchEvent({type:"dispose"})}copy(e,t){return super.copy(e,t),this.color.copy(e.color),this.intensity=e.intensity,this}toJSON(e){const t=super.toJSON(e);return t.object.color=this.color.getHex(),t.object.intensity=this.intensity,t}}class s_ extends gc{constructor(e,t,i){super(e,i),this.isHemisphereLight=!0,this.type="HemisphereLight",this.position.copy(Mt.DEFAULT_UP),this.updateMatrix(),this.groundColor=new Ze(t)}copy(e,t){return super.copy(e,t),this.groundColor.copy(e.groundColor),this}toJSON(e){const t=super.toJSON(e);return t.object.groundColor=this.groundColor.getHex(),t}}const wo=new ht,Ou=new O,Bu=new O;class r_{constructor(e){this.camera=e,this.intensity=1,this.bias=0,this.biasNode=null,this.normalBias=0,this.radius=1,this.blurSamples=8,this.mapSize=new ke(512,512),this.mapType=Jt,this.map=null,this.mapPass=null,this.matrix=new ht,this.autoUpdate=!0,this.needsUpdate=!1,this._frustum=new mc,this._frameExtents=new ke(1,1),this._viewportCount=1,this._viewports=[new _t(0,0,1,1)]}getViewportCount(){return this._viewportCount}getFrustum(){return this._frustum}updateMatrices(e){const t=this.camera,i=this.matrix;Ou.setFromMatrixPosition(e.matrixWorld),t.position.copy(Ou),Bu.setFromMatrixPosition(e.target.matrixWorld),t.lookAt(Bu),t.updateMatrixWorld(),wo.multiplyMatrices(t.projectionMatrix,t.matrixWorldInverse),this._frustum.setFromProjectionMatrix(wo,t.coordinateSystem,t.reversedDepth),t.coordinateSystem===dr||t.reversedDepth?i.set(.5,0,0,.5,0,.5,0,.5,0,0,1,0,0,0,0,1):i.set(.5,0,0,.5,0,.5,0,.5,0,0,.5,.5,0,0,0,1),i.multiply(wo)}getViewport(e){return this._viewports[e]}getFrameExtents(){return this._frameExtents}dispose(){this.map&&this.map.dispose(),this.mapPass&&this.mapPass.dispose()}copy(e){return this.camera=e.camera.clone(),this.intensity=e.intensity,this.bias=e.bias,this.radius=e.radius,this.autoUpdate=e.autoUpdate,this.needsUpdate=e.needsUpdate,this.normalBias=e.normalBias,this.blurSamples=e.blurSamples,this.mapSize.copy(e.mapSize),this.biasNode=e.biasNode,this}clone(){return new this.constructor().copy(this)}toJSON(){const e={};return this.intensity!==1&&(e.intensity=this.intensity),this.bias!==0&&(e.bias=this.bias),this.normalBias!==0&&(e.normalBias=this.normalBias),this.radius!==1&&(e.radius=this.radius),(this.mapSize.x!==512||this.mapSize.y!==512)&&(e.mapSize=this.mapSize.toArray()),e.camera=this.camera.toJSON(!1).object,delete e.camera.matrix,e}}const qr=new O,jr=new oi,bn=new O;class ed extends Mt{constructor(){super(),this.isCamera=!0,this.type="Camera",this.matrixWorldInverse=new ht,this.projectionMatrix=new ht,this.projectionMatrixInverse=new ht,this.coordinateSystem=Dn,this._reversedDepth=!1}get reversedDepth(){return this._reversedDepth}copy(e,t){return super.copy(e,t),this.matrixWorldInverse.copy(e.matrixWorldInverse),this.projectionMatrix.copy(e.projectionMatrix),this.projectionMatrixInverse.copy(e.projectionMatrixInverse),this.coordinateSystem=e.coordinateSystem,this}getWorldDirection(e){return super.getWorldDirection(e).negate()}updateMatrixWorld(e){super.updateMatrixWorld(e),this.matrixWorld.decompose(qr,jr,bn),bn.x===1&&bn.y===1&&bn.z===1?this.matrixWorldInverse.copy(this.matrixWorld).invert():this.matrixWorldInverse.compose(qr,jr,bn.set(1,1,1)).invert()}updateWorldMatrix(e,t){super.updateWorldMatrix(e,t),this.matrixWorld.decompose(qr,jr,bn),bn.x===1&&bn.y===1&&bn.z===1?this.matrixWorldInverse.copy(this.matrixWorld).invert():this.matrixWorldInverse.compose(qr,jr,bn.set(1,1,1)).invert()}clone(){return new this.constructor().copy(this)}}const gi=new O,zu=new ke,Hu=new ke;class an extends ed{constructor(e=50,t=1,i=.1,s=2e3){super(),this.isPerspectiveCamera=!0,this.type="PerspectiveCamera",this.fov=e,this.zoom=1,this.near=i,this.far=s,this.focus=10,this.aspect=t,this.view=null,this.filmGauge=35,this.filmOffset=0,this.updateProjectionMatrix()}copy(e,t){return super.copy(e,t),this.fov=e.fov,this.zoom=e.zoom,this.near=e.near,this.far=e.far,this.focus=e.focus,this.aspect=e.aspect,this.view=e.view===null?null:Object.assign({},e.view),this.filmGauge=e.filmGauge,this.filmOffset=e.filmOffset,this}setFocalLength(e){const t=.5*this.getFilmHeight()/e;this.fov=Ul*2*Math.atan(t),this.updateProjectionMatrix()}getFocalLength(){const e=Math.tan(la*.5*this.fov);return .5*this.getFilmHeight()/e}getEffectiveFOV(){return Ul*2*Math.atan(Math.tan(la*.5*this.fov)/this.zoom)}getFilmWidth(){return this.filmGauge*Math.min(this.aspect,1)}getFilmHeight(){return this.filmGauge/Math.max(this.aspect,1)}getViewBounds(e,t,i){gi.set(-1,-1,.5).applyMatrix4(this.projectionMatrixInverse),t.set(gi.x,gi.y).multiplyScalar(-e/gi.z),gi.set(1,1,.5).applyMatrix4(this.projectionMatrixInverse),i.set(gi.x,gi.y).multiplyScalar(-e/gi.z)}getViewSize(e,t){return this.getViewBounds(e,zu,Hu),t.subVectors(Hu,zu)}setViewOffset(e,t,i,s,r,a){this.aspect=e/t,this.view===null&&(this.view={enabled:!0,fullWidth:1,fullHeight:1,offsetX:0,offsetY:0,width:1,height:1}),this.view.enabled=!0,this.view.fullWidth=e,this.view.fullHeight=t,this.view.offsetX=i,this.view.offsetY=s,this.view.width=r,this.view.height=a,this.updateProjectionMatrix()}clearViewOffset(){this.view!==null&&(this.view.enabled=!1),this.updateProjectionMatrix()}updateProjectionMatrix(){const e=this.near;let t=e*Math.tan(la*.5*this.fov)/this.zoom,i=2*t,s=this.aspect*i,r=-.5*s;const a=this.view;if(this.view!==null&&this.view.enabled){const l=a.fullWidth,c=a.fullHeight;r+=a.offsetX*s/l,t-=a.offsetY*i/c,s*=a.width/l,i*=a.height/c}const o=this.filmOffset;o!==0&&(r+=e*o/this.getFilmWidth()),this.projectionMatrix.makePerspective(r,r+s,t,t-i,e,this.far,this.coordinateSystem,this.reversedDepth),this.projectionMatrixInverse.copy(this.projectionMatrix).invert()}toJSON(e){const t=super.toJSON(e);return t.object.fov=this.fov,t.object.zoom=this.zoom,t.object.near=this.near,t.object.far=this.far,t.object.focus=this.focus,t.object.aspect=this.aspect,this.view!==null&&(t.object.view=Object.assign({},this.view)),t.object.filmGauge=this.filmGauge,t.object.filmOffset=this.filmOffset,t}}class _c extends ed{constructor(e=-1,t=1,i=1,s=-1,r=.1,a=2e3){super(),this.isOrthographicCamera=!0,this.type="OrthographicCamera",this.zoom=1,this.view=null,this.left=e,this.right=t,this.top=i,this.bottom=s,this.near=r,this.far=a,this.updateProjectionMatrix()}copy(e,t){return super.copy(e,t),this.left=e.left,this.right=e.right,this.top=e.top,this.bottom=e.bottom,this.near=e.near,this.far=e.far,this.zoom=e.zoom,this.view=e.view===null?null:Object.assign({},e.view),this}setViewOffset(e,t,i,s,r,a){this.view===null&&(this.view={enabled:!0,fullWidth:1,fullHeight:1,offsetX:0,offsetY:0,width:1,height:1}),this.view.enabled=!0,this.view.fullWidth=e,this.view.fullHeight=t,this.view.offsetX=i,this.view.offsetY=s,this.view.width=r,this.view.height=a,this.updateProjectionMatrix()}clearViewOffset(){this.view!==null&&(this.view.enabled=!1),this.updateProjectionMatrix()}updateProjectionMatrix(){const e=(this.right-this.left)/(2*this.zoom),t=(this.top-this.bottom)/(2*this.zoom),i=(this.right+this.left)/2,s=(this.top+this.bottom)/2;let r=i-e,a=i+e,o=s+t,l=s-t;if(this.view!==null&&this.view.enabled){const c=(this.right-this.left)/this.view.fullWidth/this.zoom,u=(this.top-this.bottom)/this.view.fullHeight/this.zoom;r+=c*this.view.offsetX,a=r+c*this.view.width,o-=u*this.view.offsetY,l=o-u*this.view.height}this.projectionMatrix.makeOrthographic(r,a,o,l,this.near,this.far,this.coordinateSystem,this.reversedDepth),this.projectionMatrixInverse.copy(this.projectionMatrix).invert()}toJSON(e){const t=super.toJSON(e);return t.object.zoom=this.zoom,t.object.left=this.left,t.object.right=this.right,t.object.top=this.top,t.object.bottom=this.bottom,t.object.near=this.near,t.object.far=this.far,this.view!==null&&(t.object.view=Object.assign({},this.view)),t}}class a_ extends r_{constructor(){super(new _c(-5,5,5,-5,.5,500)),this.isDirectionalLightShadow=!0}}class o_ extends gc{constructor(e,t){super(e,t),this.isDirectionalLight=!0,this.type="DirectionalLight",this.position.copy(Mt.DEFAULT_UP),this.updateMatrix(),this.target=new Mt,this.shadow=new a_}dispose(){super.dispose(),this.shadow.dispose()}copy(e){return super.copy(e),this.target=e.target.clone(),this.shadow=e.shadow.clone(),this}toJSON(e){const t=super.toJSON(e);return t.object.shadow=this.shadow.toJSON(),t.object.target=this.target.uuid,t}}class l_ extends gc{constructor(e,t){super(e,t),this.isAmbientLight=!0,this.type="AmbientLight"}}const us=-90,hs=1;class c_ extends Mt{constructor(e,t,i){super(),this.type="CubeCamera",this.renderTarget=i,this.coordinateSystem=null,this.activeMipmapLevel=0;const s=new an(us,hs,e,t);s.layers=this.layers,this.add(s);const r=new an(us,hs,e,t);r.layers=this.layers,this.add(r);const a=new an(us,hs,e,t);a.layers=this.layers,this.add(a);const o=new an(us,hs,e,t);o.layers=this.layers,this.add(o);const l=new an(us,hs,e,t);l.layers=this.layers,this.add(l);const c=new an(us,hs,e,t);c.layers=this.layers,this.add(c)}updateCoordinateSystem(){const e=this.coordinateSystem,t=this.children.concat(),[i,s,r,a,o,l]=t;for(const c of t)this.remove(c);if(e===Dn)i.up.set(0,1,0),i.lookAt(1,0,0),s.up.set(0,1,0),s.lookAt(-1,0,0),r.up.set(0,0,-1),r.lookAt(0,1,0),a.up.set(0,0,1),a.lookAt(0,-1,0),o.up.set(0,1,0),o.lookAt(0,0,1),l.up.set(0,1,0),l.lookAt(0,0,-1);else if(e===dr)i.up.set(0,-1,0),i.lookAt(-1,0,0),s.up.set(0,-1,0),s.lookAt(1,0,0),r.up.set(0,0,1),r.lookAt(0,1,0),a.up.set(0,0,-1),a.lookAt(0,-1,0),o.up.set(0,-1,0),o.lookAt(0,0,1),l.up.set(0,-1,0),l.lookAt(0,0,-1);else throw new Error("THREE.CubeCamera.updateCoordinateSystem(): Invalid coordinate system: "+e);for(const c of t)this.add(c),c.updateMatrixWorld()}update(e,t){this.parent===null&&this.updateMatrixWorld();const{renderTarget:i,activeMipmapLevel:s}=this;this.coordinateSystem!==e.coordinateSystem&&(this.coordinateSystem=e.coordinateSystem,this.updateCoordinateSystem());const[r,a,o,l,c,u]=this.children,f=e.getRenderTarget(),h=e.getActiveCubeFace(),p=e.getActiveMipmapLevel(),_=e.xr.enabled;e.xr.enabled=!1;const M=i.texture.generateMipmaps;i.texture.generateMipmaps=!1;let g=!1;e.isWebGLRenderer===!0?g=e.state.buffers.depth.getReversed():g=e.reversedDepthBuffer,e.setRenderTarget(i,0,s),g&&e.autoClear===!1&&e.clearDepth(),e.render(t,r),e.setRenderTarget(i,1,s),g&&e.autoClear===!1&&e.clearDepth(),e.render(t,a),e.setRenderTarget(i,2,s),g&&e.autoClear===!1&&e.clearDepth(),e.render(t,o),e.setRenderTarget(i,3,s),g&&e.autoClear===!1&&e.clearDepth(),e.render(t,l),e.setRenderTarget(i,4,s),g&&e.autoClear===!1&&e.clearDepth(),e.render(t,c),i.texture.generateMipmaps=M,e.setRenderTarget(i,5,s),g&&e.autoClear===!1&&e.clearDepth(),e.render(t,u),e.setRenderTarget(f,h,p),e.xr.enabled=_,i.texture.needsPMREMUpdate=!0}}class u_ extends an{constructor(e=[]){super(),this.isArrayCamera=!0,this.isMultiViewCamera=!1,this.cameras=e}}class h_{constructor(){this._previousTime=0,this._currentTime=0,this._startTime=performance.now(),this._delta=0,this._elapsed=0,this._timescale=1,this._document=null,this._pageVisibilityHandler=null}connect(e){this._document=e,e.hidden!==void 0&&(this._pageVisibilityHandler=f_.bind(this),e.addEventListener("visibilitychange",this._pageVisibilityHandler,!1))}disconnect(){this._pageVisibilityHandler!==null&&(this._document.removeEventListener("visibilitychange",this._pageVisibilityHandler),this._pageVisibilityHandler=null),this._document=null}getDelta(){return this._delta/1e3}getElapsed(){return this._elapsed/1e3}getTimescale(){return this._timescale}setTimescale(e){return this._timescale=e,this}reset(){return this._currentTime=performance.now()-this._startTime,this}dispose(){this.disconnect()}update(e){return this._pageVisibilityHandler!==null&&this._document.hidden===!0?this._delta=0:(this._previousTime=this._currentTime,this._currentTime=(e!==void 0?e:performance.now())-this._startTime,this._delta=(this._currentTime-this._previousTime)*this._timescale,this._elapsed+=this._delta),this}}function f_(){this._document.hidden===!1&&this.reset()}const Vu=new ht;class d_{constructor(e,t,i=0,s=1/0){this.ray=new Na(e,t),this.near=i,this.far=s,this.camera=null,this.layers=new dc,this.params={Mesh:{},Line:{threshold:1},LOD:{},Points:{threshold:1},Sprite:{}}}set(e,t){this.ray.set(e,t)}setFromCamera(e,t){t.isPerspectiveCamera?(this.ray.origin.setFromMatrixPosition(t.matrixWorld),this.ray.direction.set(e.x,e.y,.5).unproject(t).sub(this.ray.origin).normalize(),this.camera=t):t.isOrthographicCamera?(this.ray.origin.set(e.x,e.y,(t.near+t.far)/(t.near-t.far)).unproject(t),this.ray.direction.set(0,0,-1).transformDirection(t.matrixWorld),this.camera=t):Qe("Raycaster: Unsupported camera type: "+t.type)}setFromXRController(e){return Vu.identity().extractRotation(e.matrixWorld),this.ray.origin.setFromMatrixPosition(e.matrixWorld),this.ray.direction.set(0,0,-1).applyMatrix4(Vu),this}intersectObject(e,t=!0,i=[]){return Nl(e,this,i,t),i.sort(ku),i}intersectObjects(e,t=!0,i=[]){for(let s=0,r=e.length;s<r;s++)Nl(e[s],this,i,t);return i.sort(ku),i}}function ku(n,e){return n.distance-e.distance}function Nl(n,e,t,i){let s=!0;if(n.layers.test(e.layers)&&n.raycast(e,t)===!1&&(s=!1),s===!0&&i===!0){const r=n.children;for(let a=0,o=r.length;a<o;a++)Nl(r[a],e,t,!0)}}class Gu{constructor(e=1,t=0,i=0){this.radius=e,this.phi=t,this.theta=i}set(e,t,i){return this.radius=e,this.phi=t,this.theta=i,this}copy(e){return this.radius=e.radius,this.phi=e.phi,this.theta=e.theta,this}makeSafe(){return this.phi=Je(this.phi,1e-6,Math.PI-1e-6),this}setFromVector3(e){return this.setFromCartesianCoords(e.x,e.y,e.z)}setFromCartesianCoords(e,t,i){return this.radius=Math.sqrt(e*e+t*t+i*i),this.radius===0?(this.theta=0,this.phi=0):(this.theta=Math.atan2(e,i),this.phi=Math.acos(Je(t/this.radius,-1,1))),this}clone(){return new this.constructor().copy(this)}}class p_ extends jg{constructor(e=10,t=10,i=4473924,s=8947848){i=new Ze(i),s=new Ze(s);const r=t/2,a=e/t,o=e/2,l=[],c=[];for(let h=0,p=0,_=-o;h<=t;h++,_+=a){l.push(-o,0,_,o,0,_),l.push(_,0,-o,_,0,o);const M=h===r?i:s;M.toArray(c,p),p+=3,M.toArray(c,p),p+=3,M.toArray(c,p),p+=3,M.toArray(c,p),p+=3}const u=new Mn;u.setAttribute("position",new tn(l,3)),u.setAttribute("color",new tn(c,3));const f=new $f({vertexColors:!0,toneMapped:!1});super(u,f),this.type="GridHelper"}dispose(){this.geometry.dispose(),this.material.dispose()}}class m_ extends Yi{constructor(e,t=null){super(),this.object=e,this.domElement=t,this.enabled=!0,this.state=-1,this.keys={},this.mouseButtons={LEFT:null,MIDDLE:null,RIGHT:null},this.touches={ONE:null,TWO:null}}connect(e){if(e===void 0){Oe("Controls: connect() now requires an element.");return}this.domElement!==null&&this.disconnect(),this.domElement=e}disconnect(){}dispose(){}update(){}}function Wu(n,e,t,i){const s=g_(i);switch(t){case Hf:return n*e;case ac:return n*e/s.components*s.byteLength;case oc:return n*e/s.components*s.byteLength;case ws:return n*e*2/s.components*s.byteLength;case lc:return n*e*2/s.components*s.byteLength;case Vf:return n*e*3/s.components*s.byteLength;case mn:return n*e*4/s.components*s.byteLength;case cc:return n*e*4/s.components*s.byteLength;case sa:case ra:return Math.floor((n+3)/4)*Math.floor((e+3)/4)*8;case aa:case oa:return Math.floor((n+3)/4)*Math.floor((e+3)/4)*16;case il:case rl:return Math.max(n,16)*Math.max(e,8)/4;case nl:case sl:return Math.max(n,8)*Math.max(e,8)/2;case al:case ol:case cl:case ul:return Math.floor((n+3)/4)*Math.floor((e+3)/4)*8;case ll:case hl:case fl:return Math.floor((n+3)/4)*Math.floor((e+3)/4)*16;case dl:return Math.floor((n+3)/4)*Math.floor((e+3)/4)*16;case pl:return Math.floor((n+4)/5)*Math.floor((e+3)/4)*16;case ml:return Math.floor((n+4)/5)*Math.floor((e+4)/5)*16;case gl:return Math.floor((n+5)/6)*Math.floor((e+4)/5)*16;case _l:return Math.floor((n+5)/6)*Math.floor((e+5)/6)*16;case xl:return Math.floor((n+7)/8)*Math.floor((e+4)/5)*16;case vl:return Math.floor((n+7)/8)*Math.floor((e+5)/6)*16;case Ml:return Math.floor((n+7)/8)*Math.floor((e+7)/8)*16;case Sl:return Math.floor((n+9)/10)*Math.floor((e+4)/5)*16;case yl:return Math.floor((n+9)/10)*Math.floor((e+5)/6)*16;case bl:return Math.floor((n+9)/10)*Math.floor((e+7)/8)*16;case El:return Math.floor((n+9)/10)*Math.floor((e+9)/10)*16;case Tl:return Math.floor((n+11)/12)*Math.floor((e+9)/10)*16;case Al:return Math.floor((n+11)/12)*Math.floor((e+11)/12)*16;case wl:case Cl:case Rl:return Math.ceil(n/4)*Math.ceil(e/4)*16;case Pl:case Dl:return Math.ceil(n/4)*Math.ceil(e/4)*8;case Ll:case Il:return Math.ceil(n/4)*Math.ceil(e/4)*16}throw new Error(`Unable to determine texture byte length for ${t} format.`)}function g_(n){switch(n){case Jt:case Ff:return{byteLength:1,components:1};case hr:case Of:case ri:return{byteLength:2,components:1};case sc:case rc:return{byteLength:2,components:4};case On:case ic:case pn:return{byteLength:4,components:1};case Bf:case zf:return{byteLength:4,components:3}}throw new Error(`Unknown texture type ${n}.`)}typeof __THREE_DEVTOOLS__<"u"&&__THREE_DEVTOOLS__.dispatchEvent(new CustomEvent("register",{detail:{revision:nc}}));typeof window<"u"&&(window.__THREE__?Oe("WARNING: Multiple instances of Three.js being imported."):window.__THREE__=nc);function td(){let n=null,e=!1,t=null,i=null;function s(r,a){t(r,a),i=n.requestAnimationFrame(s)}return{start:function(){e!==!0&&t!==null&&(i=n.requestAnimationFrame(s),e=!0)},stop:function(){n.cancelAnimationFrame(i),e=!1},setAnimationLoop:function(r){t=r},setContext:function(r){n=r}}}function __(n){const e=new WeakMap;function t(o,l){const c=o.array,u=o.usage,f=c.byteLength,h=n.createBuffer();n.bindBuffer(l,h),n.bufferData(l,c,u),o.onUploadCallback();let p;if(c instanceof Float32Array)p=n.FLOAT;else if(typeof Float16Array<"u"&&c instanceof Float16Array)p=n.HALF_FLOAT;else if(c instanceof Uint16Array)o.isFloat16BufferAttribute?p=n.HALF_FLOAT:p=n.UNSIGNED_SHORT;else if(c instanceof Int16Array)p=n.SHORT;else if(c instanceof Uint32Array)p=n.UNSIGNED_INT;else if(c instanceof Int32Array)p=n.INT;else if(c instanceof Int8Array)p=n.BYTE;else if(c instanceof Uint8Array)p=n.UNSIGNED_BYTE;else if(c instanceof Uint8ClampedArray)p=n.UNSIGNED_BYTE;else throw new Error("THREE.WebGLAttributes: Unsupported buffer data format: "+c);return{buffer:h,type:p,bytesPerElement:c.BYTES_PER_ELEMENT,version:o.version,size:f}}function i(o,l,c){const u=l.array,f=l.updateRanges;if(n.bindBuffer(c,o),f.length===0)n.bufferSubData(c,0,u);else{f.sort((p,_)=>p.start-_.start);let h=0;for(let p=1;p<f.length;p++){const _=f[h],M=f[p];M.start<=_.start+_.count+1?_.count=Math.max(_.count,M.start+M.count-_.start):(++h,f[h]=M)}f.length=h+1;for(let p=0,_=f.length;p<_;p++){const M=f[p];n.bufferSubData(c,M.start*u.BYTES_PER_ELEMENT,u,M.start,M.count)}l.clearUpdateRanges()}l.onUploadCallback()}function s(o){return o.isInterleavedBufferAttribute&&(o=o.data),e.get(o)}function r(o){o.isInterleavedBufferAttribute&&(o=o.data);const l=e.get(o);l&&(n.deleteBuffer(l.buffer),e.delete(o))}function a(o,l){if(o.isInterleavedBufferAttribute&&(o=o.data),o.isGLBufferAttribute){const u=e.get(o);(!u||u.version<o.version)&&e.set(o,{buffer:o.buffer,type:o.type,bytesPerElement:o.elementSize,version:o.version});return}const c=e.get(o);if(c===void 0)e.set(o,t(o,l));else if(c.version<o.version){if(c.size!==o.array.byteLength)throw new Error("THREE.WebGLAttributes: The size of the buffer attribute's array buffer does not match the original size. Resizing buffer attributes is not supported.");i(c.buffer,o,l),c.version=o.version}}return{get:s,remove:r,update:a}}var x_=`#ifdef USE_ALPHAHASH
	if ( diffuseColor.a < getAlphaHashThreshold( vPosition ) ) discard;
#endif`,v_=`#ifdef USE_ALPHAHASH
	const float ALPHA_HASH_SCALE = 0.05;
	float hash2D( vec2 value ) {
		return fract( 1.0e4 * sin( 17.0 * value.x + 0.1 * value.y ) * ( 0.1 + abs( sin( 13.0 * value.y + value.x ) ) ) );
	}
	float hash3D( vec3 value ) {
		return hash2D( vec2( hash2D( value.xy ), value.z ) );
	}
	float getAlphaHashThreshold( vec3 position ) {
		float maxDeriv = max(
			length( dFdx( position.xyz ) ),
			length( dFdy( position.xyz ) )
		);
		float pixScale = 1.0 / ( ALPHA_HASH_SCALE * maxDeriv );
		vec2 pixScales = vec2(
			exp2( floor( log2( pixScale ) ) ),
			exp2( ceil( log2( pixScale ) ) )
		);
		vec2 alpha = vec2(
			hash3D( floor( pixScales.x * position.xyz ) ),
			hash3D( floor( pixScales.y * position.xyz ) )
		);
		float lerpFactor = fract( log2( pixScale ) );
		float x = ( 1.0 - lerpFactor ) * alpha.x + lerpFactor * alpha.y;
		float a = min( lerpFactor, 1.0 - lerpFactor );
		vec3 cases = vec3(
			x * x / ( 2.0 * a * ( 1.0 - a ) ),
			( x - 0.5 * a ) / ( 1.0 - a ),
			1.0 - ( ( 1.0 - x ) * ( 1.0 - x ) / ( 2.0 * a * ( 1.0 - a ) ) )
		);
		float threshold = ( x < ( 1.0 - a ) )
			? ( ( x < a ) ? cases.x : cases.y )
			: cases.z;
		return clamp( threshold , 1.0e-6, 1.0 );
	}
#endif`,M_=`#ifdef USE_ALPHAMAP
	diffuseColor.a *= texture2D( alphaMap, vAlphaMapUv ).g;
#endif`,S_=`#ifdef USE_ALPHAMAP
	uniform sampler2D alphaMap;
#endif`,y_=`#ifdef USE_ALPHATEST
	#ifdef ALPHA_TO_COVERAGE
	diffuseColor.a = smoothstep( alphaTest, alphaTest + fwidth( diffuseColor.a ), diffuseColor.a );
	if ( diffuseColor.a == 0.0 ) discard;
	#else
	if ( diffuseColor.a < alphaTest ) discard;
	#endif
#endif`,b_=`#ifdef USE_ALPHATEST
	uniform float alphaTest;
#endif`,E_=`#ifdef USE_AOMAP
	float ambientOcclusion = ( texture2D( aoMap, vAoMapUv ).r - 1.0 ) * aoMapIntensity + 1.0;
	reflectedLight.indirectDiffuse *= ambientOcclusion;
	#if defined( USE_CLEARCOAT ) 
		clearcoatSpecularIndirect *= ambientOcclusion;
	#endif
	#if defined( USE_SHEEN ) 
		sheenSpecularIndirect *= ambientOcclusion;
	#endif
	#if defined( USE_ENVMAP ) && defined( STANDARD )
		float dotNV = saturate( dot( geometryNormal, geometryViewDir ) );
		reflectedLight.indirectSpecular *= computeSpecularOcclusion( dotNV, ambientOcclusion, material.roughness );
	#endif
#endif`,T_=`#ifdef USE_AOMAP
	uniform sampler2D aoMap;
	uniform float aoMapIntensity;
#endif`,A_=`#ifdef USE_BATCHING
	#if ! defined( GL_ANGLE_multi_draw )
	#define gl_DrawID _gl_DrawID
	uniform int _gl_DrawID;
	#endif
	uniform highp sampler2D batchingTexture;
	uniform highp usampler2D batchingIdTexture;
	mat4 getBatchingMatrix( const in float i ) {
		int size = textureSize( batchingTexture, 0 ).x;
		int j = int( i ) * 4;
		int x = j % size;
		int y = j / size;
		vec4 v1 = texelFetch( batchingTexture, ivec2( x, y ), 0 );
		vec4 v2 = texelFetch( batchingTexture, ivec2( x + 1, y ), 0 );
		vec4 v3 = texelFetch( batchingTexture, ivec2( x + 2, y ), 0 );
		vec4 v4 = texelFetch( batchingTexture, ivec2( x + 3, y ), 0 );
		return mat4( v1, v2, v3, v4 );
	}
	float getIndirectIndex( const in int i ) {
		int size = textureSize( batchingIdTexture, 0 ).x;
		int x = i % size;
		int y = i / size;
		return float( texelFetch( batchingIdTexture, ivec2( x, y ), 0 ).r );
	}
#endif
#ifdef USE_BATCHING_COLOR
	uniform sampler2D batchingColorTexture;
	vec4 getBatchingColor( const in float i ) {
		int size = textureSize( batchingColorTexture, 0 ).x;
		int j = int( i );
		int x = j % size;
		int y = j / size;
		return texelFetch( batchingColorTexture, ivec2( x, y ), 0 );
	}
#endif`,w_=`#ifdef USE_BATCHING
	mat4 batchingMatrix = getBatchingMatrix( getIndirectIndex( gl_DrawID ) );
#endif`,C_=`vec3 transformed = vec3( position );
#ifdef USE_ALPHAHASH
	vPosition = vec3( position );
#endif`,R_=`vec3 objectNormal = vec3( normal );
#ifdef USE_TANGENT
	vec3 objectTangent = vec3( tangent.xyz );
#endif`,P_=`float G_BlinnPhong_Implicit( ) {
	return 0.25;
}
float D_BlinnPhong( const in float shininess, const in float dotNH ) {
	return RECIPROCAL_PI * ( shininess * 0.5 + 1.0 ) * pow( dotNH, shininess );
}
vec3 BRDF_BlinnPhong( const in vec3 lightDir, const in vec3 viewDir, const in vec3 normal, const in vec3 specularColor, const in float shininess ) {
	vec3 halfDir = normalize( lightDir + viewDir );
	float dotNH = saturate( dot( normal, halfDir ) );
	float dotVH = saturate( dot( viewDir, halfDir ) );
	vec3 F = F_Schlick( specularColor, 1.0, dotVH );
	float G = G_BlinnPhong_Implicit( );
	float D = D_BlinnPhong( shininess, dotNH );
	return F * ( G * D );
} // validated`,D_=`#ifdef USE_IRIDESCENCE
	const mat3 XYZ_TO_REC709 = mat3(
		 3.2404542, -0.9692660,  0.0556434,
		-1.5371385,  1.8760108, -0.2040259,
		-0.4985314,  0.0415560,  1.0572252
	);
	vec3 Fresnel0ToIor( vec3 fresnel0 ) {
		vec3 sqrtF0 = sqrt( fresnel0 );
		return ( vec3( 1.0 ) + sqrtF0 ) / ( vec3( 1.0 ) - sqrtF0 );
	}
	vec3 IorToFresnel0( vec3 transmittedIor, float incidentIor ) {
		return pow2( ( transmittedIor - vec3( incidentIor ) ) / ( transmittedIor + vec3( incidentIor ) ) );
	}
	float IorToFresnel0( float transmittedIor, float incidentIor ) {
		return pow2( ( transmittedIor - incidentIor ) / ( transmittedIor + incidentIor ));
	}
	vec3 evalSensitivity( float OPD, vec3 shift ) {
		float phase = 2.0 * PI * OPD * 1.0e-9;
		vec3 val = vec3( 5.4856e-13, 4.4201e-13, 5.2481e-13 );
		vec3 pos = vec3( 1.6810e+06, 1.7953e+06, 2.2084e+06 );
		vec3 var = vec3( 4.3278e+09, 9.3046e+09, 6.6121e+09 );
		vec3 xyz = val * sqrt( 2.0 * PI * var ) * cos( pos * phase + shift ) * exp( - pow2( phase ) * var );
		xyz.x += 9.7470e-14 * sqrt( 2.0 * PI * 4.5282e+09 ) * cos( 2.2399e+06 * phase + shift[ 0 ] ) * exp( - 4.5282e+09 * pow2( phase ) );
		xyz /= 1.0685e-7;
		vec3 rgb = XYZ_TO_REC709 * xyz;
		return rgb;
	}
	vec3 evalIridescence( float outsideIOR, float eta2, float cosTheta1, float thinFilmThickness, vec3 baseF0 ) {
		vec3 I;
		float iridescenceIOR = mix( outsideIOR, eta2, smoothstep( 0.0, 0.03, thinFilmThickness ) );
		float sinTheta2Sq = pow2( outsideIOR / iridescenceIOR ) * ( 1.0 - pow2( cosTheta1 ) );
		float cosTheta2Sq = 1.0 - sinTheta2Sq;
		if ( cosTheta2Sq < 0.0 ) {
			return vec3( 1.0 );
		}
		float cosTheta2 = sqrt( cosTheta2Sq );
		float R0 = IorToFresnel0( iridescenceIOR, outsideIOR );
		float R12 = F_Schlick( R0, 1.0, cosTheta1 );
		float T121 = 1.0 - R12;
		float phi12 = 0.0;
		if ( iridescenceIOR < outsideIOR ) phi12 = PI;
		float phi21 = PI - phi12;
		vec3 baseIOR = Fresnel0ToIor( clamp( baseF0, 0.0, 0.9999 ) );		vec3 R1 = IorToFresnel0( baseIOR, iridescenceIOR );
		vec3 R23 = F_Schlick( R1, 1.0, cosTheta2 );
		vec3 phi23 = vec3( 0.0 );
		if ( baseIOR[ 0 ] < iridescenceIOR ) phi23[ 0 ] = PI;
		if ( baseIOR[ 1 ] < iridescenceIOR ) phi23[ 1 ] = PI;
		if ( baseIOR[ 2 ] < iridescenceIOR ) phi23[ 2 ] = PI;
		float OPD = 2.0 * iridescenceIOR * thinFilmThickness * cosTheta2;
		vec3 phi = vec3( phi21 ) + phi23;
		vec3 R123 = clamp( R12 * R23, 1e-5, 0.9999 );
		vec3 r123 = sqrt( R123 );
		vec3 Rs = pow2( T121 ) * R23 / ( vec3( 1.0 ) - R123 );
		vec3 C0 = R12 + Rs;
		I = C0;
		vec3 Cm = Rs - T121;
		for ( int m = 1; m <= 2; ++ m ) {
			Cm *= r123;
			vec3 Sm = 2.0 * evalSensitivity( float( m ) * OPD, float( m ) * phi );
			I += Cm * Sm;
		}
		return max( I, vec3( 0.0 ) );
	}
#endif`,L_=`#ifdef USE_BUMPMAP
	uniform sampler2D bumpMap;
	uniform float bumpScale;
	vec2 dHdxy_fwd() {
		vec2 dSTdx = dFdx( vBumpMapUv );
		vec2 dSTdy = dFdy( vBumpMapUv );
		float Hll = bumpScale * texture2D( bumpMap, vBumpMapUv ).x;
		float dBx = bumpScale * texture2D( bumpMap, vBumpMapUv + dSTdx ).x - Hll;
		float dBy = bumpScale * texture2D( bumpMap, vBumpMapUv + dSTdy ).x - Hll;
		return vec2( dBx, dBy );
	}
	vec3 perturbNormalArb( vec3 surf_pos, vec3 surf_norm, vec2 dHdxy, float faceDirection ) {
		vec3 vSigmaX = normalize( dFdx( surf_pos.xyz ) );
		vec3 vSigmaY = normalize( dFdy( surf_pos.xyz ) );
		vec3 vN = surf_norm;
		vec3 R1 = cross( vSigmaY, vN );
		vec3 R2 = cross( vN, vSigmaX );
		float fDet = dot( vSigmaX, R1 ) * faceDirection;
		vec3 vGrad = sign( fDet ) * ( dHdxy.x * R1 + dHdxy.y * R2 );
		return normalize( abs( fDet ) * surf_norm - vGrad );
	}
#endif`,I_=`#if NUM_CLIPPING_PLANES > 0
	vec4 plane;
	#ifdef ALPHA_TO_COVERAGE
		float distanceToPlane, distanceGradient;
		float clipOpacity = 1.0;
		#pragma unroll_loop_start
		for ( int i = 0; i < UNION_CLIPPING_PLANES; i ++ ) {
			plane = clippingPlanes[ i ];
			distanceToPlane = - dot( vClipPosition, plane.xyz ) + plane.w;
			distanceGradient = fwidth( distanceToPlane ) / 2.0;
			clipOpacity *= smoothstep( - distanceGradient, distanceGradient, distanceToPlane );
			if ( clipOpacity == 0.0 ) discard;
		}
		#pragma unroll_loop_end
		#if UNION_CLIPPING_PLANES < NUM_CLIPPING_PLANES
			float unionClipOpacity = 1.0;
			#pragma unroll_loop_start
			for ( int i = UNION_CLIPPING_PLANES; i < NUM_CLIPPING_PLANES; i ++ ) {
				plane = clippingPlanes[ i ];
				distanceToPlane = - dot( vClipPosition, plane.xyz ) + plane.w;
				distanceGradient = fwidth( distanceToPlane ) / 2.0;
				unionClipOpacity *= 1.0 - smoothstep( - distanceGradient, distanceGradient, distanceToPlane );
			}
			#pragma unroll_loop_end
			clipOpacity *= 1.0 - unionClipOpacity;
		#endif
		diffuseColor.a *= clipOpacity;
		if ( diffuseColor.a == 0.0 ) discard;
	#else
		#pragma unroll_loop_start
		for ( int i = 0; i < UNION_CLIPPING_PLANES; i ++ ) {
			plane = clippingPlanes[ i ];
			if ( dot( vClipPosition, plane.xyz ) > plane.w ) discard;
		}
		#pragma unroll_loop_end
		#if UNION_CLIPPING_PLANES < NUM_CLIPPING_PLANES
			bool clipped = true;
			#pragma unroll_loop_start
			for ( int i = UNION_CLIPPING_PLANES; i < NUM_CLIPPING_PLANES; i ++ ) {
				plane = clippingPlanes[ i ];
				clipped = ( dot( vClipPosition, plane.xyz ) > plane.w ) && clipped;
			}
			#pragma unroll_loop_end
			if ( clipped ) discard;
		#endif
	#endif
#endif`,U_=`#if NUM_CLIPPING_PLANES > 0
	varying vec3 vClipPosition;
	uniform vec4 clippingPlanes[ NUM_CLIPPING_PLANES ];
#endif`,N_=`#if NUM_CLIPPING_PLANES > 0
	varying vec3 vClipPosition;
#endif`,F_=`#if NUM_CLIPPING_PLANES > 0
	vClipPosition = - mvPosition.xyz;
#endif`,O_=`#if defined( USE_COLOR ) || defined( USE_COLOR_ALPHA )
	diffuseColor *= vColor;
#endif`,B_=`#if defined( USE_COLOR ) || defined( USE_COLOR_ALPHA )
	varying vec4 vColor;
#endif`,z_=`#if defined( USE_COLOR ) || defined( USE_COLOR_ALPHA ) || defined( USE_INSTANCING_COLOR ) || defined( USE_BATCHING_COLOR )
	varying vec4 vColor;
#endif`,H_=`#if defined( USE_COLOR ) || defined( USE_COLOR_ALPHA ) || defined( USE_INSTANCING_COLOR ) || defined( USE_BATCHING_COLOR )
	vColor = vec4( 1.0 );
#endif
#ifdef USE_COLOR_ALPHA
	vColor *= color;
#elif defined( USE_COLOR )
	vColor.rgb *= color;
#endif
#ifdef USE_INSTANCING_COLOR
	vColor.rgb *= instanceColor.rgb;
#endif
#ifdef USE_BATCHING_COLOR
	vColor *= getBatchingColor( getIndirectIndex( gl_DrawID ) );
#endif`,V_=`#define PI 3.141592653589793
#define PI2 6.283185307179586
#define PI_HALF 1.5707963267948966
#define RECIPROCAL_PI 0.3183098861837907
#define RECIPROCAL_PI2 0.15915494309189535
#define EPSILON 1e-6
#ifndef saturate
#define saturate( a ) clamp( a, 0.0, 1.0 )
#endif
#define whiteComplement( a ) ( 1.0 - saturate( a ) )
float pow2( const in float x ) { return x*x; }
vec3 pow2( const in vec3 x ) { return x*x; }
float pow3( const in float x ) { return x*x*x; }
float pow4( const in float x ) { float x2 = x*x; return x2*x2; }
float max3( const in vec3 v ) { return max( max( v.x, v.y ), v.z ); }
float average( const in vec3 v ) { return dot( v, vec3( 0.3333333 ) ); }
highp float rand( const in vec2 uv ) {
	const highp float a = 12.9898, b = 78.233, c = 43758.5453;
	highp float dt = dot( uv.xy, vec2( a,b ) ), sn = mod( dt, PI );
	return fract( sin( sn ) * c );
}
#ifdef HIGH_PRECISION
	float precisionSafeLength( vec3 v ) { return length( v ); }
#else
	float precisionSafeLength( vec3 v ) {
		float maxComponent = max3( abs( v ) );
		return length( v / maxComponent ) * maxComponent;
	}
#endif
struct IncidentLight {
	vec3 color;
	vec3 direction;
	bool visible;
};
struct ReflectedLight {
	vec3 directDiffuse;
	vec3 directSpecular;
	vec3 indirectDiffuse;
	vec3 indirectSpecular;
};
#ifdef USE_ALPHAHASH
	varying vec3 vPosition;
#endif
vec3 transformDirection( in vec3 dir, in mat4 matrix ) {
	return normalize( ( matrix * vec4( dir, 0.0 ) ).xyz );
}
vec3 inverseTransformDirection( in vec3 dir, in mat4 matrix ) {
	return normalize( ( vec4( dir, 0.0 ) * matrix ).xyz );
}
bool isPerspectiveMatrix( mat4 m ) {
	return m[ 2 ][ 3 ] == - 1.0;
}
vec2 equirectUv( in vec3 dir ) {
	float u = atan( dir.z, dir.x ) * RECIPROCAL_PI2 + 0.5;
	float v = asin( clamp( dir.y, - 1.0, 1.0 ) ) * RECIPROCAL_PI + 0.5;
	return vec2( u, v );
}
vec3 BRDF_Lambert( const in vec3 diffuseColor ) {
	return RECIPROCAL_PI * diffuseColor;
}
vec3 F_Schlick( const in vec3 f0, const in float f90, const in float dotVH ) {
	float fresnel = exp2( ( - 5.55473 * dotVH - 6.98316 ) * dotVH );
	return f0 * ( 1.0 - fresnel ) + ( f90 * fresnel );
}
float F_Schlick( const in float f0, const in float f90, const in float dotVH ) {
	float fresnel = exp2( ( - 5.55473 * dotVH - 6.98316 ) * dotVH );
	return f0 * ( 1.0 - fresnel ) + ( f90 * fresnel );
} // validated`,k_=`#ifdef ENVMAP_TYPE_CUBE_UV
	#define cubeUV_minMipLevel 4.0
	#define cubeUV_minTileSize 16.0
	float getFace( vec3 direction ) {
		vec3 absDirection = abs( direction );
		float face = - 1.0;
		if ( absDirection.x > absDirection.z ) {
			if ( absDirection.x > absDirection.y )
				face = direction.x > 0.0 ? 0.0 : 3.0;
			else
				face = direction.y > 0.0 ? 1.0 : 4.0;
		} else {
			if ( absDirection.z > absDirection.y )
				face = direction.z > 0.0 ? 2.0 : 5.0;
			else
				face = direction.y > 0.0 ? 1.0 : 4.0;
		}
		return face;
	}
	vec2 getUV( vec3 direction, float face ) {
		vec2 uv;
		if ( face == 0.0 ) {
			uv = vec2( direction.z, direction.y ) / abs( direction.x );
		} else if ( face == 1.0 ) {
			uv = vec2( - direction.x, - direction.z ) / abs( direction.y );
		} else if ( face == 2.0 ) {
			uv = vec2( - direction.x, direction.y ) / abs( direction.z );
		} else if ( face == 3.0 ) {
			uv = vec2( - direction.z, direction.y ) / abs( direction.x );
		} else if ( face == 4.0 ) {
			uv = vec2( - direction.x, direction.z ) / abs( direction.y );
		} else {
			uv = vec2( direction.x, direction.y ) / abs( direction.z );
		}
		return 0.5 * ( uv + 1.0 );
	}
	vec3 bilinearCubeUV( sampler2D envMap, vec3 direction, float mipInt ) {
		float face = getFace( direction );
		float filterInt = max( cubeUV_minMipLevel - mipInt, 0.0 );
		mipInt = max( mipInt, cubeUV_minMipLevel );
		float faceSize = exp2( mipInt );
		highp vec2 uv = getUV( direction, face ) * ( faceSize - 2.0 ) + 1.0;
		if ( face > 2.0 ) {
			uv.y += faceSize;
			face -= 3.0;
		}
		uv.x += face * faceSize;
		uv.x += filterInt * 3.0 * cubeUV_minTileSize;
		uv.y += 4.0 * ( exp2( CUBEUV_MAX_MIP ) - faceSize );
		uv.x *= CUBEUV_TEXEL_WIDTH;
		uv.y *= CUBEUV_TEXEL_HEIGHT;
		#ifdef texture2DGradEXT
			return texture2DGradEXT( envMap, uv, vec2( 0.0 ), vec2( 0.0 ) ).rgb;
		#else
			return texture2D( envMap, uv ).rgb;
		#endif
	}
	#define cubeUV_r0 1.0
	#define cubeUV_m0 - 2.0
	#define cubeUV_r1 0.8
	#define cubeUV_m1 - 1.0
	#define cubeUV_r4 0.4
	#define cubeUV_m4 2.0
	#define cubeUV_r5 0.305
	#define cubeUV_m5 3.0
	#define cubeUV_r6 0.21
	#define cubeUV_m6 4.0
	float roughnessToMip( float roughness ) {
		float mip = 0.0;
		if ( roughness >= cubeUV_r1 ) {
			mip = ( cubeUV_r0 - roughness ) * ( cubeUV_m1 - cubeUV_m0 ) / ( cubeUV_r0 - cubeUV_r1 ) + cubeUV_m0;
		} else if ( roughness >= cubeUV_r4 ) {
			mip = ( cubeUV_r1 - roughness ) * ( cubeUV_m4 - cubeUV_m1 ) / ( cubeUV_r1 - cubeUV_r4 ) + cubeUV_m1;
		} else if ( roughness >= cubeUV_r5 ) {
			mip = ( cubeUV_r4 - roughness ) * ( cubeUV_m5 - cubeUV_m4 ) / ( cubeUV_r4 - cubeUV_r5 ) + cubeUV_m4;
		} else if ( roughness >= cubeUV_r6 ) {
			mip = ( cubeUV_r5 - roughness ) * ( cubeUV_m6 - cubeUV_m5 ) / ( cubeUV_r5 - cubeUV_r6 ) + cubeUV_m5;
		} else {
			mip = - 2.0 * log2( 1.16 * roughness );		}
		return mip;
	}
	vec4 textureCubeUV( sampler2D envMap, vec3 sampleDir, float roughness ) {
		float mip = clamp( roughnessToMip( roughness ), cubeUV_m0, CUBEUV_MAX_MIP );
		float mipF = fract( mip );
		float mipInt = floor( mip );
		vec3 color0 = bilinearCubeUV( envMap, sampleDir, mipInt );
		if ( mipF == 0.0 ) {
			return vec4( color0, 1.0 );
		} else {
			vec3 color1 = bilinearCubeUV( envMap, sampleDir, mipInt + 1.0 );
			return vec4( mix( color0, color1, mipF ), 1.0 );
		}
	}
#endif`,G_=`vec3 transformedNormal = objectNormal;
#ifdef USE_TANGENT
	vec3 transformedTangent = objectTangent;
#endif
#ifdef USE_BATCHING
	mat3 bm = mat3( batchingMatrix );
	transformedNormal /= vec3( dot( bm[ 0 ], bm[ 0 ] ), dot( bm[ 1 ], bm[ 1 ] ), dot( bm[ 2 ], bm[ 2 ] ) );
	transformedNormal = bm * transformedNormal;
	#ifdef USE_TANGENT
		transformedTangent = bm * transformedTangent;
	#endif
#endif
#ifdef USE_INSTANCING
	mat3 im = mat3( instanceMatrix );
	transformedNormal /= vec3( dot( im[ 0 ], im[ 0 ] ), dot( im[ 1 ], im[ 1 ] ), dot( im[ 2 ], im[ 2 ] ) );
	transformedNormal = im * transformedNormal;
	#ifdef USE_TANGENT
		transformedTangent = im * transformedTangent;
	#endif
#endif
transformedNormal = normalMatrix * transformedNormal;
#ifdef FLIP_SIDED
	transformedNormal = - transformedNormal;
#endif
#ifdef USE_TANGENT
	transformedTangent = ( modelViewMatrix * vec4( transformedTangent, 0.0 ) ).xyz;
	#ifdef FLIP_SIDED
		transformedTangent = - transformedTangent;
	#endif
#endif`,W_=`#ifdef USE_DISPLACEMENTMAP
	uniform sampler2D displacementMap;
	uniform float displacementScale;
	uniform float displacementBias;
#endif`,X_=`#ifdef USE_DISPLACEMENTMAP
	transformed += normalize( objectNormal ) * ( texture2D( displacementMap, vDisplacementMapUv ).x * displacementScale + displacementBias );
#endif`,Y_=`#ifdef USE_EMISSIVEMAP
	vec4 emissiveColor = texture2D( emissiveMap, vEmissiveMapUv );
	#ifdef DECODE_VIDEO_TEXTURE_EMISSIVE
		emissiveColor = sRGBTransferEOTF( emissiveColor );
	#endif
	totalEmissiveRadiance *= emissiveColor.rgb;
#endif`,q_=`#ifdef USE_EMISSIVEMAP
	uniform sampler2D emissiveMap;
#endif`,j_="gl_FragColor = linearToOutputTexel( gl_FragColor );",K_=`vec4 LinearTransferOETF( in vec4 value ) {
	return value;
}
vec4 sRGBTransferEOTF( in vec4 value ) {
	return vec4( mix( pow( value.rgb * 0.9478672986 + vec3( 0.0521327014 ), vec3( 2.4 ) ), value.rgb * 0.0773993808, vec3( lessThanEqual( value.rgb, vec3( 0.04045 ) ) ) ), value.a );
}
vec4 sRGBTransferOETF( in vec4 value ) {
	return vec4( mix( pow( value.rgb, vec3( 0.41666 ) ) * 1.055 - vec3( 0.055 ), value.rgb * 12.92, vec3( lessThanEqual( value.rgb, vec3( 0.0031308 ) ) ) ), value.a );
}`,$_=`#ifdef USE_ENVMAP
	#ifdef ENV_WORLDPOS
		vec3 cameraToFrag;
		if ( isOrthographic ) {
			cameraToFrag = normalize( vec3( - viewMatrix[ 0 ][ 2 ], - viewMatrix[ 1 ][ 2 ], - viewMatrix[ 2 ][ 2 ] ) );
		} else {
			cameraToFrag = normalize( vWorldPosition - cameraPosition );
		}
		vec3 worldNormal = inverseTransformDirection( normal, viewMatrix );
		#ifdef ENVMAP_MODE_REFLECTION
			vec3 reflectVec = reflect( cameraToFrag, worldNormal );
		#else
			vec3 reflectVec = refract( cameraToFrag, worldNormal, refractionRatio );
		#endif
	#else
		vec3 reflectVec = vReflect;
	#endif
	#ifdef ENVMAP_TYPE_CUBE
		vec4 envColor = textureCube( envMap, envMapRotation * vec3( flipEnvMap * reflectVec.x, reflectVec.yz ) );
		#ifdef ENVMAP_BLENDING_MULTIPLY
			outgoingLight = mix( outgoingLight, outgoingLight * envColor.xyz, specularStrength * reflectivity );
		#elif defined( ENVMAP_BLENDING_MIX )
			outgoingLight = mix( outgoingLight, envColor.xyz, specularStrength * reflectivity );
		#elif defined( ENVMAP_BLENDING_ADD )
			outgoingLight += envColor.xyz * specularStrength * reflectivity;
		#endif
	#endif
#endif`,Z_=`#ifdef USE_ENVMAP
	uniform float envMapIntensity;
	uniform float flipEnvMap;
	uniform mat3 envMapRotation;
	#ifdef ENVMAP_TYPE_CUBE
		uniform samplerCube envMap;
	#else
		uniform sampler2D envMap;
	#endif
#endif`,J_=`#ifdef USE_ENVMAP
	uniform float reflectivity;
	#if defined( USE_BUMPMAP ) || defined( USE_NORMALMAP ) || defined( PHONG ) || defined( LAMBERT )
		#define ENV_WORLDPOS
	#endif
	#ifdef ENV_WORLDPOS
		varying vec3 vWorldPosition;
		uniform float refractionRatio;
	#else
		varying vec3 vReflect;
	#endif
#endif`,Q_=`#ifdef USE_ENVMAP
	#if defined( USE_BUMPMAP ) || defined( USE_NORMALMAP ) || defined( PHONG ) || defined( LAMBERT )
		#define ENV_WORLDPOS
	#endif
	#ifdef ENV_WORLDPOS
		
		varying vec3 vWorldPosition;
	#else
		varying vec3 vReflect;
		uniform float refractionRatio;
	#endif
#endif`,e0=`#ifdef USE_ENVMAP
	#ifdef ENV_WORLDPOS
		vWorldPosition = worldPosition.xyz;
	#else
		vec3 cameraToVertex;
		if ( isOrthographic ) {
			cameraToVertex = normalize( vec3( - viewMatrix[ 0 ][ 2 ], - viewMatrix[ 1 ][ 2 ], - viewMatrix[ 2 ][ 2 ] ) );
		} else {
			cameraToVertex = normalize( worldPosition.xyz - cameraPosition );
		}
		vec3 worldNormal = inverseTransformDirection( transformedNormal, viewMatrix );
		#ifdef ENVMAP_MODE_REFLECTION
			vReflect = reflect( cameraToVertex, worldNormal );
		#else
			vReflect = refract( cameraToVertex, worldNormal, refractionRatio );
		#endif
	#endif
#endif`,t0=`#ifdef USE_FOG
	vFogDepth = - mvPosition.z;
#endif`,n0=`#ifdef USE_FOG
	varying float vFogDepth;
#endif`,i0=`#ifdef USE_FOG
	#ifdef FOG_EXP2
		float fogFactor = 1.0 - exp( - fogDensity * fogDensity * vFogDepth * vFogDepth );
	#else
		float fogFactor = smoothstep( fogNear, fogFar, vFogDepth );
	#endif
	gl_FragColor.rgb = mix( gl_FragColor.rgb, fogColor, fogFactor );
#endif`,s0=`#ifdef USE_FOG
	uniform vec3 fogColor;
	varying float vFogDepth;
	#ifdef FOG_EXP2
		uniform float fogDensity;
	#else
		uniform float fogNear;
		uniform float fogFar;
	#endif
#endif`,r0=`#ifdef USE_GRADIENTMAP
	uniform sampler2D gradientMap;
#endif
vec3 getGradientIrradiance( vec3 normal, vec3 lightDirection ) {
	float dotNL = dot( normal, lightDirection );
	vec2 coord = vec2( dotNL * 0.5 + 0.5, 0.0 );
	#ifdef USE_GRADIENTMAP
		return vec3( texture2D( gradientMap, coord ).r );
	#else
		vec2 fw = fwidth( coord ) * 0.5;
		return mix( vec3( 0.7 ), vec3( 1.0 ), smoothstep( 0.7 - fw.x, 0.7 + fw.x, coord.x ) );
	#endif
}`,a0=`#ifdef USE_LIGHTMAP
	uniform sampler2D lightMap;
	uniform float lightMapIntensity;
#endif`,o0=`LambertMaterial material;
material.diffuseColor = diffuseColor.rgb;
material.specularStrength = specularStrength;`,l0=`varying vec3 vViewPosition;
struct LambertMaterial {
	vec3 diffuseColor;
	float specularStrength;
};
void RE_Direct_Lambert( const in IncidentLight directLight, const in vec3 geometryPosition, const in vec3 geometryNormal, const in vec3 geometryViewDir, const in vec3 geometryClearcoatNormal, const in LambertMaterial material, inout ReflectedLight reflectedLight ) {
	float dotNL = saturate( dot( geometryNormal, directLight.direction ) );
	vec3 irradiance = dotNL * directLight.color;
	reflectedLight.directDiffuse += irradiance * BRDF_Lambert( material.diffuseColor );
}
void RE_IndirectDiffuse_Lambert( const in vec3 irradiance, const in vec3 geometryPosition, const in vec3 geometryNormal, const in vec3 geometryViewDir, const in vec3 geometryClearcoatNormal, const in LambertMaterial material, inout ReflectedLight reflectedLight ) {
	reflectedLight.indirectDiffuse += irradiance * BRDF_Lambert( material.diffuseColor );
}
#define RE_Direct				RE_Direct_Lambert
#define RE_IndirectDiffuse		RE_IndirectDiffuse_Lambert`,c0=`uniform bool receiveShadow;
uniform vec3 ambientLightColor;
#if defined( USE_LIGHT_PROBES )
	uniform vec3 lightProbe[ 9 ];
#endif
vec3 shGetIrradianceAt( in vec3 normal, in vec3 shCoefficients[ 9 ] ) {
	float x = normal.x, y = normal.y, z = normal.z;
	vec3 result = shCoefficients[ 0 ] * 0.886227;
	result += shCoefficients[ 1 ] * 2.0 * 0.511664 * y;
	result += shCoefficients[ 2 ] * 2.0 * 0.511664 * z;
	result += shCoefficients[ 3 ] * 2.0 * 0.511664 * x;
	result += shCoefficients[ 4 ] * 2.0 * 0.429043 * x * y;
	result += shCoefficients[ 5 ] * 2.0 * 0.429043 * y * z;
	result += shCoefficients[ 6 ] * ( 0.743125 * z * z - 0.247708 );
	result += shCoefficients[ 7 ] * 2.0 * 0.429043 * x * z;
	result += shCoefficients[ 8 ] * 0.429043 * ( x * x - y * y );
	return result;
}
vec3 getLightProbeIrradiance( const in vec3 lightProbe[ 9 ], const in vec3 normal ) {
	vec3 worldNormal = inverseTransformDirection( normal, viewMatrix );
	vec3 irradiance = shGetIrradianceAt( worldNormal, lightProbe );
	return irradiance;
}
vec3 getAmbientLightIrradiance( const in vec3 ambientLightColor ) {
	vec3 irradiance = ambientLightColor;
	return irradiance;
}
float getDistanceAttenuation( const in float lightDistance, const in float cutoffDistance, const in float decayExponent ) {
	float distanceFalloff = 1.0 / max( pow( lightDistance, decayExponent ), 0.01 );
	if ( cutoffDistance > 0.0 ) {
		distanceFalloff *= pow2( saturate( 1.0 - pow4( lightDistance / cutoffDistance ) ) );
	}
	return distanceFalloff;
}
float getSpotAttenuation( const in float coneCosine, const in float penumbraCosine, const in float angleCosine ) {
	return smoothstep( coneCosine, penumbraCosine, angleCosine );
}
#if NUM_DIR_LIGHTS > 0
	struct DirectionalLight {
		vec3 direction;
		vec3 color;
	};
	uniform DirectionalLight directionalLights[ NUM_DIR_LIGHTS ];
	void getDirectionalLightInfo( const in DirectionalLight directionalLight, out IncidentLight light ) {
		light.color = directionalLight.color;
		light.direction = directionalLight.direction;
		light.visible = true;
	}
#endif
#if NUM_POINT_LIGHTS > 0
	struct PointLight {
		vec3 position;
		vec3 color;
		float distance;
		float decay;
	};
	uniform PointLight pointLights[ NUM_POINT_LIGHTS ];
	void getPointLightInfo( const in PointLight pointLight, const in vec3 geometryPosition, out IncidentLight light ) {
		vec3 lVector = pointLight.position - geometryPosition;
		light.direction = normalize( lVector );
		float lightDistance = length( lVector );
		light.color = pointLight.color;
		light.color *= getDistanceAttenuation( lightDistance, pointLight.distance, pointLight.decay );
		light.visible = ( light.color != vec3( 0.0 ) );
	}
#endif
#if NUM_SPOT_LIGHTS > 0
	struct SpotLight {
		vec3 position;
		vec3 direction;
		vec3 color;
		float distance;
		float decay;
		float coneCos;
		float penumbraCos;
	};
	uniform SpotLight spotLights[ NUM_SPOT_LIGHTS ];
	void getSpotLightInfo( const in SpotLight spotLight, const in vec3 geometryPosition, out IncidentLight light ) {
		vec3 lVector = spotLight.position - geometryPosition;
		light.direction = normalize( lVector );
		float angleCos = dot( light.direction, spotLight.direction );
		float spotAttenuation = getSpotAttenuation( spotLight.coneCos, spotLight.penumbraCos, angleCos );
		if ( spotAttenuation > 0.0 ) {
			float lightDistance = length( lVector );
			light.color = spotLight.color * spotAttenuation;
			light.color *= getDistanceAttenuation( lightDistance, spotLight.distance, spotLight.decay );
			light.visible = ( light.color != vec3( 0.0 ) );
		} else {
			light.color = vec3( 0.0 );
			light.visible = false;
		}
	}
#endif
#if NUM_RECT_AREA_LIGHTS > 0
	struct RectAreaLight {
		vec3 color;
		vec3 position;
		vec3 halfWidth;
		vec3 halfHeight;
	};
	uniform sampler2D ltc_1;	uniform sampler2D ltc_2;
	uniform RectAreaLight rectAreaLights[ NUM_RECT_AREA_LIGHTS ];
#endif
#if NUM_HEMI_LIGHTS > 0
	struct HemisphereLight {
		vec3 direction;
		vec3 skyColor;
		vec3 groundColor;
	};
	uniform HemisphereLight hemisphereLights[ NUM_HEMI_LIGHTS ];
	vec3 getHemisphereLightIrradiance( const in HemisphereLight hemiLight, const in vec3 normal ) {
		float dotNL = dot( normal, hemiLight.direction );
		float hemiDiffuseWeight = 0.5 * dotNL + 0.5;
		vec3 irradiance = mix( hemiLight.groundColor, hemiLight.skyColor, hemiDiffuseWeight );
		return irradiance;
	}
#endif`,u0=`#ifdef USE_ENVMAP
	vec3 getIBLIrradiance( const in vec3 normal ) {
		#ifdef ENVMAP_TYPE_CUBE_UV
			vec3 worldNormal = inverseTransformDirection( normal, viewMatrix );
			vec4 envMapColor = textureCubeUV( envMap, envMapRotation * worldNormal, 1.0 );
			return PI * envMapColor.rgb * envMapIntensity;
		#else
			return vec3( 0.0 );
		#endif
	}
	vec3 getIBLRadiance( const in vec3 viewDir, const in vec3 normal, const in float roughness ) {
		#ifdef ENVMAP_TYPE_CUBE_UV
			vec3 reflectVec = reflect( - viewDir, normal );
			reflectVec = normalize( mix( reflectVec, normal, pow4( roughness ) ) );
			reflectVec = inverseTransformDirection( reflectVec, viewMatrix );
			vec4 envMapColor = textureCubeUV( envMap, envMapRotation * reflectVec, roughness );
			return envMapColor.rgb * envMapIntensity;
		#else
			return vec3( 0.0 );
		#endif
	}
	#ifdef USE_ANISOTROPY
		vec3 getIBLAnisotropyRadiance( const in vec3 viewDir, const in vec3 normal, const in float roughness, const in vec3 bitangent, const in float anisotropy ) {
			#ifdef ENVMAP_TYPE_CUBE_UV
				vec3 bentNormal = cross( bitangent, viewDir );
				bentNormal = normalize( cross( bentNormal, bitangent ) );
				bentNormal = normalize( mix( bentNormal, normal, pow2( pow2( 1.0 - anisotropy * ( 1.0 - roughness ) ) ) ) );
				return getIBLRadiance( viewDir, bentNormal, roughness );
			#else
				return vec3( 0.0 );
			#endif
		}
	#endif
#endif`,h0=`ToonMaterial material;
material.diffuseColor = diffuseColor.rgb;`,f0=`varying vec3 vViewPosition;
struct ToonMaterial {
	vec3 diffuseColor;
};
void RE_Direct_Toon( const in IncidentLight directLight, const in vec3 geometryPosition, const in vec3 geometryNormal, const in vec3 geometryViewDir, const in vec3 geometryClearcoatNormal, const in ToonMaterial material, inout ReflectedLight reflectedLight ) {
	vec3 irradiance = getGradientIrradiance( geometryNormal, directLight.direction ) * directLight.color;
	reflectedLight.directDiffuse += irradiance * BRDF_Lambert( material.diffuseColor );
}
void RE_IndirectDiffuse_Toon( const in vec3 irradiance, const in vec3 geometryPosition, const in vec3 geometryNormal, const in vec3 geometryViewDir, const in vec3 geometryClearcoatNormal, const in ToonMaterial material, inout ReflectedLight reflectedLight ) {
	reflectedLight.indirectDiffuse += irradiance * BRDF_Lambert( material.diffuseColor );
}
#define RE_Direct				RE_Direct_Toon
#define RE_IndirectDiffuse		RE_IndirectDiffuse_Toon`,d0=`BlinnPhongMaterial material;
material.diffuseColor = diffuseColor.rgb;
material.specularColor = specular;
material.specularShininess = shininess;
material.specularStrength = specularStrength;`,p0=`varying vec3 vViewPosition;
struct BlinnPhongMaterial {
	vec3 diffuseColor;
	vec3 specularColor;
	float specularShininess;
	float specularStrength;
};
void RE_Direct_BlinnPhong( const in IncidentLight directLight, const in vec3 geometryPosition, const in vec3 geometryNormal, const in vec3 geometryViewDir, const in vec3 geometryClearcoatNormal, const in BlinnPhongMaterial material, inout ReflectedLight reflectedLight ) {
	float dotNL = saturate( dot( geometryNormal, directLight.direction ) );
	vec3 irradiance = dotNL * directLight.color;
	reflectedLight.directDiffuse += irradiance * BRDF_Lambert( material.diffuseColor );
	reflectedLight.directSpecular += irradiance * BRDF_BlinnPhong( directLight.direction, geometryViewDir, geometryNormal, material.specularColor, material.specularShininess ) * material.specularStrength;
}
void RE_IndirectDiffuse_BlinnPhong( const in vec3 irradiance, const in vec3 geometryPosition, const in vec3 geometryNormal, const in vec3 geometryViewDir, const in vec3 geometryClearcoatNormal, const in BlinnPhongMaterial material, inout ReflectedLight reflectedLight ) {
	reflectedLight.indirectDiffuse += irradiance * BRDF_Lambert( material.diffuseColor );
}
#define RE_Direct				RE_Direct_BlinnPhong
#define RE_IndirectDiffuse		RE_IndirectDiffuse_BlinnPhong`,m0=`PhysicalMaterial material;
material.diffuseColor = diffuseColor.rgb;
material.diffuseContribution = diffuseColor.rgb * ( 1.0 - metalnessFactor );
material.metalness = metalnessFactor;
vec3 dxy = max( abs( dFdx( nonPerturbedNormal ) ), abs( dFdy( nonPerturbedNormal ) ) );
float geometryRoughness = max( max( dxy.x, dxy.y ), dxy.z );
material.roughness = max( roughnessFactor, 0.0525 );material.roughness += geometryRoughness;
material.roughness = min( material.roughness, 1.0 );
#ifdef IOR
	material.ior = ior;
	#ifdef USE_SPECULAR
		float specularIntensityFactor = specularIntensity;
		vec3 specularColorFactor = specularColor;
		#ifdef USE_SPECULAR_COLORMAP
			specularColorFactor *= texture2D( specularColorMap, vSpecularColorMapUv ).rgb;
		#endif
		#ifdef USE_SPECULAR_INTENSITYMAP
			specularIntensityFactor *= texture2D( specularIntensityMap, vSpecularIntensityMapUv ).a;
		#endif
		material.specularF90 = mix( specularIntensityFactor, 1.0, metalnessFactor );
	#else
		float specularIntensityFactor = 1.0;
		vec3 specularColorFactor = vec3( 1.0 );
		material.specularF90 = 1.0;
	#endif
	material.specularColor = min( pow2( ( material.ior - 1.0 ) / ( material.ior + 1.0 ) ) * specularColorFactor, vec3( 1.0 ) ) * specularIntensityFactor;
	material.specularColorBlended = mix( material.specularColor, diffuseColor.rgb, metalnessFactor );
#else
	material.specularColor = vec3( 0.04 );
	material.specularColorBlended = mix( material.specularColor, diffuseColor.rgb, metalnessFactor );
	material.specularF90 = 1.0;
#endif
#ifdef USE_CLEARCOAT
	material.clearcoat = clearcoat;
	material.clearcoatRoughness = clearcoatRoughness;
	material.clearcoatF0 = vec3( 0.04 );
	material.clearcoatF90 = 1.0;
	#ifdef USE_CLEARCOATMAP
		material.clearcoat *= texture2D( clearcoatMap, vClearcoatMapUv ).x;
	#endif
	#ifdef USE_CLEARCOAT_ROUGHNESSMAP
		material.clearcoatRoughness *= texture2D( clearcoatRoughnessMap, vClearcoatRoughnessMapUv ).y;
	#endif
	material.clearcoat = saturate( material.clearcoat );	material.clearcoatRoughness = max( material.clearcoatRoughness, 0.0525 );
	material.clearcoatRoughness += geometryRoughness;
	material.clearcoatRoughness = min( material.clearcoatRoughness, 1.0 );
#endif
#ifdef USE_DISPERSION
	material.dispersion = dispersion;
#endif
#ifdef USE_IRIDESCENCE
	material.iridescence = iridescence;
	material.iridescenceIOR = iridescenceIOR;
	#ifdef USE_IRIDESCENCEMAP
		material.iridescence *= texture2D( iridescenceMap, vIridescenceMapUv ).r;
	#endif
	#ifdef USE_IRIDESCENCE_THICKNESSMAP
		material.iridescenceThickness = (iridescenceThicknessMaximum - iridescenceThicknessMinimum) * texture2D( iridescenceThicknessMap, vIridescenceThicknessMapUv ).g + iridescenceThicknessMinimum;
	#else
		material.iridescenceThickness = iridescenceThicknessMaximum;
	#endif
#endif
#ifdef USE_SHEEN
	material.sheenColor = sheenColor;
	#ifdef USE_SHEEN_COLORMAP
		material.sheenColor *= texture2D( sheenColorMap, vSheenColorMapUv ).rgb;
	#endif
	material.sheenRoughness = clamp( sheenRoughness, 0.0001, 1.0 );
	#ifdef USE_SHEEN_ROUGHNESSMAP
		material.sheenRoughness *= texture2D( sheenRoughnessMap, vSheenRoughnessMapUv ).a;
	#endif
#endif
#ifdef USE_ANISOTROPY
	#ifdef USE_ANISOTROPYMAP
		mat2 anisotropyMat = mat2( anisotropyVector.x, anisotropyVector.y, - anisotropyVector.y, anisotropyVector.x );
		vec3 anisotropyPolar = texture2D( anisotropyMap, vAnisotropyMapUv ).rgb;
		vec2 anisotropyV = anisotropyMat * normalize( 2.0 * anisotropyPolar.rg - vec2( 1.0 ) ) * anisotropyPolar.b;
	#else
		vec2 anisotropyV = anisotropyVector;
	#endif
	material.anisotropy = length( anisotropyV );
	if( material.anisotropy == 0.0 ) {
		anisotropyV = vec2( 1.0, 0.0 );
	} else {
		anisotropyV /= material.anisotropy;
		material.anisotropy = saturate( material.anisotropy );
	}
	material.alphaT = mix( pow2( material.roughness ), 1.0, pow2( material.anisotropy ) );
	material.anisotropyT = tbn[ 0 ] * anisotropyV.x + tbn[ 1 ] * anisotropyV.y;
	material.anisotropyB = tbn[ 1 ] * anisotropyV.x - tbn[ 0 ] * anisotropyV.y;
#endif`,g0=`uniform sampler2D dfgLUT;
struct PhysicalMaterial {
	vec3 diffuseColor;
	vec3 diffuseContribution;
	vec3 specularColor;
	vec3 specularColorBlended;
	float roughness;
	float metalness;
	float specularF90;
	float dispersion;
	#ifdef USE_CLEARCOAT
		float clearcoat;
		float clearcoatRoughness;
		vec3 clearcoatF0;
		float clearcoatF90;
	#endif
	#ifdef USE_IRIDESCENCE
		float iridescence;
		float iridescenceIOR;
		float iridescenceThickness;
		vec3 iridescenceFresnel;
		vec3 iridescenceF0;
		vec3 iridescenceFresnelDielectric;
		vec3 iridescenceFresnelMetallic;
	#endif
	#ifdef USE_SHEEN
		vec3 sheenColor;
		float sheenRoughness;
	#endif
	#ifdef IOR
		float ior;
	#endif
	#ifdef USE_TRANSMISSION
		float transmission;
		float transmissionAlpha;
		float thickness;
		float attenuationDistance;
		vec3 attenuationColor;
	#endif
	#ifdef USE_ANISOTROPY
		float anisotropy;
		float alphaT;
		vec3 anisotropyT;
		vec3 anisotropyB;
	#endif
};
vec3 clearcoatSpecularDirect = vec3( 0.0 );
vec3 clearcoatSpecularIndirect = vec3( 0.0 );
vec3 sheenSpecularDirect = vec3( 0.0 );
vec3 sheenSpecularIndirect = vec3(0.0 );
vec3 Schlick_to_F0( const in vec3 f, const in float f90, const in float dotVH ) {
    float x = clamp( 1.0 - dotVH, 0.0, 1.0 );
    float x2 = x * x;
    float x5 = clamp( x * x2 * x2, 0.0, 0.9999 );
    return ( f - vec3( f90 ) * x5 ) / ( 1.0 - x5 );
}
float V_GGX_SmithCorrelated( const in float alpha, const in float dotNL, const in float dotNV ) {
	float a2 = pow2( alpha );
	float gv = dotNL * sqrt( a2 + ( 1.0 - a2 ) * pow2( dotNV ) );
	float gl = dotNV * sqrt( a2 + ( 1.0 - a2 ) * pow2( dotNL ) );
	return 0.5 / max( gv + gl, EPSILON );
}
float D_GGX( const in float alpha, const in float dotNH ) {
	float a2 = pow2( alpha );
	float denom = pow2( dotNH ) * ( a2 - 1.0 ) + 1.0;
	return RECIPROCAL_PI * a2 / pow2( denom );
}
#ifdef USE_ANISOTROPY
	float V_GGX_SmithCorrelated_Anisotropic( const in float alphaT, const in float alphaB, const in float dotTV, const in float dotBV, const in float dotTL, const in float dotBL, const in float dotNV, const in float dotNL ) {
		float gv = dotNL * length( vec3( alphaT * dotTV, alphaB * dotBV, dotNV ) );
		float gl = dotNV * length( vec3( alphaT * dotTL, alphaB * dotBL, dotNL ) );
		float v = 0.5 / ( gv + gl );
		return v;
	}
	float D_GGX_Anisotropic( const in float alphaT, const in float alphaB, const in float dotNH, const in float dotTH, const in float dotBH ) {
		float a2 = alphaT * alphaB;
		highp vec3 v = vec3( alphaB * dotTH, alphaT * dotBH, a2 * dotNH );
		highp float v2 = dot( v, v );
		float w2 = a2 / v2;
		return RECIPROCAL_PI * a2 * pow2 ( w2 );
	}
#endif
#ifdef USE_CLEARCOAT
	vec3 BRDF_GGX_Clearcoat( const in vec3 lightDir, const in vec3 viewDir, const in vec3 normal, const in PhysicalMaterial material) {
		vec3 f0 = material.clearcoatF0;
		float f90 = material.clearcoatF90;
		float roughness = material.clearcoatRoughness;
		float alpha = pow2( roughness );
		vec3 halfDir = normalize( lightDir + viewDir );
		float dotNL = saturate( dot( normal, lightDir ) );
		float dotNV = saturate( dot( normal, viewDir ) );
		float dotNH = saturate( dot( normal, halfDir ) );
		float dotVH = saturate( dot( viewDir, halfDir ) );
		vec3 F = F_Schlick( f0, f90, dotVH );
		float V = V_GGX_SmithCorrelated( alpha, dotNL, dotNV );
		float D = D_GGX( alpha, dotNH );
		return F * ( V * D );
	}
#endif
vec3 BRDF_GGX( const in vec3 lightDir, const in vec3 viewDir, const in vec3 normal, const in PhysicalMaterial material ) {
	vec3 f0 = material.specularColorBlended;
	float f90 = material.specularF90;
	float roughness = material.roughness;
	float alpha = pow2( roughness );
	vec3 halfDir = normalize( lightDir + viewDir );
	float dotNL = saturate( dot( normal, lightDir ) );
	float dotNV = saturate( dot( normal, viewDir ) );
	float dotNH = saturate( dot( normal, halfDir ) );
	float dotVH = saturate( dot( viewDir, halfDir ) );
	vec3 F = F_Schlick( f0, f90, dotVH );
	#ifdef USE_IRIDESCENCE
		F = mix( F, material.iridescenceFresnel, material.iridescence );
	#endif
	#ifdef USE_ANISOTROPY
		float dotTL = dot( material.anisotropyT, lightDir );
		float dotTV = dot( material.anisotropyT, viewDir );
		float dotTH = dot( material.anisotropyT, halfDir );
		float dotBL = dot( material.anisotropyB, lightDir );
		float dotBV = dot( material.anisotropyB, viewDir );
		float dotBH = dot( material.anisotropyB, halfDir );
		float V = V_GGX_SmithCorrelated_Anisotropic( material.alphaT, alpha, dotTV, dotBV, dotTL, dotBL, dotNV, dotNL );
		float D = D_GGX_Anisotropic( material.alphaT, alpha, dotNH, dotTH, dotBH );
	#else
		float V = V_GGX_SmithCorrelated( alpha, dotNL, dotNV );
		float D = D_GGX( alpha, dotNH );
	#endif
	return F * ( V * D );
}
vec2 LTC_Uv( const in vec3 N, const in vec3 V, const in float roughness ) {
	const float LUT_SIZE = 64.0;
	const float LUT_SCALE = ( LUT_SIZE - 1.0 ) / LUT_SIZE;
	const float LUT_BIAS = 0.5 / LUT_SIZE;
	float dotNV = saturate( dot( N, V ) );
	vec2 uv = vec2( roughness, sqrt( 1.0 - dotNV ) );
	uv = uv * LUT_SCALE + LUT_BIAS;
	return uv;
}
float LTC_ClippedSphereFormFactor( const in vec3 f ) {
	float l = length( f );
	return max( ( l * l + f.z ) / ( l + 1.0 ), 0.0 );
}
vec3 LTC_EdgeVectorFormFactor( const in vec3 v1, const in vec3 v2 ) {
	float x = dot( v1, v2 );
	float y = abs( x );
	float a = 0.8543985 + ( 0.4965155 + 0.0145206 * y ) * y;
	float b = 3.4175940 + ( 4.1616724 + y ) * y;
	float v = a / b;
	float theta_sintheta = ( x > 0.0 ) ? v : 0.5 * inversesqrt( max( 1.0 - x * x, 1e-7 ) ) - v;
	return cross( v1, v2 ) * theta_sintheta;
}
vec3 LTC_Evaluate( const in vec3 N, const in vec3 V, const in vec3 P, const in mat3 mInv, const in vec3 rectCoords[ 4 ] ) {
	vec3 v1 = rectCoords[ 1 ] - rectCoords[ 0 ];
	vec3 v2 = rectCoords[ 3 ] - rectCoords[ 0 ];
	vec3 lightNormal = cross( v1, v2 );
	if( dot( lightNormal, P - rectCoords[ 0 ] ) < 0.0 ) return vec3( 0.0 );
	vec3 T1, T2;
	T1 = normalize( V - N * dot( V, N ) );
	T2 = - cross( N, T1 );
	mat3 mat = mInv * transpose( mat3( T1, T2, N ) );
	vec3 coords[ 4 ];
	coords[ 0 ] = mat * ( rectCoords[ 0 ] - P );
	coords[ 1 ] = mat * ( rectCoords[ 1 ] - P );
	coords[ 2 ] = mat * ( rectCoords[ 2 ] - P );
	coords[ 3 ] = mat * ( rectCoords[ 3 ] - P );
	coords[ 0 ] = normalize( coords[ 0 ] );
	coords[ 1 ] = normalize( coords[ 1 ] );
	coords[ 2 ] = normalize( coords[ 2 ] );
	coords[ 3 ] = normalize( coords[ 3 ] );
	vec3 vectorFormFactor = vec3( 0.0 );
	vectorFormFactor += LTC_EdgeVectorFormFactor( coords[ 0 ], coords[ 1 ] );
	vectorFormFactor += LTC_EdgeVectorFormFactor( coords[ 1 ], coords[ 2 ] );
	vectorFormFactor += LTC_EdgeVectorFormFactor( coords[ 2 ], coords[ 3 ] );
	vectorFormFactor += LTC_EdgeVectorFormFactor( coords[ 3 ], coords[ 0 ] );
	float result = LTC_ClippedSphereFormFactor( vectorFormFactor );
	return vec3( result );
}
#if defined( USE_SHEEN )
float D_Charlie( float roughness, float dotNH ) {
	float alpha = pow2( roughness );
	float invAlpha = 1.0 / alpha;
	float cos2h = dotNH * dotNH;
	float sin2h = max( 1.0 - cos2h, 0.0078125 );
	return ( 2.0 + invAlpha ) * pow( sin2h, invAlpha * 0.5 ) / ( 2.0 * PI );
}
float V_Neubelt( float dotNV, float dotNL ) {
	return saturate( 1.0 / ( 4.0 * ( dotNL + dotNV - dotNL * dotNV ) ) );
}
vec3 BRDF_Sheen( const in vec3 lightDir, const in vec3 viewDir, const in vec3 normal, vec3 sheenColor, const in float sheenRoughness ) {
	vec3 halfDir = normalize( lightDir + viewDir );
	float dotNL = saturate( dot( normal, lightDir ) );
	float dotNV = saturate( dot( normal, viewDir ) );
	float dotNH = saturate( dot( normal, halfDir ) );
	float D = D_Charlie( sheenRoughness, dotNH );
	float V = V_Neubelt( dotNV, dotNL );
	return sheenColor * ( D * V );
}
#endif
float IBLSheenBRDF( const in vec3 normal, const in vec3 viewDir, const in float roughness ) {
	float dotNV = saturate( dot( normal, viewDir ) );
	float r2 = roughness * roughness;
	float rInv = 1.0 / ( roughness + 0.1 );
	float a = -1.9362 + 1.0678 * roughness + 0.4573 * r2 - 0.8469 * rInv;
	float b = -0.6014 + 0.5538 * roughness - 0.4670 * r2 - 0.1255 * rInv;
	float DG = exp( a * dotNV + b );
	return saturate( DG );
}
vec3 EnvironmentBRDF( const in vec3 normal, const in vec3 viewDir, const in vec3 specularColor, const in float specularF90, const in float roughness ) {
	float dotNV = saturate( dot( normal, viewDir ) );
	vec2 fab = texture2D( dfgLUT, vec2( roughness, dotNV ) ).rg;
	return specularColor * fab.x + specularF90 * fab.y;
}
#ifdef USE_IRIDESCENCE
void computeMultiscatteringIridescence( const in vec3 normal, const in vec3 viewDir, const in vec3 specularColor, const in float specularF90, const in float iridescence, const in vec3 iridescenceF0, const in float roughness, inout vec3 singleScatter, inout vec3 multiScatter ) {
#else
void computeMultiscattering( const in vec3 normal, const in vec3 viewDir, const in vec3 specularColor, const in float specularF90, const in float roughness, inout vec3 singleScatter, inout vec3 multiScatter ) {
#endif
	float dotNV = saturate( dot( normal, viewDir ) );
	vec2 fab = texture2D( dfgLUT, vec2( roughness, dotNV ) ).rg;
	#ifdef USE_IRIDESCENCE
		vec3 Fr = mix( specularColor, iridescenceF0, iridescence );
	#else
		vec3 Fr = specularColor;
	#endif
	vec3 FssEss = Fr * fab.x + specularF90 * fab.y;
	float Ess = fab.x + fab.y;
	float Ems = 1.0 - Ess;
	vec3 Favg = Fr + ( 1.0 - Fr ) * 0.047619;	vec3 Fms = FssEss * Favg / ( 1.0 - Ems * Favg );
	singleScatter += FssEss;
	multiScatter += Fms * Ems;
}
vec3 BRDF_GGX_Multiscatter( const in vec3 lightDir, const in vec3 viewDir, const in vec3 normal, const in PhysicalMaterial material ) {
	vec3 singleScatter = BRDF_GGX( lightDir, viewDir, normal, material );
	float dotNL = saturate( dot( normal, lightDir ) );
	float dotNV = saturate( dot( normal, viewDir ) );
	vec2 dfgV = texture2D( dfgLUT, vec2( material.roughness, dotNV ) ).rg;
	vec2 dfgL = texture2D( dfgLUT, vec2( material.roughness, dotNL ) ).rg;
	vec3 FssEss_V = material.specularColorBlended * dfgV.x + material.specularF90 * dfgV.y;
	vec3 FssEss_L = material.specularColorBlended * dfgL.x + material.specularF90 * dfgL.y;
	float Ess_V = dfgV.x + dfgV.y;
	float Ess_L = dfgL.x + dfgL.y;
	float Ems_V = 1.0 - Ess_V;
	float Ems_L = 1.0 - Ess_L;
	vec3 Favg = material.specularColorBlended + ( 1.0 - material.specularColorBlended ) * 0.047619;
	vec3 Fms = FssEss_V * FssEss_L * Favg / ( 1.0 - Ems_V * Ems_L * Favg + EPSILON );
	float compensationFactor = Ems_V * Ems_L;
	vec3 multiScatter = Fms * compensationFactor;
	return singleScatter + multiScatter;
}
#if NUM_RECT_AREA_LIGHTS > 0
	void RE_Direct_RectArea_Physical( const in RectAreaLight rectAreaLight, const in vec3 geometryPosition, const in vec3 geometryNormal, const in vec3 geometryViewDir, const in vec3 geometryClearcoatNormal, const in PhysicalMaterial material, inout ReflectedLight reflectedLight ) {
		vec3 normal = geometryNormal;
		vec3 viewDir = geometryViewDir;
		vec3 position = geometryPosition;
		vec3 lightPos = rectAreaLight.position;
		vec3 halfWidth = rectAreaLight.halfWidth;
		vec3 halfHeight = rectAreaLight.halfHeight;
		vec3 lightColor = rectAreaLight.color;
		float roughness = material.roughness;
		vec3 rectCoords[ 4 ];
		rectCoords[ 0 ] = lightPos + halfWidth - halfHeight;		rectCoords[ 1 ] = lightPos - halfWidth - halfHeight;
		rectCoords[ 2 ] = lightPos - halfWidth + halfHeight;
		rectCoords[ 3 ] = lightPos + halfWidth + halfHeight;
		vec2 uv = LTC_Uv( normal, viewDir, roughness );
		vec4 t1 = texture2D( ltc_1, uv );
		vec4 t2 = texture2D( ltc_2, uv );
		mat3 mInv = mat3(
			vec3( t1.x, 0, t1.y ),
			vec3(    0, 1,    0 ),
			vec3( t1.z, 0, t1.w )
		);
		vec3 fresnel = ( material.specularColorBlended * t2.x + ( material.specularF90 - material.specularColorBlended ) * t2.y );
		reflectedLight.directSpecular += lightColor * fresnel * LTC_Evaluate( normal, viewDir, position, mInv, rectCoords );
		reflectedLight.directDiffuse += lightColor * material.diffuseContribution * LTC_Evaluate( normal, viewDir, position, mat3( 1.0 ), rectCoords );
		#ifdef USE_CLEARCOAT
			vec3 Ncc = geometryClearcoatNormal;
			vec2 uvClearcoat = LTC_Uv( Ncc, viewDir, material.clearcoatRoughness );
			vec4 t1Clearcoat = texture2D( ltc_1, uvClearcoat );
			vec4 t2Clearcoat = texture2D( ltc_2, uvClearcoat );
			mat3 mInvClearcoat = mat3(
				vec3( t1Clearcoat.x, 0, t1Clearcoat.y ),
				vec3(             0, 1,             0 ),
				vec3( t1Clearcoat.z, 0, t1Clearcoat.w )
			);
			vec3 fresnelClearcoat = material.clearcoatF0 * t2Clearcoat.x + ( material.clearcoatF90 - material.clearcoatF0 ) * t2Clearcoat.y;
			clearcoatSpecularDirect += lightColor * fresnelClearcoat * LTC_Evaluate( Ncc, viewDir, position, mInvClearcoat, rectCoords );
		#endif
	}
#endif
void RE_Direct_Physical( const in IncidentLight directLight, const in vec3 geometryPosition, const in vec3 geometryNormal, const in vec3 geometryViewDir, const in vec3 geometryClearcoatNormal, const in PhysicalMaterial material, inout ReflectedLight reflectedLight ) {
	float dotNL = saturate( dot( geometryNormal, directLight.direction ) );
	vec3 irradiance = dotNL * directLight.color;
	#ifdef USE_CLEARCOAT
		float dotNLcc = saturate( dot( geometryClearcoatNormal, directLight.direction ) );
		vec3 ccIrradiance = dotNLcc * directLight.color;
		clearcoatSpecularDirect += ccIrradiance * BRDF_GGX_Clearcoat( directLight.direction, geometryViewDir, geometryClearcoatNormal, material );
	#endif
	#ifdef USE_SHEEN
 
 		sheenSpecularDirect += irradiance * BRDF_Sheen( directLight.direction, geometryViewDir, geometryNormal, material.sheenColor, material.sheenRoughness );
 
 		float sheenAlbedoV = IBLSheenBRDF( geometryNormal, geometryViewDir, material.sheenRoughness );
 		float sheenAlbedoL = IBLSheenBRDF( geometryNormal, directLight.direction, material.sheenRoughness );
 
 		float sheenEnergyComp = 1.0 - max3( material.sheenColor ) * max( sheenAlbedoV, sheenAlbedoL );
 
 		irradiance *= sheenEnergyComp;
 
 	#endif
	reflectedLight.directSpecular += irradiance * BRDF_GGX_Multiscatter( directLight.direction, geometryViewDir, geometryNormal, material );
	reflectedLight.directDiffuse += irradiance * BRDF_Lambert( material.diffuseContribution );
}
void RE_IndirectDiffuse_Physical( const in vec3 irradiance, const in vec3 geometryPosition, const in vec3 geometryNormal, const in vec3 geometryViewDir, const in vec3 geometryClearcoatNormal, const in PhysicalMaterial material, inout ReflectedLight reflectedLight ) {
	vec3 diffuse = irradiance * BRDF_Lambert( material.diffuseContribution );
	#ifdef USE_SHEEN
		float sheenAlbedo = IBLSheenBRDF( geometryNormal, geometryViewDir, material.sheenRoughness );
		float sheenEnergyComp = 1.0 - max3( material.sheenColor ) * sheenAlbedo;
		diffuse *= sheenEnergyComp;
	#endif
	reflectedLight.indirectDiffuse += diffuse;
}
void RE_IndirectSpecular_Physical( const in vec3 radiance, const in vec3 irradiance, const in vec3 clearcoatRadiance, const in vec3 geometryPosition, const in vec3 geometryNormal, const in vec3 geometryViewDir, const in vec3 geometryClearcoatNormal, const in PhysicalMaterial material, inout ReflectedLight reflectedLight) {
	#ifdef USE_CLEARCOAT
		clearcoatSpecularIndirect += clearcoatRadiance * EnvironmentBRDF( geometryClearcoatNormal, geometryViewDir, material.clearcoatF0, material.clearcoatF90, material.clearcoatRoughness );
	#endif
	#ifdef USE_SHEEN
		sheenSpecularIndirect += irradiance * material.sheenColor * IBLSheenBRDF( geometryNormal, geometryViewDir, material.sheenRoughness ) * RECIPROCAL_PI;
 	#endif
	vec3 singleScatteringDielectric = vec3( 0.0 );
	vec3 multiScatteringDielectric = vec3( 0.0 );
	vec3 singleScatteringMetallic = vec3( 0.0 );
	vec3 multiScatteringMetallic = vec3( 0.0 );
	#ifdef USE_IRIDESCENCE
		computeMultiscatteringIridescence( geometryNormal, geometryViewDir, material.specularColor, material.specularF90, material.iridescence, material.iridescenceFresnelDielectric, material.roughness, singleScatteringDielectric, multiScatteringDielectric );
		computeMultiscatteringIridescence( geometryNormal, geometryViewDir, material.diffuseColor, material.specularF90, material.iridescence, material.iridescenceFresnelMetallic, material.roughness, singleScatteringMetallic, multiScatteringMetallic );
	#else
		computeMultiscattering( geometryNormal, geometryViewDir, material.specularColor, material.specularF90, material.roughness, singleScatteringDielectric, multiScatteringDielectric );
		computeMultiscattering( geometryNormal, geometryViewDir, material.diffuseColor, material.specularF90, material.roughness, singleScatteringMetallic, multiScatteringMetallic );
	#endif
	vec3 singleScattering = mix( singleScatteringDielectric, singleScatteringMetallic, material.metalness );
	vec3 multiScattering = mix( multiScatteringDielectric, multiScatteringMetallic, material.metalness );
	vec3 totalScatteringDielectric = singleScatteringDielectric + multiScatteringDielectric;
	vec3 diffuse = material.diffuseContribution * ( 1.0 - totalScatteringDielectric );
	vec3 cosineWeightedIrradiance = irradiance * RECIPROCAL_PI;
	vec3 indirectSpecular = radiance * singleScattering;
	indirectSpecular += multiScattering * cosineWeightedIrradiance;
	vec3 indirectDiffuse = diffuse * cosineWeightedIrradiance;
	#ifdef USE_SHEEN
		float sheenAlbedo = IBLSheenBRDF( geometryNormal, geometryViewDir, material.sheenRoughness );
		float sheenEnergyComp = 1.0 - max3( material.sheenColor ) * sheenAlbedo;
		indirectSpecular *= sheenEnergyComp;
		indirectDiffuse *= sheenEnergyComp;
	#endif
	reflectedLight.indirectSpecular += indirectSpecular;
	reflectedLight.indirectDiffuse += indirectDiffuse;
}
#define RE_Direct				RE_Direct_Physical
#define RE_Direct_RectArea		RE_Direct_RectArea_Physical
#define RE_IndirectDiffuse		RE_IndirectDiffuse_Physical
#define RE_IndirectSpecular		RE_IndirectSpecular_Physical
float computeSpecularOcclusion( const in float dotNV, const in float ambientOcclusion, const in float roughness ) {
	return saturate( pow( dotNV + ambientOcclusion, exp2( - 16.0 * roughness - 1.0 ) ) - 1.0 + ambientOcclusion );
}`,_0=`
vec3 geometryPosition = - vViewPosition;
vec3 geometryNormal = normal;
vec3 geometryViewDir = ( isOrthographic ) ? vec3( 0, 0, 1 ) : normalize( vViewPosition );
vec3 geometryClearcoatNormal = vec3( 0.0 );
#ifdef USE_CLEARCOAT
	geometryClearcoatNormal = clearcoatNormal;
#endif
#ifdef USE_IRIDESCENCE
	float dotNVi = saturate( dot( normal, geometryViewDir ) );
	if ( material.iridescenceThickness == 0.0 ) {
		material.iridescence = 0.0;
	} else {
		material.iridescence = saturate( material.iridescence );
	}
	if ( material.iridescence > 0.0 ) {
		material.iridescenceFresnelDielectric = evalIridescence( 1.0, material.iridescenceIOR, dotNVi, material.iridescenceThickness, material.specularColor );
		material.iridescenceFresnelMetallic = evalIridescence( 1.0, material.iridescenceIOR, dotNVi, material.iridescenceThickness, material.diffuseColor );
		material.iridescenceFresnel = mix( material.iridescenceFresnelDielectric, material.iridescenceFresnelMetallic, material.metalness );
		material.iridescenceF0 = Schlick_to_F0( material.iridescenceFresnel, 1.0, dotNVi );
	}
#endif
IncidentLight directLight;
#if ( NUM_POINT_LIGHTS > 0 ) && defined( RE_Direct )
	PointLight pointLight;
	#if defined( USE_SHADOWMAP ) && NUM_POINT_LIGHT_SHADOWS > 0
	PointLightShadow pointLightShadow;
	#endif
	#pragma unroll_loop_start
	for ( int i = 0; i < NUM_POINT_LIGHTS; i ++ ) {
		pointLight = pointLights[ i ];
		getPointLightInfo( pointLight, geometryPosition, directLight );
		#if defined( USE_SHADOWMAP ) && ( UNROLLED_LOOP_INDEX < NUM_POINT_LIGHT_SHADOWS ) && ( defined( SHADOWMAP_TYPE_PCF ) || defined( SHADOWMAP_TYPE_BASIC ) )
		pointLightShadow = pointLightShadows[ i ];
		directLight.color *= ( directLight.visible && receiveShadow ) ? getPointShadow( pointShadowMap[ i ], pointLightShadow.shadowMapSize, pointLightShadow.shadowIntensity, pointLightShadow.shadowBias, pointLightShadow.shadowRadius, vPointShadowCoord[ i ], pointLightShadow.shadowCameraNear, pointLightShadow.shadowCameraFar ) : 1.0;
		#endif
		RE_Direct( directLight, geometryPosition, geometryNormal, geometryViewDir, geometryClearcoatNormal, material, reflectedLight );
	}
	#pragma unroll_loop_end
#endif
#if ( NUM_SPOT_LIGHTS > 0 ) && defined( RE_Direct )
	SpotLight spotLight;
	vec4 spotColor;
	vec3 spotLightCoord;
	bool inSpotLightMap;
	#if defined( USE_SHADOWMAP ) && NUM_SPOT_LIGHT_SHADOWS > 0
	SpotLightShadow spotLightShadow;
	#endif
	#pragma unroll_loop_start
	for ( int i = 0; i < NUM_SPOT_LIGHTS; i ++ ) {
		spotLight = spotLights[ i ];
		getSpotLightInfo( spotLight, geometryPosition, directLight );
		#if ( UNROLLED_LOOP_INDEX < NUM_SPOT_LIGHT_SHADOWS_WITH_MAPS )
		#define SPOT_LIGHT_MAP_INDEX UNROLLED_LOOP_INDEX
		#elif ( UNROLLED_LOOP_INDEX < NUM_SPOT_LIGHT_SHADOWS )
		#define SPOT_LIGHT_MAP_INDEX NUM_SPOT_LIGHT_MAPS
		#else
		#define SPOT_LIGHT_MAP_INDEX ( UNROLLED_LOOP_INDEX - NUM_SPOT_LIGHT_SHADOWS + NUM_SPOT_LIGHT_SHADOWS_WITH_MAPS )
		#endif
		#if ( SPOT_LIGHT_MAP_INDEX < NUM_SPOT_LIGHT_MAPS )
			spotLightCoord = vSpotLightCoord[ i ].xyz / vSpotLightCoord[ i ].w;
			inSpotLightMap = all( lessThan( abs( spotLightCoord * 2. - 1. ), vec3( 1.0 ) ) );
			spotColor = texture2D( spotLightMap[ SPOT_LIGHT_MAP_INDEX ], spotLightCoord.xy );
			directLight.color = inSpotLightMap ? directLight.color * spotColor.rgb : directLight.color;
		#endif
		#undef SPOT_LIGHT_MAP_INDEX
		#if defined( USE_SHADOWMAP ) && ( UNROLLED_LOOP_INDEX < NUM_SPOT_LIGHT_SHADOWS )
		spotLightShadow = spotLightShadows[ i ];
		directLight.color *= ( directLight.visible && receiveShadow ) ? getShadow( spotShadowMap[ i ], spotLightShadow.shadowMapSize, spotLightShadow.shadowIntensity, spotLightShadow.shadowBias, spotLightShadow.shadowRadius, vSpotLightCoord[ i ] ) : 1.0;
		#endif
		RE_Direct( directLight, geometryPosition, geometryNormal, geometryViewDir, geometryClearcoatNormal, material, reflectedLight );
	}
	#pragma unroll_loop_end
#endif
#if ( NUM_DIR_LIGHTS > 0 ) && defined( RE_Direct )
	DirectionalLight directionalLight;
	#if defined( USE_SHADOWMAP ) && NUM_DIR_LIGHT_SHADOWS > 0
	DirectionalLightShadow directionalLightShadow;
	#endif
	#pragma unroll_loop_start
	for ( int i = 0; i < NUM_DIR_LIGHTS; i ++ ) {
		directionalLight = directionalLights[ i ];
		getDirectionalLightInfo( directionalLight, directLight );
		#if defined( USE_SHADOWMAP ) && ( UNROLLED_LOOP_INDEX < NUM_DIR_LIGHT_SHADOWS )
		directionalLightShadow = directionalLightShadows[ i ];
		directLight.color *= ( directLight.visible && receiveShadow ) ? getShadow( directionalShadowMap[ i ], directionalLightShadow.shadowMapSize, directionalLightShadow.shadowIntensity, directionalLightShadow.shadowBias, directionalLightShadow.shadowRadius, vDirectionalShadowCoord[ i ] ) : 1.0;
		#endif
		RE_Direct( directLight, geometryPosition, geometryNormal, geometryViewDir, geometryClearcoatNormal, material, reflectedLight );
	}
	#pragma unroll_loop_end
#endif
#if ( NUM_RECT_AREA_LIGHTS > 0 ) && defined( RE_Direct_RectArea )
	RectAreaLight rectAreaLight;
	#pragma unroll_loop_start
	for ( int i = 0; i < NUM_RECT_AREA_LIGHTS; i ++ ) {
		rectAreaLight = rectAreaLights[ i ];
		RE_Direct_RectArea( rectAreaLight, geometryPosition, geometryNormal, geometryViewDir, geometryClearcoatNormal, material, reflectedLight );
	}
	#pragma unroll_loop_end
#endif
#if defined( RE_IndirectDiffuse )
	vec3 iblIrradiance = vec3( 0.0 );
	vec3 irradiance = getAmbientLightIrradiance( ambientLightColor );
	#if defined( USE_LIGHT_PROBES )
		irradiance += getLightProbeIrradiance( lightProbe, geometryNormal );
	#endif
	#if ( NUM_HEMI_LIGHTS > 0 )
		#pragma unroll_loop_start
		for ( int i = 0; i < NUM_HEMI_LIGHTS; i ++ ) {
			irradiance += getHemisphereLightIrradiance( hemisphereLights[ i ], geometryNormal );
		}
		#pragma unroll_loop_end
	#endif
#endif
#if defined( RE_IndirectSpecular )
	vec3 radiance = vec3( 0.0 );
	vec3 clearcoatRadiance = vec3( 0.0 );
#endif`,x0=`#if defined( RE_IndirectDiffuse )
	#ifdef USE_LIGHTMAP
		vec4 lightMapTexel = texture2D( lightMap, vLightMapUv );
		vec3 lightMapIrradiance = lightMapTexel.rgb * lightMapIntensity;
		irradiance += lightMapIrradiance;
	#endif
	#if defined( USE_ENVMAP ) && defined( ENVMAP_TYPE_CUBE_UV )
		#if defined( STANDARD ) || defined( LAMBERT ) || defined( PHONG )
			iblIrradiance += getIBLIrradiance( geometryNormal );
		#endif
	#endif
#endif
#if defined( USE_ENVMAP ) && defined( RE_IndirectSpecular )
	#ifdef USE_ANISOTROPY
		radiance += getIBLAnisotropyRadiance( geometryViewDir, geometryNormal, material.roughness, material.anisotropyB, material.anisotropy );
	#else
		radiance += getIBLRadiance( geometryViewDir, geometryNormal, material.roughness );
	#endif
	#ifdef USE_CLEARCOAT
		clearcoatRadiance += getIBLRadiance( geometryViewDir, geometryClearcoatNormal, material.clearcoatRoughness );
	#endif
#endif`,v0=`#if defined( RE_IndirectDiffuse )
	#if defined( LAMBERT ) || defined( PHONG )
		irradiance += iblIrradiance;
	#endif
	RE_IndirectDiffuse( irradiance, geometryPosition, geometryNormal, geometryViewDir, geometryClearcoatNormal, material, reflectedLight );
#endif
#if defined( RE_IndirectSpecular )
	RE_IndirectSpecular( radiance, iblIrradiance, clearcoatRadiance, geometryPosition, geometryNormal, geometryViewDir, geometryClearcoatNormal, material, reflectedLight );
#endif`,M0=`#if defined( USE_LOGARITHMIC_DEPTH_BUFFER )
	gl_FragDepth = vIsPerspective == 0.0 ? gl_FragCoord.z : log2( vFragDepth ) * logDepthBufFC * 0.5;
#endif`,S0=`#if defined( USE_LOGARITHMIC_DEPTH_BUFFER )
	uniform float logDepthBufFC;
	varying float vFragDepth;
	varying float vIsPerspective;
#endif`,y0=`#ifdef USE_LOGARITHMIC_DEPTH_BUFFER
	varying float vFragDepth;
	varying float vIsPerspective;
#endif`,b0=`#ifdef USE_LOGARITHMIC_DEPTH_BUFFER
	vFragDepth = 1.0 + gl_Position.w;
	vIsPerspective = float( isPerspectiveMatrix( projectionMatrix ) );
#endif`,E0=`#ifdef USE_MAP
	vec4 sampledDiffuseColor = texture2D( map, vMapUv );
	#ifdef DECODE_VIDEO_TEXTURE
		sampledDiffuseColor = sRGBTransferEOTF( sampledDiffuseColor );
	#endif
	diffuseColor *= sampledDiffuseColor;
#endif`,T0=`#ifdef USE_MAP
	uniform sampler2D map;
#endif`,A0=`#if defined( USE_MAP ) || defined( USE_ALPHAMAP )
	#if defined( USE_POINTS_UV )
		vec2 uv = vUv;
	#else
		vec2 uv = ( uvTransform * vec3( gl_PointCoord.x, 1.0 - gl_PointCoord.y, 1 ) ).xy;
	#endif
#endif
#ifdef USE_MAP
	diffuseColor *= texture2D( map, uv );
#endif
#ifdef USE_ALPHAMAP
	diffuseColor.a *= texture2D( alphaMap, uv ).g;
#endif`,w0=`#if defined( USE_POINTS_UV )
	varying vec2 vUv;
#else
	#if defined( USE_MAP ) || defined( USE_ALPHAMAP )
		uniform mat3 uvTransform;
	#endif
#endif
#ifdef USE_MAP
	uniform sampler2D map;
#endif
#ifdef USE_ALPHAMAP
	uniform sampler2D alphaMap;
#endif`,C0=`float metalnessFactor = metalness;
#ifdef USE_METALNESSMAP
	vec4 texelMetalness = texture2D( metalnessMap, vMetalnessMapUv );
	metalnessFactor *= texelMetalness.b;
#endif`,R0=`#ifdef USE_METALNESSMAP
	uniform sampler2D metalnessMap;
#endif`,P0=`#ifdef USE_INSTANCING_MORPH
	float morphTargetInfluences[ MORPHTARGETS_COUNT ];
	float morphTargetBaseInfluence = texelFetch( morphTexture, ivec2( 0, gl_InstanceID ), 0 ).r;
	for ( int i = 0; i < MORPHTARGETS_COUNT; i ++ ) {
		morphTargetInfluences[i] =  texelFetch( morphTexture, ivec2( i + 1, gl_InstanceID ), 0 ).r;
	}
#endif`,D0=`#if defined( USE_MORPHCOLORS )
	vColor *= morphTargetBaseInfluence;
	for ( int i = 0; i < MORPHTARGETS_COUNT; i ++ ) {
		#if defined( USE_COLOR_ALPHA )
			if ( morphTargetInfluences[ i ] != 0.0 ) vColor += getMorph( gl_VertexID, i, 2 ) * morphTargetInfluences[ i ];
		#elif defined( USE_COLOR )
			if ( morphTargetInfluences[ i ] != 0.0 ) vColor += getMorph( gl_VertexID, i, 2 ).rgb * morphTargetInfluences[ i ];
		#endif
	}
#endif`,L0=`#ifdef USE_MORPHNORMALS
	objectNormal *= morphTargetBaseInfluence;
	for ( int i = 0; i < MORPHTARGETS_COUNT; i ++ ) {
		if ( morphTargetInfluences[ i ] != 0.0 ) objectNormal += getMorph( gl_VertexID, i, 1 ).xyz * morphTargetInfluences[ i ];
	}
#endif`,I0=`#ifdef USE_MORPHTARGETS
	#ifndef USE_INSTANCING_MORPH
		uniform float morphTargetBaseInfluence;
		uniform float morphTargetInfluences[ MORPHTARGETS_COUNT ];
	#endif
	uniform sampler2DArray morphTargetsTexture;
	uniform ivec2 morphTargetsTextureSize;
	vec4 getMorph( const in int vertexIndex, const in int morphTargetIndex, const in int offset ) {
		int texelIndex = vertexIndex * MORPHTARGETS_TEXTURE_STRIDE + offset;
		int y = texelIndex / morphTargetsTextureSize.x;
		int x = texelIndex - y * morphTargetsTextureSize.x;
		ivec3 morphUV = ivec3( x, y, morphTargetIndex );
		return texelFetch( morphTargetsTexture, morphUV, 0 );
	}
#endif`,U0=`#ifdef USE_MORPHTARGETS
	transformed *= morphTargetBaseInfluence;
	for ( int i = 0; i < MORPHTARGETS_COUNT; i ++ ) {
		if ( morphTargetInfluences[ i ] != 0.0 ) transformed += getMorph( gl_VertexID, i, 0 ).xyz * morphTargetInfluences[ i ];
	}
#endif`,N0=`float faceDirection = gl_FrontFacing ? 1.0 : - 1.0;
#ifdef FLAT_SHADED
	vec3 fdx = dFdx( vViewPosition );
	vec3 fdy = dFdy( vViewPosition );
	vec3 normal = normalize( cross( fdx, fdy ) );
#else
	vec3 normal = normalize( vNormal );
	#ifdef DOUBLE_SIDED
		normal *= faceDirection;
	#endif
#endif
#if defined( USE_NORMALMAP_TANGENTSPACE ) || defined( USE_CLEARCOAT_NORMALMAP ) || defined( USE_ANISOTROPY )
	#ifdef USE_TANGENT
		mat3 tbn = mat3( normalize( vTangent ), normalize( vBitangent ), normal );
	#else
		mat3 tbn = getTangentFrame( - vViewPosition, normal,
		#if defined( USE_NORMALMAP )
			vNormalMapUv
		#elif defined( USE_CLEARCOAT_NORMALMAP )
			vClearcoatNormalMapUv
		#else
			vUv
		#endif
		);
	#endif
	#if defined( DOUBLE_SIDED ) && ! defined( FLAT_SHADED )
		tbn[0] *= faceDirection;
		tbn[1] *= faceDirection;
	#endif
#endif
#ifdef USE_CLEARCOAT_NORMALMAP
	#ifdef USE_TANGENT
		mat3 tbn2 = mat3( normalize( vTangent ), normalize( vBitangent ), normal );
	#else
		mat3 tbn2 = getTangentFrame( - vViewPosition, normal, vClearcoatNormalMapUv );
	#endif
	#if defined( DOUBLE_SIDED ) && ! defined( FLAT_SHADED )
		tbn2[0] *= faceDirection;
		tbn2[1] *= faceDirection;
	#endif
#endif
vec3 nonPerturbedNormal = normal;`,F0=`#ifdef USE_NORMALMAP_OBJECTSPACE
	normal = texture2D( normalMap, vNormalMapUv ).xyz * 2.0 - 1.0;
	#ifdef FLIP_SIDED
		normal = - normal;
	#endif
	#ifdef DOUBLE_SIDED
		normal = normal * faceDirection;
	#endif
	normal = normalize( normalMatrix * normal );
#elif defined( USE_NORMALMAP_TANGENTSPACE )
	vec3 mapN = texture2D( normalMap, vNormalMapUv ).xyz * 2.0 - 1.0;
	mapN.xy *= normalScale;
	normal = normalize( tbn * mapN );
#elif defined( USE_BUMPMAP )
	normal = perturbNormalArb( - vViewPosition, normal, dHdxy_fwd(), faceDirection );
#endif`,O0=`#ifndef FLAT_SHADED
	varying vec3 vNormal;
	#ifdef USE_TANGENT
		varying vec3 vTangent;
		varying vec3 vBitangent;
	#endif
#endif`,B0=`#ifndef FLAT_SHADED
	varying vec3 vNormal;
	#ifdef USE_TANGENT
		varying vec3 vTangent;
		varying vec3 vBitangent;
	#endif
#endif`,z0=`#ifndef FLAT_SHADED
	vNormal = normalize( transformedNormal );
	#ifdef USE_TANGENT
		vTangent = normalize( transformedTangent );
		vBitangent = normalize( cross( vNormal, vTangent ) * tangent.w );
	#endif
#endif`,H0=`#ifdef USE_NORMALMAP
	uniform sampler2D normalMap;
	uniform vec2 normalScale;
#endif
#ifdef USE_NORMALMAP_OBJECTSPACE
	uniform mat3 normalMatrix;
#endif
#if ! defined ( USE_TANGENT ) && ( defined ( USE_NORMALMAP_TANGENTSPACE ) || defined ( USE_CLEARCOAT_NORMALMAP ) || defined( USE_ANISOTROPY ) )
	mat3 getTangentFrame( vec3 eye_pos, vec3 surf_norm, vec2 uv ) {
		vec3 q0 = dFdx( eye_pos.xyz );
		vec3 q1 = dFdy( eye_pos.xyz );
		vec2 st0 = dFdx( uv.st );
		vec2 st1 = dFdy( uv.st );
		vec3 N = surf_norm;
		vec3 q1perp = cross( q1, N );
		vec3 q0perp = cross( N, q0 );
		vec3 T = q1perp * st0.x + q0perp * st1.x;
		vec3 B = q1perp * st0.y + q0perp * st1.y;
		float det = max( dot( T, T ), dot( B, B ) );
		float scale = ( det == 0.0 ) ? 0.0 : inversesqrt( det );
		return mat3( T * scale, B * scale, N );
	}
#endif`,V0=`#ifdef USE_CLEARCOAT
	vec3 clearcoatNormal = nonPerturbedNormal;
#endif`,k0=`#ifdef USE_CLEARCOAT_NORMALMAP
	vec3 clearcoatMapN = texture2D( clearcoatNormalMap, vClearcoatNormalMapUv ).xyz * 2.0 - 1.0;
	clearcoatMapN.xy *= clearcoatNormalScale;
	clearcoatNormal = normalize( tbn2 * clearcoatMapN );
#endif`,G0=`#ifdef USE_CLEARCOATMAP
	uniform sampler2D clearcoatMap;
#endif
#ifdef USE_CLEARCOAT_NORMALMAP
	uniform sampler2D clearcoatNormalMap;
	uniform vec2 clearcoatNormalScale;
#endif
#ifdef USE_CLEARCOAT_ROUGHNESSMAP
	uniform sampler2D clearcoatRoughnessMap;
#endif`,W0=`#ifdef USE_IRIDESCENCEMAP
	uniform sampler2D iridescenceMap;
#endif
#ifdef USE_IRIDESCENCE_THICKNESSMAP
	uniform sampler2D iridescenceThicknessMap;
#endif`,X0=`#ifdef OPAQUE
diffuseColor.a = 1.0;
#endif
#ifdef USE_TRANSMISSION
diffuseColor.a *= material.transmissionAlpha;
#endif
gl_FragColor = vec4( outgoingLight, diffuseColor.a );`,Y0=`vec3 packNormalToRGB( const in vec3 normal ) {
	return normalize( normal ) * 0.5 + 0.5;
}
vec3 unpackRGBToNormal( const in vec3 rgb ) {
	return 2.0 * rgb.xyz - 1.0;
}
const float PackUpscale = 256. / 255.;const float UnpackDownscale = 255. / 256.;const float ShiftRight8 = 1. / 256.;
const float Inv255 = 1. / 255.;
const vec4 PackFactors = vec4( 1.0, 256.0, 256.0 * 256.0, 256.0 * 256.0 * 256.0 );
const vec2 UnpackFactors2 = vec2( UnpackDownscale, 1.0 / PackFactors.g );
const vec3 UnpackFactors3 = vec3( UnpackDownscale / PackFactors.rg, 1.0 / PackFactors.b );
const vec4 UnpackFactors4 = vec4( UnpackDownscale / PackFactors.rgb, 1.0 / PackFactors.a );
vec4 packDepthToRGBA( const in float v ) {
	if( v <= 0.0 )
		return vec4( 0., 0., 0., 0. );
	if( v >= 1.0 )
		return vec4( 1., 1., 1., 1. );
	float vuf;
	float af = modf( v * PackFactors.a, vuf );
	float bf = modf( vuf * ShiftRight8, vuf );
	float gf = modf( vuf * ShiftRight8, vuf );
	return vec4( vuf * Inv255, gf * PackUpscale, bf * PackUpscale, af );
}
vec3 packDepthToRGB( const in float v ) {
	if( v <= 0.0 )
		return vec3( 0., 0., 0. );
	if( v >= 1.0 )
		return vec3( 1., 1., 1. );
	float vuf;
	float bf = modf( v * PackFactors.b, vuf );
	float gf = modf( vuf * ShiftRight8, vuf );
	return vec3( vuf * Inv255, gf * PackUpscale, bf );
}
vec2 packDepthToRG( const in float v ) {
	if( v <= 0.0 )
		return vec2( 0., 0. );
	if( v >= 1.0 )
		return vec2( 1., 1. );
	float vuf;
	float gf = modf( v * 256., vuf );
	return vec2( vuf * Inv255, gf );
}
float unpackRGBAToDepth( const in vec4 v ) {
	return dot( v, UnpackFactors4 );
}
float unpackRGBToDepth( const in vec3 v ) {
	return dot( v, UnpackFactors3 );
}
float unpackRGToDepth( const in vec2 v ) {
	return v.r * UnpackFactors2.r + v.g * UnpackFactors2.g;
}
vec4 pack2HalfToRGBA( const in vec2 v ) {
	vec4 r = vec4( v.x, fract( v.x * 255.0 ), v.y, fract( v.y * 255.0 ) );
	return vec4( r.x - r.y / 255.0, r.y, r.z - r.w / 255.0, r.w );
}
vec2 unpackRGBATo2Half( const in vec4 v ) {
	return vec2( v.x + ( v.y / 255.0 ), v.z + ( v.w / 255.0 ) );
}
float viewZToOrthographicDepth( const in float viewZ, const in float near, const in float far ) {
	return ( viewZ + near ) / ( near - far );
}
float orthographicDepthToViewZ( const in float depth, const in float near, const in float far ) {
	#ifdef USE_REVERSED_DEPTH_BUFFER
	
		return depth * ( far - near ) - far;
	#else
		return depth * ( near - far ) - near;
	#endif
}
float viewZToPerspectiveDepth( const in float viewZ, const in float near, const in float far ) {
	return ( ( near + viewZ ) * far ) / ( ( far - near ) * viewZ );
}
float perspectiveDepthToViewZ( const in float depth, const in float near, const in float far ) {
	
	#ifdef USE_REVERSED_DEPTH_BUFFER
		return ( near * far ) / ( ( near - far ) * depth - near );
	#else
		return ( near * far ) / ( ( far - near ) * depth - far );
	#endif
}`,q0=`#ifdef PREMULTIPLIED_ALPHA
	gl_FragColor.rgb *= gl_FragColor.a;
#endif`,j0=`vec4 mvPosition = vec4( transformed, 1.0 );
#ifdef USE_BATCHING
	mvPosition = batchingMatrix * mvPosition;
#endif
#ifdef USE_INSTANCING
	mvPosition = instanceMatrix * mvPosition;
#endif
mvPosition = modelViewMatrix * mvPosition;
gl_Position = projectionMatrix * mvPosition;`,K0=`#ifdef DITHERING
	gl_FragColor.rgb = dithering( gl_FragColor.rgb );
#endif`,$0=`#ifdef DITHERING
	vec3 dithering( vec3 color ) {
		float grid_position = rand( gl_FragCoord.xy );
		vec3 dither_shift_RGB = vec3( 0.25 / 255.0, -0.25 / 255.0, 0.25 / 255.0 );
		dither_shift_RGB = mix( 2.0 * dither_shift_RGB, -2.0 * dither_shift_RGB, grid_position );
		return color + dither_shift_RGB;
	}
#endif`,Z0=`float roughnessFactor = roughness;
#ifdef USE_ROUGHNESSMAP
	vec4 texelRoughness = texture2D( roughnessMap, vRoughnessMapUv );
	roughnessFactor *= texelRoughness.g;
#endif`,J0=`#ifdef USE_ROUGHNESSMAP
	uniform sampler2D roughnessMap;
#endif`,Q0=`#if NUM_SPOT_LIGHT_COORDS > 0
	varying vec4 vSpotLightCoord[ NUM_SPOT_LIGHT_COORDS ];
#endif
#if NUM_SPOT_LIGHT_MAPS > 0
	uniform sampler2D spotLightMap[ NUM_SPOT_LIGHT_MAPS ];
#endif
#ifdef USE_SHADOWMAP
	#if NUM_DIR_LIGHT_SHADOWS > 0
		#if defined( SHADOWMAP_TYPE_PCF )
			uniform sampler2DShadow directionalShadowMap[ NUM_DIR_LIGHT_SHADOWS ];
		#else
			uniform sampler2D directionalShadowMap[ NUM_DIR_LIGHT_SHADOWS ];
		#endif
		varying vec4 vDirectionalShadowCoord[ NUM_DIR_LIGHT_SHADOWS ];
		struct DirectionalLightShadow {
			float shadowIntensity;
			float shadowBias;
			float shadowNormalBias;
			float shadowRadius;
			vec2 shadowMapSize;
		};
		uniform DirectionalLightShadow directionalLightShadows[ NUM_DIR_LIGHT_SHADOWS ];
	#endif
	#if NUM_SPOT_LIGHT_SHADOWS > 0
		#if defined( SHADOWMAP_TYPE_PCF )
			uniform sampler2DShadow spotShadowMap[ NUM_SPOT_LIGHT_SHADOWS ];
		#else
			uniform sampler2D spotShadowMap[ NUM_SPOT_LIGHT_SHADOWS ];
		#endif
		struct SpotLightShadow {
			float shadowIntensity;
			float shadowBias;
			float shadowNormalBias;
			float shadowRadius;
			vec2 shadowMapSize;
		};
		uniform SpotLightShadow spotLightShadows[ NUM_SPOT_LIGHT_SHADOWS ];
	#endif
	#if NUM_POINT_LIGHT_SHADOWS > 0
		#if defined( SHADOWMAP_TYPE_PCF )
			uniform samplerCubeShadow pointShadowMap[ NUM_POINT_LIGHT_SHADOWS ];
		#elif defined( SHADOWMAP_TYPE_BASIC )
			uniform samplerCube pointShadowMap[ NUM_POINT_LIGHT_SHADOWS ];
		#endif
		varying vec4 vPointShadowCoord[ NUM_POINT_LIGHT_SHADOWS ];
		struct PointLightShadow {
			float shadowIntensity;
			float shadowBias;
			float shadowNormalBias;
			float shadowRadius;
			vec2 shadowMapSize;
			float shadowCameraNear;
			float shadowCameraFar;
		};
		uniform PointLightShadow pointLightShadows[ NUM_POINT_LIGHT_SHADOWS ];
	#endif
	#if defined( SHADOWMAP_TYPE_PCF )
		float interleavedGradientNoise( vec2 position ) {
			return fract( 52.9829189 * fract( dot( position, vec2( 0.06711056, 0.00583715 ) ) ) );
		}
		vec2 vogelDiskSample( int sampleIndex, int samplesCount, float phi ) {
			const float goldenAngle = 2.399963229728653;
			float r = sqrt( ( float( sampleIndex ) + 0.5 ) / float( samplesCount ) );
			float theta = float( sampleIndex ) * goldenAngle + phi;
			return vec2( cos( theta ), sin( theta ) ) * r;
		}
	#endif
	#if defined( SHADOWMAP_TYPE_PCF )
		float getShadow( sampler2DShadow shadowMap, vec2 shadowMapSize, float shadowIntensity, float shadowBias, float shadowRadius, vec4 shadowCoord ) {
			float shadow = 1.0;
			shadowCoord.xyz /= shadowCoord.w;
			shadowCoord.z += shadowBias;
			bool inFrustum = shadowCoord.x >= 0.0 && shadowCoord.x <= 1.0 && shadowCoord.y >= 0.0 && shadowCoord.y <= 1.0;
			bool frustumTest = inFrustum && shadowCoord.z <= 1.0;
			if ( frustumTest ) {
				vec2 texelSize = vec2( 1.0 ) / shadowMapSize;
				float radius = shadowRadius * texelSize.x;
				float phi = interleavedGradientNoise( gl_FragCoord.xy ) * PI2;
				shadow = (
					texture( shadowMap, vec3( shadowCoord.xy + vogelDiskSample( 0, 5, phi ) * radius, shadowCoord.z ) ) +
					texture( shadowMap, vec3( shadowCoord.xy + vogelDiskSample( 1, 5, phi ) * radius, shadowCoord.z ) ) +
					texture( shadowMap, vec3( shadowCoord.xy + vogelDiskSample( 2, 5, phi ) * radius, shadowCoord.z ) ) +
					texture( shadowMap, vec3( shadowCoord.xy + vogelDiskSample( 3, 5, phi ) * radius, shadowCoord.z ) ) +
					texture( shadowMap, vec3( shadowCoord.xy + vogelDiskSample( 4, 5, phi ) * radius, shadowCoord.z ) )
				) * 0.2;
			}
			return mix( 1.0, shadow, shadowIntensity );
		}
	#elif defined( SHADOWMAP_TYPE_VSM )
		float getShadow( sampler2D shadowMap, vec2 shadowMapSize, float shadowIntensity, float shadowBias, float shadowRadius, vec4 shadowCoord ) {
			float shadow = 1.0;
			shadowCoord.xyz /= shadowCoord.w;
			#ifdef USE_REVERSED_DEPTH_BUFFER
				shadowCoord.z -= shadowBias;
			#else
				shadowCoord.z += shadowBias;
			#endif
			bool inFrustum = shadowCoord.x >= 0.0 && shadowCoord.x <= 1.0 && shadowCoord.y >= 0.0 && shadowCoord.y <= 1.0;
			bool frustumTest = inFrustum && shadowCoord.z <= 1.0;
			if ( frustumTest ) {
				vec2 distribution = texture2D( shadowMap, shadowCoord.xy ).rg;
				float mean = distribution.x;
				float variance = distribution.y * distribution.y;
				#ifdef USE_REVERSED_DEPTH_BUFFER
					float hard_shadow = step( mean, shadowCoord.z );
				#else
					float hard_shadow = step( shadowCoord.z, mean );
				#endif
				
				if ( hard_shadow == 1.0 ) {
					shadow = 1.0;
				} else {
					variance = max( variance, 0.0000001 );
					float d = shadowCoord.z - mean;
					float p_max = variance / ( variance + d * d );
					p_max = clamp( ( p_max - 0.3 ) / 0.65, 0.0, 1.0 );
					shadow = max( hard_shadow, p_max );
				}
			}
			return mix( 1.0, shadow, shadowIntensity );
		}
	#else
		float getShadow( sampler2D shadowMap, vec2 shadowMapSize, float shadowIntensity, float shadowBias, float shadowRadius, vec4 shadowCoord ) {
			float shadow = 1.0;
			shadowCoord.xyz /= shadowCoord.w;
			#ifdef USE_REVERSED_DEPTH_BUFFER
				shadowCoord.z -= shadowBias;
			#else
				shadowCoord.z += shadowBias;
			#endif
			bool inFrustum = shadowCoord.x >= 0.0 && shadowCoord.x <= 1.0 && shadowCoord.y >= 0.0 && shadowCoord.y <= 1.0;
			bool frustumTest = inFrustum && shadowCoord.z <= 1.0;
			if ( frustumTest ) {
				float depth = texture2D( shadowMap, shadowCoord.xy ).r;
				#ifdef USE_REVERSED_DEPTH_BUFFER
					shadow = step( depth, shadowCoord.z );
				#else
					shadow = step( shadowCoord.z, depth );
				#endif
			}
			return mix( 1.0, shadow, shadowIntensity );
		}
	#endif
	#if NUM_POINT_LIGHT_SHADOWS > 0
	#if defined( SHADOWMAP_TYPE_PCF )
	float getPointShadow( samplerCubeShadow shadowMap, vec2 shadowMapSize, float shadowIntensity, float shadowBias, float shadowRadius, vec4 shadowCoord, float shadowCameraNear, float shadowCameraFar ) {
		float shadow = 1.0;
		vec3 lightToPosition = shadowCoord.xyz;
		vec3 bd3D = normalize( lightToPosition );
		vec3 absVec = abs( lightToPosition );
		float viewSpaceZ = max( max( absVec.x, absVec.y ), absVec.z );
		if ( viewSpaceZ - shadowCameraFar <= 0.0 && viewSpaceZ - shadowCameraNear >= 0.0 ) {
			#ifdef USE_REVERSED_DEPTH_BUFFER
				float dp = ( shadowCameraNear * ( shadowCameraFar - viewSpaceZ ) ) / ( viewSpaceZ * ( shadowCameraFar - shadowCameraNear ) );
				dp -= shadowBias;
			#else
				float dp = ( shadowCameraFar * ( viewSpaceZ - shadowCameraNear ) ) / ( viewSpaceZ * ( shadowCameraFar - shadowCameraNear ) );
				dp += shadowBias;
			#endif
			float texelSize = shadowRadius / shadowMapSize.x;
			vec3 absDir = abs( bd3D );
			vec3 tangent = absDir.x > absDir.z ? vec3( 0.0, 1.0, 0.0 ) : vec3( 1.0, 0.0, 0.0 );
			tangent = normalize( cross( bd3D, tangent ) );
			vec3 bitangent = cross( bd3D, tangent );
			float phi = interleavedGradientNoise( gl_FragCoord.xy ) * PI2;
			vec2 sample0 = vogelDiskSample( 0, 5, phi );
			vec2 sample1 = vogelDiskSample( 1, 5, phi );
			vec2 sample2 = vogelDiskSample( 2, 5, phi );
			vec2 sample3 = vogelDiskSample( 3, 5, phi );
			vec2 sample4 = vogelDiskSample( 4, 5, phi );
			shadow = (
				texture( shadowMap, vec4( bd3D + ( tangent * sample0.x + bitangent * sample0.y ) * texelSize, dp ) ) +
				texture( shadowMap, vec4( bd3D + ( tangent * sample1.x + bitangent * sample1.y ) * texelSize, dp ) ) +
				texture( shadowMap, vec4( bd3D + ( tangent * sample2.x + bitangent * sample2.y ) * texelSize, dp ) ) +
				texture( shadowMap, vec4( bd3D + ( tangent * sample3.x + bitangent * sample3.y ) * texelSize, dp ) ) +
				texture( shadowMap, vec4( bd3D + ( tangent * sample4.x + bitangent * sample4.y ) * texelSize, dp ) )
			) * 0.2;
		}
		return mix( 1.0, shadow, shadowIntensity );
	}
	#elif defined( SHADOWMAP_TYPE_BASIC )
	float getPointShadow( samplerCube shadowMap, vec2 shadowMapSize, float shadowIntensity, float shadowBias, float shadowRadius, vec4 shadowCoord, float shadowCameraNear, float shadowCameraFar ) {
		float shadow = 1.0;
		vec3 lightToPosition = shadowCoord.xyz;
		vec3 absVec = abs( lightToPosition );
		float viewSpaceZ = max( max( absVec.x, absVec.y ), absVec.z );
		if ( viewSpaceZ - shadowCameraFar <= 0.0 && viewSpaceZ - shadowCameraNear >= 0.0 ) {
			float dp = ( shadowCameraFar * ( viewSpaceZ - shadowCameraNear ) ) / ( viewSpaceZ * ( shadowCameraFar - shadowCameraNear ) );
			dp += shadowBias;
			vec3 bd3D = normalize( lightToPosition );
			float depth = textureCube( shadowMap, bd3D ).r;
			#ifdef USE_REVERSED_DEPTH_BUFFER
				depth = 1.0 - depth;
			#endif
			shadow = step( dp, depth );
		}
		return mix( 1.0, shadow, shadowIntensity );
	}
	#endif
	#endif
#endif`,ex=`#if NUM_SPOT_LIGHT_COORDS > 0
	uniform mat4 spotLightMatrix[ NUM_SPOT_LIGHT_COORDS ];
	varying vec4 vSpotLightCoord[ NUM_SPOT_LIGHT_COORDS ];
#endif
#ifdef USE_SHADOWMAP
	#if NUM_DIR_LIGHT_SHADOWS > 0
		uniform mat4 directionalShadowMatrix[ NUM_DIR_LIGHT_SHADOWS ];
		varying vec4 vDirectionalShadowCoord[ NUM_DIR_LIGHT_SHADOWS ];
		struct DirectionalLightShadow {
			float shadowIntensity;
			float shadowBias;
			float shadowNormalBias;
			float shadowRadius;
			vec2 shadowMapSize;
		};
		uniform DirectionalLightShadow directionalLightShadows[ NUM_DIR_LIGHT_SHADOWS ];
	#endif
	#if NUM_SPOT_LIGHT_SHADOWS > 0
		struct SpotLightShadow {
			float shadowIntensity;
			float shadowBias;
			float shadowNormalBias;
			float shadowRadius;
			vec2 shadowMapSize;
		};
		uniform SpotLightShadow spotLightShadows[ NUM_SPOT_LIGHT_SHADOWS ];
	#endif
	#if NUM_POINT_LIGHT_SHADOWS > 0
		uniform mat4 pointShadowMatrix[ NUM_POINT_LIGHT_SHADOWS ];
		varying vec4 vPointShadowCoord[ NUM_POINT_LIGHT_SHADOWS ];
		struct PointLightShadow {
			float shadowIntensity;
			float shadowBias;
			float shadowNormalBias;
			float shadowRadius;
			vec2 shadowMapSize;
			float shadowCameraNear;
			float shadowCameraFar;
		};
		uniform PointLightShadow pointLightShadows[ NUM_POINT_LIGHT_SHADOWS ];
	#endif
#endif`,tx=`#if ( defined( USE_SHADOWMAP ) && ( NUM_DIR_LIGHT_SHADOWS > 0 || NUM_POINT_LIGHT_SHADOWS > 0 ) ) || ( NUM_SPOT_LIGHT_COORDS > 0 )
	vec3 shadowWorldNormal = inverseTransformDirection( transformedNormal, viewMatrix );
	vec4 shadowWorldPosition;
#endif
#if defined( USE_SHADOWMAP )
	#if NUM_DIR_LIGHT_SHADOWS > 0
		#pragma unroll_loop_start
		for ( int i = 0; i < NUM_DIR_LIGHT_SHADOWS; i ++ ) {
			shadowWorldPosition = worldPosition + vec4( shadowWorldNormal * directionalLightShadows[ i ].shadowNormalBias, 0 );
			vDirectionalShadowCoord[ i ] = directionalShadowMatrix[ i ] * shadowWorldPosition;
		}
		#pragma unroll_loop_end
	#endif
	#if NUM_POINT_LIGHT_SHADOWS > 0
		#pragma unroll_loop_start
		for ( int i = 0; i < NUM_POINT_LIGHT_SHADOWS; i ++ ) {
			shadowWorldPosition = worldPosition + vec4( shadowWorldNormal * pointLightShadows[ i ].shadowNormalBias, 0 );
			vPointShadowCoord[ i ] = pointShadowMatrix[ i ] * shadowWorldPosition;
		}
		#pragma unroll_loop_end
	#endif
#endif
#if NUM_SPOT_LIGHT_COORDS > 0
	#pragma unroll_loop_start
	for ( int i = 0; i < NUM_SPOT_LIGHT_COORDS; i ++ ) {
		shadowWorldPosition = worldPosition;
		#if ( defined( USE_SHADOWMAP ) && UNROLLED_LOOP_INDEX < NUM_SPOT_LIGHT_SHADOWS )
			shadowWorldPosition.xyz += shadowWorldNormal * spotLightShadows[ i ].shadowNormalBias;
		#endif
		vSpotLightCoord[ i ] = spotLightMatrix[ i ] * shadowWorldPosition;
	}
	#pragma unroll_loop_end
#endif`,nx=`float getShadowMask() {
	float shadow = 1.0;
	#ifdef USE_SHADOWMAP
	#if NUM_DIR_LIGHT_SHADOWS > 0
	DirectionalLightShadow directionalLight;
	#pragma unroll_loop_start
	for ( int i = 0; i < NUM_DIR_LIGHT_SHADOWS; i ++ ) {
		directionalLight = directionalLightShadows[ i ];
		shadow *= receiveShadow ? getShadow( directionalShadowMap[ i ], directionalLight.shadowMapSize, directionalLight.shadowIntensity, directionalLight.shadowBias, directionalLight.shadowRadius, vDirectionalShadowCoord[ i ] ) : 1.0;
	}
	#pragma unroll_loop_end
	#endif
	#if NUM_SPOT_LIGHT_SHADOWS > 0
	SpotLightShadow spotLight;
	#pragma unroll_loop_start
	for ( int i = 0; i < NUM_SPOT_LIGHT_SHADOWS; i ++ ) {
		spotLight = spotLightShadows[ i ];
		shadow *= receiveShadow ? getShadow( spotShadowMap[ i ], spotLight.shadowMapSize, spotLight.shadowIntensity, spotLight.shadowBias, spotLight.shadowRadius, vSpotLightCoord[ i ] ) : 1.0;
	}
	#pragma unroll_loop_end
	#endif
	#if NUM_POINT_LIGHT_SHADOWS > 0 && ( defined( SHADOWMAP_TYPE_PCF ) || defined( SHADOWMAP_TYPE_BASIC ) )
	PointLightShadow pointLight;
	#pragma unroll_loop_start
	for ( int i = 0; i < NUM_POINT_LIGHT_SHADOWS; i ++ ) {
		pointLight = pointLightShadows[ i ];
		shadow *= receiveShadow ? getPointShadow( pointShadowMap[ i ], pointLight.shadowMapSize, pointLight.shadowIntensity, pointLight.shadowBias, pointLight.shadowRadius, vPointShadowCoord[ i ], pointLight.shadowCameraNear, pointLight.shadowCameraFar ) : 1.0;
	}
	#pragma unroll_loop_end
	#endif
	#endif
	return shadow;
}`,ix=`#ifdef USE_SKINNING
	mat4 boneMatX = getBoneMatrix( skinIndex.x );
	mat4 boneMatY = getBoneMatrix( skinIndex.y );
	mat4 boneMatZ = getBoneMatrix( skinIndex.z );
	mat4 boneMatW = getBoneMatrix( skinIndex.w );
#endif`,sx=`#ifdef USE_SKINNING
	uniform mat4 bindMatrix;
	uniform mat4 bindMatrixInverse;
	uniform highp sampler2D boneTexture;
	mat4 getBoneMatrix( const in float i ) {
		int size = textureSize( boneTexture, 0 ).x;
		int j = int( i ) * 4;
		int x = j % size;
		int y = j / size;
		vec4 v1 = texelFetch( boneTexture, ivec2( x, y ), 0 );
		vec4 v2 = texelFetch( boneTexture, ivec2( x + 1, y ), 0 );
		vec4 v3 = texelFetch( boneTexture, ivec2( x + 2, y ), 0 );
		vec4 v4 = texelFetch( boneTexture, ivec2( x + 3, y ), 0 );
		return mat4( v1, v2, v3, v4 );
	}
#endif`,rx=`#ifdef USE_SKINNING
	vec4 skinVertex = bindMatrix * vec4( transformed, 1.0 );
	vec4 skinned = vec4( 0.0 );
	skinned += boneMatX * skinVertex * skinWeight.x;
	skinned += boneMatY * skinVertex * skinWeight.y;
	skinned += boneMatZ * skinVertex * skinWeight.z;
	skinned += boneMatW * skinVertex * skinWeight.w;
	transformed = ( bindMatrixInverse * skinned ).xyz;
#endif`,ax=`#ifdef USE_SKINNING
	mat4 skinMatrix = mat4( 0.0 );
	skinMatrix += skinWeight.x * boneMatX;
	skinMatrix += skinWeight.y * boneMatY;
	skinMatrix += skinWeight.z * boneMatZ;
	skinMatrix += skinWeight.w * boneMatW;
	skinMatrix = bindMatrixInverse * skinMatrix * bindMatrix;
	objectNormal = vec4( skinMatrix * vec4( objectNormal, 0.0 ) ).xyz;
	#ifdef USE_TANGENT
		objectTangent = vec4( skinMatrix * vec4( objectTangent, 0.0 ) ).xyz;
	#endif
#endif`,ox=`float specularStrength;
#ifdef USE_SPECULARMAP
	vec4 texelSpecular = texture2D( specularMap, vSpecularMapUv );
	specularStrength = texelSpecular.r;
#else
	specularStrength = 1.0;
#endif`,lx=`#ifdef USE_SPECULARMAP
	uniform sampler2D specularMap;
#endif`,cx=`#if defined( TONE_MAPPING )
	gl_FragColor.rgb = toneMapping( gl_FragColor.rgb );
#endif`,ux=`#ifndef saturate
#define saturate( a ) clamp( a, 0.0, 1.0 )
#endif
uniform float toneMappingExposure;
vec3 LinearToneMapping( vec3 color ) {
	return saturate( toneMappingExposure * color );
}
vec3 ReinhardToneMapping( vec3 color ) {
	color *= toneMappingExposure;
	return saturate( color / ( vec3( 1.0 ) + color ) );
}
vec3 CineonToneMapping( vec3 color ) {
	color *= toneMappingExposure;
	color = max( vec3( 0.0 ), color - 0.004 );
	return pow( ( color * ( 6.2 * color + 0.5 ) ) / ( color * ( 6.2 * color + 1.7 ) + 0.06 ), vec3( 2.2 ) );
}
vec3 RRTAndODTFit( vec3 v ) {
	vec3 a = v * ( v + 0.0245786 ) - 0.000090537;
	vec3 b = v * ( 0.983729 * v + 0.4329510 ) + 0.238081;
	return a / b;
}
vec3 ACESFilmicToneMapping( vec3 color ) {
	const mat3 ACESInputMat = mat3(
		vec3( 0.59719, 0.07600, 0.02840 ),		vec3( 0.35458, 0.90834, 0.13383 ),
		vec3( 0.04823, 0.01566, 0.83777 )
	);
	const mat3 ACESOutputMat = mat3(
		vec3(  1.60475, -0.10208, -0.00327 ),		vec3( -0.53108,  1.10813, -0.07276 ),
		vec3( -0.07367, -0.00605,  1.07602 )
	);
	color *= toneMappingExposure / 0.6;
	color = ACESInputMat * color;
	color = RRTAndODTFit( color );
	color = ACESOutputMat * color;
	return saturate( color );
}
const mat3 LINEAR_REC2020_TO_LINEAR_SRGB = mat3(
	vec3( 1.6605, - 0.1246, - 0.0182 ),
	vec3( - 0.5876, 1.1329, - 0.1006 ),
	vec3( - 0.0728, - 0.0083, 1.1187 )
);
const mat3 LINEAR_SRGB_TO_LINEAR_REC2020 = mat3(
	vec3( 0.6274, 0.0691, 0.0164 ),
	vec3( 0.3293, 0.9195, 0.0880 ),
	vec3( 0.0433, 0.0113, 0.8956 )
);
vec3 agxDefaultContrastApprox( vec3 x ) {
	vec3 x2 = x * x;
	vec3 x4 = x2 * x2;
	return + 15.5 * x4 * x2
		- 40.14 * x4 * x
		+ 31.96 * x4
		- 6.868 * x2 * x
		+ 0.4298 * x2
		+ 0.1191 * x
		- 0.00232;
}
vec3 AgXToneMapping( vec3 color ) {
	const mat3 AgXInsetMatrix = mat3(
		vec3( 0.856627153315983, 0.137318972929847, 0.11189821299995 ),
		vec3( 0.0951212405381588, 0.761241990602591, 0.0767994186031903 ),
		vec3( 0.0482516061458583, 0.101439036467562, 0.811302368396859 )
	);
	const mat3 AgXOutsetMatrix = mat3(
		vec3( 1.1271005818144368, - 0.1413297634984383, - 0.14132976349843826 ),
		vec3( - 0.11060664309660323, 1.157823702216272, - 0.11060664309660294 ),
		vec3( - 0.016493938717834573, - 0.016493938717834257, 1.2519364065950405 )
	);
	const float AgxMinEv = - 12.47393;	const float AgxMaxEv = 4.026069;
	color *= toneMappingExposure;
	color = LINEAR_SRGB_TO_LINEAR_REC2020 * color;
	color = AgXInsetMatrix * color;
	color = max( color, 1e-10 );	color = log2( color );
	color = ( color - AgxMinEv ) / ( AgxMaxEv - AgxMinEv );
	color = clamp( color, 0.0, 1.0 );
	color = agxDefaultContrastApprox( color );
	color = AgXOutsetMatrix * color;
	color = pow( max( vec3( 0.0 ), color ), vec3( 2.2 ) );
	color = LINEAR_REC2020_TO_LINEAR_SRGB * color;
	color = clamp( color, 0.0, 1.0 );
	return color;
}
vec3 NeutralToneMapping( vec3 color ) {
	const float StartCompression = 0.8 - 0.04;
	const float Desaturation = 0.15;
	color *= toneMappingExposure;
	float x = min( color.r, min( color.g, color.b ) );
	float offset = x < 0.08 ? x - 6.25 * x * x : 0.04;
	color -= offset;
	float peak = max( color.r, max( color.g, color.b ) );
	if ( peak < StartCompression ) return color;
	float d = 1. - StartCompression;
	float newPeak = 1. - d * d / ( peak + d - StartCompression );
	color *= newPeak / peak;
	float g = 1. - 1. / ( Desaturation * ( peak - newPeak ) + 1. );
	return mix( color, vec3( newPeak ), g );
}
vec3 CustomToneMapping( vec3 color ) { return color; }`,hx=`#ifdef USE_TRANSMISSION
	material.transmission = transmission;
	material.transmissionAlpha = 1.0;
	material.thickness = thickness;
	material.attenuationDistance = attenuationDistance;
	material.attenuationColor = attenuationColor;
	#ifdef USE_TRANSMISSIONMAP
		material.transmission *= texture2D( transmissionMap, vTransmissionMapUv ).r;
	#endif
	#ifdef USE_THICKNESSMAP
		material.thickness *= texture2D( thicknessMap, vThicknessMapUv ).g;
	#endif
	vec3 pos = vWorldPosition;
	vec3 v = normalize( cameraPosition - pos );
	vec3 n = inverseTransformDirection( normal, viewMatrix );
	vec4 transmitted = getIBLVolumeRefraction(
		n, v, material.roughness, material.diffuseContribution, material.specularColorBlended, material.specularF90,
		pos, modelMatrix, viewMatrix, projectionMatrix, material.dispersion, material.ior, material.thickness,
		material.attenuationColor, material.attenuationDistance );
	material.transmissionAlpha = mix( material.transmissionAlpha, transmitted.a, material.transmission );
	totalDiffuse = mix( totalDiffuse, transmitted.rgb, material.transmission );
#endif`,fx=`#ifdef USE_TRANSMISSION
	uniform float transmission;
	uniform float thickness;
	uniform float attenuationDistance;
	uniform vec3 attenuationColor;
	#ifdef USE_TRANSMISSIONMAP
		uniform sampler2D transmissionMap;
	#endif
	#ifdef USE_THICKNESSMAP
		uniform sampler2D thicknessMap;
	#endif
	uniform vec2 transmissionSamplerSize;
	uniform sampler2D transmissionSamplerMap;
	uniform mat4 modelMatrix;
	uniform mat4 projectionMatrix;
	varying vec3 vWorldPosition;
	float w0( float a ) {
		return ( 1.0 / 6.0 ) * ( a * ( a * ( - a + 3.0 ) - 3.0 ) + 1.0 );
	}
	float w1( float a ) {
		return ( 1.0 / 6.0 ) * ( a *  a * ( 3.0 * a - 6.0 ) + 4.0 );
	}
	float w2( float a ){
		return ( 1.0 / 6.0 ) * ( a * ( a * ( - 3.0 * a + 3.0 ) + 3.0 ) + 1.0 );
	}
	float w3( float a ) {
		return ( 1.0 / 6.0 ) * ( a * a * a );
	}
	float g0( float a ) {
		return w0( a ) + w1( a );
	}
	float g1( float a ) {
		return w2( a ) + w3( a );
	}
	float h0( float a ) {
		return - 1.0 + w1( a ) / ( w0( a ) + w1( a ) );
	}
	float h1( float a ) {
		return 1.0 + w3( a ) / ( w2( a ) + w3( a ) );
	}
	vec4 bicubic( sampler2D tex, vec2 uv, vec4 texelSize, float lod ) {
		uv = uv * texelSize.zw + 0.5;
		vec2 iuv = floor( uv );
		vec2 fuv = fract( uv );
		float g0x = g0( fuv.x );
		float g1x = g1( fuv.x );
		float h0x = h0( fuv.x );
		float h1x = h1( fuv.x );
		float h0y = h0( fuv.y );
		float h1y = h1( fuv.y );
		vec2 p0 = ( vec2( iuv.x + h0x, iuv.y + h0y ) - 0.5 ) * texelSize.xy;
		vec2 p1 = ( vec2( iuv.x + h1x, iuv.y + h0y ) - 0.5 ) * texelSize.xy;
		vec2 p2 = ( vec2( iuv.x + h0x, iuv.y + h1y ) - 0.5 ) * texelSize.xy;
		vec2 p3 = ( vec2( iuv.x + h1x, iuv.y + h1y ) - 0.5 ) * texelSize.xy;
		return g0( fuv.y ) * ( g0x * textureLod( tex, p0, lod ) + g1x * textureLod( tex, p1, lod ) ) +
			g1( fuv.y ) * ( g0x * textureLod( tex, p2, lod ) + g1x * textureLod( tex, p3, lod ) );
	}
	vec4 textureBicubic( sampler2D sampler, vec2 uv, float lod ) {
		vec2 fLodSize = vec2( textureSize( sampler, int( lod ) ) );
		vec2 cLodSize = vec2( textureSize( sampler, int( lod + 1.0 ) ) );
		vec2 fLodSizeInv = 1.0 / fLodSize;
		vec2 cLodSizeInv = 1.0 / cLodSize;
		vec4 fSample = bicubic( sampler, uv, vec4( fLodSizeInv, fLodSize ), floor( lod ) );
		vec4 cSample = bicubic( sampler, uv, vec4( cLodSizeInv, cLodSize ), ceil( lod ) );
		return mix( fSample, cSample, fract( lod ) );
	}
	vec3 getVolumeTransmissionRay( const in vec3 n, const in vec3 v, const in float thickness, const in float ior, const in mat4 modelMatrix ) {
		vec3 refractionVector = refract( - v, normalize( n ), 1.0 / ior );
		vec3 modelScale;
		modelScale.x = length( vec3( modelMatrix[ 0 ].xyz ) );
		modelScale.y = length( vec3( modelMatrix[ 1 ].xyz ) );
		modelScale.z = length( vec3( modelMatrix[ 2 ].xyz ) );
		return normalize( refractionVector ) * thickness * modelScale;
	}
	float applyIorToRoughness( const in float roughness, const in float ior ) {
		return roughness * clamp( ior * 2.0 - 2.0, 0.0, 1.0 );
	}
	vec4 getTransmissionSample( const in vec2 fragCoord, const in float roughness, const in float ior ) {
		float lod = log2( transmissionSamplerSize.x ) * applyIorToRoughness( roughness, ior );
		return textureBicubic( transmissionSamplerMap, fragCoord.xy, lod );
	}
	vec3 volumeAttenuation( const in float transmissionDistance, const in vec3 attenuationColor, const in float attenuationDistance ) {
		if ( isinf( attenuationDistance ) ) {
			return vec3( 1.0 );
		} else {
			vec3 attenuationCoefficient = -log( attenuationColor ) / attenuationDistance;
			vec3 transmittance = exp( - attenuationCoefficient * transmissionDistance );			return transmittance;
		}
	}
	vec4 getIBLVolumeRefraction( const in vec3 n, const in vec3 v, const in float roughness, const in vec3 diffuseColor,
		const in vec3 specularColor, const in float specularF90, const in vec3 position, const in mat4 modelMatrix,
		const in mat4 viewMatrix, const in mat4 projMatrix, const in float dispersion, const in float ior, const in float thickness,
		const in vec3 attenuationColor, const in float attenuationDistance ) {
		vec4 transmittedLight;
		vec3 transmittance;
		#ifdef USE_DISPERSION
			float halfSpread = ( ior - 1.0 ) * 0.025 * dispersion;
			vec3 iors = vec3( ior - halfSpread, ior, ior + halfSpread );
			for ( int i = 0; i < 3; i ++ ) {
				vec3 transmissionRay = getVolumeTransmissionRay( n, v, thickness, iors[ i ], modelMatrix );
				vec3 refractedRayExit = position + transmissionRay;
				vec4 ndcPos = projMatrix * viewMatrix * vec4( refractedRayExit, 1.0 );
				vec2 refractionCoords = ndcPos.xy / ndcPos.w;
				refractionCoords += 1.0;
				refractionCoords /= 2.0;
				vec4 transmissionSample = getTransmissionSample( refractionCoords, roughness, iors[ i ] );
				transmittedLight[ i ] = transmissionSample[ i ];
				transmittedLight.a += transmissionSample.a;
				transmittance[ i ] = diffuseColor[ i ] * volumeAttenuation( length( transmissionRay ), attenuationColor, attenuationDistance )[ i ];
			}
			transmittedLight.a /= 3.0;
		#else
			vec3 transmissionRay = getVolumeTransmissionRay( n, v, thickness, ior, modelMatrix );
			vec3 refractedRayExit = position + transmissionRay;
			vec4 ndcPos = projMatrix * viewMatrix * vec4( refractedRayExit, 1.0 );
			vec2 refractionCoords = ndcPos.xy / ndcPos.w;
			refractionCoords += 1.0;
			refractionCoords /= 2.0;
			transmittedLight = getTransmissionSample( refractionCoords, roughness, ior );
			transmittance = diffuseColor * volumeAttenuation( length( transmissionRay ), attenuationColor, attenuationDistance );
		#endif
		vec3 attenuatedColor = transmittance * transmittedLight.rgb;
		vec3 F = EnvironmentBRDF( n, v, specularColor, specularF90, roughness );
		float transmittanceFactor = ( transmittance.r + transmittance.g + transmittance.b ) / 3.0;
		return vec4( ( 1.0 - F ) * attenuatedColor, 1.0 - ( 1.0 - transmittedLight.a ) * transmittanceFactor );
	}
#endif`,dx=`#if defined( USE_UV ) || defined( USE_ANISOTROPY )
	varying vec2 vUv;
#endif
#ifdef USE_MAP
	varying vec2 vMapUv;
#endif
#ifdef USE_ALPHAMAP
	varying vec2 vAlphaMapUv;
#endif
#ifdef USE_LIGHTMAP
	varying vec2 vLightMapUv;
#endif
#ifdef USE_AOMAP
	varying vec2 vAoMapUv;
#endif
#ifdef USE_BUMPMAP
	varying vec2 vBumpMapUv;
#endif
#ifdef USE_NORMALMAP
	varying vec2 vNormalMapUv;
#endif
#ifdef USE_EMISSIVEMAP
	varying vec2 vEmissiveMapUv;
#endif
#ifdef USE_METALNESSMAP
	varying vec2 vMetalnessMapUv;
#endif
#ifdef USE_ROUGHNESSMAP
	varying vec2 vRoughnessMapUv;
#endif
#ifdef USE_ANISOTROPYMAP
	varying vec2 vAnisotropyMapUv;
#endif
#ifdef USE_CLEARCOATMAP
	varying vec2 vClearcoatMapUv;
#endif
#ifdef USE_CLEARCOAT_NORMALMAP
	varying vec2 vClearcoatNormalMapUv;
#endif
#ifdef USE_CLEARCOAT_ROUGHNESSMAP
	varying vec2 vClearcoatRoughnessMapUv;
#endif
#ifdef USE_IRIDESCENCEMAP
	varying vec2 vIridescenceMapUv;
#endif
#ifdef USE_IRIDESCENCE_THICKNESSMAP
	varying vec2 vIridescenceThicknessMapUv;
#endif
#ifdef USE_SHEEN_COLORMAP
	varying vec2 vSheenColorMapUv;
#endif
#ifdef USE_SHEEN_ROUGHNESSMAP
	varying vec2 vSheenRoughnessMapUv;
#endif
#ifdef USE_SPECULARMAP
	varying vec2 vSpecularMapUv;
#endif
#ifdef USE_SPECULAR_COLORMAP
	varying vec2 vSpecularColorMapUv;
#endif
#ifdef USE_SPECULAR_INTENSITYMAP
	varying vec2 vSpecularIntensityMapUv;
#endif
#ifdef USE_TRANSMISSIONMAP
	uniform mat3 transmissionMapTransform;
	varying vec2 vTransmissionMapUv;
#endif
#ifdef USE_THICKNESSMAP
	uniform mat3 thicknessMapTransform;
	varying vec2 vThicknessMapUv;
#endif`,px=`#if defined( USE_UV ) || defined( USE_ANISOTROPY )
	varying vec2 vUv;
#endif
#ifdef USE_MAP
	uniform mat3 mapTransform;
	varying vec2 vMapUv;
#endif
#ifdef USE_ALPHAMAP
	uniform mat3 alphaMapTransform;
	varying vec2 vAlphaMapUv;
#endif
#ifdef USE_LIGHTMAP
	uniform mat3 lightMapTransform;
	varying vec2 vLightMapUv;
#endif
#ifdef USE_AOMAP
	uniform mat3 aoMapTransform;
	varying vec2 vAoMapUv;
#endif
#ifdef USE_BUMPMAP
	uniform mat3 bumpMapTransform;
	varying vec2 vBumpMapUv;
#endif
#ifdef USE_NORMALMAP
	uniform mat3 normalMapTransform;
	varying vec2 vNormalMapUv;
#endif
#ifdef USE_DISPLACEMENTMAP
	uniform mat3 displacementMapTransform;
	varying vec2 vDisplacementMapUv;
#endif
#ifdef USE_EMISSIVEMAP
	uniform mat3 emissiveMapTransform;
	varying vec2 vEmissiveMapUv;
#endif
#ifdef USE_METALNESSMAP
	uniform mat3 metalnessMapTransform;
	varying vec2 vMetalnessMapUv;
#endif
#ifdef USE_ROUGHNESSMAP
	uniform mat3 roughnessMapTransform;
	varying vec2 vRoughnessMapUv;
#endif
#ifdef USE_ANISOTROPYMAP
	uniform mat3 anisotropyMapTransform;
	varying vec2 vAnisotropyMapUv;
#endif
#ifdef USE_CLEARCOATMAP
	uniform mat3 clearcoatMapTransform;
	varying vec2 vClearcoatMapUv;
#endif
#ifdef USE_CLEARCOAT_NORMALMAP
	uniform mat3 clearcoatNormalMapTransform;
	varying vec2 vClearcoatNormalMapUv;
#endif
#ifdef USE_CLEARCOAT_ROUGHNESSMAP
	uniform mat3 clearcoatRoughnessMapTransform;
	varying vec2 vClearcoatRoughnessMapUv;
#endif
#ifdef USE_SHEEN_COLORMAP
	uniform mat3 sheenColorMapTransform;
	varying vec2 vSheenColorMapUv;
#endif
#ifdef USE_SHEEN_ROUGHNESSMAP
	uniform mat3 sheenRoughnessMapTransform;
	varying vec2 vSheenRoughnessMapUv;
#endif
#ifdef USE_IRIDESCENCEMAP
	uniform mat3 iridescenceMapTransform;
	varying vec2 vIridescenceMapUv;
#endif
#ifdef USE_IRIDESCENCE_THICKNESSMAP
	uniform mat3 iridescenceThicknessMapTransform;
	varying vec2 vIridescenceThicknessMapUv;
#endif
#ifdef USE_SPECULARMAP
	uniform mat3 specularMapTransform;
	varying vec2 vSpecularMapUv;
#endif
#ifdef USE_SPECULAR_COLORMAP
	uniform mat3 specularColorMapTransform;
	varying vec2 vSpecularColorMapUv;
#endif
#ifdef USE_SPECULAR_INTENSITYMAP
	uniform mat3 specularIntensityMapTransform;
	varying vec2 vSpecularIntensityMapUv;
#endif
#ifdef USE_TRANSMISSIONMAP
	uniform mat3 transmissionMapTransform;
	varying vec2 vTransmissionMapUv;
#endif
#ifdef USE_THICKNESSMAP
	uniform mat3 thicknessMapTransform;
	varying vec2 vThicknessMapUv;
#endif`,mx=`#if defined( USE_UV ) || defined( USE_ANISOTROPY )
	vUv = vec3( uv, 1 ).xy;
#endif
#ifdef USE_MAP
	vMapUv = ( mapTransform * vec3( MAP_UV, 1 ) ).xy;
#endif
#ifdef USE_ALPHAMAP
	vAlphaMapUv = ( alphaMapTransform * vec3( ALPHAMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_LIGHTMAP
	vLightMapUv = ( lightMapTransform * vec3( LIGHTMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_AOMAP
	vAoMapUv = ( aoMapTransform * vec3( AOMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_BUMPMAP
	vBumpMapUv = ( bumpMapTransform * vec3( BUMPMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_NORMALMAP
	vNormalMapUv = ( normalMapTransform * vec3( NORMALMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_DISPLACEMENTMAP
	vDisplacementMapUv = ( displacementMapTransform * vec3( DISPLACEMENTMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_EMISSIVEMAP
	vEmissiveMapUv = ( emissiveMapTransform * vec3( EMISSIVEMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_METALNESSMAP
	vMetalnessMapUv = ( metalnessMapTransform * vec3( METALNESSMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_ROUGHNESSMAP
	vRoughnessMapUv = ( roughnessMapTransform * vec3( ROUGHNESSMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_ANISOTROPYMAP
	vAnisotropyMapUv = ( anisotropyMapTransform * vec3( ANISOTROPYMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_CLEARCOATMAP
	vClearcoatMapUv = ( clearcoatMapTransform * vec3( CLEARCOATMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_CLEARCOAT_NORMALMAP
	vClearcoatNormalMapUv = ( clearcoatNormalMapTransform * vec3( CLEARCOAT_NORMALMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_CLEARCOAT_ROUGHNESSMAP
	vClearcoatRoughnessMapUv = ( clearcoatRoughnessMapTransform * vec3( CLEARCOAT_ROUGHNESSMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_IRIDESCENCEMAP
	vIridescenceMapUv = ( iridescenceMapTransform * vec3( IRIDESCENCEMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_IRIDESCENCE_THICKNESSMAP
	vIridescenceThicknessMapUv = ( iridescenceThicknessMapTransform * vec3( IRIDESCENCE_THICKNESSMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_SHEEN_COLORMAP
	vSheenColorMapUv = ( sheenColorMapTransform * vec3( SHEEN_COLORMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_SHEEN_ROUGHNESSMAP
	vSheenRoughnessMapUv = ( sheenRoughnessMapTransform * vec3( SHEEN_ROUGHNESSMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_SPECULARMAP
	vSpecularMapUv = ( specularMapTransform * vec3( SPECULARMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_SPECULAR_COLORMAP
	vSpecularColorMapUv = ( specularColorMapTransform * vec3( SPECULAR_COLORMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_SPECULAR_INTENSITYMAP
	vSpecularIntensityMapUv = ( specularIntensityMapTransform * vec3( SPECULAR_INTENSITYMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_TRANSMISSIONMAP
	vTransmissionMapUv = ( transmissionMapTransform * vec3( TRANSMISSIONMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_THICKNESSMAP
	vThicknessMapUv = ( thicknessMapTransform * vec3( THICKNESSMAP_UV, 1 ) ).xy;
#endif`,gx=`#if defined( USE_ENVMAP ) || defined( DISTANCE ) || defined ( USE_SHADOWMAP ) || defined ( USE_TRANSMISSION ) || NUM_SPOT_LIGHT_COORDS > 0
	vec4 worldPosition = vec4( transformed, 1.0 );
	#ifdef USE_BATCHING
		worldPosition = batchingMatrix * worldPosition;
	#endif
	#ifdef USE_INSTANCING
		worldPosition = instanceMatrix * worldPosition;
	#endif
	worldPosition = modelMatrix * worldPosition;
#endif`;const _x=`varying vec2 vUv;
uniform mat3 uvTransform;
void main() {
	vUv = ( uvTransform * vec3( uv, 1 ) ).xy;
	gl_Position = vec4( position.xy, 1.0, 1.0 );
}`,xx=`uniform sampler2D t2D;
uniform float backgroundIntensity;
varying vec2 vUv;
void main() {
	vec4 texColor = texture2D( t2D, vUv );
	#ifdef DECODE_VIDEO_TEXTURE
		texColor = vec4( mix( pow( texColor.rgb * 0.9478672986 + vec3( 0.0521327014 ), vec3( 2.4 ) ), texColor.rgb * 0.0773993808, vec3( lessThanEqual( texColor.rgb, vec3( 0.04045 ) ) ) ), texColor.w );
	#endif
	texColor.rgb *= backgroundIntensity;
	gl_FragColor = texColor;
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
}`,vx=`varying vec3 vWorldDirection;
#include <common>
void main() {
	vWorldDirection = transformDirection( position, modelMatrix );
	#include <begin_vertex>
	#include <project_vertex>
	gl_Position.z = gl_Position.w;
}`,Mx=`#ifdef ENVMAP_TYPE_CUBE
	uniform samplerCube envMap;
#elif defined( ENVMAP_TYPE_CUBE_UV )
	uniform sampler2D envMap;
#endif
uniform float flipEnvMap;
uniform float backgroundBlurriness;
uniform float backgroundIntensity;
uniform mat3 backgroundRotation;
varying vec3 vWorldDirection;
#include <cube_uv_reflection_fragment>
void main() {
	#ifdef ENVMAP_TYPE_CUBE
		vec4 texColor = textureCube( envMap, backgroundRotation * vec3( flipEnvMap * vWorldDirection.x, vWorldDirection.yz ) );
	#elif defined( ENVMAP_TYPE_CUBE_UV )
		vec4 texColor = textureCubeUV( envMap, backgroundRotation * vWorldDirection, backgroundBlurriness );
	#else
		vec4 texColor = vec4( 0.0, 0.0, 0.0, 1.0 );
	#endif
	texColor.rgb *= backgroundIntensity;
	gl_FragColor = texColor;
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
}`,Sx=`varying vec3 vWorldDirection;
#include <common>
void main() {
	vWorldDirection = transformDirection( position, modelMatrix );
	#include <begin_vertex>
	#include <project_vertex>
	gl_Position.z = gl_Position.w;
}`,yx=`uniform samplerCube tCube;
uniform float tFlip;
uniform float opacity;
varying vec3 vWorldDirection;
void main() {
	vec4 texColor = textureCube( tCube, vec3( tFlip * vWorldDirection.x, vWorldDirection.yz ) );
	gl_FragColor = texColor;
	gl_FragColor.a *= opacity;
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
}`,bx=`#include <common>
#include <batching_pars_vertex>
#include <uv_pars_vertex>
#include <displacementmap_pars_vertex>
#include <morphtarget_pars_vertex>
#include <skinning_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <clipping_planes_pars_vertex>
varying vec2 vHighPrecisionZW;
void main() {
	#include <uv_vertex>
	#include <batching_vertex>
	#include <skinbase_vertex>
	#include <morphinstance_vertex>
	#ifdef USE_DISPLACEMENTMAP
		#include <beginnormal_vertex>
		#include <morphnormal_vertex>
		#include <skinnormal_vertex>
	#endif
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <skinning_vertex>
	#include <displacementmap_vertex>
	#include <project_vertex>
	#include <logdepthbuf_vertex>
	#include <clipping_planes_vertex>
	vHighPrecisionZW = gl_Position.zw;
}`,Ex=`#if DEPTH_PACKING == 3200
	uniform float opacity;
#endif
#include <common>
#include <packing>
#include <uv_pars_fragment>
#include <map_pars_fragment>
#include <alphamap_pars_fragment>
#include <alphatest_pars_fragment>
#include <alphahash_pars_fragment>
#include <logdepthbuf_pars_fragment>
#include <clipping_planes_pars_fragment>
varying vec2 vHighPrecisionZW;
void main() {
	vec4 diffuseColor = vec4( 1.0 );
	#include <clipping_planes_fragment>
	#if DEPTH_PACKING == 3200
		diffuseColor.a = opacity;
	#endif
	#include <map_fragment>
	#include <alphamap_fragment>
	#include <alphatest_fragment>
	#include <alphahash_fragment>
	#include <logdepthbuf_fragment>
	#ifdef USE_REVERSED_DEPTH_BUFFER
		float fragCoordZ = vHighPrecisionZW[ 0 ] / vHighPrecisionZW[ 1 ];
	#else
		float fragCoordZ = 0.5 * vHighPrecisionZW[ 0 ] / vHighPrecisionZW[ 1 ] + 0.5;
	#endif
	#if DEPTH_PACKING == 3200
		gl_FragColor = vec4( vec3( 1.0 - fragCoordZ ), opacity );
	#elif DEPTH_PACKING == 3201
		gl_FragColor = packDepthToRGBA( fragCoordZ );
	#elif DEPTH_PACKING == 3202
		gl_FragColor = vec4( packDepthToRGB( fragCoordZ ), 1.0 );
	#elif DEPTH_PACKING == 3203
		gl_FragColor = vec4( packDepthToRG( fragCoordZ ), 0.0, 1.0 );
	#endif
}`,Tx=`#define DISTANCE
varying vec3 vWorldPosition;
#include <common>
#include <batching_pars_vertex>
#include <uv_pars_vertex>
#include <displacementmap_pars_vertex>
#include <morphtarget_pars_vertex>
#include <skinning_pars_vertex>
#include <clipping_planes_pars_vertex>
void main() {
	#include <uv_vertex>
	#include <batching_vertex>
	#include <skinbase_vertex>
	#include <morphinstance_vertex>
	#ifdef USE_DISPLACEMENTMAP
		#include <beginnormal_vertex>
		#include <morphnormal_vertex>
		#include <skinnormal_vertex>
	#endif
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <skinning_vertex>
	#include <displacementmap_vertex>
	#include <project_vertex>
	#include <worldpos_vertex>
	#include <clipping_planes_vertex>
	vWorldPosition = worldPosition.xyz;
}`,Ax=`#define DISTANCE
uniform vec3 referencePosition;
uniform float nearDistance;
uniform float farDistance;
varying vec3 vWorldPosition;
#include <common>
#include <uv_pars_fragment>
#include <map_pars_fragment>
#include <alphamap_pars_fragment>
#include <alphatest_pars_fragment>
#include <alphahash_pars_fragment>
#include <clipping_planes_pars_fragment>
void main () {
	vec4 diffuseColor = vec4( 1.0 );
	#include <clipping_planes_fragment>
	#include <map_fragment>
	#include <alphamap_fragment>
	#include <alphatest_fragment>
	#include <alphahash_fragment>
	float dist = length( vWorldPosition - referencePosition );
	dist = ( dist - nearDistance ) / ( farDistance - nearDistance );
	dist = saturate( dist );
	gl_FragColor = vec4( dist, 0.0, 0.0, 1.0 );
}`,wx=`varying vec3 vWorldDirection;
#include <common>
void main() {
	vWorldDirection = transformDirection( position, modelMatrix );
	#include <begin_vertex>
	#include <project_vertex>
}`,Cx=`uniform sampler2D tEquirect;
varying vec3 vWorldDirection;
#include <common>
void main() {
	vec3 direction = normalize( vWorldDirection );
	vec2 sampleUV = equirectUv( direction );
	gl_FragColor = texture2D( tEquirect, sampleUV );
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
}`,Rx=`uniform float scale;
attribute float lineDistance;
varying float vLineDistance;
#include <common>
#include <uv_pars_vertex>
#include <color_pars_vertex>
#include <fog_pars_vertex>
#include <morphtarget_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <clipping_planes_pars_vertex>
void main() {
	vLineDistance = scale * lineDistance;
	#include <uv_vertex>
	#include <color_vertex>
	#include <morphinstance_vertex>
	#include <morphcolor_vertex>
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <project_vertex>
	#include <logdepthbuf_vertex>
	#include <clipping_planes_vertex>
	#include <fog_vertex>
}`,Px=`uniform vec3 diffuse;
uniform float opacity;
uniform float dashSize;
uniform float totalSize;
varying float vLineDistance;
#include <common>
#include <color_pars_fragment>
#include <uv_pars_fragment>
#include <map_pars_fragment>
#include <fog_pars_fragment>
#include <logdepthbuf_pars_fragment>
#include <clipping_planes_pars_fragment>
void main() {
	vec4 diffuseColor = vec4( diffuse, opacity );
	#include <clipping_planes_fragment>
	if ( mod( vLineDistance, totalSize ) > dashSize ) {
		discard;
	}
	vec3 outgoingLight = vec3( 0.0 );
	#include <logdepthbuf_fragment>
	#include <map_fragment>
	#include <color_fragment>
	outgoingLight = diffuseColor.rgb;
	#include <opaque_fragment>
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
	#include <fog_fragment>
	#include <premultiplied_alpha_fragment>
}`,Dx=`#include <common>
#include <batching_pars_vertex>
#include <uv_pars_vertex>
#include <envmap_pars_vertex>
#include <color_pars_vertex>
#include <fog_pars_vertex>
#include <morphtarget_pars_vertex>
#include <skinning_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <clipping_planes_pars_vertex>
void main() {
	#include <uv_vertex>
	#include <color_vertex>
	#include <morphinstance_vertex>
	#include <morphcolor_vertex>
	#include <batching_vertex>
	#if defined ( USE_ENVMAP ) || defined ( USE_SKINNING )
		#include <beginnormal_vertex>
		#include <morphnormal_vertex>
		#include <skinbase_vertex>
		#include <skinnormal_vertex>
		#include <defaultnormal_vertex>
	#endif
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <skinning_vertex>
	#include <project_vertex>
	#include <logdepthbuf_vertex>
	#include <clipping_planes_vertex>
	#include <worldpos_vertex>
	#include <envmap_vertex>
	#include <fog_vertex>
}`,Lx=`uniform vec3 diffuse;
uniform float opacity;
#ifndef FLAT_SHADED
	varying vec3 vNormal;
#endif
#include <common>
#include <dithering_pars_fragment>
#include <color_pars_fragment>
#include <uv_pars_fragment>
#include <map_pars_fragment>
#include <alphamap_pars_fragment>
#include <alphatest_pars_fragment>
#include <alphahash_pars_fragment>
#include <aomap_pars_fragment>
#include <lightmap_pars_fragment>
#include <envmap_common_pars_fragment>
#include <envmap_pars_fragment>
#include <fog_pars_fragment>
#include <specularmap_pars_fragment>
#include <logdepthbuf_pars_fragment>
#include <clipping_planes_pars_fragment>
void main() {
	vec4 diffuseColor = vec4( diffuse, opacity );
	#include <clipping_planes_fragment>
	#include <logdepthbuf_fragment>
	#include <map_fragment>
	#include <color_fragment>
	#include <alphamap_fragment>
	#include <alphatest_fragment>
	#include <alphahash_fragment>
	#include <specularmap_fragment>
	ReflectedLight reflectedLight = ReflectedLight( vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ) );
	#ifdef USE_LIGHTMAP
		vec4 lightMapTexel = texture2D( lightMap, vLightMapUv );
		reflectedLight.indirectDiffuse += lightMapTexel.rgb * lightMapIntensity * RECIPROCAL_PI;
	#else
		reflectedLight.indirectDiffuse += vec3( 1.0 );
	#endif
	#include <aomap_fragment>
	reflectedLight.indirectDiffuse *= diffuseColor.rgb;
	vec3 outgoingLight = reflectedLight.indirectDiffuse;
	#include <envmap_fragment>
	#include <opaque_fragment>
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
	#include <fog_fragment>
	#include <premultiplied_alpha_fragment>
	#include <dithering_fragment>
}`,Ix=`#define LAMBERT
varying vec3 vViewPosition;
#include <common>
#include <batching_pars_vertex>
#include <uv_pars_vertex>
#include <displacementmap_pars_vertex>
#include <envmap_pars_vertex>
#include <color_pars_vertex>
#include <fog_pars_vertex>
#include <normal_pars_vertex>
#include <morphtarget_pars_vertex>
#include <skinning_pars_vertex>
#include <shadowmap_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <clipping_planes_pars_vertex>
void main() {
	#include <uv_vertex>
	#include <color_vertex>
	#include <morphinstance_vertex>
	#include <morphcolor_vertex>
	#include <batching_vertex>
	#include <beginnormal_vertex>
	#include <morphnormal_vertex>
	#include <skinbase_vertex>
	#include <skinnormal_vertex>
	#include <defaultnormal_vertex>
	#include <normal_vertex>
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <skinning_vertex>
	#include <displacementmap_vertex>
	#include <project_vertex>
	#include <logdepthbuf_vertex>
	#include <clipping_planes_vertex>
	vViewPosition = - mvPosition.xyz;
	#include <worldpos_vertex>
	#include <envmap_vertex>
	#include <shadowmap_vertex>
	#include <fog_vertex>
}`,Ux=`#define LAMBERT
uniform vec3 diffuse;
uniform vec3 emissive;
uniform float opacity;
#include <common>
#include <dithering_pars_fragment>
#include <color_pars_fragment>
#include <uv_pars_fragment>
#include <map_pars_fragment>
#include <alphamap_pars_fragment>
#include <alphatest_pars_fragment>
#include <alphahash_pars_fragment>
#include <aomap_pars_fragment>
#include <lightmap_pars_fragment>
#include <emissivemap_pars_fragment>
#include <cube_uv_reflection_fragment>
#include <envmap_common_pars_fragment>
#include <envmap_pars_fragment>
#include <envmap_physical_pars_fragment>
#include <fog_pars_fragment>
#include <bsdfs>
#include <lights_pars_begin>
#include <normal_pars_fragment>
#include <lights_lambert_pars_fragment>
#include <shadowmap_pars_fragment>
#include <bumpmap_pars_fragment>
#include <normalmap_pars_fragment>
#include <specularmap_pars_fragment>
#include <logdepthbuf_pars_fragment>
#include <clipping_planes_pars_fragment>
void main() {
	vec4 diffuseColor = vec4( diffuse, opacity );
	#include <clipping_planes_fragment>
	ReflectedLight reflectedLight = ReflectedLight( vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ) );
	vec3 totalEmissiveRadiance = emissive;
	#include <logdepthbuf_fragment>
	#include <map_fragment>
	#include <color_fragment>
	#include <alphamap_fragment>
	#include <alphatest_fragment>
	#include <alphahash_fragment>
	#include <specularmap_fragment>
	#include <normal_fragment_begin>
	#include <normal_fragment_maps>
	#include <emissivemap_fragment>
	#include <lights_lambert_fragment>
	#include <lights_fragment_begin>
	#include <lights_fragment_maps>
	#include <lights_fragment_end>
	#include <aomap_fragment>
	vec3 outgoingLight = reflectedLight.directDiffuse + reflectedLight.indirectDiffuse + totalEmissiveRadiance;
	#include <envmap_fragment>
	#include <opaque_fragment>
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
	#include <fog_fragment>
	#include <premultiplied_alpha_fragment>
	#include <dithering_fragment>
}`,Nx=`#define MATCAP
varying vec3 vViewPosition;
#include <common>
#include <batching_pars_vertex>
#include <uv_pars_vertex>
#include <color_pars_vertex>
#include <displacementmap_pars_vertex>
#include <fog_pars_vertex>
#include <normal_pars_vertex>
#include <morphtarget_pars_vertex>
#include <skinning_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <clipping_planes_pars_vertex>
void main() {
	#include <uv_vertex>
	#include <color_vertex>
	#include <morphinstance_vertex>
	#include <morphcolor_vertex>
	#include <batching_vertex>
	#include <beginnormal_vertex>
	#include <morphnormal_vertex>
	#include <skinbase_vertex>
	#include <skinnormal_vertex>
	#include <defaultnormal_vertex>
	#include <normal_vertex>
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <skinning_vertex>
	#include <displacementmap_vertex>
	#include <project_vertex>
	#include <logdepthbuf_vertex>
	#include <clipping_planes_vertex>
	#include <fog_vertex>
	vViewPosition = - mvPosition.xyz;
}`,Fx=`#define MATCAP
uniform vec3 diffuse;
uniform float opacity;
uniform sampler2D matcap;
varying vec3 vViewPosition;
#include <common>
#include <dithering_pars_fragment>
#include <color_pars_fragment>
#include <uv_pars_fragment>
#include <map_pars_fragment>
#include <alphamap_pars_fragment>
#include <alphatest_pars_fragment>
#include <alphahash_pars_fragment>
#include <fog_pars_fragment>
#include <normal_pars_fragment>
#include <bumpmap_pars_fragment>
#include <normalmap_pars_fragment>
#include <logdepthbuf_pars_fragment>
#include <clipping_planes_pars_fragment>
void main() {
	vec4 diffuseColor = vec4( diffuse, opacity );
	#include <clipping_planes_fragment>
	#include <logdepthbuf_fragment>
	#include <map_fragment>
	#include <color_fragment>
	#include <alphamap_fragment>
	#include <alphatest_fragment>
	#include <alphahash_fragment>
	#include <normal_fragment_begin>
	#include <normal_fragment_maps>
	vec3 viewDir = normalize( vViewPosition );
	vec3 x = normalize( vec3( viewDir.z, 0.0, - viewDir.x ) );
	vec3 y = cross( viewDir, x );
	vec2 uv = vec2( dot( x, normal ), dot( y, normal ) ) * 0.495 + 0.5;
	#ifdef USE_MATCAP
		vec4 matcapColor = texture2D( matcap, uv );
	#else
		vec4 matcapColor = vec4( vec3( mix( 0.2, 0.8, uv.y ) ), 1.0 );
	#endif
	vec3 outgoingLight = diffuseColor.rgb * matcapColor.rgb;
	#include <opaque_fragment>
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
	#include <fog_fragment>
	#include <premultiplied_alpha_fragment>
	#include <dithering_fragment>
}`,Ox=`#define NORMAL
#if defined( FLAT_SHADED ) || defined( USE_BUMPMAP ) || defined( USE_NORMALMAP_TANGENTSPACE )
	varying vec3 vViewPosition;
#endif
#include <common>
#include <batching_pars_vertex>
#include <uv_pars_vertex>
#include <displacementmap_pars_vertex>
#include <normal_pars_vertex>
#include <morphtarget_pars_vertex>
#include <skinning_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <clipping_planes_pars_vertex>
void main() {
	#include <uv_vertex>
	#include <batching_vertex>
	#include <beginnormal_vertex>
	#include <morphinstance_vertex>
	#include <morphnormal_vertex>
	#include <skinbase_vertex>
	#include <skinnormal_vertex>
	#include <defaultnormal_vertex>
	#include <normal_vertex>
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <skinning_vertex>
	#include <displacementmap_vertex>
	#include <project_vertex>
	#include <logdepthbuf_vertex>
	#include <clipping_planes_vertex>
#if defined( FLAT_SHADED ) || defined( USE_BUMPMAP ) || defined( USE_NORMALMAP_TANGENTSPACE )
	vViewPosition = - mvPosition.xyz;
#endif
}`,Bx=`#define NORMAL
uniform float opacity;
#if defined( FLAT_SHADED ) || defined( USE_BUMPMAP ) || defined( USE_NORMALMAP_TANGENTSPACE )
	varying vec3 vViewPosition;
#endif
#include <uv_pars_fragment>
#include <normal_pars_fragment>
#include <bumpmap_pars_fragment>
#include <normalmap_pars_fragment>
#include <logdepthbuf_pars_fragment>
#include <clipping_planes_pars_fragment>
void main() {
	vec4 diffuseColor = vec4( 0.0, 0.0, 0.0, opacity );
	#include <clipping_planes_fragment>
	#include <logdepthbuf_fragment>
	#include <normal_fragment_begin>
	#include <normal_fragment_maps>
	gl_FragColor = vec4( normalize( normal ) * 0.5 + 0.5, diffuseColor.a );
	#ifdef OPAQUE
		gl_FragColor.a = 1.0;
	#endif
}`,zx=`#define PHONG
varying vec3 vViewPosition;
#include <common>
#include <batching_pars_vertex>
#include <uv_pars_vertex>
#include <displacementmap_pars_vertex>
#include <envmap_pars_vertex>
#include <color_pars_vertex>
#include <fog_pars_vertex>
#include <normal_pars_vertex>
#include <morphtarget_pars_vertex>
#include <skinning_pars_vertex>
#include <shadowmap_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <clipping_planes_pars_vertex>
void main() {
	#include <uv_vertex>
	#include <color_vertex>
	#include <morphcolor_vertex>
	#include <batching_vertex>
	#include <beginnormal_vertex>
	#include <morphinstance_vertex>
	#include <morphnormal_vertex>
	#include <skinbase_vertex>
	#include <skinnormal_vertex>
	#include <defaultnormal_vertex>
	#include <normal_vertex>
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <skinning_vertex>
	#include <displacementmap_vertex>
	#include <project_vertex>
	#include <logdepthbuf_vertex>
	#include <clipping_planes_vertex>
	vViewPosition = - mvPosition.xyz;
	#include <worldpos_vertex>
	#include <envmap_vertex>
	#include <shadowmap_vertex>
	#include <fog_vertex>
}`,Hx=`#define PHONG
uniform vec3 diffuse;
uniform vec3 emissive;
uniform vec3 specular;
uniform float shininess;
uniform float opacity;
#include <common>
#include <dithering_pars_fragment>
#include <color_pars_fragment>
#include <uv_pars_fragment>
#include <map_pars_fragment>
#include <alphamap_pars_fragment>
#include <alphatest_pars_fragment>
#include <alphahash_pars_fragment>
#include <aomap_pars_fragment>
#include <lightmap_pars_fragment>
#include <emissivemap_pars_fragment>
#include <cube_uv_reflection_fragment>
#include <envmap_common_pars_fragment>
#include <envmap_pars_fragment>
#include <envmap_physical_pars_fragment>
#include <fog_pars_fragment>
#include <bsdfs>
#include <lights_pars_begin>
#include <normal_pars_fragment>
#include <lights_phong_pars_fragment>
#include <shadowmap_pars_fragment>
#include <bumpmap_pars_fragment>
#include <normalmap_pars_fragment>
#include <specularmap_pars_fragment>
#include <logdepthbuf_pars_fragment>
#include <clipping_planes_pars_fragment>
void main() {
	vec4 diffuseColor = vec4( diffuse, opacity );
	#include <clipping_planes_fragment>
	ReflectedLight reflectedLight = ReflectedLight( vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ) );
	vec3 totalEmissiveRadiance = emissive;
	#include <logdepthbuf_fragment>
	#include <map_fragment>
	#include <color_fragment>
	#include <alphamap_fragment>
	#include <alphatest_fragment>
	#include <alphahash_fragment>
	#include <specularmap_fragment>
	#include <normal_fragment_begin>
	#include <normal_fragment_maps>
	#include <emissivemap_fragment>
	#include <lights_phong_fragment>
	#include <lights_fragment_begin>
	#include <lights_fragment_maps>
	#include <lights_fragment_end>
	#include <aomap_fragment>
	vec3 outgoingLight = reflectedLight.directDiffuse + reflectedLight.indirectDiffuse + reflectedLight.directSpecular + reflectedLight.indirectSpecular + totalEmissiveRadiance;
	#include <envmap_fragment>
	#include <opaque_fragment>
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
	#include <fog_fragment>
	#include <premultiplied_alpha_fragment>
	#include <dithering_fragment>
}`,Vx=`#define STANDARD
varying vec3 vViewPosition;
#ifdef USE_TRANSMISSION
	varying vec3 vWorldPosition;
#endif
#include <common>
#include <batching_pars_vertex>
#include <uv_pars_vertex>
#include <displacementmap_pars_vertex>
#include <color_pars_vertex>
#include <fog_pars_vertex>
#include <normal_pars_vertex>
#include <morphtarget_pars_vertex>
#include <skinning_pars_vertex>
#include <shadowmap_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <clipping_planes_pars_vertex>
void main() {
	#include <uv_vertex>
	#include <color_vertex>
	#include <morphinstance_vertex>
	#include <morphcolor_vertex>
	#include <batching_vertex>
	#include <beginnormal_vertex>
	#include <morphnormal_vertex>
	#include <skinbase_vertex>
	#include <skinnormal_vertex>
	#include <defaultnormal_vertex>
	#include <normal_vertex>
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <skinning_vertex>
	#include <displacementmap_vertex>
	#include <project_vertex>
	#include <logdepthbuf_vertex>
	#include <clipping_planes_vertex>
	vViewPosition = - mvPosition.xyz;
	#include <worldpos_vertex>
	#include <shadowmap_vertex>
	#include <fog_vertex>
#ifdef USE_TRANSMISSION
	vWorldPosition = worldPosition.xyz;
#endif
}`,kx=`#define STANDARD
#ifdef PHYSICAL
	#define IOR
	#define USE_SPECULAR
#endif
uniform vec3 diffuse;
uniform vec3 emissive;
uniform float roughness;
uniform float metalness;
uniform float opacity;
#ifdef IOR
	uniform float ior;
#endif
#ifdef USE_SPECULAR
	uniform float specularIntensity;
	uniform vec3 specularColor;
	#ifdef USE_SPECULAR_COLORMAP
		uniform sampler2D specularColorMap;
	#endif
	#ifdef USE_SPECULAR_INTENSITYMAP
		uniform sampler2D specularIntensityMap;
	#endif
#endif
#ifdef USE_CLEARCOAT
	uniform float clearcoat;
	uniform float clearcoatRoughness;
#endif
#ifdef USE_DISPERSION
	uniform float dispersion;
#endif
#ifdef USE_IRIDESCENCE
	uniform float iridescence;
	uniform float iridescenceIOR;
	uniform float iridescenceThicknessMinimum;
	uniform float iridescenceThicknessMaximum;
#endif
#ifdef USE_SHEEN
	uniform vec3 sheenColor;
	uniform float sheenRoughness;
	#ifdef USE_SHEEN_COLORMAP
		uniform sampler2D sheenColorMap;
	#endif
	#ifdef USE_SHEEN_ROUGHNESSMAP
		uniform sampler2D sheenRoughnessMap;
	#endif
#endif
#ifdef USE_ANISOTROPY
	uniform vec2 anisotropyVector;
	#ifdef USE_ANISOTROPYMAP
		uniform sampler2D anisotropyMap;
	#endif
#endif
varying vec3 vViewPosition;
#include <common>
#include <dithering_pars_fragment>
#include <color_pars_fragment>
#include <uv_pars_fragment>
#include <map_pars_fragment>
#include <alphamap_pars_fragment>
#include <alphatest_pars_fragment>
#include <alphahash_pars_fragment>
#include <aomap_pars_fragment>
#include <lightmap_pars_fragment>
#include <emissivemap_pars_fragment>
#include <iridescence_fragment>
#include <cube_uv_reflection_fragment>
#include <envmap_common_pars_fragment>
#include <envmap_physical_pars_fragment>
#include <fog_pars_fragment>
#include <lights_pars_begin>
#include <normal_pars_fragment>
#include <lights_physical_pars_fragment>
#include <transmission_pars_fragment>
#include <shadowmap_pars_fragment>
#include <bumpmap_pars_fragment>
#include <normalmap_pars_fragment>
#include <clearcoat_pars_fragment>
#include <iridescence_pars_fragment>
#include <roughnessmap_pars_fragment>
#include <metalnessmap_pars_fragment>
#include <logdepthbuf_pars_fragment>
#include <clipping_planes_pars_fragment>
void main() {
	vec4 diffuseColor = vec4( diffuse, opacity );
	#include <clipping_planes_fragment>
	ReflectedLight reflectedLight = ReflectedLight( vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ) );
	vec3 totalEmissiveRadiance = emissive;
	#include <logdepthbuf_fragment>
	#include <map_fragment>
	#include <color_fragment>
	#include <alphamap_fragment>
	#include <alphatest_fragment>
	#include <alphahash_fragment>
	#include <roughnessmap_fragment>
	#include <metalnessmap_fragment>
	#include <normal_fragment_begin>
	#include <normal_fragment_maps>
	#include <clearcoat_normal_fragment_begin>
	#include <clearcoat_normal_fragment_maps>
	#include <emissivemap_fragment>
	#include <lights_physical_fragment>
	#include <lights_fragment_begin>
	#include <lights_fragment_maps>
	#include <lights_fragment_end>
	#include <aomap_fragment>
	vec3 totalDiffuse = reflectedLight.directDiffuse + reflectedLight.indirectDiffuse;
	vec3 totalSpecular = reflectedLight.directSpecular + reflectedLight.indirectSpecular;
	#include <transmission_fragment>
	vec3 outgoingLight = totalDiffuse + totalSpecular + totalEmissiveRadiance;
	#ifdef USE_SHEEN
 
		outgoingLight = outgoingLight + sheenSpecularDirect + sheenSpecularIndirect;
 
 	#endif
	#ifdef USE_CLEARCOAT
		float dotNVcc = saturate( dot( geometryClearcoatNormal, geometryViewDir ) );
		vec3 Fcc = F_Schlick( material.clearcoatF0, material.clearcoatF90, dotNVcc );
		outgoingLight = outgoingLight * ( 1.0 - material.clearcoat * Fcc ) + ( clearcoatSpecularDirect + clearcoatSpecularIndirect ) * material.clearcoat;
	#endif
	#include <opaque_fragment>
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
	#include <fog_fragment>
	#include <premultiplied_alpha_fragment>
	#include <dithering_fragment>
}`,Gx=`#define TOON
varying vec3 vViewPosition;
#include <common>
#include <batching_pars_vertex>
#include <uv_pars_vertex>
#include <displacementmap_pars_vertex>
#include <color_pars_vertex>
#include <fog_pars_vertex>
#include <normal_pars_vertex>
#include <morphtarget_pars_vertex>
#include <skinning_pars_vertex>
#include <shadowmap_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <clipping_planes_pars_vertex>
void main() {
	#include <uv_vertex>
	#include <color_vertex>
	#include <morphinstance_vertex>
	#include <morphcolor_vertex>
	#include <batching_vertex>
	#include <beginnormal_vertex>
	#include <morphnormal_vertex>
	#include <skinbase_vertex>
	#include <skinnormal_vertex>
	#include <defaultnormal_vertex>
	#include <normal_vertex>
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <skinning_vertex>
	#include <displacementmap_vertex>
	#include <project_vertex>
	#include <logdepthbuf_vertex>
	#include <clipping_planes_vertex>
	vViewPosition = - mvPosition.xyz;
	#include <worldpos_vertex>
	#include <shadowmap_vertex>
	#include <fog_vertex>
}`,Wx=`#define TOON
uniform vec3 diffuse;
uniform vec3 emissive;
uniform float opacity;
#include <common>
#include <dithering_pars_fragment>
#include <color_pars_fragment>
#include <uv_pars_fragment>
#include <map_pars_fragment>
#include <alphamap_pars_fragment>
#include <alphatest_pars_fragment>
#include <alphahash_pars_fragment>
#include <aomap_pars_fragment>
#include <lightmap_pars_fragment>
#include <emissivemap_pars_fragment>
#include <gradientmap_pars_fragment>
#include <fog_pars_fragment>
#include <bsdfs>
#include <lights_pars_begin>
#include <normal_pars_fragment>
#include <lights_toon_pars_fragment>
#include <shadowmap_pars_fragment>
#include <bumpmap_pars_fragment>
#include <normalmap_pars_fragment>
#include <logdepthbuf_pars_fragment>
#include <clipping_planes_pars_fragment>
void main() {
	vec4 diffuseColor = vec4( diffuse, opacity );
	#include <clipping_planes_fragment>
	ReflectedLight reflectedLight = ReflectedLight( vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ) );
	vec3 totalEmissiveRadiance = emissive;
	#include <logdepthbuf_fragment>
	#include <map_fragment>
	#include <color_fragment>
	#include <alphamap_fragment>
	#include <alphatest_fragment>
	#include <alphahash_fragment>
	#include <normal_fragment_begin>
	#include <normal_fragment_maps>
	#include <emissivemap_fragment>
	#include <lights_toon_fragment>
	#include <lights_fragment_begin>
	#include <lights_fragment_maps>
	#include <lights_fragment_end>
	#include <aomap_fragment>
	vec3 outgoingLight = reflectedLight.directDiffuse + reflectedLight.indirectDiffuse + totalEmissiveRadiance;
	#include <opaque_fragment>
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
	#include <fog_fragment>
	#include <premultiplied_alpha_fragment>
	#include <dithering_fragment>
}`,Xx=`uniform float size;
uniform float scale;
#include <common>
#include <color_pars_vertex>
#include <fog_pars_vertex>
#include <morphtarget_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <clipping_planes_pars_vertex>
#ifdef USE_POINTS_UV
	varying vec2 vUv;
	uniform mat3 uvTransform;
#endif
void main() {
	#ifdef USE_POINTS_UV
		vUv = ( uvTransform * vec3( uv, 1 ) ).xy;
	#endif
	#include <color_vertex>
	#include <morphinstance_vertex>
	#include <morphcolor_vertex>
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <project_vertex>
	gl_PointSize = size;
	#ifdef USE_SIZEATTENUATION
		bool isPerspective = isPerspectiveMatrix( projectionMatrix );
		if ( isPerspective ) gl_PointSize *= ( scale / - mvPosition.z );
	#endif
	#include <logdepthbuf_vertex>
	#include <clipping_planes_vertex>
	#include <worldpos_vertex>
	#include <fog_vertex>
}`,Yx=`uniform vec3 diffuse;
uniform float opacity;
#include <common>
#include <color_pars_fragment>
#include <map_particle_pars_fragment>
#include <alphatest_pars_fragment>
#include <alphahash_pars_fragment>
#include <fog_pars_fragment>
#include <logdepthbuf_pars_fragment>
#include <clipping_planes_pars_fragment>
void main() {
	vec4 diffuseColor = vec4( diffuse, opacity );
	#include <clipping_planes_fragment>
	vec3 outgoingLight = vec3( 0.0 );
	#include <logdepthbuf_fragment>
	#include <map_particle_fragment>
	#include <color_fragment>
	#include <alphatest_fragment>
	#include <alphahash_fragment>
	outgoingLight = diffuseColor.rgb;
	#include <opaque_fragment>
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
	#include <fog_fragment>
	#include <premultiplied_alpha_fragment>
}`,qx=`#include <common>
#include <batching_pars_vertex>
#include <fog_pars_vertex>
#include <morphtarget_pars_vertex>
#include <skinning_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <shadowmap_pars_vertex>
void main() {
	#include <batching_vertex>
	#include <beginnormal_vertex>
	#include <morphinstance_vertex>
	#include <morphnormal_vertex>
	#include <skinbase_vertex>
	#include <skinnormal_vertex>
	#include <defaultnormal_vertex>
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <skinning_vertex>
	#include <project_vertex>
	#include <logdepthbuf_vertex>
	#include <worldpos_vertex>
	#include <shadowmap_vertex>
	#include <fog_vertex>
}`,jx=`uniform vec3 color;
uniform float opacity;
#include <common>
#include <fog_pars_fragment>
#include <bsdfs>
#include <lights_pars_begin>
#include <logdepthbuf_pars_fragment>
#include <shadowmap_pars_fragment>
#include <shadowmask_pars_fragment>
void main() {
	#include <logdepthbuf_fragment>
	gl_FragColor = vec4( color, opacity * ( 1.0 - getShadowMask() ) );
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
	#include <fog_fragment>
	#include <premultiplied_alpha_fragment>
}`,Kx=`uniform float rotation;
uniform vec2 center;
#include <common>
#include <uv_pars_vertex>
#include <fog_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <clipping_planes_pars_vertex>
void main() {
	#include <uv_vertex>
	vec4 mvPosition = modelViewMatrix[ 3 ];
	vec2 scale = vec2( length( modelMatrix[ 0 ].xyz ), length( modelMatrix[ 1 ].xyz ) );
	#ifndef USE_SIZEATTENUATION
		bool isPerspective = isPerspectiveMatrix( projectionMatrix );
		if ( isPerspective ) scale *= - mvPosition.z;
	#endif
	vec2 alignedPosition = ( position.xy - ( center - vec2( 0.5 ) ) ) * scale;
	vec2 rotatedPosition;
	rotatedPosition.x = cos( rotation ) * alignedPosition.x - sin( rotation ) * alignedPosition.y;
	rotatedPosition.y = sin( rotation ) * alignedPosition.x + cos( rotation ) * alignedPosition.y;
	mvPosition.xy += rotatedPosition;
	gl_Position = projectionMatrix * mvPosition;
	#include <logdepthbuf_vertex>
	#include <clipping_planes_vertex>
	#include <fog_vertex>
}`,$x=`uniform vec3 diffuse;
uniform float opacity;
#include <common>
#include <uv_pars_fragment>
#include <map_pars_fragment>
#include <alphamap_pars_fragment>
#include <alphatest_pars_fragment>
#include <alphahash_pars_fragment>
#include <fog_pars_fragment>
#include <logdepthbuf_pars_fragment>
#include <clipping_planes_pars_fragment>
void main() {
	vec4 diffuseColor = vec4( diffuse, opacity );
	#include <clipping_planes_fragment>
	vec3 outgoingLight = vec3( 0.0 );
	#include <logdepthbuf_fragment>
	#include <map_fragment>
	#include <alphamap_fragment>
	#include <alphatest_fragment>
	#include <alphahash_fragment>
	outgoingLight = diffuseColor.rgb;
	#include <opaque_fragment>
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
	#include <fog_fragment>
}`,Ke={alphahash_fragment:x_,alphahash_pars_fragment:v_,alphamap_fragment:M_,alphamap_pars_fragment:S_,alphatest_fragment:y_,alphatest_pars_fragment:b_,aomap_fragment:E_,aomap_pars_fragment:T_,batching_pars_vertex:A_,batching_vertex:w_,begin_vertex:C_,beginnormal_vertex:R_,bsdfs:P_,iridescence_fragment:D_,bumpmap_pars_fragment:L_,clipping_planes_fragment:I_,clipping_planes_pars_fragment:U_,clipping_planes_pars_vertex:N_,clipping_planes_vertex:F_,color_fragment:O_,color_pars_fragment:B_,color_pars_vertex:z_,color_vertex:H_,common:V_,cube_uv_reflection_fragment:k_,defaultnormal_vertex:G_,displacementmap_pars_vertex:W_,displacementmap_vertex:X_,emissivemap_fragment:Y_,emissivemap_pars_fragment:q_,colorspace_fragment:j_,colorspace_pars_fragment:K_,envmap_fragment:$_,envmap_common_pars_fragment:Z_,envmap_pars_fragment:J_,envmap_pars_vertex:Q_,envmap_physical_pars_fragment:u0,envmap_vertex:e0,fog_vertex:t0,fog_pars_vertex:n0,fog_fragment:i0,fog_pars_fragment:s0,gradientmap_pars_fragment:r0,lightmap_pars_fragment:a0,lights_lambert_fragment:o0,lights_lambert_pars_fragment:l0,lights_pars_begin:c0,lights_toon_fragment:h0,lights_toon_pars_fragment:f0,lights_phong_fragment:d0,lights_phong_pars_fragment:p0,lights_physical_fragment:m0,lights_physical_pars_fragment:g0,lights_fragment_begin:_0,lights_fragment_maps:x0,lights_fragment_end:v0,logdepthbuf_fragment:M0,logdepthbuf_pars_fragment:S0,logdepthbuf_pars_vertex:y0,logdepthbuf_vertex:b0,map_fragment:E0,map_pars_fragment:T0,map_particle_fragment:A0,map_particle_pars_fragment:w0,metalnessmap_fragment:C0,metalnessmap_pars_fragment:R0,morphinstance_vertex:P0,morphcolor_vertex:D0,morphnormal_vertex:L0,morphtarget_pars_vertex:I0,morphtarget_vertex:U0,normal_fragment_begin:N0,normal_fragment_maps:F0,normal_pars_fragment:O0,normal_pars_vertex:B0,normal_vertex:z0,normalmap_pars_fragment:H0,clearcoat_normal_fragment_begin:V0,clearcoat_normal_fragment_maps:k0,clearcoat_pars_fragment:G0,iridescence_pars_fragment:W0,opaque_fragment:X0,packing:Y0,premultiplied_alpha_fragment:q0,project_vertex:j0,dithering_fragment:K0,dithering_pars_fragment:$0,roughnessmap_fragment:Z0,roughnessmap_pars_fragment:J0,shadowmap_pars_fragment:Q0,shadowmap_pars_vertex:ex,shadowmap_vertex:tx,shadowmask_pars_fragment:nx,skinbase_vertex:ix,skinning_pars_vertex:sx,skinning_vertex:rx,skinnormal_vertex:ax,specularmap_fragment:ox,specularmap_pars_fragment:lx,tonemapping_fragment:cx,tonemapping_pars_fragment:ux,transmission_fragment:hx,transmission_pars_fragment:fx,uv_pars_fragment:dx,uv_pars_vertex:px,uv_vertex:mx,worldpos_vertex:gx,background_vert:_x,background_frag:xx,backgroundCube_vert:vx,backgroundCube_frag:Mx,cube_vert:Sx,cube_frag:yx,depth_vert:bx,depth_frag:Ex,distance_vert:Tx,distance_frag:Ax,equirect_vert:wx,equirect_frag:Cx,linedashed_vert:Rx,linedashed_frag:Px,meshbasic_vert:Dx,meshbasic_frag:Lx,meshlambert_vert:Ix,meshlambert_frag:Ux,meshmatcap_vert:Nx,meshmatcap_frag:Fx,meshnormal_vert:Ox,meshnormal_frag:Bx,meshphong_vert:zx,meshphong_frag:Hx,meshphysical_vert:Vx,meshphysical_frag:kx,meshtoon_vert:Gx,meshtoon_frag:Wx,points_vert:Xx,points_frag:Yx,shadow_vert:qx,shadow_frag:jx,sprite_vert:Kx,sprite_frag:$x},Se={common:{diffuse:{value:new Ze(16777215)},opacity:{value:1},map:{value:null},mapTransform:{value:new qe},alphaMap:{value:null},alphaMapTransform:{value:new qe},alphaTest:{value:0}},specularmap:{specularMap:{value:null},specularMapTransform:{value:new qe}},envmap:{envMap:{value:null},envMapRotation:{value:new qe},flipEnvMap:{value:-1},reflectivity:{value:1},ior:{value:1.5},refractionRatio:{value:.98},dfgLUT:{value:null}},aomap:{aoMap:{value:null},aoMapIntensity:{value:1},aoMapTransform:{value:new qe}},lightmap:{lightMap:{value:null},lightMapIntensity:{value:1},lightMapTransform:{value:new qe}},bumpmap:{bumpMap:{value:null},bumpMapTransform:{value:new qe},bumpScale:{value:1}},normalmap:{normalMap:{value:null},normalMapTransform:{value:new qe},normalScale:{value:new ke(1,1)}},displacementmap:{displacementMap:{value:null},displacementMapTransform:{value:new qe},displacementScale:{value:1},displacementBias:{value:0}},emissivemap:{emissiveMap:{value:null},emissiveMapTransform:{value:new qe}},metalnessmap:{metalnessMap:{value:null},metalnessMapTransform:{value:new qe}},roughnessmap:{roughnessMap:{value:null},roughnessMapTransform:{value:new qe}},gradientmap:{gradientMap:{value:null}},fog:{fogDensity:{value:25e-5},fogNear:{value:1},fogFar:{value:2e3},fogColor:{value:new Ze(16777215)}},lights:{ambientLightColor:{value:[]},lightProbe:{value:[]},directionalLights:{value:[],properties:{direction:{},color:{}}},directionalLightShadows:{value:[],properties:{shadowIntensity:1,shadowBias:{},shadowNormalBias:{},shadowRadius:{},shadowMapSize:{}}},directionalShadowMatrix:{value:[]},spotLights:{value:[],properties:{color:{},position:{},direction:{},distance:{},coneCos:{},penumbraCos:{},decay:{}}},spotLightShadows:{value:[],properties:{shadowIntensity:1,shadowBias:{},shadowNormalBias:{},shadowRadius:{},shadowMapSize:{}}},spotLightMap:{value:[]},spotLightMatrix:{value:[]},pointLights:{value:[],properties:{color:{},position:{},decay:{},distance:{}}},pointLightShadows:{value:[],properties:{shadowIntensity:1,shadowBias:{},shadowNormalBias:{},shadowRadius:{},shadowMapSize:{},shadowCameraNear:{},shadowCameraFar:{}}},pointShadowMatrix:{value:[]},hemisphereLights:{value:[],properties:{direction:{},skyColor:{},groundColor:{}}},rectAreaLights:{value:[],properties:{color:{},position:{},width:{},height:{}}},ltc_1:{value:null},ltc_2:{value:null}},points:{diffuse:{value:new Ze(16777215)},opacity:{value:1},size:{value:1},scale:{value:1},map:{value:null},alphaMap:{value:null},alphaMapTransform:{value:new qe},alphaTest:{value:0},uvTransform:{value:new qe}},sprite:{diffuse:{value:new Ze(16777215)},opacity:{value:1},center:{value:new ke(.5,.5)},rotation:{value:0},map:{value:null},mapTransform:{value:new qe},alphaMap:{value:null},alphaMapTransform:{value:new qe},alphaTest:{value:0}}},Cn={basic:{uniforms:zt([Se.common,Se.specularmap,Se.envmap,Se.aomap,Se.lightmap,Se.fog]),vertexShader:Ke.meshbasic_vert,fragmentShader:Ke.meshbasic_frag},lambert:{uniforms:zt([Se.common,Se.specularmap,Se.envmap,Se.aomap,Se.lightmap,Se.emissivemap,Se.bumpmap,Se.normalmap,Se.displacementmap,Se.fog,Se.lights,{emissive:{value:new Ze(0)},envMapIntensity:{value:1}}]),vertexShader:Ke.meshlambert_vert,fragmentShader:Ke.meshlambert_frag},phong:{uniforms:zt([Se.common,Se.specularmap,Se.envmap,Se.aomap,Se.lightmap,Se.emissivemap,Se.bumpmap,Se.normalmap,Se.displacementmap,Se.fog,Se.lights,{emissive:{value:new Ze(0)},specular:{value:new Ze(1118481)},shininess:{value:30},envMapIntensity:{value:1}}]),vertexShader:Ke.meshphong_vert,fragmentShader:Ke.meshphong_frag},standard:{uniforms:zt([Se.common,Se.envmap,Se.aomap,Se.lightmap,Se.emissivemap,Se.bumpmap,Se.normalmap,Se.displacementmap,Se.roughnessmap,Se.metalnessmap,Se.fog,Se.lights,{emissive:{value:new Ze(0)},roughness:{value:1},metalness:{value:0},envMapIntensity:{value:1}}]),vertexShader:Ke.meshphysical_vert,fragmentShader:Ke.meshphysical_frag},toon:{uniforms:zt([Se.common,Se.aomap,Se.lightmap,Se.emissivemap,Se.bumpmap,Se.normalmap,Se.displacementmap,Se.gradientmap,Se.fog,Se.lights,{emissive:{value:new Ze(0)}}]),vertexShader:Ke.meshtoon_vert,fragmentShader:Ke.meshtoon_frag},matcap:{uniforms:zt([Se.common,Se.bumpmap,Se.normalmap,Se.displacementmap,Se.fog,{matcap:{value:null}}]),vertexShader:Ke.meshmatcap_vert,fragmentShader:Ke.meshmatcap_frag},points:{uniforms:zt([Se.points,Se.fog]),vertexShader:Ke.points_vert,fragmentShader:Ke.points_frag},dashed:{uniforms:zt([Se.common,Se.fog,{scale:{value:1},dashSize:{value:1},totalSize:{value:2}}]),vertexShader:Ke.linedashed_vert,fragmentShader:Ke.linedashed_frag},depth:{uniforms:zt([Se.common,Se.displacementmap]),vertexShader:Ke.depth_vert,fragmentShader:Ke.depth_frag},normal:{uniforms:zt([Se.common,Se.bumpmap,Se.normalmap,Se.displacementmap,{opacity:{value:1}}]),vertexShader:Ke.meshnormal_vert,fragmentShader:Ke.meshnormal_frag},sprite:{uniforms:zt([Se.sprite,Se.fog]),vertexShader:Ke.sprite_vert,fragmentShader:Ke.sprite_frag},background:{uniforms:{uvTransform:{value:new qe},t2D:{value:null},backgroundIntensity:{value:1}},vertexShader:Ke.background_vert,fragmentShader:Ke.background_frag},backgroundCube:{uniforms:{envMap:{value:null},flipEnvMap:{value:-1},backgroundBlurriness:{value:0},backgroundIntensity:{value:1},backgroundRotation:{value:new qe}},vertexShader:Ke.backgroundCube_vert,fragmentShader:Ke.backgroundCube_frag},cube:{uniforms:{tCube:{value:null},tFlip:{value:-1},opacity:{value:1}},vertexShader:Ke.cube_vert,fragmentShader:Ke.cube_frag},equirect:{uniforms:{tEquirect:{value:null}},vertexShader:Ke.equirect_vert,fragmentShader:Ke.equirect_frag},distance:{uniforms:zt([Se.common,Se.displacementmap,{referencePosition:{value:new O},nearDistance:{value:1},farDistance:{value:1e3}}]),vertexShader:Ke.distance_vert,fragmentShader:Ke.distance_frag},shadow:{uniforms:zt([Se.lights,Se.fog,{color:{value:new Ze(0)},opacity:{value:1}}]),vertexShader:Ke.shadow_vert,fragmentShader:Ke.shadow_frag}};Cn.physical={uniforms:zt([Cn.standard.uniforms,{clearcoat:{value:0},clearcoatMap:{value:null},clearcoatMapTransform:{value:new qe},clearcoatNormalMap:{value:null},clearcoatNormalMapTransform:{value:new qe},clearcoatNormalScale:{value:new ke(1,1)},clearcoatRoughness:{value:0},clearcoatRoughnessMap:{value:null},clearcoatRoughnessMapTransform:{value:new qe},dispersion:{value:0},iridescence:{value:0},iridescenceMap:{value:null},iridescenceMapTransform:{value:new qe},iridescenceIOR:{value:1.3},iridescenceThicknessMinimum:{value:100},iridescenceThicknessMaximum:{value:400},iridescenceThicknessMap:{value:null},iridescenceThicknessMapTransform:{value:new qe},sheen:{value:0},sheenColor:{value:new Ze(0)},sheenColorMap:{value:null},sheenColorMapTransform:{value:new qe},sheenRoughness:{value:1},sheenRoughnessMap:{value:null},sheenRoughnessMapTransform:{value:new qe},transmission:{value:0},transmissionMap:{value:null},transmissionMapTransform:{value:new qe},transmissionSamplerSize:{value:new ke},transmissionSamplerMap:{value:null},thickness:{value:0},thicknessMap:{value:null},thicknessMapTransform:{value:new qe},attenuationDistance:{value:0},attenuationColor:{value:new Ze(0)},specularColor:{value:new Ze(1,1,1)},specularColorMap:{value:null},specularColorMapTransform:{value:new qe},specularIntensity:{value:1},specularIntensityMap:{value:null},specularIntensityMapTransform:{value:new qe},anisotropyVector:{value:new ke},anisotropyMap:{value:null},anisotropyMapTransform:{value:new qe}}]),vertexShader:Ke.meshphysical_vert,fragmentShader:Ke.meshphysical_frag};const Kr={r:0,b:0,g:0},Ui=new Bn,Zx=new ht;function Jx(n,e,t,i,s,r){const a=new Ze(0);let o=s===!0?0:1,l,c,u=null,f=0,h=null;function p(y){let A=y.isScene===!0?y.background:null;if(A&&A.isTexture){const b=y.backgroundBlurriness>0;A=e.get(A,b)}return A}function _(y){let A=!1;const b=p(y);b===null?g(a,o):b&&b.isColor&&(g(b,1),A=!0);const P=n.xr.getEnvironmentBlendMode();P==="additive"?t.buffers.color.setClear(0,0,0,1,r):P==="alpha-blend"&&t.buffers.color.setClear(0,0,0,0,r),(n.autoClear||A)&&(t.buffers.depth.setTest(!0),t.buffers.depth.setMask(!0),t.buffers.color.setMask(!0),n.clear(n.autoClearColor,n.autoClearDepth,n.autoClearStencil))}function M(y,A){const b=p(A);b&&(b.isCubeTexture||b.mapping===Ua)?(c===void 0&&(c=new qt(new bi(1,1,1),new zn({name:"BackgroundCubeMaterial",uniforms:Rs(Cn.backgroundCube.uniforms),vertexShader:Cn.backgroundCube.vertexShader,fragmentShader:Cn.backgroundCube.fragmentShader,side:Yt,depthTest:!1,depthWrite:!1,fog:!1,allowOverride:!1})),c.geometry.deleteAttribute("normal"),c.geometry.deleteAttribute("uv"),c.onBeforeRender=function(P,L,I){this.matrixWorld.copyPosition(I.matrixWorld)},Object.defineProperty(c.material,"envMap",{get:function(){return this.uniforms.envMap.value}}),i.update(c)),Ui.copy(A.backgroundRotation),Ui.x*=-1,Ui.y*=-1,Ui.z*=-1,b.isCubeTexture&&b.isRenderTargetTexture===!1&&(Ui.y*=-1,Ui.z*=-1),c.material.uniforms.envMap.value=b,c.material.uniforms.flipEnvMap.value=b.isCubeTexture&&b.isRenderTargetTexture===!1?-1:1,c.material.uniforms.backgroundBlurriness.value=A.backgroundBlurriness,c.material.uniforms.backgroundIntensity.value=A.backgroundIntensity,c.material.uniforms.backgroundRotation.value.setFromMatrix4(Zx.makeRotationFromEuler(Ui)),c.material.toneMapped=et.getTransfer(b.colorSpace)!==at,(u!==b||f!==b.version||h!==n.toneMapping)&&(c.material.needsUpdate=!0,u=b,f=b.version,h=n.toneMapping),c.layers.enableAll(),y.unshift(c,c.geometry,c.material,0,0,null)):b&&b.isTexture&&(l===void 0&&(l=new qt(new vr(2,2),new zn({name:"BackgroundMaterial",uniforms:Rs(Cn.background.uniforms),vertexShader:Cn.background.vertexShader,fragmentShader:Cn.background.fragmentShader,side:Ti,depthTest:!1,depthWrite:!1,fog:!1,allowOverride:!1})),l.geometry.deleteAttribute("normal"),Object.defineProperty(l.material,"map",{get:function(){return this.uniforms.t2D.value}}),i.update(l)),l.material.uniforms.t2D.value=b,l.material.uniforms.backgroundIntensity.value=A.backgroundIntensity,l.material.toneMapped=et.getTransfer(b.colorSpace)!==at,b.matrixAutoUpdate===!0&&b.updateMatrix(),l.material.uniforms.uvTransform.value.copy(b.matrix),(u!==b||f!==b.version||h!==n.toneMapping)&&(l.material.needsUpdate=!0,u=b,f=b.version,h=n.toneMapping),l.layers.enableAll(),y.unshift(l,l.geometry,l.material,0,0,null))}function g(y,A){y.getRGB(Kr,Qf(n)),t.buffers.color.setClear(Kr.r,Kr.g,Kr.b,A,r)}function d(){c!==void 0&&(c.geometry.dispose(),c.material.dispose(),c=void 0),l!==void 0&&(l.geometry.dispose(),l.material.dispose(),l=void 0)}return{getClearColor:function(){return a},setClearColor:function(y,A=1){a.set(y),o=A,g(a,o)},getClearAlpha:function(){return o},setClearAlpha:function(y){o=y,g(a,o)},render:_,addToRenderList:M,dispose:d}}function Qx(n,e){const t=n.getParameter(n.MAX_VERTEX_ATTRIBS),i={},s=h(null);let r=s,a=!1;function o(D,X,Y,$,W){let B=!1;const N=f(D,$,Y,X);r!==N&&(r=N,c(r.object)),B=p(D,$,Y,W),B&&_(D,$,Y,W),W!==null&&e.update(W,n.ELEMENT_ARRAY_BUFFER),(B||a)&&(a=!1,b(D,X,Y,$),W!==null&&n.bindBuffer(n.ELEMENT_ARRAY_BUFFER,e.get(W).buffer))}function l(){return n.createVertexArray()}function c(D){return n.bindVertexArray(D)}function u(D){return n.deleteVertexArray(D)}function f(D,X,Y,$){const W=$.wireframe===!0;let B=i[X.id];B===void 0&&(B={},i[X.id]=B);const N=D.isInstancedMesh===!0?D.id:0;let ie=B[N];ie===void 0&&(ie={},B[N]=ie);let le=ie[Y.id];le===void 0&&(le={},ie[Y.id]=le);let ve=le[W];return ve===void 0&&(ve=h(l()),le[W]=ve),ve}function h(D){const X=[],Y=[],$=[];for(let W=0;W<t;W++)X[W]=0,Y[W]=0,$[W]=0;return{geometry:null,program:null,wireframe:!1,newAttributes:X,enabledAttributes:Y,attributeDivisors:$,object:D,attributes:{},index:null}}function p(D,X,Y,$){const W=r.attributes,B=X.attributes;let N=0;const ie=Y.getAttributes();for(const le in ie)if(ie[le].location>=0){const se=W[le];let oe=B[le];if(oe===void 0&&(le==="instanceMatrix"&&D.instanceMatrix&&(oe=D.instanceMatrix),le==="instanceColor"&&D.instanceColor&&(oe=D.instanceColor)),se===void 0||se.attribute!==oe||oe&&se.data!==oe.data)return!0;N++}return r.attributesNum!==N||r.index!==$}function _(D,X,Y,$){const W={},B=X.attributes;let N=0;const ie=Y.getAttributes();for(const le in ie)if(ie[le].location>=0){let se=B[le];se===void 0&&(le==="instanceMatrix"&&D.instanceMatrix&&(se=D.instanceMatrix),le==="instanceColor"&&D.instanceColor&&(se=D.instanceColor));const oe={};oe.attribute=se,se&&se.data&&(oe.data=se.data),W[le]=oe,N++}r.attributes=W,r.attributesNum=N,r.index=$}function M(){const D=r.newAttributes;for(let X=0,Y=D.length;X<Y;X++)D[X]=0}function g(D){d(D,0)}function d(D,X){const Y=r.newAttributes,$=r.enabledAttributes,W=r.attributeDivisors;Y[D]=1,$[D]===0&&(n.enableVertexAttribArray(D),$[D]=1),W[D]!==X&&(n.vertexAttribDivisor(D,X),W[D]=X)}function y(){const D=r.newAttributes,X=r.enabledAttributes;for(let Y=0,$=X.length;Y<$;Y++)X[Y]!==D[Y]&&(n.disableVertexAttribArray(Y),X[Y]=0)}function A(D,X,Y,$,W,B,N){N===!0?n.vertexAttribIPointer(D,X,Y,W,B):n.vertexAttribPointer(D,X,Y,$,W,B)}function b(D,X,Y,$){M();const W=$.attributes,B=Y.getAttributes(),N=X.defaultAttributeValues;for(const ie in B){const le=B[ie];if(le.location>=0){let ve=W[ie];if(ve===void 0&&(ie==="instanceMatrix"&&D.instanceMatrix&&(ve=D.instanceMatrix),ie==="instanceColor"&&D.instanceColor&&(ve=D.instanceColor)),ve!==void 0){const se=ve.normalized,oe=ve.itemSize,we=e.get(ve);if(we===void 0)continue;const Ge=we.buffer,nt=we.type,te=we.bytesPerElement,pe=nt===n.INT||nt===n.UNSIGNED_INT||ve.gpuType===ic;if(ve.isInterleavedBufferAttribute){const ge=ve.data,He=ge.stride,Fe=ve.offset;if(ge.isInstancedInterleavedBuffer){for(let Be=0;Be<le.locationSize;Be++)d(le.location+Be,ge.meshPerAttribute);D.isInstancedMesh!==!0&&$._maxInstanceCount===void 0&&($._maxInstanceCount=ge.meshPerAttribute*ge.count)}else for(let Be=0;Be<le.locationSize;Be++)g(le.location+Be);n.bindBuffer(n.ARRAY_BUFFER,Ge);for(let Be=0;Be<le.locationSize;Be++)A(le.location+Be,oe/le.locationSize,nt,se,He*te,(Fe+oe/le.locationSize*Be)*te,pe)}else{if(ve.isInstancedBufferAttribute){for(let ge=0;ge<le.locationSize;ge++)d(le.location+ge,ve.meshPerAttribute);D.isInstancedMesh!==!0&&$._maxInstanceCount===void 0&&($._maxInstanceCount=ve.meshPerAttribute*ve.count)}else for(let ge=0;ge<le.locationSize;ge++)g(le.location+ge);n.bindBuffer(n.ARRAY_BUFFER,Ge);for(let ge=0;ge<le.locationSize;ge++)A(le.location+ge,oe/le.locationSize,nt,se,oe*te,oe/le.locationSize*ge*te,pe)}}else if(N!==void 0){const se=N[ie];if(se!==void 0)switch(se.length){case 2:n.vertexAttrib2fv(le.location,se);break;case 3:n.vertexAttrib3fv(le.location,se);break;case 4:n.vertexAttrib4fv(le.location,se);break;default:n.vertexAttrib1fv(le.location,se)}}}}y()}function P(){T();for(const D in i){const X=i[D];for(const Y in X){const $=X[Y];for(const W in $){const B=$[W];for(const N in B)u(B[N].object),delete B[N];delete $[W]}}delete i[D]}}function L(D){if(i[D.id]===void 0)return;const X=i[D.id];for(const Y in X){const $=X[Y];for(const W in $){const B=$[W];for(const N in B)u(B[N].object),delete B[N];delete $[W]}}delete i[D.id]}function I(D){for(const X in i){const Y=i[X];for(const $ in Y){const W=Y[$];if(W[D.id]===void 0)continue;const B=W[D.id];for(const N in B)u(B[N].object),delete B[N];delete W[D.id]}}}function x(D){for(const X in i){const Y=i[X],$=D.isInstancedMesh===!0?D.id:0,W=Y[$];if(W!==void 0){for(const B in W){const N=W[B];for(const ie in N)u(N[ie].object),delete N[ie];delete W[B]}delete Y[$],Object.keys(Y).length===0&&delete i[X]}}}function T(){Q(),a=!0,r!==s&&(r=s,c(r.object))}function Q(){s.geometry=null,s.program=null,s.wireframe=!1}return{setup:o,reset:T,resetDefaultState:Q,dispose:P,releaseStatesOfGeometry:L,releaseStatesOfObject:x,releaseStatesOfProgram:I,initAttributes:M,enableAttribute:g,disableUnusedAttributes:y}}function ev(n,e,t){let i;function s(c){i=c}function r(c,u){n.drawArrays(i,c,u),t.update(u,i,1)}function a(c,u,f){f!==0&&(n.drawArraysInstanced(i,c,u,f),t.update(u,i,f))}function o(c,u,f){if(f===0)return;e.get("WEBGL_multi_draw").multiDrawArraysWEBGL(i,c,0,u,0,f);let p=0;for(let _=0;_<f;_++)p+=u[_];t.update(p,i,1)}function l(c,u,f,h){if(f===0)return;const p=e.get("WEBGL_multi_draw");if(p===null)for(let _=0;_<c.length;_++)a(c[_],u[_],h[_]);else{p.multiDrawArraysInstancedWEBGL(i,c,0,u,0,h,0,f);let _=0;for(let M=0;M<f;M++)_+=u[M]*h[M];t.update(_,i,1)}}this.setMode=s,this.render=r,this.renderInstances=a,this.renderMultiDraw=o,this.renderMultiDrawInstances=l}function tv(n,e,t,i){let s;function r(){if(s!==void 0)return s;if(e.has("EXT_texture_filter_anisotropic")===!0){const I=e.get("EXT_texture_filter_anisotropic");s=n.getParameter(I.MAX_TEXTURE_MAX_ANISOTROPY_EXT)}else s=0;return s}function a(I){return!(I!==mn&&i.convert(I)!==n.getParameter(n.IMPLEMENTATION_COLOR_READ_FORMAT))}function o(I){const x=I===ri&&(e.has("EXT_color_buffer_half_float")||e.has("EXT_color_buffer_float"));return!(I!==Jt&&i.convert(I)!==n.getParameter(n.IMPLEMENTATION_COLOR_READ_TYPE)&&I!==pn&&!x)}function l(I){if(I==="highp"){if(n.getShaderPrecisionFormat(n.VERTEX_SHADER,n.HIGH_FLOAT).precision>0&&n.getShaderPrecisionFormat(n.FRAGMENT_SHADER,n.HIGH_FLOAT).precision>0)return"highp";I="mediump"}return I==="mediump"&&n.getShaderPrecisionFormat(n.VERTEX_SHADER,n.MEDIUM_FLOAT).precision>0&&n.getShaderPrecisionFormat(n.FRAGMENT_SHADER,n.MEDIUM_FLOAT).precision>0?"mediump":"lowp"}let c=t.precision!==void 0?t.precision:"highp";const u=l(c);u!==c&&(Oe("WebGLRenderer:",c,"not supported, using",u,"instead."),c=u);const f=t.logarithmicDepthBuffer===!0,h=t.reversedDepthBuffer===!0&&e.has("EXT_clip_control"),p=n.getParameter(n.MAX_TEXTURE_IMAGE_UNITS),_=n.getParameter(n.MAX_VERTEX_TEXTURE_IMAGE_UNITS),M=n.getParameter(n.MAX_TEXTURE_SIZE),g=n.getParameter(n.MAX_CUBE_MAP_TEXTURE_SIZE),d=n.getParameter(n.MAX_VERTEX_ATTRIBS),y=n.getParameter(n.MAX_VERTEX_UNIFORM_VECTORS),A=n.getParameter(n.MAX_VARYING_VECTORS),b=n.getParameter(n.MAX_FRAGMENT_UNIFORM_VECTORS),P=n.getParameter(n.MAX_SAMPLES),L=n.getParameter(n.SAMPLES);return{isWebGL2:!0,getMaxAnisotropy:r,getMaxPrecision:l,textureFormatReadable:a,textureTypeReadable:o,precision:c,logarithmicDepthBuffer:f,reversedDepthBuffer:h,maxTextures:p,maxVertexTextures:_,maxTextureSize:M,maxCubemapSize:g,maxAttributes:d,maxVertexUniforms:y,maxVaryings:A,maxFragmentUniforms:b,maxSamples:P,samples:L}}function nv(n){const e=this;let t=null,i=0,s=!1,r=!1;const a=new vi,o=new qe,l={value:null,needsUpdate:!1};this.uniform=l,this.numPlanes=0,this.numIntersection=0,this.init=function(f,h){const p=f.length!==0||h||i!==0||s;return s=h,i=f.length,p},this.beginShadows=function(){r=!0,u(null)},this.endShadows=function(){r=!1},this.setGlobalState=function(f,h){t=u(f,h,0)},this.setState=function(f,h,p){const _=f.clippingPlanes,M=f.clipIntersection,g=f.clipShadows,d=n.get(f);if(!s||_===null||_.length===0||r&&!g)r?u(null):c();else{const y=r?0:i,A=y*4;let b=d.clippingState||null;l.value=b,b=u(_,h,A,p);for(let P=0;P!==A;++P)b[P]=t[P];d.clippingState=b,this.numIntersection=M?this.numPlanes:0,this.numPlanes+=y}};function c(){l.value!==t&&(l.value=t,l.needsUpdate=i>0),e.numPlanes=i,e.numIntersection=0}function u(f,h,p,_){const M=f!==null?f.length:0;let g=null;if(M!==0){if(g=l.value,_!==!0||g===null){const d=p+M*4,y=h.matrixWorldInverse;o.getNormalMatrix(y),(g===null||g.length<d)&&(g=new Float32Array(d));for(let A=0,b=p;A!==M;++A,b+=4)a.copy(f[A]).applyMatrix4(y,o),a.normal.toArray(g,b),g[b+3]=a.constant}l.value=g,l.needsUpdate=!0}return e.numPlanes=M,e.numIntersection=0,g}}const yi=4,Xu=[.125,.215,.35,.446,.526,.582],zi=20,iv=256,Ws=new _c,Yu=new Ze;let Co=null,Ro=0,Po=0,Do=!1;const sv=new O;class qu{constructor(e){this._renderer=e,this._pingPongRenderTarget=null,this._lodMax=0,this._cubeSize=0,this._sizeLods=[],this._sigmas=[],this._lodMeshes=[],this._backgroundBox=null,this._cubemapMaterial=null,this._equirectMaterial=null,this._blurMaterial=null,this._ggxMaterial=null}fromScene(e,t=0,i=.1,s=100,r={}){const{size:a=256,position:o=sv}=r;Co=this._renderer.getRenderTarget(),Ro=this._renderer.getActiveCubeFace(),Po=this._renderer.getActiveMipmapLevel(),Do=this._renderer.xr.enabled,this._renderer.xr.enabled=!1,this._setSize(a);const l=this._allocateTargets();return l.depthBuffer=!0,this._sceneToCubeUV(e,i,s,l,o),t>0&&this._blur(l,0,0,t),this._applyPMREM(l),this._cleanup(l),l}fromEquirectangular(e,t=null){return this._fromTexture(e,t)}fromCubemap(e,t=null){return this._fromTexture(e,t)}compileCubemapShader(){this._cubemapMaterial===null&&(this._cubemapMaterial=$u(),this._compileMaterial(this._cubemapMaterial))}compileEquirectangularShader(){this._equirectMaterial===null&&(this._equirectMaterial=Ku(),this._compileMaterial(this._equirectMaterial))}dispose(){this._dispose(),this._cubemapMaterial!==null&&this._cubemapMaterial.dispose(),this._equirectMaterial!==null&&this._equirectMaterial.dispose(),this._backgroundBox!==null&&(this._backgroundBox.geometry.dispose(),this._backgroundBox.material.dispose())}_setSize(e){this._lodMax=Math.floor(Math.log2(e)),this._cubeSize=Math.pow(2,this._lodMax)}_dispose(){this._blurMaterial!==null&&this._blurMaterial.dispose(),this._ggxMaterial!==null&&this._ggxMaterial.dispose(),this._pingPongRenderTarget!==null&&this._pingPongRenderTarget.dispose();for(let e=0;e<this._lodMeshes.length;e++)this._lodMeshes[e].geometry.dispose()}_cleanup(e){this._renderer.setRenderTarget(Co,Ro,Po),this._renderer.xr.enabled=Do,e.scissorTest=!1,fs(e,0,0,e.width,e.height)}_fromTexture(e,t){e.mapping===Wi||e.mapping===As?this._setSize(e.image.length===0?16:e.image[0].width||e.image[0].image.width):this._setSize(e.image.width/4),Co=this._renderer.getRenderTarget(),Ro=this._renderer.getActiveCubeFace(),Po=this._renderer.getActiveMipmapLevel(),Do=this._renderer.xr.enabled,this._renderer.xr.enabled=!1;const i=t||this._allocateTargets();return this._textureToCubeUV(e,i),this._applyPMREM(i),this._cleanup(i),i}_allocateTargets(){const e=3*Math.max(this._cubeSize,112),t=4*this._cubeSize,i={magFilter:Ut,minFilter:Ut,generateMipmaps:!1,type:ri,format:mn,colorSpace:Cs,depthBuffer:!1},s=ju(e,t,i);if(this._pingPongRenderTarget===null||this._pingPongRenderTarget.width!==e||this._pingPongRenderTarget.height!==t){this._pingPongRenderTarget!==null&&this._dispose(),this._pingPongRenderTarget=ju(e,t,i);const{_lodMax:r}=this;({lodMeshes:this._lodMeshes,sizeLods:this._sizeLods,sigmas:this._sigmas}=rv(r)),this._blurMaterial=ov(r,e,t),this._ggxMaterial=av(r,e,t)}return s}_compileMaterial(e){const t=new qt(new Mn,e);this._renderer.compile(t,Ws)}_sceneToCubeUV(e,t,i,s,r){const l=new an(90,1,t,i),c=[1,-1,1,1,1,1],u=[1,1,1,-1,-1,-1],f=this._renderer,h=f.autoClear,p=f.toneMapping;f.getClearColor(Yu),f.toneMapping=In,f.autoClear=!1,f.state.buffers.depth.getReversed()&&(f.setRenderTarget(s),f.clearDepth(),f.setRenderTarget(null)),this._backgroundBox===null&&(this._backgroundBox=new qt(new bi,new jf({name:"PMREM.Background",side:Yt,depthWrite:!1,depthTest:!1})));const M=this._backgroundBox,g=M.material;let d=!1;const y=e.background;y?y.isColor&&(g.color.copy(y),e.background=null,d=!0):(g.color.copy(Yu),d=!0);for(let A=0;A<6;A++){const b=A%3;b===0?(l.up.set(0,c[A],0),l.position.set(r.x,r.y,r.z),l.lookAt(r.x+u[A],r.y,r.z)):b===1?(l.up.set(0,0,c[A]),l.position.set(r.x,r.y,r.z),l.lookAt(r.x,r.y+u[A],r.z)):(l.up.set(0,c[A],0),l.position.set(r.x,r.y,r.z),l.lookAt(r.x,r.y,r.z+u[A]));const P=this._cubeSize;fs(s,b*P,A>2?P:0,P,P),f.setRenderTarget(s),d&&f.render(M,l),f.render(e,l)}f.toneMapping=p,f.autoClear=h,e.background=y}_textureToCubeUV(e,t){const i=this._renderer,s=e.mapping===Wi||e.mapping===As;s?(this._cubemapMaterial===null&&(this._cubemapMaterial=$u()),this._cubemapMaterial.uniforms.flipEnvMap.value=e.isRenderTargetTexture===!1?-1:1):this._equirectMaterial===null&&(this._equirectMaterial=Ku());const r=s?this._cubemapMaterial:this._equirectMaterial,a=this._lodMeshes[0];a.material=r;const o=r.uniforms;o.envMap.value=e;const l=this._cubeSize;fs(t,0,0,3*l,2*l),i.setRenderTarget(t),i.render(a,Ws)}_applyPMREM(e){const t=this._renderer,i=t.autoClear;t.autoClear=!1;const s=this._lodMeshes.length;for(let r=1;r<s;r++)this._applyGGXFilter(e,r-1,r);t.autoClear=i}_applyGGXFilter(e,t,i){const s=this._renderer,r=this._pingPongRenderTarget,a=this._ggxMaterial,o=this._lodMeshes[i];o.material=a;const l=a.uniforms,c=i/(this._lodMeshes.length-1),u=t/(this._lodMeshes.length-1),f=Math.sqrt(c*c-u*u),h=0+c*1.25,p=f*h,{_lodMax:_}=this,M=this._sizeLods[i],g=3*M*(i>_-yi?i-_+yi:0),d=4*(this._cubeSize-M);l.envMap.value=e.texture,l.roughness.value=p,l.mipInt.value=_-t,fs(r,g,d,3*M,2*M),s.setRenderTarget(r),s.render(o,Ws),l.envMap.value=r.texture,l.roughness.value=0,l.mipInt.value=_-i,fs(e,g,d,3*M,2*M),s.setRenderTarget(e),s.render(o,Ws)}_blur(e,t,i,s,r){const a=this._pingPongRenderTarget;this._halfBlur(e,a,t,i,s,"latitudinal",r),this._halfBlur(a,e,i,i,s,"longitudinal",r)}_halfBlur(e,t,i,s,r,a,o){const l=this._renderer,c=this._blurMaterial;a!=="latitudinal"&&a!=="longitudinal"&&Qe("blur direction must be either latitudinal or longitudinal!");const u=3,f=this._lodMeshes[s];f.material=c;const h=c.uniforms,p=this._sizeLods[i]-1,_=isFinite(r)?Math.PI/(2*p):2*Math.PI/(2*zi-1),M=r/_,g=isFinite(r)?1+Math.floor(u*M):zi;g>zi&&Oe(`sigmaRadians, ${r}, is too large and will clip, as it requested ${g} samples when the maximum is set to ${zi}`);const d=[];let y=0;for(let I=0;I<zi;++I){const x=I/M,T=Math.exp(-x*x/2);d.push(T),I===0?y+=T:I<g&&(y+=2*T)}for(let I=0;I<d.length;I++)d[I]=d[I]/y;h.envMap.value=e.texture,h.samples.value=g,h.weights.value=d,h.latitudinal.value=a==="latitudinal",o&&(h.poleAxis.value=o);const{_lodMax:A}=this;h.dTheta.value=_,h.mipInt.value=A-i;const b=this._sizeLods[s],P=3*b*(s>A-yi?s-A+yi:0),L=4*(this._cubeSize-b);fs(t,P,L,3*b,2*b),l.setRenderTarget(t),l.render(f,Ws)}}function rv(n){const e=[],t=[],i=[];let s=n;const r=n-yi+1+Xu.length;for(let a=0;a<r;a++){const o=Math.pow(2,s);e.push(o);let l=1/o;a>n-yi?l=Xu[a-n+yi-1]:a===0&&(l=0),t.push(l);const c=1/(o-2),u=-c,f=1+c,h=[u,u,f,u,f,f,u,u,f,f,u,f],p=6,_=6,M=3,g=2,d=1,y=new Float32Array(M*_*p),A=new Float32Array(g*_*p),b=new Float32Array(d*_*p);for(let L=0;L<p;L++){const I=L%3*2/3-1,x=L>2?0:-1,T=[I,x,0,I+2/3,x,0,I+2/3,x+1,0,I,x,0,I+2/3,x+1,0,I,x+1,0];y.set(T,M*_*L),A.set(h,g*_*L);const Q=[L,L,L,L,L,L];b.set(Q,d*_*L)}const P=new Mn;P.setAttribute("position",new xn(y,M)),P.setAttribute("uv",new xn(A,g)),P.setAttribute("faceIndex",new xn(b,d)),i.push(new qt(P,null)),s>yi&&s--}return{lodMeshes:i,sizeLods:e,sigmas:t}}function ju(n,e,t){const i=new Un(n,e,t);return i.texture.mapping=Ua,i.texture.name="PMREM.cubeUv",i.scissorTest=!0,i}function fs(n,e,t,i,s){n.viewport.set(e,t,i,s),n.scissor.set(e,t,i,s)}function av(n,e,t){return new zn({name:"PMREMGGXConvolution",defines:{GGX_SAMPLES:iv,CUBEUV_TEXEL_WIDTH:1/e,CUBEUV_TEXEL_HEIGHT:1/t,CUBEUV_MAX_MIP:`${n}.0`},uniforms:{envMap:{value:null},roughness:{value:0},mipInt:{value:0}},vertexShader:Fa(),fragmentShader:`

			precision highp float;
			precision highp int;

			varying vec3 vOutputDirection;

			uniform sampler2D envMap;
			uniform float roughness;
			uniform float mipInt;

			#define ENVMAP_TYPE_CUBE_UV
			#include <cube_uv_reflection_fragment>

			#define PI 3.14159265359

			// Van der Corput radical inverse
			float radicalInverse_VdC(uint bits) {
				bits = (bits << 16u) | (bits >> 16u);
				bits = ((bits & 0x55555555u) << 1u) | ((bits & 0xAAAAAAAAu) >> 1u);
				bits = ((bits & 0x33333333u) << 2u) | ((bits & 0xCCCCCCCCu) >> 2u);
				bits = ((bits & 0x0F0F0F0Fu) << 4u) | ((bits & 0xF0F0F0F0u) >> 4u);
				bits = ((bits & 0x00FF00FFu) << 8u) | ((bits & 0xFF00FF00u) >> 8u);
				return float(bits) * 2.3283064365386963e-10; // / 0x100000000
			}

			// Hammersley sequence
			vec2 hammersley(uint i, uint N) {
				return vec2(float(i) / float(N), radicalInverse_VdC(i));
			}

			// GGX VNDF importance sampling (Eric Heitz 2018)
			// "Sampling the GGX Distribution of Visible Normals"
			// https://jcgt.org/published/0007/04/01/
			vec3 importanceSampleGGX_VNDF(vec2 Xi, vec3 V, float roughness) {
				float alpha = roughness * roughness;

				// Section 4.1: Orthonormal basis
				vec3 T1 = vec3(1.0, 0.0, 0.0);
				vec3 T2 = cross(V, T1);

				// Section 4.2: Parameterization of projected area
				float r = sqrt(Xi.x);
				float phi = 2.0 * PI * Xi.y;
				float t1 = r * cos(phi);
				float t2 = r * sin(phi);
				float s = 0.5 * (1.0 + V.z);
				t2 = (1.0 - s) * sqrt(1.0 - t1 * t1) + s * t2;

				// Section 4.3: Reprojection onto hemisphere
				vec3 Nh = t1 * T1 + t2 * T2 + sqrt(max(0.0, 1.0 - t1 * t1 - t2 * t2)) * V;

				// Section 3.4: Transform back to ellipsoid configuration
				return normalize(vec3(alpha * Nh.x, alpha * Nh.y, max(0.0, Nh.z)));
			}

			void main() {
				vec3 N = normalize(vOutputDirection);
				vec3 V = N; // Assume view direction equals normal for pre-filtering

				vec3 prefilteredColor = vec3(0.0);
				float totalWeight = 0.0;

				// For very low roughness, just sample the environment directly
				if (roughness < 0.001) {
					gl_FragColor = vec4(bilinearCubeUV(envMap, N, mipInt), 1.0);
					return;
				}

				// Tangent space basis for VNDF sampling
				vec3 up = abs(N.z) < 0.999 ? vec3(0.0, 0.0, 1.0) : vec3(1.0, 0.0, 0.0);
				vec3 tangent = normalize(cross(up, N));
				vec3 bitangent = cross(N, tangent);

				for(uint i = 0u; i < uint(GGX_SAMPLES); i++) {
					vec2 Xi = hammersley(i, uint(GGX_SAMPLES));

					// For PMREM, V = N, so in tangent space V is always (0, 0, 1)
					vec3 H_tangent = importanceSampleGGX_VNDF(Xi, vec3(0.0, 0.0, 1.0), roughness);

					// Transform H back to world space
					vec3 H = normalize(tangent * H_tangent.x + bitangent * H_tangent.y + N * H_tangent.z);
					vec3 L = normalize(2.0 * dot(V, H) * H - V);

					float NdotL = max(dot(N, L), 0.0);

					if(NdotL > 0.0) {
						// Sample environment at fixed mip level
						// VNDF importance sampling handles the distribution filtering
						vec3 sampleColor = bilinearCubeUV(envMap, L, mipInt);

						// Weight by NdotL for the split-sum approximation
						// VNDF PDF naturally accounts for the visible microfacet distribution
						prefilteredColor += sampleColor * NdotL;
						totalWeight += NdotL;
					}
				}

				if (totalWeight > 0.0) {
					prefilteredColor = prefilteredColor / totalWeight;
				}

				gl_FragColor = vec4(prefilteredColor, 1.0);
			}
		`,blending:ei,depthTest:!1,depthWrite:!1})}function ov(n,e,t){const i=new Float32Array(zi),s=new O(0,1,0);return new zn({name:"SphericalGaussianBlur",defines:{n:zi,CUBEUV_TEXEL_WIDTH:1/e,CUBEUV_TEXEL_HEIGHT:1/t,CUBEUV_MAX_MIP:`${n}.0`},uniforms:{envMap:{value:null},samples:{value:1},weights:{value:i},latitudinal:{value:!1},dTheta:{value:0},mipInt:{value:0},poleAxis:{value:s}},vertexShader:Fa(),fragmentShader:`

			precision mediump float;
			precision mediump int;

			varying vec3 vOutputDirection;

			uniform sampler2D envMap;
			uniform int samples;
			uniform float weights[ n ];
			uniform bool latitudinal;
			uniform float dTheta;
			uniform float mipInt;
			uniform vec3 poleAxis;

			#define ENVMAP_TYPE_CUBE_UV
			#include <cube_uv_reflection_fragment>

			vec3 getSample( float theta, vec3 axis ) {

				float cosTheta = cos( theta );
				// Rodrigues' axis-angle rotation
				vec3 sampleDirection = vOutputDirection * cosTheta
					+ cross( axis, vOutputDirection ) * sin( theta )
					+ axis * dot( axis, vOutputDirection ) * ( 1.0 - cosTheta );

				return bilinearCubeUV( envMap, sampleDirection, mipInt );

			}

			void main() {

				vec3 axis = latitudinal ? poleAxis : cross( poleAxis, vOutputDirection );

				if ( all( equal( axis, vec3( 0.0 ) ) ) ) {

					axis = vec3( vOutputDirection.z, 0.0, - vOutputDirection.x );

				}

				axis = normalize( axis );

				gl_FragColor = vec4( 0.0, 0.0, 0.0, 1.0 );
				gl_FragColor.rgb += weights[ 0 ] * getSample( 0.0, axis );

				for ( int i = 1; i < n; i++ ) {

					if ( i >= samples ) {

						break;

					}

					float theta = dTheta * float( i );
					gl_FragColor.rgb += weights[ i ] * getSample( -1.0 * theta, axis );
					gl_FragColor.rgb += weights[ i ] * getSample( theta, axis );

				}

			}
		`,blending:ei,depthTest:!1,depthWrite:!1})}function Ku(){return new zn({name:"EquirectangularToCubeUV",uniforms:{envMap:{value:null}},vertexShader:Fa(),fragmentShader:`

			precision mediump float;
			precision mediump int;

			varying vec3 vOutputDirection;

			uniform sampler2D envMap;

			#include <common>

			void main() {

				vec3 outputDirection = normalize( vOutputDirection );
				vec2 uv = equirectUv( outputDirection );

				gl_FragColor = vec4( texture2D ( envMap, uv ).rgb, 1.0 );

			}
		`,blending:ei,depthTest:!1,depthWrite:!1})}function $u(){return new zn({name:"CubemapToCubeUV",uniforms:{envMap:{value:null},flipEnvMap:{value:-1}},vertexShader:Fa(),fragmentShader:`

			precision mediump float;
			precision mediump int;

			uniform float flipEnvMap;

			varying vec3 vOutputDirection;

			uniform samplerCube envMap;

			void main() {

				gl_FragColor = textureCube( envMap, vec3( flipEnvMap * vOutputDirection.x, vOutputDirection.yz ) );

			}
		`,blending:ei,depthTest:!1,depthWrite:!1})}function Fa(){return`

		precision mediump float;
		precision mediump int;

		attribute float faceIndex;

		varying vec3 vOutputDirection;

		// RH coordinate system; PMREM face-indexing convention
		vec3 getDirection( vec2 uv, float face ) {

			uv = 2.0 * uv - 1.0;

			vec3 direction = vec3( uv, 1.0 );

			if ( face == 0.0 ) {

				direction = direction.zyx; // ( 1, v, u ) pos x

			} else if ( face == 1.0 ) {

				direction = direction.xzy;
				direction.xz *= -1.0; // ( -u, 1, -v ) pos y

			} else if ( face == 2.0 ) {

				direction.x *= -1.0; // ( -u, v, 1 ) pos z

			} else if ( face == 3.0 ) {

				direction = direction.zyx;
				direction.xz *= -1.0; // ( -1, v, -u ) neg x

			} else if ( face == 4.0 ) {

				direction = direction.xzy;
				direction.xy *= -1.0; // ( -u, -1, v ) neg y

			} else if ( face == 5.0 ) {

				direction.z *= -1.0; // ( u, v, -1 ) neg z

			}

			return direction;

		}

		void main() {

			vOutputDirection = getDirection( uv, faceIndex );
			gl_Position = vec4( position, 1.0 );

		}
	`}class nd extends Un{constructor(e=1,t={}){super(e,e,t),this.isWebGLCubeRenderTarget=!0;const i={width:e,height:e,depth:1},s=[i,i,i,i,i,i];this.texture=new Zf(s),this._setTextureOptions(t),this.texture.isRenderTargetTexture=!0}fromEquirectangularTexture(e,t){this.texture.type=t.type,this.texture.colorSpace=t.colorSpace,this.texture.generateMipmaps=t.generateMipmaps,this.texture.minFilter=t.minFilter,this.texture.magFilter=t.magFilter;const i={uniforms:{tEquirect:{value:null}},vertexShader:`

				varying vec3 vWorldDirection;

				vec3 transformDirection( in vec3 dir, in mat4 matrix ) {

					return normalize( ( matrix * vec4( dir, 0.0 ) ).xyz );

				}

				void main() {

					vWorldDirection = transformDirection( position, modelMatrix );

					#include <begin_vertex>
					#include <project_vertex>

				}
			`,fragmentShader:`

				uniform sampler2D tEquirect;

				varying vec3 vWorldDirection;

				#include <common>

				void main() {

					vec3 direction = normalize( vWorldDirection );

					vec2 sampleUV = equirectUv( direction );

					gl_FragColor = texture2D( tEquirect, sampleUV );

				}
			`},s=new bi(5,5,5),r=new zn({name:"CubemapFromEquirect",uniforms:Rs(i.uniforms),vertexShader:i.vertexShader,fragmentShader:i.fragmentShader,side:Yt,blending:ei});r.uniforms.tEquirect.value=t;const a=new qt(s,r),o=t.minFilter;return t.minFilter===Hi&&(t.minFilter=Ut),new c_(1,10,this).update(e,a),t.minFilter=o,a.geometry.dispose(),a.material.dispose(),this}clear(e,t=!0,i=!0,s=!0){const r=e.getRenderTarget();for(let a=0;a<6;a++)e.setRenderTarget(this,a),e.clear(t,i,s);e.setRenderTarget(r)}}function lv(n){let e=new WeakMap,t=new WeakMap,i=null;function s(h,p=!1){return h==null?null:p?a(h):r(h)}function r(h){if(h&&h.isTexture){const p=h.mapping;if(p===Qa||p===eo)if(e.has(h)){const _=e.get(h).texture;return o(_,h.mapping)}else{const _=h.image;if(_&&_.height>0){const M=new nd(_.height);return M.fromEquirectangularTexture(n,h),e.set(h,M),h.addEventListener("dispose",c),o(M.texture,h.mapping)}else return null}}return h}function a(h){if(h&&h.isTexture){const p=h.mapping,_=p===Qa||p===eo,M=p===Wi||p===As;if(_||M){let g=t.get(h);const d=g!==void 0?g.texture.pmremVersion:0;if(h.isRenderTargetTexture&&h.pmremVersion!==d)return i===null&&(i=new qu(n)),g=_?i.fromEquirectangular(h,g):i.fromCubemap(h,g),g.texture.pmremVersion=h.pmremVersion,t.set(h,g),g.texture;if(g!==void 0)return g.texture;{const y=h.image;return _&&y&&y.height>0||M&&y&&l(y)?(i===null&&(i=new qu(n)),g=_?i.fromEquirectangular(h):i.fromCubemap(h),g.texture.pmremVersion=h.pmremVersion,t.set(h,g),h.addEventListener("dispose",u),g.texture):null}}}return h}function o(h,p){return p===Qa?h.mapping=Wi:p===eo&&(h.mapping=As),h}function l(h){let p=0;const _=6;for(let M=0;M<_;M++)h[M]!==void 0&&p++;return p===_}function c(h){const p=h.target;p.removeEventListener("dispose",c);const _=e.get(p);_!==void 0&&(e.delete(p),_.dispose())}function u(h){const p=h.target;p.removeEventListener("dispose",u);const _=t.get(p);_!==void 0&&(t.delete(p),_.dispose())}function f(){e=new WeakMap,t=new WeakMap,i!==null&&(i.dispose(),i=null)}return{get:s,dispose:f}}function cv(n){const e={};function t(i){if(e[i]!==void 0)return e[i];const s=n.getExtension(i);return e[i]=s,s}return{has:function(i){return t(i)!==null},init:function(){t("EXT_color_buffer_float"),t("WEBGL_clip_cull_distance"),t("OES_texture_float_linear"),t("EXT_color_buffer_half_float"),t("WEBGL_multisampled_render_to_texture"),t("WEBGL_render_shared_exponent")},get:function(i){const s=t(i);return s===null&&Sa("WebGLRenderer: "+i+" extension not supported."),s}}}function uv(n,e,t,i){const s={},r=new WeakMap;function a(f){const h=f.target;h.index!==null&&e.remove(h.index);for(const _ in h.attributes)e.remove(h.attributes[_]);h.removeEventListener("dispose",a),delete s[h.id];const p=r.get(h);p&&(e.remove(p),r.delete(h)),i.releaseStatesOfGeometry(h),h.isInstancedBufferGeometry===!0&&delete h._maxInstanceCount,t.memory.geometries--}function o(f,h){return s[h.id]===!0||(h.addEventListener("dispose",a),s[h.id]=!0,t.memory.geometries++),h}function l(f){const h=f.attributes;for(const p in h)e.update(h[p],n.ARRAY_BUFFER)}function c(f){const h=[],p=f.index,_=f.attributes.position;let M=0;if(_===void 0)return;if(p!==null){const y=p.array;M=p.version;for(let A=0,b=y.length;A<b;A+=3){const P=y[A+0],L=y[A+1],I=y[A+2];h.push(P,L,L,I,I,P)}}else{const y=_.array;M=_.version;for(let A=0,b=y.length/3-1;A<b;A+=3){const P=A+0,L=A+1,I=A+2;h.push(P,L,L,I,I,P)}}const g=new(_.count>=65535?qf:Yf)(h,1);g.version=M;const d=r.get(f);d&&e.remove(d),r.set(f,g)}function u(f){const h=r.get(f);if(h){const p=f.index;p!==null&&h.version<p.version&&c(f)}else c(f);return r.get(f)}return{get:o,update:l,getWireframeAttribute:u}}function hv(n,e,t){let i;function s(h){i=h}let r,a;function o(h){r=h.type,a=h.bytesPerElement}function l(h,p){n.drawElements(i,p,r,h*a),t.update(p,i,1)}function c(h,p,_){_!==0&&(n.drawElementsInstanced(i,p,r,h*a,_),t.update(p,i,_))}function u(h,p,_){if(_===0)return;e.get("WEBGL_multi_draw").multiDrawElementsWEBGL(i,p,0,r,h,0,_);let g=0;for(let d=0;d<_;d++)g+=p[d];t.update(g,i,1)}function f(h,p,_,M){if(_===0)return;const g=e.get("WEBGL_multi_draw");if(g===null)for(let d=0;d<h.length;d++)c(h[d]/a,p[d],M[d]);else{g.multiDrawElementsInstancedWEBGL(i,p,0,r,h,0,M,0,_);let d=0;for(let y=0;y<_;y++)d+=p[y]*M[y];t.update(d,i,1)}}this.setMode=s,this.setIndex=o,this.render=l,this.renderInstances=c,this.renderMultiDraw=u,this.renderMultiDrawInstances=f}function fv(n){const e={geometries:0,textures:0},t={frame:0,calls:0,triangles:0,points:0,lines:0};function i(r,a,o){switch(t.calls++,a){case n.TRIANGLES:t.triangles+=o*(r/3);break;case n.LINES:t.lines+=o*(r/2);break;case n.LINE_STRIP:t.lines+=o*(r-1);break;case n.LINE_LOOP:t.lines+=o*r;break;case n.POINTS:t.points+=o*r;break;default:Qe("WebGLInfo: Unknown draw mode:",a);break}}function s(){t.calls=0,t.triangles=0,t.points=0,t.lines=0}return{memory:e,render:t,programs:null,autoReset:!0,reset:s,update:i}}function dv(n,e,t){const i=new WeakMap,s=new _t;function r(a,o,l){const c=a.morphTargetInfluences,u=o.morphAttributes.position||o.morphAttributes.normal||o.morphAttributes.color,f=u!==void 0?u.length:0;let h=i.get(o);if(h===void 0||h.count!==f){let Q=function(){x.dispose(),i.delete(o),o.removeEventListener("dispose",Q)};var p=Q;h!==void 0&&h.texture.dispose();const _=o.morphAttributes.position!==void 0,M=o.morphAttributes.normal!==void 0,g=o.morphAttributes.color!==void 0,d=o.morphAttributes.position||[],y=o.morphAttributes.normal||[],A=o.morphAttributes.color||[];let b=0;_===!0&&(b=1),M===!0&&(b=2),g===!0&&(b=3);let P=o.attributes.position.count*b,L=1;P>e.maxTextureSize&&(L=Math.ceil(P/e.maxTextureSize),P=e.maxTextureSize);const I=new Float32Array(P*L*4*f),x=new Wf(I,P,L,f);x.type=pn,x.needsUpdate=!0;const T=b*4;for(let D=0;D<f;D++){const X=d[D],Y=y[D],$=A[D],W=P*L*4*D;for(let B=0;B<X.count;B++){const N=B*T;_===!0&&(s.fromBufferAttribute(X,B),I[W+N+0]=s.x,I[W+N+1]=s.y,I[W+N+2]=s.z,I[W+N+3]=0),M===!0&&(s.fromBufferAttribute(Y,B),I[W+N+4]=s.x,I[W+N+5]=s.y,I[W+N+6]=s.z,I[W+N+7]=0),g===!0&&(s.fromBufferAttribute($,B),I[W+N+8]=s.x,I[W+N+9]=s.y,I[W+N+10]=s.z,I[W+N+11]=$.itemSize===4?s.w:1)}}h={count:f,texture:x,size:new ke(P,L)},i.set(o,h),o.addEventListener("dispose",Q)}if(a.isInstancedMesh===!0&&a.morphTexture!==null)l.getUniforms().setValue(n,"morphTexture",a.morphTexture,t);else{let _=0;for(let g=0;g<c.length;g++)_+=c[g];const M=o.morphTargetsRelative?1:1-_;l.getUniforms().setValue(n,"morphTargetBaseInfluence",M),l.getUniforms().setValue(n,"morphTargetInfluences",c)}l.getUniforms().setValue(n,"morphTargetsTexture",h.texture,t),l.getUniforms().setValue(n,"morphTargetsTextureSize",h.size)}return{update:r}}function pv(n,e,t,i,s){let r=new WeakMap;function a(c){const u=s.render.frame,f=c.geometry,h=e.get(c,f);if(r.get(h)!==u&&(e.update(h),r.set(h,u)),c.isInstancedMesh&&(c.hasEventListener("dispose",l)===!1&&c.addEventListener("dispose",l),r.get(c)!==u&&(t.update(c.instanceMatrix,n.ARRAY_BUFFER),c.instanceColor!==null&&t.update(c.instanceColor,n.ARRAY_BUFFER),r.set(c,u))),c.isSkinnedMesh){const p=c.skeleton;r.get(p)!==u&&(p.update(),r.set(p,u))}return h}function o(){r=new WeakMap}function l(c){const u=c.target;u.removeEventListener("dispose",l),i.releaseStatesOfObject(u),t.remove(u.instanceMatrix),u.instanceColor!==null&&t.remove(u.instanceColor)}return{update:a,dispose:o}}const mv={[Cf]:"LINEAR_TONE_MAPPING",[Rf]:"REINHARD_TONE_MAPPING",[Pf]:"CINEON_TONE_MAPPING",[Df]:"ACES_FILMIC_TONE_MAPPING",[If]:"AGX_TONE_MAPPING",[Uf]:"NEUTRAL_TONE_MAPPING",[Lf]:"CUSTOM_TONE_MAPPING"};function gv(n,e,t,i,s){const r=new Un(e,t,{type:n,depthBuffer:i,stencilBuffer:s}),a=new Un(e,t,{type:ri,depthBuffer:!1,stencilBuffer:!1}),o=new Mn;o.setAttribute("position",new tn([-1,3,0,-1,-1,0,3,-1,0],3)),o.setAttribute("uv",new tn([0,2,0,0,2,0],2));const l=new t_({uniforms:{tDiffuse:{value:null}},vertexShader:`
			precision highp float;

			uniform mat4 modelViewMatrix;
			uniform mat4 projectionMatrix;

			attribute vec3 position;
			attribute vec2 uv;

			varying vec2 vUv;

			void main() {
				vUv = uv;
				gl_Position = projectionMatrix * modelViewMatrix * vec4( position, 1.0 );
			}`,fragmentShader:`
			precision highp float;

			uniform sampler2D tDiffuse;

			varying vec2 vUv;

			#include <tonemapping_pars_fragment>
			#include <colorspace_pars_fragment>

			void main() {
				gl_FragColor = texture2D( tDiffuse, vUv );

				#ifdef LINEAR_TONE_MAPPING
					gl_FragColor.rgb = LinearToneMapping( gl_FragColor.rgb );
				#elif defined( REINHARD_TONE_MAPPING )
					gl_FragColor.rgb = ReinhardToneMapping( gl_FragColor.rgb );
				#elif defined( CINEON_TONE_MAPPING )
					gl_FragColor.rgb = CineonToneMapping( gl_FragColor.rgb );
				#elif defined( ACES_FILMIC_TONE_MAPPING )
					gl_FragColor.rgb = ACESFilmicToneMapping( gl_FragColor.rgb );
				#elif defined( AGX_TONE_MAPPING )
					gl_FragColor.rgb = AgXToneMapping( gl_FragColor.rgb );
				#elif defined( NEUTRAL_TONE_MAPPING )
					gl_FragColor.rgb = NeutralToneMapping( gl_FragColor.rgb );
				#elif defined( CUSTOM_TONE_MAPPING )
					gl_FragColor.rgb = CustomToneMapping( gl_FragColor.rgb );
				#endif

				#ifdef SRGB_TRANSFER
					gl_FragColor = sRGBTransferOETF( gl_FragColor );
				#endif
			}`,depthTest:!1,depthWrite:!1}),c=new qt(o,l),u=new _c(-1,1,1,-1,0,1);let f=null,h=null,p=!1,_,M=null,g=[],d=!1;this.setSize=function(y,A){r.setSize(y,A),a.setSize(y,A);for(let b=0;b<g.length;b++){const P=g[b];P.setSize&&P.setSize(y,A)}},this.setEffects=function(y){g=y,d=g.length>0&&g[0].isRenderPass===!0;const A=r.width,b=r.height;for(let P=0;P<g.length;P++){const L=g[P];L.setSize&&L.setSize(A,b)}},this.begin=function(y,A){if(p||y.toneMapping===In&&g.length===0)return!1;if(M=A,A!==null){const b=A.width,P=A.height;(r.width!==b||r.height!==P)&&this.setSize(b,P)}return d===!1&&y.setRenderTarget(r),_=y.toneMapping,y.toneMapping=In,!0},this.hasRenderPass=function(){return d},this.end=function(y,A){y.toneMapping=_,p=!0;let b=r,P=a;for(let L=0;L<g.length;L++){const I=g[L];if(I.enabled!==!1&&(I.render(y,P,b,A),I.needsSwap!==!1)){const x=b;b=P,P=x}}if(f!==y.outputColorSpace||h!==y.toneMapping){f=y.outputColorSpace,h=y.toneMapping,l.defines={},et.getTransfer(f)===at&&(l.defines.SRGB_TRANSFER="");const L=mv[h];L&&(l.defines[L]=""),l.needsUpdate=!0}l.uniforms.tDiffuse.value=b.texture,y.setRenderTarget(M),y.render(c,u),M=null,p=!1},this.isCompositing=function(){return p},this.dispose=function(){r.dispose(),a.dispose(),o.dispose(),l.dispose()}}const id=new Ft,Fl=new pr(1,1),sd=new Wf,rd=new Rg,ad=new Zf,Zu=[],Ju=[],Qu=new Float32Array(16),eh=new Float32Array(9),th=new Float32Array(4);function Ls(n,e,t){const i=n[0];if(i<=0||i>0)return n;const s=e*t;let r=Zu[s];if(r===void 0&&(r=new Float32Array(s),Zu[s]=r),e!==0){i.toArray(r,0);for(let a=1,o=0;a!==e;++a)o+=t,n[a].toArray(r,o)}return r}function bt(n,e){if(n.length!==e.length)return!1;for(let t=0,i=n.length;t<i;t++)if(n[t]!==e[t])return!1;return!0}function Et(n,e){for(let t=0,i=e.length;t<i;t++)n[t]=e[t]}function Oa(n,e){let t=Ju[e];t===void 0&&(t=new Int32Array(e),Ju[e]=t);for(let i=0;i!==e;++i)t[i]=n.allocateTextureUnit();return t}function _v(n,e){const t=this.cache;t[0]!==e&&(n.uniform1f(this.addr,e),t[0]=e)}function xv(n,e){const t=this.cache;if(e.x!==void 0)(t[0]!==e.x||t[1]!==e.y)&&(n.uniform2f(this.addr,e.x,e.y),t[0]=e.x,t[1]=e.y);else{if(bt(t,e))return;n.uniform2fv(this.addr,e),Et(t,e)}}function vv(n,e){const t=this.cache;if(e.x!==void 0)(t[0]!==e.x||t[1]!==e.y||t[2]!==e.z)&&(n.uniform3f(this.addr,e.x,e.y,e.z),t[0]=e.x,t[1]=e.y,t[2]=e.z);else if(e.r!==void 0)(t[0]!==e.r||t[1]!==e.g||t[2]!==e.b)&&(n.uniform3f(this.addr,e.r,e.g,e.b),t[0]=e.r,t[1]=e.g,t[2]=e.b);else{if(bt(t,e))return;n.uniform3fv(this.addr,e),Et(t,e)}}function Mv(n,e){const t=this.cache;if(e.x!==void 0)(t[0]!==e.x||t[1]!==e.y||t[2]!==e.z||t[3]!==e.w)&&(n.uniform4f(this.addr,e.x,e.y,e.z,e.w),t[0]=e.x,t[1]=e.y,t[2]=e.z,t[3]=e.w);else{if(bt(t,e))return;n.uniform4fv(this.addr,e),Et(t,e)}}function Sv(n,e){const t=this.cache,i=e.elements;if(i===void 0){if(bt(t,e))return;n.uniformMatrix2fv(this.addr,!1,e),Et(t,e)}else{if(bt(t,i))return;th.set(i),n.uniformMatrix2fv(this.addr,!1,th),Et(t,i)}}function yv(n,e){const t=this.cache,i=e.elements;if(i===void 0){if(bt(t,e))return;n.uniformMatrix3fv(this.addr,!1,e),Et(t,e)}else{if(bt(t,i))return;eh.set(i),n.uniformMatrix3fv(this.addr,!1,eh),Et(t,i)}}function bv(n,e){const t=this.cache,i=e.elements;if(i===void 0){if(bt(t,e))return;n.uniformMatrix4fv(this.addr,!1,e),Et(t,e)}else{if(bt(t,i))return;Qu.set(i),n.uniformMatrix4fv(this.addr,!1,Qu),Et(t,i)}}function Ev(n,e){const t=this.cache;t[0]!==e&&(n.uniform1i(this.addr,e),t[0]=e)}function Tv(n,e){const t=this.cache;if(e.x!==void 0)(t[0]!==e.x||t[1]!==e.y)&&(n.uniform2i(this.addr,e.x,e.y),t[0]=e.x,t[1]=e.y);else{if(bt(t,e))return;n.uniform2iv(this.addr,e),Et(t,e)}}function Av(n,e){const t=this.cache;if(e.x!==void 0)(t[0]!==e.x||t[1]!==e.y||t[2]!==e.z)&&(n.uniform3i(this.addr,e.x,e.y,e.z),t[0]=e.x,t[1]=e.y,t[2]=e.z);else{if(bt(t,e))return;n.uniform3iv(this.addr,e),Et(t,e)}}function wv(n,e){const t=this.cache;if(e.x!==void 0)(t[0]!==e.x||t[1]!==e.y||t[2]!==e.z||t[3]!==e.w)&&(n.uniform4i(this.addr,e.x,e.y,e.z,e.w),t[0]=e.x,t[1]=e.y,t[2]=e.z,t[3]=e.w);else{if(bt(t,e))return;n.uniform4iv(this.addr,e),Et(t,e)}}function Cv(n,e){const t=this.cache;t[0]!==e&&(n.uniform1ui(this.addr,e),t[0]=e)}function Rv(n,e){const t=this.cache;if(e.x!==void 0)(t[0]!==e.x||t[1]!==e.y)&&(n.uniform2ui(this.addr,e.x,e.y),t[0]=e.x,t[1]=e.y);else{if(bt(t,e))return;n.uniform2uiv(this.addr,e),Et(t,e)}}function Pv(n,e){const t=this.cache;if(e.x!==void 0)(t[0]!==e.x||t[1]!==e.y||t[2]!==e.z)&&(n.uniform3ui(this.addr,e.x,e.y,e.z),t[0]=e.x,t[1]=e.y,t[2]=e.z);else{if(bt(t,e))return;n.uniform3uiv(this.addr,e),Et(t,e)}}function Dv(n,e){const t=this.cache;if(e.x!==void 0)(t[0]!==e.x||t[1]!==e.y||t[2]!==e.z||t[3]!==e.w)&&(n.uniform4ui(this.addr,e.x,e.y,e.z,e.w),t[0]=e.x,t[1]=e.y,t[2]=e.z,t[3]=e.w);else{if(bt(t,e))return;n.uniform4uiv(this.addr,e),Et(t,e)}}function Lv(n,e,t){const i=this.cache,s=t.allocateTextureUnit();i[0]!==s&&(n.uniform1i(this.addr,s),i[0]=s);let r;this.type===n.SAMPLER_2D_SHADOW?(Fl.compareFunction=t.isReversedDepthBuffer()?hc:uc,r=Fl):r=id,t.setTexture2D(e||r,s)}function Iv(n,e,t){const i=this.cache,s=t.allocateTextureUnit();i[0]!==s&&(n.uniform1i(this.addr,s),i[0]=s),t.setTexture3D(e||rd,s)}function Uv(n,e,t){const i=this.cache,s=t.allocateTextureUnit();i[0]!==s&&(n.uniform1i(this.addr,s),i[0]=s),t.setTextureCube(e||ad,s)}function Nv(n,e,t){const i=this.cache,s=t.allocateTextureUnit();i[0]!==s&&(n.uniform1i(this.addr,s),i[0]=s),t.setTexture2DArray(e||sd,s)}function Fv(n){switch(n){case 5126:return _v;case 35664:return xv;case 35665:return vv;case 35666:return Mv;case 35674:return Sv;case 35675:return yv;case 35676:return bv;case 5124:case 35670:return Ev;case 35667:case 35671:return Tv;case 35668:case 35672:return Av;case 35669:case 35673:return wv;case 5125:return Cv;case 36294:return Rv;case 36295:return Pv;case 36296:return Dv;case 35678:case 36198:case 36298:case 36306:case 35682:return Lv;case 35679:case 36299:case 36307:return Iv;case 35680:case 36300:case 36308:case 36293:return Uv;case 36289:case 36303:case 36311:case 36292:return Nv}}function Ov(n,e){n.uniform1fv(this.addr,e)}function Bv(n,e){const t=Ls(e,this.size,2);n.uniform2fv(this.addr,t)}function zv(n,e){const t=Ls(e,this.size,3);n.uniform3fv(this.addr,t)}function Hv(n,e){const t=Ls(e,this.size,4);n.uniform4fv(this.addr,t)}function Vv(n,e){const t=Ls(e,this.size,4);n.uniformMatrix2fv(this.addr,!1,t)}function kv(n,e){const t=Ls(e,this.size,9);n.uniformMatrix3fv(this.addr,!1,t)}function Gv(n,e){const t=Ls(e,this.size,16);n.uniformMatrix4fv(this.addr,!1,t)}function Wv(n,e){n.uniform1iv(this.addr,e)}function Xv(n,e){n.uniform2iv(this.addr,e)}function Yv(n,e){n.uniform3iv(this.addr,e)}function qv(n,e){n.uniform4iv(this.addr,e)}function jv(n,e){n.uniform1uiv(this.addr,e)}function Kv(n,e){n.uniform2uiv(this.addr,e)}function $v(n,e){n.uniform3uiv(this.addr,e)}function Zv(n,e){n.uniform4uiv(this.addr,e)}function Jv(n,e,t){const i=this.cache,s=e.length,r=Oa(t,s);bt(i,r)||(n.uniform1iv(this.addr,r),Et(i,r));let a;this.type===n.SAMPLER_2D_SHADOW?a=Fl:a=id;for(let o=0;o!==s;++o)t.setTexture2D(e[o]||a,r[o])}function Qv(n,e,t){const i=this.cache,s=e.length,r=Oa(t,s);bt(i,r)||(n.uniform1iv(this.addr,r),Et(i,r));for(let a=0;a!==s;++a)t.setTexture3D(e[a]||rd,r[a])}function eM(n,e,t){const i=this.cache,s=e.length,r=Oa(t,s);bt(i,r)||(n.uniform1iv(this.addr,r),Et(i,r));for(let a=0;a!==s;++a)t.setTextureCube(e[a]||ad,r[a])}function tM(n,e,t){const i=this.cache,s=e.length,r=Oa(t,s);bt(i,r)||(n.uniform1iv(this.addr,r),Et(i,r));for(let a=0;a!==s;++a)t.setTexture2DArray(e[a]||sd,r[a])}function nM(n){switch(n){case 5126:return Ov;case 35664:return Bv;case 35665:return zv;case 35666:return Hv;case 35674:return Vv;case 35675:return kv;case 35676:return Gv;case 5124:case 35670:return Wv;case 35667:case 35671:return Xv;case 35668:case 35672:return Yv;case 35669:case 35673:return qv;case 5125:return jv;case 36294:return Kv;case 36295:return $v;case 36296:return Zv;case 35678:case 36198:case 36298:case 36306:case 35682:return Jv;case 35679:case 36299:case 36307:return Qv;case 35680:case 36300:case 36308:case 36293:return eM;case 36289:case 36303:case 36311:case 36292:return tM}}class iM{constructor(e,t,i){this.id=e,this.addr=i,this.cache=[],this.type=t.type,this.setValue=Fv(t.type)}}class sM{constructor(e,t,i){this.id=e,this.addr=i,this.cache=[],this.type=t.type,this.size=t.size,this.setValue=nM(t.type)}}class rM{constructor(e){this.id=e,this.seq=[],this.map={}}setValue(e,t,i){const s=this.seq;for(let r=0,a=s.length;r!==a;++r){const o=s[r];o.setValue(e,t[o.id],i)}}}const Lo=/(\w+)(\])?(\[|\.)?/g;function nh(n,e){n.seq.push(e),n.map[e.id]=e}function aM(n,e,t){const i=n.name,s=i.length;for(Lo.lastIndex=0;;){const r=Lo.exec(i),a=Lo.lastIndex;let o=r[1];const l=r[2]==="]",c=r[3];if(l&&(o=o|0),c===void 0||c==="["&&a+2===s){nh(t,c===void 0?new iM(o,n,e):new sM(o,n,e));break}else{let f=t.map[o];f===void 0&&(f=new rM(o),nh(t,f)),t=f}}}class ca{constructor(e,t){this.seq=[],this.map={};const i=e.getProgramParameter(t,e.ACTIVE_UNIFORMS);for(let a=0;a<i;++a){const o=e.getActiveUniform(t,a),l=e.getUniformLocation(t,o.name);aM(o,l,this)}const s=[],r=[];for(const a of this.seq)a.type===e.SAMPLER_2D_SHADOW||a.type===e.SAMPLER_CUBE_SHADOW||a.type===e.SAMPLER_2D_ARRAY_SHADOW?s.push(a):r.push(a);s.length>0&&(this.seq=s.concat(r))}setValue(e,t,i,s){const r=this.map[t];r!==void 0&&r.setValue(e,i,s)}setOptional(e,t,i){const s=t[i];s!==void 0&&this.setValue(e,i,s)}static upload(e,t,i,s){for(let r=0,a=t.length;r!==a;++r){const o=t[r],l=i[o.id];l.needsUpdate!==!1&&o.setValue(e,l.value,s)}}static seqWithValue(e,t){const i=[];for(let s=0,r=e.length;s!==r;++s){const a=e[s];a.id in t&&i.push(a)}return i}}function ih(n,e,t){const i=n.createShader(e);return n.shaderSource(i,t),n.compileShader(i),i}const oM=37297;let lM=0;function cM(n,e){const t=n.split(`
`),i=[],s=Math.max(e-6,0),r=Math.min(e+6,t.length);for(let a=s;a<r;a++){const o=a+1;i.push(`${o===e?">":" "} ${o}: ${t[a]}`)}return i.join(`
`)}const sh=new qe;function uM(n){et._getMatrix(sh,et.workingColorSpace,n);const e=`mat3( ${sh.elements.map(t=>t.toFixed(4))} )`;switch(et.getTransfer(n)){case va:return[e,"LinearTransferOETF"];case at:return[e,"sRGBTransferOETF"];default:return Oe("WebGLProgram: Unsupported color space: ",n),[e,"LinearTransferOETF"]}}function rh(n,e,t){const i=n.getShaderParameter(e,n.COMPILE_STATUS),r=(n.getShaderInfoLog(e)||"").trim();if(i&&r==="")return"";const a=/ERROR: 0:(\d+)/.exec(r);if(a){const o=parseInt(a[1]);return t.toUpperCase()+`

`+r+`

`+cM(n.getShaderSource(e),o)}else return r}function hM(n,e){const t=uM(e);return[`vec4 ${n}( vec4 value ) {`,`	return ${t[1]}( vec4( value.rgb * ${t[0]}, value.a ) );`,"}"].join(`
`)}const fM={[Cf]:"Linear",[Rf]:"Reinhard",[Pf]:"Cineon",[Df]:"ACESFilmic",[If]:"AgX",[Uf]:"Neutral",[Lf]:"Custom"};function dM(n,e){const t=fM[e];return t===void 0?(Oe("WebGLProgram: Unsupported toneMapping:",e),"vec3 "+n+"( vec3 color ) { return LinearToneMapping( color ); }"):"vec3 "+n+"( vec3 color ) { return "+t+"ToneMapping( color ); }"}const $r=new O;function pM(){et.getLuminanceCoefficients($r);const n=$r.x.toFixed(4),e=$r.y.toFixed(4),t=$r.z.toFixed(4);return["float luminance( const in vec3 rgb ) {",`	const vec3 weights = vec3( ${n}, ${e}, ${t} );`,"	return dot( weights, rgb );","}"].join(`
`)}function mM(n){return[n.extensionClipCullDistance?"#extension GL_ANGLE_clip_cull_distance : require":"",n.extensionMultiDraw?"#extension GL_ANGLE_multi_draw : require":""].filter($s).join(`
`)}function gM(n){const e=[];for(const t in n){const i=n[t];i!==!1&&e.push("#define "+t+" "+i)}return e.join(`
`)}function _M(n,e){const t={},i=n.getProgramParameter(e,n.ACTIVE_ATTRIBUTES);for(let s=0;s<i;s++){const r=n.getActiveAttrib(e,s),a=r.name;let o=1;r.type===n.FLOAT_MAT2&&(o=2),r.type===n.FLOAT_MAT3&&(o=3),r.type===n.FLOAT_MAT4&&(o=4),t[a]={type:r.type,location:n.getAttribLocation(e,a),locationSize:o}}return t}function $s(n){return n!==""}function ah(n,e){const t=e.numSpotLightShadows+e.numSpotLightMaps-e.numSpotLightShadowsWithMaps;return n.replace(/NUM_DIR_LIGHTS/g,e.numDirLights).replace(/NUM_SPOT_LIGHTS/g,e.numSpotLights).replace(/NUM_SPOT_LIGHT_MAPS/g,e.numSpotLightMaps).replace(/NUM_SPOT_LIGHT_COORDS/g,t).replace(/NUM_RECT_AREA_LIGHTS/g,e.numRectAreaLights).replace(/NUM_POINT_LIGHTS/g,e.numPointLights).replace(/NUM_HEMI_LIGHTS/g,e.numHemiLights).replace(/NUM_DIR_LIGHT_SHADOWS/g,e.numDirLightShadows).replace(/NUM_SPOT_LIGHT_SHADOWS_WITH_MAPS/g,e.numSpotLightShadowsWithMaps).replace(/NUM_SPOT_LIGHT_SHADOWS/g,e.numSpotLightShadows).replace(/NUM_POINT_LIGHT_SHADOWS/g,e.numPointLightShadows)}function oh(n,e){return n.replace(/NUM_CLIPPING_PLANES/g,e.numClippingPlanes).replace(/UNION_CLIPPING_PLANES/g,e.numClippingPlanes-e.numClipIntersection)}const xM=/^[ \t]*#include +<([\w\d./]+)>/gm;function Ol(n){return n.replace(xM,MM)}const vM=new Map;function MM(n,e){let t=Ke[e];if(t===void 0){const i=vM.get(e);if(i!==void 0)t=Ke[i],Oe('WebGLRenderer: Shader chunk "%s" has been deprecated. Use "%s" instead.',e,i);else throw new Error("Can not resolve #include <"+e+">")}return Ol(t)}const SM=/#pragma unroll_loop_start\s+for\s*\(\s*int\s+i\s*=\s*(\d+)\s*;\s*i\s*<\s*(\d+)\s*;\s*i\s*\+\+\s*\)\s*{([\s\S]+?)}\s+#pragma unroll_loop_end/g;function lh(n){return n.replace(SM,yM)}function yM(n,e,t,i){let s="";for(let r=parseInt(e);r<parseInt(t);r++)s+=i.replace(/\[\s*i\s*\]/g,"[ "+r+" ]").replace(/UNROLLED_LOOP_INDEX/g,r);return s}function ch(n){let e=`precision ${n.precision} float;
	precision ${n.precision} int;
	precision ${n.precision} sampler2D;
	precision ${n.precision} samplerCube;
	precision ${n.precision} sampler3D;
	precision ${n.precision} sampler2DArray;
	precision ${n.precision} sampler2DShadow;
	precision ${n.precision} samplerCubeShadow;
	precision ${n.precision} sampler2DArrayShadow;
	precision ${n.precision} isampler2D;
	precision ${n.precision} isampler3D;
	precision ${n.precision} isamplerCube;
	precision ${n.precision} isampler2DArray;
	precision ${n.precision} usampler2D;
	precision ${n.precision} usampler3D;
	precision ${n.precision} usamplerCube;
	precision ${n.precision} usampler2DArray;
	`;return n.precision==="highp"?e+=`
#define HIGH_PRECISION`:n.precision==="mediump"?e+=`
#define MEDIUM_PRECISION`:n.precision==="lowp"&&(e+=`
#define LOW_PRECISION`),e}const bM={[ia]:"SHADOWMAP_TYPE_PCF",[js]:"SHADOWMAP_TYPE_VSM"};function EM(n){return bM[n.shadowMapType]||"SHADOWMAP_TYPE_BASIC"}const TM={[Wi]:"ENVMAP_TYPE_CUBE",[As]:"ENVMAP_TYPE_CUBE",[Ua]:"ENVMAP_TYPE_CUBE_UV"};function AM(n){return n.envMap===!1?"ENVMAP_TYPE_CUBE":TM[n.envMapMode]||"ENVMAP_TYPE_CUBE"}const wM={[As]:"ENVMAP_MODE_REFRACTION"};function CM(n){return n.envMap===!1?"ENVMAP_MODE_REFLECTION":wM[n.envMapMode]||"ENVMAP_MODE_REFLECTION"}const RM={[wf]:"ENVMAP_BLENDING_MULTIPLY",[og]:"ENVMAP_BLENDING_MIX",[lg]:"ENVMAP_BLENDING_ADD"};function PM(n){return n.envMap===!1?"ENVMAP_BLENDING_NONE":RM[n.combine]||"ENVMAP_BLENDING_NONE"}function DM(n){const e=n.envMapCubeUVHeight;if(e===null)return null;const t=Math.log2(e)-2,i=1/e;return{texelWidth:1/(3*Math.max(Math.pow(2,t),112)),texelHeight:i,maxMip:t}}function LM(n,e,t,i){const s=n.getContext(),r=t.defines;let a=t.vertexShader,o=t.fragmentShader;const l=EM(t),c=AM(t),u=CM(t),f=PM(t),h=DM(t),p=mM(t),_=gM(r),M=s.createProgram();let g,d,y=t.glslVersion?"#version "+t.glslVersion+`
`:"";t.isRawShaderMaterial?(g=["#define SHADER_TYPE "+t.shaderType,"#define SHADER_NAME "+t.shaderName,_].filter($s).join(`
`),g.length>0&&(g+=`
`),d=["#define SHADER_TYPE "+t.shaderType,"#define SHADER_NAME "+t.shaderName,_].filter($s).join(`
`),d.length>0&&(d+=`
`)):(g=[ch(t),"#define SHADER_TYPE "+t.shaderType,"#define SHADER_NAME "+t.shaderName,_,t.extensionClipCullDistance?"#define USE_CLIP_DISTANCE":"",t.batching?"#define USE_BATCHING":"",t.batchingColor?"#define USE_BATCHING_COLOR":"",t.instancing?"#define USE_INSTANCING":"",t.instancingColor?"#define USE_INSTANCING_COLOR":"",t.instancingMorph?"#define USE_INSTANCING_MORPH":"",t.useFog&&t.fog?"#define USE_FOG":"",t.useFog&&t.fogExp2?"#define FOG_EXP2":"",t.map?"#define USE_MAP":"",t.envMap?"#define USE_ENVMAP":"",t.envMap?"#define "+u:"",t.lightMap?"#define USE_LIGHTMAP":"",t.aoMap?"#define USE_AOMAP":"",t.bumpMap?"#define USE_BUMPMAP":"",t.normalMap?"#define USE_NORMALMAP":"",t.normalMapObjectSpace?"#define USE_NORMALMAP_OBJECTSPACE":"",t.normalMapTangentSpace?"#define USE_NORMALMAP_TANGENTSPACE":"",t.displacementMap?"#define USE_DISPLACEMENTMAP":"",t.emissiveMap?"#define USE_EMISSIVEMAP":"",t.anisotropy?"#define USE_ANISOTROPY":"",t.anisotropyMap?"#define USE_ANISOTROPYMAP":"",t.clearcoatMap?"#define USE_CLEARCOATMAP":"",t.clearcoatRoughnessMap?"#define USE_CLEARCOAT_ROUGHNESSMAP":"",t.clearcoatNormalMap?"#define USE_CLEARCOAT_NORMALMAP":"",t.iridescenceMap?"#define USE_IRIDESCENCEMAP":"",t.iridescenceThicknessMap?"#define USE_IRIDESCENCE_THICKNESSMAP":"",t.specularMap?"#define USE_SPECULARMAP":"",t.specularColorMap?"#define USE_SPECULAR_COLORMAP":"",t.specularIntensityMap?"#define USE_SPECULAR_INTENSITYMAP":"",t.roughnessMap?"#define USE_ROUGHNESSMAP":"",t.metalnessMap?"#define USE_METALNESSMAP":"",t.alphaMap?"#define USE_ALPHAMAP":"",t.alphaHash?"#define USE_ALPHAHASH":"",t.transmission?"#define USE_TRANSMISSION":"",t.transmissionMap?"#define USE_TRANSMISSIONMAP":"",t.thicknessMap?"#define USE_THICKNESSMAP":"",t.sheenColorMap?"#define USE_SHEEN_COLORMAP":"",t.sheenRoughnessMap?"#define USE_SHEEN_ROUGHNESSMAP":"",t.mapUv?"#define MAP_UV "+t.mapUv:"",t.alphaMapUv?"#define ALPHAMAP_UV "+t.alphaMapUv:"",t.lightMapUv?"#define LIGHTMAP_UV "+t.lightMapUv:"",t.aoMapUv?"#define AOMAP_UV "+t.aoMapUv:"",t.emissiveMapUv?"#define EMISSIVEMAP_UV "+t.emissiveMapUv:"",t.bumpMapUv?"#define BUMPMAP_UV "+t.bumpMapUv:"",t.normalMapUv?"#define NORMALMAP_UV "+t.normalMapUv:"",t.displacementMapUv?"#define DISPLACEMENTMAP_UV "+t.displacementMapUv:"",t.metalnessMapUv?"#define METALNESSMAP_UV "+t.metalnessMapUv:"",t.roughnessMapUv?"#define ROUGHNESSMAP_UV "+t.roughnessMapUv:"",t.anisotropyMapUv?"#define ANISOTROPYMAP_UV "+t.anisotropyMapUv:"",t.clearcoatMapUv?"#define CLEARCOATMAP_UV "+t.clearcoatMapUv:"",t.clearcoatNormalMapUv?"#define CLEARCOAT_NORMALMAP_UV "+t.clearcoatNormalMapUv:"",t.clearcoatRoughnessMapUv?"#define CLEARCOAT_ROUGHNESSMAP_UV "+t.clearcoatRoughnessMapUv:"",t.iridescenceMapUv?"#define IRIDESCENCEMAP_UV "+t.iridescenceMapUv:"",t.iridescenceThicknessMapUv?"#define IRIDESCENCE_THICKNESSMAP_UV "+t.iridescenceThicknessMapUv:"",t.sheenColorMapUv?"#define SHEEN_COLORMAP_UV "+t.sheenColorMapUv:"",t.sheenRoughnessMapUv?"#define SHEEN_ROUGHNESSMAP_UV "+t.sheenRoughnessMapUv:"",t.specularMapUv?"#define SPECULARMAP_UV "+t.specularMapUv:"",t.specularColorMapUv?"#define SPECULAR_COLORMAP_UV "+t.specularColorMapUv:"",t.specularIntensityMapUv?"#define SPECULAR_INTENSITYMAP_UV "+t.specularIntensityMapUv:"",t.transmissionMapUv?"#define TRANSMISSIONMAP_UV "+t.transmissionMapUv:"",t.thicknessMapUv?"#define THICKNESSMAP_UV "+t.thicknessMapUv:"",t.vertexTangents&&t.flatShading===!1?"#define USE_TANGENT":"",t.vertexColors?"#define USE_COLOR":"",t.vertexAlphas?"#define USE_COLOR_ALPHA":"",t.vertexUv1s?"#define USE_UV1":"",t.vertexUv2s?"#define USE_UV2":"",t.vertexUv3s?"#define USE_UV3":"",t.pointsUvs?"#define USE_POINTS_UV":"",t.flatShading?"#define FLAT_SHADED":"",t.skinning?"#define USE_SKINNING":"",t.morphTargets?"#define USE_MORPHTARGETS":"",t.morphNormals&&t.flatShading===!1?"#define USE_MORPHNORMALS":"",t.morphColors?"#define USE_MORPHCOLORS":"",t.morphTargetsCount>0?"#define MORPHTARGETS_TEXTURE_STRIDE "+t.morphTextureStride:"",t.morphTargetsCount>0?"#define MORPHTARGETS_COUNT "+t.morphTargetsCount:"",t.doubleSided?"#define DOUBLE_SIDED":"",t.flipSided?"#define FLIP_SIDED":"",t.shadowMapEnabled?"#define USE_SHADOWMAP":"",t.shadowMapEnabled?"#define "+l:"",t.sizeAttenuation?"#define USE_SIZEATTENUATION":"",t.numLightProbes>0?"#define USE_LIGHT_PROBES":"",t.logarithmicDepthBuffer?"#define USE_LOGARITHMIC_DEPTH_BUFFER":"",t.reversedDepthBuffer?"#define USE_REVERSED_DEPTH_BUFFER":"","uniform mat4 modelMatrix;","uniform mat4 modelViewMatrix;","uniform mat4 projectionMatrix;","uniform mat4 viewMatrix;","uniform mat3 normalMatrix;","uniform vec3 cameraPosition;","uniform bool isOrthographic;","#ifdef USE_INSTANCING","	attribute mat4 instanceMatrix;","#endif","#ifdef USE_INSTANCING_COLOR","	attribute vec3 instanceColor;","#endif","#ifdef USE_INSTANCING_MORPH","	uniform sampler2D morphTexture;","#endif","attribute vec3 position;","attribute vec3 normal;","attribute vec2 uv;","#ifdef USE_UV1","	attribute vec2 uv1;","#endif","#ifdef USE_UV2","	attribute vec2 uv2;","#endif","#ifdef USE_UV3","	attribute vec2 uv3;","#endif","#ifdef USE_TANGENT","	attribute vec4 tangent;","#endif","#if defined( USE_COLOR_ALPHA )","	attribute vec4 color;","#elif defined( USE_COLOR )","	attribute vec3 color;","#endif","#ifdef USE_SKINNING","	attribute vec4 skinIndex;","	attribute vec4 skinWeight;","#endif",`
`].filter($s).join(`
`),d=[ch(t),"#define SHADER_TYPE "+t.shaderType,"#define SHADER_NAME "+t.shaderName,_,t.useFog&&t.fog?"#define USE_FOG":"",t.useFog&&t.fogExp2?"#define FOG_EXP2":"",t.alphaToCoverage?"#define ALPHA_TO_COVERAGE":"",t.map?"#define USE_MAP":"",t.matcap?"#define USE_MATCAP":"",t.envMap?"#define USE_ENVMAP":"",t.envMap?"#define "+c:"",t.envMap?"#define "+u:"",t.envMap?"#define "+f:"",h?"#define CUBEUV_TEXEL_WIDTH "+h.texelWidth:"",h?"#define CUBEUV_TEXEL_HEIGHT "+h.texelHeight:"",h?"#define CUBEUV_MAX_MIP "+h.maxMip+".0":"",t.lightMap?"#define USE_LIGHTMAP":"",t.aoMap?"#define USE_AOMAP":"",t.bumpMap?"#define USE_BUMPMAP":"",t.normalMap?"#define USE_NORMALMAP":"",t.normalMapObjectSpace?"#define USE_NORMALMAP_OBJECTSPACE":"",t.normalMapTangentSpace?"#define USE_NORMALMAP_TANGENTSPACE":"",t.emissiveMap?"#define USE_EMISSIVEMAP":"",t.anisotropy?"#define USE_ANISOTROPY":"",t.anisotropyMap?"#define USE_ANISOTROPYMAP":"",t.clearcoat?"#define USE_CLEARCOAT":"",t.clearcoatMap?"#define USE_CLEARCOATMAP":"",t.clearcoatRoughnessMap?"#define USE_CLEARCOAT_ROUGHNESSMAP":"",t.clearcoatNormalMap?"#define USE_CLEARCOAT_NORMALMAP":"",t.dispersion?"#define USE_DISPERSION":"",t.iridescence?"#define USE_IRIDESCENCE":"",t.iridescenceMap?"#define USE_IRIDESCENCEMAP":"",t.iridescenceThicknessMap?"#define USE_IRIDESCENCE_THICKNESSMAP":"",t.specularMap?"#define USE_SPECULARMAP":"",t.specularColorMap?"#define USE_SPECULAR_COLORMAP":"",t.specularIntensityMap?"#define USE_SPECULAR_INTENSITYMAP":"",t.roughnessMap?"#define USE_ROUGHNESSMAP":"",t.metalnessMap?"#define USE_METALNESSMAP":"",t.alphaMap?"#define USE_ALPHAMAP":"",t.alphaTest?"#define USE_ALPHATEST":"",t.alphaHash?"#define USE_ALPHAHASH":"",t.sheen?"#define USE_SHEEN":"",t.sheenColorMap?"#define USE_SHEEN_COLORMAP":"",t.sheenRoughnessMap?"#define USE_SHEEN_ROUGHNESSMAP":"",t.transmission?"#define USE_TRANSMISSION":"",t.transmissionMap?"#define USE_TRANSMISSIONMAP":"",t.thicknessMap?"#define USE_THICKNESSMAP":"",t.vertexTangents&&t.flatShading===!1?"#define USE_TANGENT":"",t.vertexColors||t.instancingColor?"#define USE_COLOR":"",t.vertexAlphas||t.batchingColor?"#define USE_COLOR_ALPHA":"",t.vertexUv1s?"#define USE_UV1":"",t.vertexUv2s?"#define USE_UV2":"",t.vertexUv3s?"#define USE_UV3":"",t.pointsUvs?"#define USE_POINTS_UV":"",t.gradientMap?"#define USE_GRADIENTMAP":"",t.flatShading?"#define FLAT_SHADED":"",t.doubleSided?"#define DOUBLE_SIDED":"",t.flipSided?"#define FLIP_SIDED":"",t.shadowMapEnabled?"#define USE_SHADOWMAP":"",t.shadowMapEnabled?"#define "+l:"",t.premultipliedAlpha?"#define PREMULTIPLIED_ALPHA":"",t.numLightProbes>0?"#define USE_LIGHT_PROBES":"",t.decodeVideoTexture?"#define DECODE_VIDEO_TEXTURE":"",t.decodeVideoTextureEmissive?"#define DECODE_VIDEO_TEXTURE_EMISSIVE":"",t.logarithmicDepthBuffer?"#define USE_LOGARITHMIC_DEPTH_BUFFER":"",t.reversedDepthBuffer?"#define USE_REVERSED_DEPTH_BUFFER":"","uniform mat4 viewMatrix;","uniform vec3 cameraPosition;","uniform bool isOrthographic;",t.toneMapping!==In?"#define TONE_MAPPING":"",t.toneMapping!==In?Ke.tonemapping_pars_fragment:"",t.toneMapping!==In?dM("toneMapping",t.toneMapping):"",t.dithering?"#define DITHERING":"",t.opaque?"#define OPAQUE":"",Ke.colorspace_pars_fragment,hM("linearToOutputTexel",t.outputColorSpace),pM(),t.useDepthPacking?"#define DEPTH_PACKING "+t.depthPacking:"",`
`].filter($s).join(`
`)),a=Ol(a),a=ah(a,t),a=oh(a,t),o=Ol(o),o=ah(o,t),o=oh(o,t),a=lh(a),o=lh(o),t.isRawShaderMaterial!==!0&&(y=`#version 300 es
`,g=[p,"#define attribute in","#define varying out","#define texture2D texture"].join(`
`)+`
`+g,d=["#define varying in",t.glslVersion===fu?"":"layout(location = 0) out highp vec4 pc_fragColor;",t.glslVersion===fu?"":"#define gl_FragColor pc_fragColor","#define gl_FragDepthEXT gl_FragDepth","#define texture2D texture","#define textureCube texture","#define texture2DProj textureProj","#define texture2DLodEXT textureLod","#define texture2DProjLodEXT textureProjLod","#define textureCubeLodEXT textureLod","#define texture2DGradEXT textureGrad","#define texture2DProjGradEXT textureProjGrad","#define textureCubeGradEXT textureGrad"].join(`
`)+`
`+d);const A=y+g+a,b=y+d+o,P=ih(s,s.VERTEX_SHADER,A),L=ih(s,s.FRAGMENT_SHADER,b);s.attachShader(M,P),s.attachShader(M,L),t.index0AttributeName!==void 0?s.bindAttribLocation(M,0,t.index0AttributeName):t.morphTargets===!0&&s.bindAttribLocation(M,0,"position"),s.linkProgram(M);function I(D){if(n.debug.checkShaderErrors){const X=s.getProgramInfoLog(M)||"",Y=s.getShaderInfoLog(P)||"",$=s.getShaderInfoLog(L)||"",W=X.trim(),B=Y.trim(),N=$.trim();let ie=!0,le=!0;if(s.getProgramParameter(M,s.LINK_STATUS)===!1)if(ie=!1,typeof n.debug.onShaderError=="function")n.debug.onShaderError(s,M,P,L);else{const ve=rh(s,P,"vertex"),se=rh(s,L,"fragment");Qe("THREE.WebGLProgram: Shader Error "+s.getError()+" - VALIDATE_STATUS "+s.getProgramParameter(M,s.VALIDATE_STATUS)+`

Material Name: `+D.name+`
Material Type: `+D.type+`

Program Info Log: `+W+`
`+ve+`
`+se)}else W!==""?Oe("WebGLProgram: Program Info Log:",W):(B===""||N==="")&&(le=!1);le&&(D.diagnostics={runnable:ie,programLog:W,vertexShader:{log:B,prefix:g},fragmentShader:{log:N,prefix:d}})}s.deleteShader(P),s.deleteShader(L),x=new ca(s,M),T=_M(s,M)}let x;this.getUniforms=function(){return x===void 0&&I(this),x};let T;this.getAttributes=function(){return T===void 0&&I(this),T};let Q=t.rendererExtensionParallelShaderCompile===!1;return this.isReady=function(){return Q===!1&&(Q=s.getProgramParameter(M,oM)),Q},this.destroy=function(){i.releaseStatesOfProgram(this),s.deleteProgram(M),this.program=void 0},this.type=t.shaderType,this.name=t.shaderName,this.id=lM++,this.cacheKey=e,this.usedTimes=1,this.program=M,this.vertexShader=P,this.fragmentShader=L,this}let IM=0;class UM{constructor(){this.shaderCache=new Map,this.materialCache=new Map}update(e){const t=e.vertexShader,i=e.fragmentShader,s=this._getShaderStage(t),r=this._getShaderStage(i),a=this._getShaderCacheForMaterial(e);return a.has(s)===!1&&(a.add(s),s.usedTimes++),a.has(r)===!1&&(a.add(r),r.usedTimes++),this}remove(e){const t=this.materialCache.get(e);for(const i of t)i.usedTimes--,i.usedTimes===0&&this.shaderCache.delete(i.code);return this.materialCache.delete(e),this}getVertexShaderID(e){return this._getShaderStage(e.vertexShader).id}getFragmentShaderID(e){return this._getShaderStage(e.fragmentShader).id}dispose(){this.shaderCache.clear(),this.materialCache.clear()}_getShaderCacheForMaterial(e){const t=this.materialCache;let i=t.get(e);return i===void 0&&(i=new Set,t.set(e,i)),i}_getShaderStage(e){const t=this.shaderCache;let i=t.get(e);return i===void 0&&(i=new NM(e),t.set(e,i)),i}}class NM{constructor(e){this.id=IM++,this.code=e,this.usedTimes=0}}function FM(n,e,t,i,s,r){const a=new dc,o=new UM,l=new Set,c=[],u=new Map,f=i.logarithmicDepthBuffer;let h=i.precision;const p={MeshDepthMaterial:"depth",MeshDistanceMaterial:"distance",MeshNormalMaterial:"normal",MeshBasicMaterial:"basic",MeshLambertMaterial:"lambert",MeshPhongMaterial:"phong",MeshToonMaterial:"toon",MeshStandardMaterial:"physical",MeshPhysicalMaterial:"physical",MeshMatcapMaterial:"matcap",LineBasicMaterial:"basic",LineDashedMaterial:"dashed",PointsMaterial:"points",ShadowMaterial:"shadow",SpriteMaterial:"sprite"};function _(x){return l.add(x),x===0?"uv":`uv${x}`}function M(x,T,Q,D,X){const Y=D.fog,$=X.geometry,W=x.isMeshStandardMaterial||x.isMeshLambertMaterial||x.isMeshPhongMaterial?D.environment:null,B=x.isMeshStandardMaterial||x.isMeshLambertMaterial&&!x.envMap||x.isMeshPhongMaterial&&!x.envMap,N=e.get(x.envMap||W,B),ie=N&&N.mapping===Ua?N.image.height:null,le=p[x.type];x.precision!==null&&(h=i.getMaxPrecision(x.precision),h!==x.precision&&Oe("WebGLProgram.getParameters:",x.precision,"not supported, using",h,"instead."));const ve=$.morphAttributes.position||$.morphAttributes.normal||$.morphAttributes.color,se=ve!==void 0?ve.length:0;let oe=0;$.morphAttributes.position!==void 0&&(oe=1),$.morphAttributes.normal!==void 0&&(oe=2),$.morphAttributes.color!==void 0&&(oe=3);let we,Ge,nt,te;if(le){const rt=Cn[le];we=rt.vertexShader,Ge=rt.fragmentShader}else we=x.vertexShader,Ge=x.fragmentShader,o.update(x),nt=o.getVertexShaderID(x),te=o.getFragmentShaderID(x);const pe=n.getRenderTarget(),ge=n.state.buffers.depth.getReversed(),He=X.isInstancedMesh===!0,Fe=X.isBatchedMesh===!0,Be=!!x.map,w=!!x.matcap,R=!!N,z=!!x.aoMap,ee=!!x.lightMap,q=!!x.bumpMap,ne=!!x.normalMap,E=!!x.displacementMap,ue=!!x.emissiveMap,re=!!x.metalnessMap,J=!!x.roughnessMap,ae=x.anisotropy>0,v=x.clearcoat>0,m=x.dispersion>0,C=x.iridescence>0,V=x.sheen>0,K=x.transmission>0,k=ae&&!!x.anisotropyMap,_e=v&&!!x.clearcoatMap,ce=v&&!!x.clearcoatNormalMap,Ce=v&&!!x.clearcoatRoughnessMap,Le=C&&!!x.iridescenceMap,he=C&&!!x.iridescenceThicknessMap,de=V&&!!x.sheenColorMap,Me=V&&!!x.sheenRoughnessMap,Ee=!!x.specularMap,Te=!!x.specularColorMap,We=!!x.specularIntensityMap,U=K&&!!x.transmissionMap,xe=K&&!!x.thicknessMap,me=!!x.gradientMap,Re=!!x.alphaMap,fe=x.alphaTest>0,Z=!!x.alphaHash,Pe=!!x.extensions;let Ve=In;x.toneMapped&&(pe===null||pe.isXRRenderTarget===!0)&&(Ve=n.toneMapping);const pt={shaderID:le,shaderType:x.type,shaderName:x.name,vertexShader:we,fragmentShader:Ge,defines:x.defines,customVertexShaderID:nt,customFragmentShaderID:te,isRawShaderMaterial:x.isRawShaderMaterial===!0,glslVersion:x.glslVersion,precision:h,batching:Fe,batchingColor:Fe&&X._colorsTexture!==null,instancing:He,instancingColor:He&&X.instanceColor!==null,instancingMorph:He&&X.morphTexture!==null,outputColorSpace:pe===null?n.outputColorSpace:pe.isXRRenderTarget===!0?pe.texture.colorSpace:Cs,alphaToCoverage:!!x.alphaToCoverage,map:Be,matcap:w,envMap:R,envMapMode:R&&N.mapping,envMapCubeUVHeight:ie,aoMap:z,lightMap:ee,bumpMap:q,normalMap:ne,displacementMap:E,emissiveMap:ue,normalMapObjectSpace:ne&&x.normalMapType===hg,normalMapTangentSpace:ne&&x.normalMapType===kf,metalnessMap:re,roughnessMap:J,anisotropy:ae,anisotropyMap:k,clearcoat:v,clearcoatMap:_e,clearcoatNormalMap:ce,clearcoatRoughnessMap:Ce,dispersion:m,iridescence:C,iridescenceMap:Le,iridescenceThicknessMap:he,sheen:V,sheenColorMap:de,sheenRoughnessMap:Me,specularMap:Ee,specularColorMap:Te,specularIntensityMap:We,transmission:K,transmissionMap:U,thicknessMap:xe,gradientMap:me,opaque:x.transparent===!1&&x.blending===Ss&&x.alphaToCoverage===!1,alphaMap:Re,alphaTest:fe,alphaHash:Z,combine:x.combine,mapUv:Be&&_(x.map.channel),aoMapUv:z&&_(x.aoMap.channel),lightMapUv:ee&&_(x.lightMap.channel),bumpMapUv:q&&_(x.bumpMap.channel),normalMapUv:ne&&_(x.normalMap.channel),displacementMapUv:E&&_(x.displacementMap.channel),emissiveMapUv:ue&&_(x.emissiveMap.channel),metalnessMapUv:re&&_(x.metalnessMap.channel),roughnessMapUv:J&&_(x.roughnessMap.channel),anisotropyMapUv:k&&_(x.anisotropyMap.channel),clearcoatMapUv:_e&&_(x.clearcoatMap.channel),clearcoatNormalMapUv:ce&&_(x.clearcoatNormalMap.channel),clearcoatRoughnessMapUv:Ce&&_(x.clearcoatRoughnessMap.channel),iridescenceMapUv:Le&&_(x.iridescenceMap.channel),iridescenceThicknessMapUv:he&&_(x.iridescenceThicknessMap.channel),sheenColorMapUv:de&&_(x.sheenColorMap.channel),sheenRoughnessMapUv:Me&&_(x.sheenRoughnessMap.channel),specularMapUv:Ee&&_(x.specularMap.channel),specularColorMapUv:Te&&_(x.specularColorMap.channel),specularIntensityMapUv:We&&_(x.specularIntensityMap.channel),transmissionMapUv:U&&_(x.transmissionMap.channel),thicknessMapUv:xe&&_(x.thicknessMap.channel),alphaMapUv:Re&&_(x.alphaMap.channel),vertexTangents:!!$.attributes.tangent&&(ne||ae),vertexColors:x.vertexColors,vertexAlphas:x.vertexColors===!0&&!!$.attributes.color&&$.attributes.color.itemSize===4,pointsUvs:X.isPoints===!0&&!!$.attributes.uv&&(Be||Re),fog:!!Y,useFog:x.fog===!0,fogExp2:!!Y&&Y.isFogExp2,flatShading:x.wireframe===!1&&(x.flatShading===!0||$.attributes.normal===void 0&&ne===!1&&(x.isMeshLambertMaterial||x.isMeshPhongMaterial||x.isMeshStandardMaterial||x.isMeshPhysicalMaterial)),sizeAttenuation:x.sizeAttenuation===!0,logarithmicDepthBuffer:f,reversedDepthBuffer:ge,skinning:X.isSkinnedMesh===!0,morphTargets:$.morphAttributes.position!==void 0,morphNormals:$.morphAttributes.normal!==void 0,morphColors:$.morphAttributes.color!==void 0,morphTargetsCount:se,morphTextureStride:oe,numDirLights:T.directional.length,numPointLights:T.point.length,numSpotLights:T.spot.length,numSpotLightMaps:T.spotLightMap.length,numRectAreaLights:T.rectArea.length,numHemiLights:T.hemi.length,numDirLightShadows:T.directionalShadowMap.length,numPointLightShadows:T.pointShadowMap.length,numSpotLightShadows:T.spotShadowMap.length,numSpotLightShadowsWithMaps:T.numSpotLightShadowsWithMaps,numLightProbes:T.numLightProbes,numClippingPlanes:r.numPlanes,numClipIntersection:r.numIntersection,dithering:x.dithering,shadowMapEnabled:n.shadowMap.enabled&&Q.length>0,shadowMapType:n.shadowMap.type,toneMapping:Ve,decodeVideoTexture:Be&&x.map.isVideoTexture===!0&&et.getTransfer(x.map.colorSpace)===at,decodeVideoTextureEmissive:ue&&x.emissiveMap.isVideoTexture===!0&&et.getTransfer(x.emissiveMap.colorSpace)===at,premultipliedAlpha:x.premultipliedAlpha,doubleSided:x.side===Pn,flipSided:x.side===Yt,useDepthPacking:x.depthPacking>=0,depthPacking:x.depthPacking||0,index0AttributeName:x.index0AttributeName,extensionClipCullDistance:Pe&&x.extensions.clipCullDistance===!0&&t.has("WEBGL_clip_cull_distance"),extensionMultiDraw:(Pe&&x.extensions.multiDraw===!0||Fe)&&t.has("WEBGL_multi_draw"),rendererExtensionParallelShaderCompile:t.has("KHR_parallel_shader_compile"),customProgramCacheKey:x.customProgramCacheKey()};return pt.vertexUv1s=l.has(1),pt.vertexUv2s=l.has(2),pt.vertexUv3s=l.has(3),l.clear(),pt}function g(x){const T=[];if(x.shaderID?T.push(x.shaderID):(T.push(x.customVertexShaderID),T.push(x.customFragmentShaderID)),x.defines!==void 0)for(const Q in x.defines)T.push(Q),T.push(x.defines[Q]);return x.isRawShaderMaterial===!1&&(d(T,x),y(T,x),T.push(n.outputColorSpace)),T.push(x.customProgramCacheKey),T.join()}function d(x,T){x.push(T.precision),x.push(T.outputColorSpace),x.push(T.envMapMode),x.push(T.envMapCubeUVHeight),x.push(T.mapUv),x.push(T.alphaMapUv),x.push(T.lightMapUv),x.push(T.aoMapUv),x.push(T.bumpMapUv),x.push(T.normalMapUv),x.push(T.displacementMapUv),x.push(T.emissiveMapUv),x.push(T.metalnessMapUv),x.push(T.roughnessMapUv),x.push(T.anisotropyMapUv),x.push(T.clearcoatMapUv),x.push(T.clearcoatNormalMapUv),x.push(T.clearcoatRoughnessMapUv),x.push(T.iridescenceMapUv),x.push(T.iridescenceThicknessMapUv),x.push(T.sheenColorMapUv),x.push(T.sheenRoughnessMapUv),x.push(T.specularMapUv),x.push(T.specularColorMapUv),x.push(T.specularIntensityMapUv),x.push(T.transmissionMapUv),x.push(T.thicknessMapUv),x.push(T.combine),x.push(T.fogExp2),x.push(T.sizeAttenuation),x.push(T.morphTargetsCount),x.push(T.morphAttributeCount),x.push(T.numDirLights),x.push(T.numPointLights),x.push(T.numSpotLights),x.push(T.numSpotLightMaps),x.push(T.numHemiLights),x.push(T.numRectAreaLights),x.push(T.numDirLightShadows),x.push(T.numPointLightShadows),x.push(T.numSpotLightShadows),x.push(T.numSpotLightShadowsWithMaps),x.push(T.numLightProbes),x.push(T.shadowMapType),x.push(T.toneMapping),x.push(T.numClippingPlanes),x.push(T.numClipIntersection),x.push(T.depthPacking)}function y(x,T){a.disableAll(),T.instancing&&a.enable(0),T.instancingColor&&a.enable(1),T.instancingMorph&&a.enable(2),T.matcap&&a.enable(3),T.envMap&&a.enable(4),T.normalMapObjectSpace&&a.enable(5),T.normalMapTangentSpace&&a.enable(6),T.clearcoat&&a.enable(7),T.iridescence&&a.enable(8),T.alphaTest&&a.enable(9),T.vertexColors&&a.enable(10),T.vertexAlphas&&a.enable(11),T.vertexUv1s&&a.enable(12),T.vertexUv2s&&a.enable(13),T.vertexUv3s&&a.enable(14),T.vertexTangents&&a.enable(15),T.anisotropy&&a.enable(16),T.alphaHash&&a.enable(17),T.batching&&a.enable(18),T.dispersion&&a.enable(19),T.batchingColor&&a.enable(20),T.gradientMap&&a.enable(21),x.push(a.mask),a.disableAll(),T.fog&&a.enable(0),T.useFog&&a.enable(1),T.flatShading&&a.enable(2),T.logarithmicDepthBuffer&&a.enable(3),T.reversedDepthBuffer&&a.enable(4),T.skinning&&a.enable(5),T.morphTargets&&a.enable(6),T.morphNormals&&a.enable(7),T.morphColors&&a.enable(8),T.premultipliedAlpha&&a.enable(9),T.shadowMapEnabled&&a.enable(10),T.doubleSided&&a.enable(11),T.flipSided&&a.enable(12),T.useDepthPacking&&a.enable(13),T.dithering&&a.enable(14),T.transmission&&a.enable(15),T.sheen&&a.enable(16),T.opaque&&a.enable(17),T.pointsUvs&&a.enable(18),T.decodeVideoTexture&&a.enable(19),T.decodeVideoTextureEmissive&&a.enable(20),T.alphaToCoverage&&a.enable(21),x.push(a.mask)}function A(x){const T=p[x.type];let Q;if(T){const D=Cn[T];Q=Jg.clone(D.uniforms)}else Q=x.uniforms;return Q}function b(x,T){let Q=u.get(T);return Q!==void 0?++Q.usedTimes:(Q=new LM(n,T,x,s),c.push(Q),u.set(T,Q)),Q}function P(x){if(--x.usedTimes===0){const T=c.indexOf(x);c[T]=c[c.length-1],c.pop(),u.delete(x.cacheKey),x.destroy()}}function L(x){o.remove(x)}function I(){o.dispose()}return{getParameters:M,getProgramCacheKey:g,getUniforms:A,acquireProgram:b,releaseProgram:P,releaseShaderCache:L,programs:c,dispose:I}}function OM(){let n=new WeakMap;function e(a){return n.has(a)}function t(a){let o=n.get(a);return o===void 0&&(o={},n.set(a,o)),o}function i(a){n.delete(a)}function s(a,o,l){n.get(a)[o]=l}function r(){n=new WeakMap}return{has:e,get:t,remove:i,update:s,dispose:r}}function BM(n,e){return n.groupOrder!==e.groupOrder?n.groupOrder-e.groupOrder:n.renderOrder!==e.renderOrder?n.renderOrder-e.renderOrder:n.material.id!==e.material.id?n.material.id-e.material.id:n.materialVariant!==e.materialVariant?n.materialVariant-e.materialVariant:n.z!==e.z?n.z-e.z:n.id-e.id}function uh(n,e){return n.groupOrder!==e.groupOrder?n.groupOrder-e.groupOrder:n.renderOrder!==e.renderOrder?n.renderOrder-e.renderOrder:n.z!==e.z?e.z-n.z:n.id-e.id}function hh(){const n=[];let e=0;const t=[],i=[],s=[];function r(){e=0,t.length=0,i.length=0,s.length=0}function a(h){let p=0;return h.isInstancedMesh&&(p+=2),h.isSkinnedMesh&&(p+=1),p}function o(h,p,_,M,g,d){let y=n[e];return y===void 0?(y={id:h.id,object:h,geometry:p,material:_,materialVariant:a(h),groupOrder:M,renderOrder:h.renderOrder,z:g,group:d},n[e]=y):(y.id=h.id,y.object=h,y.geometry=p,y.material=_,y.materialVariant=a(h),y.groupOrder=M,y.renderOrder=h.renderOrder,y.z=g,y.group=d),e++,y}function l(h,p,_,M,g,d){const y=o(h,p,_,M,g,d);_.transmission>0?i.push(y):_.transparent===!0?s.push(y):t.push(y)}function c(h,p,_,M,g,d){const y=o(h,p,_,M,g,d);_.transmission>0?i.unshift(y):_.transparent===!0?s.unshift(y):t.unshift(y)}function u(h,p){t.length>1&&t.sort(h||BM),i.length>1&&i.sort(p||uh),s.length>1&&s.sort(p||uh)}function f(){for(let h=e,p=n.length;h<p;h++){const _=n[h];if(_.id===null)break;_.id=null,_.object=null,_.geometry=null,_.material=null,_.group=null}}return{opaque:t,transmissive:i,transparent:s,init:r,push:l,unshift:c,finish:f,sort:u}}function zM(){let n=new WeakMap;function e(i,s){const r=n.get(i);let a;return r===void 0?(a=new hh,n.set(i,[a])):s>=r.length?(a=new hh,r.push(a)):a=r[s],a}function t(){n=new WeakMap}return{get:e,dispose:t}}function HM(){const n={};return{get:function(e){if(n[e.id]!==void 0)return n[e.id];let t;switch(e.type){case"DirectionalLight":t={direction:new O,color:new Ze};break;case"SpotLight":t={position:new O,direction:new O,color:new Ze,distance:0,coneCos:0,penumbraCos:0,decay:0};break;case"PointLight":t={position:new O,color:new Ze,distance:0,decay:0};break;case"HemisphereLight":t={direction:new O,skyColor:new Ze,groundColor:new Ze};break;case"RectAreaLight":t={color:new Ze,position:new O,halfWidth:new O,halfHeight:new O};break}return n[e.id]=t,t}}}function VM(){const n={};return{get:function(e){if(n[e.id]!==void 0)return n[e.id];let t;switch(e.type){case"DirectionalLight":t={shadowIntensity:1,shadowBias:0,shadowNormalBias:0,shadowRadius:1,shadowMapSize:new ke};break;case"SpotLight":t={shadowIntensity:1,shadowBias:0,shadowNormalBias:0,shadowRadius:1,shadowMapSize:new ke};break;case"PointLight":t={shadowIntensity:1,shadowBias:0,shadowNormalBias:0,shadowRadius:1,shadowMapSize:new ke,shadowCameraNear:1,shadowCameraFar:1e3};break}return n[e.id]=t,t}}}let kM=0;function GM(n,e){return(e.castShadow?2:0)-(n.castShadow?2:0)+(e.map?1:0)-(n.map?1:0)}function WM(n){const e=new HM,t=VM(),i={version:0,hash:{directionalLength:-1,pointLength:-1,spotLength:-1,rectAreaLength:-1,hemiLength:-1,numDirectionalShadows:-1,numPointShadows:-1,numSpotShadows:-1,numSpotMaps:-1,numLightProbes:-1},ambient:[0,0,0],probe:[],directional:[],directionalShadow:[],directionalShadowMap:[],directionalShadowMatrix:[],spot:[],spotLightMap:[],spotShadow:[],spotShadowMap:[],spotLightMatrix:[],rectArea:[],rectAreaLTC1:null,rectAreaLTC2:null,point:[],pointShadow:[],pointShadowMap:[],pointShadowMatrix:[],hemi:[],numSpotLightShadowsWithMaps:0,numLightProbes:0};for(let c=0;c<9;c++)i.probe.push(new O);const s=new O,r=new ht,a=new ht;function o(c){let u=0,f=0,h=0;for(let T=0;T<9;T++)i.probe[T].set(0,0,0);let p=0,_=0,M=0,g=0,d=0,y=0,A=0,b=0,P=0,L=0,I=0;c.sort(GM);for(let T=0,Q=c.length;T<Q;T++){const D=c[T],X=D.color,Y=D.intensity,$=D.distance;let W=null;if(D.shadow&&D.shadow.map&&(D.shadow.map.texture.format===ws?W=D.shadow.map.texture:W=D.shadow.map.depthTexture||D.shadow.map.texture),D.isAmbientLight)u+=X.r*Y,f+=X.g*Y,h+=X.b*Y;else if(D.isLightProbe){for(let B=0;B<9;B++)i.probe[B].addScaledVector(D.sh.coefficients[B],Y);I++}else if(D.isDirectionalLight){const B=e.get(D);if(B.color.copy(D.color).multiplyScalar(D.intensity),D.castShadow){const N=D.shadow,ie=t.get(D);ie.shadowIntensity=N.intensity,ie.shadowBias=N.bias,ie.shadowNormalBias=N.normalBias,ie.shadowRadius=N.radius,ie.shadowMapSize=N.mapSize,i.directionalShadow[p]=ie,i.directionalShadowMap[p]=W,i.directionalShadowMatrix[p]=D.shadow.matrix,y++}i.directional[p]=B,p++}else if(D.isSpotLight){const B=e.get(D);B.position.setFromMatrixPosition(D.matrixWorld),B.color.copy(X).multiplyScalar(Y),B.distance=$,B.coneCos=Math.cos(D.angle),B.penumbraCos=Math.cos(D.angle*(1-D.penumbra)),B.decay=D.decay,i.spot[M]=B;const N=D.shadow;if(D.map&&(i.spotLightMap[P]=D.map,P++,N.updateMatrices(D),D.castShadow&&L++),i.spotLightMatrix[M]=N.matrix,D.castShadow){const ie=t.get(D);ie.shadowIntensity=N.intensity,ie.shadowBias=N.bias,ie.shadowNormalBias=N.normalBias,ie.shadowRadius=N.radius,ie.shadowMapSize=N.mapSize,i.spotShadow[M]=ie,i.spotShadowMap[M]=W,b++}M++}else if(D.isRectAreaLight){const B=e.get(D);B.color.copy(X).multiplyScalar(Y),B.halfWidth.set(D.width*.5,0,0),B.halfHeight.set(0,D.height*.5,0),i.rectArea[g]=B,g++}else if(D.isPointLight){const B=e.get(D);if(B.color.copy(D.color).multiplyScalar(D.intensity),B.distance=D.distance,B.decay=D.decay,D.castShadow){const N=D.shadow,ie=t.get(D);ie.shadowIntensity=N.intensity,ie.shadowBias=N.bias,ie.shadowNormalBias=N.normalBias,ie.shadowRadius=N.radius,ie.shadowMapSize=N.mapSize,ie.shadowCameraNear=N.camera.near,ie.shadowCameraFar=N.camera.far,i.pointShadow[_]=ie,i.pointShadowMap[_]=W,i.pointShadowMatrix[_]=D.shadow.matrix,A++}i.point[_]=B,_++}else if(D.isHemisphereLight){const B=e.get(D);B.skyColor.copy(D.color).multiplyScalar(Y),B.groundColor.copy(D.groundColor).multiplyScalar(Y),i.hemi[d]=B,d++}}g>0&&(n.has("OES_texture_float_linear")===!0?(i.rectAreaLTC1=Se.LTC_FLOAT_1,i.rectAreaLTC2=Se.LTC_FLOAT_2):(i.rectAreaLTC1=Se.LTC_HALF_1,i.rectAreaLTC2=Se.LTC_HALF_2)),i.ambient[0]=u,i.ambient[1]=f,i.ambient[2]=h;const x=i.hash;(x.directionalLength!==p||x.pointLength!==_||x.spotLength!==M||x.rectAreaLength!==g||x.hemiLength!==d||x.numDirectionalShadows!==y||x.numPointShadows!==A||x.numSpotShadows!==b||x.numSpotMaps!==P||x.numLightProbes!==I)&&(i.directional.length=p,i.spot.length=M,i.rectArea.length=g,i.point.length=_,i.hemi.length=d,i.directionalShadow.length=y,i.directionalShadowMap.length=y,i.pointShadow.length=A,i.pointShadowMap.length=A,i.spotShadow.length=b,i.spotShadowMap.length=b,i.directionalShadowMatrix.length=y,i.pointShadowMatrix.length=A,i.spotLightMatrix.length=b+P-L,i.spotLightMap.length=P,i.numSpotLightShadowsWithMaps=L,i.numLightProbes=I,x.directionalLength=p,x.pointLength=_,x.spotLength=M,x.rectAreaLength=g,x.hemiLength=d,x.numDirectionalShadows=y,x.numPointShadows=A,x.numSpotShadows=b,x.numSpotMaps=P,x.numLightProbes=I,i.version=kM++)}function l(c,u){let f=0,h=0,p=0,_=0,M=0;const g=u.matrixWorldInverse;for(let d=0,y=c.length;d<y;d++){const A=c[d];if(A.isDirectionalLight){const b=i.directional[f];b.direction.setFromMatrixPosition(A.matrixWorld),s.setFromMatrixPosition(A.target.matrixWorld),b.direction.sub(s),b.direction.transformDirection(g),f++}else if(A.isSpotLight){const b=i.spot[p];b.position.setFromMatrixPosition(A.matrixWorld),b.position.applyMatrix4(g),b.direction.setFromMatrixPosition(A.matrixWorld),s.setFromMatrixPosition(A.target.matrixWorld),b.direction.sub(s),b.direction.transformDirection(g),p++}else if(A.isRectAreaLight){const b=i.rectArea[_];b.position.setFromMatrixPosition(A.matrixWorld),b.position.applyMatrix4(g),a.identity(),r.copy(A.matrixWorld),r.premultiply(g),a.extractRotation(r),b.halfWidth.set(A.width*.5,0,0),b.halfHeight.set(0,A.height*.5,0),b.halfWidth.applyMatrix4(a),b.halfHeight.applyMatrix4(a),_++}else if(A.isPointLight){const b=i.point[h];b.position.setFromMatrixPosition(A.matrixWorld),b.position.applyMatrix4(g),h++}else if(A.isHemisphereLight){const b=i.hemi[M];b.direction.setFromMatrixPosition(A.matrixWorld),b.direction.transformDirection(g),M++}}}return{setup:o,setupView:l,state:i}}function fh(n){const e=new WM(n),t=[],i=[];function s(u){c.camera=u,t.length=0,i.length=0}function r(u){t.push(u)}function a(u){i.push(u)}function o(){e.setup(t)}function l(u){e.setupView(t,u)}const c={lightsArray:t,shadowsArray:i,camera:null,lights:e,transmissionRenderTarget:{}};return{init:s,state:c,setupLights:o,setupLightsView:l,pushLight:r,pushShadow:a}}function XM(n){let e=new WeakMap;function t(s,r=0){const a=e.get(s);let o;return a===void 0?(o=new fh(n),e.set(s,[o])):r>=a.length?(o=new fh(n),a.push(o)):o=a[r],o}function i(){e=new WeakMap}return{get:t,dispose:i}}const YM=`void main() {
	gl_Position = vec4( position, 1.0 );
}`,qM=`uniform sampler2D shadow_pass;
uniform vec2 resolution;
uniform float radius;
void main() {
	const float samples = float( VSM_SAMPLES );
	float mean = 0.0;
	float squared_mean = 0.0;
	float uvStride = samples <= 1.0 ? 0.0 : 2.0 / ( samples - 1.0 );
	float uvStart = samples <= 1.0 ? 0.0 : - 1.0;
	for ( float i = 0.0; i < samples; i ++ ) {
		float uvOffset = uvStart + i * uvStride;
		#ifdef HORIZONTAL_PASS
			vec2 distribution = texture2D( shadow_pass, ( gl_FragCoord.xy + vec2( uvOffset, 0.0 ) * radius ) / resolution ).rg;
			mean += distribution.x;
			squared_mean += distribution.y * distribution.y + distribution.x * distribution.x;
		#else
			float depth = texture2D( shadow_pass, ( gl_FragCoord.xy + vec2( 0.0, uvOffset ) * radius ) / resolution ).r;
			mean += depth;
			squared_mean += depth * depth;
		#endif
	}
	mean = mean / samples;
	squared_mean = squared_mean / samples;
	float std_dev = sqrt( max( 0.0, squared_mean - mean * mean ) );
	gl_FragColor = vec4( mean, std_dev, 0.0, 1.0 );
}`,jM=[new O(1,0,0),new O(-1,0,0),new O(0,1,0),new O(0,-1,0),new O(0,0,1),new O(0,0,-1)],KM=[new O(0,-1,0),new O(0,-1,0),new O(0,0,1),new O(0,0,-1),new O(0,-1,0),new O(0,-1,0)],dh=new ht,Xs=new O,Io=new O;function $M(n,e,t){let i=new mc;const s=new ke,r=new ke,a=new _t,o=new n_,l=new i_,c={},u=t.maxTextureSize,f={[Ti]:Yt,[Yt]:Ti,[Pn]:Pn},h=new zn({defines:{VSM_SAMPLES:8},uniforms:{shadow_pass:{value:null},resolution:{value:new ke},radius:{value:4}},vertexShader:YM,fragmentShader:qM}),p=h.clone();p.defines.HORIZONTAL_PASS=1;const _=new Mn;_.setAttribute("position",new xn(new Float32Array([-1,-1,.5,3,-1,.5,-1,3,.5]),3));const M=new qt(_,h),g=this;this.enabled=!1,this.autoUpdate=!0,this.needsUpdate=!1,this.type=ia;let d=this.type;this.render=function(L,I,x){if(g.enabled===!1||g.autoUpdate===!1&&g.needsUpdate===!1||L.length===0)return;this.type===Af&&(Oe("WebGLShadowMap: PCFSoftShadowMap has been deprecated. Using PCFShadowMap instead."),this.type=ia);const T=n.getRenderTarget(),Q=n.getActiveCubeFace(),D=n.getActiveMipmapLevel(),X=n.state;X.setBlending(ei),X.buffers.depth.getReversed()===!0?X.buffers.color.setClear(0,0,0,0):X.buffers.color.setClear(1,1,1,1),X.buffers.depth.setTest(!0),X.setScissorTest(!1);const Y=d!==this.type;Y&&I.traverse(function($){$.material&&(Array.isArray($.material)?$.material.forEach(W=>W.needsUpdate=!0):$.material.needsUpdate=!0)});for(let $=0,W=L.length;$<W;$++){const B=L[$],N=B.shadow;if(N===void 0){Oe("WebGLShadowMap:",B,"has no shadow.");continue}if(N.autoUpdate===!1&&N.needsUpdate===!1)continue;s.copy(N.mapSize);const ie=N.getFrameExtents();s.multiply(ie),r.copy(N.mapSize),(s.x>u||s.y>u)&&(s.x>u&&(r.x=Math.floor(u/ie.x),s.x=r.x*ie.x,N.mapSize.x=r.x),s.y>u&&(r.y=Math.floor(u/ie.y),s.y=r.y*ie.y,N.mapSize.y=r.y));const le=n.state.buffers.depth.getReversed();if(N.camera._reversedDepth=le,N.map===null||Y===!0){if(N.map!==null&&(N.map.depthTexture!==null&&(N.map.depthTexture.dispose(),N.map.depthTexture=null),N.map.dispose()),this.type===js){if(B.isPointLight){Oe("WebGLShadowMap: VSM shadow maps are not supported for PointLights. Use PCF or BasicShadowMap instead.");continue}N.map=new Un(s.x,s.y,{format:ws,type:ri,minFilter:Ut,magFilter:Ut,generateMipmaps:!1}),N.map.texture.name=B.name+".shadowMap",N.map.depthTexture=new pr(s.x,s.y,pn),N.map.depthTexture.name=B.name+".shadowMapDepth",N.map.depthTexture.format=ai,N.map.depthTexture.compareFunction=null,N.map.depthTexture.minFilter=Ct,N.map.depthTexture.magFilter=Ct}else B.isPointLight?(N.map=new nd(s.x),N.map.depthTexture=new $g(s.x,On)):(N.map=new Un(s.x,s.y),N.map.depthTexture=new pr(s.x,s.y,On)),N.map.depthTexture.name=B.name+".shadowMap",N.map.depthTexture.format=ai,this.type===ia?(N.map.depthTexture.compareFunction=le?hc:uc,N.map.depthTexture.minFilter=Ut,N.map.depthTexture.magFilter=Ut):(N.map.depthTexture.compareFunction=null,N.map.depthTexture.minFilter=Ct,N.map.depthTexture.magFilter=Ct);N.camera.updateProjectionMatrix()}const ve=N.map.isWebGLCubeRenderTarget?6:1;for(let se=0;se<ve;se++){if(N.map.isWebGLCubeRenderTarget)n.setRenderTarget(N.map,se),n.clear();else{se===0&&(n.setRenderTarget(N.map),n.clear());const oe=N.getViewport(se);a.set(r.x*oe.x,r.y*oe.y,r.x*oe.z,r.y*oe.w),X.viewport(a)}if(B.isPointLight){const oe=N.camera,we=N.matrix,Ge=B.distance||oe.far;Ge!==oe.far&&(oe.far=Ge,oe.updateProjectionMatrix()),Xs.setFromMatrixPosition(B.matrixWorld),oe.position.copy(Xs),Io.copy(oe.position),Io.add(jM[se]),oe.up.copy(KM[se]),oe.lookAt(Io),oe.updateMatrixWorld(),we.makeTranslation(-Xs.x,-Xs.y,-Xs.z),dh.multiplyMatrices(oe.projectionMatrix,oe.matrixWorldInverse),N._frustum.setFromProjectionMatrix(dh,oe.coordinateSystem,oe.reversedDepth)}else N.updateMatrices(B);i=N.getFrustum(),b(I,x,N.camera,B,this.type)}N.isPointLightShadow!==!0&&this.type===js&&y(N,x),N.needsUpdate=!1}d=this.type,g.needsUpdate=!1,n.setRenderTarget(T,Q,D)};function y(L,I){const x=e.update(M);h.defines.VSM_SAMPLES!==L.blurSamples&&(h.defines.VSM_SAMPLES=L.blurSamples,p.defines.VSM_SAMPLES=L.blurSamples,h.needsUpdate=!0,p.needsUpdate=!0),L.mapPass===null&&(L.mapPass=new Un(s.x,s.y,{format:ws,type:ri})),h.uniforms.shadow_pass.value=L.map.depthTexture,h.uniforms.resolution.value=L.mapSize,h.uniforms.radius.value=L.radius,n.setRenderTarget(L.mapPass),n.clear(),n.renderBufferDirect(I,null,x,h,M,null),p.uniforms.shadow_pass.value=L.mapPass.texture,p.uniforms.resolution.value=L.mapSize,p.uniforms.radius.value=L.radius,n.setRenderTarget(L.map),n.clear(),n.renderBufferDirect(I,null,x,p,M,null)}function A(L,I,x,T){let Q=null;const D=x.isPointLight===!0?L.customDistanceMaterial:L.customDepthMaterial;if(D!==void 0)Q=D;else if(Q=x.isPointLight===!0?l:o,n.localClippingEnabled&&I.clipShadows===!0&&Array.isArray(I.clippingPlanes)&&I.clippingPlanes.length!==0||I.displacementMap&&I.displacementScale!==0||I.alphaMap&&I.alphaTest>0||I.map&&I.alphaTest>0||I.alphaToCoverage===!0){const X=Q.uuid,Y=I.uuid;let $=c[X];$===void 0&&($={},c[X]=$);let W=$[Y];W===void 0&&(W=Q.clone(),$[Y]=W,I.addEventListener("dispose",P)),Q=W}if(Q.visible=I.visible,Q.wireframe=I.wireframe,T===js?Q.side=I.shadowSide!==null?I.shadowSide:I.side:Q.side=I.shadowSide!==null?I.shadowSide:f[I.side],Q.alphaMap=I.alphaMap,Q.alphaTest=I.alphaToCoverage===!0?.5:I.alphaTest,Q.map=I.map,Q.clipShadows=I.clipShadows,Q.clippingPlanes=I.clippingPlanes,Q.clipIntersection=I.clipIntersection,Q.displacementMap=I.displacementMap,Q.displacementScale=I.displacementScale,Q.displacementBias=I.displacementBias,Q.wireframeLinewidth=I.wireframeLinewidth,Q.linewidth=I.linewidth,x.isPointLight===!0&&Q.isMeshDistanceMaterial===!0){const X=n.properties.get(Q);X.light=x}return Q}function b(L,I,x,T,Q){if(L.visible===!1)return;if(L.layers.test(I.layers)&&(L.isMesh||L.isLine||L.isPoints)&&(L.castShadow||L.receiveShadow&&Q===js)&&(!L.frustumCulled||i.intersectsObject(L))){L.modelViewMatrix.multiplyMatrices(x.matrixWorldInverse,L.matrixWorld);const Y=e.update(L),$=L.material;if(Array.isArray($)){const W=Y.groups;for(let B=0,N=W.length;B<N;B++){const ie=W[B],le=$[ie.materialIndex];if(le&&le.visible){const ve=A(L,le,T,Q);L.onBeforeShadow(n,L,I,x,Y,ve,ie),n.renderBufferDirect(x,null,Y,ve,L,ie),L.onAfterShadow(n,L,I,x,Y,ve,ie)}}}else if($.visible){const W=A(L,$,T,Q);L.onBeforeShadow(n,L,I,x,Y,W,null),n.renderBufferDirect(x,null,Y,W,L,null),L.onAfterShadow(n,L,I,x,Y,W,null)}}const X=L.children;for(let Y=0,$=X.length;Y<$;Y++)b(X[Y],I,x,T,Q)}function P(L){L.target.removeEventListener("dispose",P);for(const x in c){const T=c[x],Q=L.target.uuid;Q in T&&(T[Q].dispose(),delete T[Q])}}}function ZM(n,e){function t(){let U=!1;const xe=new _t;let me=null;const Re=new _t(0,0,0,0);return{setMask:function(fe){me!==fe&&!U&&(n.colorMask(fe,fe,fe,fe),me=fe)},setLocked:function(fe){U=fe},setClear:function(fe,Z,Pe,Ve,pt){pt===!0&&(fe*=Ve,Z*=Ve,Pe*=Ve),xe.set(fe,Z,Pe,Ve),Re.equals(xe)===!1&&(n.clearColor(fe,Z,Pe,Ve),Re.copy(xe))},reset:function(){U=!1,me=null,Re.set(-1,0,0,0)}}}function i(){let U=!1,xe=!1,me=null,Re=null,fe=null;return{setReversed:function(Z){if(xe!==Z){const Pe=e.get("EXT_clip_control");Z?Pe.clipControlEXT(Pe.LOWER_LEFT_EXT,Pe.ZERO_TO_ONE_EXT):Pe.clipControlEXT(Pe.LOWER_LEFT_EXT,Pe.NEGATIVE_ONE_TO_ONE_EXT),xe=Z;const Ve=fe;fe=null,this.setClear(Ve)}},getReversed:function(){return xe},setTest:function(Z){Z?pe(n.DEPTH_TEST):ge(n.DEPTH_TEST)},setMask:function(Z){me!==Z&&!U&&(n.depthMask(Z),me=Z)},setFunc:function(Z){if(xe&&(Z=Sg[Z]),Re!==Z){switch(Z){case jo:n.depthFunc(n.NEVER);break;case Ko:n.depthFunc(n.ALWAYS);break;case $o:n.depthFunc(n.LESS);break;case Ts:n.depthFunc(n.LEQUAL);break;case Zo:n.depthFunc(n.EQUAL);break;case Jo:n.depthFunc(n.GEQUAL);break;case Qo:n.depthFunc(n.GREATER);break;case el:n.depthFunc(n.NOTEQUAL);break;default:n.depthFunc(n.LEQUAL)}Re=Z}},setLocked:function(Z){U=Z},setClear:function(Z){fe!==Z&&(fe=Z,xe&&(Z=1-Z),n.clearDepth(Z))},reset:function(){U=!1,me=null,Re=null,fe=null,xe=!1}}}function s(){let U=!1,xe=null,me=null,Re=null,fe=null,Z=null,Pe=null,Ve=null,pt=null;return{setTest:function(rt){U||(rt?pe(n.STENCIL_TEST):ge(n.STENCIL_TEST))},setMask:function(rt){xe!==rt&&!U&&(n.stencilMask(rt),xe=rt)},setFunc:function(rt,Hn,Vn){(me!==rt||Re!==Hn||fe!==Vn)&&(n.stencilFunc(rt,Hn,Vn),me=rt,Re=Hn,fe=Vn)},setOp:function(rt,Hn,Vn){(Z!==rt||Pe!==Hn||Ve!==Vn)&&(n.stencilOp(rt,Hn,Vn),Z=rt,Pe=Hn,Ve=Vn)},setLocked:function(rt){U=rt},setClear:function(rt){pt!==rt&&(n.clearStencil(rt),pt=rt)},reset:function(){U=!1,xe=null,me=null,Re=null,fe=null,Z=null,Pe=null,Ve=null,pt=null}}}const r=new t,a=new i,o=new s,l=new WeakMap,c=new WeakMap;let u={},f={},h=new WeakMap,p=[],_=null,M=!1,g=null,d=null,y=null,A=null,b=null,P=null,L=null,I=new Ze(0,0,0),x=0,T=!1,Q=null,D=null,X=null,Y=null,$=null;const W=n.getParameter(n.MAX_COMBINED_TEXTURE_IMAGE_UNITS);let B=!1,N=0;const ie=n.getParameter(n.VERSION);ie.indexOf("WebGL")!==-1?(N=parseFloat(/^WebGL (\d)/.exec(ie)[1]),B=N>=1):ie.indexOf("OpenGL ES")!==-1&&(N=parseFloat(/^OpenGL ES (\d)/.exec(ie)[1]),B=N>=2);let le=null,ve={};const se=n.getParameter(n.SCISSOR_BOX),oe=n.getParameter(n.VIEWPORT),we=new _t().fromArray(se),Ge=new _t().fromArray(oe);function nt(U,xe,me,Re){const fe=new Uint8Array(4),Z=n.createTexture();n.bindTexture(U,Z),n.texParameteri(U,n.TEXTURE_MIN_FILTER,n.NEAREST),n.texParameteri(U,n.TEXTURE_MAG_FILTER,n.NEAREST);for(let Pe=0;Pe<me;Pe++)U===n.TEXTURE_3D||U===n.TEXTURE_2D_ARRAY?n.texImage3D(xe,0,n.RGBA,1,1,Re,0,n.RGBA,n.UNSIGNED_BYTE,fe):n.texImage2D(xe+Pe,0,n.RGBA,1,1,0,n.RGBA,n.UNSIGNED_BYTE,fe);return Z}const te={};te[n.TEXTURE_2D]=nt(n.TEXTURE_2D,n.TEXTURE_2D,1),te[n.TEXTURE_CUBE_MAP]=nt(n.TEXTURE_CUBE_MAP,n.TEXTURE_CUBE_MAP_POSITIVE_X,6),te[n.TEXTURE_2D_ARRAY]=nt(n.TEXTURE_2D_ARRAY,n.TEXTURE_2D_ARRAY,1,1),te[n.TEXTURE_3D]=nt(n.TEXTURE_3D,n.TEXTURE_3D,1,1),r.setClear(0,0,0,1),a.setClear(1),o.setClear(0),pe(n.DEPTH_TEST),a.setFunc(Ts),q(!1),ne(au),pe(n.CULL_FACE),z(ei);function pe(U){u[U]!==!0&&(n.enable(U),u[U]=!0)}function ge(U){u[U]!==!1&&(n.disable(U),u[U]=!1)}function He(U,xe){return f[U]!==xe?(n.bindFramebuffer(U,xe),f[U]=xe,U===n.DRAW_FRAMEBUFFER&&(f[n.FRAMEBUFFER]=xe),U===n.FRAMEBUFFER&&(f[n.DRAW_FRAMEBUFFER]=xe),!0):!1}function Fe(U,xe){let me=p,Re=!1;if(U){me=h.get(xe),me===void 0&&(me=[],h.set(xe,me));const fe=U.textures;if(me.length!==fe.length||me[0]!==n.COLOR_ATTACHMENT0){for(let Z=0,Pe=fe.length;Z<Pe;Z++)me[Z]=n.COLOR_ATTACHMENT0+Z;me.length=fe.length,Re=!0}}else me[0]!==n.BACK&&(me[0]=n.BACK,Re=!0);Re&&n.drawBuffers(me)}function Be(U){return _!==U?(n.useProgram(U),_=U,!0):!1}const w={[Bi]:n.FUNC_ADD,[Wm]:n.FUNC_SUBTRACT,[Xm]:n.FUNC_REVERSE_SUBTRACT};w[Ym]=n.MIN,w[qm]=n.MAX;const R={[jm]:n.ZERO,[Km]:n.ONE,[$m]:n.SRC_COLOR,[Yo]:n.SRC_ALPHA,[ng]:n.SRC_ALPHA_SATURATE,[eg]:n.DST_COLOR,[Jm]:n.DST_ALPHA,[Zm]:n.ONE_MINUS_SRC_COLOR,[qo]:n.ONE_MINUS_SRC_ALPHA,[tg]:n.ONE_MINUS_DST_COLOR,[Qm]:n.ONE_MINUS_DST_ALPHA,[ig]:n.CONSTANT_COLOR,[sg]:n.ONE_MINUS_CONSTANT_COLOR,[rg]:n.CONSTANT_ALPHA,[ag]:n.ONE_MINUS_CONSTANT_ALPHA};function z(U,xe,me,Re,fe,Z,Pe,Ve,pt,rt){if(U===ei){M===!0&&(ge(n.BLEND),M=!1);return}if(M===!1&&(pe(n.BLEND),M=!0),U!==Gm){if(U!==g||rt!==T){if((d!==Bi||b!==Bi)&&(n.blendEquation(n.FUNC_ADD),d=Bi,b=Bi),rt)switch(U){case Ss:n.blendFuncSeparate(n.ONE,n.ONE_MINUS_SRC_ALPHA,n.ONE,n.ONE_MINUS_SRC_ALPHA);break;case ou:n.blendFunc(n.ONE,n.ONE);break;case lu:n.blendFuncSeparate(n.ZERO,n.ONE_MINUS_SRC_COLOR,n.ZERO,n.ONE);break;case cu:n.blendFuncSeparate(n.DST_COLOR,n.ONE_MINUS_SRC_ALPHA,n.ZERO,n.ONE);break;default:Qe("WebGLState: Invalid blending: ",U);break}else switch(U){case Ss:n.blendFuncSeparate(n.SRC_ALPHA,n.ONE_MINUS_SRC_ALPHA,n.ONE,n.ONE_MINUS_SRC_ALPHA);break;case ou:n.blendFuncSeparate(n.SRC_ALPHA,n.ONE,n.ONE,n.ONE);break;case lu:Qe("WebGLState: SubtractiveBlending requires material.premultipliedAlpha = true");break;case cu:Qe("WebGLState: MultiplyBlending requires material.premultipliedAlpha = true");break;default:Qe("WebGLState: Invalid blending: ",U);break}y=null,A=null,P=null,L=null,I.set(0,0,0),x=0,g=U,T=rt}return}fe=fe||xe,Z=Z||me,Pe=Pe||Re,(xe!==d||fe!==b)&&(n.blendEquationSeparate(w[xe],w[fe]),d=xe,b=fe),(me!==y||Re!==A||Z!==P||Pe!==L)&&(n.blendFuncSeparate(R[me],R[Re],R[Z],R[Pe]),y=me,A=Re,P=Z,L=Pe),(Ve.equals(I)===!1||pt!==x)&&(n.blendColor(Ve.r,Ve.g,Ve.b,pt),I.copy(Ve),x=pt),g=U,T=!1}function ee(U,xe){U.side===Pn?ge(n.CULL_FACE):pe(n.CULL_FACE);let me=U.side===Yt;xe&&(me=!me),q(me),U.blending===Ss&&U.transparent===!1?z(ei):z(U.blending,U.blendEquation,U.blendSrc,U.blendDst,U.blendEquationAlpha,U.blendSrcAlpha,U.blendDstAlpha,U.blendColor,U.blendAlpha,U.premultipliedAlpha),a.setFunc(U.depthFunc),a.setTest(U.depthTest),a.setMask(U.depthWrite),r.setMask(U.colorWrite);const Re=U.stencilWrite;o.setTest(Re),Re&&(o.setMask(U.stencilWriteMask),o.setFunc(U.stencilFunc,U.stencilRef,U.stencilFuncMask),o.setOp(U.stencilFail,U.stencilZFail,U.stencilZPass)),ue(U.polygonOffset,U.polygonOffsetFactor,U.polygonOffsetUnits),U.alphaToCoverage===!0?pe(n.SAMPLE_ALPHA_TO_COVERAGE):ge(n.SAMPLE_ALPHA_TO_COVERAGE)}function q(U){Q!==U&&(U?n.frontFace(n.CW):n.frontFace(n.CCW),Q=U)}function ne(U){U!==Vm?(pe(n.CULL_FACE),U!==D&&(U===au?n.cullFace(n.BACK):U===km?n.cullFace(n.FRONT):n.cullFace(n.FRONT_AND_BACK))):ge(n.CULL_FACE),D=U}function E(U){U!==X&&(B&&n.lineWidth(U),X=U)}function ue(U,xe,me){U?(pe(n.POLYGON_OFFSET_FILL),(Y!==xe||$!==me)&&(Y=xe,$=me,a.getReversed()&&(xe=-xe),n.polygonOffset(xe,me))):ge(n.POLYGON_OFFSET_FILL)}function re(U){U?pe(n.SCISSOR_TEST):ge(n.SCISSOR_TEST)}function J(U){U===void 0&&(U=n.TEXTURE0+W-1),le!==U&&(n.activeTexture(U),le=U)}function ae(U,xe,me){me===void 0&&(le===null?me=n.TEXTURE0+W-1:me=le);let Re=ve[me];Re===void 0&&(Re={type:void 0,texture:void 0},ve[me]=Re),(Re.type!==U||Re.texture!==xe)&&(le!==me&&(n.activeTexture(me),le=me),n.bindTexture(U,xe||te[U]),Re.type=U,Re.texture=xe)}function v(){const U=ve[le];U!==void 0&&U.type!==void 0&&(n.bindTexture(U.type,null),U.type=void 0,U.texture=void 0)}function m(){try{n.compressedTexImage2D(...arguments)}catch(U){Qe("WebGLState:",U)}}function C(){try{n.compressedTexImage3D(...arguments)}catch(U){Qe("WebGLState:",U)}}function V(){try{n.texSubImage2D(...arguments)}catch(U){Qe("WebGLState:",U)}}function K(){try{n.texSubImage3D(...arguments)}catch(U){Qe("WebGLState:",U)}}function k(){try{n.compressedTexSubImage2D(...arguments)}catch(U){Qe("WebGLState:",U)}}function _e(){try{n.compressedTexSubImage3D(...arguments)}catch(U){Qe("WebGLState:",U)}}function ce(){try{n.texStorage2D(...arguments)}catch(U){Qe("WebGLState:",U)}}function Ce(){try{n.texStorage3D(...arguments)}catch(U){Qe("WebGLState:",U)}}function Le(){try{n.texImage2D(...arguments)}catch(U){Qe("WebGLState:",U)}}function he(){try{n.texImage3D(...arguments)}catch(U){Qe("WebGLState:",U)}}function de(U){we.equals(U)===!1&&(n.scissor(U.x,U.y,U.z,U.w),we.copy(U))}function Me(U){Ge.equals(U)===!1&&(n.viewport(U.x,U.y,U.z,U.w),Ge.copy(U))}function Ee(U,xe){let me=c.get(xe);me===void 0&&(me=new WeakMap,c.set(xe,me));let Re=me.get(U);Re===void 0&&(Re=n.getUniformBlockIndex(xe,U.name),me.set(U,Re))}function Te(U,xe){const Re=c.get(xe).get(U);l.get(xe)!==Re&&(n.uniformBlockBinding(xe,Re,U.__bindingPointIndex),l.set(xe,Re))}function We(){n.disable(n.BLEND),n.disable(n.CULL_FACE),n.disable(n.DEPTH_TEST),n.disable(n.POLYGON_OFFSET_FILL),n.disable(n.SCISSOR_TEST),n.disable(n.STENCIL_TEST),n.disable(n.SAMPLE_ALPHA_TO_COVERAGE),n.blendEquation(n.FUNC_ADD),n.blendFunc(n.ONE,n.ZERO),n.blendFuncSeparate(n.ONE,n.ZERO,n.ONE,n.ZERO),n.blendColor(0,0,0,0),n.colorMask(!0,!0,!0,!0),n.clearColor(0,0,0,0),n.depthMask(!0),n.depthFunc(n.LESS),a.setReversed(!1),n.clearDepth(1),n.stencilMask(4294967295),n.stencilFunc(n.ALWAYS,0,4294967295),n.stencilOp(n.KEEP,n.KEEP,n.KEEP),n.clearStencil(0),n.cullFace(n.BACK),n.frontFace(n.CCW),n.polygonOffset(0,0),n.activeTexture(n.TEXTURE0),n.bindFramebuffer(n.FRAMEBUFFER,null),n.bindFramebuffer(n.DRAW_FRAMEBUFFER,null),n.bindFramebuffer(n.READ_FRAMEBUFFER,null),n.useProgram(null),n.lineWidth(1),n.scissor(0,0,n.canvas.width,n.canvas.height),n.viewport(0,0,n.canvas.width,n.canvas.height),u={},le=null,ve={},f={},h=new WeakMap,p=[],_=null,M=!1,g=null,d=null,y=null,A=null,b=null,P=null,L=null,I=new Ze(0,0,0),x=0,T=!1,Q=null,D=null,X=null,Y=null,$=null,we.set(0,0,n.canvas.width,n.canvas.height),Ge.set(0,0,n.canvas.width,n.canvas.height),r.reset(),a.reset(),o.reset()}return{buffers:{color:r,depth:a,stencil:o},enable:pe,disable:ge,bindFramebuffer:He,drawBuffers:Fe,useProgram:Be,setBlending:z,setMaterial:ee,setFlipSided:q,setCullFace:ne,setLineWidth:E,setPolygonOffset:ue,setScissorTest:re,activeTexture:J,bindTexture:ae,unbindTexture:v,compressedTexImage2D:m,compressedTexImage3D:C,texImage2D:Le,texImage3D:he,updateUBOMapping:Ee,uniformBlockBinding:Te,texStorage2D:ce,texStorage3D:Ce,texSubImage2D:V,texSubImage3D:K,compressedTexSubImage2D:k,compressedTexSubImage3D:_e,scissor:de,viewport:Me,reset:We}}function JM(n,e,t,i,s,r,a){const o=e.has("WEBGL_multisampled_render_to_texture")?e.get("WEBGL_multisampled_render_to_texture"):null,l=typeof navigator>"u"?!1:/OculusBrowser/g.test(navigator.userAgent),c=new ke,u=new WeakMap;let f;const h=new WeakMap;let p=!1;try{p=typeof OffscreenCanvas<"u"&&new OffscreenCanvas(1,1).getContext("2d")!==null}catch{}function _(v,m){return p?new OffscreenCanvas(v,m):Ma("canvas")}function M(v,m,C){let V=1;const K=ae(v);if((K.width>C||K.height>C)&&(V=C/Math.max(K.width,K.height)),V<1)if(typeof HTMLImageElement<"u"&&v instanceof HTMLImageElement||typeof HTMLCanvasElement<"u"&&v instanceof HTMLCanvasElement||typeof ImageBitmap<"u"&&v instanceof ImageBitmap||typeof VideoFrame<"u"&&v instanceof VideoFrame){const k=Math.floor(V*K.width),_e=Math.floor(V*K.height);f===void 0&&(f=_(k,_e));const ce=m?_(k,_e):f;return ce.width=k,ce.height=_e,ce.getContext("2d").drawImage(v,0,0,k,_e),Oe("WebGLRenderer: Texture has been resized from ("+K.width+"x"+K.height+") to ("+k+"x"+_e+")."),ce}else return"data"in v&&Oe("WebGLRenderer: Image in DataTexture is too big ("+K.width+"x"+K.height+")."),v;return v}function g(v){return v.generateMipmaps}function d(v){n.generateMipmap(v)}function y(v){return v.isWebGLCubeRenderTarget?n.TEXTURE_CUBE_MAP:v.isWebGL3DRenderTarget?n.TEXTURE_3D:v.isWebGLArrayRenderTarget||v.isCompressedArrayTexture?n.TEXTURE_2D_ARRAY:n.TEXTURE_2D}function A(v,m,C,V,K=!1){if(v!==null){if(n[v]!==void 0)return n[v];Oe("WebGLRenderer: Attempt to use non-existing WebGL internal format '"+v+"'")}let k=m;if(m===n.RED&&(C===n.FLOAT&&(k=n.R32F),C===n.HALF_FLOAT&&(k=n.R16F),C===n.UNSIGNED_BYTE&&(k=n.R8)),m===n.RED_INTEGER&&(C===n.UNSIGNED_BYTE&&(k=n.R8UI),C===n.UNSIGNED_SHORT&&(k=n.R16UI),C===n.UNSIGNED_INT&&(k=n.R32UI),C===n.BYTE&&(k=n.R8I),C===n.SHORT&&(k=n.R16I),C===n.INT&&(k=n.R32I)),m===n.RG&&(C===n.FLOAT&&(k=n.RG32F),C===n.HALF_FLOAT&&(k=n.RG16F),C===n.UNSIGNED_BYTE&&(k=n.RG8)),m===n.RG_INTEGER&&(C===n.UNSIGNED_BYTE&&(k=n.RG8UI),C===n.UNSIGNED_SHORT&&(k=n.RG16UI),C===n.UNSIGNED_INT&&(k=n.RG32UI),C===n.BYTE&&(k=n.RG8I),C===n.SHORT&&(k=n.RG16I),C===n.INT&&(k=n.RG32I)),m===n.RGB_INTEGER&&(C===n.UNSIGNED_BYTE&&(k=n.RGB8UI),C===n.UNSIGNED_SHORT&&(k=n.RGB16UI),C===n.UNSIGNED_INT&&(k=n.RGB32UI),C===n.BYTE&&(k=n.RGB8I),C===n.SHORT&&(k=n.RGB16I),C===n.INT&&(k=n.RGB32I)),m===n.RGBA_INTEGER&&(C===n.UNSIGNED_BYTE&&(k=n.RGBA8UI),C===n.UNSIGNED_SHORT&&(k=n.RGBA16UI),C===n.UNSIGNED_INT&&(k=n.RGBA32UI),C===n.BYTE&&(k=n.RGBA8I),C===n.SHORT&&(k=n.RGBA16I),C===n.INT&&(k=n.RGBA32I)),m===n.RGB&&(C===n.UNSIGNED_INT_5_9_9_9_REV&&(k=n.RGB9_E5),C===n.UNSIGNED_INT_10F_11F_11F_REV&&(k=n.R11F_G11F_B10F)),m===n.RGBA){const _e=K?va:et.getTransfer(V);C===n.FLOAT&&(k=n.RGBA32F),C===n.HALF_FLOAT&&(k=n.RGBA16F),C===n.UNSIGNED_BYTE&&(k=_e===at?n.SRGB8_ALPHA8:n.RGBA8),C===n.UNSIGNED_SHORT_4_4_4_4&&(k=n.RGBA4),C===n.UNSIGNED_SHORT_5_5_5_1&&(k=n.RGB5_A1)}return(k===n.R16F||k===n.R32F||k===n.RG16F||k===n.RG32F||k===n.RGBA16F||k===n.RGBA32F)&&e.get("EXT_color_buffer_float"),k}function b(v,m){let C;return v?m===null||m===On||m===fr?C=n.DEPTH24_STENCIL8:m===pn?C=n.DEPTH32F_STENCIL8:m===hr&&(C=n.DEPTH24_STENCIL8,Oe("DepthTexture: 16 bit depth attachment is not supported with stencil. Using 24-bit attachment.")):m===null||m===On||m===fr?C=n.DEPTH_COMPONENT24:m===pn?C=n.DEPTH_COMPONENT32F:m===hr&&(C=n.DEPTH_COMPONENT16),C}function P(v,m){return g(v)===!0||v.isFramebufferTexture&&v.minFilter!==Ct&&v.minFilter!==Ut?Math.log2(Math.max(m.width,m.height))+1:v.mipmaps!==void 0&&v.mipmaps.length>0?v.mipmaps.length:v.isCompressedTexture&&Array.isArray(v.image)?m.mipmaps.length:1}function L(v){const m=v.target;m.removeEventListener("dispose",L),x(m),m.isVideoTexture&&u.delete(m)}function I(v){const m=v.target;m.removeEventListener("dispose",I),Q(m)}function x(v){const m=i.get(v);if(m.__webglInit===void 0)return;const C=v.source,V=h.get(C);if(V){const K=V[m.__cacheKey];K.usedTimes--,K.usedTimes===0&&T(v),Object.keys(V).length===0&&h.delete(C)}i.remove(v)}function T(v){const m=i.get(v);n.deleteTexture(m.__webglTexture);const C=v.source,V=h.get(C);delete V[m.__cacheKey],a.memory.textures--}function Q(v){const m=i.get(v);if(v.depthTexture&&(v.depthTexture.dispose(),i.remove(v.depthTexture)),v.isWebGLCubeRenderTarget)for(let V=0;V<6;V++){if(Array.isArray(m.__webglFramebuffer[V]))for(let K=0;K<m.__webglFramebuffer[V].length;K++)n.deleteFramebuffer(m.__webglFramebuffer[V][K]);else n.deleteFramebuffer(m.__webglFramebuffer[V]);m.__webglDepthbuffer&&n.deleteRenderbuffer(m.__webglDepthbuffer[V])}else{if(Array.isArray(m.__webglFramebuffer))for(let V=0;V<m.__webglFramebuffer.length;V++)n.deleteFramebuffer(m.__webglFramebuffer[V]);else n.deleteFramebuffer(m.__webglFramebuffer);if(m.__webglDepthbuffer&&n.deleteRenderbuffer(m.__webglDepthbuffer),m.__webglMultisampledFramebuffer&&n.deleteFramebuffer(m.__webglMultisampledFramebuffer),m.__webglColorRenderbuffer)for(let V=0;V<m.__webglColorRenderbuffer.length;V++)m.__webglColorRenderbuffer[V]&&n.deleteRenderbuffer(m.__webglColorRenderbuffer[V]);m.__webglDepthRenderbuffer&&n.deleteRenderbuffer(m.__webglDepthRenderbuffer)}const C=v.textures;for(let V=0,K=C.length;V<K;V++){const k=i.get(C[V]);k.__webglTexture&&(n.deleteTexture(k.__webglTexture),a.memory.textures--),i.remove(C[V])}i.remove(v)}let D=0;function X(){D=0}function Y(){const v=D;return v>=s.maxTextures&&Oe("WebGLTextures: Trying to use "+v+" texture units while this GPU supports only "+s.maxTextures),D+=1,v}function $(v){const m=[];return m.push(v.wrapS),m.push(v.wrapT),m.push(v.wrapR||0),m.push(v.magFilter),m.push(v.minFilter),m.push(v.anisotropy),m.push(v.internalFormat),m.push(v.format),m.push(v.type),m.push(v.generateMipmaps),m.push(v.premultiplyAlpha),m.push(v.flipY),m.push(v.unpackAlignment),m.push(v.colorSpace),m.join()}function W(v,m){const C=i.get(v);if(v.isVideoTexture&&re(v),v.isRenderTargetTexture===!1&&v.isExternalTexture!==!0&&v.version>0&&C.__version!==v.version){const V=v.image;if(V===null)Oe("WebGLRenderer: Texture marked for update but no image data found.");else if(V.complete===!1)Oe("WebGLRenderer: Texture marked for update but image is incomplete");else{te(C,v,m);return}}else v.isExternalTexture&&(C.__webglTexture=v.sourceTexture?v.sourceTexture:null);t.bindTexture(n.TEXTURE_2D,C.__webglTexture,n.TEXTURE0+m)}function B(v,m){const C=i.get(v);if(v.isRenderTargetTexture===!1&&v.version>0&&C.__version!==v.version){te(C,v,m);return}else v.isExternalTexture&&(C.__webglTexture=v.sourceTexture?v.sourceTexture:null);t.bindTexture(n.TEXTURE_2D_ARRAY,C.__webglTexture,n.TEXTURE0+m)}function N(v,m){const C=i.get(v);if(v.isRenderTargetTexture===!1&&v.version>0&&C.__version!==v.version){te(C,v,m);return}t.bindTexture(n.TEXTURE_3D,C.__webglTexture,n.TEXTURE0+m)}function ie(v,m){const C=i.get(v);if(v.isCubeDepthTexture!==!0&&v.version>0&&C.__version!==v.version){pe(C,v,m);return}t.bindTexture(n.TEXTURE_CUBE_MAP,C.__webglTexture,n.TEXTURE0+m)}const le={[ur]:n.REPEAT,[Qn]:n.CLAMP_TO_EDGE,[tl]:n.MIRRORED_REPEAT},ve={[Ct]:n.NEAREST,[cg]:n.NEAREST_MIPMAP_NEAREST,[Tr]:n.NEAREST_MIPMAP_LINEAR,[Ut]:n.LINEAR,[to]:n.LINEAR_MIPMAP_NEAREST,[Hi]:n.LINEAR_MIPMAP_LINEAR},se={[fg]:n.NEVER,[_g]:n.ALWAYS,[dg]:n.LESS,[uc]:n.LEQUAL,[pg]:n.EQUAL,[hc]:n.GEQUAL,[mg]:n.GREATER,[gg]:n.NOTEQUAL};function oe(v,m){if(m.type===pn&&e.has("OES_texture_float_linear")===!1&&(m.magFilter===Ut||m.magFilter===to||m.magFilter===Tr||m.magFilter===Hi||m.minFilter===Ut||m.minFilter===to||m.minFilter===Tr||m.minFilter===Hi)&&Oe("WebGLRenderer: Unable to use linear filtering with floating point textures. OES_texture_float_linear not supported on this device."),n.texParameteri(v,n.TEXTURE_WRAP_S,le[m.wrapS]),n.texParameteri(v,n.TEXTURE_WRAP_T,le[m.wrapT]),(v===n.TEXTURE_3D||v===n.TEXTURE_2D_ARRAY)&&n.texParameteri(v,n.TEXTURE_WRAP_R,le[m.wrapR]),n.texParameteri(v,n.TEXTURE_MAG_FILTER,ve[m.magFilter]),n.texParameteri(v,n.TEXTURE_MIN_FILTER,ve[m.minFilter]),m.compareFunction&&(n.texParameteri(v,n.TEXTURE_COMPARE_MODE,n.COMPARE_REF_TO_TEXTURE),n.texParameteri(v,n.TEXTURE_COMPARE_FUNC,se[m.compareFunction])),e.has("EXT_texture_filter_anisotropic")===!0){if(m.magFilter===Ct||m.minFilter!==Tr&&m.minFilter!==Hi||m.type===pn&&e.has("OES_texture_float_linear")===!1)return;if(m.anisotropy>1||i.get(m).__currentAnisotropy){const C=e.get("EXT_texture_filter_anisotropic");n.texParameterf(v,C.TEXTURE_MAX_ANISOTROPY_EXT,Math.min(m.anisotropy,s.getMaxAnisotropy())),i.get(m).__currentAnisotropy=m.anisotropy}}}function we(v,m){let C=!1;v.__webglInit===void 0&&(v.__webglInit=!0,m.addEventListener("dispose",L));const V=m.source;let K=h.get(V);K===void 0&&(K={},h.set(V,K));const k=$(m);if(k!==v.__cacheKey){K[k]===void 0&&(K[k]={texture:n.createTexture(),usedTimes:0},a.memory.textures++,C=!0),K[k].usedTimes++;const _e=K[v.__cacheKey];_e!==void 0&&(K[v.__cacheKey].usedTimes--,_e.usedTimes===0&&T(m)),v.__cacheKey=k,v.__webglTexture=K[k].texture}return C}function Ge(v,m,C){return Math.floor(Math.floor(v/C)/m)}function nt(v,m,C,V){const k=v.updateRanges;if(k.length===0)t.texSubImage2D(n.TEXTURE_2D,0,0,0,m.width,m.height,C,V,m.data);else{k.sort((he,de)=>he.start-de.start);let _e=0;for(let he=1;he<k.length;he++){const de=k[_e],Me=k[he],Ee=de.start+de.count,Te=Ge(Me.start,m.width,4),We=Ge(de.start,m.width,4);Me.start<=Ee+1&&Te===We&&Ge(Me.start+Me.count-1,m.width,4)===Te?de.count=Math.max(de.count,Me.start+Me.count-de.start):(++_e,k[_e]=Me)}k.length=_e+1;const ce=n.getParameter(n.UNPACK_ROW_LENGTH),Ce=n.getParameter(n.UNPACK_SKIP_PIXELS),Le=n.getParameter(n.UNPACK_SKIP_ROWS);n.pixelStorei(n.UNPACK_ROW_LENGTH,m.width);for(let he=0,de=k.length;he<de;he++){const Me=k[he],Ee=Math.floor(Me.start/4),Te=Math.ceil(Me.count/4),We=Ee%m.width,U=Math.floor(Ee/m.width),xe=Te,me=1;n.pixelStorei(n.UNPACK_SKIP_PIXELS,We),n.pixelStorei(n.UNPACK_SKIP_ROWS,U),t.texSubImage2D(n.TEXTURE_2D,0,We,U,xe,me,C,V,m.data)}v.clearUpdateRanges(),n.pixelStorei(n.UNPACK_ROW_LENGTH,ce),n.pixelStorei(n.UNPACK_SKIP_PIXELS,Ce),n.pixelStorei(n.UNPACK_SKIP_ROWS,Le)}}function te(v,m,C){let V=n.TEXTURE_2D;(m.isDataArrayTexture||m.isCompressedArrayTexture)&&(V=n.TEXTURE_2D_ARRAY),m.isData3DTexture&&(V=n.TEXTURE_3D);const K=we(v,m),k=m.source;t.bindTexture(V,v.__webglTexture,n.TEXTURE0+C);const _e=i.get(k);if(k.version!==_e.__version||K===!0){t.activeTexture(n.TEXTURE0+C);const ce=et.getPrimaries(et.workingColorSpace),Ce=m.colorSpace===Si?null:et.getPrimaries(m.colorSpace),Le=m.colorSpace===Si||ce===Ce?n.NONE:n.BROWSER_DEFAULT_WEBGL;n.pixelStorei(n.UNPACK_FLIP_Y_WEBGL,m.flipY),n.pixelStorei(n.UNPACK_PREMULTIPLY_ALPHA_WEBGL,m.premultiplyAlpha),n.pixelStorei(n.UNPACK_ALIGNMENT,m.unpackAlignment),n.pixelStorei(n.UNPACK_COLORSPACE_CONVERSION_WEBGL,Le);let he=M(m.image,!1,s.maxTextureSize);he=J(m,he);const de=r.convert(m.format,m.colorSpace),Me=r.convert(m.type);let Ee=A(m.internalFormat,de,Me,m.colorSpace,m.isVideoTexture);oe(V,m);let Te;const We=m.mipmaps,U=m.isVideoTexture!==!0,xe=_e.__version===void 0||K===!0,me=k.dataReady,Re=P(m,he);if(m.isDepthTexture)Ee=b(m.format===Vi,m.type),xe&&(U?t.texStorage2D(n.TEXTURE_2D,1,Ee,he.width,he.height):t.texImage2D(n.TEXTURE_2D,0,Ee,he.width,he.height,0,de,Me,null));else if(m.isDataTexture)if(We.length>0){U&&xe&&t.texStorage2D(n.TEXTURE_2D,Re,Ee,We[0].width,We[0].height);for(let fe=0,Z=We.length;fe<Z;fe++)Te=We[fe],U?me&&t.texSubImage2D(n.TEXTURE_2D,fe,0,0,Te.width,Te.height,de,Me,Te.data):t.texImage2D(n.TEXTURE_2D,fe,Ee,Te.width,Te.height,0,de,Me,Te.data);m.generateMipmaps=!1}else U?(xe&&t.texStorage2D(n.TEXTURE_2D,Re,Ee,he.width,he.height),me&&nt(m,he,de,Me)):t.texImage2D(n.TEXTURE_2D,0,Ee,he.width,he.height,0,de,Me,he.data);else if(m.isCompressedTexture)if(m.isCompressedArrayTexture){U&&xe&&t.texStorage3D(n.TEXTURE_2D_ARRAY,Re,Ee,We[0].width,We[0].height,he.depth);for(let fe=0,Z=We.length;fe<Z;fe++)if(Te=We[fe],m.format!==mn)if(de!==null)if(U){if(me)if(m.layerUpdates.size>0){const Pe=Wu(Te.width,Te.height,m.format,m.type);for(const Ve of m.layerUpdates){const pt=Te.data.subarray(Ve*Pe/Te.data.BYTES_PER_ELEMENT,(Ve+1)*Pe/Te.data.BYTES_PER_ELEMENT);t.compressedTexSubImage3D(n.TEXTURE_2D_ARRAY,fe,0,0,Ve,Te.width,Te.height,1,de,pt)}m.clearLayerUpdates()}else t.compressedTexSubImage3D(n.TEXTURE_2D_ARRAY,fe,0,0,0,Te.width,Te.height,he.depth,de,Te.data)}else t.compressedTexImage3D(n.TEXTURE_2D_ARRAY,fe,Ee,Te.width,Te.height,he.depth,0,Te.data,0,0);else Oe("WebGLRenderer: Attempt to load unsupported compressed texture format in .uploadTexture()");else U?me&&t.texSubImage3D(n.TEXTURE_2D_ARRAY,fe,0,0,0,Te.width,Te.height,he.depth,de,Me,Te.data):t.texImage3D(n.TEXTURE_2D_ARRAY,fe,Ee,Te.width,Te.height,he.depth,0,de,Me,Te.data)}else{U&&xe&&t.texStorage2D(n.TEXTURE_2D,Re,Ee,We[0].width,We[0].height);for(let fe=0,Z=We.length;fe<Z;fe++)Te=We[fe],m.format!==mn?de!==null?U?me&&t.compressedTexSubImage2D(n.TEXTURE_2D,fe,0,0,Te.width,Te.height,de,Te.data):t.compressedTexImage2D(n.TEXTURE_2D,fe,Ee,Te.width,Te.height,0,Te.data):Oe("WebGLRenderer: Attempt to load unsupported compressed texture format in .uploadTexture()"):U?me&&t.texSubImage2D(n.TEXTURE_2D,fe,0,0,Te.width,Te.height,de,Me,Te.data):t.texImage2D(n.TEXTURE_2D,fe,Ee,Te.width,Te.height,0,de,Me,Te.data)}else if(m.isDataArrayTexture)if(U){if(xe&&t.texStorage3D(n.TEXTURE_2D_ARRAY,Re,Ee,he.width,he.height,he.depth),me)if(m.layerUpdates.size>0){const fe=Wu(he.width,he.height,m.format,m.type);for(const Z of m.layerUpdates){const Pe=he.data.subarray(Z*fe/he.data.BYTES_PER_ELEMENT,(Z+1)*fe/he.data.BYTES_PER_ELEMENT);t.texSubImage3D(n.TEXTURE_2D_ARRAY,0,0,0,Z,he.width,he.height,1,de,Me,Pe)}m.clearLayerUpdates()}else t.texSubImage3D(n.TEXTURE_2D_ARRAY,0,0,0,0,he.width,he.height,he.depth,de,Me,he.data)}else t.texImage3D(n.TEXTURE_2D_ARRAY,0,Ee,he.width,he.height,he.depth,0,de,Me,he.data);else if(m.isData3DTexture)U?(xe&&t.texStorage3D(n.TEXTURE_3D,Re,Ee,he.width,he.height,he.depth),me&&t.texSubImage3D(n.TEXTURE_3D,0,0,0,0,he.width,he.height,he.depth,de,Me,he.data)):t.texImage3D(n.TEXTURE_3D,0,Ee,he.width,he.height,he.depth,0,de,Me,he.data);else if(m.isFramebufferTexture){if(xe)if(U)t.texStorage2D(n.TEXTURE_2D,Re,Ee,he.width,he.height);else{let fe=he.width,Z=he.height;for(let Pe=0;Pe<Re;Pe++)t.texImage2D(n.TEXTURE_2D,Pe,Ee,fe,Z,0,de,Me,null),fe>>=1,Z>>=1}}else if(We.length>0){if(U&&xe){const fe=ae(We[0]);t.texStorage2D(n.TEXTURE_2D,Re,Ee,fe.width,fe.height)}for(let fe=0,Z=We.length;fe<Z;fe++)Te=We[fe],U?me&&t.texSubImage2D(n.TEXTURE_2D,fe,0,0,de,Me,Te):t.texImage2D(n.TEXTURE_2D,fe,Ee,de,Me,Te);m.generateMipmaps=!1}else if(U){if(xe){const fe=ae(he);t.texStorage2D(n.TEXTURE_2D,Re,Ee,fe.width,fe.height)}me&&t.texSubImage2D(n.TEXTURE_2D,0,0,0,de,Me,he)}else t.texImage2D(n.TEXTURE_2D,0,Ee,de,Me,he);g(m)&&d(V),_e.__version=k.version,m.onUpdate&&m.onUpdate(m)}v.__version=m.version}function pe(v,m,C){if(m.image.length!==6)return;const V=we(v,m),K=m.source;t.bindTexture(n.TEXTURE_CUBE_MAP,v.__webglTexture,n.TEXTURE0+C);const k=i.get(K);if(K.version!==k.__version||V===!0){t.activeTexture(n.TEXTURE0+C);const _e=et.getPrimaries(et.workingColorSpace),ce=m.colorSpace===Si?null:et.getPrimaries(m.colorSpace),Ce=m.colorSpace===Si||_e===ce?n.NONE:n.BROWSER_DEFAULT_WEBGL;n.pixelStorei(n.UNPACK_FLIP_Y_WEBGL,m.flipY),n.pixelStorei(n.UNPACK_PREMULTIPLY_ALPHA_WEBGL,m.premultiplyAlpha),n.pixelStorei(n.UNPACK_ALIGNMENT,m.unpackAlignment),n.pixelStorei(n.UNPACK_COLORSPACE_CONVERSION_WEBGL,Ce);const Le=m.isCompressedTexture||m.image[0].isCompressedTexture,he=m.image[0]&&m.image[0].isDataTexture,de=[];for(let Z=0;Z<6;Z++)!Le&&!he?de[Z]=M(m.image[Z],!0,s.maxCubemapSize):de[Z]=he?m.image[Z].image:m.image[Z],de[Z]=J(m,de[Z]);const Me=de[0],Ee=r.convert(m.format,m.colorSpace),Te=r.convert(m.type),We=A(m.internalFormat,Ee,Te,m.colorSpace),U=m.isVideoTexture!==!0,xe=k.__version===void 0||V===!0,me=K.dataReady;let Re=P(m,Me);oe(n.TEXTURE_CUBE_MAP,m);let fe;if(Le){U&&xe&&t.texStorage2D(n.TEXTURE_CUBE_MAP,Re,We,Me.width,Me.height);for(let Z=0;Z<6;Z++){fe=de[Z].mipmaps;for(let Pe=0;Pe<fe.length;Pe++){const Ve=fe[Pe];m.format!==mn?Ee!==null?U?me&&t.compressedTexSubImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+Z,Pe,0,0,Ve.width,Ve.height,Ee,Ve.data):t.compressedTexImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+Z,Pe,We,Ve.width,Ve.height,0,Ve.data):Oe("WebGLRenderer: Attempt to load unsupported compressed texture format in .setTextureCube()"):U?me&&t.texSubImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+Z,Pe,0,0,Ve.width,Ve.height,Ee,Te,Ve.data):t.texImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+Z,Pe,We,Ve.width,Ve.height,0,Ee,Te,Ve.data)}}}else{if(fe=m.mipmaps,U&&xe){fe.length>0&&Re++;const Z=ae(de[0]);t.texStorage2D(n.TEXTURE_CUBE_MAP,Re,We,Z.width,Z.height)}for(let Z=0;Z<6;Z++)if(he){U?me&&t.texSubImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+Z,0,0,0,de[Z].width,de[Z].height,Ee,Te,de[Z].data):t.texImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+Z,0,We,de[Z].width,de[Z].height,0,Ee,Te,de[Z].data);for(let Pe=0;Pe<fe.length;Pe++){const pt=fe[Pe].image[Z].image;U?me&&t.texSubImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+Z,Pe+1,0,0,pt.width,pt.height,Ee,Te,pt.data):t.texImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+Z,Pe+1,We,pt.width,pt.height,0,Ee,Te,pt.data)}}else{U?me&&t.texSubImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+Z,0,0,0,Ee,Te,de[Z]):t.texImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+Z,0,We,Ee,Te,de[Z]);for(let Pe=0;Pe<fe.length;Pe++){const Ve=fe[Pe];U?me&&t.texSubImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+Z,Pe+1,0,0,Ee,Te,Ve.image[Z]):t.texImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+Z,Pe+1,We,Ee,Te,Ve.image[Z])}}}g(m)&&d(n.TEXTURE_CUBE_MAP),k.__version=K.version,m.onUpdate&&m.onUpdate(m)}v.__version=m.version}function ge(v,m,C,V,K,k){const _e=r.convert(C.format,C.colorSpace),ce=r.convert(C.type),Ce=A(C.internalFormat,_e,ce,C.colorSpace),Le=i.get(m),he=i.get(C);if(he.__renderTarget=m,!Le.__hasExternalTextures){const de=Math.max(1,m.width>>k),Me=Math.max(1,m.height>>k);K===n.TEXTURE_3D||K===n.TEXTURE_2D_ARRAY?t.texImage3D(K,k,Ce,de,Me,m.depth,0,_e,ce,null):t.texImage2D(K,k,Ce,de,Me,0,_e,ce,null)}t.bindFramebuffer(n.FRAMEBUFFER,v),ue(m)?o.framebufferTexture2DMultisampleEXT(n.FRAMEBUFFER,V,K,he.__webglTexture,0,E(m)):(K===n.TEXTURE_2D||K>=n.TEXTURE_CUBE_MAP_POSITIVE_X&&K<=n.TEXTURE_CUBE_MAP_NEGATIVE_Z)&&n.framebufferTexture2D(n.FRAMEBUFFER,V,K,he.__webglTexture,k),t.bindFramebuffer(n.FRAMEBUFFER,null)}function He(v,m,C){if(n.bindRenderbuffer(n.RENDERBUFFER,v),m.depthBuffer){const V=m.depthTexture,K=V&&V.isDepthTexture?V.type:null,k=b(m.stencilBuffer,K),_e=m.stencilBuffer?n.DEPTH_STENCIL_ATTACHMENT:n.DEPTH_ATTACHMENT;ue(m)?o.renderbufferStorageMultisampleEXT(n.RENDERBUFFER,E(m),k,m.width,m.height):C?n.renderbufferStorageMultisample(n.RENDERBUFFER,E(m),k,m.width,m.height):n.renderbufferStorage(n.RENDERBUFFER,k,m.width,m.height),n.framebufferRenderbuffer(n.FRAMEBUFFER,_e,n.RENDERBUFFER,v)}else{const V=m.textures;for(let K=0;K<V.length;K++){const k=V[K],_e=r.convert(k.format,k.colorSpace),ce=r.convert(k.type),Ce=A(k.internalFormat,_e,ce,k.colorSpace);ue(m)?o.renderbufferStorageMultisampleEXT(n.RENDERBUFFER,E(m),Ce,m.width,m.height):C?n.renderbufferStorageMultisample(n.RENDERBUFFER,E(m),Ce,m.width,m.height):n.renderbufferStorage(n.RENDERBUFFER,Ce,m.width,m.height)}}n.bindRenderbuffer(n.RENDERBUFFER,null)}function Fe(v,m,C){const V=m.isWebGLCubeRenderTarget===!0;if(t.bindFramebuffer(n.FRAMEBUFFER,v),!(m.depthTexture&&m.depthTexture.isDepthTexture))throw new Error("renderTarget.depthTexture must be an instance of THREE.DepthTexture");const K=i.get(m.depthTexture);if(K.__renderTarget=m,(!K.__webglTexture||m.depthTexture.image.width!==m.width||m.depthTexture.image.height!==m.height)&&(m.depthTexture.image.width=m.width,m.depthTexture.image.height=m.height,m.depthTexture.needsUpdate=!0),V){if(K.__webglInit===void 0&&(K.__webglInit=!0,m.depthTexture.addEventListener("dispose",L)),K.__webglTexture===void 0){K.__webglTexture=n.createTexture(),t.bindTexture(n.TEXTURE_CUBE_MAP,K.__webglTexture),oe(n.TEXTURE_CUBE_MAP,m.depthTexture);const Le=r.convert(m.depthTexture.format),he=r.convert(m.depthTexture.type);let de;m.depthTexture.format===ai?de=n.DEPTH_COMPONENT24:m.depthTexture.format===Vi&&(de=n.DEPTH24_STENCIL8);for(let Me=0;Me<6;Me++)n.texImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+Me,0,de,m.width,m.height,0,Le,he,null)}}else W(m.depthTexture,0);const k=K.__webglTexture,_e=E(m),ce=V?n.TEXTURE_CUBE_MAP_POSITIVE_X+C:n.TEXTURE_2D,Ce=m.depthTexture.format===Vi?n.DEPTH_STENCIL_ATTACHMENT:n.DEPTH_ATTACHMENT;if(m.depthTexture.format===ai)ue(m)?o.framebufferTexture2DMultisampleEXT(n.FRAMEBUFFER,Ce,ce,k,0,_e):n.framebufferTexture2D(n.FRAMEBUFFER,Ce,ce,k,0);else if(m.depthTexture.format===Vi)ue(m)?o.framebufferTexture2DMultisampleEXT(n.FRAMEBUFFER,Ce,ce,k,0,_e):n.framebufferTexture2D(n.FRAMEBUFFER,Ce,ce,k,0);else throw new Error("Unknown depthTexture format")}function Be(v){const m=i.get(v),C=v.isWebGLCubeRenderTarget===!0;if(m.__boundDepthTexture!==v.depthTexture){const V=v.depthTexture;if(m.__depthDisposeCallback&&m.__depthDisposeCallback(),V){const K=()=>{delete m.__boundDepthTexture,delete m.__depthDisposeCallback,V.removeEventListener("dispose",K)};V.addEventListener("dispose",K),m.__depthDisposeCallback=K}m.__boundDepthTexture=V}if(v.depthTexture&&!m.__autoAllocateDepthBuffer)if(C)for(let V=0;V<6;V++)Fe(m.__webglFramebuffer[V],v,V);else{const V=v.texture.mipmaps;V&&V.length>0?Fe(m.__webglFramebuffer[0],v,0):Fe(m.__webglFramebuffer,v,0)}else if(C){m.__webglDepthbuffer=[];for(let V=0;V<6;V++)if(t.bindFramebuffer(n.FRAMEBUFFER,m.__webglFramebuffer[V]),m.__webglDepthbuffer[V]===void 0)m.__webglDepthbuffer[V]=n.createRenderbuffer(),He(m.__webglDepthbuffer[V],v,!1);else{const K=v.stencilBuffer?n.DEPTH_STENCIL_ATTACHMENT:n.DEPTH_ATTACHMENT,k=m.__webglDepthbuffer[V];n.bindRenderbuffer(n.RENDERBUFFER,k),n.framebufferRenderbuffer(n.FRAMEBUFFER,K,n.RENDERBUFFER,k)}}else{const V=v.texture.mipmaps;if(V&&V.length>0?t.bindFramebuffer(n.FRAMEBUFFER,m.__webglFramebuffer[0]):t.bindFramebuffer(n.FRAMEBUFFER,m.__webglFramebuffer),m.__webglDepthbuffer===void 0)m.__webglDepthbuffer=n.createRenderbuffer(),He(m.__webglDepthbuffer,v,!1);else{const K=v.stencilBuffer?n.DEPTH_STENCIL_ATTACHMENT:n.DEPTH_ATTACHMENT,k=m.__webglDepthbuffer;n.bindRenderbuffer(n.RENDERBUFFER,k),n.framebufferRenderbuffer(n.FRAMEBUFFER,K,n.RENDERBUFFER,k)}}t.bindFramebuffer(n.FRAMEBUFFER,null)}function w(v,m,C){const V=i.get(v);m!==void 0&&ge(V.__webglFramebuffer,v,v.texture,n.COLOR_ATTACHMENT0,n.TEXTURE_2D,0),C!==void 0&&Be(v)}function R(v){const m=v.texture,C=i.get(v),V=i.get(m);v.addEventListener("dispose",I);const K=v.textures,k=v.isWebGLCubeRenderTarget===!0,_e=K.length>1;if(_e||(V.__webglTexture===void 0&&(V.__webglTexture=n.createTexture()),V.__version=m.version,a.memory.textures++),k){C.__webglFramebuffer=[];for(let ce=0;ce<6;ce++)if(m.mipmaps&&m.mipmaps.length>0){C.__webglFramebuffer[ce]=[];for(let Ce=0;Ce<m.mipmaps.length;Ce++)C.__webglFramebuffer[ce][Ce]=n.createFramebuffer()}else C.__webglFramebuffer[ce]=n.createFramebuffer()}else{if(m.mipmaps&&m.mipmaps.length>0){C.__webglFramebuffer=[];for(let ce=0;ce<m.mipmaps.length;ce++)C.__webglFramebuffer[ce]=n.createFramebuffer()}else C.__webglFramebuffer=n.createFramebuffer();if(_e)for(let ce=0,Ce=K.length;ce<Ce;ce++){const Le=i.get(K[ce]);Le.__webglTexture===void 0&&(Le.__webglTexture=n.createTexture(),a.memory.textures++)}if(v.samples>0&&ue(v)===!1){C.__webglMultisampledFramebuffer=n.createFramebuffer(),C.__webglColorRenderbuffer=[],t.bindFramebuffer(n.FRAMEBUFFER,C.__webglMultisampledFramebuffer);for(let ce=0;ce<K.length;ce++){const Ce=K[ce];C.__webglColorRenderbuffer[ce]=n.createRenderbuffer(),n.bindRenderbuffer(n.RENDERBUFFER,C.__webglColorRenderbuffer[ce]);const Le=r.convert(Ce.format,Ce.colorSpace),he=r.convert(Ce.type),de=A(Ce.internalFormat,Le,he,Ce.colorSpace,v.isXRRenderTarget===!0),Me=E(v);n.renderbufferStorageMultisample(n.RENDERBUFFER,Me,de,v.width,v.height),n.framebufferRenderbuffer(n.FRAMEBUFFER,n.COLOR_ATTACHMENT0+ce,n.RENDERBUFFER,C.__webglColorRenderbuffer[ce])}n.bindRenderbuffer(n.RENDERBUFFER,null),v.depthBuffer&&(C.__webglDepthRenderbuffer=n.createRenderbuffer(),He(C.__webglDepthRenderbuffer,v,!0)),t.bindFramebuffer(n.FRAMEBUFFER,null)}}if(k){t.bindTexture(n.TEXTURE_CUBE_MAP,V.__webglTexture),oe(n.TEXTURE_CUBE_MAP,m);for(let ce=0;ce<6;ce++)if(m.mipmaps&&m.mipmaps.length>0)for(let Ce=0;Ce<m.mipmaps.length;Ce++)ge(C.__webglFramebuffer[ce][Ce],v,m,n.COLOR_ATTACHMENT0,n.TEXTURE_CUBE_MAP_POSITIVE_X+ce,Ce);else ge(C.__webglFramebuffer[ce],v,m,n.COLOR_ATTACHMENT0,n.TEXTURE_CUBE_MAP_POSITIVE_X+ce,0);g(m)&&d(n.TEXTURE_CUBE_MAP),t.unbindTexture()}else if(_e){for(let ce=0,Ce=K.length;ce<Ce;ce++){const Le=K[ce],he=i.get(Le);let de=n.TEXTURE_2D;(v.isWebGL3DRenderTarget||v.isWebGLArrayRenderTarget)&&(de=v.isWebGL3DRenderTarget?n.TEXTURE_3D:n.TEXTURE_2D_ARRAY),t.bindTexture(de,he.__webglTexture),oe(de,Le),ge(C.__webglFramebuffer,v,Le,n.COLOR_ATTACHMENT0+ce,de,0),g(Le)&&d(de)}t.unbindTexture()}else{let ce=n.TEXTURE_2D;if((v.isWebGL3DRenderTarget||v.isWebGLArrayRenderTarget)&&(ce=v.isWebGL3DRenderTarget?n.TEXTURE_3D:n.TEXTURE_2D_ARRAY),t.bindTexture(ce,V.__webglTexture),oe(ce,m),m.mipmaps&&m.mipmaps.length>0)for(let Ce=0;Ce<m.mipmaps.length;Ce++)ge(C.__webglFramebuffer[Ce],v,m,n.COLOR_ATTACHMENT0,ce,Ce);else ge(C.__webglFramebuffer,v,m,n.COLOR_ATTACHMENT0,ce,0);g(m)&&d(ce),t.unbindTexture()}v.depthBuffer&&Be(v)}function z(v){const m=v.textures;for(let C=0,V=m.length;C<V;C++){const K=m[C];if(g(K)){const k=y(v),_e=i.get(K).__webglTexture;t.bindTexture(k,_e),d(k),t.unbindTexture()}}}const ee=[],q=[];function ne(v){if(v.samples>0){if(ue(v)===!1){const m=v.textures,C=v.width,V=v.height;let K=n.COLOR_BUFFER_BIT;const k=v.stencilBuffer?n.DEPTH_STENCIL_ATTACHMENT:n.DEPTH_ATTACHMENT,_e=i.get(v),ce=m.length>1;if(ce)for(let Le=0;Le<m.length;Le++)t.bindFramebuffer(n.FRAMEBUFFER,_e.__webglMultisampledFramebuffer),n.framebufferRenderbuffer(n.FRAMEBUFFER,n.COLOR_ATTACHMENT0+Le,n.RENDERBUFFER,null),t.bindFramebuffer(n.FRAMEBUFFER,_e.__webglFramebuffer),n.framebufferTexture2D(n.DRAW_FRAMEBUFFER,n.COLOR_ATTACHMENT0+Le,n.TEXTURE_2D,null,0);t.bindFramebuffer(n.READ_FRAMEBUFFER,_e.__webglMultisampledFramebuffer);const Ce=v.texture.mipmaps;Ce&&Ce.length>0?t.bindFramebuffer(n.DRAW_FRAMEBUFFER,_e.__webglFramebuffer[0]):t.bindFramebuffer(n.DRAW_FRAMEBUFFER,_e.__webglFramebuffer);for(let Le=0;Le<m.length;Le++){if(v.resolveDepthBuffer&&(v.depthBuffer&&(K|=n.DEPTH_BUFFER_BIT),v.stencilBuffer&&v.resolveStencilBuffer&&(K|=n.STENCIL_BUFFER_BIT)),ce){n.framebufferRenderbuffer(n.READ_FRAMEBUFFER,n.COLOR_ATTACHMENT0,n.RENDERBUFFER,_e.__webglColorRenderbuffer[Le]);const he=i.get(m[Le]).__webglTexture;n.framebufferTexture2D(n.DRAW_FRAMEBUFFER,n.COLOR_ATTACHMENT0,n.TEXTURE_2D,he,0)}n.blitFramebuffer(0,0,C,V,0,0,C,V,K,n.NEAREST),l===!0&&(ee.length=0,q.length=0,ee.push(n.COLOR_ATTACHMENT0+Le),v.depthBuffer&&v.resolveDepthBuffer===!1&&(ee.push(k),q.push(k),n.invalidateFramebuffer(n.DRAW_FRAMEBUFFER,q)),n.invalidateFramebuffer(n.READ_FRAMEBUFFER,ee))}if(t.bindFramebuffer(n.READ_FRAMEBUFFER,null),t.bindFramebuffer(n.DRAW_FRAMEBUFFER,null),ce)for(let Le=0;Le<m.length;Le++){t.bindFramebuffer(n.FRAMEBUFFER,_e.__webglMultisampledFramebuffer),n.framebufferRenderbuffer(n.FRAMEBUFFER,n.COLOR_ATTACHMENT0+Le,n.RENDERBUFFER,_e.__webglColorRenderbuffer[Le]);const he=i.get(m[Le]).__webglTexture;t.bindFramebuffer(n.FRAMEBUFFER,_e.__webglFramebuffer),n.framebufferTexture2D(n.DRAW_FRAMEBUFFER,n.COLOR_ATTACHMENT0+Le,n.TEXTURE_2D,he,0)}t.bindFramebuffer(n.DRAW_FRAMEBUFFER,_e.__webglMultisampledFramebuffer)}else if(v.depthBuffer&&v.resolveDepthBuffer===!1&&l){const m=v.stencilBuffer?n.DEPTH_STENCIL_ATTACHMENT:n.DEPTH_ATTACHMENT;n.invalidateFramebuffer(n.DRAW_FRAMEBUFFER,[m])}}}function E(v){return Math.min(s.maxSamples,v.samples)}function ue(v){const m=i.get(v);return v.samples>0&&e.has("WEBGL_multisampled_render_to_texture")===!0&&m.__useRenderToTexture!==!1}function re(v){const m=a.render.frame;u.get(v)!==m&&(u.set(v,m),v.update())}function J(v,m){const C=v.colorSpace,V=v.format,K=v.type;return v.isCompressedTexture===!0||v.isVideoTexture===!0||C!==Cs&&C!==Si&&(et.getTransfer(C)===at?(V!==mn||K!==Jt)&&Oe("WebGLTextures: sRGB encoded textures have to use RGBAFormat and UnsignedByteType."):Qe("WebGLTextures: Unsupported texture color space:",C)),m}function ae(v){return typeof HTMLImageElement<"u"&&v instanceof HTMLImageElement?(c.width=v.naturalWidth||v.width,c.height=v.naturalHeight||v.height):typeof VideoFrame<"u"&&v instanceof VideoFrame?(c.width=v.displayWidth,c.height=v.displayHeight):(c.width=v.width,c.height=v.height),c}this.allocateTextureUnit=Y,this.resetTextureUnits=X,this.setTexture2D=W,this.setTexture2DArray=B,this.setTexture3D=N,this.setTextureCube=ie,this.rebindTextures=w,this.setupRenderTarget=R,this.updateRenderTargetMipmap=z,this.updateMultisampleRenderTarget=ne,this.setupDepthRenderbuffer=Be,this.setupFrameBufferTexture=ge,this.useMultisampledRTT=ue,this.isReversedDepthBuffer=function(){return t.buffers.depth.getReversed()}}function QM(n,e){function t(i,s=Si){let r;const a=et.getTransfer(s);if(i===Jt)return n.UNSIGNED_BYTE;if(i===sc)return n.UNSIGNED_SHORT_4_4_4_4;if(i===rc)return n.UNSIGNED_SHORT_5_5_5_1;if(i===Bf)return n.UNSIGNED_INT_5_9_9_9_REV;if(i===zf)return n.UNSIGNED_INT_10F_11F_11F_REV;if(i===Ff)return n.BYTE;if(i===Of)return n.SHORT;if(i===hr)return n.UNSIGNED_SHORT;if(i===ic)return n.INT;if(i===On)return n.UNSIGNED_INT;if(i===pn)return n.FLOAT;if(i===ri)return n.HALF_FLOAT;if(i===Hf)return n.ALPHA;if(i===Vf)return n.RGB;if(i===mn)return n.RGBA;if(i===ai)return n.DEPTH_COMPONENT;if(i===Vi)return n.DEPTH_STENCIL;if(i===ac)return n.RED;if(i===oc)return n.RED_INTEGER;if(i===ws)return n.RG;if(i===lc)return n.RG_INTEGER;if(i===cc)return n.RGBA_INTEGER;if(i===sa||i===ra||i===aa||i===oa)if(a===at)if(r=e.get("WEBGL_compressed_texture_s3tc_srgb"),r!==null){if(i===sa)return r.COMPRESSED_SRGB_S3TC_DXT1_EXT;if(i===ra)return r.COMPRESSED_SRGB_ALPHA_S3TC_DXT1_EXT;if(i===aa)return r.COMPRESSED_SRGB_ALPHA_S3TC_DXT3_EXT;if(i===oa)return r.COMPRESSED_SRGB_ALPHA_S3TC_DXT5_EXT}else return null;else if(r=e.get("WEBGL_compressed_texture_s3tc"),r!==null){if(i===sa)return r.COMPRESSED_RGB_S3TC_DXT1_EXT;if(i===ra)return r.COMPRESSED_RGBA_S3TC_DXT1_EXT;if(i===aa)return r.COMPRESSED_RGBA_S3TC_DXT3_EXT;if(i===oa)return r.COMPRESSED_RGBA_S3TC_DXT5_EXT}else return null;if(i===nl||i===il||i===sl||i===rl)if(r=e.get("WEBGL_compressed_texture_pvrtc"),r!==null){if(i===nl)return r.COMPRESSED_RGB_PVRTC_4BPPV1_IMG;if(i===il)return r.COMPRESSED_RGB_PVRTC_2BPPV1_IMG;if(i===sl)return r.COMPRESSED_RGBA_PVRTC_4BPPV1_IMG;if(i===rl)return r.COMPRESSED_RGBA_PVRTC_2BPPV1_IMG}else return null;if(i===al||i===ol||i===ll||i===cl||i===ul||i===hl||i===fl)if(r=e.get("WEBGL_compressed_texture_etc"),r!==null){if(i===al||i===ol)return a===at?r.COMPRESSED_SRGB8_ETC2:r.COMPRESSED_RGB8_ETC2;if(i===ll)return a===at?r.COMPRESSED_SRGB8_ALPHA8_ETC2_EAC:r.COMPRESSED_RGBA8_ETC2_EAC;if(i===cl)return r.COMPRESSED_R11_EAC;if(i===ul)return r.COMPRESSED_SIGNED_R11_EAC;if(i===hl)return r.COMPRESSED_RG11_EAC;if(i===fl)return r.COMPRESSED_SIGNED_RG11_EAC}else return null;if(i===dl||i===pl||i===ml||i===gl||i===_l||i===xl||i===vl||i===Ml||i===Sl||i===yl||i===bl||i===El||i===Tl||i===Al)if(r=e.get("WEBGL_compressed_texture_astc"),r!==null){if(i===dl)return a===at?r.COMPRESSED_SRGB8_ALPHA8_ASTC_4x4_KHR:r.COMPRESSED_RGBA_ASTC_4x4_KHR;if(i===pl)return a===at?r.COMPRESSED_SRGB8_ALPHA8_ASTC_5x4_KHR:r.COMPRESSED_RGBA_ASTC_5x4_KHR;if(i===ml)return a===at?r.COMPRESSED_SRGB8_ALPHA8_ASTC_5x5_KHR:r.COMPRESSED_RGBA_ASTC_5x5_KHR;if(i===gl)return a===at?r.COMPRESSED_SRGB8_ALPHA8_ASTC_6x5_KHR:r.COMPRESSED_RGBA_ASTC_6x5_KHR;if(i===_l)return a===at?r.COMPRESSED_SRGB8_ALPHA8_ASTC_6x6_KHR:r.COMPRESSED_RGBA_ASTC_6x6_KHR;if(i===xl)return a===at?r.COMPRESSED_SRGB8_ALPHA8_ASTC_8x5_KHR:r.COMPRESSED_RGBA_ASTC_8x5_KHR;if(i===vl)return a===at?r.COMPRESSED_SRGB8_ALPHA8_ASTC_8x6_KHR:r.COMPRESSED_RGBA_ASTC_8x6_KHR;if(i===Ml)return a===at?r.COMPRESSED_SRGB8_ALPHA8_ASTC_8x8_KHR:r.COMPRESSED_RGBA_ASTC_8x8_KHR;if(i===Sl)return a===at?r.COMPRESSED_SRGB8_ALPHA8_ASTC_10x5_KHR:r.COMPRESSED_RGBA_ASTC_10x5_KHR;if(i===yl)return a===at?r.COMPRESSED_SRGB8_ALPHA8_ASTC_10x6_KHR:r.COMPRESSED_RGBA_ASTC_10x6_KHR;if(i===bl)return a===at?r.COMPRESSED_SRGB8_ALPHA8_ASTC_10x8_KHR:r.COMPRESSED_RGBA_ASTC_10x8_KHR;if(i===El)return a===at?r.COMPRESSED_SRGB8_ALPHA8_ASTC_10x10_KHR:r.COMPRESSED_RGBA_ASTC_10x10_KHR;if(i===Tl)return a===at?r.COMPRESSED_SRGB8_ALPHA8_ASTC_12x10_KHR:r.COMPRESSED_RGBA_ASTC_12x10_KHR;if(i===Al)return a===at?r.COMPRESSED_SRGB8_ALPHA8_ASTC_12x12_KHR:r.COMPRESSED_RGBA_ASTC_12x12_KHR}else return null;if(i===wl||i===Cl||i===Rl)if(r=e.get("EXT_texture_compression_bptc"),r!==null){if(i===wl)return a===at?r.COMPRESSED_SRGB_ALPHA_BPTC_UNORM_EXT:r.COMPRESSED_RGBA_BPTC_UNORM_EXT;if(i===Cl)return r.COMPRESSED_RGB_BPTC_SIGNED_FLOAT_EXT;if(i===Rl)return r.COMPRESSED_RGB_BPTC_UNSIGNED_FLOAT_EXT}else return null;if(i===Pl||i===Dl||i===Ll||i===Il)if(r=e.get("EXT_texture_compression_rgtc"),r!==null){if(i===Pl)return r.COMPRESSED_RED_RGTC1_EXT;if(i===Dl)return r.COMPRESSED_SIGNED_RED_RGTC1_EXT;if(i===Ll)return r.COMPRESSED_RED_GREEN_RGTC2_EXT;if(i===Il)return r.COMPRESSED_SIGNED_RED_GREEN_RGTC2_EXT}else return null;return i===fr?n.UNSIGNED_INT_24_8:n[i]!==void 0?n[i]:null}return{convert:t}}const eS=`
void main() {

	gl_Position = vec4( position, 1.0 );

}`,tS=`
uniform sampler2DArray depthColor;
uniform float depthWidth;
uniform float depthHeight;

void main() {

	vec2 coord = vec2( gl_FragCoord.x / depthWidth, gl_FragCoord.y / depthHeight );

	if ( coord.x >= 1.0 ) {

		gl_FragDepth = texture( depthColor, vec3( coord.x - 1.0, coord.y, 1 ) ).r;

	} else {

		gl_FragDepth = texture( depthColor, vec3( coord.x, coord.y, 0 ) ).r;

	}

}`;class nS{constructor(){this.texture=null,this.mesh=null,this.depthNear=0,this.depthFar=0}init(e,t){if(this.texture===null){const i=new Jf(e.texture);(e.depthNear!==t.depthNear||e.depthFar!==t.depthFar)&&(this.depthNear=e.depthNear,this.depthFar=e.depthFar),this.texture=i}}getMesh(e){if(this.texture!==null&&this.mesh===null){const t=e.cameras[0].viewport,i=new zn({vertexShader:eS,fragmentShader:tS,uniforms:{depthColor:{value:this.texture},depthWidth:{value:t.z},depthHeight:{value:t.w}}});this.mesh=new qt(new vr(20,20),i)}return this.mesh}reset(){this.texture=null,this.mesh=null}getDepthTexture(){return this.texture}}class iS extends Yi{constructor(e,t){super();const i=this;let s=null,r=1,a=null,o="local-floor",l=1,c=null,u=null,f=null,h=null,p=null,_=null;const M=typeof XRWebGLBinding<"u",g=new nS,d={},y=t.getContextAttributes();let A=null,b=null;const P=[],L=[],I=new ke;let x=null;const T=new an;T.viewport=new _t;const Q=new an;Q.viewport=new _t;const D=[T,Q],X=new u_;let Y=null,$=null;this.cameraAutoUpdate=!0,this.enabled=!1,this.isPresenting=!1,this.getController=function(te){let pe=P[te];return pe===void 0&&(pe=new lo,P[te]=pe),pe.getTargetRaySpace()},this.getControllerGrip=function(te){let pe=P[te];return pe===void 0&&(pe=new lo,P[te]=pe),pe.getGripSpace()},this.getHand=function(te){let pe=P[te];return pe===void 0&&(pe=new lo,P[te]=pe),pe.getHandSpace()};function W(te){const pe=L.indexOf(te.inputSource);if(pe===-1)return;const ge=P[pe];ge!==void 0&&(ge.update(te.inputSource,te.frame,c||a),ge.dispatchEvent({type:te.type,data:te.inputSource}))}function B(){s.removeEventListener("select",W),s.removeEventListener("selectstart",W),s.removeEventListener("selectend",W),s.removeEventListener("squeeze",W),s.removeEventListener("squeezestart",W),s.removeEventListener("squeezeend",W),s.removeEventListener("end",B),s.removeEventListener("inputsourceschange",N);for(let te=0;te<P.length;te++){const pe=L[te];pe!==null&&(L[te]=null,P[te].disconnect(pe))}Y=null,$=null,g.reset();for(const te in d)delete d[te];e.setRenderTarget(A),p=null,h=null,f=null,s=null,b=null,nt.stop(),i.isPresenting=!1,e.setPixelRatio(x),e.setSize(I.width,I.height,!1),i.dispatchEvent({type:"sessionend"})}this.setFramebufferScaleFactor=function(te){r=te,i.isPresenting===!0&&Oe("WebXRManager: Cannot change framebuffer scale while presenting.")},this.setReferenceSpaceType=function(te){o=te,i.isPresenting===!0&&Oe("WebXRManager: Cannot change reference space type while presenting.")},this.getReferenceSpace=function(){return c||a},this.setReferenceSpace=function(te){c=te},this.getBaseLayer=function(){return h!==null?h:p},this.getBinding=function(){return f===null&&M&&(f=new XRWebGLBinding(s,t)),f},this.getFrame=function(){return _},this.getSession=function(){return s},this.setSession=async function(te){if(s=te,s!==null){if(A=e.getRenderTarget(),s.addEventListener("select",W),s.addEventListener("selectstart",W),s.addEventListener("selectend",W),s.addEventListener("squeeze",W),s.addEventListener("squeezestart",W),s.addEventListener("squeezeend",W),s.addEventListener("end",B),s.addEventListener("inputsourceschange",N),y.xrCompatible!==!0&&await t.makeXRCompatible(),x=e.getPixelRatio(),e.getSize(I),M&&"createProjectionLayer"in XRWebGLBinding.prototype){let ge=null,He=null,Fe=null;y.depth&&(Fe=y.stencil?t.DEPTH24_STENCIL8:t.DEPTH_COMPONENT24,ge=y.stencil?Vi:ai,He=y.stencil?fr:On);const Be={colorFormat:t.RGBA8,depthFormat:Fe,scaleFactor:r};f=this.getBinding(),h=f.createProjectionLayer(Be),s.updateRenderState({layers:[h]}),e.setPixelRatio(1),e.setSize(h.textureWidth,h.textureHeight,!1),b=new Un(h.textureWidth,h.textureHeight,{format:mn,type:Jt,depthTexture:new pr(h.textureWidth,h.textureHeight,He,void 0,void 0,void 0,void 0,void 0,void 0,ge),stencilBuffer:y.stencil,colorSpace:e.outputColorSpace,samples:y.antialias?4:0,resolveDepthBuffer:h.ignoreDepthValues===!1,resolveStencilBuffer:h.ignoreDepthValues===!1})}else{const ge={antialias:y.antialias,alpha:!0,depth:y.depth,stencil:y.stencil,framebufferScaleFactor:r};p=new XRWebGLLayer(s,t,ge),s.updateRenderState({baseLayer:p}),e.setPixelRatio(1),e.setSize(p.framebufferWidth,p.framebufferHeight,!1),b=new Un(p.framebufferWidth,p.framebufferHeight,{format:mn,type:Jt,colorSpace:e.outputColorSpace,stencilBuffer:y.stencil,resolveDepthBuffer:p.ignoreDepthValues===!1,resolveStencilBuffer:p.ignoreDepthValues===!1})}b.isXRRenderTarget=!0,this.setFoveation(l),c=null,a=await s.requestReferenceSpace(o),nt.setContext(s),nt.start(),i.isPresenting=!0,i.dispatchEvent({type:"sessionstart"})}},this.getEnvironmentBlendMode=function(){if(s!==null)return s.environmentBlendMode},this.getDepthTexture=function(){return g.getDepthTexture()};function N(te){for(let pe=0;pe<te.removed.length;pe++){const ge=te.removed[pe],He=L.indexOf(ge);He>=0&&(L[He]=null,P[He].disconnect(ge))}for(let pe=0;pe<te.added.length;pe++){const ge=te.added[pe];let He=L.indexOf(ge);if(He===-1){for(let Be=0;Be<P.length;Be++)if(Be>=L.length){L.push(ge),He=Be;break}else if(L[Be]===null){L[Be]=ge,He=Be;break}if(He===-1)break}const Fe=P[He];Fe&&Fe.connect(ge)}}const ie=new O,le=new O;function ve(te,pe,ge){ie.setFromMatrixPosition(pe.matrixWorld),le.setFromMatrixPosition(ge.matrixWorld);const He=ie.distanceTo(le),Fe=pe.projectionMatrix.elements,Be=ge.projectionMatrix.elements,w=Fe[14]/(Fe[10]-1),R=Fe[14]/(Fe[10]+1),z=(Fe[9]+1)/Fe[5],ee=(Fe[9]-1)/Fe[5],q=(Fe[8]-1)/Fe[0],ne=(Be[8]+1)/Be[0],E=w*q,ue=w*ne,re=He/(-q+ne),J=re*-q;if(pe.matrixWorld.decompose(te.position,te.quaternion,te.scale),te.translateX(J),te.translateZ(re),te.matrixWorld.compose(te.position,te.quaternion,te.scale),te.matrixWorldInverse.copy(te.matrixWorld).invert(),Fe[10]===-1)te.projectionMatrix.copy(pe.projectionMatrix),te.projectionMatrixInverse.copy(pe.projectionMatrixInverse);else{const ae=w+re,v=R+re,m=E-J,C=ue+(He-J),V=z*R/v*ae,K=ee*R/v*ae;te.projectionMatrix.makePerspective(m,C,V,K,ae,v),te.projectionMatrixInverse.copy(te.projectionMatrix).invert()}}function se(te,pe){pe===null?te.matrixWorld.copy(te.matrix):te.matrixWorld.multiplyMatrices(pe.matrixWorld,te.matrix),te.matrixWorldInverse.copy(te.matrixWorld).invert()}this.updateCamera=function(te){if(s===null)return;let pe=te.near,ge=te.far;g.texture!==null&&(g.depthNear>0&&(pe=g.depthNear),g.depthFar>0&&(ge=g.depthFar)),X.near=Q.near=T.near=pe,X.far=Q.far=T.far=ge,(Y!==X.near||$!==X.far)&&(s.updateRenderState({depthNear:X.near,depthFar:X.far}),Y=X.near,$=X.far),X.layers.mask=te.layers.mask|6,T.layers.mask=X.layers.mask&-5,Q.layers.mask=X.layers.mask&-3;const He=te.parent,Fe=X.cameras;se(X,He);for(let Be=0;Be<Fe.length;Be++)se(Fe[Be],He);Fe.length===2?ve(X,T,Q):X.projectionMatrix.copy(T.projectionMatrix),oe(te,X,He)};function oe(te,pe,ge){ge===null?te.matrix.copy(pe.matrixWorld):(te.matrix.copy(ge.matrixWorld),te.matrix.invert(),te.matrix.multiply(pe.matrixWorld)),te.matrix.decompose(te.position,te.quaternion,te.scale),te.updateMatrixWorld(!0),te.projectionMatrix.copy(pe.projectionMatrix),te.projectionMatrixInverse.copy(pe.projectionMatrixInverse),te.isPerspectiveCamera&&(te.fov=Ul*2*Math.atan(1/te.projectionMatrix.elements[5]),te.zoom=1)}this.getCamera=function(){return X},this.getFoveation=function(){if(!(h===null&&p===null))return l},this.setFoveation=function(te){l=te,h!==null&&(h.fixedFoveation=te),p!==null&&p.fixedFoveation!==void 0&&(p.fixedFoveation=te)},this.hasDepthSensing=function(){return g.texture!==null},this.getDepthSensingMesh=function(){return g.getMesh(X)},this.getCameraTexture=function(te){return d[te]};let we=null;function Ge(te,pe){if(u=pe.getViewerPose(c||a),_=pe,u!==null){const ge=u.views;p!==null&&(e.setRenderTargetFramebuffer(b,p.framebuffer),e.setRenderTarget(b));let He=!1;ge.length!==X.cameras.length&&(X.cameras.length=0,He=!0);for(let R=0;R<ge.length;R++){const z=ge[R];let ee=null;if(p!==null)ee=p.getViewport(z);else{const ne=f.getViewSubImage(h,z);ee=ne.viewport,R===0&&(e.setRenderTargetTextures(b,ne.colorTexture,ne.depthStencilTexture),e.setRenderTarget(b))}let q=D[R];q===void 0&&(q=new an,q.layers.enable(R),q.viewport=new _t,D[R]=q),q.matrix.fromArray(z.transform.matrix),q.matrix.decompose(q.position,q.quaternion,q.scale),q.projectionMatrix.fromArray(z.projectionMatrix),q.projectionMatrixInverse.copy(q.projectionMatrix).invert(),q.viewport.set(ee.x,ee.y,ee.width,ee.height),R===0&&(X.matrix.copy(q.matrix),X.matrix.decompose(X.position,X.quaternion,X.scale)),He===!0&&X.cameras.push(q)}const Fe=s.enabledFeatures;if(Fe&&Fe.includes("depth-sensing")&&s.depthUsage=="gpu-optimized"&&M){f=i.getBinding();const R=f.getDepthInformation(ge[0]);R&&R.isValid&&R.texture&&g.init(R,s.renderState)}if(Fe&&Fe.includes("camera-access")&&M){e.state.unbindTexture(),f=i.getBinding();for(let R=0;R<ge.length;R++){const z=ge[R].camera;if(z){let ee=d[z];ee||(ee=new Jf,d[z]=ee);const q=f.getCameraImage(z);ee.sourceTexture=q}}}}for(let ge=0;ge<P.length;ge++){const He=L[ge],Fe=P[ge];He!==null&&Fe!==void 0&&Fe.update(He,pe,c||a)}we&&we(te,pe),pe.detectedPlanes&&i.dispatchEvent({type:"planesdetected",data:pe}),_=null}const nt=new td;nt.setAnimationLoop(Ge),this.setAnimationLoop=function(te){we=te},this.dispose=function(){}}}const Ni=new Bn,sS=new ht;function rS(n,e){function t(g,d){g.matrixAutoUpdate===!0&&g.updateMatrix(),d.value.copy(g.matrix)}function i(g,d){d.color.getRGB(g.fogColor.value,Qf(n)),d.isFog?(g.fogNear.value=d.near,g.fogFar.value=d.far):d.isFogExp2&&(g.fogDensity.value=d.density)}function s(g,d,y,A,b){d.isMeshBasicMaterial?r(g,d):d.isMeshLambertMaterial?(r(g,d),d.envMap&&(g.envMapIntensity.value=d.envMapIntensity)):d.isMeshToonMaterial?(r(g,d),f(g,d)):d.isMeshPhongMaterial?(r(g,d),u(g,d),d.envMap&&(g.envMapIntensity.value=d.envMapIntensity)):d.isMeshStandardMaterial?(r(g,d),h(g,d),d.isMeshPhysicalMaterial&&p(g,d,b)):d.isMeshMatcapMaterial?(r(g,d),_(g,d)):d.isMeshDepthMaterial?r(g,d):d.isMeshDistanceMaterial?(r(g,d),M(g,d)):d.isMeshNormalMaterial?r(g,d):d.isLineBasicMaterial?(a(g,d),d.isLineDashedMaterial&&o(g,d)):d.isPointsMaterial?l(g,d,y,A):d.isSpriteMaterial?c(g,d):d.isShadowMaterial?(g.color.value.copy(d.color),g.opacity.value=d.opacity):d.isShaderMaterial&&(d.uniformsNeedUpdate=!1)}function r(g,d){g.opacity.value=d.opacity,d.color&&g.diffuse.value.copy(d.color),d.emissive&&g.emissive.value.copy(d.emissive).multiplyScalar(d.emissiveIntensity),d.map&&(g.map.value=d.map,t(d.map,g.mapTransform)),d.alphaMap&&(g.alphaMap.value=d.alphaMap,t(d.alphaMap,g.alphaMapTransform)),d.bumpMap&&(g.bumpMap.value=d.bumpMap,t(d.bumpMap,g.bumpMapTransform),g.bumpScale.value=d.bumpScale,d.side===Yt&&(g.bumpScale.value*=-1)),d.normalMap&&(g.normalMap.value=d.normalMap,t(d.normalMap,g.normalMapTransform),g.normalScale.value.copy(d.normalScale),d.side===Yt&&g.normalScale.value.negate()),d.displacementMap&&(g.displacementMap.value=d.displacementMap,t(d.displacementMap,g.displacementMapTransform),g.displacementScale.value=d.displacementScale,g.displacementBias.value=d.displacementBias),d.emissiveMap&&(g.emissiveMap.value=d.emissiveMap,t(d.emissiveMap,g.emissiveMapTransform)),d.specularMap&&(g.specularMap.value=d.specularMap,t(d.specularMap,g.specularMapTransform)),d.alphaTest>0&&(g.alphaTest.value=d.alphaTest);const y=e.get(d),A=y.envMap,b=y.envMapRotation;A&&(g.envMap.value=A,Ni.copy(b),Ni.x*=-1,Ni.y*=-1,Ni.z*=-1,A.isCubeTexture&&A.isRenderTargetTexture===!1&&(Ni.y*=-1,Ni.z*=-1),g.envMapRotation.value.setFromMatrix4(sS.makeRotationFromEuler(Ni)),g.flipEnvMap.value=A.isCubeTexture&&A.isRenderTargetTexture===!1?-1:1,g.reflectivity.value=d.reflectivity,g.ior.value=d.ior,g.refractionRatio.value=d.refractionRatio),d.lightMap&&(g.lightMap.value=d.lightMap,g.lightMapIntensity.value=d.lightMapIntensity,t(d.lightMap,g.lightMapTransform)),d.aoMap&&(g.aoMap.value=d.aoMap,g.aoMapIntensity.value=d.aoMapIntensity,t(d.aoMap,g.aoMapTransform))}function a(g,d){g.diffuse.value.copy(d.color),g.opacity.value=d.opacity,d.map&&(g.map.value=d.map,t(d.map,g.mapTransform))}function o(g,d){g.dashSize.value=d.dashSize,g.totalSize.value=d.dashSize+d.gapSize,g.scale.value=d.scale}function l(g,d,y,A){g.diffuse.value.copy(d.color),g.opacity.value=d.opacity,g.size.value=d.size*y,g.scale.value=A*.5,d.map&&(g.map.value=d.map,t(d.map,g.uvTransform)),d.alphaMap&&(g.alphaMap.value=d.alphaMap,t(d.alphaMap,g.alphaMapTransform)),d.alphaTest>0&&(g.alphaTest.value=d.alphaTest)}function c(g,d){g.diffuse.value.copy(d.color),g.opacity.value=d.opacity,g.rotation.value=d.rotation,d.map&&(g.map.value=d.map,t(d.map,g.mapTransform)),d.alphaMap&&(g.alphaMap.value=d.alphaMap,t(d.alphaMap,g.alphaMapTransform)),d.alphaTest>0&&(g.alphaTest.value=d.alphaTest)}function u(g,d){g.specular.value.copy(d.specular),g.shininess.value=Math.max(d.shininess,1e-4)}function f(g,d){d.gradientMap&&(g.gradientMap.value=d.gradientMap)}function h(g,d){g.metalness.value=d.metalness,d.metalnessMap&&(g.metalnessMap.value=d.metalnessMap,t(d.metalnessMap,g.metalnessMapTransform)),g.roughness.value=d.roughness,d.roughnessMap&&(g.roughnessMap.value=d.roughnessMap,t(d.roughnessMap,g.roughnessMapTransform)),d.envMap&&(g.envMapIntensity.value=d.envMapIntensity)}function p(g,d,y){g.ior.value=d.ior,d.sheen>0&&(g.sheenColor.value.copy(d.sheenColor).multiplyScalar(d.sheen),g.sheenRoughness.value=d.sheenRoughness,d.sheenColorMap&&(g.sheenColorMap.value=d.sheenColorMap,t(d.sheenColorMap,g.sheenColorMapTransform)),d.sheenRoughnessMap&&(g.sheenRoughnessMap.value=d.sheenRoughnessMap,t(d.sheenRoughnessMap,g.sheenRoughnessMapTransform))),d.clearcoat>0&&(g.clearcoat.value=d.clearcoat,g.clearcoatRoughness.value=d.clearcoatRoughness,d.clearcoatMap&&(g.clearcoatMap.value=d.clearcoatMap,t(d.clearcoatMap,g.clearcoatMapTransform)),d.clearcoatRoughnessMap&&(g.clearcoatRoughnessMap.value=d.clearcoatRoughnessMap,t(d.clearcoatRoughnessMap,g.clearcoatRoughnessMapTransform)),d.clearcoatNormalMap&&(g.clearcoatNormalMap.value=d.clearcoatNormalMap,t(d.clearcoatNormalMap,g.clearcoatNormalMapTransform),g.clearcoatNormalScale.value.copy(d.clearcoatNormalScale),d.side===Yt&&g.clearcoatNormalScale.value.negate())),d.dispersion>0&&(g.dispersion.value=d.dispersion),d.iridescence>0&&(g.iridescence.value=d.iridescence,g.iridescenceIOR.value=d.iridescenceIOR,g.iridescenceThicknessMinimum.value=d.iridescenceThicknessRange[0],g.iridescenceThicknessMaximum.value=d.iridescenceThicknessRange[1],d.iridescenceMap&&(g.iridescenceMap.value=d.iridescenceMap,t(d.iridescenceMap,g.iridescenceMapTransform)),d.iridescenceThicknessMap&&(g.iridescenceThicknessMap.value=d.iridescenceThicknessMap,t(d.iridescenceThicknessMap,g.iridescenceThicknessMapTransform))),d.transmission>0&&(g.transmission.value=d.transmission,g.transmissionSamplerMap.value=y.texture,g.transmissionSamplerSize.value.set(y.width,y.height),d.transmissionMap&&(g.transmissionMap.value=d.transmissionMap,t(d.transmissionMap,g.transmissionMapTransform)),g.thickness.value=d.thickness,d.thicknessMap&&(g.thicknessMap.value=d.thicknessMap,t(d.thicknessMap,g.thicknessMapTransform)),g.attenuationDistance.value=d.attenuationDistance,g.attenuationColor.value.copy(d.attenuationColor)),d.anisotropy>0&&(g.anisotropyVector.value.set(d.anisotropy*Math.cos(d.anisotropyRotation),d.anisotropy*Math.sin(d.anisotropyRotation)),d.anisotropyMap&&(g.anisotropyMap.value=d.anisotropyMap,t(d.anisotropyMap,g.anisotropyMapTransform))),g.specularIntensity.value=d.specularIntensity,g.specularColor.value.copy(d.specularColor),d.specularColorMap&&(g.specularColorMap.value=d.specularColorMap,t(d.specularColorMap,g.specularColorMapTransform)),d.specularIntensityMap&&(g.specularIntensityMap.value=d.specularIntensityMap,t(d.specularIntensityMap,g.specularIntensityMapTransform))}function _(g,d){d.matcap&&(g.matcap.value=d.matcap)}function M(g,d){const y=e.get(d).light;g.referencePosition.value.setFromMatrixPosition(y.matrixWorld),g.nearDistance.value=y.shadow.camera.near,g.farDistance.value=y.shadow.camera.far}return{refreshFogUniforms:i,refreshMaterialUniforms:s}}function aS(n,e,t,i){let s={},r={},a=[];const o=n.getParameter(n.MAX_UNIFORM_BUFFER_BINDINGS);function l(y,A){const b=A.program;i.uniformBlockBinding(y,b)}function c(y,A){let b=s[y.id];b===void 0&&(_(y),b=u(y),s[y.id]=b,y.addEventListener("dispose",g));const P=A.program;i.updateUBOMapping(y,P);const L=e.render.frame;r[y.id]!==L&&(h(y),r[y.id]=L)}function u(y){const A=f();y.__bindingPointIndex=A;const b=n.createBuffer(),P=y.__size,L=y.usage;return n.bindBuffer(n.UNIFORM_BUFFER,b),n.bufferData(n.UNIFORM_BUFFER,P,L),n.bindBuffer(n.UNIFORM_BUFFER,null),n.bindBufferBase(n.UNIFORM_BUFFER,A,b),b}function f(){for(let y=0;y<o;y++)if(a.indexOf(y)===-1)return a.push(y),y;return Qe("WebGLRenderer: Maximum number of simultaneously usable uniforms groups reached."),0}function h(y){const A=s[y.id],b=y.uniforms,P=y.__cache;n.bindBuffer(n.UNIFORM_BUFFER,A);for(let L=0,I=b.length;L<I;L++){const x=Array.isArray(b[L])?b[L]:[b[L]];for(let T=0,Q=x.length;T<Q;T++){const D=x[T];if(p(D,L,T,P)===!0){const X=D.__offset,Y=Array.isArray(D.value)?D.value:[D.value];let $=0;for(let W=0;W<Y.length;W++){const B=Y[W],N=M(B);typeof B=="number"||typeof B=="boolean"?(D.__data[0]=B,n.bufferSubData(n.UNIFORM_BUFFER,X+$,D.__data)):B.isMatrix3?(D.__data[0]=B.elements[0],D.__data[1]=B.elements[1],D.__data[2]=B.elements[2],D.__data[3]=0,D.__data[4]=B.elements[3],D.__data[5]=B.elements[4],D.__data[6]=B.elements[5],D.__data[7]=0,D.__data[8]=B.elements[6],D.__data[9]=B.elements[7],D.__data[10]=B.elements[8],D.__data[11]=0):(B.toArray(D.__data,$),$+=N.storage/Float32Array.BYTES_PER_ELEMENT)}n.bufferSubData(n.UNIFORM_BUFFER,X,D.__data)}}}n.bindBuffer(n.UNIFORM_BUFFER,null)}function p(y,A,b,P){const L=y.value,I=A+"_"+b;if(P[I]===void 0)return typeof L=="number"||typeof L=="boolean"?P[I]=L:P[I]=L.clone(),!0;{const x=P[I];if(typeof L=="number"||typeof L=="boolean"){if(x!==L)return P[I]=L,!0}else if(x.equals(L)===!1)return x.copy(L),!0}return!1}function _(y){const A=y.uniforms;let b=0;const P=16;for(let I=0,x=A.length;I<x;I++){const T=Array.isArray(A[I])?A[I]:[A[I]];for(let Q=0,D=T.length;Q<D;Q++){const X=T[Q],Y=Array.isArray(X.value)?X.value:[X.value];for(let $=0,W=Y.length;$<W;$++){const B=Y[$],N=M(B),ie=b%P,le=ie%N.boundary,ve=ie+le;b+=le,ve!==0&&P-ve<N.storage&&(b+=P-ve),X.__data=new Float32Array(N.storage/Float32Array.BYTES_PER_ELEMENT),X.__offset=b,b+=N.storage}}}const L=b%P;return L>0&&(b+=P-L),y.__size=b,y.__cache={},this}function M(y){const A={boundary:0,storage:0};return typeof y=="number"||typeof y=="boolean"?(A.boundary=4,A.storage=4):y.isVector2?(A.boundary=8,A.storage=8):y.isVector3||y.isColor?(A.boundary=16,A.storage=12):y.isVector4?(A.boundary=16,A.storage=16):y.isMatrix3?(A.boundary=48,A.storage=48):y.isMatrix4?(A.boundary=64,A.storage=64):y.isTexture?Oe("WebGLRenderer: Texture samplers can not be part of an uniforms group."):Oe("WebGLRenderer: Unsupported uniform value type.",y),A}function g(y){const A=y.target;A.removeEventListener("dispose",g);const b=a.indexOf(A.__bindingPointIndex);a.splice(b,1),n.deleteBuffer(s[A.id]),delete s[A.id],delete r[A.id]}function d(){for(const y in s)n.deleteBuffer(s[y]);a=[],s={},r={}}return{bind:l,update:c,dispose:d}}const oS=new Uint16Array([12469,15057,12620,14925,13266,14620,13807,14376,14323,13990,14545,13625,14713,13328,14840,12882,14931,12528,14996,12233,15039,11829,15066,11525,15080,11295,15085,10976,15082,10705,15073,10495,13880,14564,13898,14542,13977,14430,14158,14124,14393,13732,14556,13410,14702,12996,14814,12596,14891,12291,14937,11834,14957,11489,14958,11194,14943,10803,14921,10506,14893,10278,14858,9960,14484,14039,14487,14025,14499,13941,14524,13740,14574,13468,14654,13106,14743,12678,14818,12344,14867,11893,14889,11509,14893,11180,14881,10751,14852,10428,14812,10128,14765,9754,14712,9466,14764,13480,14764,13475,14766,13440,14766,13347,14769,13070,14786,12713,14816,12387,14844,11957,14860,11549,14868,11215,14855,10751,14825,10403,14782,10044,14729,9651,14666,9352,14599,9029,14967,12835,14966,12831,14963,12804,14954,12723,14936,12564,14917,12347,14900,11958,14886,11569,14878,11247,14859,10765,14828,10401,14784,10011,14727,9600,14660,9289,14586,8893,14508,8533,15111,12234,15110,12234,15104,12216,15092,12156,15067,12010,15028,11776,14981,11500,14942,11205,14902,10752,14861,10393,14812,9991,14752,9570,14682,9252,14603,8808,14519,8445,14431,8145,15209,11449,15208,11451,15202,11451,15190,11438,15163,11384,15117,11274,15055,10979,14994,10648,14932,10343,14871,9936,14803,9532,14729,9218,14645,8742,14556,8381,14461,8020,14365,7603,15273,10603,15272,10607,15267,10619,15256,10631,15231,10614,15182,10535,15118,10389,15042,10167,14963,9787,14883,9447,14800,9115,14710,8665,14615,8318,14514,7911,14411,7507,14279,7198,15314,9675,15313,9683,15309,9712,15298,9759,15277,9797,15229,9773,15166,9668,15084,9487,14995,9274,14898,8910,14800,8539,14697,8234,14590,7790,14479,7409,14367,7067,14178,6621,15337,8619,15337,8631,15333,8677,15325,8769,15305,8871,15264,8940,15202,8909,15119,8775,15022,8565,14916,8328,14804,8009,14688,7614,14569,7287,14448,6888,14321,6483,14088,6171,15350,7402,15350,7419,15347,7480,15340,7613,15322,7804,15287,7973,15229,8057,15148,8012,15046,7846,14933,7611,14810,7357,14682,7069,14552,6656,14421,6316,14251,5948,14007,5528,15356,5942,15356,5977,15353,6119,15348,6294,15332,6551,15302,6824,15249,7044,15171,7122,15070,7050,14949,6861,14818,6611,14679,6349,14538,6067,14398,5651,14189,5311,13935,4958,15359,4123,15359,4153,15356,4296,15353,4646,15338,5160,15311,5508,15263,5829,15188,6042,15088,6094,14966,6001,14826,5796,14678,5543,14527,5287,14377,4985,14133,4586,13869,4257,15360,1563,15360,1642,15358,2076,15354,2636,15341,3350,15317,4019,15273,4429,15203,4732,15105,4911,14981,4932,14836,4818,14679,4621,14517,4386,14359,4156,14083,3795,13808,3437,15360,122,15360,137,15358,285,15355,636,15344,1274,15322,2177,15281,2765,15215,3223,15120,3451,14995,3569,14846,3567,14681,3466,14511,3305,14344,3121,14037,2800,13753,2467,15360,0,15360,1,15359,21,15355,89,15346,253,15325,479,15287,796,15225,1148,15133,1492,15008,1749,14856,1882,14685,1886,14506,1783,14324,1608,13996,1398,13702,1183]);let En=null;function lS(){return En===null&&(En=new Kf(oS,16,16,ws,ri),En.name="DFG_LUT",En.minFilter=Ut,En.magFilter=Ut,En.wrapS=Qn,En.wrapT=Qn,En.generateMipmaps=!1,En.needsUpdate=!0),En}class cS{constructor(e={}){const{canvas:t=vg(),context:i=null,depth:s=!0,stencil:r=!1,alpha:a=!1,antialias:o=!1,premultipliedAlpha:l=!0,preserveDrawingBuffer:c=!1,powerPreference:u="default",failIfMajorPerformanceCaveat:f=!1,reversedDepthBuffer:h=!1,outputBufferType:p=Jt}=e;this.isWebGLRenderer=!0;let _;if(i!==null){if(typeof WebGLRenderingContext<"u"&&i instanceof WebGLRenderingContext)throw new Error("THREE.WebGLRenderer: WebGL 1 is not supported since r163.");_=i.getContextAttributes().alpha}else _=a;const M=p,g=new Set([cc,lc,oc]),d=new Set([Jt,On,hr,fr,sc,rc]),y=new Uint32Array(4),A=new Int32Array(4);let b=null,P=null;const L=[],I=[];let x=null;this.domElement=t,this.debug={checkShaderErrors:!0,onShaderError:null},this.autoClear=!0,this.autoClearColor=!0,this.autoClearDepth=!0,this.autoClearStencil=!0,this.sortObjects=!0,this.clippingPlanes=[],this.localClippingEnabled=!1,this.toneMapping=In,this.toneMappingExposure=1,this.transmissionResolutionScale=1;const T=this;let Q=!1;this._outputColorSpace=Zt;let D=0,X=0,Y=null,$=-1,W=null;const B=new _t,N=new _t;let ie=null;const le=new Ze(0);let ve=0,se=t.width,oe=t.height,we=1,Ge=null,nt=null;const te=new _t(0,0,se,oe),pe=new _t(0,0,se,oe);let ge=!1;const He=new mc;let Fe=!1,Be=!1;const w=new ht,R=new O,z=new _t,ee={background:null,fog:null,environment:null,overrideMaterial:null,isScene:!0};let q=!1;function ne(){return Y===null?we:1}let E=i;function ue(S,F){return t.getContext(S,F)}try{const S={alpha:!0,depth:s,stencil:r,antialias:o,premultipliedAlpha:l,preserveDrawingBuffer:c,powerPreference:u,failIfMajorPerformanceCaveat:f};if("setAttribute"in t&&t.setAttribute("data-engine",`three.js r${nc}`),t.addEventListener("webglcontextlost",Pe,!1),t.addEventListener("webglcontextrestored",Ve,!1),t.addEventListener("webglcontextcreationerror",pt,!1),E===null){const F="webgl2";if(E=ue(F,S),E===null)throw ue(F)?new Error("Error creating WebGL context with your selected attributes."):new Error("Error creating WebGL context.")}}catch(S){throw Qe("WebGLRenderer: "+S.message),S}let re,J,ae,v,m,C,V,K,k,_e,ce,Ce,Le,he,de,Me,Ee,Te,We,U,xe,me,Re;function fe(){re=new cv(E),re.init(),xe=new QM(E,re),J=new tv(E,re,e,xe),ae=new ZM(E,re),J.reversedDepthBuffer&&h&&ae.buffers.depth.setReversed(!0),v=new fv(E),m=new OM,C=new JM(E,re,ae,m,J,xe,v),V=new lv(T),K=new __(E),me=new Qx(E,K),k=new uv(E,K,v,me),_e=new pv(E,k,K,me,v),Te=new dv(E,J,C),de=new nv(m),ce=new FM(T,V,re,J,me,de),Ce=new rS(T,m),Le=new zM,he=new XM(re),Ee=new Jx(T,V,ae,_e,_,l),Me=new $M(T,_e,J),Re=new aS(E,v,J,ae),We=new ev(E,re,v),U=new hv(E,re,v),v.programs=ce.programs,T.capabilities=J,T.extensions=re,T.properties=m,T.renderLists=Le,T.shadowMap=Me,T.state=ae,T.info=v}fe(),M!==Jt&&(x=new gv(M,t.width,t.height,s,r));const Z=new iS(T,E);this.xr=Z,this.getContext=function(){return E},this.getContextAttributes=function(){return E.getContextAttributes()},this.forceContextLoss=function(){const S=re.get("WEBGL_lose_context");S&&S.loseContext()},this.forceContextRestore=function(){const S=re.get("WEBGL_lose_context");S&&S.restoreContext()},this.getPixelRatio=function(){return we},this.setPixelRatio=function(S){S!==void 0&&(we=S,this.setSize(se,oe,!1))},this.getSize=function(S){return S.set(se,oe)},this.setSize=function(S,F,j=!0){if(Z.isPresenting){Oe("WebGLRenderer: Can't change size while VR device is presenting.");return}se=S,oe=F,t.width=Math.floor(S*we),t.height=Math.floor(F*we),j===!0&&(t.style.width=S+"px",t.style.height=F+"px"),x!==null&&x.setSize(t.width,t.height),this.setViewport(0,0,S,F)},this.getDrawingBufferSize=function(S){return S.set(se*we,oe*we).floor()},this.setDrawingBufferSize=function(S,F,j){se=S,oe=F,we=j,t.width=Math.floor(S*j),t.height=Math.floor(F*j),this.setViewport(0,0,S,F)},this.setEffects=function(S){if(M===Jt){console.error("THREE.WebGLRenderer: setEffects() requires outputBufferType set to HalfFloatType or FloatType.");return}if(S){for(let F=0;F<S.length;F++)if(S[F].isOutputPass===!0){console.warn("THREE.WebGLRenderer: OutputPass is not needed in setEffects(). Tone mapping and color space conversion are applied automatically.");break}}x.setEffects(S||[])},this.getCurrentViewport=function(S){return S.copy(B)},this.getViewport=function(S){return S.copy(te)},this.setViewport=function(S,F,j,G){S.isVector4?te.set(S.x,S.y,S.z,S.w):te.set(S,F,j,G),ae.viewport(B.copy(te).multiplyScalar(we).round())},this.getScissor=function(S){return S.copy(pe)},this.setScissor=function(S,F,j,G){S.isVector4?pe.set(S.x,S.y,S.z,S.w):pe.set(S,F,j,G),ae.scissor(N.copy(pe).multiplyScalar(we).round())},this.getScissorTest=function(){return ge},this.setScissorTest=function(S){ae.setScissorTest(ge=S)},this.setOpaqueSort=function(S){Ge=S},this.setTransparentSort=function(S){nt=S},this.getClearColor=function(S){return S.copy(Ee.getClearColor())},this.setClearColor=function(){Ee.setClearColor(...arguments)},this.getClearAlpha=function(){return Ee.getClearAlpha()},this.setClearAlpha=function(){Ee.setClearAlpha(...arguments)},this.clear=function(S=!0,F=!0,j=!0){let G=0;if(S){let H=!1;if(Y!==null){const ye=Y.texture.format;H=g.has(ye)}if(H){const ye=Y.texture.type,Ae=d.has(ye),be=Ee.getClearColor(),De=Ee.getClearAlpha(),Ue=be.r,Xe=be.g,$e=be.b;Ae?(y[0]=Ue,y[1]=Xe,y[2]=$e,y[3]=De,E.clearBufferuiv(E.COLOR,0,y)):(A[0]=Ue,A[1]=Xe,A[2]=$e,A[3]=De,E.clearBufferiv(E.COLOR,0,A))}else G|=E.COLOR_BUFFER_BIT}F&&(G|=E.DEPTH_BUFFER_BIT),j&&(G|=E.STENCIL_BUFFER_BIT,this.state.buffers.stencil.setMask(4294967295)),G!==0&&E.clear(G)},this.clearColor=function(){this.clear(!0,!1,!1)},this.clearDepth=function(){this.clear(!1,!0,!1)},this.clearStencil=function(){this.clear(!1,!1,!0)},this.dispose=function(){t.removeEventListener("webglcontextlost",Pe,!1),t.removeEventListener("webglcontextrestored",Ve,!1),t.removeEventListener("webglcontextcreationerror",pt,!1),Ee.dispose(),Le.dispose(),he.dispose(),m.dispose(),V.dispose(),_e.dispose(),me.dispose(),Re.dispose(),ce.dispose(),Z.dispose(),Z.removeEventListener("sessionstart",Mc),Z.removeEventListener("sessionend",Sc),Ai.stop()};function Pe(S){S.preventDefault(),pu("WebGLRenderer: Context Lost."),Q=!0}function Ve(){pu("WebGLRenderer: Context Restored."),Q=!1;const S=v.autoReset,F=Me.enabled,j=Me.autoUpdate,G=Me.needsUpdate,H=Me.type;fe(),v.autoReset=S,Me.enabled=F,Me.autoUpdate=j,Me.needsUpdate=G,Me.type=H}function pt(S){Qe("WebGLRenderer: A WebGL context could not be created. Reason: ",S.statusMessage)}function rt(S){const F=S.target;F.removeEventListener("dispose",rt),Hn(F)}function Hn(S){Vn(S),m.remove(S)}function Vn(S){const F=m.get(S).programs;F!==void 0&&(F.forEach(function(j){ce.releaseProgram(j)}),S.isShaderMaterial&&ce.releaseShaderCache(S))}this.renderBufferDirect=function(S,F,j,G,H,ye){F===null&&(F=ee);const Ae=H.isMesh&&H.matrixWorld.determinant()<0,be=cd(S,F,j,G,H);ae.setMaterial(G,Ae);let De=j.index,Ue=1;if(G.wireframe===!0){if(De=k.getWireframeAttribute(j),De===void 0)return;Ue=2}const Xe=j.drawRange,$e=j.attributes.position;let Ne=Xe.start*Ue,lt=(Xe.start+Xe.count)*Ue;ye!==null&&(Ne=Math.max(Ne,ye.start*Ue),lt=Math.min(lt,(ye.start+ye.count)*Ue)),De!==null?(Ne=Math.max(Ne,0),lt=Math.min(lt,De.count)):$e!=null&&(Ne=Math.max(Ne,0),lt=Math.min(lt,$e.count));const xt=lt-Ne;if(xt<0||xt===1/0)return;me.setup(H,G,be,j,De);let gt,ct=We;if(De!==null&&(gt=K.get(De),ct=U,ct.setIndex(gt)),H.isMesh)G.wireframe===!0?(ae.setLineWidth(G.wireframeLinewidth*ne()),ct.setMode(E.LINES)):ct.setMode(E.TRIANGLES);else if(H.isLine){let Rt=G.linewidth;Rt===void 0&&(Rt=1),ae.setLineWidth(Rt*ne()),H.isLineSegments?ct.setMode(E.LINES):H.isLineLoop?ct.setMode(E.LINE_LOOP):ct.setMode(E.LINE_STRIP)}else H.isPoints?ct.setMode(E.POINTS):H.isSprite&&ct.setMode(E.TRIANGLES);if(H.isBatchedMesh)if(H._multiDrawInstances!==null)Sa("WebGLRenderer: renderMultiDrawInstances has been deprecated and will be removed in r184. Append to renderMultiDraw arguments and use indirection."),ct.renderMultiDrawInstances(H._multiDrawStarts,H._multiDrawCounts,H._multiDrawCount,H._multiDrawInstances);else if(re.get("WEBGL_multi_draw"))ct.renderMultiDraw(H._multiDrawStarts,H._multiDrawCounts,H._multiDrawCount);else{const Rt=H._multiDrawStarts,Ie=H._multiDrawCounts,jt=H._multiDrawCount,tt=De?K.get(De).bytesPerElement:1,cn=m.get(G).currentProgram.getUniforms();for(let Sn=0;Sn<jt;Sn++)cn.setValue(E,"_gl_DrawID",Sn),ct.render(Rt[Sn]/tt,Ie[Sn])}else if(H.isInstancedMesh)ct.renderInstances(Ne,xt,H.count);else if(j.isInstancedBufferGeometry){const Rt=j._maxInstanceCount!==void 0?j._maxInstanceCount:1/0,Ie=Math.min(j.instanceCount,Rt);ct.renderInstances(Ne,xt,Ie)}else ct.render(Ne,xt)};function vc(S,F,j){S.transparent===!0&&S.side===Pn&&S.forceSinglePass===!1?(S.side=Yt,S.needsUpdate=!0,Sr(S,F,j),S.side=Ti,S.needsUpdate=!0,Sr(S,F,j),S.side=Pn):Sr(S,F,j)}this.compile=function(S,F,j=null){j===null&&(j=S),P=he.get(j),P.init(F),I.push(P),j.traverseVisible(function(H){H.isLight&&H.layers.test(F.layers)&&(P.pushLight(H),H.castShadow&&P.pushShadow(H))}),S!==j&&S.traverseVisible(function(H){H.isLight&&H.layers.test(F.layers)&&(P.pushLight(H),H.castShadow&&P.pushShadow(H))}),P.setupLights();const G=new Set;return S.traverse(function(H){if(!(H.isMesh||H.isPoints||H.isLine||H.isSprite))return;const ye=H.material;if(ye)if(Array.isArray(ye))for(let Ae=0;Ae<ye.length;Ae++){const be=ye[Ae];vc(be,j,H),G.add(be)}else vc(ye,j,H),G.add(ye)}),P=I.pop(),G},this.compileAsync=function(S,F,j=null){const G=this.compile(S,F,j);return new Promise(H=>{function ye(){if(G.forEach(function(Ae){m.get(Ae).currentProgram.isReady()&&G.delete(Ae)}),G.size===0){H(S);return}setTimeout(ye,10)}re.get("KHR_parallel_shader_compile")!==null?ye():setTimeout(ye,10)})};let Ba=null;function ld(S){Ba&&Ba(S)}function Mc(){Ai.stop()}function Sc(){Ai.start()}const Ai=new td;Ai.setAnimationLoop(ld),typeof self<"u"&&Ai.setContext(self),this.setAnimationLoop=function(S){Ba=S,Z.setAnimationLoop(S),S===null?Ai.stop():Ai.start()},Z.addEventListener("sessionstart",Mc),Z.addEventListener("sessionend",Sc),this.render=function(S,F){if(F!==void 0&&F.isCamera!==!0){Qe("WebGLRenderer.render: camera is not an instance of THREE.Camera.");return}if(Q===!0)return;const j=Z.enabled===!0&&Z.isPresenting===!0,G=x!==null&&(Y===null||j)&&x.begin(T,Y);if(S.matrixWorldAutoUpdate===!0&&S.updateMatrixWorld(),F.parent===null&&F.matrixWorldAutoUpdate===!0&&F.updateMatrixWorld(),Z.enabled===!0&&Z.isPresenting===!0&&(x===null||x.isCompositing()===!1)&&(Z.cameraAutoUpdate===!0&&Z.updateCamera(F),F=Z.getCamera()),S.isScene===!0&&S.onBeforeRender(T,S,F,Y),P=he.get(S,I.length),P.init(F),I.push(P),w.multiplyMatrices(F.projectionMatrix,F.matrixWorldInverse),He.setFromProjectionMatrix(w,Dn,F.reversedDepth),Be=this.localClippingEnabled,Fe=de.init(this.clippingPlanes,Be),b=Le.get(S,L.length),b.init(),L.push(b),Z.enabled===!0&&Z.isPresenting===!0){const Ae=T.xr.getDepthSensingMesh();Ae!==null&&za(Ae,F,-1/0,T.sortObjects)}za(S,F,0,T.sortObjects),b.finish(),T.sortObjects===!0&&b.sort(Ge,nt),q=Z.enabled===!1||Z.isPresenting===!1||Z.hasDepthSensing()===!1,q&&Ee.addToRenderList(b,S),this.info.render.frame++,Fe===!0&&de.beginShadows();const H=P.state.shadowsArray;if(Me.render(H,S,F),Fe===!0&&de.endShadows(),this.info.autoReset===!0&&this.info.reset(),(G&&x.hasRenderPass())===!1){const Ae=b.opaque,be=b.transmissive;if(P.setupLights(),F.isArrayCamera){const De=F.cameras;if(be.length>0)for(let Ue=0,Xe=De.length;Ue<Xe;Ue++){const $e=De[Ue];bc(Ae,be,S,$e)}q&&Ee.render(S);for(let Ue=0,Xe=De.length;Ue<Xe;Ue++){const $e=De[Ue];yc(b,S,$e,$e.viewport)}}else be.length>0&&bc(Ae,be,S,F),q&&Ee.render(S),yc(b,S,F)}Y!==null&&X===0&&(C.updateMultisampleRenderTarget(Y),C.updateRenderTargetMipmap(Y)),G&&x.end(T),S.isScene===!0&&S.onAfterRender(T,S,F),me.resetDefaultState(),$=-1,W=null,I.pop(),I.length>0?(P=I[I.length-1],Fe===!0&&de.setGlobalState(T.clippingPlanes,P.state.camera)):P=null,L.pop(),L.length>0?b=L[L.length-1]:b=null};function za(S,F,j,G){if(S.visible===!1)return;if(S.layers.test(F.layers)){if(S.isGroup)j=S.renderOrder;else if(S.isLOD)S.autoUpdate===!0&&S.update(F);else if(S.isLight)P.pushLight(S),S.castShadow&&P.pushShadow(S);else if(S.isSprite){if(!S.frustumCulled||He.intersectsSprite(S)){G&&z.setFromMatrixPosition(S.matrixWorld).applyMatrix4(w);const Ae=_e.update(S),be=S.material;be.visible&&b.push(S,Ae,be,j,z.z,null)}}else if((S.isMesh||S.isLine||S.isPoints)&&(!S.frustumCulled||He.intersectsObject(S))){const Ae=_e.update(S),be=S.material;if(G&&(S.boundingSphere!==void 0?(S.boundingSphere===null&&S.computeBoundingSphere(),z.copy(S.boundingSphere.center)):(Ae.boundingSphere===null&&Ae.computeBoundingSphere(),z.copy(Ae.boundingSphere.center)),z.applyMatrix4(S.matrixWorld).applyMatrix4(w)),Array.isArray(be)){const De=Ae.groups;for(let Ue=0,Xe=De.length;Ue<Xe;Ue++){const $e=De[Ue],Ne=be[$e.materialIndex];Ne&&Ne.visible&&b.push(S,Ae,Ne,j,z.z,$e)}}else be.visible&&b.push(S,Ae,be,j,z.z,null)}}const ye=S.children;for(let Ae=0,be=ye.length;Ae<be;Ae++)za(ye[Ae],F,j,G)}function yc(S,F,j,G){const{opaque:H,transmissive:ye,transparent:Ae}=S;P.setupLightsView(j),Fe===!0&&de.setGlobalState(T.clippingPlanes,j),G&&ae.viewport(B.copy(G)),H.length>0&&Mr(H,F,j),ye.length>0&&Mr(ye,F,j),Ae.length>0&&Mr(Ae,F,j),ae.buffers.depth.setTest(!0),ae.buffers.depth.setMask(!0),ae.buffers.color.setMask(!0),ae.setPolygonOffset(!1)}function bc(S,F,j,G){if((j.isScene===!0?j.overrideMaterial:null)!==null)return;if(P.state.transmissionRenderTarget[G.id]===void 0){const Ne=re.has("EXT_color_buffer_half_float")||re.has("EXT_color_buffer_float");P.state.transmissionRenderTarget[G.id]=new Un(1,1,{generateMipmaps:!0,type:Ne?ri:Jt,minFilter:Hi,samples:Math.max(4,J.samples),stencilBuffer:r,resolveDepthBuffer:!1,resolveStencilBuffer:!1,colorSpace:et.workingColorSpace})}const ye=P.state.transmissionRenderTarget[G.id],Ae=G.viewport||B;ye.setSize(Ae.z*T.transmissionResolutionScale,Ae.w*T.transmissionResolutionScale);const be=T.getRenderTarget(),De=T.getActiveCubeFace(),Ue=T.getActiveMipmapLevel();T.setRenderTarget(ye),T.getClearColor(le),ve=T.getClearAlpha(),ve<1&&T.setClearColor(16777215,.5),T.clear(),q&&Ee.render(j);const Xe=T.toneMapping;T.toneMapping=In;const $e=G.viewport;if(G.viewport!==void 0&&(G.viewport=void 0),P.setupLightsView(G),Fe===!0&&de.setGlobalState(T.clippingPlanes,G),Mr(S,j,G),C.updateMultisampleRenderTarget(ye),C.updateRenderTargetMipmap(ye),re.has("WEBGL_multisampled_render_to_texture")===!1){let Ne=!1;for(let lt=0,xt=F.length;lt<xt;lt++){const gt=F[lt],{object:ct,geometry:Rt,material:Ie,group:jt}=gt;if(Ie.side===Pn&&ct.layers.test(G.layers)){const tt=Ie.side;Ie.side=Yt,Ie.needsUpdate=!0,Ec(ct,j,G,Rt,Ie,jt),Ie.side=tt,Ie.needsUpdate=!0,Ne=!0}}Ne===!0&&(C.updateMultisampleRenderTarget(ye),C.updateRenderTargetMipmap(ye))}T.setRenderTarget(be,De,Ue),T.setClearColor(le,ve),$e!==void 0&&(G.viewport=$e),T.toneMapping=Xe}function Mr(S,F,j){const G=F.isScene===!0?F.overrideMaterial:null;for(let H=0,ye=S.length;H<ye;H++){const Ae=S[H],{object:be,geometry:De,group:Ue}=Ae;let Xe=Ae.material;Xe.allowOverride===!0&&G!==null&&(Xe=G),be.layers.test(j.layers)&&Ec(be,F,j,De,Xe,Ue)}}function Ec(S,F,j,G,H,ye){S.onBeforeRender(T,F,j,G,H,ye),S.modelViewMatrix.multiplyMatrices(j.matrixWorldInverse,S.matrixWorld),S.normalMatrix.getNormalMatrix(S.modelViewMatrix),H.onBeforeRender(T,F,j,G,S,ye),H.transparent===!0&&H.side===Pn&&H.forceSinglePass===!1?(H.side=Yt,H.needsUpdate=!0,T.renderBufferDirect(j,F,G,H,S,ye),H.side=Ti,H.needsUpdate=!0,T.renderBufferDirect(j,F,G,H,S,ye),H.side=Pn):T.renderBufferDirect(j,F,G,H,S,ye),S.onAfterRender(T,F,j,G,H,ye)}function Sr(S,F,j){F.isScene!==!0&&(F=ee);const G=m.get(S),H=P.state.lights,ye=P.state.shadowsArray,Ae=H.state.version,be=ce.getParameters(S,H.state,ye,F,j),De=ce.getProgramCacheKey(be);let Ue=G.programs;G.environment=S.isMeshStandardMaterial||S.isMeshLambertMaterial||S.isMeshPhongMaterial?F.environment:null,G.fog=F.fog;const Xe=S.isMeshStandardMaterial||S.isMeshLambertMaterial&&!S.envMap||S.isMeshPhongMaterial&&!S.envMap;G.envMap=V.get(S.envMap||G.environment,Xe),G.envMapRotation=G.environment!==null&&S.envMap===null?F.environmentRotation:S.envMapRotation,Ue===void 0&&(S.addEventListener("dispose",rt),Ue=new Map,G.programs=Ue);let $e=Ue.get(De);if($e!==void 0){if(G.currentProgram===$e&&G.lightsStateVersion===Ae)return Ac(S,be),$e}else be.uniforms=ce.getUniforms(S),S.onBeforeCompile(be,T),$e=ce.acquireProgram(be,De),Ue.set(De,$e),G.uniforms=be.uniforms;const Ne=G.uniforms;return(!S.isShaderMaterial&&!S.isRawShaderMaterial||S.clipping===!0)&&(Ne.clippingPlanes=de.uniform),Ac(S,be),G.needsLights=hd(S),G.lightsStateVersion=Ae,G.needsLights&&(Ne.ambientLightColor.value=H.state.ambient,Ne.lightProbe.value=H.state.probe,Ne.directionalLights.value=H.state.directional,Ne.directionalLightShadows.value=H.state.directionalShadow,Ne.spotLights.value=H.state.spot,Ne.spotLightShadows.value=H.state.spotShadow,Ne.rectAreaLights.value=H.state.rectArea,Ne.ltc_1.value=H.state.rectAreaLTC1,Ne.ltc_2.value=H.state.rectAreaLTC2,Ne.pointLights.value=H.state.point,Ne.pointLightShadows.value=H.state.pointShadow,Ne.hemisphereLights.value=H.state.hemi,Ne.directionalShadowMatrix.value=H.state.directionalShadowMatrix,Ne.spotLightMatrix.value=H.state.spotLightMatrix,Ne.spotLightMap.value=H.state.spotLightMap,Ne.pointShadowMatrix.value=H.state.pointShadowMatrix),G.currentProgram=$e,G.uniformsList=null,$e}function Tc(S){if(S.uniformsList===null){const F=S.currentProgram.getUniforms();S.uniformsList=ca.seqWithValue(F.seq,S.uniforms)}return S.uniformsList}function Ac(S,F){const j=m.get(S);j.outputColorSpace=F.outputColorSpace,j.batching=F.batching,j.batchingColor=F.batchingColor,j.instancing=F.instancing,j.instancingColor=F.instancingColor,j.instancingMorph=F.instancingMorph,j.skinning=F.skinning,j.morphTargets=F.morphTargets,j.morphNormals=F.morphNormals,j.morphColors=F.morphColors,j.morphTargetsCount=F.morphTargetsCount,j.numClippingPlanes=F.numClippingPlanes,j.numIntersection=F.numClipIntersection,j.vertexAlphas=F.vertexAlphas,j.vertexTangents=F.vertexTangents,j.toneMapping=F.toneMapping}function cd(S,F,j,G,H){F.isScene!==!0&&(F=ee),C.resetTextureUnits();const ye=F.fog,Ae=G.isMeshStandardMaterial||G.isMeshLambertMaterial||G.isMeshPhongMaterial?F.environment:null,be=Y===null?T.outputColorSpace:Y.isXRRenderTarget===!0?Y.texture.colorSpace:Cs,De=G.isMeshStandardMaterial||G.isMeshLambertMaterial&&!G.envMap||G.isMeshPhongMaterial&&!G.envMap,Ue=V.get(G.envMap||Ae,De),Xe=G.vertexColors===!0&&!!j.attributes.color&&j.attributes.color.itemSize===4,$e=!!j.attributes.tangent&&(!!G.normalMap||G.anisotropy>0),Ne=!!j.morphAttributes.position,lt=!!j.morphAttributes.normal,xt=!!j.morphAttributes.color;let gt=In;G.toneMapped&&(Y===null||Y.isXRRenderTarget===!0)&&(gt=T.toneMapping);const ct=j.morphAttributes.position||j.morphAttributes.normal||j.morphAttributes.color,Rt=ct!==void 0?ct.length:0,Ie=m.get(G),jt=P.state.lights;if(Fe===!0&&(Be===!0||S!==W)){const Tt=S===W&&G.id===$;de.setState(G,S,Tt)}let tt=!1;G.version===Ie.__version?(Ie.needsLights&&Ie.lightsStateVersion!==jt.state.version||Ie.outputColorSpace!==be||H.isBatchedMesh&&Ie.batching===!1||!H.isBatchedMesh&&Ie.batching===!0||H.isBatchedMesh&&Ie.batchingColor===!0&&H.colorTexture===null||H.isBatchedMesh&&Ie.batchingColor===!1&&H.colorTexture!==null||H.isInstancedMesh&&Ie.instancing===!1||!H.isInstancedMesh&&Ie.instancing===!0||H.isSkinnedMesh&&Ie.skinning===!1||!H.isSkinnedMesh&&Ie.skinning===!0||H.isInstancedMesh&&Ie.instancingColor===!0&&H.instanceColor===null||H.isInstancedMesh&&Ie.instancingColor===!1&&H.instanceColor!==null||H.isInstancedMesh&&Ie.instancingMorph===!0&&H.morphTexture===null||H.isInstancedMesh&&Ie.instancingMorph===!1&&H.morphTexture!==null||Ie.envMap!==Ue||G.fog===!0&&Ie.fog!==ye||Ie.numClippingPlanes!==void 0&&(Ie.numClippingPlanes!==de.numPlanes||Ie.numIntersection!==de.numIntersection)||Ie.vertexAlphas!==Xe||Ie.vertexTangents!==$e||Ie.morphTargets!==Ne||Ie.morphNormals!==lt||Ie.morphColors!==xt||Ie.toneMapping!==gt||Ie.morphTargetsCount!==Rt)&&(tt=!0):(tt=!0,Ie.__version=G.version);let cn=Ie.currentProgram;tt===!0&&(cn=Sr(G,F,H));let Sn=!1,wi=!1,ji=!1;const ft=cn.getUniforms(),wt=Ie.uniforms;if(ae.useProgram(cn.program)&&(Sn=!0,wi=!0,ji=!0),G.id!==$&&($=G.id,wi=!0),Sn||W!==S){ae.buffers.depth.getReversed()&&S.reversedDepth!==!0&&(S._reversedDepth=!0,S.updateProjectionMatrix()),ft.setValue(E,"projectionMatrix",S.projectionMatrix),ft.setValue(E,"viewMatrix",S.matrixWorldInverse);const ui=ft.map.cameraPosition;ui!==void 0&&ui.setValue(E,R.setFromMatrixPosition(S.matrixWorld)),J.logarithmicDepthBuffer&&ft.setValue(E,"logDepthBufFC",2/(Math.log(S.far+1)/Math.LN2)),(G.isMeshPhongMaterial||G.isMeshToonMaterial||G.isMeshLambertMaterial||G.isMeshBasicMaterial||G.isMeshStandardMaterial||G.isShaderMaterial)&&ft.setValue(E,"isOrthographic",S.isOrthographicCamera===!0),W!==S&&(W=S,wi=!0,ji=!0)}if(Ie.needsLights&&(jt.state.directionalShadowMap.length>0&&ft.setValue(E,"directionalShadowMap",jt.state.directionalShadowMap,C),jt.state.spotShadowMap.length>0&&ft.setValue(E,"spotShadowMap",jt.state.spotShadowMap,C),jt.state.pointShadowMap.length>0&&ft.setValue(E,"pointShadowMap",jt.state.pointShadowMap,C)),H.isSkinnedMesh){ft.setOptional(E,H,"bindMatrix"),ft.setOptional(E,H,"bindMatrixInverse");const Tt=H.skeleton;Tt&&(Tt.boneTexture===null&&Tt.computeBoneTexture(),ft.setValue(E,"boneTexture",Tt.boneTexture,C))}H.isBatchedMesh&&(ft.setOptional(E,H,"batchingTexture"),ft.setValue(E,"batchingTexture",H._matricesTexture,C),ft.setOptional(E,H,"batchingIdTexture"),ft.setValue(E,"batchingIdTexture",H._indirectTexture,C),ft.setOptional(E,H,"batchingColorTexture"),H._colorsTexture!==null&&ft.setValue(E,"batchingColorTexture",H._colorsTexture,C));const ci=j.morphAttributes;if((ci.position!==void 0||ci.normal!==void 0||ci.color!==void 0)&&Te.update(H,j,cn),(wi||Ie.receiveShadow!==H.receiveShadow)&&(Ie.receiveShadow=H.receiveShadow,ft.setValue(E,"receiveShadow",H.receiveShadow)),(G.isMeshStandardMaterial||G.isMeshLambertMaterial||G.isMeshPhongMaterial)&&G.envMap===null&&F.environment!==null&&(wt.envMapIntensity.value=F.environmentIntensity),wt.dfgLUT!==void 0&&(wt.dfgLUT.value=lS()),wi&&(ft.setValue(E,"toneMappingExposure",T.toneMappingExposure),Ie.needsLights&&ud(wt,ji),ye&&G.fog===!0&&Ce.refreshFogUniforms(wt,ye),Ce.refreshMaterialUniforms(wt,G,we,oe,P.state.transmissionRenderTarget[S.id]),ca.upload(E,Tc(Ie),wt,C)),G.isShaderMaterial&&G.uniformsNeedUpdate===!0&&(ca.upload(E,Tc(Ie),wt,C),G.uniformsNeedUpdate=!1),G.isSpriteMaterial&&ft.setValue(E,"center",H.center),ft.setValue(E,"modelViewMatrix",H.modelViewMatrix),ft.setValue(E,"normalMatrix",H.normalMatrix),ft.setValue(E,"modelMatrix",H.matrixWorld),G.isShaderMaterial||G.isRawShaderMaterial){const Tt=G.uniformsGroups;for(let ui=0,Ki=Tt.length;ui<Ki;ui++){const wc=Tt[ui];Re.update(wc,cn),Re.bind(wc,cn)}}return cn}function ud(S,F){S.ambientLightColor.needsUpdate=F,S.lightProbe.needsUpdate=F,S.directionalLights.needsUpdate=F,S.directionalLightShadows.needsUpdate=F,S.pointLights.needsUpdate=F,S.pointLightShadows.needsUpdate=F,S.spotLights.needsUpdate=F,S.spotLightShadows.needsUpdate=F,S.rectAreaLights.needsUpdate=F,S.hemisphereLights.needsUpdate=F}function hd(S){return S.isMeshLambertMaterial||S.isMeshToonMaterial||S.isMeshPhongMaterial||S.isMeshStandardMaterial||S.isShadowMaterial||S.isShaderMaterial&&S.lights===!0}this.getActiveCubeFace=function(){return D},this.getActiveMipmapLevel=function(){return X},this.getRenderTarget=function(){return Y},this.setRenderTargetTextures=function(S,F,j){const G=m.get(S);G.__autoAllocateDepthBuffer=S.resolveDepthBuffer===!1,G.__autoAllocateDepthBuffer===!1&&(G.__useRenderToTexture=!1),m.get(S.texture).__webglTexture=F,m.get(S.depthTexture).__webglTexture=G.__autoAllocateDepthBuffer?void 0:j,G.__hasExternalTextures=!0},this.setRenderTargetFramebuffer=function(S,F){const j=m.get(S);j.__webglFramebuffer=F,j.__useDefaultFramebuffer=F===void 0};const fd=E.createFramebuffer();this.setRenderTarget=function(S,F=0,j=0){Y=S,D=F,X=j;let G=null,H=!1,ye=!1;if(S){const be=m.get(S);if(be.__useDefaultFramebuffer!==void 0){ae.bindFramebuffer(E.FRAMEBUFFER,be.__webglFramebuffer),B.copy(S.viewport),N.copy(S.scissor),ie=S.scissorTest,ae.viewport(B),ae.scissor(N),ae.setScissorTest(ie),$=-1;return}else if(be.__webglFramebuffer===void 0)C.setupRenderTarget(S);else if(be.__hasExternalTextures)C.rebindTextures(S,m.get(S.texture).__webglTexture,m.get(S.depthTexture).__webglTexture);else if(S.depthBuffer){const Xe=S.depthTexture;if(be.__boundDepthTexture!==Xe){if(Xe!==null&&m.has(Xe)&&(S.width!==Xe.image.width||S.height!==Xe.image.height))throw new Error("WebGLRenderTarget: Attached DepthTexture is initialized to the incorrect size.");C.setupDepthRenderbuffer(S)}}const De=S.texture;(De.isData3DTexture||De.isDataArrayTexture||De.isCompressedArrayTexture)&&(ye=!0);const Ue=m.get(S).__webglFramebuffer;S.isWebGLCubeRenderTarget?(Array.isArray(Ue[F])?G=Ue[F][j]:G=Ue[F],H=!0):S.samples>0&&C.useMultisampledRTT(S)===!1?G=m.get(S).__webglMultisampledFramebuffer:Array.isArray(Ue)?G=Ue[j]:G=Ue,B.copy(S.viewport),N.copy(S.scissor),ie=S.scissorTest}else B.copy(te).multiplyScalar(we).floor(),N.copy(pe).multiplyScalar(we).floor(),ie=ge;if(j!==0&&(G=fd),ae.bindFramebuffer(E.FRAMEBUFFER,G)&&ae.drawBuffers(S,G),ae.viewport(B),ae.scissor(N),ae.setScissorTest(ie),H){const be=m.get(S.texture);E.framebufferTexture2D(E.FRAMEBUFFER,E.COLOR_ATTACHMENT0,E.TEXTURE_CUBE_MAP_POSITIVE_X+F,be.__webglTexture,j)}else if(ye){const be=F;for(let De=0;De<S.textures.length;De++){const Ue=m.get(S.textures[De]);E.framebufferTextureLayer(E.FRAMEBUFFER,E.COLOR_ATTACHMENT0+De,Ue.__webglTexture,j,be)}}else if(S!==null&&j!==0){const be=m.get(S.texture);E.framebufferTexture2D(E.FRAMEBUFFER,E.COLOR_ATTACHMENT0,E.TEXTURE_2D,be.__webglTexture,j)}$=-1},this.readRenderTargetPixels=function(S,F,j,G,H,ye,Ae,be=0){if(!(S&&S.isWebGLRenderTarget)){Qe("WebGLRenderer.readRenderTargetPixels: renderTarget is not THREE.WebGLRenderTarget.");return}let De=m.get(S).__webglFramebuffer;if(S.isWebGLCubeRenderTarget&&Ae!==void 0&&(De=De[Ae]),De){ae.bindFramebuffer(E.FRAMEBUFFER,De);try{const Ue=S.textures[be],Xe=Ue.format,$e=Ue.type;if(S.textures.length>1&&E.readBuffer(E.COLOR_ATTACHMENT0+be),!J.textureFormatReadable(Xe)){Qe("WebGLRenderer.readRenderTargetPixels: renderTarget is not in RGBA or implementation defined format.");return}if(!J.textureTypeReadable($e)){Qe("WebGLRenderer.readRenderTargetPixels: renderTarget is not in UnsignedByteType or implementation defined type.");return}F>=0&&F<=S.width-G&&j>=0&&j<=S.height-H&&E.readPixels(F,j,G,H,xe.convert(Xe),xe.convert($e),ye)}finally{const Ue=Y!==null?m.get(Y).__webglFramebuffer:null;ae.bindFramebuffer(E.FRAMEBUFFER,Ue)}}},this.readRenderTargetPixelsAsync=async function(S,F,j,G,H,ye,Ae,be=0){if(!(S&&S.isWebGLRenderTarget))throw new Error("THREE.WebGLRenderer.readRenderTargetPixels: renderTarget is not THREE.WebGLRenderTarget.");let De=m.get(S).__webglFramebuffer;if(S.isWebGLCubeRenderTarget&&Ae!==void 0&&(De=De[Ae]),De)if(F>=0&&F<=S.width-G&&j>=0&&j<=S.height-H){ae.bindFramebuffer(E.FRAMEBUFFER,De);const Ue=S.textures[be],Xe=Ue.format,$e=Ue.type;if(S.textures.length>1&&E.readBuffer(E.COLOR_ATTACHMENT0+be),!J.textureFormatReadable(Xe))throw new Error("THREE.WebGLRenderer.readRenderTargetPixelsAsync: renderTarget is not in RGBA or implementation defined format.");if(!J.textureTypeReadable($e))throw new Error("THREE.WebGLRenderer.readRenderTargetPixelsAsync: renderTarget is not in UnsignedByteType or implementation defined type.");const Ne=E.createBuffer();E.bindBuffer(E.PIXEL_PACK_BUFFER,Ne),E.bufferData(E.PIXEL_PACK_BUFFER,ye.byteLength,E.STREAM_READ),E.readPixels(F,j,G,H,xe.convert(Xe),xe.convert($e),0);const lt=Y!==null?m.get(Y).__webglFramebuffer:null;ae.bindFramebuffer(E.FRAMEBUFFER,lt);const xt=E.fenceSync(E.SYNC_GPU_COMMANDS_COMPLETE,0);return E.flush(),await Mg(E,xt,4),E.bindBuffer(E.PIXEL_PACK_BUFFER,Ne),E.getBufferSubData(E.PIXEL_PACK_BUFFER,0,ye),E.deleteBuffer(Ne),E.deleteSync(xt),ye}else throw new Error("THREE.WebGLRenderer.readRenderTargetPixelsAsync: requested read bounds are out of range.")},this.copyFramebufferToTexture=function(S,F=null,j=0){const G=Math.pow(2,-j),H=Math.floor(S.image.width*G),ye=Math.floor(S.image.height*G),Ae=F!==null?F.x:0,be=F!==null?F.y:0;C.setTexture2D(S,0),E.copyTexSubImage2D(E.TEXTURE_2D,j,0,0,Ae,be,H,ye),ae.unbindTexture()};const dd=E.createFramebuffer(),pd=E.createFramebuffer();this.copyTextureToTexture=function(S,F,j=null,G=null,H=0,ye=0){let Ae,be,De,Ue,Xe,$e,Ne,lt,xt;const gt=S.isCompressedTexture?S.mipmaps[ye]:S.image;if(j!==null)Ae=j.max.x-j.min.x,be=j.max.y-j.min.y,De=j.isBox3?j.max.z-j.min.z:1,Ue=j.min.x,Xe=j.min.y,$e=j.isBox3?j.min.z:0;else{const wt=Math.pow(2,-H);Ae=Math.floor(gt.width*wt),be=Math.floor(gt.height*wt),S.isDataArrayTexture?De=gt.depth:S.isData3DTexture?De=Math.floor(gt.depth*wt):De=1,Ue=0,Xe=0,$e=0}G!==null?(Ne=G.x,lt=G.y,xt=G.z):(Ne=0,lt=0,xt=0);const ct=xe.convert(F.format),Rt=xe.convert(F.type);let Ie;F.isData3DTexture?(C.setTexture3D(F,0),Ie=E.TEXTURE_3D):F.isDataArrayTexture||F.isCompressedArrayTexture?(C.setTexture2DArray(F,0),Ie=E.TEXTURE_2D_ARRAY):(C.setTexture2D(F,0),Ie=E.TEXTURE_2D),E.pixelStorei(E.UNPACK_FLIP_Y_WEBGL,F.flipY),E.pixelStorei(E.UNPACK_PREMULTIPLY_ALPHA_WEBGL,F.premultiplyAlpha),E.pixelStorei(E.UNPACK_ALIGNMENT,F.unpackAlignment);const jt=E.getParameter(E.UNPACK_ROW_LENGTH),tt=E.getParameter(E.UNPACK_IMAGE_HEIGHT),cn=E.getParameter(E.UNPACK_SKIP_PIXELS),Sn=E.getParameter(E.UNPACK_SKIP_ROWS),wi=E.getParameter(E.UNPACK_SKIP_IMAGES);E.pixelStorei(E.UNPACK_ROW_LENGTH,gt.width),E.pixelStorei(E.UNPACK_IMAGE_HEIGHT,gt.height),E.pixelStorei(E.UNPACK_SKIP_PIXELS,Ue),E.pixelStorei(E.UNPACK_SKIP_ROWS,Xe),E.pixelStorei(E.UNPACK_SKIP_IMAGES,$e);const ji=S.isDataArrayTexture||S.isData3DTexture,ft=F.isDataArrayTexture||F.isData3DTexture;if(S.isDepthTexture){const wt=m.get(S),ci=m.get(F),Tt=m.get(wt.__renderTarget),ui=m.get(ci.__renderTarget);ae.bindFramebuffer(E.READ_FRAMEBUFFER,Tt.__webglFramebuffer),ae.bindFramebuffer(E.DRAW_FRAMEBUFFER,ui.__webglFramebuffer);for(let Ki=0;Ki<De;Ki++)ji&&(E.framebufferTextureLayer(E.READ_FRAMEBUFFER,E.COLOR_ATTACHMENT0,m.get(S).__webglTexture,H,$e+Ki),E.framebufferTextureLayer(E.DRAW_FRAMEBUFFER,E.COLOR_ATTACHMENT0,m.get(F).__webglTexture,ye,xt+Ki)),E.blitFramebuffer(Ue,Xe,Ae,be,Ne,lt,Ae,be,E.DEPTH_BUFFER_BIT,E.NEAREST);ae.bindFramebuffer(E.READ_FRAMEBUFFER,null),ae.bindFramebuffer(E.DRAW_FRAMEBUFFER,null)}else if(H!==0||S.isRenderTargetTexture||m.has(S)){const wt=m.get(S),ci=m.get(F);ae.bindFramebuffer(E.READ_FRAMEBUFFER,dd),ae.bindFramebuffer(E.DRAW_FRAMEBUFFER,pd);for(let Tt=0;Tt<De;Tt++)ji?E.framebufferTextureLayer(E.READ_FRAMEBUFFER,E.COLOR_ATTACHMENT0,wt.__webglTexture,H,$e+Tt):E.framebufferTexture2D(E.READ_FRAMEBUFFER,E.COLOR_ATTACHMENT0,E.TEXTURE_2D,wt.__webglTexture,H),ft?E.framebufferTextureLayer(E.DRAW_FRAMEBUFFER,E.COLOR_ATTACHMENT0,ci.__webglTexture,ye,xt+Tt):E.framebufferTexture2D(E.DRAW_FRAMEBUFFER,E.COLOR_ATTACHMENT0,E.TEXTURE_2D,ci.__webglTexture,ye),H!==0?E.blitFramebuffer(Ue,Xe,Ae,be,Ne,lt,Ae,be,E.COLOR_BUFFER_BIT,E.NEAREST):ft?E.copyTexSubImage3D(Ie,ye,Ne,lt,xt+Tt,Ue,Xe,Ae,be):E.copyTexSubImage2D(Ie,ye,Ne,lt,Ue,Xe,Ae,be);ae.bindFramebuffer(E.READ_FRAMEBUFFER,null),ae.bindFramebuffer(E.DRAW_FRAMEBUFFER,null)}else ft?S.isDataTexture||S.isData3DTexture?E.texSubImage3D(Ie,ye,Ne,lt,xt,Ae,be,De,ct,Rt,gt.data):F.isCompressedArrayTexture?E.compressedTexSubImage3D(Ie,ye,Ne,lt,xt,Ae,be,De,ct,gt.data):E.texSubImage3D(Ie,ye,Ne,lt,xt,Ae,be,De,ct,Rt,gt):S.isDataTexture?E.texSubImage2D(E.TEXTURE_2D,ye,Ne,lt,Ae,be,ct,Rt,gt.data):S.isCompressedTexture?E.compressedTexSubImage2D(E.TEXTURE_2D,ye,Ne,lt,gt.width,gt.height,ct,gt.data):E.texSubImage2D(E.TEXTURE_2D,ye,Ne,lt,Ae,be,ct,Rt,gt);E.pixelStorei(E.UNPACK_ROW_LENGTH,jt),E.pixelStorei(E.UNPACK_IMAGE_HEIGHT,tt),E.pixelStorei(E.UNPACK_SKIP_PIXELS,cn),E.pixelStorei(E.UNPACK_SKIP_ROWS,Sn),E.pixelStorei(E.UNPACK_SKIP_IMAGES,wi),ye===0&&F.generateMipmaps&&E.generateMipmap(Ie),ae.unbindTexture()},this.initRenderTarget=function(S){m.get(S).__webglFramebuffer===void 0&&C.setupRenderTarget(S)},this.initTexture=function(S){S.isCubeTexture?C.setTextureCube(S,0):S.isData3DTexture?C.setTexture3D(S,0):S.isDataArrayTexture||S.isCompressedArrayTexture?C.setTexture2DArray(S,0):C.setTexture2D(S,0),ae.unbindTexture()},this.resetState=function(){D=0,X=0,Y=null,ae.reset(),me.reset()},typeof __THREE_DEVTOOLS__<"u"&&__THREE_DEVTOOLS__.dispatchEvent(new CustomEvent("observe",{detail:this}))}get coordinateSystem(){return Dn}get outputColorSpace(){return this._outputColorSpace}set outputColorSpace(e){this._outputColorSpace=e;const t=this.getContext();t.drawingBufferColorSpace=et._getDrawingBufferColorSpace(e),t.unpackColorSpace=et._getUnpackColorSpace()}}const ph={type:"change"},xc={type:"start"},od={type:"end"},Zr=new Na,mh=new vi,uS=Math.cos(70*bg.DEG2RAD),yt=new O,Gt=2*Math.PI,ut={NONE:-1,ROTATE:0,DOLLY:1,PAN:2,TOUCH_ROTATE:3,TOUCH_PAN:4,TOUCH_DOLLY_PAN:5,TOUCH_DOLLY_ROTATE:6},Uo=1e-6;class hS extends m_{constructor(e,t=null){super(e,t),this.state=ut.NONE,this.target=new O,this.cursor=new O,this.minDistance=0,this.maxDistance=1/0,this.minZoom=0,this.maxZoom=1/0,this.minTargetRadius=0,this.maxTargetRadius=1/0,this.minPolarAngle=0,this.maxPolarAngle=Math.PI,this.minAzimuthAngle=-1/0,this.maxAzimuthAngle=1/0,this.enableDamping=!1,this.dampingFactor=.05,this.enableZoom=!0,this.zoomSpeed=1,this.enableRotate=!0,this.rotateSpeed=1,this.keyRotateSpeed=1,this.enablePan=!0,this.panSpeed=1,this.screenSpacePanning=!0,this.keyPanSpeed=7,this.zoomToCursor=!1,this.autoRotate=!1,this.autoRotateSpeed=2,this.keys={LEFT:"ArrowLeft",UP:"ArrowUp",RIGHT:"ArrowRight",BOTTOM:"ArrowDown"},this.mouseButtons={LEFT:Ms.ROTATE,MIDDLE:Ms.DOLLY,RIGHT:Ms.PAN},this.touches={ONE:ms.ROTATE,TWO:ms.DOLLY_PAN},this.target0=this.target.clone(),this.position0=this.object.position.clone(),this.zoom0=this.object.zoom,this._cursorStyle="auto",this._domElementKeyEvents=null,this._lastPosition=new O,this._lastQuaternion=new oi,this._lastTargetPosition=new O,this._quat=new oi().setFromUnitVectors(e.up,new O(0,1,0)),this._quatInverse=this._quat.clone().invert(),this._spherical=new Gu,this._sphericalDelta=new Gu,this._scale=1,this._panOffset=new O,this._rotateStart=new ke,this._rotateEnd=new ke,this._rotateDelta=new ke,this._panStart=new ke,this._panEnd=new ke,this._panDelta=new ke,this._dollyStart=new ke,this._dollyEnd=new ke,this._dollyDelta=new ke,this._dollyDirection=new O,this._mouse=new ke,this._performCursorZoom=!1,this._pointers=[],this._pointerPositions={},this._controlActive=!1,this._onPointerMove=dS.bind(this),this._onPointerDown=fS.bind(this),this._onPointerUp=pS.bind(this),this._onContextMenu=SS.bind(this),this._onMouseWheel=_S.bind(this),this._onKeyDown=xS.bind(this),this._onTouchStart=vS.bind(this),this._onTouchMove=MS.bind(this),this._onMouseDown=mS.bind(this),this._onMouseMove=gS.bind(this),this._interceptControlDown=yS.bind(this),this._interceptControlUp=bS.bind(this),this.domElement!==null&&this.connect(this.domElement),this.update()}set cursorStyle(e){this._cursorStyle=e,e==="grab"?this.domElement.style.cursor="grab":this.domElement.style.cursor="auto"}get cursorStyle(){return this._cursorStyle}connect(e){super.connect(e),this.domElement.addEventListener("pointerdown",this._onPointerDown),this.domElement.addEventListener("pointercancel",this._onPointerUp),this.domElement.addEventListener("contextmenu",this._onContextMenu),this.domElement.addEventListener("wheel",this._onMouseWheel,{passive:!1}),this.domElement.getRootNode().addEventListener("keydown",this._interceptControlDown,{passive:!0,capture:!0}),this.domElement.style.touchAction="none"}disconnect(){this.domElement.removeEventListener("pointerdown",this._onPointerDown),this.domElement.ownerDocument.removeEventListener("pointermove",this._onPointerMove),this.domElement.ownerDocument.removeEventListener("pointerup",this._onPointerUp),this.domElement.removeEventListener("pointercancel",this._onPointerUp),this.domElement.removeEventListener("wheel",this._onMouseWheel),this.domElement.removeEventListener("contextmenu",this._onContextMenu),this.stopListenToKeyEvents(),this.domElement.getRootNode().removeEventListener("keydown",this._interceptControlDown,{capture:!0}),this.domElement.style.touchAction="auto"}dispose(){this.disconnect()}getPolarAngle(){return this._spherical.phi}getAzimuthalAngle(){return this._spherical.theta}getDistance(){return this.object.position.distanceTo(this.target)}listenToKeyEvents(e){e.addEventListener("keydown",this._onKeyDown),this._domElementKeyEvents=e}stopListenToKeyEvents(){this._domElementKeyEvents!==null&&(this._domElementKeyEvents.removeEventListener("keydown",this._onKeyDown),this._domElementKeyEvents=null)}saveState(){this.target0.copy(this.target),this.position0.copy(this.object.position),this.zoom0=this.object.zoom}reset(){this.target.copy(this.target0),this.object.position.copy(this.position0),this.object.zoom=this.zoom0,this.object.updateProjectionMatrix(),this.dispatchEvent(ph),this.update(),this.state=ut.NONE}pan(e,t){this._pan(e,t),this.update()}dollyIn(e){this._dollyIn(e),this.update()}dollyOut(e){this._dollyOut(e),this.update()}rotateLeft(e){this._rotateLeft(e),this.update()}rotateUp(e){this._rotateUp(e),this.update()}update(e=null){const t=this.object.position;yt.copy(t).sub(this.target),yt.applyQuaternion(this._quat),this._spherical.setFromVector3(yt),this.autoRotate&&this.state===ut.NONE&&this._rotateLeft(this._getAutoRotationAngle(e)),this.enableDamping?(this._spherical.theta+=this._sphericalDelta.theta*this.dampingFactor,this._spherical.phi+=this._sphericalDelta.phi*this.dampingFactor):(this._spherical.theta+=this._sphericalDelta.theta,this._spherical.phi+=this._sphericalDelta.phi);let i=this.minAzimuthAngle,s=this.maxAzimuthAngle;isFinite(i)&&isFinite(s)&&(i<-Math.PI?i+=Gt:i>Math.PI&&(i-=Gt),s<-Math.PI?s+=Gt:s>Math.PI&&(s-=Gt),i<=s?this._spherical.theta=Math.max(i,Math.min(s,this._spherical.theta)):this._spherical.theta=this._spherical.theta>(i+s)/2?Math.max(i,this._spherical.theta):Math.min(s,this._spherical.theta)),this._spherical.phi=Math.max(this.minPolarAngle,Math.min(this.maxPolarAngle,this._spherical.phi)),this._spherical.makeSafe(),this.enableDamping===!0?this.target.addScaledVector(this._panOffset,this.dampingFactor):this.target.add(this._panOffset),this.target.sub(this.cursor),this.target.clampLength(this.minTargetRadius,this.maxTargetRadius),this.target.add(this.cursor);let r=!1;if(this.zoomToCursor&&this._performCursorZoom||this.object.isOrthographicCamera)this._spherical.radius=this._clampDistance(this._spherical.radius);else{const a=this._spherical.radius;this._spherical.radius=this._clampDistance(this._spherical.radius*this._scale),r=a!=this._spherical.radius}if(yt.setFromSpherical(this._spherical),yt.applyQuaternion(this._quatInverse),t.copy(this.target).add(yt),this.object.lookAt(this.target),this.enableDamping===!0?(this._sphericalDelta.theta*=1-this.dampingFactor,this._sphericalDelta.phi*=1-this.dampingFactor,this._panOffset.multiplyScalar(1-this.dampingFactor)):(this._sphericalDelta.set(0,0,0),this._panOffset.set(0,0,0)),this.zoomToCursor&&this._performCursorZoom){let a=null;if(this.object.isPerspectiveCamera){const o=yt.length();a=this._clampDistance(o*this._scale);const l=o-a;this.object.position.addScaledVector(this._dollyDirection,l),this.object.updateMatrixWorld(),r=!!l}else if(this.object.isOrthographicCamera){const o=new O(this._mouse.x,this._mouse.y,0);o.unproject(this.object);const l=this.object.zoom;this.object.zoom=Math.max(this.minZoom,Math.min(this.maxZoom,this.object.zoom/this._scale)),this.object.updateProjectionMatrix(),r=l!==this.object.zoom;const c=new O(this._mouse.x,this._mouse.y,0);c.unproject(this.object),this.object.position.sub(c).add(o),this.object.updateMatrixWorld(),a=yt.length()}else console.warn("WARNING: OrbitControls.js encountered an unknown camera type - zoom to cursor disabled."),this.zoomToCursor=!1;a!==null&&(this.screenSpacePanning?this.target.set(0,0,-1).transformDirection(this.object.matrix).multiplyScalar(a).add(this.object.position):(Zr.origin.copy(this.object.position),Zr.direction.set(0,0,-1).transformDirection(this.object.matrix),Math.abs(this.object.up.dot(Zr.direction))<uS?this.object.lookAt(this.target):(mh.setFromNormalAndCoplanarPoint(this.object.up,this.target),Zr.intersectPlane(mh,this.target))))}else if(this.object.isOrthographicCamera){const a=this.object.zoom;this.object.zoom=Math.max(this.minZoom,Math.min(this.maxZoom,this.object.zoom/this._scale)),a!==this.object.zoom&&(this.object.updateProjectionMatrix(),r=!0)}return this._scale=1,this._performCursorZoom=!1,r||this._lastPosition.distanceToSquared(this.object.position)>Uo||8*(1-this._lastQuaternion.dot(this.object.quaternion))>Uo||this._lastTargetPosition.distanceToSquared(this.target)>Uo?(this.dispatchEvent(ph),this._lastPosition.copy(this.object.position),this._lastQuaternion.copy(this.object.quaternion),this._lastTargetPosition.copy(this.target),!0):!1}_getAutoRotationAngle(e){return e!==null?Gt/60*this.autoRotateSpeed*e:Gt/60/60*this.autoRotateSpeed}_getZoomScale(e){const t=Math.abs(e*.01);return Math.pow(.95,this.zoomSpeed*t)}_rotateLeft(e){this._sphericalDelta.theta-=e}_rotateUp(e){this._sphericalDelta.phi-=e}_panLeft(e,t){yt.setFromMatrixColumn(t,0),yt.multiplyScalar(-e),this._panOffset.add(yt)}_panUp(e,t){this.screenSpacePanning===!0?yt.setFromMatrixColumn(t,1):(yt.setFromMatrixColumn(t,0),yt.crossVectors(this.object.up,yt)),yt.multiplyScalar(e),this._panOffset.add(yt)}_pan(e,t){const i=this.domElement;if(this.object.isPerspectiveCamera){const s=this.object.position;yt.copy(s).sub(this.target);let r=yt.length();r*=Math.tan(this.object.fov/2*Math.PI/180),this._panLeft(2*e*r/i.clientHeight,this.object.matrix),this._panUp(2*t*r/i.clientHeight,this.object.matrix)}else this.object.isOrthographicCamera?(this._panLeft(e*(this.object.right-this.object.left)/this.object.zoom/i.clientWidth,this.object.matrix),this._panUp(t*(this.object.top-this.object.bottom)/this.object.zoom/i.clientHeight,this.object.matrix)):(console.warn("WARNING: OrbitControls.js encountered an unknown camera type - pan disabled."),this.enablePan=!1)}_dollyOut(e){this.object.isPerspectiveCamera||this.object.isOrthographicCamera?this._scale/=e:(console.warn("WARNING: OrbitControls.js encountered an unknown camera type - dolly/zoom disabled."),this.enableZoom=!1)}_dollyIn(e){this.object.isPerspectiveCamera||this.object.isOrthographicCamera?this._scale*=e:(console.warn("WARNING: OrbitControls.js encountered an unknown camera type - dolly/zoom disabled."),this.enableZoom=!1)}_updateZoomParameters(e,t){if(!this.zoomToCursor)return;this._performCursorZoom=!0;const i=this.domElement.getBoundingClientRect(),s=e-i.left,r=t-i.top,a=i.width,o=i.height;this._mouse.x=s/a*2-1,this._mouse.y=-(r/o)*2+1,this._dollyDirection.set(this._mouse.x,this._mouse.y,1).unproject(this.object).sub(this.object.position).normalize()}_clampDistance(e){return Math.max(this.minDistance,Math.min(this.maxDistance,e))}_handleMouseDownRotate(e){this._rotateStart.set(e.clientX,e.clientY)}_handleMouseDownDolly(e){this._updateZoomParameters(e.clientX,e.clientX),this._dollyStart.set(e.clientX,e.clientY)}_handleMouseDownPan(e){this._panStart.set(e.clientX,e.clientY)}_handleMouseMoveRotate(e){this._rotateEnd.set(e.clientX,e.clientY),this._rotateDelta.subVectors(this._rotateEnd,this._rotateStart).multiplyScalar(this.rotateSpeed);const t=this.domElement;this._rotateLeft(Gt*this._rotateDelta.x/t.clientHeight),this._rotateUp(Gt*this._rotateDelta.y/t.clientHeight),this._rotateStart.copy(this._rotateEnd),this.update()}_handleMouseMoveDolly(e){this._dollyEnd.set(e.clientX,e.clientY),this._dollyDelta.subVectors(this._dollyEnd,this._dollyStart),this._dollyDelta.y>0?this._dollyOut(this._getZoomScale(this._dollyDelta.y)):this._dollyDelta.y<0&&this._dollyIn(this._getZoomScale(this._dollyDelta.y)),this._dollyStart.copy(this._dollyEnd),this.update()}_handleMouseMovePan(e){this._panEnd.set(e.clientX,e.clientY),this._panDelta.subVectors(this._panEnd,this._panStart).multiplyScalar(this.panSpeed),this._pan(this._panDelta.x,this._panDelta.y),this._panStart.copy(this._panEnd),this.update()}_handleMouseWheel(e){this._updateZoomParameters(e.clientX,e.clientY),e.deltaY<0?this._dollyIn(this._getZoomScale(e.deltaY)):e.deltaY>0&&this._dollyOut(this._getZoomScale(e.deltaY)),this.update()}_handleKeyDown(e){let t=!1;switch(e.code){case this.keys.UP:e.ctrlKey||e.metaKey||e.shiftKey?this.enableRotate&&this._rotateUp(Gt*this.keyRotateSpeed/this.domElement.clientHeight):this.enablePan&&this._pan(0,this.keyPanSpeed),t=!0;break;case this.keys.BOTTOM:e.ctrlKey||e.metaKey||e.shiftKey?this.enableRotate&&this._rotateUp(-Gt*this.keyRotateSpeed/this.domElement.clientHeight):this.enablePan&&this._pan(0,-this.keyPanSpeed),t=!0;break;case this.keys.LEFT:e.ctrlKey||e.metaKey||e.shiftKey?this.enableRotate&&this._rotateLeft(Gt*this.keyRotateSpeed/this.domElement.clientHeight):this.enablePan&&this._pan(this.keyPanSpeed,0),t=!0;break;case this.keys.RIGHT:e.ctrlKey||e.metaKey||e.shiftKey?this.enableRotate&&this._rotateLeft(-Gt*this.keyRotateSpeed/this.domElement.clientHeight):this.enablePan&&this._pan(-this.keyPanSpeed,0),t=!0;break}t&&(e.preventDefault(),this.update())}_handleTouchStartRotate(e){if(this._pointers.length===1)this._rotateStart.set(e.pageX,e.pageY);else{const t=this._getSecondPointerPosition(e),i=.5*(e.pageX+t.x),s=.5*(e.pageY+t.y);this._rotateStart.set(i,s)}}_handleTouchStartPan(e){if(this._pointers.length===1)this._panStart.set(e.pageX,e.pageY);else{const t=this._getSecondPointerPosition(e),i=.5*(e.pageX+t.x),s=.5*(e.pageY+t.y);this._panStart.set(i,s)}}_handleTouchStartDolly(e){const t=this._getSecondPointerPosition(e),i=e.pageX-t.x,s=e.pageY-t.y,r=Math.sqrt(i*i+s*s);this._dollyStart.set(0,r)}_handleTouchStartDollyPan(e){this.enableZoom&&this._handleTouchStartDolly(e),this.enablePan&&this._handleTouchStartPan(e)}_handleTouchStartDollyRotate(e){this.enableZoom&&this._handleTouchStartDolly(e),this.enableRotate&&this._handleTouchStartRotate(e)}_handleTouchMoveRotate(e){if(this._pointers.length==1)this._rotateEnd.set(e.pageX,e.pageY);else{const i=this._getSecondPointerPosition(e),s=.5*(e.pageX+i.x),r=.5*(e.pageY+i.y);this._rotateEnd.set(s,r)}this._rotateDelta.subVectors(this._rotateEnd,this._rotateStart).multiplyScalar(this.rotateSpeed);const t=this.domElement;this._rotateLeft(Gt*this._rotateDelta.x/t.clientHeight),this._rotateUp(Gt*this._rotateDelta.y/t.clientHeight),this._rotateStart.copy(this._rotateEnd)}_handleTouchMovePan(e){if(this._pointers.length===1)this._panEnd.set(e.pageX,e.pageY);else{const t=this._getSecondPointerPosition(e),i=.5*(e.pageX+t.x),s=.5*(e.pageY+t.y);this._panEnd.set(i,s)}this._panDelta.subVectors(this._panEnd,this._panStart).multiplyScalar(this.panSpeed),this._pan(this._panDelta.x,this._panDelta.y),this._panStart.copy(this._panEnd)}_handleTouchMoveDolly(e){const t=this._getSecondPointerPosition(e),i=e.pageX-t.x,s=e.pageY-t.y,r=Math.sqrt(i*i+s*s);this._dollyEnd.set(0,r),this._dollyDelta.set(0,Math.pow(this._dollyEnd.y/this._dollyStart.y,this.zoomSpeed)),this._dollyOut(this._dollyDelta.y),this._dollyStart.copy(this._dollyEnd);const a=(e.pageX+t.x)*.5,o=(e.pageY+t.y)*.5;this._updateZoomParameters(a,o)}_handleTouchMoveDollyPan(e){this.enableZoom&&this._handleTouchMoveDolly(e),this.enablePan&&this._handleTouchMovePan(e)}_handleTouchMoveDollyRotate(e){this.enableZoom&&this._handleTouchMoveDolly(e),this.enableRotate&&this._handleTouchMoveRotate(e)}_addPointer(e){this._pointers.push(e.pointerId)}_removePointer(e){delete this._pointerPositions[e.pointerId];for(let t=0;t<this._pointers.length;t++)if(this._pointers[t]==e.pointerId){this._pointers.splice(t,1);return}}_isTrackingPointer(e){for(let t=0;t<this._pointers.length;t++)if(this._pointers[t]==e.pointerId)return!0;return!1}_trackPointer(e){let t=this._pointerPositions[e.pointerId];t===void 0&&(t=new ke,this._pointerPositions[e.pointerId]=t),t.set(e.pageX,e.pageY)}_getSecondPointerPosition(e){const t=e.pointerId===this._pointers[0]?this._pointers[1]:this._pointers[0];return this._pointerPositions[t]}_customWheelEvent(e){const t=e.deltaMode,i={clientX:e.clientX,clientY:e.clientY,deltaY:e.deltaY};switch(t){case 1:i.deltaY*=16;break;case 2:i.deltaY*=100;break}return e.ctrlKey&&!this._controlActive&&(i.deltaY*=10),i}}function fS(n){this.enabled!==!1&&(this._pointers.length===0&&(this.domElement.setPointerCapture(n.pointerId),this.domElement.ownerDocument.addEventListener("pointermove",this._onPointerMove),this.domElement.ownerDocument.addEventListener("pointerup",this._onPointerUp)),!this._isTrackingPointer(n)&&(this._addPointer(n),n.pointerType==="touch"?this._onTouchStart(n):this._onMouseDown(n),this._cursorStyle==="grab"&&(this.domElement.style.cursor="grabbing")))}function dS(n){this.enabled!==!1&&(n.pointerType==="touch"?this._onTouchMove(n):this._onMouseMove(n))}function pS(n){switch(this._removePointer(n),this._pointers.length){case 0:this.domElement.releasePointerCapture(n.pointerId),this.domElement.ownerDocument.removeEventListener("pointermove",this._onPointerMove),this.domElement.ownerDocument.removeEventListener("pointerup",this._onPointerUp),this.dispatchEvent(od),this.state=ut.NONE,this._cursorStyle==="grab"&&(this.domElement.style.cursor="grab");break;case 1:const e=this._pointers[0],t=this._pointerPositions[e];this._onTouchStart({pointerId:e,pageX:t.x,pageY:t.y});break}}function mS(n){let e;switch(n.button){case 0:e=this.mouseButtons.LEFT;break;case 1:e=this.mouseButtons.MIDDLE;break;case 2:e=this.mouseButtons.RIGHT;break;default:e=-1}switch(e){case Ms.DOLLY:if(this.enableZoom===!1)return;this._handleMouseDownDolly(n),this.state=ut.DOLLY;break;case Ms.ROTATE:if(n.ctrlKey||n.metaKey||n.shiftKey){if(this.enablePan===!1)return;this._handleMouseDownPan(n),this.state=ut.PAN}else{if(this.enableRotate===!1)return;this._handleMouseDownRotate(n),this.state=ut.ROTATE}break;case Ms.PAN:if(n.ctrlKey||n.metaKey||n.shiftKey){if(this.enableRotate===!1)return;this._handleMouseDownRotate(n),this.state=ut.ROTATE}else{if(this.enablePan===!1)return;this._handleMouseDownPan(n),this.state=ut.PAN}break;default:this.state=ut.NONE}this.state!==ut.NONE&&this.dispatchEvent(xc)}function gS(n){switch(this.state){case ut.ROTATE:if(this.enableRotate===!1)return;this._handleMouseMoveRotate(n);break;case ut.DOLLY:if(this.enableZoom===!1)return;this._handleMouseMoveDolly(n);break;case ut.PAN:if(this.enablePan===!1)return;this._handleMouseMovePan(n);break}}function _S(n){this.enabled===!1||this.enableZoom===!1||this.state!==ut.NONE||(n.preventDefault(),this.dispatchEvent(xc),this._handleMouseWheel(this._customWheelEvent(n)),this.dispatchEvent(od))}function xS(n){this.enabled!==!1&&this._handleKeyDown(n)}function vS(n){switch(this._trackPointer(n),this._pointers.length){case 1:switch(this.touches.ONE){case ms.ROTATE:if(this.enableRotate===!1)return;this._handleTouchStartRotate(n),this.state=ut.TOUCH_ROTATE;break;case ms.PAN:if(this.enablePan===!1)return;this._handleTouchStartPan(n),this.state=ut.TOUCH_PAN;break;default:this.state=ut.NONE}break;case 2:switch(this.touches.TWO){case ms.DOLLY_PAN:if(this.enableZoom===!1&&this.enablePan===!1)return;this._handleTouchStartDollyPan(n),this.state=ut.TOUCH_DOLLY_PAN;break;case ms.DOLLY_ROTATE:if(this.enableZoom===!1&&this.enableRotate===!1)return;this._handleTouchStartDollyRotate(n),this.state=ut.TOUCH_DOLLY_ROTATE;break;default:this.state=ut.NONE}break;default:this.state=ut.NONE}this.state!==ut.NONE&&this.dispatchEvent(xc)}function MS(n){switch(this._trackPointer(n),this.state){case ut.TOUCH_ROTATE:if(this.enableRotate===!1)return;this._handleTouchMoveRotate(n),this.update();break;case ut.TOUCH_PAN:if(this.enablePan===!1)return;this._handleTouchMovePan(n),this.update();break;case ut.TOUCH_DOLLY_PAN:if(this.enableZoom===!1&&this.enablePan===!1)return;this._handleTouchMoveDollyPan(n),this.update();break;case ut.TOUCH_DOLLY_ROTATE:if(this.enableZoom===!1&&this.enableRotate===!1)return;this._handleTouchMoveDollyRotate(n),this.update();break;default:this.state=ut.NONE}}function SS(n){this.enabled!==!1&&n.preventDefault()}function yS(n){n.key==="Control"&&(this._controlActive=!0,this.domElement.getRootNode().addEventListener("keyup",this._interceptControlUp,{passive:!0,capture:!0}))}function bS(n){n.key==="Control"&&(this._controlActive=!1,this.domElement.getRootNode().removeEventListener("keyup",this._interceptControlUp,{passive:!0,capture:!0}))}function ES(n,e){const t=new hS(n,e);return t.enableDamping=!0,t.dampingFactor=.08,t.enablePan=!0,t.screenSpacePanning=!0,t.minDistance=6,t.maxDistance=120,t.maxPolarAngle=Math.PI/2.02,t.mouseButtons.LEFT=0,t.mouseButtons.MIDDLE=1,t.mouseButtons.RIGHT=2,t}function Ys(n,e,t){return`${n}:${e}:${t}`}function _i(n){return Number(n)+.5}function Lt(n="plain"){const t=document.createElement("canvas");t.width=64,t.height=64;const i=t.getContext("2d");if(!i)return null;if(n==="wood_oak"){i.fillStyle="#8b5a2b",i.fillRect(0,0,64,64);for(let r=0;r<8;r+=1){i.strokeStyle=`rgba(60,30,10,${.12+r%3*.08})`,i.lineWidth=2;const a=r/8*64;i.beginPath(),i.moveTo(0,a),i.bezierCurveTo(64*.3,a+2,64*.7,a-2,64,a+1),i.stroke()}}else if(n==="wood_walnut"){i.fillStyle="#5b3a29",i.fillRect(0,0,64,64);for(let r=0;r<10;r+=1){i.strokeStyle=`rgba(30,18,12,${.14+r%3*.1})`,i.lineWidth=2;const a=r/10*64;i.beginPath(),i.moveTo(0,a),i.bezierCurveTo(64*.2,a-2,64*.8,a+2,64,a),i.stroke()}}else if(n==="wood_pine"){i.fillStyle="#c9924e",i.fillRect(0,0,64,64);for(let r=0;r<9;r+=1){i.strokeStyle=`rgba(120,70,30,${.11+r%2*.08})`,i.lineWidth=1.8;const a=r/9*64;i.beginPath(),i.moveTo(0,a),i.bezierCurveTo(64*.35,a+1,64*.65,a-1,64,a+1),i.stroke()}}else if(n==="concrete"){i.fillStyle="#7b8794",i.fillRect(0,0,64,64);for(let r=0;r<120;r+=1){const a=1+Math.random()*3;i.fillStyle=`rgba(230,235,240,${.08+Math.random()*.2})`,i.fillRect(Math.random()*64,Math.random()*64,a,a)}}else if(n==="concrete_dark"){i.fillStyle="#4b5563",i.fillRect(0,0,64,64);for(let r=0;r<140;r+=1){const a=1+Math.random()*2.5;i.fillStyle=`rgba(220,225,230,${.06+Math.random()*.14})`,i.fillRect(Math.random()*64,Math.random()*64,a,a)}}else if(n==="concrete_light"){i.fillStyle="#cfd6de",i.fillRect(0,0,64,64);for(let r=0;r<130;r+=1){const a=1+Math.random()*2.2;i.fillStyle=`rgba(120,130,145,${.05+Math.random()*.12})`,i.fillRect(Math.random()*64,Math.random()*64,a,a)}}else if(n==="sea_shallow"){const r=i.createLinearGradient(0,0,64,64);r.addColorStop(0,"#2dd4bf"),r.addColorStop(1,"#0ea5e9"),i.fillStyle=r,i.fillRect(0,0,64,64);for(let a=0;a<8;a+=1){i.strokeStyle=`rgba(255,255,255,${.09+a*.01})`,i.lineWidth=1.5;const o=a/8*64;i.beginPath(),i.moveTo(0,o),i.bezierCurveTo(64*.2,o+2,64*.8,o-2,64,o+1),i.stroke()}}else if(n==="sea_deep"){const r=i.createLinearGradient(0,0,64,64);r.addColorStop(0,"#1d4ed8"),r.addColorStop(1,"#0f172a"),i.fillStyle=r,i.fillRect(0,0,64,64);for(let a=0;a<9;a+=1){i.strokeStyle=`rgba(148,197,255,${.08+a*.008})`,i.lineWidth=1.2;const o=a/9*64;i.beginPath(),i.moveTo(0,o),i.bezierCurveTo(64*.25,o-2,64*.75,o+2,64,o),i.stroke()}}else if(n==="sea_foam"){i.fillStyle="#0284c7",i.fillRect(0,0,64,64);for(let r=0;r<100;r+=1){const a=Math.random()*2.8;i.fillStyle=`rgba(226,248,255,${.14+Math.random()*.28})`,i.beginPath(),i.arc(Math.random()*64,Math.random()*64,a,0,Math.PI*2),i.fill()}}else if(n==="grass"){i.fillStyle="#22c55e",i.fillRect(0,0,64,64);for(let r=0;r<180;r+=1)i.fillStyle=`rgba(10,110,40,${.08+Math.random()*.2})`,i.fillRect(Math.random()*64,Math.random()*64,1,2+Math.random()*3)}else if(n==="grass_fresh"){i.fillStyle="#84cc16",i.fillRect(0,0,64,64);for(let r=0;r<170;r+=1)i.fillStyle=`rgba(50,130,20,${.08+Math.random()*.2})`,i.fillRect(Math.random()*64,Math.random()*64,1.2,2+Math.random()*3)}else if(n==="grass_dark"){i.fillStyle="#15803d",i.fillRect(0,0,64,64);for(let r=0;r<190;r+=1)i.fillStyle=`rgba(5,60,20,${.08+Math.random()*.22})`,i.fillRect(Math.random()*64,Math.random()*64,1,2+Math.random()*3)}else if(n==="flat_matte")i.fillStyle="#f8fafc",i.fillRect(0,0,64,64);else if(n==="flat_soft"){const r=i.createLinearGradient(0,0,64,64);r.addColorStop(0,"#ffffff"),r.addColorStop(1,"#e2e8f0"),i.fillStyle=r,i.fillRect(0,0,64,64)}else if(n==="flat_gloss"){const r=i.createLinearGradient(0,0,64,64);r.addColorStop(0,"#ffffff"),r.addColorStop(.45,"#f1f5f9"),r.addColorStop(1,"#cbd5e1"),i.fillStyle=r,i.fillRect(0,0,64,64)}else i.fillStyle="#ffffff",i.fillRect(0,0,64,64);const s=new Kg(t);return s.colorSpace=Zt,s.wrapS=ur,s.wrapT=ur,s.repeat.set(1,1),s.needsUpdate=!0,s}class TS{constructor({container:e,onHoverCell:t,onBlocksChanged:i}){this.container=e,this.onHoverCell=t,this.onBlocksChanged=i,this.mode="add",this.theme="light",this.activeColor="#38bdf8",this.activeTexture="plain",this.blocks=new Map,this.animations=[],this.raycaster=new d_,this.pointer=new ke,this.mouseEvent=null,this.scene=new Og,this.scene.background=new Ze("#dbe8ff"),this.scene.fog=new pc("#dbe8ff",55,150),this.camera=new an(52,1,.1,400),this.camera.position.set(22,24,22),this.renderer=new cS({antialias:!0,alpha:!1}),this.renderer.setPixelRatio(Math.min(window.devicePixelRatio||1,2)),this.renderer.shadowMap.enabled=!0,this.renderer.shadowMap.type=Af,this.container.appendChild(this.renderer.domElement),this.controls=ES(this.camera,this.renderer.domElement),this.controls.target.set(0,0,0),this.controls.update(),this.grid=new p_(64,64,"#8fb0ea","#c3d6f5"),this.grid.position.y=0,this.scene.add(this.grid);const s=new vr(64,64),r=new Yr({color:"#e9f1ff",metalness:.02,roughness:.95,side:Pn});this.groundPlane=new qt(s,r),this.groundPlane.rotation.x=-Math.PI/2,this.groundPlane.receiveShadow=!0,this.scene.add(this.groundPlane);const a=new l_("#ffffff",.62);this.scene.add(a),this.ambientLight=a;const o=new s_("#dbeafe","#c7d2fe",.55);this.scene.add(o),this.hemiLight=o;const l=new o_("#fff4e0",1.05);l.position.set(18,30,12),l.shadow.normalBias=.03,l.shadow.radius=3,l.castShadow=!0,l.shadow.mapSize.width=2048,l.shadow.mapSize.height=2048,l.shadow.camera.left=-48,l.shadow.camera.right=48,l.shadow.camera.top=48,l.shadow.camera.bottom=-48,this.scene.add(l),this.keyLight=l;const c=new bi(1.02,1.02,1.02),u=new Yr({color:"#38bdf8",transparent:!0,opacity:.3,emissive:"#38bdf8",emissiveIntensity:.25,depthWrite:!1});this.hoverCube=new qt(c,u),this.hoverCube.visible=!1,this.scene.add(this.hoverCube);const f=new bi(1,1,1),h=new Yr({color:this.activeColor,transparent:!0,opacity:.45,emissive:this.activeColor,emissiveIntensity:.1});this.previewCube=new qt(f,h),this.previewCube.visible=!1,this.scene.add(this.previewCube),this.baseGeometry=new bi(1,1,1),this.baseGeometry.computeVertexNormals(),this.textureCache={plain:Lt("plain"),wood_oak:Lt("wood_oak"),wood_walnut:Lt("wood_walnut"),wood_pine:Lt("wood_pine"),concrete:Lt("concrete"),concrete_dark:Lt("concrete_dark"),concrete_light:Lt("concrete_light"),sea_shallow:Lt("sea_shallow"),sea_deep:Lt("sea_deep"),sea_foam:Lt("sea_foam"),grass:Lt("grass"),grass_fresh:Lt("grass_fresh"),grass_dark:Lt("grass_dark"),flat_matte:Lt("flat_matte"),flat_soft:Lt("flat_soft"),flat_gloss:Lt("flat_gloss")},this.meshGroup=new Ks,this.scene.add(this.meshGroup),this.instanceMeshes=[],this.timer=new h_,this.timer.connect?.(document),this.isPointerDown=!1,this.activePointerId=null,this.lastDragActionKey="",this.pointerDownButton=-1,this.pointerDownPos={x:0,y:0},this.pointerDownAt=0,this.didDragSincePointerDown=!1,this.pointerDragDistance=0,this.pointerDownCameraPos=new O,this.pointerDownCameraQuat=new oi,this.clickDragThreshold=6,this.onPointerMove=this.onPointerMove.bind(this),this.onPointerDown=this.onPointerDown.bind(this),this.onPointerUp=this.onPointerUp.bind(this),this.onResize=this.onResize.bind(this),this.animate=this.animate.bind(this),this.renderer.domElement.addEventListener("pointermove",this.onPointerMove),this.renderer.domElement.addEventListener("pointerdown",this.onPointerDown),window.addEventListener("pointerup",this.onPointerUp),this.renderer.domElement.addEventListener("contextmenu",p=>p.preventDefault()),window.addEventListener("resize",this.onResize),this.resizeObserver=new ResizeObserver(()=>this.onResize()),this.resizeObserver.observe(this.container),this.onResize(),this.setTheme("light"),this.animate()}dispose(){this.renderer.domElement.removeEventListener("pointermove",this.onPointerMove),this.renderer.domElement.removeEventListener("pointerdown",this.onPointerDown),window.removeEventListener("pointerup",this.onPointerUp),window.removeEventListener("resize",this.onResize),this.resizeObserver?.disconnect(),this.controls.dispose(),this.renderer.dispose()}setMode(e){this.mode=e}setTheme(e="light"){this.theme=e==="dark"?"dark":"light";const t=this.theme==="dark",i=t?"#0a1738":"#dbe8ff";this.scene.background.set(i),this.scene.fog&&this.scene.fog.color.set(i);const s=this.grid.material;Array.isArray(s)?(s[0]&&s[0].color.set(t?"#3a5db0":"#8fb0ea"),s[1]&&s[1].color.set(t?"#1f3470":"#c3d6f5"),s.forEach(r=>{r.needsUpdate=!0})):s&&(s.color.set(t?"#3a5db0":"#8fb0ea"),s.needsUpdate=!0),this.groundPlane.material.color.set(t?"#0d1d44":"#e9f1ff"),this.ambientLight&&(this.ambientLight.color.set(t?"#c9d8ff":"#ffffff"),this.ambientLight.intensity=t?.5:.62),this.hemiLight&&(this.hemiLight.color.set(t?"#9db8f5":"#dbeafe"),this.hemiLight.groundColor.set(t?"#08112b":"#c7d2fe"),this.hemiLight.intensity=t?.4:.55),this.keyLight&&(this.keyLight.color.set(t?"#d6e2ff":"#fff4e0"),this.keyLight.intensity=t?.95:1.05)}setActiveMaterial({color:e,texture:t}){e&&(this.activeColor=e),t&&(this.activeTexture=t),this.previewCube.material.color.set(this.activeColor),this.previewCube.material.emissive.set(this.activeColor);const i=this.textureCache[this.activeTexture]||null;this.previewCube.material.map=i,this.previewCube.material.needsUpdate=!0}clearAll(){this.blocks.clear(),this.rebuildInstances(),this.emitChange()}setBlocks(e=[]){this.blocks.clear();for(const t of e){const i=Number(t.x),s=Number(t.y),r=Number(t.z);!Number.isFinite(i)||!Number.isFinite(s)||!Number.isFinite(r)||this.blocks.set(Ys(i,s,r),{x:i,y:s,z:r,color:String(t.color||"#38bdf8"),texture:String(t.texture||"plain")})}this.rebuildInstances(),this.emitChange()}getBlocks(){return Array.from(this.blocks.values())}resetCamera(){this.camera.position.set(22,24,22),this.controls.target.set(0,0,0),this.controls.update()}onResize(){const e=this.container.getBoundingClientRect(),t=Math.max(1,Math.round(window.innerWidth||e.width||this.container.clientWidth||800)),i=Math.max(1,Math.round(window.innerHeight||e.height||this.container.clientHeight||600));this.camera.aspect=t/i,this.camera.updateProjectionMatrix(),this.renderer.setSize(t,i)}getHighestY(e,t){let i=-1;for(const s of this.blocks.values())s.x===e&&s.z===t&&s.y>i&&(i=s.y);return i}getHitBlockFromIntersection(e){const t=e?.object;if(!(t instanceof Lu))return null;const i=Number(e.instanceId);if(!Number.isInteger(i)||i<0)return null;const s=new ht,r=new O;t.getMatrixAt(i,s),r.setFromMatrixPosition(s);const a=Math.floor(r.x),o=Math.floor(r.y),l=Math.floor(r.z);return{x:a,y:o,z:l}}resolveFaceNormal(e){if(!e?.face?.normal)return new O(0,1,0);const t=e.face.normal.clone(),i=Math.abs(t.x),s=Math.abs(t.y),r=Math.abs(t.z);return s>=i&&s>=r?new O(0,Math.sign(t.y)||1,0):i>=s&&i>=r?new O(Math.sign(t.x)||1,0,0):new O(0,0,Math.sign(t.z)||1)}raycastCell(e){const t=this.renderer.domElement.getBoundingClientRect();this.pointer.x=(e.clientX-t.left)/t.width*2-1,this.pointer.y=-((e.clientY-t.top)/t.height)*2+1,this.raycaster.setFromCamera(this.pointer,this.camera);const i=[...this.instanceMeshes,this.groundPlane],s=this.raycaster.intersectObjects(i,!1);if(!s.length)return null;const r=s[0],a=this.getHitBlockFromIntersection(r);if(a){const f=this.resolveFaceNormal(r),h={x:a.x+f.x,y:a.y+f.y,z:a.z+f.z};return h.y<0&&(h.y=0),{addCell:h,eraseCell:a}}const o=r.point,l=Math.floor(o.x),c=Math.floor(o.z),u=this.getHighestY(l,c);return{addCell:{x:l,y:u+1,z:c},eraseCell:u>=0?{x:l,y:u,z:c}:null}}applyActionAtCell(e,t=this.mode){if(!e)return;if(t==="erase"){const s=e.eraseCell;s&&this.deleteBlockAt(s.x,s.y,s.z);return}const i=e.addCell;i&&this.addBlock({x:i.x,y:i.y,z:i.z,color:this.activeColor,texture:this.activeTexture})}onPointerMove(e){this.mouseEvent=e;const t=this.raycastCell(e);if(!t){this.hoverCube.visible=!1,this.previewCube.visible=!1;return}const i=t.addCell,s=t.eraseCell,r=this.mode==="erase"&&s?s:i;if(this.hoverCube.visible=!0,this.hoverCube.position.set(_i(r.x),r.y+.5,_i(r.z)),this.previewCube.visible=this.mode==="add",this.previewCube.position.set(_i(i.x),i.y+.5,_i(i.z)),this.onHoverCell&&this.onHoverCell(r),this.isPointerDown&&this.activePointerId===e.pointerId){const a=Number(e.clientX||0)-this.pointerDownPos.x,o=Number(e.clientY||0)-this.pointerDownPos.y;this.pointerDragDistance=Math.max(this.pointerDragDistance,Math.hypot(a,o)),this.pointerDragDistance>1.5&&(this.didDragSincePointerDown=!0)}}onPointerDown(e){e.button!==1&&(this.isPointerDown=!0,this.activePointerId=e.pointerId,this.pointerDownButton=Number(e.button),this.pointerDownPos={x:Number(e.clientX||0),y:Number(e.clientY||0)},this.pointerDownAt=performance.now(),this.didDragSincePointerDown=!1,this.pointerDragDistance=0,this.pointerDownCameraPos.copy(this.camera.position),this.pointerDownCameraQuat.copy(this.camera.quaternion),this.lastDragActionKey="")}onPointerUp(e){if(this.activePointerId!==null&&e.pointerId!==this.activePointerId)return;const t=this.pointerDownButton,i=Math.max(0,performance.now()-Number(this.pointerDownAt||0)),s=this.didDragSincePointerDown||this.pointerDragDistance>this.clickDragThreshold,r=this.camera.position.distanceTo(this.pointerDownCameraPos)>.001||1-Math.abs(this.camera.quaternion.dot(this.pointerDownCameraQuat))>1e-5,a=i<=220;if(!s&&!r&&a&&(t===0||t===2)){const l=this.raycastCell(e);l&&(t===2?this.applyActionAtCell(l,"erase"):this.applyActionAtCell(l,this.mode))}this.isPointerDown=!1,this.activePointerId=null,this.pointerDownButton=-1,this.pointerDownAt=0,this.didDragSincePointerDown=!1,this.pointerDragDistance=0,this.lastDragActionKey=""}addBlock(e){const t=Ys(e.x,e.y,e.z);this.blocks.has(t)||(this.blocks.set(t,{...e}),this.animations.push({key:t,startAt:performance.now(),duration:180}),this.rebuildInstances(),this.emitChange())}deleteTopBlock(e,t){const i=this.getHighestY(e,t);i<0||(this.blocks.delete(Ys(e,i,t)),this.rebuildInstances(),this.emitChange())}deleteBlockAt(e,t,i){const s=Ys(e,t,i);this.blocks.has(s)&&(this.blocks.delete(s),this.rebuildInstances(),this.emitChange())}emitChange(){this.onBlocksChanged&&this.onBlocksChanged(this.getBlocks())}rebuildInstances(){for(const i of this.instanceMeshes)this.meshGroup.remove(i),i.dispose?.(),i.material?.dispose?.();this.instanceMeshes=[];const e=new Map;for(const i of this.blocks.values()){const s=`${i.texture}::${i.color}`;e.has(s)||e.set(s,[]),e.get(s).push(i)}const t=new Mt;for(const[i,s]of e.entries()){const[r,a]=i.split("::"),o=this.textureCache[r]||null,l=new Yr({color:a,map:o,metalness:.05,roughness:.72}),c=new Lu(this.baseGeometry,l,s.length);c.castShadow=!0,c.receiveShadow=!0,s.forEach((u,f)=>{t.position.set(_i(u.x),u.y+.5,_i(u.z)),t.scale.set(1,1,1),t.updateMatrix(),c.setMatrixAt(f,t.matrix)}),c.instanceMatrix.needsUpdate=!0,this.meshGroup.add(c),this.instanceMeshes.push(c)}}applyAnimations(){if(!this.animations.length)return;const e=performance.now(),t=new Map;if(this.animations=this.animations.filter(i=>{const s=e-i.startAt,r=Math.min(1,s/i.duration),a=r<1?.2+.8*(1-Math.pow(1-r,3)):1;return t.set(i.key,a),r<1}),!!t.size)for(const i of this.instanceMeshes){const s=new Mt,r=i.material?.color?.getStyle?.()||"",a=Object.keys(this.textureCache).find(l=>i.material?.map===this.textureCache[l])||"plain";Array.from(this.blocks.values()).filter(l=>{const c=l.texture===a,u=new Ze(l.color).getStyle();return c&&u===r}).forEach((l,c)=>{const u=t.get(Ys(l.x,l.y,l.z))||1;s.position.set(_i(l.x),l.y+.5,_i(l.z)),s.scale.set(u,u,u),s.updateMatrix(),i.setMatrixAt(c,s.matrix)}),i.instanceMatrix.needsUpdate=!0}}animate(){requestAnimationFrame(this.animate),this.timer.update(),!(this.timer.getDelta()>.3)&&(this.controls.update(),this.applyAnimations(),this.renderer.render(this.scene,this.camera))}}const AS=(n,e)=>{const t=n.__vccOpts||n;for(const[i,s]of e)t[i]=s;return t},wS={class:"studio-frame"},CS={class:"topbar"},RS={class:"top-actions"},PS=["disabled"],DS=["disabled"],LS={class:"tool-panel"},IS={class:"seg",role:"group","aria-label":"Araç"},US={class:"group"},NS={class:"swatches"},FS=["title","onClick"],OS={class:"swatch custom",title:"Özel renk"},BS={class:"group group-scroll"},zS={class:"tex-label"},HS={class:"tex-grid"},VS=["title","onClick"],kS={class:"tool-foot"},GS={class:"hud"},WS={class:"chip"},XS={class:"chip"},YS={class:"chip chip-status"},qS={class:"chip chip-tip"},jS={__name:"BlockBuilder",props:{visible:{type:Boolean,default:!1},standalone:{type:Boolean,default:!1}},emits:["close","saved","loaded","changed"],setup(n,{emit:e}){const t=n,i=e,s=nn(null),r=nn(null),a=nn("add"),o=nn("light"),l=nn("#38bdf8"),c=nn("flat_matte"),u=nn(null),f=nn([]),h=nn("Tasarim"),p=nn(null),_=nn(!1),M=nn("Hazır"),g=[{label:"Düz Renk",items:[{value:"flat_matte",label:"Düz Mat"},{value:"flat_soft",label:"Düz Soft"},{value:"flat_gloss",label:"Düz Parlak"},{value:"plain",label:"Düz Klasik"}]},{label:"Ahşap",items:[{value:"wood_oak",label:"Meşe"},{value:"wood_walnut",label:"Ceviz"},{value:"wood_pine",label:"Çam"}]},{label:"Beton / Taş",items:[{value:"concrete",label:"Beton Standart"},{value:"concrete_dark",label:"Beton Koyu"},{value:"concrete_light",label:"Beton Açık"}]},{label:"Su / Deniz",items:[{value:"sea_shallow",label:"Deniz Sığ"},{value:"sea_deep",label:"Deniz Derin"},{value:"sea_foam",label:"Deniz Köpük"}]},{label:"Çim / Doğa",items:[{value:"grass",label:"Çim Standart"},{value:"grass_fresh",label:"Çim Taze"},{value:"grass_dark",label:"Çim Koyu"}]}],d=["#38bdf8","#2563eb","#7c3aed","#ec4899","#ef4444","#f97316","#facc15","#22c55e","#14b8a6","#ffffff","#94a3b8","#1e293b"],y={flat_matte:"linear-gradient(145deg, #cbd5e1, #94a3b8)",flat_soft:"linear-gradient(145deg, #f1f5f9, #cbd5e1)",flat_gloss:"linear-gradient(145deg, #ffffff 10%, #93c5fd 45%, #3b82f6)",plain:"linear-gradient(145deg, #e2e8f0, #cbd5e1)",wood_oak:"repeating-linear-gradient(90deg, #c08a4a 0 6px, #a9733a 6px 9px)",wood_walnut:"repeating-linear-gradient(90deg, #6b4423 0 6px, #55341a 6px 9px)",wood_pine:"repeating-linear-gradient(90deg, #e3b97a 0 6px, #cfa062 6px 9px)",concrete:"radial-gradient(circle at 30% 30%, #b6bcc6 0 2px, transparent 3px) 0 0 / 9px 9px, #9ca3af",concrete_dark:"radial-gradient(circle at 30% 30%, #6b7280 0 2px, transparent 3px) 0 0 / 9px 9px, #4b5563",concrete_light:"radial-gradient(circle at 30% 30%, #e5e7eb 0 2px, transparent 3px) 0 0 / 9px 9px, #d1d5db",sea_shallow:"repeating-linear-gradient(160deg, #67e8f9 0 5px, #22d3ee 5px 10px)",sea_deep:"repeating-linear-gradient(160deg, #1d4ed8 0 5px, #1e3a8a 5px 10px)",sea_foam:"repeating-linear-gradient(160deg, #e0f7ff 0 5px, #7dd3fc 5px 10px)",grass:"repeating-linear-gradient(100deg, #4ade80 0 3px, #22c55e 3px 6px)",grass_fresh:"repeating-linear-gradient(100deg, #a3e635 0 3px, #65a30d 3px 6px)",grass_dark:"repeating-linear-gradient(100deg, #15803d 0 3px, #14532d 3px 6px)"};function A(se){return{background:y[se]||"#cbd5e1"}}function b(se){l.value=se,B()}function P(se){c.value=se,B()}const L=bf(()=>{if(!u.value)return"-";const se=u.value;return`${se.x}, ${se.y}, ${se.z}`});function I(){i("close")}function x(){const se=localStorage.getItem("mysql_auth_user"),oe=se?JSON.parse(se):null,we=String(oe?.uid||oe?.id||"").trim(),Ge=String(oe?.token||"").trim();if(!we||!Ge)throw new Error("Önce giriş yapmalısınız.");return{headers:{"Content-Type":"application/json","X-Client-Uid":we,"X-Client-Token":Ge,Authorization:`Bearer ${Ge}`}}}function T(){const se=String(document.querySelector('meta[name="app-base-url"]')?.content||"").trim().replace(/\/+$/,"");return se?`${se}/api/client`:`${/\/index\.php(\/|$)/i.test(window.location.pathname)?`${window.location.origin}/index.php`:window.location.origin}/api/client`}function Q(se){return`${T().replace(/\/+$/,"")}/block-builder${se}`}async function D(se,oe={}){const we=x(),Ge=await fetch(Q(se),{...oe,headers:{...oe.headers||{},...we.headers}}),nt=await Ge.json().catch(()=>({}));if(!Ge.ok)throw new Error(String(nt?.message||`HTTP ${Ge.status}`));return nt}function X(){!s.value||r.value||(r.value=new TS({container:s.value,onHoverCell:se=>{u.value=se},onBlocksChanged:se=>{f.value=se,i("changed",se)}}),r.value.setMode(a.value),r.value.setActiveMaterial({color:l.value,texture:c.value}),r.value.setTheme(o.value))}function Y(){r.value&&(r.value.dispose(),s.value?.replaceChildren(),r.value=null)}function $(se){a.value=se,r.value?.setMode(se)}function W(){o.value=o.value==="light"?"dark":"light",r.value?.setTheme(o.value)}function B(){r.value?.setActiveMaterial({color:l.value,texture:c.value})}function N(){r.value?.clearAll(),M.value="Sahne temizlendi"}function ie(){r.value?.resetCamera()}async function le(){try{_.value=!0,M.value="Kaydediliyor...";const se={id:p.value,name:h.value,blocks:r.value?.getBlocks()||[]},oe=await D("/designs",{method:"POST",body:JSON.stringify(se)});p.value=Number(oe?.design?.id||0)||null,h.value=String(oe?.design?.name||h.value),M.value="Kaydedildi",i("saved",oe?.design||null)}catch(se){M.value=`Hata: ${se.message}`}finally{_.value=!1}}async function ve(){try{_.value=!0,M.value="Yükleniyor...";const se=await D("/designs/latest",{method:"GET"});if(!se?.design){M.value="Kayıt bulunamadı",r.value?.setBlocks([]),p.value=null;return}p.value=Number(se.design.id||0)||null,h.value=String(se.design.name||"Tasarim"),r.value?.setBlocks(Array.isArray(se.design.blocks)?se.design.blocks:[]),M.value="Yüklendi",i("loaded",se.design)}catch(se){M.value=`Hata: ${se.message}`}finally{_.value=!1}}return ea(()=>[t.visible,t.standalone],async([se,oe])=>{if(oe||se){await Gh(),X(),f.value.length||ve().catch(()=>{});return}M.value="Hazır"},{immediate:!0}),Qh(()=>{Y()}),(se,oe)=>Xa((Oi(),Zi("div",{class:Mi(["studio-root",o.value==="dark"?"is-dark":"is-light"])},[n.standalone?nm("",!0):(Oi(),Zi("div",{key:0,class:"studio-overlay",onClick:I})),Ye("div",wS,[Ye("div",{ref_key:"sceneHost",ref:s,class:"scene-host"},null,512),Ye("header",CS,[oe[4]||(oe[4]=Ye("div",{class:"brand"},[Ye("span",{class:"brand-mark"},"🧱"),Ye("div",{class:"brand-text"},[Ye("strong",null,"Blok Tasarım Stüdyosu"),Ye("small",null,"3D Grid · Bilişim Kod")])],-1)),Xa(Ye("input",{"onUpdate:modelValue":oe[0]||(oe[0]=we=>h.value=we),type:"text",class:"name-field",placeholder:"Tasarım adı","aria-label":"Tasarım adı"},null,512),[[su,h.value]]),Ye("div",RS,[Ye("button",{class:"tbtn tbtn-save",disabled:_.value,onClick:le},"💾 Kaydet",8,PS),Ye("button",{class:"tbtn tbtn-load",disabled:_.value,onClick:ve},"📂 Yükle",8,DS),Ye("button",{class:"tbtn tbtn-exit",onClick:I},jn(n.standalone?"Panele Dön":"Kapat"),1)])]),Ye("aside",LS,[Ye("div",IS,[Ye("button",{class:Mi(["seg-btn",a.value==="add"?"on add":""]),onClick:oe[1]||(oe[1]=we=>$("add"))},"＋ Ekle",2),Ye("button",{class:Mi(["seg-btn",a.value==="erase"?"on erase":""]),onClick:oe[2]||(oe[2]=we=>$("erase"))},"⌫ Sil",2)]),Ye("section",US,[oe[6]||(oe[6]=Ye("h4",null,"Renk",-1)),Ye("div",NS,[(Oi(),Zi(rn,null,Ya(d,we=>Ye("button",{key:we,class:Mi(["swatch",{on:we.toLowerCase()===l.value.toLowerCase()}]),style:sr({background:we}),title:we,onClick:Ge=>b(we)},null,14,FS)),64)),Ye("label",OS,[Xa(Ye("input",{"onUpdate:modelValue":oe[3]||(oe[3]=we=>l.value=we),type:"color",onInput:B},null,544),[[su,l.value]]),oe[5]||(oe[5]=Ye("span",null,"＋",-1))])])]),Ye("section",BS,[oe[7]||(oe[7]=Ye("h4",null,"Doku",-1)),(Oi(),Zi(rn,null,Ya(g,we=>Ye("div",{key:we.label,class:"tex-group"},[Ye("div",zS,jn(we.label),1),Ye("div",HS,[(Oi(!0),Zi(rn,null,Ya(we.items,Ge=>(Oi(),Zi("button",{key:Ge.value,class:Mi(["tex",{on:c.value===Ge.value}]),title:Ge.label,onClick:nt=>P(Ge.value)},[Ye("i",{class:"tex-chip",style:sr(A(Ge.value))},null,4),Ye("span",null,jn(Ge.label),1)],10,VS))),128))])])),64))]),Ye("div",kS,[Ye("button",{class:"mini",onClick:W},jn(o.value==="light"?"🌙 Koyu":"☀️ Açık"),1),Ye("button",{class:"mini",onClick:ie},"🎥 Kamera"),Ye("button",{class:"mini danger",onClick:N},"🗑 Temizle")])]),Ye("div",GS,[Ye("span",WS,"📍 "+jn(L.value),1),Ye("span",XS,"🧱 "+jn(f.value.length)+" blok",1),Ye("span",YS,jn(M.value),1),Ye("span",qS,"Sol tık: "+jn(a.value==="add"?"ekle":"sil")+" · Sürükle: döndür · Sağ tık: kaydır · Tekerlek: yakınlaş",1)])])],2)),[[Mm,n.standalone||n.visible]])}},KS=AS(jS,[["__scopeId","data-v-516ef541"]]);function gh(){const n=document.getElementById("block-builder-page-root");if(!n)return;Bm({setup(){const t=()=>{if(window.history.length>1){window.history.back();return}window.location.href="/"};return()=>dm(KS,{visible:!0,standalone:!0,onClose:t})}}).mount(n)}document.readyState==="loading"?document.addEventListener("DOMContentLoaded",gh):gh();
