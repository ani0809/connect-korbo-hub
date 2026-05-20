class ProductCardBuilder {
    constructor() {
        this.cards = window.productCards || [];
        this.active = window.activeProductCard || this.defaultCard();
        this.isDirty = false;
        this.init();
    }
    defaultCard() {
        return {id:'card_custom_1',name:'My Custom Card',is_active:true,settings:{border:true,border_color:'#e2e8f0',border_radius:'12px',padding:'15px',background:'#ffffff'},layout:[{id:'thumb',type:'thumbnail',settings:{ratio:'1:1'},children:[{id:'wish',type:'wishlist-button',settings:{style:'circle'}}]},{id:'info',type:'info-block',settings:{},children:[{id:'title',type:'title',settings:{}},{id:'price',type:'price',settings:{}},{id:'atc',type:'add-to-cart',settings:{text:'Add to Cart'}}]}]};
    }
    init() { if (!this.cards.length) this.cards = [this.defaultCard()]; this.renderCardsList(); this.renderPreview(); this.bindActions(); window.addEventListener('beforeunload',(e)=>{if(this.isDirty){e.preventDefault();e.returnValue='';}}); }
    renderCardsList() {
        const el = document.getElementById('cards-list'); el.innerHTML='';
        this.cards.forEach((c) => {
            const n = document.createElement('div'); n.className = `card-item ${c.id===this.active.id?'active':''}`;
            n.innerHTML = `<strong>${c.name}</strong> ${c.is_active?'<span>Active</span>':''}<div><button data-a="edit">Edit</button><button data-a="active">Set Active</button><button data-a="del">Delete</button></div>`;
            n.querySelector('[data-a="edit"]').addEventListener('click',()=>{this.active=c;this.renderCardsList();this.renderPreview();});
            n.querySelector('[data-a="active"]').addEventListener('click',()=>{this.cards.forEach(x=>x.is_active=false);c.is_active=true;this.active=c;this.markDirty();this.renderCardsList();});
            n.querySelector('[data-a="del"]').addEventListener('click',()=>{if(confirm('Delete this design?')){this.cards=this.cards.filter(x=>x.id!==c.id);this.active=this.cards[0]||this.defaultCard();this.markDirty();this.renderCardsList();this.renderPreview();}});
            el.appendChild(n);
        });
    }
    renderPreview() {
        document.getElementById('card-name').value = this.active.name || '';
        const wrap = document.getElementById('card-preview');
        wrap.innerHTML = `<div class="pc-mock" style="border:${this.active.settings.border?'1px solid '+this.active.settings.border_color:'none'};border-radius:${this.active.settings.border_radius};padding:${this.active.settings.padding};background:${this.active.settings.background};"><div class="thumb">Thumbnail</div><div class="info"><div class="cat">Category</div><div class="title">Demo product title here</div><div class="price">$59.00 <del>$79.00</del></div><button>Add to Cart</button></div></div>`;
        const blocks = document.getElementById('card-elements');
        blocks.innerHTML = ['thumbnail','badges','title','price','rating','category','short-description','add-to-cart','wishlist-button','compare-button','quick-view-button','seller-name','stock-bar','timer'].map((x)=>`<button data-b="${x}">+ ${x}</button>`).join('');
        blocks.querySelectorAll('button[data-b]').forEach((b)=>b.addEventListener('click',()=>{this.active.layout.push({id:'blk_'+Math.random().toString(16).slice(2,6),type:b.dataset.b,settings:{}});this.markDirty();this.renderSettings();}));
        this.renderSettings();
    }
    renderSettings() {
        const el = document.getElementById('card-settings');
        el.innerHTML = `<h4>Card Settings</h4><label>Border Color<input type="color" id="cs-border" value="${this.active.settings.border_color || '#e2e8f0'}"></label><label>Background<input type="color" id="cs-bg" value="${this.active.settings.background || '#ffffff'}"></label><label>Padding<input type="range" min="0" max="30" value="${parseInt(this.active.settings.padding || '15',10)}" id="cs-pad"></label><h5>Blocks</h5>${this.active.layout.map((b)=>`<div class="blk-row">${b.type}</div>`).join('')}`;
        el.querySelector('#cs-border')?.addEventListener('input',(e)=>{this.active.settings.border_color=e.target.value;this.markDirty();this.renderPreview();});
        el.querySelector('#cs-bg')?.addEventListener('input',(e)=>{this.active.settings.background=e.target.value;this.markDirty();this.renderPreview();});
        el.querySelector('#cs-pad')?.addEventListener('input',(e)=>{this.active.settings.padding=`${e.target.value}px`;this.markDirty();this.renderPreview();});
    }
    bindActions() {
        document.getElementById('pc-new')?.addEventListener('click',()=>{const c=this.defaultCard();c.id='card_'+Math.random().toString(16).slice(2,8);c.name='New Card';this.cards.push(c);this.active=c;this.markDirty();this.renderCardsList();this.renderPreview();});
        document.getElementById('pc-dup')?.addEventListener('click',()=>{const c=JSON.parse(JSON.stringify(this.active));c.id='card_'+Math.random().toString(16).slice(2,8);c.name=c.name+' (Copy)';this.cards.push(c);this.active=c;this.markDirty();this.renderCardsList();this.renderPreview();});
        document.getElementById('pc-preview')?.addEventListener('click',()=>window.open('/shop','_blank'));
        document.getElementById('pc-save')?.addEventListener('click',()=>this.save());
        document.getElementById('card-name')?.addEventListener('input',(e)=>{this.active.name=e.target.value;this.markDirty();this.renderCardsList();});
        document.addEventListener('keydown',(e)=>{if((e.ctrlKey||e.metaKey)&&e.key.toLowerCase()==='s'){e.preventDefault();this.save();}});
    }
    save() { const b=document.getElementById('pc-save'); b.disabled=true; b.textContent='Saving...'; fetch('/api/builder/product-card/save',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':window.csrfToken},body:JSON.stringify({config:this.cards})}).then(r=>r.json()).then(j=>alert(j.message||'Saved')).catch(()=>alert('Could not save card config.')).finally(()=>{b.disabled=false;b.textContent='Save';this.isDirty=false;}); }
    markDirty() { this.isDirty=true; }
}
window.addEventListener('DOMContentLoaded',()=>new ProductCardBuilder());
