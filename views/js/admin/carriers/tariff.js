window.SQ_Tariffs = window.SQ_Tariffs || {};

document.addEventListener("DOMContentLoaded", function () {
  const cfg = window.SQ_Tariffs;

  console.log("Tariff config loaded", cfg);

  // esempio uso
  let rowIndex = cfg.rowIndex || 1;

  document
    .getElementById("btn-add-row")
    ?.addEventListener("click", function () {
      const tpl = document.getElementById("tariff-row-template").innerHTML;
      const html = tpl.replaceAll("__INDEX__", rowIndex++);
      document
        .getElementById("tariff-rows")
        .insertAdjacentHTML("beforeend", html);
    });
});
