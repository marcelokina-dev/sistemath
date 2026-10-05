<?php
class TapologyService
{
    public function analisar($texto)
    {
        $linhas = $this->linhas($texto);
        $lutas = [];

        for ($i = 0; $i < count($linhas); $i++) {
            $linha = $linhas[$i];

            if ($this->temVs($linha)) {
                $p = preg_split('/\s+(?:vs\.?|v\.?|x)\s+/iu', $linha, 2);
                if (count($p) === 2) {
                    $lutas[] = $this->montar(
                        $this->nome($p[0]),
                        $this->nome($p[1]),
                        $linhas,
                        $i
                    );
                }
                continue;
            }

            if ($this->ehVs($linha) && isset($linhas[$i - 1], $linhas[$i + 1])) {
                $a = $this->nome($linhas[$i - 1]);
                $b = $this->nome($linhas[$i + 1]);

                if ($this->pareceAtleta($a) && $this->pareceAtleta($b)) {
                    $lutas[] = $this->montar($a, $b, $linhas, $i);
                }
            }
        }

        $out = [];
        $seen = [];

        foreach ($lutas as $l) {
            if (!$l['vermelho'] || !$l['azul']) {
                continue;
            }

            $k = $this->normalizar($l['vermelho']) . '|' . $this->normalizar($l['azul']);
            $ki = $this->normalizar($l['azul']) . '|' . $this->normalizar($l['vermelho']);

            if (isset($seen[$k]) || isset($seen[$ki])) {
                continue;
            }

            $seen[$k] = 1;
            $l['ordem'] = count($out) + 1;
            $out[] = $l;
        }

        return [
            'lutas' => $out,
            'total' => count($out),
            'linhas' => count($linhas)
        ];
    }

    /**
     * Busca e interpreta o perfil individual do atleta no Tapology.
     * O perfil individual funciona via cURL mesmo quando o card do evento
     * depende de JavaScript.
     */
    public function buscarPerfil($url)
    {
        $url = trim((string)$url);

        if (!preg_match('~^https?://(?:www\.)?tapology\.com/fightcenter/fighters/[a-z0-9/_-]+~i', $url)) {
            return [
                'ok' => false,
                'url' => $url,
                'erro' => 'URL Tapology de atleta inválida'
            ];
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_ENCODING => '',
            CURLOPT_HTTPHEADER => [
                'Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7'
            ],
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/140 Safari/537.36'
        ]);

        $html = curl_exec($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if (!$html || $http >= 400) {
            return [
                'ok' => false,
                'url' => $url,
                'erro' => $err ?: 'HTTP ' . $http
            ];
        }

        $texto = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $texto = preg_replace('/\s+/u', ' ', trim($texto));

        $d = [
            'ok' => true,
            'url' => $url,
            'nome_completo' => '',
            'apelido' => '',
            'equipe' => '',
            'peso' => null,
            'altura' => null,
            'envergadura' => null,
            'vitorias' => null,
            'derrotas' => null,
            'empates' => null,
            'sexo' => null,
            'nacionalidade' => ''
        ];

        if (preg_match('~<h1[^>]*>(.*?)</h1>~is', $html, $m)) {
            $d['nome_completo'] = trim(
                html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8')
            );
        }

        if ($d['nome_completo'] === '' &&
            preg_match('/(?:^|\s)([A-ZÁÀÃÂÉÊÍÓÔÕÚÇ][^|]{2,100})\s+Pro MMA\s+/u', $texto, $m)) {
            $d['nome_completo'] = trim($m[1]);
        }

        if (preg_match('/(?:Pro MMA Record|Professional MMA Record)\s*(\d+)\s*-\s*(\d+)\s*-\s*(\d+)/i', $texto, $m)) {
            $d['vitorias'] = (int)$m[1];
            $d['derrotas'] = (int)$m[2];
            $d['empates'] = (int)$m[3];
        }

        if (preg_match('/Nickname\s*:\s*([^|]{1,80})/i', $texto, $m)) {
            $apelido = trim($m[1]);
            if ($apelido !== '' && !preg_match('/^(N\/A|None|N\/D)$/i', $apelido)) {
                $d['apelido'] = trim($apelido, " \t\n\r\"'");
            }
        }

        if (preg_match('/Affiliation\s*:\s*([^|]{2,120})/i', $texto, $m)) {
            $d['equipe'] = trim($m[1]);
        }

        if ($d['equipe'] === '' &&
            preg_match('/(?:Team|Trains at)\s*:\s*([^|]{2,120})/i', $texto, $m)) {
            $d['equipe'] = trim($m[1]);
        }

        // Tapology normalmente mostra altura em cm entre parênteses.
        if (preg_match('/Height\s*:?\s*[^|]{0,30}\((\d+(?:\.\d+)?)cm\)/i', $texto, $m)) {
            $d['altura'] = (float)$m[1] / 100;
        } elseif (preg_match('/Height\s*:?\s*(\d+(?:\.\d+)?)\s*cm/i', $texto, $m)) {
            $d['altura'] = (float)$m[1] / 100;
        }

        // Peso: se vier em lbs, converte para kg. Se vier em kg, usa diretamente.
        if (preg_match('/Weight\s*:?\s*([0-9]{2,3}(?:\.[0-9]+)?)\s*lbs/i', $texto, $m)) {
            $d['peso'] = round(((float)$m[1]) * 0.45359237, 2);
        } elseif (preg_match('/Weight\s*:?\s*([0-9]{2,3}(?:[\.,][0-9]+)?)\s*kg/i', $texto, $m)) {
            $d['peso'] = (float)str_replace(',', '.', $m[1]);
        }

        if (preg_match('/Reach\s*:?\s*[^|]{0,30}\((\d+(?:\.\d+)?)cm\)/i', $texto, $m)) {
            $d['envergadura'] = (float)$m[1] / 100;
        }

        if (preg_match('/\b(?:Women|Women\'s|Female|Feminino)\b/i', $texto)) {
            $d['sexo'] = 'F';
        } elseif (preg_match('/\b(?:Men|Men\'s|Male|Masculino)\b/i', $texto)) {
            $d['sexo'] = 'M';
        }

        if (preg_match('/Born\s*:\s*([^|]{2,100})/i', $texto, $m)) {
            $local = trim($m[1]);
            if (preg_match('/Brazil|Brasil/i', $local)) {
                $d['nacionalidade'] = 'Brasileiro';
            }
        }

        // Se não houver nacionalidade explícita, não inventamos.
        return $d;
    }

    private function montar($a, $b, $linhas, $i)
    {
        $ctx = implode(' ', array_slice($linhas, max(0, $i - 4), 9));
        $urls = $this->urls($ctx);

        $met = $this->metodo($ctx);
        $round = '';
        $tempo = '';

        if (preg_match('/\b(?:R(?:ound)?\s*)?(\d{1,2})\b/i', $ctx, $m) &&
            (int)$m[1] >= 1 && (int)$m[1] <= 10) {
            $round = (int)$m[1];
        }

        if (preg_match('/\b(\d{1,2}:\d{2})\b/', $ctx, $m)) {
            $tempo = $m[1];
        }

        $v = '';
        if (preg_match('/\b(draw|empate)\b/i', $ctx)) {
            $v = 'E';
        }

        // Quando o texto contém exatamente dois perfis Tapology no contexto
        // da luta, associamos pela ordem em que aparecem.
        $vermelhoUrl = $urls[0] ?? '';
        $azulUrl = $urls[1] ?? '';

        return [
            'ordem' => 0,
            'vermelho' => $a,
            'azul' => $b,
            'vermelho_url' => $vermelhoUrl,
            'azul_url' => $azulUrl,
            'vencedor' => $v,
            'metodo' => $met,
            'round' => $round,
            'tempo' => $tempo,
            'num_rounds' => 3,
            'categoria' => $this->categoria($ctx),
            'tapology_urls' => $urls
        ];
    }

    private function linhas($t)
    {
        $t = html_entity_decode(strip_tags((string)$t), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $o = [];

        foreach (preg_split('/\r\n?|\n/', $t) as $l) {
            $l = trim(preg_replace('/[\t ]+/', ' ', $l));

            if ($l !== '' && mb_strlen($l, 'UTF-8') <= 180) {
                $o[] = $l;
            }
        }

        return $o;
    }

    private function nome($s)
    {
        $s = trim($s);
        $s = preg_replace('/^\d+[\.)]\s*/', '', $s);
        return trim(
            preg_replace('/\s+(?:W|L|WIN|LOSS)$/i', '', $s),
            " -–—|\t"
        );
    }

    private function temVs($s)
    {
        return (bool)preg_match('/\s+(?:vs\.?|v\.?|x)\s+/iu', $s);
    }

    private function ehVs($s)
    {
        return (bool)preg_match('/^(?:vs\.?|v\.?|x)$/iu', trim($s));
    }

    private function pareceAtleta($s)
    {
        if ($s === '' || mb_strlen($s, 'UTF-8') < 3) {
            return false;
        }

        if (preg_match(
            '/^(main card|preliminary|results|upcoming|fight card|tapology|record|method|weight|date|def)$/i',
            trim($s)
        )) {
            return false;
        }

        // Evita transformar títulos/notícias em atletas.
        if (preg_match('/^(MMA Junkie|Yahoo Sports|ESPN|UFC|News|Moving on up|weigh-in results)/i', $s)) {
            return false;
        }

        return (bool)preg_match('/[\p{L}]/u', $s);
    }

    private function metodo($s)
    {
        foreach ([
            '/\bKO\/TKO\b/i' => 'KO',
            '/\bTKO\b/i' => 'TKO',
            '/\bKO\b/i' => 'KO',
            '/\bSUB(?:MISSION)?\b/i' => 'SUB',
            '/\bUD\b/i' => 'UD',
            '/\bSD\b/i' => 'SD',
            '/\bMD\b/i' => 'MD',
            '/\bDQ\b/i' => 'DQ',
            '/\bNC\b|no contest/i' => 'NC'
        ] as $r => $v) {
            if (preg_match($r, $s)) {
                return $v;
            }
        }

        return '';
    }

    private function categoria($s)
    {
        if (preg_match('/\b(\d{2,3}(?:[\.,]\d+)?)\s*kg\b/i', $s, $m)) {
            return str_replace(',', '.', $m[1]) . 'kg';
        }

        // Mais específico primeiro para não confundir Light Heavyweight
        // com Heavyweight.
        foreach ([
            'Light Heavyweight' => 'Peso Meio-Pesado',
            'Heavyweight' => 'Peso Pesado',
            'Middleweight' => 'Peso Médio',
            'Welterweight' => 'Peso Meio-Médio',
            'Lightweight' => 'Peso Leve',
            'Featherweight' => 'Peso Pena',
            'Bantamweight' => 'Peso Galo',
            'Flyweight' => 'Peso Mosca',
            'Strawweight' => 'Peso Palha'
        ] as $en => $pt) {
            if (preg_match('/\b' . preg_quote($en, '/') . '\b/i', $s)) {
                return $pt;
            }
        }

        return '';
    }

    private function urls($s)
    {
        preg_match_all(
            '~https?://(?:www\.)?tapology\.com/fightcenter/fighters/[a-z0-9/_-]+~i',
            $s,
            $m
        );

        return array_values(array_unique($m[0] ?? []));
    }

    private function normalizar($s)
    {
        $s = mb_strtolower(trim((string)$s), 'UTF-8');
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        return preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9 ]/', '', $s));
    }
}
