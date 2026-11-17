(function () {
  "use strict";

  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var root = document.documentElement;

  function storageGet(key) {
    try {
      return window.localStorage.getItem(key);
    } catch (error) {
      return null;
    }
  }

  function storageSet(key, value) {
    try {
      window.localStorage.setItem(key, value);
    } catch (error) {
      /* storage may be unavailable */
    }
  }

  function applyTheme(theme) {
    if (theme === "dark") {
      root.setAttribute("data-theme", "dark");
    } else {
      root.removeAttribute("data-theme");
    }
    storageSet("oliyettebook-theme", theme);
  }

  var savedTheme = storageGet("oliyettebook-theme");
  if (savedTheme === "dark") {
    root.setAttribute("data-theme", "dark");
  }

  document.querySelectorAll("[data-theme-toggle]").forEach(function (button) {
    button.addEventListener("click", function () {
      applyTheme(root.getAttribute("data-theme") === "dark" ? "light" : "dark");
    });
  });

  var drawer = document.getElementById("mobileNav");
  var backdrop = document.querySelector(".drawer-backdrop");
  var navToggle = document.querySelector("[data-nav-toggle]");

  function openDrawer() {
    if (!drawer) return;
    drawer.hidden = false;
    if (backdrop) backdrop.hidden = false;
    window.requestAnimationFrame(function () {
      drawer.classList.add("is-open");
      if (backdrop) backdrop.classList.add("is-open");
    });
    document.body.classList.add("nav-open");
    if (navToggle) navToggle.setAttribute("aria-expanded", "true");
    var firstLink = drawer.querySelector("a, button");
    if (firstLink) firstLink.focus();
  }

  function closeDrawer() {
    if (!drawer) return;
    drawer.classList.remove("is-open");
    if (backdrop) backdrop.classList.remove("is-open");
    document.body.classList.remove("nav-open");
    if (navToggle) navToggle.setAttribute("aria-expanded", "false");
    window.setTimeout(function () {
      drawer.hidden = true;
      if (backdrop) backdrop.hidden = true;
    }, reduceMotion ? 0 : 320);
    if (navToggle) navToggle.focus();
  }

  if (navToggle) {
    navToggle.addEventListener("click", function () {
      if (drawer && drawer.classList.contains("is-open")) {
        closeDrawer();
      } else {
        openDrawer();
      }
    });
  }

  document.querySelectorAll("[data-nav-close]").forEach(function (element) {
    element.addEventListener("click", closeDrawer);
  });

  if (drawer) {
    drawer.querySelectorAll("a").forEach(function (link) {
      link.addEventListener("click", function () {
        if (window.innerWidth < 768) closeDrawer();
      });
    });
  }

  document.querySelectorAll("[data-flash]").forEach(function (flash) {
    var close = flash.querySelector("[data-flash-close]");
    function dismiss() {
      flash.classList.add("is-leaving");
      window.setTimeout(function () {
        flash.remove();
      }, reduceMotion ? 0 : 320);
    }
    if (close) close.addEventListener("click", dismiss);
    window.setTimeout(dismiss, 6500);
  });

  document.querySelectorAll(".password-field").forEach(function (wrapper) {
    var input = wrapper.querySelector("input");
    var toggle = wrapper.querySelector("[data-password-toggle]");
    if (!input || !toggle) return;
    toggle.addEventListener("click", function () {
      var showing = input.type === "text";
      input.type = showing ? "password" : "text";
      toggle.setAttribute("aria-label", showing ? "Show password" : "Hide password");
      toggle.setAttribute("title", showing ? "Show password" : "Hide password");
      input.focus({ preventScroll: true });
    });
  });

  var lastFocused = null;

  function focusableElements(container) {
    return Array.prototype.slice.call(
      container.querySelectorAll(
        'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
      )
    ).filter(function (element) {
      return element.offsetParent !== null;
    });
  }

  function openModal(modal) {
    if (!modal) return;
    lastFocused = document.activeElement;
    modal.hidden = false;
    document.body.classList.add("modal-open");
    window.requestAnimationFrame(function () {
      modal.classList.add("is-open");
    });
    var focusables = focusableElements(modal);
    if (focusables.length) focusables[0].focus();
  }

  function closeModal(modal) {
    if (!modal) return;
    modal.classList.remove("is-open");
    document.body.classList.remove("modal-open");
    window.setTimeout(function () {
      modal.hidden = true;
    }, reduceMotion ? 0 : 280);
    if (lastFocused && typeof lastFocused.focus === "function") {
      lastFocused.focus();
    }
  }

  document.querySelectorAll("[data-modal-target]").forEach(function (trigger) {
    trigger.addEventListener("click", function (event) {
      event.preventDefault();
      openModal(document.getElementById(trigger.getAttribute("data-modal-target")));
    });
  });

  document.querySelectorAll("[data-modal]").forEach(function (modal) {
    modal.querySelectorAll("[data-modal-close]").forEach(function (close) {
      close.addEventListener("click", function () {
        closeModal(modal);
      });
    });
    var backdropElement = modal.querySelector(".modal__backdrop");
    if (backdropElement) {
      backdropElement.addEventListener("click", function () {
        closeModal(modal);
      });
    }
    modal.addEventListener("keydown", function (event) {
      if (event.key === "Escape") {
        event.preventDefault();
        closeModal(modal);
        return;
      }
      if (event.key !== "Tab") return;
      var focusables = focusableElements(modal);
      if (!focusables.length) return;
      var first = focusables[0];
      var last = focusables[focusables.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    });
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape" && drawer && drawer.classList.contains("is-open")) {
      closeDrawer();
    }
  });

  window.addEventListener("resize", function () {
    if (window.innerWidth >= 768 && drawer && drawer.classList.contains("is-open")) {
      closeDrawer();
    }
  });

  var revealItems = document.querySelectorAll(".reveal");
  if (!revealItems.length) {
    revealItems = [];
  }

  if (reduceMotion || !("IntersectionObserver" in window)) {
    revealItems.forEach(function (item) {
      item.classList.add("is-visible");
    });
  } else {
    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.12, rootMargin: "0px 0px -40px 0px" }
    );
    revealItems.forEach(function (item) {
      observer.observe(item);
    });
  }

  var cottageMore = document.querySelector("[data-cottage-more]");
  if (cottageMore) {
    var extraCottages = document.querySelectorAll("[data-cottage-extra]");

    cottageMore.addEventListener("click", function () {
      var expanded = cottageMore.getAttribute("aria-expanded") === "true";

      extraCottages.forEach(function (card) {
        if (expanded) {
          card.hidden = true;
          card.classList.remove("is-visible");
        } else {
          card.hidden = false;
          card.classList.add("is-visible");
        }
      });

      cottageMore.setAttribute("aria-expanded", expanded ? "false" : "true");
      cottageMore.textContent = expanded ? "View more cottages" : "Show fewer cottages";

      if (expanded) {
        var header = document.querySelector(".page-head");
        if (header) {
          header.scrollIntoView({ behavior: reduceMotion ? "auto" : "smooth", block: "start" });
        }
      }
    });
  }

  var roomMore = document.querySelector("[data-room-more]");
  if (roomMore) {
    var extraRooms = document.querySelectorAll("[data-room-extra]");

    roomMore.addEventListener("click", function () {
      var expanded = roomMore.getAttribute("aria-expanded") === "true";

      extraRooms.forEach(function (card) {
        if (expanded) {
          card.hidden = true;
          card.classList.remove("is-visible");
        } else {
          card.hidden = false;
          card.classList.add("is-visible");
        }
      });

      roomMore.setAttribute("aria-expanded", expanded ? "false" : "true");
      roomMore.textContent = expanded ? "View more rooms" : "Show fewer rooms";

      if (expanded) {
        var header = document.querySelector(".page-head");
        if (header) {
          header.scrollIntoView({ behavior: reduceMotion ? "auto" : "smooth", block: "start" });
        }
      }
    });
  }

  document.querySelectorAll("[data-confirm]").forEach(function (element) {
    var message = element.getAttribute("data-confirm") || "Are you sure?";
    if (element.tagName === "FORM") {
      element.addEventListener("submit", function (event) {
        if (!window.confirm(message)) event.preventDefault();
      });
    } else {
      element.addEventListener("click", function (event) {
        if (!window.confirm(message)) event.preventDefault();
      });
    }
  });

  document.querySelectorAll('input[data-phone]').forEach(function (input) {
    input.addEventListener("blur", function () {
      var digits = input.value.replace(/\D+/g, "");
      if (digits.length === 11 && digits.slice(0, 2) === "09") {
        input.value = digits.slice(0, 4) + " " + digits.slice(4, 7) + " " + digits.slice(7);
      }
    });
  });

  var bookingForm = document.getElementById("bookingForm");
  var summaryModal = document.getElementById("bookingSummary");
  var summaryConfirm = document.getElementById("summaryConfirm");
  var summaryPhoto = document.getElementById("summaryPhoto");
  var summaryBody = document.getElementById("summaryBody");
  var summaryTotal = document.getElementById("summaryTotal");
  var unavailable = [];

  var unavailableNode = document.getElementById("unavailableDates");
  if (unavailableNode) {
    try {
      unavailable = JSON.parse(unavailableNode.textContent || "[]");
    } catch (error) {
      unavailable = [];
    }
  }

  function toDate(value) {
    if (!value) return null;
    var parts = value.split("-").map(Number);
    if (parts.length !== 3) return null;
    return new Date(parts[0], parts[1] - 1, parts[2]);
  }

  function dateValue(date) {
    var month = String(date.getMonth() + 1).padStart(2, "0");
    var day = String(date.getDate()).padStart(2, "0");
    return date.getFullYear() + "-" + month + "-" + day;
  }

  function nightsBetween(checkIn, checkOut) {
    if (!checkIn || !checkOut) return 0;
    var inDate = toDate(checkIn);
    var outDate = toDate(checkOut);
    if (!inDate || !outDate) return 0;
    var days = Math.round((outDate - inDate) / 86400000);
    return days > 0 ? days : 0;
  }

  function toDateTime(dateValueString, timeValueString, fallback) {
    var date = toDate(dateValueString);
    if (!date) return null;
    var time = String(timeValueString || fallback || "00:00").split(":");
    if (time.length < 2) return null;
    date.setHours(Number(time[0]) || 0, Number(time[1]) || 0, 0, 0);
    return date;
  }

  function overlapsUnavailable(checkIn, checkOut, checkInTime, checkOutTime) {
    if (!checkIn || !checkOut || !unavailable.length) return false;
    var start = toDateTime(checkIn, checkInTime, "14:00");
    var end = toDateTime(checkOut, checkOutTime, "12:00");
    if (!start || !end) return false;
    return unavailable.some(function (range) {
      var rStart = toDateTime(range.check_in, range.check_in_time, "14:00");
      var rEnd = toDateTime(range.check_out, range.check_out_time, "12:00");
      return rStart && rEnd && start < rEnd && end > rStart;
    });
  }

  function formatTime(timeValueString) {
    var time = String(timeValueString || "").split(":");
    if (time.length < 2) return "";
    var hours = Number(time[0]);
    var minutes = time[1];
    var suffix = hours >= 12 ? "PM" : "AM";
    var display = hours % 12;
    if (display === 0) display = 12;
    return display + ":" + minutes + " " + suffix;
  }

  function setAvailabilityMessage(message) {
    var node = document.getElementById("dateAvailability");
    if (!node) return;
    node.textContent = message || "";
    node.hidden = !message;
  }

  function syncDateInputs() {
    if (!bookingForm) return;
    var checkIn = bookingForm.querySelector('[name="check_in"]');
    var checkOut = bookingForm.querySelector('[name="check_out"]');
    var checkInTime = bookingForm.querySelector('[name="check_in_time"]');
    var checkOutTime = bookingForm.querySelector('[name="check_out_time"]');
    if (!checkIn || !checkOut) return;
    var today = new Date();
    today.setHours(0, 0, 0, 0);
    var todayValue = dateValue(today);
    checkIn.min = todayValue;

    if (checkIn.value) {
      checkOut.min = checkIn.value;
      if (!checkOut.value || checkOut.value < checkIn.value) {
        checkOut.value = checkIn.value;
      }
    }

    if (checkIn.setCustomValidity) checkIn.setCustomValidity("");
    if (checkOut.setCustomValidity) checkOut.setCustomValidity("");
    if (checkInTime && checkInTime.setCustomValidity) checkInTime.setCustomValidity("");
    if (checkOutTime && checkOutTime.setCustomValidity) checkOutTime.setCustomValidity("");

    var inTime = checkInTime ? checkInTime.value : "14:00";
    var outTime = checkOutTime ? checkOutTime.value : "12:00";
    var start = toDateTime(checkIn.value, inTime, "14:00");
    var end = toDateTime(checkOut.value, outTime, "12:00");

    if (start && end && end <= start) {
      setAvailabilityMessage("The check-out date and time must be after the check-in date and time.");
      if (checkOutTime) checkOutTime.setCustomValidity("Check-out must be after check-in.");
    } else if (checkIn.value && checkOut.value && overlapsUnavailable(checkIn.value, checkOut.value, inTime, outTime)) {
      setAvailabilityMessage("That schedule overlaps an existing reservation. Please choose another date or time.");
      if (checkOut) checkOut.setCustomValidity("Schedule overlaps an existing reservation.");
    } else {
      setAvailabilityMessage("");
    }
  }

  if (bookingForm) {
    var checkInInput = bookingForm.querySelector('[name="check_in"]');
    var checkOutInput = bookingForm.querySelector('[name="check_out"]');
    var checkInTimeInput = bookingForm.querySelector('[name="check_in_time"]');
    var checkOutTimeInput = bookingForm.querySelector('[name="check_out_time"]');
    if (checkInInput) checkInInput.addEventListener("change", syncDateInputs);
    if (checkOutInput) checkOutInput.addEventListener("change", syncDateInputs);
    if (checkInTimeInput) checkInTimeInput.addEventListener("change", syncDateInputs);
    if (checkOutTimeInput) checkOutTimeInput.addEventListener("change", syncDateInputs);
    syncDateInputs();

    bookingForm.addEventListener("submit", function (event) {
      if (bookingForm.dataset.confirmed === "yes") return;
      if (!summaryModal) return;
      event.preventDefault();

      var data = new FormData(bookingForm);
      var checkIn = String(data.get("check_in") || "");
      var checkOut = String(data.get("check_out") || "");
      var checkInTime = String(data.get("check_in_time") || "14:00");
      var checkOutTime = String(data.get("check_out_time") || "12:00");
      var sameDay = checkIn !== "" && checkIn === checkOut;
      var nights = Math.max(1, nightsBetween(checkIn, checkOut));
      var adults = Number(data.get("adults") || 0);
      var children = Number(data.get("children") || 0);
      var guestName = bookingForm.getAttribute("data-guest-name") || "";
      var cottageName = bookingForm.getAttribute("data-cottage-name") || "";
      var itemLabel = bookingForm.getAttribute("data-item-label") || "Cottage";
      var rate = Number(bookingForm.getAttribute("data-rate") || 0);
      var entranceFee = Number(bookingForm.getAttribute("data-entrance-fee") || 0);
      var photo = bookingForm.getAttribute("data-photo") || "";
      var subtotal = nights * rate;
      var entrance = adults * entranceFee;
      var total = subtotal + entrance;

      if (summaryPhoto) {
        summaryPhoto.src = photo;
        summaryPhoto.alt = cottageName + " photo";
      }

      if (summaryBody) {
        summaryBody.innerHTML =
          '<div class="summary-list">' +
          '<div class="summary-list__row"><span>' + escapeHtml(itemLabel) + "</span><span>" + escapeHtml(cottageName) + "</span></div>" +
          '<div class="summary-list__row"><span>Guest</span><span>' + escapeHtml(guestName) + "</span></div>" +
          '<div class="summary-list__row"><span>Dates</span><span>' + escapeHtml(checkIn + " " + formatTime(checkInTime)) + " to " + escapeHtml(checkOut + " " + formatTime(checkOutTime)) + "</span></div>" +
          '<div class="summary-list__row"><span>Duration</span><span>' + (sameDay ? "1 day (day use)" : nights + " night" + (nights === 1 ? "" : "s")) + "</span></div>" +
          '<div class="summary-list__row"><span>Guests</span><span>' + adults + " adult" + (adults === 1 ? "" : "s") + ", " + children + " child" + (children === 1 ? "" : "ren") + "</span></div>" +
          "</div>" +
          '<div class="price-breakdown mt-2">' +
          '<div class="summary-list__row"><span>' + escapeHtml(itemLabel) + " rate</span><span>" + peso(rate) + " × " + nights + " day" + (nights === 1 ? "" : "s") + " = " + peso(subtotal) + "</span></div>" +
          '<div class="summary-list__row"><span>Entrance fee</span><span>' + adults + " × " + peso(entranceFee) + " = " + peso(entrance) + "</span></div>" +
          '<div class="summary-list__row total-line"><span>Total amount</span><span class="amount">' + peso(total) + "</span></div>" +
          "</div>";
      }

      if (summaryTotal) summaryTotal.textContent = peso(total);
      openModal(summaryModal);
    });
  }

  if (summaryConfirm && bookingForm) {
    summaryConfirm.addEventListener("click", function () {
      bookingForm.dataset.confirmed = "yes";
      closeModal(summaryModal);
      window.setTimeout(function () {
        bookingForm.submit();
      }, 120);
    });
  }

  function peso(amount) {
    return "₱" + Number(amount || 0).toLocaleString("en-PH", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  }

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }
})();
