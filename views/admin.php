<?php
require_once __DIR__ . '/../includes/auth.php';
qc_require_admin();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin - Questão Certa</title>
  <link rel="stylesheet" href="../assets/style.css">
  <style>
    .admin-wrap { max-width:900px; margin:40px auto; padding:24px; background:#fff; border-radius:12px; }
    .admin-wrap h1 { color:var(--azul); }
    .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    .full { grid-column: 1 / -1; }
    .alt-row { display:flex; gap:8px; }
    label { font-size:0.88rem; color:#374151; display:block; margin-bottom:6px; }
    input[type="text"], input[type="number"], textarea, select { width:100%; padding:10px; border-radius:8px; border:1px solid #e5e7eb; }
    .btn-primary { background:#0e70fa; color:#fff; padding:10px 14px; border-radius:8px; border:none; cursor:pointer; }
    .msg { padding:10px 12px; background:#ecfdf5; border-left:4px solid #10b981; border-radius:6px; margin-bottom:12px; }
  </style>
</head>
<body>
  <div class="admin-wrap">
    <h1>Painel do Administrador</h1>
    <p>Aqui você pode adicionar e gerenciar questões. (Página protegida — somente administradores)</p>

    <?php if (isset($_GET['saved']) && $_GET['saved'] == '1'): ?>
      <div class="msg">Questão adicionada com sucesso.</div>
    <?php endif; ?>
      <?php if (isset($_GET['deleted']) && $_GET['deleted'] == '1'): ?>
        <div class="msg">Questão removida com sucesso.</div>
      <?php endif; ?>
      <?php if (isset($_GET['edited']) && $_GET['edited'] == '1'): ?>
        <div class="msg">Questão atualizada com sucesso.</div>
      <?php endif; ?>

    <form action="../api/admin_questions.php" method="POST" id="questao-form">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(qc_csrf_token()) ?>">
      <input type="hidden" name="action" value="add" id="form-action">
      <input type="hidden" name="id" value="" id="form-id">
      <div class="form-grid">
        <div>
          <label>Grande Área</label>
          <select name="grande_area" id="grande_area" required>
            <option value="">Selecione...</option>
            <option value="humanas">Ciências Humanas</option>
            <option value="matematica">Matemática</option>
            <option value="natureza">Ciências da Natureza</option>
            <option value="linguagens">Linguagens</option>
          </select>
        </div>
        <div>
          <label>Componente</label>
          <select name="componente" id="componente" required>
            <option value="">Selecione área primeiro...</option>
          </select>
        </div>
        <div>
          <label>Nível</label>
          <select name="nivel" required>
            <option value="">Selecione...</option>
            <option value="facil">Fácil</option>
            <option value="medio">Médio</option>
            <option value="dificil">Difícil</option>
            <option value="impossivel">Impossível</option>
          </select>
        </div>
        <div>
          <label>Número da questão</label>
          <input type="number" name="numero" required />
        </div>
        <div>
          <label>Ano</label>
          <input type="number" name="ano" value="2025" required />
        </div>
        <div>
          <label>Gabarito (índice 0-4)</label>
          <select name="gabarito" required>
            <option value="0">A (0)</option>
            <option value="1">B (1)</option>
            <option value="2">C (2)</option>
            <option value="3">D (3)</option>
            <option value="4">E (4)</option>
          </select>
        </div>

        <div class="full">
          <label>Enunciado</label>
          <textarea name="enunciado" rows="5" required></textarea>
        </div>

        <div class="full">
          <label>Alternativas (preencha pelo menos 2)</label>
          <div class="alt-row">
            <input type="text" name="alt[]" placeholder="Alternativa A" required />
            <input type="text" name="alt[]" placeholder="Alternativa B" required />
          </div>
          <div class="alt-row" style="margin-top:8px;">
            <input type="text" name="alt[]" placeholder="Alternativa C" />
            <input type="text" name="alt[]" placeholder="Alternativa D" />
            <input type="text" name="alt[]" placeholder="Alternativa E" />
          </div>
        </div>

        <div class="full">
          <label>Dica (opcional)</label>
          <input type="text" name="dica" />
        </div>

      </div>
      <div style="margin-top:14px;">
        <button class="btn-primary" type="submit">Adicionar Questão</button>
        <a href="dashboard.php" class="btn-voltar" style="margin-left:12px;">Voltar</a>
      </div>
    </form>

      <hr style="margin:20px 0">
      <h2>Lista de questões</h2>
      <?php
        $dataFile = __DIR__ . '/../data/questoes.json';
        $raw = file_get_contents($dataFile);
        $bank = json_decode($raw, true);
        if (!is_array($bank)) $bank = [];
      ?>
      <?php if (count($bank) === 0): ?>
        <p class="vazio">Nenhuma questão cadastrada ainda.</p>
      <?php else: ?>
        <table style="width:100%; border-collapse:collapse; margin-top:10px;">
          <thead>
            <tr style="text-align:left; border-bottom:1px solid #e5e7eb;">
              <th style="padding:8px">ID</th>
              <th style="padding:8px">Área</th>
              <th style="padding:8px">Componente</th>
              <th style="padding:8px">Nível</th>
              <th style="padding:8px">Nº</th>
              <th style="padding:8px">Ano</th>
              <th style="padding:8px">Enunciado</th>
              <th style="padding:8px">Ações</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($bank as $item): ?>
              <tr>
                <td style="padding:8px; vertical-align:top; font-family:monospace; font-size:0.85rem;"><?php echo htmlspecialchars($item['id'] ?? ''); ?></td>
                <td style="padding:8px; vertical-align:top"><?php echo htmlspecialchars($item['grande_area'] ?? ''); ?></td>
                <td style="padding:8px; vertical-align:top"><?php echo htmlspecialchars($item['componente'] ?? ''); ?></td>
                <td style="padding:8px; vertical-align:top"><?php echo htmlspecialchars($item['nivel'] ?? ''); ?></td>
                <td style="padding:8px; vertical-align:top"><?php echo htmlspecialchars($item['numero'] ?? ''); ?></td>
                <td style="padding:8px; vertical-align:top"><?php echo htmlspecialchars($item['ano'] ?? ''); ?></td>
                <td style="padding:8px; vertical-align:top; max-width:320px;"><?php echo htmlspecialchars(mb_strimwidth(strip_tags($item['enunciado'] ?? ''), 0, 100, '...')); ?></td>
                <td style="padding:8px; vertical-align:top;">
                  <button type="button" class="btn-primary" data-action="edit" data-id="<?php echo htmlspecialchars($item['id']); ?>">Editar</button>
                  <form action="../api/admin_questions.php" method="POST" style="display:inline; margin-left:8px;" onsubmit="return confirm('Confirmar remoção desta questão?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($item['id']); ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(qc_csrf_token()) ?>">
                    <button type="submit" class="btn-voltar" style="background:#ef4444; color:#fff; border:none; padding:8px 10px; border-radius:6px;">Remover</button>
                  </form>
                </td>
              </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>

      <!-- serialized bank for JS (keeps PHP out of JS expression) -->
      <script id="qc-bank" type="application/json"><?php echo json_encode($bank, JSON_UNESCAPED_UNICODE); ?></script>
      <script>
        // Mapeamento de componentes por grande área
        const COMPONENTES = {
          'humanas': ['historia', 'geografia', 'filosofia', 'sociologia'],
          'matematica': ['matematica'],
          'natureza': ['fisica', 'quimica', 'biologia'],
          'linguagens': ['portugues', 'literatura', 'ingles', 'espanhol', 'artes']
        };

        // Atualiza select de componente quando grande_area muda
        document.getElementById('grande_area').addEventListener('change', function() {
          const area = this.value;
          const componenteSelect = document.getElementById('componente');
          componenteSelect.innerHTML = '<option value="">Selecione...</option>';
          
          if (area && COMPONENTES[area]) {
            COMPONENTES[area].forEach(comp => {
              const option = document.createElement('option');
              option.value = comp;
              option.textContent = comp.charAt(0).toUpperCase() + comp.slice(1);
              componenteSelect.appendChild(option);
            });
          }
        });

        // Read bank data from the application/json script node
        const QC_BANK = JSON.parse(document.getElementById('qc-bank').textContent || '[]');
        document.querySelectorAll('button[data-action="edit"]').forEach(btn => {
          btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            // find item by id in flat array
            const found = QC_BANK.find(q => q.id === id);
            if (!found) return alert('Questão não encontrada');
            
            // populate form
            document.getElementById('form-action').value = 'edit';
            document.getElementById('form-id').value = found.id || '';
            document.getElementById('grande_area').value = found.grande_area || '';
            
            // Trigger change to populate componente options
            document.getElementById('grande_area').dispatchEvent(new Event('change'));
            setTimeout(() => {
              document.getElementById('componente').value = found.componente || '';
            }, 50);
            
            document.querySelector('select[name="nivel"]').value = found.nivel || '';
            document.querySelector('input[name="numero"]').value = found.numero || '';
            document.querySelector('input[name="ano"]').value = found.ano || '';
            document.querySelector('select[name="gabarito"]').value = found.gabarito || 0;
            document.querySelector('textarea[name="enunciado"]').value = found.enunciado || '';
            // clear and populate alternatives inputs
            const altInputs = document.querySelectorAll('input[name="alt[]"]');
            for (let i = 0; i < altInputs.length; i++) {
              altInputs[i].value = (found.alternativas && found.alternativas[i]) ? found.alternativas[i] : '';
            }
            document.querySelector('input[name="dica"]').value = found.dica || '';
            // scroll to form
            window.scrollTo({ top: 0, behavior: 'smooth' });
          });
        });
      </script>

  </div>
</body>
</html>
