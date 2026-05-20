<div class="notification-bell" x-data="NotificationBell()" @click.away="open = false">
  <button @click="open = !open" class="bell-btn" aria-label="Notifications">?? <span class="bell-badge" x-show="unread > 0" x-text="unread > 99 ? '99+' : unread"></span></button>
  <div class="notification-dropdown" x-show="open" x-transition>
    <div class="notif-header"><span class="notif-title">Notifications</span><button @click="markAllRead()" x-show="unread > 0" class="notif-read-all">Mark all read</button></div>
    <div class="notif-list" x-ref="list">
      <template x-for="n in notifications" :key="n.id"><a :href="n.url ?? '#'" class="notif-item" :class="{ 'unread': !n.read_at }" @click="markRead(n.id)"><div class="notif-icon"><span x-text="n.icon ?? '??'"></span></div><div class="notif-content"><div class="notif-text" x-text="n.title"></div><div class="notif-time" x-text="n.time"></div></div><div class="notif-dot" x-show="!n.read_at"></div></a></template>
      <div x-show="notifications.length === 0" class="notif-empty">?? No notifications yet</div>
    </div>
    <div class="notif-footer" x-show="notifications.length > 0"><a href="/account/notifications">View All Notifications</a></div>
  </div>
</div>
<script>
function NotificationBell(){return{open:false,unread:0,notifications:[],async init(){if(!window.siteConfig?.isLoggedIn)return;await this.load();setInterval(()=>this.load(),30000);},async load(){const res=await fetch('/api/notifications?limit=10');const data=await res.json();this.notifications=data.notifications||[];this.unread=data.unread_count||0;},async markRead(id){await fetch('/api/notifications/'+id+'/read',{method:'POST',headers:{'X-CSRF-TOKEN':window.siteConfig.csrfToken}});const n=this.notifications.find(x=>x.id===id);if(n){n.read_at=new Date().toISOString();this.unread=Math.max(0,this.unread-1);}},async markAllRead(){await fetch('/api/notifications/mark-all-read',{method:'POST',headers:{'X-CSRF-TOKEN':window.siteConfig.csrfToken}});this.notifications.forEach(n=>n.read_at=new Date().toISOString());this.unread=0;}}}
</script>
