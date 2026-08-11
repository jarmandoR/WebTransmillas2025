(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    var modal = document.getElementById("tm-whatsapp-welcome");
    if (!modal) return;

    var closeElements = modal.querySelectorAll("[data-whatsapp-welcome-close]");

    function closeModal() {
      modal.hidden = true;
      modal.setAttribute("aria-hidden", "true");
      document.body.classList.remove("tm-whatsapp-welcome-open");
    }

    function openModal() {
      modal.hidden = false;
      modal.setAttribute("aria-hidden", "false");
      document.body.classList.add("tm-whatsapp-welcome-open");
      modal.querySelector(".tm-whatsapp-welcome-close").focus();
    }

    closeElements.forEach(function (element) {
      element.addEventListener("click", closeModal);
    });

    modal.querySelector(".tm-whatsapp-welcome-action").addEventListener("click", function () {
      closeModal();
    });

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && !modal.hidden) closeModal();
    });

    window.setTimeout(openModal, 650);
  });
})();
