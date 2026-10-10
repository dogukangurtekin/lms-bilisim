<footer style="border-top:1px solid #E4E1D8;padding:28px 0 36px;color:#585A72;font-size:13.5px">
    <div style="width:min(100% - 32px,1100px);margin-inline:auto;display:flex;flex-wrap:wrap;gap:12px 24px;justify-content:space-between;align-items:center">
        <div>© {{ date('Y') }} Bilişim Kod — Okullar için kodlama ve yapay zekâ platformu</div>
        <nav aria-label="Yasal bağlantılar" style="display:flex;flex-wrap:wrap;gap:8px 18px">
            <a href="{{ route('legal.privacy') }}">Gizlilik Politikası</a>
            <a href="{{ route('legal.terms') }}">Kullanım Şartları</a>
            <a href="{{ route('legal.cookies') }}">Çerez Politikası</a>
            <a href="{{ url('/#sss') }}">SSS</a>
            <a href="mailto:{{ config('app.contact_email') }}">{{ config('app.contact_email') }}</a>
        </nav>
    </div>
</footer>
