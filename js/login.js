
// --- KEEP this part to show errors from PHP ---
const urlParams = new URLSearchParams(window.location.search);
const errorBox = document.getElementById("loginError"); // Ensure you have a div with this ID in HTML

if (urlParams.has('error')) {
    if (errorBox) {
        errorBox.classList.remove("d-none"); // Or errorBox.style.display = 'block';
        
        const errType = urlParams.get('error');
        if (errType === 'wrong_pass') {
            errorBox.textContent = "Incorrect password.";
        } else if (errType === 'not_found') {
            errorBox.textContent = "Email not found.";
        }
    }
}