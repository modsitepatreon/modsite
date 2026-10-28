'use strict';
document.getElementById('mobile-menu')?.addEventListener('click',e=>{const b=e.currentTarget;const side=document.getElementById('game-sidebar');if(!side)return;const open=side.classList.toggle('mobile-open');b.setAttribute('aria-expanded',String(open));});
document.addEventListener('keydown',e=>{if(e.key==='Escape'){document.getElementById('game-sidebar')?.classList.remove('mobile-open');document.getElementById('mobile-menu')?.setAttribute('aria-expanded','false');}});
