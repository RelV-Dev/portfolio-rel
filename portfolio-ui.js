// Accessible keyboard behavior shared by the public page's existing modal UI.
document.addEventListener('DOMContentLoaded', () => {
  const specs = [
    ['journeyModal','Riwayat pendidikan','journeyModalClose',true],
    ['lightboxOverlay','Detail proyek','lightboxClose'],
    ['guestbookModalOverlay','Tulis pesan','closeGuestbookModalBtn'],
    ['lightboxFullImg','Pratinjau gambar',null]
  ];
  const dialogs=specs.map(([id,label,closeId,usesHidden])=>({node:document.getElementById(id),label,closeId,usesHidden,open:false,trigger:null})).filter(d=>d.node);
  let stack=[];
  let inertSnapshot=new Map();
  const restoreBackground=()=>{for(const [el,value] of inertSnapshot) el.inert=value; inertSnapshot.clear();};
  const focusables=node=>Array.from(node.querySelectorAll('a[href],button,input,select,textarea,[tabindex="0"]')).filter(el=>!el.disabled && !el.closest('[inert]') && el.getClientRects().length);
  function sync() {
    let changed=false, returnTarget=null;
    for(const d of dialogs) {
      const open=d.usesHidden?!d.node.hidden:d.node.classList.contains('active');
      if(open===d.open) continue;
      changed=true; d.open=open;
      if(open) {d.trigger=document.activeElement; stack.push(d);}
      else {stack=stack.filter(item=>item!==d); returnTarget=d.trigger;}
    }
    if(!changed) return;
    restoreBackground();
    const active=stack.at(-1);
    document.body.style.overflow=active?'hidden':'';
    for(const d of dialogs) {d.node.setAttribute('aria-modal',String(d===active)); d.node.inert=!d.open;}
    if(active) {
      for(const el of document.body.children) {
        if(el===active.node || el.contains(active.node) || ['SCRIPT','STYLE'].includes(el.tagName)) continue;
        inertSnapshot.set(el,el.inert); el.inert=true;
      }
      (active.node.contains(returnTarget)?returnTarget:(focusables(active.node)[0]||active.node)).focus();
    } else if(returnTarget?.isConnected) returnTarget.focus();
  }
  for(const d of dialogs) {
    d.node.setAttribute('role','dialog'); d.node.setAttribute('aria-label',d.label); d.node.tabIndex=-1; d.node.inert=true;
    if(d.closeId) document.getElementById(d.closeId)?.setAttribute('aria-label','Tutup '+d.label.toLowerCase());
    new MutationObserver(sync).observe(d.node,{attributes:true,attributeFilter:['class','hidden']});
  }
  document.addEventListener('keydown',e=>{
    const active=stack.at(-1);
    if(active) {
      if(e.key==='Escape') {
        e.preventDefault();e.stopImmediatePropagation();
        if(active.closeId) document.getElementById(active.closeId).click(); else active.node.click();
      } else if(e.key==='Tab') {
        const list=focusables(active.node), first=list[0], last=list.at(-1);
        if(!first) {e.preventDefault();active.node.focus();}
        else if(e.shiftKey && (document.activeElement===first || document.activeElement===active.node)) {e.preventDefault();last.focus();}
        else if(!e.shiftKey && document.activeElement===last) {e.preventDefault();first.focus();}
      }
    }
    if((e.key==='Enter'||e.key===' ') && e.target.matches('[role="button"]:not(button),.journey-card')) {e.preventDefault();e.target.click();}
  },true);
  document.querySelectorAll('.journey-card,.star-btn').forEach(el=>{el.tabIndex=0;el.setAttribute('role','button');if(el.dataset.value) el.setAttribute('aria-label',`Rating ${el.dataset.value} dari 5`);});
  // Keep duplicated marquee content out of the accessibility tree; use text labels on icon-only actions.
  document.querySelectorAll('.overlay-btn').forEach(el=>el.setAttribute('aria-label',el.title||'Buka proyek'));
});
