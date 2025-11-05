// StorageAPI: utilitários mínimos para persistir dados no localStorage.
// Fornece helpers para ler/escrever JSON, gerenciar listas de erros/acertos e
// salvar respostas/estado de progresso.

const StorageAPI = (function () {
	function safeParse(json, fallback) {
		try {
			return JSON.parse(json);
		} catch (e) {
			return fallback;
		}
	}

	function getJSON(key, fallback) {
		try {
			const v = localStorage.getItem(key);
			if (v === null) return fallback;
			return safeParse(v, fallback);
		} catch (err) {
			console.error('StorageAPI.getJSON error', err);
			return fallback;
		}
	}

	function setJSON(key, value) {
		try {
			localStorage.setItem(key, JSON.stringify(value));
			return true;
		} catch (err) {
			console.error('StorageAPI.setJSON error', err);
			return false;
		}
	}

	function addToList(key, item, uniqueBy) {
		const list = getJSON(key, []);
		// avoid duplicates if uniqueBy is provided
		if (uniqueBy) {
			const exists = list.find(x => uniqueBy(x, item));
			if (exists) return false;
		}
		list.push(item);
		return setJSON(key, list);
	}

	function removeFromList(key, predicate) {
		const list = getJSON(key, []);
		const filtered = list.filter(x => !predicate(x));
		setJSON(key, filtered);
		return filtered;
	}

	// Public API
	return {
		getJSON,
		setJSON,
		removeItem(key) {
			try { localStorage.removeItem(key); return true; } catch (e) { return false; }
		},
		clearAll() {
			try { localStorage.clear(); return true; } catch (e) { return false; }
		},

		// Errors/Acertos helpers (structure: { materia, qid, info... })
		getErros() { return getJSON('erros', []); },
		addErro(item) { return addToList('erros', item, (a,b) => a.materia === b.materia && a.qid === b.qid); },
		removeErro(materia, qid) { return removeFromList('erros', x => x.materia === materia && x.qid === qid); },

		getAcertos() { return getJSON('acertos', []); },
		addAcerto(item) { return addToList('acertos', item, (a,b) => a.materia === b.materia && a.qid === b.qid); },
		removeAcerto(materia, qid) { return removeFromList('acertos', x => x.materia === materia && x.qid === qid); },

		// Responses: store user's selected alternative and metadata
		// Key format: 'response::{materia}::{qid}'
		saveResponse(materia, qid, payload) {
			const k = `response::${materia}::${qid}`;
			return setJSON(k, payload);
		},
		getResponse(materia, qid, fallback = null) {
			const k = `response::${materia}::${qid}`;
			return getJSON(k, fallback);
		},

		// Progress object (generic storage for progress/flags)
		getProgress() { return getJSON('progress', {}); },
		setProgress(obj) { return setJSON('progress', obj); },

			// Convenience: mark result.
			// IMPORTANT: once a question is added to 'erros' it is NOT removed when later marked correct,
			// so the user will still have it in the revisão list as requested.
			// If isCorrect true -> add to acertos (do NOT remove from erros). If false -> add to erros.
			markResult(materia, qid, meta = {}, isCorrect = false) {
				const entry = Object.assign({ materia, qid, ts: Date.now() }, meta);
				if (isCorrect) {
					this.addAcerto(entry);
					// do NOT remove erro: keep history for revisão
				} else {
					this.addErro(entry);
				}
			}
	};
})();

// Expose globally
window.StorageAPI = StorageAPI;

