(function () {
  "use strict";

  var API_URL = "https://sistema.transmillas.com/nueva_plataforma/api/solicitudesWeb/";

  function getForm() {
    return document.getElementById("tm-web-service-form");
  }

  function setStatus(message, type) {
    var status = document.getElementById("tm-form-status");
    if (!status) return;
    status.textContent = message || "";
    status.className = type ? "is-" + type : "";
  }

  function option(value, label) {
    var item = document.createElement("option");
    item.value = value;
    item.textContent = label;
    return item;
  }

  function fillSelect(select, items, valueKey, labelKey, placeholder) {
    select.innerHTML = "";
    select.appendChild(option("", placeholder));
    (items || []).forEach(function (item) {
      select.appendChild(option(item[valueKey], item[labelKey]));
    });
  }

  function loadCatalogs(form) {
    setStatus("Cargando información del formulario...", "loading");

    return fetch(API_URL + "?accion=catalogos", {
      method: "GET",
      headers: { "X-Requested-With": "XMLHttpRequest" }
    })
      .then(function (response) {
        if (!response.ok) throw new Error("No fue posible cargar los catálogos.");
        return response.json();
      })
      .then(function (result) {
        if (!result.ok || !result.data) throw new Error(result.mensaje || "Respuesta inválida.");

        form.querySelectorAll("[data-catalog]").forEach(function (select) {
          var catalog = select.getAttribute("data-catalog");
          if (catalog === "ciudades_origen" || catalog === "ciudades_destino") {
            fillSelect(select, result.data[catalog], "idciudades", "ciu_nombre", "Selecciona una ciudad");
          } else if (catalog === "tipos_via") {
            fillSelect(select, result.data.tipos_via, "dir_nombre", "dir_nombre", "Selecciona el tipo de vía");
          } else if (catalog === "lugares") {
            fillSelect(select, result.data.lugares, "lug_nombre", "lug_nombre", "Selecciona un lugar");
          }
        });

        setStatus("", "");
      })
      .catch(function (error) {
        setStatus(error.message + " Recarga la página para intentarlo nuevamente.", "error");
        throw error;
      });
  }

  function debounce(callback, wait) {
    var timeout;
    return function () {
      var args = arguments;
      clearTimeout(timeout);
      timeout = setTimeout(function () {
        callback.apply(null, args);
      }, wait);
    };
  }

  function setField(form, name, value) {
    var field = form.elements[name];
    if (field) field.value = value || "";
  }

  function parseAddress(address) {
    var parts = (address || "").split("&");
    var streetNumbers = { principal: "", secondary: "", plate: "" };
    var base = parts[1] || "";
    var hashParts = base.split("#");
    streetNumbers.principal = (hashParts[0] || "").trim();
    var numberParts = (hashParts[1] || "").split("-");
    streetNumbers.secondary = (numberParts[0] || "").trim();
    streetNumbers.plate = numberParts.slice(1).join("-").trim();

    return {
      streetType: (parts[0] || "").trim(),
      principal: streetNumbers.principal,
      secondary: streetNumbers.secondary,
      plate: streetNumbers.plate,
      place: (parts[2] || "").trim(),
      neighborhood: (parts[4] || "").trim()
    };
  }

  function applyClient(form, role, client) {
    var address = parseAddress(client.cli_direccion);

    if (role === "sender") {
      setField(form, "param1", client.cli_iddocumento);
      setField(form, "param6", client.cli_nombre);
      setField(form, "param4", client.cli_idciudad);
      setField(form, "param5", address.streetType);
      setField(form, "dir1R", address.principal);
      setField(form, "dir2R", address.secondary);
      setField(form, "dir3R", address.plate);
      setField(form, "selectComplemento", address.place);
      setField(form, "param23", address.neighborhood);
    } else {
      setField(form, "param9", client.cli_nombre);
      setField(form, "param11", client.cli_idciudad);
      setField(form, "param10", address.streetType);
      setField(form, "dir1D", address.principal);
      setField(form, "dir2D", address.secondary);
      setField(form, "dir3D", address.plate);
      setField(form, "param21", address.place);
      setField(form, "param24", address.neighborhood);
    }
  }

  function getRoleSection(form, role) {
    return form.querySelector('[data-client-role="' + role + '"]');
  }

  function setClientStatus(form, role, message, type) {
    var section = getRoleSection(form, role);
    var status = section && section.querySelector("[data-client-status]");
    if (!status) return;
    status.textContent = message || "";
    status.className = "tm-client-check-status" + (type ? " is-" + type : "");
  }

  function setProgressiveVisibility(element, visible) {
    if (!element) return;
    element.classList.toggle("is-progressive-hidden", !visible);
  }

  function updateFinalVisibility(form) {
    var sender = getRoleSection(form, "sender");
    var receiver = getRoleSection(form, "receiver");
    var ready = sender && receiver &&
      sender.dataset.clientState === "resolved" &&
      receiver.dataset.clientState === "resolved";

    form.querySelectorAll("[data-progressive-final]").forEach(function (element) {
      setProgressiveVisibility(element, ready);
    });

    var submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) submitButton.disabled = !ready;
  }

  function resolveClientRole(form, role, found) {
    var section = getRoleSection(form, role);
    if (!section) return;

    section.dataset.clientState = "resolved";
    section.classList.toggle("is-client-found", found === true);
    section.classList.toggle("is-client-new", found !== true);

    section.querySelectorAll("[data-progressive-detail]").forEach(function (label) {
      var field = label.querySelector("input, select, textarea");
      var hasValue = field && String(field.value || "").trim() !== "";
      setProgressiveVisibility(label, found !== true || !hasValue);
    });

    if (found === true) {
      setClientStatus(form, role, "Datos encontrados. Solo completa los campos que hagan falta.", "found");
    } else if (found === null) {
      setClientStatus(form, role, "No fue posible verificar el número. Completa los datos para continuar.", "new");
    } else {
      setClientStatus(form, role, "No encontramos este número. Completa los datos para registrarlo.", "new");
    }
    updateFinalVisibility(form);
  }

  function resetClientRole(form, role, clearFields) {
    var section = getRoleSection(form, role);
    if (!section) return;

    section.dataset.clientState = "pending";
    section.classList.remove("is-client-found", "is-client-new");
    section.querySelectorAll("[data-progressive-detail]").forEach(function (label) {
      setProgressiveVisibility(label, false);
      if (clearFields) {
        var field = label.querySelector("input, select, textarea");
        if (field) field.value = "";
      }
    });

    if (clearFields) {
      var cityField = role === "sender" ? form.elements.param4 : form.elements.param11;
      if (cityField) cityField.value = "";
    }
    setClientStatus(form, role, "", "");
    updateFinalVisibility(form);
  }

  function initializeProgressiveForm(form) {
    form.classList.add("is-progressive");
    resetClientRole(form, "sender", false);
    resetClientRole(form, "receiver", false);
    form.querySelectorAll("[data-progressive-final]").forEach(function (element) {
      setProgressiveVisibility(element, false);
    });
  }

  function findClient(form, role) {
    var phoneField = role === "sender" ? form.elements.param2 : form.elements.param8;
    var phone = phoneField.value.trim();
    if (phone.replace(/\D/g, "").length < 7) return Promise.resolve(false);

    phoneField.classList.add("is-searching");
    return fetch(API_URL, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
        "X-Requested-With": "XMLHttpRequest"
      },
      body: new URLSearchParams({ accion: "cliente", telefono: phone }).toString()
    })
      .then(function (response) { return response.json(); })
      .then(function (result) {
        if (phoneField.value.trim() !== phone) return false;
        if (!result.ok || !result.encontrado || !result.cliente) return false;
        applyClient(form, role, result.cliente);
        return true;
      })
      .catch(function () {
        return null;
      })
      .finally(function () {
        phoneField.classList.remove("is-searching");
      });
  }

  function loadCredits(form) {
    var senderPhone = form.elements.param2.value.trim();
    var receiverPhone = form.elements.param8.value.trim();
    var field = document.getElementById("tm-credit-field");
    var select = document.getElementById("tm-credit-select");

    if (!field || !select || (senderPhone.length < 7 && receiverPhone.length < 7)) {
      if (field) field.hidden = true;
      return;
    }

    var body = new URLSearchParams({
      accion: "creditos",
      telremitente: senderPhone,
      teldestino: receiverPhone
    });

    fetch(API_URL, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
        "X-Requested-With": "XMLHttpRequest"
      },
      body: body.toString()
    })
      .then(function (response) { return response.json(); })
      .then(function (result) {
        select.innerHTML = "";
        select.appendChild(option("", "Selecciona un crédito si aplica"));

        if (!result.ok || !Array.isArray(result.creditos) || result.creditos.length === 0) {
          field.hidden = true;
          return;
        }

        result.creditos.forEach(function (credit) {
          select.appendChild(option(credit.idcreditos, credit.cre_nombre));
        });
        field.hidden = false;
      })
      .catch(function () {
        field.hidden = true;
      });
  }

  function validateFile(form) {
    var input = form.querySelector('input[type="file"]');
    var file = input && input.files ? input.files[0] : null;
    if (!file) return true;

    if (file.size > 5 * 1024 * 1024) {
      setStatus("La fotografía supera el tamaño máximo de 5 MB.", "error");
      input.focus();
      return false;
    }
    return true;
  }

  function submitForm(form) {
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }
    if (!validateFile(form)) return;

    var button = form.querySelector('button[type="submit"]');
    var formData = new FormData(form);
    formData.append("accion", "crear");

    button.disabled = true;
    button.dataset.originalText = button.innerHTML;
    button.textContent = "Enviando solicitud...";
    setStatus("Estamos registrando tu solicitud.", "loading");

    fetch(API_URL, {
      method: "POST",
      headers: { "X-Requested-With": "XMLHttpRequest" },
      body: formData
    })
      .then(function (response) {
        return response.json().then(function (data) {
          if (!response.ok || !data.ok) {
            throw new Error(data.mensaje || "No fue posible enviar la solicitud.");
          }
          return data;
        });
      })
      .then(function (result) {
        var reference = result.idservicio ? " Número de solicitud: " + result.idservicio + "." : "";
        setStatus("Solicitud registrada correctamente." + reference, "success");
        form.reset();
        document.getElementById("tm-credit-field").hidden = true;
        initializeProgressiveForm(form);
      })
      .catch(function (error) {
        setStatus(error.message, "error");
      })
      .finally(function () {
        button.innerHTML = button.dataset.originalText;
        updateFinalVisibility(form);
      });
  }

  document.addEventListener("DOMContentLoaded", function () {
    var form = getForm();
    if (!form) return;

    initializeProgressiveForm(form);
    loadCatalogs(form).catch(function () {});

    var checkCredits = debounce(function () { loadCredits(form); }, 450);
    form.elements.param2.addEventListener("blur", function () {
      var phone = form.elements.param2.value.replace(/\D/g, "");
      if (phone.length < 7) {
        setClientStatus(form, "sender", phone.length ? "Ingresa un número válido para continuar." : "", phone.length ? "new" : "");
        return;
      }
      setClientStatus(form, "sender", "Verificando el número...", "checking");
      findClient(form, "sender").then(function (found) {
        resolveClientRole(form, "sender", found);
        checkCredits();
      });
    });
    form.elements.param8.addEventListener("blur", function () {
      var phone = form.elements.param8.value.replace(/\D/g, "");
      if (phone.length < 7) {
        setClientStatus(form, "receiver", phone.length ? "Ingresa un número válido para continuar." : "", phone.length ? "new" : "");
        return;
      }
      setClientStatus(form, "receiver", "Verificando el número...", "checking");
      findClient(form, "receiver").then(function (found) {
        resolveClientRole(form, "receiver", found);
        checkCredits();
      });
    });

    form.elements.param2.addEventListener("input", function () {
      var section = getRoleSection(form, "sender");
      if (section && section.dataset.clientState === "resolved") {
        resetClientRole(form, "sender", true);
      }
    });
    form.elements.param8.addEventListener("input", function () {
      var section = getRoleSection(form, "receiver");
      if (section && section.dataset.clientState === "resolved") {
        resetClientRole(form, "receiver", true);
      }
    });

    form.addEventListener("input", function (event) {
      var field = event.target;
      if (!field || field.type === "email" || field.type === "file" || field.type === "tel") return;
      if (field.tagName === "INPUT" || field.tagName === "TEXTAREA") {
        var start = field.selectionStart;
        var end = field.selectionEnd;
        field.value = field.value.toUpperCase();
        if (typeof start === "number") field.setSelectionRange(start, end);
      }
    });

    form.addEventListener("submit", function (event) {
      event.preventDefault();
      submitForm(form);
    });
  });
})();
