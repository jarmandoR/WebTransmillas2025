(function () {
  "use strict";

  var API_URL = "https://sistema.transmillas.com/nueva_plataforma/api/rastreoWeb/";
  var GUIDE_URL = "https://sistema.transmillas.com/nueva_plataforma/controller/VerguiaController.php?guia=";

  function normalizeText(value) {
    return String(value || "")
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .trim()
      .toUpperCase();
  }

  function baseGuide(value) {
    return normalizeText(value).replace(/[RE]$/, "");
  }

  function stateCode(rastreo) {
    var code = Number(rastreo.estado_codigo);
    return Number.isInteger(code) ? code : null;
  }

  function isPickedUp(rastreo) {
    var code = stateCode(rastreo);
    if (code !== null) {
      return [4, 6, 7, 8, 9, 10, 11, 12, 13, 14, 16, 17, 18, 19, 20, 22].indexOf(code) !== -1;
    }

    return normalizeText(rastreo.estado) === "PAQUETE RECOGIDO";
  }

  function isDelivered(rastreo) {
    var code = stateCode(rastreo);
    if (code !== null) return code === 10;

    var status = normalizeText(rastreo.estado);
    return status === "ENTREGADO" || status === "ENTREGADA";
  }

  function documentUrl(guide, suffix) {
    return GUIDE_URL + encodeURIComponent(baseGuide(guide) + suffix);
  }

  function progressStage(rastreo) {
    var code = stateCode(rastreo);
    var stagesByState = {
      0: 1, 1: 1, 2: 1, 15: 1, 21: 1, 100: 1,
      3: 2, 4: 2, 5: 2,
      6: 3, 16: 3, 17: 3,
      7: 4, 12: 4, 13: 4,
      8: 5, 14: 5,
      9: 6, 11: 6, 18: 6, 19: 6, 20: 6, 22: 6,
      10: 7
    };

    if (code !== null && stagesByState[code]) return stagesByState[code];

    var apiStage = Number(rastreo.etapa);
    var fallbackStages = { 1: 1, 2: 2, 3: 3, 4: 6, 5: 7 };
    return fallbackStages[apiStage] || 1;
  }

  function parseJsonResponse(response) {
    return response.text().then(function (body) {
      var payload;
      try {
        payload = JSON.parse(body);
      } catch (error) {
        throw new Error("El sistema de rastreo devolvió una respuesta inválida.");
      }
      if (!response.ok || !payload.ok) {
        throw new Error(payload.mensaje || "No fue posible consultar el envío.");
      }
      return payload;
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    var form = document.getElementById("tm-hero-tracking-form");
    var feedback = document.getElementById("tm-hero-tracker-feedback");
    var modal = document.getElementById("tm-tracking-modal");
    var modalGuide = document.getElementById("tm-tracking-result-guide");
    var modalStatus = document.getElementById("tm-tracking-result-status");
    var modalDescription = document.getElementById("tm-tracking-result-description");
    var progressSteps = document.querySelectorAll("#tm-tracking-progress-steps [data-stage]");
    var deliveryPhoto = document.getElementById("tm-tracking-delivery-photo");
    var deliveryPhotoLink = document.getElementById("tm-tracking-delivery-photo-link");
    var deliveryPhotoImage = document.getElementById("tm-tracking-delivery-photo-image");
    var pickupGuideLink = document.getElementById("tm-tracking-pickup-guide-link");
    var deliveryGuideLink = document.getElementById("tm-tracking-delivery-guide-link");
    var lastFocusedElement = null;
    if (!form || !feedback) return;

    function closeModal() {
      if (!modal) return;
      modal.hidden = true;
      modal.setAttribute("aria-hidden", "true");
      document.body.classList.remove("tm-modal-open");
      if (lastFocusedElement) lastFocusedElement.focus();
    }

    function openModal(rastreo) {
      if (!modal) return;
      lastFocusedElement = document.activeElement;
      var guide = rastreo.guia || form.elements.guia.value.trim().toUpperCase();
      modalGuide.textContent = guide;
      modalStatus.textContent = rastreo.estado || "Estado consultado";
      modalDescription.textContent = rastreo.descripcion || "Tu envío fue encontrado correctamente.";

      var currentStage = progressStage(rastreo);
      progressSteps.forEach(function (step) {
        var stepStage = Number(step.dataset.stage);
        step.classList.toggle("is-complete", Number.isInteger(currentStage) && stepStage < currentStage);
        step.classList.toggle("is-current", Number.isInteger(currentStage) && stepStage === currentStage);
        if (stepStage === currentStage) {
          step.setAttribute("aria-current", "step");
        } else {
          step.removeAttribute("aria-current");
        }
      });

      deliveryPhoto.hidden = true;
      deliveryPhotoLink.removeAttribute("href");
      deliveryPhotoImage.removeAttribute("src");
      if (rastreo.imagen_entrega_url) {
        deliveryPhotoImage.src = rastreo.imagen_entrega_url;
        deliveryPhotoLink.href = rastreo.imagen_entrega_url;
        deliveryPhoto.hidden = false;
      }

      pickupGuideLink.hidden = true;
      pickupGuideLink.removeAttribute("href");
      if (isPickedUp(rastreo)) {
        pickupGuideLink.href = documentUrl(guide, "R");
        pickupGuideLink.hidden = false;
      }

      deliveryGuideLink.hidden = true;
      deliveryGuideLink.removeAttribute("href");
      if (isDelivered(rastreo)) {
        deliveryGuideLink.href = documentUrl(guide, "E");
        deliveryGuideLink.hidden = false;
      }

      modal.hidden = false;
      modal.setAttribute("aria-hidden", "false");
      document.body.classList.add("tm-modal-open");
      modal.querySelector(".tm-tracking-modal-close").focus();
    }

    if (modal) {
      deliveryPhotoImage.addEventListener("error", function () {
        deliveryPhoto.hidden = true;
      });

      modal.querySelectorAll("[data-tracking-close]").forEach(function (element) {
        element.addEventListener("click", closeModal);
      });

      document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && !modal.hidden) closeModal();
      });
    }

    form.addEventListener("submit", function (event) {
      event.preventDefault();
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }

      var button = form.querySelector('button[type="submit"]');
      var data = new FormData(form);
      data.set("guia", form.elements.guia.value.trim().toUpperCase());

      button.disabled = true;
      button.dataset.originalText = button.innerHTML;
      button.textContent = "Consultando...";
      feedback.textContent = "Buscando tu envío...";
      feedback.className = "tm-hero-tracker-feedback";

      fetch(API_URL, {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
        body: data
      })
        .then(function (response) {
          return parseJsonResponse(response);
        })
        .then(function (payload) {
          feedback.textContent = "";
          feedback.className = "tm-hero-tracker-feedback";
          openModal(payload.rastreo);
        })
        .catch(function (error) {
          feedback.textContent = error instanceof TypeError
            ? "No pudimos conectar con el sistema de rastreo. Intenta nuevamente."
            : error.message;
          feedback.className = "tm-hero-tracker-feedback is-error";
        })
        .finally(function () {
          button.disabled = false;
          button.innerHTML = button.dataset.originalText;
        });
    });
  });
})();
