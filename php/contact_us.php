<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>

    <title>Contact Us - Farm Bidding System</title>

    <?php include 'components/head_css.php'; ?>

    <!-- Page-Specific CSS -->
    <link rel="stylesheet" href="../css/contact_us.css" />
</head>

<body>
    <?php include 'components/header.php'; ?>

    <!-- Main Content -->
    <main class="container my-5 py-4">
        <!-- Page Header -->
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold mb-3">
                    <i class="fas fa-envelope text-primary"></i> Contact Us
                </h1>
                <p class="lead text-muted">
                    We'd love to hear from you! Send us a message and we'll respond as soon as possible.
                </p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Contact Form -->
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="card-title h4 mb-4 fw-semibold">
                            <i class="fas fa-paper-plane text-success me-2"></i> Send Us a Message
                        </h3>
                        
                        <form id="contactForm" novalidate>
                            <!-- Name -->
                            <div class="mb-3">
                                <label for="fullName" class="form-label fw-medium">
                                    <i class="fas fa-user text-muted me-1"></i> Full Name
                                </label>
                                <input type="text" class="form-control form-control-lg" id="fullName" 
                                    placeholder="Enter your full name" required>
                                <div class="invalid-feedback">
                                    Please enter your full name.
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label fw-medium">
                                    <i class="fas fa-envelope text-muted me-1"></i> Email Address
                                </label>
                                <input type="email" class="form-control form-control-lg" id="email" 
                                    placeholder="your.email@example.com" required>
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="mb-3">
                                <label for="phone" class="form-label fw-medium">
                                    <i class="fas fa-phone text-muted me-1"></i> Phone Number <span class="text-muted">(Optional)</span>
                                </label>
                                <input type="tel" class="form-control form-control-lg" id="phone" 
                                    placeholder="+1 (555) 000-0000">
                            </div>

                            <!-- Subject -->
                            <div class="mb-3">
                                <label for="subject" class="form-label fw-medium">
                                    <i class="fas fa-tag text-muted me-1"></i> Subject
                                </label>
                                <select class="form-select form-select-lg" id="subject" required>
                                    <option value="">Select a subject...</option>
                                    <option value="general">General Inquiry</option>
                                    <option value="support">Technical Support</option>
                                    <option value="billing">Billing Question</option>
                                    <option value="partnership">Partnership Opportunity</option>
                                    <option value="feedback">Feedback & Suggestions</option>
                                    <option value="other">Other</option>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a subject.
                                </div>
                            </div>

                            <!-- Message -->
                            <div class="mb-4">
                                <label for="message" class="form-label fw-medium">
                                    <i class="fas fa-comment-dots text-muted me-1"></i> Message
                                </label>
                                <textarea class="form-control form-control-lg" id="message" rows="5" 
                                    placeholder="Tell us how we can help you..." required></textarea>
                                <div class="invalid-feedback">
                                    Please enter your message.
                                </div>
                            </div>

                            <!-- Privacy Agreement -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="privacy" required>
                                    <label class="form-check-label" for="privacy">
                                        I agree to the <a href="#" class="text-decoration-none">Privacy Policy</a> and consent to being contacted
                                    </label>
                                    <div class="invalid-feedback">
                                        You must agree before submitting.
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i> Send Message
                                </button>
                                <button type="reset" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-redo me-2"></i> Reset Form
                                </button>
                            </div>
                        </form>

                        <!-- Success Message (Hidden by default) -->
                        <div id="successMessage" class="alert alert-success mt-4 d-none" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Success!</strong> Your message has been sent. We'll get back to you soon!
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information Sidebar -->
            <div class="col-lg-5">
                <!-- Contact Details Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <h3 class="card-title h5 mb-4 fw-semibold">
                            <i class="fas fa-info-circle text-primary me-2"></i> Contact Information
                        </h3>

                        <!-- Location -->
                        <div class="mb-4">
                            <h5 class="h6 fw-semibold mb-2">
                                <i class="fas fa-map-marker-alt text-danger me-2"></i> Our Location
                            </h5>
                            <p class="text-muted mb-0">
                                123 Farm Auction Lane<br>
                                Des Moines, Iowa 50309<br>
                                United States
                            </p>
                        </div>

                        <hr class="my-3">

                        <!-- Phone -->
                        <div class="mb-4">
                            <h5 class="h6 fw-semibold mb-2">
                                <i class="fas fa-phone text-success me-2"></i> Phone
                            </h5>
                            <p class="mb-1">
                                <a href="tel:+15551234567" class="text-decoration-none text-dark">+1 (555) 123-4567</a>
                            </p>
                            <small class="text-muted">Monday - Friday: 8:00 AM - 6:00 PM CST</small>
                        </div>

                        <hr class="my-3">

                        <!-- Email -->
                        <div class="mb-4">
                            <h5 class="h6 fw-semibold mb-2">
                                <i class="fas fa-envelope text-primary me-2"></i> Email
                            </h5>
                            <p class="mb-1">
                                <a href="mailto:support@farmauction.com" class="text-decoration-none text-dark">support@farmauction.com</a>
                            </p>
                            <small class="text-muted">We respond within 24 hours</small>
                        </div>

                        <hr class="my-3">

                        <!-- Social Media -->
                        <div>
                            <h5 class="h6 fw-semibold mb-3">
                                <i class="fas fa-share-alt text-info me-2"></i> Follow Us
                            </h5>
                            <div class="d-flex gap-2">
                                <a href="#" class="btn btn-outline-primary" aria-label="Facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="btn btn-outline-info" aria-label="Twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="#" class="btn btn-outline-danger" aria-label="Instagram">
                                    <i class="fab fa-instagram"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary" aria-label="LinkedIn">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Help Card -->
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h3 class="card-title h5 mb-3 fw-semibold">
                            <i class="fas fa-question-circle text-warning me-2"></i> Quick Help
                        </h3>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <a href="#" class="text-decoration-none d-flex align-items-center">
                                    <i class="fas fa-chevron-right text-success me-2 small"></i>
                                    <span>How to Place a Bid</span>
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="#" class="text-decoration-none d-flex align-items-center">
                                    <i class="fas fa-chevron-right text-success me-2 small"></i>
                                    <span>Seller Guidelines</span>
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="#" class="text-decoration-none d-flex align-items-center">
                                    <i class="fas fa-chevron-right text-success me-2 small"></i>
                                    <span>Payment Methods</span>
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="#" class="text-decoration-none d-flex align-items-center">
                                    <i class="fas fa-chevron-right text-success me-2 small"></i>
                                    <span>Shipping Information</span>
                                </a>
                            </li>
                            <li class="mb-0">
                                <a href="#" class="text-decoration-none d-flex align-items-center fw-semibold">
                                    <i class="fas fa-chevron-right text-success me-2 small"></i>
                                    <span>View Full FAQ</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer placeholder -->

    <!-- Scripts -->
    <script src="../js/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a2e0e6ad65.js" crossorigin="anonymous"></script>
    <script src="../js/contact_us.js"></script>
</body>

</html>