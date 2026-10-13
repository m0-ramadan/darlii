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
  // PARTNERS SLIDER DOTS & ARROWS
  // ==========================================
  const partnerDots = document.querySelectorAll('.darli-partners-section .darli-dot');
  const partnerPrev = document.querySelector('.darli-partners-prev');
  const partnerNext = document.querySelector('.darli-partners-next');
  let curPartnerIdx = 0;

  function setPartnerActive(idx) {
    if (!partnerDots.length) return;
    curPartnerIdx = (idx + partnerDots.length) % partnerDots.length;
    partnerDots.forEach((d, i) => d.classList.toggle('active', i === curPartnerIdx));
  }

  partnerDots.forEach((dot, index) => {
    dot.addEventListener('click', () => setPartnerActive(index));
  });

  if (partnerPrev) {
    partnerPrev.addEventListener('click', () => setPartnerActive(curPartnerIdx - 1));
  }
  if (partnerNext) {
    partnerNext.addEventListener('click', () => setPartnerActive(curPartnerIdx + 1));
  }

  // ==========================================
  // TESTIMONIALS SLIDER DOTS
  // ==========================================
  const testiDots = document.querySelectorAll('.darli-testimonials-section .darli-dot');
  testiDots.forEach((dot, index) => {
    dot.addEventListener('click', function () {
      testiDots.forEach(d => d.classList.remove('active'));
      this.classList.add('active');
    });
  });
});
