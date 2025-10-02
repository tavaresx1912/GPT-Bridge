const input_usuário = document.getElementById("mensagem");
const botaoEnviar = document.querySelector(".btn-submit");
const resposta = document.getElementById("resposta-chat");

input_usuário.addEventListener("input", function(){
  if(input_usuário.value.trim() !== ""){
    botaoEnviar.classList.remove("apagar")
  }else{
    botaoEnviar.classList.add("apagar")
  }
})

document.addEventListener("DOMContentLoaded", function () {
  botaoEnviar.addEventListener("click", function (e) {
    resposta.classList.remove("apagar");
  });
});