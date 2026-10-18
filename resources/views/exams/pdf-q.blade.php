@php
    /** @var array $q */
    $L = 'ABCDEFGHIJ';
    $optLabel = fn (int $k) => match ($s['opt_style']) { 'bracket' => '('.$L[$k].')', 'dot' => $L[$k].'.', default => $L[$k].')' };
    $num = match ($s['num_style']) { 'paren' => ($i + 1).')', 'soru' => 'Soru '.($i + 1).'.', default => ($i + 1).'.' };
    $pts = ! empty($s['show_points']) && (float) $q['points'] > 0
        ? ' <span class="qp">('.rtrim(rtrim(number_format((float) $q['points'], 2, ',', ''), '0'), ',').' p)</span>' : '';
    $body = trim((string) $q['body']) !== '' ? nl2br(e($q['body'])) : '&nbsp;';
    $teacher = $mode === 'teacher';
    $ans = (string) ($q['answer'] ?? '');
    $opts = (array) ($q['options'] ?? []);
    $imgHtml = '';
    $imgSz = \App\Support\ExamLayout::imageSize($q['image_path'] ?? null, $colW - 40, 200, (int) ($q['image_scale'] ?? 100));
    if ($imgSz && is_file(public_path($q['image_path']))) {
        $mime = str_ends_with($q['image_path'], '.jpg') ? 'jpeg' : 'png';
        $ial = in_array($q['image_align'] ?? 'left', ['center', 'right'], true) ? $q['image_align'] : 'left';
        $imgHtml = '<div style="margin:5px 0;text-align:'.$ial.'"><img src="data:image/'.$mime.';base64,'.base64_encode((string) file_get_contents(public_path($q['image_path']))).'" style="width:'.$imgSz[0].'px;height:'.$imgSz[1].'px"></div>';
    }
    $ipos = in_array($q['image_pos'] ?? 'below', ['above', 'end'], true) ? $q['image_pos'] : 'below';
    $ia = $ipos === 'above' ? $imgHtml : '';
    $ib = $ipos === 'below' ? $imgHtml : '';
    $ie = $ipos === 'end' ? $imgHtml : '';
@endphp
<div class="qblk">
    @if(trim((string) ($q['section_title'] ?? '')) !== '')<div class="qsec">{{ $q['section_title'] }}</div>@endif
    <table class="qr"><tr>
        <td class="qn">{{ $num }}</td>
        <td class="qb">
            @if($q['type'] === 'multiple_choice')
                {!! $ia !!}<div>{!! $body !!}{!! $pts !!}</div>{!! $ib !!}
                @php
                    $short = $columns === 1 && count($opts) <= 4 && collect($opts)->every(fn ($o) => mb_strlen((string) $o) <= 20);
                @endphp
                @if($short)
                    <table class="qo4"><tr>
                        @foreach($opts as $k => $o)
                            <td class="{{ $teacher && $ans === $L[$k] ? 'ok' : '' }}"><b>{{ $optLabel($k) }}</b> {{ $o }}</td>
                        @endforeach
                    </tr></table>
                @else
                    @foreach($opts as $k => $o)
                        <div class="qo {{ $teacher && $ans === $L[$k] ? 'ok' : '' }}"><b>{{ $optLabel($k) }}</b> {{ $o }}{!! $teacher && $ans === $L[$k] ? ' <span style="font-family:\'DejaVu Sans\'">✓</span>' : '' !!}</div>
                    @endforeach
                @endif
                {!! $ie !!}
            @elseif($q['type'] === 'true_false')
                {!! $ia !!}<div><b>({!! $teacher ? '&nbsp;'.($ans === 'Y' ? 'Y' : 'D').'&nbsp;' : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!})</b> {!! $body !!}{!! $pts !!}</div>{!! $ib !!}{!! $ie !!}
            @elseif($q['type'] === 'open_ended')
                {!! $ia !!}<div>{!! $body !!}{!! $pts !!}</div>{!! $ib !!}
                @if($teacher)
                    @if(trim($ans) !== '')<div class="ans">Cevap: {!! nl2br(e($ans)) !!}</div>@endif
                @else
                    @for($l = 0; $l < (int) ($q['answer_lines'] ?? 0); $l++)<div class="ln">&nbsp;</div>@endfor
                @endif
                {!! $ie !!}
            @elseif($q['type'] === 'matching')
                @php
                    $n = count($opts); $shift = max(1, intdiv($n, 2)); $order = [];
                    for ($j = 0; $j < $n; $j++) { $order[] = ($j + $shift) % $n; }
                @endphp
                {!! $ia !!}<div>{!! $body !!}{!! $pts !!}</div>{!! $ib !!}
                <table class="qm"><tr>
                    <td>
                        @foreach($opts as $k => $o)
                            <div><b>({!! $teacher ? '&nbsp;'.$L[array_search($k, $order, true)].'&nbsp;' : '&nbsp;&nbsp;&nbsp;' !!})</b> {{ $k + 1 }}. {{ is_array($o) ? ($o['l'] ?? '') : $o }}</div>
                        @endforeach
                    </td>
                    <td>
                        @foreach($order as $k => $src)
                            <div><b>{{ $optLabel($k) }}</b> {{ is_array($opts[$src] ?? null) ? ($opts[$src]['r'] ?? '') : '' }}</div>
                        @endforeach
                    </td>
                </tr></table>
                {!! $ie !!}
            @else
                {!! $ia !!}<div>{!! $body !!}{!! $pts !!}</div>{!! $ib !!}
                @if($teacher && trim($ans) !== '')<div class="ans">Cevap: {{ $ans }}</div>@endif
                {!! $ie !!}
            @endif
        </td>
    </tr></table>
</div>
