<?php
// 1. DATABASE CONNECTION
include 'php/config/db_connect.php';

// Prepare array for JavaScript
$auctionItems = [];

// FETCH DATA
$sql = "SELECT l.*, u.full_name 
        FROM bids l 
        LEFT JOIN users u ON l.farmer_id = u.user_id 
        WHERE l.visibility = 'public' 
        AND l.end_time > NOW() 
        ORDER BY l.created_at DESC";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Calculate Time Left
        $end = new DateTime($row['end_time']);
        $now = new DateTime();
        $interval = $now->diff($end);

        // Format time string
        if ($interval->days > 0) {
            $timeLeft = $interval->days . "d " . $interval->h . "h";
        } else {
            $timeLeft = $interval->h . "h " . $interval->i . "m";
        }

        // Image Handling
        $img = !empty($row['image_path']) ? $row['image_path'] : 'https://images.unsplash.com/photo-1595245840498-f2b7a956277b?w=600&h=400&fit=crop';

        // Map DB columns to JS object
        $auctionItems[] = [
            'id' => (int)$row['bids_id'],
            'name' => $row['title'],
            'category' => $row['category'],
            'currentBid' => (float)$row['price'],
            'startingBid' => (float)$row['starting_bid'],
            'minIncrement' => (float)$row['min_increment'],
            'image' => $img,
            'endTime' => $row['end_time'],
            'timeLeft' => $timeLeft,
            'bids' => (int)$row['bids_count'],
            'location' => $row['location'],
            'urgent' => ($interval->days < 1),
            'description' => $row['description'],
            'year' => $row['year'],
            'hours' => $row['hours_used'],
            'seller' => $row['full_name'] ?? 'Unknown Farmer'
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <title>Farm Bidding System</title>
    <?php include 'php/components/head_css.php'; ?>
    <link rel="stylesheet" href="css/index.css" />

    <style>
        /* 1. FORCE CARD DIMENSIONS */
        .auction-card {
            display: flex;
            flex-direction: column;
            height: 100%;
            /* Fills the container height */
            min-width: 280px;
            max-width: 280px;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        /* 2. FORCE UNIFORM IMAGE SIZE */
        .card-image-container {
            width: 100%;
            height: 180px;
            /* FIXED HEIGHT: The key fix */
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
            /* Prevents image from squishing */
        }

        .card-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* CROPS image nicely */
            object-position: center;
        }

        /* 3. ALIGN CONTENT & BUTTONS */
        .card-content {
            padding: 15px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            /* Pushes content to fill space */
            height: 100%;
        }

        /* 4. HANDLE TEXT LENGTH */
        .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 10px;
            height: 2.6em;
            /* Reserves space for exactly 2 lines */
            line-height: 1.3em;
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
        }

        /* 5. PUSH BUTTONS TO BOTTOM */
        .card-actions {
            margin-top: auto;
            /* Magically pushes buttons to bottom */
            padding-top: 15px;
            display: flex;
            gap: 10px;
        }

        /* 6. GRID LAYOUT FIX FOR SCROLL CONTAINERS */
        /* This ensures the scroll container respects the card height */
        .scrollContainer {
            display: flex;
            gap: 20px;
            padding: 10px 5px 20px 5px;
            /* Bottom padding for shadow */
            align-items: stretch;
            /* Stretches cards to same height */
        }
    </style>
</head>

<body>
    <?php include 'php/components/header.php'; ?>

    <main id="mainContent">

        <section class="container">
            <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="0" class="active"></button>
                    <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="1"></button>
                    <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="2"></button>
                </div>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="assets/images/mainPic1.webp" class="d-block w-100" alt="Tractor">
                        <div class="carousel-caption d-none d-md-block">
                            <h3>Premium Farm Equipment</h3>
                            <p>Bid on top-quality tractors and harvesters.</p>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="assets/images/mainPic2.webp" class="d-block w-100" alt="Harvest">
                        <div class="carousel-caption d-none d-md-block">
                            <h3>Harvest Season Deals</h3>
                            <p>Get ready for the season with our special auctions.</p>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="assets/images/mainPic3.webp" class="d-block w-100" alt="Field">
                    </div>
                </div>
            </div>
        </section>

        <div class="search-container">
            <input type="text" class="search-bar" id="searchInput" placeholder="🔍 Search auctions, equipment, locations...">
        </div>

        <div id="categorySections">
        </div>

        <div id="searchResults" class="hidden">
            <div class="container">
                <h2 class="big-heading">Search Results</h2>
                <div id="searchGrid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;"></div>
                <div id="noResults" class="hidden" style="text-align:center; padding:50px;">
                    <h3>No results found</h3>
                </div>
            </div>
        </div>

    </main>

    <div id="bidModal" class="modal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeBidModal()">&times;</button>
            <h2><i class="fas fa-gavel"></i> Place Bid</h2>
            <h5 id="bidItemName" style="color:#666; margin-bottom:5px;">Item Name</h5>
            <div class="form-group">
                <label>Current Bid: <strong id="bidCurrentPrice" style="color: var(--primary-green);">$0</strong></label>
            </div>
            <div class="form-group">
                <label>Your Offer</label>
                <input type="number" id="bidAmount" placeholder="Enter amount">
                <div id="quickBidRange" style="font-size:0.8rem; color:#888; margin-top:5px;"></div>
            </div>
            <button class="btn-primary" onclick="placeBid()">Confirm Bid</button>
        </div>
    </div>

    <div id="detailView" class="detail-view"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // --- 1. DATA FROM PHP ---
        const auctionItems = <?php echo json_encode($auctionItems); ?>;
        const wishlist = new Set();
        let currentViewItem = null;

        // --- 2. CATEGORY CONFIGURATION ---
        const categoryStructure = {
            "Featured Equipment": [{
                    name: "Tractors",
                    filter: i => i.category && i.category.toLowerCase().includes('tractor')
                },
                {
                    name: "Harvesters",
                    filter: i => i.category && i.category.toLowerCase().includes('harvester')
                },
                {
                    name: "All Bids",
                    filter: i => true
                }
            ],
            "Urgent Auctions": [{
                name: "Ending Soon",
                filter: i => i.urgent === true
            }]
        };

        // --- 3. INITIALIZATION ---
        document.addEventListener('DOMContentLoaded', function() {
            renderCategorySections();
            setupSearchListener();
            startLiveTimer();
            console.log("System Initialized with " + auctionItems.length + " items.");
        });

        // --- 4. RENDER FUNCTIONS ---
        function renderCategorySections() {
            const container = document.getElementById('categorySections');
            let html = '';

            if (auctionItems.length === 0) {
                container.innerHTML = '<div class="text-center py-5"><h3>No active auctions found.</h3></div>';
                return;
            }

            Object.entries(categoryStructure).forEach(([bigHeading, categories]) => {
                const hasItems = categories.some(cat => auctionItems.filter(item => cat.filter(item)).length > 0);

                if (hasItems) {
                    html += `<div class="category-section">
                        <h2 class="big-heading">${bigHeading}</h2>`;

                    categories.forEach(cat => {
                        const items = auctionItems.filter(item => cat.filter(item));
                        const uniqueItems = [...new Map(items.map(item => [item.id, item])).values()];

                        if (uniqueItems.length > 0) {
                            html += `
                            <h3 class="category-heading"><i class="fas fa-angle-right"></i> ${cat.name}</h3>
                            <div class="scroll-wrapper">
                                <button class="scrollButton left-btn" onclick="scrollContainer('cat-${cat.name.replace(/\s+/g, '')}','left')"><i class="fas fa-chevron-left"></i></button>
                                <div class="scrollContainer" id="cat-${cat.name.replace(/\s+/g, '')}">
                                    ${uniqueItems.map(item => renderAuctionCard(item)).join('')}
                                </div>
                                <button class="scrollButton right-btn" onclick="scrollContainer('cat-${cat.name.replace(/\s+/g, '')}','right')"><i class="fas fa-chevron-right"></i></button>
                            </div>`;
                        }
                    });
                    html += `</div>`;
                }
            });
            container.innerHTML = html;
        }

        function renderAuctionCard(item) {
            const isWishlisted = wishlist.has(item.id);

            // UPDATED: Removed inline styles from .card-actions so CSS can handle alignment
            return `
            <article class="auction-card ${item.urgent ? 'urgent' : ''}" onclick="showDetail(${item.id})">
                
                <div class="card-image-container">
                    <img src="${item.image}" alt="${item.name}" class="card-img" onerror="this.src='https://placehold.co/600x400?text=No+Image'">
                    <button class="wishlist-btn ${isWishlisted ? 'active' : ''}" onclick="toggleWishlist(${item.id}, event)">
                        <i class="fas fa-heart"></i>
                    </button>
                    ${item.urgent ? '<span class="badge-urgent">Ending Soon</span>' : ''}
                </div>

                <div class="card-content">
                    <h4 class="card-title">${item.name}</h4>
                    
                    <div class="price-row">
                        <span class="current-bid">$${item.currentBid.toLocaleString()}</span>
                        <span class="starting-bid" style="font-size:0.8em; color:#999; text-decoration:line-through;">$${item.startingBid.toLocaleString()}</span>
                    </div>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <i class="far fa-clock"></i> 
                            <span class="live-timer" data-end="${item.endTime}">${item.timeLeft}</span>
                        </div>
                        <div class="info-item"><i class="fas fa-gavel"></i> ${item.bids} bids</div>
                        <div class="info-item"><i class="fas fa-map-marker-alt"></i> ${item.location}</div>
                    </div>

                    <div class="card-actions">
                        <button class="btn btn-outline-secondary btn-sm" style="flex:1;" onclick="showDetail(${item.id}, event)">
                            View
                        </button>
                        <button class="btn btn-success btn-sm" style="flex:1;" onclick="openQuickBid(${item.id}, event)">
                            Bid Now
                        </button>
                    </div>
                </div>
            </article>`;
        }

        // --- 5. UTILITIES ---
        function scrollContainer(id, dir) {
            const container = document.getElementById(id);
            if (container) {
                const scrollAmount = 300;
                container.scrollBy({
                    left: dir === 'left' ? -scrollAmount : scrollAmount,
                    behavior: 'smooth'
                });
            }
        }

        function setupSearchListener() {
            document.getElementById('searchInput').addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase();
                if (query.length > 0) {
                    document.getElementById('categorySections').classList.add('hidden');
                    document.getElementById('searchResults').classList.remove('hidden');
                    const results = auctionItems.filter(i => i.name.toLowerCase().includes(query));
                    const grid = document.getElementById('searchGrid');
                    if (results.length > 0) {
                        grid.innerHTML = results.map(i => renderAuctionCard(i)).join('');
                        document.getElementById('noResults').classList.add('hidden');
                    } else {
                        grid.innerHTML = '';
                        document.getElementById('noResults').classList.remove('hidden');
                    }
                } else {
                    document.getElementById('categorySections').classList.remove('hidden');
                    document.getElementById('searchResults').classList.add('hidden');
                }
            });
        }

        function toggleWishlist(id, e) {
            e.stopPropagation();
            if (wishlist.has(id)) wishlist.delete(id);
            else wishlist.add(id);
            const btns = document.querySelectorAll(`button[onclick="toggleWishlist(${id}, event)"]`);
            btns.forEach(b => b.classList.toggle('active'));
        }

        // --- 6. MODAL ACTIONS ---
        function openQuickBid(id, e) {
            e.stopPropagation();
            const item = auctionItems.find(i => i.id === id);
            currentViewItem = item;
            document.getElementById('bidItemName').innerText = item.name;
            document.getElementById('bidCurrentPrice').innerText = "$" + item.currentBid.toLocaleString();

            const nextBid = item.currentBid + item.minIncrement;
            document.getElementById('bidAmount').value = nextBid;
            document.getElementById('quickBidRange').innerText = `Min bid increment: $${item.minIncrement}`;

            document.getElementById('bidModal').classList.add('show');
        }

        function closeBidModal() {
            document.getElementById('bidModal').classList.remove('show');
        }

        // ============================================
        // DYNAMIC BID INCREMENT CALCULATION
        // ============================================

        /**
         * Calculate minimum bid increment based on current price
         * @param {number} currentBid - The current highest bid
         * @returns {number} - The minimum increment required
         */
        function calculateMinIncrement(currentBid) {
            if (currentBid < 1000) {
                return 100; // For bids under 1,000
            } else if (currentBid < 10000) {
                return 500; // For 1,000 - 9,999
            } else if (currentBid < 100000) {
                return 5000; // For 10,000 - 99,999
            } else if (currentBid < 1000000) {
                return 50000; // For 100,000 - 999,999
            } else if (currentBid < 10000000) {
                return 500000; // For 1,000,000 - 9,999,999
            } else {
                return 5000000; // For 10,000,000+
            }
        }

        // ============================================
        // UPDATED PLACEBID FUNCTION
        // ============================================

        function placeBid() {
            if (!currentViewItem) return;

            const amount = parseFloat(document.getElementById('bidAmount').value);

            // Calculate dynamic minimum increment
            const dynamicIncrement = calculateMinIncrement(currentViewItem.currentBid);
            const minNext = currentViewItem.currentBid + dynamicIncrement;

            if (amount < minNext) {
                alert(`Bid must be at least Rs ${minNext.toLocaleString()}\n(Minimum increment: Rs ${dynamicIncrement.toLocaleString()})`);
                return;
            }

            // Store the old endTime before making changes
            const oldEndTime = currentViewItem.endTime;

            // Send bid to server
            fetch('php/bids/place_bid.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        bidId: currentViewItem.id,
                        bidAmount: amount
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(result => {
                    if (result.success) {
                        alert(result.message);

                        // 1. Update the Data Object
                        const itemIndex = auctionItems.findIndex(i => i.id === currentViewItem.id);
                        if (itemIndex !== -1) {
                            auctionItems[itemIndex].currentBid = result.newPrice;
                            auctionItems[itemIndex].bids = result.bidsCount;

                            // 2. Handle Time Extension
                            if (result.timeExtended && result.newEndTime) {
                                auctionItems[itemIndex].endTime = result.newEndTime;

                                // Update timers BEFORE re-rendering
                                const timers = document.querySelectorAll(`.live-timer[data-end="${oldEndTime}"]`);
                                timers.forEach(timer => {
                                    timer.setAttribute('data-end', result.newEndTime);
                                });
                            }
                        }

                        // 3. Update currentViewItem for consistency
                        currentViewItem.currentBid = result.newPrice;
                        currentViewItem.bids = result.bidsCount;
                        if (result.timeExtended && result.newEndTime) {
                            currentViewItem.endTime = result.newEndTime;
                        }

                        // 4. Re-render UI to show updated prices and bid counts
                        renderCategorySections();

                        // 5. Force immediate timer update after re-render
                        updateAllTimers();

                        // 6. Close modal
                        closeBidModal();
                    } else {
                        alert(result.message || 'Failed to place bid. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error placing bid:', error);
                    alert('Failed to place bid. Please check your connection and try again.');
                });
        }

        // ============================================
        // UPDATED OPENQUICKBID FUNCTION
        // ============================================

        function openQuickBid(id, e) {
            e.stopPropagation();
            const item = auctionItems.find(i => i.id === id);
            currentViewItem = item;

            document.getElementById('bidItemName').innerText = item.name;
            document.getElementById('bidCurrentPrice').innerText = "Rs " + item.currentBid.toLocaleString();

            // Calculate dynamic minimum increment
            const dynamicIncrement = calculateMinIncrement(item.currentBid);
            const nextBid = item.currentBid + dynamicIncrement;

            document.getElementById('bidAmount').value = nextBid;
            document.getElementById('quickBidRange').innerHTML =
                `Min increment: <strong>Rs ${dynamicIncrement.toLocaleString()}</strong><br>` +
                `Next minimum bid: <strong>Rs ${nextBid.toLocaleString()}</strong>`;

            document.getElementById('bidModal').classList.add('show');
        }

        // ============================================
        // HELPER FUNCTION FOR TIMER UPDATES
        // ============================================

        function updateAllTimers() {
            const timers = document.querySelectorAll('.live-timer');
            timers.forEach(timer => {
                const endTime = new Date(timer.getAttribute('data-end')).getTime();
                const now = new Date().getTime();
                const distance = endTime - now;

                if (distance < 0) {
                    timer.innerHTML = "<span style='color:red'>Auction Ended</span>";
                } else {
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    let timeString = "";
                    if (days > 0) timeString += `${days}d `;
                    if (hours > 0 || days > 0) timeString += `${hours}h `;
                    timeString += `${minutes}m ${seconds}s`;

                    timer.innerText = timeString;
                }
            });
        }

        // ============================================
        // EXAMPLE USAGE & TESTING
        // ============================================

        // Test the increment calculation
        console.log("Bid Increment Examples:");
        console.log("Rs 500 → Increment: Rs", calculateMinIncrement(500)); // 100
        console.log("Rs 5,000 → Increment: Rs", calculateMinIncrement(5000)); // 500
        console.log("Rs 50,000 → Increment: Rs", calculateMinIncrement(50000)); // 5,000
        console.log("Rs 500,000 → Increment: Rs", calculateMinIncrement(500000)); // 50,000
        console.log("Rs 5,000,000 → Increment: Rs", calculateMinIncrement(5000000)); // 500,000

        function showDetail(id, e) {
            if (e) e.stopPropagation();
            const item = auctionItems.find(i => i.id === id);
            if (!item) return;

            const detail = document.getElementById('detailView');

            // Build detail HTML
            const detailHTML = `
        <div class="detail-modal-content">
            <button class="back-btn" onclick="hideDetail()">
                <i class="fas fa-arrow-left"></i> Back
            </button>

            <div class="detail-modal-header">
                <h2 class="detail-modal-title" style="text-align: center;>
                    <i class="fas fa-info-circle text-center"></i> Auction Details
                </h2>
                <div class="detail-modal-badges">
                    ${item.urgent 
                        ? '<span class="detail-status-badge badge-urgent"><i class="fas fa-fire"></i> Ending Soon</span>'
                        : '<span class="detail-status-badge badge-active"><i class="fas fa-circle"></i> Active</span>'
                    }
                </div>
            </div>

            <div class="detail-modal-body">
                <div class="detail-image-section">
                    <img src="${item.image}" 
                         class="detail-bid-image" 
                         alt="${item.name}"
                         onerror="this.src='https://placehold.co/600x400?text=No+Image'">
                </div>

                <div class="detail-info-section">
                    <h3 class="detail-item-title">${item.name}</h3>
                    
                    <div class="detail-price-box">
                        <div class="detail-price-item">
                            <span class="detail-price-label">Current Price</span>
                            <span class="detail-price-value primary">$${item.currentBid.toLocaleString()}</span>
                        </div>
                        <div class="detail-price-item">
                            <span class="detail-price-label">Starting Price</span>
                            <span class="detail-price-value">$${item.startingBid.toLocaleString()}</span>
                        </div>
                        <div class="detail-price-item">
                            <span class="detail-price-label">Min Increment</span>
                            <span class="detail-price-value">$${item.minIncrement.toLocaleString()}</span>
                        </div>
                    </div>

                    <div class="detail-stats-grid">
                        <div class="detail-stat-card">
                            <i class="fas fa-gavel stat-icon"></i>
                            <div class="stat-content">
                                <span class="stat-value">${item.bids}</span>
                                <span class="stat-label">Total Bids</span>
                            </div>
                        </div>
                        <div class="detail-stat-card">
                            <i class="far fa-clock stat-icon"></i>
                            <div class="stat-content">
                                <span class="stat-value live-timer" data-end="${item.endTime}">${item.timeLeft}</span>
                                <span class="stat-label">Time Left</span>
                            </div>
                        </div>
                    </div>

                    <div class="detail-details-grid">
                        <div class="detail-row">
                            <span class="detail-label"><i class="fas fa-tag"></i> Category</span>
                            <span class="detail-value">${item.category}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><i class="fas fa-map-marker-alt"></i> Location</span>
                            <span class="detail-value">${item.location}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><i class="fas fa-user"></i> Seller</span>
                            <span class="detail-value">${item.seller}</span>
                        </div>
                        ${item.year ? `
                        <div class="detail-row">
                            <span class="detail-label"><i class="fas fa-calendar-alt"></i> Year</span>
                            <span class="detail-value">${item.year}</span>
                        </div>` : ''}
                        ${item.hours ? `
                        <div class="detail-row">
                            <span class="detail-label"><i class="fas fa-tachometer-alt"></i> Operating Hours</span>
                            <span class="detail-value">${Number(item.hours).toLocaleString()} hrs</span>
                        </div>` : ''}
                    </div>

                    ${item.description ? `
                    <div class="detail-description">
                        <h4 class="description-title"><i class="fas fa-align-left"></i> Description</h4>
                        <p class="description-text">${item.description}</p>
                    </div>` : ''}

                    <div class="detail-modal-actions">
                        <button class="btn-detail-action btn-bid-now" onclick="handleBidClick(${item.id})">
                            <i class="fas fa-gavel"></i> Place Bid Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

            detail.innerHTML = detailHTML;
            detail.classList.add('show');
            document.body.classList.add('detail-open');
        }

        function hideDetail() {
            const detail = document.getElementById('detailView');
            detail.classList.remove('show');
            document.body.classList.remove('detail-open');
        }

        function handleBidClick(id) {
            hideDetail();
            openQuickBid(id, {
                stopPropagation: () => {}
            });
        }

        function startLiveTimer() {
            setInterval(() => {
                const timers = document.querySelectorAll('.live-timer');

                timers.forEach(timer => {
                    const endTime = new Date(timer.getAttribute('data-end')).getTime();
                    const now = new Date().getTime();
                    const distance = endTime - now;

                    if (distance < 0) {
                        timer.innerHTML = "<span style='color:red'>Auction Ended</span>";
                    } else {
                        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                        let timeString = "";
                        if (days > 0) timeString += `${days}d `;
                        if (hours > 0 || days > 0) timeString += `${hours}h `;
                        timeString += `${minutes}m ${seconds}s`;

                        timer.innerText = timeString;
                    }
                });
            }, 1000);
        }
    </script>
</body>

</html>