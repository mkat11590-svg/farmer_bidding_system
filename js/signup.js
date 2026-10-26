document.addEventListener('DOMContentLoaded', () => {
  // ── Cached elements ────────────────────────────────────────
  const form              = document.getElementById('signupForm');
  const buyerRadio        = document.getElementById('buyerRadio');
  const farmerRadio       = document.getElementById('farmerRadio');
  const buyerFields       = document.getElementById('buyerFields');
  const farmerFields      = document.getElementById('farmerFields');
  const password          = document.getElementById('password');
  const confirmPassword   = document.getElementById('confirmPassword');
  const strengthFill      = document.getElementById('strengthFill');
  const strengthText      = document.getElementById('strengthText');
  const errorMessage      = document.getElementById('errorMessage');
  const successMessage    = document.getElementById('successMessage');

  // ── User type toggle ───────────────────────────────────────
  buyerRadio?.addEventListener('change',  () => toggleUserType(true));
  farmerRadio?.addEventListener('change', () => toggleUserType(false));

  function toggleUserType(isBuyer) {
    buyerFields.style.display  = isBuyer ? 'block' : 'none';
    farmerFields.style.display = isBuyer ? 'none'  : 'block';
  }

  // ── Password visibility (DRY) ──────────────────────────────
  function toggleVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon  = btn.querySelector('i');
    if (!input || !icon) return;

    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
  }

  document.getElementById('togglePassword')?.addEventListener('click', function() {
    toggleVisibility('password', this);
  });

  document.getElementById('toggleConfirmPassword')?.addEventListener('click', function() {
    toggleVisibility('confirmPassword', this);
  });

  // ── Password strength ──────────────────────────────────────
  password?.addEventListener('input', () => {
    const val = password.value;
    let score = 0;
    if (val.length    >= 8)                    score++;
    if (/[a-z]/.test(val) && /[A-Z]/.test(val)) score++;
    if (/\d/.test(val))                        score++;
    if (/[^a-zA-Z\d]/.test(val))               score++;

    strengthFill.className = 'strength-fill';
    if      (score === 0) { strengthFill.style.width = '0%';   strengthText.textContent = 'Password strength'; }
    else if (score <= 2)  { strengthFill.classList.add('weak');   strengthFill.style.width = '33%'; strengthText.textContent = 'Weak'; }
    else if (score === 3) { strengthFill.classList.add('medium'); strengthFill.style.width = '66%'; strengthText.textContent = 'Medium'; }
    else                  { strengthFill.classList.add('strong'); strengthFill.style.width = '100%'; strengthText.textContent = 'Strong'; }
  });

  // ── Submit validation ──────────────────────────────────────
  form?.addEventListener('submit', e => {
    e.preventDefault();
    errorMessage.classList.remove('show');
    successMessage.classList.remove('show');

    const pwd = password.value.trim();
    const pwd2 = confirmPassword.value.trim();

    if (!buyerRadio.checked && !farmerRadio.checked)   return showError('Please select Buyer or Farmer.');
    if (pwd !== pwd2)                                  return showError('Passwords do not match.');
    if (pwd.length < 8)                                return showError('Password must be ≥ 8 characters.');
    if (!/[A-Z]/.test(pwd))                            return showError('At least one uppercase letter required.');
    if (!/[a-z]/.test(pwd))                            return showError('At least one lowercase letter required.');
    if (!/\d/.test(pwd))                               return showError('At least one number required.');

    // User-type specific
    if (buyerRadio.checked) {
      const el = document.getElementById('companyName');
      if (el && el.value.trim() === '') return showError('Company name is required.');
    }
    if (farmerRadio.checked) {
      const el = document.getElementById('farmLocation');
      if (el && el.value.trim() === '') return showError('Farm location is required.');
    }

    // Success
    successMessage.textContent = 'Validation passed — creating account…';
    successMessage.classList.add('show');
    successMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });

    setTimeout(() => form.submit(), 1400);
  });

  function showError(msg) {
    errorMessage.textContent = msg;
    errorMessage.classList.add('show');
    errorMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
});