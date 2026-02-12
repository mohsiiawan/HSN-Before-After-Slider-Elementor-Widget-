(function () {
  function clamp(n, min, max) { return Math.max(min, Math.min(max, n)); }

  function setPos(wrap, pct) {
    pct = clamp(pct, 0, 100);
    const beforeClip = wrap.querySelector(".hsn-ba-before");
    const line = wrap.querySelector(".hsn-ba-line");
    const knob = wrap.querySelector(".hsn-ba-knob");
    const range = wrap.querySelector(".hsn-ba-range");
    if (!beforeClip || !line || !knob) return;

    // Before overlays after: change ONLY overlay width
    beforeClip.style.width = pct + "%";
    line.style.left = pct + "%";
    knob.style.left = pct + "%";
    if (range) range.value = String(pct);
    wrap.setAttribute("data-pos", String(pct));
  }

  function pctFromPointer(wrap, e) {
    const rect = wrap.getBoundingClientRect();
    const clientX = e.clientX != null ? e.clientX : (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
    const x = clientX - rect.left;
    return (x / rect.width) * 100;
  }

  function initOne(wrap) {
    if (wrap.__hsnBAInit) return;
    wrap.__hsnBAInit = true;

    const range = wrap.querySelector(".hsn-ba-range");
    const startAttr = parseFloat(wrap.getAttribute("data-start"));
    const start = !Number.isNaN(startAttr) ? startAttr : (range ? parseFloat(range.value) : 50);
    setPos(wrap, start);

    // Keyboard / a11y
    if (range) range.addEventListener("input", (ev) => setPos(wrap, parseFloat(ev.target.value)));

    let dragging = false;

    function down(e) {
      dragging = true;
      wrap.classList.add("hsn-ba-dragging");
      if (wrap.setPointerCapture && e.pointerId != null) {
        try { wrap.setPointerCapture(e.pointerId); } catch (_) {}
      }
      setPos(wrap, pctFromPointer(wrap, e));
      e.preventDefault();
    }

    function move(e) {
      if (!dragging) return;
      setPos(wrap, pctFromPointer(wrap, e));
      e.preventDefault();
    }

    function up(e) {
      if (!dragging) return;
      dragging = false;
      wrap.classList.remove("hsn-ba-dragging");
      if (wrap.releasePointerCapture && e.pointerId != null) {
        try { wrap.releasePointerCapture(e.pointerId); } catch (_) {}
      }
      e.preventDefault();
    }

    wrap.addEventListener("pointerdown", down, { passive: false });
    window.addEventListener("pointermove", move, { passive: false });
    window.addEventListener("pointerup", up, { passive: false });
    window.addEventListener("pointercancel", up, { passive: false });
  }

  function initAll() {
    document.querySelectorAll(".hsn-ba-wrap").forEach(initOne);
  }

  document.addEventListener("DOMContentLoaded", initAll);

  // Elementor editor support
  if (window.elementorFrontend && window.elementorFrontend.hooks) {
    window.elementorFrontend.hooks.addAction("frontend/element_ready/widget", function () {
      initAll();
    });
  }
})();
