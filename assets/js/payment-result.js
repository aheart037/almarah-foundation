(function () {
  "use strict";
  var page = document.querySelector(".pr-page");
  if (!page) return;
  page.querySelectorAll("[data-print]").forEach(function (button) {
    button.addEventListener("click", function () { window.print(); });
  });
  var url = page.getAttribute("data-pr-poll");
  var counter = page.querySelector("#prCountdown");
  var line = page.querySelector("#prPoll");
  if (!url || !counter) return;
  var key = "prPolls:" + page.getAttribute("data-pr-ref");
  var tries = 0;
  try { tries = Number(sessionStorage.getItem(key)) || 0; } catch (error) {}
  if (tries >= 12) {
    line.textContent = "Confirmation is still pending. Keep your reference and check the status later. Please do not pay again.";
    return;
  }
  var left = 20;
  var timer = window.setInterval(function () {
    if (document.hidden) return;
    counter.textContent = String(--left);
    if (left <= 0) {
      window.clearInterval(timer);
      try { sessionStorage.setItem(key, String(tries + 1)); } catch (error) {}
      window.location.assign(url);
    }
  }, 1000);
})();
