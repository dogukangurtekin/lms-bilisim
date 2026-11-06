(function () {
  // Kolay şifre uyarısı: yalnızca bilgilendirir, kaydı ENGELLEMEZ.
  var COMMON = ['123456','1234567','12345678','123456789','1234567890','111111','000000','123123','654321','password','parola','qwerty','sifre','şifre','abc123','asdfgh','12345','1q2w3e'];
  function isEasy(pw, user) {
    var p = (pw || '').toLowerCase();
    if (p.length < 6) return false;
    if (COMMON.indexOf(p) !== -1) return true;
    if (/^(.)\1+$/.test(p)) return true;                                   // aaaaaa, 111111
    if (/^\d+$/.test(p)) {                                                // yalnızca rakam: ardışık ya da kısa
      var up = true, down = true;
      for (var i = 1; i < p.length; i++) {
        if (+p[i] !== (+p[i - 1] + 1) % 10) up = false;
        if (+p[i] !== (+p[i - 1] + 9) % 10) down = false;
      }
      if (up || down || p.length <= 6) return true;
    }
    if (user && (p === user || p.indexOf(user) !== -1)) return true;       // kullanıcı adı içeriyor
    return false;
  }
  document.querySelectorAll('input[data-weak-warn]').forEach(function (inp) {
    var box = document.createElement('div');
    box.setAttribute('role', 'status');
    box.style.cssText = 'display:none;margin:8px 0 0;padding:9px 12px;border-radius:10px;background:#fff7ed;border:1px solid #fdba74;color:#9a3412;font-size:13px;line-height:1.5';
    box.textContent = '⚠️ Bu şifre kolay tahmin edilebilir. İstersen böyle kullanabilirsin ama daha zor bir şifre hesabını daha güvenli yapar.';
    var host = inp.closest('.field') || inp;
    host.parentNode.insertBefore(box, host.nextSibling);
    var user = (inp.getAttribute('data-username') || '').toLowerCase();
    inp.addEventListener('input', function () { box.style.display = isEasy(inp.value, user) ? 'block' : 'none'; });
  });
})();
