document.addEventListener("DOMContentLoaded", function () {
  const input_usuário = document.querySelector(".btn-submit");
  const mensagem = document.getElementById("resposta-chat");

  input_usuário.addEventListener("click", function (e) {
    e.preventDefault();
    mensagem.classList.remove("apagar");
  });
});