<div class="container-fluid p-4">
<h2><i class="fas fa-bolt"></i> Importar card do Tapology</h2>
<p class="text-muted">Cole o conteúdo visível do card. A primeira etapa apenas analisa; nada é salvo.</p>
<?php if(!empty($ok)): ?><div class="alert alert-success"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
<?php if(!empty($erro)): ?><div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
<form method="POST" action="<?= $this->url('/importacao/tapology/analisar') ?>" class="card mb-4">
<div class="card-header bg-dark text-white">1. Evento e card</div><div class="card-body">
<select name="id_evento" class="form-select mb-3" required><option value="">-- Selecione o evento --</option>
<?php foreach($eventos as $e): ?><option value="<?= $e['id_evento'] ?>"><?= htmlspecialchars($e['nome']) ?> (<?= htmlspecialchars($e['data_evento']) ?>)</option><?php endforeach; ?>
</select>
<textarea name="tapology_texto" rows="18" class="form-control" required placeholder="Cole aqui o card copiado do Tapology..."></textarea>
<div class="form-text">Se o conteúdo tiver URLs de perfis <code>tapology.com/fightcenter/fighters/...</code>, elas também serão reconhecidas.</div>
</div><div class="card-footer"><button class="btn btn-danger btn-lg"><i class="fas fa-search"></i> Analisar card</button></div></form>
<?php if(!empty($preview)): ?>
<div class="card"><div class="card-header bg-danger text-white">2. Prévia — <?= (int)$preview['total'] ?> luta(s) encontrada(s)</div><div class="card-body">
<p class="small text-muted">Verde = atleta identificado. Vermelho = cadastro não encontrado. Amarelo = ambiguidade.</p>
<form method="POST" action="<?= $this->url('/importacao/tapology/importar') ?>">
<div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>#</th><th>Vermelho</th><th>Azul</th><th>Resultado</th><th>Método</th><th>Categoria</th></tr></thead><tbody>
<?php foreach($preview['lutas'] as $i=>$l): ?><tr><td><?= $l['ordem'] ?></td>
<?php foreach(['vermelho','azul'] as $lado): $m=$l[$lado.'_match']; ?><td><strong><?= htmlspecialchars($l[$lado]) ?></strong><br>
<?php if($m['status']==='encontrado'): ?><span class="badge bg-success">#<?= (int)$m['id'] ?> encontrado</span><input type="hidden" name="atleta[<?= $i ?>.<?= $lado ?>]" value="<?= (int)$m['id'] ?>">
<?php elseif($m['status']==='novo'): ?><span class="badge bg-danger">NOVO — cadastre antes</span>
<?php else: ?><span class="badge bg-warning text-dark">AMBÍGUO</span><?php foreach($m['opcoes'] as $op): ?><div><label><input type="radio" name="atleta[<?= $i ?>.<?= $lado ?>]" value="<?= (int)$op['id_atleta'] ?>"> #<?= (int)$op['id_atleta'] ?> <?= htmlspecialchars($this->label($op)) ?></label></div><?php endforeach; ?><?php endif; ?></td><?php endforeach; ?>
<td><?= htmlspecialchars($l['vencedor']?:'-') ?></td><td><?= htmlspecialchars($l['metodo']?:'-') ?></td><td><?= htmlspecialchars($l['categoria']?:'-') ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<div class="alert alert-warning"><strong>Importante:</strong> esta V1 não cria atletas automaticamente. Isso evita duplicações por erro de nome. Cadastre os novos e analise novamente.</div>
<button class="btn btn-success btn-lg" <?= $this->podeImportar($preview)?'':'disabled' ?>><i class="fas fa-check"></i> Confirmar importação</button>
</form></div></div><?php endif; ?>
</div>