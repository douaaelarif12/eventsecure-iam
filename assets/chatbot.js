document.addEventListener('DOMContentLoaded', () => {
  const button = document.getElementById('chatbot-button');
  const widget = document.getElementById('chatbot-widget');
  const close = document.getElementById('chatbot-close');
  const send = document.getElementById('chatbot-send');
  const input = document.getElementById('chatbot-input');
  const messages = document.getElementById('chatbot-messages');
  if (!button || !widget || !close || !send || !input || !messages) return;
  const append = (className, text) => { const item=document.createElement('div'); item.className=className; item.textContent=String(text); messages.appendChild(item); messages.scrollTop=messages.scrollHeight; };
  const sendMessage = () => { const message=input.value.trim(); if (!message) return; append('user-message', message); input.value=''; fetch('/gestion_evenements/chatbot/chatbot_api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({question:message})}).then(r=>r.ok?r.json():Promise.reject(new Error('request failed'))).then(data=>append('bot-message',data?.answer||'Je ne peux pas répondre pour le moment.')).catch(error=>{append('bot-message text-danger','Erreur serveur. Veuillez réessayer.'); console.error(error);}); };
  button.onclick=()=>{widget.style.display='flex';}; close.onclick=()=>{widget.style.display='none';}; send.onclick=sendMessage; input.addEventListener('keypress',e=>{if(e.key==='Enter') sendMessage();});
});
