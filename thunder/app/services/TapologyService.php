<?php
class TapologyService
{
    public function analisar($texto)
    {
        $linhas=$this->linhas($texto); $lutas=[];
        for($i=0;$i<count($linhas);$i++){
            $linha=$linhas[$i];
            if($this->temVs($linha)){
                $p=preg_split('/\s+(?:vs\.?|v\.?|x)\s+/iu',$linha,2);
                if(count($p)===2) $lutas[]=$this->montar($this->nome($p[0]),$this->nome($p[1]),$linhas,$i);
                continue;
            }
            if($this->ehVs($linha)&&isset($linhas[$i-1],$linhas[$i+1])){
                $a=$this->nome($linhas[$i-1]); $b=$this->nome($linhas[$i+1]);
                if($this->pareceAtleta($a)&&$this->pareceAtleta($b)) $lutas[]=$this->montar($a,$b,$linhas,$i);
            }
        }
        $out=[];$seen=[];
        foreach($lutas as $l){
            if(!$l['vermelho']||!$l['azul']) continue;
            $k=$this->normalizar($l['vermelho']).'|'.$this->normalizar($l['azul']);
            $ki=$this->normalizar($l['azul']).'|'.$this->normalizar($l['vermelho']);
            if(isset($seen[$k])||isset($seen[$ki])) continue;
            $seen[$k]=1; $l['ordem']=count($out)+1; $out[]=$l;
        }
        return ['lutas'=>$out,'total'=>count($out),'linhas'=>count($linhas)];
    }

    public function buscarPerfil($url)
    {
        if(!preg_match('~^https?://(?:www\.)?tapology\.com/fightcenter/fighters/~i',$url))
            return ['ok'=>false,'url'=>$url,'erro'=>'URL Tapology inválida'];
        $ch=curl_init($url);
        curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_TIMEOUT=>15,CURLOPT_USERAGENT=>'Mozilla/5.0 Chrome/140']);
        $html=curl_exec($ch); $http=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); $err=curl_error($ch); curl_close($ch);
        if(!$html||$http>=400) return ['ok'=>false,'url'=>$url,'erro'=>$err?:'HTTP '.$http];
        $texto=html_entity_decode(strip_tags($html),ENT_QUOTES|ENT_HTML5,'UTF-8');
        $texto=preg_replace('/\s+/u',' ',trim($texto));
        $d=['ok'=>true,'url'=>$url,'nome_completo'=>'','equipe'=>'','peso'=>null,'altura'=>null,'vitorias'=>null,'derrotas'=>null,'empates'=>null];
        if(preg_match('~<h1[^>]*>(.*?)</h1>~is',$html,$m)) $d['nome_completo']=trim(html_entity_decode(strip_tags($m[1]),ENT_QUOTES|ENT_HTML5,'UTF-8'));
        if(preg_match('/(?:Pro MMA Record|Professional MMA Record)\s*(\d+)\s*-\s*(\d+)\s*-\s*(\d+)/i',$texto,$m)){ $d['vitorias']=(int)$m[1];$d['derrotas']=(int)$m[2];$d['empates']=(int)$m[3]; }
        if(preg_match('/(?:Team|Fighting out of|Trains at)\s*:?\s*([^|]{2,80})/i',$texto,$m)) $d['equipe']=trim($m[1]);
        if(preg_match('/(?:Weight)\s*:?\s*([0-9]{2,3}(?:\.[0-9]+)?)\s*(?:lbs|kg)/i',$texto,$m)) $d['peso']=(float)$m[1];
        return $d;
    }

    private function montar($a,$b,$linhas,$i)
    {
        $ctx=implode(' ',array_slice($linhas,max(0,$i-4),9));
        $urls=$this->urls($ctx);$met=$this->metodo($ctx);$round='';$tempo='';
        if(preg_match('/\b(?:R(?:ound)?\s*)?(\d{1,2})\b/i',$ctx,$m)&&$m[1]>=1&&$m[1]<=10)$round=(int)$m[1];
        if(preg_match('/\b(\d{1,2}:\d{2})\b/',$ctx,$m))$tempo=$m[1];
        $v=''; if(preg_match('/\b(draw|empate)\b/i',$ctx))$v='E';
        return ['ordem'=>0,'vermelho'=>$a,'azul'=>$b,'vencedor'=>$v,'metodo'=>$met,'round'=>$round,'tempo'=>$tempo,'num_rounds'=>3,'categoria'=>$this->categoria($ctx),'tapology_urls'=>$urls];
    }
    private function linhas($t){$t=html_entity_decode(strip_tags((string)$t),ENT_QUOTES|ENT_HTML5,'UTF-8');$o=[];foreach(preg_split('/\r\n?|\n/',$t) as $l){$l=trim(preg_replace('/[\t ]+/',' ',$l));if($l!==''&&mb_strlen($l,'UTF-8')<=180)$o[]=$l;}return $o;}
    private function nome($s){$s=trim($s);$s=preg_replace('/^\d+[\.)]\s*/','',$s);return trim(preg_replace('/\s+(?:W|L|WIN|LOSS)$/i','',$s)," -–—|\t");}
    private function temVs($s){return (bool)preg_match('/\s+(?:vs\.?|v\.?|x)\s+/iu',$s);}
    private function ehVs($s){return (bool)preg_match('/^(?:vs\.?|v\.?|x)$/iu',trim($s));}
    private function pareceAtleta($s){if($s===''||mb_strlen($s,'UTF-8')<3)return false;if(preg_match('/^(main card|preliminary|results|upcoming|fight card|tapology|record|method|weight|date)$/i',$s))return false;return (bool)preg_match('/[\p{L}]/u',$s);}
    private function metodo($s){foreach(['/\bKO\/TKO\b/i'=>'KO','/\bTKO\b/i'=>'TKO','/\bKO\b/i'=>'KO','/\bSUB(?:MISSION)?\b/i'=>'SUB','/\bUD\b/i'=>'UD','/\bSD\b/i'=>'SD','/\bMD\b/i'=>'MD','/\bDQ\b/i'=>'DQ','/\bNC\b|no contest/i'=>'NC'] as $r=>$v)if(preg_match($r,$s))return $v;return '';}
    private function categoria($s){if(preg_match('/\b(\d{2,3}(?:[\.,]\d+)?)\s*kg\b/i',$s,$m))return str_replace(',','.',$m[1]).'kg';foreach(['Heavyweight'=>'Peso Pesado','Light Heavyweight'=>'Peso Meio-Pesado','Middleweight'=>'Peso Médio','Welterweight'=>'Peso Meio-Médio','Lightweight'=>'Peso Leve','Featherweight'=>'Peso Pena','Bantamweight'=>'Peso Galo','Flyweight'=>'Peso Mosca','Strawweight'=>'Peso Palha'] as $en=>$pt)if(preg_match('/\b'.preg_quote($en,'/').'\b/i',$s))return $pt;return '';}
    private function urls($s){preg_match_all('~https?://(?:www\.)?tapology\.com/fightcenter/fighters/[^\s<>"]+~i',$s,$m);return array_values(array_unique($m[0]??[]));}
    private function normalizar($s){$s=mb_strtolower(trim((string)$s),'UTF-8');$s=iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s);return preg_replace('/\s+/',' ',preg_replace('/[^a-z0-9 ]/','',$s));}
}
