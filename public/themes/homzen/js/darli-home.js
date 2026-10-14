/* ==========================================================================
   DARLI REAL ESTATE PLATFORM - HOMEPAGE INTERACTIONS
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
  // Initialize Lucide icons if available
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }

  // ==========================================
  // HERO FLOATING SEARCH BAR - ADVANCED TOGGLE
  // ==========================================
  const toggleAdvBtn = document.getElementById('darli-toggle-advanced');
  const advPanel = document.getElementById('darli-advanced-panel');

  if (toggleAdvBtn && advPanel) {
    toggleAdvBtn.addEventListener('click', function (e) {
      e.preventDefault();
      const isOpen = advPanel.classList.toggle('is-open');
      toggleAdvBtn.classList.toggle('active', isOpen);
      toggleAdvBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  }

  // ==========================================
  // MORTGAGE CALCULATOR
  // ==========================================
  const priceInput = document.getElementById('calc-price');
  const downPaymentInput = document.getElementById('calc-down-payment');
  const downPaymentSlider = document.getElementById('calc-slider');
  const sliderPercentLabel = document.getElementById('calc-slider-percent');
  const periodSelect = document.getElementById('calc-period');
  const interestInput = document.getElementById('calc-interest');
  const calcBtn = document.getElementById('calc-submit-btn');
  const resultDisplay = document.getElementById('calc-result-value');

  function parseFormattedNumber(val) {
    if (!val) return 0;
    return parseFloat(val.toString().replace(/[^\d.]/g, '')) || 0;
  }

  function formatNumber(num) {
    return new Intl.NumberFormat('en-US').format(Math.round(num));
  }

  function updateMortgageCalculation() {
    if (!priceInput || !downPaymentInput || !resultDisplay) return;

    const price = parseFormattedNumber(priceInput.value) || 2500000;
    const downPayment = parseFormattedNumber(downPaymentInput.value) || 500000;
    const years = parseFloat(periodSelect ? periodSelect.value : 20) || 20;
    const annualRate = parseFloat(interestInput ? interestInput.value : 8.5) || 8.5;

    const loanAmount = Math.max(0, price - downPayment);
    if (loanAmount <= 0) {
      resultDisplay.textContent = '0 ج.م';
      return;
    }

    const monthlyRate = (annualRate / 100) / 12;
    const totalMonths = years * 12;

    let monthlyPayment = 0;
    if (monthlyRate === 0) {
      monthlyPayment = loanAmount / totalMonths;
    } else {
      monthlyPayment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, totalMonths)) / (Math.pow(1 + monthlyRate, totalMonths) - 1);
    }

    resultDisplay.textContent = formatNumber(monthlyPayment) + ' ج.م';
  }

  if (priceInput && downPaymentInput && downPaymentSlider) {
    // Sync slider when typing in down payment or price
    function syncSliderFromInputs() {
      const price = parseFormattedNumber(priceInput.value) || 2500000;
      const downPayment = parseFormattedNumber(downPaymentInput.value) || 0;
      let percent = Math.round((downPayment / price) * 100);
      percent = Math.min(Math.max(percent, 0), 100);
      downPaymentSlider.value = percent;
      if (sliderPercentLabel) sliderPercentLabel.textContent = percent + '%';
    }

    // Sync input when dragging slider
    downPaymentSlider.addEventListener('input', function () {
      const percent = parseFloat(this.value) || 0;
      if (sliderPercentLabel) sliderPercentLabel.textContent = percent + '%';
      const price = parseFormattedNumber(priceInput.value) || 2500000;
      const downPayment = Math.round(price * (percent / 100));
      downPaymentInput.value = formatNumber(downPayment);
      updateMortgageCalculation();
    });

    priceInput.addEventListener('input', function () {
      const cursor = this.selectionStart;
      const raw = parseFormattedNumber(this.value);
      this.value = formatNumber(raw);
      syncSliderFromInputs();
      updateMortgageCalculation();
    });

    downPaymentInput.addEventListener('input', function () {
      const raw = parseFormattedNumber(this.value);
      this.value = formatNumber(raw);
      syncSliderFromInputs();
      updateMortgageCalculation();
    });

    if (periodSelect) periodSelect.addEventListener('change', updateMortgageCalculation);
    if (interestInput) interestInput.addEventListener('change', updateMortgageCalculation);
    if (calcBtn) {
      calcBtn.addEventListener('click', function (e) {
        e.preventDefault();
        updateMortgageCalculation();
      });
    }

    // Initial calculation
    syncSliderFromInputs();
    updateMortgageCalculation();
  }

  // ==========================================
  // RECOMMENDED PROPERTY IMAGE SLIDER
  // ==========================================
  const recImgs = [
    '/storage/properties/2.jpg',
    '/storage/properties/1.jpg',
    '/storage/properties/3.jpg',
    '/storage/properties/4.jpg'
  ];
  let curRecIdx = 0;
  const recImgEl = document.getElementById('rec-slider-img');
  const recPrevBtn = document.getElementById('rec-prev-btn');
  const recNextBtn = document.getElementById('rec-next-btn');

  function showRecImg(idx) {
    if (!recImgEl) return;
    curRecIdx = (idx + recImgs.length) % recImgs.length;
    recImgEl.style.opacity = '0.4';
    setTimeout(() => {
      recImgEl.src = recImgs[curRecIdx];
      recImgEl.style.opacity = '1';
    }, 150);
  }

  if (recPrevBtn && recNextBtn) {
    recPrevBtn.addEventListener('click', () => showRecImg(curRecIdx - 1));
    recNextBtn.addEventListener('click', () => showRecImg(curRecIdx + 1));
  }

  // ==========================================
  // REUSABLE MOUSE DRAG-TO-SCROLL HELPER
  // ==========================================
  function makeDraggableScroll(slider) {
    if (!slider) return;

    let isDown = false;
    let startX = 0;
    let startScrollLeft = 0;
    let clickPrevented = false;

    slider.style.cursor = 'grab';

    slider.addEventListener('mousedown', (e) => {
      if (e.button !== 0) return; // Only left click
      isDown = true;
      clickPrevented = false;
      slider.classList.add('is-dragging');
      slider.style.cursor = 'grabbing';
      slider.style.scrollBehavior = 'auto';
      slider.style.scrollSnapType = 'none';
      startX = e.pageX;
      startScrollLeft = slider.scrollLeft;
    });

    window.addEventListener('mousemove', (e) => {
      if (!isDown) return;
      const dx = e.pageX - startX;
      if (Math.abs(dx) > 4) {
        clickPrevented = true;
      }
      slider.scrollLeft = startScrollLeft - dx;
    });

    window.addEventListener('mouseup', () => {
      if (!isDown) return;
      isDown = false;
      slider.classList.remove('is-dragging');
      slider.style.cursor = 'grab';
      slider.style.scrollBehavior = 'smooth';
      slider.style.scrollSnapType = 'x mandatory';
      setTimeout(() => {
        clickPrevented = false;
      }, 50);
    });

    // Prevent clicking links/buttons during drag
    slider.addEventListener('click', (e) => {
      if (clickPrevented) {
        e.preventDefault();
        e.stopPropagation();
      }
    }, true);

    // Mouse wheel horizontal scroll
    slider.addEventListener('wheel', (e) => {
      if (Math.abs(e.deltaY) > Math.abs(e.deltaX) && slider.scrollWidth > slider.clientWidth) {
        e.preventDefault();
        const step = 260;
        const dir = document.dir === 'rtl' ? -1 : 1;
        slider.scrollBy({
          left: (e.deltaY > 0 ? step : -step) * dir,
          behavior: 'smooth'
        });
      }
    }, { passive: false });
  }

  // ==========================================
  // PARTNERS SLIDER (Drag-to-Scroll + Arrows + Dots)
  // ==========================================
  const partnerSlider = document.querySelector('.darli-partners-grid');
  const partnerDots = document.querySelectorAll('.darli-partners-section .darli-dot');
  const partnerPrev = document.querySelector('.darli-partners-prev');
  const partnerNext = document.querySelector('.darli-partners-next');

  if (partnerSlider) {
    makeDraggableScroll(partnerSlider);

    function getCardStep() {
      const card = partnerSlider.querySelector('.darli-partner-card');
      return card ? card.offsetWidth + 20 : 260;
    }

    if (partnerPrev) {
      partnerPrev.addEventListener('click', () => {
        const step = getCardStep();
        const dir = document.dir === 'rtl' ? 1 : -1;
        partnerSlider.scrollBy({ left: step * dir, behavior: 'smooth' });
      });
    }

    if (partnerNext) {
      partnerNext.addEventListener('click', () => {
        const step = getCardStep();
        const dir = document.dir === 'rtl' ? -1 : 1;
        partnerSlider.scrollBy({ left: step * dir, behavior: 'smooth' });
      });
    }

    // Dynamic dot updates on scroll
    let scrollRaf = null;
    function updatePartnerDots() {
      if (!partnerDots.length) return;
      if (scrollRaf) cancelAnimationFrame(scrollRaf);
      scrollRaf = requestAnimationFrame(() => {
        const maxScroll = partnerSlider.scrollWidth - partnerSlider.clientWidth;
        if (maxScroll <= 0) return;
        const curScroll = Math.abs(partnerSlider.scrollLeft);
        const ratio = Math.min(1, Math.max(0, curScroll / maxScroll));
        const activeIdx = Math.round(ratio * (partnerDots.length - 1));
        partnerDots.forEach((d, i) => d.classList.toggle('active', i === activeIdx));
      });
    }

    partnerSlider.addEventListener('scroll', updatePartnerDots);

    partnerDots.forEach((dot, index) => {
      dot.addEventListener('click', () => {
        const maxScroll = partnerSlider.scrollWidth - partnerSlider.clientWidth;
        const targetScroll = (index / (partnerDots.length - 1)) * maxScroll;
        const finalLeft = document.dir === 'rtl' ? -targetScroll : targetScroll;
        partnerSlider.scrollTo({ left: finalLeft, behavior: 'smooth' });
      });
    });
  }

  // Also enable drag to scroll on mobile chips scroll if present
  const chipsScroll = document.querySelector('.chips-scroll');
  if (chipsScroll) {
    makeDraggableScroll(chipsScroll);
  }

  // ==========================================
  // TESTIMONIALS SLIDER (Drag-to-Scroll + Dots)
  // ==========================================
  const testiSlider = document.querySelector('.darli-testimonials-grid');
  const testiDots = document.querySelectorAll('.darli-testimonials-section .darli-dot');

  if (testiSlider) {
    makeDraggableScroll(testiSlider);

    let testiScrollRaf = null;
    function updateTestiDots() {
      if (!testiDots.length) return;
      if (testiScrollRaf) cancelAnimationFrame(testiScrollRaf);
      testiScrollRaf = requestAnimationFrame(() => {
        const maxScroll = testiSlider.scrollWidth - testiSlider.clientWidth;
        if (maxScroll <= 0) return;
        const curScroll = Math.abs(testiSlider.scrollLeft);
        const ratio = Math.min(1, Math.max(0, curScroll / maxScroll));
        const activeIdx = Math.round(ratio * (testiDots.length - 1));
        testiDots.forEach((d, i) => d.classList.toggle('active', i === activeIdx));
      });
    }

    testiSlider.addEventListener('scroll', updateTestiDots);

    testiDots.forEach((dot, index) => {
      dot.addEventListener('click', () => {
        const maxScroll = testiSlider.scrollWidth - testiSlider.clientWidth;
        const targetScroll = (index / (testiDots.length - 1)) * maxScroll;
        const finalLeft = document.dir === 'rtl' ? -targetScroll : targetScroll;
        testiSlider.scrollTo({ left: finalLeft, behavior: 'smooth' });
      });
    });
  }
});
