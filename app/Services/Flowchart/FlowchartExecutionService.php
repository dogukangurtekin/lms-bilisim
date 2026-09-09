<?php

namespace App\Services\Flowchart;

use RuntimeException;

class FlowchartExecutionService
{
    private const MAX_ITERATION = 1000;

    public function validate(array $nodes, array $edges): array
    {
        $errors = [];
        $nodeById = [];
        foreach ($nodes as $node) {
            $nodeById[(string) ($node['id'] ?? '')] = $node;
        }

        $starts = array_values(array_filter($nodes, fn ($n) => ($n['type'] ?? '') === 'start'));
        if (count($starts) !== 1) {
            $errors[] = 'Sadece 1 adet Start bloğu olmalıdır.';
        }

        foreach ($nodes as $node) {
            $id = (string) ($node['id'] ?? '');
            $type = (string) ($node['type'] ?? '');
            $outEdges = array_values(array_filter($edges, fn ($e) => ($e['from'] ?? null) === $id));

            if ($type === 'end' && count($outEdges) > 0) {
                $errors[] = "End bloğu ({$id}) çıkış veremez.";
            }

            if ($type === 'decision') {
                $yes = array_values(array_filter($outEdges, fn ($e) => ($e['condition'] ?? null) === 'yes'));
                $no = array_values(array_filter($outEdges, fn ($e) => ($e['condition'] ?? null) === 'no'));
                if (count($yes) !== 1 || count($no) !== 1) {
                    $errors[] = "Decision bloğu ({$id}) YES ve NO çıkışlarına sahip olmalıdır.";
                }
            }
        }

        if (count($starts) === 1) {
            $visited = [];
            $queue = [(string) ($starts[0]['id'] ?? '')];
            while (! empty($queue)) {
                $current = array_shift($queue);
                if ($current === '' || isset($visited[$current])) {
                    continue;
                }
                $visited[$current] = true;
                foreach ($edges as $edge) {
                    if (($edge['from'] ?? null) === $current) {
                        $queue[] = (string) ($edge['to'] ?? '');
                    }
                }
            }

            foreach ($nodes as $node) {
                $id = (string) ($node['id'] ?? '');
                if ($id !== '' && ! isset($visited[$id])) {
                    $errors[] = "Bağlantısız blok var: {$id}";
                }
            }
        }

        return $errors;
    }

    public function execute(array $nodes, array $edges, array $inputs = [], bool $stepMode = false): array
    {
        $validationErrors = $this->validate($nodes, $edges);
        if (! empty($validationErrors)) {
            return [
                'ok' => false,
                'errors' => $validationErrors,
                'logs' => [],
                'steps' => [],
                'variables' => [],
            ];
        }

        $nodeById = [];
        foreach ($nodes as $node) {
            $nodeById[(string) $node['id']] = $node;
        }

        $start = array_values(array_filter($nodes, fn ($n) => ($n['type'] ?? '') === 'start'))[0] ?? null;
        $currentId = (string) ($start['id'] ?? '');
        $variables = [];
        $logs = [];
        $steps = [];
        $inputIndex = 0;
        $iteration = 0;

        while ($currentId !== '') {
            $iteration++;
            if ($iteration > self::MAX_ITERATION) {
                return [
                    'ok' => false,
                    'errors' => ['Sonsuz döngü tespit edildi (iteration limiti aşıldı).'],
                    'logs' => $logs,
                    'steps' => $steps,
                    'variables' => $variables,
                ];
            }

            $node = $nodeById[$currentId] ?? null;
            if (! $node) {
                throw new RuntimeException("Node bulunamadı: {$currentId}");
            }

            $type = (string) ($node['type'] ?? '');
            $code = (string) ($node['code'] ?? '');
            $text = (string) ($node['text'] ?? '');
            $step = [
                'nodeId' => $currentId,
                'type' => $type,
                'text' => $text,
                'variablesBefore' => $variables,
            ];

            try {
                if ($type === 'process') {
                    $this->executeAssignment($code, $variables);
                } elseif ($type === 'io') {
                    if (preg_match('/^\s*input\s+([a-zA-Z_]\w*)\s*$/', $code, $m)) {
                        $variables[$m[1]] = $inputs[$inputIndex] ?? null;
                        $inputIndex++;
                    } elseif (preg_match('/^\s*output\s+(.+)$/', $code, $m)) {
                        $value = $this->evaluateExpression($m[1], $variables);
                        $logs[] = (string) $value;
                        $step['output'] = $value;
                    } elseif ($code !== '') {
                        $value = $this->evaluateExpression($code, $variables);
                        $logs[] = (string) $value;
                        $step['output'] = $value;
                    }
                }
            } catch (\Throwable $e) {
                return [
                    'ok' => false,
                    'errors' => ["Çalıştırma hatası ({$currentId}): ".$e->getMessage()],
                    'logs' => $logs,
                    'steps' => $steps,
                    'variables' => $variables,
                ];
            }

            $nextId = null;
            if ($type === 'decision') {
                $decision = (bool) $this->evaluateExpression($code, $variables);
                $edgeCondition = $decision ? 'yes' : 'no';
                $edge = $this->findEdge($edges, $currentId, $edgeCondition);
                $nextId = $edge['to'] ?? null;
                $step['decision'] = $decision;
            } elseif ($type !== 'end') {
                $edge = $this->findEdge($edges, $currentId, null);
                $nextId = $edge['to'] ?? null;
            }

            $step['variablesAfter'] = $variables;
            $step['nextNodeId'] = $nextId;
            $steps[] = $step;

            if ($type === 'end') {
                break;
            }
            $currentId = (string) ($nextId ?? '');
            if ($stepMode) {
                break;
            }
        }

        return [
            'ok' => true,
            'errors' => [],
            'logs' => $logs,
            'steps' => $steps,
            'variables' => $variables,
        ];
    }

    private function findEdge(array $edges, string $from, ?string $condition): ?array
    {
        foreach ($edges as $edge) {
            if (($edge['from'] ?? null) !== $from) {
                continue;
            }
            if ($condition === null) {
                return $edge;
            }
            if (($edge['condition'] ?? null) === $condition) {
                return $edge;
            }
        }

        return null;
    }

    private function executeAssignment(string $code, array &$variables): void
    {
        if (! preg_match('/^\s*([a-zA-Z_]\w*)\s*=\s*(.+)\s*$/', $code, $m)) {
            throw new RuntimeException("Geçersiz işlem kodu: {$code}");
        }

        $varName = $m[1];
        $expr = $m[2];
        $variables[$varName] = $this->evaluateExpression($expr, $variables);
    }

    private function evaluateExpression(string $expr, array $variables): mixed
    {
        // GÜVENLİK: Bu eval() içine gömülecek kodu inşa ediyor - "code" alanı
        // API üzerinden HERHANGİ bir giriş yapmış kullanıcı (öğrenci dahil,
        // bkz. routes/api.php: /execute sadece 'auth' ister) tarafından
        // serbestçe belirlenebiliyor ve "inputs" (io node'larındaki "input x"
        // için sağlanan değerler) de çalışma zamanında tamamen çağıranın
        // kontrolünde. Eski kod, değişken değerlerini tek tırnak içine
        // koyarken SADECE tek tırnağı kaçışlıyordu (str_replace("'", "\\'", ...)),
        // ters eğik çizgiyi (\) hiç kaçışlamıyordu. Bir değişken değeri sonu
        // ters eğik çizgiyle biten bir girdi içerdiğinde ("\") bu, PHP'nin
        // tek-tırnaklı string ayrıştırmasında \' dizisini "kaçışlanmış tek
        // tırnak" olarak yorumlamasına yol açar - yani eklenen kapanış
        // tırnağı string'i GERÇEKTEN kapatmaz, string açık kalır ve
        // sonrasındaki (saldırganın "code" alanına serbestçe yazabildiği)
        // metin PHP KODU olarak yorumlanmaya devam eder. Bu, dikkatlice
        // hazırlanmış bir girdiyle sunucuda rastgele PHP kodu çalıştırmaya
        // (RCE) açık bırakıyordu.
        //
        // Düzeltme: string değerleri json_encode() ile PHP-uyumlu, TAM ve
        // güvenli şekilde kaçışlanmış çift tırnaklı literallere çeviriyoruz
        // (JS tarafındaki JSON.stringify ile aynı, kanıtlanmış yaklaşım).
        // Ayrıca tırnak içindeki metinler artık identifier regex'inin
        // yanlışlıkla içlerindeki kelimeleri "bilinmeyen değişken" sanıp 0'a
        // çevirmesinden de korunuyor.
        $safe = preg_replace_callback(
            '/(\'[^\']*\'|"[^"]*")|\b([a-zA-Z_]\w*)\b/',
            function ($m) use ($variables) {
                if ($m[1] !== '') {
                    // Doğrudan kodda yazılmış bir metin literali: dokunma.
                    return $m[1];
                }
                $name = $m[2];
                if (in_array(strtolower($name), ['true', 'false', 'null'], true)) {
                    return $name;
                }
                if (array_key_exists($name, $variables)) {
                    $value = $variables[$name];
                    if (is_int($value) || is_float($value)) {
                        return (string) $value;
                    }
                    if (is_bool($value)) {
                        return $value ? 'true' : 'false';
                    }
                    $encoded = json_encode((string) $value, JSON_UNESCAPED_UNICODE);
                    return $encoded !== false ? $encoded : '""';
                }
                return '0';
            },
            $expr
        );

        $safe = str_replace(['&&', '||'], [' and ', ' or '], (string) $safe);
        $allowed = '/^[0-9\.\s\+\-\*\/\%\(\)\<\>\=\!\&\|\'\"a-zA-Z_]+$/u';
        if (! preg_match($allowed, $safe)) {
            throw new RuntimeException('İzin verilmeyen ifade karakteri.');
        }

        set_error_handler(static fn () => true);
        try {
            /** @var mixed $result */
            $result = eval("return ({$safe});");
        } finally {
            restore_error_handler();
        }

        return $result;
    }
}

