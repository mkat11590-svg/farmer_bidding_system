/* ============================================
   MY BIDS / MY LISTINGS PAGE - Frontend Logic
   ============================================ */

let userBids = [];
let currentEditingBid = null;

// ============================================
// INITIALIZATION
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    loadUserBids();
    setupEventListeners();
    console.log('My Bids page initialized');
});

// ============================================
// DATA LOADING
// ============================================
function loadUserBids() {
    // Ensure this path points to your actual API
    fetch('../php/api/get_my_bids.php') 
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            userBids = Array.isArray(data) ? data : [];
            renderBidsGrid();
        })
        .catch(err => {
            console.error('Failed to load bids:', err);
        });
}
// ============================================
// RENDERING
// ============================================
function renderBidsGrid() {
    const grid = document.getElementById('bidsGrid');
    if (!grid) return;

    // "Create New" Card
    grid.innerHTML = `
        <div class="plus-card" onclick="openCreateBidModal()">
            <i class="fas fa-plus-circle plus-icon"></i>
            <p class="plus-text">Create New Bid</p>
        </div>`;

    const tutorial = document.getElementById('tutorialSection');
    
    // Handle Empty State
    if (userBids.length === 0) {
        if (tutorial) tutorial.style.display = 'block';
        return;
    }

    if (tutorial) tutorial.style.display = 'none';

    // Render Cards
    userBids.forEach(bid => {
        grid.insertAdjacentHTML('beforeend', createBidCard(bid));
    });
}

function createBidCard(bid) {
    const isActive = new Date(bid.end_date) > new Date();

    const statusBadge = isActive
        ? `<span class="bid-status-badge badge-active"><i class="fas fa-circle"></i> Active</span>`
        : `<span class="bid-status-badge badge-ended"><i class="fas fa-times-circle"></i> Ended</span>`;

    const visibilityBadge = bid.visibility === 'public'
        ? `<span class="bid-status-badge badge-public"><i class="fas fa-globe"></i> Public</span>`
        : `<span class="bid-status-badge badge-private"><i class="fas fa-lock"></i> Private</span>`;

    // KEY INTEGRATION: The Evaluate button calls evaluateBid(id)
    return `
    <div class="bid-card ${bid.visibility === 'private' ? 'private' : ''}" data-bid-id="${bid.id}">
        ${statusBadge}
        ${visibilityBadge}
        
        <img src="${bid.image || '/images/placeholder-item.jpg'}" 
             alt="${bid.item_name}" 
             class="bid-card-image"
             onerror="this.src='/images/placeholder-item.jpg'">

        <div class="bid-card-content">
            <h4 class="bid-card-title">${bid.item_name}</h4>
            
            <div class="bid-price-row">
                <span class="bid-current-price">$${Number(bid.current_bid || bid.starting_bid).toLocaleString()}</span>
                <span class="bid-starting-price">Start: $${Number(bid.starting_bid).toLocaleString()}</span>
            </div>
            
            <div class="bid-info-item"><i class="fas fa-gavel"></i><span>${bid.total_bids || 0} bids</span></div>
            <div class="bid-info-item"><i class="far fa-clock"></i><span>${calculateTimeLeft(bid.end_date)}</span></div>
            
            <div class="bid-actions">
                <button class="btn-action btn-view" onclick="viewBidDetails(${bid.id})">
                    <i class="fas fa-eye"></i> View
                </button>
                <button class="btn-action btn-evaluate" onclick="evaluateBid(${bid.id})" style="background-color: #6c757d; color: white;">
                    <i class="fas fa-chart-line"></i> Evaluate
                </button>
                <button class="btn-action btn-edit" onclick="editBid(${bid.id})">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn-action btn-delete" onclick="deleteBid(${bid.id})">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>`;
}

function calculateTimeLeft(endDateStr) {
    const diff = new Date(endDateStr) - new Date();
    if (diff <= 0) return 'Ended';

    const days = Math.floor(diff / 86400000);
    const hours = Math.floor((diff % 86400000) / 3600000);

    return days > 0 ? `${days}d ${hours}h left` : `${hours}h left`;
}

// ============================================
// MODAL & FORM
// ============================================
function openCreateBidModal() {
    currentEditingBid = null;
    resetForm();
    document.getElementById('formModalTitle').innerHTML = '<i class="fas fa-plus-circle"></i> Create New Auction';
    document.getElementById('bidFormModal').classList.add('show');

    // Hide navbar
    const headerMainNav = document.querySelector('.header-main-nav');
    if (headerMainNav) {
        headerMainNav.style.display = 'none';
    }

    // Make header not sticky
    const headerMain = document.querySelector('.header-main');
    if (headerMain) {
        headerMain.classList.add('not-sticky');
    }

    // Prevent body scroll
    document.body.classList.add('bid-modal-open');
}

function resetForm() {
    const form = document.getElementById('bidForm');
    form.reset();
    clearFormErrors();

    document.getElementById('bidId').value = '';
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('uploadPlaceholder').style.display = 'block';
    document.getElementById('imageUploadArea').classList.remove('has-image');
}

function closeBidFormModal() {
    document.getElementById('bidFormModal').classList.remove('show');
    currentEditingBid = null;
    clearFormErrors();

    // Show navbar again
    const headerMainNav = document.querySelector('.header-main-nav');
    if (headerMainNav) {
        headerMainNav.style.display = '';
    }

    // Revert header to sticky
    const headerMain = document.querySelector('.header-main');
    if (headerMain) {
        headerMain.classList.remove('not-sticky');
    }

    // Re-enable body scroll
    document.body.classList.remove('bid-modal-open');
}

// ============================================
// IMAGE PREVIEW
// ============================================
function previewImage(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
        alert('Image must be smaller than 5MB');
        event.target.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = e => {
        const preview = document.getElementById('imagePreview');
        preview.src = e.target.result;
        preview.style.display = 'block';
        document.getElementById('uploadPlaceholder').style.display = 'none';
        document.getElementById('imageUploadArea').classList.add('has-image');
        clearErrorForField('imageUploadArea');
    };
    reader.readAsDataURL(file);
}

// ============================================
// FORM VALIDATION
// ============================================
function validateForm(formData) {
    let isValid = true;
    clearFormErrors();

    const checks = [
        { field: 'item_name', cond: v => v.trim().length >= 3, msg: 'Item name must be at least 3 characters.' },
        { field: 'category', cond: v => !!v, msg: 'Please select a category.' },
        { field: 'starting_bid', cond: v => Number(v) > 0, msg: 'Starting bid must be positive.' },
        { field: 'min_increment', cond: v => Number(v) > 0, msg: 'Increment must be positive.' },
        { field: 'location', cond: v => v.trim(), msg: 'Location is required.' },
        { field: 'condition', cond: v => !!v, msg: 'Please select condition.' },
        { field: 'visibility', cond: v => !!v, msg: 'Please select visibility.' },
        {
            field: 'year', cond: v => {
                const y = Number(v); const cy = new Date().getFullYear();
                return !isNaN(y) && y >= 1900 && y <= cy + 1;
            }, msg: `Year must be between 1900 and ${new Date().getFullYear() + 1}`
        },
        { field: 'quantity', cond: v => Number(v) >= 1, msg: 'Quantity must be at least 1.' },
        { field: 'description', cond: v => v.trim().length >= 10, msg: 'Description must be at least 10 characters.' }
    ];

    checks.forEach(({ field, cond, msg }) => {
        const value = formData.get(field);
        if (!cond(value)) {
            showError(field, msg);
            isValid = false;
        }
    });

    // Image is required for new bid
    if (!currentEditingBid && !document.getElementById('imageUploadArea').classList.contains('has-image')) {
        showError('imageUploadArea', 'Please upload an image.');
        isValid = false;
    }

    return isValid;
}

function showError(fieldId, message) {
    const el = document.getElementById(fieldId);
    if (!el) return;

    el.style.borderColor = 'var(--danger-color)';

    const error = document.createElement('small');
    error.className = 'error-msg';
    error.textContent = message;
    el.parentNode.appendChild(error);
}

function clearErrorForField(fieldId) {
    const el = document.getElementById(fieldId);
    if (!el) return;
    el.style.borderColor = '#ddd';
    const err = el.parentNode.querySelector('.error-msg');
    if (err) err.remove();
}

function clearFormErrors() {
    document.querySelectorAll('.form-input, .form-select, .form-textarea, .image-upload-area')
        .forEach(el => el.style.borderColor = '#ddd');
    document.querySelectorAll('.error-msg').forEach(el => el.remove());
}

// ============================================
// FORM SUBMISSION (AJAX)
// ============================================
function setupEventListeners() {
    const form = document.getElementById('bidForm');
    if (!form) return;

    // Live error clearing
    form.querySelectorAll('input, select, textarea')
        .forEach(input => input.addEventListener('input', () => clearErrorForField(input.id)));

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        console.log("Form submit event triggered!");

        const formData = new FormData(form);
        console.log("FormData contents:");
        for (let [key, value] of formData.entries()) {
            console.log(`  ${key}:`, value);
        }

        const isValid = validateForm(formData);
        console.log("Validation result:", isValid);

        if (!isValid) {
            console.log("Validation failed → stopping here");
            document.querySelector('.modal-content')?.scrollTo(0, 0);
            return;
        }

        console.log("Validation passed → attempting to send fetch...");

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        const endpoint = currentEditingBid
            ? '../php/bids/update_bid.php'
            : '../php/bids/create_bid.php';

        console.log("Sending to endpoint:", endpoint);

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData
            });

            console.log("Fetch response status:", response.status);

            if (!response.ok) {
                console.log("Response not OK →", response.statusText);
            }

            const result = await response.json();
            console.log("Server response:", result);

            if (result.success) {
                alert(result.message || (currentEditingBid ? 'Bid updated!' : 'Bid created!'));
                closeBidFormModal();
                loadUserBids();
            } else {
                alert('Error: ' + (result.message || 'Something went wrong'));
            }
        } catch (err) {
            console.error("Fetch / network error:", err);
            alert('Failed to save bid. Please check your connection.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

// ============================================
// ACTIONS
// ============================================
function editBid(id) {
    const bid = userBids.find(b => b.id === id);
    if (!bid) return;

    currentEditingBid = bid;

    resetForm();

    // Populate fields
    Object.entries({
        bidId: bid.id,
        itemName: bid.item_name,
        category: bid.category,
        startingBid: bid.starting_bid,
        minIncrement: bid.min_increment,
        duration: bid.duration || 3,
        location: bid.location,
        condition: bid.condition,
        year: bid.year,
        hours: bid.hours || '',
        color: bid.color || '',
        size: bid.size || '',
        quantity: bid.quantity,
        description: bid.description
    }).forEach(([id, value]) => {
        const el = document.getElementById(id);
        if (el) el.value = value;
    });

    document.getElementById(bid.visibility === 'public' ? 'visibilityPublic' : 'visibilityPrivate').checked = true;

    if (bid.image) {
        const preview = document.getElementById('imagePreview');
        preview.src = bid.image;
        preview.style.display = 'block';
        document.getElementById('uploadPlaceholder').style.display = 'none';
        document.getElementById('imageUploadArea').classList.add('has-image');
    }

    document.getElementById('formModalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Auction';
    document.getElementById('bidFormModal').classList.add('show');
    
    // Hide navbar
    const headerMainNav = document.querySelector('.header-main-nav');
    if (headerMainNav) {
        headerMainNav.style.display = 'none';
    }

    // Make header not sticky
    const headerMain = document.querySelector('.header-main');
    if (headerMain) {
        headerMain.classList.add('not-sticky');
    }

    // Prevent body scroll
    document.body.classList.add('bid-modal-open');
}

function deleteBid(id) {
    if (!confirm('Delete this auction permanently?')) return;

    fetch('../php/bids/delete_bid.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    })
        .then(r => r.json())
        .then(result => {
            if (result.success) {
                loadUserBids();
            } else {
                alert('Delete failed: ' + (result.message || 'Unknown error'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Failed to delete bid.');
        });
}

/**
 * UPDATED FUNCTION: Evaluate Bid
 * Redirects to manage_bid.php with the specific bid ID
 */
function evaluateBid(id) {
    if(!id) {
        alert("Error: Bid ID not found.");
        return;
    }
    // Adjust this path if your folder structure is different
    window.location.href = `../php/farmer/manage_bids.php?id=${id}`;
}

// Global variable to handle the interval
let viewModalTimerInterval = null;

// ============================================
// VIEW BID DETAILS MODAL
// ============================================

function viewBidDetails(id) {
    const bid = userBids.find(b => b.id === id);
    if (!bid) return;

    const isActive = new Date(bid.end_date) > new Date();
    const timeLeft = calculateTimeLeft(bid.end_date);

    // Create modal HTML
    const modalHTML = `
        <div id="viewBidModal" class="modal show">
            <div class="modal-content view-modal-content">
                <button class="close-modal" onclick="closeViewBidModal()">&times;</button>
                
                <div class="view-modal-header">
                    <h2 class="view-modal-title">
                        <i class="fas fa-info-circle"></i> Auction Details
                    </h2>
                    <div class="view-modal-badges">
                        ${isActive 
                            ? '<span class="bid-status-badge badge-active"><i class="fas fa-circle"></i> Active</span>'
                            : '<span class="bid-status-badge badge-ended"><i class="fas fa-times-circle"></i> Ended</span>'
                        }
                        ${bid.visibility === 'public'
                            ? '<span class="bid-status-badge badge-public"><i class="fas fa-globe"></i> Public</span>'
                            : '<span class="bid-status-badge badge-private"><i class="fas fa-lock"></i> Private</span>'
                        }
                    </div>
                </div>

                <div class="view-modal-body">
                    <div class="view-image-section">
                        <img src="${bid.image || '/images/placeholder-item.jpg'}" 
                             alt="${bid.item_name}"
                             class="view-bid-image"
                             onerror="this.src='/images/placeholder-item.jpg'">
                    </div>

                    <div class="view-info-section">
                        <h3 class="view-item-title">${bid.item_name}</h3>
                        
                        <div class="view-price-box">
                            <div class="view-price-item">
                                <span class="view-price-label">Current Price</span>
                                <span class="view-price-value primary">$${Number(bid.current_bid || bid.starting_bid).toLocaleString()}</span>
                            </div>
                            <div class="view-price-item">
                                <span class="view-price-label">Starting Price</span>
                                <span class="view-price-value">$${Number(bid.starting_bid).toLocaleString()}</span>
                            </div>
                            <div class="view-price-item">
                                <span class="view-price-label">Min Increment</span>
                                <span class="view-price-value">$${Number(bid.min_increment).toLocaleString()}</span>
                            </div>
                        </div>

                        <div class="view-stats-grid">
                            <div class="view-stat-card">
                                <i class="fas fa-gavel stat-icon"></i>
                                <div class="stat-content">
                                    <span class="stat-value">${bid.total_bids || 0}</span>
                                    <span class="stat-label">Total Bids</span>
                                </div>
                            </div>
                            <div class="view-stat-card">
                                <i class="far fa-clock stat-icon"></i>
                                <div class="stat-content">
                                    <span class="stat-value">${timeLeft}</span>
                                    <span class="stat-label">Time Left</span>
                                </div>
                            </div>
                        </div>

                        <div class="view-details-grid">
                            <div class="detail-row">
                                <span class="detail-label"><i class="fas fa-tag"></i> Category</span>
                                <span class="detail-value">${bid.category}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label"><i class="fas fa-map-marker-alt"></i> Location</span>
                                <span class="detail-value">${bid.location}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label"><i class="fas fa-check-circle"></i> Condition</span>
                                <span class="detail-value">${bid.condition}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label"><i class="fas fa-calendar-alt"></i> Year</span>
                                <span class="detail-value">${bid.year}</span>
                            </div>
                            ${bid.size ? `
                            <div class="detail-row">
                                <span class="detail-label"><i class="fas fa-expand"></i> Size</span>
                                <span class="detail-value">${bid.size}</span>
                            </div>` : ''}
                            ${bid.color ? `
                            <div class="detail-row">
                                <span class="detail-label"><i class="fas fa-palette"></i> Color</span>
                                <span class="detail-value">${bid.color}</span>
                            </div>` : ''}
                            ${bid.hours ? `
                            <div class="detail-row">
                                <span class="detail-label"><i class="fas fa-tachometer-alt"></i> Operating Hours</span>
                                <span class="detail-value">${Number(bid.hours).toLocaleString()} hrs</span>
                            </div>` : ''}
                            <div class="detail-row">
                                <span class="detail-label"><i class="fas fa-boxes"></i> Quantity</span>
                                <span class="detail-value">${bid.quantity}</span>
                            </div>
                        </div>

                        <div class="view-description">
                            <h4 class="description-title"><i class="fas fa-align-left"></i> Description</h4>
                            <p class="description-text">${bid.description}</p>
                        </div>

                        <div class="view-timeline">
                            <div class="timeline-item">
                                <i class="fas fa-clock"></i>
                                <div>
                                    <span class="timeline-label">Created</span>
                                    <span class="timeline-value">${formatDate(bid.created_at)}</span>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <i class="fas fa-flag-checkered"></i>
                                <div>
                                    <span class="timeline-label">Ends</span>
                                    <span class="timeline-value">${formatDate(bid.end_date)}</span>
                                </div>
                            </div>
                        </div>

                        <div class="view-modal-actions">
                             <button class="btn-view-action btn-evaluate-view" onclick="evaluateBid(${bid.id})" style="background-color: #6c757d; color: white; margin-right: 10px;">
                                <i class="fas fa-chart-line"></i> Evaluate
                            </button>
                            <button class="btn-view-action btn-edit-view" onclick="closeViewBidModal(); editBid(${bid.id});">
                                <i class="fas fa-edit"></i> Edit Auction
                            </button>
                            <button class="btn-view-action btn-delete-view" onclick="closeViewBidModal(); deleteBid(${bid.id});">
                                <i class="fas fa-trash"></i> Delete Auction
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Insert modal into body
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Hide navbar
    const headerMainNav = document.querySelector('.header-main-nav');
    if (headerMainNav) {
        headerMainNav.style.display = 'none';
    }

    // Make header not sticky
    const headerMain = document.querySelector('.header-main');
    if (headerMain) {
        headerMain.classList.add('not-sticky');
    }

    // Prevent body scroll
    document.body.classList.add('bid-modal-open');
}

function closeViewBidModal() {
    const modal = document.getElementById('viewBidModal');
    if (modal) {
        modal.remove();
    }

    // Show navbar again
    const headerMainNav = document.querySelector('.header-main-nav');
    if (headerMainNav) {
        headerMainNav.style.display = '';
    }

    // Revert header to sticky
    const headerMain = document.querySelector('.header-main');
    if (headerMain) {
        headerMain.classList.remove('not-sticky');
    }

    // Re-enable body scroll
    document.body.classList.remove('bid-modal-open');
}

// Helper function to format dates
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit' 
    };
    return date.toLocaleDateString('en-US', options);
}