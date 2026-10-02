<?php
require_once __DIR__.'/BaseController.php';
require_once __DIR__.'/../services/TapologyService.php';

class TapologyController extends BaseController
{
    private $db; private $service;
    public function __construct($db){$this->db=$db;$this->service=new TapologyService();if(session_status()===PHP_SESSION_NONE)session_start();}
    public function index(){
        $eventos=$this->db->query("SELECT id_evento,nome,data_evento,slugs,id_modalidade FROM eventos ORDER BY data_evento DESC")->fetchAll(PDO::FETCH_ASSOC);
        $preview=$_SESSION['tapology_preview']??null;$erro=$_SESSION['tapology_erro']??null;$ok=$_SESSION['tapology_ok']??null;
        unset($_SESSION['tapology_preview'],$_SESSION['tapology_erro'],$_SESSION['tapology_ok']);
        $this->render('tapology/index',['titulo'=>'Importar Tapology','eventos'=>$eventos,'preview'=>$preview,'erro'=>$erro,'ok'=>$ok]);
    }
    public function analisar(){
        if($_SERVER['REQUEST_METHOD']!=='POST')return $this->redirect('/importacao/tapology');
        $texto=trim($_POST['tapology_texto']??'');$id=(int)($_POST['id_evento']??0);
        if($texto===''||!$id){$_SESSION['tapology_erro']='Selecione o evento e cole o conteúdo do card.';return $this->redirect('/importacao/tapology');}
        $evento=$this->buscarEvento($id);if(!$evento){$_SESSION['tapology_erro']='Evento não encontrado.';return $this->redirect('/importacao/tapology');}
        $a=$this->service->analisar($texto);$p=['id_evento'=>$id,'evento'=>$evento,'total'=>$a['total'],'lutas'=>[]];
        foreach($a['lutas'] as $l){$l['vermelho_match']=$this->resolverAtleta($l['vermelho']);$l['azul_match']=$this->resolverAtleta($l['azul']);$p['lutas'][]=$l;}
        $_SESSION['tapology_preview']=$p;return $this->redirect('/importacao/tapology');
    }
    public function importar(){
        if($_SERVER['REQUEST_METHOD']!=='POST')return $this->redirect('/importacao/tapology');
        $p=$_SESSION['tapology_preview']??null;if(!$p){$_SESSION['tapology_erro']='A prévia expirou. Faça a análise novamente.';return $this->redirect('/importacao/tapology');}
        $ids=$_POST['atleta']??[];
        try{
            $this->db->beginTransaction();$ok=0;$skip=0;
            foreach($p['lutas'] as $i=>$l){
                $vr=(int)($ids[$i.'.vermelho']??($l['vermelho_match']['id']??0));$az=(int)($ids[$i.'.azul']??($l['azul_match']['id']??0));
                if(!$vr||!$az||$vr===$az){$skip++;continue;}
                $d=$this->db->prepare("SELECT id_luta FROM lutas WHERE id_evento=? AND ((id_atleta_azul=? AND id_atleta_vermelho=?) OR (id_atleta_azul=? AND id_atleta_vermelho=?)) LIMIT 1");$d->execute([$p['id_evento'],$az,$vr,$vr,$az]);if($d->fetch()){$skip++;continue;}
                $mod=(int)$p['evento']['id_modalidade'];$cat=$this->resolverCategoria($l['categoria'],$mod,$this->sexoAtleta($vr));$met=$this->resolverMetodo($l['metodo']);$v=null;if($l['vencedor']==='V')$v=$vr;elseif($l['vencedor']==='A')$v=$az;
                $s=$this->db->prepare("INSERT INTO lutas (id_evento,ordem_card,id_atleta_azul,id_atleta_vermelho,vencedor_id,id_metodo,round_final,tempo_final,id_modalidade,id_categoria_peso,status,num_rounds,vale_cinturao) VALUES (?,?,?,?,?,?,?,?,?,?, 'realizada',?,0)");
                $s->execute([$p['id_evento'],$l['ordem'],$az,$vr,$v,$met,$l['round']?:null,$this->tempoSql($l['tempo']),$mod,$cat,$l['num_rounds']?:3]);$ok++;
            }
            $this->db->commit();unset($_SESSION['tapology_preview']);$_SESSION['tapology_ok']="Importação concluída: {$ok} luta(s) criada(s), {$skip} ignorada(s).";
        }catch(Exception $e){if($this->db->inTransaction())$this->db->rollBack();$_SESSION['tapology_erro']='Importação cancelada: '.$e->getMessage();}
        return $this->redirect('/importacao/tapology');
    }
    private function buscarEvento($id){$s=$this->db->prepare("SELECT * FROM eventos WHERE id_evento=?");$s->execute([$id]);return $s->fetch(PDO::FETCH_ASSOC);}
    private function resolverAtleta($nome){
        $alvo=$this->normalizar($nome);if($alvo==='')return ['id'=>null,'nome'=>$nome,'status'=>'novo','opcoes'=>[]];
        $s=$this->db->query("SELECT id_atleta,nome,sobrenome,apelido,sexo,tapology FROM atletas");$m=[];
        while($r=$s->fetch(PDO::FETCH_ASSOC)){
            $full=$this->normalizar(trim($r['nome'].' '.$r['sobrenome']));
            $slug=$r['tapology']?$this->slugNome($r['tapology']):'';
            if($full===$alvo||($slug&&$slug===$alvo))$m[]=$r;
        }
        if(count($m)===1)return ['id'=>$m[0]['id_atleta'],'nome'=>$this->label($m[0]),'status'=>'encontrado','opcoes'=>$m];
        if(count($m)>1)return ['id'=>null,'nome'=>$nome,'status'=>'ambiguo','opcoes'=>$m];
        return ['id'=>null,'nome'=>$nome,'status'=>'novo','opcoes'=>[]];
    }
    private function podeImportar($p){foreach($p['lutas'] as $l)if(($l['vermelho_match']['id']??0)<1||($l['azul_match']['id']??0)<1)return false;return !empty($p['lutas']);}
    private function label($r){return trim($r['nome'].' '.$r['sobrenome']).($r['apelido']?' ('.$r['apelido'].')':'');}
    private function slugNome($u){$s=basename(parse_url($u,PHP_URL_PATH));$s=preg_replace('/^\d+-/','',$s);return $this->normalizar(str_replace('-',' ',$s));}
    private function normalizar($s){$s=mb_strtolower(trim((string)$s),'UTF-8');$s=iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s);return preg_replace('/\s+/',' ',preg_replace('/[^a-z0-9 ]/','',$s));}
    private function sexoAtleta($id){$s=$this->db->prepare("SELECT sexo FROM atletas WHERE id_atleta=?");$s->execute([$id]);return $s->fetchColumn()?:'M';}
    private function resolverCategoria($t,$mod,$sexo){
        if(!$t)return null;if(ctype_digit((string)$t)){ $s=$this->db->prepare("SELECT id_categoria_peso FROM categoria_peso WHERE id_categoria_peso=?");$s->execute([(int)$t]);return $s->fetchColumn()?:null;}
        $a=$this->normalizar($t);$s=$this->db->prepare("SELECT id_categoria_peso,nome,peso_max,peso_min FROM categoria_peso WHERE id_modalidade=? AND (sexo=? OR sexo IS NULL)");$s->execute([$mod,$sexo]);$rows=$s->fetchAll(PDO::FETCH_ASSOC);
        foreach($rows as $r)if($this->normalizar($r['nome'])===$a)return $r['id_categoria_peso'];
        if(preg_match('/([0-9]+(?:\.[0-9]+)?)\s*kg/i',$t,$m)){foreach($rows as $r)if((float)$m[1]<=(float)$r['peso_max']&&($r['peso_min']===null||(float)$m[1]>(float)$r['peso_min']))return $r['id_categoria_peso'];}
        return null;
    }
    private function resolverMetodo($s){if(!$s)return null;$q=$this->db->prepare("SELECT id_metodo FROM metodos_vitoria WHERE UPPER(sigla)=UPPER(?) OR UPPER(nome)=UPPER(?) LIMIT 1");$q->execute([$s,$s]);return $q->fetchColumn()?:null;}
    private function tempoSql($t){if(!$t)return null;return substr_count($t,':')===1?'00:'.$t:$t;}
}
