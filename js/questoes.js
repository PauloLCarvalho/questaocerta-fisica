// QuestoesAPI: carrega o arquivo JSON com as questões e expõe um array `todasQuestoes`.
// Implementação mínima para evitar ReferenceError em `views/questoes.html`.
const QuestoesAPI = {
	todasQuestoes: [],
	_carregado: false,

	// Carrega o banco de questões a partir de ../data/questoes.json
	async carregarBanco() {
		if (this._carregado) return;
		try {
			// Resolve caminho relativo à página atual
			const url = new URL('../data/questoes.json', window.location.href).href;
			const resp = await fetch(url);
			if (!resp.ok) throw new Error(`Falha ao carregar ${url}: ${resp.status}`);
			const data = await resp.json();
			if (!Array.isArray(data)) throw new Error('Formato inválido de questoes.json');
			this.todasQuestoes = data;
			this._carregado = true;
		} catch (err) {
			console.error('QuestoesAPI.carregarBanco error:', err);
			throw err;
		}
	}
};

// Expõe globalmente para uso em páginas que esperam `QuestoesAPI` no escopo global
window.QuestoesAPI = QuestoesAPI;
