// UI helpers mínimos usados por `views/questoes.html`.
// Implementa `renderizarQuestoes(questoes, materia)` para mostrar a lista.

function renderizarQuestoes(questoes, materia) {
	const container = document.getElementById('questoes-container');
	if (!container) return;

	if (!questoes || questoes.length === 0) {
		container.innerHTML = '<p>Nenhuma questão encontrada.</p>';
		return;
	}

	const html = questoes.map((q, qIndex) => {
			// Garantir exatamente 4 alternativas mostradas: incluir sempre o gabarito
			const maxAlts = 4;
			const originalAlts = q.alternativas || [];
			const correctIndex = Number.isFinite(q.gabarito) ? q.gabarito : 0;
			// construir mapa com índices originais incluíndo o correto e completando com outros aleatórios
			const available = originalAlts.map((_, idx) => idx).filter(i => i !== correctIndex);
			// embaralhar disponíveis
			for (let i = available.length - 1; i > 0; i--) {
				const j = Math.floor(Math.random() * (i + 1));
				[available[i], available[j]] = [available[j], available[i]];
			}
			const chosen = [correctIndex].concat(available.slice(0, Math.max(0, maxAlts - 1)));
			// caso haja menos que maxAlts no total, replicar alguns índices (fallback)
			while (chosen.length < maxAlts) chosen.push(0);
			// embaralhar a ordem final para não deixar o gabarito sempre na mesma posição
			for (let i = chosen.length - 1; i > 0; i--) {
				const j = Math.floor(Math.random() * (i + 1));
				[chosen[i], chosen[j]] = [chosen[j], chosen[i]];
			}
			// salvamos o mapa na questão para verificação posterior
			q.map = chosen;

			const alternativas = chosen.map((origIdx, displayIdx) => {
				const letra = String.fromCharCode(65 + displayIdx);
				const texto = originalAlts[origIdx] || '';
				return `<li class="alternativa" data-i="${displayIdx}">
									<div class="eliminar-btn" title="Marcar como eliminada" data-q="${q.id}" data-i="${displayIdx}">X</div>
									<div class="selecionar-btn" title="Selecionar resposta" data-q="${q.id}" data-i="${displayIdx}">${letra}</div>
									<div class="texto-alternativa">${escapeHtml(texto)}</div>
								</li>`;
			}).join('');

		return `
			<div class="questao" data-qid="${q.id}" data-qindex="${qIndex}">
				<div class="questao-numero">${escapeHtml(q.materia || '')} • Q${escapeHtml(String(q.numero || ''))} • ${escapeHtml(String(q.ano || ''))}</div>
				<div class="enunciado">${escapeHtml(q.enunciado || '')}</div>
				<ul class="alternativas">${alternativas}</ul>
				<div style="margin-top:12px; display:flex; gap:8px; align-items:center;">
					<button class="btn-responder">Verificar</button>
					<button class="btn-dica" data-id="${q.id}">Mostrar dica</button>
				</div>
				<div class="dica" id="dica-${q.id}">${escapeHtml(q.dica || '')}</div>
				<div class="resposta" id="resposta-${q.id}" style="margin-top:10px; display:none;"></div>
			</div>`;
	}).join('');

	container.innerHTML = html;

	// Toggle para dicas
	document.querySelectorAll('.btn-dica').forEach(btn => {
		btn.addEventListener('click', () => {
			const id = btn.dataset.id;
			const d = document.getElementById('dica-' + id);
			if (d) d.classList.toggle('mostrando');
		});
	});

	// Eliminar (riscar) alternativa
	document.querySelectorAll('.eliminar-btn').forEach(btn => {
		btn.addEventListener('click', (e) => {
			const li = e.currentTarget.closest('.alternativa');
			if (!li) return;
			li.classList.toggle('eliminado');
		});
	});

	// Selecionar alternativa (apenas uma por questão)
	document.querySelectorAll('.selecionar-btn').forEach(btn => {
		btn.addEventListener('click', (e) => {
			const clicked = e.currentTarget;
			const qElem = clicked.closest('.questao');
			if (!qElem) return;

			// desmarcar anteriores
			qElem.querySelectorAll('.selecionar-btn').forEach(b => b.classList.remove('selecionado'));
			qElem.querySelectorAll('.alternativa').forEach(li => li.classList.remove('selecionado'));

			// marcar o selecionado
			clicked.classList.add('selecionado');
			const liParent = clicked.closest('.alternativa');
			if (liParent) liParent.classList.add('selecionado');

			// armazenar seleção no elemento da questão
			qElem.dataset.selected = clicked.dataset.i;
		});
	});

	// Verificar resposta
	document.querySelectorAll('.btn-responder').forEach((btn) => {
		btn.addEventListener('click', (e) => {
			const qElem = e.currentTarget.closest('.questao');
			if (!qElem) return;
			const qid = qElem.dataset.qid;
			const selected = typeof qElem.dataset.selected !== 'undefined' ? parseInt(qElem.dataset.selected, 10) : null;

			const pergunta = questoes.find(x => String(x.id) === String(qid));
			const respostaDiv = document.getElementById('resposta-' + qid);
			if (!pergunta) return;

			if (selected === null || isNaN(selected)) {
				if (respostaDiv) {
					respostaDiv.style.display = 'block';
					respostaDiv.className = 'resposta errado';
					respostaDiv.textContent = 'Por favor, selecione uma alternativa antes de verificar.';
				} else {
					alert('Selecione uma alternativa antes de verificar.');
				}
				return;
			}

			const isCorrect = Number(selected) === Number(pergunta.gabarito);

			if (respostaDiv) {
				respostaDiv.style.display = 'block';
				respostaDiv.className = 'resposta ' + (isCorrect ? 'correto' : 'errado');
				respostaDiv.textContent = isCorrect ? 'Correto! ✅' : `Errado. A resposta correta é ${String.fromCharCode(65 + Number(pergunta.gabarito))}.`;
			}

			// Persistir resposta usando StorageAPI se disponível
			try {
				if (window.StorageAPI) {
							StorageAPI.saveResponse(pergunta.materia, pergunta.id, { selected, ts: Date.now() });
							// Add question copy to erros if incorrect; markResult will also add to acertos when correct.
							StorageAPI.markResult(pergunta.materia, pergunta.id, Object.assign({ selected, q: pergunta }, { ts: Date.now() }), isCorrect);

						// Atualiza matriz em tempo real após marcação
						try { if (window.atualizarMatriz) atualizarMatriz(questoes); } catch (e) { /* ignore */ }
				}
			} catch (err) {
				console.warn('StorageAPI not available or failed', err);
			}
		});
	});
  
			// Atualiza a matriz de progresso após renderizar
			try { if (window.atualizarMatriz) atualizarMatriz(questoes); } catch (e) { /* ignore */ }
}

		// Atualiza o elemento #progresso mostrando bolinhas por questão: verde=acerto, vermelho=erro, cinza=neutro
		function atualizarMatriz(questoes) {
			const progresso = document.getElementById('progresso');
			if (!progresso) return;

			// obter listas do StorageAPI
			const erros = (window.StorageAPI && StorageAPI.getErros()) || [];
			const acertos = (window.StorageAPI && StorageAPI.getAcertos()) || [];

			// Mapear por qid para rápida verificação
			const errosSet = new Set(erros.map(e => String(e.qid)));
			const acertosSet = new Set(acertos.map(a => String(a.qid)));

			// Gera os itens (mantém mesma ordem das questões passadas)
					const items = questoes.map((q, idx) => {
						const qid = String(q.id);
						let cls = 'bolinha';
						if (acertosSet.has(qid)) cls += ' correta';
						else if (errosSet.has(qid)) cls += ' errada';
						return `<div class="${cls}" data-qindex="${idx}" data-qid="${qid}" title="Q ${q.numero}">${q.numero}</div>`;
					}).join('');

					progresso.innerHTML = items;

					// adicionar clique para rolar até a questão correspondente
					progresso.querySelectorAll('.bolinha').forEach(b => {
						b.style.cursor = 'pointer';
						b.addEventListener('click', () => {
							const idx = b.dataset.qindex;
							const qElem = document.querySelector(`.questao[data-qindex="${idx}"]`);
							if (qElem) qElem.scrollIntoView({ behavior: 'smooth', block: 'center' });
						});
					});
		}

		// Expose for external calls
		window.atualizarMatriz = atualizarMatriz;

// Pequena função de escape para evitar injeção de HTML nas strings do JSON
function escapeHtml(str) {
	return String(str)
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#39;');
}

// Exportar para escopo global caso outras partes do app chamem diretamente
window.renderizarQuestoes = renderizarQuestoes;
