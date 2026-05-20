class FooterBuilder {
    constructor() {
        this.config = window.builderConfig || { rows: [] };
        this.history = [];
        this.historyIndex = -1;
        this.dragData = null;
        this.isDirty = false;
        this.init();
    }

    init() {
        this.renderCanvas();
        this.bindDrag();
        this.bindActions();
        this.pushHistory();
        window.addEventListener('beforeunload', (e) => { if (this.isDirty) { e.preventDefault(); e.returnValue = ''; } });
    }

    renderCanvas() {
        const canvas = document.getElementById('canvas');
        canvas.innerHTML = '';
        (this.config.rows || []).forEach((row, r) => {
            const rowEl = document.createElement('div');
            rowEl.className = 'builder-row';
            rowEl.innerHTML = `<div class="row-head"><strong>ROW: ${row.id}</strong></div>`;
            const cols = document.createElement('div');
            cols.className = 'row-cols';
            (row.columns || []).forEach((col, c) => {
                const z = document.createElement('div');
                z.className = 'drop-zone';
                z.dataset.row = String(r); z.dataset.col = String(c);
                z.innerHTML = `<div class="col-meta">${col.id} <span class="badge">${(col.elements||[]).length}</span></div>`;
                if ((col.elements || []).length === 0) z.innerHTML += '<div class="placeholder">Drop here</div>';
                (col.elements || []).forEach((el, i) => {
                    const n = document.createElement('div');
                    n.className = 'canvas-element'; n.draggable = true;
                    n.dataset.row = String(r); n.dataset.col = String(c); n.dataset.index = String(i);
                    n.innerHTML = `<span>${el.type}</span><button class="btn-del" data-row="${r}" data-col="${c}" data-index="${i}">?</button>`;
                    z.appendChild(n);
                });
                cols.appendChild(z);
            });
            rowEl.appendChild(cols); canvas.appendChild(rowEl);
        });
        this.bindDrag();
    }

    bindDrag() {
        document.querySelectorAll('#element-list .element-card').forEach((n) => n.addEventListener('dragstart', () => { this.dragData = { source: 'panel', type: n.dataset.type }; }));
        document.querySelectorAll('.canvas-element').forEach((n) => n.addEventListener('dragstart', () => { this.dragData = { source: 'canvas', fromRow: Number(n.dataset.row), fromCol: Number(n.dataset.col), fromIndex: Number(n.dataset.index) }; }));
        document.querySelectorAll('.drop-zone').forEach((z) => {
            z.addEventListener('dragover', (e) => { e.preventDefault(); z.classList.add('drag-over'); });
            z.addEventListener('dragleave', () => z.classList.remove('drag-over'));
            z.addEventListener('drop', (e) => {
                e.preventDefault(); z.classList.remove('drag-over');
                const r = Number(z.dataset.row), c = Number(z.dataset.col);
                if (this.dragData?.source === 'panel') this.config.rows[r].columns[c].elements.push({ id: this.id(), type: this.dragData.type, settings: {} });
                if (this.dragData?.source === 'canvas') {
                    const from = this.config.rows[this.dragData.fromRow].columns[this.dragData.fromCol].elements;
                    const [el] = from.splice(this.dragData.fromIndex, 1);
                    this.config.rows[r].columns[c].elements.push(el);
                }
                this.isDirty = true; this.pushHistory(); this.renderCanvas();
            });
        });
        document.querySelectorAll('.btn-del').forEach((b) => b.addEventListener('click', () => { this.config.rows[Number(b.dataset.row)].columns[Number(b.dataset.col)].elements.splice(Number(b.dataset.index), 1); this.isDirty=true; this.pushHistory(); this.renderCanvas(); }));
    }

    bindActions() {
        document.getElementById('btn-save')?.addEventListener('click', () => this.save());
        document.getElementById('btn-undo')?.addEventListener('click', () => this.undo());
        document.getElementById('btn-redo')?.addEventListener('click', () => this.redo());
        document.getElementById('btn-load-preset')?.addEventListener('click', () => this.loadPreset(document.getElementById('preset-select')?.value || 'preset-1'));
        document.getElementById('btn-preview')?.addEventListener('click', () => window.open('/', '_blank'));
    }

    save() { const b=document.getElementById('btn-save'); b.innerHTML='Saving...'; fetch('/api/builder/footer/save',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':window.csrfToken},body:JSON.stringify({config:this.config})}).then(r=>r.json()).then(j=>this.toast(j.success?'Footer saved!':'Save failed!',j.success?'success':'error')).finally(()=>b.innerHTML='Save'); }
    loadPreset(p){ if(!confirm('Are you sure? This will replace current layout.')) return; fetch('/api/builder/footer/preset',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':window.csrfToken},body:JSON.stringify({preset:p})}).then(r=>r.json()).then(j=>{if(j.success){this.config=j.data;this.isDirty=true;this.pushHistory();this.renderCanvas();}}); }
    undo(){ if(this.historyIndex>0){this.historyIndex--;this.config=JSON.parse(JSON.stringify(this.history[this.historyIndex]));this.renderCanvas();} }
    redo(){ if(this.historyIndex<this.history.length-1){this.historyIndex++;this.config=JSON.parse(JSON.stringify(this.history[this.historyIndex]));this.renderCanvas();} }
    pushHistory(){ this.history=this.history.slice(0,this.historyIndex+1); this.history.push(JSON.parse(JSON.stringify(this.config))); if(this.history.length>20)this.history.shift(); this.historyIndex=this.history.length-1; document.getElementById('undo-count').textContent=String(this.historyIndex); }
    id(){ return `el_${Math.random().toString(16).slice(2,10)}`; }
    toast(msg,type){ const n=document.createElement('div'); n.className=`builder-toast ${type}`; n.textContent=msg; document.body.appendChild(n); setTimeout(()=>n.remove(),2200); }
}
window.addEventListener('DOMContentLoaded', () => new FooterBuilder());
