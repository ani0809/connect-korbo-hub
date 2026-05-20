class HomepageBuilder {
    constructor() {
        this.sections = window.homepageConfig?.sections || [];
        this.library = window.homepageLibrary || [];
        this.isDirty = false;
        this.activeSettings = null;
        this.init();
    }
    init() {
        this.renderLibrary(this.library);
        this.renderCanvas();
        this.bindSectionPanelSearch();
        this.bindSave();
        this.bindPreview();
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { e.preventDefault(); this.save(); }
            if (e.key === 'Escape') { this.activeSettings = null; this.renderCanvas(); }
        });
        window.addEventListener('beforeunload', (e) => { if (this.isDirty) { e.preventDefault(); e.returnValue = ''; } });
    }
    renderLibrary(items) {
        const wrap = document.getElementById('section-library');
        wrap.innerHTML = '';
        items.forEach((type) => {
            const card = document.createElement('div');
            card.className = 'section-lib-card';
            card.innerHTML = `<div class="thumb">${this.getSectionPreview(type)}</div><h4>${type}</h4><button data-t="${type}">+ Add Section</button>`;
            card.querySelector('button').addEventListener('click', () => this.addSection(type));
            wrap.appendChild(card);
        });
    }
    renderCanvas() {
        const el = document.getElementById('homepage-canvas');
        el.innerHTML = '';
        this.sections.forEach((section, i) => {
            const card = document.createElement('div');
            card.className = `section-card ${section.enabled ? '' : 'is-disabled'}`;
            card.draggable = true;
            card.dataset.i = String(i);
            card.innerHTML = `<div class="head"><span>? ${section.type}</span><span>${section.enabled ? '' : '<em>Disabled</em>'}</span></div><div class="mini">${this.getSectionPreview(section.type)}</div><div class="actions"><button data-a="toggle">??</button><button data-a="settings">?</button><button data-a="clone">?</button><button data-a="up">?</button><button data-a="down">?</button><button data-a="del">??</button></div><div class="settings-inline"></div>`;
            card.querySelectorAll('button[data-a]').forEach((btn) => btn.addEventListener('click', () => {
                const action = btn.dataset.a;
                if (action === 'toggle') this.toggleSection(section.id);
                if (action === 'settings') this.openSettings(section.id);
                if (action === 'clone') this.cloneSection(section.id);
                if (action === 'up' && i > 0) this.reorderSections(i, i - 1);
                if (action === 'down' && i < this.sections.length - 1) this.reorderSections(i, i + 1);
                if (action === 'del') this.deleteSection(section.id);
            }));
            card.addEventListener('dragstart', () => card.classList.add('dragging'));
            card.addEventListener('dragend', () => card.classList.remove('dragging'));
            card.addEventListener('dragover', (e) => e.preventDefault());
            card.addEventListener('drop', (e) => {
                e.preventDefault();
                const from = Number(document.querySelector('.section-card.dragging')?.dataset.i ?? -1);
                if (from >= 0 && from !== i) this.reorderSections(from, i);
            });
            el.appendChild(card);
            if (this.activeSettings === section.id) this.renderSettingsInline(card.querySelector('.settings-inline'), section);
        });
    }
    renderSettingsInline(target, section) {
        target.innerHTML = `<div class="inline-panel"><label>Section ID<input value="${section.settings.section_id || ''}" data-k="section_id"></label><label>Padding Top<input type="number" value="${parseInt(section.settings.padding_top || 60, 10)}" data-k="padding_top"></label><label>Padding Bottom<input type="number" value="${parseInt(section.settings.padding_bottom || 60, 10)}" data-k="padding_bottom"></label><label>Background<input type="color" value="${section.settings.background || '#ffffff'}" data-k="background"></label></div>`;
        target.querySelectorAll('input[data-k]').forEach((input) => input.addEventListener('input', (e) => {
            const k = e.target.dataset.k;
            section.settings[k] = k.includes('padding') ? `${e.target.value}px` : e.target.value;
            this.markDirty();
        }));
    }
    bindSectionPanelSearch() { document.getElementById('section-search')?.addEventListener('input', (e) => this.renderLibrary(this.library.filter((s) => s.toLowerCase().includes(e.target.value.toLowerCase())))); }
    addSection(type) { const s={id:'sec_'+this.generateId(),type,enabled:true,sort_order:this.sections.length+1,settings:this.getSectionDefaults(type)}; this.sections.push(s); this.activeSettings=s.id; this.markDirty(); this.renderCanvas(); }
    openSettings(id) { this.activeSettings = this.activeSettings === id ? null : id; this.renderCanvas(); }
    toggleSection(id) { const s=this.findSection(id); if (s){s.enabled=!s.enabled;this.markDirty();this.renderCanvas();} }
    deleteSection(id) { if (!confirm('Delete this section? Cannot be undone.')) return; this.sections=this.sections.filter((s)=>s.id!==id); this.markDirty(); this.renderCanvas(); }
    cloneSection(id){ const s=this.findSection(id); if(!s)return; const c=JSON.parse(JSON.stringify(s)); c.id='sec_'+this.generateId(); c.settings.title=(c.settings.title||c.type)+' (Copy)'; const i=this.sections.findIndex(x=>x.id===id); this.sections.splice(i+1,0,c); this.markDirty(); this.renderCanvas(); }
    reorderSections(from,to){ const x=this.sections.splice(from,1)[0]; this.sections.splice(to,0,x); this.sections.forEach((s,i)=>s.sort_order=i+1); this.markDirty(); this.renderCanvas(); }
    save(){ const b=document.getElementById('hp-save'); b.disabled=true; b.textContent='Saving...'; fetch('/api/builder/homepage/save',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':window.csrfToken},body:JSON.stringify({config:{version:'1.0',sections:this.sections}})}).then(r=>r.json()).then(j=>alert(j.message||'Saved')).catch(()=>alert('Could not save homepage config.')).finally(()=>{b.disabled=false;b.textContent='Save';this.isDirty=false;}); }
    bindSave(){ document.getElementById('hp-save')?.addEventListener('click',()=>this.save()); }
    bindPreview(){ document.getElementById('hp-preview')?.addEventListener('click',()=>window.open('/','_blank')); }
    getSectionPreview(type){ return `<svg viewBox="0 0 120 40" width="120" height="40"><rect x="1" y="1" width="118" height="38" rx="4" fill="#e2e8f0"/><text x="8" y="24" fill="#334155" font-size="11">${type}</text></svg>`; }
    getSectionDefaults(type){ return {title:type.replace('-', ' '),padding_top:'60px',padding_bottom:'60px',background:'#ffffff',visibility:{hide_mobile:false,hide_desktop:false}}; }
    findSection(id){ return this.sections.find((s)=>s.id===id); }
    generateId(){ return Math.random().toString(16).slice(2,8); }
    markDirty(){ this.isDirty=true; }
}

document.addEventListener('DOMContentLoaded',()=>new HomepageBuilder());
