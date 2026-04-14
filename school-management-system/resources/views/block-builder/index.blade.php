<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="app-base-url" content="{{ rtrim(request()->getBaseUrl(), '/') }}">
    <title>3D Grid Tasarim Studyosu</title>
    @php
        $viteFailed = false;
        try {
            echo app(\Illuminate\Foundation\Vite::class)('resources/js/block-builder-page.js');
        } catch (\Throwable $e) {
            $viteFailed = true;
        }
    @endphp
</head>
<body style="margin:0; background:#06070b; overflow:hidden;">
    <div id="block-builder-page-root"></div>
    @if($viteFailed)
        <div style="position:fixed; inset:0; display:flex; align-items:center; justify-content:center; color:#fff; font:16px/1.5 Arial,sans-serif; padding:24px; text-align:center;">
            Block Builder kaynaklari bulunamadi. Sunucuda <code>npm run build</code> calistirip tekrar deneyin.
        </div>
    @endif
</body>
</html>
