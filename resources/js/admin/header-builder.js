class HeaderBuilder {
    constructor() {
        this.config = window.builderConfig || { rows: [] };
        this.history = [];
        this.historyIndex = -1;
        this.selectedElement = null;
        this.selectedRow = null;
        this.isDirty = false;
        this.dragData = null;
        this.historyTimer = null;
        this.init();
    }

    init() {
        this.renderCanvas();
        this.bindElementPanelDrag();
        this.bindSaveButton();
        this.bindUndoRedo();
        this.bindPresetLoader();
        this.bindPreview();
        this.bindKeyboard();
        this.pushHistory();
        window.addEventListener('beforeunload', (e) => { if (this.isDirty) { e.preventDefault(); e.returnValue = ''; } });
    }

    renderCanvas() {
        const canvas = document.getElementById('canvas');
        canvas.innerHTML = '';
        (this.config.rows || []).forEach((row, rowIndex) => {
            const rowEl = document.createElement('div');
            rowEl.className = 'builder-row';
            rowEl.innerHTML = `<div class="row-head"><button class="drag-handle" data-row-index="${rowIndex}">??</button><strong>ROW: ${row.id}</strong><div><button class="btn-row-settings" data-row-index="${rowIndex}">?</button><button class="btn-row-toggle" data-row-index="${rowIndex}">${row.enabled ? '??' : '??'}</button></div></div>`;
            const cols = document.createElement('div');
            cols.className = 'row-cols';
            (row.columns || []).forEach((col, colIndex) => {
                const colEl = document.createElement('div');
                colEl.className = 'drop-zone';
                colEl.dataset.row = String(rowIndex);
                colEl.dataset.col = String(colIndex);
                colEl.innerHTML = `<div class="col-meta">${col.id} <span class="badge">${(col.elements || []).length}</span></div>`;
                if ((col.elements || []).length === 0) {
                    const ph = document.createElement('div');
                    ph.className = 'placeholder';
                    ph.textContent = 'Drop here';
                    colEl.appendChild(ph);
                }
                (col.elements || []).forEach((el, elIndex) => {
                    const elNode = document.createElement('div');
                    elNode.className = 'canvas-element';
                    elNode.draggable = true;
                    elNode.dataset.row = String(rowIndex);
                    elNode.dataset.col = String(colIndex);
                    elNode.dataset.index = String(elIndex);
                    elNode.dataset.id = el.id;
                    elNode.innerHTML = `<span>${el.type}</span><div><button class="btn-edit" data-id="${el.id}">?</button><button class="btn-clone" data-id="${el.id}">?</button><button class="btn-del" data-id="${el.id}">?</button></div>`;
                    colEl.appendChild(elNode);
                });
                cols.appendChild(colEl);
            });
            rowEl.appendChild(cols);
            canvas.appendChild(rowEl);
        });

        this.bindCanvasEvents();
    }

    bindElementPanelDrag() {
        document.querySelectorAll('#element-list .element-card').forEach((node) => {
            node.addEventListener('dragstart', (e) => {
                this.dragData = { source: 'panel', type: node.dataset.type };
                e.dataTransfer?.setData('text/plain', JSON.stringify(this.dragData));
            });
        });
    }

    bindCanvasEvents() {
        document.querySelectorAll('.drop-zone').forEach((zone) => {
            zone.addEventListener('dragover', (e) => { e.preventDefault(); zone.classList.add('drag-over'); });
            zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('drag-over');
                const rowId = Number(zone.dataset.row);
                const colId = Number(zone.dataset.col);
                if (this.dragData?.source === 'panel') {
                    this.onDropNewElement(rowId, colId, this.dragData.type, null);
                }
                if (this.dragData?.source === 'canvas') {
                    this.onMoveElement(this.dragData.fromRow, this.dragData.fromCol, this.dragData.fromIndex, rowId, colId, null);
                }
                this.dragData = null;
            });
        });

        document.querySelectorAll('.canvas-element').forEach((el) => {
            el.addEventListener('dragstart', (e) => {
                this.dragData = {
                    source: 'canvas',
                    fromRow: Number(el.dataset.row),
                    fromCol: Number(el.dataset.col),
                    fromIndex: Number(el.dataset.index),
                };
                e.dataTransfer?.setData('text/plain', JSON.stringify(this.dragData));
            });
            el.addEventListener('click', () => this.onSelectElement(el.dataset.id));
        });

        document.querySelectorAll('.btn-edit').forEach((b) => b.addEventListener('click', (e) => { e.stopPropagation(); this.onSelectElement(b.dataset.id); }));
        document.querySelectorAll('.btn-del').forEach((b) => b.addEventListener('click', (e) => { e.stopPropagation(); this.deleteElement(b.dataset.id); }));
        document.querySelectorAll('.btn-clone').forEach((b) => b.addEventListener('click', (e) => { e.stopPropagation(); this.cloneElement(b.dataset.id); }));
        document.querySelectorAll('.btn-row-settings').forEach((b) => b.addEventListener('click', () => this.selectRow(Number(b.dataset.rowIndex))));
        document.querySelectorAll('.btn-row-toggle').forEach((b) => b.addEventListener('click', () => { const idx = Number(b.dataset.rowIndex); this.config.rows[idx].enabled = !this.config.rows[idx].enabled; this.markDirty(); this.pushHistory(); this.renderCanvas(); }));
    }

    onDropNewElement(rowId, colId, elementType) {
        const newElement = { id: this.generateId(), type: elementType, settings: this.getElementDefaults(elementType) };
        this.config.rows[rowId].columns[colId].elements.push(newElement);
        this.markDirty();
        this.pushHistory();
        this.renderCanvas();
    }

    onMoveElement(fromRowId, fromColId, fromIndex, toRowId, toColId) {
        const fromList = this.config.rows[fromRowId].columns[fromColId].elements;
        const [element] = fromList.splice(fromIndex, 1);
        this.config.rows[toRowId].columns[toColId].elements.push(element);
        this.markDirty();
        this.pushHistory();
        this.renderCanvas();
    }

    onSelectElement(elementId) {
        this.selectedElement = elementId;
        this.selectedRow = null;
        const element = this.findElement(elementId);
        this.renderSettingsPanel(element);
    }

    selectRow(rowIndex) {
        this.selectedRow = rowIndex;
        this.selectedElement = null;
        const row = this.config.rows[rowIndex];
        const panel = document.getElementById('settings-panel');
        panel.innerHTML = `<h4>${row.id} settings</h4><label>Height <input id="row-height" value="${row.settings?.height || '80px'}"></label><label>Background <input type="color" id="row-bg" value="${row.settings?.background || '#ffffff'}"></label>`;
        document.getElementById('row-height').addEventListener('input', (e) => { row.settings.height = e.target.value; this.markDirty(); this.renderCanvas(); });
        document.getElementById('row-bg').addEventListener('input', (e) => { row.settings.background = e.target.value; this.markDirty(); this.renderCanvas(); });
    }

    onSettingChange(elementId, key, value) {
        const element = this.findElement(elementId);
        if (!element) return;
        element.settings[key] = value;
        this.updateElementPreview(elementId);
        this.markDirty();
        clearTimeout(this.historyTimer);
        this.historyTimer = setTimeout(() => this.pushHistory(), 500);
    }

    save() {
        const btn = document.getElementById('btn-save');
        const ind = document.getElementById('save-indicator');
        btn.innerHTML = 'Saving...';
        btn.disabled = true;
        ind.innerHTML = '?';

        fetch('/api/builder/header/save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
            body: JSON.stringify({ config: this.config }),
        })
            .then((r) => r.json())
            .then((data) => {
                if (data.success) {
                    this.markClean();
                    this.showToast('Header saved!', 'success');
                } else {
                    this.showToast('Save failed!', 'error');
                }
            })
            .catch(() => this.showToast('Save failed!', 'error'))
            .finally(() => {
                btn.innerHTML = 'Save';
                btn.disabled = false;
                ind.innerHTML = '';
            });
    }

    undo() {
        if (this.historyIndex > 0) {
            this.historyIndex -= 1;
            this.config = JSON.parse(JSON.stringify(this.history[this.historyIndex]));
            this.renderCanvas();
        }
    }

    redo() {
        if (this.historyIndex < this.history.length - 1) {
            this.historyIndex += 1;
            this.config = JSON.parse(JSON.stringify(this.history[this.historyIndex]));
            this.renderCanvas();
        }
    }

    pushHistory() {
        this.history = this.history.slice(0, this.historyIndex + 1);
        this.history.push(JSON.parse(JSON.stringify(this.config)));
        if (this.history.length > 20) this.history.shift();
        this.historyIndex = this.history.length - 1;
        const badge = document.getElementById('undo-count');
        if (badge) badge.textContent = String(this.historyIndex);
    }

    findElement(id) {
        for (const row of this.config.rows || []) {
            for (const col of row.columns || []) {
                const found = (col.elements || []).find((e) => e.id === id);
                if (found) return found;
            }
        }
        return null;
    }

    generateId() { return `el_${Math.random().toString(16).slice(2, 10)}`; }
    getElementDefaults(type) { return { type, text: type, style: 'default' }; }

    renderSettingsPanel(element) {
        const panel = document.getElementById('settings-panel');
        if (!element) {
            panel.textContent = 'Click any element or row to edit settings';
            return;
        }
        const entries = Object.entries(element.settings || {});
        panel.innerHTML = `<h4>${element.type}</h4>` + entries.map(([k, v]) => `<label>${k}<input data-k="${k}" value="${String(v)}"></label>`).join('');
        panel.querySelectorAll('input[data-k]').forEach((i) => {
            i.addEventListener('input', (e) => this.onSettingChange(element.id, e.target.dataset.k, e.target.value));
        });
    }

    updateElementPreview() { this.renderCanvas(); }
    markDirty() { this.isDirty = true; }
    markClean() { this.isDirty = false; }

    showToast(msg, type) {
        const n = document.createElement('div');
        n.className = `builder-toast ${type}`;
        n.textContent = msg;
        document.body.appendChild(n);
        setTimeout(() => n.remove(), 2500);
    }

    loadPreset(presetName) {
        if (!confirm('Are you sure? This will replace current layout.')) return;
        fetch('/api/builder/header/preset', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
            body: JSON.stringify({ preset: presetName }),
        }).then((r) => r.json()).then((j) => {
            if (j.success) {
                this.config = j.data;
                this.pushHistory();
                this.markDirty();
                this.renderCanvas();
            }
        });
    }

    deleteElement(id) {
        for (const row of this.config.rows || []) {
            for (const col of row.columns || []) {
                const idx = (col.elements || []).findIndex((e) => e.id === id);
                if (idx >= 0) {
                    col.elements.splice(idx, 1);
                    this.markDirty();
                    this.pushHistory();
                    this.renderCanvas();
                    return;
                }
            }
        }
    }

    cloneElement(id) {
        for (const row of this.config.rows || []) {
            for (const col of row.columns || []) {
                const el = (col.elements || []).find((e) => e.id === id);
                if (el) {
                    col.elements.push({ ...JSON.parse(JSON.stringify(el)), id: this.generateId() });
                    this.markDirty();
                    this.pushHistory();
                    this.renderCanvas();
                    return;
                }
            }
        }
    }

    reorderRows(fromIndex, toIndex) {
        const [row] = this.config.rows.splice(fromIndex, 1);
        this.config.rows.splice(toIndex, 0, row);
        this.markDirty();
        this.pushHistory();
        this.renderCanvas();
    }

    bindSaveButton() { document.getElementById('btn-save')?.addEventListener('click', () => this.save()); }
    bindUndoRedo() {
        document.getElementById('btn-undo')?.addEventListener('click', () => this.undo());
        document.getElementById('btn-redo')?.addEventListener('click', () => this.redo());
    }
    bindPresetLoader() {
        document.getElementById('btn-load-preset')?.addEventListener('click', () => {
            const preset = document.getElementById('preset-select')?.value || 'preset-1';
            this.loadPreset(preset);
        });
    }
    bindPreview() {
        document.getElementById('btn-preview')?.addEventListener('click', () => window.open('/', '_blank'));
    }
    bindKeyboard() {
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { e.preventDefault(); this.save(); }
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z') { e.preventDefault(); this.undo(); }
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'y') { e.preventDefault(); this.redo(); }
            if (e.key === 'Delete' && this.selectedElement) this.deleteElement(this.selectedElement);
        });
    }
}

document.addEventListener('DOMContentLoaded', () => new HeaderBuilder());
