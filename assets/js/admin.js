// Tab Switching
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const tabId = this.getAttribute('data-tab');

        // Remove active class from all tabs and contents
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

        // Add active class to clicked tab and content
        this.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    });
});

// Modal Functions
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
    // Reset form
    const modal = document.getElementById(modalId);
    const form = modal.querySelector('form');
    if (form) form.reset();
}

function openBannerModal(position) {
    document.getElementById('banner_position').value = position;
    openModal('bannerModal');
}

// File Upload with Drag & Drop
function initFileUpload(uploadAreaId, inputName) {
    const uploadArea = document.getElementById(uploadAreaId);
    if (!uploadArea) return;

    const fileInput = uploadArea.querySelector('input[type="file"]');

    uploadArea.addEventListener('click', () => fileInput.click());

    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('drag-over');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('drag-over');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file) handleFileUpload(file, uploadArea, inputName);
    });

    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) handleFileUpload(file, uploadArea, inputName);
    });
}

function handleFileUpload(file, uploadArea, inputName) {
    const formData = new FormData();
    formData.append('file', file);

    fetch('api/upload.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const urlInput = uploadArea.parentElement.previousElementSibling.querySelector('input[type="text"]');
            if (urlInput) urlInput.value = data.url;

            uploadArea.querySelector('.upload-text').textContent = 'Yüklendi: ' + file.name;
            uploadArea.style.borderColor = '#10b981';
        } else {
            alert('Upload hatası: ' + data.error);
        }
    })
    .catch(err => {
        alert('Upload hatası: ' + err.message);
    });
}

// Initialize all file uploads
initFileUpload('logoUpload', 'site_logo');
initFileUpload('carouselLogoUpload', 'logo_url');
initFileUpload('premiumLogoUpload', 'logo_url');
initFileUpload('bannerImageUpload', 'image_url');

// Settings Form
document.getElementById('settingsForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    try {
        const response = await fetch('api/settings.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            alert('Ayarlar kaydedildi!');
            location.reload();
        } else {
            alert('Hata: ' + data.error);
        }
    } catch (err) {
        alert('Hata: ' + err.message);
    }
});

// ==================== SOCIAL MEDIA FUNCTIONS ====================
document.getElementById('socialForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const id = formData.get('id');
    formData.append('action', id ? 'update' : 'add');

    try {
        const response = await fetch('api/social_new.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            alert('Kaydedildi!');
            location.reload();
        } else {
            alert('Hata: ' + data.error);
        }
    } catch (err) {
        alert('Hata: ' + err.message);
    }
});

function editSocial(id) {
    fetch(`api/social_new.php?action=get&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data) {
                const item = data.data;
                const form = document.getElementById('socialForm');
                form.querySelector('[name="id"]').value = item.id;
                form.querySelector('[name="name"]').value = item.name;
                form.querySelector('[name="icon_url"]').value = item.icon_url;
                form.querySelector('[name="link"]').value = item.link;
                form.querySelector('[name="bg_color"]').value = item.bg_color;
                form.querySelector('[name="is_active"]').value = item.is_active;
                openModal('socialModal');
            }
        });
}

function deleteSocial(id) {
    if (!confirm('Silmek istediğinize emin misiniz?')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch('api/social_new.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Silindi!');
            location.reload();
        } else {
            alert('Hata: ' + data.error);
        }
    });
}

// ==================== SITES FUNCTIONS (Premium Sites) ====================
document.getElementById('premiumForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const id = formData.get('id');
    formData.append('action', id ? 'update' : 'add');

    try {
        const response = await fetch('api/sites.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            alert('Kaydedildi!');
            location.reload();
        } else {
            alert('Hata: ' + data.error);
        }
    } catch (err) {
        alert('Hata: ' + err.message);
    }
});

function editPremium(id) {
    fetch(`api/sites.php?action=get&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data) {
                const item = data.data;
                const form = document.getElementById('premiumForm');
                form.querySelector('[name="id"]').value = item.id;
                form.querySelector('[name="site_name"]').value = item.site_name;
                form.querySelector('[name="logo_url"]').value = item.logo_path || '';
                form.querySelector('[name="description"]').value = item.description || '';
                form.querySelector('[name="bonus_text"]').value = item.bonus_text || '';
                form.querySelector('[name="promo_code"]').value = item.promo_code || '';
                form.querySelector('[name="site_link"]').value = item.site_url;
                form.querySelector('[name="is_active"]').value = item.is_active;
                openModal('premiumModal');
            }
        });
}

function deletePremium(id) {
    if (!confirm('Silmek istediğinize emin misiniz?')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch('api/sites.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Silindi!');
            location.reload();
        } else {
            alert('Hata: ' + data.error);
        }
    });
}

// ==================== CAROUSEL FUNCTIONS ====================
document.getElementById('carouselForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const id = formData.get('id');
    formData.append('action', id ? 'update' : 'add');

    try {
        const response = await fetch('api/carousel_new.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            alert('Kaydedildi!');
            location.reload();
        } else {
            alert('Hata: ' + data.error);
        }
    } catch (err) {
        alert('Hata: ' + err.message);
    }
});

function editCarousel(id) {
    fetch(`api/carousel_new.php?action=get&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data) {
                const item = data.data;
                const form = document.getElementById('carouselForm');
                form.querySelector('[name="id"]').value = item.id;
                form.querySelector('[name="site_id"]').value = item.site_id;
                form.querySelector('[name="is_active"]').value = item.is_active;
                openModal('carouselModal');
            }
        });
}

function deleteCarousel(id) {
    if (!confirm('Silmek istediğinize emin misiniz?')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch('api/carousel_new.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Silindi!');
            location.reload();
        } else {
            alert('Hata: ' + data.error);
        }
    });
}

// ==================== BANNER FUNCTIONS ====================
document.getElementById('bannerForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const id = formData.get('id');
    formData.append('action', id ? 'update' : 'add');

    try {
        const response = await fetch('api/banners_new.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            alert('Kaydedildi!');
            location.reload();
        } else {
            alert('Hata: ' + data.error);
        }
    } catch (err) {
        alert('Hata: ' + err.message);
    }
});

function editBanner(id) {
    fetch(`api/banners_new.php?action=get&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data) {
                const item = data.data;
                const form = document.getElementById('bannerForm');
                form.querySelector('[name="id"]').value = item.id;
                form.querySelector('[name="position"]').value = item.position;
                form.querySelector('[name="site_id"]').value = item.site_id;
                form.querySelector('[name="banner_image"]').value = item.banner_image || '';
                form.querySelector('[name="is_active"]').value = item.is_active;
                openModal('bannerModal');
            }
        });
}

function deleteBanner(id) {
    if (!confirm('Silmek istediğinize emin misiniz?')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch('api/banners_new.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Silindi!');
            location.reload();
        } else {
            alert('Hata: ' + data.error);
        }
    });
}

// ==================== DRAG & DROP REORDERING ====================
function initDragAndDrop(tableId, apiEndpoint) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const tbody = table.querySelector('tbody');
    let draggedRow = null;

    tbody.querySelectorAll('tr').forEach(row => {
        row.setAttribute('draggable', true);

        row.addEventListener('dragstart', function() {
            draggedRow = this;
            this.classList.add('dragging');
        });

        row.addEventListener('dragend', function() {
            this.classList.remove('dragging');
        });

        row.addEventListener('dragover', function(e) {
            e.preventDefault();
            const afterElement = getDragAfterElement(tbody, e.clientY);
            if (afterElement == null) {
                tbody.appendChild(draggedRow);
            } else {
                tbody.insertBefore(draggedRow, afterElement);
            }
        });
    });

    tbody.addEventListener('drop', function() {
        updateOrder(tbody, apiEndpoint);
    });
}

function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('tr:not(.dragging)')];

    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;

        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

function updateOrder(tbody, apiEndpoint) {
    const rows = tbody.querySelectorAll('tr');
    const orders = [];

    rows.forEach((row, index) => {
        orders.push({
            id: parseInt(row.getAttribute('data-id')),
            position: index
        });
    });

    const formData = new FormData();
    formData.append('action', 'reorder');
    formData.append('orders', JSON.stringify(orders));

    fetch(apiEndpoint, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert('Sıralama güncellenemedi!');
            location.reload();
        }
    });
}

// Initialize drag and drop for all tables
initDragAndDrop('socialTable', 'api/social_new.php');
initDragAndDrop('carouselTable', 'api/carousel_new.php');
initDragAndDrop('premiumTable', 'api/sites.php');
initDragAndDrop('leftBannersTable', 'api/banners_new.php');
initDragAndDrop('rightBannersTable', 'api/banners_new.php');
