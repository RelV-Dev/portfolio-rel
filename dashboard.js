/* ═══════════════════════════════════════════════════════════
   ADMIN DASHBOARD — JavaScript
   ═══════════════════════════════════════════════════════════ */

// ───── Configuration ─────
const SUPABASE_URL  = 'https://srkisngeashoaeiwazcr.supabase.co';
const SUPABASE_KEY  = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InNya2lzbmdlYXNob2FlaXdhemNyIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODA4MjM4NDcsImV4cCI6MjA5NjM5OTg0N30.85xq9c1lbpPrNOBSm8pSZNqwdv2pTh-MWgJtFunC6wI';
const CLOUDINARY_CLOUD  = 'dvpq5fsef';
const CLOUDINARY_PRESET = 'portfolio_testimonials';
const CLOUDINARY_URL = `https://api.cloudinary.com/v1_1/${CLOUDINARY_CLOUD}/image/upload`;
const TABLE = 'testimonials';

// ───── Supabase Client ─────
const supabase = window.supabase.createClient(SUPABASE_URL, SUPABASE_KEY);

// ───── DOM Cache ─────
const $ = (sel) => document.querySelector(sel);
const $$ = (sel) => document.querySelectorAll(sel);

const DOM = {
  // Views
  loginView:    $('#login-view'),
  dashView:     $('#dashboard-view'),
  formView:     $('#form-view'),
  listView:     $('#list-view'),

  // Login
  loginForm:    $('#login-form'),
  loginEmail:   $('#login-email'),
  loginPass:    $('#login-password'),
  loginBtn:     $('#login-btn'),
  togglePwBtn:  $('.toggle-password'),

  // Sidebar
  sidebar:      $('#sidebar'),
  overlay:      $('#sidebar-overlay'),
  hamburger:    $('#hamburger-btn'),
  navNew:       $('#nav-new'),
  navList:      $('#nav-list'),
  logoutBtn:    $('#logout-btn'),
  userEmail:    $('#user-email'),

  // Form
  form:         $('#testimonial-form'),
  editId:       $('#edit-id'),
  formTitle:    $('#form-title'),
  formCancel:   $('#form-cancel-btn'),
  submitBtn:    $('#submit-btn'),
  resetBtn:     $('#reset-btn'),
  visToggle:    $('#is_visible'),
  visLabel:     $('#visibility-label'),

  // List
  listLoading:  $('#list-loading'),
  listEmpty:    $('#list-empty'),
  cardsGrid:    $('#cards-grid'),
  searchInput:  $('#search-input'),
  addNewBtn:    $('#add-new-btn'),
  emptyAddBtn:  $('#empty-add-btn'),

  // Stats
  statTotal:    $('#stat-total'),
  statVisible:  $('#stat-visible'),
  statHidden:   $('#stat-hidden'),
  statRevenue:  $('#stat-revenue'),

  // Modal
  confirmModal: $('#confirm-modal'),
  confirmTitle: $('#confirm-title'),
  confirmMsg:   $('#confirm-message'),
  confirmOk:    $('#confirm-ok'),
  confirmCancel:$('#confirm-cancel'),

  // Lightbox
  lightbox:     $('#lightbox'),
  lightboxImg:  $('#lightbox-img'),
  lightboxClose:$('#lightbox-close'),

  // Toast
  toastContainer: $('#toast-container'),
};

// ───── State ─────
let allTestimonials = [];
let confirmCallback = null;

// ═══════════════════════════════════════════════════════════
// TOAST NOTIFICATIONS
// ═══════════════════════════════════════════════════════════
function showToast(type, title, message = '') {
  const icons = {
    success: 'fa-check',
    error:   'fa-xmark',
    info:    'fa-info',
  };

  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `
    <div class="toast-icon"><i class="fa-solid ${icons[type]}"></i></div>
    <div class="toast-content">
      <div class="toast-title">${title}</div>
      ${message ? `<div class="toast-message">${message}</div>` : ''}
    </div>
    <button class="toast-close"><i class="fa-solid fa-xmark"></i></button>
  `;

  DOM.toastContainer.appendChild(toast);

  const closeBtn = toast.querySelector('.toast-close');
  const remove = () => {
    toast.classList.add('exiting');
    toast.addEventListener('animationend', () => toast.remove());
  };
  closeBtn.addEventListener('click', remove);
  setTimeout(remove, 4500);
}

// ═══════════════════════════════════════════════════════════
// CONFIRM MODAL
// ═══════════════════════════════════════════════════════════
function showConfirm(title, message, okText = 'Delete') {
  return new Promise((resolve) => {
    DOM.confirmTitle.textContent = title;
    DOM.confirmMsg.textContent = message;
    DOM.confirmOk.textContent = okText;
    DOM.confirmModal.hidden = false;

    const cleanup = () => {
      DOM.confirmModal.hidden = true;
      DOM.confirmOk.removeEventListener('click', onOk);
      DOM.confirmCancel.removeEventListener('click', onCancel);
    };
    const onOk = () => { cleanup(); resolve(true); };
    const onCancel = () => { cleanup(); resolve(false); };

    DOM.confirmOk.addEventListener('click', onOk);
    DOM.confirmCancel.addEventListener('click', onCancel);
  });
}

// ═══════════════════════════════════════════════════════════
// LIGHTBOX
// ═══════════════════════════════════════════════════════════
function openLightbox(src) {
  DOM.lightboxImg.src = src;
  DOM.lightbox.hidden = false;
}
DOM.lightboxClose.addEventListener('click', () => DOM.lightbox.hidden = true);
DOM.lightbox.addEventListener('click', (e) => {
  if (e.target === DOM.lightbox) DOM.lightbox.hidden = true;
});

// ═══════════════════════════════════════════════════════════
// FORMAT HELPERS
// ═══════════════════════════════════════════════════════════
function formatRupiah(num) {
  if (!num && num !== 0) return 'Rp 0';
  return 'Rp ' + Number(num).toLocaleString('id-ID');
}

function formatDate(dateStr) {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric',
  });
}

// ═══════════════════════════════════════════════════════════
// AUTH
// ═══════════════════════════════════════════════════════════
async function checkSession() {
  const { data: { session } } = await supabase.auth.getSession();
  if (session) {
    showDashboard(session.user);
  } else {
    DOM.loginView.hidden = false;
    DOM.dashView.hidden = true;
  }
}

DOM.loginForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  const email = DOM.loginEmail.value.trim();
  const password = DOM.loginPass.value;

  setLoading(DOM.loginBtn, true);

  const { data, error } = await supabase.auth.signInWithPassword({ email, password });

  setLoading(DOM.loginBtn, false);

  if (error) {
    showToast('error', 'Login Failed', error.message);
    return;
  }

  showToast('success', 'Welcome Back!', `Signed in as ${email}`);
  showDashboard(data.user);
});

DOM.logoutBtn.addEventListener('click', async () => {
  await supabase.auth.signOut();
  DOM.loginView.hidden = false;
  DOM.dashView.hidden = true;
  DOM.loginForm.reset();
  showToast('info', 'Signed Out', 'You have been logged out.');
});

// Toggle password visibility
DOM.togglePwBtn.addEventListener('click', () => {
  const input = DOM.loginPass;
  const icon = DOM.togglePwBtn.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.replace('fa-eye', 'fa-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.replace('fa-eye-slash', 'fa-eye');
  }
});

function showDashboard(user) {
  DOM.loginView.hidden = true;
  DOM.dashView.hidden = false;
  DOM.userEmail.textContent = user.email || 'admin';
  navigateTo('list');
}

function setLoading(btn, loading) {
  const text = btn.querySelector('.btn-text');
  const loader = btn.querySelector('.btn-loader');
  if (text) text.hidden = loading;
  if (loader) loader.hidden = !loading;
  btn.disabled = loading;
}

// ═══════════════════════════════════════════════════════════
// SIDEBAR NAVIGATION
// ═══════════════════════════════════════════════════════════
function navigateTo(view) {
  // Update nav active state
  $$('.nav-item[data-view]').forEach(n => n.classList.remove('active'));
  const activeNav = $(`.nav-item[data-view="${view}"]`);
  if (activeNav) activeNav.classList.add('active');

  if (view === 'form') {
    DOM.formView.hidden = false;
    DOM.listView.hidden = true;
  } else {
    DOM.formView.hidden = true;
    DOM.listView.hidden = false;
    loadTestimonials();
  }

  // Close mobile sidebar
  closeSidebar();
}

DOM.navNew.addEventListener('click', () => {
  resetForm();
  navigateTo('form');
});
DOM.navList.addEventListener('click', () => navigateTo('list'));
DOM.addNewBtn.addEventListener('click', () => {
  resetForm();
  navigateTo('form');
});
DOM.emptyAddBtn.addEventListener('click', () => {
  resetForm();
  navigateTo('form');
});

// ───── Mobile Sidebar ─────
DOM.hamburger.addEventListener('click', toggleSidebar);
DOM.overlay.addEventListener('click', closeSidebar);

function toggleSidebar() {
  DOM.sidebar.classList.toggle('open');
  DOM.overlay.classList.toggle('open');
}
function closeSidebar() {
  DOM.sidebar.classList.remove('open');
  DOM.overlay.classList.remove('open');
}

// ═══════════════════════════════════════════════════════════
// CLOUDINARY UPLOAD
// ═══════════════════════════════════════════════════════════
const UPLOAD_FIELDS = ['transfer_proof', 'project_proof', 'chat_screenshot'];

UPLOAD_FIELDS.forEach((field) => {
  const zone = $(`.upload-zone[data-target="${field}"]`);
  const fileInput = $(`#file-${field}`);
  const placeholder = $(`#placeholder-${field}`);
  const preview = $(`#preview-${field}`);
  const progress = $(`#progress-${field}`);
  const hiddenInput = $(`#${field}`);

  // Click to open file picker
  zone.addEventListener('click', (e) => {
    if (e.target.closest('.remove-upload')) return;
    fileInput.click();
  });

  // Drag & drop
  zone.addEventListener('dragover', (e) => {
    e.preventDefault();
    zone.classList.add('dragging');
  });
  zone.addEventListener('dragleave', () => zone.classList.remove('dragging'));
  zone.addEventListener('drop', (e) => {
    e.preventDefault();
    zone.classList.remove('dragging');
    if (e.dataTransfer.files.length) {
      uploadFile(e.dataTransfer.files[0], field);
    }
  });

  // File input change
  fileInput.addEventListener('change', () => {
    if (fileInput.files.length) {
      uploadFile(fileInput.files[0], field);
      fileInput.value = '';
    }
  });

  // Remove upload
  const removeBtn = zone.querySelector('.remove-upload');
  removeBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    hiddenInput.value = '';
    preview.hidden = true;
    placeholder.hidden = false;
  });
});

async function uploadFile(file, field) {
  const placeholder = $(`#placeholder-${field}`);
  const preview = $(`#preview-${field}`);
  const progressEl = $(`#progress-${field}`);
  const progressFill = progressEl.querySelector('.progress-fill');
  const progressText = progressEl.querySelector('.progress-text');
  const hiddenInput = $(`#${field}`);

  // Validate file
  if (!file.type.startsWith('image/')) {
    showToast('error', 'Invalid File', 'Please upload an image file.');
    return;
  }
  if (file.size > 10 * 1024 * 1024) {
    showToast('error', 'File Too Large', 'Maximum file size is 10MB.');
    return;
  }

  // Show progress
  placeholder.hidden = true;
  preview.hidden = true;
  progressEl.hidden = false;
  progressFill.style.width = '0%';
  progressText.textContent = '0%';

  const formData = new FormData();
  formData.append('file', file);
  formData.append('upload_preset', CLOUDINARY_PRESET);

  try {
    const xhr = new XMLHttpRequest();

    xhr.upload.addEventListener('progress', (e) => {
      if (e.lengthComputable) {
        const pct = Math.round((e.loaded / e.total) * 100);
        progressFill.style.width = pct + '%';
        progressText.textContent = pct + '%';
      }
    });

    const result = await new Promise((resolve, reject) => {
      xhr.onload = () => {
        if (xhr.status >= 200 && xhr.status < 300) {
          resolve(JSON.parse(xhr.responseText));
        } else {
          reject(new Error('Upload failed'));
        }
      };
      xhr.onerror = () => reject(new Error('Upload error'));
      xhr.open('POST', CLOUDINARY_URL);
      xhr.send(formData);
    });

    // Success
    hiddenInput.value = result.secure_url;
    const previewImg = preview.querySelector('img');
    previewImg.src = result.secure_url;
    progressEl.hidden = true;
    preview.hidden = false;
    showToast('success', 'Upload Complete', file.name);

  } catch (err) {
    progressEl.hidden = true;
    placeholder.hidden = false;
    showToast('error', 'Upload Failed', err.message);
  }
}

// ═══════════════════════════════════════════════════════════
// VISIBILITY TOGGLE LABEL
// ═══════════════════════════════════════════════════════════
DOM.visToggle.addEventListener('change', () => {
  DOM.visLabel.textContent = DOM.visToggle.checked ? 'Visible' : 'Hidden';
});

// ═══════════════════════════════════════════════════════════
// FORM SUBMIT (CREATE / UPDATE)
// ═══════════════════════════════════════════════════════════
DOM.form.addEventListener('submit', async (e) => {
  e.preventDefault();

  const isEdit = !!DOM.editId.value;

  const payload = {
    customer_name:    $('#customer_name').value.trim(),
    project_title:    $('#project_title').value.trim(),
    category:         $('#category').value,
    price:            parseInt($('#price').value) || 0,
    transfer_proof:   $('#transfer_proof').value || null,
    project_proof:    $('#project_proof').value || null,
    chat_screenshot:  $('#chat_screenshot').value || null,
    project_link:     $('#project_link').value.trim() || null,
    demo_video_url:   $('#demo_video_url').value.trim() || null,
    proof_type:       $('#proof_type').value,
    transaction_date: $('#transaction_date').value || null,
    is_visible:       DOM.visToggle.checked,
  };

  setLoading(DOM.submitBtn, true);

  try {
    let error;

    if (isEdit) {
      ({ error } = await supabase
        .from(TABLE)
        .update(payload)
        .eq('id', DOM.editId.value));
    } else {
      ({ error } = await supabase
        .from(TABLE)
        .insert([payload]));
    }

    if (error) throw error;

    showToast('success',
      isEdit ? 'Testimonial Updated' : 'Testimonial Created',
      payload.customer_name
    );

    resetForm();
    navigateTo('list');

  } catch (err) {
    showToast('error', 'Save Failed', err.message);
  } finally {
    setLoading(DOM.submitBtn, false);
  }
});

// Reset & Cancel
DOM.resetBtn.addEventListener('click', resetForm);
DOM.formCancel.addEventListener('click', () => {
  resetForm();
  navigateTo('list');
});

function resetForm() {
  DOM.form.reset();
  DOM.editId.value = '';
  DOM.formTitle.innerHTML = '<i class="fa-solid fa-plus-circle"></i> New Testimonial';
  DOM.formCancel.hidden = true;
  DOM.visLabel.textContent = 'Visible';
  DOM.visToggle.checked = true;

  // Clear upload previews
  UPLOAD_FIELDS.forEach((field) => {
    $(`#${field}`).value = '';
    $(`#preview-${field}`).hidden = true;
    $(`#placeholder-${field}`).hidden = false;
    $(`#progress-${field}`).hidden = true;
  });
}

// ═══════════════════════════════════════════════════════════
// LOAD & RENDER TESTIMONIALS
// ═══════════════════════════════════════════════════════════
async function loadTestimonials() {
  DOM.listLoading.hidden = false;
  DOM.listEmpty.hidden = true;
  DOM.cardsGrid.innerHTML = '';

  try {
    const { data, error } = await supabase
      .from(TABLE)
      .select('*')
      .order('created_at', { ascending: false });

    if (error) throw error;

    allTestimonials = data || [];
    updateStats();
    renderCards(allTestimonials);

  } catch (err) {
    showToast('error', 'Load Failed', err.message);
  } finally {
    DOM.listLoading.hidden = true;
  }
}

function updateStats() {
  const total   = allTestimonials.length;
  const visible = allTestimonials.filter(t => t.is_visible).length;
  const hidden  = total - visible;
  const revenue = allTestimonials.reduce((sum, t) => sum + (t.price || 0), 0);

  DOM.statTotal.textContent   = total;
  DOM.statVisible.textContent = visible;
  DOM.statHidden.textContent  = hidden;
  DOM.statRevenue.textContent = formatRupiah(revenue);
}

function renderCards(testimonials) {
  DOM.cardsGrid.innerHTML = '';

  if (!testimonials.length) {
    DOM.listEmpty.hidden = false;
    return;
  }

  DOM.listEmpty.hidden = true;

  testimonials.forEach((t) => {
    const card = document.createElement('div');
    card.className = `testimonial-card${t.is_visible ? '' : ' hidden-card'}`;
    card.dataset.id = t.id;

    // Collect thumbnail images
    const thumbs = [t.transfer_proof, t.project_proof, t.chat_screenshot].filter(Boolean);

    card.innerHTML = `
      <span class="card-visibility-badge ${t.is_visible ? 'visible' : 'hidden-badge'}">
        <i class="fa-solid ${t.is_visible ? 'fa-eye' : 'fa-eye-slash'}"></i>
        ${t.is_visible ? 'Visible' : 'Hidden'}
      </span>

      ${thumbs.length ? `
        <div class="card-thumbs">
          ${thumbs.map(url => `<img src="${url}" alt="Proof" loading="lazy" onclick="openLightbox('${url}')">`).join('')}
        </div>
      ` : `
        <div class="card-thumbs-empty">
          <i class="fa-solid fa-image"></i>&nbsp; No images
        </div>
      `}

      <div class="card-body">
        <div class="card-top-row">
          <span class="card-customer">${escapeHtml(t.customer_name)}</span>
          <span class="card-category">${escapeHtml(t.category || '—')}</span>
        </div>
        <div class="card-project">
          <i class="fa-solid fa-briefcase"></i>
          ${escapeHtml(t.project_title)}
        </div>
        <div class="card-meta">
          <span class="card-price">${formatRupiah(t.price)}</span>
          <span class="card-date">
            <i class="fa-solid fa-calendar"></i>
            ${formatDate(t.transaction_date)}
          </span>
        </div>
      </div>

      <div class="card-actions">
        <button class="btn-icon toggle-vis" title="Toggle visibility" data-id="${t.id}" data-visible="${t.is_visible}">
          <i class="fa-solid ${t.is_visible ? 'fa-eye-slash' : 'fa-eye'}"></i>
        </button>
        <button class="btn-icon edit" title="Edit" data-id="${t.id}">
          <i class="fa-solid fa-pen-to-square"></i>
        </button>
        <button class="btn-icon delete" title="Delete" data-id="${t.id}">
          <i class="fa-solid fa-trash-can"></i>
        </button>
      </div>
    `;

    DOM.cardsGrid.appendChild(card);
  });

  // Attach card action listeners
  attachCardListeners();
}

function attachCardListeners() {
  // Toggle visibility
  DOM.cardsGrid.querySelectorAll('.toggle-vis').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.dataset.id;
      const currentlyVisible = btn.dataset.visible === 'true';
      const newVal = !currentlyVisible;

      const { error } = await supabase
        .from(TABLE)
        .update({ is_visible: newVal })
        .eq('id', id);

      if (error) {
        showToast('error', 'Update Failed', error.message);
        return;
      }

      showToast('success', newVal ? 'Now Visible' : 'Now Hidden');
      loadTestimonials();
    });
  });

  // Edit
  DOM.cardsGrid.querySelectorAll('.edit').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.id;
      const t = allTestimonials.find(x => String(x.id) === String(id));
      if (t) populateForm(t);
    });
  });

  // Delete
  DOM.cardsGrid.querySelectorAll('.delete').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.dataset.id;
      const t = allTestimonials.find(x => String(x.id) === String(id));
      const name = t ? t.customer_name : 'this testimonial';

      const ok = await showConfirm(
        'Delete Testimonial?',
        `Are you sure you want to delete "${name}"? This action cannot be undone.`
      );

      if (!ok) return;

      const { error } = await supabase
        .from(TABLE)
        .delete()
        .eq('id', id);

      if (error) {
        showToast('error', 'Delete Failed', error.message);
        return;
      }

      showToast('success', 'Deleted', `"${name}" has been removed.`);
      loadTestimonials();
    });
  });
}

// ═══════════════════════════════════════════════════════════
// POPULATE FORM FOR EDIT
// ═══════════════════════════════════════════════════════════
function populateForm(t) {
  navigateTo('form');

  DOM.editId.value = t.id;
  DOM.formTitle.innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Edit Testimonial';
  DOM.formCancel.hidden = false;

  $('#customer_name').value   = t.customer_name || '';
  $('#project_title').value   = t.project_title || '';
  $('#category').value        = t.category || '';
  $('#price').value           = t.price || '';
  $('#project_link').value    = t.project_link || '';
  $('#demo_video_url').value  = t.demo_video_url || '';
  $('#proof_type').value      = t.proof_type || 'image';
  $('#transaction_date').value= t.transaction_date || '';
  DOM.visToggle.checked      = t.is_visible !== false;
  DOM.visLabel.textContent   = DOM.visToggle.checked ? 'Visible' : 'Hidden';

  // Restore upload previews
  UPLOAD_FIELDS.forEach((field) => {
    const url = t[field];
    const hiddenInput = $(`#${field}`);
    const placeholder = $(`#placeholder-${field}`);
    const preview = $(`#preview-${field}`);

    hiddenInput.value = url || '';

    if (url) {
      preview.querySelector('img').src = url;
      preview.hidden = false;
      placeholder.hidden = true;
    } else {
      preview.hidden = true;
      placeholder.hidden = false;
    }
  });
}

// ═══════════════════════════════════════════════════════════
// SEARCH / FILTER
// ═══════════════════════════════════════════════════════════
DOM.searchInput.addEventListener('input', () => {
  const q = DOM.searchInput.value.toLowerCase().trim();
  if (!q) {
    renderCards(allTestimonials);
    return;
  }
  const filtered = allTestimonials.filter(t =>
    (t.customer_name || '').toLowerCase().includes(q) ||
    (t.project_title || '').toLowerCase().includes(q) ||
    (t.category || '').toLowerCase().includes(q)
  );
  renderCards(filtered);
});

// ═══════════════════════════════════════════════════════════
// UTILITIES
// ═══════════════════════════════════════════════════════════
function escapeHtml(str) {
  if (!str) return '';
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

// ═══════════════════════════════════════════════════════════
// KEYBOARD SHORTCUTS
// ═══════════════════════════════════════════════════════════
document.addEventListener('keydown', (e) => {
  // Escape to close modals/lightbox
  if (e.key === 'Escape') {
    if (!DOM.lightbox.hidden) DOM.lightbox.hidden = true;
    if (!DOM.confirmModal.hidden) {
      DOM.confirmModal.hidden = true;
    }
    closeSidebar();
  }
});

// ═══════════════════════════════════════════════════════════
// INIT
// ═══════════════════════════════════════════════════════════
checkSession();
