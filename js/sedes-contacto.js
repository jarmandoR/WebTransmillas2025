(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    var sedes = document.getElementById("sedes");
    if (!sedes) return;

    function formatPhone(number) {
      var digits = String(number || "").replace(/\D/g, "");
      if (digits.indexOf("57") === 0 && digits.length === 12) digits = digits.slice(2);
      if (digits.length !== 10) return "";
      return digits.slice(0, 3) + " " + digits.slice(3, 6) + " " + digits.slice(6);
    }

    sedes.querySelectorAll('a[title="Llamar"]').forEach(function (link) {
      link.href = "tel:+573002173949";
      link.removeAttribute("onclick");
      link.dataset.contact = "300 217 3949";
      link.setAttribute("aria-label", "Llamar al 300 217 3949");
    });

    sedes.querySelectorAll('a[title="Whatsapp"]').forEach(function (link) {
      var phone = "";
      try {
        phone = formatPhone(new URL(link.href, window.location.href).searchParams.get("phone"));
      } catch (error) {
        phone = "";
      }
      link.dataset.contact = phone || "WhatsApp";
      link.setAttribute("aria-label", phone ? "Escribir por WhatsApp al " + phone : "Escribir por WhatsApp");
    });
  });
})();
